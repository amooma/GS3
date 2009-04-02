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
*        MAKE SURE THAT THIS FILE IS VALID PHP CODE!
*                        Please run
*           /opt/gemeinschaft/sbin/gs-configtest
*                 after making any changes.
***********************************************************/



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
$DB_MASTER_USER         = 'gemeinschaft';
$DB_MASTER_PWD          = '';
$DB_MASTER_DB           = 'asterisk';

//----------------------[  Slave  ]-----------------------//

$DB_SLAVE_HOST          = '127.0.0.1';
$DB_SLAVE_USER          = 'gemeinschaft';
$DB_SLAVE_PWD           = '';
$DB_SLAVE_DB            = 'asterisk';

//--------------------[  CDR Master  ]--------------------//

//$DB_CDR_MASTER_HOST     = '192.168.1.130';
//$DB_CDR_MASTER_USER     = 'cdr';
//$DB_CDR_MASTER_PWD      = '';
//$DB_CDR_MASTER_DB       = 'cdr';
  # if DB_CDR_MASTER_HOST is not set the normal master DB will
  # be used

//-----------------------[  Misc  ]-----------------------//

$DB_MASTER_TRANSACTIONS = true;     # use transactions?
$DB_SIP_REG_UPDATE      = false;
  # Send SIP registry updates to slave database? Attention:
  # Asterisk will update the fields on the *slave* database
  # connection, so if you use this make sure the slave is
  # in fact not a slave but a node of a MySQL cluster! It's
  # safe to use this with INSTALLATION_TYPE = "single".



/***********************************************************
*    LDAP
***********************************************************/

//$LDAP_HOST              = '192.168.1.130';
//$LDAP_SSL               = false;
//$LDAP_PORT              = 0;        # 0 for default (389 / 636)
//$LDAP_BINDDN            = 'cn=root,dc=example,dc=com';  # i.e. the rootdn
//$LDAP_PWD               = 'secret';
//$LDAP_PROTOCOL          = 3;        # protocol version. 2|3

//$LDAP_SEARCHBASE = 'ou=People,dc=example,dc=com';
  # e.g. "ou=People,dc=example,dc=com" | "ou=users,o=Company,c=de"

//$LDAP_PROP_USER         = 'uid';    # e.g. "uid"
  # the user name in the LDAP attribute LDAP_PROP_USER must match
  # the user name/code you use in Gemeinschaft
  # e.g. "uid" | "employeenumber"

//$LDAP_PROP_UID          = 'uid';
  # LDAP_PROP_UID is the "primary key" in the "dn", normally
  # "uid" for users (or "cn").

  # for the phonebook:
//$LDAP_PROP_FIRSTNAME    = 'givenname';       # e.g. "givenname"
//$LDAP_PROP_LASTNAME     = 'sn';              # e.g. "sn"
//$LDAP_PROP_PHONE        = 'telephonenumber'; # e.g. "telephonenumber"
//$LDAP_PROP_EMAIL        = 'mail';            # e.g. "mail"



/***********************************************************
*    WEB INTERFACE
***********************************************************/

//$GUI_SESSIONS           = false;    # use sessions?
  # not used. always start session but fallback gracefully

//$GUI_AUTH_METHOD            = 'gemeinschaft';
  # "gemeinschaft": Authenticate users against our internal database.
  # "webseal"     : Trust the non-standard "IV-User" HTTP header.
  #                 Make sure every access goes through WebSeal
  #                 and nobody can access our GUI directly!

$GUI_NUM_RESULTS            = 12;

//$GUI_SUDO_ADMINS            = '2000, 2001, 2002, 2003, peter';
$GUI_SUDO_ADMINS            = '';
  # comma separated list of admin users who can manage *all* accounts

//$GUI_SUDO_EXTENDED          = false;
  # Whether to include htdocs/gui/inc/permissions.php and consult
  # gui_sudo_allowed() to find out if a user can act as a certain
  # other user. (You may need to adjust this function!). For the
  # method used see GUI_PERMISSIONS_METHOD.

//$GUI_PERMISSIONS_METHOD     = 'gemeinschaft';
  # determines the method used to find out if a user can act as a
  # certain other user. "gemeinschaft" or "lvm". ugly solution.
  # see GUI_SUDO_EXTENDED, GUI_MON_PEERS_ENABLED and
  # htdocs/gui/inc/permissions.php. deprecated.

