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

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

error_reporting(0);

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
set_error_handler('err_handler_quiet');
require_once( GS_DIR .'inc/log.php' );
require_once( GS_DIR .'inc/langhelper.php' );


# get gateways from DB
#
require_once( GS_DIR .'inc/db_connect.php' );
$DB = gs_db_master_connect();
if (! $DB) {
	exit(1);
}
$rs = $DB->execute(
'SELECT
	`g`.`id`, `g`.`name`, `g`.`host`, `g`.`proxy`, `g`.`user`, `g`.`pwd`, `g`.`register`,
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
	# a line break in a value would add lines to the config
	foreach ($gw as $gw_k => $gw_v) {
		if (is_string($gw_v)) $gw[$gw_k] = preg_replace('/[\x00-\x1F\x7F]/', '', $gw_v);
	}
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
		$params_override[$param['param']] = preg_replace('/[\x00-\x1F\x7F]/', '', (string)$param['value']);
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
	
	
	$name = $gw['name'];
	$nat  = ($params['nat'] !== 'no') ? 'yes' : 'no';
	$host_port = $gw['host'] . ($params['port'] > 0 ? ':'.$params['port'] : '');
	$has_auth = ($gw['user'] != '' && $gw['pwd'] != '');
	
	$codecs = array();
	foreach ($codecs_allow as $codec => $allowed) {
		if ($allowed) $codecs[] = $codec;
	}
	$dtmfmode = ($params['dtmfmode'] === 'rfc2833') ? 'rfc4733' : $params['dtmfmode'];
	
	echo '[', $name ,']' ,"\n";
	echo 'type = endpoint' ,"\n";
	echo 'transport = transport-udp' ,"\n";
	echo 'context = from-gg-', $gw['gg_name'] ,"\n";
	echo 'aors = ', $name ,"\n";
	if ($has_auth) {
		echo 'outbound_auth = ', $name ,'-auth' ,"\n";
	}
	echo 'identify_by = ip' ,"\n";
	echo 'disallow = all' ,"\n";
	echo 'allow = ', implode(',', $codecs) ,"\n";
	echo 'dtmf_mode = ', $dtmfmode ,"\n";
	echo 'direct_media = ', ($params['canreinvite'] === 'yes' ? 'yes' : 'no') ,"\n";
	echo 'force_rport = ', $nat ,"\n";
	echo 'rewrite_contact = ', $nat ,"\n";
	echo 'rtp_symmetric = ', $nat ,"\n";
	echo 'set_var = __is_from_gateway=1' ,"\n";
	if ($gw['proxy'] != null) {
		echo 'outbound_proxy = sip:', $gw['proxy'] ,'\;lr' ,"\n";
	}
	if ($params['fromdomain'] != null) {
		echo 'from_domain = ', $params['fromdomain'] ,"\n";
	}
	if ($params['fromuser'] != null) {
		echo 'from_user = ', $params['fromuser'] ,"\n";
	}
	if (strlen(trim(gs_get_conf('GS_INTL_ASTERISK_LANG'))) > 0) {
		echo 'language = ', gs_get_lang_global(GS_LANG_OPT_AST, GS_LANG_FORMAT_AST) ,"\n";
	}
	if ($params['permit'] != null
	&&  'x'.$params['permit'] != 'x'.'0.0.0.0/0') {
		echo 'deny = 0.0.0.0/0.0.0.0' ,"\n";  # deny all
		echo 'permit = ', $params['permit'] ,"\n";
	}
	echo "\n";
	
	echo '[', $name ,']' ,"\n";
	echo 'type = aor' ,"\n";
	echo 'contact = sip:', $host_port ,"\n";
	if ($params['qualify'] !== 'no') {
		echo 'qualify_frequency = 60' ,"\n";
	}
	echo "\n";
	
	if ($has_auth) {
		echo '[', $name ,'-auth]' ,"\n";
		echo 'type = auth' ,"\n";
		echo 'auth_type = userpass' ,"\n";
		echo 'username = ', $gw['user'] ,"\n";
		echo 'password = ', $gw['pwd'] ,"\n";
		echo "\n";
	}
	
	echo '[', $name ,'-identify]' ,"\n";
	echo 'type = identify' ,"\n";
	echo 'endpoint = ', $name ,"\n";
	echo 'match = ', $gw['host'] ,"\n";
	echo "\n";
	
	if ($gw['register'] && $gw['user'] != '') {
		$reg_domain = ($params['fromdomain'] != null) ? $params['fromdomain'] : $gw['host'];
		$server = ($gw['proxy'] != null) ? $gw['proxy'] : $host_port;
		
		echo '[', $name ,'-reg]' ,"\n";
		echo 'type = registration' ,"\n";
		echo 'transport = transport-udp' ,"\n";
		echo 'server_uri = sip:', $server ,"\n";
		echo 'client_uri = sip:', $gw['user'] ,'@', $reg_domain ,"\n";
		echo 'contact_user = ', $gw['user'] ,"\n";
		if ($has_auth) {
			echo 'outbound_auth = ', $name ,'-auth' ,"\n";
		}
		echo 'endpoint = ', $name ,"\n";
		echo 'line = yes' ,"\n";
		echo 'retry_interval = 60' ,"\n";
		echo 'forbidden_retry_interval = 60' ,"\n";
		echo 'max_retries = 10000' ,"\n";
		echo "\n";
	}
}

?>
