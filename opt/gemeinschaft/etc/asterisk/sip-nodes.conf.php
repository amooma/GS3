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

function printparam( $param, $value, &$userparamarray, $html=false ) {
	if ($param == '')
		return;
	$printbr = true;

	if (array_key_exists($param, $userparamarray)) {
		if ($userparamarray[$param] != '')
			echo $param ." = ". $userparamarray[$param];
		else
			$printbr = false;
		unset($userparamarray[$param]);
	} else {
		echo $param ." = ". $value;
	}
	if ($printbr) {
		if ($html)
			echo "<br \>";
		echo "\n";
	}
}

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

error_reporting(0);

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
set_error_handler('err_handler_quiet');
require_once( GS_DIR .'inc/log.php' );
require_once( GS_DIR .'inc/langhelper.php' );

if (! gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
	
	require_once( GS_DIR .'inc/get-listen-to-ids.php' );
	require_once( GS_DIR .'inc/gs-lib.php' );
	include_once( GS_DIR .'inc/gs-fns/gs_hosts_get.php' );
	
	$our_ids = @ gs_get_listen_to_ids();
	if (! is_array($our_ids)) $our_ids = array();
	//echo 'OUR IDS: ', implode(', ', $our_ids), "\n";
	
	$hosts = @ gs_hosts_get();
	if (isGsError( $hosts )) $hosts = array();
	if (! $hosts)            $hosts = array();
	//echo "HOSTS:\n"; print_r($hosts);
	
	$min_our_ids = (count($our_ids) > 0) ? min($our_ids) : 0;
	$outUser = 'gs-'. str_pad( $min_our_ids, 4, '0', STR_PAD_LEFT );
	
	$out = '';
	foreach ($hosts as $host) {
		if (in_array( (int)$host['id'], $our_ids )) {
			//echo "SKIPPING ", $host['id'], "\n";
			continue;
		} else
			//echo "DOING ", $host['id'], "\n";
		
		# it's one of the other nodes
		
		$inUser = 'gs-'. str_pad( $host['id'], 4, '0', STR_PAD_LEFT );
		$inPass = 'thiS is rEally seCret.';
		$inPass = subStr( str_replace(
			array( '+', '/', '=' ),
			array( '', '', ''  ),
			base64_encode( $inPass )
		), 0, 25 );
		$outPass = $inPass;
		
		$name = str_pad( $host['id'], 4, '0', STR_PAD_LEFT );
		$out .= '
[gs-'. $name .'](node-user)
host='. $host['host'] .'
defaultip='. $host['host'] .'
username='. $inUser .'
secret='. $inPass .'
setvar=__from_node=yes

[gs-'. $name .'](node-peer)
host='. 'dynamic' .'
defaultip='. $host['host'] .'
username='. $outUser .'
fromuser='. $outUser .'
secret='. $outPass .'
setvar=__from_node=yes
';
	}
	echo $out;
	
}
echo "\n";



