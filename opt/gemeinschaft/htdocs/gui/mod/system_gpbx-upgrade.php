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

######################################################
##
##   ALL STRINGS IN HERE NEED TO BE TRANSLATED!
##
######################################################

defined('GS_VALID') or die('No direct access.');
require_once( GS_DIR .'inc/quote_shell_arg.php' );
//require_once( GS_DIR .'inc/find_executable.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

if (gs_get_conf('GS_INSTALLATION_TYPE') !== 'gpbx') {
	echo "Not available on non-GPBX systems.\n";
	return;
}

$gpbx_userdata = '/mnt/userdata/';
$disk_free_spare_mb = 10;   # MB, not MiB



function _encode_val( $str )
{
	return urlEncode($str);
}

function _upgrade_info_decode_val( $str )
{
	return urlDecode($str);
}

function _find_disk( $preferred_device=null )
{
	$devs = array();
	if ($preferred_device) {
		$devs[] = $preferred_device;
	}
	
	$tmp = @glob('/dev/hd?');
	if (is_array($tmp)) {
		foreach ($tmp as $dev) {
			$devs[] = baseName($dev);
		}
	}
	
	foreach ($devs as $dev) {
		$filetype = @fileType('/dev/'.$dev);
		if ($filetype === 'block') {
			if (file_exists('/proc/ide/'.$dev.'/media')) {
				if (trim(@file_get_contents('/proc/ide/'.$dev.'/media')) === 'disk') {
					return $dev;
				}
			}
		}
	}
	
	return null;
}


$installed_gpbx_vers = @gs_file_get_contents( '/etc/gemeinschaft/.gpbx-version' );
$installed_gs_vers   = @gs_file_get_contents( '/etc/gemeinschaft/.gemeinschaft-version' );
echo "\n";
echo '<!-- installed gpbx version: ', preg_replace('/[\-]{2,}/', '-', htmlEnt($installed_gpbx_vers)) ,' -->' ,"\n";
echo '<!-- installed gemeinschaft version: ', preg_replace('/[\-]{2,}/', '-', htmlEnt($installed_gs_vers)) ,' -->' ,"\n";
echo "\n";



# set auto-check?
#

if (@$_POST['action'] === 'set-auto-check') {
	
	@exec( 'sudo sh -c '. qsa('echo -n "'. (@$_REQUEST['auto_check']==='1' ? 'yes':'no') .'" > '. qsa($gpbx_userdata.'upgrades/auto-check') .' 2>>/dev/null') .' 2>>/dev/null' );
	
}




# search for upgrades now?
#

if (@$_POST['action'] === 'upgrade-check-now') {
	
	@exec( 'sudo /usr/local/bin/gpbx-upgrade-info 2>>/dev/null' );
	clearStatCache();
	
}




# abort download?
#

if (@$_REQUEST['action'] === 'abort-download') {
	
	# abort the process:
	@exec( 'sudo killall curl 2>>/dev/null' );
	@exec( 'sudo kill -INT `cat /tmp/gpbx-downloading-upgrade.pid 2>>/dev/null` 2>>/dev/null' );
	$err=0; $out=array();
	@exec( 'sudo ps ax 2>>/dev/null | grep gpbx-upgrade-download | grep -v grep 2>>/dev/null', $out, $err );
	if ($err === 0) {
		foreach ($out as $line) {
			if (preg_match('/^\s*([0-9]+)/m', $line, $m)) {
				@exec( 'sudo kill -INT '. $m[1] .' 2>>/dev/null' );
			}
		}
	}
	
	# remove the download:
	@exec( 'sudo rm -f '. qsa($gpbx_userdata.'upgrades/dl/download') .' 2>>/dev/null' );
	
	# release the lock:
	@exec( 'sudo rm -f /tmp/gpbx-downloading-upgrade.pid 2>>/dev/null' );
	clearStatCache();
	
}




# delete downloaded upgrade?
#

if (@$_POST['action'] === 'delete'
&&  @$_POST['delete_confirmed'] === '1'
) {
	
	@exec( 'sudo rm -rf '. qsa($gpbx_userdata.'upgrades/upgrade-info') .' 2>>/dev/null' );
	@exec( 'sudo rm -rf '. qsa($gpbx_userdata.'upgrades/dl/download') .' 2>>/dev/null' );
	@exec( 'sudo rm -rf '. qsa($gpbx_userdata.'upgrades/dl/update_script.sh') .' 2>>/dev/null' );
	@exec( 'sudo sh -c '. qsa('echo -n "no" > '. qsa($gpbx_userdata.'upgrades/upgrade-avail') .' 2>>/dev/null') .' 2>>/dev/null' );
	
}




