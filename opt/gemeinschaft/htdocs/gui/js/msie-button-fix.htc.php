<?php

header( 'Content-Type: text/x-component' );
//header( 'Content-Type: text/plain' );

header( 'Cache-Control: public, max-age=120, must-revalidate' );
header( 'Expires: '. gmDate('D, d M Y H:i:s', time()+120) .' GMT');

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );


header( 'ETag: '. gmDate('Ymd').'-'.md5($_SERVER['SCRIPT_NAME']) );


function _not_modified()
{
	header( 'HTTP/1.0 304 Not Modified', true, 304 );
	header( 'Status: 304 Not Modified', true, 304 );
	exit();
}

if (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER)
&&  $_SERVER['HTTP_IF_NONE_MATCH'] === gmDate('Ymd').'-'.md5($_SERVER['SCRIPT_NAME']) ) {
	//_not_modified();
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
<public:attach event="onclick" onevent="button_click()" />
<script type="text/javascript" language="JavaScript">

/*
-- Philipp Kempgen
*/

function button_click()
{
	alert('auf einen Button geklickt');
	//...
	
}

</script>
</public:component>

<?php

if (! headers_sent())
	header( 'Content-Length: '. (int)ob_get_length() );
ob_end_flush();
exit();

?>