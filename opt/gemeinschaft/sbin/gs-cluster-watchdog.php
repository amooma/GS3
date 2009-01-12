#!/usr/bin/php -q
<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* 
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301, USA.
\*******************************************************************/

######################################################################
#
#  Checking if nodes of "gemeinschaft" cluster are alive
#
######################################################################

define( 'GS_VALID', true );  /// this is a parent file

require_once( dirName(__FILE__) .'/../inc/conf.php' );
include_once( '/opt/gemeinschaft/etc/gs-cluster-watchdog.conf' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


function getListenToIPs()
{
	$file = IPFILE ;
	if (! @file_exists( $file )) return false;
	if (! is_array($lines = @file( $file ))) return false;
	$ips = array();
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line=='' || @$line[0]=='#') continue;
		if (! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $line, $m)) continue;
		$ips[] = preg_replace( '/\b0{1,2}(\d)/', '$1', $m[0] );
	}
	return $ips;
}

function write_log( $log_string )
{
	global $active;
	
	$logfilep = @fOpen(LOGFILE, 'a');
	if (! $logfilep) {
		echo "ERROR - Cannot access logfile - nothing will be logged\n";
		echo "LOG> $log_string\n";
		return 0;
	}
	$logentry = date('Y-m-d H:i:s') .' '. $log_string ."\n";
	if ($active > 1) echo $logentry; 
	@fWrite($logfilep, $logentry, strLen($logentry));
	fClose($logfilep);
}

function writeIPFile( $ip_file, $ip )
{
	global $node;
	global $ips;
	
	if (! array_search($ip, $ips)) {
		$ipfilep = @fOpen($ip_file, 'a');
		if (!$ipfilep) {
			write_log("Cannot open $ip_file for writing! Bad things will happen!");
			return 1;
		}
		if (@fWrite($ipfilep,$ip."\n") > 6) {
			write_log("IP $ip written to $ip_file");
		} else {
			write_log("Failed to write  $ip to $ip_file");
		}
		fClose($ipfilep);
	} else {
		write_log("IP $ip is already in $ip_file");
	}
}

function writeDataFile( $data_file )
{
	global $node;
	
	$save_struct = serialize($node);
	$datafilep = @fOpen($data_file, 'wb');
	if (!$datafilep) {
		write_log("Cannot open $data_file for writing! Bad things will happen!");
		return 1;
	}  
	@fWrite($datafilep, $save_struct, strLen($save_struct));
	fClose($datafilep);
	write_log("Data file $data_file written.");
}

function readDataFile( $data_file )
{
	global $node;
	
	$datafilep = @fOpen($data_file, 'rb');
	if (!$datafilep)  {
		write_log("Cannot open $data_file for reading! Using cluster data from configuration. This will reset *all* states!");
		return 1;
	}
	$datafilesize = fileSize($data_file);
	$save_struct = @fRead($datafilep, $datafilesize);
	$node = unSerialize($save_struct);
	fClose($datafilep);
}


function checkIfNodeAlive( $node_id )
{
	global $node;
	
	$cmd = GS_DIR .'sbin/check-sip-alive '. qsa( 'sip:'
		. $node[$node_id]['extension'] .'@'. $node[$node_id]['dynamic_ip'] )
		.' '. (int)SIP_TIMEOUT;
	
	$err=0;
	$out=array();
	@exec($cmd .' 1>>/dev/null 2>>/dev/null', $out, $err);
	return $err;
}

function sendArp( $node_id )
{
	global $node;
	
	//$cmd = '/usr/sbin/send_arp '.$node[$node_id]['dynamic_ip'].' '.$node[$node_id]['local_mac'].' '.$node[$node_id]['broadcast']. ' FF:FF:FF:FF:FF:FF '.$node[$node_id]['local_interface'];
	
	$cmd = '/sbin/arping -c 3 -I '. qsa( $node[$node_id]['local_interface'] ) .' -s '. qsa( $node[$node_id]['dynamic_ip'] ) .' -A '. qsa( $node[$node_id]['broadcast'] );
	
	write_log("Execute $cmd");
	$err=0;
	 $out=array();
	@exec($cmd .' 1>>/dev/null 2>>/dev/null', $out, $err);
}

