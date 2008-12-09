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
require_once( GS_DIR .'htdocs/gui/inc/session.php' );
require_once( GS_DIR .'inc/db_connect.php' );


@header('Content-Type: image/svg+xml; charset=utf-8');
@header('Content-Disposition: inline');

echo '<','?xml version="1.0" encoding="utf-8" ?','>' ,"\n";
echo ' <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' ,"\n";


$dataset = @$_REQUEST['dataset'];
if ($dataset != 'callvolume'
&&  $dataset != 'avgdur'
) {
	$dataset = '';
	//FIXME
}


$width  = (int)@$_REQUEST['width' ];
$height = (int)@$_REQUEST['height'];
if ($width  < 50) $width  = 550;
if ($height < 50) $height = 180;

$padt = 12;
$padb = 22;
$padl = 35;
$padr = 14;

$cw = $width  - $padl - $padr;
$ch = $height - $padt - $padb;


$fr_y = (int)lTrim(@$_REQUEST['fy'],'0-');
$fr_m = (int)lTrim(@$_REQUEST['fm'],'0-');
$fr_d = (int)lTrim(@$_REQUEST['fd'],'0-');
$to_y = (int)lTrim(@$_REQUEST['ty'],'0-');
$to_m = (int)lTrim(@$_REQUEST['tm'],'0-');
$to_d = (int)lTrim(@$_REQUEST['td'],'0-');

$fr = mkTime( 0, 0, 0, $fr_m, $fr_d, $fr_y);
$to = mkTime(23,59,59, $to_m, $to_d, $to_y);
if ($to < $fr) {
	$tmp = $fr;
	$fr = $to;
	$to = $tmp;
}
$time_range = $to - $fr;
$time_range_days = $time_range/(60*60*24);
if     ($time_range_days <= 2     ) {$xtstr = '+1 hours' ; $xdfmt = 'H'  ;}
elseif ($time_range_days <= 31    ) {$xtstr = '+1 days'  ; $xdfmt = 'j'  ;}
elseif ($time_range_days <= 31*2  ) {$xtstr = '+1 weeks' ; $xdfmt = 'M j';}
elseif ($time_range_days <= 31*6  ) {$xtstr = '+1 months'; $xdfmt = 'M/y';}
elseif ($time_range_days <= 31*12 ) {$xtstr = '+1 months'; $xdfmt = 'n/y';}
elseif ($time_range_days <= 31*32 ) {$xtstr = '+6 months'; $xdfmt = 'n/y';}
elseif ($time_range_days <= 365*10) {$xtstr = '+1 years' ; $xdfmt = 'Y'  ;}
else                                {$xtstr ='+10 years' ; $xdfmt = 'Y'  ;}


$i=0;
$t = $fr;
while ($t < $to) {
	$t = strToTime($xtstr, $t);
	++$i;
}
$i--;
if ($i<1) $i=1;
$xfact = ($cw-1)/$i-1;



if ($dataset != '') {
	
	$db = gs_db_cdr_master_connect();
	if(!$db)
		die();

	$t = $fr;
	$i=0;
	$vals = array();
	$maxval = 0;
	
	if ($dataset === 'callvolume') {
		
		while ($t <= $to) {
			$qtto = strToTime($xtstr, $t);
			$tdiffdays = ($qtto - $t)/(60*60*24);
			
			$val = (int)@$db->executeGetOne(
'SELECT COUNT(*)
FROM `ast_cdr`
WHERE
	`calldate`>=\''. $db->escape(gmDate('Y-m-d H:i:s', $t   )) .'\' AND
	`calldate`< \''. $db->escape(gmDate('Y-m-d H:i:s', $qtto)) .'\' AND
	`dst`<>\'s\' AND
	`dst`<>\'h\' AND
	`dst` NOT LIKE \'*%\''
			);
			$val = $val/$tdiffdays;
			$vals[] = $val;
			if ($val > $maxval) $maxval = $val;
			
			$t = $qtto;
			++$i;
		}
		//print_r($vals);
		
	}
	elseif ($dataset === 'avgdur') {
		
		$db = gs_db_cdr_master_connect();
		if(!$db)
			die();

		$t = $fr;
		$i=0;
		$vals = array();
		$maxval = 0;
		while ($t <= $to) {
			$qtto = strToTime($xtstr, $t);
			
			$val = (int)@$db->executeGetOne(
'SELECT AVG(`duration`)
FROM `ast_cdr`
WHERE
	`calldate`>=\''. $db->escape(gmDate('Y-m-d H:i:s', $t   )) .'\' AND
	`calldate`< \''. $db->escape(gmDate('Y-m-d H:i:s', $qtto)) .'\' AND
	`dst`<>\'s\' AND
	`dst`<>\'h\' AND
	`dst` NOT LIKE \'*%\' AND
	`disposition`=\'ANSWERED\''
			);
			$vals[] = $val;
			if ($val > $maxval) $maxval = $val;
			
			$t = $qtto;
			++$i;
		}
		//print_r($vals);
		
	}
	
	$ystep = (float)@$_REQUEST['ystep'];
	if ($ystep < 1) {  #auto
		
		$ystep = 0;
		do {
			++$ystep;
		} while ( ($ch-8)/($maxval>0?$maxval:1)*$ystep <= 18 );
	}
	$yfact = ($maxval > 0) ? (($ch-9)/$maxval) : ($ch-8);
}





