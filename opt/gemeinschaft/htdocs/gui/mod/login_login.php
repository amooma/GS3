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


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";




if (@$_REQUEST['login_action'] == 'forgotpwd') {
	$login_user = trim(@$_REQUEST['login_user']);
	if ($login_user == '') {
		$action_info = '';
	} else {
		$db = @gs_db_slave_connect();
		if (!$db) $action_info = 'DB error';
		else {
			$rs = @$db->execute('SELECT `email`, `pin`, `firstname`, `lastname` FROM `users` WHERE `user`=\''. $db->escape($login_user) .'\'');
			if (!$rs) $action_info = 'DB error';
			else {
				$u = $rs->fetchRow();
				if (!$u) $action_info = __('Benutzer nicht vorhanden');
				else {
					//print_r($u);
					$u['email'] = trim($u['email']);
					if ($u['email'] == '')
						$action_info = __('Benutzer hat keine E-Mail-Adresse');
					elseif (! preg_match(GS_EMAIL_PATTERN_VALID, $u['email']))
						$action_info = __('Benutzer hat keine g&uuml;tige E-Mail-Adresse');
					else {
						
						@exec('hostname 2>>/dev/null', $out, $err);
						if ($err == 0) {
							$hostname = trim(implode(' ', $out));
							if (! $hostname) $hostname = '127.0.0.1';
						} else
							$hostname = '127.0.0.1';
						
						$headers =
							'From: "Gemeinschaft" <root>' ."\r\n".
							'Reply-To: "'. 'Nicht antworten' .'" <noreply@noreply.local>' ."\r\n".
							'MIME-Version: 1.0' ."\r\n".
							'Content-Type: text/plain; charset=utf-8';
						$msg = sPrintF(
"Hallo %s

 Sie (oder jemand, der sich als Sie ausgeben wollte) haben in der
Web-Oberfl\xC3\xA4che von Gemeinschaft die Zusendung Ihres Pa\xC3\x9Fwortes
angefordert.

 Ihre PIN-Nummer lautet:  %s

Viele Gr\xC3\xBC\xC3\x9Fe,
 Gemeinschaft

Dies ist eine automatisierte Nachricht. Bitte antworten Sie nicht.
-- 
Gemeinschaft auf \"". $hostname ."\"
",
							(@$u['firstname'] != '' ? $u['firstname'] : '') .
							(@$u['lastname'] != '' ? ' '.$u['lastname'] : ''),
							@$u['pin']
						);
						$ok = @mail( $u['email'], 'Gemeinschaft Passwort', $msg, $headers );
						
						$action_info = $ok
							? sPrintF(__('Pa&szlig;wort wurde an &quot;%s&quot; gesendet'), htmlSpecialChars($u['email']))
							: sPrintF(__('Fehler beim Senden an &quot;%s&quot;'), htmlSpecialChars($u['email']));
					}
				}
			}
		}
	}
?>
<div style="text-align:center; width:auto; margin:1em 100px 0 0;" />
<span style="color:#e00;"><?php echo (@$action_info != '') ? $action_info : '&nbsp;'; ?></span>
<div style="border:1px solid #ddd; text-align:left; width:200px; margin:0 auto; background:#eee; padding:20px 25px;" />
<form method="get" action="<?php echo GS_URL_PATH; ?>">
<input type="hidden" name="s" value="home" />
<input type="hidden" name="m" value="home" />
<input type="hidden" name="login_action" value="forgotpwd" />

<label for="ipt-login_user"><?php echo __('Benutzername'); ?>:</label><br />
<input name="login_user" id="ipt-login_user" type="text" size="15" maxlength="20" value="<?php echo @$_REQUEST['login_user']; ?>" style="width:150px; font-size:1.2em;" /><br />

<br />
<div style="text-align:right;">
	<input type="submit" value="<?php echo __('Pa&szlig;wort mailen'); ?>" />
</div>
</form>
</div>
<a href="<?php echo gs_url($SECTION, $MODULE); ?>" style="font-size:0.95em;"><?php echo __('Einloggen'); ?></a>
</div>
<?php
	
} else {
	
?>
<div style="text-align:center; width:auto; margin:1em 100px 0 0;" />
<span style="color:#e00;"><?php echo (@$login_errmsg != '' && trim(@$_REQUEST['login_user']) != '') ? $login_errmsg : '&nbsp;'; ?></span>
<div style="border:1px solid #ddd; text-align:left; width:200px; margin:0 auto; background:#eee; padding:20px 25px;" />
<form method="post" action="<?php echo GS_URL_PATH; ?>">
<input type="hidden" name="s" value="home" />
<input type="hidden" name="m" value="home" />

<label for="ipt-login_user"><?php echo __('Benutzername'); ?>:</label><br />
<input name="login_user" id="ipt-login_user" type="text" size="15" maxlength="20" value="<?php echo @$_REQUEST['login_user']; ?>" style="width:150px; font-size:1.2em;" /><br />

<label for="ipt-login_pwd"><?php echo __('Pa&szlig;wort'); ?>:</label><br />
<input name="login_pwd" id="ipt-login_pwd" type="password" size="15" maxlength="20" value="" style="width:150px; font-size:1.2em;" /><br />

<br />
<div style="text-align:right;">
	<input type="submit" value="<?php echo __('Einloggen'); ?>" />
</div>
</form>
</div>
<a href="<?php echo gs_url($SECTION, $MODULE, null, 'login_action=forgotpwd'); ?>" style="font-size:0.95em;"><?php echo __('Pa&szlig;wort vergessen'); ?></a>
</div>
<?php
	
}


?>
<br />
<br style="clear:right" />