//$GUI_USER_MAP_METHOD        = '';
  # determines the method used to map legacy usernames to usernames
  # in Gemeinschaft. "" or "lvm". something like the "lvm" method
  # (see gs_legacy_user_map() in htdocs/gui/inc/session.php) can
  # be handy if GUI_AUTH_METHOD is "webseal".

$GUI_QUEUE_SHOW_NUM_CALLS   = false;
  # show number of completed calls for each member in Monitor->Queues

$GUI_QUEUE_INFO_FROM_DB     = true;
  # get queue statistics for Monitor->Queues from database (table
  # queue_log)? otherwise the stats are taken from the manager
  # interface. does not make sense if you don't set up a cron job
  # for /opt/gemeinschaft/sbin/gs-queuelog-to-db (every minute)

$GUI_MON_NOQUEUEBLUE        = true;
  # used in Monitor->Peers. if true idle users who are not member
  # of a queue get a blue led instead of a green one

//$GUI_MON_PEERS_ENABLED      = false;
  # Whether to enable the peers monitor. The visible peers for each
  # user depend on the GUI_PERMISSIONS_METHOD setting.
  # For GUI_PERMISSIONS_METHOD=="lvm" an LDAP with Kostenstelle
  # is required, see gui_monitor_which_peers() in
  # htdocs/gui/inc/permissions.php

//$GUI_SHUTDOWN_ENABLED       = false;
  # enable shutdown via web interface?
  # default: true for INSTALLATION_TYPE=='gpbx', false
  # otherwise

//$GUI_LANGS = 'de_DE:de_DE:de-DE:Deutsch, en_US:en_US:en-US:English';

//$GUI_ADDITIONAL_STYLESHEET  = 'gemeinschaft.css';



/***********************************************************
*    EXTERNAL NUMBERS BACKEND
***********************************************************/

$EXTERNAL_NUMBERS_BACKEND   = 'db'; # "db"|"ldap"
//$EXTERNAL_NUMBERS_LDAP_PROP = 'telephoneNumber';
  # e.g. "externaltelephone"



/***********************************************************
*    NOBODY ACCOUNTS
***********************************************************/

$NOBODY_EXTEN_PATTERN   = '95xxxx';
  # The only wildcard is "x" which can only occur at the end of
  # the pattern - once or multiple times. Take care that there
  # is enough room for all of your phones! E.g. "95xxxx" can
  # hold a maximum of 9999 phones.
  # It it strongly recommended not to change this value!
  # Call scripts/gs_nobodies_change if you ever change this!

$NOBODY_CID_NAME        = 'Namenlos-';
  # The CallerID name prefix.
  # Call scripts/gs_nobodies_change if you ever change this!



/***********************************************************
*    PROVISIONING
***********************************************************/

$PROV_HOST                  = '192.168.1.130';
//$PROV_PORT                  = 0;  # 0 for default port for $PROV_SCHEME
//$PROV_SCHEME                = 'http';  # without "://"
$PROV_PATH                  = '/gemeinschaft/prov/';
  # with starting and trailing "/"
  # URL is build like this:
  # <PROV_SCHEME>://<PROV_HOST>:<PROV_PORT><PROV_PATH>snom/dial-log.php
$PROV_AUTO_ADD_PHONE        = true;
  # if a phone with a MAC address which is not in our database
  # asks for provisioning, should the phone automatically be
  # added and a user account be created?
$PROV_AUTO_ADD_PHONE_HOST   = 'first';
  # which of the hosts should the new nobody account be assigned to?
  # can be "first", "last" or "random"
$PROV_DIAL_LOG_LIFE         = 14*24*3600;  # 14 days
  # for how long to keep the dial log entries (dialed, missed,
  # in; CDR will be stored forever)

//$PROV_PROXIES_TRUST         = '';
  # trust the $PROV_PROXIES_XFF_HEADER header of requests for
  # configuration from these proxies.
  # e.g. '192.168.1.2, 192.168.1.3'
  # the proxy must pass the phone's User-Agent header unmodified.
//$PROV_PROXIES_XFF_HEADER    = 'X-Forwarded-For';
  # 'X-Forwarded-For' is the de-facto standard but depending on the
  # proxy it might be 'X-Real-IP' or 'X-Client-IP'. case-insensitive.
