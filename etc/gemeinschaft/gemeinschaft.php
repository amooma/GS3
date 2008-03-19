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

////////////////////////////////////////////////////////////
///     MAKE SURE THAT THIS FILE IS VALID PHP CODE!      ///
////////////////////////////////////////////////////////////



/***********************************************************
*    TYPE OF INSTALLATION
***********************************************************/

$INSTALLATION_TYPE      = 'single';
  # the type of the current installation.
  # "gpbx"    : GPBX
  # "single"  : single-server installation (Gemeinschaft,
  #             Asterisk and MySQL on one server)
  # "cluster" : cluster setup
  # This is just a hint to the GUI and various other scripts.
  # Changing it breaks things but does not change the type of
  # your installation!



/***********************************************************
*    DB
***********************************************************/

//----------------------[  Master  ]----------------------//

$DB_MASTER_HOST         = '192.168.1.130';
$DB_MASTER_USER         = 'root';
$DB_MASTER_PWD          = '';
$DB_MASTER_DB           = 'asterisk';
$DB_MASTER_TRANSACTIONS = true;     # use transactions?


//----------------------[  Slave  ]-----------------------//

$DB_SLAVE_HOST          = '127.0.0.1';
$DB_SLAVE_USER          = 'root';
$DB_SLAVE_PWD           = '';
$DB_SLAVE_DB            = 'asterisk';



/***********************************************************
*    LDAP
***********************************************************/

$LDAP_HOST              = '192.168.1.130';
$LDAP_SSL               = false;
$LDAP_PORT              = 0;        # 0 for default (389 / 636)
$LDAP_BINDDN            = 'cn=root,dc=example,dc=com';  # i.e. the rootdn
$LDAP_PWD               = 'secret';
$LDAP_PROTOCOL          = 3;        # protocol version. 2|3

$LDAP_SEARCHBASE = 'ou=People,dc=example,dc=com';
  # e.g. "ou=People,dc=example,dc=com" | "ou=users,o=Company,c=de"

$LDAP_PROP_USER         = 'uid';    # e.g. "uid"
  # the user name in the LDAP attribute LDAP_PROP_USER must match
  # the user name/code you use in Gemeinschaft
  # e.g. "uid" | "employeenumber"

$LDAP_PROP_UID          = 'uid';
  # LDAP_PROP_UID is the "primary key" in the "dn", normally
  # "uid" for users (or "cn").

  # for the phonebook:
$LDAP_PROP_FIRSTNAME    = 'givenname';       # e.g. "givenname"
$LDAP_PROP_LASTNAME     = 'sn';              # e.g. "sn"
$LDAP_PROP_PHONE        = 'telephonenumber'; # e.g. "telephonenumber"



/***********************************************************
*    WEB INTERFACE
***********************************************************/

//$GUI_SESSIONS           = false;    # use sessions?
// not used. always start session but fallback gracefully

$GUI_AUTH_METHOD            = 'gemeinschaft';
  # "gemeinschaft": Authenticate users against our internal database.
  # "webseal"     : Trust the non-standard "IV-User" HTTP header.
  #                 Make sure every access goes through WebSeal
  #                 and nobody can access our GUI directly!

$GUI_NUM_RESULTS            = 12;

//$GUI_SUDO_ADMINS            = '2000, 2001, 2002, 2003, peter';
$GUI_SUDO_ADMINS            = '';
  # comma separated list of admin users who can manage *all* accounts

$GUI_SUDO_EXTENDED          = false;
  # whether to include htdocs/gui/inc/permissions.php and consult
  # gui_sudo_allowed() to find out if a user can act as a certain
  # other user (you may need to adjust this function!).
  # also see GUI_PERMISSIONS_METHOD

$GUI_PERMISSIONS_METHOD     = 'gemeinschaft';
  # "gemeinschaft" or "lvm". ugly hack, see GUI_SUDO_EXTENDED,
  # GUI_MON_PEERS_ENABLED and htdocs/gui/inc/permissions.php

