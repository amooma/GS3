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
* Author: Sven Neukirchner <s.neukirchner@konabi.de>
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
require_once( GS_DIR .'inc/gs-fns/gs_sysrec_hash_get.php' );
set_error_handler('err_handler_quiet');
require_once( GS_DIR .'inc/db_connect.php' );

$keys = array(
	'0'	=> '0',
	'1'	=> '1',
	'2'	=> '2',
	'3'	=> '3',
	'4'	=> '4',
	'5'	=> '5',
	'6'	=> '6',
	'7'	=> '7',
	'8'	=> '8',
	'9'	=> '9',
	'#'	=> 'pound',
	'*'	=> 'star'
);





function gs_ivr_target( $typ,$value)
{
	$cmd = '';
	if ( $typ == 'announce' ) {
		$announce_file = gs_sysrec_hash_get( $value );

 		if ( !isGsError( $announce_file ) )
			$cmd = 'Background(/opt/gemeinschaft/sys-rec/'. $announce_file .');';
	}
	else if ( $typ == 'extension' ) {
		if ( $value != '0' && strlen( $value ) > 0 ) 
			$cmd = 'goto to-internal-users|'. $value .'|1;';
	}		
	else if ( $typ == 'repeat' )
		$cmd = 'jump loop;';
		
	else
		$cmd = 'jump h;';

	return $cmd;
}

echo "\n";
echo '// (auto-generated)' ,"\n";
echo "\n";

$db = gs_db_slave_connect();
if (! $db) die();
//FIXME - should probably write a message to gs_log() before dying



$rs = $db->execute(
	'SELECT  * FROM `ivrs` ORDER BY  `id'
);

if (! $rs) {
	echo '//ERROR' ,"\n";
	die();
	//FIXME - should probably write a message to gs_log() before dying
}


while ($r = $rs->fetchRow()) {

  $announce_file = gs_sysrec_hash_get($r['announcement']);
  
  if ( isGsError( $announce_file ) )
      continue;

   echo 'context IVR-', $r['name'] ,' {' ,"\n";
   echo "\t", 's => {', "\n";
   echo "\t\t", 'SET(LOOPCOUNT=0);' ,"\n";
   echo "\t\t", 'Answer();' ,"\n";
   echo "\t", 'begin:' ,"\n";
   echo "\t\t" ,'Background(/opt/gemeinschaft/sys-rec/', $announce_file ,');' ,"\n";
   echo "\t\t" ,'WaitExten(', $r['timeout'] ,');' ,"\n";
   echo  "\t", '};' ,"\n";


	foreach($keys as $key => $v) {

		if ($r['key_'. $v .'_type']){
			$action = gs_ivr_target($r['key_'. $v .'_type'],$r['key_'. $v .'_value']);
			if ( strlen ( $action ) <= 0 )
				continue;
			else {
	  			echo "\t", ''. $key .'=> {', "\n";
				echo "\t\t" ,$action ,"\n";
				echo  "\t", '};' ,"\n";
 			}
   		}
	}

	if ($r['t_action_type']){
		$action = gs_ivr_target($r['t_action_type'],$r['t_action_value']);
		if ( strlen ( $action ) <= 0 )
			$action = "jump h;";
		echo "\t", 't => {', "\n";
		echo "\t\t" ,$action ,"\n";
		echo  "\t", '};' ,"\n";
	}
 	if ($r['i_action_type']){
		$action = gs_ivr_target($r['i_action_type'],$r['i_action_value']);
		if ( strlen ( $action ) <= 0 )
			$action = "jump h;";
		echo "\t", 'i => {', "\n";
		echo "\t\t" ,$action ,"\n";
		echo  "\t", '};' ,"\n";
 	}

 	
  echo "\t", 'loop => {', "\n";
  echo "\t\t", 'LOOPCOUNT=${LOOPCOUNT}+1;' ,"\n";
  echo "\t\t", 'NoOP(retry number: ${LOOPCOUNT});' ,"\n";
  echo "\t\t", 'if(${LOOPCOUNT} > '. $r['retry'] .') jump h;' ,"\n";
  echo "\t\t", 'goto s|begin;' ,"\n";
  echo "\t", '};' ,"\n";

  echo "\t", 'h => {', "\n";
  echo "\t\t" , 'Hangup();' ,"\n";
  echo  "\t", '};' ,"\n";

  echo '};' ,"\n";
  echo "\n";
}




?>