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

############################################################
##                                                        ##
##            ALL CONFIG OPTIONS WERE MOVED TO            ##
##                   /etc/gemeinschaft/                   ##
##                                                        ##
##              DO NOT MAKE ANY CHANGES HERE!             ##
##                                                        ##
############################################################


# error levels introduced in newer versions of PHP:
if (! defined('E_STRICT'           )) define('E_STRICT'           , 1<<11); # since PHP 5
if (! defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 1<<12); # since PHP 5.2
if (! defined('E_DEPRECATED'       )) define('E_DEPRECATED'       , 1<<13); # since PHP 5.3
if (! defined('E_USER_DEPRECATED'  )) define('E_USER_DEPRECATED'  , 1<<14); # since PHP 5.3

# available since PHP 4.4.0 and 5.0.5:
if (! defined('PHP_INT_SIZE')) define('PHP_INT_SIZE', 4);
if (! defined('PHP_INT_MAX' )) define('PHP_INT_MAX' , 2147483647);
# not available by default:
if (! defined('PHP_INT_MIN' )) define('PHP_INT_MIN' , (int)(-PHP_INT_MAX-1));


# override mbstring settings from php.ini
#
if (function_exists('mb_internal_encoding'))
	@mb_internal_encoding('UTF-8');
if (function_exists('mb_regex_encoding'))
	@mb_regex_encoding('UTF-8');
if (function_exists('mb_regex_set_options'))
	@mb_regex_set_options('pr');  # default: "pr"
if (function_exists('mb_http_output'))
	@mb_http_output('pass');
if (function_exists('mb_language'))
	@mb_language('uni');
if (function_exists('mb_substitute_character'))
	@mb_substitute_character(0xFFFD);
	# Unicode Replacement Character:
	# U+FFFD = 0xFFFD (utf16 hex) = 65533 (dec) = "\xEF\xBF\xBD" (utf8 hex)
if (function_exists('mb_detect_order'))
	@mb_detect_order('auto');
$tmp = strToLower(trim(@ini_get('mbstring.func_overload')));
if ($tmp >= '1' || $tmp === 'on') {
	echo "mbstring.func_overload must not be enabled in php.ini\n";
	exit(1);
}

# other php.ini settings
#
ini_set('display_errors', true);  # to be changed when our error handler is installed
error_reporting(E_ALL ^ E_NOTICE);
ini_set('log_errors', false);
ini_set('track_errors', false);
ini_set('default_socket_timeout', 20);
ini_set('html_errors', false);  # or else we'd have HTML in our log file for
                                # PHP errors
ini_set('tidy.clean_output', false);
ini_set('soap.wsdl_cache_enabled', true);
ini_set('soap.wsdl_cache_ttl', 86400);
//ob_implicit_flush(false);  # do not set to false! breaks various things like
                             # the #exec'ed scripts in asterisk conf files!
ini_set('output_buffering', false);
ini_set('output_handler', '');
//if (extension_loaded('zlib')) {
	//ini_set('zlib.output_compression', 'on');
	//ini_set('zlib.output_compression_level', 5);
	//ini_set('zlib.output_handler', '');
//}
set_magic_quotes_runtime(0);
//set_include_path('.');
set_time_limit(65);


# STDIN, STDOUT, STDERR
#
# http://bugs.php.net/bug.php?id=43283
# http://bugs.centos.org/view.php?id=1633
if (! defined('STDIN' )) define('STDIN' , @fOpen('php://stdin' , 'rb'));
if (! defined('STDOUT')) define('STDOUT', @fOpen('php://stdout', 'wb'));
if (! defined('STDERR')) define('STDERR', @fOpen('php://stderr', 'wb'));


# our root directory
#
define( 'GS_DIR', realPath(dirName(__FILE__).'/../').'/' );  # DO NOT CHANGE!


# log levels
#
define( 'GS_LOG_FATAL'  , 1<<1 );  # SYSTEM CONSTANTS. DO NOT CHANGE!
define( 'GS_LOG_WARNING', 1<<2 );  #  "
define( 'GS_LOG_NOTICE' , 1<<3 );  #  "
define( 'GS_LOG_DEBUG'  , 1<<4 );  #  "


function gs_get_conf( $key, $default=null )
{
	return @defined($key) ? constant($key) : $default;
}


