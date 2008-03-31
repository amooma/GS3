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


# get step
#
$step = preg_replace('/[^a-z0-9\-_]/', '', @$_REQUEST['step']);
if ($step == '') $step = 'system-check';


# authenticate the user
#
$keyval_setup_pwd = trim(gs_keyval_get('setup_pwd'));

function gs_setup_auth_by_pwd()
{
	global $keyval_setup_pwd;
	
	$user_entered = trim(@$_REQUEST['login_user']);
	if ($user_entered !== 'sysadmin') return false;
	
	$pwd_entered = trim(@$_REQUEST['login_pwd']);
	if ($pwd_entered=='') return false;
	
	if ($pwd_entered === $keyval_setup_pwd)
		return true;
	return false;
}

$login_info   = '';
$login_errmsg = '';
if (! @$_SESSION['login_ok']) {
	$_SESSION['login_ok'] = false;
	if ($keyval_setup_pwd == '') {
		$_SESSION['login_ok'] = true;
	}
	else {
		if (! gs_setup_auth_by_pwd()) {
			$login_info = 'Sie sind nicht eingeloggt.';
			$login_errmsg = 'Benutzername/Pa&szlig;wort falsch.';
		}
		else {
			$_SESSION['login_ok'] = true;
		}
	}
}
if ($keyval_setup_pwd == '') {
	$step = 'login';
}
elseif (! @$_SESSION['login_ok']) {
	$step = 'login';
}


# check step file
#
$step_file = GS_DIR .'htdocs/gui/setup/mod/'.$step.'.php';
if (! file_exists($step_file)) {
	@header( 'HTTP/1.0 404 Not Found', true, 404 );
	@header( 'Status: 404 Not Found', true, 404 );
	@header( 'Content-Type: text/plain; charset=utf-8' );
	echo 'Page "', $step ,'" not found!' ,"\n";
	exit(1);
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo @$_SESSION['isolang']; ?>" xml:lang="<?php echo @$_SESSION['isolang']; ?>">
<head><!--<![CDATA[
                Gemeinschaft
  @(_)=====(_)  (c) 2007-2008, amooma GmbH - http://www.amooma.de
 @   / ### \    Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
 @  |  ###  |   Philipp Kempgen <philipp.kempgen@amooma.de>
  @@|_______|   Peter Kozak <peter.kozak@amooma.de>
                                                      GNU GPL ]]>-->
<title><?php
	switch ($GS_INSTALLATION_TYPE) {
		case 'gpbx': echo 'GPBX'        ; break;
		default    : echo 'Gemeinschaft';
	}
	echo ' ', 'Setup';
?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/original.css" />
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>setup/setup.css" />
<?php if ($GUI_ADDITIONAL_STYLESHEET = gs_get_conf('GS_GUI_ADDITIONAL_STYLESHEET')) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/<?php echo rawUrlEncode($GUI_ADDITIONAL_STYLESHEET); ?>" />
<?php } ?>
<link rel="shortcut icon" type="image/x-icon" href="<?php echo GS_URL_PATH; ?>favicon.ico" />
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<!-- for stupid MSIE: -->
<!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/msie-fix-6.css" /><![endif]-->
<!--[if gte IE 7]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/msie-fix-7.css" /><![endif]-->
<!--[if lt IE 7]><style type="text/css">img {behavior: url("<?php echo GS_URL_PATH; ?>js/pngbehavior.htc.php?msie-sucks=.htc");}</style><![endif]-->
</head>
<body>
<div id="topheader"></div>
<div id="headerboxes">
<div id="boxtitle">
<?php
	switch ($GS_INSTALLATION_TYPE) {
		case 'gpbx':
			echo '<div style="position:relative;top:0;left:0;" class="fl">' ,"\n";
			echo '<h1 style="margin:0;padding:0;">' ,"\n";
			echo '<img style="position:absolute;top:2px;left:2px;z-index:1;" alt=" " src="', GS_URL_PATH ,'img/gpbx-shadow-32.png" />' ,"\n";
			echo '<img style="position:absolute;top:0px;left:0px;z-index:2;" alt="GPBX" src="', GS_URL_PATH ,'img/gpbx-32.png" />' ,"\n";
			echo '<div style="margin:0 0 0 125px;"><b>[', 'setup' ,']</b></div>' ,"\n";
			echo '</h1>' ,"\n";
			echo '</div>' ,"\n";
			break;
		default    :
			echo '<img alt=" " src="', GS_URL_PATH ,'crystal-svg/32/app/yast_PhoneTTOffhook.png" class="fl" />' ,"\n";
			echo '<h1>', 'Gemeinschaft' ,' ', 'Setup' ,'</h1>' ,"\n";
	}
?>
</div>
</div>

<div>
<div class="fl l" style="width:40%;">
<?php
	if (gs_keyval_get('setup_show') !== 'autoshow') {
		echo '<a href="', gs_get_conf('GS_URL_PATH') ,'">', 'zum normalen Interface' ,'</a>', "\n";
	}
?>
</div>
<div class="fr r" style="width:60%;">
<?php
	if (@$_SESSION['login_ok'] && $step !== 'network') {
?>
	<form class="inline" method="post" action="<?php echo GS_URL_PATH ,'setup/?step=reboot'; ?>">
		<input type="hidden" name="action" value="reboot" />
		<input type="submit" value="<?php echo 'Neustarten'; ?>" />
	</form>
	<form class="inline" method="post" action="<?php echo GS_URL_PATH ,'setup/?step=shutdown'; ?>">
		<input type="hidden" name="action" value="shutdown" />
		<input type="submit" value="<?php echo 'Herunterfahren'; ?>" />
	</form>
<?php
	}
?>
</div>
</div>

<?php
	@include_once( $step_file );
?>

</body>
</html>