$PROV_ALLOW_NET             = '192.168.1.0/24, 172.16.0.0/12, 10.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8';
  # comma (,) separated list of <IP address>/<netmask> pairs (CIDR
  # or dotted decimal notation) of phones which may ask for
  # configuration.
  # e.g.: '192.168.1.0/255.255.255.0, 192.168.0.0/16'
  # allow all: '0.0.0.0/0', allow none: '0.0.0.0/32'

//$PROV_LAN_NETS = '0.0.0.0/0';
  # CIDR notation, comma-separated
  # e.g. '10.0.0.0/8, 127.0.0.0/8, 169.254.0.0/16, 172.16.0.0/12, 192.168.0.0/16'
  # every phone which is not in one of these subnets is regarded
  # as being in the WAN. if a phone in the WAN needs to register
  # at a SIP server in the LAN the corresponding sip_proxy_from_wan
  # address from table host_params (if any) will be set as the
  # phone's outbound proxy (which is inbound from Gemeinschaft's
  # point of view)
  # default: '0.0.0.0/0' => all in LAN => no proxy

  # Phones shown in the GUI (comma-separated lists,
  # '*' for all supported models of a brand);
//$PROV_MODELS_ENABLED_SNOM        = '*';  # or '360,370'
//$PROV_MODELS_ENABLED_SIEMENS     = '*';  # or 'os20,os40,os60,os80'
//$PROV_MODELS_ENABLED_AASTRA      = '*';  # or '51i,53i,55i,57i'
//$PROV_MODELS_ENABLED_GRANDSTREAM = '*';  # or 'bt110,gxp2000,gxp2020', ...



/***********************************************************
*    HANDSETS
***********************************************************/

//---------------------[  Snom 3xx  ]---------------------//

$SNOM_PROV_ENABLED          = true;   # do provisioning for Snom?
                                      # show keyset for Snom in the GUI?

$SNOM_PROV_HTTP_USER        = '';     # e.g. "gs"
$SNOM_PROV_HTTP_PASS        = '';     # e.g. "gEheiM23y89sdo23"
  # to password protect the phone's web gui.
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
$SNOM_PROV_FW_6TO7          = false;  # allow upgrade from v.6 to 7?

//$SNOM_PROV_FW_DEFAULT_300   = '7.1.24';
//$SNOM_PROV_FW_DEFAULT_320   = '7.1.24';
//$SNOM_PROV_FW_DEFAULT_360   = '6.5.1';
//$SNOM_PROV_FW_DEFAULT_370   = '7.1.24';

//$SNOM_PROV_KEY_BLACKLIST    = '';
  # do not show these softkey functions in GUI,
  # comma separated list


//----------------------[  Snom M3  ]---------------------//

$SNOM_PROV_M3_ACCOUNTS      = 1;
  # set to 0 to disable Snom M3 provisioning

//$SNOM_PROV_M3_FW_DEFAULT_SNOM_M3 = 'x.x.x';  # not used (yet)


//----------------------[  Aastra  ]----------------------//

$AASTRA_PROV_ENABLED        = false;  # do provisioning for Aastra?
                                      # experimental!
                                      # show keyset for Aastra in the GUI?
$AASTRA_PROV_PB_NUM_RESULTS = 10;
  # number of results in phonebook search on Aastra phone

//$AASTRA_PROV_FW_DEFAULT_51I = 'x.x.x';  # not used (yet)
//$AASTRA_PROV_FW_DEFAULT_53I = 'x.x.x';  # not used (yet)
//$AASTRA_PROV_FW_DEFAULT_55I = 'x.x.x';  # not used (yet)
//$AASTRA_PROV_FW_DEFAULT_57I = 'x.x.x';  # not used (yet)

//$AASTRA_PROV_KEY_BLACKLIST  = '';
  # do not show these softkey functions in GUI,
  # comma separated list


//-----------------[  Siemens OpenStage  ]----------------//

$SIEMENS_PROV_ENABLED       = false;  # do provisioning for Siemens?
//...

//$SIEMENS_PROV_PREFER_HTTP   = true;
  # prefer HTTP(S) (to FTP) for file deployment (ringtones, ...).
  # default: true

//$SIEMENS_PROV_FW_DEFAULT_OS20 = '1.3.5.0';
//$SIEMENS_PROV_FW_DEFAULT_OS40 = '1.3.5.0';
//$SIEMENS_PROV_FW_DEFAULT_OS60 = '1.3.5.0';
//$SIEMENS_PROV_FW_DEFAULT_OS80 = '1.3.5.0';

