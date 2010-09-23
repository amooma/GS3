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
include_once( GS_DIR .'inc/gs-fns/gs_callforward_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_email_address_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_email_notify_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_email_notify_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_vm_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_vm_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_numbers_get.php' );

require_once( GS_DIR .'inc/get-listen-to-ids.php' );
require_once( GS_DIR .'inc/remote-exec.php' );


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
/*
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
*/
echo __('Rufumleitung');
echo '</h2>', "\n";

function _pack_int( $int ) {
	$str = base64_encode(pack('N', $int ));
	return preg_replace('/[^a-z0-9]/i', '', $str);
}



function InitHinttoggleCall() {  //FIXME  --- fix what?
	$user=gs_user_get( $_SESSION['sudo_user']['name'] );

	$call   //= "Channel: Local/". $from_num_dial ."\n"
		= "Channel: local/toggle@toggle-cfwd-hint\n"
		. "MaxRetries: 0\n"
		. "WaitTime: 15\n"
		. "Context: toggle-cfwd-hint\n"
		. "Extension: toggle\n"
		. "Callerid: $user <Toggle>\n"
		. "Setvar: __user_id=".  $_SESSION['sudo_user']['info']['id'] ."\n"
		. "Setvar: __user_name=".  $_SESSION['sudo_user']['info']['ext'] ."\n"
		. "Setvar: CHANNEL(language)=". gs_get_conf('GS_INTL_ASTERISK_LANG','de') ."\n"
		. "Setvar: __is_callfile_origin=1\n"  # no forwards and no mailbox on origin side
		. "Setvar: __callfile_from_user=".  $_SESSION['sudo_user']['info']['ext'] ."\n"
		//. "Setvar: __record_file=".  $filename ."\n"
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
	} else {
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



$sources = array(
	'internal' => __('intern'),
	'external' => __('extern')
);
$cases = array(
	'always' => __('immer'),
	'busy'   => __('besetzt'),
	'unavail'=> __('keine Antw.'),
	//'offline'=> __('offline/DND'),
	'offline'=> __('abgemeldet')
);

$actives = array(
	'no'  => '-',
	'std' => __('Std.'),
	'var' => __('Tmp.'),
	//'vml' => __('AB'  )
);

$vm_rec_num_idx_table=array();


//loop Voicemail-Announce-Files
$rs = $DB->execute('SELECT * from `vm_rec_messages` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']);
$ncnt=0;
while ($r = $rs->fetchRow()) {
	$actives['vml-'.++$ncnt] = __('AB mit Ansage ').$ncnt;
	$vm_rec_num_idx_table[$ncnt] = $r['id'];
}
if ($ncnt==0)
  $actives['vml'] = __('AB');

$rs = $DB->execute('SELECT * from `vm_rec_messages` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']);
$ncnt=0;
while ($r = $rs->fetchRow()) {
	$actives['vmln-'.++$ncnt] = __('Ansage ').$ncnt;
}

$id = (int)$DB->executeGetOne('SELECT `_user_id` from `cf_timerules` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']);
if ($id) {
	$actives['trl'] = __('Zeitsteuerung');
}

$id = (int)$DB->executeGetOne('SELECT `_user_id` from `cf_parallelcall` WHERE `_user_id`='.$_SESSION['sudo_user']['info']['id']);
if ($id) {
	$actives['par'] = __('Parallelruf');
}

$show_email_notification = ! @$_SESSION['sudo_user']['info']['host_is_foreign'];


$warnings = array();

