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

echo "<br />\n";



/*
$err=0; $out=array();
@exec( 'sudo df --block-size=1000000 | grep \'^[ ]*'..'/dev/\' | grep \'% [ ]*'..'/[ ]*$\' 2>>/dev/null', $out, $err );
if ($err !== 0) {
	echo 'Error.';
	return;
}
if (! preg_match('/ +([0-9]+)[^0-9]+([0-9]+)[^0-9]+([0-9]+)/', @$out[0], $m)) {
	echo 'Error.';
	return;
}
$sys_blocks_mb_total = (int)$m[1];
//$sys_blocks_mb_used  = (int)$m[2];
//$sys_blocks_mb_avail = (int)$m[3];
*/


$err=0; $out=array();
@exec( 'sudo df --block-size=1000000 | grep \'^[ ]*/dev/\' | grep \'% [ ]*/mnt/userdata[ ]*$\' 2>>/dev/null', $out, $err );
if ($err===0 && preg_match('/ +([0-9]+)[^0-9]+([0-9]+)[^0-9]+([0-9]+)/', @$out[0], $m)) {
	$userdata_blocks_mb_total = (int)$m[1];
	$userdata_blocks_mb_used  = (int)$m[2];
	$userdata_blocks_mb_avail = (int)$m[3];
} else {
	$userdata_blocks_mb_total = null;
	$userdata_blocks_mb_used  = null;
	$userdata_blocks_mb_avail = null;
}


$err=0; $out=array();
@exec( 'cd /mnt/userdata/gpbx/ && LANG=C sudo du -sc --block-size=1000000 db logs vals | grep total | tail -n 1 2>>/dev/null', $out, $err );
if ($err===0 && preg_match('/^\s*([0-9]+)/', @$out[0], $m)) {
	$db_blocks_mb_used = (int)$m[1];
} else {
	$db_blocks_mb_used = null;
}


$err=0; $out=array();
@exec( 'LANG=C sudo du -s --block-size=1000000  /mnt/userdata/upgrades 2>>/dev/null', $out, $err );
if ($err===0 && preg_match('/^\s*([0-9]+)/', @$out[0], $m)) {
	$upgrades_blocks_mb_used = (int)$m[1];
} else {
	$upgrades_blocks_mb_used = null;
}


$err=0; $out=array();
@exec( 'LANG=C sudo du -s --block-size=1000000  /mnt/userdata/user/voicemail 2>>/dev/null', $out, $err );
if ($err===0 && preg_match('/^\s*([0-9]+)/', @$out[0], $m)) {
	$vm_blocks_mb_used = (int)$m[1];
} else {
	$vm_blocks_mb_used = null;
}


$err=0; $out=array();
@exec( 'LANG=C sudo du -s --block-size=1000000  /mnt/userdata/user/voicemail-ann 2>>/dev/null', $out, $err );
if ($err===0 && preg_match('/^\s*([0-9]+)/', @$out[0], $m)) {
	$vmann_blocks_mb_used = (int)$m[1];
} else {
	$vmann_blocks_mb_used = null;
}


$err=0; $out=array();
@exec( 'LANG=C sudo du -s --block-size=1000000  /mnt/userdata/user/ringtones 2>>/dev/null', $out, $err );
if ($err===0 && preg_match('/^\s*([0-9]+)/', @$out[0], $m)) {
	$ringtones_blocks_mb_used = (int)$m[1];
} else {
	$ringtones_blocks_mb_used = null;
}




?>

<h3><?php echo __('Daten-Partition'); ?></h3>
<table cellspacing="1" style="margin-left:20px;">
<tbody>

<tr>
	<th colspan="2"><?php echo __('Belegt'); ?></th>
	<th class="r" width="65"><?php
		if ($userdata_blocks_mb_used)
			echo $userdata_blocks_mb_used ,'&nbsp;MB';
		else
			echo '?';
	?></th>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php echo __('Datenbank etc.'); ?></td>
	<td class="r"><?php
		if ($db_blocks_mb_used)
			echo $db_blocks_mb_used ,'&nbsp;MB';
		else
			echo '?';
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php echo __('Voicemail-Nachrichten'); ?></td>
	<td class="r"><?php
		if ($vm_blocks_mb_used)
			echo $vm_blocks_mb_used ,'&nbsp;MB';
		else
			echo '?';
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php echo __('Voicemail-Ansagen'); ?></td>
	<td class="r"><?php
		if ($vmann_blocks_mb_used)
			echo $vmann_blocks_mb_used ,'&nbsp;MB';
		else
			echo '?';
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php echo __('Klingelt&ouml;ne'); ?></td>
	<td class="r"><?php
		if ($ringtones_blocks_mb_used)
			echo $ringtones_blocks_mb_used ,'&nbsp;MB';
		else
			echo '?';
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php echo __('Upgrades'); ?></td>
	<td class="r"><?php
		if ($upgrades_blocks_mb_used)
			echo $upgrades_blocks_mb_used ,'&nbsp;MB';
		else
			echo '?';
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td coslpan="2">...</td>
	<td class="r">&nbsp;</td>
</tr>

<tr>
	<th colspan="2"><?php echo __('Freier Speicherplatz'); ?></th>
	<th class="r"><?php
		if ($userdata_blocks_mb_avail)
			echo $userdata_blocks_mb_avail ,'&nbsp;MB';
		else
			echo '?';
	?></th>
</tr>


</tbody>
</table>
