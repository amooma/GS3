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

define( 'GS_VALID', true );  /// this is a parent file

require_once( dirName(__FILE__) .'/../inc/conf.php' );
require_once( dirName(__FILE__) .'/../etc/gs-cluster-watchdog.conf' );
include_once( GS_DIR .'lib/getopt.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


function ReadDataFile( $data_file )
{
	global $node;
	
	@$datafilep=fopen($data_file,'r');
	if (!$datafilep)  {
		gs_script_error("Cannot open $data_file for reading!");
		return 1;
	}
	$datafilesize=filesize($data_file);
	$save_struct=fread($datafilep,$datafilesize);
	$node=unserialize($save_struct);
	fclose($datafilep);
}

function WriteDataFile( $data_file )
{
	global $node;
	
	$save_struct = serialize($node);
	@$datafilep=fopen($data_file,'w');
	if (!$datafilep) {
		gs_script_error("Cannot open $data_file for writing!");
	}
	fwrite($datafilep,$save_struct);
	fclose($datafilep);
}

function ExecuteRemoteCommands( $node_id )
{
	global $node;
	
	//$exec_string='/usr/sbin/send_arp '.$node[$node_id]['dynamic_ip'].' '.$node[$node_id]['remote_mac'].' '.$node[$node_id]['broadcast']. ' FF:FF:FF:FF:FF:FF '.$node[$node_id]['local_interface'];
	
	$cmd='/sbin/ifconfig '. qsa( $node[$node_id]['remote_interface'] ) .' '. qsa( $node[$node_id]['dynamic_ip'] ) .' netmask '. qsa( $node[$node_id]['netmask'] ) .' broadcast '. qsa( $node[$node_id]['broadcast'] );
	$exec_string = 'ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root '. qsa($node[$node_id]['static_ip']) .' '. qsa($cmd);
	echo "Execute: $exec_string\n";
	exec($exec_string,$ret_array,$ret_val);
	
	if ($ret_val > 0) return $ret_val;
	
	$cmd='/sbin/arping -c 3 -I '. qsa( $node[$node_id]['remote_interface'] ) .' -s '. qsa( $node[$node_id]['dynamic_ip'] ) .' -A '. qsa( $node[$node_id]['broadcast'] );
	$exec_string = 'ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root '. qsa($node[$node_id]['static_ip']) .' '. qsa($cmd);
	echo "Execute: $exec_string\n";
	exec($exec_string,$ret_array,$ret_val);
	
	if ($ret_val > 0) return $ret_val;
	
	$cmd=GS_DIR.'/sbin/start-asterisk';
	$exec_string = 'ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root '. qsa($node[$node_id]['static_ip']) .' '. qsa($cmd);
	echo "Execute: $exec_string\n";
	exec($exec_string,$ret_array,$ret_val);
	
	return $ret_val;
}

function ReleaseIP( $node_id )
{
	global $node;
	
	$exec_string='/sbin/ifconfig '. qsa( $node[$node_id]['local_interface'] ) .' down';
	echo "Execute: $exec_string\n";
	exec($exec_string,$ret_array,$ret_val);
	
	return $ret_val;
}

function WriteIPFile( $ip_file, $ip )
{
	@ $ip_lines=file($ip_file);
	
	@$ipfilep=fopen($ip_file,'w');
	if (!$ipfilep) {
		echo "Cannot open $ip_file for reading! Please check access rights!\n";
		return 1;
	}
	
	foreach($ip_lines as $line){
		if (trim($line)!=$ip) fwrite($ipfilep,$line);
	}
	
	fclose($ipfilep);
}

function RestartLocalAsterisk()
{
	$exec_string=GS_DIR.'sbin/start-asterisk';
	echo "Execute $exec_string";
	exec($exec_string,$ret_array,$ret_val);
	return $ret_val;
}

/***********************************************************
*    the shell parameters
***********************************************************/
$usage = 'Usage: '. baseName(__FILE__) .' [--host=<host_id>] [--release] [--show-config] [--show-data] [--clear-data]';

$opts = @getOptsNoMultiples( '',
	array(
		'host=',
		'release',
		'clear-data',
		'show-config',
		'show-data'
	),
	$usage
);


function show_nodes( $nodes )
{
	echo '# NODES: ', count($nodes), "\n\n";
	if (count($nodes) < 1) return;
	
	$props = array();
	foreach ($nodes[0] as $k => $v) $props[] = $k;
	
	foreach($nodes as $i => $node) {
		echo 'NODE ', $i, "\n";
		foreach ($props as $k)
			echo str_pad($k,12,' ',STR_PAD_LEFT), ': ', $node[$k], "\n";
		echo "\n";
	}
}


if (array_key_exists('show-config', $opts)) {
	echo "SLEEP_SECONDS: ".SLEEP_SECONDS."\n";
	echo "SIP_TIMEOUT: ".SIP_TIMEOUT."\n";
	echo "RETRY: ".RETRY."\n";
	echo "GS_DIR: ".GS_DIR."\n";
	echo "DATAFILE: ".DATAFILE."\n";
	echo "LOGFILE: ".LOGFILE."\n";
	echo "IPFILE: ".IPFILE."\n";
	show_nodes( $node );
	die(0);
}

if (array_key_exists('clear-data', $opts)) {
	WriteDataFile(DATAFILE);
	die(0);
}

ReadDataFile(DATAFILE);

if (array_key_exists('show-data', $opts)) {
	show_nodes( $node );
	die(0);
}

if (! isSet($opts['host'])) {
	gs_script_invalid_usage( $usage );
} else if (count($node) <= $opts['host']) {
	gs_script_error("Host ID out of range.");
}

switch ( (int)$node[$opts['host']]['status'] ) {
	
	case 0:
		echo "Host ".$opts['host']." (".$node[$opts['host']]['dynamic_ip'].") is in status NORMAL\n";
		die(0);
	
	case 1:
		echo "Host ".$opts['host']." (".$node[$opts['host']]['dynamic_ip'].") is in status FAILED\n";
		if (array_key_exists('release', $opts)) {
			$node[$opts['host']]['status']='0';
			$node[$opts['host']]['tries']='0';
			WriteDataFile(DATAFILE);
			echo "Host ".$opts['host']." (".$node[$opts['host']]['dynamic_ip'].") set status to NORMAL\n";
		}
		break;
	
	case 2:
		echo "Host ".$opts['host']." (".$node[$opts['host']]['dynamic_ip'].") is in status TAKEN\n";
		if (array_key_exists('release', $opts)) {
			if (ReleaseIP($opts['host'])) gs_script_error("Failed to release IP");
			if (ExecuteRemoteCommands($opts['host']))
				gs_script_error("Failed to execute remote commands");
			WriteIPFile(IPFILE,$node[$opts['host']]['dynamic_ip']);
			$node[$opts['host']]['status']='0';
			$node[$opts['host']]['tries']='0';
			WriteDataFile(DATAFILE);
			echo "Host ".$opts['host']." (".$node[$opts['host']]['dynamic_ip'].") set status to NORMAL\n";
			echo "Restarting asterisk now!\n";
			RestartLocalAsterisk();
		}
		break;
	
	default:
		gs_script_error("Invalid exit status.");
}


?>