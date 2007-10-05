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

include_once( GS_DIR .'inc/util.php' );



if (@$_REQUEST['action']=='reboot') {
	
	gs_prov_phone_checkcfg_by_ext( $_SESSION['sudo_user']['info']['ext'], true );
	
} elseif (@$_REQUEST['action']=='setcomment') {
	
	$comment = rTrim(mb_subStr(trim( @$_REQUEST['comment'] ),0,200));
	gs_user_comment_set( $_SESSION['sudo_user']['name'], $comment );
	
}


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


//echo '<br />', "\n";
echo '<h2>', __('Willkommen'), ', ', htmlEnt( $_SESSION['sudo_user']['info']['firstname'] .' '. $_SESSION['sudo_user']['info']['lastname'] ), '!</h2>', "\n";

echo '<p>', __('Ihre Durchwahl'), ': <b>', htmlEnt( $_SESSION['sudo_user']['info']['ext'] ), '</b></p><br />', "\n";

?>


<div class="fl" style="clear:right; width:99%;">
	
	<div class="fl" style="width:49%; min-width:20em; max-width:35em; margin:1px;">
		<?php
		
		$rs = $DB->execute( 'SELECT SQL_CALC_FOUND_ROWS `id`, `orig_time`, `cidnum`, `cidname` FROM `vm_msgs` WHERE `user_id`='. (int)@$_SESSION['sudo_user']['info']['id'] .' AND `folder`=\'INBOX\' ORDER BY `orig_time` DESC LIMIT 5' );
		$num = @$DB->numFoundRows();
		
		?>
		<div class="th" style="padding:0.35em 0.6em; margin-bottom:2px;">
			<?php echo __('Neue Voicemail-Nachrichten') /*//TRANSLATE ME*/, ' (',$num,')'; ?>
		</div>
		<div class="td" style="padding:0.0em;">
			<?php
			
			echo '<table cellspacing="1" style="width:100%;">' ,"\n";
			echo '<tbody>' ,"\n";
			$i=0;
			while ($r = $rs->fetchRow()) {
				echo '<tr class="', ($i%2?'even':'odd') ,'">' ,"\n";
				echo '<td style="width:30%;"><nobr>', htmlEnt(date_human($r['orig_time'])) ,'</nobr></td>' ,"\n";
				echo '<td style="width:70%;">', htmlEnt($r['cidnum']);
				if ($r['cidname'] != '') echo ' (', htmlEnt($r['cidname']) ,')';
				echo '</td>' ,"\n";
				echo '</tr>' ,"\n";
				++$i;
			}
			echo '</tbody>' ,"\n";
			echo '</table>' ,"\n";
			
			?>
		</div>
	</div>
	
	<div class="fl" style="width:49%; min-width:20em; max-width:30em; margin:1px;">
		<div class="th" style="padding:0.35em 0.6em; margin-bottom:2px;">
			<?php echo __('Rufumleitung'); /*//TRANSLATE ME*/ ?>
		</div>
		<div class="td" style="padding:0.0em;">
			<?php
			
			$callforwards = gs_callforward_get( $_SESSION['sudo_user']['name'] );
			if (isGsError($callforwards)) {
				echo $callforwards->getMsg();
			} else {
				/*
				echo "<pre>\n";
				print_r($callforwards);
				echo "</pre>\n";
				*/
				
				$actives = array();
				$internal_always = false;
				$external_always = false;
				foreach ($callforwards as $src => $cfs) {
					foreach ($cfs as $case => $cf) {
						if ($cf['active'] == 'no'
						||  $cf['active'] == '') continue;
						
						$actives[] = array(
							'src'     =>  $src,
							'case'    =>  $case,
							'number'  => @$cf['number_'.$cf['active']],
							'timeout' =>  $cf['timeout']
						);
						if ($case==='always') {
							if     ($src==='internal') $internal_always = true;
							elseif ($src==='external') $external_always = true;
						}
					}
				}
				if ($internal_always || $external_always) {
					foreach ($actives as $i => $cf) {
						if ($cf['case'] != 'always') {
							if ($cf['src']==='internal' && $internal_always
							||  $cf['src']==='external' && $external_always) {
								unset($actives[$i]);
							}
						}
					}
				}
				unset($callforwards);
				$i=0;
				echo '<table cellspacing="1" style="width:100%;">' ,"\n";
				echo '<tbody>' ,"\n";
				foreach ($actives as $cf) {
					echo '<tr class="', ($i%2?'even':'odd') ,'">' ,"\n";
					echo '<td>';
					switch ($cf['src']) {
						case 'internal': echo __('von intern'); break;
						case 'external': echo __('von extern'); break;
						default        : echo htmlEnt($cf['src']);
					}
					echo '</td>' ,"\n";
					echo '<td>';
					switch ($cf['case']) {
						case 'always' : echo __('direkt'); break;
						case 'busy'   : echo __('besetzt'); break;
						case 'unavail': echo __('keine Antwort'),
						                ' <nobr>(',$cf['timeout'],' s)</nobr>'; break;
						case 'offline': echo __('offline'); break;
						default        : echo htmlEnt($cf['case']);
					}
					echo '</td>' ,"\n";
					echo '<td> &rarr; ', htmlEnt($cf['number']) ,'</td>' ,"\n";
					echo '</tr>' ,"\n";
					++$i;
				}
				unset($actives);
				echo '</tbody>' ,"\n";
				echo '</table>' ,"\n";
			}
			
			?>
		</div>
	</div>

