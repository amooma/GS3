<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Sebastian Ertz <gemeinschaft@swastel.eisfair.net>
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
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
include_once( GS_DIR .'inc/pcre_check.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );

$default_dialstrs = array(
	'iax'     => 'IAX2/{gateway}/{number:1}',
	);
$gw_type = 'iax';

$action = @$_REQUEST['action'];
if (! in_array($action, array( '', 'edit', 'save', 'del' ), true))
	$action = '';


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo '<script type="text/javascript">
//<![CDATA[
function confirm_delete() {
	return confirm(', utf8_json_quote(__("Wirklich l\xC3\xB6schen?")) ,');
}
//]]>
</script>' ,"\n";



#####################################################################
if ($action === 'save') {
	
	$gwid = (int)@$_REQUEST['gw-id'];
	
	if ($gwid < 1) {
		$DB->execute(
'INSERT INTO `gates` (
	`id`,
	`grp_id`,
	`type`,
	`name`,
	`title`,
	`allow_out`,
	`dialstr`,
	`host`,
	`proxy`,
	`user`,
	`pwd`,
	`register`
) VALUES (
	NULL,
	NULL,
	\'iax\',
	\'gw_tmp_'. rand(100000,999999) .'\',
	\'\',
	0,
	\''. $DB->escape( $default_dialstrs[$gw_type] ) .'\',
	\'\',
	NULL,
	\'\',
	\'\',
	0
)'
		);
		$gwid = (int)$DB->getLastInsertId();
	}
	
	$iax_friend_name = strToLower(@$_REQUEST['gw-title']);
	$iax_friend_name = preg_replace('/[^a-z0-9]/', '', $iax_friend_name);
	$iax_friend_name = subStr('gw_'.$gwid.'_'.$iax_friend_name, 0, 20);
	
	$host  = preg_replace('/[^a-zA-Z0-9\-_.]/', '', @$_REQUEST['gw-host']);
	
	$query =
'UPDATE `gates` SET
	`grp_id` = '. ((int)@$_REQUEST['gw-grp_id'] > 0 ? (int)@$_REQUEST['gw-grp_id'] : 'NULL') .',
	`name` = \''. $DB->escape($iax_friend_name) .'\',
	`title` = \''. $DB->escape(trim(@$_REQUEST['gw-title'])) .'\',
	`allow_out` = '. (@$_REQUEST['gw-allow_out'] ? 1 : 0) .',
	`dialstr` = \''. $DB->escape(trim(@$_REQUEST['gw-dialstr'])) .'\',
	`host` = \''. $DB->escape($host) .'\',
	`proxy` = NULL,
	`user` = \''. $DB->escape(preg_replace('/[^a-zA-Z0-9\-_.@]/', '', @$_REQUEST['gw-user'])) .'\',
	`pwd` = \''. $DB->escape(preg_replace('/[^a-zA-Z0-9\-_.#*]/', '', @$_REQUEST['gw-pwd'])) .'\',
	`register` = '. (@$_REQUEST['gw-register'] ? 1 : 0) .'
WHERE `id`='. (int)$gwid
	;
	//echo "<pre>$query</pre>\n";
	$DB->execute($query);
	
	$v = (int)trim(@$_REQUEST['gw-param-port']);
	if ($v < 1 || $v > 65535) $v = null;
	$DB->execute( 'DELETE FROM `gate_params` WHERE `gate_id`='.$gwid.' AND `param`=\''.$DB->escape('port').'\'' );
	//if ($v !== null) {
		$DB->execute( 'INSERT INTO `gate_params` (`gate_id`, `param`, `value`)'.
			' VALUES ('.$gwid.', \''.$DB->escape('port').'\', \''.$DB->escape($v).'\')' );
	//}
	
	$v = @$_REQUEST['gw-param-jitterbuffer'];
	$DB->execute( 'DELETE FROM `gate_params` WHERE `gate_id`='.$gwid.' AND `param`=\''.$DB->escape('jitterbuffer').'\'' );
	$DB->execute( 'INSERT INTO `gate_params` (`gate_id`, `param`, `value`)'.
		' VALUES ('.$gwid.', \''.$DB->escape('jitterbuffer').'\', \''.$DB->escape($v).'\')' );
	
	$v = @$_REQUEST['gw-param-trunk'];
	$DB->execute( 'DELETE FROM `gate_params` WHERE `gate_id`='.$gwid.' AND `param`=\''.$DB->escape('trunk').'\'' );
	$DB->execute( 'INSERT INTO `gate_params` (`gate_id`, `param`, `value`)'.
		' VALUES ('.$gwid.', \''.$DB->escape('trunk').'\', \''.$DB->escape($v).'\')' );
	
	$v = @$_REQUEST['gw-param-qualify'];
	$DB->execute( 'DELETE FROM `gate_params` WHERE `gate_id`='.$gwid.' AND `param`=\''.$DB->escape('qualify').'\'' );
	$DB->execute( 'INSERT INTO `gate_params` (`gate_id`, `param`, `value`)'.
		' VALUES ('.$gwid.', \''.$DB->escape('qualify').'\', \''.$DB->escape($v).'\')' );
	
	$v = @$_REQUEST['gw-param-encryption'];
	$DB->execute( 'DELETE FROM `gate_params` WHERE `gate_id`='.$gwid.' AND `param`=\''.$DB->escape('encryption').'\'' );
	$DB->execute( 'INSERT INTO `gate_params` (`gate_id`, `param`, `value`)'.
		' VALUES ('.$gwid.', \''.$DB->escape('encryption').'\', \''.$DB->escape($v).'\')' );
	
	$vv = array();
	for ($i=0; $i<=3; ++$i) {
		$v = (int)lTrim(preg_replace('/[^0-9]/', '', @$_REQUEST['gw-param-permit-'.$i]),'0');
		if ($v > 255) $v = 255;
		elseif ($v < 0) $v = 0;
		$vv[] = $v;
	}
	$v = (int)lTrim(preg_replace('/[^0-9]/', '', @$_REQUEST['gw-param-permit-mask']),'0');
	if ($v > 32) $v = 32;
	elseif ($v < 0) $v = 0;
	$v = implode('.',$vv).'/'.$v;
	unset($vv);
	if ('x'.$v === 'x'.'0.0.0.0/0') $v = null;
	$DB->execute( 'DELETE FROM `gate_params` WHERE `gate_id`='.$gwid.' AND `param`=\''.$DB->escape('permit').'\'' );
	if ($v !== null) {
		$DB->execute( 'INSERT INTO `gate_params` (`gate_id`, `param`, `value`)'.
			' VALUES ('.$gwid.', \''.$DB->escape('permit').'\', \''.$DB->escape($v).'\')' );
	}
	
	$v = @$_REQUEST['gw-param-codecs'];
	if (! is_array($v) || count($v) < 1) {
		$v = array('alaw'=>1);
		gs_log( GS_LOG_WARNING, 'You did not allow any codecs for gateway '. $iax_friend_name .'. Allowing G.711a by default.' );
	}
	$v = array_keys($v);
	$DB->execute( 'DELETE FROM `gate_params` WHERE `gate_id`='.$gwid.' AND `param`=\''.$DB->escape('allow').'\'' );
	$DB->execute( 'INSERT INTO `gate_params` (`gate_id`, `param`, `value`)'.
		' VALUES ('.$gwid.', \''.$DB->escape('allow').'\', \''.$DB->escape(implode(',',$v)).'\')' );
	
	$v = @$_REQUEST['gw-param-auth'];
	if (! is_array($v) || count($v) < 1) {
		$v = array('plaintext'=>1);
		gs_log( GS_LOG_WARNING, 'You did not allow any codecs for gateway '. $iax_friend_name .'. Allowing G.711a by default.' );
	}
	$v = array_keys($v);
	$v = array_reverse($v);
	$DB->execute( 'DELETE FROM `gate_params` WHERE `gate_id`='.$gwid.' AND `param`=\''.$DB->escape('auth').'\'' );
	$DB->execute( 'INSERT INTO `gate_params` (`gate_id`, `param`, `value`)'.
		' VALUES ('.$gwid.', \''.$DB->escape('auth').'\', \''.$DB->escape(implode(',',$v)).'\')' );
	
	$cmd = '/opt/gemeinschaft/sbin/start-asterisk 1>>/dev/null 2>>/dev/null';
	@exec( 'sudo sh -c '. qsa($cmd) .' 1>>/dev/null 2>>/dev/null' );
	
	$action = '';
}
#####################################################################