if (@$_REQUEST['action']==='save') {
	
	$num_std = preg_replace('/[^0-9vm]/', '', @$_REQUEST['num-std']);
	$num_var = preg_replace('/[^0-9vm]/', '', @$_REQUEST['num-var']);
	//$num_vml = 'vm'. $_SESSION['sudo_user']['info']['ext'];
	$timeout = abs((int)@$_REQUEST['timeout']);
	if ($timeout < 1) $timeout = 1;
	
	if ($num_std=='')
		$warnings['std-empty'] = __('Sie sollten eine Std.-Umleitungsnummer angeben! Sie wird f&uuml;r die Nicht-St&ouml;ren-Funktion am Telefon ben&ouml;tigt.');
	
	foreach ($sources as $src => $ignore) {
		foreach ($cases as $case => $gnore2) {
			$ret = gs_callforward_set( $_SESSION['sudo_user']['name'],
				$src, $case, 'std', $num_std, $timeout );
			if (isGsError($ret))
				$warnings['std'] = __('Fehler beim Setzen der Std.-Umleitungsnummer') .' ('. $ret->getMsg() .')';
			$ret = gs_callforward_set( $_SESSION['sudo_user']['name'],
				$src, $case, 'var', $num_var, $timeout );
			if (isGsError($ret))
				$warnings['var'] = __('Fehler beim Setzen der Tempor&auml;ren Umleitungsnummer') .' ('. $ret->getMsg() .')';
		
			$vmail_rec_num = 0;
			//Voicemail or just Announce-File
			if (substr(@$_REQUEST[$src.'-'.$case],0,5) === 'vmln-') {
				//Play only Announce-File with Number n
				$idx =(int)substr(@$_REQUEST[$src.'-'.$case],5);
				$vmail_rec_num = $vm_rec_num_idx_table[$idx];
				$num_vml = 'vm*'. $_SESSION['sudo_user']['info']['ext'];
				$_REQUEST[$src.'-'.$case] = 'vml';
			} else if (substr(@$_REQUEST[$src.'-'.$case],0,4) === 'vml-') {
				//Voicemail with Anncounce-File Number n
				$idx =(int)substr(@$_REQUEST[$src.'-'.$case],4);
				$vmail_rec_num = $vm_rec_num_idx_table[$idx];
				$num_vml = 'vm'. $_SESSION['sudo_user']['info']['ext'];
				$_REQUEST[$src.'-'.$case] = 'vml';
			} else if (@$_REQUEST[$src.'-'.$case] === 'vmln') {
				$num_vml = 'vm*'. $_SESSION['sudo_user']['info']['ext'];
				$_REQUEST[$src.'-'.$case] = 'vml';
			} else {
				$num_vml = 'vm' . $_SESSION['sudo_user']['info']['ext'];
			}
			$ret = gs_callforward_set( $_SESSION['sudo_user']['name'],
				$src, $case, 'vml', $num_vml, $timeout, $vmail_rec_num );
			if (isGsError($ret))
				$warnings['vml'] = __('Fehler beim Setzen der AB-Nummer') .' ('. $ret->getMsg() .')';
			$ret = gs_callforward_activate( $_SESSION['sudo_user']['name'],
				$src, $case, @$_REQUEST[$src.'-'.$case] );
			if (isGsError($ret))
				$warnings['act'] = __('Fehler beim Aktivieren der Umleitungsnummer') .' ('. $ret->getMsg() .')';
		}
	}
	
	/*
	$vm_internal = (bool)@$_REQUEST['vm-internal'];
	$vm_external = (bool)@$_REQUEST['vm-external'];
	$ret = gs_vm_activate( $_SESSION['sudo_user']['name'], 'internal', $vm_internal );
	if (isGsError($ret))
		$warnings['vm_act_i'] = __('Fehler beim (De-)Aktivieren des Anrufbeantworters von intern') .' ('. $ret->getMsg() .')';
	$ret = gs_vm_activate( $_SESSION['sudo_user']['name'], 'external', $vm_external );
	if (isGsError($ret))
		$warnings['vm_act_e'] = __('Fehler beim (De-)Aktivieren des Anrufbeantworters von extern') .' ('. $ret->getMsg() .')';
	*/
	
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
	//Set Devstate for Customhint
	//InitHinttoggleCall();
}




# get call forwards
#
$callforwards = gs_callforward_get( $_SESSION['sudo_user']['name'] );
if (isGsError($callforwards)) {
	echo __('Fehler beim Abfragen.'), '<br />', $callforwards->getMsg();
	return;  # return to parent file
}

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
if ($number_std=='')
	$warnings['std-empty'] = __('Sie sollten eine Std.-Umleitungsnummer angeben! Sie wird f&uuml;r die Nicht-St&ouml;ren-Funktion am Telefon ben&ouml;tigt.');

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