$conf = '/etc/gemeinschaft/gemeinschaft.php';
if (! file_exists( $conf )) {
	trigger_error( "Config file \"$conf\" not found!\n", E_USER_ERROR );
	exit(1);
} else {
	if ((@include( $conf )) === false) {
		// () around the include are important!
		trigger_error( "Could not include config file \"$conf\"!\n", E_USER_ERROR );
		exit(1);
	}
}


function _gscnf( $param, $default=null )
{
	if (@array_key_exists($param, $GLOBALS)) {
		if (@is_scalar($GLOBALS[$param])) {
			define('GS_'.$param, $GLOBALS[$param]);
		} else {
			define('GS_'.$param, $default);
		}
		unset( $GLOBALS[$param] );
	} else {
		define('GS_'.$param, $default);
	}
}


if (! in_array($INSTALLATION_TYPE, array('gpbx', 'single', 'cluster'), true)) {
	trigger_error( "INSTALLATION_TYPE \"$INSTALLATION_TYPE\" not recognized! Must be one of \"gpbx\", \"single\", \"cluster\".\n", E_USER_ERROR );
	exit(1);
}
_gscnf( 'INSTALLATION_TYPE'         , 'single'           );
$INSTALLATION_TYPE_SINGLE = (in_array(GS_INSTALLATION_TYPE, array('gpbx', 'single'), true));
_gscnf( 'INSTALLATION_TYPE_SINGLE'  , false              );

if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
	$DB_MASTER_HOST = '127.0.0.1';
}
_gscnf( 'DB_MASTER_HOST'            , '0.0.0.0'          );
_gscnf( 'DB_MASTER_USER'            , 'gemeinschaft'     );
_gscnf( 'DB_MASTER_PWD'             , ''                 );
_gscnf( 'DB_MASTER_DB'              , 'asterisk'         );
_gscnf( 'DB_MASTER_TRANSACTIONS'    , true               );

if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
	# single server => db slave = db master,
	# so gs_db_slave_is_master() returns true
	$DB_SLAVE_HOST  = gs_get_conf('GS_DB_MASTER_HOST');
	$DB_SLAVE_USER  = gs_get_conf('GS_DB_MASTER_USER');
	$DB_SLAVE_PWD   = gs_get_conf('GS_DB_MASTER_PWD' );
	$DB_SLAVE_DB    = gs_get_conf('GS_DB_MASTER_DB'  );
}
_gscnf( 'DB_SLAVE_HOST'             , '127.0.0.1'        );
_gscnf( 'DB_SLAVE_USER'             , 'gemeinschaft'     );
_gscnf( 'DB_SLAVE_PWD'              , ''                 );
_gscnf( 'DB_SLAVE_DB'               , 'asterisk'         );

# null => normal master DB
_gscnf( 'DB_CDR_MASTER_HOST'        , null               );
_gscnf( 'DB_CDR_MASTER_USER'        , null               );
_gscnf( 'DB_CDR_MASTER_PWD'         , null               );
_gscnf( 'DB_CDR_MASTER_DB'          , null               );

_gscnf( 'DB_SIP_REG_UPDATE'         , gs_get_conf('GS_INSTALLATION_TYPE_SINGLE') );

_gscnf( 'LDAP_HOST'                 , '0.0.0.0'          );
_gscnf( 'LDAP_SSL'                  , false              );
_gscnf( 'LDAP_PORT'                 , 0                  );
_gscnf( 'LDAP_BINDDN'               , ''                 );
_gscnf( 'LDAP_PWD'                  , ''                 );
_gscnf( 'LDAP_PROTOCOL'             , 3                  );
_gscnf( 'LDAP_SEARCHBASE'           , ''                 );
_gscnf( 'LDAP_PROP_USER'            , 'uid'              );
_gscnf( 'LDAP_PROP_UID'             , 'uid'              );
_gscnf( 'LDAP_PROP_FIRSTNAME'       , 'givenname'        );
_gscnf( 'LDAP_PROP_LASTNAME'        , 'sn'               );
_gscnf( 'LDAP_PROP_PHONE'           , 'telephonenumber'  );
_gscnf( 'LDAP_PROP_EMAIL'           , 'mail'             );

