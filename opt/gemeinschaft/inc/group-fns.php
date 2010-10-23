<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Rev$
* 
* Copyright 2009, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
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
include_once( GS_DIR .'inc/gs-lib.php' );

function gs_group_types_get()
{
	return array(
		'user',
		'queue',
		'host',
		'module_gui'
	);
}

function gs_group_permission_types_get()
{
	return array(
		'monitor_peers',
		'monitor_queues',
		'forward_queues',
		'sudo_user',
		'call_stats', 
		'phonebook_user',
		'display_module_gui',
		'group_pickup',
		'pickup',
		'roaming',
		'agent',
		'queue_member',
		'forward',
		'clip_set',
		'clir_set',
		'callwaiting_set',
		'ringtone_set',
		'dnd_set',
		'forward_vmconfig',
		'wakeup_call',
		'room_state',
		'intercom',
		'login_queues',
		'record_call',
		'private_call'
	);
}

function gs_group_parameter_types_get()
{
	return array(
		'asterisk',
		'gui',
	);
}

function gs_group_external_types_get()
{
	return array(
		'user',
		'queue',
		'host',
		'mysql'
	);
}

function gs_group_includes_get($group_ids, $climb_down = true, $flat = false, $processed = Array()) 
{
	if (!$group_ids)
		return array();	

	$db_slave = gs_db_slave_connect();
	if (! $db_slave)
		return new GsError( 'Could not connect to database.' );	
	
	$group_ids_sql = implode(',',$group_ids);

	if ($climb_down) 
		$rs = $db_slave->execute('SELECT `member` AS `output_group` FROM `group_includes` WHERE `group` IN ('.$group_ids_sql.')');
	else
		$rs = $db_slave->execute('SELECT `group` AS  `output_group` FROM `group_includes` WHERE `member` IN ('.$group_ids_sql.')');

	$groups_included = Array();

	if ($rs) 
		while ($r = $rs->fetchRow()) {
			if (array_search($r['output_group'], $processed) === FALSE) $groups_included[]=$r['output_group'];
		}

	$groups_included = array_unique($groups_included);
	$group_id_list   = array_merge($processed, $group_ids);
	$groups_included = array_diff($groups_included, $processed);

	if ($flat) return $groups_included;

	if (count($groups_included) > 0 ) {
		$groups_down = gs_group_includes_get($groups_included, $climb_down, false, $group_id_list);
		if (isGsError( $groups_down )) return $groups_down;
		if (count($groups_down) > 0 )
			$group_id_list = array_merge($group_id_list, $groups_included, $groups_down); 
		else
			$group_id_list = array_merge($group_id_list, $groups_included);

	}

	$group_id_list = array_unique($group_id_list);
	sort($group_id_list, SORT_NUMERIC);
	return $group_id_list;
	
}

