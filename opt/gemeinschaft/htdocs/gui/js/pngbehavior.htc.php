<?php

header( 'Content-Type: text/x-component' );
//header( 'Content-Type: text/plain' );

//header( 'Pragma: cache' );
header( 'Cache-Control: public, max-age=120, must-revalidate' );
//header( 'Last-Modified: '. gmDate('D, d M Y 00:00:00') .' GMT');
//header( 'Expires: '. gmDate('D, d M Y 23:59:59') .' GMT');
header( 'Expires: '. gmDate('D, d M Y H:i:s', time()+120) .' GMT');
//header( 'ETag: '. gmDate('Ymd') );

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );

# set paths
#
$GS_URL_PATH = dirName(dirName(@$_SERVER['SCRIPT_NAME']));
if (subStr($GS_URL_PATH,-1,1) != '/') $GS_URL_PATH .= '/';

header( 'ETag: '. gmDate('Ymd').'-'.md5($GS_URL_PATH) );


function _not_modified()
{
	header( 'HTTP/1.0 304 Not Modified', true, 304 );
	header( 'Status: 304 Not Modified', true, 304 );
	exit();
}

if (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER)
&&  $_SERVER['HTTP_IF_NONE_MATCH'] === gmDate('Ymd').'-'.md5($GS_URL_PATH) ) {
	_not_modified();
}
/*
$tmp = gmDate('D, d M Y');
if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER)
&&  subStr($_SERVER['HTTP_IF_MODIFIED_SINCE'],0,strLen($tmp)) == $tmp ) {
	_not_modified();
}
*/


ob_start();

?><public:component lightWeight="true">
<public:attach event="onpropertychange" onevent="pngb_property_changed()" />
<public:attach event="onbeforeprint" onevent="pngb_before_print()" for="window" />
<public:attach event="onafterprint" onevent="pngb_after_print()" for="window" />
<script type="text/javascript" language="JavaScript">

/*
 * PNG Behavior
 *
 * This script was created by Erik Arvidsson (http://webfx.eae.net/contact.html#erik)
 * for WebFX (http://webfx.eae.net)
 * Copyright 2002-2004
 *
 * For usage see license at http://webfx.eae.net/license.html
 *
 * Version: 1.02
 * Created: 2001-??-??	First working version
 * Updated: 2002-03-28	Fixed issue when starting with a non png image and
 *                      switching between non png images
 *          2003-01-06	Fixed RegExp to correctly work with IE 5.0x
 *          2004-05-09  When printing revert to original
 *
 */

/*
License info from the URL above: "we've decided to re-license our
components under the Apache Software License 2.0, allowing anyone to
use them free of charge."
Some improvements by me.
-- Philipp Kempgen
*/

var pngb_blank_src = '<?php echo $GS_URL_PATH; ?>img/blank.gif';
var pngb_real_src;
var pngb_is_printing = false;
var pngb_supported = element.nodeName.toUpperCase()=='IMG' &&
	navigator.userAgent.match(/MSIE\s*(5\.5|6\.)/i) &&
	navigator.platform.match(/^Win/i);
var filter = 'DXImageTransform.Microsoft.AlphaImageLoader';

function pngb_fix_png()
{
	var src = element.src;
	if (src == pngb_real_src && /\.png$/i.test(src)) {
		element.src = pngb_blank_src;
		return;
	}
	if (! new RegExp(pngb_blank_src).test(src)) pngb_real_src = src;
	if (/\.png$/i.test(pngb_real_src)) {
		element.runtimeStyle.width  =
			(element.clientWidth  > 0 ? element.clientWidth  : 1) +'px';
		element.runtimeStyle.height =
			(element.clientHeight > 0 ? element.clientHeight : 1) +'px';
		element.runtimeStyle.border = '0 none transparent';
		element.src = pngb_blank_src;
		element.runtimeStyle.filter =
			"progid:"+ filter +"("+
			"src='"+ src +"',sizingMethod='scale')";
		if (element.filters && element.filters[filter])
			element.filters[filter].enabled = true;
		if (element.parentElement.href) element.style.cursor = 'pointer';
	} else {
		element.runtimeStyle.filter = "";
	}
}

function pngb_before_print()
{
	pngb_is_printing = true;
	element.src = pngb_real_src;
	element.runtimeStyle.filter = "";
	pngb_real_src = null;
}

function pngb_after_print()
{
	pngb_is_printing = false;
	pngb_fix_png();
}

function pngb_property_changed()
{
	if (!pngb_supported || pngb_is_printing) return;
	
	var pName = event.propertyName;
	if (pName != "src") return;
	// if not set to blank
	if (! new RegExp(pngb_blank_src).test(src))
		pngb_fix_png();
};

if (pngb_supported) pngb_fix_png();

</script>
</public:component>

<?php

if (! headers_sent())
	header( 'Content-Length: '. (int)ob_get_length() );
ob_end_flush();
exit();

?>