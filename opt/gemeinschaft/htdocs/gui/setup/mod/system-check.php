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
defined('GS_VALID') or die('No direct access.');
require_once( GS_DIR .'inc/find_executable.php' );
$can_continue = true;


?>

<div style="width:550px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">
<h1><?php echo __('Willkommen!'); ?></h1>
<p>
<?php
	switch ($GS_INSTALLATION_TYPE) {
		case 'gpbx':
			echo __('Die folgenden Schritte leiten Sie durch die grundlegende Netzwerk-Konfiguration der <b>GPBX</b>.') ,"\n";
			break;
		default:
			echo __('Die folgenden Schritte leiten Sie durch die grundlegende Netzwerk-Konfiguration der Telefonanlage <b>Gemeinschaft</b>.') ,"\n";
			break;
	}
	echo __('Sie k&ouml;nnen sp&auml;ter jederzeit zum Setup zur&uuml;ckkehren um Einstellungen zu ver&auml;ndern.') ,"\n";
?>
</p>
<p>
<?php
	echo __('Installationsart') ,': <b>';
	switch ($GS_INSTALLATION_TYPE) {
		case 'gpbx'  :  echo    'GPBX'          ; break;
		case 'single':  echo __('Einzel-Server'); break;
		default      :  echo    'unbekannt'     ; break;
	}
	echo '</b>, ';
	echo __('Sprache') ,': <b>', __('Deutsch') ,'</b><br />' ,"\n";
?>
</p>
<hr />

<?php


function php_ini_true( $val )
{
	return ($val == '1' || strToLower($val) == 'on');
}
function php_ini_false( $val )
{
	return ($val == '0' || strToLower($val) == 'off' || $val == '');
}
function php_ini_bool_verbose( $val )
{
	return ($val == '1' || strToLower($val) == 'on') ? 'On' : 'Off';
}
function _test_result( $str, $status )
{
	global $can_continue;
	echo 'class="test_res test_',$status,'">', $str;
	if (in_array($status, array('warn'), true))
		$can_continue = false;
}


?>

<table cellspacing="1" border="0">
<tbody>

<tr>
	<td width="120"><?php echo __('Betriebssystem'); ?>:</td>
	<td width="240"><?php echo htmlEnt(PHP_OS); ?></td>
	<td width="110" <?php
		switch (PHP_OS) {
			case 'Linux':
				echo _test_result(__('OK'                    ), 'ok'  );
				break;
			default     :
				echo _test_result(__('NICHT UNTERST&Uuml;TZT'), 'warn');
				break;
		}
	?></td>
</tr>

<tr>
	<td><?php echo __('PHP-Version'); ?>:</td>
	<td><tt><?php echo htmlEnt(PHP_VERSION); ?></tt></td>
	<td <?php
		if     (version_compare(PHP_VERSION, '5.2') >= 0)
			echo _test_result(__('OK'), 'ok');
		elseif (version_compare(PHP_VERSION, '5'  ) >= 0)
			echo _test_result(__('NICHT UNTERST&Uuml;TZT'), 'warn');
		elseif (version_compare(PHP_VERSION, '4.3') >= 0)
			echo _test_result(__('OK'), 'ok');
		else
			echo _test_result(__('NICHT UNTERST&Uuml;TZT'), 'warn');
	?></td>
</tr>

