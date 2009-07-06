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
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$action = @$_REQUEST['action'];
if (! in_array($action, array('', 'upload', 'preview', 'import'), true))
	$action = '';


/*
if ($action == ''
&&  @array_key_exists('pb-csv-file', @$_SESSION['sudo_user'])
&&  is_file(@$_SESSION['sudo_user']['pb-csv-file']) )
{
	$action = 'preview';
}
*/


if ($action === 'upload') {
	
	# delete old files:
	$err=0; $out=array();
	@exec( 'find /tmp/ -maxdepth 1 -type f -name \'gs-pb-prv-csv-import.*\' -amin +60 -delete 1>>/dev/null 2>>/dev/null &', $out, $err );
	
	if (! @array_key_exists('pb_csv_file', @$_FILES)) {
		echo 'Error.';
	} else {
		$finfo = $_FILES['pb_csv_file'];
		if (@$finfo['error'] != 0) {
			echo 'Error.';
			// we could do some better error handling here
		} elseif (@$finfo['size'] < 1) {
			echo 'File is empty.';
		}
		elseif ( @$finfo['type'] != 'text/comma-separated-values'
		     &&  @$finfo['type'] != 'text/tab-separated-values'
		     &&  @$finfo['type'] != 'text/plain'
		     &&  strToLower(subStr(@$finfo['name'],-4)) != '.csv'
		     &&  strToLower(subStr(@$finfo['name'],-4)) != '.tsv'
		     &&  strToLower(subStr(@$finfo['name'],-4)) != '.txt' )
		{
			echo 'Unrecognized Format.';
		} else {
			$tmpfile = @$finfo['tmp_name'];
			if (! is_file($tmpfile) || ! is_uploaded_file($tmpfile)) {
				echo 'Error.';
			} else {
				$filename = '/tmp/gs-pb-prv-csv-import.'. @$_SESSION['sudo_user']['name'] .'.'. time();
				if (! @move_uploaded_file($tmpfile, $filename)) {
					echo 'Error.';
				} else {
					@$_SESSION['sudo_user']['pb-csv-file'] = $filename;
					$action = 'preview';
				}
			}
		}
	}
	
}