#####################################################################
if ($action === 'del') {
	
	$gwid = (int)@$_REQUEST['gw-id'];
	
	$DB->execute( 'DELETE FROM `gate_params` WHERE `gate_id`='.$gwid );
	$DB->execute( 'DELETE FROM `gates` WHERE `id`='.$gwid );
	
	$action = '';
}
#####################################################################



#####################################################################
if ($action === 'edit') {
	$gwid = (int)@$_REQUEST['gw-id'];
?>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="save" />
<input type="hidden" name="gw-id" value="<?php echo $gwid; ?>" />

<?php
	if ($gwid > 0) {
		# get gateway from DB
		$rs = $DB->execute( 'SELECT `grp_id`, `type`, `name`, `title`, `allow_out`, `host`, `proxy`, `user`, `pwd`, `register`, `dialstr` FROM `gates` WHERE `id`='.$gwid );
		$gw = $rs->fetchRow();
		if (! $gw) {
			echo 'Gateway not found.';
			return;
		}
		if ($gw['type'] !== 'iax') {
			echo 'Not a IAX" gateway.';
			return;
		}
		# get gateway parameters
		$rs = $DB->execute( 'SELECT `param`, `value` FROM `gate_params` WHERE `gate_id`='.$gwid );
		$gw_params = array();
		while ($r = $rs->fetchRow()) $gw_params[$r['param']] = $r['value'];
	}
	else {
		$gw = array(
			'grp_id'     => null,
			'type'       => 'iax',
			'name'       => '',
			'title'      => '',
			'allow_out'  => 1,
			'host'       => '',
			'proxy'      => '',
			'user'       => '',
			'dialstr'    => $default_dialstrs[$gw_type],
			'pwd'        => '',
			'register'  => 1
		);
		$gw_params = array();
	}
	if (! array_key_exists('port'        , $gw_params)) $gw_params['port'        ] = /*''*/ '4569';
	if (! array_key_exists('jitterbuffer', $gw_params)) $gw_params['jitterbuffer'] = 'yes';
	if (! array_key_exists('trunk'       , $gw_params)) $gw_params['trunk'       ] = 'yes';
	if (! array_key_exists('qualify'     , $gw_params)) $gw_params['qualify'     ] = 'yes';
	if (! array_key_exists('encryption'  , $gw_params)) $gw_params['encryption'  ] = 'no';
	if (! array_key_exists('auth'        , $gw_params)) $gw_params['auth'        ] = 'md5';
	if (! array_key_exists('allow'       , $gw_params)) $gw_params['allow'       ] = 'alaw';
	if (! array_key_exists('permit'      , $gw_params)) $gw_params['permit'      ] = '0.0.0.0/0';
?>


<table cellspacing="1">
<tbody>

<?php
	echo '<tr class="m">',"\n";
	echo '<th style="width:14em;">', __('Titel') ,':</th>',"\n";
	echo '<th style="width:28em;"><input type="text" name="gw-title" value="', htmlEnt($gw['title']) ,'" size="30" maxlength="35" style="font-weight:bold; width:97%;" /></th>',"\n";
	echo '<td class="transp xs gray"><code>iax.conf<code>:</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	//echo '<th>', __('Name') ,':</th>',"\n";
	echo '<th class="s gray">', __('Name') ,':</th>',"\n";
	echo '<td class="s gray" style="padding-top:4px;padding-bottom:4px;"><tt>', htmlEnt($gw['name']) ,'</tt></td>',"\n";
	echo '<td class="transp xs gray"><code>[<i>peer-name</i>]</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('Registrar') ,' / ', __('Server') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-host" value="', htmlEnt($gw['host']) ,'" size="30" maxlength="50" style="width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>host</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('Benutzername') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-user" value="', htmlEnt($gw['user']) ,'" size="25" maxlength="35" style="width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>username</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('Pa&szlig;wort') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-pwd" value="', htmlEnt($gw['pwd']) ,'" size="25" maxlength="35" style="width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>secret</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('Authentifizierung') ,' <sup>[1]</sup>:</th>',"\n";
	echo '<td>',"\n";
	$auth = array(
		'plaintext'   => 'Plaintext',
		'md5'   => 'MD5',
		//'rsa'    => 'RSA'
	);
	$gw_params_auth = preg_split('/\s*,\s*/', trim(@$gw_params['auth']));
	foreach ($auth as $k => $v) {
		echo '<input type="checkbox" name="gw-param-auth[', htmlEnt($k) ,']" id="ipt-gw-param-auth-', htmlEnt($k) ,'" value="1"';
		if (in_array($k, $gw_params_auth, true)) echo ' checked="checked"';
		echo ' />',"\n";
		echo '<label for="ipt-gw-auth-auth-', htmlEnt($k) ,'">', htmlEnt($v) ,'</label>',"\n";
	}
	echo ' &nbsp; <small>(', htmlEnt(__('Standard')) ,': ', htmlEnt('MD5') ,')</small>',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>auth</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>&nbsp;</th>',"\n";
	echo '<td>';
	echo '<input type="checkbox" name="gw-allow_out" id="ipt-gw-allow_out" value="1" ', ($gw['allow_out'] ? 'checked="checked" ' : '') ,'/> <label for="ipt-gw-allow_out">', __('ausgehende Anrufe zulassen') ,'</label>',"\n";
	echo '&nbsp;&nbsp;',"\n";;
	echo '<input type="checkbox" name="gw-register" id="ipt-gw-register" value="1" ', ($gw['register'] ? 'checked="checked" ' : '') ,'/> <label for="ipt-gw-register">', __('registrieren') ,'</label>',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray">..., <code>register</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('W&auml;hlbefehl') ,' <sup>[2]</sup>:</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-dialstr" value="', htmlEnt($gw['dialstr']) ,'" size="25" maxlength="50" style="font-family:monospace; width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray">&#x223C; <code>Dial(IAX2/...)</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('Gruppe') ,' <sup>[3]</sup>:</th>',"\n";
	echo '<td>';
	echo '<select name="gw-grp_id" style="width:97%;">',"\n";
	echo '<option value=""', ($gw['grp_id'] < 1 ? ' selected="selected"' : '') ,'>-- ', __('nicht zugeordnet') ,' --</option>' ,"\n";
	echo '<option value="" disabled="disabled">', '' ,'</option>' ,"\n";
	$rs = $DB->execute(
'SELECT `id`, `title`, `name`
FROM `gate_grps`
ORDER BY `title`'
	);
	$gg_context = '';
	while ($gg = $rs->fetchRow()) {
		echo '<option value="', $gg['id'] ,'"';
		if ($gg['id'] === $gw['grp_id']) {
			echo ' selected="selected"';
			$gg_context = 'from-gg-'.$gg['name'];
		}
		echo '>', htmlEnt($gg['title']) ,'</option>' ,"\n";
	}
	echo '</select>',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>context = ', ($gg_context != '' ? htmlEnt($gg_context) : 'from-gg-<i>...</i>') ,'</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('Port') ,' <sup>[4]</sup>:</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-param-port" value="', htmlEnt(@$gw_params['port']) ,'" class="r" size="5" maxlength="5" />',"\n";
	echo ' &nbsp; <small>(', htmlEnt(__('Standard')) ,': ', htmlEnt('4569') ,')</small>',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>port</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('Jitterbuffer') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="radio" name="gw-param-jitterbuffer" id="ipt-gw-param-jitterbuffer-yes" value="yes"';
	if (@$gw_params['jitterbuffer'] === 'yes') echo ' checked="checked"';
	echo ' />',"\n";
	echo '<label for="ipt-gw-param-jitterbuffer-yes">', htmlEnt(__('ja')) ,'</label>',"\n";
	echo '<input type="radio" name="gw-param-jitterbuffer" id="ipt-gw-param-jitterbuffer-no" value="no"';
	if (@$gw_params['jitterbuffer'] === 'no') echo ' checked="checked"';
	echo ' />',"\n";
	echo '<label for="ipt-gw-param-jitterbuffer-no">', htmlEnt(__('nein')) ,'</label>',"\n";
	echo ' &nbsp; <small>(', htmlEnt(__('Standard')) ,': ', htmlEnt(__('ja')) ,')</small>',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>jitterbuffer = </code><code>yes</code> | <code>no</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('Trunk') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="radio" name="gw-param-trunk" id="ipt-gw-param-trunk-yes" value="yes"';
	if (@$gw_params['trunk'] === 'yes') echo ' checked="checked"';
	echo ' />',"\n";
	echo '<label for="ipt-gw-param-trunk-yes">', htmlEnt(__('ja')) ,'</label>',"\n";
	echo '<input type="radio" name="gw-param-trunk" id="ipt-gw-param-trunk-no" value="no"';
	if (@$gw_params['trunk'] === 'no') echo ' checked="checked"';
	echo ' />',"\n";
	echo '<label for="ipt-gw-param-trunk-no">', htmlEnt(__('nein')) ,'</label>',"\n";
	echo ' &nbsp; <small>(', htmlEnt(__('Standard')) ,': ', htmlEnt(__('ja')) ,')</small>',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>trunk = </code><code>yes</code> | <code>no</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __("Verf\xC3\xBCgbarkeit pr\xC3\xBCfen") ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="radio" name="gw-param-qualify" id="ipt-gw-param-qualify-yes" value="yes"';
	if (@$gw_params['qualify'] === 'yes') echo ' checked="checked"';
	echo ' />',"\n";
	echo '<label for="ipt-gw-param-qualify-yes">', htmlEnt(__('ja')) ,'</label>',"\n";
	echo '<input type="radio" name="gw-param-qualify" id="ipt-gw-param-qualify-no" value="no"';
	if (@$gw_params['qualify'] === 'no') echo ' checked="checked"';
	echo ' />',"\n";
	echo '<label for="ipt-gw-param-qualify-no">', htmlEnt(__('nein')) ,'</label>',"\n";
	echo ' &nbsp; <small>(', htmlEnt(__('Standard')) ,': ', htmlEnt(__('ja')) ,')</small>',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>qualify = </code><code>yes</code> | <code>no</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __("Verschl\xC3\xBCsselung") ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="radio" name="gw-param-encryption" id="ipt-gw-param-encryption-yes" value="yes"';
	if (@$gw_params['encryption'] === 'yes') echo ' checked="checked"';
	echo ' />',"\n";
	echo '<label for="ipt-gw-param-encryption-yes">', htmlEnt(__('ja')) ,'</label>',"\n";
	echo '<input type="radio" name="gw-param-encryption" id="ipt-gw-param-encryption-no" value="no"';
	if (@$gw_params['encryption'] === 'no') echo ' checked="checked"';
	echo ' />',"\n";
	echo '<label for="ipt-gw-param-encryption-no">', htmlEnt(__('nein')) ,'</label>',"\n";
	echo ' &nbsp; <small>(', htmlEnt(__('Standard')) ,': ', htmlEnt(__('nein')) ,')</small>',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>encryption = </code><code>yes</code> | <code>no</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('Codecs') ,':</th>',"\n";
	echo '<td>',"\n";
	$codecs = array(
		'g722'   => 'G.722',
		'alaw'   => 'G.711a',
		'ulaw'   => 'G.711u',
		'gsm'    => 'GSM'
	);
	$gw_params_codecs = preg_split('/\s*,\s*/', trim(@$gw_params['allow']));
	foreach ($codecs as $k => $v) {
		echo '<input type="checkbox" name="gw-param-codecs[', htmlEnt($k) ,']" id="ipt-gw-param-codecs-', htmlEnt($k) ,'" value="1"';
		if (in_array($k, $gw_params_codecs, true)) echo ' checked="checked"';
		echo ' />',"\n";
		echo '<label for="ipt-gw-param-codecs-', htmlEnt($k) ,'">', htmlEnt($v) ,'</label><BR>',"\n";
	}
	echo ' &nbsp; <small>(', htmlEnt(__('Standard')) ,': ', htmlEnt('G.711a') ,')</small>',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>allow</code></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr class="m">',"\n";
	echo '<th>', __('Erlaubtes IP-Subnetz') ,' <sup>[5]</sup>:</th>',"\n";
	echo '<td>',"\n";
	@list($permit_ip_addr, $permit_cidr_netmask) = explode('/', @$gw_params['permit']);
	$permit_ip_addr_parts = explode('.', @$permit_ip_addr);
	//echo '<input type="text" name="gw-param-permit" value="', htmlEnt(@$gw_params['permit']) ,'" class="r" size="31" maxlength="50" />',"\n";
	echo '<input type="text" name="gw-param-permit-0" value="', (int)lTrim(@$permit_ip_addr_parts[0],'0 ') ,'" class="r" size="3" maxlength="3" style="max-width:3.1em;" />.';
	echo '<input type="text" name="gw-param-permit-1" value="', (int)lTrim(@$permit_ip_addr_parts[1],'0 ') ,'" class="r" size="3" maxlength="3" style="max-width:3.1em;" />.';
	echo '<input type="text" name="gw-param-permit-2" value="', (int)lTrim(@$permit_ip_addr_parts[2],'0 ') ,'" class="r" size="3" maxlength="3" style="max-width:3.1em;" />.';
	echo '<input type="text" name="gw-param-permit-3" value="', (int)lTrim(@$permit_ip_addr_parts[3],'0 ') ,'" class="r" size="3" maxlength="3" style="max-width:3.1em;" />',"\n";
	echo ' / <input type="text" name="gw-param-permit-mask" value="', (int)lTrim(@$permit_cidr_netmask,'0 ') ,'" class="r" size="2" maxlength="2" style="max-width:2.4em;" />',"\n";
	echo ' &nbsp; <small>(', htmlEnt(__('Standard')) ,': ', htmlEnt('0.0.0.0/0') ,')</small>',"\n";
	echo '</td>',"\n";
	echo '<td class="transp xs gray"><code>permit</code></td>',"\n";
	echo '</tr>',"\n";
	
	if ($gw['name'] == '') $gw['name'] = 'gw_...';
	
?>

</tbody>
</table>

<br />
<button type="submit">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
	<?php echo __('Speichern'); ?>
</button>

<br />
<br />
<br />
<p class="text"><sup>[1]</sup> <?php echo __("Plaintext: Diese Methode ist sehr unsicher, da das Passwort im Klartext \xC3\xBCbertragen wird. Sie sollte daher nicht verwendet werden.<br />MD5: In diesem Challenge/Response Verfahren wird eine MD5-Pr\xC3\xBCfsumme \xC3\xBCbertragen."); ?></p>
<p class="text"><sup>[2]</sup> <?php echo sPrintF(htmlEnt(__("String f\xC3\xBCr den %s-Befehl. Dabei wird %s automatisch von Gemeinschaft durch die zu w\xC3\xA4hlende Rufnummer, %s durch die Rufnummer ohne die erste Ziffer und %s durch die interne Bezeichnung %s ersetzt.")), '<tt>'.htmlEnt('Dial()').'</tt>', '<tt>'.htmlEnt('{number}').'</tt>', '<tt>'.htmlEnt('{number:1}').'</tt>', '<tt>'.htmlEnt('{gateway}').'</tt>', '<q><tt>'.htmlEnt($gw['name']).'</tt></q>' ); ?></p>
<p class="text"><sup>[3]</sup> <?php echo __('Gateways m&uuml;ssen jeweils einer Gateway-Gruppe zugeordnet werden damit sie benutzt werden k&ouml;nnen.'); ?></p>
<p class="text"><sup>[4]</sup> <?php echo sPrintF(htmlEnt(__("Mit Angabe des Ports (Standard-IAX-Port: %s) wird direkt dieser Port verwendet.")), '4569' ); ?></p>
<p class="text"><sup>[5]</sup> <?php echo sPrintF(htmlEnt(__("Sinnvolle Einstellungen w\xC3\xA4ren z.B. %s um Anrufe von allen IP-Adressen zu erlauben, %s um nur Anrufe aus dem Netz %s zu erlauben, %s um nur Anrufe aus dem Netz %s zu erlauben, %s um nur Anrufe von der IP-Adresse %s zu erlauben usw.")), '<br /><tt>'.htmlEnt('0.0.0.0/0').'</tt>', '<br /><tt>'.htmlEnt('192.0.2.0/24')/* special example net */.'</tt>', htmlEnt('192.0.2.*'), '<br /><tt>'.htmlEnt('192.168.0.0/16').'</tt>', htmlEnt('192.168.*.*'), '<br /><tt>'.htmlEnt('192.168.1.1/32').'</tt>', '192.168.1.1' ); ?><br />
<?php echo htmlEnt(__("Weitere Informationen:"));
echo  "\n", sPrintF('<a href="%s" target="_blank">%s</a>', htmlEnt(__('http://de.wikipedia.org/wiki/IP-Adresse')), htmlEnt(__("IP-Adresse")));
echo ",\n", sPrintF('<a href="%s" target="_blank">%s</a>', htmlEnt(__('http://de.wikipedia.org/wiki/Subnetz')), htmlEnt(__("Subnetz")));
echo ",\n", sPrintF('<a href="%s" target="_blank">%s</a>', htmlEnt(__('http://de.wikipedia.org/wiki/Classless_Inter-Domain_Routing')), htmlEnt(__("CIDR")));
echo ",\n", sPrintF('<a href="%s" target="_blank">%s</a>', htmlEnt(__('http://www.das-asterisk-buch.de/2.1/iax.html')), htmlEnt('iax.conf'));
echo  "\n";
?></p>

</form>

<?php
}
#####################################################################



