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

######################################################
##
##   ALL STRINGS IN HERE NEED TO BE TRANSLATED!
##
######################################################

defined('GS_VALID') or die('No direct access.');
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


$action = @$_REQUEST['action'];
if (! in_array($action, array( '', 'gedit', 'gsave', 'gwdel', 'ggdel' /*, 'zapata', 'zapata_save'*/ ), true))
	$action = '';
$ggid = (int)@$_REQUEST['ggid'];

/*
echo '<div class="fr">',"\n";
if ($action == '') {
	echo '<a href="', gs_url($SECTION, $MODULE) ,'&amp;action=zapata">Zapata conf</a><br />';
}
elseif ($action == 'zapata') {
	echo '<a href="', gs_url($SECTION, $MODULE) ,'&amp;action=">Gateways</a><br />';
}
echo '</div>',"\n";
*/


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

$gw_types = array(
	'sip' => 'SIP',
	'zap' => 'Zap'
);

#####################################################################
/*
if ($action == 'zapata' || $action == 'zapata_save') {
	echo '<h3>zapata</h3>',"\n";
	$zapata_conf_file = GS_DIR .'etc/asterisk/zapata.conf';
	//FIXME
	if (! file_exists($zapata_conf_file)) {
		echo "File \"$zapata_conf_file\" not found.\n";
	} else {
		$zapata_conf = @file($zapata_conf_file);
		if (empty($zapata_conf)) {
			echo "Failed to read \"$zapata_conf_file\".\n";
		} else {
			echo 'Ver&auml;nderungen sind momentan nicht m&ouml;glich.<br />',"\n";
			echo '<br />',"\n";
			
			echo '<form method="post" action="', GS_URL_PATH ,'">',"\n";  //FIXME
			echo gs_form_hidden($SECTION, $MODULE);
			echo '<input type="hidden" name="action" value="zapata_save" />',"\n";
			
			echo '<table cellspacing="1" style="margin-left:3em;">',"\n";
			echo '<tbody>',"\n";
			
			$in_channels_section = false;
			foreach ($zapata_conf as $line) {
				$line = trim($line);
				if ($line==='' || subStr($line,0,1)===';') continue;
				if (subStr($line,0,1)==='[') {
					if ($line === '[channels]')
						$in_channels_section = true;
					else
						$in_channels_section = false;
					continue;
				}
				
				if (preg_match('/^#(exec|include)\s*(.*)/S', $line, $m))
				{
					$key = '#'.$m[1];
					$input_name = 'zapata-'.$m[1];  //FIXME
					$val = trim($m[2],' "\'');
					$size = 60;
				}
				elseif (preg_match('/^([a-z0-9\-_]+)\s*=[>]?\s*([^;]*)/S', $line, $m)) {
					$key = $m[1];
					$input_name = 'zapata-'.$key;  //FIXME
					$val = rTrim($m[2]);
					$size = 25;
				}
				else {
					continue;
				}
				
				echo '<tr class="">',"\n";
				
				echo '<td>',"\n";
				echo '<label>', $key ,'</label>',"\n";
				echo '</td>',"\n";
				
				echo '<td>',"\n";
				echo '<input type="text" name="', $input_name ,'" value="', htmlEnt($val) ,'" size="', $size ,'" maxlength="50" disabled="disabled" />';
				echo '</td>',"\n";
				
				echo '</tr>',"\n";
			}
			echo '</tbody>',"\n";
			echo '</table>',"\n";
			
			echo '<br />', "\n";
			echo '<input type="submit" value="', __('Speichern') ,'" disabled="disabled" />',"\n";
			
			echo '</form>',"\n";
		}
	}
	echo '<br />', "\n";
}
*/
#####################################################################





