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
include_once( GS_DIR .'inc/gs-fns/gs_ringtones_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_ringtone_set.php' );


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$sources = array(
	'internal' => __('intern'),
	'external' => __('extern')
);

$audio_exts = array( 'aif', 'aiff', 'wav', 'au', 'al', 'alaw', 'la',
                     'ul', 'ulaw', 'lu', 'gsm', 'cdr', 'mp3', 'ogg' );

$errMsgs = array();

if (@$_REQUEST['action']=='save') {
	
	/*
	echo "<pre>";
	print_r($_REQUEST);
	print_r($_FILES);
	echo "</pre>";
	*/
	
	foreach ($sources as $src => $srcv) {
		$bellcore = (int)@$_REQUEST[$src .'-bellcore'];
		
		if (@$_REQUEST[$src .'-file-change'] == 'new')
			$change_file = true;
		elseif (@$_REQUEST[$src .'-file-change'] == 'off')
			$change_file = true;
		else  # "keep"
			$change_file = false;
		
		$ul_filename = '';
		
		if ($change_file && @$_REQUEST[$src .'-file-change'] == 'new') {
			if (! isSet($_FILES[$src .'-file'])) {
				$errMsgs[] = sprintf(__('Datei-Upload f&uuml;r %s fehlgeschlagen.'), $sources[$src]);
				$change_file = false;
			} else {
				switch (@$_FILES[$src .'-file']['error']) {
					case UPLOAD_ERR_OK:
						/*
						if (strToLower(subStr(@$_FILES[$src .'-file']['type'],0,6)) != 'audio/') {
							$errMsgs[] = 'Datei f&uuml;r '. $sources[$src] .' keine Audio-Datei.';
							$change_file = false;
						} else {
						*/
						preg_match('/\.([a-z\d]+)$/', strToLower( @$_FILES[$src .'-file']['name'] ), $m);
						$ext = @$m[1];
						if (! in_array($ext, $audio_exts, true)) {
							$errMsgs[] = sprintf(__('Datei f&uuml;r %s keine Audio-Datei'),  $sources[$src]) .' ('. $ext .').' .'<br />'. __('Erlaubte Formate') .': '. implode(', ', $audio_exts);
							$change_file = false;
						} elseif (! is_uploaded_file( @$_FILES[$src .'-file']['tmp_name'] )) {
							$errMsgs[] = __('Zugriffsverletzung!');
							$change_file = false;
						} else {
							$ul_filename = @$_FILES[$src .'-file']['tmp_name'] .'.'. $ext;
							$ok = @rename( @$_FILES[$src .'-file']['tmp_name'], $ul_filename );
							if (! $ok) {
								$errMsgs[] = sprintf(__('Fehler beim Upload f&uuml;r %s.'), $sources[$src]);
								$change_file = false;
							} else {
								@chmod($ul_filename, 0666);
								# upload ok, leave $change_file == true
							}
						}
						break;
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$errMsgs[] = sprintf(__('Datei f&uuml;r %s zu gro&szlig;.'), $sources[$src]);
						$change_file = false;
						break;
					case UPLOAD_ERR_PARTIAL:
					case UPLOAD_ERR_NO_FILE:
					default:
						$errMsgs[] = sprintf(__('Datei-Upload f&uuml;r %s fehlgeschlagen.'), $sources[$src]);
						$change_file = false;
						break;
				}
			}
		}
		$ok = gs_ringtone_set( $_SESSION['sudo_user']['name'], $src, $bellcore, $change_file, ($change_file ? $ul_filename : null) );
		if (is_file($ul_filename))
			@unlink($ul_filename);
		if (isGsError($ok))
			$errMsgs[] = $ok->getMsg();
		elseif (! $ok)
			$errMsgs[] = __('Fehler beim Setzen des eigenen Klingeltons.');
		
	}
	
}




$ringtones = gs_ringtones_get( $_SESSION['sudo_user']['name'] );
if (isGsError($ringtones)) {
	echo __('Fehler beim Abfragen.'), '<br />', $ringtones->getMsg();
	die();
}

//$cur_phone_type = $DB->executeGetOne( 'SELECT `type` FROM `phones` WHERE `user_id`='. (int)@$_SESSION['sudo_user']['info']['id'] );


?>