_gscnf( 'GUI_AUTH_METHOD'           , 'gemeinschaft'     );
_gscnf( 'GUI_NUM_RESULTS'           , 12                 );
$GUI_SUDO_ADMINS = 'sysadmin,'. @$GUI_SUDO_ADMINS;
_gscnf( 'GUI_SUDO_ADMINS'           , ''                 );
_gscnf( 'GUI_SUDO_EXTENDED'         , false              );
_gscnf( 'GUI_PERMISSIONS_METHOD'    , 'gemeinschaft'     );
_gscnf( 'GUI_USER_MAP_METHOD'       , ''                 );
_gscnf( 'GUI_QUEUE_SHOW_NUM_CALLS'  , false              );
_gscnf( 'GUI_QUEUE_INFO_FROM_DB'    , true               );
_gscnf( 'GUI_MON_NOQUEUEBLUE'       , true               );
_gscnf( 'GUI_MON_PEERS_ENABLED'     , false              );
_gscnf( 'GUI_SHUTDOWN_ENABLED'      , gs_get_conf('GS_INSTALLATION_TYPE')==='gpbx');
_gscnf( 'GUI_LANGS'                 , 'de_DE:de_DE:de-DE:Deutsch, en_US:en_US:en-US:English' );
_gscnf( 'GUI_ADDITIONAL_STYLESHEET' , 'gemeinschaft.css' );

_gscnf( 'EXTERNAL_NUMBERS_BACKEND'  , 'db'               );
_gscnf( 'EXTERNAL_NUMBERS_LDAP_PROP', ''                 );

_gscnf( 'NOBODY_EXTEN_PATTERN'      , '95xxxx'           );
_gscnf( 'NOBODY_CID_NAME'           , 'Namenlos-'        );

_gscnf( 'PROV_HOST'                 , '0.0.0.0'          );
_gscnf( 'PROV_PORT'                 , 0                  );
_gscnf( 'PROV_SCHEME'               , 'http'             );
_gscnf( 'PROV_PATH'                 , '/'                );
_gscnf( 'PROV_AUTO_ADD_PHONE'       , false              );
_gscnf( 'PROV_AUTO_ADD_PHONE_HOST'  , 'first'            );
_gscnf( 'PROV_DIAL_LOG_LIFE'        , 14*24*3600         );
_gscnf( 'PROV_PROXIES_TRUST'        , ''                 );
_gscnf( 'PROV_PROXIES_XFF_HEADER'   , 'X-Forwarded-For'  );
_gscnf( 'PROV_ALLOW_NET'            , '192.168.1.0/24, 172.16.0.0/12, 10.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8' );
_gscnf( 'PROV_LAN_NETS'             , '0.0.0.0/0'        );
_gscnf( 'PROV_MODELS_ENABLED_SNOM'        , '*'          );  # / '360,370'
_gscnf( 'PROV_MODELS_ENABLED_SIEMENS'     , '*'          );  # / 'os20,os40,os60,os80'
_gscnf( 'PROV_MODELS_ENABLED_AASTRA'      , '*'          );  # / '51i,53i,55i,57i'
_gscnf( 'PROV_MODELS_ENABLED_GRANDSTREAM' , '*'          );

_gscnf( 'SNOM_PROV_ENABLED'         , false              );
_gscnf( 'SNOM_PROV_HTTP_USER'       , ''                 );
_gscnf( 'SNOM_PROV_HTTP_PASS'       , ''                 );
_gscnf( 'SNOM_PROV_PB_NUM_RESULTS'  , 15                 );
_gscnf( 'SNOM_PROV_FW_UPDATE'       , false              );
//_gscnf( 'SNOM_PROV_FW_BETA'         , false              );
_gscnf( 'SNOM_PROV_FW_6TO7'         , false              );
_gscnf( 'SNOM_PROV_FW_DEFAULT_300'  , null               );
_gscnf( 'SNOM_PROV_FW_DEFAULT_320'  , null               );
_gscnf( 'SNOM_PROV_FW_DEFAULT_360'  , null               );
_gscnf( 'SNOM_PROV_FW_DEFAULT_370'  , null               );
_gscnf( 'SNOM_PROV_KEY_BLACKLIST'   , ''                 );

_gscnf( 'SNOM_PROV_M3_ACCOUNTS'     , 1                  );
//_gscnf( 'SNOM_PROV_M3_FW_DEFAULT_SNOM_M3', null          );
//_gscnf( 'SNOM_PROV_M3_KEY_BLACKLIST', ''                 );

