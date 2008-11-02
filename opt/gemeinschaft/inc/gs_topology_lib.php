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
include_once( GS_DIR .'inc/log.php' );
include_once( GS_DIR .'inc/quote_shell_arg.php' );
include_once( GS_DIR .'inc/gs-lib.php' );


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


function _try_ssh( $server )
{
	$cmd = 'echo '. qsa('Hello World');
	$cmd = 'ssh -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa('root@'.$server) .' '. qsa($cmd);
	
	$err=0; $out=array();
	@exec( $sudo . $cmd, $out, $err );
	if ($err != 0)
		return new GsError( 'Could not SSH to '. $server );
	
	return true;
}


function _check_etc_gemeinschaft_php( $server, $master_host )
{
	$cmd = 'grep '. qsa('DB_MASTER_HOST') .' '. qsa('/etc/gemeinschaft/gemeinschaft.php');
	$cmd = 'ssh -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa('root@'.$server) .' '. qsa($cmd);
	
	$err=0; $out=array();
	@exec( $sudo . $cmd, $out, $err );
	if ($err != 0)
		return new GsError( 'Could not SSH to '. $server );
	
	@preg_match("/'[a-zA-Z0-9.]+'/", $out[0], $m );  //FIXME
	@preg_match("/[a-zA-Z0-9.]+/"  ,  @$m[0], $m1);  //FIXME
	if (@$m1[0] != $master_host) {
		return new GsError( 'Master host in gemeinschaft.php ('.$m1[0].') on host '. $server .' differs from the Master host in the Topology ('.$master_Host.')!' );
	}
	$master_host = _validate_ip_addr(@$m1[0]);
	if (! $master_host)
		return new GsError( 'Invalid IP address "'.$master_host.'"' );
	
	return true;
}


function _change_etc_gemeinschaft_php( $server, $master_host )
{
	if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE'))
		return new GsError( 'Not allowed on single server systems.' );
	
	$cmd = 'grep '. qsa('DB_MASTER_HOST') .' '. qsa('/etc/gemeinschaft/gemeinschaft.php');
	$cmd = 'ssh -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa('root@'.$server) .' '. qsa($cmd);
	
	$err=0; $out=array();
	@exec( $sudo . $cmd, $out, $err );
	if ($err != 0)
		return new GsError( 'Could not SSH to '. $server );
	
	$master_host_old = _validate_ip_addr(@$out[0]);
	if (! $master_host_old)
		return new GsError( 'Invalid IP address "'.$master_host_old.'"' );
	
	$master_host     = _validate_ip_addr($master_host);
	if (! $master_host)
		return new GsError( 'Invalid IP address "'.$master_host.'"' );
	
	$file = '/etc/gemeinschaft/gemeinschaft.php';
	$sed_cmd = 's/'.$master_host_old.'/\$DB_MASTER_HOST = \''.$master_host.'\';/g';  //FIXME
	$cmd = 'sed '. qsa($sed_cmd)
		. ' '. qsa($file)
		.' > '. qsa($file.'.tmp')
		.' && mv '. qsa($file.'.tmp')
		.' '. qsa($file);
	$cmd = 'ssh -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa('root@'.$server) .' '. qsa($cmd);
	
	$err=0; $out=array();
	@exec( $sudo . $cmd, $out, $err );
	if ($err != 0)
		return new GsError( 'Could not execute SED via SSH on '. $server );
	
	return true;
}


