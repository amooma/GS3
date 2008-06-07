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
require_once( GS_DIR .'inc/util.php' );

set_error_handler('err_handler_die_on_err');


function _not_found( $msg='Not Found.' )
{
	@header( 'HTTP/1.0 404 Not Found', true, 404 );
	@header( 'Status: 404 Not Found' , true, 404 );
	echo $msg;
	exit(1);
}
function _not_allowed( $msg='Not Allowed.' )
{
	@header( 'HTTP/1.0 403 Forbidden', true, 403 );
	@header( 'Status: 403 Forbidden' , true, 403 );
	echo $msg;
	exit(1);
}

define('GS_WEB_REWRITE',
	   array_key_exists('REDIRECT_URL'             , $_SERVER)
	|| array_key_exists('_GS_HAVE_REWRITE'         , $_SERVER)
	|| array_key_exists('REDIRECT__GS_HAVE_REWRITE', $_SERVER) );



$GS_INSTALLATION_TYPE = gs_get_conf('GS_INSTALLATION_TYPE');
if (in_array($GS_INSTALLATION_TYPE, array('gpbx', 'single'), true)) {
	require_once( GS_DIR .'htdocs/gui/setup/inc/aux-fns.php' );
	if (gs_setup_autoshow()) {
		if (subStr($_SERVER['SERVER_PROTOCOL'],5) >= '1.1') {
			@header( 'HTTP/1.1 303 See Other', true, 303 );
			@header( 'Status: 303 See Other' , true, 303 );
		} else {
			@header( 'HTTP/1.0 302 Moved Temporarily', true, 302 );
			@header( 'Status: 302 Moved Temporarily' , true, 302 );
		}
		$url = (array_key_exists('HTTPS', $_SERVER) ? 'https' : 'http') .'://';
		if (array_key_exists('HTTP_HOST', $_SERVER))
			$url .= $_SERVER['HTTP_HOST'];
		elseif (array_key_exists('SERVER_NAME', $_SERVER))
			$url .= $_SERVER['SERVER_NAME'];
		else
			$url .= @$_SERVER['SERVER_ADDR'];
		if (array_key_exists('SERVER_PORT', $_SERVER) && @$_SERVER['SERVER_PORT'] != 80)
			$url .= ':'.$_SERVER['SERVER_PORT'];
		$url .= dirName($_SERVER['SCRIPT_NAME']).'/setup/';
		@header( 'Location: '. $url );
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' ,"\n";
		echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">' ,"\n";
		echo '<head>' ,"\n";
		echo '<title>Gemeinschaft</title>' ,"\n";
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' ,"\n";
		echo '</head>' ,"\n";
		echo '<body>' ,"\n";
		echo '<br /><p align="center"><a href="setup/">Setup</a></p>' ,"\n";
		echo '</body>' ,"\n";
		echo '</html>';		
		exit;
	}
}


