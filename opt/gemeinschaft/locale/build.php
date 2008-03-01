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


# needs msgfmt
# Debian: apt-get install gettext


define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../inc/conf.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


$copyright =
'/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
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
\*******************************************************************/';

$dir = dirName(__FILE__).'/';

$langdirs = glob( $dir.'*_*/' );
if (is_array($langdirs)) {
	foreach ($langdirs as $langdir) {
		$pofiles = glob( $langdir.'LC_MESSAGES/*.po' );
		if (is_array($pofiles)) {
			foreach ($pofiles as $pofile) {
				
				$domain = baseName($pofile, '.po');
				$lang = baseName(dirName(dirName($pofile)));
				
				# build .mo file for gettext
				#
				echo "Building $lang $domain.mo\n";
				$mofile = preg_replace('/\.po$/', '.mo', $pofile);
				passThru( 'msgfmt -o '. qsa($mofile) .' '. qsa($pofile) );
				
				# build .php file for php
				#
				echo "Building $lang $domain.php\n";
				$phpout = po_to_php( $pofile );
				if (! is_array($phpout)) $phpout = array();
				$phpout = '<'."?php\n"
					. "// AUTO-GENERATED FILE. TO MAKE CHANGES EDIT\n"
					. "// ".$domain.".po AND REBUILD\n\n"
					. $copyright ."\n\n"
					. '$g_gs_LANG[\''.$lang.'\'][\''.$domain.'\'] = '
					. var_export($phpout, true) .";\n\n"
					. '?'.'>';
				
				$phpfile = preg_replace('/\.po$/', '.php', $pofile);
				$f = fOpen($phpfile, 'wb');
				fWrite($f, $phpout, strLen($phpout));
				fClose($f);
				
			}
		}
	}
}

function po_to_php( $pofile )
{
	$lines = file($pofile);
	if (! is_array($lines)) return false;
	
	$trans = array();
	$msgid = null;
	$msgstr = '';
	$context = 'msgstr';
	
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line=='' || subStr($line,0,1)=='#') continue;
		
		if (preg_match('/msgid\s*"(.*)"/iS', $line, $m)) {
			if ($msgid !== null) {
				$trans[$msgid] = str_replace(
					array('\n', '\r', '\t', '\"'),
					array("\n", "\r", "\t", '"' ),
					$msgstr);
				$msgstr = '';
			}
			
			$msgid = $m[1];
			$context = 'msgid';
		} elseif (preg_match('/msgstr\s*"(.*)"/iS', $line, $m)) {
			$msgstr = $m[1];
			$context = 'msgstr';
		} else {
			if ($context == 'msgstr')
				$msgstr .= trim($line, '"');
			else
				$msgid .= trim($line, '"');
		}
	}
	if ($msgid !== null) {
		$trans[$msgid] = str_replace(
			array('\n', '\r', '\t', '\"'),
			array("\n", "\r", "\t", '"' ),
			$msgstr);
		$msgstr = '';
	}
	
	return $trans;
}


?>