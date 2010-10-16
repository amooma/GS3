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
include_once( GS_DIR .'inc/gs-fns/gs_queue_callforward_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_callforward_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queues_get.php' );
include_once( GS_DIR .'inc/group-fns.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
require_once( GS_DIR .'inc/get-listen-to-ids.php' );


function _pack_int( $int ) {
	$str = base64_encode(pack('N', $int ));
	return preg_replace('/[^a-z0-9]/i', '', $str);
}

function InitRecordCall($filename, $index, $comment) {  //FIXME
	
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
?>
<script type="text/javascript">
//<![CDATA[
function gs_num_sel( el )
{
try {
	if (el.value == '') return;
	switch (el.id) {
		case 'sel-num-std': var text_el_id = 'ipt-num-std'; break;
		case 'sel-num-var': var text_el_id = 'ipt-num-var'; break;
		default: return;
	}
	document.getElementById(text_el_id).value = el.value;
	//el.value = '';
} catch(e){}
}
//]]>
</script>

<?php
echo __('Rufumleitung Warteschleifen');
echo '</h2>', "\n";

$sources = array(
	'internal' => __('intern'),
	'external' => __('extern')
);
$cases = array(
	'always' => __('immer'),
	'full'   => __('voll'),
	'timeout'=> __('keine Antw.'),
	'empty'  => __('leer')
);
$actives = array(
	'no'  => '-',
	'std' => __('Std.'),
	'var' => __('Tmp.')
);

$timeruleactives = array(
	'std' => __('Std.'),
	'var' => __('Tmp.')
);

$queue_ext = preg_replace('/[^\d]$/', '', @$_REQUEST['queue']);
$queue_id = NULL;
if ($queue_ext != '') {
	
	$queue_id = $DB->executeGetOne('SELECT `_id` from `ast_queues` WHERE `name`=\''. $DB->escape($queue_ext).'\'');
	$vm_rec_num_idx_table=array();
	//loop Voicemail-Announce-Files
	$rs = $DB->execute('SELECT * from `queue_vm_rec_messages` WHERE `_queue_id`='.$queue_id);
	$ncnt=0;
	while ($r = $rs->fetchRow()) {
		$actives['vml-'.++$ncnt] = sprintf(__('AB mit Ansg. %u'), $ncnt);
		$timeruleactives['vml-'.$r['id']] =  sprintf(__('AB mit Ansg. %u'), $ncnt);
		$vm_rec_num_idx_table[$ncnt] = $r['id'];
	}
	$rs = $DB->execute('SELECT * from `queue_vm_rec_messages` WHERE `_queue_id`='.$queue_id);
	$ncnt=0;
	while ($r = $rs->fetchRow()) {
		$actives['vmln-'.++$ncnt] = sprintf(__('Ansg. %u'), $ncnt);
		$timeruleactives['vmln-'.$r['id']] = sprintf(__('Ansg. %u'), $ncnt);
	}
	
	$id = (int)$DB->executeGetOne('SELECT `_queue_id` from `queue_cf_timerules` WHERE `_queue_id`='.$queue_id);
	if ($id) {
		$actives['trl'] = __('Zeitsteuerung');
	}
	$id = (int)$DB->executeGetOne('SELECT `_queue_id` from `queue_cf_parallelcall` WHERE `_queue_id`='.$queue_id);
	if ($id) {
		$actives['par'] = __('Parallelruf');
		$timeruleactives['par'] = __('Parallelruf');
	}
	
}







$queues = @gs_queues_get();

$user_groups  = gs_group_members_groups_get( array(@$_SESSION['sudo_user']['info']['id']), 'user');
$queue_groups = gs_group_members_get( gs_group_permissions_get( $user_groups, 'forward_queues', 'queue'));

if (isGsError($queues)) {
	echo __('Fehler beim Abfragen der Warteschlangen.'), ' - ', $queues->getMsg();
	return;  # return to parent file
} elseif (! is_array($queues)) {
	echo __('Fehler beim Abfragen der Warteschlangen.');
	return;  # return to parent file
}

$queue = null;
if ($queue_ext != '') {
	foreach ($queues as $q) {
		if (($q['name'] == $queue_ext)
		&&  (array_search($q['id'], $queue_groups) !== false)) {
			$queue = $q;
			break;
		}
	}
}


$warnings = array();

if (@$_REQUEST['action']=='save' && $queue) {
	if (preg_match('/sysrec\B[\d]/', @$_REQUEST['num-std'])) {
		$num_std = @$_REQUEST['num-std'];
	} else {
		$num_std = preg_replace('/[^\d]/', '', @$_REQUEST['num-std']);
	}
	if (preg_match('/sysrec\B[\d]/', @$_REQUEST['num-var'])) {
                $num_var = @$_REQUEST['num-var'];
        } else {
                $num_var = preg_replace('/[^\d]/', '', @$_REQUEST['num-var']);
        }
	$num_vml_orig = preg_replace('/[^\d]/', '', @$_REQUEST['num-vml']);
	$timeout = abs((int)@$_REQUEST['timeout']);
	if ($timeout < 1) $timeout = 1;
	
	foreach ($sources as $src => $ignore) {
		foreach ($cases as $case => $gnore2) {
			$ret = gs_queue_callforward_set( $queue_ext,
				$src, $case, 'std', $num_std, $timeout );
			if (isGsError($ret))
				$warnings['std'] = __('Fehler beim Setzen der Std.-Umleitungsnummer') .' ('. $ret->getMsg() .')';
			$ret = gs_queue_callforward_set( $queue_ext,
				$src, $case, 'var', $num_var, $timeout );
			if (isGsError($ret))
				$warnings['var'] = __('Fehler beim Setzen der Tempor&auml;ren Umleitungsnummer') .' ('. $ret->getMsg() .')';
			$ret = gs_queue_callforward_set( $queue_ext, $src, $case, 'vml', $num_vml, $timeout );
			if (isGsError($ret))
				$warnings['vml'] = __('Fehler beim Setzen der Anrufbeantworter-Nummer') .' ('. $ret->getMsg() .')';

			$vmail_rec_num = 0;
			//Voicemail or just Announce-File
			if (substr(@$_REQUEST[$src.'-'.$case],0,5) === 'vmln-') {
				//Play only Announce-File with Number n
				$idx =(int)substr(@$_REQUEST[$src.'-'.$case],5);
				$vmail_rec_num = $vm_rec_num_idx_table[$idx];
				$num_vml = 'vm*'. $num_vml_orig;
				$_REQUEST[$src.'-'.$case] = 'vml';
			} else if (substr(@$_REQUEST[$src.'-'.$case],0,4) === 'vml-') {
				//Voicemail with Anncounce-File Number n
				$idx =(int)substr(@$_REQUEST[$src.'-'.$case],4);
				$vmail_rec_num = $vm_rec_num_idx_table[$idx];
				$num_vml = 'vm'. $num_vml_orig;
				$_REQUEST[$src.'-'.$case] = 'vml';
			} else if (@$_REQUEST[$src.'-'.$case] === 'vmln') {
				$num_vml = 'vm*'. $num_vml_orig;
				$_REQUEST[$src.'-'.$case] = 'vml';
			} else {
				$num_vml = 'vm' . $num_vml_orig;
			}
			$ret = gs_queue_callforward_set( $queue_ext, $src, $case, 'vml', $num_vml, $timeout, $vmail_rec_num );
			if (isGsError($ret))
				$warnings['vml'] = __('Fehler beim Setzen der AB-Nummer') .' ('. $ret->getMsg() .')';
			$ret = gs_queue_callforward_activate( $queue_ext, $src, $case, @$_REQUEST[$src.'-'.$case] );
			if (isGsError($ret))
				$warnings['act'] = __('Fehler beim Aktivieren der Umleitungsnummer') .' ('. $ret->getMsg() .')';
		}
	}
	
}
elseif (@$_REQUEST['action']==='savevmrec') {
	if (isset($_REQUEST['save_new'])) {
		$new_entry = $_REQUEST['comment_new'];
		if ($new_entry != "") {
			$id= $DB->executeGetOne('SELECT `id` from `queue_vm_rec_messages` WHERE `_queue_id`='.$queue_id.' ORDER BY `id` DESC');
			if ($id=='')
				$id=0;
			++$id;

			$DB->execute('INSERT INTO `queue_vm_rec_messages` (`_queue_id`,`vm_rec_file`,`vm_comment`)'.
		' VALUES('.$queue_id.", '". 'vm-q'.$queue_ext.'-'.$id."', '".$DB->escape($new_entry)."')");
		}
		//Save changes from the Old entrys:
		foreach ($_REQUEST as $item => $comment ) {
			if (substr($item,0,8) == "comment_" && $item != "comment_new") {
				$id = substr($item,8);
				$DB->execute('UPDATE `queue_vm_rec_messages` SET `vm_comment`=\''.$DB->escape($comment).'\' WHERE `_queue_id`='.$queue_id.' AND `id`='.$DB->escape($id));
			}
		}
	} else if (isset($_REQUEST['delete'])) {
		$id = $_REQUEST['delete'];
		//disable call Forwards which using this File
		$DB->execute('UPDATE `queue_callforwards` SET `active`=\'no\', `vm_rec_id`=\'0\' WHERE `queue_id`='.$queue_id.' AND `vm_rec_id`='.$DB->escape($id));
		$DB->execute('DELETE from `queue_vm_rec_messages` WHERE `_queue_id`='.$queue_id.' AND `id`='.$DB->escape($id));
		//FIXME delete file from Server!
	} else if ($_REQUEST['record'] != 0) {
		$i = (int)$_REQUEST['record'];
		$file = $DB->executeGetOne('SELECT `vm_rec_file` from `queue_vm_rec_messages` WHERE `_queue_id`='.$queue_id.' AND `id`='.$DB->escape($i));
		$comment = $DB->executeGetOne('SELECT `vm_comment` from `queue_vm_rec_messages` WHERE `_queue_id`='.$queue_id.' AND `id`='.$DB->escape($i));
		//execute Call to record the message!			
		InitRecordCall($file, $i,$comment);
	}
}
elseif (@$_REQUEST['action']==='movetimeruleup'
||      @$_REQUEST['action']==='movetimeruledown') {
	
	$id=(int)$_REQUEST['move'];
	$rs=$DB->execute('SELECT `id`,`ord` FROM `queue_cf_timerules` WHERE `id` ='.$id);
	$entry = $rs->fetchRow();
	$oldord = (int)$entry['ord'];
	if ($_REQUEST['action']==='movetimeruleup')
		$neword = $oldord-1;
	else
		$neword = $oldord+1;
	$rs2=$DB->execute('SELECT `id`,`ord` FROM `queue_cf_timerules` WHERE `_queue_id` ='.$queue_id.' AND `ord`='.$neword);
	$entry2 = $rs2->fetchRow();
	if ($entry2['id'] != 0) {
		$DB->execute('UPDATE `queue_cf_timerules` SET `ord`='.$oldord.' WHERE `id` ='.$entry2['id']);
		$ord--;
		$DB->execute('UPDATE `queue_cf_timerules` SET `ord`='.$neword.' WHERE `id` ='.$entry['id']);
	}
}
elseif (@$_REQUEST['action']==='savetimerule') {
	# SAVE changes in old timerules...
	foreach ($_REQUEST as $tr => $trid) {
		if(substr($tr,0,3) == "tr_") {
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

			$query = 'UPDATE `queue_cf_timerules` SET 
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
				WHERE `_queue_id` = '. $queue_id. ' AND `id` ='.$trid;
				$DB->execute($query);
		}
	}

	# SAVE NEW timerule

	if (array_key_exists('new_r_target' , $_REQUEST) && $_REQUEST['new_r_target'] != "" ) {
		
		# get value for sort
		$ord =(int)$DB->executeGetOne('SELECT `ord` FROM `queue_cf_timerules` WHERE `_queue_id`='.$queue_id.' ORDER BY `ord` DESC');
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
	
		$query = 'INSERT INTO `queue_cf_timerules` (`_queue_id`,`ord`,`d_mo`,`d_tu`,`d_we`,`d_th`,`d_fr`,`d_sa`,`d_su`,`h_from`,`h_to`,`target`) '.
			'VALUES('.$queue_id.','.$ord.','.
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
elseif (@$_REQUEST['action']==='deltimerule') {
	$id = (int)$_REQUEST['delete'];
	if ($id) {
		$DB->execute('DELETE FROM `queue_cf_timerules` WHERE `_queue_id`='.$queue_id.' AND `id`='.$id);
		//sort entrys
		$rs=$DB->execute('SELECT `id`,`ord` FROM `queue_cf_timerules` WHERE `_queue_id`='.$queue_id.' ORDER BY `ord`');
		$i=1;
		while($r = $rs->fetchRow()) {
			if($r['ord'] != $i) {
				 $query = 'UPDATE `queue_cf_timerules` SET 
				`ord`='.$i.' WHERE `id` ='.$r['id'];
				$DB->execute($query);
			}
			++$i;
		}
	}
	
}
elseif (@$_REQUEST['action']==='saveparcall') {
	if (isset($_REQUEST['save_new'])) {
		$new_entry = preg_replace('/[^0-9*#]/S', '', @$_REQUEST['number_new']);
		if ($new_entry != "") {
			//dont allow one number to be the target twice
			$id = $DB->executeGetOne('SELECT `id` FROM `queue_cf_parallelcall` WHERE `_queue_id` = '.$queue_id.' AND `number` ="' .$DB->escape($new_entry).'"');
			if (!$id)
				$DB->execute('INSERT INTO `queue_cf_parallelcall` (`_queue_id`,`number`) VALUES('.$queue_id.", '".$DB->escape($new_entry)."')");
		}
		//Save changes from the Old entrys:
		foreach ( $_REQUEST as $item => $comment ) {
			if (substr($item,0,7) == "number_" && $item != "number_new") {
				$id = substr($item,7);
				$DB->execute('UPDATE `queue_cf_parallelcall` SET `number`=\''.$DB->escape($comment).'\' WHERE `_queue_id`='.$queue_id.' AND `id`='.$DB->escape($id));
			}
		}
	} else if (isset($_REQUEST['delete'])) {
		$id = $_REQUEST['delete'];
		$DB->execute('DELETE from `queue_cf_parallelcall` WHERE `_queue_id`='.$queue_id.' AND `id`='.$DB->escape($id));
	}	
}
?>

<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo __('Warteschlange'); ?>:
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<?php

if (count($queues) <= 25) {
	echo '<select name="queue" onchange="this.form.submit();">', "\n";
	foreach ($queues as $q) {
		if (array_search($q['id'], $queue_groups) !== false) {
			echo '<option value="', $q['name'], '"', ($q['name']==$queue_ext ? ' selected="selected"' :''), '>', $q['name'], ' (', htmlEnt($q['title']), ')</option>', "\n";
		}
	}
	echo '</select>', "\n";
} else {
	echo '<input type="text" name="queue" value="', $queue_ext, '" size="7" maxlength="6" />', "\n";
}

?>
<input type="submit" value="<?php echo __('Anzeigen'); ?>" />
</form>
<hr size="1" />
<br />

<?php





$queue_exists = false;
if ($queue_ext != '') {
	/*
	$queue = @gs_queue_get( $queue_ext );
	if (isGsError($queue))
		$warnings[] = __('Fehler beim Abfragen der Warteschlange.') .' - '. $queue->getMsg();
	elseif (! is_array($queue))
		$warnings[] = __('Fehler beim Abfragen der Warteschlange.');
	else {
		$cf = @gs_queue_callforward_get( $queue_ext );
		if (isGsError($cf))
			$warnings[] = __('Fehler beim Abfragen der Rufumleitungen der Warteschlange.') .' - '. $cf->getMsg();
		elseif (! is_array($cf))
			$warnings[] = __('Fehler beim Abfragen der Rufumleitungen der Warteschlange.');
		else
			$queue_exists = true;
	}
	*/
	if ($queue) {
		# get call forwards
		#
		$callforwards = @gs_queue_callforward_get( $queue_ext );
		if (isGsError($callforwards)) {
			echo __('Fehler beim Abfragen der Rufumleitungen der Warteschlange.') .' - '. $callforwards->getMsg();
			return;  # return to parent file
		} elseif (! is_array($callforwards)) {
			echo __('Fehler beim Abfragen der Rufumleitungen der Warteschlange.');
			return;  # return to parent file
		} else
			$queue_exists = true;
	}
}
if (! $queue_exists) {
	echo __('Bitte w&auml;hlen Sie eine Warteschleife.');
	return;  # return to parent file
}

echo '<h3>', $queue['name'];
if ($queue['title'] != '')
	echo ' (', htmlEnt($queue['title']), ')';
echo '</h3>';

# find best match for std number
#
$number_std = '';
foreach ($callforwards as $_source => $_cases) {
	foreach ($_cases as $_case => $_info) {
		if ($_info['number_std'] != '') {
			$number_std = $_info['number_std'];
			break;
		}
	}
}
foreach ($callforwards as $_source => $_cases) {
	foreach ($_cases as $_case => $_info) {
		if ($_info['number_std'] != '' && $_info['active']=='std') {
			$number_std = $_info['number_std'];
			break;
		}
	}
}

# find best match for var number
#
$number_var = '';
foreach ($callforwards as $_source => $_cases) {
	foreach ($_cases as $_case => $_info) {
		if ($_info['number_var'] != '') {
			$number_var = $_info['number_var'];
			break;
		}
	}
}
foreach ($callforwards as $_source => $_cases) {
	foreach ($_cases as $_case => $_info) {
		if ($_info['number_var'] != '' && $_info['active']=='var') {
			$number_var = $_info['number_var'];
			break;
		}
	}
}

# find best match for vml number
#
$number_vml = '';
foreach ($callforwards as $_source => $_cases) {
	foreach ($_cases as $_case => $_info) {
		if ($_info['number_vml'] != '') {
			$number_vml = $_info['number_vml'];
			break;
		}
	}
}
foreach ($callforwards as $_source => $_cases) {
	foreach ($_cases as $_case => $_info) {
		if ($_info['number_vml'] != '' && $_info['active']=='vml') {
			$number_vml = $_info['number_vml'];
			break;
		}
	}
}

$show_number_vml = preg_replace( '/[^\d]/', '', $number_vml );

# find best match for unavail timeout
#
if ( @$callforwards['internal']['timeout']['active'] != 'no'
  && @$callforwards['external']['timeout']['active'] != 'no' )
{
	$timeout = ceil((
		(int)@$callforwards['internal']['timeout']['timeout'] +
		(int)@$callforwards['external']['timeout']['timeout']
	)/2);
} elseif (@$callforwards['internal']['timeout']['active'] != 'no') {
	$timeout = (int)@$callforwards['internal']['timeout']['timeout'];
} elseif (@$callforwards['external']['timeout']['active'] != 'no') {
	$timeout = (int)@$callforwards['external']['timeout']['timeout'];
} else {
	$timeout = 15;
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


?>

<br />
<h2><?php echo __('Rufumleitung'); ?>
</h2>


<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="save" />
<input type="hidden" name="queue" value="<?php echo $queue_ext; ?>" />
<?php
$rs = $DB->execute('SELECT `id`, `description` FROM `systemrecordings`;');
$announce = array();
while ($r = $rs->fetchRow()) {
	$announce[$r['id']] = $r['id'] ;
	$announce_name[$r['id']] = $r['description'];
	}
?>
<table cellspacing="1">
<thead>
<tr>
	<th colspan="2"><?php echo __('Zielrufnummern f&uuml;r Anrufumleitung'); ?></th>
</tr>
</thead>
<tbody>
<tr class="even">
	<td style="width:170px;"><?php echo __('Standardnummer'); ?></td>
	<td style="width:400px;">
		<input type="text" name="num-std" id="ipt-num-std" value="<?php echo htmlEnt($number_std); ?>" size="30" style="width:220px;" maxlength="25" />
		<div id="num-select-std" style="display:none;">
		&larr;<select  width='100' style='width: 100px' id="sel-num-std" onchange="gs_num_sel(this);">
<?php
	if (! isGsError($announce) && is_array($announce)) {
		echo '<option value="">', __('Audiodateien') ,'</option>' ,"\n";
		foreach ($announce as $number) {
			echo '<option value="', htmlEnt('sysrec'.$number) ,'">', htmlEnt($number.' '.$announce_name[$number]) ,'</option>' ,"\n";
		}
	}
?>
		</select>
		</div>

	</td>
</tr>
<tr class="even">
	<td><?php echo __('Tempor&auml;re Nummer'); ?></td>
	<td>
		<input type="text" name="num-var" id="ipt-num-var" value="<?php echo htmlEnt($number_var); ?>" size="30" style="width:220px;" maxlength="25" />
		<div id="num-select-var" style="display:none;">
		&larr;<select width='100' style="width: 100px" id="sel-num-var" onchange="gs_num_sel(this);">
<?php
	if (! isGsError($announce) && is_array($announce)) {
		echo '<option value="">', __('Audiodateien') ,'</option>' ,"\n";
		foreach ($announce as $number) {
			echo '<option value="', htmlEnt('sysrec'.$number) ,'">', htmlEnt($number.' '.$announce_name[$number]) ,'</option>' ,"\n";
		}
	}
?>
		</select>
		</div>

	</td>
</tr>
<tr class="even">
	<td><?php echo __('AB-Nummer (interner Nutzer)'); ?></td>
	<td>
		<input type="text" name="num-vml" value="<?php echo htmlEnt($show_number_vml); ?>" size="30" style="width:220px;" maxlength="25" />
	</td>
</tr>
</tbody>
</table>
<script>
//<![CDATA[
// show selectors if javascript is available
try { document.getElementById('num-select-std').style.display = 'inline'; } catch(e){}
try { document.getElementById('num-select-var').style.display = 'inline'; } catch(e){}
//]]>
</script>

<br />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="5"><?php echo __('Umleiten in folgenden F&auml;llen'); ?></th>
</tr>
</thead>
<tbody>
<tr class="even">
	<td>&nbsp;</td>
<?php

foreach ($cases as $case => $ctitle) {
	echo '<td style="width:85px;">', $ctitle, '</td>', "\n";
}
//echo '<td style="width:80px;">', __('AB'), '</td>', "\n";

?>
</tr>
<?php
foreach ($sources as $src => $srctitle) {  //internal, external
	echo '<tr>';
	echo '<td style="width:90px;">', __('von'), ' ', $srctitle, '</td>';
	foreach ($cases as $case => $ctitle) { //busy, offline, etc...
		echo '<td>';
		echo '<select name="', $src, '-', $case, '" />', "\n";
		foreach ($actives as $active => $atitle) { //voicemail, Std, tmp ...
			if ($active === 'vml') {
				echo '<option value="', $active, '"';
				if ($callforwards[$src][$case]['active'] === $active
				&&  substr($callforwards[$src][$case]['number_vml'],0,3) !== 'vm*')
					echo ' selected="selected"';
				echo '>', $atitle, '</option>', "\n";
				
				echo '<option value="', 'vmln' , '"';
				if ($callforwards[$src][$case]['active'] === $active
				&&  substr($callforwards[$src][$case]['number_vml'],0,3) === 'vm*')
					echo ' selected="selected"';
				echo '>', __('Ansg.') ,'</option>', "\n";
			} else if (substr($active,0,4) === 'vml-')  {
				//multiple ansagen mit AB
				$idx= $vm_rec_num_idx_table[(int) substr($active,4)];
				echo '<option value="', $active, '"';
				if ($callforwards[$src][$case]['active'] === 'vml' && $idx==$callforwards[$src][$case]['vm_rec_id'])
					echo "active! $idx\n";
				if ($callforwards[$src][$case]['active'] === 'vml'
				&& $idx==$callforwards[$src][$case]['vm_rec_id']
				&& substr($callforwards[$src][$case]['number_vml'],0,3) !== 'vm*' )
					echo ' selected="selected"';
				echo '>', $atitle, '</option>', "\n";
			} else if (substr($active,0,5) === 'vmln-') {
				//multiple ansagen ohne AB
				$idx= $vm_rec_num_idx_table[(int) substr($active,5)];
				echo '<option value="', $active, '"';
				if ($callforwards[$src][$case]['active'] === 'vml' 
				&& $idx==$callforwards[$src][$case]['vm_rec_id'] 
				&& substr($callforwards[$src][$case]['number_vml'],0,3) === 'vm*')
					echo ' selected="selected"';
				echo '>', $atitle, '</option>', "\n";
			}
			else {
				echo '<option value="', $active, '"';
				if ($callforwards[$src][$case]['active'] === $active)
					echo ' selected="selected"';
				echo '>', $atitle, '</option>', "\n";
			}
		}
		echo '</select>';
		echo '</td>', "\n";
	}


	/*foreach ($cases as $case => $ctitle) {
		echo '<td>';
		echo '<select name="', $src, '-', $case, '" />', "\n";
		foreach ($actives as $active => $atitle) {
			$s = ($callforwards[$src][$case]['active'] == $active) ? ' selected="selected"' : '';
			echo '<option value="', $active, '"', $s, '>', $atitle, '</option>', "\n";
		}
		echo '</select>';
		echo '</td>', "\n";
	}*/
	
	/*
	echo '<td>';
	echo '<select name="vm-', $src, '" />', "\n";
	echo '<option value="1"', $s, ($vm[$src .'_active'] ? ' selected="selected"' : ''), '>', __('An'), '</option>', "\n";
	echo '<option value="0"', $s, (!$vm[$src .'_active'] ? ' selected="selected"' : ''), '>', __('Aus'), '</option>', "\n";
	echo '</select>';
	echo '</td>', "\n";
	*/
	
	echo '</tr>', "\n";
}
?>

<tr>
	<td colspan="3">&nbsp;</td>
	<td>
		<?php echo __('nach'); ?>
		<input type="text" name="timeout" value="<?php echo $timeout; ?>" size="3" maxlength="3" class="r" />&nbsp;s
	</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td colspan="6" class="quickchars r">
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
<h2><?php echo __('Ansagen f&uuml;r den Anrufbeantworter dieser Warteschlange'); ?>
</h2>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="savevmrec" />
<input type="hidden" name="queue" value="<?php echo $queue_ext; ?>" />
<table cellspacing="1">
<thead>
<tr>
	<th colspan="6"><?php echo __('Ansagen'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:45px;"><?php echo __('Ansage'); ?></td>
	<td style="width:409px;"><?php echo __('Kommentar'); ?></td>
	<td style="width:42px;"></td>
</tr>
<?php
//loop all files
$rs = $DB->execute('SELECT * from `queue_vm_rec_messages` WHERE `_queue_id`='.$queue_id);

$ncnt=0;
while ($r = $rs->fetchRow()) {
	echo "<tr>\n";
	echo "<td>".++$ncnt."</td>";
	echo "<td>";
	echo '<input type="text" name="comment_'.$r['id'].'" value="'.htmlEnt($r['vm_comment']).'" size="50" maxlength="180"/>';
	echo "</td>";
	echo "<td>";
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'record='.$r['id'].'&amp;action=savevmrec&amp;queue='.$queue_ext) ,'">', '<img alt="', __('Sprachnachricht mit dem Telefon aufnehmen') ,'" src="', GS_URL_PATH,'crystal-svg/16/app/yast_PhoneTTOffhook.png" />', '</a>', "\n";	
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'].'&amp;action=savevmrec&amp;queue='.$queue_ext) ,'">', '<img alt="', __('Eintrag Entfernen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/editdelete.png" />', '</a>', "\n";
	echo "</td>";
	echo "</tr>\n";
}
?>

<tr>
	<td><?php echo __('Neu'); ?>:</td>
	<td>
		<input type="text" name="comment_new" value="" size="50" maxlength="180"/>
	</td>
	<td>
<?php echo '<button type="submit" name="save_new" value="1" title="', __('Eintrag speichern') ,'" class="plain"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>';?>
	</td>
</tr>
</tbody>
</table>
</form>

<br />
<h2><?php echo __('Zeitsteuerung');?></h2>
<br />
<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="savetimerule" />
<input type="hidden" name="queue" value="<?php echo $queue_ext; ?>" />
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th><?php echo __('Priorit&auml;t'); ?></th>
	<th><?php echo __('Wochentage'); ?></th>
	<th><?php echo __('Uhrzeit'); ?></th>
	<th><?php echo __('Ziel'); ?> </th>
	<th></th>
</tr>
</thead>
<tbody>
<?php
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
	$rs = $DB->execute('SELECT * from `queue_cf_timerules` WHERE `_queue_id`='. $queue_id.' ORDER BY `ord`');
	while (	$route = $rs->fetchRow() ) {
		echo '<input type="hidden" name="tr_'.$route['id'].'" value="'.$route['id'].'" />';
		echo '<tr><td>';
		echo $route['ord'];
		echo '</td><td>';
		foreach ($wdaysl as $col => $v) {
			echo '<span class="nobr"><input type="checkbox" name="'.$route['id'].'-d_',$col,'" id="ipt-'.$route['id'].'-r_d_',$col,'" value="1" ', ($route['d_'.$col] ? 'checked="checked" ' : ''), '/>';
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
		echo '<input type="text" name="'.$route['id'].'-r_h_from_h" value="', $hf, '" size="2" maxlength="2" class="r" />:';
		echo '<input type="text" name="'.$route['id'].'-r_h_from_m" value="', $mf, '" size="2" maxlength="2" class="r" /> -';
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
		echo '<input type="text" name="'.$route['id'].'-r_h_to_h" value="', $ht, '" size="2" maxlength="2" class="r" />:';
		echo '<input type="text" name="'.$route['id'].'-r_h_to_m" value="', $mt, '" size="2" maxlength="2" class="r" />';
		echo '</span>';
		echo '</td>', "\n";
		echo '<td>'."\n";

		echo '<select name="'.$route['id'].'-r_target" />', "\n";
		foreach ($timeruleactives as $active => $atitle) { //voicemail, Std, tmp ...
                     
			echo '<option value="', $active, '"';
			if ($route['target'] === $active)
				echo ' selected="selected"';
			echo '>', $atitle, '</option>', "\n";
		}
		echo '</select>';
		echo '</td>'. "\n";
		echo '<td>'."\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$route['id'].'&amp;action=deltimerule&amp;queue='.$queue_ext) ,'">', '<img alt="', __('Eintrag Entfernen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/editdelete.png" />', '</a>', "\n";
	
		if ($i > 0) {
			echo '&thinsp;<a href="', gs_url($SECTION, $MODULE, null, 'move='.$route['id'].'&amp;action=movetimeruleup&amp;queue='.$queue_ext) ,'">', '<img alt="', __('Eintrag nach oben schieben') ,'" src="', GS_URL_PATH,'img/move_up.gif" />', '</a>', "\n";
		} else {
			echo '&thinsp;<img alt="&uarr;" src="', GS_URL_PATH, 'img/move_up_d.gif" />';
		}
	
		if ($i < $rs->numRows()-1) {
			echo '&thinsp;<a href="', gs_url($SECTION, $MODULE, null, 'move='.$route['id'].'&amp;action=movetimeruledown&amp;queue='.$queue_ext) ,'">', '<img alt="', __('Eintrag nach unten schieben') ,'" src="', GS_URL_PATH,'img/move_down.gif" />', '</a>', "\n";
		} else {
			echo '&thinsp;<img alt="&darr;" src="', GS_URL_PATH, 'img/move_down_d.gif" />';
		}

		echo '</td>'."\n";
		echo '</tr>';
		$i++;
	}

	//new timerule...
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
	echo "</tbody>\n</table>";
	echo '<button type="submit" name="savetimerule" value="1" title="', __('Eintrag speichern') ,'" class="plain"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>';
?>
</form>
<br />
<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="saveparcall" />
<input type="hidden" name="queue" value="<?php echo $queue_ext; ?>" />
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th></th>
	<th><?php echo __('Nummer'); ?></th>
	<th></th>
</tr>
</thead>
<tbody>
<h2><?php echo __('Parallelruf');?></h2>
<?php
$rs = $DB->execute('SELECT * from `queue_cf_parallelcall` WHERE `_queue_id`='.$queue_id);
$ncnt=0;
while ($r = $rs->fetchRow()) {
	echo "<tr>\n";
	echo "<td>".++$ncnt."</td>";
	echo "<td>";
	echo '<input type="text" name="number_'.$r['id'].'" value="'.htmlEnt($r['number']).'" size="61" maxlength="20"/>';
	echo "</td>";
	echo "<td>";
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'].'&amp;action=saveparcall&amp;queue='.$queue_ext) ,'">', '<img alt="', __('Entfernen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/editdelete.png" />', '</a>', "\n";
	echo "</td>";
	echo "</tr>\n";
}
?>
<tr>
	<td><?php echo __('Neu'); ?>:</td>
	<td>
		<input type="text" name="number_new" value="" size="61" maxlength="20"/>
	</td>
	<td>
<?php echo '<button type="submit" name="save_new" value="1" title="', __('Eintrag speichern') ,'" class="plain"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>';?>
	</td>
</tr>
</tbody>
</table>
</form>
<br />