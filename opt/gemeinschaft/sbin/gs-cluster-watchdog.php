#!/usr/bin/php -q
<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1703 $
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

function GetListenToIPs()
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
	
	@$logfilep=fopen(LOGFILE,'a');
	if (! $logfilep) {
		echo "ERROR - Cannot access logfile - nothing will be logged\n";
		echo "LOG> $log_string\n";
		return 0;
	}
	$logentry = date("Y-m-d H:i:s").' '.$log_string."\n";
	if ($active > 1) echo $logentry; 
	fwrite($logfilep,$logentry);
	fclose($logfilep);
}

function WriteIPFile( $ip_file, $ip )
{
	global $node;
	global $ips;
	
	if (!array_search($ip, $ips)) {
		
		@$ipfilep=fopen($ip_file,'a');
		if (!$ipfilep) {
			write_log("Cannot open $ip_file for writing! Bad things will happen!");
			return 1;
		}
		if (fwrite($ipfilep,$ip."\n")>6) {
			write_log("IP $ip written to $ip_file");
		} else {
			write_log("Failed to write  $ip to $ip_file");
		}
		fclose($ipfilep);
	} else {
		write_log("IP $ip is already in $ip_file");
	}
}

function WriteDataFile( $data_file )
{
	global $node;
	
	$save_struct = serialize($node);
	@$datafilep=fopen($data_file,'w');
	if (!$datafilep) {
		write_log("Cannot open $data_file for writing! Bad things will happen!");
		return 1;
	}  
	fwrite($datafilep,$save_struct);
	fclose($datafilep);
	write_log("Data File $data_file written.");
}

function ReadDataFile( $data_file )
{
	global $node;
	
	@$datafilep=fopen($data_file,'r');
	if (!$datafilep)  {
		write_log("Cannot open $data_file for reading! Using cluster data from configuration. This will reset *all* states!");
		return 1;
	}
	$datafilesize=filesize($data_file);
	$save_struct=fread($datafilep,$datafilesize);
	$node=unserialize($save_struct);
	fclose($datafilep);
}


function CheckIfNodeAlive( $node_id )
{
	global $node;
	
	$exec_string = GS_DIR .'sbin/check-sip-alive '. escapeShellArg( 'sip:'
		. $node[$node_id]['extension'] .'@'. $node[$node_id]['dynamic_ip'] )
		.' '. (int)SIP_TIMEOUT;
	
	#$exec_string='/usr/local/bin/sipsak -s sip:'.$node[$node_id]['extension'].'@'.$node[$node_id]['dynamic_ip'];
	exec($exec_string,$ret_array,$ret_val);
	
	return $ret_val;
}

function SendArp( $node_id )
{
	global $node;
	
	//$exec_string='/usr/sbin/send_arp '.$node[$node_id]['dynamic_ip'].' '.$node[$node_id]['local_mac'].' '.$node[$node_id]['broadcast']. ' FF:FF:FF:FF:FF:FF '.$node[$node_id]['local_interface'];
	
	$exec_string='/sbin/arping -c 3 -I '. escapeShellArg( $node[$node_id]['local_interface'] ) .' -s '. escapeShellArg( $node[$node_id]['dynamic_ip'] ) .' -A '. escapeShellArg( $node[$node_id]['broadcast'] );
	
	write_log("Execute $exec_string");
	exec($exec_string,$ret_array,$ret_val);
}

function Stonith( $node_id )
{
	global $node;
	
	$exec_string=GS_DIR.'sbin/stonith.sh '. escapeShellArg( $node[$node_id]['dynamic_ip'] );
	write_log("Execute $exec_string");
	exec($exec_string,$ret_array,$ret_val);
	return $ret_val;
}

function TakeOverIP( $node_id )
{
	global $node;
	
	$exec_string='/sbin/ifconfig '. escapeShellArg( $node[$node_id]['local_interface'] ) .' '. escapeShellArg( $node[$node_id]['dynamic_ip'] ) .' netmask '. escapeShellArg( $node[$node_id]['netmask'] ) . ' broadcast '. escapeShellArg( $node[$node_id]['broadcast'] );
	write_log("Execute $exec_string");
	exec($exec_string,$ret_array,$ret_val);
	$exec_string='/sbin/route add -host '. escapeShellArg( $node[$node_id]['dynamic_ip'] ) .' '. escapeShellArg( $node[$node_id]['local_interface'] );
	write_log("Execute $exec_string");
	exec($exec_string,$ret_array,$ret_val);
}

function SendAlarm( $node_id )
{
	global $node;
	
	$exec_string='ALARM! (not implemented yet)';
	write_log("$exec_string");
}
  
function RestartLocalAsterisk()
{  
	$exec_string=GS_DIR.'sbin/start-asterisk';
	write_log("Execute $exec_string");
	exec($exec_string,$ret_array,$ret_val);
	return $ret_val;
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
	echo "This ist the cluster watchdog of the Gemeinschaft project - it should not be started directly by the user\nPlease read the documentation for usage instructions.\n";
	die(0);
}

$starttime=time();
$endtime=$starttime + 60 - (SLEEP_SECONDS * 2);

ReadDataFile(DATAFILE);

$node[0]['pid']=getmypid();

for ($node_id=1; $node_id<count($node); $node_id++) {
	if (($node[$node_id]['status'] ==  STAT_FAILED) && ($node[$node_id]['timestamp'] < (time() - (REC_PERIOD * 60))))
		$node[$node_id]['status'] = STAT_NORMAL;
}

$ips=GetListenToIPs();

while (time() < $endtime)  {
	
	for ($node_id=1; $node_id<count($node); $node_id++) {
		
		$node_status=CheckIfNodeAlive($node_id);
		
		if ($node_status > STAT_NORMAL) {
			if ($node_status == STAT_NORMAL) {
				$node[$node_id]['status'] = STAT_FAILED;
				$node[$node_id]['timestamp'] = time();
			}
			write_log("*** Node $node_id is not alive with status ". $node[$node_id]['status'] .". Tried ". $node[$node_id]['tries'] ." times.");
			
			if ($node[$node_id]['tries'] == RETRY) {
				if ($node_id > 0) {
					if ($active < 3) {
						Stonith($node_id);
						TakeOverIP($node_id);
						SendArp($node_id);
						WriteIPFile(IPFILE,$node[$node_id]['dynamic_ip']);
						RestartLocalAsterisk();
						SendAlarm($node_id);
					}
					$node[$node_id]['status'] = STAT_TAKEN;
					$node[$node_id]['timestamp'] = time();
					write_log("Set status of node $node_id to ". $node[$node_id]['status']);
					WriteDataFile(DATAFILE);
				} else {
					SendAlarm($node_id);
				}
			}
			
			$node[$node_id]['tries']++;
			WriteDataFile(DATAFILE);
		} else {
			if ($active > 1) write_log("Node $node_id alive with status ". $node[$node_id]['status']);
		}
	}
	if (time() < $endtime) sleep(SLEEP_SECONDS);
}


?>