$GUI_QUEUE_SHOW_NUM_CALLS   = false;
  # show number of completed calls for each member in Monitor->Queues

$GUI_QUEUE_INFO_FROM_DB     = false;
  # get queue statistics for Monitor->Queues from database (table
  # queue_log)? otherwise the stats are taken from the manager
  # interface. does not make sense if you don't set up a cron job
  # for /opt/gemeinschaft/sbin/gs-queuelog-to-db (every minute)

$GUI_MON_NOQUEUEBLUE        = true;
  # used in Monitor->Peers. if true idle users who are not member
  # of a queue get a blue led instead of a green one

$GUI_MON_PEERS_ENABLED      = false;
  # needs LDAP with Kostenstelle or a similar mechanism, see
  # gui_monitor_which_peers() in htdocs/gui/inc/permissions.php .
  # also see GUI_PERMISSIONS_METHOD

#$GUI_SHUTDOWN_ENABLED       = false;
  # enable shutdown via web interface?
  # default: true for INSTALLATION_TYPE=='gpbx', false
  # otherwise

$GUI_LANGS = 'de_DE:de_DE:de-DE:Deutsch, en_US:en_US:en-US:English';

$GUI_ADDITIONAL_STYLESHEET = 'gemeinschaft.css';



/***********************************************************
*    EXTERNAL NUMBERS BACKEND
***********************************************************/

$EXTERNAL_NUMBERS_BACKEND   = 'db'; # "db"|"ldap"
$EXTERNAL_NUMBERS_LDAP_PROP = 'telephoneNumber';
  # e.g. "externaltelephone"



/***********************************************************
*    NOBODY ACCOUNTS
***********************************************************/

$NOBODY_EXTEN_PATTERN   = '95xxxx';
  # The only wildcard is "x" which can only occur at the end of
  # the pattern - once or multiple times. Take care that there
  # is enough room for all of your phones! E.g. "95xxxx" can
  # hold a maximum of 9999 phones.
  # Call scripts/gs_nobodies_change if you ever change this!

$NOBODY_CID_NAME        = 'Namenlos-';
  # The CallerID name prefix.
  # Call scripts/gs_nobodies_change if you ever change this!



/***********************************************************
*    PROVISIONING
***********************************************************/

$PROV_HOST                  = '192.168.1.130';
//$PROV_PORT                  = 82;
$PROV_PORT                  = 80;
$PROV_SCHEME                = 'http';  # without "://"
//$PROV_PATH                  = '/';
$PROV_PATH                  = '/gemeinschaft/prov/';
  # with starting and trailing "/"
  # URL is build like this:
  # <PROV_SCHEME>://<PROV_HOST>:<PROV_PORT><PROV_PATH>snom/dial-log.php
$PROV_AUTO_ADD_PHONE        = true;
  # if a phone with a MAC address which is not in our database
  # asks for provisioning, should the phone automatically be
  # added and a user account be created?
$PROV_AUTO_ADD_PHONE_HOST   = 'first';
  # which of the hosts should the new phone be assigned to?
  # can be "first", "last" or "random"
$PROV_DIAL_LOG_LIFE         = 14*24*3600;
  # for how long to keep the dial log entries (dialed, missed,
  # in; CDR will be stored forever)



/***********************************************************
*    HANDSETS
***********************************************************/

//---------------------[  Snom 3xx  ]---------------------//

$SNOM_PROV_ENABLED          = true;  # do provisioning for Snom?
                                     # show keyset for Snom in the GUI?

$SNOM_PROV_HTTP_USER        = 'gs';
$SNOM_PROV_HTTP_PASS        = 'gEheiM23y89sdo23';
  # changing these values will likely cause automatic rebooting to fail

$SNOM_PROV_PB_NUM_RESULTS   = 15;
  # number of results in phonebook search on Snom phone

  # Before you do firmware updates be sure to read
  # doc/other/snom-and-cisco-switches.txt
  # Set $LOG_LEVEL to "NOTICE" or even "DEBUG" and
  # tail -f /var/log/gemeinschaft/gs.log
  # Test the update mechanism with 1 or 2 phones. This is especially
  # important with PoE (Power over Ethernet) switches.
