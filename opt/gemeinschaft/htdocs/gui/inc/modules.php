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


$MODULES = array();


#####################################################################

$MODULES['login'    ]=  array(
	'title' => __('Login'),
	'icon'  => 'crystal-svg/%s/act/unlock.png',
	'inmenu'=> false,
	'id' => 1000,
	'sub' => array(
		'login'        => array('title' => __('Login'), 'id' => 1001)
	)
);

#####################################################################

$MODULES['home'     ]=  array(
	'title' => __('Startseite'),
	'icon'  => 'crystal-svg/%s/app/kfm_home.png',
	'id' => 2000,
	'sub' => array(
		'home'         => array('title' => __('Startseite'), 'id' => 2001)
	)
);

#####################################################################

$MODULES['pb'       ]=  array(
	'title' => __('Telefonbuch'),
	'icon'  => 'crystal-svg/%s/act/contents.png',
	'boi_ok'=> false,  //FIXME?
	'id' => 3000,
	'sub' => array(
	)
);
$tmp = array(
	15=>array(
		'k' => 'common' ,
		's' => array('title' => __('Intern' ), 'id' => 3001)  ),
	25=>array(
		'k' => 'private',
		's' => array('title' => __('Pers&ouml;nlich' ), 'id' => 3002)  )
);
if (gs_get_conf('GS_PB_IMPORTED_ENABLED')) {
	$pos = (int)gs_get_conf('GS_PB_IMPORTED_ORDER', 9) * 10;
	$tmp[$pos] = array(
		'k' => 'imported',
		's' => array('title' => __('Extern'), 'id' => 3003)
	);
}
kSort($tmp);
foreach ($tmp as $arr) {
$MODULES['pb'       ]['sub'][
		$arr['k']]     = $arr['s'];
}

$MODULES['pb'       ]['sub'][
		'csvimport']   =  array('title' => __('CSV-Import/Export'), 'id' => 3004);

#####################################################################

$MODULES['calls'    ]=  array(
	'title' => __('Anruflisten'),
	'icon'  => 'crystal-svg/%s/app/karm.png',
	'boi_ok'=> false,
	'id' => 4000,
	'sub' => array(
		'out'          => array('title' => __('gew&auml;hlt'), 'id' => 4001),
		'missed'       => array('title' => __('verpasst'), 'id' => 4002),
		'in'           => array('title' => __('angenommen'), 'id' => 4003)
	)
);

#####################################################################

$MODULES['voicemail']=  array(
	'title' => __('Anrufbeantworter'),
	'icon'  => 'crystal-svg/%s/act/inbox.png',
	'boi_ok'=> false,
	'id' => 5000,
	'sub' => array(
		'messages'     => array('title' => __('Nachrichten'), 'id' => 5001)
	)
);

#####################################################################

$MODULES['forwards' ]=  array(
	'title' => __('Rufumleitung'),
	'icon'  => 'crystal-svg/%s/app/yast_route.png',
	'boi_ok'=> false,
	'id' => 6000,
	'sub' => array(
		'forwards'     => array('title' => __('Rufumleitung'), 'id' => 6001),
		'vmconfig'     => array('title' => __('Konfiguration'), 'id' => 6002),
		'extnumbers'   => array('title' => __('externe Nummern'), 'id' => 6003),
		'queues'       => array('title' => __('Warteschlangen'), 'perms' => 'admin', 'id' => 6004),
		'hgroups'      => array('title' => __('Sammelanschl&uuml;sse'), 'perms' => 'admin', 'id' => 6005)
	)
);

#####################################################################

