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


/***********************************************************
*    OUR ROOT DIRECTORY
***********************************************************/

define( 'GS_DIR', realPath(dirName(__FILE__).'/../').'/' );  # DO NOT CHANGE!


/***********************************************************
*    DB (MASTER)
***********************************************************/

define( 'GS_DB_MASTER_HOST' , '192.168.1.130' );
define( 'GS_DB_MASTER_USER' , 'root' );
define( 'GS_DB_MASTER_PWD'  , '' );
define( 'GS_DB_MASTER_DB'   , 'asterisk' );
define( 'GS_DB_MASTER_TRANSACTIONS', true );
# whether to use transactions


/***********************************************************
*    DB (SLAVE)
***********************************************************/

define( 'GS_DB_SLAVE_HOST'  , '127.0.0.1' );
define( 'GS_DB_SLAVE_USER'  , 'root' );
define( 'GS_DB_SLAVE_PWD'   , '' );
define( 'GS_DB_SLAVE_DB'    , 'asterisk' );


/***********************************************************
*    LDAP
***********************************************************/

define( 'GS_LDAP_HOST'      , '192.168.1.130' );
define( 'GS_LDAP_SSL'       , false );
define( 'GS_LDAP_PORT'      , 0 );  # 0 for default (389 / 636)
define( 'GS_LDAP_BINDDN'    , 'cn=root,dc=example,dc=com' );  # i.e. the rootdn
define( 'GS_LDAP_PWD'       , 'secret' );
define( 'GS_LDAP_PROTOCOL'  , 3 );  # protocol version. 2|3

define( 'GS_LDAP_SEARCHBASE', 'ou=People,dc=example,dc=com' );
# e.g. "ou=People,dc=example,dc=com" | "ou=users,o=Company,c=de"

define( 'GS_LDAP_PROP_USER' , 'uid' );  # e.g. "uid"
# the user name in the LDAP GS_LDAP_PROP_USER field must match the
# user name/code you use in Gemeinschaft
# e.g. "uid" | "employeenumber"

define( 'GS_LDAP_PROP_UID'  , 'uid' );
# GS_LDAP_PROP_UID is the "primary key" in the "dn", normally
# "uid" for users (or "cn").

# for the phonebook:
define( 'GS_LDAP_PROP_FIRSTNAME', 'givenname' );  # e.g. "givenname"
define( 'GS_LDAP_PROP_LASTNAME' , 'sn' );  # e.g. "sn"
define( 'GS_LDAP_PROP_PHONE'    , 'telephonenumber' );  # e.g. "telephonenumber"


/***********************************************************
*    WEB INTERFACE
***********************************************************/

//define( 'GS_GUI_SESSIONS', false );  # use sessions?
# not used. always start session but fallback gracefully

define( 'GS_GUI_AUTH_METHOD', 'gemeinschaft' );  # "gemeinschaft" | "webseal"

define( 'GS_GUI_NUM_RESULTS', 12 );

define( 'GS_GUI_SUDO_ADMINS', '47110002, 47110003' );
# comma separated list of admin users who can manage *all* accounts

define( 'GS_GUI_SUDO_EXTENDED', false );
# whether to include htdocs/gui/inc/permissions.php and consult
# gui_sudo_allowed() to find out if a user can act as a certain
# other user (you may need to adjust this function!)

define( 'GS_GUI_QUEUE_SHOW_NUM_CALLS', false );
# show number of completed calls for each member in Monitor->Queues

define( 'GS_GUI_MON_NOQUEUEBLUE', true );
# used in Monitor->Peers. if true idle users who are not member
# of a queue get a blue led instead of a green one


/***********************************************************
*    EXTERNAL NUMBERS BACKEND
***********************************************************/

define( 'GS_EXTERNAL_NUMBERS_BACKEND', 'db' );  # "db"|"ldap"
define( 'GS_EXTERNAL_NUMBERS_LDAP_PROP', 'telephoneNumber' );
# e.g. "externaltelephone"


