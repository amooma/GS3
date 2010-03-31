<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Sascha Daniels <sd@alternative-solution.de> 
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

defined('PAMAL_DIR') or die('No direct access.');


class PAMAL_auth_ldap extends PAMAL_auth
{
	function PAMAL_auth_ldap()
	{
		$this->_user = $this->_getUser();
	}
	function _getUser()
	{
		include_once( PAMAL_DIR .'mod/functions.php' ); 
		// Check if we have an LDAP USER
		$user_found=_searchUser_ldap();
		if ( $user_found )
		{
			$user=_getUser_ldap();
		}
		else {
			//USER not found in LDAP
			//guess user is in gemeinschaft
			$user=_getUser_gemeinschaft();
		}
		if ( $user ) 
		{
			return $user;
		}
		else {
			return false;
		}
	}
}
?>