_gscnf( 'SIEMENS_PROV_ENABLED'      , false              );
_gscnf( 'SIEMENS_PROV_PREFER_HTTP'  , true               );
_gscnf( 'SIEMENS_PROV_FW_DEFAULT_OS20', null             );
_gscnf( 'SIEMENS_PROV_FW_DEFAULT_OS40', null             );
_gscnf( 'SIEMENS_PROV_FW_DEFAULT_OS60', null             );
_gscnf( 'SIEMENS_PROV_FW_DEFAULT_OS80', null             );
_gscnf( 'SIEMENS_PROV_KEY_BLACKLIST', ''                 );

_gscnf( 'AASTRA_PROV_ENABLED'       , false              );
_gscnf( 'AASTRA_PROV_PB_NUM_RESULTS', 10                 );
_gscnf( 'AASTRA_PROV_FW_DEFAULT_51I', null               );
_gscnf( 'AASTRA_PROV_FW_DEFAULT_53I', null               );
_gscnf( 'AASTRA_PROV_FW_DEFAULT_55I', null               );
_gscnf( 'AASTRA_PROV_FW_DEFAULT_57I', null               );
_gscnf( 'AASTRA_PROV_KEY_BLACKLIST' , ''                 );

_gscnf( 'GRANDSTREAM_PROV_ENABLED'  , false              );
_gscnf( 'GRANDSTREAM_PROV_HTTP_PASS', ''                 );
_gscnf( 'GRANDSTREAM_PROV_NTP'      , gs_get_conf('GS_PROV_HOST','') );
//_gscnf( 'GRANDSTREAM_PROV_FW_DEFAULT_BT110'  , null      );  //FIXME?
//_gscnf( 'GRANDSTREAM_PROV_FW_DEFAULT_BT200'  , null      );  // "
//_gscnf( 'GRANDSTREAM_PROV_FW_DEFAULT_GXP2000', null      );  // "
//...
//_gscnf( 'GRANDSTREAM_PROV_KEY_BLACKLIST', ''             );  //FIXME?

_gscnf( 'CANONIZE_OUTBOUND'         , true               );
_gscnf( 'CANONIZE_INTL_PREFIX'      , '00'               );
_gscnf( 'CANONIZE_COUNTRY_CODE'     , '49'               );
_gscnf( 'CANONIZE_NATL_PREFIX'      , '0'                );
_gscnf( 'CANONIZE_NATL_PREFIX_INTL' , false              );
_gscnf( 'CANONIZE_AREA_CODE'        , '999'              );
_gscnf( 'CANONIZE_LOCAL_BRANCH'     , '999999'           );
_gscnf( 'CANONIZE_SPECIAL'          , '/^1(?:1[0-9]{1,5}|9222)/' );
_gscnf( 'CANONIZE_CBC_PREFIX'       , '010'              );

_gscnf( 'DP_SUBSYSTEM'              , false              );
_gscnf( 'DP_EMERGENCY_POLICE'       , '110,0110'         );
_gscnf( 'DP_EMERGENCY_POLICE_MAP'   , '110'              );
_gscnf( 'DP_EMERGENCY_FIRE'         , '112,0112'         );
_gscnf( 'DP_EMERGENCY_FIRE_MAP'     , '112'              );
_gscnf( 'DP_DIALTIMEOUT_IN'         , 45                 );
_gscnf( 'DP_PRV_CALL_PREFIX'        , '*7*'              );
_gscnf( 'DP_FORWARD_REQ_EXT_NUM'    , false              );
_gscnf( 'DP_ALLOW_DIRECT_DIAL'      , false              );
_gscnf( 'DP_CONNID'                 , false              );

_gscnf( 'PB_IMPORTED_ENABLED'       , false              );
_gscnf( 'PB_IMPORTED_ORDER'         , 2                  );
_gscnf( 'PB_IMPORTED_TITLE'         , "Importiert"       );
_gscnf( 'PB_INTERNAL_TITLE'         , "Intern"           );
_gscnf( 'PB_PRIVATE_TITLE'          , "Pers\xC3\xB6nlich");

_gscnf( 'LOCK_DIR'                  , '/var/lock/'       );
_gscnf( 'CALL_INIT_FROM_NET'        , '0.0.0.0/32'       ); # deny all
_gscnf( 'MONITOR_FROM_NET'          , '0.0.0.0/32'       ); # deny all
_gscnf( 'LVM_USER_6_DIGIT_INT'      , false              );
_gscnf( 'LVM_CALL_INIT_USERS_500000', false              );
_gscnf( 'CC_TIMEOUT'                , 60                 );
_gscnf( 'INTL_LANG'                 , 'de_DE'            );
_gscnf( 'INTL_USE_GETTEXT'          , false              );
_gscnf( 'INTL_ASTERISK_LANG'        , 'de'               );