function _run_topology_tests( $hosts )
{
	if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
		trigger_error( "Not allowed on single server systems.\n", E_USER_ERROR );
		exit(1);
	}
	
	$conf = '/etc/gemeinschaft/topology.php';
	if (! file_exists($conf)) {
		trigger_error( "Config file \"$conf\" not found!\n", E_USER_ERROR );
		exit(1);
	} else {
		if ((@include($conf)) === false) {
			// () around the include are important!
			trigger_error( "Could not include config file \"$conf\"!\n", E_USER_ERROR );
			exit(1);
		}
	}
	
	echo "Current EDPC (de: RZ): ", $CUR_RZ ,"\n";
	
	echo "Stage 1: Trying to reach each system via SSH ...\n";
	
	foreach ($hosts as $host) {
		echo $host['desc'] ,"... ";
		$ok = _try_ssh($host['host']);
		if (isGsError($ok)) {
			echo $ok->getMsg() ,"\n";
			exit(1);
		} elseif (! $ok) {
			echo 'Error' ,"\n";
			exit(1);
		}
		echo "ok.\n";
	}
	echo "SSH seems to be working.\n\n";
	
	echo "============================\n\n";
	echo "Stage 2: Trying to reach each system via MySQL ...\n";
	
	foreach ($hosts as $key => $info) {
		echo $arr['desc'] ,"... ";
		
		if       ($key === 'DB_MASTER_SERVER1' && $CUR_RZ === 'A') {
			$hosts[$key]['con'] = _db_master_connect( $hosts[$key]['host'],
				$SUPER_MYSQL_USER, $SUPER_MYSQL_PASS, $hosts[$key]['con'] );
		} elseif ($key === 'DB_MASTER_SERVER2' && $CUR_RZ === 'B') {
			$hosts[$key]['con'] = _db_master_connect( $hosts[$key]['host'],
				$SUPER_MYSQL_USER, $SUPER_MYSQL_PASS, $hosts[$key]['con'] );
		} else {
			$hosts[$key]['con'] = _db_slave_connect ( $hosts[$key]['host'],
				$SUPER_MYSQL_USER, $SUPER_MYSQL_PASS, $hosts[$key]['con'] );
		}
		
		if (! $hosts[$key]['con']) {
			echo "Could not connect to ". $hosts[$key]['host'] ."\n";
			exit(1);
		}
		echo "ok.\n";
	}
	echo "MySQL connections seems to be working.\n\n";
	
	echo "============================\n\n";
	echo "Stage 3: Checking replication process on each system via MySQL ...\n";
	$warningcounter = 0;
	foreach ($hosts as $key => $host) {
		if ($key === 'DB_MASTER_SERVER1' && $CUR_RZ === 'A') {
			echo "(MASTER) ", $host['desc'] ,"... ";
			$rs = $host['con']->execute( 'SHOW MASTER STATUS' );
			if (! $rs) {
				echo "Could not execute SHOW MASTER STATUS on ", $host['host'] ,"\n";
				exit(1);
			}
			$master_status = $rs->fetchRow();
			if (! isSet($master_status['Position'])
			||  ! isSet($master_status['File'])) {
				echo "Error. Master not running on ", $host['host'] ,"\n";
				exit(1);
			}
			echo "ok.\n";
		}
		elseif ($key == 'DB_MASTER_SERVER2' && $CUR_RZ === 'B') {
			echo "(MASTER) ", $host['desc'] ,"... ";
			$rs = $host['con']->execute( 'SHOW MASTER STATUS' );
			if (! $rs) {
				echo "Could not execute SHOW MASTER STATUS on ", $host['host'] ,"\n";
				exit(1);
			}
			$master_status = $rs->fetchRow();
			if (! isSet($master_status['Position'])
			||  ! isSet($master_status['File'])) {
				echo "Error. Master not running on ", $host['host'] ,"\n";
				exit(1);
			}
			echo "ok.\n";
		}
		else {
			$bOk = true;
			echo $host['desc'] ,"... ";
			
			if ($CUR_RZ === 'A' && $host['host'] === $DB_MASTER_SERVER1_SERVICE_IP) {
				echo "Skipping, because it's the same host as DB_MASTER_SERVER1_SERVICE_IP.\n";
				echo "This host does not need to be a slave to himself.";
				continue;
			}
			if ($CUR_RZ === 'B' && $host['host'] === $DB_MASTER_SERVER2_SERVICE_IP) {
				echo "Skipping, because it's the same host as DB_MASTER_SERVER2_SERVICE_IP.\n";
				echo "This host does not need to be a slave to himself.\n";
				continue;
			}
			
			$rs = $host['con']->execute( 'SHOW SLAVE STATUS' );
			if (! $rs) {
				echo "Could not execute SHOW SLAVE STATUS on ", $host['host'] ,"\n";
				exit(1);
			}
			$slave_status = $rs->fetchRow();
			if (@$slave_status['Slave_IO_State'] == '') {
				echo "WARNING: Slave on ", $host['host'] ," is not running!\n";
				$bOk = false;
				++$warningcounter;
			}
			
 			if ($CUR_RZ === 'A' && @$slave_status['Master_Host'] != $hosts['DB_MASTER_SERVER1']['host']) {
				echo "WARNING: Slave on ", $host['host'] ," has the wrong Master!\n";
				echo "The Master on that host is: ", $slave_status["Master_Host"] ,"\n";
				echo "but should be ", $hosts['DB_MASTER_SERVER1']['host'] ,"\n";
				echo "You may want to execute:\n";
				echo "gs-db-slave-replication-setup"
					," --master=", qsa($hosts['DB_MASTER_SERVER1']['host'])
					," --slave=", qsa($host['host'])
					," --user=",qsa($SUPER_MYSQL_USER)
					," --pass=", qsa($SUPER_MYSQL_PASS)
					,"\n";
				$bOk = false;
				++$warningcounter;
			}
			if ($CUR_RZ === 'B' && @$slave_status['Master_Host'] != $hosts['DB_MASTER_SERVER2']['host']) {
				echo "WARNING: Slave on ", $host['host'] ," has the wrong Master!\n";
				echo "The Master on that host is: ", $slave_status["Master_Host"] ,"\n";
				echo "but should be ", $hosts['DB_MASTER_SERVER2']['host'] ,"\n";
				echo "You may want to execute:\n";
				echo "gs-db-slave-replication-setup"
					," --master=", qsa($hosts['DB_MASTER_SERVER2']['host'])
					," --slave=", qsa($host['host'])
					," --user=",qsa($SUPER_MYSQL_USER)
					," --pass=", qsa($SUPER_MYSQL_PASS)
					,"\n";
				$bOk = false;
				++$warningcounter;
			}
			
			if ($bOk)
				echo "ok.\n";
			else
				echo "a warning occurred.\n";
		}
	} // foreach ($hosts as $key => $host)
	
	if ($warningcounter > 0) {
		echo "Found ", $warningcounter ," warnings. Please try to fix them!\n";
		exit(1);
	}
	echo "Replication seems to be working.\n\n";
	
	echo "============================\n\n";
	echo "Stage 4: Checking gemeinschaft.php for variable DB_MASTER_HOST on each system ...\n";
	
	$master_host = null;
	if       ($CUR_RZ === 'A') {
		$master_host = $DB_MASTER_SERVER1_SERVICE_IP;
	} elseif ($CUR_RZ === 'B') {
		$master_host = $DB_MASTER_SERVER2_SERVICE_IP;
	} else {
		echo "Error.\n";
		exit(1);
	}
	
	foreach ($hosts as $host) {
		echo $host['desc'] ,"... ";
		$ok = _check_etc_gemeinschaft_php( $host['host'], $master_host );
		if (isGsError($ok)) {
			echo $ok->getMsg() ,"\n";
			exit(1);
		} elseif (! $ok) {
			echo 'Error' ,"\n";
			exit(1);
		}
		echo "ok.\n";
	}
	echo "The DB_MASTER_HOST setting in gemeinschaft.php on each host seems to be ok.\n\n";
	
	echo "All systems should be up and running properly.\n";
	
	//TODO: add test to check if the Virtual Interfaces exists
	//TODO: add test to check if Web services are running
	//TODO: add test to check if Voice services are running
	//TODO: add test for the listen-to-ip file
	
	return true;
}












