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

// GPBX: override defaults from /etc/gemeinschaft/gemeinschaft.php

include_once( GS_DIR .'inc/keyval.php' );


/***********************************************************
*    TYPE OF INSTALLATION
***********************************************************/

//$INSTALLATION_TYPE      = 'gpbx';
// we came here because INSTALLATION_TYPE==='gpbx' in the first place


/***********************************************************
*    DB
***********************************************************/

//----------------------[  Master  ]----------------------//

$DB_MASTER_HOST         = '127.0.0.1';
$DB_MASTER_USER         = 'root';
$DB_MASTER_PWD          = '';
$DB_MASTER_DB           = 'asterisk';

//----------------------[  Slave  ]-----------------------//

$DB_SLAVE_HOST          = '127.0.0.1';
$DB_SLAVE_USER          = 'root';
$DB_SLAVE_PWD           = '';
$DB_SLAVE_DB            = 'asterisk';

//-----------------------[  Misc  ]-----------------------//

$DB_MASTER_TRANSACTIONS = false;
$DB_SIP_REG_UPDATE      = true;


/***********************************************************
*    LDAP
***********************************************************/

$LDAP_HOST              = '0.0.0.0';


/***********************************************************
*    WEB INTERFACE
***********************************************************/

$GUI_AUTH_METHOD            = 'gemeinschaft';
$GUI_NUM_RESULTS            = 12;
$GUI_SUDO_ADMINS            = '';
$GUI_SUDO_EXTENDED          = false;
$GUI_PERMISSIONS_METHOD     = 'gemeinschaft';
$GUI_USER_MAP_METHOD        = '';
$GUI_QUEUE_SHOW_NUM_CALLS   = false;
$GUI_QUEUE_INFO_FROM_DB     = true;
$GUI_MON_NOQUEUEBLUE        = false;
$GUI_MON_PEERS_ENABLED      = false;
$GUI_SHUTDOWN_ENABLED       = true;
$GUI_LANGS                  = 'de_DE:de_DE:de-DE:Deutsch';
$GUI_ADDITIONAL_STYLESHEET  = 'gemeinschaft.css';


/***********************************************************
*    EXTERNAL NUMBERS BACKEND
***********************************************************/

$EXTERNAL_NUMBERS_BACKEND   = 'db';


/***********************************************************
*    NOBODY ACCOUNTS
***********************************************************/



/***********************************************************
*    PROVISIONING
***********************************************************/

$PROV_HOST                  = trim(gs_keyval_get('vlan_0_ipaddr'));
$PROV_PORT                  = 0;
$PROV_SCHEME                = 'http';
$PROV_PATH                  = '/gemeinschaft/prov/';
$PROV_AUTO_ADD_PHONE        = true;
$PROV_AUTO_ADD_PHONE_HOST   = 'first';
$PROV_DIAL_LOG_LIFE         = 14*24*3600;
$PROV_PROXIES_TRUST         = '';
$PROV_PROXIES_XFF_HEADER    = 'X-Forwarded-For';
$PROV_ALLOW_NET             = '0.0.0.0/0';
$PROV_LAN_NETS              = '0.0.0.0/0';


/***********************************************************
*    HANDSETS
***********************************************************/

//---------------------[  Snom 3xx  ]---------------------//

$SNOM_PROV_ENABLED          = true;
$SNOM_PROV_HTTP_USER        = '';
$SNOM_PROV_HTTP_PASS        = '';
$SNOM_PROV_PB_NUM_RESULTS   = 15;
$SNOM_PROV_FW_UPDATE        = false;
$SNOM_PROV_FW_6TO7          = false;

//----------------------[  Snom M3  ]---------------------//

$SNOM_PROV_M3_ACCOUNTS      = 1;


//----------------------[  Aastra  ]----------------------//

$AASTRA_PROV_ENABLED        = false;
$AASTRA_PROV_PB_NUM_RESULTS = 10;


//-----------------[  Siemens OpenStage  ]----------------//