$SNOM_PROV_FW_UPDATE        = false;  # allow firmware updates?
$SNOM_PROV_FW_BETA          = false;  # allow beta versions?
$SNOM_PROV_FW_6TO7          = false;  # allow upgrade from v.6 to 7?
  # upgrading vom 6 to 7 might require SNOM_PROV_FW_BETA


//----------------------[  Aastra  ]----------------------//

$AASTRA_PROV_ENABLED        = true;  # do provisioning for Aastra?
                                     # show keyset for Aastra in the GUI?
$AASTRA_PROV_PB_NUM_RESULTS = 10;
  # number of results in phonebook search on Aastra phone


//-----------------[  Siemens OpenStage  ]----------------//

//...



/***********************************************************
*    CANONICAL PHONE NUMBERS (FQTN)
***********************************************************/

$CANONIZE_OUTBOUND      = true;     # canonize numbers before matching
                                    # against routes? also determines
                                    # whether we dial in national form or
                                    # as is
$CANONIZE_INTL_PREFIX   = '00';     # international prefix. Do not use "+"
                                    # (we know the canonical format is "+"!)
                                    # in Germany: 00, USA: 011
$CANONIZE_COUNTRY_CODE  = '49';     # country code (Landesvorwahl) without
                                    # prefix
                                    # Germany: 49, USA: 1
$CANONIZE_NATL_PREFIX   = '0';      # National prefix
                                    # (Verkehrsausscheidungsziffer)
                                    # in Germany: 0
$CANONIZE_NATL_PREFIX_INTL = false; # Whether the area code needs the
                                    # national prefix even when dialing
                                    # in international format (in Italy)
$CANONIZE_AREA_CODE     = '251';    # Area code (Ortsvorwahl) without
                                    # national prefix
$CANONIZE_LOCAL_BRANCH  = '702';    # Private branch (private Kopfnummer).
                                    # If all you have is a single phone
                                    # number put your local number in here,
                                    # i.e. the rest after the area code
$CANONIZE_SPECIAL       = '/^1(?:1[0-9]{1,5}|9222)/';
                                    # numbers matching this pattern will
                                    # not be prefixed with anything
$CANONIZE_CBC_PREFIX    = '010';    # Call-by-Call prefix (Germany: 010)



/***********************************************************
*    DIALPLAN SETTINGS
***********************************************************/

  # emergency numbers - no checking for permissions etc.:
$DP_EMERGENCY_POLICE        = '110,0110';  # 110,0110,911,999,767,...
$DP_EMERGENCY_POLICE_MAP    = '110';
$DP_EMERGENCY_FIRE          = '112,0112';
$DP_EMERGENCY_FIRE_MAP      = '112';
  # if you do not dial to the PSTN directly but via some kind
  # of gateway you might need to prefix the ..._MAP numbers
  # with 0

$DP_DIALTIMEOUT_IN          = 45;
  # default timeout when dialing to internal users

$DP_PRV_CALL_PREFIX         = '*7*';  # e.g. "*7*", "96", ...
                                      # must not collide with any other
                                      # extension!
  //FIXME - fix e.ael to honor this setting



/***********************************************************
*    MISC
***********************************************************/

$LOCK_DIR               = '/var/lock/';
  # where to write Gemeinschaft lock files. including trailing "/"!
  # e.g. "/var/lock/" or "/tmp/". the directory must exists.
  # does not control where apache, mysql, zaptel etc. put their
  # lock files

$CALL_INIT_FROM_NET         = '192.168.1.0/24';
  # a comma (,) separated list of IP addresses or
  # <IP address>/<netmask> pairs from where calls can be inited
  # with HTTP GET
  # CIDR or dotted decimal notation
  # e.g.: '127.0.0.1, 192.168.1.130/255.255.255.0, 192.168.1.130/24'
  # allow all: '0.0.0.0/0', allow none: '0.0.0.0/32'