<div style="max-width:600px;">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/info.png" class="fl" />
	<p style="margin-left:22px;">
		<?php echo __('Bitte beachten Sie, da&szlig; die unterst&uuml;tzten Klingelt&ouml;ne stark von dem Endger&auml;t abh&auml;ngig sind, auf dem Sie sich anmelden. Ggf. wird also ein anderer als der hier eingestellte Klingelton gespielt.'); ?>
		<sup>[1]</sup>
		<sup>[2]</sup>
	</p>
</div>


<?php
if (is_array($errMsgs) && count($errMsgs) > 0) {
?>
<div style="max-width:600px;">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/app/important.png" class="fl" />
	<p style="margin-left:22px;">
		<?php echo implode('<br />', $errMsgs); ?>
	</p>
</div>
<?php
}
?>

<form method="post" action="<?php echo GS_URL_PATH; ?>" enctype="multipart/form-data">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="save" />

<table cellspacing="1">
<thead>
<tr>
	<th><?php echo __('Von'); ?></th>
	<th>Bellcore</th>
	<th><?php echo __('Audio-Datei'); ?></th>
</tr>
</thead>
<tbody>
<?php
foreach ($sources as $source => $source_v) {
?>
<tr>
	<td style="width:55px;"><?php echo $source_v; ?></td>
	<td style="width:115px;">
		<select name="<?php echo $source; ?>-bellcore">
<?php
for ($i=1; $i<=11; ++$i) {
	if ($i <= 10) {
		$dr_i = $i;
		$dr_v = 'Bellcore '. $i;
	} elseif ($i == 11) {
		$dr_i = 0;
		$dr_v = __('Lautlos');
	} /*elseif ($i == 12) {
		$dr_i = -1;
		$dr_v = 'eigene Datei';
	}*/
	$sel = ($dr_i == @$ringtones[$source]['bellcore']) ? ' selected="selected"' : '';
	echo '<option value="', $dr_i, '"', $sel, '>', $dr_v, '</option>', "\n";
}
?>
		</select>
	</td>
	<td style="width:430px;">
		<input type="radio" name="<?php echo $source; ?>-file-change" value="off" id="<?php echo $source; ?>-file-change-off" <?php if (! @$ringtones[$source]['file']) echo 'checked="checked" '; ?>/>
			<label for="<?php echo $source; ?>-file-change-off"><?php echo __('keine'); ?></label><br />
<?php
if (@$ringtones[$source]['file']) {
?>
		<input type="radio" name="<?php echo $source; ?>-file-change" value="keep" id="<?php echo $source; ?>-file-change-keep" checked="checked" />
			<label for="<?php echo $source; ?>-file-change-keep"><?php echo __('eigene beibehalten'); ?></label><br />
<?php
}
?>
		<input type="radio" name="<?php echo $source; ?>-file-change" value="new" id="<?php echo $source; ?>-file-change-new" />
		<input type="file" name="<?php echo $source; ?>-file" size="45" style="font-size:10px;" accept="audio/*" onchange="var r=document.getElementById('<?php echo $source; ?>-file-change-new'); if(r)r.click(); return true;" />
	</td>
</tr>
<?php
}
?>

<tr>
	<td colspan="3" class="quickchars r">
		<br />
		<br />
		<button type="submit">
			<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			<?php echo __('Speichern'); ?>
		</button>
	</td>
</tr>
</tbody>
</table>

</form>


<br />
<?php

//if (strToLower(subStr($cur_phone_type,0,4)) === 'snom') {
?>
<p class="small" style="max-width:48em;">
	<sup>[1]</sup>
	<?php echo __('Das Snom unterst&uuml;tzt Ringer 1-5 und lautlos.<br />Die L&auml;nge der eigenen Klingelt&ouml;ne wird auf wenige Sekunden begrenzt. F&uuml;r den eigenen Klingelton k&ouml;nnen Sie Audio-Dateien in den Dateiformaten "wav", "mp3" oder "ogg" nutzen.'); ?>
</p>
<?php
//}
//elseif (strToLower(subStr($cur_phone_type,0,7)) === 'siemens') {
?>
<p class="small" style="max-width:48em;">
	<sup>[2]</sup>
	<?php echo htmlEnt(__("Das Siemens OpenStage kann in der derzeitigen Firmware noch nicht zwischen intern und extern unterscheiden. Es wird immer die Ruftonmelodie f\xC3\xBCr intern verwendet!")); ?>
</p>
<?php
//}

?>
