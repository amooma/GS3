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
require_once( GS_DIR .'inc/gettext.php' );
require_once( GS_DIR .'htdocs/gui/setup/inc/aux.php' );
require_once( GS_DIR .'inc/keyval.php' );


# set URL path
#
$GS_URL_PATH = dirName(dirName(@$_SERVER['SCRIPT_NAME']));
if (subStr($GS_URL_PATH,-1,1) != '/') $GS_URL_PATH .= '/';
define( 'GS_URL_PATH', $GS_URL_PATH );
unset($GS_URL_PATH);


# get type of installation
#
$GS_INSTALLATION_TYPE = gs_get_conf('GS_INSTALLATION_TYPE');
switch ($GS_INSTALLATION_TYPE) {
	case 'gpbx'  :  $title = 'GPBX'         ; break;
	default      :  $title = 'Gemeinschaft' ; break;
}


# setup possible on this installation?
#
if (! gs_setup_possible()) {
	@header( 'Content-Type: text/plain; charset=utf-8' );
	echo 'Setup via GUI not possible for your installation!' ,"\n";
	exit(1);
}


# some headers
#
@header( 'Content-Type: text/html; charset=utf-8' );
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
/*
if (array_key_exists('setlang', $_REQUEST)) {
	$setlang = preg_replace('/[^a-z\d_]/i', '', @$_REQUEST['setlang']);
	@$_SESSION['lang'] = $setlang;
}
*/
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
$login_info   = '';
$login_errmsg = '';
if (! @$_SESSION['login_ok']) {
	$_SESSION['login_ok'] = false;
	if (gs_keyval_get('setup_pwd') == '') {
		$_SESSION['login_ok'] = true;
	}
	else {
		function gs_setup_auth_by_pwd()
		{
			$pwd_entered = @$_REQUEST['login_pwd'];
			if ($pwd_entered=='') return false;
			$pwd_correct = gs_keyval_get('setup_pwd');
			if ($pwd_entered === $pwd_correct)
				return true;
			return false;
		}
		if (! gs_setup_auth_by_pwd()) {
			$login_info = __('Sie sind nicht eingeloggt.');
			$login_errmsg = __('Pa&szlig;wort ung&uuml;ltig');
		}
		else {
			$_SESSION['login_ok'] = true;
		}
	}
}


# get step
#
$step = preg_replace('/[^a-z0-9\-_]/', '', @$_REQUEST['step']);
if ($step == '') $step = 'system-check';
$step_file = GS_DIR .'htdocs/gui/setup/mod/'.$step.'.php';
if (! file_exists($step_file)) {
	@header( 'HTTP/1.0 404 Not Found', true, 404 );
	@header( 'Status: 404 Not Found', true, 404 );
	@header( 'Content-Type: text/plain; charset=utf-8' );
	echo 'Page not found!' ,"\n";
	exit(1);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo @$_SESSION['isolang']; ?>" xml:lang="<?php echo @$_SESSION['isolang']; ?>">
<head><!--<![CDATA[
                Gemeinschaft
  @(_)=====(_)  (c) 2007, amooma GmbH - http://www.amooma.de
 @   / ### \    Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
 @  |  ###  |   Philipp Kempgen <philipp.kempgen@amooma.de>
  @@|_______|   Peter Kozak <peter.kozak@amooma.de>
                                                      GNU GPL ]]>-->
<title><?php echo $title ,' ', 'Setup'; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/original.css" />
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>setup/setup.css" />
<link rel="shortcut icon" type="image/x-icon" href="<?php echo GS_URL_PATH; ?>favicon.ico" />
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<!-- for stupid MSIE: -->
<!--[if IE]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/msie-fix.css" /><![endif]-->
<!--[if lte IE 6]><style type="text/css">img {behavior: url("<?php echo GS_URL_PATH; ?>js/pngbehavior.htc.php?msie-sucks=.htc");}</style><![endif]-->
</head>
<body>
<div id="topheader"></div>
<div id="headerboxes">
<div id="boxtitle">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/32/app/yast_PhoneTTOffhook.png" class="fl" />
	<h1><?php echo $title ,' ', 'Setup'; ?></h1> 
</div>
</div>

<?php

//echo GS_URL_PATH ,"<br/>\n";
//print_r($_SESSION);

@include_once( $step_file );


?>

</body>
</html>