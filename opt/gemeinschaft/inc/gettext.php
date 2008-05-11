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

defined('GS_VALID') or die('No direct access.');


#####################################################################
# gettext emulation (do not call directly!) {
#####################################################################

/*
$cs = array(
	'LC_CTYPE'    => 0,
	'LC_NUMERIC'  => 1,
	'LC_TIME'     => 2,
	'LC_COLLATE'  => 3,
	'LC_MONETARY' => 4,
	'LC_MESSAGES' => 5,
	'LC_ALL'      => 6
);
foreach ($cs as $c => $v) {
	if (! defined($c)) define($c, $v);
}

if (! function_exists('gettext')) {
	function gettext( $msg )
	{
		// implement some nice fallback here for when gettext is not available
		//$locale = setlocale(LC_MESSAGES,'0');  # get locale
		return $msg;
	}
}

if (! function_exists('_')) {
	function _( $msg )
	{
		return gettext( $msg );
	}
}

if (! function_exists('bindtextdomain')) {
	$gettext_bindtextdomains = array();
	function bindtextdomain( $domain, $directory )
	{
		global $gettext_bindtextdomains;
		$directory = realPath($directory);
		if (!empty($domain) && is_dir($directory)) {
			$gettext_bindtextdomains[$domain] = $directory;
			return $directory;
		} else
			return false;
	}
}

if (! function_exists('textdomain')) {
	$gettext_textdomain = null;
	function textdomain( $domain )
	{
		global $gettext_textdomain;
		if (!empty($domain))
			$gettext_textdomain = $domain;
		return $gettext_textdomain;
	}
}

if (! function_exists('dgettext')) {
	function dgettext( $domain, $msg )
	{
		$olddomain = textdomain( null );
		textdomain( $domain );
		$ret = gettext( $msg );
		textdomain( $olddomain );
		return $ret;
	}
}

if (! function_exists('dcgettext')) {
	function dcgettext( $domain, $msg, $category )
	{
		///FIXME
		$olddomain = textdomain( null );
		textdomain( $domain );
		$ret = gettext( $msg );
		textdomain( $olddomain );
		return $ret;
	}
}

if (! function_exists('ngettext')) {
	function ngettext( $msg_s, $msg_p, $n )
	{
		///FIXME
		return sprintf( ($n==1 ? $msg_s : $msg_p), $n );
	}
}

if (! function_exists('dngettext')) {
	function dngettext( $domain, $msg_s, $msg_p, $n )
	{
		$olddomain = textdomain( null );
		textdomain( $domain );
		$ret = ngettext( $msg_s, $msg_p, $n );
		textdomain( $olddomain );
		return $ret;
	}
}

if (! function_exists('dcngettext')) {
	function dcngettext( $domain, $msg_s, $msg_p, $n, $category )
	{
		///FIXME
		return dngettext( $domain, $msg_s, $msg_p, $n );
	}
}
*/

#####################################################################
# } gettext emulation
#####################################################################



#####################################################################
# Gemeinschaft functions {
#####################################################################

function gs_get_enabled_langs()
{
	$lang_defs = explode(',', gs_get_conf('GS_GUI_LANGS'));
	$langs = array();
	foreach ($lang_defs as $tmp) {
		$parts = explode(':', trim($tmp));
		$lang_info = array();
		$iso = str_replace('_','-', @$parts[0]);
		if (preg_match('/^([a-z]{2}|x)-([a-z]{2,})$/i', $iso, $m)) {
			if ($m[1] !== 'x') {
				$iso = strToLower($m[1]) .'-'.
					(strLen($m[2])===2 ? strToUpper($m[2]) : strToLower($m[2]));
			} else {
				$iso = strToLower($m[0]);
			}
		} else {
			$iso = 'x-unknown';
		}
		$lang_info['icon'   ] = @$parts[1];
		//$lang_info['iconalt'] = @$parts[2];
		$lang_info['title'  ] = @$parts[3];
		$langs[$iso] = $lang_info;
	}
	return $langs;
}

/*
function gs_get_env_lang()
{
}
*/

