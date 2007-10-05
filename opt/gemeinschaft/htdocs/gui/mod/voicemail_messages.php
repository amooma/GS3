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
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/get-listen-to-ids.php' );


$folders = array(
	'INBOX'  => __('Neue'),
	'Old'    => __('Alte'),
	'Work'   => __('Arbeit'),
	'Family' => __('Familie'),
	'Friends'=> __('Freunde'),
	'Cust1'  => __('Verzeichnis 5'),
	'Cust2'  => __('Verzeichnis 6'),
	'Cust3'  => __('Verzeichnis 7'),
	'Cust4'  => __('Verzeichnis 8'),
	'Cust5'  => __('Verzeichnis 9')
);


# find host
#
$rs = $DB->execute(
'SELECT `u`.`host_id` `id`, `h`.`host`
FROM
	`users` `u` LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`)
WHERE
	`u`.`id`='. (int)@$_SESSION['sudo_user']['info']['id']
);
$host = $rs->fetchRow();
if (! $host)
	die( 'Failed to get host.' );

$our_host_ids = @gs_get_listen_to_ids();
if (! is_array($our_host_ids))
	die( 'Failed to get our host IDs.' );




if (@$_REQUEST['action']=='play') {
	
	$vm_dir = '/var/spool/asterisk/voicemail/default/';
	
	$fld  = preg_replace('/[^a-z\d]/i', '', @$_REQUEST['fld']);
	$file = preg_replace('/[^a-z\d]/i', '', @$_REQUEST['file']);
	
	$origfile = $vm_dir . @$_SESSION['sudo_user']['info']['ext'] .'/'. $fld .'/'. $file .'.gsm';
	$tmpfile = '/tmp/gs-vm-'. preg_replace('/[^\d]/', '', @$_SESSION['sudo_user']['info']['ext']) .'-'. $fld .'-'. $file .'.gsm';
	
	$msg_exists = false;
	
	if (array_key_exists($fld, $folders)) {
		$out = array();
		if (in_array($host['id'], $our_host_ids, true)) {
			# user is on this host
			if (file_exists( $origfile )) {
				@exec( 'sudo rm -rf '. escapeShellArg($tmpfile) );
				@exec( 'sudo cp '. escapeShellArg($origfile) .' '. escapeShellArg($tmpfile) .' && sudo chmod 666 '. escapeShellArg($tmpfile) );
				$msg_exists = true;
			}
		} else {
			# user is not on this host
			@exec( 'sudo rm -rf '. escapeShellArg($tmpfile) );
			@exec( 'sudo -u root cp '. escapeShellArg($origfile) .' '. escapeShellArg($tmpfile) .' && sudo -u root chmod 666 '. escapeShellArg($tmpfile) );
			
			$cmd = 'sudo scp -o StrictHostKeyChecking=no -o BatchMode=yes '. escapeShellArg( $tmpfile ) .' '. escapeShellArg( 'root@'. $host['host'] .':'. $tmpfile );
			@ exec( $cmd .' 1>>/dev/null 2>&1', $out, $err );
			//@exec( 'sudo rm -rf '. escapeShellArg($tmpfile) );
			$msg_exists = true;
		}
	}
	if ($msg_exists) {
		echo '<div class="fr" style="width:250px; border:1px solid #ccc; padding:4px; background:#eee;">', "\n";
		echo __('Player'), "\n";
		
		if (strPos(@$_SERVER['HTTP_USER_AGENT'], 'MSIE')===false) {
		?>
		
		<!-- W3 compliant version: -->
		<object
			id="player"
			type="audio/x-gsm"
			data="<?php echo GS_URL_PATH, 'vm-play.php?sudo=', @$_SESSION['sudo_user']['name'], '&amp;fld=', $fld, '&amp;msg=', $file, '&amp;msie=.gsm'; ?>"
			width="250"
			height="18"
			align="right"
			>
			<param name="autoplay" value="true" />
			<param name="controller" value="true" />
			<?php echo sPrintF(__('Ihr Browser kann die %s-Datei nicht abspielen.'), 'GSM'); ?>
			
		</object>
		
		<?php
		} else {
		?>
		
		<!-- MSIE version (for QuickTime ActiveX): -->
		<object
			id="player"
			type="audio/x-gsm"
			classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
			codebase="http://www.apple.com/qtactivex/qtplugin.cab"
			data="<?php echo GS_URL_PATH, 'vm-play.php?sudo=', @$_SESSION['sudo_user']['name'], '&amp;fld=', $fld, '&amp;msg=', $file, '&amp;msie=.gsm'; ?>"
			width="250"
			height="18"
			align="right"
			>
			<param name="src" value="<?php echo GS_URL_PATH, 'vm-play.php?sudo=', @$_SESSION['sudo_user']['name'], '&amp;fld=', $fld, '&amp;msg=', $file, '&amp;_=.gsm'; ?>" />
			<param name="autoplay" value="true" />
			<param name="controller" value="true" />
			<?php echo sPrintF(__('Ihr Browser kann die %s-Datei nicht abspielen.'), 'GSM'); ?>
			
		</object>
		
		<?php
		}
		?>
		
		</div>
		<?php
	}
}




echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";





if (@$_REQUEST['action']=='del') {
	
	$fld  = preg_replace('/[^a-z\d]/i', '', @$_REQUEST['fld']);
	$file = preg_replace('/[^a-z\d]/i', '', @$_REQUEST['file']);
	if (array_key_exists($fld, $folders)) {
		$cmd = GS_DIR .'sbin/vm-local-del '. escapeShellArg( @$_SESSION['sudo_user']['info']['ext'] ) .' '. escapeShellArg($fld) .' '. escapeShellArg($file);
		$out = array();
		if (in_array($host['id'], $our_host_ids, true)) {
			# user is on this host
			@exec( 'sudo -u root '. $cmd .' 2>>/dev/null', $out, $err );
		} else {
			# user is not on this host
			exec( GS_DIR .'sbin/remote-exec-do '. escapeShellArg($host['host']) .' '. escapeShellArg($cmd) .' 10 2>>/dev/null', $out, $err );
		}
	}
	
}




# get list
#
$cmd = GS_DIR .'sbin/vm-local-list '. escapeShellArg( @$_SESSION['sudo_user']['info']['ext'] );
$out = array();
if (in_array($host['id'], $our_host_ids, true)) {
	# user is on this host
	@exec( 'sudo -u root '. $cmd .' 2>>/dev/null', $out, $err );
} else {
	# user is not on this host
	exec( GS_DIR .'sbin/remote-exec-do '. escapeShellArg($host['host']) .' '. escapeShellArg($cmd) .' 10 2>>/dev/null', $out, $err );
}
$out = trim(implode("\n", $out));
$messages = @unserialize( @base64_decode( $out ) );
unset($out);
if (! is_array($messages))
	die( 'Failed to get message information.' );



# sort by time
# ...

# sort by folder
#
$msgs = array();
foreach ($messages as $msg) {
	$msgs[@$msg['fld']][] = $msg;
}


foreach ($folders as $folder => $folder_title) {
	if ($folder=='INBOX' || $folder=='Old' || @count(@$msgs[$folder])>0) {
?>

<h3><?php echo $folder_title; ?></h3>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:135px;"><?php echo __('Datum'); ?></th>
	<th style="width:230px;"><?php echo __('Anrufer'); ?></th>
	<th style="width:45px;" class="r"><?php echo __('Dauer'); ?></th>
	<th style="width:105px;">&nbsp;</th>
</tr>
</thead>
<tbody>
<?php

if (! is_array(@$msgs[$folder]) || count($msgs[$folder]) < 1) {
	echo '<tr class="odd"><td colspan="4"><i>- ', __('keine'), ' -</i></td></tr>', "\n";
} else {
	foreach ($msgs[$folder] as $i => $msg) {
		echo '<tr class="', ($i%2==0 ? 'odd':'even'), '">', "\n";
		
		echo '<td>';
		if ($folder=='INBOX')
			echo '<img alt=" " src="', GS_URL_PATH, 'img/star.gif" /> ';
		echo htmlEnt(date_human( @$msg['ts'] ));
		echo '</td>', "\n";
		
		echo '<td>';
		if (@$msg['cidnum'] != '') {
			echo htmlEnt( $msg['cidnum'] );
			if (@$msg['cidname'] != '')
				echo ' &nbsp; (', htmlEnt( $msg['cidname'] ), ')';
		} else
			echo '<i>', __('unbekannt'), '</i>';
		echo '</td>', "\n";
		
		$dur = abs((int)@$msg['dur']);
		$dur_m = floor($dur/60);
		$dur_s = (int)($dur - $dur_m*60);
		echo '<td class="r">', $dur_m, ':', str_pad($dur_s, 2, '0', STR_PAD_LEFT), '</td>', "\n";
		
		echo '<td>';
		echo ' <a href="', gs_url($SECTION, $MODULE), '&amp;action=play&amp;fld=', rawUrlEncode( @$msg['fld'] ), '&amp;file=', rawUrlEncode( @$msg['file'] ), '" title="', __('abspielen'), '"><img alt="', __('abspielen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/kmix.png" /></a>';
		if (@$msg['cidnum'] != '') {
			$sudo_url =
				(@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
				? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
			echo ' &nbsp; <a href="', GS_URL_PATH, 'pb-dial.php?n=', htmlEnt(@$msg['cidnum']), $sudo_url, '" title="', __('w&auml;hlen'), '"><img alt="', __('w&auml;hlen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/yast_PhoneTTOffhook.png" /></a>';
		} else {
			echo ' &nbsp; <img alt="', __('w&auml;hlen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/yast_PhoneTTOffhook-cust-dis.png" />';
		}
		echo ' &nbsp;&nbsp; <a href="', gs_url($SECTION, $MODULE), '&amp;action=del&amp;fld=', rawUrlEncode( @$msg['fld'] ), '&amp;file=', rawUrlEncode( @$msg['file'] ), '" title="', __('l&ouml;schen'), '"><img alt="l&ouml;schen" src="', GS_URL_PATH, 'img/trash.gif" /></a>';
		echo '</td>', "\n";
		
		echo '</tr>', "\n";
	}
}

?>
</tbody>
</table>
<br />

<?php
	}
}
?>