$MODULES['monitor'  ]=  array(
	'title' => __('Monitor'),
	'icon'  => 'crystal-svg/%s/app/display.png',
	'boi_ok'=> false,
	'id' => 7000,
	'sub' => array(
		'queues'       => array('title' => __('Warteschlangen'), 'id' => 7001),
		'pgrps'        => array('title' => __('Rufannahmegrp.'), 'id' => 7002)
	)
);
if (gs_get_conf('GS_GUI_MON_PEERS_ENABLED')) {
$MODULES['monitor'  ]['sub'][
		'peers']       =  array('title' => __('Kollegen'), 'id' => 7003);
/*
if (gs_get_conf('GS_GUI_PERMISSIONS_METHOD') === 'gemeinschaft') {
$MODULES['monitor'  ]['sub'][
		'friends']     =  array('title' => __('Berechtigungen'), 'id' => 7004);
}
*/
}

#####################################################################

$MODULES['features' ]=  array(
	'title' => __('Dienstmerkmale'),
	'icon'  => 'crystal-svg/%s/act/configure.png',
	'boi_ok'=> false,
	'id' => 8000,
	'sub' => array(
		'features'     => array('title' => __('Dienstmerkmale'), 'id' => 8001)
	)
);

#####################################################################

$MODULES['keys'     ]=  array(
	'title' => __('Tastenbelegung'),
	'icon'  => 'crystal-svg/%s/app/keyboard.png',
	'id' => 9000,
	'sub' => array(
	)
);
$MODULES['keys'     ]['sub'][
		'keyprof'     ]=  array('title' => __('Tastenbelegung'), 'id' => 9001);

#####################################################################

$MODULES['ringtones']=  array(
	'title' => __('Klingelt&ouml;ne'),
	'icon'  => 'crystal-svg/%s/app/knotify.png',
	'id' => 10000,
	'sub' => array(
		'ringtones'    => array('title' => __('Klingelt&ouml;ne'), 'id' => 10001)
	)
);

#####################################################################

$MODULES['volume']=  array(
	'title' => __('Lautst&auml;rken'),
	'icon'  => 'crystal-svg/%s/app/kmix.png',
	'id' => 20000,
	'sub' => array(
		'phone'    => array('title' => __('Lautst&auml;rken'), 'id' => 20001)
	)
);

#####################################################################

$MODULES['stats'    ]=  array(
	'title' => __('Statistik'),
	'icon'  => 'crystal-svg/%s/app/yast_partitioner.png',
	'boi_ok'=> false,
	'id' => 11000,
	'sub' => array(
		'qclassical'   => array('title' => __('Q Klassisch'), 'id' => 11001),
		'callvolume'   => array('title' => __('Gespr.-Volumen'), 'id' => 11002)
	)
);

$MODULES['stats'    ]['sub'][
		'groupc']      =  array('title' => __('Kollegenstatistik'), 'id' => 11003);
$MODULES['stats'    ]['sub'][
		'groupout']    =  array('title' => __('Kollegen ausgehend'), 'id' => 11004);
$MODULES['stats'    ]['sub'][
		'combined']    =  array('title' => __('Tagesauswertung'), 'id' => 11005);

#####################################################################

if (gs_get_conf('GS_FAX_ENABLED')) {
$MODULES['fax'      ]=  array(
	'title' => __('Fax'),
	'icon'  => 'crystal-svg/%s/app/kdeprintfax.png',
	'boi_ok'=> false,
	'id' => 12000,
	'sub' => array(
		'rec'          => array('title' => __('Empfangen'), 'id' => 12001),
		'send'         => array('title' => __('Fax versenden'), 'id' => 12002),
		'out'          => array('title' => __('Ausgang'), 'id' => 12003),
		'done'         => array('title' => __('Gesendet'), 'id' => 12004)
	)
);
}

$MODULES['pin']=  array(
	'title' => __('PIN &auml;ndern'),
	'icon'  => 'crystal-svg/%s/app/pin.png',
	'id' => 13000,
	'sub' => array(
		'pin'    => array('title' => __('PIN &auml;ndern'), 'id' => 13001)
	)
);

#####################################################################

