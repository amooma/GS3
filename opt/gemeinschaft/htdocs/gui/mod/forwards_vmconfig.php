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
include_once( GS_DIR .'inc/gs-fns/gs_user_email_address_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_email_notify_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_email_notify_set.php' );
require_once( GS_DIR .'inc/get-listen-to-ids.php' );
require_once( GS_DIR .'inc/remote-exec.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_numbers_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
include_once( GS_DIR .'inc/group-fns.php' );

function _pack_int( $int ) {
	$str = base64_encode(pack('N', $int ));
	return preg_replace('/[^a-z0-9]/i', '', $str);
}

function InitRecordCall($filename, $index, $comment) {
	
	$user=gs_user_get( $_SESSION['sudo_user']['name'] );

	$call
		//= "Channel: Local/". $from_num_dial ."\n"
		= "Channel: SIP/".$_SESSION['sudo_user']['info']['ext']."\n"
		. "MaxRetries: 0\n"
		. "WaitTime: 15\n"
		. "Context: vm-rec-multiple\n"
		. "Extension: webdialrecord\n"
		. "Callerid: $comment <Aufnahme>\n"
		. "Setvar: __user_id=".  $_SESSION['sudo_user']['info']['id'] ."\n"
		. "Setvar: __user_name=".  $_SESSION['sudo_user']['info']['ext'] ."\n"
		. "Setvar: CHANNEL(language)=". gs_get_conf('GS_INTL_ASTERISK_LANG','de') ."\n"
		. "Setvar: __is_callfile_origin=1\n"  # no forwards and no mailbox on origin side
		. "Setvar: __callfile_from_user=".  $_SESSION['sudo_user']['info']['ext'] ."\n"
		. "Setvar: __record_file=".  $filename ."\n"
		;

	$filename = '/tmp/gs-'. $_SESSION['sudo_user']['info']['id'] .'-'. _pack_int(time()) . rand(100,999) .'.call';

	$cf = @fOpen( $filename, 'wb' );
	if (! $cf) {
		gs_log( GS_LOG_WARNING, 'Failed to write call file "'. $filename .'"' );
		echo 'Failed to write call file.';
		die();
	}
	@fWrite( $cf, $call, strLen($call) );
	@fClose( $cf );
	@chmod( $filename, 00666 );
	
	$spoolfile = '/var/spool/asterisk/outgoing/'. baseName($filename);

	if (! gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
		$our_host_ids = @gs_get_listen_to_ids();
		if (! is_array($our_host_ids)) $our_host_ids = array();
		$user_is_on_this_host = in_array( $_SESSION['sudo_user']['info']['host_id'], $our_host_ids );
	} else {
		$user_is_on_this_host = true;
	}
	if ($user_is_on_this_host) {

		# the Asterisk of this user and the web server both run on this host

		$err=0; $out=array();
		@exec( 'sudo mv '. qsa($filename) .' '. qsa($spoolfile) .' 1>>/dev/null 2>>/dev/null', $out, $err );
		if ($err != 0) {
			@unlink( $filename );
			gs_log( GS_LOG_WARNING, 'Failed to move call file "'. $filename .'" to "'. '/var/spool/asterisk/outgoing/'. baseName($filename) .'"' );
			echo 'Failed to move call file.';
			die();
		}

	}
	else {
		$cmd = 'sudo scp -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa( $filename ) .' '. qsa( 'root@'. $user['host'] .':'. $filename );
		//echo $cmd, "\n";
		@exec( $cmd .' 1>>/dev/null 2>>/dev/null', $out, $err );
		@unlink( $filename );
		if ($err != 0) {
			gs_log( GS_LOG_WARNING, 'Failed to scp call file "'. $filename .'" to '. $user['host'] );
			echo 'Failed to scp call file.';
			die();
		}
		//remote_exec( $user['host'], $cmd, 10, $out, $err ); // <-- does not use sudo!
		$cmd = 'sudo ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root '. qsa( $user['host'] ) .' '. qsa( 'mv '. qsa( $filename ) .' '. qsa( $spoolfile ) );
		//echo $cmd, "\n";
		@exec( $cmd .' 1>>/dev/null 2>>/dev/null', $out, $err );
		if ($err != 0) {
			gs_log( GS_LOG_WARNING, 'Failed to mv call file "'. $filename .'" on '. $user['host'] .' to "'. $spoolfile .'"' );
		echo 'Failed to mv call file on remote host.';
		die();
		}
	}
}

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
/*
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
*/
echo __('Anrufbeantworterkonfiguration');
echo '</h2>', "\n";

$timeruleactives = array(
	'std' => __('Std.'),
	'var' => __('Tmp.')
);

$vm_rec_num_idx_table=array();

## check permissions
#

$user_groups  = gs_group_members_groups_get( array( $_SESSION['real_user']['info']['id'] ), 'user' );
$members = gs_group_permissions_get ( $user_groups, 'forward_vmconfig' );
$members_adm = gs_group_permissions_get ( $user_groups , 'sudo_user' );

if ( count ( $members_adm ) > 0 || count ( $members ) > 0 ) 
	$disabled = '';
else
	$disabled = ' disabled';


//loop Voicemail-Announce-Files
$rs = $DB->execute('SELECT * from `vm_rec_messages` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']);
$ncnt=0;
while ($r = $rs->fetchRow()) {
	$actives['vml-'.++$ncnt] = __('AB mit Ansage ').$ncnt;
	$timeruleactives['vml-'.$r['id']] =  __('AB mit Ansage ').$ncnt;;
	$vm_rec_num_idx_table[$ncnt] = $r['id'];
}
if ($ncnt==0)
  $actives['vml'] = __('AB');