if (! function_exists('__')) {
	function __($str) {return $str;}
}

function xmlEnt( $str )
{
	return htmlSpecialChars($str, ENT_QUOTES, 'UTF-8');
}

function round01( $float )
{
	return number_format( $float, 1, '.', '' );
}

echo
'<svg
	xmlns="http://www.w3.org/2000/svg"
	xmlns:xlink="http://www.w3.org/1999/xlink"
	version="1.1"
	viewBox="0 0 ',$width,' ',$height,'"
	width="100%"
	height="100%"
	>
';
echo '<!-- generated by Gemeinschaft (by AMOOMA GmbH) -->
';
echo '<title>', 'Gemeinschaft statistics' ,'</title>
';
echo '<desc>', 'Gemeinschaft statistics' ,'</desc>
';

?>

<defs>
<linearGradient id="graphGrad" x1="0%" y1="5%" x2="0%" y2="90%">
	<stop offset="5%" stop-color="#fefefe" stop-opacity="0"/>
	<stop offset="90%" stop-color="#ffffff" stop-opacity="1"/>
</linearGradient>
<linearGradient id="key1grad" x1="0%" y1="30%" x2="0%" y2="95%">
	<stop offset="10%" stop-color="#00f" stop-opacity="1.0"/>
	<stop offset="95%" stop-color="#aaf" stop-opacity="0.9"/>
</linearGradient>


