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
require_once( GS_DIR .'inc/db_connect.php' );
//set_error_handler('err_handler_die_on_err');


header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Expires: 0' );
header( 'Vary: *' );
header( 'Content-Type: text/html; charset=utf-8' );


$remote_ip = @$_SERVER['REMOTE_ADDR'];
$allowed = false;
$networks = explode(',', gs_get_conf('GS_MONITOR_FROM_NET'));
foreach ($networks as $net) {
	if (ip_addr_in_network( $remote_ip, trim($net) )) {
		$allowed = true;
		break;
	}
}
if (! $allowed) {
	@header( 'HTTP/1.0 403 Forbidden', true, 403 );
	@header( 'Status: 403 Forbidden', true, 403 );
	@header( 'Content-Type: text/plain; charset=utf-8' );
	echo "Not allowed for $remote_ip.\nSee config.\n";
	die();
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de-DE" xml:lang="de-DE">
<head><!--<![CDATA[
                Gemeinschaft
  @(_)=====(_)  (c) 2007-2008, amooma GmbH - http://www.amooma.de/
 @   / ### \    Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
 @  |  ###  |   Philipp Kempgen <philipp.kempgen@amooma.de>
  @@|_______|   Peter Kozak <peter.kozak@amooma.de>
                                                      GNU GPL ]]>-->
<title>Gemeinschaft Extension Monitor</title>
<script type="text/javascript" src="../js/prototype.js"></script>
<script type="text/javascript" src="comm.js"></script>
<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<style type="text/css">

body {
	background-color: #444;
}

.nofloat { float: none; clear: both; }

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


#ext-self {
	border: 2px solid #e60;
	
	background-color: #fee;
	color: #930;
	width: 5em;
	font-size: 9pt;
	text-align: center;
	padding: 1px 0;
	margin: 0 0 0.5em 0;
}

.e_ukn {
	border-color: #888 #333 #222 #999;
	background-color: #666;
	color: #323;
}
.e_off {
	border-color: #888 #333 #222 #999;
	background-color: #666;
	color: #323;
}
.e_idl {
	border-color: #fff #333 #222 #fff;
	background-color: #eee;
	color: #000;
}
.e_rng {
	border-color: #990 #330 #330 #990;
	background-color: #ff6;
	color: #540;
}
.e_bsi {
	border-color: #696 #030 #030 #696;
	background-color: #0f8;
	color: #050;
}
.e_hld {
	border-color: #69a #033 #033 #69a;
	background-color: #0ea;
	color: #053;
}
.e_bse {
	border-color: #66f #006 #006 #66f;
	background-color: #36f;
	color: #039;
}

.e {
	display: block;
	position: relative;
	width: 8em; height: 2.2em;
	float: left;
	border-width: 1px;
	border-style: solid;
	/*
	border-color: #888 #333 #222 #999;
	background-color: #333;
	color: #323;
	*/
	font-family: Courier, Courier New, monospace;
	font-size: 9pt;
	font-weight: normal;
	line-height: 0.9em;
	/*padding: 0.2em 0.3em 0.02em 0.3em;*/
	margin: 0px 0px 2px 2px;
}

.e .num {
	/*float: left;*/
	position: absolute;
	top: 0; left: 1px;
	font-weight: bold;
	font-size: 1.5em;
	line-height: 0.9em;
}
.e .nam {
	/*float: right;*/
	position: absolute;
	top: 1px; right: 1px;
	font-family: Times, Times New Roman, serif;
	font-size: 8.8pt;
	line-height: 1em;
}
.e .link {
	position: absolute;
	bottom: 0px; right: 1px;
	font-family: Courier, Courier New, monospace;
	font-size: 8.8pt;
	line-height: 1em;
}

.extensions-block {
	/*border: 2px solid red;*/
	padding: 0.2em 0;
}

.first-digit {
	display: block;
	float: left;
	font-size: 12pt;
	width: 1.2em; height: 1em;
	/*border: 2px solid red;*/
	color: #eee;
}

#transport,
#dummy_iframe {
	/* some browsers do not load the src of 0x0px or display=none iframes! */
	width: 10px;
	height: 2px;
	display: block;
	margin: 0; padding: 0;
	background: transparent;
	border: 0px none transparent;
}

#mon-status .err { background:#d00; color:#fed; padding:1px 3px; }
#mon-status .ok  { background:transparent; color:#0c0; padding:1px 3px; }

</style>
<script type="text/javascript">
// <![CDATA[
<?php

# accepts strings like '200-205, 444, 555, 100-105'
function get_ext_ranges( $ext_ranges )
{
	$ext_ranges = explode(',', (string)$ext_ranges);
	$ret = array(
		'ranges'  => array(),
		'singles' => array()
	);
	foreach ($ext_ranges as $range) {
		$range = trim($range);
		if (! preg_match('/^([0-9]+)(?:\s*-\s*([0-9]+))?$/S', $range, $m)) continue;
		if (! array_key_exists(2, $m) || $m[2]===$m[1]) {
			$ret['singles'][] = $m[1];
		} else {
			if ($m[1] < $m[2])
				$ret['ranges'][] = array($m[1], $m[2]);
			else
				$ret['ranges'][] = array($m[2], $m[1]);
		}
	}
	return $ret;
}

function js_quote( $str )
{
	$str = str_replace(
		array( "'"  , "\n"  ),
		array( "\\'", '\\n' ),
		$str
	);
	return '\''. $str .'\'';
}