$rs = $DB->execute('SELECT * from `vm_rec_messages` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']);
$ncnt=0;
while ($r = $rs->fetchRow()) {
	$actives['vmln-'.++$ncnt] = __('Ansage ').$ncnt;
	$timeruleactives['vmln-'.$r['id']] = __('Ansage ').$ncnt;
}

$id = (int)$DB->executeGetOne('SELECT `_user_id` from `cf_timerules` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']);
if ($id) {
	$actives['trl'] = __('Zeitsteuerung');
}

$id = (int)$DB->executeGetOne('SELECT `_user_id` from `cf_parallelcall` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']);
if ($id) {
	$actives['par'] = __('Parallelruf');
	$timeruleactives['par'] = __('Parallelruf');
}



$show_email_notification = ! @$_SESSION['sudo_user']['info']['host_is_foreign'];

$warnings = array();

if (@$_REQUEST['action']==='savemailnotify' && $disabled == '' ) {
	
	if ($show_email_notification) {
		$email_address = gs_user_email_address_get( $_SESSION['sudo_user']['name'] );
		$email_notify = @$_REQUEST['email_notify'];
		if ($email_address == '') $email_notify = 'off';
		switch ($email_notify) {
			case 'on' : $email_notify = 1; break;
			case 'off':
			default   : $email_notify = 0;
		}
		$ret = gs_user_email_notify_set( $_SESSION['sudo_user']['name'], $email_notify );
		if (isGsError($ret))
			$warnings['vm_email_n'] = __('Fehler beim (De-)Aktivieren der E-Mail-Benachrichtigung') .' ('. $ret->getMsg() .')';
	}
}
elseif (@$_REQUEST['action']==='savevmrec' && $disabled == '' ) {
	
	if (isset($_REQUEST['save_new'])) {
		$new_entry = $_REQUEST['comment_new'];
		if ($new_entry != "") {
			$id= $DB->executeGetOne('SELECT `id` from `vm_rec_messages` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']. ' ORDER BY `id` DESC');
			if ($id=='')
				$id=0;
			++$id;

			$DB->execute('INSERT INTO `vm_rec_messages` (`_user_id`,`vm_rec_file`,`vm_comment`)'.
		' VALUES('.$_SESSION['sudo_user']['info']['id'].", '". 'vm-'.$_SESSION['sudo_user']['name'].'-'.$id."', '".$DB->escape($new_entry)."')");
		}
		//Save changes from the Old entrys:
		foreach ($_REQUEST as $item => $comment ) {
			if (substr($item,0,8) == "comment_" && $item != "comment_new") {
				$id = substr($item,8);
				$DB->execute('UPDATE `vm_rec_messages` SET `vm_comment`=\''.$DB->escape($comment).'\' WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id'].' AND `id`='.$DB->escape($id));
			}
		}
	}
	elseif (isset($_REQUEST['delete'])) {
		$id = $_REQUEST['delete'];
		//disable call Forwards which using this File
		$DB->execute('UPDATE `callforwards` SET `active`=\'no\', `vm_rec_id`=\'0\' WHERE `user_id`='.$_SESSION['sudo_user']['info']['id'].' AND `vm_rec_id`='.$DB->escape($id));
		$DB->execute('DELETE from `vm_rec_messages` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id'].' AND `id`='.$DB->escape($id));
		//FIXME delete file from Server!
	}
	elseif ($_REQUEST['record'] != 0) {
		$i = (int)$_REQUEST['record'];
		$file = $DB->executeGetOne('SELECT `vm_rec_file` from `vm_rec_messages` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id'].' AND `id`='.$DB->escape($i));
		$comment = $DB->executeGetOne('SELECT `vm_comment` from `vm_rec_messages` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id'].' AND `id`='.$DB->escape($i));
		//execute Call to record the message!			
		InitRecordCall($file, $i,$comment);
	}
	
}
elseif ( $disabled == '' && ( @$_REQUEST['action']==='movetimeruleup'
||      @$_REQUEST['action']==='movetimeruledown' ) )   {
	
	$user_id =  $_SESSION['sudo_user']['info']['id'];
	$id=(int)$_REQUEST['move'];
	$rs=$DB->execute('SELECT `id`,`ord` FROM `cf_timerules` WHERE `id` ='.$id.' AND `_user_id`= '.$user_id);
	$entry = $rs->fetchRow();
	$oldord = (int)$entry['ord'];
	if ($_REQUEST['action']==='movetimeruleup')
		$neword = $oldord-1;
	else
		$neword = $oldord+1;
	$rs2=$DB->execute('SELECT `id`,`ord` FROM `cf_timerules` WHERE `_user_id` ='.$user_id.' AND `ord`='.$neword);
	$entry2 = $rs2->fetchRow();
	if ($entry2['id'] != 0) {
		$DB->execute('UPDATE `cf_timerules` SET `ord`='.$oldord.' WHERE `id` ='.$entry2['id']);
		$ord--;
		$DB->execute('UPDATE `cf_timerules` SET `ord`='.$neword.' WHERE `id` ='.$entry['id']);
	}
	
}
elseif (@$_REQUEST['action']==='savetimerule' && $disabled == '' ) {
	
	$user_id =  $_SESSION['sudo_user']['info']['id'];
	# SAVE changes in old timerules...
	foreach ($_REQUEST as $tr => $trid) {
		if (substr($tr,0,3) == "tr_") {
			$trid = (int) $trid;
			$target = preg_replace('/[^a-z0-9]-/S', '', @$_REQUEST[$trid.'-r_target']);

			#save rule to Database
			$h_from_h = (int)lTrim(@$_REQUEST[$trid.'-r_h_from_h'],' 0');
			if ($h_from_h <  0) $h_from_h =  0;
			elseif ($h_from_h > 23) $h_from_h = 23;
			$h_from_h = str_pad($h_from_h, 2, '0', STR_PAD_LEFT);
			$h_from_m = (int)lTrim(@$_REQUEST[$trid.'-r_h_from_m'],' 0');
			if     ($h_from_m <  0) $h_from_m =  0;
			elseif ($h_from_m > 59) $h_from_m = 59;
			$h_from_m = str_pad($h_from_m, 2, '0', STR_PAD_LEFT);
			$h_from = $h_from_h.':'.$h_from_m;

			$h_to_h = (int)lTrim(@$_REQUEST[$trid.'-r_h_to_h'],' 0');
			if     ($h_to_h <  0) $h_to_h =  0;
			elseif ($h_to_h > 24) $h_to_h = 24;
			$h_to_h = str_pad($h_to_h, 2, '0', STR_PAD_LEFT);
			$h_to_m = (int)lTrim(@$_REQUEST[$trid.'-r_h_to_m'],' 0');
			if ($h_to_m <  0) $h_to_m =  0;
			elseif ($h_to_m > 59) $h_to_m = 59;
			$h_to_m = str_pad($h_to_m, 2, '0', STR_PAD_LEFT);
			$h_to = $h_to_h.':'.$h_to_m;
			if ($h_to > '24:00') $h_to = '24:00';
			if ($h_to < $h_from) $h_to = $h_from;

			$query = 'UPDATE `cf_timerules` SET 
				`d_mo`='. ((int)(bool)@$_REQUEST[$trid.'-d_mo']) .',
				`d_tu`='. ((int)(bool)@$_REQUEST[$trid.'-d_tu']) .',
				`d_we`='. ((int)(bool)@$_REQUEST[$trid.'-d_we']) .',
				`d_th`='. ((int)(bool)@$_REQUEST[$trid.'-d_th']) .',
				`d_fr`='. ((int)(bool)@$_REQUEST[$trid.'-d_fr']) .',
				`d_sa`='. ((int)(bool)@$_REQUEST[$trid.'-d_sa']) .',
				`d_su`='. ((int)(bool)@$_REQUEST[$trid.'-d_su']) .',
				`h_from`=\''. $DB->escape($h_from) .'\',
				`h_to`=\''  . $DB->escape($h_to  ) .'\',
				`target`=\''  . $DB->escape($target  ) .'\'
				WHERE `_user_id` = '. $user_id. ' AND `id` ='.$trid;
			$DB->execute($query);
		}
	}

	# SAVE NEW timerule

	if (array_key_exists('new_r_target' , $_REQUEST) && $_REQUEST['new_r_target'] != ""  && $disabled == '' ) {

		# get value for sort
		$ord =(int)$DB->executeGetOne('SELECT `ord` FROM `cf_timerules` WHERE `_user_id`='.$user_id.' ORDER BY `ord` DESC');
		++$ord;
		$target = preg_replace('/[^a-z0-9]-/S', '', @$_REQUEST['new_r_target']);
		
		#save rule to Database
		$h_from_h = (int)lTrim(@$_REQUEST['new_r_h_from_h'],' 0');
		if ($h_from_h <  0) $h_from_h =  0;
		elseif ($h_from_h > 23) $h_from_h = 23;
		$h_from_h = str_pad($h_from_h, 2, '0', STR_PAD_LEFT);
		$h_from_m = (int)lTrim(@$_REQUEST['new_r_h_from_m'],' 0');
		if     ($h_from_m <  0) $h_from_m =  0;
		elseif ($h_from_m > 59) $h_from_m = 59;
		$h_from_m = str_pad($h_from_m, 2, '0', STR_PAD_LEFT);
		$h_from = $h_from_h.':'.$h_from_m;
		
		$h_to_h = (int)lTrim(@$_REQUEST['new_r_h_to_h'],' 0');
		if     ($h_to_h <  0) $h_to_h =  0;
		elseif ($h_to_h > 24) $h_to_h = 24;
		$h_to_h = str_pad($h_to_h, 2, '0', STR_PAD_LEFT);
		$h_to_m = (int)lTrim(@$_REQUEST['new_r_h_to_m'],' 0');
		if ($h_to_m <  0) $h_to_m =  0;
		elseif ($h_to_m > 59) $h_to_m = 59;
		$h_to_m = str_pad($h_to_m, 2, '0', STR_PAD_LEFT);
		$h_to = $h_to_h.':'.$h_to_m;
		if ($h_to > '24:00') $h_to = '24:00';
		if ($h_to < $h_from) $h_to = $h_from;

		$query = 'INSERT INTO `cf_timerules` (`_user_id`,`ord`,`d_mo`,`d_tu`,`d_we`,`d_th`,`d_fr`,`d_sa`,`d_su`,`h_from`,`h_to`,`target`) '.
			'VALUES('.$user_id.','.$ord.','.
				((int)(bool)@$_REQUEST['new_d_mo']).','.
				((int)(bool)@$_REQUEST['new_d_tu']).','.
				((int)(bool)@$_REQUEST['new_d_we']).','.
				((int)(bool)@$_REQUEST['new_d_th']).','.
				((int)(bool)@$_REQUEST['new_d_fr']).','.
				((int)(bool)@$_REQUEST['new_d_sa']).','.
				((int)(bool)@$_REQUEST['new_d_su']).','.
				'\''. $DB->escape($h_from) .'\','.
				'\''. $DB->escape($h_to) .'\','.
				'\''. $DB->escape($target) .'\''.
			')';
		$DB->execute($query);
	}

}
elseif (@$_REQUEST['action']==='deltimerule'  && $disabled == '' ) {
	$id = (int)$_REQUEST['delete'];
	if ($id) {
		$DB->execute('DELETE FROM `cf_timerules` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id'].' AND `id`='.$id);
		//sort entrys
		$rs=$DB->execute('SELECT `id`,`ord` FROM `cf_timerules` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id'].' ORDER BY `ord`');
		$i=1;
		while($r = $rs->fetchRow()) {
			if($r['ord'] != $i) {
				$query = 'UPDATE `cf_timerules` SET 
				`ord`='.$i.' WHERE `id` ='.$r['id'];
				$DB->execute($query);
			}
			++$i;
		}
	}
}
elseif (@$_REQUEST['action']==='saveparcall'  && $disabled == '' ) {
	if (isset($_REQUEST['save_new'])) {
		$new_entry = preg_replace('/[^0-9*#]/S', '', @$_REQUEST['number_new']);
		if ($new_entry != "") {
			//dont allow one number to be the target twice
			$id = $DB->executeGetOne('SELECT `id` FROM `cf_parallelcall` WHERE `_user_id` = '.$_SESSION['sudo_user']['info']['id'].' AND `number` ="' .$DB->escape($new_entry).'"');
			if (!$id)
				$DB->execute('INSERT INTO `cf_parallelcall` (`_user_id`,`number`) VALUES('.$_SESSION['sudo_user']['info']['id'].", '".$DB->escape($new_entry)."')");
		}
		//Save changes from the Old entrys:
		foreach ( $_REQUEST as $item => $comment ) {
			if (substr($item,0,7) == "number_" && $item != "number_new") {
				$id = substr($item,7);
				$DB->execute('UPDATE `cf_parallelcall` SET `number`=\''.$DB->escape($comment).'\' WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id'].' AND `id`='.$DB->escape($id));
			}
		}
	} else if (isset($_REQUEST['delete'])) {
		$id = $_REQUEST['delete'];
		$DB->execute('DELETE from `cf_parallelcall` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id'].' AND `id`='.$DB->escape($id));
	}	
}
elseif (@$_REQUEST['action']==='delparcall'  && $disabled == '' ) {
	if (isset($_REQUEST['delete'])) {
		$id = $_REQUEST['delete'];
		$DB->execute('DELETE from `cf_parallelcall` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id'].' AND `id`='.$DB->escape($id));
	}
} 