$MONITOR_FROM_NET           = '192.168.1.0/24';
  # allow access to the extension monitor panel from these network
  # ranges
  # CIDR or dotted decimal notation

$LVM_USER_6_DIGIT_INT       = false;
  # compare user names as 6 digit integers (padded with zeros (0)
  # on the left. currently used by htdocs/prov/call-init.php and
  # inc/gs-fns/gs_user_external_number*. should normally be off.

$LVM_CALL_INIT_USERS_500000 = false;
  # should normally be off

$LVM_FORWARD_REQ_EXT_NUM    = true;
  # if true call forwards can be set to numbers in a user's list of
  # external numbers only - apart from numbers not starting in "0"
  # which are always allowed

$CC_TIMEOUT             =  60;      # timeout of programmed call
                                    # completions in minutes

$INTL_LANG              = 'de_DE';  # "de_DE" or "en_US"
$INTL_USE_GETTEXT       = false;
  # whether to use gettext files or php arrays. gettext seems to have
  # problems on some systems

$INTL_ASTERISK_LANG     = 'de';
  # "de". if you use anything else make sure to have the appropriate
  # sound files installed - especially the prompts in the "gemeinschaft"
  # subdirectory which are available in German only for now

$USERCOMMENT_OFFTIME    = 'Feierabend';  # e.g. "off-time"

$EMAIL_PATTERN_VALID    = '/^[a-z0-9\-._]+@[a-z0-9\-._]{2,80}\.[a-z]{2,10}$/i';

$EMAIL_DELIVERY         = 'sendmail';
  # how to deliver e-mails to the users ("forgot password" function etc.)
  # "sendmail"    :  use PHP's mail() - needs *a* local sendmail (e.g.
  #                  sendmail / postfix / exim)
  # "direct-smtp" :  connect to the MX servers of the recipient directly
  #                  via SMTP



/***********************************************************
*    PHONEBOOK
***********************************************************/

$PB_IMPORTED_ENABLED    = false;
$PB_IMPORTED_ORDER      = 2;                    # 1|2|3
$PB_IMPORTED_TITLE      = "Firma (aus LDAP)";   # short! no HTML entities!

$PB_INTERNAL_TITLE      = "Intern";             #  "
$PB_PRIVATE_TITLE       = "Pers\xC3\xB6nlich";  #  "


/***********************************************************
*    FAX
***********************************************************/

$FAX_ENABLED            = false;
$FAX_PREFIX             = '6';           # not used yet  //FIXME
$FAX_TSI_PREFIX         = @$CANONIZE_NATL_PREFIX.
                          @$CANONIZE_AREA_CODE.
                          @$CANONIZE_LOCAL_BRANCH;
$FAX_TSI                = @$FAX_TSI_PREFIX.'0,'   .
                          @$FAX_TSI_PREFIX.'100,' .
                          @$FAX_TSI_PREFIX.'99'   ;
  # (TSI = Transmitting Subscriber Identification)
$FAX_HYLAFAX_HOST       = '127.0.0.1';
$FAX_HYLAFAX_PORT       = 4559;          # HylaFax port (FTP-like protocol)
$FAX_HYLAFAX_ADMIN      = 'webmgr';      # admin user of your HylaFax
                                         # (see HylaFax's hosts.hfaxd)
$FAX_HYLAFAX_PASS       = 'a9bl2ue7';


/***********************************************************
*    LOGGING
***********************************************************/

$LOG_LEVEL   = 'NOTICE';            # "FATAL"|"WARNING"|"NOTICE"|"DEBUG"
$LOG_FILE    = '/var/log/gemeinschaft/gs.log';
$LOG_GMT     = true;                # use GMT or local time



/***********************************************************
*    INCLUDES
***********************************************************/

if (@$INSTALLATION_TYPE === 'gpbx') {
	$inc_file = '/etc/gemeinschaft/gemeinschaft-gpbx.php';
	if (file_exists($inc_file)) {
		include_once($inc_file);
	}
}

// NO NEWLINES AFTER THE CLOSING TAG!
?>