if ($action === 'preview' || $action === 'import') {
	
	if (! @array_key_exists('pb-csv-file', @$_SESSION['sudo_user'])
	||  ! is_file(@$_SESSION['sudo_user']['pb-csv-file']) )
	{
		$action = '';
	} else {
		$file = @$_SESSION['sudo_user']['pb-csv-file'];
		$fh = @fOpen($file, 'rb');
		if (! $fh) {
			echo 'Could not read file.';
		} else {
			
			if (@array_key_exists('sep' , @$_REQUEST)
			&&  @array_key_exists('encl', @$_REQUEST)
			&&  @array_key_exists('enc' , @$_REQUEST)
			) {
				$sep  = @$_REQUEST['sep' ];
				$encl = @$_REQUEST['encl'];
				$enc  = @$_REQUEST['enc' ];
			} else {
				# try to guess separator
				$line = @fGets($fh);
				@rewind($fh);
				
				$cnt = array();
				$cnt['s'] = (int)preg_match_all('/;/'  , $line, $m);
				$cnt['c'] = (int)preg_match_all('/,/'  , $line, $m);
				$cnt['t'] = (int)preg_match_all('/\\t/', $line, $m);
				if     ($cnt['t'] > max($cnt['s'],$cnt['c'])) $sep = 't';
				elseif ($cnt['s'] > max($cnt['c'],$cnt['t'])) $sep = 's';
				elseif ($cnt['c'] > max($cnt['s'],$cnt['t'])) $sep = 'c';
				else                                          $sep = 't';
				
				unset($line);
				unset($m);
				unset($cnt);
			}
			if (! in_array($sep, array('s','c','t'), true))
				$sep = 's';
			if (! in_array($encl, array('q','s','n'), true))
				$encl = 'q';
			if (! in_array($enc, array('utf8', 'iso88591'), true))
				$enc = 'utf8';
			switch ($sep) {
				case 's': $separator = ';' ; break;
				case 'c': $separator = ',' ; break;
				case 't': $separator = "\t"; break;
				default : $separator = ';' ;
			}
			switch ($encl) {
				case 'q': $enclosure = '"' ; break;
				case 's': $enclosure = '\''; break;
				case 'n': $enclosure = ''  ; break;
				default : $enclosure = '"' ;
			}
			
			$col_ln = (int)@$_REQUEST['col_ln'];
			$col_fn = (int)@$_REQUEST['col_fn'];
			$col_nr = (int)@$_REQUEST['col_nr'];
			if ($col_ln < 1) $col_ln = 1;
			if ($col_fn < 1) $col_fn = 1;
			if ($col_nr < 1) $col_nr = 1;
			while ($col_fn === $col_ln || $col_fn === $col_nr) ++$col_fn;
			while ($col_nr === $col_ln || $col_nr === $col_fn) ++$col_nr;
			
			$skip_1st = (bool)@$_REQUEST['skip_1st'];
			
			$records = array();
			$i=0;
			while (true) {
				$csv = ($enclosure != '')
					? @fGetCsv($fh, 8000, $separator, $enclosure)
					: @fGetCsv($fh, 8000, $separator);
				if (! is_array($csv)) break;
				if ($i===0 && $skip_1st) {++$i; continue;}
				
				$ln = trim(@$csv[$col_ln-1]);
				$fn = trim(@$csv[$col_fn-1]);
				$nr = trim(@$csv[$col_nr-1]);
				if ($enc==='iso88591') {
					$ln = utf8_encode($ln);
					$fn = utf8_encode($fn);
					$nr = utf8_encode($nr);
				}
				if ($ln==='' && $fn==='' && $nr==='') continue;
				
				$nrimp = preg_replace('/\(0\)/S'    , ' ', $nr);
				$nrimp = preg_replace('/[^0-9+*]+/S', '-', $nrimp);
				$nrimp = preg_replace('/(?!^)[+]/S' , '--', $nrimp);
				$nrimp = preg_replace('/[\s\-]+/S'  , '-', $nrimp);
				$nrimp = trim($nrimp);
				
				if ($action !== 'import') {
					$records[] = array(
						'ln'   => $ln,
						'fn'   => $fn,
						'nr'   => $nr,
						'nrimp'=> $nrimp
					);
				} else {
					$records[] = array(
						'ln'   => $ln,
						'fn'   => $fn,
						'nrimp'=> $nrimp
					);
				}
				++$i;
			}
			
		}
	}
	
}