function stonith( $node_id )
{
	global $node;
	
	$cmd = GS_DIR.'sbin/stonith.sh '. qsa( $node[$node_id]['dynamic_ip'] );
	write_log("Execute $cmd");
	$err=0;
	$out=array();
	@exec($cmd .' 1>>/dev/null 2>>/dev/null', $out, $err);
	return $err;
}

function takeOverIP( $node_id )
{
	global $node;
	
	$cmd = '/sbin/ifconfig '. qsa( $node[$node_id]['local_interface'] ) .' '. qsa( $node[$node_id]['dynamic_ip'] ) .' netmask '. qsa( $node[$node_id]['netmask'] ) . ' broadcast '. qsa( $node[$node_id]['broadcast'] );
	write_log("Execute $cmd");
	$err=0;
	$out=array();
	@exec($cmd .' 1>>/dev/null', $out, $err);
	
	$cmd = '/sbin/route add -host '. qsa( $node[$node_id]['dynamic_ip'] ) .' '. qsa( $node[$node_id]['local_interface'] );
	write_log("Execute $cmd");
	$err=0;
	$out=array();
	@exec($cmd .' 1>>/dev/null', $out, $err);
}

function sendAlarm( $node_id )
{
	global $node;
	
	$cmd = 'ALARM! (not implemented yet)';
	write_log("$cmd");
}
  
function restartLocalAsterisk()
{  
	$cmd = '/etc/init.d/asterisk stop';
	write_log("Execute $cmd");
	$err=0;
	$out=array();
	@exec($cmd .' 1>>/dev/null 2>>/dev/null', $out, $err);
	
	$cmd = GS_DIR.'sbin/start-asterisk';
	write_log("Execute $cmd");
	$err=0;
	$out=array();
	@exec($cmd .' 1>>/dev/null 2>>/dev/null', $out, $err);
	return $err;
}



if (count($argv) > 1) {
	switch ($argv[1]) {
		case 'active' : $active = 1; break;
		case 'debug'  : $active = 2; break;
		case 'testing': $active = 3; break;
		default       : $active = 0;
	}
} else
	$active = 0;

if ($active == 0) {
	echo
		"This ist the cluster watchdog of the Gemeinschaft project \n",
		"- it should not be started directly by the user\n",
		"Please read the documentation for usage instructions.\n";
	exit(1);
}

$starttime = time();
$endtime = $starttime + 60 - (SLEEP_SECONDS * 2);

readDataFile(DATAFILE);

$node[0]['pid'] = getMyPid();

for ($node_id=1; $node_id<count($node); $node_id++) {
	if ($node[$node_id]['status'   ] == STAT_FAILED
	&&  $node[$node_id]['timestamp'] < (time() - REC_PERIOD*60)
	) {
		$node[$node_id]['status'] = STAT_NORMAL;
	}
}

$ips = getListenToIPs();

while (time() < $endtime)  {
	
	for ($node_id=1; $node_id<count($node); $node_id++) {
		
		$node_status = checkIfNodeAlive($node_id);
		
		if ($node_status > STAT_NORMAL) {
			if ($node_status == STAT_NORMAL) {
				$node[$node_id]['status'   ] = STAT_FAILED;
				$node[$node_id]['timestamp'] = time();
			}
			write_log("*** Node $node_id is not alive with status ". $node[$node_id]['status'] .". Tried ". $node[$node_id]['tries'] ." times.");
			
			if ($node[$node_id]['tries'] == RETRY) {
				if ($node_id > 0) {
					if ($active < 3) {
						stonith($node_id);
						takeOverIP($node_id);
						sendArp($node_id);
						writeIPFile(IPFILE,$node[$node_id]['dynamic_ip']);
						restartLocalAsterisk();
						sendAlarm($node_id);
					}
					$node[$node_id]['status'   ] = STAT_TAKEN;
					$node[$node_id]['timestamp'] = time();
					write_log("Set status of node $node_id to ". $node[$node_id]['status']);
					writeDataFile(DATAFILE);
				} else {
					sendAlarm($node_id);
				}
			}
			
			$node[$node_id]['tries']++;
			writeDataFile(DATAFILE);
		} else {
			if ($active > 1)
				write_log("Node $node_id alive with status ". $node[$node_id]['status']);
		}
	}
	if (time() < $endtime) sleep(SLEEP_SECONDS);
}


?>