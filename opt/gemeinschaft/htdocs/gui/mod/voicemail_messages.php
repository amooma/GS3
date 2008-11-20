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
require_once( GS_DIR .'inc/quote_shell_arg.php' );


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

$formats = array( # internal name to info
	'wav-pcma' => array( 'title'=>'aLaw', 'ext'=>'alaw.wav', 'mime'=>'audio/x-wav'  ),
	//'pcma'     => array( 'title'=>'aLaw', 'ext'=>'al'      , 'mime'=>'audio/PCMA'   ),  # RFC 4856
	'mp3'      => array( 'title'=>'MP3' , 'ext'=>'mp3'     , 'mime'=>'audio/mpeg'   ),  # RFC 3003
	'sun-pcmu' => array( 'title'=>'Au'  , 'ext'=>'au'      , 'mime'=>'audio/basic'  ),  # RFC 2046
	'wav-pcm'  => array( 'title'=>'sLin', 'ext'=>'slin.wav', 'mime'=>'audio/x-wav'  ),
);
# For MIME types see http://www.iana.org/assignments/media-types/audio/
# Keep in sync with srv/vm-play.php


$fmt  = preg_replace('/[^a-z0-9\-_]/i', '', @$_REQUEST['fmt' ]);
if (! array_key_exists($fmt, $formats)) {
	reset($formats);
	$fmt = key($formats);
}

$GS_INSTALLATION_TYPE_SINGLE = gs_get_conf('GS_INSTALLATION_TYPE_SINGLE');
if (! $GS_INSTALLATION_TYPE_SINGLE) {
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
	
	$user_is_on_this_host = in_array($host['id'], $our_host_ids, true);
} else {
	$user_is_on_this_host = true;
}



//echo "<pre>"; print_r($_REQUEST); echo "</pre>";


?>

<script type="text/javascript">
/*<![CDATA[*/
function change_fmt()
{
	try {
		var play = document.location.search.match(/\bplay=([a-zA-Z0-9_\-.]+)/)[1];
		if (play) {
			var bts = document.getElementsByName('play');
			for (var i=0; i<bts.length; i++) {
				if (bts[i].tagName.toLowerCase() == 'button'
				&&  bts[i].value == play
				&&  bts[i].click)
				{
					bts[i].click();
					break;
				}
			}
		}
	} catch(e) {}
}
/*]]>*/
</script>

<form method="get" action="<?php echo gs_url($SECTION, $MODULE, null, 'action=play'); ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="play" />
<?php

echo '<div class="fr" style="clear:right; width:250px; padding:0 3px 4px 3px; margin-top:-5px;">' ,"\n";
foreach ($formats as $fmt_name => $fmt_info) {
	echo '<input type="radio" name="fmt" value="',$fmt_name,'" id="ipt-fmt-',$fmt_name,'"';
	if ($fmt_name === $fmt) echo ' checked="checked"';
	echo ' onchange="try{change_fmt();}catch(e){}" /><label for="ipt-fmt-',$fmt_name,'"';
	if ($fmt_name === 'wav-pcma') echo ' style="font-weight:bold;"';
	echo '><small>', htmlEnt($fmt_info['title']) ,'</small></label>' ,"\n";
}
echo "\n", '</div>' ,"\n";