if ($action === 'preview') {
	
	if (! @is_array(@$records) || count($records)<1) {
		echo 'Error.';
	} else {
		
		echo '<form method="post" action="', GS_URL_PATH, '" enctype="multipart/form-data">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<input type="hidden" name="action" value="preview" />', "\n";
		
		echo '<table cellspacing="1" class="phonebook">', "\n";
		echo '<tbody>', "\n";
		
		echo '<tr>', "\n";
		echo '<td>', __('Begrenzer') ,':</td>', "\n";
		echo '<td>', "\n";
		echo '<nobr><input type="radio" name="sep" id="ipt-sep-s" value="s" ', ($sep==='s' ? 'checked="checked" ' : '') ,'/>',
			 '<label for="ipt-sep-s">', __('Semikolon') ,' (<code>;</code>)</label></nobr> ', "\n";
		echo '<nobr><input type="radio" name="sep" id="ipt-sep-c" value="c" ', ($sep==='c' ? 'checked="checked" ' : '') ,'/>',
			 '<label for="ipt-sep-c">', __('Komma') ,' (<code>,</code>)</label></nobr> ', "\n";
		echo '<nobr><input type="radio" name="sep" id="ipt-sep-t" value="t" ', ($sep==='t' ? 'checked="checked" ' : '') ,'/>',
			 '<label for="ipt-sep-t">', __('Tabulator') ,' (<code>\\t</code>)</label></nobr> ', "\n";
		echo '</td>', "\n";
		echo '</tr>', "\n";
		
		echo '<tr>', "\n";
		echo '<td>', __('Umschlie&szlig;ung') ,':</td>', "\n";
		echo '<td>', "\n";
		echo '<nobr><input type="radio" name="encl" id="ipt-encl-q" value="q" ', ($encl==='q' ? 'checked="checked" ' : '') ,'/>',
			 '<label for="ipt-encl-q">', __('Anf&uuml;hrungszeichen') ,' (<code>&quot;</code>)</label></nobr> ', "\n";
		echo '<nobr><input type="radio" name="encl" id="ipt-encl-s" value="s" ', ($encl==='s' ? 'checked="checked" ' : '') ,'/>',
			 '<label for="ipt-encl-s">', __('Apostroph') ,' (<code>&apos;</code>)</label></nobr> ', "\n";
		echo '<nobr><input type="radio" name="encl" id="ipt-encl-n" value="n" ', ($encl==='n' ? 'checked="checked" ' : '') ,'/>',
			 '<label for="ipt-encl-n">', __('nichts') ,'</label></nobr> ', "\n";
		echo '</td>', "\n";
		echo '</tr>', "\n";
		
		echo '<tr>', "\n";
		echo '<td>', __('Zeichenkodierung') ,':</td>', "\n";
		echo '<td>', "\n";
		echo '<nobr><input type="radio" name="enc" id="ipt-enc-utf8" value="utf8" ', ($enc==='utf8' ? 'checked="checked" ' : '') ,'/>',
			 '<label for="ipt-enc-utf8">UTF-8</label></nobr> ', "\n";
		echo '<nobr><input type="radio" name="enc" id="ipt-enc-iso88591" value="iso88591" ', ($enc==='iso88591' ? 'checked="checked" ' : '') ,'/>',
			 '<label for="ipt-enc-iso88591">ISO-8859-1</label></nobr> ', "\n";
		echo '</td>', "\n";
		echo '</tr>', "\n";
		
		echo '<tr>', "\n";
		echo '<td>', __('Spalten') ,':</td>', "\n";
		echo '<td>', "\n";
		echo '<nobr><label for="ipt-col_ln">', __('Nachname'), ':</label>', "\n",
			 '<input type="text" name="col_ln" id="ipt-col_ln" size="2" maxlength="2" value="', $col_ln ,'"></nobr> &nbsp;', "\n";
		echo '<nobr><label for="ipt-col_fn">', __('Vorname'), ':</label>', "\n",
			 '<input type="text" name="col_fn" id="ipt-col_fn" size="2" maxlength="2" value="', $col_fn ,'"></nobr> &nbsp;', "\n";
		echo '<nobr><label for="ipt-col_nr">', __('Telefonnummer'), ':</label>', "\n",
			 '<input type="text" name="col_nr" id="ipt-col_nr" size="2" maxlength="2" value="', $col_nr ,'"></nobr> &nbsp;', "\n";
		echo '</td>', "\n";
		echo '</tr>', "\n";
		
		echo '<tr>', "\n";
		echo '<td>', __('Kopfzeile') ,':</td>', "\n";
		echo '<td>', "\n";
		echo '<input type="checkbox" name="skip_1st" id="ipt-skip_1st" value="1" ', ($skip_1st ? 'checked="checked" ' : '') ,'/>', "\n",
			 '<label for="ipt-skip_1st">', __('erste Zeile auslassen') ,'</label>', "\n";
		echo '</td>', "\n";
		echo '</tr>', "\n";
		
		echo '</tbody>', "\n";
		echo '</table>', "\n";
		
		echo '<input type="submit" value="', __('Vorschau') ,'" /><br />', "\n";
		echo '</form>', "\n";
		echo '<br />', "\n";
		
		
		
		echo '<p class="text">', __('Bitte kontrollieren Sie, ob die Daten so importiert werden sollen wie sie hier angezeigt werden. Dr&uuml;cken Sie danach den Import-Knopf am Ende dieser Seite.') ,'</p>', "\n";
		
		echo '<table cellspacing="1" class="phonebook">', "\n";
		echo '<thead>', "\n";
		echo '<tr>', "\n";
		echo '<th>', __('Nachname') ,'</th>', "\n";
		echo '<th>', __('Vorname') ,'</th>', "\n";
		echo '<th>', __('Tel.Nr.') ,'</th>', "\n";
		echo '<th>', __('Tel.Nr. (Import)') ,'</th>', "\n";
		echo '</tr>', "\n";
		echo '</thead>', "\n";
		echo '<tbody>', "\n";
		$i=0;
		foreach ($records as $r) {
			echo '<tr class="', ($i%2? 'even':'odd') ,'">', "\n";
			echo '<td>', $r['ln'   ] ,'</td>', "\n";
			echo '<td>', $r['fn'   ] ,'</td>', "\n";
			echo '<td>', $r['nr'   ] ,'</td>', "\n";
			echo '<td>', $r['nrimp'] ,'</td>', "\n";
			echo '</tr>', "\n";
			++$i;
		}
		echo '</tbody>', "\n";
		echo '</table>', "\n";
		echo '<br />', "\n";
		
		
		echo '<form method="post" action="', GS_URL_PATH, '" enctype="multipart/form-data">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<input type="hidden" name="action" value="import" />', "\n";
		
		echo '<input type="hidden" name="sep"      value="', $sep    ,'" />', "\n";
		echo '<input type="hidden" name="encl"     value="', $encl   ,'" />', "\n";
		echo '<input type="hidden" name="enc"      value="', $enc    ,'" />', "\n";
		echo '<input type="hidden" name="col_ln"   value="', $col_ln ,'" />', "\n";
		echo '<input type="hidden" name="col_fn"   value="', $col_fn ,'" />', "\n";
		echo '<input type="hidden" name="col_nr"   value="', $col_nr ,'" />', "\n";
		echo '<input type="hidden" name="skip_1st" value="', (int)$skip_1st ,'" />', "\n";
		
		echo '<p class="text">',
			@sPrintF(__('Daten in Ihr pers&ouml;nliches Telefonbuch (Benutzer <code>%s</code>, %s %s, Durchwahl <code>%s</code>) importieren?'),
				@$_SESSION['sudo_user']['name'],
				@$_SESSION['sudo_user']['info']['firstname'],
				@$_SESSION['sudo_user']['info']['lastname'],
				@$_SESSION['sudo_user']['info']['ext']
			),
			'</p>', "\n";
		
		echo '<input type="submit" value="', __('Import') ,'" /><br />', "\n";
		echo '</form>', "\n";
		echo '<br />', "\n";
		
	}
	
}


