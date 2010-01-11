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

require_once( GS_DIR .'htdocs/gui/inc/pamal/pamal.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
if (gs_get_conf('GS_GUI_PERMISSIONS_METHOD') === 'lvm') {
	require_once( GS_DIR .'inc/ldap.php' );
}

/*
Die API fuer den Rest von Gemeinschaft sind hier die Funktionen
gui_sudo_allowed() und gui_monitor_which_peers().
Sie koennen ggf. vom Admin angepasst werden, da es sich nicht
generisch definieren laesst, dass z.B. fuer die Editier-Rechte
die ersten 4 Stellen einer Kostenstellennr. aus dem LDAP ausschlag-
gebend sind.
gui_sudo_allowed() muss true oder false zurueckgeben.
gui_monitor_which_peers() muss false oder ein Array der ueberwachbaren
User zurueckgeben.

Um diese Lookups abzuschalten GUI_SUDO_EXTENDED auf false setzen.

Siehe auch session.php gs_legacy_user_map()
*/


//FIXME - this custom function should probably somehow be moved to
// /etc/gemeinschaft/custom-functions.php or
// /etc/gemeinschaft/custom-functions.examples.php
function _get_kostenstellen_lvm( $user )
{
	$kostenstelle_prop = 'lvmkostenstelle';
	
	$ldap = gs_ldap_connect();
	//if (gs_get_conf('GS_LVM_USER_6_DIGIT_INT')) {
	// this check is not really needed as this is a custom function anyway
		$user = preg_replace('/^0+/', '', $user);
		# without leading "0" in their LDAP
	//}
	$u = gs_ldap_get_first( $ldap, GS_LDAP_SEARCHBASE,
		'('. GS_LDAP_PROP_USER .'='. $user .')',
		array($kostenstelle_prop) );
	if (isGsError($u)) return false;
	if (! is_array($u)) {
		echo "Failed to get (". GS_LDAP_PROP_USER ."=". $user .") from LDAP server.\n";
		return false;
	}
	$kostenstelle_prop = strToLower($kostenstelle_prop);
	if (! is_array( $u[$kostenstelle_prop] )) return array();
	return $u[$kostenstelle_prop];
}


//FIXME - this custom function should probably somehow be moved to
// /etc/gemeinschaft/custom-functions.php or
// /etc/gemeinschaft/custom-functions.examples.php
function _gui_sudo_allowed_lvm( $real_user, $sudo_user )
{
	$kkr = @_get_kostenstellen_lvm( $real_user );
	if ($kkr === false || ! is_array($kkr)) return false;
	$kks = @_get_kostenstellen_lvm( $sudo_user );
	if ($kks === false || ! is_array($kks)) return false;
	
	foreach ($kkr as $kr)
		foreach ($kks as $ks)
			if (subStr($kr,0,2) === subStr($ks,0,2))
				return true;
	return false;
}

function _gui_sudo_allowed_group( $real_user, $sudo_user )
{
	$db_slave = gs_db_slave_connect();
	
	$real_user_id =  $db_slave->executeGetOne('SELECT `id` FROM `users` WHERE `user` = \''.$db_slave->escape($real_user).'\'');
	$sudo_user_id =  $db_slave->executeGetOne('SELECT `id` FROM `users` WHERE `user` = \''.$db_slave->escape($sudo_user).'\'');
	
	$group_members = gui_get_grouped_peers($real_user_id, 'sudo_user');

	return in_array($sudo_user_id, $group_members);
}

function gui_sudo_allowed( $real_user, $sudo_user )
{
	if (gs_get_conf('GS_GUI_PERMISSIONS_METHOD') === 'lvm')
		return _gui_sudo_allowed_lvm( $real_user, $sudo_user );
	else
		return _gui_sudo_allowed_group( $real_user, $sudo_user );
}


//FIXME - this custom function should probably somehow be moved to
// /etc/gemeinschaft/custom-functions.php or
// /etc/gemeinschaft/custom-functions.examples.php
function _gui_monitor_which_peers_lvm( $sudo_user )
{
	$kks = @_get_kostenstellen_lvm( $sudo_user );
	if ($kks === false || ! is_array($kks)) return false;
	
	$kostenstelle_prop = 'lvmkostenstelle';
	$limit = 100;
	
	$filter = '';
	foreach ($kks as $ks) {
		$filter .= '('. $kostenstelle_prop .'='. subStr($ks,0,2) .'*)';
	}
	$filter = '(|'. $filter .')';
	//echo $filter, "<br />\n";
	
	$ldap = gs_ldap_connect();
	$matches = gs_ldap_get_list( $ldap, GS_LDAP_SEARCHBASE,
		$filter, array(GS_LDAP_PROP_USER), (int)$limit );
	if (isGsError( $matches )) return false;
	if (! is_array( $matches )) return false;
	/*
	echo "<pre>";
	print_r($matches);
	echo "</pre>";
	*/
	
	$lc_GS_LDAP_PROP_USER = strToLower(GS_LDAP_PROP_USER);
	$peers = array();
	foreach ($matches as $match) {
		if (! is_array( $match[$lc_GS_LDAP_PROP_USER] )) continue;
		foreach ($match[$lc_GS_LDAP_PROP_USER] as $mm) {
			//if (gs_get_conf('GS_LVM_USER_6_DIGIT_INT')) {
			// this check is not really needed as this is a custom function anyway
				$mm = str_pad($mm, 6, '0', STR_PAD_LEFT);
				# without leading "0" in their LDAP database
			//}
			$peers[] = $mm;
		}
	}
	/*
	echo "<pre>";
	print_r($peers);
	echo "</pre>";
	*/
	
	return $peers;
}

function gui_get_grouped_peers( $user_id, $type )
{
	require_once( GS_DIR .'inc/group-fns.php' );

	$user_groups    = gs_group_members_groups_get(Array($user_id), 'user');
	$permission_groups = gs_group_permissions_get($user_groups, $type);
	$group_members  = gs_group_members_get($permission_groups);

	return $group_members;
}


function _gui_monitor_which_peers_group( $sudo_user )
{
	$db_slave = gs_db_slave_connect();
	$sudo_user_id =  $db_slave->executeGetOne( 'SELECT `id` FROM `users` WHERE `user` = \''.$db_slave->escape($sudo_user).'\'');
	
	$group_members = gui_get_grouped_peers($sudo_user_id, 'monitor_peers');
	
	$peers = array();
	if (is_array($group_members)) {
		$rs = $db_slave->execute('SELECT `user` FROM
			`users`	WHERE `id` IN ('.implode(',',$group_members).') ');
			$peers = array();
			if (! $rs)
				return $peers;
			while ($r = @$rs->fetchRow()) {
				$peers[] = $r['user']; 
			}
	}
	return $peers;
}


function gui_monitor_which_peers( $sudo_user )
{
	switch (gs_get_conf('GS_GUI_PERMISSIONS_METHOD')) {
	case 'lvm':
		return _gui_monitor_which_peers_lvm( $sudo_user );
		break;
	case 'gemeinschaft':
		return _gui_monitor_which_peers_group( $sudo_user );
		break;
	default:
		/* //FIXME?
		$db_slave = gs_db_slave_connect();
		$rs = $db_slave->execute('SELECT `u`.`user` FROM
		`user_watchlist` `f` LEFT JOIN
		`users` `u` ON (`u`.`id`=`f`.`user_id`)
		WHERE `f`.`status`= \'ack\' AND`f`.`buddy_user_id`= '.(int)@$_SESSION['sudo_user']['info']['id']);
		$peers = array();
		if (! $rs)
			return $peers;
		while ($r = @$rs->fetchRow()) {
			$peers[] = $r['user'];
		}
		return $peers;
		*/
		return array();
		break;
	}
}


?>
