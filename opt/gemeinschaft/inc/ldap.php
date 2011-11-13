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

include_once( GS_DIR .'lib/yadb/yadb.php' );
include_once( GS_DIR .'inc/log.php' );
include_once( GS_DIR .'inc/gs-lib.php' );

# simply including this file does not break anything, even if the
# LDAP extension for PHP is not available. (but calling the
# functions would be fatal.)


function gs_ldap_connect( $host=null, $port=null, $binddn=null, $pwd=null, $proto=null, $ssl=null )
{
	if (! function_exists('ldap_connect')) {
		gs_log( GS_LOG_FATAL, 'This PHP does not support LDAP (module missing)!' );
		return null;
	}
	
	if ($host   === null) $host   = GS_LDAP_HOST;
	if ($port   === null) $port   = GS_LDAP_PORT;
	if ($binddn === null) $binddn = GS_LDAP_BINDDN;
	if ($pwd    === null) $pwd    = GS_LDAP_PWD;
	if ($proto  === null) $proto  = GS_LDAP_PROTOCOL;
	if ($ssl    === null) $ssl    = GS_LDAP_SSL;
	
	$uri = ($ssl ? 'ldaps':'ldap') .'://'. $host
		. ($port < 1 ? '' : ':'.(int)$port) .'/';
	
	if (! $ssl) {
		if ($port < 1)
			$ldap = @ ldap_connect( $host );
		else
			$ldap = @ ldap_connect( $host, (int)$port );
	} else {
			$ldap = @ ldap_connect( $uri );
	}
	if (! is_resource($ldap)) {
		gs_log( GS_LOG_WARNING, 'Could not connect to LDAP server "'. $uri .'"!' );
		return null;
	}
	
	$ok = @ ldap_set_option( $ldap, LDAP_OPT_PROTOCOL_VERSION, (int)$proto );
	if (! $ok) {
		gs_log( GS_LOG_WARNING, 'Could not set LDAP protocol version to '. (int)$proto .'!' );
		return null;
	}
	
	$ok = @ ldap_bind( $ldap, $binddn, $pwd );
	if (! $ok) {
		gs_log( GS_LOG_WARNING, 'Could not connect to LDAP server "'. $uri .'" as "'. $binddn .'"! - '. gs_get_ldap_error($ldap) );
		return null;
	}
	
	@ ldap_set_option( $ldap, LDAP_OPT_TIMELIMIT, 5 );
	
	return $ldap;
}


function gs_ldap_disconnect( $ldap_conn )
{
	@ldap_close( $ldap_conn );
}


function gs_get_ldap_error( $ldap_conn )
{
	if (! function_exists('ldap_errno')) {
		gs_log( GS_LOG_FATAL, 'This PHP does not support LDAP (module missing)!' );
		return ' NO LDAP SUPPORT';
	}
	return '['. @ldap_errNo($ldap_conn) .'] '. @ldap_error($ldap_conn);
}



function ldap_prop_abbr( $prop )
{
	static $to_short = array(
		'userid'            => 'uid',
		'commonname'        => 'cn',
		'surname'           => 'sn',
		'givenname'         => 'gn',
		'country'           => 'c',
		'state'             => 'st',
		'organization'      => 'o',
		'organizationalunit'=> 'ou'
	);
	$prop = strToLower($prop);
	return (array_key_exists($prop, $to_short) ? $to_short[$prop] : $prop);
}


function ldap_prop_equal( $a, $b )
{
	$a = strToLower($a);
	$b = strToLower($b);
	if ($a === $b) return true;
	$a = ldap_prop_abbr($a);
	$b = ldap_prop_abbr($b);
	return ($a === $b);
}


function gs_ldap_get_list( $ldap_conn, $base, $filter='', $props=array(), $limit=0 )
{
	if (! is_resource($ldap_conn)) {
		return new GsError( 'Failed to query LDAP - Not connected!' );
	}
	if (strLen($filter) < 1) $filter = 'objectclass=*';
	if (! is_array($props)) $props = array();
	
	$rs = @ldap_search( $ldap_conn, $base, $filter, $props, 0, (int)$limit, 60, LDAP_DEREF_SEARCHING );
	if (! $rs)
		return new GsError( 'Failed to query LDAP, search base: "'. $base .'", filter: "'. $filter .'" - '. @gs_get_ldap_error($ldap) );
	
	/*
	if (count($sort) > 0) {
		foreach ($sort as $sort_prop) {
			if (count($props) < 1 || in_array($sort_prop, $props)) {
				echo ".";
				ldap_sort( $ldap_conn, $rs, array('telephonenumber') );
			}
		}
	}
	*/
	if (count($props) > 0) {
		$revProps = array_reverse($props);
		foreach ($revProps as $prop)
			ldap_sort( $ldap_conn, $rs, $prop );
	}
	
	$res = @ldap_get_entries( $ldap_conn, $rs );
	@ ldap_free_result( $rs );
	if (! is_array($res))
		return new GsError( 'Failed to get LDAP entries.' );
	
	/*
	$arr = array();
	$cnt = @$res['count'];
	for ($i=0; $i<$cnt; ++$i) {
		$res_entry = @$res[$i];
		if (! is_array($res_entry)) continue;
		$entry = array(
			'dn' => @$res_entry['dn'];
		);
		$ecnt = @$res_entry['count'];
		for ($j=0; $j<$ecnt; ++$j) {
			$propname = @$res_entry[$j];
			
			
		}
	}
	*/
	$ret = array();
	$numRows = @$res['count'];
	unset( $res['count'] );
	for ($i=0; $i<$numRows; ++$i) {
		$cnt = @$res[$i]['count'];
		unset( $res[$i]['count'] );
		for ($j=0; $j<$cnt; ++$j) {
			$propname = @$res[$i][$j];
			unset( $res[$i][$j] );
			unset( $res[$i][$propname]['count'] );
		}
		foreach ($res[$i] as $key => $arr) {
			unset( $res[$i][$key] );
			$res[$i][strToLower($key)] = $arr;
		}
		/*
		if (count($props) > 0) {
			foreach ($props as $wanted_prop) {
				$wanted_prop = strToLower($wanted_prop);
				if (! array_key_exists($wanted_prop, $res[$i]))
					$res[$i][$wanted_prop] = array();
			}
		}
		*/
		if (count($props) > 0) {
			$ret[$i] = array();
			foreach ($res[$i] as $returned_prop => $arr) {
				foreach ($props as $wanted_prop) {
					if (ldap_prop_equal($returned_prop, $wanted_prop)) {
						$ret[$i][$wanted_prop] = $arr;
					}
				}
			}
		} else {
			$ret[$i] = $res[$i];
		}
		unset($res[$i]);
	}
	
	return $ret;
}


function gs_ldap_get_first( $ldap_conn, $base, $filter='', $props=array() )
{
	$list = gs_ldap_get_list( $ldap_conn, $base, $filter, $props, 1 );
	if (isGsError($list))
		return new GsError( $list->getMsg() );
	if (! is_array($list))
		return new GsError( 'Failed to get LDAP entries.' );
	return reset($list);
}


/*
function array_tablesort( $table, $sort )
{
	$cols = array();
	foreach ($table as $i => $row) {
		foreach ($sort as $col => $order) {
			$cols[$col][$i] = $row[$col];
		}
	}
	print_r($cols);
	print_r($table);
	$args = array();
	foreach ($cols as $colname => $col) {
		$args[] = $col;
		$args[] = $sort[$colname]=='desc' ? SORT_DESC : SORT_ASC;
	}
	$table = call_user_func_array( 'array_multisort', $args );
	print_r($table);
	return $table;
}
*/


?>