#####################################################################
if ($action == '') {
?>

<table cellspacing="1">
<thead>
<tr>
	<th style="width:150px;"><?php echo __('Gateway'); ?></th>
	<th style="width:150px;"><?php echo __('Gruppe'); ?></th>
	<th style="width:150px;"><?php echo __('Registrar'); ?></th>
	<th style="width:55px;">&nbsp;</th>
</tr>
</thead>
<tbody>
<?php
	
	/*
	$err=0; $out=array();
	@exec( 'sudo asterisk -rx \'sip show registry\' 2>>/dev/null', $out, $err );
	$regs = array();
	if ($err === 0) {
		foreach ($out as $line) {
			if (! preg_match('/(gw_[0-9]+_[a-z0-9\-_]*)/', $line, $m))
				continue;
			$peername = $m[1];
			if (! preg_match('/\b((?:Un)?[Rr]egistered)/', $line, $m)) {
				$regs[$peername] = false;
				continue;
			}
			$regs[$peername] = ($m[1] === 'Registered');
		}
	}
	*/
	
	# get gateways from DB
	#
	$rs = $DB->execute(
'SELECT
	`g`.`id`, `g`.`name`, `g`.`title`, `g`.`host`,
	`gg`.`id` `gg_id`, `gg`.`title` `gg_title`
FROM
	`gates` `g` LEFT JOIN
	`gate_grps` `gg` ON (`gg`.`id`=`g`.`grp_id`)
WHERE
	`g`.`type`=\'iax\'
ORDER BY `g`.`grp_id`, `g`.`title`'
	);
	$i=0;
	while ($gw = $rs->fetchRow()) {
		echo '<tr class="', ($i%2===0?'odd':'even') ,'">',"\n";
		
		/*
		if (subStr($gw['name'],0,3)==='gw_')
			$gw['name'] = subStr($gw['name'],3);
		echo '<td><tt>gw_</tt><input type="text" name="gw-',$gw['id'],'-name" value="', htmlEnt($gw['name']) ,'" size="20" maxlength="20" style="font-family:monospace;" /></td>',"\n";
		*/
		
		echo '<td><b>', htmlEnt($gw['title']) ,'</b></td>',"\n";
		
		echo '<td>';
		if ($gw['gg_id'] > 0) {
			echo htmlEnt($gw['gg_title']);
		} else {
			echo '<i>-- ', __('nicht zugeordnet') ,' --</i>';
		}
		echo '</td>',"\n";
		
		//echo '<td><input type="checkbox" name="gw-',$gw['id'],'-allow_out" value="1" ', ($gw['allow_out'] ? 'checked="checked" ' : '') ,'/></td>',"\n";
		
		echo '<td>', htmlEnt($gw['host']) ,'</td>',"\n";
		
		/*
		echo '<td>';
		if (@$regs[$gw['name']])
			echo '<img alt=" " src="', GS_URL_PATH ,'crystal-svg/16/act/greenled.png" />';
		else
			echo '&nbsp;';
		echo '</td>',"\n";
		*/
		
		echo '<td>',"\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=edit&amp;gw-id='.$gw['id']) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=del&amp;gw-id='.$gw['id']) ,'" title="', __('l&ouml;schen'), '" onclick="return confirm_delete();"><img alt="', __('l&ouml;schen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo '</td>',"\n";
		
		echo '</tr>',"\n";
		++$i;
	}
	echo '<tr class="', ($i%2===0?'odd':'even') ,'">',"\n";
	echo '<td colspan="3" class="transp">&nbsp;</td>',"\n";
	echo '<td class="transp">',"\n";
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=edit&amp;gw-id=0') ,'" title="', __('hinzuf&uuml;gen'), '"><img alt="', __('hinzuf&uuml;gen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
?>
</tbody>
</table>

<br />
<br />
<?php echo __('Registrierungs-Status'); ?><?php echo ' (',__('lokaler Asterisk'),')'; ?><br />
<div style="font-family:monospace; white-space:pre; background:#eee; border:1px solid #e9e9e9; padding:1px 3px;"><?php
	$err=0; $out=array();
	@exec( 'sudo asterisk -rx '.qsa('iax2 show registry').' 2>>/dev/null', $out, $err );
	if ($err !== 0) {
		echo '?';
	} else {
		foreach ($out as $line) {
			echo preg_replace(
				array( '/^(\s*)([a-zA-Z0-9.\-_]+[.\-_][a-zA-Z0-9.\-_]+)/', '/\b(Registered|Unregistered|Rejected)\b/' ),
				array( '$1<b>$2</b>', '<b>$1</b>' ),
				rTrim($line)) ,"\n";
		}
	}
?></div>


<?php
}
#####################################################################


?>