/***********************************************************
*    NOBODY ACCOUNTS
***********************************************************/

define( 'GS_NOBODY_EXTEN_PATTERN', '95xxxx' );
# The only wildcard is "x" which can only occur at the end of
# the pattern - once or multiple times. Take care that there
# is enough room for all of your phones! E.g. "95xxxx" can
# hold a maximum of 9999 phones.
# Call scripts/gs_nobodies_change if you ever change this!

define( 'GS_NOBODY_CID_NAME', 'Namenlos-' );
# The CallerID name prefix.
# Call scripts/gs_nobodies_change if you ever change this!


/***********************************************************
*    PROVISIONING
***********************************************************/

define( 'GS_PROV_HOST'      , '192.168.1.130' );
define( 'GS_PROV_PORT'      , 82 );
define( 'GS_PROV_SCHEME'    , 'http' );  # without "://"
define( 'GS_PROV_PATH'      , '/' );  # with starting and trailing "/"
# URL is build like this:
# <GS_PROV_SCHEME>://<GS_PROV_HOST>:<GS_PROV_PORT><GS_PROV_PATH>snom/dial-log.php

define( 'GS_PROV_AUTO_ADD_PHONE', true );
# if a phone with a MAC address which is not in our database
# asks for provisioning, should the phone automatically be
# added and a user account be created?
define( 'GS_PROV_AUTO_ADD_PHONE_HOST', 'first' );
# which of the hosts should the new phone be assigned to?
# can be "first", "last" or "random"

define( 'GS_PROV_SNOM_HTTP_USER', 'gs' );
define( 'GS_PROV_SNOM_HTTP_PASS', 'gEheiM' );
# changing these values will likely cause automatic rebooting to fail

define( 'GS_PROV_DIAL_LOG_LIFE', 7*24*3600 );
# for how long to keep the dial log entries (dialed, missed,
# in) - CDR will be stored forever

define( 'GS_PROV_SNOM_PB_NUM_RESULTS', 15 );
# number of results in phonebook search on Snom phone


/***********************************************************
*    CANONICAL PHONE NUMBERS (FQTN)
***********************************************************/

define( 'GS_CANONIZE_OUTBOUND'    , true  ); # canonize numbers before matching
                                             # against routes? also determines
                                             # whether we dial in national form or
                                             # as is
define( 'GS_CANONIZE_INTL_PREFIX' , '00'  ); # international prefix. Do not use "+",
                                             # we know the canonical format is "+".
define( 'GS_CANONIZE_COUNTRY_CODE', '49'  ); # country code (Landesvorwahl) without
                                             # prefix
define( 'GS_CANONIZE_NATL_PREFIX' , '0'   ); # National prefix
                                             # (Verkehrsausscheidungsziffer)
define( 'GS_CANONIZE_NATL_PREFIX_INTL', false );
                                             # Whether the area code needs the
                                             # national prefix even when dialing
                                             # in international format (in Italy)
define( 'GS_CANONIZE_AREA_CODE'   , '251' ); # Area code (Ortsvorwahl) without
                                             # national prefix
define( 'GS_CANONIZE_LOCAL_BRANCH', '702' ); # Private branch (private Kopfnummer).
                                             # If all you have is a single phone
                                             # number put your local number in here,
                                             # i.e. the rest after the area code
define( 'GS_CANONIZE_SPECIAL', '/^1(?:1[0-9]{1,5}|9222)/' );
                                             # numbers matching this pattern will
                                             # not be prefixed with anything
define( 'GS_CANONIZE_CBC_PREFIX'  , '010' ); # Call-by-Call prefix (Germany: 010)


/***********************************************************
*    DIALPLAN SETTINGS
***********************************************************/

