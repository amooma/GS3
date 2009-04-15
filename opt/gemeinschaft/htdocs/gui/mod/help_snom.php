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

?>

<div class="fr">
<img alt="Snom 360" src="<?php echo GS_URL_PATH; ?>img/snom/snom360.jpg" />
</div>

<?php

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

?>

<p style="max-width:550px; font-size:0.92em; line-height:1.2em;">
<?php echo __('snom-Telefone weisen abh&auml;ngig vom Ger&auml;te-Typ konfigurierbare Funktionstasten auf, deren Funktionen unten aufgef&uuml;hrt sind. Bitte entnehmen Sie eine ausf&uuml;hrliche Beschreibung der Tasten und Funktionen dem Handbuch zum snom-Telefon.'), ' '; ?>

<br><a target="_blank" href="http://wiki.snom.com/Snom320/Documentation"><?php echo __('Dokumentation snom 320'); ?></a>
/ <a target="_blank" href="http://wiki.snom.com/Snom360/Documentation"><?php echo __('Dokumentation snom 360'); ?></a>
/ <a target="_blank" href="http://wiki.snom.com/Snom370/Documentation"><?php echo __('Dokumentation snom 370'); ?></a>
</p>

<table cellspacing="1">
<thead>
<tr>
	<th colspan="2"><?php echo __('Tastenbelegung snom-Telefon'); ?></th>
</tr>
</thead>
<tbody>

<tr class="odd">
	<td class="transp" style="width:80px;">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_soft.png" /><br />
		<small><?php echo __('4 Softkeys unter dem Bildschirm'); ?></small>
	</td>
	<td style="width:450px;">
		<?php echo __('Diese Tasten werden mit kontextabh&auml;ngigen Funktionen belegt. Die Bedeutungen werden ggf. am unteren Rand des Displays angezeigt.'); ?>
	</td>
</tr>

<tr class="even">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_volume.png" /><br />
		<small>- Volume +</small>
	</td>
	<td>
		<?php echo __('<b>Lautst&auml;rke</b> des H&ouml;rers oder Lautsprechers -/+'); ?>
	</td>
</tr>

<tr class="odd">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_mute.png" /><br />
		<small>Mute</small>
	</td>
	<td>
		<?php echo __('<b>Stummschaltung</b> des Mikrofons ein/aus'); ?>
	</td>
</tr>

<tr class="even">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_speaker.png" /><br />
		<small>Speaker</small>
	</td>
	<td>
		<?php echo __('<b>Lauth&ouml;ren</b> ein/aus'); ?>
	</td>
</tr>

<tr class="odd">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_cancel.png" /><br />
		<small><?php echo __('Abbrechen'); ?></small>
	</td>
	<td>
		<?php echo __('<b>Abbrechen</b><br /> Gespr&auml;ch beenden, Gespr&auml;ch abweisen'); ?>
	</td>
</tr>

<tr class="even">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_tick.png" /><br />
		<small><?php echo __('OK'); ?></small>
	</td>
	<td>
		<?php echo __('<b>Best&auml;tigen</b><br /> W&auml;hlen, Gespr&auml;ch annehmen'); ?>
	</td>
</tr>

<tr class="odd">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_retrieve.gif" /><br />
		<small>Retrieve</small>
	</td>
	<td>
		<?php echo __('<b>Anrufbeantworter</b> abfragen<br /> Hat der Anrufbeantworter ein Gespr&auml;ch f&uuml;r Sie aufgezeichnet, wird dies durch Blinken der Message-LED signalisiert. Die Anzahl alter und neuer Nachrichten wird zus&auml;tzlich auch auf dem Display angezeigt.'); ?>
	</td>
</tr>

<tr class="even">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_redial.gif" /><br />
		<small>Redial</small>
	</td>
	<td>
		<?php echo __('<b>Wahlwiederholung</b><br /> Durch Dr&uuml;cken dieser Taste k&ouml;nnen Sie die letzten angerufenen Nummern erneut anrufen und sehen angenommene und verpasste Anrufe. W&auml;hlen Sie zun&auml;chst mit den Pfeiltasten die Nummer aus und dr&uuml;cken danach die OK-Taste.'); ?>
	</td>
</tr>

<tr class="odd">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_adrb.gif" /><br />
		<small>Directory</small>
	</td>
	<td>
		<?php echo __('<b>Telefonbuch</b> (Firma / pers&ouml;nlich)<br /> z.B. 63437 f&uuml;r MEIER dr&uuml;cken (&auml;hnlich &quot;T9&quot;).'); ?>
	</td>
</tr>

<tr class="even">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_transfer.gif" /><br />
		<small>Transfer</small>
	</td>
	<td>
		<?php echo __('<b>Vermitteln</b><br /> Es gibt zwei Wege, wie Sie Ihren Gespr&auml;chspartner mit einer anderen Nummer verbinden k&ouml;nnen:<br /> <i>Direktes Verbinden</i>: Wenn Sie w&auml;hrend eines Gespr&auml;chs die Transfer-Taste dr&uuml;cken, wird Ihr Gespr&auml;chsparter auf Halten gesetzt. Geben Sie danach die Nummer an, mit der Sie den Anrufer verbinden m&ouml;chten. Durch Dr&uuml;cken der OK-Taste wird der Teilnehmer dann mit der gew&auml;hlten Nummer verbunden.<br /> <i>Verbinden mit R&uuml;ckfrage</i>: Dr&uuml;cken Sie hierzu w&auml;hrend eines Gespr&auml;chs die Hold-Taste um Ihren Gespr&auml;chspartner zu halten und w&auml;hlen dann die Nummer, mit der Sie ihn verbinden m&ouml;chten. Sie k&ouml;nnen jetzt mit dem zweiten Teilnehmer sprechen. Durch Dr&uuml;cken der Transfer-Taste oder durch Auflegen werden die beiden Teilnehmer verbunden.'); ?>
	</td>
</tr>

<tr class="odd">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_hold.gif" /><br />
		<small>Hold</small>
	</td>
	<td>
		<?php echo __('<b>Halten</b> (R&uuml;ckfrage)<br /> Wenn Sie diese Taste w&auml;hrend eines Gespr&auml;chs dr&uuml;cken, wird Ihr Gespr&auml;chspartner auf Halten gesetzt. Danach k&ouml;nnen Sie eine weitere Verbindung zu einem anderen Teilnehmer herstellen. Mit den leuchtenden Leitungstasten k&ouml;nnen Sie zwischen beiden Leitungen hin- und herschalten (Makeln).'); ?>
	</td>
</tr>

<tr class="even">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom/key_dnd.gif" /><br />
		<small>DND</small>
	</td>
	<td>
		<?php echo __('<b>Nicht st&ouml;ren</b> ein/aus<br /> Wenn dieser Modus aktiviert ist, werden keine Anrufe zu Ihrem Telefon durchgestellt. Dabei wird auf dem Display "Ruhe/DND" angezeigt. Auf Ihre ggf. eingestellte Rufweiterleitung wirkt dieser Modus, als w&uuml;rden Sie telefonieren, also besetzt.'); ?>
	</td>
</tr>

<tr class="odd">
	<td class="transp">
		<img alt=" " src="<?php echo GS_URL_PATH; ?>img/snom_fkleft_off.gif" />
	</td>
	<td>
		<?php echo __('<b>12 Leitungstasten</b><br /> Diese Tasten lassen sich mit Rufnummern programmieren.<br /> Im Men&uuml;punkt &quot;Tastenbelegung&quot; k&ouml;nnen Sie Ihre Einstellung ansehen und ver&auml;ndern.'); ?>
	</td>
</tr>

</tbody>
</table>

