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
include_once( GS_DIR .'inc/canonization.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt="" src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$action = @$_REQUEST['action'];
if ($action == 'canonize')
	$number = trim(@$_REQUEST['number']);
else
	$number = '030 1234567';


$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);

?>

<p class="text"><?php echo __('$$$ Hier k&ouml;nnen Sie &uuml;berpr&uuml;fen, wie nach extern gew&auml;hlte Telefonnummern entsprechend Ihrer Einstellungen kanonisiert werden.'); ?></p>

<?php if (! gs_get_conf('GS_CANONIZE_OUTBOUND')) { ?>
<p class="text">(<?php echo __('$$$ Kanonisierung ist nicht aktiviert.'); ?>)</p>
<?php } ?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="canonize" />

<label for="ipt-number"><?php echo __('$$$ Telefonnummer'); ?>:</label><br />
<input type="text" name="number" id="ipt-number" value="<?php echo $number; ?>" size="25" maxlength="30" />

<input type="submit" value="<?php echo __('$$$ Kanonisieren'); ?>" />
</form>
<br />


<?php
$canonical = new CanonicalPhoneNumber( $number );
?>

<table cellspacing="1">
<tbody>

<tr class="even">
	<td><b><?php echo __('$$$ Kanonisch'); ?>:</b></td>
	<td class="pre r"><?php echo $canonical->norm; ?></td>
</tr>

<tr class="odd">
	<td><b><?php echo __('$$$ International'); ?>:</b></td>
	<td class="pre r"><?php echo $canonical->intl; ?></td>
</tr>

<tr class="even">
	<td><b><?php echo __('$$$ National'); ?>:</b></td>
	<td class="pre r"><?php echo $canonical->natl; ?></td>
</tr>

<tr class="odd">
	<td><b><?php echo __('$$$ Innerorts'); ?>:</b></td>
	<td class="pre r"><?php echo $canonical->locl; ?></td>
</tr>

<tr class="even">
	<td><b><?php echo __('$$$ In eigener Telefonanlage?'); ?>:</b></td>
	<td class="pre"><?php echo ($canonical->in_prv_branch ? __('$$$ ja') : __('$$$ nein')); ?></td>
</tr>

<tr class="odd">
	<td><b><?php echo __('$$$ Durchwahl'); ?>:</b></td>
	<td class="pre r"><?php echo $canonical->extn; ?></td>
</tr>

<tr class="even">
	<td><b><?php echo __('$$$ Sondernummer?'); ?>:</b></td>
	<td class="pre"><?php echo ($canonical->is_special ? __('$$$ ja') : __('$$$ nein')); ?></td>
</tr>

<tr class="odd">
	<td><b><?php echo __('$$$ Call-by-Call?'); ?>:</b></td>
	<td class="pre"><?php echo ($canonical->is_call_by_call ? __('$$$ ja') : __('$$$ nein')); ?></td>
</tr>

<tr class="even">
	<td><b><?php echo __('$$$ Ergebnis'); ?>:</b></td>
	<td class="pre r"><?php echo $canonical->dial; ?></td>
</tr>

</tbody>
</table>

<br />
<p>
<?php
switch (@$canonical->errt) {
	case 'empty':
		echo "$$$ Keine Telefonnummer angegeben.";
		break;
	case 'cbc':
		echo "$$$ Der Endanwender soll keine Call-by-Call-Vorwahlen verwenden.";
		break;
	case 'self':
		echo "$$$ Diese Nummer ist innerhalb der eigenen Telefonanlage.";
		break;
	case '':
	default:
		echo sPrintF("$$$ Die Nummer w&uuml;rde als <b>%s</b> gew&auml;hlt.", $canonical->dial);
}
?>
</p>


<br />

<?php
/*
echo "<pre>\n";
print_r($canonical);
echo "</pre>\n";
*/
?>