# find best match for unavail timeout
#
if ( @$callforwards['internal']['unavail']['active'] != 'no'
  && @$callforwards['external']['unavail']['active'] != 'no' )
{
	$timeout = ceil((
		(int)@$callforwards['internal']['unavail']['timeout'] +
		(int)@$callforwards['external']['unavail']['timeout']
	)/2);
} elseif (@$callforwards['internal']['unavail']['active'] != 'no') {
	$timeout = (int)@$callforwards['internal']['unavail']['timeout'];
} elseif (@$callforwards['external']['unavail']['active'] != 'no') {
	$timeout = (int)@$callforwards['external']['unavail']['timeout'];
} else {
	$timeout = 15;
}


/*
# get vm states
#
$vm = gs_vm_get( $_SESSION['sudo_user']['name'] );
if (isGsError($vm)) {
	echo __('Fehler beim Abfragen.'), '<br />', $vm->getMsg();
	return;  # return to parent file
}
*/



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


$e_numbers = gs_user_external_numbers_get( $_SESSION['sudo_user']['name'] );

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

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="save" />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="2"><?php echo __('Zielrufnummern f&uuml;r Anrufumleitung'); ?></th>
</tr>
</thead>
<tbody>
<tr class="even">
	<td style="width:157px;"><?php echo __('Standardnummer'); ?></td>
	<td style="width:392px;">
		<input type="text" name="num-std" id="ipt-num-std" value="<?php echo htmlEnt($number_std); ?>" size="25" style="width:200px;" maxlength="25" />
		<div id="ext-num-select-std" style="display:none;">
		&larr;<select name="_ignore-1" id="sel-num-std" onchange="gs_num_sel(this);">
<?php
	if (! isGsError($e_numbers) && is_array($e_numbers)) {
		echo '<option value="">', __('einf&uuml;gen &hellip;') ,'</option>' ,"\n";
		foreach ($e_numbers as $e_number) {
			//echo '<option value="', htmlEnt($e_number) ,'">', htmlEnt($e_number) ,'</option>' ,"\n";
			echo '<option value="0', htmlEnt($e_number) ,'">0', htmlEnt($e_number) ,'</option>' ,"\n";
		}
	}
?>
		</select>
		</div>
		<?php /* <small>(<?php echo __('nicht leer!'); ?>)</small> */ ?>
	</td>
</tr>
<tr class="even">
	<td><?php echo __('Tempor&auml;re Nummer'); ?></td>
	<td>
		<input type="text" name="num-var" id="ipt-num-var" value="<?php echo htmlEnt($number_var); ?>" size="25" style="width:200px;" maxlength="25" />
		<div id="ext-num-select-var" style="display:none;">
		&larr;<select name="_ignore-2" id="sel-num-var" onchange="gs_num_sel(this);">
<?php
	if (! isGsError($e_numbers) && is_array($e_numbers)) {
		echo '<option value="">', __('einf&uuml;gen &hellip;') ,'</option>' ,"\n";
		foreach ($e_numbers as $e_number) {
			//echo '<option value="', htmlEnt($e_number) ,'">', htmlEnt($e_number) ,'</option>' ,"\n";
			echo '<option value="0', htmlEnt($e_number) ,'">0', htmlEnt($e_number) ,'</option>' ,"\n";
		}
	}
?>
		</select>
		</div>
	</td>
</tr>
</tbody>
</table>

<script type="text/javascript">
//<![CDATA[
// show selectors if javascript is available
try { document.getElementById('ext-num-select-std').style.display = 'inline'; } catch(e){}
try { document.getElementById('ext-num-select-var').style.display = 'inline'; } catch(e){}
//]]>
</script>

