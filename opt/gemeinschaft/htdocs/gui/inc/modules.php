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

$MODULES['login'    ]=  array('title' => __('Login'),
                              'icon'  => 'crystal-svg/%s/act/unlock.png',
                              'inmenu'=> false,
   'sub' => array(
      'login'        => array('title' => __('Login'))
   )
);

#####################################################################

$MODULES['home'     ]=  array('title' => __('Home'),
                              'icon'  => 'crystal-svg/%s/app/kfm_home.png',
   'sub' => array(
      'home'         => array('title' => __('Home'))
   )
);

#####################################################################

$MODULES['pb'       ]=  array('title' => __('Telefonbuch'),
                              'icon'  => 'crystal-svg/%s/act/contents.png',
   'sub' => array(
   )
);
$tmp = array(
	15=>array('k' => 'common' ,
		      's' => array('title' => __('Firma (Projekt)' ))  ),
	25=>array('k' => 'private',
	          's' => array('title' => __('Pers&ouml;nlich' ))  )
);
if (gs_get_conf('GS_PB_IMPORTED_ENABLED')) {
	$pos = (int)gs_get_conf('GS_PB_IMPORTED_ORDER', 9) * 10;
	$tmp[$pos] = array(
	          'k' => 'imported',
	          's' => array('title' => __('Firma (aus LDAP)'))
	);
}
kSort($tmp);
foreach ($tmp as $arr) {
$MODULES['pb'       ]['sub'][
      $arr['k']]     = $arr['s'];
}

$MODULES['pb'       ]['sub'][
      'csvimport']   =  array('title' => __('CSV-Import'));   //TRANSLATE ME

#####################################################################

$MODULES['calls'    ]=  array('title' => __('Anruflisten'),
                              'icon'  => 'crystal-svg/%s/app/karm.png',
   'sub' => array(
      'out'          => array('title' => __('gew&auml;hlt')),
      'missed'       => array('title' => __('verpasst')),
      'in'           => array('title' => __('angenommen'))
   )
);

#####################################################################

$MODULES['voicemail']=  array('title' => __('Voicemail'),
                              'icon'  => 'crystal-svg/%s/act/inbox.png',
   'sub' => array(
      'messages'     => array('title' => __('Nachrichten'))
   )
);

#####################################################################

$MODULES['forwards' ]=  array('title' => __('Rufumleitung'),
                              'icon'  => 'crystal-svg/%s/app/yast_route.png',
   'sub' => array(
      'forwards'     => array('title' => __('Rufumleitung')),
      'extnumbers'   => array('title' => __('externe Nummern')),
      'queues'       => array('title' => __('Queues'))
   )
);

#####################################################################

$MODULES['monitor'  ]=  array('title' => __('Monitor'),
                              'icon'  => 'crystal-svg/%s/app/display.png',
   'sub' => array(
      'queues'       => array('title' => __('Queues')),
      'pgrps'        => array('title' => __('Gruppen'))
   )
);
if (gs_get_conf('GS_GUI_MON_PEERS_ENABLED')) {
$MODULES['monitor'  ]['sub'][
      'peers']       =  array('title' => __('Kollegen'));
}

#####################################################################

$MODULES['features' ]=  array('title' => __('Dienstmerkmale'),
                              'icon'  => 'crystal-svg/%s/act/configure.png',
   'sub' => array(
      'features'     => array('title' => __('Dienstmerkmale'))
   )
);

#####################################################################

$MODULES['keys'     ]=  array('title' => __('Tastenbelegung'),
                              'icon'  => 'crystal-svg/%s/app/keyboard.png',
   'sub' => array(
   )
);
if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
$MODULES['keys'     ]['sub'][
      'snom']        =  array('title' => __('Snom'));
}

#####################################################################

$MODULES['ringtones']=  array('title' => __('Klingelt&ouml;ne'),
                              'icon'  => 'crystal-svg/%s/app/knotify.png',
   'sub' => array(
      'ringtones'    => array('title' => __('Klingelt&ouml;ne'))
   )
);

#####################################################################

$MODULES['stats'    ]=  array('title' => __('Statistik'),     //TRANSLATE ME
                              'icon'  => 'crystal-svg/%s/app/yast_partitioner.png',
   'sub' => array(
      'qclassical'   => array('title' => __('Q Klassisch')),  //TRANSLATE ME
      'callvolume'   => array('title' => __('Gespr.-Volumen'))//TRANSLATE ME
   )
);