_gscnf( 'USERCOMMENT_OFFTIME'       , 'Feierabend'       );
_gscnf( 'EMAIL_PATTERN_VALID'       , '/^[a-z0-9\-._]+@[a-z0-9\-._]{2,80}\.[a-z]{2,10}$/i'              );
_gscnf( 'EMAIL_DELIVERY'            , 'sendmail'         );
_gscnf( 'USER_SELECT_CALLERID'      , false		 );
#Variables for the Astbuttond
_gscnf( 'BUTTONDAEMON_USE'            , false           );
_gscnf( 'BUTTONDAEMON_HOST'            , '127.0.0.1'    );
_gscnf( 'BUTTONDAEMON_PORT'            , 5041           );
_gscnf( 'BUTTONDAEMON_SECRET'            , 'SecretLocaNetPassword' );

# to communicate with HylaFax ftp_raw() is required, which is not
# available in PHP < 5
if ((float)PHP_VERSION < 5.0)
	$FAX_ENABLED = false;
_gscnf( 'FAX_ENABLED'               , false              );
_gscnf( 'FAX_TSI_PREFIX',
	gs_get_conf('GS_CANONIZE_NATL_PREFIX' , '0'  ).
	gs_get_conf('GS_CANONIZE_AREA_CODE'   , '999').
	gs_get_conf('GS_CANONIZE_LOCAL_BRANCH', '999'));
_gscnf( 'FAX_PREFIX'                , ''                 );
_gscnf( 'FAX_TSI'                   , ''                 );
# (TSI = Transmitting Subscriber Identification)
_gscnf( 'FAX_HYLAFAX_HOST'          , '127.0.0.1'        );
_gscnf( 'FAX_HYLAFAX_PORT'          , 4559               );
$FAX_HYLAFAX_ADMIN =
	preg_replace('/[^a-z0-9\-_.]/i', '',
	@$FAX_HYLAFAX_ADMIN );
_gscnf( 'FAX_HYLAFAX_ADMIN'         , ''                 );
$FAX_HYLAFAX_PASS  =
	preg_replace('/[^a-z0-9\-_.]/i', '',
	@$FAX_HYLAFAX_PASS  );
_gscnf( 'FAX_HYLAFAX_PASS'          , ''                 );
_gscnf( 'FAX_HYLAFAX_PATH'          , '/var/spool/hylafax/' );

_gscnf( 'BOI_ENABLED'               , false              );
_gscnf( 'BOI_API_DEFAULT'           , 'm01'              );
_gscnf( 'BOI_BRANCH_NETMASK'        , '/24'              );
_gscnf( 'BOI_BRANCH_PBX'            , '0.0.0.130'        );
_gscnf( 'BOI_NOBODY_EXTEN_PATTERN'  , '95xxxx'           );
_gscnf( 'BOI_GUI_REVERSE_PROXY'     , 'http://'. gs_get_conf('GS_PROV_HOST') .':8080/' );
_gscnf( 'BOI_GUI_HOME_USER'         , 'information/praesenzmonitor' );
_gscnf( 'BOI_GUI_HOME_ADMIN'        , 'information/status' );

_gscnf( 'LOG_TO'                    , 'file'             );
_gscnf( 'LOG_FILE'                  , '/var/log/gemeinschaft/gs.log' );
_gscnf( 'LOG_GMT'                   , true               );
_gscnf( 'LOG_SYSLOG_FACILITY'       , 'local5'           );
$LOG_LEVEL = strToUpper(@$LOG_LEVEL);
if (! in_array($LOG_LEVEL, array('FATAL', 'WARNING', 'NOTICE', 'DEBUG'), true))
	$LOG_LEVEL = 'NOTICE';
define( 'GS_LOG_LEVEL', constant('GS_LOG_'.$LOG_LEVEL) );



if (function_exists('date_default_timezone_set')) {
	# PHP >= 5.1.0
	# needed by date() and other functions
	@date_default_timezone_set( @date_default_timezone_get() );
}

# logger and error handler:
require_once( GS_DIR .'inc/util.php' );
set_error_handler('err_handler_die_on_err');
ini_set('display_errors', false);

//ini_set('log_errors', true);
//ini_set('error_log', '/var/log/gemeinschaft/gs.log');

# include gettext functions here because conf.php is included
# in every file
include_once( GS_DIR .'inc/gettext.php' );


?>