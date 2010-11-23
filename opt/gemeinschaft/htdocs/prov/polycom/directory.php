<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
*
* $Revision$
*
* Copyright 2007-2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* 
* Author: Daniel Scheller <scheller@loca.net>
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

header( 'Content-Type: text/plain; charset=utf-8' );
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

require_once( dirname(__FILE__) .'/../../../inc/conf.php' );
require_once(GS_DIR ."inc/util.php");
require_once(GS_DIR ."inc/gs-lib.php");
require_once( GS_DIR .'inc/prov-fns.php' );
require_once(GS_DIR ."inc/langhelper.php");
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/string.php' );

//---------------------------------------------------------------------------

function _settings_err( $msg='' )
{
	@ob_end_clean();
	@ob_start();

	echo '<!-- // ', ($msg != '' ? str_replace('--','- -',$msg) : 'Error') ,' // -->',"\n";
	if (!headers_sent())
	{
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}

	@ob_end_flush();
	exit(1);
}

//---------------------------------------------------------------------------

if (! gs_get_conf('GS_POLYCOM_PROV_ENABLED') )
{
	gs_log(GS_LOG_DEBUG, 'Polycom provisioning not enabled');
	_settings_err('Not enabled.');
}

$requester = gs_prov_check_trust_requester();
if (!$requester['allowed'])
{
	_settings_err('No! See log for details.');
}

//--- identify polycom phone

$mac = preg_replace('/[^0-9A-F]/', '', strtoupper(@$_REQUEST['mac']));
if ( strlen($mac) !== 12 )
{
        gs_log(GS_LOG_NOTICE, 'Polycom provisioning: Invalid MAC address \"$mac\" (wrong length)');
        //--- don't explain this to the users
        _settings_err('No! See log for details.');
}
if ( hexdec(substr($mac, 0, 2)) % 2 == 1 )
{
        gs_log(GS_LOG_NOTICE, 'Polycom provisioning: Invalid MAC address \"$mac\" (multicast address)');
        //--- don't explain this to the users
        _settings_err('No! See log for details.');
}
if ( $mac === '000000000000' )
{
        gs_log(GS_LOG_NOTICE, 'Polycom provisioning: Invalid MAC address \"$mac\" (huh?)');
        //--- don't explain this to the users
        _settings_err('No! See log for details.');
}

//--- make sure the phone is a Polycom

if ( substr($mac, 0, 6) !== '0004F2' )
{
        gs_log(GS_LOG_NOTICE, 'Polycom provisioning: MAC address \"$mac\" is not a Polycom phone');
        //--- don't explain this to the users
        _settings_err('No! See log for details.');
}

//--- useragent identification

$ua = trim(@$_SERVER['HTTP_USER_AGENT']);
$ua = "PolycomSoundPointIP";

if ( preg_match('/PolycomSoundPointIP/', $ua) )
{
	$phone_model = ((preg_match('/PolycomSoundPointIP\-SPIP_(\d+)\-UA\//', $ua, $m)) ? $m[1] : 'unknown');
	$phone_type = 'polycom-spip-'. $phone_model;
}
else if(preg_match('/PolycomSoundStationIP/', $ua))
{
	$phone_model = ((preg_match('/PolycomSoundStationIP\-SSIP_(\d+)\-UA\//', $ua, $m)) ? $m[1] : 'unknown');
	$phone_type = 'polycom-ssip-'. $phone_model;
}
else
{
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Polycom) has invalid User-Agent (\"". $ua ."\")");
	//--- don't explain this to the users
	_settings_err('No! See log for details.');
}

//--- check if this phone has the XHTML microbrowser and prepare vars
//--- for directory generator.

switch ($phone_model)
{
        case '300' :
        case '500' :
                $phone_has_microbrowser = FALSE;
                break;
        default :
                $phone_has_microbrowser = TRUE;
                break;
}

$db = gs_db_slave_connect();

//---debug
$user_id = @gs_prov_user_id_by_mac_addr($db, $mac);
if(!$user_id) die();

$userinfo = @gs_prov_get_user_info($db, $user_id);

// setup i18n stuff
gs_setlang(gs_lang_ast2gs($userinfo["language"]));
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );

//--- echo the phone directory

echo '<' . '?xml version="1.0" encoding="UTF-8" standalone="yes"?' . '>' ."\n";

if (!$phone_has_microbrowser)
{
	//--- this phone does not have microbrowser capabilities, so create
	//--- a company directory based on the local users table

	$query =
		'SELECT '.
		'  `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext` '.
		'FROM '.
		'  `users` `u` '.
		'JOIN '.
		'  `ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) '.
		'WHERE '.
		'  `u`.`pb_hide` = 0 '.
		'  AND `u`.`nobody_index` IS NULL '.
		'ORDER BY `u`.`lastname`, `u`.`firstname`';

	$rs = $db->execute($query);

	if ($rs->numRows() !== 0)
	{
		echo '<directory>',"\n";
		echo '   <item_list>',"\n";

		while($r = $rs->fetchRow())
		{
			echo '      <item>',"\n";
			echo '         <fn>'. htmlEnt($r['fn']) .'</fn>',"\n";
			echo '         <ln>'. htmlEnt($r['ln']) .'</ln>',"\n";
			echo '         <ct>'. $r['ext'] .'</ct>',"\n";
			echo '      </item>',"\n";
		}

		echo '   </item_list>',"\n";
		echo '</directory>',"\n";
	}
}
else
{
	//--- this phone model has the microbrowser - create speeddials
	//--- for the key remappings

	echo '<directory>',"\n";
	echo '   <item_list>',"\n";
	echo '      <item>',"\n";
	echo '         <fn>', __("Ruflisten"), '</fn>',"\n";
	echo '         <ct>!gsdiallog</ct>',"\n";
	echo '         <sd>1</sd>',"\n";
	echo '         <bw>0</bw>',"\n";
	echo '         <bb>0</bb>',"\n";
	echo '      </item>',"\n";
	echo '      <item>',"\n";
	echo '         <fn>', __("Telefonbuch"), '</fn>',"\n";
	echo '         <ct>!gsphonebook</ct>',"\n";
	echo '         <sd>2</sd>',"\n";
	echo '         <bw>0</bw>',"\n";
	echo '         <bb>0</bb>',"\n";
	echo '      </item>',"\n";
	/*
	echo '      <item>',"\n";
	echo '         <fn>Ruhe/DND</fn>',"\n";
	echo '         <ct>!gsdnd</ct>',"\n";
	echo '         <sd>3</sd>',"\n";
	echo '         <bw>0</bw>',"\n";
	echo '         <bb>0</bb>',"\n";
	echo '      </item>',"\n";
	 */
	echo '      <item>',"\n";
	echo '         <fn>', __("Einstellungen"), '</fn>',"\n";
	echo '         <ct>!gsmenu</ct>',"\n";
	echo '         <sd>4</sd>',"\n";
	echo '         <bw>0</bw>',"\n";
	echo '         <bb>0</bb>',"\n";
	echo '      </item>',"\n";
	echo '   </item_list>',"\n";
	echo '</directory>',"\n";
}

?>