# local functions, almost identical to gs_db_master_connect()
# resp. gs_db_slave_connect() in inc/db_connect.php
# The difference is that these local functions do not use
# global $gs_db_conn_master, $gs_db_conn_slave;

function & _db_master_connect( $host, $user, $pass, &$db_conn_master )
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


function & _db_slave_connect( $host, $user, $pass, &$db_conn_slave )
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
		GS_DB_SLAVE_DB,
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
	if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE'))
		return new GsError( 'Not allowed on single server systems.' );
	
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
	if (strLen($pass) < 4)
		return new GsError( 'Password too short.' );
	
	# connect
	$old_master = null;
	$old_master = _db_master_connect( $old_master_host, $user, $pass, $old_master );
	if (! $old_master)
		return new GsError( 'Failed to connect to old master DB.' );
	$new_master = null;
	$new_master = _db_slave_connect ( $new_master_host, $user, $pass, $new_master );
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
	
	echo "Adding permissions on new Master ...\n";
	$ok = $new_master->execute(
		'GRANT REPLICATION SLAVE '.
		'ON *.* '.
		'TO \''. $new_master->escape($user) .'\'@\'%\' '.
		'IDENTIFIED BY \''. $new_master->escape($pass) .'\''
		);
	$new_master->execute( 'FLUSH PRIVILEGES' );
	if (! $ok) {
		@$old_master->execute( 'UNLOCK TABLES' );
		return new GsError( "Failed to grant replication permissions on new master!" );
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


function gs_db_setup_replication( $master_host, $slave_host, $user, $pass )
{
	if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE'))
		return new GsError( 'Not allowed on single server systems.' );
	
	# are we root? do we have to sudo?
	#
	$uid = @posix_geteuid();
	$uinfo = @posix_getPwUid($uid);
	$uname = @$uinfo['name'];
	$sudo = ($uname==='root') ? '' : 'sudo ';
	
	# get binlog position
	#
	/*
	$master = null;
	$master = _db_master_connect( $master_host, $user, $pass, $master );
	if (! $master)
		return new GsError( 'Failed to connect to master database.' );
	$rs = $master->execute( 'SHOW MASTER STATUS' );
	if (! $rs)
		return new GsError( 'DB error.' );
	$master_status = $rs->fetchRow();
	*/
	
	# Stop Slave
	$slave  = null;
	$slave  = _db_slave_connect( $slave_host  , $user, $pass, $slave );
	if (! $slave)
		return new GsError( 'Failed to connect to slave database.' );
	$ok = $slave->execute( 'STOP SLAVE' );
	if (! $ok)
		return new GsError( 'Failed to stop database slave replication' );
	
	$dump_filename = '/tmp/gs-db-resync-dump-'. rand() .'.sql';
	
	# dump Master database
	#
	$cmd = 'mysqldump --databases asterisk --opt --skip-extended-insert  --single-transaction --lock-tables';
	$cmd = 'ssh -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa('root@'.$master_host) .' '
		. qsa($cmd) .' > '. qsa($dump_filename) .' 2>>/dev/null';
	
	$err=0; $out=array();
	@exec( $sudo . $cmd, $out, $err );
	if ($err != 0)
		return new GsError( 'Failed to save dump of master database!' );
	
	# restore dump on Slave
	#
	$cmd = 'cat '. qsa($dump_filename)
		.' | ssh -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa('root@'.$slave_host)
		.' '. qsa( 'mysql asterisk' );
	//FIXME - instead of cat, copy the dump to the slave first
	
	$err=0; $out=array();
	@exec( $sudo . $cmd, $out, $err );
	if ($err != 0)
		return new GsError( 'Failed to restore database dump on slave!' );
	
	# start replication on Slave
	#
	$query =
		'CHANGE MASTER TO '.
			'MASTER_HOST=\''    . $master->escape($master_host) .'\', '.
			'MASTER_USER=\''    . $master->escape($user) .'\', '.
			'MASTER_PASSWORD=\''. $master->escape($pass) .'\', '.
			'MASTER_LOG_FILE=\''. $master->escape($master_status['File']) .'\', '.
			'MASTER_LOG_POS='   . (int)$master_status['Position']
		;
	$ok = $slave->execute($query);
	if (! $ok)
		return new GsError( 'Failed to Change Master on Slave!' );
	
	$ok = $slave->execute( 'START SLAVE' );
	if (! $ok)
		return new GsError( 'Failed to Start Slave Replication Process' );
	
	return true;
}

?>