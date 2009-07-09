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
* Soeren Sprenger <soeren.sprenger@amooma.de>
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

function printparam( $param, $value, &$userparamarray, $html=false ) {
	if ($param == '')
		return;
	$printbr = true;

	if (array_key_exists($param, $userparamarray)) {
		if ($userparamarray[$param] != '')
			echo $param ." = ". $userparamarray[$param];
		else
			$printbr = false;
		unset($userparamarray[$param]);
	} else {
		echo $param ." = ". $value;
	}
	if ($printbr) {
		if ($html)
			echo "<br \>";
		echo "\n";
	}
}

$default_dialstrs = array(
	'sip'     => 'SIP/{number:1}@{gateway}',
	);
$gw_type = 'sip';

$action = @$_REQUEST['action'];
if (! in_array($action, array( '', 'edit', 'save', 'saveextended', 'delextended', 'del' ), true))
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

print_r($_REQUEST);

#####################################################################
if ($action === 'saveextended') {
	
	$gwid = (int)@$_REQUEST['gw-id'];
	$newparam = $DB->escape($_REQUEST['extra-new-param']);
	$newvalue = $DB->escape($_REQUEST['extra-new-value']);

	if ($newparam != '') {
		echo 'INSERT INTO `gate_params` (`gate_id`,`param`, `value`) VALUES('.$gwid.", '".$newparam."', '".$newvalue."')";
		$DB->execute('INSERT INTO `gate_params` (`gate_id`,`param`, `value`) VALUES('.$gwid.", '".$newparam."', '".$newvalue."')");
	}
}

if ($action === 'delextended') {
	$gwid = (int)@$_REQUEST['gw-id'];
	$delparam = $DB->escape($_REQUEST['deleteparam']);
	$delvalue = $DB->escape($_REQUEST['deletevalue']);

	if ($delparam != '') {
		echo 'DELETE FROM `gate_params` WHERE `param` = \''.$delparam.'\' AND value =\''.$delvalue.'\'';
		$DB->execute('DELETE FROM `gate_params` WHERE `param` = \''.$delparam.'\' AND value =\''.$delvalue.'\'');
	}
}

#####################################################################



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
	\'sip\',
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
	
	$sip_friend_name = strToLower(@$_REQUEST['gw-title']);
	$sip_friend_name = preg_replace('/[^a-z0-9]/', '', $sip_friend_name);
	$sip_friend_name = subStr('gw_'.$gwid.'_'.$sip_friend_name, 0, 20);
	
	$host  = preg_replace('/[^a-zA-Z0-9\-_.]/', '', @$_REQUEST['gw-host']);
	$proxy = preg_replace('/[^a-zA-Z0-9\-_.]/', '', @$_REQUEST['gw-proxy']);
	if ($proxy == '') $proxy = null;
	elseif ($proxy === $host) $proxy = null;
	
	$query =
'UPDATE `gates` SET
	`grp_id` = '. ((int)@$_REQUEST['gw-grp_id'] > 0 ? (int)@$_REQUEST['gw-grp_id'] : 'NULL') .',
	`name` = \''. $DB->escape($sip_friend_name) .'\',
	`title` = \''. $DB->escape(trim(@$_REQUEST['gw-title'])) .'\',
	`allow_out` = '. (@$_REQUEST['gw-allow_out'] ? 1 : 0) .',
	`dialstr` = \''. $DB->escape(trim(@$_REQUEST['gw-dialstr'])) .'\',
	`host` = \''. $DB->escape($host) .'\',
	`proxy` = '. ($proxy == null ? 'NULL' : ('\''. $DB->escape($proxy) .'\'') ) .',
	`user` = \''. $DB->escape(preg_replace('/[^a-zA-Z0-9\-_.@]/', '', @$_REQUEST['gw-user'])) .'\',
	`pwd` = \''. $DB->escape(preg_replace('/[^a-zA-Z0-9\-_.#*]/', '', @$_REQUEST['gw-pwd'])) .'\',
	`register` = '. (@$_REQUEST['gw-register'] ? 1 : 0) .'
WHERE `id`='. (int)$gwid
	;
	// allow "@" in SIP username so you can enter user@fromdomain
	// (e.g. 12345@sipgate.de) as your username to register with sipgate.de
	//echo "<pre>$query</pre>\n";
	$DB->execute($query);
	
	$cmd = '/opt/gemeinschaft/sbin/start-asterisk 1>>/dev/null 2>>/dev/null';
	@exec( 'sudo sh -c '. qsa($cmd) .' 1>>/dev/null 2>>/dev/null' );
	
	$action = '';
}
#####################################################################