if (@$_REQUEST['action']==='play') {
	
	/*
	$fld  = preg_replace('/[^a-z0-9\-_]/i', '', @$_REQUEST['fld' ]);
	$file = preg_replace('/[^a-z0-9\-_]/i', '', @$_REQUEST['file']);
	*/
	$play = explode('--', @$_REQUEST['play']);
	$fld  = preg_replace('/[^a-z0-9\-_]/i', '', @$play[0]);
	$file = preg_replace('/[^a-z0-9\-_]/i', '', @$play[1]);
	
	/*
	$vm_dir = '/var/spool/asterisk/voicemail/default/';
	$origfile = $vm_dir . @$_SESSION['sudo_user']['info']['ext'] .'/'. $fld .'/'. $file .'.gsm';
	$tmpfile = '/tmp/gs-vm-'. preg_replace('/[^0-9]/', '', @$_SESSION['sudo_user']['info']['ext']) .'-'. $fld .'-'. $file .'.gsm';
	
	$msg_exists = false;
	if (array_key_exists($fld, $folders)) {
		$out = array();
		if ($user_is_on_this_host) {
			# user is on this host
			if (file_exists( $origfile )) {
				@exec( 'sudo rm -rf '. qsa($tmpfile) );
				@exec( 'sudo cp '. qsa($origfile) .' '. qsa($tmpfile) .' && sudo chmod 666 '. qsa($tmpfile) );
				$msg_exists = true;
			}
		} else {
			# user is not on this host
			@exec( 'sudo rm -rf '. qsa($tmpfile) );
			@exec( 'sudo -u root cp '. qsa($origfile) .' '. qsa($tmpfile) .' && sudo -u root chmod 666 '. qsa($tmpfile) );
			
			$cmd = 'sudo scp -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa( $tmpfile ) .' '. qsa( 'root@'. $host['host'] .':'. $tmpfile );
			@ exec( $cmd .' 1>>/dev/null 2>&1', $out, $err );
			//@exec( 'sudo rm -rf '. qsa($tmpfile) );
			$msg_exists = true;
		}
	}
	
	if (! $msg_exists) {
		echo '?';
	}
	else {
		echo '<div class="fr" style="width:250px; border:1px solid #ccc; padding:4px; background:#eee;">', "\n";
		echo __('Player'), "\n";
		
		if (strPos(@$_SERVER['HTTP_USER_AGENT'], 'MSIE')===false) {
		?>
		
		<!-- W3 compliant version: -->
		<object
			id="player"
			type="audio/x-gsm"
			data="<?php echo GS_URL_PATH, 'srv/vm-play.php?sudo=', @$_SESSION['sudo_user']['name'], '&amp;fld=',$fld, '&amp;msg=',$file, '&amp;msie=.gsm'; ?>"
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
			data="<?php echo GS_URL_PATH, 'srv/vm-play.php?sudo=', @$_SESSION['sudo_user']['name'], '&amp;fld=',$fld, '&amp;msg=',$file, '&amp;msie=.gsm'; ?>"
			width="250"
			height="18"
			align="right"
			>
			<param name="src" value="<?php echo GS_URL_PATH, 'srv/vm-play.php?sudo=', @$_SESSION['sudo_user']['name'], '&amp;fld=',$fld, '&amp;msg=',$file, '&amp;msie=.gsm'; ?>" />
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
	*/
	
	echo '<div class="fr" style="clear:right; width:250px; border:1px solid #ccc; padding:3px 3px 1px 3px; background:#eee;">' ,"\n";
	//echo __('Player') ,"\n";
	
	$audio_url_base_esc
		= GS_URL_PATH .'srv/vm-play.php?sudo='. @$_SESSION['sudo_user']['name']
		.'&amp;fld='.$fld
		.'&amp;msg='.$file
		.'&amp;fmt='.$fmt
		;
	$audio_url_esc
		= $audio_url_base_esc
		.'&amp;disp=inline'
		.'&amp;msie=.'. $formats[$fmt]['ext']
		;
	$audio_url_dl_esc
		= $audio_url_base_esc
		.'&amp;disp=attach'
		.'&amp;msie=.'. $formats[$fmt]['ext']
		;
	
	if (strPos(@$_SERVER['HTTP_USER_AGENT'], 'MSIE')===false) {
?>
	
	<!-- W3 compliant version: -->
	<object
		id="player"
		type="<?php echo $formats[$fmt]['mime']; ?>"
		data="<?php echo $audio_url_esc; ?>"
		width="250"
		height="22"
		align="right"
		>
		<param name="autoplay" value="true" />
		<param name="controller" value="true" />
		<small><?php echo htmlEnt(sPrintF(__("Datei nicht gefunden, Konvertierungsfehler oder fehlendes Plugin f\xC3\xBCr %s"), $formats[$fmt]['title'])); ?></small>
	</object>
	
<?php
	} else {
?>
	
	<!-- MSIE version (for QuickTime ActiveX): -->
	<object
		id="player"
		type="<?php echo $formats[$fmt]['mime']; ?>"
		classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
		codebase="http://www.apple.com/qtactivex/qtplugin.cab"
		data="<?php echo $audio_url_esc; ?>"
		width="250"
		height="22"
		align="right"
		>
		<param name="src" value="<?php echo $audio_url_esc; ?>" />
		<param name="autoplay" value="true" />
		<param name="controller" value="true" />
		<small><?php echo htmlEnt(sPrintF(__("Datei nicht gefunden, Konvertierungsfehler oder fehlendes Plugin f\xC3\xBCr %s"), $formats[$fmt]['title'])); ?></small>
	</object>
	
<?php
	}
	echo '<br />';
	echo '<div class="r" style="font-size:80%; line-height:100%;"><a href="', $audio_url_dl_esc ,'" style="text-decoration:none;" target="_blank"><img alt="+" title="', htmlEnt(__("In neuem Fenster \xC3\xB6ffnen")) ,'" src="', GS_URL_PATH ,'img/new-window.gif" /></a></div>';
	echo "\n", '</div>' ,"\n";
}




echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";





if (@$_REQUEST['action']==='del') {
	
	$fld  = preg_replace('/[^a-z0-9\-_]/i', '', @$_REQUEST['fld' ]);
	$file = preg_replace('/[^a-z0-9\-_]/i', '', @$_REQUEST['file']);
	
	if (array_key_exists($fld, $folders)) {
		$cmd = GS_DIR .'sbin/vm-local-del '. qsa( @$_SESSION['sudo_user']['info']['ext'] ) .' '. qsa($fld) .' '. qsa($file);
		$err=0; $out=array();
		if ($user_is_on_this_host) {
			# user is on this host
			@exec( 'sudo '. $cmd .' 2>>/dev/null', $out, $err );
		} else {
			# user is not on this host
			@exec( GS_DIR .'sbin/remote-exec-do '. qsa($host['host']) .' '. qsa($cmd) .' 10 2>>/dev/null', $out, $err );
		}
	}
	
}



/*
# get list
#
$cmd = GS_DIR .'sbin/vm-local-list '. qsa( @$_SESSION['sudo_user']['info']['ext'] );
$out = array();
if ($user_is_on_this_host) {
	# user is on this host
	@exec( 'sudo -u root '. $cmd .' 2>>/dev/null', $out, $err );
} else {
	# user is not on this host
	exec( GS_DIR .'sbin/remote-exec-do '. qsa($host['host']) .' '. qsa($cmd) .' 10 2>>/dev/null', $out, $err );
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
*/

$rs = $DB->execute(
'SELECT
	`id`, `host_id`, `folder` `fld`, `file`, `orig_time` `ts`, `dur`, `cidnum`, `cidname`, `listened_to`
FROM `vm_msgs`
WHERE
	`user_id`='. (int)@$_SESSION['sudo_user']['info']['id'] .'
ORDER BY `folder`, `orig_time`'
);
$msgs = array();
while ($msg = $rs->fetchRow()) {
	$msgs[$msg['fld']][] = $msg;
}


