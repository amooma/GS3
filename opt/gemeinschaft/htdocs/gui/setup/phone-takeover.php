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
require_once( GS_DIR .'htdocs/gui/setup/inc/aux-fns.php' );
require_once( GS_DIR .'inc/keyval.php' );

# set URL path
#
$GS_URL_PATH = dirName(dirName(@$_SERVER['SCRIPT_NAME']));
if (subStr($GS_URL_PATH,-1,1) != '/') $GS_URL_PATH .= '/';
define( 'GS_URL_PATH', $GS_URL_PATH );
unset($GS_URL_PATH);


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
if (! @$_SESSION['login_ok']) {
	echo 'Not logged in.';
	exit(1);
}


if (! function_exists('qsa')) {
function qsa( $str )
{
	return ($str != '') ? escapeShellArg($str) : '\'\'';
}
}

function _snom_setting( $ip, $https, $user, $pass, $key, $val )
{
	/*
	$ret = array( 'err'=>0, 'http_code'=>0 );
	$out = array();
	$cmd = 'sudo wget -q -t 1 -T 2 --no-proxy --save-headers'. ($user=='' ? '' : ' --user='.qsa($user)) . ($pass=='' ? '' : ' --password='.qsa($pass)) .' --no-check-certificate --no-http-keep-alive -O - '. qsa( ($https?'https':'http') .'://'. $ip.'/dummy.htm?settings=save&'. $key.'='.urlEncode($val) ) .' 2>>/dev/null';
	echo "<pre>$cmd</pre>";
	@exec( $cmd, $out, $ret['err'] );
	//if ($ret['err'] === 0) {
		if (preg_match('/^HTTP\/[0-9]\.[0-9] *([1-9][0-9]{2})/S', implode("\n",$out), $m)) {
			$ret['http_code'] = (int)$m[1];
		}
	//}
	return $ret;
	*/
	$ret = array( 'err'=>0, 'http_code'=>0 );
	$out = array();
	$cmd = 'curl -q --silent --retry 0 --insecure --proxy-anyauth --max-redirs 5 --anyauth'. ($user=='' ? '' : ' --user '.qsa($user .':'. $pass)) .' --max-time 4 --write-out '. qsa('### %{http_code} ###') .' '. qsa( ($https?'https':'http') .'://'. $ip.'/dummy.htm?settings=save&'. $key.'='.urlEncode($val) ) .' 2>>/dev/null';
	//echo "<pre>$cmd</pre>";
	@exec( $cmd, $out, $ret['err'] );
	if (preg_match('/### ([1-9][0-9]{2}) ###/S', implode("\n",$out), $m)) {
		$ret['http_code'] = (int)$m[1];
	}
	return $ret;
}

function _snom_reboot( $ip, $https, $user, $pass )
{
	$cmd = 'curl -q --silent --retry 0 --insecure --proxy-anyauth --max-redirs 5 --anyauth'. ($user=='' ? '' : ' --user '.qsa($user .':'. $pass)) .' --max-time 4 --write-out '. qsa('### %{http_code} ###') .' '. qsa( ($https?'https':'http') .'://'. $ip.'/dummy.htm?REBOOT=true' ) .' 2>>/dev/null';
	$err=0; $out=array();
	@exec( $cmd, $out, $err );
}


$ip     = @$_REQUEST['ip'    ];
$type   = @$_REQUEST['type'  ];
$action = @$_REQUEST['action'];


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo @$_SESSION['isolang']; ?>" xml:lang="<?php echo @$_SESSION['isolang']; ?>">
<head>
<title>phone takeover</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/original.css" />
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>setup/setup.css" />
<?php if ($GUI_ADDITIONAL_STYLESHEET = gs_get_conf('GS_GUI_ADDITIONAL_STYLESHEET')) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/<?php echo rawUrlEncode($GUI_ADDITIONAL_STYLESHEET); ?>" />
<?php } ?>
<style type="text/css">
	body, table, td {
		font-family: 'Lucida Sans', Arial, Helvetica, sans-serif;
		font-size: 12px; line-height: 1.1em;
	}
	body {
		background: transparent;
		margin: 0;
		padding: 0;
	}
</style>
</head>
<body>

<?php

if (! preg_match('/([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/', $ip)) {
	exit(1);
}