#####################################################################
if ($action === 'gsave') {
	
	if ($ggid > 0) {
		$rs = $DB->execute( 'SELECT `name`, `title`, `type`, `allow_in`, `in_dest_search`, `in_dest_replace` FROM `gate_grps` WHERE `id`='.$ggid );
		$oldgg = $rs->fetchRow();
	}
	if ($ggid < 1 || ($ggid > 0 && ! $oldgg)) {
		$oldgg = array(
			'name'            => '',
			'title'           => '',
			'type'            => '',
			'allow_in'        => 0,
			'in_dest_search'  => '',
			'in_dest_replace' => ''
		);
	}
	
	$title = trim(@$_REQUEST['gg-title']);
	$name  = preg_replace('/[^a-z0-9\-_]/', '', @$_REQUEST['gg-name']);
	$type = 'balance';
	$allow_in = (@$_REQUEST['gg-allow_in'] ? 1 : 0);
	$in_dest_search  = trim(@$_REQUEST['gg-in_dest_search' ]);
	$in_dest_replace = trim(@$_REQUEST['gg-in_dest_replace']);
	
	if ($ggid > 0) {
		$DB->execute(
'UPDATE `gate_grps` SET
	`title`=\''. $DB->escape($title) .'\',
	`type`=\''. $DB->escape($type) .'\',
	`allow_in`='. $allow_in .',
	`in_dest_search`=\''. $DB->escape($in_dest_search) .'\',
	`in_dest_replace`=\''. $DB->escape($in_dest_replace) .'\'
WHERE `id`='.$ggid
		);
		// separate query because there's a "unique" constraint on the name
		// column:
		$DB->execute(
'UPDATE `gate_grps` SET
	`name`=\''. $DB->escape($name) .'\'
WHERE `id`='.$ggid
		);
	}
	else {
		$DB->execute(
'INSERT INTO `gate_grps` (
	`id`,
	`name`,
	`title`,
	`type`,
	`allow_in`,
	`in_dest_search`,
	`in_dest_replace`
) VALUES (
	NULL,
	\''. $DB->escape($name) .'\',
	\''. $DB->escape($title) .'\',
	\''. $DB->escape($type) .'\',
	'. $allow_in .',
	\''. $DB->escape($in_dest_search) .'\',
	\''. $DB->escape($in_dest_replace) .'\'
)'
		);
		$ggid = (int)$DB->getLastInsertId();
		if ($ggid < 1) $ggid = 0;
		$_REQUEST['ggid'] = $ggid;
	}
	
	if ($ggid > 0) {
		
		$gwids = array();
		foreach ($_REQUEST as $k => $v) {
			if (preg_match('/^gw-([0-9]+)/S', $k, $m)) {
				$gwids[(int)$m[1]] = true;
			}
		}
		foreach ($gwids as $gwid => $ignore) {
			
			$type = strToLower(@$_REQUEST['gw-'.$gwid.'-type']);
			$name = preg_replace('/[^a-z0-9\-_]/', '', strToLower(@$_REQUEST['gw-'.$gwid.'-name']));
			$title = trim(@$_REQUEST['gw-'.$gwid.'-title']);
			$allow_out = (@$_REQUEST['gw-'.$gwid.'-allow_out'] ? 1 : 0);
			$dialstr = trim(@$_REQUEST['gw-'.$gwid.'-dialstr']);
			$dialstr = preg_replace('/\s+/S', '', $dialstr);
			$dialstr = preg_replace('/\{number\}/iS', '{number}', $dialstr);
			$dialstr = preg_replace('/\{peer\}/iS', '{peer}', $dialstr);
			switch ($type) {
				case 'sip':
					$dialstr = preg_replace('/^[^\/]*\//S', 'SIP/', $dialstr);
					break;
				case 'zap':
					$dialstr = preg_replace('/^[^\/]*\//S', 'Zap/', $dialstr);
					break;
			}
			
			if ($gwid > 0) {
				if (! in_array($type, array('sip', 'zap'), true))
					$type = 'sip';
				
				$DB->execute(
'UPDATE `gates` SET
	`type`=\''. $DB->escape($type) .'\',
	`title`=\''. $DB->escape($title) .'\',
	`allow_out`=\''. $allow_out .'\',
	`dialstr`=\''. $DB->escape($dialstr) .'\'
WHERE `id`='.$gwid
				);
				// separate query because there's a "unique" constraint on the name
				// column:
				$DB->execute(
'UPDATE `gates` SET
	`name`=\'gw_'. $DB->escape($name) .'\'
WHERE `id`='.$gwid
				);
			}
			else {
				if (in_array($type, array('sip', 'zap'), true)
				&&  $name != ''
				&&  $dialstr != ''
				) {
					$DB->execute(
'INSERT INTO `gates` (
	`id`,
	`grp_id`,
	`type`,
	`name`,
	`title`,
	`allow_out`,
	`dialstr`
) VALUES (
	NULL,
	'. $ggid .',
	\''. $DB->escape($type) .'\',
	\''. $DB->escape($name) .'\',
	\''. $DB->escape($title) .'\',
	'. $allow_out .',
	\''. $DB->escape($dialstr) .'\'
)'
					);
				}
			}
		}
		
	}
	
	$action = 'gedit';
}
#####################################################################



