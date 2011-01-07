<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
*                    Add-on Siemens provisioning
* 
* $Revision: 366 $
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
*    PROVISIONING (SIEMENS)
***********************************************************/

# whether to deploy new firmware versions. do not leave this always
# on as there is a chance of infinite firmware deployment loops if
# the phone cannot contact the FTP server or if something else goes
# wrong!
define( 'GS_PROV_SIEMENS_SW_UPDATE', true );

# whether to allow deployment of firmware images which look (internally)
# like pre-release versions:
define( 'GS_PROV_SIEMENS_SW_UPDATE_PRE', true );

# FTP server (IP address / hostname) to use for firmware deployment.
# null or undefined for normal provisioning server
//define( 'GS_PROV_SIEMENS_FTP_SERVER_LAN', null );
//define( 'GS_PROV_SIEMENS_FTP_SERVER_WAN', null );
//define( 'GS_PROV_SIEMENS_FTP_PATH', './' );
define( 'GS_PROV_SIEMENS_FTP_USER', 'gs-siemens-fw' );
define( 'GS_PROV_SIEMENS_FTP_PWD' , 'gs-siemens-fw' );

# enable raw logging of the communication to
# /var/log/gemeinschaft/siemens-access-debug.log ?
define( 'GS_PROV_SIEMENS_LOG_RAW', false );

# idlescreen background logo. just the filename. os40 gets bmp
# extension, os60 and os80 get png
define( 'GS_PROV_SIEMENS_LOGO', 'logoscreen.%s.%s' );
define( 'GS_PROV_SIEMENS_WALLPAPER', 'idlescreen.%s.%s');

# SNMP server IP address
define( 'GS_PROV_SIEMENS_SNMP_TRAP_ADDR', '' );
define( 'GS_PROV_SIEMENS_SNMP_TRAP_PORT', 162 );  # default: 162

# XML Applications

# Phonebook
#
$gs_phonebook = array(
	'XML-app-name' => 'gs-phonebook',
	'XML-app-display-name' => 'Telefonbuch',
	'XML-app-program-name' => 'gemeinschaft/prov/siemens/pb/pb.php',
	'XML-app-special-instance' => '0',
	'XML-app-server-addr' => '{GS_PROV_HOST}',
	'XML-app-server-port' => '{GS_PROV_PORT}',
	'XML-app-proxy-enabled' => 'false',
	'XML-app-remote-debug' => 'false',
	'XML-app-debug-prog-name' => '',
	'XML-app-num-tabs' => '0',
	'XML-app-auto-start' => 'true',
);

# Phonebook (integrated on phonebook mode key)
$gs_phonebook_pb_key = array(
	'XML-app-name' => 'XMLPhonebook',
	'XML-app-display-name' => 'Telefonbuch',
	'XML-app-program-name' => 'gemeinschaft/prov/siemens/pb/pb.php',
	'XML-app-special-instance' => '2',
	'XML-app-server-addr' => '{GS_PROV_HOST}',
	'XML-app-server-port' => '80',
	'XML-app-proxy-enabled' => 'false',
	'XML-app-remote-debug' => 'false',
	'XML-app-debug-prog-name' => '',
	'XML-app-num-tabs' => '3',
	'XML-app-tab1-name' => 'XMLPhonebook',
	'XML-app-tab1-display-name' => gs_get_conf('GS_PB_PRIVATE_TITLE' , __("Pers\xC3\xB6nlich")),
	'XML-app-tab2-name' => 'XMLPhonebook_2',
	'XML-app-tab2-display-name' => gs_get_conf('GS_PB_INTERNAL_TITLE', __("Intern")),
	'XML-app-tab3-name' => 'XMLPhonebook_3',
	'XML-app-tab3-display-name' => gs_get_conf('GS_PB_IMPORTED_TITLE', __("Extern")),
	'XML-app-auto-start' => 'true',
	'XML-app-all-tabs-start' => 'true',
);


# Gemeinschaft Diallog
$gs_diallog_dl_key = array(
	'XML-app-name' => 'gs-diallog',
	'XML-app-display-name' => 'Anrufliste',
	'XML-app-program-name' => 'gemeinschaft/prov/siemens/dial-log/dlog.php',
	'XML-app-special-instance' => '0',
	'XML-app-server-addr' => '{GS_PROV_HOST}',
	'XML-app-server-port' => '{GS_PROV_PORT}',
	'XML-app-proxy-enabled' => 'false',
	'XML-app-remote-debug' => 'false',
	'XML-app-debug-prog-name' => '',
	'XML-app-num-tabs' => '0',
	'XML-app-tab1-display-name' => 'gs-diallog',
	'XML-app-auto-start' => 'true',
	'XML-app-control-key' => '3',
);



$GS_PROV_SIEMENS_XML_APPS = array('Telefonbuch' => $gs_phonebook_pb_key, 'Anruflisten' => $gs_diallog_dl_key);

?>