if (is_array($warnings) && count($warnings) > 0) {
?>
	<div style="max-width:600px;">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/app/important.png" class="fl" />
	<p style="margin-left:22px;">
		<?php echo implode('<br />', $warnings); ?>
	</p>
</div>
<?php
}

$email_notify = (int)gs_user_email_notify_get( $_SESSION['sudo_user']['name'] );
$email_address = gs_user_email_address_get( $_SESSION['sudo_user']['name'] );
?>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="savevmrec" />

<br />
<table cellspacing="1">
<thead>
<tr>
	<th colspan="6"><?php echo __('Ansagen'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:45px;"><?php echo __('Ansage'); ?></td>
	<td style="width:410px;"><?php echo __('Kommentar'); ?></td>

<?php
	if ( $disabled == '' )
		echo '<td style="width:41px;"></td>';
?>

</tr>
<?php
//loop all files
$rs = $DB->execute('SELECT * from `vm_rec_messages` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']);
$ncnt=0;
while ($r = $rs->fetchRow()) {
	echo "<tr>\n";
	echo "<td>".++$ncnt."</td>";
	echo "<td>";
	echo '<input type="text" name="comment_'.$r['id'].'" value="'.htmlEnt($r['vm_comment']).'" size="50" maxlength="180" ' , $disabled ,  '/>';
	echo "</td>";
	if ( $disabled == '' )  {
		echo "<td>";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'record='.$r['id'].'&amp;action=savevmrec') ,'">', '<img alt="', __('Sprachnachricht mit dem Telefon aufnehmen') ,'" src="', GS_URL_PATH,'crystal-svg/16/app/yast_PhoneTTOffhook.png" />', '</a>', "\n";

		echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'].'&amp;action=savevmrec') ,'">', '<img alt="', __('Eintrag Entfernen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/editdelete.png" />', '</a>', "\n";
	}
	echo "</td>";
}
	echo "</tr>\n";

	if ( $disabled == '' )  {

		echo "<tr>\n";
		
		echo "<td>", __('Neu'), "</td>";
		echo "<td>";
			echo '<input type="text" name="comment_new" value="" size="50" maxlength="180"/>';
		echo "</td>";
		echo "<td>";
			echo '<button type="submit" name="save_new" value="1" title="', __('Eintrag speichern') ,'" class="plain"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>';
		echo "</td>";
		
		echo "</tr>\n";
	}
