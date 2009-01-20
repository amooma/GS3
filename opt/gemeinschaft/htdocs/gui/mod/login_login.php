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



if (gs_get_conf('GS_INSTALLATION_TYPE') === 'gpbx') {
	if (trim(@gs_file_get_contents('/mnt/userdata/upgrades/upgrade-avail')) === 'yes') {
		echo '<p align="center" style="font-size:0.94em; margin:0 auto 0.5em auto; padding:0.5em; border:1px solid #fd9; background:#ffc; color:#444; line-height:1em;">';
		echo __('Ein Software-Upgrade ist verf&uuml;gbar. F&uuml;r mehr Informationen bitte mit dem Administrator-Account anmelden.');
		echo '</p>' ,"\n";
	}
}




echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";




if (@$_REQUEST['login_action'] === 'forgotpwd') {
	
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
						if ($err === 0) {
							$hostname = trim(implode(' ', $out));
							if (! $hostname) $hostname = 'localhost';
						} else {
							$hostname = 'localhost';
						}
						
						$msg = sPrintF(
"Hallo %s

Sie (oder jemand, der sich als Sie ausgeben wollte) haben in der
Web-Oberfl\xC3\xA4che von Gemeinschaft die Zusendung Ihres Pa\xC3\x9Fwortes
angefordert.

Ihre PIN-Nummer lautet:  %s

Viele Gr\xC3\xBC\xC3\x9Fe,
 Gemeinschaft
-- 
Dies ist eine automatisierte Nachricht. Bitte antworten Sie nicht.
Gemeinschaft auf \"%s\"
",
							(@$u['firstname'] != '' ? $u['firstname'] : '') .
							(@$u['lastname'] != '' ? ' '.$u['lastname'] : ''),
							@$u['pin'],
							$hostname
						);
						
						$GS_EMAIL_DELIVERY = gs_get_conf('GS_EMAIL_DELIVERY');
						
						if ($GS_EMAIL_DELIVERY === 'sendmail') {
							
							$headers =
								'From: "Gemeinschaft" <root>' ."\r\n".
								'Reply-To: "'. 'Nicht antworten' .'" <noreply@noreply.local>' ."\r\n".
								'MIME-Version: 1.0' ."\r\n".
								'Content-Type: text/plain; charset=utf-8';
							
							$ok = @mail( $u['email'], 'Gemeinschaft Passwort', $msg, $headers );
							if ($ok) {
								gs_log( GS_LOG_NOTICE, 'Sent PIN number for user '. $login_user .' to "'. $u['email'] .'" via '. $GS_EMAIL_DELIVERY );
							} else {
								gs_log( GS_LOG_WARNING, 'Failed to send PIN number for user '. $login_user .' to "'. $u['email'] .'" via '. $GS_EMAIL_DELIVERY );
							}
							
							$action_info = $ok
								? sPrintF(__('Pa&szlig;wort wurde an <tt>%s</tt> gesendet'), htmlEnt($u['email']))
								: sPrintF(__('Fehler beim Senden an <tt>%s</tt>'), htmlEnt($u['email']));
							
						}
						elseif ($GS_EMAIL_DELIVERY === 'direct-smtp') {
							
							require_once( GS_DIR .'lib/phpmailer/class.phpmailer.php' );
							$mail = new PHPMailer();
							$mail->CharSet = 'utf-8';
							$mail->SetLanguage(
								strToLower(subStr( gs_get_conf('GS_INTL_LANG') ,0,2)),
								GS_DIR .'lib/phpmailer/language/' );
							$mail->Hostname = $hostname;
							$mail->From = 'root@'.$hostname;
							$mail->FromName = 'Gemeinschaft';
							$mail->AddReplyTo( '', 'Nicht antworten' );
							$mail->AddAddress(
								$u['email'],
								(@$u['firstname'] != '' ? $u['firstname'] : '') .
								(@$u['lastname'] != '' ? ' '.$u['lastname'] : '')
								);
							$mail->Subject = "Gemeinschaft Pa\xC3\x9Fwort";
							$mail->Body = $msg;
							$mail->IsSMTP();
							//$mail->SMTPDebug = true;
							$mail->Timeout = 10;
							if (! preg_match('/@([^@]+)/', $u['email'], $m)) {
								$ok = false;
								$action_info = sPrintF(__('Fehler beim Senden an <tt>%s</tt>'), htmlEnt($u['email'])) .'<br />(no host)';
							} else {
								$ok = false;
								$host = $m[1];
								$mx_hosts   = array();
								$mx_weights = array();
								getMXRR($host, $mx_hosts, $mx_weights);
								$mxs = array();
								if (@count($mx_hosts) > 0) {
									for($i=0; $i<count($mx_hosts); ++$i) {
										$mxs[$mx_hosts[$i]] = @$mx_weights[$i];
									}
									aSort($mxs, SORT_NUMERIC);
								} else {
									$mxs[$host] = 0;  # RFC 2821
								}
								//echo "<pre>\n"; print_r($mxs); echo "</pre>\n";
								
								while (list($mx_host, $mx_weight) = each($mxs)) {
									//echo "<b>$mx_host</b><br />\n";
									@set_time_limit(30);
									$mail->Host = $mx_host;
									//echo "<pre>\n";
									$ok = @$mail->Send();
									//echo "</pre>\n";
									if ($ok) break;
								}
								if ($ok) {
									gs_log( GS_LOG_NOTICE, 'Sent PIN number for user '. $login_user .' to "'. $u['email'] .'" via '. $GS_EMAIL_DELIVERY );
								} else {
									gs_log( GS_LOG_WARNING, 'Failed to send PIN number for user '. $login_user .' to "'. $u['email'] .'" via '. $GS_EMAIL_DELIVERY );
								}
								
								$action_info = $ok
									? sPrintF(__('Pa&szlig;wort wurde an <tt>%s</tt> gesendet'), htmlEnt($u['email']))
									: sPrintF(__('Fehler beim Senden an <tt>%s</tt>'), htmlEnt($u['email'])) .'<br />('. $mail->ErrorInfo .')';
							}
							
						}
						else {
							
							$action_info = 'Unknown <tt>EMAIL_DELIVERY</tt> &quot;<tt>'. htmlEnt($GS_EMAIL_DELIVERY) .'</tt>&quot;!';
							
						}
						
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
<script type="text/javascript">/*<![CDATA[*/ try{ document.getElementById('ipt-login_user').focus(); }catch(e){} /*]]>*/</script>

<br />
<div style="text-align:right;">
	<input type="submit" value="<?php echo __('Pa&szlig;wort mailen'); ?>" />
</div>
</form>
</div>
<a href="<?php echo gs_url($SECTION, $MODULE); ?>" style="font-size:0.95em;"><?php echo __('Einloggen'); ?></a>
</div>
<?php
	
}
else {
	
	if (gs_get_conf('GS_INSTALLATION_TYPE') !== 'gpbx'
	&& trim(gs_get_conf('GS_GUI_SUDO_ADMINS')) == '') {
?>
<div class="noticebox">
	<big><b><?php echo htmlEnt(__('Willkommen!')); ?></b></big><br />
	<?php echo htmlEnt(__('Vermutlich haben Sie Gemeinschaft gerade frisch installiert.')); ?><br />
	<?php echo htmlEnt(__('Bitte legen Sie auf der Kommandozeile mit folgenden Befehlen einen Benutzer an:')); ?><br />
	<code class="nobr"><?php echo htmlEnt('cd /opt/gemeinschaft/scripts/'); ?></code><br />
	<code class="nobr"><?php echo htmlEnt('./gs-user-add'); ?></code> (gibt ohne Parameter eine Hilfe aus)<br />
	<code class="nobr"><?php echo htmlEnt('./gs-user-add --user lisa ...'); ?></code><br />
	<?php echo sPrintF(htmlEnt(__('und geben Sie diesem Benutzer dann Admin-Rechte indem Sie in der Datei %s folgende Einstellung vornehmen:')),
		'<code class="nobr">'.htmlEnt('/etc/gemeinschaft/gemeinschaft.php').'</code>'); ?><br />
	<code class="nobr"><?php echo htmlEnt('$GUI_SUDO_ADMINS = \'lisa\';'); ?></code>
</div>
<?php
	}
	
?>
<div style="text-align:center; width:auto; margin:1em 100px 0 0;">
<span style="color:#e00;"><?php echo (@$login_errmsg != '' && trim(@$_REQUEST['login_user']) != '') ? $login_errmsg : '&nbsp;'; ?></span>
<div style="border:1px solid #ddd; text-align:left; width:200px; margin:0 auto; background:#eee; padding:20px 25px;">
<?php
if (isSet( $_REQUEST['s'] )
&& ! in_array($_REQUEST['s'], array('','login','logout'), true)) {
	$requested_section = @$_REQUEST['s'];
	$requested_module  = @$_REQUEST['m'];
} else {
	$requested_section = 'home';
	$requested_module  = '';
}
//echo " $requested_section $requested_module ";
?>
<form method="post" action="<?php echo GS_URL_PATH ,'?s=',urlEncode($requested_section);
if ($requested_module) echo '&amp;m=',urlEncode($requested_module);
foreach ($_GET as $k => $v) {
	if (! in_array($k, array('s','m','login_pwd'), true)) {
		echo '&amp;', urlEncode($k) ,'=', urlEncode($v);
	}
}
?>">
<input type="hidden" name="s" value="<?php echo htmlEnt($requested_section); ?>" />
<input type="hidden" name="m" value="<?php echo htmlEnt($requested_module); ?>" />

<label for="ipt-login_user"><?php echo __('Benutzername'); ?>:</label><br />
<input name="login_user" id="ipt-login_user" type="text" size="15" maxlength="20" value="<?php echo @$_REQUEST['login_user']; ?>" style="width:150px; font-size:1.2em;" /><br />
<script type="text/javascript">/*<![CDATA[*/ try{ document.getElementById('ipt-login_user').focus(); }catch(e){} /*]]>*/</script>

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
