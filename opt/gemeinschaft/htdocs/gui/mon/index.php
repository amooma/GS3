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

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );

include_once( GS_DIR .'inc/gettext.php' );
require_once( GS_DIR .'inc/netmask.php' );
//set_error_handler('err_handler_die_on_err');

$remote_ip = @$_SERVER['REMOTE_ADDR'];
$allowed = false;
if (defined('GS_MONITOR_FROM_NET')) {
	$networks = explode(',', GS_MONITOR_FROM_NET);
	foreach ($networks as $net) {
		if (ip_addr_in_network( $remote_ip, trim($net) )) {
			$allowed = true;
			break;
		}
	}
}
if (! $allowed) {
	header( 'HTTP/1.0 403 Forbidden', true, 403 );
	header( 'Status: 403 Forbidden', true, 403 );
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: private, no-cache, must-revalidate' );
	header( 'Expires: 0' );
	header( 'Vary: *' );
	header( 'Content-Type: text/plain' );
	echo "Not allowed for $remote_ip.\nSee config.\n";
	die();
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de-DE" xml:lang="de-DE">
<head><![CDATA[<!--
                Gemeinschaft
  @(_)-----(_)  (c) 2007, amooma GmbH - http://www.amooma.de/
 @   / ### \    Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
 @  |  ###  |   Philipp Kempgen <philipp.kempgen@amooma.de>
  @@|_______|   Peter Kozak <peter.kozak@amooma.de>
                                                      GNU GPL -->]]>
<title>Gemeinschaft Extension Monitor</title>
<script type="text/javascript" src="comm.js"></script>
<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<style type="text/css">

body {
	background-color: #444;
}

.number-block-900 td {
	padding: 0.3em;
}
.number-block-100 td {
	border-width: 1px;
	border-style: solid;
	font-family: Courier, Courier New, monospace;
	font-size: 9pt;
	font-weight: normal;
	line-height: 0.9em;
	padding: 0.2em 0.3em 0.02em 0.3em;
	background-color: #444;
	color: #434;
	border-color: #333 #555 #555 #333;
	/*-moz-border-radius: 6px;*/
}
td.e_ukn { /* see above */ }
td.e_off {
	border-color: #000 #777 #777 #000;
	background-color: #666;
	color: #333;
}
td.e_idl {
	border-color: #000 #aaa #aaa #000;
	background-color: #eee;
	color: #000;
}
td.e_rng {
	border-color: #330 #990 #990 #330;
	background-color: #ff6;
	color: #540;
}
td.e_bsi {
	border-color: #030 #696 #696 #000;
	background-color: #0f8;
	color: #050;
}
td.e_bse {
	border-color: #006 #66f #66f #006;
	background-color: #36f;
	color: #039;
}

</style>
</head>
<body style="margin:0; padding:0.5em;">

<?php
echo '<table class="number-block-900" border="0" cellspacing="0" align="center">', "\n";
echo '<tr>', "\n";
for ($e=100; $e<1000; $e+=100) {
	echo '<td>';
	echo '<table class="number-block-100" border="0" cellspacing="3">', "\n";
	for ($y=0; $y<10; ++$y) {
		echo '<tr>';
		for ($x=0; $x<10; ++$x) {
			$ext = $e+10*$x+$y;
			echo '<td id="e', $ext, '">';
			echo $ext;
			echo '</td>';
		}
		echo '</tr>', "\n";
	}
	echo '</table>', "\n";
	echo '</td>', "\n";
	if ($e % 300 == 0) echo '</tr><tr>', "\n";
}
echo '</tr>', "\n";
echo '</table>', "\n";
?>



<div style="float:left; width:40%; color:#222;"><small>Status: <span id="mon-status">---</span></small></div>

<div id="copyright" style="float:right; width:40%; text-align:right; color:#222;"><small>&copy; amooma gmbh</small></div>

<br />


<?php /*
<script type="text/javascript">
function demo() {
	var el = null;
	for (var ext=100; ext<=999; ++ext) {
		el = document.getElementById('e'+ext);
		if (! el) continue;
		
		var c;
		var r = Math.random()*100;
		
		if      (r > 98) c = 'e_off';
		else if (r > 70) c = 'e_idl';
		else if (r > 69) c = 'e_rng';
		else if (r > 66) c = 'e_bsi';
		else if (r > 63) c = 'e_bse';
		else             c = 'e_ukn';
		
		el.className = c;
	}
}
demo();
//window.setInterval('demo();', 2000);
</script>
*/ ?>