# do upgrade?
#

if (@$_POST['action'] === 'upgrade'
&&  @$_POST['upgrade_confirmed'] === '1'
&&  ! file_exists('/tmp/gpbx-downloading-upgrade.pid')
&&  file_exists($gpbx_userdata.'upgrades/upgrade-info')
) {
	
	$upgrade_info = @gs_file_get_contents($gpbx_userdata.'upgrades/upgrade-info');
	
	/*
	if (strToLower(trim(@shell_exec( 'file '. qsa($gpbx_userdata.'upgrades/dl/download') .' 2>>/dev/null | grep -i -o tar 2>>/dev/null' ))) !== 'tar') {
		echo 'Fehlerhafter Dateityp. (tar erwartet.)';
		@exec( 'sudo rm -rf '. qsa($gpbx_userdata.'upgrades/dl/download') .' 2>>/dev/null' );
		return;
	}
	*/
	
	$gpbx_upgrade_cs_md5 = null;
	if (preg_match('/^\s*gpbx_upgrade_cs_md5\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		$gpbx_upgrade_cs_md5 = strToLower(_upgrade_info_decode_val($m[1]));
	}
	$gpbx_upgrade_cs_md5_real = strToLower(trim(@shell_exec( 'md5sum -b '. qsa($gpbx_userdata.'upgrades/dl/download') .' 2>>/dev/null | grep -i -o -E \'[0-9a-f]{32}\' 2>>/dev/null' )));
	if (! preg_match('/[0-9a-f]{32}/', $gpbx_upgrade_cs_md5_real)) {
		echo 'Runtergeladenes Upgrade konnte nicht verifiziert werden!';
		@exec( 'sudo rm -rf '. qsa($gpbx_userdata.'upgrades/dl/download') .' 2>>/dev/null' );
		return;
	}
	if ($gpbx_upgrade_cs_md5_real !== $gpbx_upgrade_cs_md5) {
		echo 'Fehlerhafter Download!';
		@exec( 'sudo rm -rf '. qsa($gpbx_userdata.'upgrades/dl/download') .' 2>>/dev/null' );
		return;
	}
	
	
	$flash_dev = _find_disk('hda');
	if (! $flash_dev) {
		echo 'Flash-Device nicht gefunden.';
		return;
	}
	$booter_partition = 1;  # counting from 1
	$booter_dev = $flash_dev.$booter_partition;
	
	$err=0; $out=array();
	@exec( 'sudo sh -c '. qsa('mkdir /mnt/booter && mount /dev/'.$booter_dev.' /mnt/booter && grub-set-default --root-directory=/mnt/booter 1') .' 2>>/dev/null', $out, $err );
	if ($err !== 0) {
		echo '<p class="text">', 'Bei der Vorbereitung des Upgrades ist ein Fehler aufgetreten.' ,'</p>' ,"\n";
	} else {
		echo '<p class="text"><big><b>', 'Das Upgrade wird nun installiert.<br />Bitte haben Sie Geduld!<br />Unterbrechen Sie keinesfalls die Stromzufuhr!<br />Das Upgrade dauert ca. 15 Minuten.' ,'</b></big></p>' ,"\n";
	}
	
	$err=0; $out=array();
	@exec( 'sudo sh -c '. qsa('echo -n "yes" > '. qsa($gpbx_userdata.'upgrades/upgrade-do') .' 2>>/dev/null') .' 2>>/dev/null', $out, $err );
	if ($err !== 0) {
		echo 'Fehler.';
		return;
	}
	
	if (@file_exists('/usr/sbin/gs-pre-shutdown')) {
		$err=0; $out=array();
		@exec( 'sudo /usr/sbin/gs-pre-shutdown 2>>/dev/null', $out, $err );
	}
	
	$err=0; $out=array();
	@exec( 'sudo sh -c '. qsa('sleep 1 ; shutdown -r now') .' 2>>/dev/null &', $out, $err );
	
	return;
	
}




# make sure to set upgrade-do to "no"
#

$upgrade_do_old = @gs_file_get_contents($gpbx_userdata.'upgrades/upgrade-do');
if ($upgrade_do_old != 'no') {
	@exec( 'sudo sh -c '. qsa('echo -n "no" > '. qsa($gpbx_userdata.'upgrades/upgrade-do') .' 2>>/dev/null') .' 2>>/dev/null', $out, $err );
}




# manual upload?
#

if (@$_POST['action'] === 'upload-upgrade'
&&  ! file_exists('/tmp/gpbx-downloading-upgrade.pid')
) {
	
	if (! defined('UPLOAD_ERR_NO_TMP_DIR')) define('UPLOAD_ERR_NO_TMP_DIR', 6);
	if (! defined('UPLOAD_ERR_CANT_WRITE')) define('UPLOAD_ERR_CANT_WRITE', 7);
	if (! defined('UPLOAD_ERR_EXTENSION' )) define('UPLOAD_ERR_EXTENSION' , 8);
	
	//print_r($_FILES);
	if (! is_array(@$_FILES['upgrade-file'])) {
		echo 'Error.';
		return;
	}
	switch (@$_FILES['upgrade-file']['error']) {
		case UPLOAD_ERR_NO_FILE:
			echo 'No file was uploaded.'; break;
		case UPLOAD_ERR_PARTIAL:
			echo 'The file was only partially uploaded.'; break;
		case UPLOAD_ERR_FORM_SIZE:
		case UPLOAD_ERR_INI_SIZE:
			echo 'The file exceeds the maximum size.'; break;
		case UPLOAD_ERR_NO_TMP_DIR:
			echo 'Temporary folder missing.'; break;
		case UPLOAD_ERR_CANT_WRITE:
			echo 'Failed to write file.'; break;
		case UPLOAD_ERR_EXTENSION:
			echo 'File upload stopped by extension.'; break;
	}
	if (@$_FILES['upgrade-file']['error'] !== UPLOAD_ERR_OK) {
		return;
	}
	if ((int)@$_FILES['upgrade-file']['size'] < 1) {
		echo 'File is empty.';
		return;
	}
	
	$disk_free_mb = (int)trim(@shell_exec( 'LANG=C df --block-size=1000000 '. qsa($gpbx_userdata.'upgrades/dl/') .' 2>>/dev/null | grep '. qsa(' /') .' | sed '. qsa('s/\s\s*/ /g') .' | cut -d '. qsa(' ') .' -f 4' ));
	$gpbx_upgrade_size_mb = ceil((int)@$_FILES['upgrade-file']['size'] / 1000000);
	if ($disk_free_mb < $gpbx_upgrade_size_mb + $disk_free_spare_mb) {
		echo 'Zu wenig Speicherplatz. (weniger als '. round($gpbx_upgrade_size_mb + $disk_free_spare_mb) .' MB)';
		return;
	}
	
	$out = @shell_exec( 'file '. qsa(@$_FILES['upgrade-file']['tmp_name']) .' 2>>/dev/null' );
	if (! preg_match('/tar/i', $out)) {
		echo 'Invalid file type.';
		return;
	}
	
	echo 'Extracting ...' ,'<br />',"\n"; @ob_flush(); @flush();
	set_time_limit(25*60);
	$extract_file_cmd = 'cd '. qsa($gpbx_userdata.'upgrades/') .' && tar -x --overwrite --no-same-owner --mode=0666 -f '. qsa($_FILES['upgrade-file']['tmp_name']) .' %s 2>&1';
	
	$err=0;
	@passThru('sudo sh -c '. qsa(sPrintF( $extract_file_cmd, qsa('./dl/download') ) .' 2>&1'), $err);
	if ($err != 0) {
		echo "<br />\nError while extracting ./dl/download .<br />\n";
		@exec('sudo rm -rf '. qsa( $gpbx_userdata.'upgrades/dl/download' ) .' 2>>/dev/null');
		return;
	}
	echo " * \n"; @ob_flush(); @flush();
	
	$err=0;
	@passThru('sudo sh -c '. qsa(sPrintF( $extract_file_cmd, qsa('./dl/update_script.sh') ) .' 2>&1'), $err);
	if ($err != 0) {
		echo "<br />\nError while extracting ./dl/update_script.sh .<br />\n";
		@exec('sudo rm -rf '. qsa( $gpbx_userdata.'upgrades/dl/download' ) .' 2>>/dev/null');
		@exec('sudo rm -rf '. qsa( $gpbx_userdata.'upgrades/dl/update_script.sh' ) .' 2>>/dev/null');
		return;
	}
	echo " * \n"; @ob_flush(); @flush();
	
	$err=0;
	@passThru('sudo sh -c '. qsa(sPrintF( $extract_file_cmd, qsa('./upgrade-info') ) .' 2>&1'), $err);
	if ($err != 0) {
		echo "<br />\nError while extracting ./upgrade-info .<br />\n";
		@exec('sudo rm -rf '. qsa( $gpbx_userdata.'upgrades/dl/download' ) .' 2>>/dev/null');
		@exec('sudo rm -rf '. qsa( $gpbx_userdata.'upgrades/dl/update_script.sh' ) .' 2>>/dev/null');
		@exec('sudo rm -rf '. qsa( $gpbx_userdata.'upgrades/upgrade-info' ) .' 2>>/dev/null');
		return;
	}
	echo " * \n"; @ob_flush(); @flush();
	
	@exec( 'sudo sh -c '. qsa('echo -n "yes" > '. qsa($gpbx_userdata.'upgrades/upgrade-avail') .' 2>>/dev/null') .' 2>>/dev/null' );
	echo " * \n"; @ob_flush(); @flush();
	
	sleep(1);
	clearStatCache();
	echo "Done extracting.<br />\n"; @ob_flush(); @flush();
	
}




# start download?
#

if (@$_POST['action'] === 'download-upgrade'
&&  ! file_exists('/tmp/gpbx-downloading-upgrade.pid')
&&  file_exists($gpbx_userdata.'upgrades/upgrade-info')
) {
	
	$upgrade_info = @gs_file_get_contents($gpbx_userdata.'upgrades/upgrade-info');
	/*$upgrade_info = '
gpbx_upgrade_file      = http%3A%2F%2Fwww.amooma.de%2Fgpbx-upgrade%2Fimage.img
gpbx_upgrade_size      = 260000000
gpbx_upgrade_req_size  = 550000000
gpbx_upgrade_cs_md5    = 50bd740c69e63abc6f83310113c95c2f
gpbx_upgrade_version   = 2
gpbx_upgrade_descr     = Ganz+viele+Verbesserungen%21
gpbx_upgrade_descr_url = http%3A%2F%2Fwww.amooma.de%2Fgpbx-upgrade%2Fchangelog-2.html
';*/
	
	$gpbx_upgrade_file = null;
	if (! preg_match('/^\s*gpbx_upgrade_file\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		echo 'Missing download URL.';
		return;
	}
	$m[1] = _upgrade_info_decode_val($m[1]);
	if (! preg_match('/^https?:\/\//', $m[1])) {
		echo 'Invalid download URL.';
		return;
	}
	$gpbx_upgrade_file = $m[1];
	
	$gpbx_upgrade_script = null;
	if (! preg_match('/^\s*gpbx_upgrade_script\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		echo 'Missing upgrade script URL.';
		return;
	}
	$m[1] = _upgrade_info_decode_val($m[1]);
	if (! preg_match('/^https?:\/\//', $m[1])) {
		echo 'Invalid upgrade script URL.';
		return;
	}
	$gpbx_upgrade_script = $m[1];
	
	$disk_free_mb = (int)trim(@shell_exec( 'LANG=C df --block-size=1000000 '. qsa($gpbx_userdata.'upgrades/dl/') .' 2>>/dev/null | grep '. qsa(' /') .' | sed '. qsa('s/\s\s*/ /g') .' | cut -d '. qsa(' ') .' -f 4' ));
	
	$gpbx_upgrade_size_mb = null;
	if (preg_match('/^\s*gpbx_upgrade_size\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		$gpbx_upgrade_size_mb = ceil((int)_upgrade_info_decode_val($m[1]) / 1000000);
	}
	if ($disk_free_mb < $gpbx_upgrade_size_mb + $disk_free_spare_mb) {
		echo 'Zu wenig Speicherplatz. (weniger als '. round($gpbx_upgrade_size_mb + $disk_free_spare_mb) .' MB)';
		return;
	}
	
	/*
	$gpbx_upgrade_req_size_mb = null;
	if (preg_match('/^\s*gpbx_upgrade_req_size\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		$gpbx_upgrade_req_size_mb = ceil((int)_upgrade_info_decode_val($m[1]) / 1000000);
	}
	if ($disk_free_mb < $gpbx_upgrade_req_size_mb + $disk_free_spare_mb) {
		echo 'Zu wenig Speicherplatz. (weniger als '. round($gpbx_upgrade_req_size_mb + $disk_free_spare_mb) .' MB)';
		return;
	}
	*/
	
	set_time_limit(20+30);
	$err=0; $out=array();
	//@exec( 'curl -s -S -I -m 20 --retry 0 -f -k -L --max-redirs 5 -A '. qsa('GPBX') .' -H '. qsa('Pragma: no-cache') .' -H '. qsa('Cache-Control: no-cache, no-store, max-age=0') .' -H '. qsa('Accept: application/x-tar;q=1.0, application/tar;q=0.9, application/octet-stream;q=0.8, */*;q=0.1') .' '. qsa($gpbx_upgrade_file) .' 2>&1', $out, $err );
	@exec( 'curl -s -S -I -m 20 --retry 0 -f -k -L --max-redirs 5 -A '. qsa('GPBX') .' -H '. qsa('Pragma: no-cache') .' -H '. qsa('Cache-Control: no-cache, no-store, max-age=0') .' -H '. qsa('Accept: */*') .' '. qsa($gpbx_upgrade_file) .' 2>&1', $out, $err );
	set_time_limit(30);
	$out = implode("\n", $out);
	if ($err !== 0) {
		echo 'Fehler beim Abfragen von Datei-Informationen per HTTP HEAD.' ,'<br />',"\n";
		echo '<pre>', htmlEnt($out) ,'</pre>';
		return;
	}
	
	$content_type = '';
	if (preg_match( '/^\s*Content-Type:\s*([a-z0-9\-_]+\/[a-z0-9\-_]+)/mi', $out, $m)) {
		$content_type = $m[1];
	}
	/*
	if (! in_array($content_type, array(
		'application/x-tar'  , 'application/tar'  ,
		'application/x-gtar' , 'application/gtar' ,
		'application/x-ustar', 'application/ustar',
		'application/octet-stream'
	), true)) {
		echo sPrintF('Fehlerhafter Content-Type. &quot;%s&quot; erwartet, &quot;%s&quot; erhalten', 'application/x-tar', htmlEnt($m[1])) ,'<br />' ,"\n";
		return;
	}
	*/
	
	if (! preg_match('/^\s*Content-Length:\s*([0-9]+)/mi', $out, $m)) {
		echo 'Fehler beim Abfragen der Dateigr&ouml;&szlig;e per HTTP HEAD.' ,'<br />',"\n";
		return;
	}
	$content_length_mb = ceil((int)$m[1] / 1000000);
	if ($disk_free_mb < $content_length_mb + $disk_free_spare_mb) {
		echo 'Zu wenig Speicherplatz. (weniger als '. round($content_length_mb + $disk_free_spare_mb) .' MB)';
		return;
	}
	
	$download_script = '/usr/local/bin/gpbx-upgrade-download';
	if (! file_exists($download_script)) {
		echo 'Error.';
		return;
	}
	//$download_script = '/opt/gpbx-svn/trunk/deb-factory/custom/gemeinschaft/usr-local-bin-gpbx-upgrade-download';
	$err=0; $out=array();
	@exec( 'sudo sh -c '. qsa( $download_script .' '. qsa($gpbx_upgrade_script) .' '. qsa($gpbx_upgrade_file) .' '. qsa(($content_length_mb+4)*1000000) .' '. qsa('GPBX') .' 1>>/dev/null 2>>/dev/null &') .' 0<&- 1>&- 2>&- &', $out, $err );
	//echo $err;
	//echo "<pre>", implode("\n",$out) ,"</pre>";
	if ($err !== 0) {
		echo 'Fehler.';
		return;
	}
	
	
	sleep(4);
	clearStatCache();
}




# download in progress?
#

if (file_exists('/tmp/gpbx-downloading-upgrade.pid')
|| (int)@shell_exec('sudo ps ax 2>>/dev/null | grep gpbx-upgrade-download | grep -v grep | wc -l') > 0) {
	
	echo '<br /><p>', 'Momentan wird ein Upgrade heruntergeladen.' ,'</p>' ,"\n";
	$upgrade_info = @gs_file_get_contents($gpbx_userdata.'upgrades/upgrade-info');
	//$upgrade_info = ' gpbx_upgrade_size = 250420000 ';
	if (preg_match('/^\s*gpbx_upgrade_size\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		$upgrade_size = (int)_upgrade_info_decode_val($m[1]);
		if ($upgrade_size > 50) {
			if (file_exists($gpbx_userdata.'upgrades/dl/download')) {
				$download_size = @fileSize($gpbx_userdata.'upgrades/dl/download');
				//$download_size = 210420000;
				if ($download_size !== false) {
					echo '<p>', 'Fortschritt' ,': &nbsp; <b>', number_format($download_size/$upgrade_size*100, 1, ',', '') ,' %</b>';
					if     ($upgrade_size > 1000000) {$factor = 1000000; $units = 'MB';}
					elseif ($upgrade_size >    1000) {$factor =    1000; $units = 'kB';}
					else                             {$factor =       1; $units =  'B';}
					echo ' &nbsp; (', round($download_size/$factor) ,' / ', round($upgrade_size/$factor) ,' ', $units ,')</p>' ,"\n";
					echo '<pre>';
					$chars_total = 60;
					$chars_done = floor($download_size/$upgrade_size*$chars_total);
					echo '|', str_repeat('=', $chars_done) ,'&gt;', str_repeat(' ', $chars_total-$chars_done) ,'|';
					echo '</pre><br />' ,"\n";
				}
			}
		}
	}
	echo '<br /><a href="', gs_url($SECTION, $MODULE) ,'"><button type="button">', 'Anzeige aktualisieren' ,'</button></a><br />' ,"\n";
	echo '<br /><a href="', gs_url($SECTION, $MODULE, null, 'action=abort-download') ,'"><button type="button">', 'Download abbrechen' ,'</button></a><br />' ,"\n";
	echo '<script type="text/javascript">' ,"\n";
	echo 'window.setTimeout("try{ document.location.href = \'', gs_url($SECTION, $MODULE) ,'\'; }catch(e){}", 10000);' ,"\n";
	echo '</script>' ,"\n";
	
	return;
}




# downloaded upgrade file available?
#

if (file_exists($gpbx_userdata.'upgrades/dl/download')
&&  file_exists($gpbx_userdata.'upgrades/upgrade-info')) {
	
	echo '<p>', 'Ein Upgrade wurde heruntergeladen.' ,'</p>' ,"\n";
	$upgrade_info = @gs_file_get_contents($gpbx_userdata.'upgrades/upgrade-info');
	/*$upgrade_info = '
gpbx_upgrade_file      = http%3A%2F%2Fwww.amooma.de%2Fgpbx-upgrade%2Fimage.img
gpbx_upgrade_size      = 260000000
gpbx_upgrade_req_size  = 550000000
gpbx_upgrade_cs_md5    = 50bd740c69e63abc6f83310113c95c2f
gpbx_upgrade_version   = 2
gpbx_upgrade_descr     = Ganz+viele+Verbesserungen%21
gpbx_upgrade_descr_url = http%3A%2F%2Fwww.amooma.de%2Fgpbx-upgrade%2Fchangelog-2.html
';*/
	
	$gpbx_upgrade_version = '';
	if (preg_match('/^\s*gpbx_upgrade_version\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		$gpbx_upgrade_version = _upgrade_info_decode_val($m[1]);
	}
	if ($gpbx_upgrade_version != '') {
		echo '<p><b>', 'Version' ,':</b> &nbsp; ', "\n";
		echo '<tt>', htmlEnt($gpbx_upgrade_version) ,'</tt></p>' ,"\n";
	}
	
	$gpbx_upgrade_descr = '';
	if (preg_match('/^\s*gpbx_upgrade_descr\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		$gpbx_upgrade_descr = _upgrade_info_decode_val($m[1]);
	}
	if ($gpbx_upgrade_descr != '') {
		echo '<b>', 'Beschreibung' ,':</b>', "\n";
		echo '<pre>', htmlEnt($gpbx_upgrade_descr) ,'</pre>' ,"\n";
	}
	
	$gpbx_upgrade_descr_url = '';
	if (preg_match('/^\s*gpbx_upgrade_descr_url\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		$m[1] = _upgrade_info_decode_val($m[1]);
		if (preg_match('/^https?:\/\//', $m[1])) {
			$gpbx_upgrade_descr_url = $m[1];
		}
	}
	if ($gpbx_upgrade_descr_url != '') {
		echo '<p><a target="_blank" href="', htmlEnt($gpbx_upgrade_descr_url) ,'">', 'Weitere Informationen' ,'</a></p>' ,"\n";
	}
	
	echo '<p class="text" style="background:#ffc; border:2px solid #fea; padding:0.5em; line-height:1.1em;">', '<b>Hinweis</b>: W&auml;hrend der Installation eines Upgrades darf die Stromzufuhr der GPBX nicht unterbrochen werden, sonst wird das System zerst&ouml;rt! Betreiben Sie die GPBX am besten an einer USV (Unterbrechungsfreie Strom-Versorgung). Die Installation erfolgt auf eigene Gefahr. Ihre Einstellungen und Daten werden nach M&ouml;glichkeit in das neue System &uuml;bernommen, dies kann jedoch nicht garantiert werden. Das Upgrade dauert ca. 15 Minuten.' ,'</p>' ,"\n";
	
	echo '<form method="post" action="', GS_URL_PATH ,'">' ,"\n";
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="action" value="upgrade" />' ,"\n";
	echo '<input type="checkbox" name="upgrade_confirmed" id="ipt-upgrade_confirmed" value="1" />' ,"\n";
	echo '<label for="ipt-upgrade_confirmed">', 'Hinweis gelesen' ,'</label><br />' ,"\n";
	echo '<input type="submit" value="', 'Upgrade durchf&uuml;hren' ,'" style="margin-top:5px; background:#fdd; color:#d00;" />' ,"\n";
	echo '</form>' ,"\n";
	
	echo '<br />' ,"\n";
	echo '<form method="post" action="', GS_URL_PATH ,'">' ,"\n";
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="action" value="delete" />' ,"\n";
	echo '<input type="checkbox" name="delete_confirmed" id="ipt-delete_confirmed" value="1" />' ,"\n";
	echo '<label for="ipt-delete_confirmed">', 'Runtergeladenes Upgrade l&ouml;schen' ,'</label><br />' ,"\n";
	echo '<input type="submit" value="', 'L&ouml;schen' ,'" style="margin-top:5px; background:#fdd; color:#d00;" />' ,"\n";
	echo '</form>' ,"\n";
	
	return;
}




# upgrade available?
#

if (trim(@gs_file_get_contents($gpbx_userdata.'upgrades/upgrade-avail')) === 'yes'
&&  file_exists($gpbx_userdata.'upgrades/upgrade-info')) {
	
	echo '<p>', 'Ein Upgrade ist verf&uuml;gbar.' ,'</p>' ,"\n";
	$upgrade_info = @gs_file_get_contents($gpbx_userdata.'upgrades/upgrade-info');
	/*$upgrade_info = '
gpbx_upgrade_file      = http%3A%2F%2Fwww.amooma.de%2Fgpbx-upgrade%2Fimage.img
gpbx_upgrade_size      = 260000000
gpbx_upgrade_req_size  = 550000000
gpbx_upgrade_cs_md5    = 50bd740c69e63abc6f83310113c95c2f
gpbx_upgrade_version   = 2
gpbx_upgrade_descr     = Ganz+viele+Verbesserungen%21
gpbx_upgrade_descr_url = http%3A%2F%2Fwww.amooma.de%2Fgpbx-upgrade%2Fchangelog-2.html
';*/
	
	$gpbx_upgrade_version = '';
	if (preg_match('/^\s*gpbx_upgrade_version\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		$gpbx_upgrade_version = _upgrade_info_decode_val($m[1]);
	}
	if ($gpbx_upgrade_version != '') {
		echo '<p><b>', 'Version' ,':</b> &nbsp; ', "\n";
		echo '<tt>', htmlEnt($gpbx_upgrade_version) ,'</tt></p>' ,"\n";
	}
	
	$gpbx_upgrade_descr = '';
	if (preg_match('/^\s*gpbx_upgrade_descr\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		$gpbx_upgrade_descr = _upgrade_info_decode_val($m[1]);
	}
	if ($gpbx_upgrade_descr != '') {
		echo '<b>', 'Beschreibung' ,':</b>', "\n";
		echo '<pre>', htmlEnt($gpbx_upgrade_descr) ,'</pre>' ,"\n";
	}
	
	$gpbx_upgrade_descr_url = '';
	if (preg_match('/^\s*gpbx_upgrade_descr_url\s*=\s*([^\s]*)/m', $upgrade_info, $m)) {
		$m[1] = _upgrade_info_decode_val($m[1]);
		if (preg_match('/^https?:\/\//', $m[1])) {
			$gpbx_upgrade_descr_url = $m[1];
		}
	}
	if ($gpbx_upgrade_descr_url != '') {
		echo '<p><a target="_blank" href="', htmlEnt($gpbx_upgrade_descr_url) ,'">', 'Weitere Informationen' ,'</a></p>' ,"\n";
	}
	
	echo '<br />',"\n";
	echo '<form method="post" action="', GS_URL_PATH ,'">' ,"\n";
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="action" value="download-upgrade" />' ,"\n";
	echo '<p class="text">', 'Wollen Sie das Upgrade herunterladen? W&auml;hrend des Downloads kann es - abh&auml;ngig von Ihrer Internet-Anbindung - zu Beeintr&auml;chtigungen der Sprachqualit&auml;t kommen.', '</p>',"\n";
	echo '<input type="submit" value="', 'Upgrade herunterladen' ,'" style="margin-top:5px; background:#fdd; color:#d00;" />' ,"\n";
	echo '</form>' ,"\n";
	
	return;
}






/*
$gpbx_version = trim(@gs_file_get_contents( '/etc/gemeinschaft/.gpbx-version' ));
echo '<p>Installierte GPBX-Version: ', ($gpbx_version != '' ? htmlEnt($gpbx_version) : '?') ,'</p>' ,"\n";
*/

?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="set-auto-check" />
<fieldset style="background:#dfd; max-width:48em;">
<legend style="font-weight:bold;"><?php echo 'Automatisch suchen'; ?></legend>
<p class="text" style="margin-bottom:0; padding-bottom:1px;"><?php
	echo 'Soll automatisch regelm&auml;&szlig;ig nach verf&uuml;gbaren Upgrades gesucht werden? (Dazu m&uuml;ssen Informationen &uuml;ber die momentan installierte Version und die Hardware &uuml;bermittelt werden.)';
?></p>
<?php
	$auto_check = trim(@gs_file_get_contents( $gpbx_userdata.'upgrades/auto-check' ));
?>
<input type="checkbox" name="auto_check" id="ipt-auto_check" value="1"<?php if ($auto_check === 'yes') echo ' checked="checked"'; ?> />
 <label for="ipt-auto_check"><?php echo 'Automatisch suchen'; ?></label><br />
<input type="submit" value="<?php echo 'Speichern'; ?>" style="margin-top:5px;" />
</fieldset>
</form>

<br />
<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="upgrade-check-now" />
<fieldset style="background:#dfd; max-width:48em;">
<legend style="font-weight:bold;"><?php echo 'Jetzt suchen'; ?></legend>
<p class="text" style="margin-bottom:0; padding-bottom:1px;"><?php
	echo 'Jetzt nach verf&uuml;gbaren Upgrades suchen?';
?></p>
<input type="submit" value="<?php echo 'Suchen'; ?>" style="margin-top:5px;" />
</fieldset>
</form>

<br />
<form method="post" action="<?php echo GS_URL_PATH; ?>" enctype="multipart/form-data">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="upload-upgrade" />
<fieldset style="background:#fdd; max-width:48em;">
<legend style="font-weight:bold;"><?php echo 'Manuell'; ?></legend>
<p class="text" style="margin-bottom:0; padding-bottom:1px;"><?php
	echo 'Upgrade manuell einspielen.' ,' <span style="color:#e00;">(', 'Nur f&uuml;r Entwickler!' ,')</span>';
?></p>
<input type="hidden" name="MAX_FILE_SIZE" value="350000000" />
Datei: <input type="file" name="upgrade-file" size="60" maxlength="350000000" style="font-size:10px;" /><br />
<input type="submit" value="<?php echo 'Hochladen'; ?>" style="margin-top:5px;" />
</fieldset>
</form>