#####################################################################
if ($action === 'gwdel') {
	
	$gwid = (int)@$_REQUEST['gwid'];
	
	$DB->execute( 'DELETE FROM `gates` WHERE `id`='.$gwid );
	
	
	$action = 'gedit';
}
#####################################################################



#####################################################################
if ($action === 'ggdel') {
		
	$DB->execute( 'DELETE FROM `gate_grps` WHERE `id`='.$ggid );
	
	$action = '';
}
#####################################################################




# get gateway groups from DB
#
$rs = $DB->execute( 'SELECT `id`, `name`, `title` FROM `gate_grps` ORDER BY `title`' );
$ggs = array();
while ($r = $rs->fetchRow())
	$ggs[] = $r;


?>
<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="gedit" />
<?php
echo __('Gateway-Gruppe'),': ';
echo '<select name="ggid" onchange="this.form.submit();">',"\n";
foreach ($ggs as $gg) {
	echo '<option value="', $gg['id'] ,'"', ($gg['id']==$ggid ? ' selected="selected"' : '') ,'>', htmlEnt($gg['title']) ,' (', htmlEnt($gg['name']) ,')</option>',"\n";
}
echo '<option value="" disabled="disabled">-</option>',"\n";
echo '<option value="0"', ($ggid < 1 ? ' selected="selected"' : '') ,'>', __('Neue Gateway-Gruppe anlegen ...') ,'</option>',"\n";
echo '</select> ',"\n";
echo '<input type="submit" value="', __('anzeigen') ,'" />',"\n";
echo '</form>',"\n";
echo '<hr size="1" />',"\n";


if ($ggid > 0) {
	echo '<div class="fr">',"\n";
	echo '<a href="', gs_url($SECTION, $MODULE), '&amp;ggid=', $ggid ,'&amp;action=ggdel" title="', __('l&ouml;schen'), '"><button type="button"><img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /> ', __('l&ouml;schen') ,'</button></a>';
	echo '</div>',"\n";
}


