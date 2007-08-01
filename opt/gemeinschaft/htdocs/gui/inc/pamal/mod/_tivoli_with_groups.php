<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1196 $
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
defined('PAMAL_DIR') or die('No direct access.');

include_once( PAMAL_DIR .'mod/tivoli.php' );


#####################################################################
#	like PAMAL_auth_tivoli but also reads groups from the
#	iv-groups HTTP header
#####################################################################
class PAMAL_auth_tivoli_with_groups extends PAMAL_auth_tivoli
{
	# constructor:
	function PAMAL_auth_tivoli_with_groups() {
		$parent = get_parent_class($this);
		parent::$parent();
		$this->_groups = $this->_getGroups();
	}
	
	# private, returns the user:
	function _getGroups() {
		$groupsStr = @$_SERVER['HTTP_IV_GROUPS'];
		//$groupsStr = @$_REQUEST['groups'];
		if ($groupsStr) {
			$groupsStr = str_replace(
				array('"', '\'', '\\'),
				array(''  , '' , ''  ),
				$groupsStr);
			$tmp = explode(',', $groupsStr);
			$groups = array();
			foreach ($tmp as $g)
				$groups[] = trim($g);
			return $groups;
		} else
			return array();
	}
}

?>