$MODULES['help'     ]=  array(
	'title' => __('Hilfe'),
	'icon'  => 'crystal-svg/%s/act/help.png',
	'boi_ok'=> false,
	'id' => 14000,
	'sub' => array(
		'numbers'      => array('title' => __('Service-Nummern'), 'id' => 14001),
		'keys'      => array('title' => __('Tastenbelegung'), 'id' => 14002)
	)
);
if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
$MODULES['help'     ]['sub'][
		'snom']        =  array('title' => __('snom'), 'id' => 14003);
}

#####################################################################

$MODULES['admin'    ]=  array(
	'title' => __('Administration'),
	'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
	'perms' => 'admin',
	'boi_ok'=> false,
	'id' => 15000,
	'sub' => array(
		'overview'     => array('title' => __('&Uuml;bersicht'), 'id' => 15001),
		'users'        => array('title' => __('Benutzer'), 'id' => 15002),
		//'groups'       => array('title' => __('Benutzergruppen'), 'id' => 15003),
		'groups'       => array('title' => __('Gruppen'), 'id' => 15004),
		'gui'          => array('title' => __('GUI'), 'id' => 15016),
		'agents'       => array('title' => __('Agenten'), 'id' => 15015),
		'queues'       => array('title' => __('Warteschlangen'), 'id' => 15005),
		'pgroups'      => array('title' => __('Rufannahmegrp.#pl'), 'id' => 15006),
		'hgroups'      => array('title' => __('Sammelanschl&uuml;sse'), 'id' => 15014),
		'ivrs'         => array('title' => __('Sprachmen&uuml;'), 'id' => 15008),
		'sysrecs'      => array('title' => __('Audiodateien'), 'id' => 15013),
//		'conferences'  => array('title' => __('Konferenzen'), 'id' => 15007),
		'calls'        => array('title' => __('CDRs'), 'id' => 15009),
		'reload'       => array('title' => __('Reload'), 'id' => 15010)
	)
);
if (gs_get_conf('GS_BOI_ENABLED')) {
$MODULES['admin'    ]['sub'][
		'boi-perms']   =  array('title' => __('Lokale Admins'), 'id' => 15011);
}
if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
$MODULES['admin'    ]['sub'][
		'ami']         =  array('title' => __('Asterisk-Manager'), 'id' => 15012);
}

#####################################################################

$MODULES['prov'     ]=  array(
	'title' => __('Provisioning'),
	'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
	'perms' => 'admin',
	'boi_ok'=> false,
	'id' => 16000,
	'sub' => array(
		'phones'       => array('title' => __('Telefone'), 'id' => 16001),
		'groups'       => array('title' => __('Provisioning-Grp.'), 'id' => 16002),
		'keyprof'      => array('title' => __('Tasten-Profile'), 'id' => 16003),
		'provparams'   => array('title' => __('Parameter'), 'id' => 16004),
		'jobs'         => array('title' => __('Jobs'), 'id' => 16005)
	)
);

#####################################################################

$MODULES['routing'  ]=  array(
	'title' => __('Routen'),
	'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
	'perms' => 'admin',
	'boi_ok'=> false,
	'id' => 17000,
	'sub' => array(
		'gwgrps'       => array('title' => __('Gateway-Gruppen'), 'id' => 17001),
		'gws-sip'      => array('title' => __('SIP-Gateways'), 'id' => 17002),
		'gws-iax'      => array('title' => __('IAX-Gateways'), 'id' => 17009),
		'gws-isdn'     => array('title' => __('ISDN-Gateways'), 'id' => 17003)
	)
);
if ($GS_INSTALLATION_TYPE === 'gpbx') {
$MODULES['routing'  ]['sub'][
		'e164']        =  array('title' => __('E.164'), 'id' => 17004);
}
$MODULES['routing'  ]['sub'][
		'inbound']     =  array('title' => __('Routen eingehend'), 'id' => 17005);
$MODULES['routing'  ]['sub'][
		'outbound']    =  array('title' => __('Routen &amp; LCR'), 'id' => 17006);
$MODULES['routing'  ]['sub'][
		'test']        =  array('title' => __('Routing-Test'), 'id' => 17007);
