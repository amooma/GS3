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

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

?>


<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Ein-/Ausloggen am Telefon'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>*0 <i><?php echo __('Durchwahl'); ?></i></code></td>
	<td style="width:420px;">
		<?php echo __('Benutzer am Endger&auml;t einloggen. Hierzu wird die Eingabe der PIN-Nummer abgefragt und mit der "#"-Taste best&auml;tigt.'); ?>
	</td>
</tr>
<tr>
	<td><code>*0*</code></td>
	<td>
		<?php echo __('Benutzer am Endger&auml;t ausloggen'); ?>
	</td>
</tr>
</tbody>
</table>


<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Rufumleitung'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>*2</code></td>
	<td style="width:420px;">
		<?php echo __('Rufumleitung f&uuml;r Anrufe von intern und extern im Fall &quot;immer&quot; auf die Std.-Nummer aktivieren (zur Funktion muss im Men&uuml;punkt "Rufumleitung" eine Std.-Nr. angegeben werden!).'); ?>
	</td>
</tr>
<tr>
	<td><code>*2 <i><?php echo __('Nummer'); ?></i></code></td>
	<td>
		<?php echo __('Tempor&auml;re Rufumleitung f&uuml;r Anrufe von intern und extern im Fall &quot;immer&quot; auf die angegebene Nummer aktivieren'); ?>
	</td>
</tr>
<tr>
	<td><code>*2*</code></td>
	<td>
		<?php echo __('Rufumleitung f&uuml;r Anrufe von intern und extern im Fall &quot;immer&quot; deaktivieren'); ?>
	</td>
</tr>

<tr>
	<td style="width:140px;"><code>*30</code></td>
	<td style="width:420px;">
		<?php echo __('Rufumleitung f&uuml;r Anrufe von intern und extern im Fall &quot;immer&quot; auf den Anrufbeantworter aktivieren.'); ?>
	</td>
</tr>