if ($action == '') {
	echo '<form method="post" action="', baseName($_SERVER['PHP_SELF']) ,'" style="display:inline;">' ,"\n";
	
	echo '<input type="hidden" name="ip"   value="', $ip ,'" />' ,"\n";
	echo '<input type="hidden" name="type" value="', $type ,'" />' ,"\n";
	
	echo '<input type="hidden" name="action" value="takeover" />' ,"\n";
	echo '<input type="submit" value="', __('&Uuml;bernehmen') ,'" />' ,"\n";
	echo '</form>' ,"\n";
}
elseif ($action === 'takeover') {
	switch ($type) {
		
		case 'snom':
			
			$err=0; $out=array();
			@exec( 'sudo nmap -P0 -sT -p T:80,443 -r --max-retries 2 -oG - '. qsa($ip) .' 2>>/dev/null', $out, $err );
			if ($err !== 0) {
				if ($err === 127)
					echo '<code>nmap</code> not available!' ,"\n";
				else
					echo '<code>nmap</code> failed!' ,"\n";
				exit(1);
			}
			$http_open  = false;
			$https_open = false;
			foreach ($out as $line) {
				if ($line==='' || subStr($line,0,1)==='#') continue;
				if (preg_match('/\b80\/open/i', $line)) $http_open = true;
				if (preg_match('/\b443\/open/i', $line)) $https_open = true;
			}
			//echo 'HTTP  is ', ($http_open  ? 'open':'closed') ,"<br/>\n";
			//echo 'HTTPS is ', ($https_open ? 'open':'closed') ,"<br/>\n";
			if (! $https_open && ! $http_open) {
				echo 'HTTP/HTTPS not open!' ,"\n";
				exit(1);
			}
			
			$credentials = array(
				array('u'=>''             , 'p'=>''    ),
				array('u'=>'admin'        , 'p'=>'0000'),
				array('u'=>'admin'        , 'p'=>'1234'),
				array('u'=>'admin'        , 'p'=>'123' ),
				array('u'=>gs_get_conf('GS_SNOM_PROV_HTTP_USER'), 'p'=>gs_get_conf('GS_SNOM_PROV_HTTP_PASS')),
				array('u'=>'Administrator', 'p'=>'0000'),
				array('u'=>'Administrator', 'p'=>'1234'),
				array('u'=>'Administrator', 'p'=>'123' )
			);
			$success = false;
			$reachable = false;
			$all_401_403 = true;
			foreach ($credentials as $creds) {
				$ret = _snom_setting( $ip, $https_open, $creds['u'], $creds['p'], 'dnd_mode', 'off' );
				if ($ret['http_code'] === 200) {
					$success = true;
					$all_401_403 = false;
					$valid_user = $creds['u'];
					$valid_pass = $creds['p'];
					break;
				}
				if ($ret['http_code'] >= 100) {
					$reachable = true;
					if ($ret['http_code'] !== 401 && $ret['http_code'] !== 403) {
						$all_401_403 = false;
					}
				}
			}
			if (! $success) {
				if (! $reachable) {
					echo __('Verbindung fehlgeschlagen.') ,"\n";
				} elseif ($all_401_403) {
					echo __('Nicht autorisiert.') ,"\n";
				} else {
					echo 'Fehler.' ,"\n";
				}
				exit(1);
			}
			
			/*
			$myipaddr = trim(gs_keyval_get('vlan_0_ipaddr'));
			$ret = _snom_setting( $ip, $https_open, $valid_user, $valid_pass, 'setting_server', 'http://'.$myipaddr.':80/gemeinschaft/prov/snom/settings.php?mac={mac}' );
			*/
			$GS_PROV_PORT = (int)gs_get_conf('GS_PROV_PORT');
			$setting_server_url = gs_get_conf('GS_PROV_SCHEME') .'://'. gs_get_conf('GS_PROV_HOST') . ($GS_PROV_PORT ? ':'.$GS_PROV_PORT : '') . gs_get_conf('GS_PROV_PATH') .'snom/settings.php?mac={mac}';
			$ret = _snom_setting( $ip, $https_open, $valid_user, $valid_pass, 'setting_server', $setting_server_url );
			
			if ($ret['http_code'] !== 200) {
				echo 'Fehler!' ,"\n";
				exit(1);
			}
			echo 'OK' ,"\n";
			_snom_reboot( $ip, $https_open, $valid_user, $valid_pass );
			exit(0);
			
			break;
		
		default:
			echo '?';
	}
}


?>

</body>
</html>