if ($action === 'import') {
	
	if (! @is_array(@$records) || count($records)<1) {
		echo 'Error.';
	} else {
		
		$user_id = (int)@$_SESSION['sudo_user']['info']['id'];
		$query_start = 'INSERT INTO `pb_prv` (`user_id`, `firstname`, `lastname`, `number`) VALUES '."\n";
		$sql_values = array();
		gs_db_start_trans($DB);
		foreach ($records as $r) {
			$sql_values[] = '('. $user_id .', \''. $DB->escape($r['fn']) .'\', \''. $DB->escape($r['ln']) .'\', \''. $DB->escape($r['nrimp']) .'\')';
			if (count($sql_values) == 10) {
				$query = $query_start . implode(",\n", $sql_values);
				$sql_values = array();
				$DB->execute($query);
			}
		}
		if (count($sql_values) > 0) {
			$query = $query_start . implode(",\n", $sql_values);
			$sql_values = array();
			$DB->execute($query);
		}
		//$ok = (gs_get_conf('GS_DB_MASTER_TRANSACTIONS') ? @$DB->completeTrans() : true);
		$ok = gs_db_commit_trans($DB);
		$file = @$_SESSION['sudo_user']['pb-csv-file'];
		@$_SESSION['sudo_user']['pb-csv-file'] = null;
		if (@is_file($file)) {
			$err=0; $out=array();
			@exec( 'rm -f '. qsa($file) );
		}
		
		echo '<br />', "\n";
		echo '<p class="text">', ($ok ? __('Die Daten wurden in Ihr pers&ouml;nliches Telefonbuch importiert.') : 'DB Error.') ,'</p>', "\n";
		
	}
	
}


