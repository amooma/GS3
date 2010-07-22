<?php
/*
* Retrieve Queues Status
* Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*/
define( 'GS_VALID', true );
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/sbclient.php' );

function return_error($errcode, $errstr)
{
	return '[\'ERROR_STR\',\''.htmlentities($errstr).'\'],[\'ERROR_TYPE\', '.$errcode.']';
}

function get_data_from_server($host, $port, $extensions, $queues)
{

	$client =new SBClient($host, $port);

	$client->sendmsg_extgroupset(127, $extensions);
	$client->sendmsg_queuegroupset(128, $queues);

	$line = '';
	$data = $client->receive();
	$client->close();
	foreach($data as $message) {
		if ($message['type'] != 0x61 && $message['type'] != 0x71 && $message['type'] != 0x63 && $message['type'] != 0x73) continue;
		if (!is_array($message['data'])) continue;
		if (!array_key_exists('group', $message['data'])) continue;
		if (!array_key_exists('states', $message['data'])) continue;
		if (!is_array($message['data']['states'])) continue;
		if (count($message['data']['states']) == 0) continue;

		if ($message['type'] == 0x63 || $message['type'] == 0x61) {
			$i = 0;
			foreach($extensions as $extension) {
				if ($line != '') $line .= ',';
				if (array_key_exists($i, $message['data']['states'])) {
					$line .= '[\'a'.$extension.'\', \''.$message['data']['states'][$i].'\']';
				} else {
					$line .= '[\'a'.$extension.'\', \'255\']';
				}
				++$i;
			}
		}
		if ($message['type'] == 0x73 || $message['type'] == 0x71) {
			$i = 0;
			foreach($queues as $queue) {
				if ($line != '') $line .= ',';
				if (array_key_exists($i, $message['data']['states'])) {
					$line .= '[\'c'.$queue.'\', \''.$message['data']['states'][$i].'\']';
				} else {
					$line .= '[\'c'.$queue.'\', \'255\']';
				}
				++$i;
			}
		}
	}

	return $line;
}


function getdata($servers, $queues)
{
	$line = '';

	$extbyhost = array();
	$queuesbyhost = array();

	foreach ($queues as $queue => $queue_data) {
		if (!array_key_exists('members', $queue_data)) continue;
		if (!array_key_exists($queue_data['host'], $extbyhost))
			$extbyhost[$queue_data['host']] = array();

		$queuesbyhost[$queue_data['host']][] =  $queue;
		foreach ($queue_data['members'] as $member) {
			if (!array_key_exists($member['ext'], $extbyhost[$queue_data['host']])) {
				$extbyhost[$queue_data['host']][$member['ext']] = False;
			}
		}
	}
	unset($queues);

	foreach($queuesbyhost as $host => $queues) {
		if ($line) $line .= ',';
		if (array_key_exists($host, $servers)) {
			$line .= get_data_from_server($host, $servers[$host]['port'], @array_keys($extbyhost[$host]), @$queuesbyhost[$host]);
		} else {
			$line .= get_data_from_server($servers[0]['host'], $servers[0]['port'], @array_keys($extbyhost[$host]), @$queuesbyhost[$host]);
		}
	}

	if (!$extbyhost) return return_error(3, 'no extension states');

	if (!$line) return return_error(101, 'NO DATA');

	return $line;
	
}

ini_set('session.referer_check', '');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 0);
ini_set('session.use_trans_sid', 0);
ini_set('session.hash_function', 1);
ini_set('session.hash_bits_per_character', 5);
ini_set('session.entropy_length', 0);

session_name('gemeinschaft');
session_start();

if (@$_REQUEST['t'] == 'm' || @$_REQUEST['t'] == 's') {
	if (@$_SESSION['queuemon']['servers']) {
		$servers = $_SESSION['queuemon']['servers'];
	} else {
		$servers = array();
		$server_lines = explode(',', GS_SBSERVER_HOSTS);
		foreach ($server_lines as $server_line) {
			$server = array();
			$server_array = explode(':', $server_line);
			if (count($server_array) == 2) {
				$server['host'] = trim($server_array[0]);
				$server['port'] = (int)$server_array[1];
			} else
			if (count($server_array) == 1) {
				$server['host'] = trim($server_array[0]);
				$server['port'] = GS_SBSERVER_PORT;
			}
			if ($server) $servers[]=$server;
		}
		if (!$servers) {
			echo return_error(1, 'no servers');
			exit;
		}
		$_SESSION['queuemon']['servers'] = $servers; 
	}
	
	if (@$_SESSION['queuemon']['queues']) {
		echo getdata($servers, $_SESSION['queuemon']['queues']);
	} else {
		echo return_error(2, 'no queues');
	}
}
?>