<tr>
	<td><?php echo __('PHP-Module'); ?>:</td>
	<td><tt><?php echo 'ftp'; ?></tt></td>
	<td <?php
		echo (extension_loaded('ftp')
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn') );
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'ldap'; ?></tt></td>
	<td <?php
		echo (extension_loaded('ldap')
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn') );
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'mbstring'; ?></tt></td>
	<td <?php
		echo (extension_loaded('mbstring')
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn') );
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'mysql'; ?></tt></td>
	<td <?php
		echo (extension_loaded('mysql')
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn') );
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'pcre'; ?></tt></td>
	<td <?php
		echo (extension_loaded('pcre')
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn') );
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'posix'; ?></tt></td>
	<td <?php
		echo (extension_loaded('posix')
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn') );
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'session'; ?></tt></td>
	<td <?php
		echo (extension_loaded('session')
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn') );
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'sockets'; ?></tt></td>
	<td <?php
		echo (extension_loaded('sockets')
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn') );
	?></td>
</tr>

<tr>
	<td><?php echo __('PHP-Optionen'); ?>:</td>
	<td><?php
		$k = 'safe_mode';
		$v = ini_get($k);
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td <?php
		echo php_ini_false($v)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('FEHLER'), 'warn');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'register_globals';
		$v = ini_get($k);
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td <?php
		echo php_ini_false($v)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT OPTIMAL'), 'notice');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'register_long_arrays';
		$v = ini_get($k);
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td <?php
		echo php_ini_false($v)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT OPTIMAL'), 'notice');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'magic_quotes_gpc';
		$v = get_magic_quotes_gpc();
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td <?php
		echo php_ini_false($v)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT OPTIMAL'), 'notice');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'magic_quotes_runtime';
		$v = get_magic_quotes_runtime();
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td <?php
		echo php_ini_false($v)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT OPTIMAL'), 'notice');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'max_execution_time';
		$v = (int)ini_get('max_execution_time');
		echo '<tt>', $k ,': ', $v ,'</tt>';
	?></td>
	<td <?php
		echo ($v >= 30 || $v == 0)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('ZU KURZ'), 'notice');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		set_time_limit(40);
		$k = 'max_execution_time';
		$v = (int)ini_get('max_execution_time');
		$ok = ($v == 40);
		echo '<tt>', $k ,' ', __('setzbar?') ,' ', ($ok ? __('ja') : __('nein')) ,'</tt>';
	?></td>
	<td <?php
		echo $ok
			? _test_result(__('OK'), 'ok')
			: _test_result(__('FEHLER'), 'notice');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'mbstring.func_overload';
		$v = (int)ini_get('mbstring.func_overload');
		echo '<tt>', $k ,': ', $v ,'</tt>';
	?></td>
	<td <?php
		echo ($v == 0)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('FEHLER'), 'warn');
	?></td>
</tr>

<tr>
	<td><?php echo 'SAPI'; ?>:</td>
	<td><?php
		$sapi = php_sapi_name();
		switch ($sapi) {
			case 'apache2handler': echo 'Apache 2 + <tt>mod_php</tt>'; break;
			case 'apache2filter' : echo 'Apache 2 + <tt>mod_php</tt>'; break;
			case 'apache'        : echo 'Apache 1 + <tt>mod_php</tt>'; break;
			case 'cgi-fcgi'      : echo 'FCGI'                       ; break;
			case 'cgi'           : echo 'CGI'                        ; break;
			case 'cli'           : echo 'CLI'                        ; break;
			default              : echo $sapi                        ; break;
		}
		?></td>
		<td <?php
		switch ($sapi) {
			case 'apache2handler': echo _test_result(__('OK'                    ), 'ok'    ); break;
			case 'apache2filter' : echo _test_result(__('OK'                    ), 'ok'    ); break;
			case 'apache'        : echo _test_result(__('OK'                    ), 'ok'    ); break;
			case 'cgi-fcgi'      : echo _test_result(__('OK'                    ), 'ok'    ); break;
			case 'cgi'           : echo _test_result(__('NICHT OPTIMAL'         ), 'notice'); break;
			case 'cli'           : echo _test_result(__('NICHT OPTIMAL'         ), 'notice'); break;
			default              : echo _test_result(__('NICHT UNTERST&Uuml;TZT'), 'warn'  ); break;
		}
	?></td>
</tr>

<tr>
	<td><?php echo __('Apache-Module'); ?>:</td>
	<td><?php
		$have_apache_get_modules = false;
		if (subStr($sapi,0,6) !== 'apache') {
			echo '&nbsp;';
		} else {
			if (! function_exists('apache_get_modules')) {
				echo '?';
			} else {
				$have_apache_get_modules = true;
				$apache_mods = array_flip(apache_get_modules());
				echo '&nbsp;';
			}
		}
	?></td>
	<td <?php
		echo $have_apache_get_modules
			? _test_result('&nbsp;', '')
			: _test_result(__('FEHLER'), 'notice');
	?></td>
</tr>
<?php if ($have_apache_get_modules) { ?>
<tr>
	<td>&nbsp;</td>
	<td><tt>mod_env</tt></td>
	<td <?php
		echo array_key_exists('mod_env', $apache_mods)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt>mod_mime</tt></td>
	<td <?php
		echo array_key_exists('mod_mime', $apache_mods)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt>mod_rewrite</tt></td>
	<td <?php
		echo array_key_exists('mod_rewrite', $apache_mods)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT GELADEN'), 'warn');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt>mod_alias</tt></td>
	<td <?php
		echo array_key_exists('mod_alias', $apache_mods)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT OPTIMAL'), 'notice');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt>mod_expires</tt></td>
	<td <?php
		echo array_key_exists('mod_expires', $apache_mods)
			? _test_result(__('OK'), 'ok')
			: _test_result(__('NICHT OPTIMAL'), 'notice');
	?></td>
</tr>
<?php } ?>

<tr>
	<td><?php echo __('Benutzer'); ?>:</td>
	<td><?php
		$euser = posix_getpwuid(posix_geteuid());
		if (is_array($euser))
			echo '<tt>', @$euser['name'], '</tt>';
		else
			echo '?';
		echo ' / ';
		$egroup = posix_getpwuid(posix_getegid());
		if (is_array($egroup))
			echo '<tt>', @$egroup['name'] ,'</tt>';
		else
			echo '?';
	?></td>
	<td>&nbsp;</td>
</tr>
<?php /* ?>
<tr>
	<td><?php echo 'Benutzerrechte'; ?>:</td>
	<td><?php
		$ok = false;
		$group_gemeinschaft = 'root';
		$grp_gemeinschaft = posix_getgrnam($group_gemeinschaft);
		if (! is_array($grp_gemeinschaft)) {
			echo 'Gruppe <tt>gemeinschaft</tt> nicht vorhanden.';
		} else {
			$groups = posix_getgroups();
			if (! is_array($groups)) {
				echo '-';
			} else {
				foreach ($groups as $gid) {
					$group = posix_getgrgid($gid);
					if (is_array($group) && $group['name']===$group_gemeinschaft) {
						echo 'Gruppe <tt>gemeinschaft</tt>';
						$ok = true;
						break;
					}
				}
				echo 'Nicht Mitglied der Gruppe <tt>gemeinschaft</tt>';
			}
		}
	?></td>
	<td <?php
		echo $ok
			? _test_result('OK', 'ok')
			: _test_result('FEHLER', 'warn');
	?></td>
</tr>
<?php */ ?>
<tr>
	<td><?php echo __('Ausf&uuml;hrungsrechte'); ?>:</td>
	<td>&nbsp;</td>
	<td <?php
		$user_info = @posix_getpwuid(@posix_geteuid());
		$username = @$user_info['name'];
		unset($user_info);
		
		$expect_basename = 'expect';
		$expect = find_executable($expect_basename, array(
			'/usr/bin/',
			'/usr/sbin/',
			'/usr/local/bin/',
			'/usr/local/sbin/'
		));
		if (! $expect) {
			_test_result( sPrintF('FEHLER (&quot;%s&quot; nicht gefunden)', $expect_basename), 'warn' );
		} else {
			$gs_sbin_dir = '/opt/gemeinschaft/sbin/';
			$sudo_check      = $gs_sbin_dir .'sudo-check';
			$sudo_sudo_check = $gs_sbin_dir .'sudo-sudo-check';
			if (! file_exists($sudo_check)
			||  ! file_exists($sudo_sudo_check)) {
				_test_result( 'FEHLER (Test-Skript nicht gefunden)', 'warn' );
			} else {
				$err=0; $out=array();
				@exec( $sudo_check .' 1>>/dev/null 2>>/dev/null', $out, $err );
				if ($err !== 0) {
					_test_result( sPrintF('FEHLER (User &quot;%s&quot; hat nicht gen&uuml;gend Rechte)', $username), 'warn' );
				} else {
					$err=0; $out=array();
					@exec( $sudo_sudo_check .' 1>>/dev/null 2>>/dev/null', $out, $err );
					if ($err !== 0) {
						_test_result( sPrintF('FEHLER (User &quot;%s&quot; hat nicht gen&uuml;gend Rechte)', 'root'), 'warn' );
					} else {
						_test_result( 'OK', 'ok' );;
						//$can_continue = true;
					}
				}
			}
		}
	?></td>
</tr>

</tbody>
</table>

<br />
<hr />
<?php


/*
    check php-cli as well !?
*/



echo '<div class="fl"><a href="', GS_URL_PATH ,'setup/?step=user">', __('zur&uuml;ck') ,'</a></div>' ,"\n";
echo '<div class="fr">';
if ($can_continue)
	echo '<a href="', GS_URL_PATH ,'setup/?step=network"><big>', __('weiter') ,'</big></a>';
else
	echo '<span style="color:#999;">', __('weiter') ,'</span>';
echo '</div>' ,"\n";
echo '<br class="nofloat" />' ,"\n";

?>
</div>