#####################################################################
if ($action === 'gedit') {
	
?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="ggid" value="<?php echo $ggid; ?>" />
<input type="hidden" name="action" value="gsave" />

<?php
	
	if ($ggid > 0) {
		# get gateway group from DB
		$rs = $DB->execute( 'SELECT `name`, `title`, `type`, `allow_in`, `in_dest_search`, `in_dest_replace` FROM `gate_grps` WHERE `id`='.$ggid );
		$gg = $rs->fetchRow();
		if (! $gg) return;
	}
	else {
		$gg = array(
			'name'           => '',
			'title'          => '',
			'type'           => 'balance',
			'allow_in'       => '',
			'in_dest_search' => '',
			'in_dest_replace'=> ''
		);
	}
	
?>


<h3><?php echo __('Gateway-Gruppe'); ?></h3>
<table cellspacing="1">
<tbody>

<?php
	echo '<tr>',"\n";
	echo '<th style="width:70px;">', __('Titel') ,':</th>',"\n";
	echo '<th style="width:340px;"><input type="text" name="gg-title" value="', htmlEnt($gg['title']) ,'" size="35" maxlength="35" style="font-weight:bold; font-size:1.2em; width:97%;" /></th>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', 'Context' ,':</th>',"\n";
	echo '<td style="padding-top:5px;"><tt>from-gg-</tt><input type="text" name="gg-name" value="', htmlEnt($gg['name']) ,'" size="20" maxlength="20" style="font-family:monospace;" /> <sup>[1]</sup></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Art') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<select name="gg-type" disabled="disabled">',"\n";
	echo '<option name="balance" selected="selected">Load Balance</option>',"\n";
	echo '</select>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>&nbsp;</th>',"\n";
	echo '<td>';
	echo '<input type="checkbox" name="gg-allow_in" id="ipt-gg-allow_in" value="1" ', ($gg['allow_in'] ? 'checked="checked" ' : '') ,'/> <label for="ipt-gg-allow_in">', __('eingehende Anrufe zulassen') ,'</label>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>&nbsp;</th>',"\n";
	echo '<td>',"\n";
	echo __('Suchen/Ersetzen-Muster (um Pr&auml;fix wegzuschneiden)') ,'<sup>[2]</sup>:',"\n";
	echo '<div style="font-family:monospace;"',"\n";
	echo '<nobr>s/<input type="text" name="gg-in_dest_search" value="', htmlEnt($gg['in_dest_search']) ,'" size="35" maxlength="50" style="font-family:monospace;" /></nobr><br />',"\n";
	echo '<nobr>&nbsp;/<input type="text" name="gg-in_dest_replace" value="', htmlEnt($gg['in_dest_replace']) ,'" size="35" maxlength="20" style="font-family:monospace;" />/</nobr>',"\n";
	echo '</div>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
?>

</tbody>
</table>

<h3><?php echo __('Gateways'); ?></h3>
<table cellspacing="1">
<thead>
<tr>
	<th style="width:40px;"><?php echo __('Typ'); ?></th>
	<th style="width:180px;"><?php echo __('Name'); ?><sup>[3]</sup></th>
	<th style="width:160px;"><?php echo __('Title'); ?></th>
	<th style="width:35px;"><?php echo __('Abg.'); ?><sup>[4]</sup></th>
	<th style="width:180px;"><?php echo __('W&auml;hlstring'); ?><sup>[5]</sup></th>
	<th style="width:25px;">&nbsp;</th>
