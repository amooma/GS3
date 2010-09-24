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

defined('GS_VALID') or die('No direct access.');
require_once( dirName(__FILE__) .'/../../../../inc/conf.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/langhelper.php' );
require_once( GS_DIR .'inc/group-fns.php' );
require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
require_once( GS_DIR .'lib/yadb/yadb.php' );
require_once( GS_DIR .'htdocs/gui/inc/pamal/pamal.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_add.php' );

$db = gs_db_master_connect();
echo '<div style="width:550px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">';

$nuser = trim(@$_REQUEST['uuser']);
$nfname = trim(@$_REQUEST['ufname']);
$nlname = trim(@$_REQUEST['ulname']);
$nexten = (int)trim(@$_REQUEST['uexten']);
$npin = (int)trim(@$_REQUEST['upin']);
$action = trim(@$_REQUEST['action']);
if (! $db)
	return new GsError( 'Could not connect to database.' );
if (! in_array($action, array('list','useradd'), true))$action = 'list';

$group_id = gs_group_id_get('admins');
if ( $action === 'useradd' ) {
	$ulang = gs_get_lang_global(GS_LANG_OPT_AST, GS_LANG_FORMAT_AST);
	$ret = gs_user_add( $nuser, $nexten, $npin, $nfname, $nlname, $ulang, '1', '' );
	if (isGsError( $ret )) { 
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
		} else {
			$ret = gs_group_member_add( $group_id,  $nuser);
			if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
		}
		$action = 'list';
} 
if ( $action === 'list' ) {
echo '<h1>',__('Administratoren'),'</h1>';
echo __('Sie m&uuml;ssen mindestens einen Benutzer mit Adminrechten anlegen. Mit diesem Benutzer k&ouml;nnem Sie sich am normalen Web-Interface anmelden. Pflichtfelder sind: Benutzername, Durchwahl und PIN.');
echo '<p>';
echo '<form method="post" action="', GS_URL_PATH, 'setup/?step=user">';
echo '<input type="hidden" name="action" value="useradd" />';
echo '<table><thead><tr>';
echo '<th colspan="5">',__('Angelegte Aministratoren'),'</th></tr><tr>';
echo '<th>',__('Benutzer'),'</th>';
echo '<th>',__('Vorname'),'</th>';
echo '<th>',__('Nachname'),'</th>';
echo '<th>',__('Durchwahl'),'</th>';
echo '<th>',__('PIN'),'</th></thead><tbody>';

$admin_ids = gs_group_members_get(array(gs_group_id_get('admins')));
if (! empty($admin_ids)) {
$rs = $db->execute('SELECT `user`, `name`, `pin`, `firstname`, `lastname` FROM `users`, `ast_sipfriends_gs` WHERE `id` IN ('.implode(',', $admin_ids).') AND `id`=`_user_id`');


while ( $admin = $rs->fetchrow()) {
echo '<tr>';
echo '<td>',$admin['user'], '</td><td>', $admin['firstname'], '</td><td>', $admin['lastname'], '</td><td>', $admin['name'], '</td><td>', $admin['pin'], '</td>';
echo '</tr>';
}
}
echo '<tr><td><input type="text" name="uuser" value="" size="8" maxlength="20" /></td>';
echo '<td><input type="text" name="ufname" value="" size="8" maxlength="20" /></td>';
echo '<td><input type="text" name="ulname" value="" size="8" maxlength="20" /></td>';
echo '<td><input type="text" name="uexten" value="" size="8" maxlength="20" /></td>';
echo '<td><input type="text" name="upin" value="" size="8" maxlength="20" /></td></tr>';
echo '</tbody></table>';

echo '<div class="fr"><button type="submit" title="', __('Speichern'), '" class="plain">';
echo '<img alt="', __('Hinzuf&uuml;gen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button></div><br /><hr />' ,"\n";
echo '</form>';
}

echo '<div class="fl"><a href="', GS_URL_PATH ,'setup/?step=login">', __('zur&uuml;ck') ,'</a></div>' ,"\n";
echo '<div class="fr">';

$count_admins = count(gs_group_members_get(array($group_id)));

if ($count_admins < 1) {
	$can_continue = false;
} else {
	$can_continue = true;
}
if ($can_continue) {
	switch ($GS_INSTALLATION_TYPE) {
		# "system-check" unnecessary for the GPBX
		case 'gpbx': $next_step = 'network'     ; break;
		default    : $next_step = 'system-check'; break;
	}
	echo '<a href="', GS_URL_PATH ,'setup/?step=',$next_step ,'"><big>', __('weiter') ,'</big></a>';
} else {
	echo '<span style="color:#999;">', __('weiter') ,'</span>';
}
echo '</div>' ,"\n";
echo '<br class="nofloat" />' ,"\n";

echo '</div>';
?>