</div>

<br style="clear:right" />
<div class="fl" style="clear:right; width:100%; height:5px;"></div>

<div class="fl" style="clear:right; width:99%;">
	
	<div class="fl" style="width:49%; min-width:20em; max-width:35em; margin:1px;">
		<?php
		
		$rs = $DB->execute(
'SELECT
	MAX(`d`.`timestamp`) `ts`, `d`.`number`, `d`.`remote_name`
FROM
	`dial_log` `d`
WHERE
	`d`.`user_id`='. (int)@$_SESSION['sudo_user']['info']['id'] .' AND
	`d`.`type`=\'missed\' AND
	`d`.`timestamp`>'. (time()-GS_PROV_DIAL_LOG_LIFE) .' AND
	`d`.`number` <> \''. $DB->escape( @$_SESSION['sudo_user']['info']['ext'] ) .'\'
GROUP BY `d`.`number`
ORDER BY `ts` DESC
LIMIT 5'
		);
		//$num = @$DB->numFoundRows();
		
		?>
		<div class="th" style="padding:0.35em 0.6em; margin-bottom:2px;">
			<?php echo __('Letzte entgangene Anrufe') /*//TRANSLATE ME*/; ?>
		</div>
		<div class="td" style="padding:0.0em;">
			<?php
			
			echo '<table cellspacing="1" style="width:100%;">' ,"\n";
			echo '<tbody>' ,"\n";
			$i=0;
			while ($r = $rs->fetchRow()) {
				echo '<tr class="', ($i%2?'even':'odd') ,'">' ,"\n";
				echo '<td style="width:30%;"><nobr>', htmlEnt(date_human($r['ts'])) ,'</nobr></td>' ,"\n";
				echo '<td style="width:70%;">', htmlEnt($r['number']);
				if ($r['remote_name'] != '') echo ' (', htmlEnt($r['remote_name']) ,')';
				echo '</td>' ,"\n";
				echo '</tr>' ,"\n";
				++$i;
			}
			echo '</tbody>' ,"\n";
			echo '</table>' ,"\n";
			
			?>
		</div>
	</div>
	
	<div class="fl" style="width:49%; min-width:20em; max-width:30em; margin:1px;">
		<div class="th" style="padding:0.35em 0.6em; margin-bottom:2px;">
			<?php echo __('Pr&auml;senz'); /*//TRANSLATE ME*/ ?>
		</div>
		<div class="td" style="padding:0.6em;">
			<form method="get" action="<?php echo GS_URL_PATH; ?>">
			<?php echo gs_form_hidden($SECTION, $MODULE); ?>
			<input type="hidden" name="action" value="setcomment" />
			<?php echo __('Ihr Kommentar f&uuml;r Kollegen (z.B. &quot;Feierabend&quot;)'); ?>:<br />
			<?php
			$comment = gs_user_comment_get( $_SESSION['sudo_user']['name'] );
			?>
			<input name="comment" size="40" maxlength="80" value="<?php echo htmlEnt($comment); ?>" style="max-width:99%;" />
			<br />
			<input type="submit" value="<?php echo __('Speichern'); ?>" />
			</form>
		</div>
	</div>
	
</div>

<br style="clear:right" />
<div class="fl" style="clear:right; width:100%; height:5px;"></div>


<br />
<div style="height:150px;"></div>

<div class="fr">
<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="reboot" />
<input type="submit" value="<?php echo __('Telefon neustarten'); ?>" />
</form>
</div>

<br style="clear:right" />