function gs_group_members_external_get($group_ids) 
{
	$db_slave = gs_db_slave_connect();
	if (! $db_slave)
		return new GsError( 'Could not connect to database.' );	

	$rs = $db_slave->execute('
	SELECT `type`, `key`, `connection` 
	FROM `group_connections` 
	WHERE `group` 
	IN ('. implode(',',$group_ids) .') ');
	
	$members = Array();
	
	if ($rs) 
		while ($r = $rs->fetchRow()) {
			$type = preg_replace('/[^a-z0-9\-_]/', '', strToLower($r['type']));
			if (! preg_match( '/^[a-z0-9\-_]+$/', $type ))
				break;
			switch ($type) {
				case 'user':
					$rs_members = $db_slave->execute('
					SELECT `id` 
					FROM `users` 
					WHERE `nobody_index` IS NULL');

					if ($rs_members)
						while ($r_members = $rs_members->fetchRow()) {
							$members[] = $r_members['id'];
						}
					break;

				case 'queue':
					$rs_members = $db_slave->execute('
					SELECT `_id` AS `id` 
					FROM `ast_queues`');

					if ($rs_members)
						while ($r_members = $rs_members->fetchRow()) {
							$members[] = $r_members['id'];
						}
					break;

				case 'host':
					$rs_members = $db_slave->execute('
					SELECT `id` 
					FROM `hosts`');

					if ($rs_members)
						while ($r_members = $rs_members->fetchRow()) {
							$members[] = $r_members['id'];
						}
					break;

				case 'mysql':
					$rs_members = $db_slave->execute($r['connection']);
					if ($rs_members)
						while ($r_members = $rs_members->fetchRow()) {
							$members[] = $r_members['id'];
						}
					break;
	
				default:
					break;
			}
		}

	return $members;
}

function gs_group_members_get($group_ids, $includes = true)
{

	if (!$group_ids)
		return array();

	$db_slave = gs_db_slave_connect();
	if (! $db_slave)
		return new GsError( 'Could not connect to database.' );	
	
	if ($includes) {
		$group_includes = gs_group_includes_get($group_ids, true);
		if (isGsError( $group_includes )) return $group_includes;
	}
	else
		$group_includes = $group_ids;

	$rs = $db_slave->execute('SELECT `member` FROM `group_members` WHERE `group` IN ('. implode(',',$group_includes) .')');

	$members = gs_group_members_external_get($group_includes);	

	if ($rs)
		while ($r = $rs->fetchRow())
			$members[] = $r['member'];	
	
	return $members;

}

function gs_group_members_groups_get($member_ids, $type, $subgroups = true)
{
	if (!$member_ids)
		return array();

	$db_slave = gs_db_slave_connect();
	if (! $db_slave)
		return new GsError( 'Could not connect to database.' );	
	
	$rs = $db_slave->execute('SELECT `id` FROM `groups` JOIN `group_members` ON `group_members`.`group` = `groups`.`id` WHERE `groups`.`type` = \''.$db_slave->escape($type).'\' AND `group_members`.`member` IN ('.implode(',',$member_ids).')');

	
	$groups = gs_group_members_groups_external_get($member_ids, $type);

	if ($rs)
		while ($r = $rs->fetchRow()) {
			$groups[] = $r['id'];		
		}

	if (($groups) && ($subgroups)) $groups = gs_group_includes_get($groups, false);

	return $groups;
	
}

function gs_group_members_groups_external_get($member_ids, $type) 
{
	if (!$member_ids)
		return array();

	$db_slave = gs_db_slave_connect();
	if (! $db_slave)
		return new GsError( 'Could not connect to database.' );	

	$rs = $db_slave->execute('
	SELECT `group_connections`.`group` AS `group` 
	FROM `group_connections` JOIN `groups` ON `groups`.`id` = `group_connections`.`group`
	WHERE `groups`.`type` = \''.$db_slave->escape($type).'\'');
	
	$members = Array();
	
	if ($rs) 
		while ($r = $rs->fetchRow()) {
			foreach ($member_ids as $member_id) {	
				if (in_array($member_id, gs_group_members_external_get(Array($r['group'])))) {
					$members[] = $r['group'];
					break;
				}
			}
		}

	return $members;
}

function gs_group_permissions_get($group_ids, $type = false, $group_type = false)
{
	if (!$group_ids)
		return array();

	$db_slave = gs_db_slave_connect();
	if (! $db_slave)
		return new GsError( 'Could not connect to database.' );

	if ($type) 
		$type_sql = 	'`group_permissions`.`type` = \''.$db_slave->escape($type).'\' AND';
	else
		$type_sql = '';

	if ($group_type) 
		$rs = $db_slave->execute('SELECT `group_permissions`.`permit` FROM `group_permissions` JOIN `groups` ON `group_permissions`.`permit` =  `groups`.`id` WHERE '. $type_sql.' `group_permissions`.`group` IN ('. implode(',',$group_ids) .') AND `groups`.`type` = \''.$db_slave->escape($group_type).'\'');
	else 
		$rs = $db_slave->execute('SELECT `permit` FROM `group_permissions` WHERE '. $type_sql.' `group` IN ('. implode(',',$group_ids) .')');

	$members = array();
	
	if ($rs)
		while ($r = $rs->fetchRow())
			$members[] = $r['permit'];

	return $members;
}

function gs_group_info_get($group_ids = false, $type = false)
{
	$db_slave = gs_db_slave_connect();
	if (! $db_slave)
		return new GsError( 'Could not connect to database.' );
	
	if ($group_ids === false)
		$sql_query = 'SELECT * FROM `groups` WHERE `id` > 0';
	else if ($group_ids) 
		$sql_query = 'SELECT * FROM `groups` WHERE `id` IN ('. implode(',',$group_ids) .')';
	else
		return array();

	if ($type) $sql_query .= ' AND `type` = \''.$db_slave->escape($type).'\'';

	$sql_query .= ' ORDER BY `name` ASC';

	$rs = $db_slave->execute($sql_query);

	$members = array();
	
	if ($rs)
	while ($r = $rs->fetchRow())
		$members[] = $r;

	return $members;

}

function gs_group_id_get( $group )
{
	
	$group = preg_replace('/[^a-z0-9\-_]/', '', strToLower($group));
	if (! preg_match( '/^[a-z0-9\-_]+$/', $group ))
		return new GsError( 'Invalid group name.');

	$db_slave = gs_db_slave_connect();
	if (! $db_slave) {
		return new GsError( 'Could not connect to database.' );	
	}

	$group_id = $db_slave->executeGetOne( 'SELECT `id` FROM `groups` WHERE `name` = \''. $db_slave->escape($group).'\' LIMIT 1' );

	if (! $group_id) return new GsError( 'Group not found.' );

	return $group_id;
	
}

function gs_group_name_get( $id )
{
	
	$db_slave = gs_db_slave_connect();
	if (! $db_slave) {
		return new GsError( 'Could not connect to database.' );
	}
	
	$group_name = $db_slave->executeGetOne( 'SELECT `name` FROM `groups` WHERE `id` = '. $id .' LIMIT 1' );
	
	if (! $group_name) return new GsError( 'Group not found.' );
	
	return $group_name;
	
}

function gs_group_change($id, $name, $title)
{
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	$name = preg_replace('/[^a-z0-9\-_]/', '', strToLower($name));
	if (! preg_match( '/^[a-z0-9\-_]+$/', $name ))
		return new GsError( 'Invalid group name.');

	$ret = $db_master->execute('UPDATE `groups` SET `name` = \''. $db_master->escape($name) .'\', `title` = \''. $db_master->escape($title) .'\' WHERE id = '.$id);

	if (!$ret)
		return new GsError( 'Error on changing group name or title');
	
	return $ret;
}

function gs_group_add($name, $title, $type)
{
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	$name = preg_replace('/[^a-z0-9\-_]/', '', strToLower($name));
	if (! preg_match( '/^[a-z0-9\-_]+$/', $name ))
		return new GsError( 'Invalid group name.');

	$type = preg_replace('/[^a-z0-9\-_]/', '', strToLower($type));
	
	if (array_search($type, gs_group_types_get()) === false)
		return new GsError( 'Invalid group type.');

	if ($title == '') $title = $name;

	$id = (int)$db_master->executeGetOne( 'SELECT `id` FROM `groups` WHERE `name`=\''. $db_master->escape($name) .'\'' );
	
	if ($id > 0)
		return new GsError( 'Cannot add group. Group "'.$name.'" allready exists.' );

	$ret = $db_master->execute('INSERT INTO `groups` (`name`, `title`, `type`) VALUES (\''. $db_master->escape($name) .'\',\''. $db_master->escape($title) .'\',\''. $db_master->escape($type) .'\')');

	return $ret;
}

function gs_group_del($name)
{
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	$name = preg_replace('/[^a-z0-9\-_]/', '', strToLower($name));
	if (! preg_match( '/^[a-z0-9\-_]+$/', $name ))
		return new GsError( 'Invalid group name.');

	$id = (int)$db_master->executeGetOne( 'SELECT `id` FROM `groups` WHERE `name`=\''. $db_master->escape($name) .'\'' );
	if ($id < 1)
		return new GsError( 'Unknown group "'.$name.'".' );

	$ret = $db_master->execute('DELETE FROM `group_includes` WHERE `group` = '.$id.' OR `member` = '.$id);
	$ret = $db_master->execute('DELETE FROM `group_members` WHERE `group` = '.$id);
	$ret = $db_master->execute('DELETE FROM `group_permissions` WHERE `group` = '.$id.' OR `permit` = '.$id);
	$ret = $db_master->execute('DELETE FROM `group_connections` WHERE `group` = '.$id);
	$ret = $db_master->execute('DELETE FROM `groups` WHERE `id` = '.$id);	

	return $ret;
}

function gs_group_member_add($group_id, $member, $include = false)
{
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	if (!$group_id) return new GsError( 'Group not found.');

	$type = $db_master->executeGetOne( 'SELECT `type` FROM `groups` WHERE `id` = '. $group_id );
	
	if (!$type) return new GsError( 'Type not found.');
	$table = 'group_members';

	if (!$include) {
		switch ($type) {
			case 'user':
				$sql_query = 'SELECT `id` FROM `users` WHERE `user` = \''. $db_master->escape($member) .'\'';
				break;
			case 'queue':
				$sql_query = 'SELECT `_id` AS `id` FROM `ast_queues` WHERE `name` = \''. $db_master->escape($member) .'\'';
				break;
			case 'host':
				$sql_query = 'SELECT `id` FROM `hosts` WHERE `host` = \''. $db_master->escape($member) .'\'';
				break;
			default:
				$sql_query = false;
				break;
		}
	} else {
		$sql_query = 'SELECT `id` FROM `groups` WHERE `name` = \''. $db_master->escape($member) .'\' AND `type` = \''. $db_master->escape($type) .'\'';
		$table = 'group_includes';
	}

	if ($sql_query === false)
		$member_id = (int)$member;
	else 
		$member_id = (int)$db_master->executeGetOne( $sql_query );
	
	if (!$member_id)
		return new GsError( 'Cannot add member. Member "'.$member.'" of type "'.$type.'" not found.' );

	$ret = $db_master->execute('INSERT INTO `'.$db_master->escape($table).'` (`group`, `member`) VALUES ('.$group_id.', '.$member_id.')');

	return $ret;
}

function gs_group_member_del($group_id, $member, $include = false)
{
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	if (!$group_id) return new GsError( 'Group not found.');

	$type = $db_master->executeGetOne( 'SELECT `type` FROM `groups` WHERE `id` = '. $group_id );
	if (!$type) return new GsError( 'Type not found.');	

	$table = 'group_members';

	if (!$include) {
		switch ($type) {
			case 'user':
				$sql_query = 'SELECT `id` FROM `users` WHERE `user` = \''. $db_master->escape($member) .'\'';
				break;
			case 'queue':
				$sql_query = 'SELECT `_id` AS `id` FROM `ast_queues` WHERE `name` = \''. $db_master->escape($member) .'\'';
				break;
			case 'host':
				$sql_query = 'SELECT `id` FROM `hosts` WHERE `host` = \''. $db_master->escape($member) .'\'';
				break;
			case 'group':
				$sql_query = 'SELECT `id` FROM `groups` WHERE `name` = \''. $db_master->escape($member) .'\'';
				$table = 'group_includes';
				break;
			default:
				$sql_query = false;
				break;
		}

	} else {
		$sql_query = 'SELECT `id` FROM `groups` WHERE `name` = \''. $db_master->escape($member) .'\'';
		$table = 'group_includes';
	}
	if ($sql_query === false)
		$member_id = (int)$member;
	else 
		$member_id = (int)$db_master->executeGetOne( $sql_query );
	
	if (!$member_id)
		return new GsError( 'Cannot delete member. Member "'.$member.'" of type "'.$type.'" not found.' );

	
	$ret = $db_master->execute('DELETE FROM `'.$db_master->escape($table).'` WHERE `group` = '.$group_id.' AND `member` = '.$member_id);

	return $ret;
}

function gs_group_members_purge($group_ids, $member_ids)
{
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	$ret = $db_master->execute('DELETE FROM `group_members` WHERE `group` IN ('. implode(',',$group_ids) .') AND `member` = '. implode(',',$member_ids));
	
	return $ret;
}

function gs_group_members_purge_by_type($type, $member_ids)
{
	$db_slave = gs_db_slave_connect();
	
	if (! $db_slave)
		return new GsError( 'Could not connect to database.' );
	
	if (!in_array($type, gs_group_types_get())) return new GsError( 'Invalid group type.');
	
	$sql_query = 'SELECT `id` FROM `groups` WHERE `type` = \''. $db_slave->escape($type).'\'' ;
	
	$rs = $db_slave->execute( $sql_query );
	
	$group_ids = Array();
	if ($rs)
		while ($r = $rs->fetchRow()) {
			$group_ids[] = $r['id'];
		}
	
	if ($group_ids) $ret = gs_group_members_purge($group_ids, $member_ids);
	
	return $ret;
}


function gs_group_permission_add($group_id, $permission_id, $type)
{
	$type = preg_replace('/[^a-z0-9\-_]/', '', strToLower($type));
	if (!in_array($type, gs_group_permission_types_get()))
		return new GsError( 'Invalid permission type.');
	
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	$ret = $db_master->execute('INSERT INTO `group_permissions` (`group`, `permit`, `type`) VALUES ('.$group_id.', '.$permission_id.', \''.$db_master->escape($type).'\')');

	return $ret;
}

function gs_group_permission_del($group_id, $permission_id, $type)
{
	
	$type = preg_replace('/[^a-z0-9\-_]/', '', strToLower($type));
	if (!in_array($type, gs_group_permission_types_get()))
		return new GsError( 'Invalid permission type.');

	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	$ret = $db_master->execute('DELETE FROM `group_permissions` WHERE `group` = '.$group_id.' AND `permit` = '.$permission_id.' AND  `type` = \''.$db_master->escape($type).'\'');

	return $ret;
}

function gs_group_members_get_names($group, $includes = true)
{
	
	$members = gs_group_members_get(Array($group), $includes);

	if (isGsError( $members )) gs_script_error( $members->getMsg() );
	if (! $members)            return Array();

	$db_slave = gs_db_slave_connect();
	if (! $db_slave) {
		return new GsError( 'Could not connect to database.' );	
	}

	if (!$members) return Array(); 

	$type = $db_slave->executeGetOne( 'SELECT `type` FROM `groups` WHERE `id` = '. $group.' LIMIT 1' );

	switch ($type) {
		case 'user':
			$sql_query = 'SELECT `user` AS `member` FROM `users` WHERE `id` IN ('.implode(',',$members).')';
			break;
		case 'queue':
			$sql_query = 'SELECT `name` AS `member` FROM `ast_queues` WHERE `_id` IN ('.implode(',',$members).')';
			break;
		case 'host':
			$sql_query = 'SELECT `host` AS `member` FROM `hosts` WHERE `id` IN ('.implode(',',$members).')';
			break;
		default:
			$sql_query = false;
			break;
	}
	
	$members_a = Array();
	if ($sql_query === false) {
		foreach ($members as $member) {
			$r = array();
			$r['type'] = $type;
			$r['member'] = $member;
			$members_a[] = $r;
		}
	} else {
		$rs = $db_slave->execute( $sql_query );
		
		if ($rs)
			while ($r = $rs->fetchRow()) {
				$r['type'] = $type;
				$members_a[] = $r;
			}
	}
	return $members_a;
}

function gs_group_permissions_get_names($group)
{
	

	$db_slave = gs_db_slave_connect();
	if (! $db_slave) {
		return new GsError( 'Could not connect to database.' );	
	}

	$sql_query = 
	'SELECT `groups`.`name` AS `name`, 
		`groups`.`type` AS `type`, 
		`group_permissions`.`type` AS `permission`,
		`groups`.`id` AS `id`,
		`groups`.`title` AS `title`
	FROM `groups` 
	JOIN `group_permissions` 
	ON `groups`.`id` = `group_permissions`.`permit` 
	WHERE `group_permissions`.`group` = '.$group ;

	$rs = $db_slave->execute( $sql_query );

	$members = Array();
	if ($rs)
		while ($r = $rs->fetchRow()) {
			$members[] = $r;
		}

	return $members;
}

function gs_group_connection_add($group_id, $key, $connection, $type)
{
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	$ret = $db_master->execute('INSERT INTO `group_connections` (`group`, `key`, `connection`, `type`) VALUES ('.$group_id.', \''.$db_master->escape($key).'\', \''.$db_master->escape($connection).'\', \''.$db_master->escape($type).'\')');
	
	return $ret;
}

function gs_group_connection_del($group_id, $key, $connection, $type)
{
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	$ret = $db_master->execute('DELETE FROM `group_connections` WHERE `group` = '.$group_id.' AND `key` = \''.$db_master->escape($key).'\' AND `connection` = \''.$db_master->escape($connection).'\' AND `type` = \''.$db_master->escape($type).'\'');
	
	return $ret;
}

function gs_group_connections_get($group_id, $type = false)
{
	$db_slave = gs_db_slave_connect();
	if (! $db_slave)
		return new GsError( 'Could not connect to database.' );
	
	$sql_query = 'SELECT * FROM `group_connections` WHERE `group` = '. $group_id .' ';
	
	if ($type) $sql_query .= ' AND `type` = \''.$db_slave->escape($type).'\'';

	$rs = $db_slave->execute($sql_query);

	$members = array();
	
	if ($rs)
	while ($r = $rs->fetchRow())
		$members[] = $r;

	return $members;
}

function gs_group_parameters_get($group_id, $type = false)
{
	$db_slave = gs_db_slave_connect();
	if (! $db_slave)
		return new GsError( 'Could not connect to database.' );
	
	$sql_query = 'SELECT * FROM `group_parameters` WHERE `group` = '. $group_id;
	
	if ($type) $sql_query .= ' AND `type` = \''.$db_slave->escape($type).'\'';

	$rs = $db_slave->execute($sql_query);

	$parameters = array();
	
	if ($rs)
	while ($r = $rs->fetchRow())
		$parameters[] = $r;

	return $parameters;
}

function gs_group_parameter_add($group_id, $parameter, $value, $type)
{
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	$ret = $db_master->execute('INSERT INTO `group_parameters` (`group`, `parameter`, `value`, `type`) VALUES ('.$group_id.', \''.$db_master->escape($parameter).'\', \''.$db_master->escape($value).'\', \''.$db_master->escape($type).'\')');
	
	return $ret;
}

function gs_group_parameter_del($group_id, $parameter_id)
{
	$db_master = gs_db_master_connect();
	if (! $db_master)
		return new GsError( 'Could not connect to database.' );
	
	$ret = $db_master->execute('DELETE FROM `group_parameters` WHERE `group` = '.$group_id.' AND `id` = '.$parameter_id);
	
	return $ret;
}

?>