?>


</tbody>
</table>
</form>

<br />
<h2><?php echo __('Zeitsteuerung');?></h2>
<br />
<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="savetimerule" />

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th><?php echo __('Priorit&auml;t'); ?></th>
	<th><?php echo __('Wochentage'); ?></th>
	<th><?php echo __('Uhrzeit'); ?></th>
	<th><?php echo __('Ziel'); ?> </th>
<?php
	if ( $disabled == '' )
		echo '<th></th>' , "\n";
?>

</tr>
</thead>
<tbody>
<?php
	$user_id = $_SESSION['sudo_user']['info']['id'];
	$oldLocale = setLocale(LC_TIME, '0'); # probably "C"
	$lang = @$_SESSION['lang'];
	if (! $lang)
		$lang = gs_get_conf('GS_INTL_LANG', 'de_DE');
	$lang = strToLower(subStr($lang,0,2));
	switch ($lang) {
		case 'de':
		$l = array('de_DE.UTF-8', 'de_DE.utf8', 'de_DE.iso88591', 'de_DE.iso885915@euro', 'de_DE.ISO8859-1', 'de_DE.ISO8859-15', 'de_DE@euro', 'de_DE', 'de');
		break;
	case 'en':
		$l = array('en_US.utf8', 'en_US.iso88591', 'en_US.ISO8859-1', 'en_US.US-ASCII', 'en_US', 'en');
		break;
	default  :
		$l = array('C');
	}
	$lfound = setLocale(LC_TIME, $l);
	if ($lfound === false) {
	$err=0; $out=array();
		exec('locale -a | grep -i '. qsa('^'.$lang.'_') .' 2>>/dev/null', $out, $err);
		if ($err != 0)
			gs_log( GS_LOG_NOTICE, 'Failed to find locales on your system' );
		else {
			$lfound = setLocale(LC_TIME, $out);
			if ($lfound === false) {
				gs_log( GS_LOG_NOTICE, 'Your system does not have any locales like "'. $lang .'_*"' );
			} else {
				gs_log( GS_LOG_NOTICE, 'Using locale "'. $lfound .'" as a fallback' );
			}
		}
	}
	$wdays = array('mo'=>'Mon', 'tu'=>'Tue', 'we'=>'Wed', 'th'=>'Thu', 'fr'=>'Fri', 'sa'=>'Sat', 'su'=>'Sun');
	$wdaysl = array();
	foreach ($wdays as $col => $wdca)
		$wdaysl[$col] = mb_subStr(strFTime('%a', strToTime('last '.$wdca)),0,1);
	unset($wdays);
	setLocale(LC_TIME, array($oldLocale, 'C'));

	$i=0;
	$rs = $DB->execute('SELECT * from `cf_timerules` WHERE `_user_id`='. $user_id.' ORDER BY `ord`');
	while ( $route = $rs->fetchRow() ) {
		echo '<input type="hidden" name="tr_'.$route['id'].'" value="'.$route['id'].'" />';
		echo '<tr><td>';
		echo $route['ord'];
		echo '</td><td>';
		foreach ($wdaysl as $col => $v) {
			echo '<span class="nobr"><input type="checkbox" name="'.$route['id'].'-d_',$col,'" id="ipt-'.$route['id'].'-r_d_',$col,'" value="1" ', ($route['d_'.$col] ? 'checked="checked" ' : ''), $disabled , '/>';
			echo '<label for="ipt-r_d_',$col,'">', $v, '</label></span>';
		}
		echo '</td>', "\n";

		echo '<td>';
		$tmp = explode(':', $route['h_from']);
		$hf = (int)lTrim(@$tmp[0], '0-');
		if     ($hf <  0) $hf =  0;
		elseif ($hf > 23) $hf = 23;
		$hf = str_pad($hf, 2, '0', STR_PAD_LEFT);
		$mf = (int)lTrim(@$tmp[1], '0-');
		if     ($mf <  0) $mf =  0;
		elseif ($mf > 59) $mf = 59;
		$mf = str_pad($mf, 2, '0', STR_PAD_LEFT);
		echo '<span class="nobr">';
		echo '<input type="text" name="'.$route['id'].'-r_h_from_h" value="', $hf, '" size="2" maxlength="2" class="r" ' , $disabled , '/>:';
		echo '<input type="text" name="'.$route['id'].'-r_h_from_m" value="', $mf, '" size="2" maxlength="2" class="r" ' , $disabled , '/> -';
		echo '</span> ', "\n";
		$tmp = explode(':', $route['h_to']);
		$ht = (int)lTrim(@$tmp[0], '0-');
		if     ($ht <  0) $ht =  0;
		elseif ($ht > 24) $ht = 24;
		$ht = str_pad($ht, 2, '0', STR_PAD_LEFT);
		$mt = (int)lTrim(@$tmp[1], '0-');
		if     ($mt <  0) $mt =  0;
		elseif ($mt > 59) $mt = 59;
		$mt = str_pad($mt, 2, '0', STR_PAD_LEFT);
		if ($ht.':'.$mt < $hf.':'.$mf) {
			$ht = $hf;
			$hm = $mf;
		}
		echo '<span class="nobr">';
		echo '<input type="text" name="'.$route['id'].'-r_h_to_h" value="', $ht, '" size="2" maxlength="2" class="r" ' , $disabled , '/>:';
		echo '<input type="text" name="'.$route['id'].'-r_h_to_m" value="', $mt, '" size="2" maxlength="2" class="r" ' , $disabled , '/>';
		echo '</span>';
		echo '</td>', "\n";
		echo '<td>'."\n";

		echo '<select name="'.$route['id'].'-r_target" ' , $disabled , '/>', "\n";
		foreach ($timeruleactives as $active => $atitle) { //voicemail, Std, tmp ...
			echo '<option value="', $active, '"';
			if ($route['target'] === $active)
				echo ' selected="selected"';
				echo '>', $atitle, '</option>', "\n";
		}
		echo '</select>';

		echo '</td>'. "\n";
		
		if ( $disabled == '' ) {
		
			echo '<td>'."\n";
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$route['id'].'&amp;action=deltimerule') ,'">', '<img alt="', __('Eintrag Entfernen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/editdelete.png" />', '</a>', "\n";

			if ($i > 0) {
				echo '&thinsp;<a href="', gs_url($SECTION, $MODULE, null, 'move='.$route['id'].'&amp;action=movetimeruleup') ,'">', '<img alt="', __('Eintrag nach oben schieben') ,'" src="', GS_URL_PATH,'img/move_up.gif" />', '</a>', "\n";
			} else {
				echo '&thinsp;<img alt="&uarr;" src="', GS_URL_PATH, 'img/move_up_d.gif" />';
			}

			if ($i < $rs->numRows()-1) {
				echo '&thinsp;<a href="', gs_url($SECTION, $MODULE, null, 'move='.$route['id'].'&amp;action=movetimeruledown') ,'">', '<img alt="', __('Eintrag nach unten schieben') ,'" src="', GS_URL_PATH,'img/move_down.gif" />', '</a>', "\n";
			} else {
				echo '&thinsp;<img alt="&darr;" src="', GS_URL_PATH, 'img/move_down_d.gif" />';
			}

			echo '</td>'."\n";
		
		}
		
		echo '</tr>';
		$i++;
	}

	//new timerule...
	if ( $disabled == '' ) {
		echo '<tr><td>'._("Neu:").'</td><td>';
		foreach ($wdaysl as $col => $v) {
			echo '<span class="nobr"><input type="checkbox" name="new_d_',$col,'" id="ipt-r_d_',$col,'" value="1" ', ($route['d_'.$col] ? 'checked="checked" ' : ''), '/>';
			echo '<label for="ipt-r_d_',$col,'">', $v, '</label></span>';
	}
	echo '</td>', "\n";
	echo '<td>';
	echo '<span class="nobr">';
	echo '<input type="text" name="new_r_h_from_h" value="00" size="2" maxlength="2" class="r" />:';
	echo '<input type="text" name="new_r_h_from_m" value="00" size="2" maxlength="2" class="r" /> -';
	echo '</span> ', "\n";

	echo '<span class="nobr">';
	echo '<input type="text" name="new_r_h_to_h" value="23" size="2" maxlength="2" class="r" />:';
	echo '<input type="text" name="new_r_h_to_m" value="59" size="2" maxlength="2" class="r" />';
	echo '</span>';
	echo '</td>', "\n";
	echo '<td>'."\n";
	echo '<select name="new_r_target" />', "\n";
	echo "<option value=\"\">-</option>\n";

	foreach ($timeruleactives as $active => $atitle) { //voicemail, Std, tmp ...
		echo "<option value=\"".$active."\">".$atitle."</option>\n";
	}
	echo '</select>';
	echo '</td>'. "\n";
	echo '<td>'. "\n";
	echo '<button type="submit" name="savetimerule" value="1" title="', __('Eintrag speichern') ,'" class="plain"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>';
	echo '</td>'. "\n";
	}
	echo "</tbody>\n</table>";
