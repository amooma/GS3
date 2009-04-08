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
	'sub' => array(
		'login'        => array('title' => __('Login'))
	)
);

#####################################################################

$MODULES['home'     ]=  array(
	'title' => __('Startseite'),
	'icon'  => 'crystal-svg/%s/app/kfm_home.png',
	'sub' => array(
		'home'         => array('title' => __('Startseite'))
	)
);

#####################################################################

$MODULES['pb'       ]=  array(
	'title' => __('Telefonbuch'),
	'icon'  => 'crystal-svg/%s/act/contents.png',
	'boi_ok'=> false,  //FIXME?
	'sub' => array(
	)
);
$tmp = array(
	15=>array(
		'k' => 'common' ,
		's' => array('title' => __('Intern' ))  ),
	25=>array(
		'k' => 'private',
		's' => array('title' => __('Pers&ouml;nlich' ))  )
);
if (gs_get_conf('GS_PB_IMPORTED_ENABLED')) {
	$pos = (int)gs_get_conf('GS_PB_IMPORTED_ORDER', 9) * 10;
	$tmp[$pos] = array(
		'k' => 'imported',
		's' => array('title' => __('Extern'))
	);
}
kSort($tmp);
foreach ($tmp as $arr) {
$MODULES['pb'       ]['sub'][
		$arr['k']]     = $arr['s'];
}

$MODULES['pb'       ]['sub'][
		'csvimport']   =  array('title' => __('CSV-Import'));

#####################################################################

$MODULES['calls'    ]=  array(
	'title' => __('Anruflisten'),
	'icon'  => 'crystal-svg/%s/app/karm.png',
	'boi_ok'=> false,
	'sub' => array(
		'out'          => array('title' => __('gew&auml;hlt')),
		'missed'       => array('title' => __('verpasst')),
		'in'           => array('title' => __('angenommen'))
	)
);

#####################################################################

$MODULES['voicemail']=  array(
	'title' => __('Anrufbeantworter'),
	'icon'  => 'crystal-svg/%s/act/inbox.png',
	'boi_ok'=> false,
	'sub' => array(
		'messages'     => array('title' => __('Nachrichten'))
	)
);

#####################################################################

$MODULES['forwards' ]=  array(
	'title' => __('Rufumleitung'),
	'icon'  => 'crystal-svg/%s/app/yast_route.png',
	'boi_ok'=> false,
	'sub' => array(
		'forwards'     => array('title' => __('Rufumleitung')),
		'extnumbers'   => array('title' => __('externe Nummern')),
		'queues'       => array('title' => __('Warteschlangen')),
		'hgroups'      => array('title' => __('Sammelanschl&uuml;sse'))

	)
);

#####################################################################

$MODULES['monitor'  ]=  array(
	'title' => __('Monitor'),
	'icon'  => 'crystal-svg/%s/app/display.png',
	'boi_ok'=> false,
	'sub' => array(
		'queues'       => array('title' => __('Warteschlangen')),
		'pgrps'        => array('title' => __('Rufannahmegrp.'))
	)
);
if (gs_get_conf('GS_GUI_MON_PEERS_ENABLED')) {
$MODULES['monitor'  ]['sub'][
		'peers']       =  array('title' => __('Kollegen'));
$MODULES['monitor'  ]['sub'][
		'friends']     =  array('title' => __('Berechtigungen'));
}

#####################################################################

$MODULES['features' ]=  array(
	'title' => __('Dienstmerkmale'),
	'icon'  => 'crystal-svg/%s/act/configure.png',
	'boi_ok'=> false,
	'sub' => array(
		'features'     => array('title' => __('Dienstmerkmale'))
	)
);

#####################################################################

$MODULES['keys'     ]=  array(
	'title' => __('Tastenbelegung'),
	'icon'  => 'crystal-svg/%s/app/keyboard.png',
	'sub' => array(
	)
);
$MODULES['keys'     ]['sub'][
		'keyprof'     ]=  array('title' => __('Tastenbelegung'));

#####################################################################

$MODULES['ringtones']=  array(
	'title' => __('Klingelt&ouml;ne'),
	'icon'  => 'crystal-svg/%s/app/knotify.png',
	'sub' => array(
		'ringtones'    => array('title' => __('Klingelt&ouml;ne'))
	)
);

#####################################################################

$MODULES['stats'    ]=  array(
	'title' => __('Statistik'),
	'icon'  => 'crystal-svg/%s/app/yast_partitioner.png',
	'boi_ok'=> false,
	'sub' => array(
		'qclassical'   => array('title' => __('Q Klassisch')),
		'callvolume'   => array('title' => __('Gespr.-Volumen'))
	)
);

#####################################################################

if (gs_get_conf('GS_FAX_ENABLED')) {
$MODULES['fax'      ]=  array(
	'title' => __('Fax'),
	'icon'  => 'crystal-svg/%s/app/kdeprintfax.png',
	'boi_ok'=> false,
	'sub' => array(
		'rec'          => array('title' => __('Empfangen')),
		'send'         => array('title' => __('Fax versenden')),
		'out'          => array('title' => __('Ausgang')),
		'done'         => array('title' => __('Gesendet'))
	)
);
}

$MODULES['pin']=  array(
	'title' => __('PIN &auml;ndern'),
	'icon'  => 'crystal-svg/%s/app/pin.png',
	'sub' => array(
		'pin'    => array('title' => __('PIN &auml;ndern'))
	)
);

#####################################################################

