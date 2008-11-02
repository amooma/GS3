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
* Soeren Sprenger <soeren.sprenger@amooma.de>
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
defined('GS_VALID') or die('No direct access.');


function _validate_ip_addr( $ipaddr )
{
	$ipaddr = trim($ipaddr);
	if (! preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/', $ipaddr, $m))
		return false;
	for ($i=1; $i<=4; ++$i) {
		$m[$i] = (int)lTrim($m[$i],'0');
		if ($m[$i] > 255) return false;
	}
	$ipaddr = $m[1].'.'.$m[2].'.'.$m[3].'.'.$m[4];
	if (in_array( @ip2long($ipaddr), array(false, null, -1, 0), true)) {
		return false;
	}
	return $ipaddr;
}

function RunTests($hosts) {
	$conf = '/etc/gemeinschaft/topology.php';
	if (! file_exists( $conf )) {
		trigger_error( "Config file \"$conf\" not found!\n", E_USER_ERROR );
		exit(1);
	} else {
		if ((@include( $conf )) === false) {
			// () around the include are important!
			trigger_error( "Could not include config file \"$conf\"!\n", E_USER_ERROR );
			exit(1);
		}
	}


	echo "Current RZ=" . $CUR_RZ."\n";

//look if we can acces each Machine via ssh
	echo "Stage 1: Trying to reach each System via SSH...\n";
	
	foreach ( $hosts as $host) {
		echo $host['disc']."... ";
		$ok = TrySsh($host['host']);
		if (isGsError( $ok )) {
			echo $ok->getMsg()."\n";
			exit(1);
		}
		echo "ok.\n";
	}
	
	echo "SSH seams to be working...\n\n";
	echo "============================\n\n";
	echo "Stage 2: Trying to reach each system via MySQL\n";
			
	foreach ($hosts as $key => $value) {
		echo $hosts[$key]['disc']."... ";
		if($key == 'DB_MASTER_SERVER1' && $CUR_RZ=='A')
			$hosts[$key]['con'] = db_master_connect($hosts[$key]['host'], $SUPER_MYSQL_USER, $SUPER_MYSQL_PASS, $hosts[$key]['con']);
		else if($key == 'DB_MASTER_SERVER2' && $CUR_RZ=='B')
			$hosts[$key]['con'] = db_master_connect($hosts[$key]['host'], $SUPER_MYSQL_USER, $SUPER_MYSQL_PASS, $hosts[$key]['con']);
		else
			$hosts[$key]['con'] = db_slave_connect($hosts[$key]['host'], $SUPER_MYSQL_USER, $SUPER_MYSQL_PASS, $hosts[$key]['con']);

		if(!$hosts[$key]['con']) {
			echo "Could not connect to ". $hosts[$key]['host'] ."\n";
			exit(1);
		}
		echo "ok.\n";
	}	

	
	echo "MySQL connections seams to be working...\n\n";
	echo "============================\n\n";
	echo "Stage 3: Checking REPLICATION-process on each system via MySQL\n";
	$warningcounter=0;
	foreach ( $hosts as $key => $host) {
		if($key == 'DB_MASTER_SERVER1' && $CUR_RZ=='A') {
			echo "(MASTER)".$host['disc']."... ";
			$ok = $host['con']->execute("SHOW MASTER STATUS");
			if (!$ok) {
				echo "Cant execute SHOW MASTER STATUS on " .$host['host'];
				exit(1);
			}
			$master_status = $ok->fetchRow();
			if(!isSet($master_status['Position']) || !isSet($master_status['File'])) {
				echo "Error, Master not running on " .$host['host']."\n";
				exit(1);
			}
			echo "ok.\n";
		}
		else if($key == 'DB_MASTER_SERVER2' && $CUR_RZ=='B') {
			echo "(MASTER) ".$host['disc']."... ";
			$ok = $host['con']->execute("SHOW MASTER STATUS");
			if (!$ok) {
				echo "Cant execute SHOW MASTER STATUS on " .$host['host']."\n";
				exit(1);
			}
			$master_status = $ok->fetchRow();
			if(!isSet($master_status['Position']) || !isSet($master_status['File'])) {
				echo "Error, Master not running on " .$host['host']."\n";
				exit(1);
			}
			echo "ok.\n";
		}
		else {
			$bOk = true;
			echo $host['disc']."... ";

			if($CUR_RZ=='A' && $host['host'] == $DB_MASTER_SERVER1_SERVICE_IP) {
				echo "Skipping, cause' it's the same host of \$DB_MASTER_SERVER1_SERVICE_IP. This host dont need to be a slave of hisself";
				continue;
				}
			if($CUR_RZ=='B' && $host['host'] == $DB_MASTER_SERVER2_SERVICE_IP) {
				echo "Skipping, cause' it's the same host of \$DB_MASTER_SERVER2_SERVICE_IP This host dont need to be a slave of hisself\n";
				continue;
				}


			$ok = $host['con']->execute("SHOW SLAVE STATUS");
			if (!$ok) {
				echo "Cant execute SHOW SLAVE STATUS on " .$host['host'];
				exit(1);
			}
			$slave_status = $ok->fetchRow();
			if($slave_status["Slave_IO_State"] == "") {
				echo "WARNING: Slave on ". $host['host'] ." is not running! ";
				$bOk = false;
				++$warningcounter;
			}
			
 			if($CUR_RZ=='A' && $slave_status["Master_Host"] != $hosts['DB_MASTER_SERVER1']['host']) {
				echo "WARNING: Slave on ". $host['host'] ." has the wrong Master!\n";
				echo "The Master on the Host is: ".$slave_status["Master_Host"]." and schould be ". $hosts['DB_MASTER_SERVER1']['host']."\n";
				echo "You may execute:\n";
				echo "gs-db-slave-replication-setup --master=".$hosts['DB_MASTER_SERVER1']['host']." --slave=".$host['host']." --user=".$SUPER_MYSQL_USER." --pass=".$SUPER_MYSQL_PASS."\n";
				$bOk = false;
				++$warningcounter;
			}
			if($CUR_RZ=='B' && $slave_status["Master_Host"] != $hosts['DB_MASTER_SERVER2']['host']) {
				echo "WARNING: Slave on ". $host['host'] ." has the wrong Master!\n";
				echo "The Master on the Host is: ".$slave_status["Master_Host"]." and schould be ". $hosts['DB_MASTER_SERVER2']['host']."\n";
				echo "You may execute:\n";
				echo "gs-db-slave-replication-setup --master=".$hosts['DB_MASTER_SERVER1']['host']." --slave=".$host['host']." --user=".$SUPER_MYSQL_USER." --pass=".$SUPER_MYSQL_PASS."\n";
				$bOk = false;
				++$warningcounter;
			}

			if($bOk)
				echo "ok.\n";
			else
				echo "a warning occoured\n";

		}
	} // foreach ( $hosts as $key => $host)
	
	if($warningcounter) {
		echo "Found ". $warningcounter ." warnings. Please try to Fix them!\n";
		exit(1);
	}
	echo "REPLICATION seams to be working...\n\n";
	echo "============================\n\n";
	echo "Stage 4: Checking gemeinschaft.php for variable \$DB_MASTER_HOST on each system via SSH\n";

	$master_host = null;
	if($CUR_RZ=='A')
		$master_host = $DB_MASTER_SERVER1_SERVICE_IP;
	else 
		$master_host = $DB_MASTER_SERVER2_SERVICE_IP;

	foreach ( $hosts as $host) {
		echo $host['disc']."... ";
		$ok = CheckGemeinschaft_php($host['host'], $master_host);
		if (isGsError( $ok )) {
			echo $ok->getMsg()."\n";
			exit(1);
		}
		echo "ok.\n";
	}

	echo "the variable \$DB_MASTER_HOST in the gemeinschaft.php on each Host seams to be ok.\n\n";

	echo "All systems should be up and running properly\n";
	
	//TODO: add Test to check if the Virtual Interfaces exists
	//TODO: add Test to check if Webservices are running
	//TODO: add Test to check if Voice-Services are running
	//TODO: add Test of the listen-to-ip File

	return 0;
}


function TrySsh($Server) {
	$cmd = "ssh -o StrictHostKeyChecking=no -o BatchMode=yes root@".$Server." 'echo \"Hello World\"'" ;

	@ exec( $sudo . $cmd , $out, $err );

	$ok = true;
	$ok = $ok && ($err==0);
	if (! $ok) {
		return new GsError( 'Could not SSH to '.$Server );
		}

	return 0;
}

function CheckGemeinschaft_php($Server, $Master_Host) {

	$cmd = "ssh -o StrictHostKeyChecking=no -o BatchMode=yes root@".$Server." 'grep DB_MASTER_HOST /etc/gemeinschaft/gemeinschaft.php'" ;

	@ exec( $sudo . $cmd , $out, $err );

	$ok = true;
	$ok = $ok && ($err==0);
	if (! $ok) {
		return new GsError( 'Could not SSH to '.$Server );
		}
	preg_match("/'[a-zA-Z0-9.]+'/", $out[0], $res);
	preg_match("/[a-zA-Z0-9.]+/", $res[0], $res1);
	if($res1[0] != $Master_Host)
		return new GsError( 'Error, Master host in gemeinschaft.php ('.$res1[0].') on host ' .$Server. ' differs with the Master host in the Topology ('.$Master_Host.')!');

	return 0;
}

function ChangeGemeinschaft_php($Server, $Master_Host) {

	$cmd = "ssh -o StrictHostKeyChecking=no -o BatchMode=yes root@".$Server." 'grep DB_MASTER_HOST /etc/gemeinschaft/gemeinschaft.php'" ;

	@ exec( $sudo . $cmd , $out, $err );

	$ok = true;
	$ok = $ok && ($err==0);
	if (! $ok) {
		return new GsError( 'Could not SSH to '.$Server );
		}
	$cmd = "ssh -o StrictHostKeyChecking=no -o BatchMode=yes root@".
$Server." 'sed \"s/".$out[0]."/\$DB_MASTER_HOST = '".$Master_Host."';/g\" /etc/gemeinschaft/gemeinschaft.php > /etc/gemeinschaft/gemeinschaft.php.tmp && mv /etc/gemeinschaft/gemeinschaft.php.tmp /etc/gemeinschaft/gemeinschaft.php'" ;

	@ exec( $sudo . $cmd , $out, $err );
	$ok = true;
	$ok = $ok && ($err==0);
	if (! $ok) {
		return new GsError( 'Could not execute SED via SSH on '.$Server );
		}
	return 0;
}




# local functions, almost identical to gs_db_master_connect()
# resp. gs_db_slave_connect() in inc/db_connect.php

function & db_master_connect( $host, $user, $pass, &$db_conn_master )
{
 	$caller_info = '';
	if (GS_LOG_LEVEL >= GS_LOG_DEBUG) {
		$bt = debug_backtrace();
		if (is_array($bt) && array_key_exists(0, $bt)) {
			$caller_info = ' (for '. @$bt[0]['file'] .':'. @$bt[0]['line'] .')';
			unset($bt);
		}
	}
	
	if (getType($db_conn_master) === 'object'
	&&  method_exists($db_conn_master, 'isConnected')
	&&  $db_conn_master->isConnected())
	{
		//gs_log( GS_LOG_DEBUG, 'Using the existing master DB connection'. $caller_info );
		return $db_conn_master;
	}
	gs_log( GS_LOG_DEBUG, 'Opening a new DB connection'. $caller_info );
	
	if (!( $db = YADB_newConnection( 'mysql' ) )) {
		$null = null;
		return $null;
	}
	if (!( $db->connect(
		$host,
		$user,
		$pass,
		GS_DB_MASTER_DB,
		array('reuse'=>false)  // do not use. leaves lots of connections
		)))
	{
		$lastNativeError    = @$db->getLastNativeError();
		$lastNativeErrorMsg = @$db->getLastNativeErrorMsg();
		gs_log( GS_LOG_WARNING, 'Could not connect to database!'. ($lastNativeError ? ' (#'.$lastNativeError.' - '.$lastNativeErrorMsg.')' : '') );
		$null = null;
		return $null;
	}
	@ $db->setCharSet( 'utf8', 'utf8_unicode_ci' );
	
	$db_conn_master = $db;
	return $db_conn_master;
}


function & db_slave_connect( $host, $user, $pass, &$db_conn_slave )
{
	$caller_info = '';
	if (GS_LOG_LEVEL >= GS_LOG_DEBUG) {
		$bt = debug_backtrace();
		if (is_array($bt) && array_key_exists(0, $bt)) {
			$caller_info = ' (for '. @$bt[0]['file'] .':'. @$bt[0]['line'] .')';
			unset($bt);
		}
	}
	
	if (getType($db_conn_slave) === 'object'
	&&  method_exists($db_conn_slave, 'isConnected')
	&&  $db_conn_slave->isConnected())
	{
		//gs_log( GS_LOG_DEBUG, 'Using the existing slave DB connection'. $caller_info );
		return $db_conn_slave;
	}
	gs_log( GS_LOG_DEBUG, 'Opening a new slave DB connection'. $caller_info );
	
	if (!( $db = YADB_newConnection( 'mysql' ) )) {
		$null = null;
		return $null;
	}
	if (!( $db->connect(
		$host,
		$user,
		$pass,
		GS_DB_MASTER_DB,
		array('reuse'=>false)  // do not use. leaves lots of connections
		)))
	{
		$lastNativeError    = @$db->getLastNativeError();
		$lastNativeErrorMsg = @$db->getLastNativeErrorMsg();
		gs_log( GS_LOG_WARNING, 'Could not connect to slave database!'. ($lastNativeError ? ' (#'.$lastNativeError.' - '.$lastNativeErrorMsg.')' : '') );
		$null = null;
		return $null;
	}
	@ $db->setCharSet( 'utf8', 'utf8_unicode_ci' );
	
	$db_conn_slave = $db;
	return $db_conn_slave;
}


function gs_db_master_migration( $old_master_host, $new_master_host, $user, $pass)
{
	$old_master_host = _validate_ip_addr($old_master_host);
	$new_master_host = _validate_ip_addr($new_master_host);
	if (! $old_master_host)
		return new GsError( 'Invalid IP address (old DB master).' );
	if (! $new_master_host)
		return new GsError( 'Invalid IP address (new DB master).' );
	if (subStr($old_master_host,0,4) == '127.')
		return new GsError( 'IP address on localhost not allowed for old DB master.' );
	if (subStr($new_master_host,0,4) == '127.')
		return new GsError( 'IP address on localhost not allowed for new DB master.' );
	if ($new_master_host == $old_master_host)
		return new GsError( 'New DB master == old DB master.' );
	
	# connect
	$old_master = db_master_connect( $old_master_host, $user, $pass, $old_master );
	if (! $old_master)
		return new GsError( 'Failed to connect to old master DB.' );
	$new_master = db_slave_connect ( $new_master_host, $user, $pass, $new_master );
	if (! $new_master)
		return new GsError( 'Failed to connect to new master DB.' );
	
	echo "Moving $old_master_host to $new_master_host\n";

	$ok = $old_master->execute( 'FLUSH TABLES WITH READ LOCK' );
	if (! $ok)  {
		return new GsError( 'Failed to lock tables.' );
	}
	
	sleep(2);
	
	$rs = $old_master->execute( 'SHOW MASTER STATUS' );
	if (! $rs) {
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( 'Error on old DB master.' );
	}
	$master_status = $rs->fetchRow();
	if (! array_key_exists('File'    , $master_status)
	||  ! array_key_exists('Position', $master_status)) {
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( 'Error on old DB master.' );
	}
	
	$rs = $new_master->execute( 'SHOW SLAVE STATUS' );
	if (! $rs) {
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( 'Error on new DB master.' );
	}
	$slave_status = $rs->fetchRow();
	if (! array_key_exists('Master_Log_File'    , $slave_status)
	||  ! array_key_exists('Read_Master_Log_Pos', $slave_status)) {
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( 'Error on new DB master.' );
	}
	
	$do_dump = false;
	
	if ($slave_status['Master_Log_File'] != $master_status['File']) {
		echo "Master bin-log file (". $master_status['File'] .") differs from slave (". $slave_status['Master_Log_File'] .")\n";
		$do_dump = true;
	}
	elseif ($slave_status['Read_Master_Log_Pos'] != $master_status['Position']) {
		echo "Master bin-log position (". $master_status['Position'] .") differs from slave (". $slave_status['Read_Master_Log_Pos'] .")\n";
		$do_dump = true;
	}
	
	if ($do_dump) {
		echo "Dumping database ...\n";
		$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( "Copying database dump currently not implemented. Please do it manually." );
	}
	else {
		echo "The new master's database is up to date. No need for a database dump.\n";
	}
	
	echo "Stopping Slave on new Master ...\n";
	$ok = $new_master->execute( 'STOP SLAVE' );
	if (! $ok) {
		@$new_master->execute( 'START SLAVE' );
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( "Failed to stop slave on new master" );
	}
	
	echo "Resetting Slave on new Master ...\n";
	$ok = $new_master->execute( 'RESET SLAVE' );
	if (! $ok) {
		@$new_master->execute( 'START SLAVE' );
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( "Failed to reset slave on new master" );
	}
	
	echo "Resetting Master on new Master ...\n";
	$ok = $new_master->execute( 'RESET MASTER' );
	if (! $ok) {
		@$new_master->execute( 'START SLAVE' );
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( "Failed to reset the new master!!!" );
	}
	
	echo "Adding permissions on new Master ...\n";
	$ok = $new_master->execute(
		'GRANT REPLICATION SLAVE '.
		'ON *.* '.
		'TO \''. $new_master->escape($user) .'\'@\'%\' '.
		'IDENTIFIED BY \''. $new_master->escape($pass) .'\''
		);
	if (! $ok) {
		@$new_master->execute( 'START SLAVE' );
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( "Failed to grant replication permissions on new master!" );
	}
	
	echo "Setting the old Master to run as a Slave ...\n";
	$ok = $old_master->execute( 'STOP SLAVE' );
	$ok = $old_master->execute( 'RESET SLAVE' );
	$ok = $old_master->execute( 'RESET MASTER' );
	sleep(1);
	
	$ok = $old_master->execute(
		'CHANGE MASTER TO '.
			'MASTER_HOST=\''    . $old_master->escape($new_master_host) .'\', '.
			'MASTER_USER=\''    . $old_master->escape($user) .'\', '.
			'MASTER_PASSWORD=\''. $old_master->escape($pass) .'\''
		);
	if (! $ok) {
		@$new_master->execute( 'START SLAVE' );
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( "Failed to change old Master to a Slave!");
	}
	echo "Starting Slave on old Master\n";
	$ok = $old_master->execute( 'START SLAVE' );
	if (! $ok) {
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( "Failed to start Slave on old Master!" );
	}
	echo "Unlock Tables on old Master\n";
	$ok = $old_master->execute( 'UNLOCK TABLES' );
	
	return true;
}

function gs_db_setup_replication( $master_host, $slave_host, $user, $pass)
{
	# are we root? do we have to sudo?
	#
	$uid = @ posix_geteuid();
	$uinfo = @ posix_getPwUid($uid);
	$uname = @ $uinfo['name'];
	$sudo = ($uname=='root') ? '' : 'sudo ';
	
	#get binlog position
	#
	$master = db_master_connect($master_host, $user, $pass, $master);
	$res = $master->execute("SHOW MASTER STATUS");
	$master_status = $res->fetchRow();
	
	
	#Stop Slave
	$slave  = db_slave_connect($slave_host , $user, $pass, $slave);
	$ok = $slave->execute('STOP SLAVE');
	if (! $ok) {
		return new GsError( "Failed to Stop Slave-Replication Process");
		}
	
	
	$dump_filename = "/tmp/db-resync-dump-" . rand() .".sql";
	
	#dump master database
	#
	$sshcommand = "'mysqldump --databases asterisk --opt --skip-extended-insert  --single-transaction --lock-tables'";
	
	$cmd = "ssh -o StrictHostKeyChecking=no -o BatchMode=yes root@".$master_host." " . $sshcommand." > ".$dump_filename;
	
	@ exec( $sudo . $cmd , $out, $err );
	
	$ok = true;
	$ok = $ok && ($err==0);
	if (! $ok) {
		return new GsError( "Failed to dump Master Database!");
		
		}
	
	#restore dump on Slave
	#
	$cmd = "cat ".$dump_filename." | ssh -o StrictHostKeyChecking=no -o BatchMode=yes root@".$slave_host." 'mysql asterisk' ";
	
	@ exec( $sudo . $cmd , $out, $err );
	
	#start slave-replication on Slave
	#
	
	$query = 'CHANGE MASTER TO '.
			'MASTER_HOST=\''    . $master->escape($master_host) .'\', '.
			'MASTER_USER=\''    . $master->escape($user) .'\', '.
			'MASTER_PASSWORD=\''. $master->escape($pass) .'\', '.
			'MASTER_LOG_FILE=\''. $master_status['File']  .'\', '.
			'MASTER_LOG_POS=' . $master_status['Position'];
	
	$ok = $slave->execute($query);
	if (! $ok) {
		return new GsError("Failed to Change Master on Slave!");
		}
	
	$ok = $slave->execute('START SLAVE');
	if (! $ok) {
		return new GsError("Failed to Start Slave-Replication Process");
		}

	return true;
}

?>