?>

</tbody>
</table>
</form>

<br />
<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="saveparcall" />
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th></th>
	<th><?php echo __('Nummer'); ?></th>
<?php
	if ( $disabled == '' )
		echo '<th></th>' ,"\n";
?>
</tr>
</thead>
<tbody>
<h2><?php echo __('Parallelruf');?></h2>

<?php
$jscriptcode = "";
$e_numbers = gs_user_external_numbers_get( $_SESSION['sudo_user']['name'] );
$rs = $DB->execute('SELECT * from `cf_parallelcall` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']);
$ncnt=0;
while ($r = $rs->fetchRow()) {
	echo "<tr>\n";
	echo "<td>".++$ncnt."</td>";
	echo "<td>";
	echo '<input type="text" id="number_'.$r['id'].'" name="number_'.$r['id'].'" value="'.htmlEnt($r['number']).'" size="41" maxlength="20" ' , $disabled , '/>';
	echo '<div id="ext-num-select-'.$ncnt.'" style="display:none;">';
	echo '&larr;<select name="_ignore-1" id="sel-num-'.$r['id'].'" onchange="gs_num_sel(this);"' , $disabled , '>';
	$jscriptcode .= "case 'sel-num-".$r['id']."': var text_el_id = 'number_".$r['id']."'; break;\n";
	if (! isGsError($e_numbers) && is_array($e_numbers)) {
		echo '<option value="">', __('einf&uuml;gen &hellip;') ,'</option>' ,"\n";
		foreach ($e_numbers as $e_number) {
			echo '<option value="', htmlEnt($e_number) ,'">', htmlEnt($e_number) ,'</option>' ,"\n";
		}
	}		
	echo '</select></div>';
	echo "</td>";
	if ( $disabled == '' ) {
		echo "<td>";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'].'&amp;action=delparcall') ,'">', '<img alt="', __('Entfernen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/editdelete.png" />', '</a>', "\n";
		echo "</td>";
	}
	echo "</tr>\n";
}

