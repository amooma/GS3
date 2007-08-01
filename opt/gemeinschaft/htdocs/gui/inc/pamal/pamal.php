<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1197 $
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

define('PAMAL_DIR', dirName(__FILE__).'/');


#####################################################################
#	the abstract authentication class,
#	use new_PAMAL_auth() to instantiate
#####################################################################
class PAMAL_auth
{
	var $_user = false;
	var $_groups = array();
	
	# constructor:
	function PAMAL_auth() { die(get_class($this) .' is an abstract class!'); }
	
	# returns the identifier of the authenticated user:
	function getUser()   { return $this->_user; }
	
	# returns the groups of the user as an array:
	function getGroups() { return $this->_groups; }
}


#####################################################################
#	instantiates PAMAL_auth
#	$method: the authentication plugin, eg. 'tivoli'
#####################################################################
function new_PAMAL_auth( $method )
{
	$file = PAMAL_DIR .'mod/'. $method .'.php';
	$class = 'PAMAL_auth_'. $method;
	
	if (file_exists( $file )) include_once $file;
	if (class_exists( $class )) return new $class;
	else {
		/*
		$file = PAMAL_DIR .'mod/dummy.php';
		if (file_exists( $file )) include_once $file;
		if (class_exists( $class )) return new PAMAL_auth_dummy;
		else die("PAMAL error: Cannot find module $method and even the dummy is not available!");
		*/
		die( "PAMAL error: Cannot find module \"$method\"!" );
	}
}

#####################################################################
#	PAMAL - Pluggable Authentication Module Abstraction Layer
#	PAMAL is a PAM in PHP and can even be used to abstract from
#	the username/groups returned by a module
#	
#	$method: the authentication plugin, eg. 'tivoli'
#	$fnMap : a function to map the user/groups supplied by the module
#	to internal ones. must return an array like this:
#	u => 'username',
#	g => ('grp1', 'grp2')
#	
#	Usage:
#	$pam = new PAMAL( 'tivoli_with_groups', 'myMap' );
#	echo 'User: '. $pam->getUser() ."<br />\n";
#	echo 'Groups: '. implode(', ', $pam->getGroups() ) ."<br />\n";
#	echo 'in group "admin": '. (int)$pam->isGroup('admin') ."<br />\n";
#####################################################################
class PAMAL
{
	var $_authMethod = '';
	var $_authObj    = null;
	var $_user       = false;
	var $_groups     = false;
	
	function PAMAL( $method, $fnMap=false )
	{
		$this->_authObj = new_PAMAL_auth( $method );
		
		$this->_user   = $this->_authObj->getUser();
		$this->_groups = $this->_authObj->getGroups();
		$this->_authMethod = $method;
		if ($fnMap && function_exists( $fnMap )) {
			$map = $fnMap( $this->_user, $this->_groups );
			if (is_array($map)) {
				if (@$map['u']) $this->_user = (string)$map['u'];
				else            $this->_user = false;
				$this->_groups = array();
				if (is_array( @$map['g'] )) {
					foreach( $map['g'] as $g ) $this->_groups[] = $g;
				}
			}
		}
	}
	
	function getAuthMethod() { return $this->_authMethod; }
	
	# returns the identifier of the authenticated user:
	function getUser() { return $this->_user; }
	
	# returns the groups as an array:
	function getGroups() { return $this->_groups; }
	
	# returns whether the user belongs to the specified group:
	function isGroup( $group ) {
		return in_array( $group, $this->_groups );
	}
}



# example:

/*
function myMap( $user, $groups ) {
	$groups[] = 'admin';
	return array(
		'u' => 'mapped_'. $user,
		'g' => $groups
	);
}

$pam = new PAMAL( 'tivoli_with_groups', 'myMap' );
echo 'User: '. $pam->getUser() ."<br />\n";
echo 'Groups: '. implode(' , ', $pam->getGroups() ) ."<br />\n";
echo 'in group "admin": '. (int)$pam->isGroup('admin') ."<br />\n";
*/

?>