//$SIEMENS_PROV_KEY_BLACKLIST = '';
  # do not show these softkey functions in GUI,
  # comma separated list (e.g. 'f11,f59,f10'), default: ''
  # can be used to disable DND for example.
  # 'f1'  selected dialing
  # 'f59' extension
  # 'f9'  ringer off
  # 'f10' hold
  # 'f11' alternate
  # 'f13' attended transfer
  # 'f12' blind transfer
  # 'f14' deflect
  # 'f18' shift
  # 'f24' headset
  # 'f25' do not disturb
  # 'f29' group pickup
  # 'f30' repertory dial
  # 'f31' line
  # 'f50' consultation


//---------------------[ Grandstream ]--------------------//

$GRANDSTREAM_PROV_ENABLED   = false;  # do provisioning for Grandstream?
  # Warning: The phonebook for Grandstream does not currently have
  # authentication!

$GRANDSTREAM_PROV_HTTP_PASS = '';     # e.g. "gEheiM23y89sdo23"
  # to password protect the phone's web gui.

//$GRANDSTREAM_PROV_NTP       = @$PROV_HOST;
//$GRANDSTREAM_PROV_NTP       = '192.168.1.130';
  # NTP Server. the stupid Grandstream needs it



/***********************************************************
*    CANONICAL PHONE NUMBERS (FQTN)
***********************************************************/

$CANONIZE_OUTBOUND      = false;    # canonize numbers before matching
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

$DP_SUBSYSTEM               = false;
  # are we a sub-system behind another PBX in the same private
  # branch? 

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

//$DP_PRV_CALL_PREFIX         = '*7*';
  # e.g. "*7*", "96", ...
  # must not collide with any other extension!
  //FIXME - fix e.ael to honor this setting

//$DP_FORWARD_REQ_EXT_NUM     = false;
  # if true call forwards can be set to numbers in a user's list of
  # external numbers only - apart from numbers not starting in "0"
  # which are always allowed
  # does not make much sense any more since users can edit their
  # external numbers

$DP_ALLOW_DIRECT_DIAL       = false;
  # allow direct dialing to an extension (overrides call forwards),
  # for boss/secretary functionality

//$DP_CONNID                  = false;
  # pass (/generate) a "connection ID" via the custom X-Org-ConnID
  # SIP header, store in CDR(x_connid)



/***********************************************************
*    MISC
***********************************************************/

$LOCK_DIR               = '/tmp/';
  # where to write Gemeinschaft lock files. including trailing "/"!
  # e.g. "/var/lock/" or "/tmp/". the directory must exists.
  # does not control where apache, mysql, zaptel etc. put their
  # lock files

$CALL_INIT_FROM_NET         = '192.168.1.0/24, 172.16.0.0/12, 10.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8';
  # a comma (,) separated list of IP addresses or
  # <IP address>/<netmask> pairs from where calls can be inited
  # with HTTP GET
  # CIDR or dotted decimal notation
  # e.g.: '127.0.0.1, 192.168.1.130/255.255.255.0, 192.168.1.130/24'
  # allow all: '0.0.0.0/0', allow none: '0.0.0.0/32'

$MONITOR_FROM_NET           = '0.0.0.0/32';  # deny all
//$MONITOR_FROM_NET           = '192.168.1.0/24, 172.16.0.0/12, 10.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8';
  # allow access to the extension monitor panel from these network
  # ranges
  # CIDR or dotted decimal notation

$CC_TIMEOUT             =  60;      # timeout of programmed call
                                    # completions in minutes

$INTL_LANG              = 'de_DE';  # "de_DE" or "en_US"
//$INTL_USE_GETTEXT       = false;
  # whether to use gettext files or php arrays. gettext seems to have
  # problems on some systems

$INTL_ASTERISK_LANG     = 'de';
  # "de". if you use anything else make sure to have the appropriate
  # sound files installed - especially the prompts in the "gemeinschaft"
  # subdirectory which are available in German only for now

$USERCOMMENT_OFFTIME    = 'Feierabend';  # e.g. "off-time"

//$EMAIL_PATTERN_VALID    = '/^[a-z0-9\-._]+@[a-z0-9\-._]{2,80}\.[a-z]{2,10}$/i';