<tr>
	<td style="width:140px;"><code>*90</code></td>
	<td style="width:420px;">
		<?php echo __('Rufumleitung f&uuml;r Anrufe von intern im Fall "immer" auf die Std.-Nummer aktivieren (zur Funktion muss im Men&uuml;punkt "Rufumleitung" eine Std.-Nr. angegeben werden!).'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*90 <i><?php echo __('Nummer'); ?></i></code></td>
	<td style="width:420px;">
		<?php echo __('Tempor&auml;re Rufumleitung f&uuml;r Anrufe von intern im Fall "immer" auf die angegebene Nummer aktivieren'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*90*</code></td>
	<td style="width:420px;">
		<?php echo __('Rufumleitung f&uuml;r Anrufe von intern im Fall &quot;immer&quot; deaktivieren'); ?>
	</td>
</tr>

<tr>
	<td style="width:140px;"><code>*91</code></td>
	<td style="width:420px;">
		<?php echo __('Rufumleitung f&uuml;r Anrufe von extern im Fall "immer" auf die Std.-Nummer aktivieren (zur Funktion muss im Men&uuml;punkt "Rufumleitung" eine Std.-Nr. angegeben werden!).'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*91 <i><?php echo __('Nummer'); ?></i></code></td>
	<td style="width:420px;">
		<?php echo __('Tempor&auml;re Rufumleitung f&uuml;r Anrufe von extern im Fall "immer" auf die angegebene Nummer aktivieren'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*91*</code></td>
	<td style="width:420px;">
		<?php echo __('Rufumleitung f&uuml;r Anrufe von extern im Fall &quot;immer&quot; deaktivieren'); ?>
	</td>
</tr>

</tbody>
</table>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Anruf &uuml;bernehmen'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>*81* <i><?php echo __('Durchwahl'); ?></i></code></td>
	<td style="width:420px;">
		<?php echo __('Gezieltes Heranholen (Pick-up) von Anrufen'); ?>
	</td>
</tr>
</tbody>
</table>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Anrufbeantworter abfragen'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>80</code></td>
	<td style="width:420px;">
		<?php echo __('Eigenen Anrufbeantworter abfragen (an snom-Endger&auml;ten durch Bet&auml;tigen der "Retrive"-Taste m&ouml;glich)'); ?>
	</td>
</tr>
<tr>
	<td><code>80 <i><?php echo __('Durchwahl'); ?></i></code></td>
	<td>
		<?php echo __('Anderen Anrufbeantworter abfragen (mit PIN)'); ?>
	</td>
</tr>
</tbody>
</table>


<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Abwesenheitsansage aufzeichnen'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>*99*</code></td>
	<td style="width:420px;">
		<?php echo __('Abwesenheitsansage f&uuml;r Anrufe von intern aufzeichnen'); ?>
	</td>
</tr>
<tr>
	<td><code>*98*</code></td>
	<td>
		<?php echo __('Abwesenheitsansage f&uuml;r Anrufe von extern aufzeichnen'); ?>
	</td>
</tr>
</tbody>
</table>


<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Privatgespr&auml;che'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>*7* <i><?php echo __('Nummer'); ?></i></code></td>
	<td style="width:420px;">
		<?php echo __('Privatgespr&auml;ch f&uuml;hren'); ?>
	</td>
</tr>
</tbody>
</table>


<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Warteschlangen'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>*5 <i><?php echo __('Durchwahl'); ?></i></code></td>
	<td style="width:420px;">
		<?php echo __('Einloggen in die Warteschlange mit angegebenen Durchwahl'); ?>
	</td>
</tr>
<tr>
	<td><code>*5 <i><?php echo __('Durchwahl'); ?></i> *</code></td>
	<td>
		<?php echo __('Ausloggen aus der Warteschlange mit angegebenen Durchwahl'); ?>
	</td>
</tr>
<tr>
	<td><code>*5*</code></td>
	<td>
		<?php echo __('Ausloggen aus allen Warteschlangen'); ?>
	</td>
</tr>
</tbody>
</table>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Agenten'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>*6 <i><?php echo __('Agentennummer'); ?></i></code></td>
	<td style="width:420px;">
		<?php echo __('Direktes Einloggen eines Agenten. Hierzu wird die Eingabe der PIN-Nummer abgefragt und mit der "#"-Taste best&auml;tigt.'); ?>
	</td>
</tr>
<tr>
	<td><code>*6 </code></td>
	<td>
		<?php echo __('Einloggen eines Agenten. Hierzu wird die Eingabe der Agentennummer und der PIN-Nummer abgefragt und mit der "#"-Taste best&auml;tigt.'); ?>
	</td>
</tr>
<tr>
	<td><code>*6*</code></td>
	<td>
		<?php echo __('Agenten ausloggen'); ?>
	</td>
</tr>
<tr>
	<td><code>*6#*</code></td>
	<td>
		<?php echo __('Status Pause f&uuml;r Agent aktivieren'); ?>
	</td>
</tr>
<tr>
	<td><code>*6##</code></td>
	<td>
		<?php echo __('Status Pause f&uuml;r Agent deaktivieren'); ?>
	</td>
</tr>
</tbody>
</table>


<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Konferenzen'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>88 <i><?php echo __('Konf.nr.'); ?></i></code></td>
	<td style="width:420px;">
		<?php echo __('Einen Konferenzraum betreten bzw. er&ouml;ffnen (dazu 88 und eine beliebige 3- oder 4-stellige Nummer w&auml;hlen). Ist der Konferenzraum leer, so kann eine PIN gesetzt werden (ohne PIN Taste # dr&uuml;cken). Ist eine PIN vergeben, so muss diese zum Betreten des Konferenzraumes eingegeben werden.'); ?>
	</td>
</tr>
<tr>
	<td><code>88000</code> <?php echo __('oder'); ?> <code>880000</code></td>
	<td>
		<?php echo __('Einen freien Konferenzraum finden. Die Nummer der Konferenz wird beim Betreten angesagt. (Die anderen Teilnehmer w&auml;hlen dann <code>88 <i>Konferenznr.</i></code>, s.o.)'); ?>
	</td>
</tr>
</tbody>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Rufnummernunterdr&uuml;ckung (CLIR)'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>*31</code></td>
	<td style="width:420px;">
		<?php echo __('Unterdr&uuml;ckung der eigenen Rufnummer f&uuml;r den n&auml;chsten Anruf (intern und extern)'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*31*</code></td>
	<td style="width:420px;">
		<?php echo __('Unterdr&uuml;ckung der eigenen Rufnummer deaktivieren (intern und extern)'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*31<i><?php echo __('Rufziel'); ?></code></td>
	<td style="width:420px;">
		<?php echo __('Unterdr&uuml;ckung der eigenen Rufnummer f&uuml;r einen Anruf zu einem beliebigen Rufziel'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*32</code></td>
	<td style="width:420px;">
		<?php echo __('Permanenten Unterdr&uuml;ckung der eigenen Rufnummer f&uuml;r alle internen Rufziele'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*32*</code></td>
	<td style="width:420px;">
		<?php echo __('Unterdr&uuml;ckung der eigenen Rufnummer f&uuml;r alle internen Rufziele deaktivieren'); ?>
	</td>
</tr>

<tr>
	<td style="width:140px;"><code>*33</code></td>
	<td style="width:420px;">
		<?php echo __('Permanenten Unterdr&uuml;ckung der eigenen Rufnummer f&uuml;r alle externen Rufziele'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*33*</code></td>
	<td style="width:420px;">
		<?php echo __('Unterdr&uuml;ckung der eigenen Rufnummer f&uuml;r alle externen Rufziele deaktivieren'); ?>
	</td>
</tr>
</tbody>
</table>
<?php if( GS_OUTBOUNDNUM_SELECTABLE ){ ?>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Angezeigte Rufnummer (CLIP)'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>*34</code></td>
	<td style="width:420px;">
		<?php echo __('Als angezeigte Rufnummer f&uuml;r interne Gespr&auml;che die Standardnummer festlegen'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*34<i><?php echo __('Nummer'); ?></code></td>
	<td style="width:420px;">
		<?php echo __('Als angezeigte Rufnummer f&uuml;r interne Gespr&auml;che die angegebene Nummer festlegen'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*35</code></td>
	<td style="width:420px;">
		<?php echo __('Als angezeigte Rufnummer f&uuml;r externe Gespr&auml;che die Standardnummer festlegen'); ?>
	</td>
</tr>
<tr>
	<td style="width:140px;"><code>*35<i><?php echo __('Nummer'); ?></code></td>
	<td style="width:420px;">
		<?php echo __('Als angezeigte Rufnummer f&uuml;r externe Gespr&auml;che die angegebene Nummer festlegen'); ?>
	</td>
</tr>
</tbody>
</table>
<?php } ?>
</table>

