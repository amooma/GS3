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


$MODULES = array(
	
	##################################################################
	'login'            => array('title' => __('Login'),
	                            'icon'  => 'crystal-svg/%s/app/kfm_home.png',
	                            'inmenu'=> false,
	   'sub' => array(
	      'login'      => array('title' => __('Login'))
	   )
	),
	##################################################################
	'home'             => array('title' => __('Home'),
	                            'icon'  => 'crystal-svg/%s/app/kfm_home.png',
	   'sub' => array(
	      'home'       => array('title' => __('Home'))
	   )
	),
	##################################################################
	'pb'               => array('title' => __('Telefonbuch'),
	                            'icon'  => 'crystal-svg/%s/act/contents.png',
	   'sub' => array(
	      //'imported'   => array('title' => __('Firma (aus LDAP)')),
	      'gs'         => array('title' => __('Firma (Projekt)')),
	      'private'    => array('title' => __('Pers&ouml;nlich')),
	      'csvimport'  => array('title' => __('_ CSV-Import'))
	   )
	),
	##################################################################
	'diallog'          => array('title' => __('Anruflisten'),
	                            'icon'  => 'crystal-svg/%s/app/karm.png',
	   'sub' => array(
	      'out'        => array('title' => __('gew&auml;hlt')),
	      'missed'     => array('title' => __('verpasst')),
	      'in'         => array('title' => __('angenommen'))
	   )
	),
	##################################################################
	'voicemail'        => array('title' => __('Voicemail'),
	                            'icon'  => 'crystal-svg/%s/act/inbox.png',
	   'sub' => array(
	      'messages'   => array('title' => __('Nachrichten'))
	   )
	),
	##################################################################
	'forwards'         => array('title' => __('Rufumleitung'),
	                            'icon'  => 'crystal-svg/%s/app/yast_route.png',
	   'sub' => array(
	      'forwards'   => array('title' => __('Rufumleitung')),
	      'extnumbers' => array('title' => __('externe Nummern')),
	      'queues'     => array('title' => __('Queues'))
	   )
	),
	##################################################################
	'monitor'          => array('title' => __('Monitor'),
	                            'icon'  => 'crystal-svg/%s/app/display.png',
	   'sub' => array(
	      'queues'     => array('title' => __('Queues')),
	      'pgrps'      => array('title' => __('Gruppen'))
	      //'peers'      => array('title' => __('Kollegen'))
	   )
	),
	##################################################################
	'features'         => array('title' => __('Dienstmerkmale'),
	                            'icon'  => 'crystal-svg/%s/act/configure.png',
	   'sub' => array(
	      'features'   => array('title' => __('Dienstmerkmale'))
	   )
	),
	##################################################################
	'keys'             => array('title' => __('Tastenbelegung'),
	                            'icon'  => 'crystal-svg/%s/app/keyboard.png',
	   'sub' => array(
	      'snom'       => array('title' => __('Snom'))
	   )
	),
	##################################################################
	'ringtones'        => array('title' => __('Klingelt&ouml;ne'),
	                            'icon'  => 'crystal-svg/%s/app/knotify.png',
	   'sub' => array(
	      'ringtones'  => array('title' => __('Klingelt&ouml;ne'))
	   )
	),
	##################################################################
	'stats'            => array('title' => __('_ Statistik'),
	                            'icon'  => 'crystal-svg/%s/app/yast_partitioner.png',
	   'sub' => array(
	      'qclassical' => array('title' => __('_ Q Klassisch'))
	   )
	),
	##################################################################
	'fax'              => array('title' => __('Fax'),
	                            'icon'  => 'crystal-svg/%s/act/fileprint.png',
	   'sub' => array(
	      'rec'        => array('title' => __('Empfangen')),
	      'done'       => array('title' => __('Gesendet')),
	      'out'        => array('title' => __('Fax Ausgang')),
	      'send'       => array('title' => __('Fax versenden'))
	   )
	),
	##################################################################
	'help'             => array('title' => __('Hilfe'),
	                            'icon'  => 'crystal-svg/%s/act/help.png',
	   'sub' => array(
	      'numbers'    => array('title' => __('Service-Nummern')),
	      'snom'       => array('title' => __('Snom'))
	   )
	),
	##################################################################
	'admin'            => array('title' => __('Administration'),
	                            'icon'  => 'crystal-svg/%s/app/yast_sysadmin.png',
	                            'perms' => 'admin',
	   'sub' => array(
	      'overview'   => array('title' => __('&Uuml;bersicht')),
	      'users'      => array('title' => __('Benutzer')),
	      'calls'      => array('title' => __('Verbindungen')),
	      'nodes'      => array('title' => __('Nodes')),
	      'routes'     => array('title' => __('_ Routen')),
	      'testroute'  => array('title' => __('_ Routing-Test'))
	   )
	),
	##################################################################
	'logout'           => array('title' => __('Logout'),
	                            'icon'  => 'crystal-svg/%s/act/exit.png',
	   'sub' => array(
	      'logout'     => array('title' => __('Logout'))
	   )
	)
	##################################################################
	
);


?>