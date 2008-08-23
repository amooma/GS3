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

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

error_reporting(0);

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
set_error_handler('err_handler_quiet');
require_once( GS_DIR .'inc/db_connect.php' );


//if (!gs_get_conf('GS_BOI_ENABLED')


$use_views = true;

$db = gs_db_slave_connect();
if (! $db) {
	gs_log(GS_LOG_WARNING, 'Could not connect to database!');
} else {
	$mysql_vers = $db->serverVers();
	if ($mysql_vers['vint'] < 50001) {
		# does not have views
		$use_views = false;
		if (gs_get_conf('GS_BOI_ENABLED')) {
			gs_log(GS_LOG_FATAL, 'Branch office integration requires MySQL >= 5.0.1');
		}
		else {
			$c = $db->executeGetOne('SELECT COUNT(*) FROM `hosts` WHERE `is_foreign`=1');
			if ($c > 0) {
				# BOI is disabled but there are foreign hosts
				gs_log(GS_LOG_WARNING, 'You have foreign hosts. You should enable BOI_ENABLED.');
			}
		}
	} else {
		$rs = @$db->execute('SHOW CREATE VIEW `ast_sipfriends_gs`');
		if (! $rs) {
			$last_native_error     = $db->getLastNativeError();
			$last_native_error_msg = $db->getLastNativeErrorMsg();
			$last_error_code       = $db->getLastErrorCode();
			if ($last_error_code === '42S02' || $last_native_error === 1146) {
				if (gs_get_conf('GS_BOI_ENABLED')) {
					gs_log(GS_LOG_FATAL, 'Database view "ast_sipfriends_gs" missing!');
					$use_views = false;
				} else {
					gs_log(GS_LOG_WARNING, 'Database view "ast_sipfriends_gs" missing!');
					$use_views = false;
				}
			} else {
				gs_log(GS_LOG_WARNING, 'Database error: '. $last_native_error_msg);
				gs_log(GS_LOG_WARNING, 'Database view "ast_sipfriends_gs" missing!');
			}
		}
	}
}

$db_name = gs_get_conf('GS_DB_SLAVE_DB');

echo "\n";
echo 'sipusers      => mysql,'. $db_name .',ast_sipfriends'.($use_views ? '_gs':'') ."\n";
echo 'sippeers      => mysql,'. $db_name .',ast_sipfriends'.($use_views ? '_gs':'') ."\n";
echo 'voicemail     => mysql,'. $db_name .',ast_voicemail' ."\n";
echo 'queues        => mysql,'. $db_name .',ast_queues' ."\n";
echo 'queue_members => mysql,'. $db_name .',ast_queue_members' ."\n";
echo "\n";

gs_log(GS_LOG_DEBUG, 'extconfig.conf.php has just been loaded.');


?>