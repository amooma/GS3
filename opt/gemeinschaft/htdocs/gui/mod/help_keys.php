<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 5383 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* Soeren Sprenger <soeren.sprenger@amooma.de>
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

echo __("Gemeinschaft bietet die M&ouml;glichkeit, Tasten an unterst&uuml;tzen Engeräten &uuml;ber diese Benutzeroberfl&auml;che zu Konfigurieren.")."<br>";
echo __("Um Tasten f&uuml;r Ihr Endger&auml;t zu Konfigurieren, folgen Sie einfach den folgenden Schritten, nachdem Sie Links im Men&uuml; auf Tastenbelegung geklickt haben:")."<br><br>";
echo '<h3>' .__("Schritt 1:") .'</h3>';
echo __("W&auml;hlen Sie ihr Telefonmodell aus (1) und dr&uuml;cken Sie auf 'Zeigen' (2)")."<br>";
echo '<img alt="Step1" src="'.GS_URL_PATH.'img/help_keys/step1.jpg"/>';
echo '<br>';
echo '<br>';

echo '<h3>' .__("Schritt 2:") .'</h3>';
echo __("Dannach sehen Sie die folgenden &Uuml;bersicht (oder &auml;hnlich):")."<br>";
echo '<img alt="Step2" src="'.GS_URL_PATH.'img/help_keys/step2.jpg"/>';
?>
<br><br>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Die Bedeutung der einzelnen Spalten:'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"><?php echo __('Taste'); ?></td>
	<td style="width:420px;">
		<?php echo __('Beschreibt die Taste am Telefon im Bespiel ist F001 die oberste Taste am Display vom Siemens Openstage 60.'); ?>
	</td>
</tr>
<tr>
	<td>:=</td>
	<td>
		<?php echo __('Zeigt an ob die Taste Aktiviert ist oder nicht, Sie k&ouml;nnen die Taste nur definieren, wenn Sie diese vorher mit dem Haken in dieser Box aktivieren.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Nummer/Daten'); ?></td>
	<td>
		<?php echo __('In dieses Feld werden Funktionsspezifische Daten eingetragen, z.B.: die Zielnummer bei einer Ziehlwahl. Mehr dazu erfahren Sie im n&auml;chsten Schritt.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Beschriftung'); ?></td>
	<td>
		<?php echo __('Hier muss der Text eingegeben werden, wie er am Telefon neben der Taste erscheinen soll.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Gesch&uuml;tzt?'); ?></td>
	<td>
		<?php echo __('Wenn in dieser Spalte ein kleines Schloss angezeigt wird (im Beispiel in den letzten beiden Zeilen) dann ist diese Taste vom Administrator gegen eine Bearbeitung gesch&uuml;tzt. Sie k&ouml;nnen diese Taste nicht Konfigurieren.'); ?>
	</td>
