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


/***********************************************************
*    MISC
***********************************************************/

define( 'GS_CALL_INIT_FROM_NET', '192.168.1.0/24' );
# a comma (,) separated list of IP addresses or
# <IP address>/<netmask> pairs from where calls can be inited
# with HTTP GET
# e.g.: '127.0.0.1, 192.168.1.130/255.255.255.0, 192.168.1.130/24'
# allow all: '0.0.0.0/0', allow none: '0.0.0.0/32'

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


/***********************************************************
*    LOGGING
***********************************************************/

# log levels:
define( 'GS_LOG_FATAL'  , 1<<1 );  # SYSTEM CONSTANTS. DO NOT CHANGE!
define( 'GS_LOG_WARNING', 1<<2 );  #  "
define( 'GS_LOG_NOTICE' , 1<<3 );  #  "
define( 'GS_LOG_DEBUG'  , 1<<4 );  #  "

define( 'GS_LOG_LEVEL'  , GS_LOG_DEBUG );  # your log level
define( 'GS_LOG_FILE'   , '/var/log/gemeinschaft/gs.log' );
define( 'GS_LOG_GMT'    , true );  # use GMT or local time



# include gettext functions here because conf.php is included
# in every file
include_once( GS_DIR .'inc/gettext.php' );

?>