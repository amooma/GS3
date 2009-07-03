#!/usr/bin/php -q
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

if ($argc < 2) {
	echo '### No AGI script specified!'."\n";
	exit(1);
}
$AGI_SCRIPT = $argv[1];
$cwd = getCwd();
if (subStr($AGI_SCRIPT,0,1) !== '/') {
	$AGI_SCRIPT = $cwd.'/'.$AGI_SCRIPT;
}
$AGI_SCRIPT = realPath($AGI_SCRIPT);
if (! file_exists($AGI_SCRIPT)) {
	echo '### AGI script "'.$argv[1].'" not found!'."\n";
	exit(1);
}


putEnv( 'AST_AGI_DIR=/tmp/FAKE' );
putEnv( 'AST_CONFIG_DIR=/tmp/FAKE' );
putEnv( 'AST_CONFIG_FILE=' );
putEnv( 'AST_DATA_DIR=/tmp/FAKE' );
putEnv( 'AST_KEY_DIR=/tmp/FAKE' );
putEnv( 'AST_LOG_DIR=/tmp/FAKE' );
putEnv( 'AST_MODULE_DIR=/tmp/FAKE' );
putEnv( 'AST_MONITOR_DIR=/tmp/FAKE' );
putEnv( 'AST_RUN_DIR=/tmp/FAKE' );
putEnv( 'AST_SPOOL_DIR=/tmp/FAKE' );
putEnv( 'AST_VAR_DIR=/tmp/FAKE' );

$cmd = $AGI_SCRIPT;
for ($i=2; $i<$argc; ++$i) {
	$cmd .= ' '. ($argv[$i] != '' ? escapeShellArg($argv[$i]) : '\'\'');
}
//echo "### CMD: ".$cmd."\n";

$pipes = array();
$proc = proc_open(
	$cmd,
	array(
		0 => array('pipe', 'rb'),  # stdin is a pipe that the child will read from
		1 => array('pipe', 'wb'),  # stdout is a pipe that the child will write to
		2 => array('pipe', 'wb'),  # stderr is a pipe that the child will write to
		),
	$pipes,
	'/tmp'
	);
if (! $proc) {
	echo '### Failed to run AGI script!'."\n";
	exit(1);
}

function _fake_agi_send( $fd, $line )
{
	$bytes = fWrite($fd, $line."\n", strLen($line)+1);
	fFlush($fd);
	return $bytes;
}

_fake_agi_send( $pipes[0], 'agi_request: FAKE' );
_fake_agi_send( $pipes[0], 'agi_channel: FAKE' );
_fake_agi_send( $pipes[0], 'agi_language: en' );
_fake_agi_send( $pipes[0], 'agi_type: FAKE' );
_fake_agi_send( $pipes[0], 'agi_uniqueid: FAKE' );
_fake_agi_send( $pipes[0], 'agi_callerid: unknown' );
_fake_agi_send( $pipes[0], 'agi_calleridname: unknown' );
_fake_agi_send( $pipes[0], 'agi_callingpres: 0' );
_fake_agi_send( $pipes[0], 'agi_callingani2: 0' );
_fake_agi_send( $pipes[0], 'agi_callington: 0' );
_fake_agi_send( $pipes[0], 'agi_callingtns: 0' );
_fake_agi_send( $pipes[0], 'agi_dnid: s' );
_fake_agi_send( $pipes[0], 'agi_rdnis: unknown' );
_fake_agi_send( $pipes[0], 'agi_context: FAKE' );
_fake_agi_send( $pipes[0], 'agi_extension: s' );
_fake_agi_send( $pipes[0], 'agi_priority: 1' );
_fake_agi_send( $pipes[0], 'agi_enhanced: 0.0' );
_fake_agi_send( $pipes[0], 'agi_accountcode: ' );
_fake_agi_send( $pipes[0], '' );


$select = array($pipes[1]);  # needs to be passed by reference
$null   = null;              # needs to be passed by reference
stream_set_blocking($pipes[1], true);
while (true) {
	$agi_cmd_line = '';
	$i=0;
	while (true) {
		if (fEof($pipes[1])) {  # AGI script exited
			break(2);
		}
		if (stream_select($select, $null, $null, 1) > 0) {
			$agi_cmd_line .= fGetS($pipes[1], 8192);
			if (subStr($agi_cmd_line,-1) === "\n") {  # end of line
				//$agi_cmd_line = subStr($agi_cmd_line,0,-1);
				$agi_cmd_line = rTrim($agi_cmd_line);
				break;
			}
		}
		if (++$i > 2) {  # 1 s * 10 = 10 s
			echo "### Timeout while waiting for AGI command. Buffer is \"$agi_cmd_line\".\n";
			//echo "### ". (int)fEof($pipes[1]) ."\n";
			if ($agi_cmd_line === '') $agi_cmd_line = false;
			exit(1);
			break;
		}
	}
	
	echo $agi_cmd_line."\n";
	@fFlush(STDOUT);
	@ob_flush(); @flush();
	
	# the AGI script expects us to respond
	#
	if (preg_match('/^SET VARIABLE/i', $agi_cmd_line)) {
		_fake_agi_send( $pipes[0], '200 result=1' );
	}
	else {
		_fake_agi_send( $pipes[0], '500 result=-1' );
	}
}

proc_close($proc);
exit(0);