function cdata_escape( $str )
{
	return str_replace(']]>', '] ] >', $str);
}

function js_cdeq( $str )
{
	return js_quote( cdata_escape( $str ));
}



$ext_ranges = preg_replace('/\s+/', '', @$_REQUEST['extensions']);
//$ext_ranges = '200-205,444,555,100-95,2000-2005';

$ext_ranges = get_ext_ranges( $ext_ranges );
//echo "var gs_ext_info = {\n";

$db = gs_db_slave_connect();
$exts_display = array();
if ($db) {
	if (@is_array(@$ext_ranges['ranges'])) {
		foreach ($ext_ranges['ranges'] as $range) {
			$rs = $db->execute('SELECT `s`.`name` `ext`, `u`.`firstname` `fn`, `u`.`lastname` `ln` FROM `users` `u` JOIN `ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) WHERE `s`.`name`>=\''. $db->escape(@$range[0]) .'\' AND `s`.`name`<=\''. $db->escape(@$range[1]) .'\'');
			while ($r = $rs->fetchRow()) {
				if ((int)$r['ext'] < @$range[0]
				||  (int)$r['ext'] > @$range[1]) continue;
				
				$abbr = '';
				if ($r['fn'] != '') $abbr .= mb_subStr($r['fn'], 0, 1).'.';
				if ($r['ln'] != '') $abbr .= mb_subStr($r['ln'], 0, 3).'.';
				
				//echo js_cdeq($r['ext']) ,":{fn:", js_cdeq($r['fn']) ,",ln:", js_cdeq($r['ln']) ,",abbr:", js_cdeq($abbr) ,"},\n";
				$exts_display[$r['ext']] = array(
					'fn'   => $r['fn'  ],
					'ln'   => $r['ln'  ],
					'abbr' => $abbr
				);
			}
		}
	}
	if (@is_array(@$ext_ranges['singles'])
	&&  count($ext_ranges['singles']) > 0) {
		$sql_in = '';
		$i=0;
		foreach ($ext_ranges['singles'] as $ext) {
			if (!($i===0)) $sql_in .= ',';
			$sql_in .= '\''. $db->escape($ext) .'\'';
			++$i;
		}
		$rs = $db->execute('SELECT `s`.`name` `ext`, `u`.`firstname` `fn`, `u`.`lastname` `ln` FROM `users` `u` JOIN `ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) WHERE `s`.`name` IN ('. $sql_in .')');
		while ($r = $rs->fetchRow()) {
			$abbr = '';
			if ($r['fn'] != '') $abbr .= mb_subStr($r['fn'], 0, 1).'.';
			if ($r['ln'] != '') $abbr .= mb_subStr($r['ln'], 0, 3).'.';
			
			//echo js_cdeq($r['ext']) ,":{fn:", js_cdeq($r['fn']) ,",ln:", js_cdeq($r['ln']) ,",abbr:", js_cdeq($abbr) ,"},\n";
			$exts_display[$r['ext']] = array(
				'fn'   => $r['fn'  ],
				'ln'   => $r['ln'  ],
				'abbr' => $abbr
			);
		}
	}
	//echo "'dummy':{}\n";
}

//echo "};\n";

?>
// ]]>
</script>
</head>
<body style="margin:0; padding:0.5em;">
<iframe id="transport" src="about:blank"></iframe>
<iframe id="dummy_iframe" src="about:blank"></iframe>

<?php

if (! $db) {
	
	echo 'Could not connect to database!';
	
} else {
	
	//echo '<div id="ext-self">Eigene Leitung</div>' ,"\n";
	
	
	kSort($exts_display, SORT_STRING);
	echo '<div class="extensions-block">', "\n";
	$first_digit = null;
	foreach ($exts_display as $ext => $ext_info) {
		$new_first_digit = subStr($ext,0,1);
		if ($new_first_digit != $first_digit) {
			if ($first_digit !== null) {
				echo '<br class="nofloat" />', "\n";
				echo '</div>', "\n";
				echo '<div class="extensions-block">', "\n";
			}
			$first_digit = $new_first_digit;
			//echo '<div class="first-digit">', $first_digit ,'</div>' ,"\n";
		}
		echo '<div class="e e_ukn" id="e',$ext,'">';
		echo '<span class="num">', $ext ,'</span>';
		//echo '<span class="nam">', $ext_info['abbr'] ,'</span>';
		$ext_info['ln'] = $ext_info['ln'];
		$abbr = mb_strCut($ext_info['ln'], 0, 18-(strLen($ext)*2.8));
		if (mb_strLen($abbr) < 9 && trim($ext_info['fn']) != '')
			$abbr = mb_subStr($ext_info['fn'],0,1) .'. '. $abbr;
		if (mb_strLen($ext_info['ln']) > mb_strLen($abbr))
			$abbr = mb_strCut($abbr,0,-1) .'.';
		echo '<span class="nam">', htmlSpecialChars($abbr, ENT_QUOTES, 'UTF-8') ,'</span>';
		echo '<span class="link" id="e',$ext,'l"></span>';
		echo '</div>' ,"\n";
	}
	echo '<br class="nofloat" />', "\n";
	echo '</div>', "\n";
	
}

?>




<br />

<div style="float:left; width:40%; color:#ddd;"><small>Status: <span id="mon-status">---</span></small></div>

<div id="copyright" style="float:right; width:40%; text-align:right; color:#222;"><small>&copy; amooma gmbh</small></div>

<br class="nofloat" />

