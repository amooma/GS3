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
//include_once( GS_DIR .'inc/db_connect.php' );

$host = '127.0.0.1';
$port = 5039;
$maxtime = 35;

@session_write_close();


header( 'Content-Type: text/html; charset=utf-8' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Expires: 0' );
header( 'Vary: *' );

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

@ini_set('max_execution_time', $maxtime+10);


$html_start =
	'<html><head><title>-</title>' ."\n".
	'<script type="text/javascript">' ."\n".
	'function e(s) {try{ window.parent.gs_e(s); }catch(e){}}' ."\n".
	'function m(o) {try{ window.parent.gs_m(o); }catch(e){}}' ."\n".
	'</script>' ."\n".
	'</head><body>' ."\n\n";
$html_end =
	"\n".
	'</body></html>' ."\n";

$msg_open  = '<script type="text/javascript">' ."\n";
$msg_close = '</script>' .'.' ."\n";
// padding for the stupid MSIE:
$msie_pad = str_repeat(' ', 256-1) ."\n";


$sock = @fSockOpen( $host, $port, $err, $errMsg, 5 );
if (! is_resource($sock)) {
	header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
	header( 'Status: 500 Internal Server Error', true, 500 );
	
	echo $html_start;
	
	echo $msg_open;
	echo 'e("daemondown");' ,"\n";
	echo $msie_pad;
	echo $msg_close;
	
	echo $html_end;
	
	sleep(1);
	die();
}



@stream_set_blocking( $sock, false );
$tStart = time();
$cnt_no_data = 0;
$buf = '';

echo $html_start;

echo $msg_open;
echo 'e("");' ,"\n";  # everything is fine
echo $msie_pad;
echo $msg_close;

//$db = gs_db_slave_connect();

while (! @fEof( $sock ) && time() < $tStart+$maxtime) {
	$data = @fRead( $sock, 8190 );
	if (strLen($data) > 0) {
		$cnt_no_data = 0;
		$buf .= $data;
	} else {
		if (++$cnt_no_data > 500) {
			# we sleep 0.01 secs so this is 5 secs
			echo $html_end;
			exit(0);
		}
	}
	
	while (preg_match('/=====/S', $buf, $m, PREG_OFFSET_CAPTURE)) {
		$pos = (int)@$m[0][1];
		$msg = subStr($buf, 0, $pos);
		$buf = subStr($buf, $pos+strLen(@$m[0][0]));
		
		@ob_start();
		$states = null;
		$ret = eval('$states = '.$msg.';');
		@ob_end_clean();
		if ($ret === false || ! is_array(@$states)) continue;
		
		echo $msg_open;
		echo 'm({'."\n";
		$i = 0;
		$c = count($states);
		foreach ($states as $ext => $info) {
			echo "'$ext':{";
			echo "s:". (int)@$info['s'];
			if (@$info['e']) echo ",e:1";
			//if (@$info['l']) echo ",l:'". $info['l'] ."'";
			/*
			if (@$info['l']) {
				$rs = $db->execute('SELECT `u`.`firstname` `fn`, `u`.`lastname` `ln` FROM `users` `u` JOIN `ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) WHERE `s`.`name`=\''. $db->escape($info['l']) .'\'');
				//...
			}
			*/
			if (@$info['l']) {
				if (subStr($info['l'],0,3) === '*7*') {
					echo ",l:'". 'privat' ."'";
				} else {
					if (preg_match('/[^0-9*#+]/', $info['l'])) {
						// e.g. "offer" when offered call completion
						echo ",l:'". "*" ."'";
					} else {
						echo ",l:'". $info['l'] ."'";
					}
				}
			}
			echo "}";
			++$i;
			if ($i < $c) echo ",";
			echo "\n";
		}
		echo "});\n";
		echo $msie_pad;
		echo $msg_close;
	}
	
	uSleep(10000);  # sleep 0.01 secs
}

echo $html_end;
exit(0);

?>