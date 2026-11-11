<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007-2026, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
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

defined('GS_VALID') or die('No direct access.');
require_once( GS_DIR .'inc/util.php' );

# Time rules (call forwards, inbound and outbound routes) store a
# window as "HH:MM" from h_from to h_to, where h_to may be "24:00".

function _gs_timerule_hm( $h, $m, $max_h )
{
	$h = (int)$h;
	$m = (int)$m;
	if     ($h <      0) $h =      0;
	elseif ($h > $max_h) $h = $max_h;
	if     ($m <  0) $m =  0;
	elseif ($m > 59) $m = 59;
	return array(
		str_pad($h, 2, '0', STR_PAD_LEFT),
		str_pad($m, 2, '0', STR_PAD_LEFT) );
}

# Reads the fields <prefix>h_from_h, _h_from_m, _h_to_h and _h_to_m
# from the request. Returns array(h_from, h_to), h_to never before h_from.
function gs_timerule_times_from_request( $prefix )
{
	$hm = array();
	foreach (array('from'=>23, 'to'=>24) as $end => $max_h) {
		list($h, $m) = _gs_timerule_hm(
			@$_REQUEST[$prefix.'h_'.$end.'_h'],
			@$_REQUEST[$prefix.'h_'.$end.'_m'],
			$max_h );
		$hm[$end] = $h.':'.$m;
	}
	if ($hm['to'] > '24:00'    ) $hm['to'] = '24:00';
	if ($hm['to'] < $hm['from']) $hm['to'] = $hm['from'];
	return array($hm['from'], $hm['to']);
}

# Splits stored times for the form fields.
# Returns array(from_h, from_m, to_h, to_m), the end never before the start.
function gs_timerule_times_for_display( $h_from, $h_to )
{
	$tmp = explode(':', $h_from);
	list($hf, $mf) = _gs_timerule_hm( lTrim(@$tmp[0], '0-'), lTrim(@$tmp[1], '0-'), 23 );
	$tmp = explode(':', $h_to);
	list($ht, $mt) = _gs_timerule_hm( lTrim(@$tmp[0], '0-'), lTrim(@$tmp[1], '0-'), 24 );
	if ($ht.':'.$mt < $hf.':'.$mf) {
		$ht = $hf;
		$mt = $mf;
	}
	return array($hf, $mf, $ht, $mt);
}

# The two time fields of a rule row, named like
# gs_timerule_times_from_request() reads them.
function gs_timerule_times_html( $prefix, $hf, $mf, $ht, $mt )
{
	$field = '<input type="text" name="%s" value="%s" size="2" maxlength="2" class="r" />';
	return '<span class="nobr">'
		. sPrintF($field, $prefix.'h_from_h', $hf) .':'
		. sPrintF($field, $prefix.'h_from_m', $mf) .' -'
		. '</span> '."\n"
		. '<span class="nobr">'
		. sPrintF($field, $prefix.'h_to_h', $ht) .':'
		. sPrintF($field, $prefix.'h_to_m', $mt)
		. '</span>';
}

# One-letter weekday names in the user's language, keyed by the
# column suffix (d_mo ... d_su). Needs the system locales
# (Debian: apt-get install locales locales-all).
function gs_timerule_weekday_initials()
{
	$lang = @$_SESSION['lang'];
	if (! $lang)
		$lang = gs_get_conf('GS_INTL_LANG', 'de_DE');
	$lang = strToLower(subStr($lang,0,2));
	$wdays = array('mo'=>'Mon', 'tu'=>'Tue', 'we'=>'Wed', 'th'=>'Thu', 'fr'=>'Fri', 'sa'=>'Sat', 'su'=>'Sun');
	$initials = array();
	foreach ($wdays as $col => $wdca)
		$initials[$col] = mb_subStr(gs_strftime('%a', strToTime('last '.$wdca), $lang),0,1);
	return $initials;
}

?>