$SIEMENS_PROV_ENABLED       = false;
//...


/***********************************************************
*    CANONICAL PHONE NUMBERS (FQTN)
***********************************************************/

$CANONIZE_OUTBOUND         =     trim(gs_keyval_get('canonize_outbound'        )) === 'yes';
$CANONIZE_INTL_PREFIX      =     trim(gs_keyval_get('canonize_intl_prefix'     ));
$CANONIZE_COUNTRY_CODE     =     trim(gs_keyval_get('canonize_country_code'    ));
$CANONIZE_NATL_PREFIX      =     trim(gs_keyval_get('canonize_natl_prefix'     ));
$CANONIZE_NATL_PREFIX_INTL =     trim(gs_keyval_get('canonize_natl_prefix_intl')) === 'yes';
$CANONIZE_AREA_CODE        =     trim(gs_keyval_get('canonize_area_code'       ));
$CANONIZE_LOCAL_BRANCH     =     trim(gs_keyval_get('canonize_local_branch'    ));
$CANONIZE_SPECIAL          = '/'.trim(gs_keyval_get('canonize_special'         )).'/';
$CANONIZE_CBC_PREFIX       =     trim(gs_keyval_get('canonize_cbc_prefix'      ));


/***********************************************************
*    DIALPLAN SETTINGS
***********************************************************/

$DP_SUBSYSTEM               = false;
$DP_EMERGENCY_POLICE        = '0110';
$DP_EMERGENCY_POLICE_MAP    = '110';
$DP_EMERGENCY_FIRE          = '0112';
$DP_EMERGENCY_FIRE_MAP      = '112';
$DP_DIALTIMEOUT_IN          = 90;
$DP_PRV_CALL_PREFIX         = '*7*';
$DP_FORWARD_REQ_EXT_NUM     = false;
$DP_ALLOW_DIRECT_DIAL       = true;
$DP_CONNID                  = false;


/***********************************************************
*    MISC
***********************************************************/

$LOCK_DIR               = '/tmp/';
$CALL_INIT_FROM_NET         = '0.0.0.0/0';  //FIXME
$MONITOR_FROM_NET           = '0.0.0.0/32';
$LVM_USER_6_DIGIT_INT       = false;
$LVM_CALL_INIT_USERS_500000 = false;
$CC_TIMEOUT             =  60;
$INTL_LANG              = 'de_DE';
$INTL_USE_GETTEXT       = false;
$INTL_ASTERISK_LANG     = 'de';
$USERCOMMENT_OFFTIME    = 'Feierabend';
$EMAIL_PATTERN_VALID    = '/^[a-z0-9\-._]+@[a-z0-9\-._]{2,80}\.[a-z]{2,10}$/i';
$EMAIL_DELIVERY         = 'direct-smtp';


/***********************************************************
*    PHONEBOOK
***********************************************************/

$PB_IMPORTED_ENABLED    = false;
$PB_IMPORTED_ORDER      = 2;
$PB_IMPORTED_TITLE      = "Importiert";
$PB_INTERNAL_TITLE      = "Intern";
$PB_PRIVATE_TITLE       = "Pers\xC3\xB6nlich";


/***********************************************************
*    FAX
***********************************************************/

$FAX_ENABLED            = false;
$FAX_PREFIX             = '6';
$FAX_HYLAFAX_HOST       = '127.0.0.1';


/***********************************************************
*    BRANCH OFFICE INTEGRATION (BOI)
***********************************************************/

$BOI_ENABLED            = false;
$BOI_API_DEFAULT        = '';


/***********************************************************
*    LOGGING
***********************************************************/

$LOG_TO      = 'file';
$LOG_LEVEL   = 'FATAL';
$LOG_FILE    = '/var/log/gemeinschaft/gs.log';
$LOG_GMT     = true;
$LOG_SYSLOG_FACILITY    = 'local5';


// NO NEWLINES AFTER THE CLOSING TAG!
?>