</tr>
</tbody>
</table>
<br><br>
<?php
echo '<h3>' .__("Schritt 3:") .'</h3>';
echo __("Aktivieren Sie die Taste durch anklicken des Hakens in der 2. Spalte (1)")."<br>";
echo __("W&auml;hlen Sie aus der Liste (2) die gew&uuml;nschte Tastenfunktion (3) aus:")."<br>";
echo '<img alt="Step3" src="'.GS_URL_PATH.'img/help_keys/step3.jpg"/>';
echo '<br>';
echo __("Die Tastenfunktionen sind stark vom Benutzten Endger&auml;t ab. Hier eine Liste f&uuml;r das Openstage 60, welches die Tastenfunktionen der meisten Telefone abdecken sollte:")."<br>";
?>
<br><br>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th colspan="2"><?php echo __('Liste der einzelnen Tastenfunktionen:'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:140px;"> <?php echo __('-erben-'); ?></td>
	<td style="width:420px;">
		<?php echo __('Wenn "-erben-" aktiviert ist, wird die vom Administrator voreinstellte Funktion benutzt.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Zielwahl'); ?></td>
	<td>
		<?php echo __('Wenn Sie eine Zielwahl auf eine Rufnummer definiert haben, dann k&ouml;nnen Sie die Nummer durch einen Tastendruck anrufen.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Nebenstelle/BLF'); ?></td>
	<td>
		<?php echo __('Aktiviert die Besetztlampenfeldfunktion f&uuml;r eine Bestimmte Nebenstelle. Mit der Besetztlampenfeldfunktion k&ouml;nnen Sie den Status der Nebenstelle sehen (Besetzt=Leuchten, Klingeln=Blinken). Desweiteren k&ouml;nnen Sie die Nebenstelle durch einen druck auf diese Taste Anrufen oder eine Ruf&uuml;bernahme durchf&uuml;hren, wenn Sie die Taste dr&uuml;cken w&auml;hrend die Besetztlampe blinkt.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Rufton aus'); ?></td>
	<td>
		<?php echo __('Schaltet den Rufton der Endger&auml;tes an oder aus. Wenn der Rufton aus ist, leuchtet die Lampe der Taste.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Halten'); ?></td>
	<td>
		<?php echo __('H&auml;lt das aktuell geführte Gespr&auml;ch. Der Anrufer h&ouml;rt Wartemusik.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Makeln'); ?></td>
	<td>
		<?php echo __('Mit dieser Funktion kann man zwischen Anrufern hin und her schalten, w&auml;hrend man sich in einer &Uuml;bergabe befindet.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('&Uuml;bergabe'); ?></td>
	<td>
		<?php echo __('Mit dieser Taste kann man eine Anruf&uuml;bergabe (mit R&uuml;ckfrage) einleiten.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('&Uuml;berg. v. Melden'); ?></td>
	<td>
		<?php echo __('Mit dieser Taste kann man eine Anruf&uuml;bergabe (ohne R&uuml;ckfrage) einleiten.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Weiterleiten'); ?></td>
	<td>
		<?php echo __('Mit dieser Funktion k&ouml;nnen sie den Anrufer zu einem Bestimmten Ziel weiterleiten.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Shift'); ?></td>
	<td>
		<?php echo __('Mit der Shift-Funktion hat man die M&ouml;glichkeit Tasten doppelt zu belegen. Die Funktion schaltet zwischen den Ebenen hin und her. Die eigentlichen Tastenfunktionen kann man dann in "Shift-Ebene" festlegen'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Kopfh&ouml;rer'); ?></td>
	<td>
		<?php echo __('Mit dieser Taste k&ouml;nnen Sie das gespr&auml;ch zwischen Kopfh&ouml;rer und Lautsprecher umschalten.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Nicht st&ouml;ren'); ?></td>
	<td>
		<?php echo __('Sie k&ouml;nnen diese Funktion aktvieren, falls Sie z.B.: im Kundengespr&auml;ch sind und nicht gest&ouml;rt werden wollen. Das Telefon wird dann nicht Klingeln und auch nicht Anzeigen wenn es angerufen wird.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Rufanahmegrp.'); ?></td>
	<td>
		<?php echo __('Wenn Sie in einer Rufanahmegruppe sind, k&ouml;nnen Sie ein ankommendes Gespr&auml;ch mit dieser Taste aufnehmen.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Kurzwahl'); ?></td>
	<td>
		<?php echo __('Wenn Sie eine Kurzwahl auf eine Rufnummer definiert haben, dann k&ouml;nnen Sie die Nummer durch einen Tastendruck anrufen.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('R&uuml;ckfrage'); ?></td>
	<td>
		<?php echo __('Setzt den Anrufer auf "Halten" und Ruft die Nummer die bei Nummer/Daten eingegeben wurden für eine R&uuml;ckfrage an.'); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Fn.Schalter'); ?></td>
	<td>
		<?php echo __('Mit dem Funktionsschalter kann man bestimmte Telefoniefunktionen aktivieren oder Deaktivieren (z.B.: die Rufumleitung). W&auml;hlen Sie einfach die gew&uuml;nschte Funktion aus dem Hilfe-Dialog aus. '); ?>
	</td>
</tr>
<tr>
	<td><?php echo __('Appl. aufrufen'); ?></td>
	<td>
		<?php echo __('Erm&ouml;glicht das Belegen einer Appliaktion auf eine Taste, damit ist es m&ouml;glich, z.B.: das Telefonbuch zu starten. W&auml;hlen Sie einfach die gew&uuml;nschte Applikation aus dem Hilfe-Dialog aus.'); ?>
	</td>
</tr>
</tbody>
</table>
<br><br>
<?php
echo '<br>';
echo '<h3>' .__("Schritt 4:") .'</h3>';
echo __("Bei manchen Tastenfunktionen bietet das Programm eine Hilfestellung zum ausf&uuml;llen der 3.Spalte 'Nummern/Daten' an, wenn man - wie im Beispiel - 'Nebenstelle/BLF' ausw&auml;hlt, dann kommt dieser Dialog:")."<br>";
echo '<img alt="Step4" src="'.GS_URL_PATH.'img/help_keys/step4.jpg"/>';
echo '<br>';
echo __("In diesem Dialog k&ouml;nnen Sie die Nummer (1) die Sie im Besetztlampenfeld anzeigen wollen eintragen und den Dialog mit dem Haken (2) best&auml;tigen.")."<br>";
echo '<br>';
echo '<br>';


echo '<h3>' .__("Schritt 5:") .'</h3>';
echo __("Dannach sehen Sie, wie der Hilfedialog die Daten in Spalte 3 eingetragen hat, Sie m&uuml;ssen an diesem Text nichts mehr &auml;ndern. Sie k&ouml;nnen nun noch eine Beschriftung (1) eingeben, die die Taste am Telefon haben soll:")."<br>";
echo '<img alt="Step5" src="'.GS_URL_PATH.'img/help_keys/step5.jpg"/>';
echo '<br>';
echo '<br>';


echo '<h3>' .__("Schritt 6:") .'</h3>';
echo __("Sie können Schritt 3-5 f&uuml;r jede Taste, die Sie definieren wollen, wiederholen. Vergessen Sie aber nicht, am Ende auf 'Speichern und Telefon aktualisieren' (1) zu klicken. Wenn Sie dies getan haben, sollte die &Auml;nderung ca. eine Minute sp&auml;ter auf Ihrem Telefon zu sehen sein.")."<br>";
echo '<img alt="Step6" src="'.GS_URL_PATH.'img/help_keys/step6.jpg"/>';
echo '<br>';
echo '<br>'

?>