include_once( GS_DIR .'inc/gettext.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );
require_once( GS_HTDOCS_DIR .'inc/modules.php' );


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
	_not_found();
if (! array_key_exists($SECTION, $MODULES)
||  ! array_key_exists('sub', $MODULES[$SECTION]))
	_not_found();

if (count( $MODULES[$SECTION]['sub'] ) < 2 || ! $MODULE) {
	list($k,$v) = each( $MODULES[$SECTION]['sub'] );
	$MODULE = $k;
}
if (! array_key_exists($MODULE, $MODULES[$SECTION]['sub']))
	_not_found();

if (@$MODULES[$SECTION]['perms'] === 'admin'
&&  !(preg_match('/\\b'.(@$_SESSION['sudo_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS)) )
{
	//_not_allowed( 'You are not an admin.' );
	$SECTION = 'home';
	$MODULE  = 'home';
}



function htmlEnt( $str )
{
	return htmlSpecialChars( $str, ENT_QUOTES, 'UTF-8' );
}

function gs_url( $sect='', $mod='', $sudo_user=null, $argstr='' )
{
	global $SECTION, $MODULE, $_SESSION;
	if (! $sudo_user) $sudo_user = @$_SESSION['sudo_user']['name'];
	if (! GS_WEB_REWRITE) {
		return GS_URL_PATH
			.'?s='.$sect
			. ($mod ? '&amp;m='.$mod : '')
			. ($sudo_user ? '&amp;sudo='.$sudo_user : '')
			. ($argstr ? '&amp;'.$argstr : '');
	} else {
		return GS_URL_PATH
			. ($sudo_user ? $sudo_user : 'my') .'/'
			. ($sect ? $sect.'/'. ($mod ? $mod.'/' : '') : '')
			. ($argstr ? '?'.$argstr : '');
	}
}

function gs_form_hidden( $sect='', $mod='', $sudo_user=null )
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


//echo "<pre>"; print_r($_SESSION); echo "</pre>";
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
		case 'gpbx': echo    'GPBX'                         ; break;
		//default    : echo __('Gemeinschaft Telefon-Manager');
		default    : echo    'Gemeinschaft';
	}
?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/original.css" />
<?php if ($GUI_ADDITIONAL_STYLESHEET = gs_get_conf('GS_GUI_ADDITIONAL_STYLESHEET')) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/<?php echo rawUrlEncode($GUI_ADDITIONAL_STYLESHEET); ?>" />
<?php } ?>
<link rel="shortcut icon" type="image/x-icon" href="<?php echo GS_URL_PATH; ?>favicon.ico" />
<!-- for stupid MSIE: -->
<!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/msie-fix-6.css" /><![endif]-->
<!--[if gte IE 7]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/msie-fix-7.css" /><![endif]-->
<!--[if lt IE 7]><style type="text/css">img {behavior: url("<?php echo GS_URL_PATH; ?>js/pngbehavior.htc.php?msie-sucks=.htc");}</style><![endif]-->
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
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
			echo '</h1>' ,"\n";
			echo '</div>' ,"\n";
			break;
		default    :
			echo '<img alt=" " src="', GS_URL_PATH ,'img/gemeinschaft-32.png" class="fl" />' ,"\n";
			//echo '<h1>', __('Telefon-Manager') ,'</h1>' ,"\n";
			echo '<h1>', 'Gemeinschaft' ,'</h1>' ,"\n";
	}
?>
</div>
<div class="tty"><a href="#a-content"><?php echo __('Navigation &uuml;berspringen'); /*//TRANSLATE ME*/ ?></a></div>

<?php
	if (@$_SESSION['login_ok']) {
		if (@$_SESSION['sudo_user']['name'] === 'sysadmin'
		|| (@$_SESSION['sudo_user']['name'] != ''
		&&  preg_match('/\\b'.(@$_SESSION['sudo_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS)
		)) {
			if (! in_array($SECTION, array('logout'), true)) {
				echo '<a href="', gs_url('system', 'shutdown') ,'" title="', __('Auschalten / Neustarten ...') ,'" class="fr" style="display:block; margin:1px 6px;"><img alt="', __('Ausschalten ...') ,'" src="', GS_URL_PATH ,'img/power.png" /></a>' ,"\n";
			}
		}
	}
	
	$langs = gs_get_enabled_langs();
	if (count($langs) > 1) {
		echo '<div id="langs">' ,"\n";
		echo '<span class="tty">', __('Sprache') /*//TRANSLATE ME*/ ,':</span>' ,"\n";
		//$langs = array_reverse($langs);
		foreach ($langs as $lang_name => $l) {
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'setlang='.@$lang_name) ,'" title="', htmlEnt(@$l['title']) ,'">' ,"\n";
			echo '<img alt="', @$lang_name ,'" src="', GS_URL_PATH ,'img/lang/', htmlEnt(@$l['icon']) ,'.png" /></a>' ,"\n";
		}
		echo '</div>' ,"\n";
		unset($iso, $l);
	}
	unset($langs);
?>
</div>

<div class="sidebar">
<div id="menu">
<ul>
<?php

foreach ($MODULES as $sectname => $sectinfo) {
	$sect_active = ($sectname === $SECTION);
	
	if (array_key_exists('inmenu', $sectinfo) && ! $sectinfo['inmenu'])
		continue;
	if (count($sectinfo['sub']) < 1)
		continue;
	if (! @$_SESSION['login_ok']
	&&  ! in_array($sectname, array('home','login'), true)) {
		continue;
	}
	
	if (@$_SESSION['sudo_user']['name'] !== 'sysadmin') {
		if (@$sectinfo['perms'] === 'admin' && (
			   @$_SESSION['sudo_user']['name'] == ''
			|| ! preg_match('/\\b'.(@$_SESSION['sudo_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS)
		)) {
			continue;
		}
	} else {
		if (@$sectinfo['perms'] !== 'admin'
		&&  ! in_array($sectname, array('home','login','logout'), true)) {
			continue;
		}
	}
	
	echo '<li class="'. ($sect_active ? 'expanded' : 'collapsed') .'">', "\n";
	//echo '<a href="'. GS_URL_PATH .'?s='. $sectname .'" class="'. (($sect_active) ? 'active' : '') .'">'. $sectinfo['title'] .'</a>', "\n";
	echo '<a href="', gs_url($sectname, ''), '" class="', (($sect_active) ? 'active' : ''), '">';
	if (@$sectinfo['icon'])
		echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '16', $sectinfo['icon']), '" /> ';
	echo $sectinfo['title'], '</a>', "\n";
	if (count($sectinfo['sub']) > 1) {
		echo '<ul class="menu">', "\n";
		foreach ($sectinfo['sub'] as $modname => $modinfo) {
			if (array_key_exists('inmenu', $modinfo) && ! $modinfo['inmenu']) {
				continue;
			}
			
			echo '<li class="leaf"><a href="'. gs_url($sectname, $modname) .'" class="'. ($modname==$MODULE ? 'active' : '') .'"><img alt=" " src="', GS_URL_PATH, 'img/tree.gif" />'. $modinfo['title'] .'</a></li>', "\n";
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
<div id="sudo-info"><?php
	
	if (@$_SESSION['login_ok']) {
		if (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name']) {
			echo __('Angemeldet'), ': <span class="user-name"><b>', htmlEnt(
				@$_SESSION['real_user']['info']['firstname'] .' '.
				@$_SESSION['real_user']['info']['lastname']
		), '</b></span>';
		} else {
			echo __('Angemeldet'), ': <span class="user-name">', htmlEnt(
				$_SESSION['real_user']['info']['firstname'] .' '.
				$_SESSION['real_user']['info']['lastname']
			), '</span> &nbsp;&nbsp; ', __('Als'), ': <span class="user-name"><b>', htmlEnt(
				$_SESSION['sudo_user']['info']['firstname'] .' '.
				$_SESSION['sudo_user']['info']['lastname']
		), '</b></span>';
		}
	} else {
		echo __('Nicht angemeldet');
	}

?></div>
<?php if (@$_SESSION['login_ok']) { ?>
<form id="sudo-form" method="get" action="<?php echo GS_URL_PATH; ?>" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="s" value="<?php echo $SECTION; ?>" />
<input type="hidden" name="m" value="<?php echo $MODULE; ?>" />
<?php echo __('Benutzer wechseln'), ': '; ?>
<input type="text" size="15" maxlength="30" id="sudo-user" name="sudo" value="<?php echo @$_SESSION['sudo_user']['name']; ?>" tabindex="100" />
</form>
<?php } ?>
<br style="float:none; clear:right;" />
</div>

<div id="content">
<hr class="tty" width="50%" align="left" />
<a name="a-content" id="a-content" class="tty"></a>

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
<div id="copyright"><a target="_blank" href="http://www.amooma.de/"><img alt="&copy; amooma" src="<?php echo GS_URL_PATH; ?>img/amooma-c.gif" /></a></div>
</body>
</html>