</tr>
</thead>
<tbody>
<?php

	# get gateways from DB
	#
	$rs = $DB->execute( 'SELECT `id`, `type`, `name`, `title`, `allow_out`, `dialstr` FROM `gates` WHERE `grp_id`='.$ggid.' ORDER BY `title`' );
	$i=0;
	while ($gw = $rs->fetchRow()) {
		echo '<tr class="', ($i%2?'even':'odd') ,'">',"\n";
		
		echo '<td>',"\n";
		echo '<select name="gw-',$gw['id'],'-type">',"\n";
		foreach ($gw_types as $type => $type_v) {
			echo '<option value="',$type,'"', ($type==$gw['type'] ? ' selected="selected"' : '') ,'>', $type_v ,'</option>',"\n";
		}
		echo '</select>',"\n";
		echo '</td>',"\n";
		
		if (subStr($gw['name'],0,3)==='gw_')
			$gw['name'] = subStr($gw['name'],3);
		echo '<td><tt>gw_</tt><input type="text" name="gw-',$gw['id'],'-name" value="', htmlEnt($gw['name']) ,'" size="20" maxlength="20" style="font-family:monospace;" /></td>',"\n";
		
		echo '<td><input type="text" name="gw-',$gw['id'],'-title" value="', htmlEnt($gw['title']) ,'" size="30" maxlength="50" /></td>',"\n";
		
		echo '<td><input type="checkbox" name="gw-',$gw['id'],'-allow_out" value="1" ', ($gw['allow_out'] ? 'checked="checked" ' : '') ,'/></td>',"\n";
		
		echo '<td><input type="text" name="gw-',$gw['id'],'-dialstr" value="', htmlEnt($gw['dialstr']) ,'" size="30" maxlength="50" style="font-family:monospace;" /></td>',"\n";
		
		echo '<td>',"\n";
		echo '<a href="', gs_url($SECTION, $MODULE), '&amp;ggid=', $ggid ,'&amp;action=gwdel&amp;gwid=', $gw['id'] ,'" title="', __('l&ouml;schen'), '"><img alt="', __('l&ouml;schen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo '</td>',"\n";
		
		echo '</tr>',"\n";
		++$i;
	}
	
	$rs = $DB->execute( 'SELECT `id`, `type`, `name`, `title`, `allow_out`, `dialstr` FROM `gates` WHERE `grp_id`='.$ggid.' ORDER BY `title`' );
	$gw = array(
		'id'       => 0,
		'type'     => '',
		'name'     => '',
		'title'    => '',
		'allow_out'=> 0,
		'dialstr'  => ''
	);
	
	echo '<tr class="', ($i%2?'even':'odd') ,'">',"\n";
	
	echo '<td>',"\n";
	echo '<select name="gw-',$gw['id'],'-type">',"\n";
	echo '<option value="" selected="selected"></option>',"\n";
	foreach ($gw_types as $type => $type_v) {
		echo '<option value="',$type,'"', ($type==$gw['type'] ? ' selected="selected"' : '') ,'>', $type_v ,'</option>',"\n";
	}
	echo '</select>',"\n";
	echo '</td>',"\n";
	
	echo '<td><tt>gw_</tt><input type="text" name="gw-',$gw['id'],'-name" value="', htmlEnt($gw['name']) ,'" size="20" maxlength="20" style="font-family:monospace;" /></td>',"\n";
	
	echo '<td><input type="text" name="gw-',$gw['id'],'-title" value="', htmlEnt($gw['title']) ,'" size="30" maxlength="50" /></td>',"\n";
	
	echo '<td><input type="checkbox" name="gw-',$gw['id'],'-allow_out" value="1" ', ($gw['allow_out'] ? 'checked="checked" ' : '') ,'/></td>',"\n";
	
	echo '<td><input type="text" name="gw-',$gw['id'],'-dialstr" value="', htmlEnt($gw['dialstr']) ,'" size="30" maxlength="50" style="font-family:monospace;" /></td>',"\n";
	
	echo '<td>&nbsp;</td>',"\n";
	
	echo '</tr>',"\n";
	
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

<?php
		echo '<p class="text"><sup>[1]</sup> ', __('Dieser Kontext mu&szlig; bei der Definition der Gateways in der sip.conf, zaptel.conf o.&auml;. angegeben werden!') ,'</p>',"\n";
		
		echo '<p class="text"><sup>[1]</sup> ', __('Geben Sie hier falls erforderlich ein PCRE-Muster an, das eventuelle Pr&auml;fixe von eingehend gew&auml;hlten Nummern wegschneidet, soda&szlig; nur noch die interne Durchwahl &uuml;brig bleibt! Beispiele:<br /> &nbsp;&nbsp;&nbsp; <tt>'.'s/^0251702//'.'</tt><br /> &nbsp;&nbsp;&nbsp; <tt>'.'s/^(((0049|0)251))702//'.'</tt><br /> &nbsp;&nbsp;&nbsp; <tt>'.'s/^(?:(?:0049|0)251)?702(.*)/$1/'.'</tt>') ,'</p>',"\n";
		
		echo '<p class="text"><sup>[3]</sup> ', __('Bei SIP-Gateways mu&szlig; der Name des Gateways dem Namen des Peers in der sip.conf entsprechen!') ,'</p>',"\n";
		
		echo '<p class="text"><sup>[4]</sup> ', __('Abgehende Anrufe auf diesem Gateway erlauben?') ,'</p>',"\n";
		
		echo '<p class="text"><sup>[5]</sup> ', __('Dieses Ziel wird mit Dial() angew&auml;hlt. Dabei werden die Platzhalter <tt>{number}</tt> und {peer} ersetzt. Beispiele: <tt>SIP/{number}@{peer}</tt>, <tt>Zap/r1/{number}</tt>, <tt>Zap/r2/{number}</tt>') ,'</p>',"\n";
	}
?>

</form>

<?php

#####################################################################


?>

