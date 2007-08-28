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
require_once( dirName(__FILE__) .'/../../inc/conf.php' );

include_once( GS_DIR .'inc/gettext.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );
require_once( GS_HTDOCS_DIR .'inc/modules.php' );
//set_error_handler('err_handler_die_on_err');


# get section & module
#
if (isSet( $_REQUEST['s'] )) {
	$SECTION = @$_REQUEST['s'];
	$MODULE  = @$_REQUEST['m'];
} else {	
	$SECTION = 'home';
	$MODULE  = '';
}
if (preg_match('/[^a-z0-9\\-_]/', $SECTION.$MODULE))
	die( 'Invalid request! ');
if (! isSet( $MODULES[$SECTION]['sub'] ))
	die( 'Invalid request! ');

if (count( $MODULES[$SECTION]['sub'] ) < 2 || ! $MODULE) {
	list($k,$v) = each( $MODULES[$SECTION]['sub'] );
	$MODULE = $k;
}
if (! isSet( $MODULES[$SECTION]['sub'][$MODULE] ))
	die( 'Invalid request! ');

if ( @$MODULES[$SECTION]['perms'] == 'admin'
  && ! (preg_match('/\\b'.(@$_SESSION['real_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS)) )
{
	die( 'You are not an admin!' );
}



function htmlEnt( $str )
{
	return htmlSpecialChars( $str, ENT_QUOTES );
}

function gs_url( $sect='', $mod='', $sudo_user='' )
{
	global $SECTION, $MODULE, $_SESSION;
	if (! $sudo_user) $sudo_user = @$_SESSION['sudo_user']['name'];
	return GS_URL_PATH
		.'?s='. $sect
		. ($mod ? '&amp;m='. $mod :'')
		. ($sudo_user ? '&amp;sudo='. $sudo_user :'');
}

function gs_form_hidden( $sect='', $mod='', $sudo_user='' )
{
	global $SECTION, $MODULE, $_SESSION;
	if (! $sudo_user) $sudo_user = @$_SESSION['sudo_user']['name'];
	$ret = '<input type="hidden" name="s" value="'. $sect .'" />';
	if ($mod)
		$ret.= '<input type="hidden" name="m" value="'. $mod .'" />';
	if ($sudo_user)
		$ret.= '<input type="hidden" name="sudo" value="'. $sudo_user .'" />';
	return $ret ."\n";
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de-DE" xml:lang="de-DE">
<head><!--<![CDATA[
                Gemeinschaft
  @(_)-----(_)  (c) 2007, amooma GmbH - http://www.amooma.de/
 @   / ### \    Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
 @  |  ###  |   Philipp Kempgen <philipp.kempgen@amooma.de>
  @@|_______|   Peter Kozak <peter.kozak@amooma.de>
                                                      GNU GPL ]]>-->
<title><?php echo __('Gemeinschaft Telefon-Manager'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/original.css" />
<link rel="shortcut icon" type="image/x-icon" href="<?php echo GS_URL_PATH; ?>favicon.ico" />
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<!-- for stupid MSIE: -->
<!--[if IE]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/msie-fix.css" /><![endif]-->
<!--[if lte IE 6]><style type="text/css">img {behavior: url("js/pngbehavior.htc.php?msie-sucks=.htc");}</style><![endif]-->
</head>
<body>

<div id="topheader"></div>
<div id="headerboxes">
<div id="boxtitle">
	<img alt="Suchen" src="<?php echo GS_URL_PATH; ?>crystal-svg/32/app/yast_PhoneTTOffhook.png" class="fl" />
	<h1><?php echo __('Telefon-Manager'); ?></h1> 
</div>
<!--<img alt="Gemeinschaft" src="<?php echo GS_URL_PATH; ?>img/logo.gif" class="fr" />-->

<a href="<?php echo gs_url($SECTION, $MODULE); ?>&amp;setlang=en_US" title="English">
<img alt="en_US" src="<?php echo GS_URL_PATH; ?>img/lang/en_US.png" class="fr" /></a>
<a href="<?php echo gs_url($SECTION, $MODULE); ?>&amp;setlang=de_DE" title="Deutsch">
<img alt="de_DE" src="<?php echo GS_URL_PATH; ?>img/lang/de_DE.png" class="fr" /></a>
</div>

<div class="sidebar">
<div id="menu">

<ul>
<?php

foreach ($MODULES as $sectname => $sectinfo) {
	$sect_active = ($sectname == $SECTION);
	
	if (array_key_exists('inmenu', $sectinfo) && ! $sectinfo['inmenu'])
		continue;
	
	if ( @$sectinfo['perms'] == 'admin'
	&&   ! (preg_match('/\\b'.(@$_SESSION['real_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS))
	)
		continue;
	
	echo '<li class="'. ($sect_active ? 'expanded' : 'collapsed') .'">', "\n";
	//echo '<a href="'. GS_URL_PATH .'?s='. $sectname .'" class="'. (($sect_active) ? 'active' : '') .'">'. $sectinfo['title'] .'</a>', "\n";
	echo '<a href="', gs_url($sectname, ''), '" class="', (($sect_active) ? 'active' : ''), '">';
	if (@$sectinfo['icon'])
		echo '<img alt="" src="', GS_URL_PATH, str_replace('%s', '16', $sectinfo['icon']), '" /> ';
	echo $sectinfo['title'], '</a>', "\n";
	if (count($sectinfo['sub']) > 1) {
		echo '<ul class="menu">', "\n";
		foreach ($sectinfo['sub'] as $modname => $modinfo) {
			if (array_key_exists('inmenu', $modinfo) && ! $modinfo['inmenu'])
				continue;
			
			echo "\t", '<li class="leaf"><a href="'. gs_url($sectname, $modname) .'" class="'. ($modname==$MODULE ? 'active' : '') .'"><img alt="" src="', GS_URL_PATH, 'img/tree.gif" />'. $modinfo['title'] .'</a></li>', "\n";
		}
		echo '</ul>', "\n";
	}
	echo '</li>', "\n";
}

?>
</ul>
</div>
</div>

<div id="content-container">


<div id="sudo-bar">
<div id="sudo-info">
<?php

if (@$_SESSION['login_ok']) {
	if (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name']) {
		echo __('Angemeldet'), ': <span class="user-name"><b>', htmlEnt(
			@$_SESSION['real_user']['info']['firstname'] .' '.
			@$_SESSION['real_user']['info']['lastname']
	), '</b></span>', "\n";
	} else {
		echo __('Angemeldet'), ': <span class="user-name">', htmlEnt(
			$_SESSION['real_user']['info']['firstname'] .' '.
			$_SESSION['real_user']['info']['lastname']
		), '</span> &nbsp;&nbsp; ', __('Als'), ': <span class="user-name"><b>', htmlEnt(
			$_SESSION['sudo_user']['info']['firstname'] .' '.
			$_SESSION['sudo_user']['info']['lastname']
	), '</b></span>', "\n";
	}
} else {
	echo __('Nicht angemeldet');
}

?>
</div>
<form id="sudo-form" method="get" action="<?php echo GS_URL_PATH; ?>" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="s" value="<?php echo $SECTION; ?>" />
<input type="hidden" name="m" value="<?php echo $MODULE; ?>" />
<?php echo __('Benutzer wechseln'), ': '; ?>
<input type="text" size="15" maxlength="30" id="sudo-user" name="sudo" value="<?php echo @$_SESSION['sudo_user']['name']; ?>" tabindex="100" <?php if (!@$_SESSION['login_ok']) echo 'disabled="disabled" '; ?>/>
</form>
<br style="float:none; display:block; clear:right;" />
</div>


<div id="content">

<?php

# check authentication
if (!@$_SESSION['login_ok']) {
	$SECTION = 'login';
	$MODULE  = 'login';
}

$file = GS_HTDOCS_DIR .'mod/'. $SECTION .'_'. $MODULE .'.php';
if (file_exists( $file )) {
	include $file;
} else {
	echo 'Error. Module &quot;'. $SECTION .'_'. $MODULE .'&quot; is missing.';
}

?>

</div>
</div>

<div class="nofloat"></div>

<div id="copyright">&copy; amooma gmbh</div>
</body>
</html>
