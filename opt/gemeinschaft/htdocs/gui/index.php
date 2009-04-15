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
if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
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
require_once( GS_DIR .'htdocs/gui/inc/session.php' );  # defines $DB
require_once( GS_HTDOCS_DIR .'inc/modules.php' );


# get section & module
#
if (array_key_exists('s', $_REQUEST)) {
	$SECTION = $_REQUEST['s'];
	$MODULE  = array_key_exists('m', $_REQUEST) ? $_REQUEST['m'] : '';
} else {
	$SECTION = 'home';
	$MODULE  = '';
}
if (preg_match('/[^a-z0-9\\-_]/', $SECTION.$MODULE)) {
	_not_found();
}


# BOI menu
#
$boi_menu_error_msg = false;
if (gs_get_conf('GS_BOI_ENABLED')
&&  $_SESSION['sudo_user']['boi_host_id'] > 0
&&  $SECTION !== 'logout')
{
	include_once( GS_DIR .'inc/boi-soap/boi-api.php' );
	$api = gs_host_get_api( $_SESSION['sudo_user']['boi_host_id'] );
	switch ($api) {
		case 'm01':
		case 'm02':
			$menu = false;
			
			$boi_soap_role = preg_replace('/^boi-/', '', $_SESSION['sudo_user']['boi_role']);
			$boi_soap_ext =
				in_array($_SESSION['sudo_user']['boi_role'], array('gs', 'boi-user'))
				? $_SESSION['sudo_user']['info']['ext']
				: '';
			
			if ($boi_soap_ext != '') {
				$hp_route_prefix = (string)$DB->executeGetOne(
					'SELECT `value` FROM `host_params` '.
					'WHERE '.
						'`host_id`='. (int)$_SESSION['sudo_user']['boi_host_id'] .' AND '.
						'`param`=\'route_prefix\''
					);
				$sub_ext = (subStr($boi_soap_ext,0,strLen($hp_route_prefix)) === $hp_route_prefix)
					? subStr($boi_soap_ext, strLen($hp_route_prefix)) : $boi_soap_ext;
				gs_log( GS_LOG_DEBUG, "Mapping ext. $boi_soap_ext to $sub_ext for SOAP call" );
				$boi_soap_ext = $sub_ext;
				unset($sub_ext);
			}
			
			$boi_menu_key =
				$api .'-'.
				$boi_soap_role .'-'.
				@$_SESSION['sudo_user']['boi_host'] .'-'.
				//@$_SESSION['sudo_user']['name'] .'-'.
				$boi_soap_ext;
			if (array_key_exists('boi_menu_cache', $_SESSION['sudo_user'])
			&&  is_array($_SESSION['sudo_user']['boi_menu_cache'])
			&&  $_SESSION['sudo_user']['boi_menu_cache']['key'] === $boi_menu_key
			&&  $_SESSION['sudo_user']['boi_menu_cache']['expires'] > time()
			) {
				gs_log( GS_LOG_DEBUG, 'Using BOI menu "'.$boi_menu_key.'" from cache (expires in '. ($_SESSION['sudo_user']['boi_menu_cache']['expires'] - time()) .' s)' );
				$menu = $_SESSION['sudo_user']['boi_menu_cache']['menu'];
				if ($_SESSION['sudo_user']['boi_menu_cache']['expires'] > time()+180) {
					$_SESSION['sudo_user']['boi_menu_cache']['expires'] = time()+180;
				}
			}
			else {
				//gs_log( GS_LOG_DEBUG, 'BOI menu "'.$boi_menu_key.'" not cached' );
				
				//if (! class_exists('SoapClient')) {
				if (! extension_loaded('soap')) {
					$boi_menu_error_msg = 'Fehler beim Abfragen des Men&uuml;s der Agentur.';
				} else {
					include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );
					
					$soap_faultcode = null;
					if ($_SESSION['sudo_user']['boi_session'] === null) {
						# start a new session at the branch office PBX
						$_SESSION['sudo_user']['boi_session']
							= gs_boi_start_gui_session(
								$api,
								$_SESSION['sudo_user']['boi_host'],
								$boi_soap_role,
								$boi_soap_ext,
								$soap_faultcode  # by reference
								);
						if (! $_SESSION['sudo_user']['boi_session'])
							$_SESSION['sudo_user']['boi_session'] = null;
					}
					
					if ($soap_faultcode === 'HTTP') {
						gs_log( GS_LOG_DEBUG, 'Skipping SOAP call to get menu. Server not reachable.' );
					} else {
						$menu = @gs_boi_get_gui_menu(
							$api,
							$_SESSION['sudo_user']['boi_host'],
							$boi_soap_role,
							$boi_soap_ext,
							$_SESSION['sudo_user']['boi_session']
							);
					}
					unset($soap_faultcode);
				}
				
				$expires = ($boi_menu_error_msg === false ? 180 : 50);
				gs_log( GS_LOG_DEBUG, 'Caching BOI menu "'.$boi_menu_key.'" (expires in '.$expires.' s)' );
				$_SESSION['sudo_user']['boi_menu_cache'] = array(
					'key'     => $boi_menu_key,
					'expires' => time()+$expires,
					'menu'    => $menu
				);
				unset($expires);
			}
			unset($boi_soap_role);
			unset($boi_soap_ext);
			//unset($boi_menu_key);
			
			if (! is_array($menu)) {
				if ($boi_menu_error_msg === false) {
					$boi_menu_error_msg = 'Fehler beim Abfragen des Men&uuml;s der Agentur.';
				}
			} else {
				/*
				$MODULES['boi-diallog'] = array(
					'is_boi'=> true,
					'title' => 'Anruflisten',
					'icon'  => 'crystal-svg/%s/act/misc.png',
					'sub'   => array(
						'in' => array('title'=>'eingehend'),
						'out' => array('title'=>'ausgehend')
					)
				);
				$MODULES['boi-help'] = array(
					'is_boi'=> true,
					'title' => 'Hilfe',
					'icon'  => 'crystal-svg/%s/act/misc.png',
					'sub'   => array(
						'snom' => array('title'=>'Snom'),
						'os' => array('title'=>'OpenStage')
					)
				);
				*/
				$MODULES = array_merge($MODULES, $menu);
			}
			unset($menu);
			break;
		
		case '':
			# host does not provide any API
			break;
		
		default:
			gs_log( GS_LOG_WARNING, 'Invalid API "'.$api.'" for host '. $_SESSION['sudo_user']['boi_host'] );
			$boi_menu_error_msg = 'Fehler beim Abfragen des Men&uuml;s der Agentur.';
	}
}