/*
$MODULES['routing'  ]['sub'][
		'blacklist']   =  array('title' => __('Blacklist'), 'id' => 17008);
*/

#####################################################################

$MODULES['system'   ]=  array(
	'title' => __('System'),
	'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
	'perms' => 'admin',
	'boi_ok'=> false,
	'id' => 18000,
	'sub' => array(
	)
);
if ($GS_INSTALLATION_TYPE === 'gpbx') {
$MODULES['system'   ]['sub'][
		'gpbx-diskusage']=array('title' => __('Speicherplatz'), 'id' => 18001);
}
$MODULES['system'   ]['sub'][
		'sysstatus']   =  array('title' => __('System-Status'), 'id' => 18002);
$MODULES['system'   ]['sub'][
		'network']     =  array('title' => __('Netzwerk'), 'id' => 18003);
if ($GS_INSTALLATION_TYPE === 'gpbx') {
$MODULES['system'  ]['sub'][
		'dhcp-leases'] =  array('title' => __('DHCP-Leases'), 'id' => 18004);
}
/*
$MODULES['system'   ]['sub'][
		'logging']     =  array('title' => __('Logging'), 'id' => 18005);
*/
$MODULES['system'   ]['sub'][
		'nodesmon']    =  array('title' => __('Nodes-Status'), 'id' => 18006);
if ($GS_INSTALLATION_TYPE !== 'gpbx') {
$MODULES['system'   ]['sub'][
		'hosts']       =  array('title' => __('Hosts'), 'id' => 18007);
if (gs_get_conf('GS_BOI_ENABLED')) {
$MODULES['system'   ]['sub'][
		'hosts-foreign']= array('title' => __('Hosts (fremde)'), 'id' => 18008);
}
}
if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
$MODULES['system'   ]['sub'][
		'cards']       =  array('title' => __('PCI-Karten'), 'id' => 18009);
if ($GS_INSTALLATION_TYPE !== 'gpbx') {  //FIXME
$MODULES['system'   ]['sub'][
		'isdnports']   =  array('title' => __('ISDN-Ports'), 'id' => 18010);
} else {  //FIXME
$MODULES['system'   ]['sub'][
		'gpbx-b410p']  =  array('title' => __('ISDN-Ports (BRI)'), 'id' => 18011);
}
if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
$MODULES['system'   ]['sub'][
		'asterisk-log']=  array('title' => __('Asterisk-Log'), 'id' => 18012);
}
if ($GS_INSTALLATION_TYPE === 'gpbx') {
$MODULES['system'   ]['sub'][
		'gpbx-upgrade']=  array('title' => __('Upgrade'), 'id' => 18013);
}
$MODULES['system'   ]['sub'][
		'shutdown']    =  array('title' => __('Ausschalten'), 'id' => 18014);
}
/*
$MODULES['system'   ]['sub'][
		'config']      =  array('title' => __('Konfiguration'), 'id' => 18015);
*/

#####################################################################

$MODULES['wakeupcall' ]=  array(
	'title' => __('Weckruf'),
	'icon'  => 'crystal-svg/%s/app/kalarm.png',
	'boi_ok'=> false,
	'id' => 22000,
	'sub' => array(
		'wakeupcall'     => array('title' => __('Weckruf'), 'id' => 22001)
	)
);

#####################################################################

$MODULES['room' ]=  array(
	'title' => __('Zimmer'),
	'icon'  => 'crystal-svg/%s/app/assistant.png',
	'boi_ok'=> false,
	'id' => 21000,
	'sub' => array(
		'state'     => array('title' => __('Status'), 'id' => 21001)
	)
);

#####################################################################

if (gs_get_conf('GS_GUI_AUTH_METHOD') !== 'webseal') {
$MODULES['logout'   ]=  array(
	'title' => __('Logout'),
	'icon'  => 'crystal-svg/%s/act/exit.png',
	'id' => 19000,
	'sub' => array(
		'logout'       => array('title' => __('Logout'), 'id' => 19001)
	)
);
}

#####################################################################

?>