$EMAIL_DELIVERY         = 'sendmail';
  # how to deliver e-mails to the users ("forgot password" function etc.)
  # "sendmail"    :  use PHP's mail() - needs *a* local sendmail (e.g.
  #                  sendmail / postfix / exim)
  # "direct-smtp" :  connect to the MX servers of the recipient directly
  #                  via SMTP



/***********************************************************
*    PHONEBOOK
***********************************************************/

//$PB_IMPORTED_ENABLED    = false;
//$PB_IMPORTED_ORDER      = 2;                    # 1|2|3
//$PB_IMPORTED_TITLE      = "Firma (aus LDAP)";   # short! no HTML entities!

//$PB_INTERNAL_TITLE      = "Intern";             #  "
//$PB_PRIVATE_TITLE       = "Pers\xC3\xB6nlich";  #  "



/***********************************************************
*    FAX
***********************************************************/

$FAX_ENABLED            = false;
//$FAX_PREFIX             = '*96';  # e.g. "*96" or "6"
  # internally faxes can be sent to <FAX_PREFIX><extension>
//$FAX_TSI_PREFIX         = '02501234';
//$FAX_TSI_PREFIX         = @$CANONIZE_NATL_PREFIX . @$CANONIZE_AREA_CODE . @$CANONIZE_LOCAL_BRANCH;
//$FAX_TSI                = '025012340,02501234100,0250123499';
//$FAX_TSI                = @$FAX_TSI_PREFIX.'0,' . @$FAX_TSI_PREFIX.'100,' . @$FAX_TSI_PREFIX.'99';
  # (TSI = Transmitting Subscriber Identification)

//$FAX_HYLAFAX_HOST       = '127.0.0.1';
//$FAX_HYLAFAX_PORT       = 4559;          # HylaFax port (FTP-like protocol)
//$FAX_HYLAFAX_ADMIN      = '';            # Adds admin user to your HylaFax's "hosts.hfaxd"
//$FAX_HYLAFAX_PASS       = '';



/***********************************************************
*    BRANCH OFFICE INTEGRATION (BOI)
***********************************************************/

$BOI_ENABLED            = false;

//$BOI_API_DEFAULT        = '';
  # default API (add users, GUI integration, ...) when adding new
  # foreign hosts
  # ""   : no API
  # "m01": SOAP-API 1.0, WSDL definition slightly invalid but works
  #        and thus left as is for backward compatibility

//$BOI_BRANCH_NETMASK     = '/24';        # CIDR notation, e.g. "/24"
//$BOI_BRANCH_PBX         = '0.0.0.130';  # e.g. "0.0.0.130"
  # will be added to the network address. example: new phone
  # requests settings, IP 10.1.2.190 => network addr. is
  # 10.1.2.0 => registrar addr. is 10.1.2.130. if that host
  # exists add the nobody user there, else add the nobody user
  # to a Gemeinschaft host according to PROV_AUTO_ADD_PHONE_HOST

//$BOI_NOBODY_EXTEN_PATTERN = '95xxxx';
  # like NOBODY_EXTEN_PATTERN but for foreign hosts

//$BOI_GUI_REVERSE_PROXY    = 'http://'. @$PROV_HOST .':8080/';
//$BOI_GUI_REVERSE_PROXY    = 'http://192.168.1.130:8080/';
  # scheme, host, port, path (including trailing "/") to the reverse
  # proxy for GUI integration

//$BOI_GUI_HOME_USER      = 'information/praesenzmonitor';
//$BOI_GUI_HOME_ADMIN     = 'information/status';
  # overrides the GUI's "home" page for foreign users



/***********************************************************
*    LOGGING
***********************************************************/

$LOG_TO      = 'file';              # 'file'|'syslog'
$LOG_LEVEL   = 'NOTICE';            # "FATAL"|"WARNING"|"NOTICE"|"DEBUG"

  # these settings affect only file logging:
//$LOG_FILE    = '/var/log/gemeinschaft/gs.log';
$LOG_GMT     = false;               # use GMT or local time

  # these settings affect only logging to syslog:
$LOG_SYSLOG_FACILITY    = 'local5'; # 'local0'-'local7' | 'user'



/***********************************************************
*    INCLUDES
***********************************************************/

if (@$INSTALLATION_TYPE === 'gpbx') {
	$inc_file = '/etc/gemeinschaft/gemeinschaft-gpbx.php';
	if (file_exists($inc_file)) {
		include_once($inc_file);
	}
}

