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
require_once( GS_DIR .'inc/group-fns.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

$user_id     = (int)@$_SESSION['sudo_user']['info']['id'];
$user_groups = gs_group_members_groups_get(array($user_id), 'user');

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
		<?php echo __('Einloggen an einem Telefon. Dabei wird die Eingabe der PIN-Nummer verlangt.'); ?>
	</td>
</tr>
<tr>
	<td><code>*0*</code></td>
	<td>
		<?php echo __('Ausloggen'); ?>
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
		<?php echo __('Rufumleitung f&uuml;r Anrufe von intern und extern im Fall &quot;immer&quot; auf die Std.-Nummer aktivieren. (Dazu m&uuml;ssen Sie im Men&uuml;punkt &quot;Rufumleitung&quot; eine Std.-Nr. angegeben haben!)'); ?>
	</td>
</tr>
<tr>
	<td><code>*2 <i><?php echo __('Nummer'); ?></i></code></td>
	<td>
		<?php echo __('Tempor&auml;re Rufumleitung f&uuml;r Anrufe von intern und extern im Fall &quot;immer&quot; auf die angegebene Nummer programmieren'); ?>
	</td>
</tr>
<tr>
	<td><code>*21</code></td>
	<td>
		<?php echo __('Rufumleitung f&uuml;r Anrufe von intern und extern im Fall &quot;immer&quot; auf den Anrufbeantworter aktivieren'); ?>
	</td>
</tr>
<tr>
	<td><code>*22</code></td>
	<td>
		<?php echo __('Rufumleitung f&uuml;r Anrufe von intern und extern im Fall &quot;immer&quot; auf die Ansage ohne Anrufbeantworter aktivieren'); ?>
	</td>
</tr>
<tr>
	<td><code>*2*</code></td>
	<td>
		<?php echo __('Rufumleitung f&uuml;r Anrufe von intern und extern im Fall &quot;immer&quot; deaktivieren'); ?>
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
		<?php echo __('Eigenen Anrufbeantworter abfragen (Am Snom-Telefon kann daf&uuml;r auch die &quot;Retrieve&quot;-Taste gedr&uuml;ckt werden.)'); ?>
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

<?php if ( count(gs_group_permissions_get($user_groups, 'private_call')) > 0 ) { ?>
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
<?php } ?>

<?php if ( count(gs_group_permissions_get($user_groups, 'wakeup_call')) > 0 ) { ?>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Weckruf'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><code>*4</code></td>
	<td style="width:420px;">
		<?php echo __('Einrichten eines Weckrufes'); ?>
	</td>
</tr>
</tbody>
</table>
<?php } ?>


<?php if ( count(gs_group_permissions_get($user_groups, 'login_queues')) > 0 ) { ?>
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
		<?php echo __('Einloggen in die Warteschlange mit der angegebenen Durchwahl'); ?>
	</td>
</tr>
<tr>
	<td><code>*5 <i><?php echo __('Durchwahl'); ?></i> *</code></td>
	<td>
		<?php echo __('Ausloggen aus der Warteschlange mit der angegebenen Durchwahl'); ?>
	</td>
</tr>
<tr>
	<td><code>*5*</code></td>
	<td>
		<?php echo __('Ausloggen aus allen Warteschlangen'); ?>
	</td>
</tr>
<tr>
	<td><code>*5</code></td>
	<td>
		<?php echo __('Einloggen in alle Warteschlangen'); ?>
	</td>
</tr>
</tbody>
</table>
<?php } ?>


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
		<?php echo __('Einen Konferenzraum (nach 88 eine 3- oder 4-stellige Nummer) betreten bzw. er&ouml;ffnen. Wenn die Konferenz leer ist, kann eine PIN-Nr. gesetzt werden (f&uuml;r keine PIN # dr&uuml;cken). Ansonsten muss eine ggf. f&uuml;r diesen Raum gesetzte PIN eingegeben werden.'); ?>
	</td>
</tr>
<tr>
	<td><code>88000</code> <?php echo __('oder'); ?> <code>880000</code></td>
	<td>
		<?php echo __('Einen freien Konferenzraum finden. Die Nummer der Konferenz wird beim Betreten angesagt. (Die anderen Teilnehmer w&auml;hlen dann <code>88 <i>Konferenznr.</i></code>, s.o.)'); ?>
	</td>
</tr>
</tbody>
</table>