# get gateways from DB
#
require_once( GS_DIR .'inc/db_connect.php' );
$DB = gs_db_master_connect();
if (! $DB) {
	exit(1);
}
$rs = $DB->execute(
'SELECT
	`g`.`id`, `g`.`name`, `g`.`host`, `g`.`proxy`, `g`.`user`, `g`.`pwd`,
	`gg`.`name` `gg_name`
FROM
	`gates` `g` JOIN
	`gate_grps` `gg` ON (`gg`.`id`=`g`.`grp_id`)
WHERE
	`g`.`type`=\'sip\' AND
	`g`.`host` IS NOT NULL
ORDER BY `g`.`id`'
);
while ($gw = $rs->fetchRow()) {
	if ($gw['name'] == '') continue;
	if ($gw['host'] == '') continue;
	
	$params = array();
	$params['nat'           ] = 'yes';
	//$params['canreinvite'   ] = 'no';
	$params['canreinvite'   ] = 'nonat';
	$params['port'          ] = 5060;
	$params['call-limit'    ] = 0;
	$params['dtmfmode'      ] = 'rfc2833';
	$params['qualify'       ] = 'yes';
	$params['maxexpiry'     ] =  185;
	$params['defaultexpiry' ] =  145;
	$params['permit'        ] = null;
	
	$codecs_allow = array();
	$codecs_allow['alaw'   ] = true;
	$codecs_allow['ulaw'   ] = false;
	
	//$params['fromdomain'    ] = 'gemeinschaft.localdomain';
	$params['fromdomain'    ] = null;
	$params['fromuser'      ] = null;
	
	if (preg_match('/@([^@]*)$/', $gw['user'], $m)) {
		# set domain in the From header
		$params['fromdomain'    ] = $m[1];
		$gw['user'] = subStr($gw['user'], 0, -strLen($m[0]));
		
		# assume that this SIP provider requires the username
		# instead of the caller ID number in the From header (and
		# that the caller ID is to be set in a P-Preferred-Identity
		# header)
		$params['fromuser'      ]   = $gw['user'];
		
		# also assume that this gateway is a SIP provider and
		# that re-invites will not work
		$params['canreinvite'   ]    = 'no';
	}
	
	if ($gw['proxy'] == null || $gw['proxy'] === $gw['host']) {
		$gw['proxy'] = null;
	}
	
	/*
	if (strToLower($gw['host']) === 'sip.1und1.de') {  # special settings for 1und1.de
		//$canreinvite    = 'no';
		
		//$fromdomain     = '1und1.de';
		//$fromuser       = $gw['user'];
		
		//$codecs_allow['alaw'   ] = true;
		//$codecs_allow['ulaw'   ] = true;
		//$codecs_allow['ilbc'   ] = true;
		//$codecs_allow['gsm'    ] = true;
		//$codecs_allow['g729'   ] = true;
		//$codecs_allow['slinear'] = true;
	}
	elseif (strToLower($gw['host']) === 'sipgate.de') {  # special settings for SipGate.de
		//$canreinvite    = 'no';
		//$fromdomain     = 'sipgate.de';
		//$fromuser       = $gw['user'];
	}
	elseif (preg_match('/\\.sipgate\\.de$/i', $gw['host'])) {  # special settings for SipGate.de
		# sipconnect.sipgate.de, SipGate "Team" trunk
		//$fromuser       = $gw['user'];
		//$canreinvite    = 'no';
	}
	*/
	
	
	$params_override = array();
	$params_rs = $DB->execute( 'SELECT `param`, `value` FROM `gate_params` WHERE `gate_id`='.$gw['id'] );
	while ($param = $params_rs->fetchRow()) {
		$params_override[$param['param']] = $param['value'];
	}
	
	if (array_key_exists('port'          , $params_override)) {
	//&&  (int)$params_override['port'          ] != 0) {
		$params['port'          ] = $params_override['port'          ];
	}
	if (array_key_exists('nat'           , $params_override)) {
		$params['nat'           ] = $params_override['nat'           ];
	}
	if (array_key_exists('canreinvite'   , $params_override)) {
		$params['canreinvite'   ] = $params_override['canreinvite'   ];
	}
	if (array_key_exists('qualify'       , $params_override)) {
		$params['qualify'       ] = $params_override['qualify'       ];
	}
	if (array_key_exists('call-limit'    , $params_override)) {
		$params['call-limit'    ] = $params_override['call-limit'    ];
	}
	if (array_key_exists('dtmfmode'      , $params_override)) {
		$params['dtmfmode'      ] = $params_override['dtmfmode'      ];
	}
	if (array_key_exists('allow'         , $params_override)) {
		foreach ($codecs_allow as $codec => $allow) {
			$codecs_allow[$codec] = false;
		}
		
		$params_override_codecs = preg_split('/\s*,\s*/', trim($params_override['allow']));
		foreach ($params_override_codecs as $codec) {
			$codecs_allow[$codec] = true;
		}
		
		$num_allowed_codecs = 0;
		foreach ($codecs_allow as $codec => $allow) {
			if ($allow) ++$num_allowed_codecs;
		}
		if ($num_allowed_codecs < 1) {
			gs_log( GS_LOG_WARNING, 'You did not allow any codecs for gateway '. $gw['name'] .'. Allowing G.711a by default.' );
			$codecs_allow['alaw'   ] = true;
		}
	}
	if (array_key_exists('permit'        , $params_override)
	&&  'x'.$params_override['permit'        ] != 'x'.'0.0.0.0/0') {
		$params['permit'        ] = $params_override['permit'        ];
	}
	
	
	$userparamarray = array();
	$g_params = $DB->execute('SELECT * FROM `gate_params` WHERE `gate_id` ='.$gw['id']);
	while ($param = $g_params->fetchRow())
		$userparamarray[$param['param']] = $param['value'];

	echo '[', $gw['name'] ,']' ,"\n";
	echo 'type = '            , 'peer' ,"\n";
	echo 'host = '            , $gw['host'] ,"\n";
	if ($params['port'          ] > 0) {
		echo 'port = '            , $params['port'          ] ,"\n";
	}
	echo 'username = '            , $gw['user'] ,"\n";
	echo 'secret = '              , $gw['pwd' ] ,"\n";
	if ($gw['proxy'] != null) {
		echo 'outboundproxy = '   , $gw['proxy'] ,"\n";
	}
	if ($params['fromdomain'    ] != null) {
		echo 'fromdomain = '      , $params['fromdomain'    ] ,"\n";
	}
	if ($params['fromuser'      ] != null) {
		echo 'fromuser = '        , $params['fromuser'      ] ,"\n";
	}
	echo 'insecure = '        , 'port,invite' ,"\n";
	echo 'nat = '             , $params['nat'           ] ,"\n";
	echo 'canreinvite = '     , $params['canreinvite'   ] ,"\n";
	echo 'dtmfmode = '        , $params['dtmfmode'      ] ,"\n";
	echo 'call-limit = '      , $params['call-limit'    ] ,"\n";
	echo 'registertimeout = ' , '60' ,"\n";
	//echo 't38pt_udptl = yes' ,"\n";
	echo 'setvar=__is_from_gateway=1' ,"\n";
	echo 'context = '         , 'from-gg-'.$gw['gg_name'] ,"\n";
	echo 'qualify = '         , $params['qualify'       ] ,"\n";
	echo 'maxexpiry = '       , $params['maxexpiry'     ] ,"\n";
	echo 'defaultexpiry = '   , $params['defaultexpiry' ] ,"\n";
	echo 'disallow = '        , 'all' ,"\n";

	if (strlen(trim(gs_get_conf('GS_INTL_ASTERISK_LANG'))) > 0)
		echo 'language = ', gs_get_lang_global(GS_LANG_OPT_AST, GS_LANG_FORMAT_AST) ,"\n";

	foreach ($codecs_allow as $codec => $allowed) {
		if ($allowed) {
			printparam( 'allow', $codec, $userparamarray);
		}
	}
	if ($params['permit'        ] != null
	&&  'x'.$params['permit'        ] != 'x'.'0.0.0.0/0') {
		echo 'deny = '            ,'0.0.0.0/0.0.0.0' ,"\n";  # deny all
		echo 'permit = '          , $params['permit'        ] ,"\n";
	}
	echo "\n";
}

?>