<br />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="5"><?php echo __('Umleiten in folgenden F&auml;llen'); ?></th>
<?php /* <th>&nbsp;</th> */ ?>
</tr>
</thead>
<tbody>
<tr class="even">
	<td style="width:110px;">&nbsp;</td>
<?php

foreach ($cases as $case => $ctitle) {
	echo '<td style="width:100px;">', $ctitle, '</td>', "\n";
}
//echo '<td style="width:80px;">', __('AB'), '</td>', "\n";

?>
</tr>
<?php
foreach ($sources as $src => $srctitle) {
	echo '<tr>';
	echo '<td>', __('von'), ' ', $srctitle, '</td>';
	
	foreach ($cases as $case => $ctitle) {
		echo '<td>';
		echo '<select name="', $src, '-', $case, '" />', "\n";
		foreach ($actives as $active => $atitle) {
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
				echo '>', __('Ansage') ,'</option>', "\n";
			}
			elseif (substr($active,0,4) === 'vml-')  {
				//multiple ansagen mit AB
				$idx= $vm_rec_num_idx_table[(int) substr($active,4)];
				echo '<option value="', $active, '"';
				if ($callforwards[$src][$case]['active'] === 'vml'
				&& $idx==$callforwards[$src][$case]['vm_rec_id']
				&& substr($callforwards[$src][$case]['number_vml'],0,3) !== 'vm*' )
				echo ' selected="selected"';
				echo '>', $atitle, '</option>', "\n";
			}
			elseif (substr($active,0,5) === 'vmln-') {
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
	
	/*
	echo '<td>';
	echo '<select name="vm-', $src, '" />', "\n";
	echo '<option value="1"', ( $vm[$src .'_active'] ? ' selected="selected"' : ''), '>', __('An'), '</option>', "\n";
	echo '<option value="0"', (!$vm[$src .'_active'] ? ' selected="selected"' : ''), '>', __('Aus'), '</option>', "\n";
	echo '</select>';
	echo '</td>', "\n";
	*/
	
	echo '</tr>', "\n";
}

$email_notify = (int)gs_user_email_notify_get( $_SESSION['sudo_user']['name'] );
$email_address = gs_user_email_address_get( $_SESSION['sudo_user']['name'] );
?>

<tr>
	<td colspan="3">&nbsp;</td>
	<td>
		<?php echo __('nach'); ?>
		<input type="text" name="timeout" value="<?php echo $timeout; ?>" size="3" maxlength="3" class="r" />&nbsp;s
	</td>
	<td colspan="1">&nbsp;</td>
<?php /* <td colspan="1">&nbsp;</td> */ ?>
</tr>
<tr>
<?php /*
	<td colspan="6" class="transp">
		<small><?php echo __('Achtung: Ihr Anrufbeantworter wird nur dann aktiv, wenn Sie keine Weiterleitung eingestellt haben.'); ?></small>
	</td>
*/ ?>
</tr>
</tbody>
</table>

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
	<td style="width:409px;">
		<input type="text" name="email_address" value="<?php echo htmlEnt($email_address); ?>" size="40" maxlength="50" disabled="disabled" />
	</td>
</tr>
<tr>
	<td><?php echo __('Benachrichtigung'); ?></td>
	<td>
<?php
	$disabled = ($email_address == '');
	if ($disabled) $email_notify = false;
	
	echo '<input type="radio" name="email_notify" value="on" id="ipt-email_notify-on"';
	if ($email_notify) echo ' checked="checked"';
	if ($disabled) echo ' disabled="disabled"';
	echo ' />';
	echo '<label for="ipt-email_notify-on">', __('an') ,'</label>' ,"\n";
	
	echo '<input type="radio" name="email_notify" value="off" id="ipt-email_notify-off"';
	if (! $email_notify) echo ' checked="checked"';
	if ($disabled) echo ' disabled="disabled"';
	echo ' />';
	echo '<label for="ipt-email_notify-off">', __('aus') ,'</label>' ,"\n";
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
		<button type="submit">
			<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			<?php echo __('Speichern'); ?>
		</button>
	</td>
</tr>
</tbody>
</table>

</form>