# emergency numbers - no checking for permissions etc.:
define( 'GS_DP_EMERGENCY_POLICE'     , '110,0110' ); # 911,999,767,...
define( 'GS_DP_EMERGENCY_POLICE_MAP' , '110' );
define( 'GS_DP_EMERGENCY_FIRE'       , '112,0112' );
define( 'GS_DP_EMERGENCY_FIRE_MAP'   , '112' );
# if you do not dial to the PSTN directly but via some kind
# of gateway you might need to prefix the ..._MAP numbers
# with 0

define( 'GS_DP_DIALTIMEOUT_IN', 45 );
# default timeout when dialing to internal users


/***********************************************************
*    MISC
***********************************************************/

define( 'GS_CALL_INIT_FROM_NET', '192.168.1.0/24' );
# a comma (,) separated list of IP addresses or
# <IP address>/<netmask> pairs from where calls can be inited
# with HTTP GET
# e.g.: '127.0.0.1, 192.168.1.130/255.255.255.0, 192.168.1.130/24'
# allow all: '0.0.0.0/0', allow none: '0.0.0.0/32'

define( 'GS_MONITOR_FROM_NET', '192.168.1.0/24' );
# allow access to the extension monitor panel from these network
# ranges

define( 'GS_LVM_USER_6_DIGIT_INT', false );
# compare user names as 6 digit integers (padded with zeros (0) on
# the left. currently used by htdocs/prov/call-init.php and
# inc/gs-fns/gs_user_external_number*. should normally be off.

define( 'GS_LVM_CALL_INIT_USERS_500000', false );
# should normally be off.

define( 'GS_LVM_FORWARD_REQ_EXT_NUM', true );
# if true call forwards can be set to numbers in a user's list of
# external numbers only - apart from numbers not starting in "0"
# which are always allowed

define( 'GS_CC_TIMEOUT',  60 );  # timeout of programmed call completions
                                 # in minutes

define( 'GS_INTL_LANG', 'de_DE' );  # "de_DE" or "en_US"
define( 'GS_INTL_USE_GETTEXT', false );
# whether to use gettext files or php arrays. gettext seems to have
# problems on some systems

define( 'GS_USERCOMMENT_OFFTIME', 'Feierabend' );  # i.e. "off-time"

define( 'GS_EMAIL_PATTERN_VALID', '/^[a-z0-9\-._]+@[a-z0-9\-._]{2,80}\.[a-z]{2,10}$/i' );


/***********************************************************
*    PHONEBOOK
***********************************************************/

define( 'GS_PB_IMPORTED_USE'  , false );
define( 'GS_PB_IMPORTED_ORDER', 2 );  # 1|2|3
define( 'GS_PB_IMPORTED_TITLE', "Firma (aus LDAP)"  );  # short! no HTML entities!

define( 'GS_PB_INTERNAL_TITLE', "Intern"            );  #  "
define( 'GS_PB_PRIVATE_TITLE' , "Pers\xC3\xB6nlich" );  #  "


/***********************************************************
*    LOGGING
***********************************************************/

# log levels:
define( 'GS_LOG_FATAL'  , 1<<1 );  # SYSTEM CONSTANTS. DO NOT CHANGE!
define( 'GS_LOG_WARNING', 1<<2 );  #  "
define( 'GS_LOG_NOTICE' , 1<<3 );  #  "
define( 'GS_LOG_DEBUG'  , 1<<4 );  #  "

define( 'GS_LOG_LEVEL'  , GS_LOG_NOTICE );  # your log level
define( 'GS_LOG_FILE'   , '/var/log/gemeinschaft/gs.log' );
define( 'GS_LOG_GMT'    , true );  # use GMT or local time


/***********************************************************
***********************************************************/


function gs_get_conf( $key, $default=null )
{
	return @defined($key) ? constant($key) : $default;
}


# include gettext functions here because conf.php is included
# in every file
include_once( GS_DIR .'inc/gettext.php' );

if (function_exists('date_default_timezone_set')) {
	# PHP >= 5.1.0
	# needed by date()
	@date_default_timezone_set( @date_default_timezone_get() );
}

?>