#####################################################################

if (gs_get_conf('GS_FAX_ENABLED')) {
$MODULES['fax'      ]=  array('title' => __('Fax'),           //TRANSLATE ME
                              'icon'  => 'crystal-svg/%s/app/kdeprintfax.png',
   'sub' => array(
      'rec'          => array('title' => __('Empfangen')),    //TRANSLATE ME
      'send'         => array('title' => __('Fax versenden')),//TRANSLATE ME
      'out'          => array('title' => __('Ausgang')),      //TRANSLATE ME
      'done'         => array('title' => __('Gesendet'))      //TRANSLATE ME
   )
);
}

#####################################################################

$MODULES['help'     ]=  array('title' => __('Hilfe'),
                              'icon'  => 'crystal-svg/%s/act/help.png',
   'sub' => array(
      'numbers'      => array('title' => __('Service-Nummern')),
      'snom'         => array('title' => __('Snom'))
   )
);

#####################################################################

$MODULES['admin'    ]=  array('title' => __('Administration'),
                              'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
                              'perms' => 'admin',
   'sub' => array(
      'overview'     => array('title' => __('&Uuml;bersicht')),
      'users'        => array('title' => __('Benutzer')),
      'queues'       => array('title' => __('Warteschlangen')), //TRANSLATE ME
      'pgroups'      => array('title' => __('PickUp-Gruppen')), //TRANSLATE ME
      //'ivrs'         => array('title' => __('IVRs')),           //TRANSLATE ME
      'calls'        => array('title' => __('Verbindungen')),
      'reload'       => array('title' => __('Reload'))          //TRANSLATE ME
   )
);

#####################################################################

$MODULES['routing'  ]=  array('title' => __('Routen'),          //TRANSLATE ME
                              'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
                              'perms' => 'admin',
   'sub' => array(
      'gws-sip'      => array('title' => __('SIP-Gateways')),   //TRANSLATE ME
      'gwgrps'       => array('title' => __('Gateway-Gruppen')) //TRANSLATE ME
   )
);
if ($GS_INSTALLATION_TYPE === 'gpbx') {
$MODULES['routing'  ]['sub'][
      'e164']        =  array('title' => __('E.164'));          //TRANSLATE ME
}
$MODULES['routing'  ]['sub'][
      'inbound']     =  array('title' => __('Routen eingehend'));//TRANSLATE ME
$MODULES['routing'  ]['sub'][
      'outbound']    =  array('title' => __('Routen &amp; LCR'));//TRANSLATE ME
$MODULES['routing'  ]['sub'][
      'test']        =  array('title' => __('Routing-Test'));   //TRANSLATE ME

#####################################################################

$MODULES['system'   ]=  array('title' => __('System'),          //TRANSLATE ME
                              'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
                              'perms' => 'admin',
   'sub' => array(
      'sysstatus'    => array('title' => __('System-Status')),  //TRANSLATE ME
      'network'      => array('title' => __('Netzwerk'))        //TRANSLATE ME
      //'logging'      => array('title' => __('Logging'))         //TRANSLATE ME
   )
);
if ($GS_INSTALLATION_TYPE !== 'gpbx') {
$MODULES['system'   ]['sub'][
      'hosts']       =  array('title' => __('Hosts'));          //TRANSLATE ME
}
$MODULES['system'   ]['sub'][
      'nodesmon']    =  array('title' => __('Nodes'));
if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
$MODULES['system'   ]['sub'][
      'cards']       =  array('title' => __('PCI-Karten'));     //TRANSLATE ME
if ($GS_INSTALLATION_TYPE !== 'gpbx') {  //FIXME
$MODULES['system'   ]['sub'][
      'isdnports']   =  array('title' => __('ISDN-Ports'));     //TRANSLATE ME
} else {  //FIXME
$MODULES['system'   ]['sub'][
      'gpbx-b410p']  =  array('title' => __('ISDN-Ports (BRI)'));//TRANSLATE ME
}
}
/*
$MODULES['system'   ]['sub'][
      'config']      =  array('title' => __('Konfiguration'));  //TRANSLATE ME
*/

#####################################################################

$MODULES['logout'   ]=  array('title' => __('Logout'),
                              'icon'  => 'crystal-svg/%s/act/exit.png',
   'sub' => array(
      'logout'       => array('title' => __('Logout'))
   )
);

#####################################################################


?>