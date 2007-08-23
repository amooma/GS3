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
	      'private'    => array('title' => __('Pers&ouml;nlich'))
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
	      'routes'     => array('title' => __('$$$ Routen'))
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


/*
<ul>
	<li class="leaf"><a href="?m=home" title="">Home</a></li>
	<li class="expanded"><a href="?m=pb&amp;s=org" title="" class="active">Telefonbuch</a>
		<ul class="menu">
			<li class="leaf"><a href="?m=pb&amp;s=contacts" title="">Kontakte</a></li>
			<li class="leaf"><a href="?m=pb&amp;s=local" title="">Kontakte lokal</a></li>
			<li class="leaf"><a href="?m=pb&amp;s=private" title="">Pers&ouml;nlich</a></li>
		</ul>
	</li>
	<li class="collapsed"><a href="?m=vm" title="" class="">Voicemail</a>
		<ul class="menu">
			<li class="leaf"><a href="?m=vm&amp;s=msg" title="">Nachrichten</a></li>
			<li class="leaf"><a href="?m=vm&amp;s=pref" title="">Einstellungen</a></li>
		</ul>
	</li>
	<li class="collapsed"><a href="?m=calls" title="" class="">Anruflisten</a>
		<ul class="menu">
			<li class="leaf"><a href="?m=calls&amp;s=dialed" title="">gew&auml;hlte</a></li>
			<li class="leaf"><a href="?m=calls&amp;s=missed" title="">verpasste</a></li>
			<li class="leaf"><a href="?m=calls&amp;s=accepted" title="">angenommene</a></li>
		</ul>
	</li>
	<li class="collapsed"><a href="?m=fw" title="">Rufumleitung</a></li>
	<li class="collapsed"><a href="?m=misc" title="">Verschiedenes</a></li>
	<li class="collapsed"><a href="?m=reporting" title="" class="">Reporting</a>
		<ul class="menu">
			<li class="leaf"><a href="?m=reporting&amp;s=mon&amp;s=group" title="">Live-Monitor</a></li>
			<li class="leaf"><a href="?m=reporting&amp;s=stats&amp;s=stats" title="">Live-Statistik</a></li>
			<li class="leaf"><a href="?m=reporting&amp;s=reports&amp;s=stats" title="">Auswertung</a></li>
		</ul>
	</li>
	<li class="collapsed"><a href="?m=private" title="">Privatgespr&auml;che</a></li>
	<li class="collapsed"><a href="?m=help" title="" class="">Hilfe</a>
		<ul class="menu">
			<li class="leaf"><a href="?m=help&amp;s=pbx" title="">Telefonanlage</a></li>
			<li class="leaf"><a href="?m=help&amp;s=snom" title="">Snom</a></li>
		</ul>
	</li>
</ul>
*/


?>