<?php

die();





?>
<style type="text/css">
body, table, td {
	font-family: 'Lucida Sans', Arial, Helvetica, sans-serif;
	font-size: 12px; line-height: 1.1em;
}
body {background: #fff;}
td {background: #ddd; padding: 2px 0.3em;}
.test_ok     {line-height: 1em; padding: 0 4px; background: #0f0; color: #000;}
.test_notice {line-height: 1em; padding: 0 4px; background: #ff0; color: #000;}
.test_warn   {line-height: 1em; padding: 0 4px; background: #f32; color: #000;}
</style>
<?php



function htmlEnt( $str )
{
	return htmlSpecialChars($str, ENT_QUOTES, 'UTF-8');
}

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
	echo '<span class="test_',$status,'">', htmlEnt($str) ,'</span>';
}

?>


<table cellspacing="1" border="0">
<thead>
<tr>
	<th width="140"></th>
	<th width="300"></th>
	<th width="130"></th>
</tr>
</thead>
<tbody>

<tr>
	<td><?php echo 'Betriebssystem:'; ?></td>
	<td><?php echo htmlEnt(PHP_OS); ?></td>
	<td><?php
		switch (PHP_OS) {
			case 'Linux':
				echo _test_result('OK'                     ,'ok'  );
				break;
			default     :
				echo _test_result('NICHT UNTERST&Uuml;TZT', 'warn');
				break;
		}
	?></td>
</tr>

<tr>
	<td><?php echo 'PHP-Version:'; ?></td>
	<td><?php echo htmlEnt(PHP_VERSION); ?></td>
	<td><?php
		if     (version_compare(PHP_VERSION, '5.2') >= 0)
			echo _test_result('OK', 'ok');
		elseif (version_compare(PHP_VERSION, '5'  ) >= 0)
			echo _test_result('NICHT UNTERST&Uuml;TZT', 'warn');
		elseif (version_compare(PHP_VERSION, '4.3') >= 0)
			echo _test_result('OK', 'ok');
		else
			echo _test_result('NICHT UNTERST&Uuml;TZT', 'warn');
	?></td>
</tr>

<tr>
	<td><?php echo 'PHP-Module:'; ?></td>
	<td><tt><?php echo 'ftp'; ?></tt></td>
	<td><?php
		echo (extension_loaded('ftp')
			? _test_result('OK', 'ok')
			: _test_result('NICHT GELADEN', 'warn') );
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'ldap'; ?></tt></td>
	<td><?php
		echo (extension_loaded('ldap')
			? _test_result('OK', 'ok')
			: _test_result('NICHT GELADEN', 'warn') );	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'mbstring'; ?></tt></td>
	<td><?php
		echo (extension_loaded('mbstring')
			? _test_result('OK', 'ok')
			: _test_result('NICHT GELADEN', 'warn') );	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'mysql'; ?></tt></td>
	<td><?php
		echo (extension_loaded('mysql')
			? _test_result('OK', 'ok')
			: _test_result('NICHT GELADEN', 'warn') );	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'pcre'; ?></tt></td>
	<td><?php
		echo (extension_loaded('pcre')
			? _test_result('OK', 'ok')
			: _test_result('NICHT GELADEN', 'warn') );	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'posix'; ?></tt></td>
	<td><?php
		echo (extension_loaded('posix')
			? _test_result('OK', 'ok')
			: _test_result('NICHT GELADEN', 'warn') );	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'session'; ?></tt></td>
	<td><?php
		echo (extension_loaded('session')
			? _test_result('OK', 'ok')
			: _test_result('NICHT GELADEN', 'warn') );	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'sockets'; ?></tt></td>
	<td><?php
		echo (extension_loaded('sockets')
			? _test_result('OK', 'ok')
			: _test_result('NICHT GELADEN', 'warn') );	?></td>
</tr>

<tr>
	<td><?php echo 'PHP-Optionen:'; ?></td>
	<td><?php
		$k = 'safe_mode';
		$v = ini_get($k);
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td><?php
		echo php_ini_false($v)
			? _test_result('OK', 'ok')
			: _test_result('FEHLER', 'warn');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'register_globals';
		$v = ini_get($k);
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td><?php
		echo php_ini_false($v)
			? _test_result('OK', 'ok')
			: _test_result('NICHT OPTIMAL', 'notice');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'register_long_arrays';
		$v = ini_get($k);
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td><?php
		echo php_ini_false($v)
			? _test_result('OK', 'ok')
			: _test_result('NICHT OPTIMAL', 'notice');	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'magic_quotes_gpc';
		$v = get_magic_quotes_gpc();
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td><?php
		echo php_ini_false($v)
			? _test_result('OK', 'ok')
			: _test_result('NICHT OPTIMAL', 'notice');	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'magic_quotes_runtime';
		$v = get_magic_quotes_runtime();
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td><?php
		echo php_ini_false($v)
			? _test_result('OK', 'ok')
			: _test_result('NICHT OPTIMAL', 'notice');	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'max_execution_time';
		$v = (int)ini_get('max_execution_time');
		echo '<tt>', $k ,': ', $v ,'</tt>';
	?></td>
	<td><?php
		echo ($v >= 30 || $v == 0)
			? _test_result('OK', 'ok')
			: _test_result('ZU KURZ', 'notice');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		set_time_limit(40);
		$k = 'max_execution_time';
		$v = (int)ini_get('max_execution_time');
		$ok = ($v == 40);
		echo '<tt>', $k ,' setzbar? ', ($ok ? 'ja' : 'nein') ,'</tt>';
	?></td>
	<td><?php
		echo $ok
			? _test_result('OK', 'ok')
			: _test_result('FEHLER', 'notice');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'mbstring.func_overload';
		$v = (int)ini_get('mbstring.func_overload');
		echo '<tt>', $k ,': ', $v ,'</tt>';
	?></td>
	<td><?php
		echo ($v == 0)
			? _test_result('OK', 'ok')
			: _test_result('FEHLER', 'warn');
	?></td>
</tr>

<tr>
	<td><?php echo 'Benutzer:'; ?></td>
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
<tr>
	<td><?php echo 'Benutzerrechte:'; ?></td>
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
	<td><?php
		echo $ok
			? _test_result('OK', 'ok')
			: _test_result('FEHLER', 'warn');
	?></td>
</tr>

<tr>
	<td><?php echo 'Web-Server / SAPI:'; ?></td>
	<td><?php
		$sapi = php_sapi_name();
		switch ($sapi) {
			case 'apache2handler': echo 'Apache 2 + <tt>mod_php</tt>'; break;
			case 'apache2filter' : echo 'Apache 2 + <tt>mod_php</tt>'; break;
			case 'apache'        : echo 'Apache 1 + <tt>mod_php</tt>'; break;
			case 'cgi-fcgi'      : echo 'FCGI'                       ; break;
			case 'cgi'           : echo 'CGI'                        ; break;
			case 'cli'           : echo 'CLI (RedHat?)'              ; break;
			default              : echo $sapi                        ; break;
		}
		?></td>
		<td><?php
		switch ($sapi) {
			case 'apache2handler': echo _test_result('OK'                    , 'ok'    ); break;
			case 'apache2filter' : echo _test_result('OK'                    , 'ok'    ); break;
			case 'apache'        : echo _test_result('OK'                    , 'ok'    ); break;
			case 'cgi-fcgi'      : echo _test_result('OK'                    , 'ok'    ); break;
			case 'cgi'           : echo _test_result('NICHT OPTIMAL'         , 'notice'); break;
			case 'cli'           : echo _test_result('NICHT OPTIMAL'         , 'notice'); break;
			default              : echo _test_result('NICHT UNTERST&Uuml;TZT', 'warn'  ); break;
		}
	?></td>
</tr>

<tr>
	<td><?php echo 'Apache-Module:'; ?></td>
	<td><tt><?php
		$have_apache_get_modules = false;
		if (subStr($sapi,0,6) === 'apache') {
			if (! function_exists('apache_get_modules')) {
				echo 'keine Informationen';
			} else {
				$have_apache_get_modules = true;
				$apache_mods = array_flip(apache_get_modules());
				echo '&nbsp;';
			}
		} else {
			echo 'nicht zutreffend';
		}
	?></tt></td>
	<td>&nbsp;</td>
</tr>
<?php if ($have_apache_get_modules) { ?>
<tr>
	<td>&nbsp;</td>
	<td><tt>mod_mime</tt></td>
	<td><?php
		echo array_key_exists('mod_mime', $apache_mods)
			? _test_result('OK', 'ok')
			: _test_result('NICHT GELADEN', 'warn');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt>mod_rewrite</tt></td>
	<td><?php
		echo array_key_exists('mod_rewrite', $apache_mods)
			? _test_result('OK', 'ok')
			: _test_result('NICHT OPTIMAL', 'notice');
	?></td>
</tr>
<?php } ?>

<tr>
	<td>HTTP Keep-Alive:</td>
	<td><?php
		$keepalive = -1;
		if (array_key_exists('HTTP_KEEP_ALIVE', $_SERVER)) {
			if ((int)$_SERVER['HTTP_KEEP_ALIVE'] > 1)
				$keepalive = (int)$_SERVER['HTTP_KEEP_ALIVE'];
		} elseif (array_key_exists('HTTP_CONNECTION', $_SERVER)) {
			if (strToLower($_SERVER['HTTP_CONNECTION']) == 'keep-alive')
				$keepalive = 1;
			else
				$keepalive = 0;
		}
		if     ($keepalive > 0) echo $keepalive;
		elseif ($keepalive < 0) echo '?';
		else                    echo '-';
	?></td>
	<td><?php
		if     ($keepalive >= 60) echo _test_result('OK', 'ok');
		elseif ($keepalive >   0) echo _test_result('ZU KURZ', 'warning');
		elseif ($keepalive ==  0) echo _test_result('AUS', 'warning');
		else                      echo _test_result('?', 'notice');
	?></td>
</tr>

</tbody>
</table>