if ($action == '') {
	
	echo '<form method="post" action="', GS_URL_PATH, '" enctype="multipart/form-data">', "\n";
	echo gs_form_hidden($SECTION, $MODULE), "\n";
	echo '<input type="hidden" name="action" value="upload" />', "\n";
	
	echo '<p class="text">', __('Sie haben hier die M&ouml;glichkeit, eine Datei im CSV-Format hochzuladen, um die Eintr&auml;ge in Ihr pers&ouml;nliches Telefonbuch zu &uuml;bernehmen.'), '</p>', "\n";
	echo '<p class="text">', __('Vor dem Import der Datens&auml;tze wird Ihnen zur Kontrolle eine Vorschau angezeigt.'), '</p>', "\n";
	
	echo '<br />', "\n";
	echo '<label for="ipt-pb_csv_file">', __('CSV-Datei') ,':</label><br />', "\n";
	echo '<input type="file" name="pb_csv_file" id="ipt-pb_csv_file" size="50" style="font-size:0.96em;" /><br />', "\n";
	
	echo '<br />', "\n";
	echo '<input type="submit" value="', __('Hochladen') ,'" /><br />', "\n";
	
	echo '</form>', "\n";
	
	
	echo '<br />' ,"\n";
	echo '<br />' ,"\n";
	echo '<br />' ,"\n";
	echo '<div style="max-width:45em;">', "\n";
	echo htmlEnt(__("Das Format sollte etwa folgenderma\xC3\x9Fen aussehen.")) ,"\n";
	echo '<pre style="margin:0.5em 1em; padding:0.06em 0.3em; background-color:#eee; width:60%; border:1px solid #ddd;">', htmlEnt(__("Vorname;Nachname;Telefon\nAlbert;Einstein;+309876543\nRobert;Bosch;00711123456")) ,'</pre>',"\n";
	echo htmlEnt(__("Ob eine Kopfzeile vorhanden ist oder nicht, die Reihenfolge der Spalten und die Trennzeichen werden automatisch erkannt und k\xC3\xB6nnen ggf. interaktiv in der Vorschau eingestellt werden.")) ,"\n";
	echo '</div>' ,"\n";
	
}

?>










<?php return; ?>

<br />
<hr />
<?php echo __('Vorschau'); ?>:<br />

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:270px;">
		<?php echo ($number=='') ? '<span class="sort-col">'. __('Nachname') .', '. __('Vorname') .'</span>' : _('Nachname') .', '. __('Vorname'); ?>
	</th>
	<th style="width:200px;">
		<?php echo ($number=='') ? __('Nummer') : '<span class="sort-col">'. __('Nummer'), '</span>'; ?>
	</th>
	<th style="width:100px;">&nbsp;</th>
</tr>
</thead>
<tbody>

<?php

/*
if (@$rs) {
	$i = 0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="', ((++$i % 2 == 0) ? 'even':'odd'), '">', "\n";
		
		if ($r['id']==$edit_entry) {
 			
			echo '<td>';
			echo '<input type="text" name="slname" value="', htmlEnt($r['lastname']), '" size="15" maxlength="40" style="width:125px;" /><input type="text" name="sfname" value="', htmlEnt($r['firstname']), '" size="15" maxlength="40" style="width:115px;" />';
			echo '</td>', "\n";
			
			echo '<td>';
			echo '<input type="text" name="snumber" value="', htmlEnt($r['number']), '" size="15" maxlength="25" style="width:150px;" />';
			echo '</td>', "\n";
			
			echo '<td>';
			echo '<input type="hidden" name="save" value="', $r['id'], '" />';
			echo '<input type="hidden" name="page" value="', $page, '" />';
			echo '<button type="submit" title="', __('Eintrag speichern'), '" class="plain">';
			echo '<img alt="', __('speichern'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/filesave.png" />';
			echo '</button>';
			echo '<button type="reset" title="', __('r&uuml;ckg&auml;ngig'), '" class="plain">';
			echo '<img alt="', __('r&uuml;ckg&auml;ngig'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/reload.png" />';
			echo '</button>';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'page='.$page), '" title="', __('abbrechen'), '"><img alt="', __('abbrechen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/cancel.png" /></a>';
	
			echo '</td>';
			
		} else {
			
			echo '<td>', htmlEnt($r['lastname']);
			if ($r['firstname'] != '')
				echo ', ', htmlEnt($r['firstname']);
			echo '</td>', "\n";
			
			echo '<td>', htmlEnt($r['number']), '</td>', "\n";
			
			echo '<td>';
			$sudo_url =
				(@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
				? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
			echo '<a href="', GS_URL_PATH, 'srv/pb-dial.php?n=', htmlEnt($r['number']), $sudo_url, '" title="', __('w&auml;hlen'), '"><img alt="', __('w&auml;hlen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/yast_PhoneTTOffhook.png" /></a> &nbsp; ';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['id'] .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page), '" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'] .'&amp;page='.$page), '" title="', __('entfernen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			echo '</td>';
			
		}
		
		echo '</tr>', "\n";
	}
}
*/

?>

</tbody>
</table>

