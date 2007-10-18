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


# the E_STRICT error level (and constant) was introduced in PHP 5
if (! defined('E_STRICT')) define('E_STRICT', 2048);


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
if ($tmp === '1' || $tmp === 'on') {
	echo "mbstring.func_overload must not be enabled in php.ini\n";
	exit(1);
}


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


_gscnf( 'DB_MASTER_HOST'            , '0.0.0.0'          );
_gscnf( 'DB_MASTER_USER'            , 'root'             );
_gscnf( 'DB_MASTER_PWD'             , ''                 );
_gscnf( 'DB_MASTER_DB'              , 'asterisk'         );
_gscnf( 'DB_MASTER_TRANSACTIONS'    , true               );

_gscnf( 'DB_SLAVE_HOST'             , '127.0.0.1'        );
_gscnf( 'DB_SLAVE_USER'             , 'root'             );
_gscnf( 'DB_SLAVE_PWD'              , ''                 );
_gscnf( 'DB_SLAVE_DB'               , 'asterisk'         );

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

_gscnf( 'GUI_AUTH_METHOD'           , 'gemeinschaft'     );
_gscnf( 'GUI_NUM_RESULTS'           , 12                 );
_gscnf( 'GUI_SUDO_ADMINS'           , ''                 );
_gscnf( 'GUI_SUDO_EXTENDED'         , false              );
_gscnf( 'GUI_QUEUE_SHOW_NUM_CALLS'  , false              );
_gscnf( 'GUI_QUEUE_INFO_FROM_DB'    , false              );
_gscnf( 'GUI_MON_NOQUEUEBLUE'       , true               );
_gscnf( 'GUI_MON_PEERS_ENABLED'     , false              );
_gscnf( 'GUI_SHUTDOWN_ENABLED'      , false              );

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

_gscnf( 'SNOM_PROV_ENABLED'         , false              );
_gscnf( 'SNOM_PROV_HTTP_USER'       , 'gs'               );
_gscnf( 'SNOM_PROV_HTTP_PASS'       , 'fS8jr5mo6s2Zs83D' );
_gscnf( 'SNOM_PROV_PB_NUM_RESULTS'  , 15                 );
_gscnf( 'SNOM_PROV_FW_UPDATE'       , false              );
_gscnf( 'SNOM_PROV_FW_BETA'         , false              );
_gscnf( 'SNOM_PROV_FW_6TO7'         , false              );

_gscnf( 'CANONIZE_OUTBOUND'         , true               );
_gscnf( 'CANONIZE_INTL_PREFIX'      , '00'               );
_gscnf( 'CANONIZE_COUNTRY_CODE'     , '49'               );
_gscnf( 'CANONIZE_NATL_PREFIX'      , '0'                );
_gscnf( 'CANONIZE_NATL_PREFIX_INTL' , false              );
_gscnf( 'CANONIZE_AREA_CODE'        , ''                 );
_gscnf( 'CANONIZE_LOCAL_BRANCH'     , ''                 );
_gscnf( 'CANONIZE_SPECIAL'          , '/^1(?:1[0-9]{1,5}|9222)/' );
_gscnf( 'CANONIZE_CBC_PREFIX'       , '010'              );

_gscnf( 'DP_EMERGENCY_POLICE'       , '110,0110'         );
_gscnf( 'DP_EMERGENCY_POLICE_MAP'   , '110'              );
_gscnf( 'DP_EMERGENCY_FIRE'         , '112,0112'         );
_gscnf( 'DP_EMERGENCY_FIRE_MAP'     , '112'              );
_gscnf( 'DP_DIALTIMEOUT_IN'         , 45                 );
_gscnf( 'DP_PRV_CALL_PREFIX'        , '*7*'              );

_gscnf( 'PB_IMPORTED_ENABLED'       , false              );
_gscnf( 'PB_IMPORTED_ORDER'         , 2                  );
_gscnf( 'PB_IMPORTED_TITLE'         , "Firma (aus LDAP)" );
_gscnf( 'PB_INTERNAL_TITLE'         , "Intern"           );
_gscnf( 'PB_PRIVATE_TITLE'          , "Pers\xC3\xB6nlich");

_gscnf( 'LOCK_DIR'                  , '/var/lock/'       );
_gscnf( 'CALL_INIT_FROM_NET'        , '0.0.0.0/32'       ); # deny all
_gscnf( 'MONITOR_FROM_NET'          , '0.0.0.0/32'       ); # deny all
_gscnf( 'LVM_USER_6_DIGIT_INT'      , false              );
_gscnf( 'LVM_CALL_INIT_USERS_500000', false              );
_gscnf( 'LVM_FORWARD_REQ_EXT_NUM'   , true               );
_gscnf( 'CC_TIMEOUT'                , 60                 );
_gscnf( 'INTL_LANG'                 , 'de_DE'            );
_gscnf( 'INTL_USE_GETTEXT'          , false              );
_gscnf( 'INTL_ASTERISK_LANG'        , 'de'               );

_gscnf( 'USERCOMMENT_OFFTIME'       , 'Feierabend'       );
_gscnf( 'EMAIL_PATTERN_VALID'       , '/^[a-z0-9\-._]+@[a-z0-9\-._]{2,80}\.[a-z]{2,10}$/i'              );

# to communicate with HylaFax ftp_raw() is required, which is not
# available in PHP < 5
if ((float)PHP_VERSION < 5.0)
	$FAX_ENABLED = false;
_gscnf( 'FAX_ENABLED'               , false              );
_gscnf( 'FAX_PREFIX',    gs_get_conf('GS_CANONIZE_NATL_PREFIX' , '0'  ).
                         gs_get_conf('GS_CANONIZE_AREA_CODE'   , '999').
                         gs_get_conf('GS_CANONIZE_LOCAL_BRANCH', '999'));
_gscnf( 'FAX_TSI_PREFIX'            , ''                 );
_gscnf( 'FAX_TSI'                   , ''                 );
# (TSI = Transmitting Subscriber Identification)
_gscnf( 'FAX_HYLAFAX_HOST'          , '127.0.0.1'        );
_gscnf( 'FAX_HYLAFAX_PORT'          , 4559               );
$FAX_HYLAFAX_ADMIN =
	preg_replace('/[^a-z0-9\-_.]/i', '',
	@$FAX_HYLAFAX_ADMIN );
_gscnf( 'FAX_HYLAFAX_ADMIN'         , 'admin'            );
$FAX_HYLAFAX_PASS  =
	preg_replace('/[^a-z0-9\-_.]/i', '',
	@$FAX_HYLAFAX_PASS  );
_gscnf( 'FAX_HYLAFAX_PASS'          , 'sEcr3T'           );

_gscnf( 'LOG_FILE'                  , '/var/log/gemeinschaft/gs.log' );
_gscnf( 'LOG_GMT'                   , true               );
$LOG_LEVEL = strToUpper(@$LOG_LEVEL);
if (! in_array($LOG_LEVEL, array('FATAL', 'WARNING', 'NOTICE', 'DEBUG'), true))
	$LOG_LEVEL = 'NOTICE';
define( 'GS_LOG_LEVEL', constant('GS_LOG_'.$LOG_LEVEL) );



# include gettext functions here because conf.php is included
# in every file
include_once( GS_DIR .'inc/gettext.php' );

if (function_exists('date_default_timezone_set')) {
	# PHP >= 5.1.0
	# needed by date() and other functions
	@date_default_timezone_set( @date_default_timezone_get() );
}

?>