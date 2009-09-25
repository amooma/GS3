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
require_once( GS_DIR .'inc/quote_shell_arg.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
//echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo htmlEnt(__("Asterisk Manager Interface (AMI)"));
echo '</h2>', "\n";


if (! gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
	echo "This module is not implemented for cluster installations, sorry.";
	return;
}


function _gs_ami_get_enabled()
{
	$conf_files_enabled = array();
	clearStatCache();
	$files = glob( '/etc/gemeinschaft/asterisk/manager.conf.d-enabled/*.conf' );
	foreach ($files as $filename) {
		$filename_basename = baseName($filename);
		$conf_files_enabled[] = $filename_basename;
	}
	unset($files);
	return $conf_files_enabled;
}


# save
#

if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] === 'save') {
	if (array_key_exists('ami-file', $_POST) && is_array($_POST['ami-file'])) {
		
		$conf_files_enabled = _gs_ami_get_enabled();
		
		foreach ($_POST['ami-file'] as $file => $arr) {
			if (array_key_exists('available', $arr) && $arr['available'] == '1') {
				if (! preg_match('/^[a-zA-Z0-9\\-_.]+$/', $file)) continue;
				if ($file === 'ignore.conf') continue;
				
				$enabled = (array_key_exists('enabled', $arr) && $arr['enabled'] == '1');
				
				if ($enabled != in_array($file, $conf_files_enabled, true)) {
					if ($enabled) {
						$err=0; $out=array();
						@exec( 'cd '.qsa('/etc/gemeinschaft/asterisk/manager.conf.d-enabled').' 2>&1 && sudo -- ln -sn '.qsa('../manager.conf.d-available/'.$file) .' 2>&1', $out, $err );
						if ($err === 0) {
							echo '<div class="successbox">';
							echo sPrintF( htmlEnt(__("%s aktiviert.")),
								'<q><tt>'.htmlEnt($file).'</tt></q>' );
							echo '</div>' ,"\n";
						} else {
							echo '<div class="errorbox">';
							echo sPrintF( htmlEnt(__("Konnte %s nicht aktivieren.")),
								'<q><tt>'.htmlEnt($file).'</tt></q>' );
							echo '<pre style="margin:0;">', implode("\n", $out) ,'</pre>' ,"\n";
							echo '</div>' ,"\n";
						}
					} else {
						$err=0; $out=array();
						@exec( 'cd '.qsa('/etc/gemeinschaft/asterisk/manager.conf.d-enabled').' 2>&1 && sudo -- rm -f '.qsa($file) .' 2>&1', $out, $err );
						if ($err === 0) {
							echo '<div class="successbox">';
							echo sPrintF( htmlEnt(__("%s deaktiviert.")),
								'<q><tt>'.htmlEnt($file).'</tt></q>' );
							echo '</div>' ,"\n";
						} else {
							echo '<div class="errorbox">';
							echo sPrintF( htmlEnt(__("Konnte %s nicht deaktivieren.")),
								'<q><tt>'.htmlEnt($file).'</tt></q>' );
							echo '<pre style="margin:0;">', implode("\n", $out) ,'</pre>' ,"\n";
							echo '</div>' ,"\n";
						}
					}
				}
			}
		}
	}
}



# list
#