$MODULES['help'     ]=  array(
	'title' => __('Hilfe'),
	'icon'  => 'crystal-svg/%s/act/help.png',
	'boi_ok'=> false,
	'sub' => array(
		'numbers'      => array('title' => __('Service-Nummern'))
	)
);
if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
$MODULES['help'     ]['sub'][
		'snom']        =  array('title' => __('Snom'));
}

#####################################################################

$MODULES['admin'    ]=  array(
	'title' => __('Administration'),
	'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
	'perms' => 'admin',
	'boi_ok'=> false,
	'sub' => array(
		'overview'     => array('title' => __('&Uuml;bersicht')),
		'users'        => array('title' => __('Benutzer')),
		'groups'       => array('title' => __('Benutzergruppen')),
		'queues'       => array('title' => __('Warteschlangen')),
		'pgroups'      => array('title' => __('Rufannahmegrp.#pl')),
		'hgroups'      => array('title' => __('Sammelanschl&uuml;sse')),
		//'ivrs'         => array('title' => __('IVRs')),
		'calls'        => array('title' => __('CDRs')),
		'reload'       => array('title' => __('Reload'))
	)
);
if (gs_get_conf('GS_BOI_ENABLED')) {
$MODULES['admin'    ]['sub'][
		'boi-perms']   =  array('title' => __('Lokale Admins'));
}

#####################################################################

$MODULES['prov'     ]=  array(
	'title' => __('Provisioning'),
	'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
	'perms' => 'admin',
	'boi_ok'=> false,
	'sub' => array(
		'phones'       => array('title' => __('Telefone')),
		'keyprof'      => array('title' => __('Tasten-Profile')),
		'provparams'   => array('title' => __('Parameter')),
		'jobs'         => array('title' => __('Jobs'))
	)
);

#####################################################################

$MODULES['routing'  ]=  array(
	'title' => __('Routen'),
	'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
	'perms' => 'admin',
	'boi_ok'=> false,
	'sub' => array(
		'gwgrps'       => array('title' => __('Gateway-Gruppen')),
		'gws-sip'      => array('title' => __('SIP-Gateways')),
		'gws-misdn'    => array('title' => __('BRI-Gateways'))
	)
);
if ($GS_INSTALLATION_TYPE === 'gpbx') {
$MODULES['routing'  ]['sub'][
		'e164']        =  array('title' => __('E.164'));
}
$MODULES['routing'  ]['sub'][
		'inbound']     =  array('title' => __('Routen eingehend'));
$MODULES['routing'  ]['sub'][
		'outbound']    =  array('title' => __('Routen &amp; LCR'));
$MODULES['routing'  ]['sub'][
		'test']        =  array('title' => __('Routing-Test'));

#####################################################################

$MODULES['system'   ]=  array(
	'title' => __('System'),
	'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
	'perms' => 'admin',
	'boi_ok'=> false,
	'sub' => array(
	)
);
if ($GS_INSTALLATION_TYPE === 'gpbx') {
$MODULES['system'   ]['sub'][
		'gpbx-diskusage']=array('title' => __('Speicherplatz'));
}
$MODULES['system'   ]['sub'][
		'sysstatus']   =  array('title' => __('System-Status'));
$MODULES['system'   ]['sub'][
		'network']     =  array('title' => __('Netzwerk'));
if ($GS_INSTALLATION_TYPE === 'gpbx') {
$MODULES['system'  ]['sub'][
		'dhcp-leases'] =  array('title' => __('DHCP-Leases'));
}
/*
$MODULES['system'   ]['sub'][
		'logging']     =  array('title' => __('Logging'));
*/
$MODULES['system'   ]['sub'][
		'nodesmon']    =  array('title' => __('Nodes-Status'));
if ($GS_INSTALLATION_TYPE !== 'gpbx') {
$MODULES['system'   ]['sub'][
		'hosts']       =  array('title' => __('Hosts'));
if (gs_get_conf('GS_BOI_ENABLED')) {
$MODULES['system'   ]['sub'][
		'hosts-foreign']= array('title' => __('Hosts (fremde)'));
}
}
if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
$MODULES['system'   ]['sub'][
		'cards']       =  array('title' => __('PCI-Karten'));
if ($GS_INSTALLATION_TYPE !== 'gpbx') {  //FIXME
$MODULES['system'   ]['sub'][
		'isdnports']   =  array('title' => __('ISDN-Ports'));
} else {  //FIXME
$MODULES['system'   ]['sub'][
		'gpbx-b410p']  =  array('title' => __('ISDN-Ports (BRI)'));
}
if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
$MODULES['system'   ]['sub'][
		'asterisk-log']=  array('title' => __('Asterisk-Log'));
}
if ($GS_INSTALLATION_TYPE === 'gpbx') {
$MODULES['system'   ]['sub'][
		'gpbx-upgrade']=  array('title' => __('Upgrade'));
}
$MODULES['system'   ]['sub'][
		'shutdown']    =  array('title' => __('Ausschalten'));
}
/*
$MODULES['system'   ]['sub'][
		'config']      =  array('title' => __('Konfiguration'));
*/

#####################################################################

if (gs_get_conf('GS_GUI_AUTH_METHOD') !== 'webseal') {
$MODULES['logout'   ]=  array(
	'title' => __('Logout'),
	'icon'  => 'crystal-svg/%s/act/exit.png',
	'sub' => array(
		'logout'       => array('title' => __('Logout'))
	)
);
}

#####################################################################


?>