#####################################################################
if ($action === 'del') {
	
	$gwid = (int)@$_REQUEST['gw-id'];
	
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
		if ($gw['type'] !== 'sip') {
			echo 'Not a SIP gateway.';
			return;
		}
	}
	else {
		$gw = array(
			'grp_id'     => null,
			'type'       => 'sip',
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
	}
?>


<table cellspacing="1">
<tbody>

<?php
	echo '<tr>',"\n";
	echo '<th style="width:105pt;">', __('Titel') ,':</th>',"\n";
	echo '<th style="width:260pt;"><input type="text" name="gw-title" value="', htmlEnt($gw['title']) ,'" size="30" maxlength="35" style="font-weight:bold; width:97%;" /></th>',"\n";
	echo '</tr>',"\n";
	
	/*
	echo '<tr>',"\n";
	echo '<th>', __('Name') ,':</th>',"\n";
	echo '<td style="padding-top:5px;"><tt>', htmlEnt($gw['name']) ,'</tt></td>',"\n";
	echo '</tr>',"\n";
	*/
	
	echo '<tr>',"\n";
	echo '<th>', __('Registrar') ,' / ', __('Server') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-host" value="', htmlEnt($gw['host']) ,'" size="30" maxlength="50" style="width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Proxy') ,' <sup>[1]</sup>:</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-proxy" value="', htmlEnt($gw['proxy']) ,'" size="30" maxlength="50" style="width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Benutzername') ,' <sup>[2]</sup>:</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-user" value="', htmlEnt($gw['user']) ,'" size="25" maxlength="35" style="width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Pa&szlig;wort') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-pwd" value="', htmlEnt($gw['pwd']) ,'" size="25" maxlength="35" style="width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>&nbsp;</th>',"\n";
	echo '<td>';
	echo '<input type="checkbox" name="gw-allow_out" id="ipt-gw-allow_out" value="1" ', ($gw['allow_out'] ? 'checked="checked" ' : '') ,'/> <label for="ipt-gw-allow_out">', __('ausgehende Anrufe zulassen') ,'</label>',"\n";
	echo '&nbsp;&nbsp;',"\n";;
	echo '<input type="checkbox" name="gw-register" id="ipt-gw-register" value="1" ', ($gw['register'] ? 'checked="checked" ' : '') ,'/> <label for="ipt-gw-register">', __('registrieren') ,'</label>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('W&auml;hlbefehl') ,' <sup>[3]</sup>:</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-dialstr" value="', htmlEnt($gw['dialstr']) ,'" size="25" maxlength="50" style="font-family:monospace; width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Gruppe') ,' <sup>[4]</sup>:</th>',"\n";
	echo '<td>';
	echo '<select name="gw-grp_id" style="width:97%;">',"\n";
	echo '<option value=""', ($gw['grp_id'] < 1 ? ' selected="selected"' : '') ,'>-- ', __('nicht zugeordnet') ,' --</option>' ,"\n";
	echo '<option value="" disabled="disabled">', '' ,'</option>' ,"\n";
	$rs = $DB->execute(
'SELECT `id`, `title`
FROM `gate_grps`
ORDER BY `title`'
	);
	while ($gg = $rs->fetchRow()) {
		echo '<option value="', $gg['id'] ,'"', ($gg['id'] === $gw['grp_id'] ? ' selected="selected"' : '') ,'>', htmlEnt($gg['title']) ,'</option>' ,"\n";
	}
	echo '</select>',"\n";
	echo '</td>',"\n";
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

<button type="submit" name="extended" value="show">
	<img alt="<?php echo __('Speichern und zur Expertenansicht wechseln'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
	<?php echo __('Erweitert'); ?>
</button>


<br />
<br />
<br />

<p class="text"><sup>[1]</sup> <?php echo htmlEnt(__("Leer f\xC3\xBCr keinen Proxy.")); ?></p>
<p class="text"><sup>[2]</sup> <?php echo __('Abh&auml;ngig vom SIP-Provider kann es erforderlich sein die Form <tt>benutzer@domain</tt> anzugeben. (<tt>domain</tt> wird dann im <tt>From</tt>-Header verwendet, was <tt>fromdomain</tt> in Asterisk entspricht.)'); ?></p>
<p class="text"><sup>[3]</sup> <?php echo htmlEnt(sPrintF(__("String f\xC3\xBCr den Dial()-Befehl. Dabei wird {number} automatisch von Gemeinschaft durch die zu w\xC3\xA4hlende Rufnummer, {number:1} durch die Rufnummer ohne die erste Ziffer und {gateway} durch die interne Bezeichnung \"%s\" ersetzt."), $gw['name'])); ?></p>
<p class="text"><sup>[4]</sup> <?php echo __('Gateways m&uuml;ssen jeweils einer Gateway-Gruppe zugeordnet werden damit sie benutzt werden k&ouml;nnen.'); ?></p>

</form>

<?php
}
#####################################################################
if ($_REQUEST['extended'] == "show") {
?>
<h3><?php echo __('Erweiterte Parameter für das SIP-Gateway: ').htmlEnt($_REQUEST['gw-title']);?></h3>
<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<br />
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="saveextended" />
<input type="hidden" name="extended" value="show" />
<input type="hidden" name="gw-id" value="<?php echo $gwid; ?>" />
<input type="hidden" name="gw-title" value="<?php echo $_REQUEST['gw-title']; ?>" />		
<table cellspacing=1 class=phonebook\">
<thead>
<tr>
	<th style="width: 160px;"><?php echo __('Parameter');?></th>
	<th style="width: 285px;"><?php echo __('Wert');?></th>
	<th style="width: 20px;"></th>
</tr>
</thead>
<tbody>
<tr>
<?php
	$gwid = (int)@$_REQUEST['gw-id'];
	$userparamarray = array();
	$rs = $DB->execute('SELECT * FROM `gate_params` WHERE `gate_id` ='.$gwid);
	while ($param = $rs->fetchRow()) {
		$userparamarray[$param['param']] = $param['value'];
		echo "<tr>";
		echo "<td>";
		echo $param['param'];
		echo "</td>";
		echo "<td>";
		echo $param['value'];
		echo "</td>";
		echo "<td>";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'deleteparam='. rawUrlEncode($param['param']) .'&amp;deletevalue='.rawUrlEncode($param['value']).'&amp;action=delextended&amp;extended=show&amp;gw-title='.rawUrlEncode($_REQUEST['gw-title']).'&amp;gw-id='.rawUrlEncode($gwid).'').'" title="',__('l&ouml;schen'), '" onclick="return confirm_delete();"><img alt="',__('entfernen'), '" src="',GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo "</td>";
		echo "</tr>";
	}
	
	echo "<td>";
	echo '<input type="text" name="extra-new-param" value="" size="25" maxlength="50" style="width:97%;" />',"\n";
	echo "</td>";
	echo "<td>";
	echo '<input type="text" name="extra-new-value" value="" size="25" maxlength="50" style="width:97%;" />',"\n";
	echo "</td>";
	echo "<td>";
	echo "<button type=\"submit\" title=\"". __('Parameter Speichern'). "\" class=\"plain\" name=\"extra-action\" value=\"save\"><img alt=\"". __('Speichern')."\" src=\"".GS_URL_PATH."crystal-svg/16/act/filesave.png\" </button>";
	echo "</td>";
	echo "</tr>\n";

	$action = 'extended';

	echo "</tbody>";
	echo "</table>";
	echo "</form>";

	echo "<br \>";
	
	echo '<h3>'.__('Vorschau des peers in der sip.conf :') ."</h3>\n";

	$rs = $DB->execute(
	'SELECT
		`g`.`name`, `g`.`host`, `g`.`proxy`, `g`.`user`, `g`.`pwd`,
		`gg`.`name` `gg_name`
	FROM
		`gates` `g` JOIN
		`gate_grps` `gg` ON (`gg`.`id`=`g`.`grp_id`)
	WHERE
		`g`.`type`=\'sip\' AND
		`g`.`host` IS NOT NULL AND
		`g`.`id`='.$gwid
	);
	$gw = $rs->fetchRow();

	if ($gw['name'] != '' && $gw['host'] != '') {
	
		$nat            = 'yes';
		//$canreinvite    = 'no';
		$canreinvite    = 'nonat';
		
		$qualify        = 'yes';
		$maxexpiry      =  185;
		$defaultexpiry  =  145;
		
		$codecs_allow = array();
		$codecs_allow['alaw'   ] = true;
		$codecs_allow['ulaw'   ] = false;
		
		//$fromdomain     = 'gemeinschaft.localdomain';
		$fromdomain     = null;
		$fromuser       = null;
		
		if (preg_match('/@([^@]*)$/', $gw['user'], $m)) {
			# set domain in the From header
			$fromdomain = $m[1];
			$gw['user'] = subStr($gw['user'], 0, -strLen($m[0]));
			
			# assume that this SIP provider requires the username
			# instead of the caller ID number in the From header (and
			# that the caller ID is to be set in a P-Preferred-Identity
			# header)
			$fromuser   = $gw['user'];
			
			# also assume that this gateway is a SIP provider and
			# that re-invites will not work
			$canreinvite    = 'no';
		}
		
		if ($gw['proxy'] == null || $gw['proxy'] === $gw['host']) {
			$gw['proxy'] = null;
		}
		
		
		if (strToLower($gw['host']) === 'sip.1und1.de') {  # special settings for 1und1.de
			//$canreinvite    = 'no';
			
			//$fromdomain     = '1und1.de';
			//$fromuser       = $gw['user'];
			
			$qualify        = 'no';
			$maxexpiry      = 3600;
			$defaultexpiry  = 3600;
			
			$codecs_allow['alaw'   ] = true;
			$codecs_allow['ulaw'   ] = true;
			$codecs_allow['ilbc'   ] = true;
			$codecs_allow['gsm'    ] = true;
			$codecs_allow['g729'   ] = true;
			$codecs_allow['slinear'] = true;
		}
		elseif (strToLower($gw['host']) === 'sipgate.de') {  # special settings for SipGate.de
			//$canreinvite    = 'no';
			//$fromdomain     = 'sipgate.de';
			//$fromuser       = $gw['user'];
		}
		elseif (preg_match('/\\.sipgate\\.de$/i', $gw['host'])) {  # special settings for SipGate.de
			# sipconnect.sipgate.de, SipGate "Team" trunk
			//$fromuser       = $gw['user'];
			//$canreinvite    = 'no';
		}
		
		echo '[', $gw['name'] ,']' ,"<br>\n";
		printparam( 'type', 'peer', $userparamarray, true);
		printparam( 'host', $gw['host'], $userparamarray, true);
		printparam( 'port', '5060', $userparamarray, true);
		printparam( 'username', $gw['user'], $userparamarray, true);
		printparam( 'secret', $gw['pwd'], $userparamarray, true);
		
		if ($gw['proxy'] != null) {
			printparam( 'outboundproxy', $gw['proxy'], $userparamarray, true);
		}
		if ($fromdomain != null) {
			printparam( 'fromdomain', $fromdomain, $userparamarray, true);
		}
		if ($fromuser != null) {
			printparam( 'fromuser', $fromuser, $userparamarray, true);
		}
		printparam( 'insecure', 'port,invite', $userparamarray, true);
		printparam( 'nat', $nat, $userparamarray, true);
		printparam( 'canreinvite', $canreinvite, $userparamarray, true);
		printparam( 'dtmfmode', 'rfc2833', $userparamarray, true);
		printparam( 'call-limit', '0', $userparamarray, true);
		printparam( 'registertimeout', '60', $userparamarray, true);
		printparam( 'setvar=__is_from_gateway', '1', $userparamarray, true);
		printparam( 'context', 'from-gg-'.$gw['gg_name'], $userparamarray, true);
		printparam( 'qualify', $qualify, $userparamarray, true);
		printparam( 'language', 'de', $userparamarray, true);
		printparam( 'maxexpiry', $maxexpiry, $userparamarray, true);
		printparam( 'defaultexpiry', $defaultexpiry, $userparamarray, true);
		printparam( 'disallow', 'all', $userparamarray, true);
		foreach ($codecs_allow as $codec => $allowed) {
			if ($allowed) {
				printparam( 'allow', $codec, $userparamarray, true);
			}
		}
		foreach ($userparamarray as $param => $value) {
			printparam( $param, '', $userparamarray, true);
		}
	}
}


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
	`g`.`type`=\'sip\'
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
	@exec( 'sudo asterisk -rx '.qsa('sip show registry').' 2>>/dev/null', $out, $err );
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
