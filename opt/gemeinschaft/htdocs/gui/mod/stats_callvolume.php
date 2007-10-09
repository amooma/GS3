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

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$service_level  = 90;  # 90 s = 1:30 min
$waittime_level = 15;  # 15 s


function _secs_to_minsecs( $s )
{
	$s = (int)$s;
	$m = floor($s/60);
	$s = $s - $m*60;
	return $m .':'. str_pad($s, 2, '0', STR_PAD_LEFT);
}

$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);


function _check_range( &$val, $min, $max )
{
	if ($val > $max) $val = $max;
	if ($val < $min) $val = $min;
}

$action = @$_REQUEST['action'];
if ($action === 'report') {
	$fr_y = abs((int)lTrim(@$_REQUEST['fr_y'],'0'));
	$fr_m = abs((int)lTrim(@$_REQUEST['fr_m'],'0'));
	$fr_d = abs((int)lTrim(@$_REQUEST['fr_d'],'0'));
	$to_y = abs((int)lTrim(@$_REQUEST['to_y'],'0'));
	$to_m = abs((int)lTrim(@$_REQUEST['to_m'],'0'));
	$to_d = abs((int)lTrim(@$_REQUEST['to_d'],'0'));
	_check_range( $fr_y, 1980, 2030 );
	_check_range( $to_y, 1980, 2030 );
	_check_range( $fr_m, 1, 12 );
	_check_range( $to_m, 1, 12 );
	_check_range( $fr_d, 1, (int)date('t', mkTime(12,1,1, $fr_m, 15, $fr_y)) );
	_check_range( $to_d, 1, (int)date('t', mkTime(12,1,1, $to_m, 15, $to_y)) );
} else {
	$action   = '';
	$now = time();
	$default_to = $now;
	$default_fr = $now-(7*24*3600);
	$fr_y = (int)date('Y', $default_fr);
	$fr_m = (int)date('n', $default_fr);
	$fr_d = (int)date('j', $default_fr);
	$to_y = (int)date('Y', $default_to);
	$to_m = (int)date('n', $default_to);
	$to_d = (int)date('j', $default_to);
}

?>

<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="report" />
<table cellspacing="1">
<tbody>
<tr>
	<th class="r" style="vertical-align:middle;"><?php echo __('Vom'); ?>:</th>
	<td>
		<input name="fr_y" id="ipt-fr_y" size="4" maxlength="4" value="<?php echo $fr_y; ?>" class="r" />-
		<input name="fr_m" id="ipt-fr_m" size="2" maxlength="2" value="<?php echo $fr_m; ?>" class="r" />-
		<input name="fr_d" id="ipt-fr_d" size="2" maxlength="2" value="<?php echo $fr_d; ?>" class="r" />
	</td>
</tr>
<tr>
	<th class="r" style="vertical-align:middle;"><?php echo __('Bis'); ?>:</th>
	<td>
		<input name="to_y" id="ipt-to_y" size="4" maxlength="4" value="<?php echo $to_y; ?>" class="r" />-
		<input name="to_m" id="ipt-to_m" size="2" maxlength="2" value="<?php echo $to_m; ?>" class="r" />-
		<input name="to_d" id="ipt-to_d" size="2" maxlength="2" value="<?php echo $to_d; ?>" class="r" />
	</td>
</tr>
</tbody>
</table>
<input type="submit" value="<?php echo __('Report'); ?>" />
</form>

<hr />
<br />

<?php

if ($action == '') return;

#####################################################################




?>

<span id="font-size-sensor" style="color:#fff; position:absolute; bottom:1px; right:1px;">...</span>



<?php
#####################################################################
#    call volume {
#####################################################################

$w = 580;
$h = 180;
$src = GS_URL_PATH .'graph.php?';
$args = array(
	'width'   => $w,
	'height'  => $h,
	'dataset' => 'callvolume',
	'fy' => $fr_y,
	'fm' => $fr_m,
	'fd' => $fr_d,
	'ty' => $to_y,
	'tm' => $to_m,
	'td' => $to_d,
	'ystep'   => 0,  # auto
	'_msie'   => '.svg'
);
$i=0;
foreach ($args as $k => $v) {
	$src .= ($i===0 ? '' : '&amp;') . rawUrlEncode($k) .'='. rawUrlEncode($v);
	++$i;
}
?>

<object
	type="image/svg+xml"
	width="<?php echo $w; ?>"
	height="<?php echo $h; ?>"
	data="<?php echo $src; ?>"
	style="border:1px solid #aaa;"
>
	<param name="src" value="<?php echo $src; ?>">
	<?php echo __('Ihr Browser kann die Datei nicht anzeigen.'); ?>
</object>

<br />
<br />

<?php
#####################################################################
#    } call volume
#####################################################################
?>



<?php
#####################################################################
#    avg call duration {
#####################################################################

$w = 580;
$h = 180;
$src = GS_URL_PATH .'graph.php?';
$args = array(
	'width'   => $w,
	'height'  => $h,
	'dataset' => 'avgdur',
	'fy' => $fr_y,
	'fm' => $fr_m,
	'fd' => $fr_d,
	'ty' => $to_y,
	'tm' => $to_m,
	'td' => $to_d,
	'ystep'   => 0,  # auto
	'_msie'   => '.svg'
);
$i=0;
foreach ($args as $k => $v) {
	$src .= ($i===0 ? '' : '&amp;') . rawUrlEncode($k) .'='. rawUrlEncode($v);
	++$i;
}
?>

<object
	type="image/svg+xml"
	width="<?php echo $w; ?>"
	height="<?php echo $h; ?>"
	data="<?php echo $src; ?>"
	style="border:1px solid #aaa;"
>
	<param name="src" value="<?php echo $src; ?>">
	<?php echo __('Ihr Browser kann die Datei nicht anzeigen.'); ?>
</object>

<br />
<br />

<?php
#####################################################################
#    } avg call duration
#####################################################################
?>




<script type="text/javascript" src="<?php echo GS_URL_PATH; ?>js/textresizedetector.js"></script>
<script type="text/javascript">
//<![CDATA[

// the SVGs need to be redrawn if the font size changes
//
function reload_svgs()
{
	if (document && document.getElementsByTagName) {
		var objs = document.getElementsByTagName('object');
		if (objs) {
			var n = objs.length;
			for (var i=0; i<n; ++i) {
				var obj = objs[i];
				var cdoc = obj.contentDocument;
				if (cdoc && cdoc.documentElement) {
					var docel = cdoc.documentElement;
					if (docel.nodeName && docel.nodeName.toLowerCase()==='svg'
					&&  cdoc.location && cdoc.location.reload)
					{
						cdoc.location.reload();
					}
				}
			}
		}
	}
}

function fontResizeInit()
{
	//var iBase = TextResizeDetector.addEventListener(onFontResize,null);
	//alert("The base font size = " + iBase);
	TextResizeDetector.addEventListener(reload_svgs,null);
}
function onFontResize(e,args)
{
	/*
	var msg = "Base font size: "+ args[0].iBase +"px\n";
	msg    += "Current font size: "+ args[0].iSize +"px\n";
	msg    += "Delta: "+ args[0].iDelta +"\n";
	alert(msg);
	*/
}
//id of element to check for and insert control
TextResizeDetector.TARGET_ELEMENT_ID = 'font-size-sensor';
//function to call once TextResizeDetector has init'd
TextResizeDetector.USER_INIT_FUNC = fontResizeInit;

//]]>
</script>


