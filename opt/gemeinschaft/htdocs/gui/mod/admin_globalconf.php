<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 5708 $
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

include_once( GS_DIR .'inc/gs-fns/gs_gen_globalconf.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$user_id      = (int)@$_SESSION['sudo_user']['info']['id'];
$save = (int)trim(@$_REQUEST['save'   ]);



#update Database entrys
if ($save) {
	# get current configuration
	$config= $DB->execute('SELECT * FROM `config_options` WHERE `path` IS NOT NULL ORDER BY `path`');
	if (@$config) {
		while ($r = @$config->fetchRow()) {
			$value = '0'; //is boolen or it will be overwritten by the next two lines
			if(array_key_exists($r['id'], $_REQUEST))
				$value = $_REQUEST[$r['id']];
			if( $r['type'] == "BOOL" ) {
				if ($value != '0') 
					$value = '1';
				$DB->execute("UPDATE `config_options` SET `value`='".$DB->escape($value)."' WHERE `id` = ".$r['id']);
			} else if ($r['type'] == "IP") {
				$ip1 = $_REQUEST[$r['id']."-0"];
				$ip2 = $_REQUEST[$r['id']."-1"];
				$ip3 = $_REQUEST[$r['id']."-2"];
				$ip4 = $_REQUEST[$r['id']."-3"];
				$DB->execute("UPDATE `config_options` SET `value`='".$DB->escape("$ip1.$ip2.$ip3.$ip4")."' WHERE `id` = ".$r['id']);
			} else {
				$DB->execute("UPDATE `config_options` SET `value`='".$DB->escape($value)."' WHERE `id` = ".$DB->escape($r['id']));
			}
		}
	}

	//generate the var-dump-file
	$ok = gs_generate_autoconf_php_hosts();
	if( isGsError($ok)) {
		echo '<div class="errorbox">';
		echo $ok->getMsg();
		echo '</div>',"\n";
		}
}

# get current configuration
$config= $DB->execute('SELECT * FROM `config_options` WHERE `path` IS NOT NULL ORDER BY `path`');


echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
echo '<input type="hidden" name="save" value="1" />', "\n";
echo gs_form_hidden($SECTION, $MODULE), "\n";

$cur_path = "";
if (@$config) {
	while ($r = @$config->fetchRow()) {
		//wenn eine neue Sektion geöffnet wird, dann die Überschrift und den Tabellenkopf schreiben:
		if (!array_key_exists($r['path'], $path_section_names ))
			echo "Error: ".$r['path']." \n";
		if ($cur_path != $r['path'] && array_key_exists($r['path'], $path_section_names )) {
			if($r['path'] != '/') //wenn nicht die erste sektion ist, dann die Tabelle der vorherigen schliessen
				echo "</tbody></table><br>";
			echo "<h2>". $path_section_names[$r['path']] ."</h2>"."\n";
			$cur_path = $r['path'];
			
			echo '<table cellspacing="1" class="phonebook">'."\n";
			echo '<thead>'."\n";
			echo '<tr>'."\n";
			echo '<th style="width:400px;" class="nobr">'. __('Option' ).'</th>'."\n";
			echo '<th style="width:150px;" class="nobr">'. __('Wert' ).'</th>'."\n";
			echo '<th style="width:500px;" class="nobr">'. __('Beschreibung' ).'</th>'."\n";
			echo '</tr>'."\n";
			echo '</thead>'."\n";
			echo '<tbody>'."\n";
		}

		#parameter name
		echo '<tr>', "\n";
		echo '<td>';
		//Swap ident with short description - if exists
		if (array_key_exists( $r['ident'], $option_short_descr) && $option_short_descr[$r['ident']] != 'X') 
			echo htmlEnt($option_short_descr[$r['ident']]);
		else
			echo htmlEnt($r['ident']); 
		echo '</td>';

		#parameter value
		echo '<td>';
		$value="";
		if($r['value'] == "") 
			$value = $r['default'];
		else
			$value = $r['value'];

		if ( $r['type'] == "SELECT") {
			echo '<select name="'.$r['id'].'">' ,"\n";
			$possibilities = split('[|]',$r['possibilities']);
			foreach ($possibilities as $item) {
				echo '<option value="', htmlEnt($item) ,'"';
				if ($item == $value) echo ' selected="selected"';
				echo '>', htmlEnt($item) ,'</option>' ,"\n";
			}
			echo '</select>';
		}
		else if ( $r['type'] == "BOOL") {
			echo '<input type="checkbox" name="'.htmlEnt($r['id']).'" value="'.htmlEnt($r['id']).'" ';
			if ($value)
				echo 'checked="checked"';
			echo ">\n";
		}
		else if ( $r['type'] == "INT" || $r['type'] == "TEXT" || $r['type'] == "COMMA_SEP_" ) {
			echo '<input type="text" name="'.htmlEnt($r['id']).'" value="'.htmlEnt($value).'" >';
			echo "\n";
		}
		else if ( $r['type'] == "IP" ) {
			$ip = split('[.]', $value);
			echo '<input type="text" size=2 maxlength=3 name="'.htmlEnt($r['id']."-0").'" value="'.htmlEnt($ip[0]).'" >.';
			echo '<input type="text" size=2 maxlength=3 name="'.htmlEnt($r['id']."-1").'" value="'.htmlEnt($ip[1]).'" >.';
			echo '<input type="text" size=2 maxlength=3 name="'.htmlEnt($r['id']."-2").'" value="'.htmlEnt($ip[2]).'" >.';
			echo '<input type="text" size=2 maxlength=3 name="'.htmlEnt($r['id']."-3").'" value="'.htmlEnt($ip[3]).'" >';
			echo "\n";
		}
		echo '</td>';
		#long description
		echo '<td>';
		if (array_key_exists( $r['ident'], $option_long_descr) && $option_long_descr[$r['ident']] != 'X') 
			echo htmlEnt($option_long_descr[$r['ident']]);
		echo '</td>';


		echo '</tr>', "\n";
	
	}
	echo "</tbody></table><br>";
}
echo '<button type="submit" value="save" title="', __('Speichern'), '" class="plain">';
echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" />';
echo '</button>' ,"\n";

?>
</form>