if ( $disabled == '' ) {
	echo "<tr>\n";
	echo '<td>' , __('Neu') , ':</td>';
	echo "<td>\n";
		echo '<input type="text" id="number_new" name="number_new" value="" size="41" maxlength="20"/>' , "\n";
		echo '<div id="ext-num-select-new" style="display:none;">' , "\n" ;
		echo '&larr;<select name="_ignore-1" id="sel-num-new" onchange="gs_num_sel(this);">' , "\n";
	if (! isGsError($e_numbers) && is_array($e_numbers)) {
		echo '<option value="">', __('einf&uuml;gen &hellip;') ,'</option>' ,"\n";
		foreach ($e_numbers as $e_number) {
			echo '<option value="', htmlEnt($e_number) ,'">', htmlEnt($e_number) ,'</option>' ,"\n";
		}
	}       
	
	echo "</select></div>\n";
	echo "</td>\n";
	echo "<td>\n";
	echo '<button type="submit" name="save_new" value="1" title="', __('Eintrag speichern') ,'" class="plain"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>';
	echo "</td>\n";
	echo "</tr>\n";
}
?>

</tbody>
</table>
<script type="text/javascript">
//<![CDATA[
// show selectors if javascript is available
<?php
//FIXME: Get these things working!!
for ($i=1;$i<$ncnt+1;$i++)
	echo "try { document.getElementById('ext-num-select-$i').style.display = 'inline'; } catch(e){}\n";
