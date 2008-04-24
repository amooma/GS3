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

if (trim(gs_keyval_get('setup_show')) === 'autoshow')
	gs_keyval_set('setup_show', 'password');

?>

<div style="width:550px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">
<h1><?php echo __('Fertig'); ?></h1>
<p>
<?php
	switch ($GS_INSTALLATION_TYPE) {
		case 'gpbx':
			echo __('Die grundlegende Netzwerk-Konfiguration der GPBX ist jetzt abgeschlossen. Bitte nehmen Sie die weiteren Einstellungen im Administrator-Bereich der normalen Web-Oberfl&auml;che vor. Nat&uuml;rlich k&ouml;nnen Sie auch sp&auml;ter wieder das Setup aufrufen.');
			break;
		default:
			echo __('Die grundlegende Netzwerk-Konfiguration des Systems ist jetzt abgeschlossen. Bitte nehmen Sie die weiteren Einstellungen im Administrator-Bereich der normalen Web-Oberfl&auml;che vor. Nat&uuml;rlich k&ouml;nnen Sie auch sp&auml;ter wieder das Setup aufrufen.');
	}
?>
</p>
<p align="center">
	<a href="<?php echo GS_URL_PATH; ?>"><?php echo __('zum normalen Web-Interface'); ?></a>
</p>

<br />
<br />
<hr />

<?php

echo '<div class="fl"><a href="', GS_URL_PATH ,'setup/?step=phones-scan">', __('zur&uuml;ck') ,'</a></div>' ,"\n";
echo '<div class="fr">';
echo '</div>' ,"\n";
echo '<br class="nofloat" />' ,"\n";

?>
</div>
