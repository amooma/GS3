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

require_once( GS_DIR .'inc/string.php' );

/*
    setup_pwd set?
    no  => go to setup
    yes => depends on setup_show
    
    Valid values for setup_show:
               autoshow  password
    "autoshow"   [x]       [ ]    (sets setup_show to "password" after the setup)
    "password"   [ ]       [x]
    Other values are treated as "password" for security reasons.
*/

/*
function _gs_setup_mode()
{
	if (in_array(gs_get_conf('GS_INSTALLATION_TYPE'), array('embedded', 'single'), true)) {
		@include_once( GS_DIR .'inc/keyval.php' );
		$val = gs_keyval_get('setup_show');
		if (in_array($val, array('autoshow','password'), true))
			return $val;
	}
	return false;
}

function gs_setup_allowed( $_val=null )
{
	$val = ($_val===null ? _gs_setup_mode() : $_val);
	return (in_array($val, array('autoshow','password'), true));
}

function gs_setup_autoshow()
{
	$val = _gs_setup_mode();
	if (gs_setup_allowed($val)) {
		if ($val==='autoshow') return true;
	}
	return false;
}
*/

function gs_setup_possible()
{
	return (
		//in_array(gs_get_conf('GS_INSTALLATION_TYPE'), array('gpbx', 'single'), true)
		in_array(gs_get_conf('GS_INSTALLATION_TYPE'), array('gpbx'), true)
		&& file_exists('/etc/debian_version')
	);
}

function gs_setup_autoshow()
{
	if (gs_setup_possible()) {
		@include_once( GS_DIR .'inc/keyval.php' );
		$val = gs_keyval_get('setup_pwd');
		if ($val == '') return true;
		$val = gs_keyval_get('setup_show');
		if ($val === 'autoshow') return true;
	}
	return false;
}

function gs_setup_have_vlan_support()
{
	return @file_exists( '/proc/net/vlan' );
}


?>