# get section & module
#
if (! array_key_exists($SECTION, $MODULES)
||  ! array_key_exists('sub', $MODULES[$SECTION]))
{
	//_not_found();
	$SECTION = 'home';
	$MODULE  = '';
}

if (count( $MODULES[$SECTION]['sub'] ) < 2 || ! $MODULE) {
	list($k,$v) = each( $MODULES[$SECTION]['sub'] );
	$MODULE = $k;
}
if (! array_key_exists($MODULE, $MODULES[$SECTION]['sub'])) {
	//_not_found();
	$SECTION = 'home';
	$MODULE  = '';
}

if (array_key_exists('perms', $MODULES[$SECTION])
&&  $MODULES[$SECTION]['perms'] === 'admin'
&&  !(preg_match('/\\b'.(@$_SESSION['sudo_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS)) )
{
	//_not_allowed( 'You are not an admin.' );
	$SECTION = 'home';
	$MODULE  = 'home';
}
if ($_SESSION['sudo_user']['boi_host_id'] > 0
&&  $_SESSION['sudo_user']['boi_role'] !== 'gs'
&&  array_key_exists('boi_ok', $MODULES[$SECTION])
&&  $sectinfo['boi_ok'] == false) {
	$SECTION = 'home';
	$MODULE  = 'home';
}

if (@array_key_exists('is_boi', @$MODULES[$SECTION])
&&  @$MODULES[$SECTION]['is_boi']) {
	if (! headers_sent()) {
		$etag = $boi_menu_key .'-'. $SECTION .'-'. $MODULE .'-'. date('YmdHi');
		header( 'Pragma: no-cache' );
		header( 'Cache-Control: private, must-revalidate' );
		header( 'ETag: '. $etag );
		if (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER)
		&&  $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
			header( 'HTTP/1.1 304 Not Modified', true, 304 );
			header( 'Status: 304 Not Modified' , true, 304 );
			gs_log( GS_LOG_DEBUG, "Page not modified ($etag)" );
			exit(0);
		}
	}
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
<?php
	if (array_key_exists('is_boi', $MODULES[$SECTION])
	&&  $MODULES[$SECTION]['is_boi'])
	{
		echo '<script type="text/javascript" src="', GS_URL_PATH ,'js/anti-xss.js"></script>', "\n";
	}
	
	$reverse_proxy = gs_get_conf('GS_BOI_GUI_REVERSE_PROXY');
	if (! preg_match('/^https?:\/\//', $reverse_proxy))
		$reverse_proxy = 'http://'.$reverse_proxy;
	if (subStr($reverse_proxy,-1) != '/')
		$reverse_proxy.= '/';
	
	/*
	if (gs_get_conf('GS_BOI_ENABLED')) {
?>
<script type="text/javascript">
//<![CDATA[
function gs_boi_menu_sc( url )
{
	if (document.getElementById) {
		if ((ifrm = document.getElementById('boi-content'))) {
			var src = '<?php echo htmlEnt($reverse_proxy); ?>' + 'http' +'/'+ '<?php echo $_SESSION['sudo_user']['boi_host']; ?>' + url + (url.indexOf('?')==-1 ? '?':'&amp;') + 'SESSID=' + '<?php echo $_SESSION['sudo_user']['boi_session']; ?>';
			ifrm.src = src;
			return false;
		}
	}
	return true;
}
//]]>
</script>
<?php
	}
	*/
?>
<!-- for stupid MSIE: -->
<!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/msie-fix-6.css" /><![endif]-->
<!--[if gte IE 7]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/msie-fix-7.css" /><![endif]-->
<!--[if lt IE 8]><style type="text/css">button {behavior: url("<?php echo GS_URL_PATH; ?>js/msie-button-fix.htc.php?msie-sucks=.htc");}</style><![endif]-->
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
			echo '<img alt=" " src="', GS_URL_PATH ,'img/locanet.png" class="fl" />' ,"\n";
			//echo '<h1>', __('Telefon-Manager') ,'</h1>' ,"\n";
			echo '<h1>', 'Gemeinschaft' ,'</h1>' ,"\n";
	}
?>
</div>
<div class="tty"><a href="#a-content"><?php echo __('Navigation &uuml;berspringen'); ?></a></div>

<?php
	if (@$_SESSION['login_ok'] && gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
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
		echo '<span class="tty">', __('Sprache') ,':</span>' ,"\n";
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
	if ($_SESSION['sudo_user']['boi_host_id'] > 0
	&&  $_SESSION['sudo_user']['boi_role'] !== 'gs'
	&&  array_key_exists('boi_ok', $sectinfo)
	&&  $sectinfo['boi_ok'] == false) {
		continue;
	}
	
	if (@$_SESSION['sudo_user']['name'] !== 'sysadmin') {
		if (array_key_exists('perms', $sectinfo)
		&&  $sectinfo['perms'] === 'admin'
		&&  (@$_SESSION['sudo_user']['name'] == ''
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
			echo '<li class="leaf"><a href="', gs_url($sectname, $modname) ,'" class="';
			if ($modname == $MODULE) echo 'active';
			echo '"';
			/*
			if (array_key_exists('boi_url', $modinfo)) {
				echo ' onclick="try{ return gs_boi_menu_sc(\'', htmlEnt($modinfo['boi_url']) ,'\'); }catch(e){}"';
			}
			*/
			echo '><img alt=" " src="', GS_URL_PATH ,'img/tree.gif" />', $modinfo['title'] ,'</a></li>', "\n";
		}
		echo '</ul>', "\n";
	}
	echo '</li>', "\n";
}

?>
</ul>
<?php
if (gs_get_conf('GS_BOI_ENABLED') && $boi_menu_error_msg !== false) {
	echo '<div class="errorbox" style="min-width:auto; max-width:90%; margin:1.5em auto; padding:3px; font-size:95%; line-height:120%;">', ($boi_menu_error_msg) ,'</div>' ,"\n";
	unset($boi_menu_error_msg);
}
?>
</div>
</div>

<div id="content-container">
<div id="sudo-bar">
<div id="sudo-info">
<?php
	
	if (@$_SESSION['login_ok']) {
		if (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name']) {
			echo __('Angemeldet'), ': <span class="user-name nobr"><b>', htmlEnt(
				@$_SESSION['real_user']['info']['firstname'] .' '.
				@$_SESSION['real_user']['info']['lastname']
		), '</b></span>';
		} else {
			echo __('Angemeldet'), ': <span class="user-name nobr">', htmlEnt(
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
<?php
	if (@$_SESSION['login_ok']) {
		echo '<form id="sudo-form" method="get" action="', GS_URL_PATH ,'" enctype="application/x-www-form-urlencoded">' ,"\n";
		echo '<input type="hidden" name="s" value="', $SECTION ,'" />' ,"\n";
		echo '<input type="hidden" name="m" value="', $MODULE ,'" />' ,"\n";
		if (! gs_get_conf('GS_BOI_ENABLED')) {
			
			echo __('Benutzer wechseln') ,': ';
			echo '<input type="text" size="15" maxlength="30" id="sudo-user" name="sudo" value="', @$_SESSION['sudo_user']['name'] ,'" tabindex="100" />' ,"\n";
			
		} else {
			
			echo '<div class="nobr fr">' ,"\n";
			echo ' &nbsp;<button type="submit">&rarr;</button>';
			echo '</div>' ,"\n";
			
			echo '<div class="nobr fr">' ,"\n";
			echo ' &nbsp;&nbsp; ', __('Benutzer') ,': ';
			echo '<input type="text" size="10" maxlength="30" id="sudo-user" name="sudo" value="', @$_SESSION['sudo_user']['name'] ,'" tabindex="102" />' ,"\n";
			echo '</div>' ,"\n";
			
			$roles = array();
			if ($_SESSION['real_user']['name'] === 'sysadmin'
			||  preg_match('/\\b'.($_SESSION['real_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS)) {
				$roles = array(
					'gs'             => __('Zentrale'),
					'boi-user'       => __('Agentur-Benutzer'),
					'boi-localadmin' => __('Agentur-Admin'),
					'boi-sysadmin'   => __('Agentur-Sysadmin')
				);
			} else {
				if (! $_SESSION['real_user']['info']['host_is_foreign']) {
					$roles['gs'] = __('Zentrale');
				} else {
					$roles['boi-user'] = __('Agentur-Benutzer');
					$db_roles = $DB->executeGetOne( 'SELECT `roles` FROM `boi_perms` WHERE `user_id`='. (int)$_SESSION['real_user']['info']['id'] );
					if (strPos($db_roles,'l') !== false) {
						$roles['boi-localadmin'] = __('Agentur-Admin');
					}
				}
			}
			echo '<div class="nobr fr">' ,"\n";
			echo ' &nbsp;&nbsp; ', __('Rolle') ,':',"\n";
			echo '<select name="boi_role" tabindex="101" onchange="this.form.submit();">' ,"\n";
			foreach ($roles as $role => $title) {
				echo '<option value="',$role ,'"';
				if ($role === $_SESSION['sudo_user']['boi_role'])
					echo ' selected="selected"';
				echo '>', $title ,'</option>' ,"\n";
			}
			echo '</select>' ,"\n";
			echo '</div>' ,"\n";
			
			echo '<div class="nobr fr">' ,"\n";
			if ($_SESSION['real_user']['name'] === 'sysadmin'
			||  preg_match('/\\b'.($_SESSION['real_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS)) {
				$query =
'(SELECT 0 `id`, \''. $DB->escape(__('Zentrale')) .'\' `comment`, 0 `ord`
)
UNION
(SELECT `id`, `comment`, 1 `ord`
FROM `hosts`
WHERE `is_foreign`=1
)
ORDER BY `ord`, `comment`'
				;
			} else {
				if (! $_SESSION['real_user']['info']['host_is_foreign']) {
					$query =
'(SELECT 0 `id`, \''. $DB->escape(__('Zentrale')) .'\' `comment`, 0 `ord`
)
UNION
(SELECT `h`.`id`, `h`.`comment`, 1 `ord`
FROM
	`hosts` `h` JOIN
	`boi_perms` `p` ON (`p`.`host_id`=`h`.`id`)
WHERE
	`p`.`user_id`='. (int)$_SESSION['real_user']['info']['id'] .' AND
	`h`.`is_foreign`=1 AND
	`p`.`roles`<>\'\'
)
ORDER BY `ord`, `comment`'
					;
				} else {
					$query =
'(SELECT `h`.`id`, `h`.`comment`, 0 `ord`
FROM `hosts` `h`
WHERE `h`.`id`='. (int)$_SESSION['real_user']['info']['host_id'] .'
)
UNION
(SELECT `h`.`id`, `h`.`comment`, 1 `ord`
FROM
	`hosts` `h` JOIN
	`boi_perms` `p` ON (`p`.`host_id`=`h`.`id`)
WHERE
	`p`.`user_id`='. (int)$_SESSION['real_user']['info']['id'] .' AND
	`h`.`is_foreign`=1 AND
	`p`.`roles`<>\'\'
)
ORDER BY `ord`, `comment`'
					;
				}
			}
			$rs = $DB->execute($query);
			echo ' &nbsp;&nbsp; ', __('Anlage') ,':',"\n";
			echo '<select name="boi_host_id" tabindex="100" onchange="this.form.submit();">' ,"\n";
			$tmp_host_id = null;
			if (! $rs) {
				gs_log( GS_LOG_WARNING, 'Failed to get nodes / foreign hosts' );
			} else {
				while ($r = $rs->fetchRow()) {
					if ($tmp_host_id !== null) continue;  # do not show foreign host twice for local admins
					echo '<option value="',$r['id'],'"';
					if ($r['id'] === $_SESSION['sudo_user']['boi_host_id']) {
						echo ' selected="selected"';
					}
					echo '>', htmlEnt(mb_subStr($r['comment'],0,20));
					if ($r['id'] === $_SESSION['real_user']['info']['host_id']
					||  (! $_SESSION['real_user']['info']['host_is_foreign'] && $r['id'] === 0)
					) {
						echo ' &bull;';
						$tmp_host_id = $r['id'];
					}
					echo '</option>' ,"\n";
				}
			}
			unset($tmp_host_id);
			echo '</select>' ,"\n";
			echo '</div>' ,"\n";
			
		}
		echo '</form>' ,"\n";
	}
?>
<br style="float:none; clear:right;" />
</div>

<hr class="tty" width="50%" align="left" />
<a name="a-content" id="a-content" class="tty"></a>

<?php

# check authentication
if (!@$_SESSION['login_ok']) {
	$SECTION = 'login';
	$MODULE  = 'login';
}

$boi_home_override = false;
if (gs_get_conf('GS_BOI_ENABLED')
&&  $_SESSION['sudo_user']['boi_host_id'] > 0
&&  $SECTION === 'home'
&&  in_array($MODULE, array('','home'), true)
&&  @$_SESSION['login_ok']
) {
	$boi_home = explode('/', gs_get_conf(@$_SESSION['sudo_user']['boi_role'] === 'boi-user' ? 'GS_BOI_GUI_HOME_USER' : 'GS_BOI_GUI_HOME_ADMIN'));
	$boi_home_section = 'boi-'.@$boi_home[0];
	$boi_home_module  =        @$boi_home[1];
	if (array_key_exists($boi_home_section, $MODULES)
	&&  array_key_exists('sub', $MODULES[$boi_home_section])
	&&  array_key_exists($boi_home_module, $MODULES[$boi_home_section]['sub'])
	) {
		$boi_home_override = true;
		$SECTION = $boi_home_section;
		$MODULE  = $boi_home_module;
	}
}

if ((! array_key_exists('is_boi', $MODULES[$SECTION])
||   ! $MODULES[$SECTION]['is_boi'])
&&   ! $boi_home_override)
{
	echo '<div id="content">' ,"\n";
	$file = GS_HTDOCS_DIR .'mod/'. $SECTION .'_'. $MODULE .'.php';
	if (file_exists( $file )) {
		include $file;
	} else {
		echo 'Error. Module &quot;'. $SECTION .'_'. $MODULE .'&quot; is missing.';
	}
	echo '</div>' ,"\n";
}
else {
	echo '<div style="width:auto; margin-left:170px; padding:0; position:relative; top:0; left:0;">' ,"\n";
	if (! @array_key_exists('boi_url', @$MODULES[$SECTION]['sub'][$MODULE])) {
		echo 'Error.';
	} else {
		echo '<iframe id="boi-content" src="';
		echo $reverse_proxy;
		unset($reverse_proxy);
		
		/*
		include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );
		if (_gs_boi_ssl_is_possible($_SESSION['sudo_user']['boi_host']))
			echo 'https';
		else
		*/
			echo 'http';
		echo '/', $_SESSION['sudo_user']['boi_host'];
		echo htmlEnt($MODULES[$SECTION]['sub'][$MODULE]['boi_url']);
		
		echo (strPos($MODULES[$SECTION]['sub'][$MODULE]['boi_url'], '?') === false)
			? '?':'&amp;';
		echo 'SESSID=', htmlEnt(@$_SESSION['sudo_user']['boi_session']);
		
		echo '" style="width:100%; position:absolute; left:0; top:0; margin:0; padding:0; border-width:1px 0 0 0; border-style:solid none none none; border-color:#99f transparent transparent #eef; background:transparent; min-height:370px; height:100em;"></iframe>' ,"\n";
	}
	echo '</div>' ,"\n";
	echo '<script type="text/javascript">try {gs_sandbox_iframe("boi-content");} catch(e){}</script>' ,"\n";
}

?>

</div>

</body>
</html>