<style type="text/css">
<![CDATA[

* {
	shape-rendering: auto;
	text-rendering: optimizeLegibility;
}

.svgBackground {
	fill: #e9e9e9;
}

.graphBackground {
	fill: #fcfcfc;
	fill: url(#graphGrad);
}

.mainTitleF,
.mainTitleB {
	text-anchor: middle;
	fill: #00aa00;
	font-size: 14px;
	font-family: 'Helvetica', sans-serif;
	font-weight: normal;
	font-style: normal;
}
.mainTitleB {
	fill: #fff;
	stroke: #fff;
	stroke-width: 3px;
	stroke-opacity: 0.7;
}

.dataPointLabelF,
.dataPointLabelB {
	font-size: 16px;
	fill: #000000;
	text-anchor: middle;
	font-family: 'Helvetica', sans-serif;
	font-weight: normal;
	font-style: normal;
}
.dataPointLabelB {
	fill: #fff;
	stroke: #fff;
	stroke-width: 2px;
}

.axis {
	stroke: #000000;
	stroke-width: 2px;
	shape-rendering: crispEdges;
}

.xAxisLabel {
	fill: #000000;
	font-size: 12px;
	font-family: 'Helvetica', sans-serif;
	font-weight: normal;
	font-style: normal;
	text-anchor: middle;
}
.yAxisLabel {
	text-anchor: end;
	fill: #000000;
	font-size: 12px;
	font-family: 'Helvetica', sans-serif;
	font-weight: normal;
	font-style: normal;
	text-anchor: end;
}

.guideLine {
	stroke-width: 1px;
	stroke: #000000;
	stroke-opacity: 0.5;
	shape-rendering: crispEdges;
	stroke-dasharray: 1,3;
}

.line {
	fill: none;
	stroke: #000000;
	stroke-width: 3px;
	stroke-opacity: 0.9;
	stroke-linecap: round;
}
.fill {
	fill: #0000ff;
	fill: url(#key1grad);
	fill-opacity: 0.6;
	stroke: none;
	stroke-width: 0px;
	stroke-opacity: 0;
}

.copy {
	text-anchor: end;
	fill: #bbb;
	font-size: 10px;
	font-family: 'Helvetica', sans-serif;
	font-weight: normal;
	font-style: normal;
}

.err {
	text-anchor: middle;
	fill: #ee0000;
	font-size: 12px;
	font-family: 'Helvetica', sans-serif;
	font-weight: normal;
	font-style: normal;
}

]]>
</style>
</defs>

<!-- SVG background -->
<rect class="svgBackground" x="0" y="0" width="100%" height="100%" />

<?php

#####################################################################
##                             output {                            ##
#####################################################################
if ($dataset != '') {
	switch ($dataset) {
		case 'callvolume':
			//$header = __("Gespr\xC3\xA4chsaufkommen / Tag");
			$header = __("Gespr\xC3\xA4chsaufkommen");
			break;
		case 'avgdur':
			//$header = __("Durchschnittliche Gespr\xC3\xA4chsdauer / s");
			$header = __("Durchschnittliche Gespr\xC3\xA4chsdauer");
			break;
		default:
			$header = '';
	}
?>

<g transform="translate(<?php echo $padl; ?> <?php echo $padt; ?>)">
	
	<rect class="graphBackground" x="0" y="0" width="<?php echo $cw; ?>" height="<?php echo $ch; ?>" />
	
	<a xlink:href="http://www.amooma.de/" target="_blank">
		<text class="copy" x="<?php echo $width-$padl-2; ?>" y="<?php echo $height-$padt-3; ?>">gemeinschaft</text>
	</a>
	
	<!-- guidelines -->
<?php
		$v=0;
		while ($v <= $maxval) {
			echo "\t", '<path class="guideLine" d="M0 ', round01($ch-($v*$yfact)) ,' h', $cw+2 ,'" />' ,"\n";
			$v += $ystep;
		}
?>
	
	
	<!-- path 1 -->
<?php
	
	$pstr = '';
	foreach ($vals as $i => $val) {
		$pstr .= ' '. ($i*$xfact) .' '. ($ch-($val*$yfact));
	}
	echo '<path class="fill" d="M0 '. $ch . $pstr .' '. ($i*$xfact) .' '. $ch .' Z" style="stroke:#0000cc;" />' ,"\n";
	echo '<path class="line" d="M'. $pstr .'" style="stroke:#0000cc;" />' ,"\n";
	
?>
	
	
	<!-- y-axis -->
	<path class="axis" d="M0 <?php echo $ch+1; ?> v-<?php echo $ch+3; ?>" />
	<!-- x-axis -->
	<path class="axis" d="M-1 <?php echo $ch; ?> h<?php echo $cw+4; ?>" />
	
	<!-- y-axis labels -->
	<g transform="translate(-6 3)">
<?php
			$v=0;
			$i=0;
			while ($v <= $maxval) {
				echo "\t\t", '<text class="yAxisLabel" x="0" y="', round01($ch-($v*$yfact)) ,'">', $v ,'</text>' ,"\n";
				$v += $ystep;
				++$i;
			}
			if ($i < 2) {  # no label written except for 0
				echo "\t\t", '<text class="yAxisLabel" x="0" y="', round01($ch-($maxval*$yfact)) ,'">', $maxval ,'</text>' ,"\n";
			}
?>
	</g>
	
	<!-- x-axis labels -->
	<g transform="translate(1 <?php echo $ch+17; ?>)">
<?php
			$i=0;
			$t = $fr;
			while ($t <= $to) {
				echo "\t\t", '<text class="xAxisLabel" y="0" x="', round01($i*$xfact) ,'">', xmlEnt(date($xdfmt,$t)) ,'</text>' ,"\n";
				$t = strToTime($xtstr, $t);
				++$i;
			}
?>
	</g>
	
</g>

<!-- header -->
<text class="mainTitleB" x="<?php echo round01($width/2); ?>" y="16"><?php echo xmlEnt($header) ?></text>
<text class="mainTitleF" x="<?php echo round01($width/2); ?>" y="16"><?php echo xmlEnt($header); ?></text>

<?php
}
#####################################################################
##                            } output                             ##
#####################################################################





#####################################################################
##                            bad args {                           ##
#####################################################################
else {
?>
<text class="err" x="<?php echo round01($width/2); ?>" y="<?php echo round01($height/2); ?>"><?php echo 'Error (bad args).'; ?></text>
<?php
}
#####################################################################
##                           } bad args                            ##
#####################################################################
?>

</svg>