$g_gs_LANG = array();
$g_gs_default_locale = 'de_DE';
$g_gs_language = $g_gs_default_locale;
$g_gs_textdomain = null;

function gs_setlang( $locale )
{
	global $g_gs_language;
	
	if (GS_INTL_USE_GETTEXT && extension_loaded('gettext')) {
		
		switch (strToLower(subStr($locale,0,2))) {
			case 'de':
				setLocale( LC_MESSAGES, array($locale, 'de_DE@euro', 'de_DE', 'de', 'deu_deu', 'ge') );
				break;
			case 'en':
				setLocale( LC_MESSAGES, array($locale, 'en_US', 'en') );
				break;
			default:
				setLocale( LC_MESSAGES, $locale );
		}
		putEnv( 'LANGUAGE='. $locale );
		putEnv( 'LANG='    . $locale );
		return setLocale(LC_MESSAGES,'0');
		
	} else {
		
		$langdirs = glob( GS_DIR .'locale/*_*' );
		foreach ($langdirs as $langdir) {
			$langdir = baseName($langdir);
			$lc_locale = strToLower($locale);
			if ($lc_locale === strToLower($langdir)) {
				$g_gs_language = $langdir;
				return $langdir;
			}
		}
		foreach ($langdirs as $langdir) {
			$langdir = baseName($langdir);
			$lc_lang_locale = strToLower(subStr($locale,0,2));
			if ($lc_lang_locale === strToLower(subStr($langdir,0,2))) {
				$g_gs_language = $langdir;
				return $langdir;
			}
		}
		return false;
		
	}
}

function gs_loadtextdomain( $domain )
{
	global $g_gs_language, $g_gs_LANG, $g_gs_default_locale;
	
	if (GS_INTL_USE_GETTEXT && extension_loaded('gettext')) {
		
		$ok = bindtextdomain( $domain, GS_DIR .'locale/' );
		return (bool)$ok;
		
	} else {
		
		$filestr = GS_DIR .'locale/%lang/LC_MESSAGES/%domain.php';
		
		$file = str_replace(
			array( '%lang'             , '%domain' ),
			array( $g_gs_default_locale, $domain   ),
			$filestr);
		if (! file_exists($file)) return false;
		include_once( $file );
		
		if ($g_gs_language === $g_gs_default_locale) {
			$g_gs_LANG['current'][$domain] = @$g_gs_LANG[$g_gs_default_locale][$domain];
		} else {
			$file = str_replace(
				array( '%lang'       , '%domain' ),
				array( $g_gs_language, $domain   ),
				$filestr);
			if (! file_exists($file)) return false;
			include_once( $file );
			$g_gs_LANG['current'][$domain] = array_merge(
				@$g_gs_LANG[$g_gs_default_locale][$domain],
				@$g_gs_LANG[$g_gs_language      ][$domain]
			);
		}
		return true;
		
	}
}

function gs_settextdomain( $domain )
{
	global $g_gs_textdomain, $g_gs_language, $g_gs_LANG;
	
	if (GS_INTL_USE_GETTEXT && extension_loaded('gettext')) {
		
		$ok = textdomain( $domain );
		bind_textdomain_codeset( $domain, 'UTF-8' );
		return (bool)$ok;
		
	} else {
		
		if (! array_key_exists($domain, @$g_gs_LANG[$g_gs_language]))
			return false;
		
		$g_gs_textdomain = $domain;
		return true;
		
	}
}

function __( $msg )
{
	global $g_gs_textdomain, $g_gs_LANG;
	
	if (GS_INTL_USE_GETTEXT && extension_loaded('gettext')) {
		return gettext( $msg );
	} else {
		if (@is_array($g_gs_LANG['current'][$g_gs_textdomain])
		&&  array_key_exists($msg, $g_gs_LANG['current'][$g_gs_textdomain])
		&&  $g_gs_LANG['current'][$g_gs_textdomain][$msg] != '')
		{
			return $g_gs_LANG['current'][$g_gs_textdomain][$msg];
		}
		return $msg;
	}
}


?>