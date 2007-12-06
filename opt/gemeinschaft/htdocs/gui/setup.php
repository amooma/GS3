<?php


die();




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

?>


<table cellspacing="1" border="0">
<thead>
<tr>
	<td width="160"></td>
	<td width="300"></td>
	<td width="150"></td>
</tr>
</thead>
<tbody>

<tr>
	<td><?php echo 'Betriebssystem:'; ?></td>
	<td><?php echo htmlEnt(PHP_OS); ?></td>
	<td><?php
		switch (PHP_OS) {
			case 'Linux': echo 'OK'                    ; break;
			default     : echo 'NICHT UNTERST&Uuml;TZT'; break;
		}
	?></td>
</tr>

<tr>
	<td><?php echo 'PHP-Version:'; ?></td>
	<td><?php echo htmlEnt(PHP_VERSION); ?></td>
	<td><?php
		if     (version_compare(PHP_VERSION, '5.2') >= 0)
			echo 'OK';
		elseif (version_compare(PHP_VERSION, '5'  ) >= 0)
			echo 'NICHT UNTERST&Uuml;TZT';
		elseif (version_compare(PHP_VERSION, '4.3') >= 0)
			echo 'OK';
		else
			echo 'NICHT UNTERST&Uuml;TZT';
	?></td>
</tr>

<tr>
	<td><?php echo 'PHP-Module:'; ?></td>
	<td><tt><?php echo 'ftp'; ?></tt></td>
	<td><?php
		echo (extension_loaded('ftp') ? 'OK' : 'NICHT GELADEN');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'ldap'; ?></tt></td>
	<td><?php
		echo (extension_loaded('ldap') ? 'OK' : 'NICHT GELADEN');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'mbstring'; ?></tt></td>
	<td><?php
		echo (extension_loaded('mbstring') ? 'OK' : 'NICHT GELADEN');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'mysql'; ?></tt></td>
	<td><?php
		echo (extension_loaded('mysql') ? 'OK' : 'NICHT GELADEN');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'pcre'; ?></tt></td>
	<td><?php
		echo (extension_loaded('pcre') ? 'OK' : 'NICHT GELADEN');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'posix'; ?></tt></td>
	<td><?php
		echo (extension_loaded('posix') ? 'OK' : 'NICHT GELADEN');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'session'; ?></tt></td>
	<td><?php
		echo (extension_loaded('session') ? 'OK' : 'NICHT GELADEN');
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><tt><?php echo 'sockets'; ?></tt></td>
	<td><?php
		echo (extension_loaded('sockets') ? 'OK' : 'NICHT GELADEN');
	?></td>
</tr>

<tr>
	<td><?php echo 'PHP-Optionen:'; ?></td>
	<td><?php
		$k = 'safe_mode';
		$v = ini_get($k);
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td><?php
		echo php_ini_false($v) ? 'OK' : 'FEHLER';
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
		echo php_ini_false($v) ? 'OK' : 'NICHT OPTIMAL';
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
		echo php_ini_false($v) ? 'OK' : 'NICHT OPTIMAL';
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'magic_quotes_gpc';
		$v = get_magic_quotes_gpc();
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td><?php
		echo php_ini_false($v) ? 'OK' : 'NICHT OPTIMAL';
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'magic_quotes_runtime';
		$v = get_magic_quotes_runtime();
		echo '<tt>', $k ,': ', php_ini_bool_verbose($v) ,'</tt>';
	?></td>
	<td><?php
		echo php_ini_false($v) ? 'OK' : 'NICHT OPTIMAL';
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php
		$k = 'max_execution_time';
		$v = (int)ini_get('max_execution_time');
		echo '<tt>', $k ,': ', $v ,'</tt>';
	?></td>
	<td><?php
		echo ($v >= 30 || $v == 0) ? 'OK' : 'ZU KURZ';
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
		echo $ok ? 'OK' : 'FEHLER';
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
	<td><?php echo ($ok ? 'OK' : 'FEHLER'); ?></td>
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
			case 'apache2handler': echo 'OK'                    ; break;
			case 'apache2filter' : echo 'OK'                    ; break;
			case 'apache'        : echo 'OK'                    ; break;
			case 'cgi-fcgi'      : echo 'OK'                    ; break;
			case 'cgi'           : echo 'NICHT OPTIMAL'         ; break;
			case 'cli'           : echo 'NICHT OPTIMAL'         ; break;
			default              : echo 'NICHT UNTERST&Uuml;TZT'; break;
		}
	?></td>
</tr>

</tbody>
</table>


