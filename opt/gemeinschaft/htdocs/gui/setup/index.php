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
define( 'GS_VALID', true );  /// this is a parent file

require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
# do not rely on any settings in the main config!
# this is the setup!
include_once( GS_DIR .'inc/gettext.php' );
include_once( GS_DIR .'htdocs/gui/setup/inc/aux.php' );


# set URL path
#
$GS_URL_PATH = dirName(@$_SERVER['SCRIPT_NAME']);
if (subStr($GS_URL_PATH,-1,1) != '/') $GS_URL_PATH .= '/';
define( 'GS_URL_PATH', $GS_URL_PATH );
unset($GS_URL_PATH);


# some headers
#
@header( 'Content-type: text/html; charset=utf-8' );
@header( 'Pragma: no-cache' );
@header( 'Cache-Control: private, no-cache, must-revalidate' );
@header( 'Expires: 0' );
@header( 'Vary: *' );


# start or bind to session
#
# start session even if GS_GUI_SESSIONS==false so $_SESSION is
# superglobal
session_name('gemeinschaft-setup');
session_start();


# set language
#
if (array_key_exists('setlang', $_REQUEST)) {
	$setlang = preg_replace('/[^a-z\d_]/i', '', @$_REQUEST['setlang']);
	@$_SESSION['lang'] = $setlang;
}
if (array_key_exists('lang', $_SESSION))
	$ret = gs_setlang( $_SESSION['lang'] );
else
	#$ret = gs_setlang( GS_INTL_LANG );
	$ret = gs_setlang( 'de' );
if ($ret) $_SESSION['lang'] = $ret;
$_SESSION['isolang'] = str_replace('_', '-', $_SESSION['lang']);
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );


# authenticate the user
#
if (! @$_SESSION['login_ok'] && ! @$_SESSION['login_user']) {
	require_once( GS_DIR .'htdocs/gui/inc/pamal/pamal.php' );
	
}




echo GS_URL_PATH;







?>