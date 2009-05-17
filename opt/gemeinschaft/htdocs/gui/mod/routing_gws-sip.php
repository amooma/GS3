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

$default_dialstrs = array(
	'sip'     => 'SIP/{number:1}@{gateway}',
	);
$gw_type = 'sip';

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
	`user`,
	`pwd`
) VALUES (
	NULL,
	NULL,
	\'sip\',
	\'gw_tmp_'. rand(100000,999999) .'\',
	\'\',
	0,
	\''. $DB->escape( $default_dialstrs[$gw_type] ) .'\',
	\'\',
	\'\',
	\'\'
)'
		);
		$gwid = (int)$DB->getLastInsertId();
	}
	
	$sip_friend_name = strToLower(@$_REQUEST['gw-title']);
	$sip_friend_name = preg_replace('/[^a-z0-9]/', '', $sip_friend_name);
	$sip_friend_name = subStr('gw_'.$gwid.'_'.$sip_friend_name, 0, 20);
	
	$query =
'UPDATE `gates` SET
	`grp_id` = '. ((int)@$_REQUEST['gw-grp_id'] > 0 ? (int)@$_REQUEST['gw-grp_id'] : 'NULL') .',
	`name` = \''. $DB->escape($sip_friend_name) .'\',
	`title` = \''. $DB->escape(trim(@$_REQUEST['gw-title'])) .'\',
	`allow_out` = '. (@$_REQUEST['gw-allow_out'] ? 1 : 0) .',
	`dialstr` = \''. $DB->escape(trim(@$_REQUEST['gw-dialstr']) ) .'\',
	`host` = \''. $DB->escape(preg_replace('/[^a-zA-Z0-9\-_.:]/', '', @$_REQUEST['gw-host'])) .'\',
	`user` = \''. $DB->escape(preg_replace('/[^a-zA-Z0-9\-_.@]/', '', @$_REQUEST['gw-user'])) .'\',
	`pwd` = \''. $DB->escape(preg_replace('/[^a-zA-Z0-9\-_.#*]/', '', @$_REQUEST['gw-pwd'])) .'\'
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
		$rs = $DB->execute( 'SELECT `grp_id`, `type`, `name`, `title`, `allow_out`, `host`, `user`, `pwd`, `dialstr` FROM `gates` WHERE `id`='.$gwid );
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
			'user'       => '',
			'dialstr'    => $default_dialstrs[$gw_type],
			'pwd'        => ''
		);
	}
?>


<table cellspacing="1">
<tbody>

<?php
	echo '<tr>',"\n";
	echo '<th style="width:120px;">', __('Titel') ,':</th>',"\n";
	echo '<th style="width:360px;"><input type="text" name="gw-title" value="', htmlEnt($gw['title']) ,'" size="30" maxlength="35" style="font-weight:bold; width:97%;" /></th>',"\n";
	echo '</tr>',"\n";
	
	/*
	echo '<tr>',"\n";
	echo '<th>', __('Name') ,':</th>',"\n";
	echo '<td style="padding-top:5px;"><tt>', htmlEnt($gw['name']) ,'</tt></td>',"\n";
	echo '</tr>',"\n";
	*/
	
	echo '<tr>',"\n";
	echo '<th>', __('Host') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-host" value="', htmlEnt($gw['host']) ,'" size="30" maxlength="50" style="width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Benutzername') ,' <sup>[1]</sup>:</th>',"\n";
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
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('W&auml;hlbefehl') ,' <sup>[2]</sup>:</th>',"\n";
	echo '<td>',"\n";
	echo '<input type="text" name="gw-dialstr" value="', htmlEnt($gw['dialstr']) ,'" size="25" maxlength="50" style="font-family:monospace; width:97%;" />',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Gruppe') ,' <sup>[3]</sup>:</th>',"\n";
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

<br />
<br />
<br />
<p class="text"><sup>[1]</sup> <?php echo __('Abh&auml;ngig vom SIP-Provider kann es erforderlich sein die Form <tt>benutzer@domain</tt> anzugeben. (<tt>domain</tt> wird dann im <tt>From</tt>-Header verwendet, was <tt>fromdomain</tt> in Asterisk entspricht.)'); ?></p>
<p class="text"><sup>[2]</sup> <?php echo htmlEnt(sPrintF(__("String f\xC3\xBCr den Dial()-Befehl. Dabei wird {number} automatisch von Gemeinschaft durch die zu w\xC3\xA4hlende Rufnummer, {number:1} durch die Rufnummer ohne die erste Ziffer und {gateway} durch die interne Bezeichnung \"%s\" ersetzt."), $gw['name'])); ?></p>
<p class="text"><sup>[3]</sup> <?php echo __('Gateways m&uuml;ssen jeweils einer Gateway-Gruppe zugeordnet werden damit sie benutzt werden k&ouml;nnen.'); ?></p>

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
	<th style="width:150px;"><?php echo __('Host'); ?></th>
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