$conf_files = array();
$files = glob( '/etc/gemeinschaft/asterisk/manager.conf.d-available/*.conf' );
foreach ($files as $filename) {
	$filename_basename = baseName($filename);
	if (! preg_match('/^[a-zA-Z0-9\\-_.]+$/', $filename_basename)) continue;
	if ($filename_basename === 'ignore.conf') continue;
	
	$data = @gs_file_get_contents($filename);
	if ($data === false) {  # file not readable
		continue;
	}
	$info = array();
	$lines = preg_split('/\\r\\n?|\\n/', $data);
	unset($data);
	$info['definition'] = array();
	
	$in_info_block = false;
	$after_info_block = false;
	$key = '';
	$value = '';
	foreach ($lines as $line) {
		if (! $in_info_block) {
			if (! $after_info_block) {
				if (preg_match('/^\\s*;;;\\s*BEGIN\\s+GEMEINSCHAFT\\s+INFO/', $line)) {
					$in_info_block = true;
				}
			}
			else {
				$line = trim($line);
				if ($line != '') {
					$info['definition'][] = $line;
				}
			}
		}
		else {
			if (preg_match('/^\\s*;;;\\s*END\\s+GEMEINSCHAFT\\s+INFO/', $line)) {
				$in_info_block = false;
				$after_info_block = true;
				//break;
			}
			elseif (preg_match('/^\\s*;(?: {4,}|\\t)[ \\t]*(.*)/', $line, $m)) {
				# a continuation
				if (trim($m[1]) == '') $value.= "\n";
				else $value.= "\t". $m[1];
			}
			elseif (preg_match('/^\\s*;\\s*([A-Za-z0-9]+)\\s*:\\s*(.*)/', $line, $m)) {
				# start
				if ($key != '') {
					$value = preg_replace('/ ?\\t ?/', ' ', $value);
					$info[strToLower($key)] = $value;
					$key = '';
					$value = '';
				}
				$key = $m[1];
				$value = $m[2];
			}
			/*
			elseif (preg_match('/^\\s*;;(?:[^;]|$)/', $line)) {
				# a "comment"
				continue;
			}
			*/
		}
	}
	if ($key != '') {
		$value = preg_replace('/ ?\\t ?/', ' ', $value);
		$info[strToLower($key)] = $value;
		$key = '';
		$value = '';
	}
	
	unset($lines);
	$conf_files[$filename_basename] = $info;
	unset($info);
}
unset($files);


$conf_files_enabled = _gs_ami_get_enabled();

?>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="save" />
<div class="r">
	<input type="submit" value="<?php echo htmlEnt("Speichern"); ?>" />
</div>
<table cellspacing="1">
<thead>
<tr>
	<th><?php echo htmlEnt(__("Aktiviert?")); ?></th>
	<th><?php echo htmlEnt(__("Dateiname")); ?>, <?php echo htmlEnt(__("Beschreibung")); ?></th>
	<th><?php echo htmlEnt(__("Inhalt")); ?> (<?php echo htmlEnt(__("Format")); ?>: manager.conf)</th>
</tr>
</thead>
<tbody>
<?php
foreach ($conf_files as $file => $conf_file) {
?>
<tr>
	<td>
		<input type="hidden" name="ami-file[<?php echo htmlEnt($file); ?>][available]" value="1" />
		<input type="checkbox" name="ami-file[<?php echo htmlEnt($file); ?>][enabled]" value="1"<?php
			if (in_array($file, $conf_files_enabled, true)) echo ' checked="checked"';
		?> />
	</td>
	<td>
		<p class="s"><tt><?php echo htmlEnt($file); ?></tt></p>
<?php
		if (array_key_exists('title', $conf_file)) {
			echo '<p><b>', htmlEnt(trim( $conf_file['title'])) ,'</b></p>' ,"\n";
		}
		
		if (array_key_exists('description', $conf_file)) {
			$descr_html = htmlEnt( trim( $conf_file['description'] ));
			$descr_html = preg_replace('/\\b(?:https?):\\/\\/(?:[a-z0-9\\-_.]+)(?:(?::[0-9]+)?)(?:[\\/a-z0-9\\-_#%]*)\\b/i', '<a href="$0" target="_blank" rel="nofollow">$0</a>', $descr_html);
			$paras = preg_split('/(?:\\r\\n?|\\n) */', $descr_html);
			unset($descr_html);
			foreach ($paras as $para) {
				echo "\t\t", '<p class="s" style="margin:0; padding:1px 1px 0.5em 1px; line-height:1.2em; max-width:40em;">';
				echo $para;
				echo '</p>' ,"\n";
			}
			unset($paras);
		} else {
			echo '-';
		}
?>
	</td>
	<td>
		<pre class="s" style="margin:0;"><?php
			foreach ($conf_file['definition'] as $line) {
				$line_html = htmlEnt($line);
				$line_html = preg_replace('/^(\\[)([a-zA-Z0-9\\-_]+)(\\])(.*)/', '$1<b>$2</b>$3$4', $line_html);
				$line_html = preg_replace('/^(secret\\s*=\\s*)([a-zA-Z0-9\\-_]*)(.*)/', '$1<b>$2</b>$3', $line_html);
				$line_html = preg_replace('/(;.*)/', '<span class="gray s">$1</span>', $line_html);
				echo $line_html ,"\n";
			}
		?></pre>
	</td>
</tr>
<?php
}
?>
</tbody>
</table>

<div class="r">
	<input type="submit" value="<?php echo htmlEnt("Speichern"); ?>" />
</div>
</form>

