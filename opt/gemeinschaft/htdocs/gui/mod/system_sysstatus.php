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

include_once( GS_DIR .'inc/gs-fns/gs_astphonebuttons.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";



function _secs_to_days( $secs )
{
	$ret = ($secs < 0) ? '- ' : '';
	$secs = abs($secs);
	$ret.= sPrintF('%d days %d:%02d:%02d',
		$secs / (60*60*24)     ,
		$secs / (60*60)    % 24,
		$secs / (60)       % 60,
		$secs / (1)        % 60
	);
	return $ret;
}

#####################################################################
#   LocaPhone-Version
#####################################################################


$versionfile_gemeinschaft="/opt/gemeinschaft/etc/.gemeinschaft-version";

if ( file_exists ($versionfile_gemeinschaft)) {

	$version = file_get_contents( $versionfile_gemeinschaft ); 
	

	
	if( strlen ( $version ) > 0 ) {
	
		echo '<h3>LocaPhone</h3>' ,"\n";
		echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">";
		echo '<b>',  __("Version:"), ' ' , htmlEnt($version), '</b>';
		echo "\n</pre>\n";
	}

}

#####################################################################
#   AstButtond-Version
#####################################################################


if (  GS_BUTTONDAEMON_USE == true ) {

	$version =  gs_buttondeamon_version() ;
	
	if( $version &&  strlen ( $version ) > 0 ) {
	
		echo '<h3>AstButtond</h3>' ,"\n";
		echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">";
		echo ' <b>', htmlEnt($version), '</b>';
		echo "\n</pre>\n";
	}

}



#####################################################################
#   date
#####################################################################

echo '<h3>Date</h3>' ,"\n";
$err=0; $out=array();
exec( 'date -R', $out, $err );
if ($err===0) {
	$out = trim(implode(' ', $out));
	echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">";
	echo htmlEnt($out);
	echo "\n</pre>\n";
} else {
	echo "<p>Error.</p>\n";
}



#####################################################################
#   uname
#####################################################################

echo '<h3>Kernel &amp; Architecture</h3>' ,"\n";
$err=0; $out=array();
exec( 'uname -r ; uname -v ; uname -m ; uname -o', $out, $err );
if ($err===0) {
	$out = trim(implode('  ', $out));
	$out = htmlEnt($out);
	$out = preg_replace('/(?<=^|\s)([1-9]\.[1-9]{1,2}[.0-9\-_a-zA-Z]+)/', '<b>$1</b>', $out);
	echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">\n";
	echo $out;
	echo "\n</pre>\n";
} else {
	echo "<p>Error.</p>\n";
}



#####################################################################
#   uptime & loadavg
#####################################################################

if (file_exists('/proc/uptime') && file_exists('/proc/loadavg')) {
	
	echo '<h3>Uptime</h3>' ,"\n";
	$out = trim(gs_file_get_contents('/proc/uptime'));
	$tmp = explode(' ', $out);
	$uptime = (float)(@$tmp[0]);
	echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">\n";
	echo date('Y-m-d H:i:s', time()-$uptime), ',  ';
	echo '<b>', _secs_to_days($uptime) ,'</b>', "\n";
	echo "</pre>\n";
	
	echo '<h3>Load average</h3>' ,"\n";
	$out = trim(gs_file_get_contents('/proc/loadavg'));
	$tmp = explode(' ', $out);
	echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">\n";
	echo '<b>', number_format((float)@$tmp[0],2,'.','') ,'</b> (1 min)  ';
	echo '<b>', number_format((float)@$tmp[1],2,'.','') ,'</b> (5 min)  ';
	echo '<b>', number_format((float)@$tmp[2],2,'.','') ,'</b> (15 min)';
	echo "\n</pre>\n";
	
} else {
	
	echo '<h3>Uptime &amp; Load Average</h3>' ,"\n";
	$err=0; $out=array();
	exec( 'LANG=C uptime', $out, $err );
	if ($err===0) {
		echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">\n";
		echo htmlEnt(trim(implode("\n", $out)));
		echo "\n</pre>\n";
	} else {
		echo "<p>Error.</p>\n";
	}
	
}



#####################################################################
#   meminfo
#####################################################################

echo '<h3>Memory info</h3>' ,"\n";
if (file_exists('/proc/meminfo')) {
	
	$out = trim(gs_file_get_contents('/proc/meminfo'));
	echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">\n";
	
	//echo 'Physical', "\n";
	if (preg_match('/^MemTotal:\s*([0-9]+)/mi', $out, $m)) {
		$memtotal = (int)((int)$m[1] * 1024 / 1000);  # kiB -> kB
		//echo '  Total: ', str_pad(ceil($memtotal/1024),4,' ',STR_PAD_LEFT) ,' MiB', "\n";
		if (preg_match('/^MemFree:\s*([0-9]+)/mi', $out, $m)) {
			$memfree = (int)((int)$m[1] * 1024 / 1000);  # kiB -> kB
			$memused = $memtotal - $memfree;
			//echo '  Used : ', str_pad(ceil($memused/1024),4,' ',STR_PAD_LEFT) ,' MiB', "\n";
			echo 'RAM : ',
				'<b>', str_pad(ceil($memtotal/1000),4,' ',STR_PAD_LEFT) ,'</b> MB, ',
				'<b>', str_pad(ceil($memused/$memtotal*100),2,' ',STR_PAD_LEFT) ,' %</b> used',
				"\n";
		}
	}
	//echo 'Swap', "\n";
	if (preg_match('/^SwapTotal:\s*([0-9]+)/mi', $out, $m)) {
		$memtotal = (int)((int)$m[1] * 1024 / 1000);  # kiB -> kB
		if ($memtotal > 0) {  # avoid division by 0
			//echo '  Total: ', str_pad(ceil($memtotal/1024),4,' ',STR_PAD_LEFT) ,' MiB', "\n";
			if (preg_match('/^SwapFree:\s*([0-9]+)/mi', $out, $m)) {
				$memfree = (int)((int)$m[1] * 1024 / 1000);  # kiB -> kB
				$memused = $memtotal - $memfree;
				//echo '  Used : ', str_pad(ceil($memused/1024),4,' ',STR_PAD_LEFT) ,' MiB', "\n";
				echo 'Swap: ',
					'<b>', str_pad(ceil($memtotal/1000),4,' ',STR_PAD_LEFT) ,'</b> MB, ',
					'<b>', str_pad(ceil($memused/$memtotal*100),2,' ',STR_PAD_LEFT) ,' %</b> used',
					"\n";
			}
		}
	}
	
	echo "</pre>\n";
	
} else {
	echo "<p>?</p>\n";
}



#####################################################################
#   df
#####################################################################

echo '<h3>Disk free</h3>' ,"\n";
$err=0; $out=array();
if (gs_get_conf('GS_INSTALLATION_TYPE') === 'gpbx') {
	exec( 'LANG=C df -H -x tmpfs | grep -v \' /live\'', $out, $err );
} else {
	exec( 'LANG=C df -H -T -x tmpfs', $out, $err );
}
if ($err===0) {
	$out = trim(implode("\n", $out));
	echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">\n";
	$out = htmlEnt($out);
	$out = preg_replace('/(\/dev(?:\/[a-zA-Z0-9\-_.]+)+)/', '<b>$1</b>', $out);
	# needs to match e.g. "/dev/hda1", "/dev/mapper/VolGroup00-LogVol00"
	echo $out;
	echo "\n</pre>\n";
} else {
	echo "<p>?</p>\n";
}



#####################################################################
#   net dev
#####################################################################

echo '<h3>Network devices</h3>' ,"\n";
if (file_exists('/proc/net/dev')) {
	
	$out = trim(gs_file_get_contents('/proc/net/dev'));
	echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">\n";
	echo htmlEnt($out);
	echo "\n</pre>\n";
	
} else {
	echo "<p>?</p>\n";
}



#####################################################################
#   Asterisk version
#####################################################################
echo '<h3>Asterisk version</h3>', "\n";
$err=0; $out=array();
@exec( 'sudo asterisk -rx \'core show version\' 2>>/dev/null', $out,
$err );
if ($err===0) {
	$out = trim(implode(' ', $out));
	$out = htmlEnt($out);
	$out = preg_replace('/(?<=^|\s|-)((branch-)?[1-9]\.[0-9][.0-9]*(?:\-r[0-9]+M?)?)/', '<b>$1</b>', $out);
	echo "<pre style=\"margin:0:1em 0.5em 1.2em 0.5em;\">";
	echo $out;
	echo "\n</pre>\n";
} else {
	echo "<p>Error.</p>\n";
}



echo "<br />\n";

?>
