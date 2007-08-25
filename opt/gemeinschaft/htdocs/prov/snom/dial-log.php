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

# caution: earlier versions of Snom firmware do not like
# indented XML

define( 'GS_VALID', true );  /// this is a parent file

header( 'Content-type: text/plain; charset=utf-8' );
header( 'Expires: 0' );


require_once( '../../../inc/conf.php' );
require_once( GS_DIR .'inc/db_connect.php' );

if (! gs_get_conf('GS_SNOM_ENABLED', true)) {
	gs_log( GS_LOG_DEBUG, "Snom provisioning not enabled" );
	die( 'Not enabled.' );
}


$user = trim( @ $_REQUEST['user'] );
if (! preg_match('/^\d+$/', $user))
	die( 'Not a valid SIP user.' );
$type = trim( @ $_REQUEST['type'] );
if (! in_array( $type, array('in','out','missed'), true ))
	$type = false;


$db = gs_db_slave_connect();

# get user_id
#
$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($user) .'\'' );
if ($user_id < 1)
	die( 'Unknown user.' );


$typeToTitle = array(
	'out'    => "Gew\xC3\xA4hlt",
	'missed' => "Verpasst",
	'in'     => "Angenommen",
);



if (! $type) {
	
	# delete outdated entries
	#
	$db->execute( 'DELETE FROM `dial_log` WHERE `user_id`='. $user_id .' AND `timestamp`<'. (time()-(int)GS_PROV_DIAL_LOG_LIFE) );
	
	
	
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n";
	echo '<SnomIPPhoneMenu>', "\n",
	       '<Title>Anruflisten</Title>', "\n";
	
	foreach ($typeToTitle as $t => $title) {
		
		$num_calls = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `dial_log` WHERE `user_id`='. $user_id .' AND `type`=\''. $t .'\'' );
		if ($num_calls > 0) {
			echo "\n",
			     '<MenuItem>', "\n",
			       '<Name>', htmlSpecialChars($title, ENT_QUOTES) ,'</Name>', "\n",
			       '<URL>', GS_PROV_SCHEME,'://', GS_PROV_HOST, (GS_PROV_PORT==80 ? '' : (':'. GS_PROV_PORT)), GS_PROV_PATH, 'snom/dial-log.php?user=',$user, '&type=',$t, '</URL>', "\n",
			     '</MenuItem>', "\n";
			# Snom does not understand &amp; !
		}
	}
	
	echo "\n",
	     '</SnomIPPhoneMenu>';
	die();
	
}




echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n";
echo '<SnomIPPhoneDirectory>', "\n",
       '<Title>', htmlSpecialChars($typeToTitle[$type], ENT_QUOTES) ,'</Title>', "\n";

$query =
'SELECT
	MAX(`timestamp`) `ts`, `number`, `remote_name`, `remote_user_id`,
	COUNT(*) `num_calls`
FROM `dial_log`
WHERE
	`user_id`='. $user_id .' AND
	`type`=\''. $type .'\'
GROUP BY `number`
ORDER BY `ts` DESC
LIMIT 20';
$rs = $db->execute( $query );
while ($r = $rs->fetchRow()) {
	
	$entry_name = $r['number'];
	if ($r['remote_name'] != '') {
		$entry_name .= ' '. $r['remote_name'];
	}
	if ($type=='missed') {
		$when = date('H:i', (int)$r['ts']);
		$entry_name = $when .'  '. $entry_name;
	}
	if ($r['num_calls'] > 1) {
		$entry_name .= ' ('. $r['num_calls'] .')';
	}
	echo "\n",
	     '<DirectoryEntry>', "\n",
	       '<Name>', htmlSpecialChars($entry_name, ENT_QUOTES) ,'</Name>', "\n",
	       '<Telephone>', htmlSpecialChars($r['number'], ENT_QUOTES) ,'</Telephone>', "\n",
	     '</DirectoryEntry>', "\n";
	
}

echo "\n",
     '</SnomIPPhoneDirectory>';


?>