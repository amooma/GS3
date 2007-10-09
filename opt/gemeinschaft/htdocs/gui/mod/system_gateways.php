<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision:2656 $
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
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


$action = @$_REQUEST['action'];
if ($action != ''
&&  $action != 'zapata'
&&  $action != 'zapata_save'
) {
	$action = '';
}


echo '<div class="fr">',"\n";
if ($action == '') {
	echo '<a href="', gs_url($SECTION, $MODULE) ,'&amp;action=zapata">Zapata conf</a><br />';
}
elseif ($action == 'zapata') {
	echo '<a href="', gs_url($SECTION, $MODULE) ,'&amp;action=">Gateways</a><br />';
}
echo '</div>',"\n";


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


#####################################################################
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
#####################################################################




#####################################################################
if ($action == '') {
	
	# get gateway groups from DB
	#
	$rs = $DB->execute( 'SELECT `id`, `title`, `type` FROM `gate_grps` ORDER BY `title`' );
	$gate_grps = array();
	while ($r = $rs->fetchRow())
		$gate_grps[] = $r;
	
	foreach ($gate_grps as $gate_grp) {
?>

<table cellspacing="1" style="width:600px;">
<tbody>

<?php
	echo '<tr>',"\n";
	
	echo '<th style="width:18px;">';
	//echo '<img alt=" " src="', GS_URL_PATH ,'crystal-svg/16/fld/network.png" />';
	echo '</th>',"\n";
	
	echo '<th colspan="2" style="width:175px;">';
	echo htmlEnt($gate_grp['title']);
	echo '</th>',"\n";
	
	echo '<th colspan="2" style="width:370px;">';
	echo '<span style="font-weight:normal">(', 'strategy: ';
	switch (strToLower($gate_grp['type'])) {
		case 'balance': echo 'load balance'; break;
		default       : echo htmlEnt($gate_grp['type']);
	}
	echo ')</span>';
	echo '</th>',"\n";
	
	echo '</tr>',"\n";
	
	$rs = $DB->execute( 'SELECT `id`, `type`, `name`, `title`, `allow_out`, `allow_in`, `dialstr` FROM `gates` WHERE `grp_id`='. $gate_grp['id'] );
	$i=0;
	while ($gw = $rs->fetchRow()) {
		echo '<tr class="', ($i%2?'even':'odd') ,'">',"\n";
		
		echo '<td style="width:18px;">', '<img alt=" " src="', GS_URL_PATH ,'crystal-svg/16/fld/socket.png" />' ,'</td>',"\n";
		
		echo '<td style="width:30px;">';
		switch (strToLower($gw['type'])) {
			case 'sip': echo 'SIP'; break;
			case 'zap': echo 'Zap'; break;
			default   : echo htmlEnt($gw['type']);
		}
		echo '</td>',"\n";
		
		echo '<td style="width:130px;">', htmlEnt($gw['title']) ,'</td>',"\n";
		
		echo '<td style="width:170px;"><tt>', htmlEnt($gw['name']) ,'</tt></td>',"\n";
		
		$dialstr_html = htmlEnt($gw['dialstr']);
		$dialstr_html = preg_replace('/\{(number|peer)\}/', '<span style="color:#0b0;">{$1}</span>', $dialstr_html);
		echo '<td style="width:170px;"><tt>', $dialstr_html ,'</tt></td>',"\n";
		
		echo '</tr>',"\n";
		++$i;
	}
	if ($i==0) {
		echo '<tr>',"\n";
		echo '<td>&nbsp;</td>',"\n";
		echo '<td colspan="4">-</td>',"\n";
		echo '</tr>',"\n";
	}
?>

</tbody>
</table>
<br />

<?php
	}
}
#####################################################################


?>