?>
try { document.getElementById('ext-num-select-new').style.display = 'inline'; } catch(e){}
//]]>
</script>

<script type="text/javascript">
//<![CDATA[
function gs_num_sel( el )
{
try {
	if (el.value == '') return;
	switch (el.id) {
		<?php echo $jscriptcode;?>
		case 'sel-num-new': var text_el_id = 'number_new'; break;
		default: return;
	}
	document.getElementById(text_el_id).value = el.value;
	//el.value = '';
} catch(e){}
}
//]]>
</script>
</form>

<br />
<h2><?php echo __('E-Mail Benachrichtigung');?></h2>
<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="savemailnotify" />
<?php if ($show_email_notification) { ?>
<br />
<table cellspacing="1">
<thead>
<tr>
	<th colspan="6"><?php echo __('E-Mail-Benachrichtigung bei eingehenden Sprach-Nachrichten'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><?php echo __('E-Mail-Adresse'); ?></td>
	<td style="width:370px;">
		<input type="text" name="email_address" value="<?php echo htmlEnt($email_address); ?>" size="40" maxlength="50" disabled="disabled" />
	</td>
</tr>
<tr>
	<td><?php echo __('Benachrichtigung'); ?></td>
	<td>
<?php
	$dis = ($email_address == '');
	if ($dis) $email_notify = false;
	
	echo '<select name="email_notify" id="ipt-email_notify-on" ';
	if ( $dis || $disabled != '' )
		echo ' disabled="disabled"';
	echo '>', "\n";
	
		echo '<option value="off"';
		if ( $email_notify  === 0 )
			echo ' selected="selected"';
			
		echo '>',  __('aus') , '</option>', "\n";
		
		echo '<option value="on"';
		if ( $email_notify  === 1 )
			echo ' selected="selected"';
			
		echo '>',  __('ein') , '</option>', "\n";
		
		echo '<option value="delete"';
		if ( $email_notify  === 2 )
			echo ' selected="selected"';
			
		echo '>',  __('Nachricht nach Versand l&ouml;schen') , '</option>', "\n";
			
	echo '</select>';
?>
	</td>
</tr>
</tbody>
</table>
<?php } ?>

<br />
<table cellspacing="1">
<tbody>
<tr>
	<td style="width:562px;" class="transp r">
	<?php
		
		if ( $disabled == '' ) {
			echo '<button type="submit">', "\n";
				echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/filesave.png" />', "\n";
				echo __('Speichern'), "\n";
			echo '</button>', "\n";
		}
	?>
	</td>
</tr>
</tbody>
</table>

</form>
