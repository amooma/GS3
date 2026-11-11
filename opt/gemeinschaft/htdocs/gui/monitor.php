<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 2540 $
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
require_once( dirName(__FILE__) .'/../../inc/conf.php' );

include_once( GS_DIR .'inc/gettext.php' );
require_once( GS_DIR .'inc/netmask.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );  //FIXME
include_once( GS_DIR .'inc/extension-state.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/string.php' );
require_once( GS_DIR .'inc/ext-ranges.php' );


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
  @(_)=====(_)  (c) 2007-2010, AMOOMA GmbH - http://www.amooma.de/
 @   / ### \    Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
 @  |  ###  |   Philipp Kempgen <philipp.kempgen@amooma.de>
  @@|_______|   Peter Kozak <peter.kozak@amooma.de>
                                                      GNU GPL ]]>-->
<title><?php echo __('Gemeinschaft Monitor'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/original.css" />
<?php if ($GUI_ADDITIONAL_STYLESHEET = gs_get_conf('GS_GUI_ADDITIONAL_STYLESHEET')) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/<?php echo rawUrlEncode($GUI_ADDITIONAL_STYLESHEET); ?>" />
<?php } ?>
<link rel="shortcut icon" type="image/x-icon" href="<?php echo GS_URL_PATH; ?>favicon.ico" />
<!-- for stupid MSIE: -->
<!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/msie-fix-6.css" /><![endif]-->
<!--[if gte IE 7]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH; ?>styles/msie-fix-7.css" /><![endif]-->
<!--[if lt IE 7]><style type="text/css">img {behavior: url("js/pngbehavior.htc.php?msie-sucks=.htc");}</style><![endif]-->
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
</head>
<body>
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

.e_st {
	border-color: #888 #333 #222 #999;
	background-color: #444;
	color: #323;
}
.e_st4 { /* AST_MGR_EXT_OFFLINE */
	border-color: #888 #333 #222 #999;
	background-color: #666;
	color: #323;
}
.e_st0 { /* AST_MGR_EXT_IDLE */
	border-color: #fff #333 #222 #fff;
	background-color: #eee;
	color: #000;
}
.e_st8 { /* AST_MGR_EXT_RINGING */
	border-color: #990 #330 #330 #990;
	background-color: #ff6;
	color: #540;
}
.e_st9 { /* AST_MGR_EXT_RINGINUSE (AST_MGR_EXT_INUSE + AST_MGR_EXT_RINGING) */
	border-color: #990 #330 #330 #990;
	background-color: #ff6;
	color: #540;
}
.e_st1 { /* AST_MGR_EXT_INUSE */
	border-color: #696 #030 #030 #696;
	background-color: #0f8;
	color: #050;
}
.e_st16 { /* AST_MGR_EXT_ONHOLD */
	border-color: #69a #033 #033 #69a;
	background-color: #0ea;
	color: #053;
}
.e_st2 { /* AST_MGR_EXT_BUSY */
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

#transport {
	/* some browsers do not load the src of 0x0px or display=none iframes! */
	width: 10px;
	height: 2px;
	display: block;
	margin: 0; padding: 0;
	background: transparent;
	border: 0px none transparent;
}

</style>
<?php

$db = gs_db_slave_connect();
$exts_display = $db ? gs_ext_ranges_users( $db, @$_REQUEST['extensions'] ) : array();

kSort($exts_display, SORT_STRING);
echo '<div class="extensions-block">', "\n";
$first_digit = null;
foreach ($exts_display as $extension => $ext_info) {
	$extstate = gs_extstate_single( $extension );
	$img = '<img alt=" " src="'. GS_URL_PATH;
	switch ($extstate) {
		case  0: $img.= 'crystal-svg/16/act/greenled.png' ; break;
		case  2: $img.= 'crystal-svg/16/act/yellowled.png'; break;
		case  1: $img.= 'crystal-svg/16/act/redled.png'   ; break;
		default: $img.= 'crystal-svg/16/act/free_icon.png'; break;
	}
	$img.= '" /> ';
	$new_first_digit = subStr($extension,0,1);
	if ($new_first_digit != $first_digit) {
		if ($first_digit !== null) {
			echo '<br class="nofloat" />', "\n";
			echo '</div>', "\n";
			echo '<div class="extensions-block">', "\n";
		}
		$first_digit = $new_first_digit;
	}
	echo '<div class="e e_st'.$extstate.'" id="e'.$extension.'">';
	echo '<span class="num">', $extension ,'</span>';
	$abbr = mb_strCut($ext_info['ln'], 0, 18-(strLen($extension)*2.8));
	if (mb_strLen($abbr) < 9 && trim($ext_info['fn']) != '')
		$abbr = mb_subStr($ext_info['fn'],0,1) .'. '. $abbr;
	if (mb_strLen($ext_info['ln']) > mb_strLen($abbr))
		$abbr = mb_strCut($abbr,0,-1) .'.';
	echo '<span class="nam">', htmlEnt($abbr) ,'</span>';
	echo '<span class="link" id="e',$extension,'l"></span>';
	echo '</div>' ,"\n";
	//echo "<tr><td> $img $extension: ".$ext_info['abbr']."</td></tr>\n";
	
}

echo '<br class="nofloat" />', "\n";
echo '</div>', "\n";
echo "<table>";
echo "</table>";

?>

<script type="text/javascript">/*<![CDATA[*/
window.setTimeout('document.location.reload();', 9000);
/*]]>*/</script>
<div class="nofloat"></div>

<div id="copyright">&copy; amooma gmbh</div>
</body>
</html>