?>
<table cellspacing="1" class="phonebook" style="clear:right;">
<tbody>
<?php
foreach ($folders as $folder => $folder_title) {
	if ($folder=='INBOX' || $folder=='Old' || @count(@$msgs[$folder])>0) {
?>
<tr>
	<th colspan="5" class="transp"><h3><?php echo $folder_title; ?></h3></th>
</tr>
<tr>
	<th style="width:10px;">&nbsp;</th>
	<th style="width:135px;"><?php echo __('Datum'); ?> <small>&darr;</small></th>
	<th style="width:230px;"><?php echo __('Anrufer'); ?></th>
	<th style="width:45px;" class="r"><?php echo __('Dauer'); ?></th>
	<th style="width:105px;">&nbsp;</th>
</tr>
<?php

if (! is_array(@$msgs[$folder]) || count($msgs[$folder]) < 1) {
	echo '<tr class="odd"><td colspan="5"><i>- ', __('keine'), ' -</i></td></tr>', "\n";
} else {
	foreach ($msgs[$folder] as $i => $msg) {
		echo '<tr class="', ($i%2==0 ? 'odd':'even'), '">', "\n";
		
		echo '<td style="vertical-align:middle;">';
		//if ($folder=='INBOX')
		if (! $msg['listened_to'])
			echo '<img id="', htmlEnt( 'vm-'.$msg['fld'].'-'.$msg['file'].'-flag' ) ,'" alt=" " src="', GS_URL_PATH, 'img/star.gif" />';
		else
			echo '&nbsp;';
		echo '</td>', "\n";
		
		echo '<td>';
		echo htmlEnt(date_human( @$msg['ts'] ));
		echo '</td>', "\n";
		
		echo '<td>';
		if (@$msg['cidnum'] != '') {
			echo htmlEnt( $msg['cidnum'] );
			if (@$msg['cidname'] != '')
				echo ' &nbsp; (', htmlEnt( $msg['cidname'] ), ')';
		} else {
			echo '<i>', __('anonym'), '</i>';
		}
		echo '</td>', "\n";
		
		$dur = abs((int)@$msg['dur']);
		$dur_m = floor($dur/60);
		$dur_s = (int)($dur - $dur_m*60);
		echo '<td class="r">', $dur_m, ':', str_pad($dur_s, 2, '0', STR_PAD_LEFT), '</td>', "\n";
		
		echo '<td class="r">';
		//echo ' <a href="', gs_url($SECTION, $MODULE, null, 'action=play&amp;fld='. rawUrlEncode(@$msg['fld']) .'&amp;file='. rawUrlEncode(@$msg['file'])), '" title="', __('abspielen'), '"><img alt="', __('abspielen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/kmix.png" /></a>';
		echo ' <button class="submit plain" name="play" value="', rawUrlEncode(@$msg['fld'].'--'.@$msg['file']) ,'" title="', __('abspielen'), '"><img alt="', __('abspielen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/kmix.png" /></button>';
		if (@$msg['cidnum'] != '') {
			$sudo_url =
				(@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
				? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
			echo ' &nbsp; <a href="', GS_URL_PATH, 'srv/pb-dial.php?n=', htmlEnt(@$msg['cidnum']), $sudo_url, '" title="', __('w&auml;hlen'), '"><img alt="', __('w&auml;hlen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/yast_PhoneTTOffhook.png" /></a>';
		} else {
			echo ' &nbsp; <img alt="', __('w&auml;hlen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/yast_PhoneTTOffhook-cust-dis.png" />';
		}
		echo ' &nbsp;&nbsp; <a href="', gs_url($SECTION, $MODULE, null, 'action=del&amp;fld='. rawUrlEncode(@$msg['fld']) .'&amp;file='. rawUrlEncode(@$msg['file'])), '" title="', __('l&ouml;schen'), '"><img alt="l&ouml;schen" src="', GS_URL_PATH, 'img/trash.gif" /></a>';
		echo '</td>', "\n";
		
		echo '</tr>', "\n";
	}
}

?>
<tr>
	<td colspan="5" class="transp" style="height:0.8em;"></td>
</tr>
<?php
	}
}
?>
</tbody>
</table>
</form>
<?php


if (@$_REQUEST['action']==='play') {
?>

<script type="text/javascript">
/*<![CDATA[*/

function check_player()
{
	if (! document || ! document.getElementById) {
		window.clearInterval( player_interval );
		return;
	}
	var pl = document.getElementById('player');
	if (! pl) return;
	if (! pl.GetTime) {
		window.clearInterval( player_interval );
		return;
	}
	var playtime = 0;
	try{ playtime = pl.GetTime(); }catch(e){ return; }
	if (playtime <= 0) return;
	
	// player has started playing
	window.clearInterval( player_interval );
	var flag = document.getElementById('<?php echo "vm-$fld-$file-flag"; ?>');
	if (! flag) return;
	var par = flag.parentNode;
	if (! par || ! par.removeChild) return;
	par.removeChild(flag);
}

var player_interval = window.setInterval('check_player();', 2000);

/*]]>*/
</script>

<?php
}
?>
