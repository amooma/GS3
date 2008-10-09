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
$can_continue = true;

$current_dhcp_daemon_start = (trim(gs_keyval_get('dhcp_daemon_start')) === 'yes');


$action = @$_REQUEST['action'];

if ($action === 'save') {
	
	$new_dhcp_daemon_start = (bool)@$_REQUEST['dhcpd'];
	gs_keyval_set('dhcp_daemon_start', ($new_dhcp_daemon_start ? 'yes':'no'));
	
	$err=0; $out=array();
	@exec( 'sudo /etc/init.d/dhcp3-server '. ($new_dhcp_daemon_start ? 'restart' : 'stop') .' 2>>/dev/null' );
	
	$current_dhcp_daemon_start = $new_dhcp_daemon_start;
	
	gs_keyval_set('setup_show', 'password');
	
}


?>

<script type="text/javascript" src="<?php echo GS_URL_PATH; ?>js/unsaved-changes.js"></script>

<div style="width:550px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">
<h1><?php echo __('DHCP-Server'); ?></h1>
<p>
<?php
	switch ($GS_INSTALLATION_TYPE) {
		case 'gpbx':
			echo __('F&uuml;r das automatische Einrichten (Provisioning) der Telefone wird ein DHCP-Server ben&ouml;tigt. Sie k&ouml;nnen entweder den DHCP-Server auf der GPBX verwenden oder einen bereits vorhandenen DHCP-Server entsprechend konfigurieren.');
			break;
		default:
			echo __('F&uuml;r das automatische Einrichten (Provisioning) der Telefone wird ein DHCP-Server ben&ouml;tigt. Sie k&ouml;nnen entweder den DHCP-Server auf diesem System verwenden oder einen bereits vorhandenen DHCP-Server entsprechend konfigurieren.');
	}
?>
</p>
<hr />

<form method="post" action="<?php echo GS_URL_PATH ,'setup/?step=dhcpd'; ?>">
<input type="hidden" name="action" value="save" />
<table cellspacing="8" align="center" style="margin:0 auto;">
<tbody>
	<tr>
		<td class="transp r"><input type="radio" name="dhcpd" id="ipt-dhcpd-1" value="1"<?php if ($current_dhcp_daemon_start) echo ' checked="checked"'; ?> /></td>
		<td class="transp"><label for="ipt-dhcpd-1"><?php echo __('Mitgelieferten DHCP-Server aktivieren.<br />(empfohlen)'); ?></label></td>
	</tr>
	<tr>
		<td class="transp r"><input type="radio" name="dhcpd" id="ipt-dhcpd-0" value="0"<?php if (! $current_dhcp_daemon_start) echo ' checked="checked"'; ?> /></td>
		<td class="transp"><label for="ipt-dhcpd-0"><?php echo __('Vorhandenen DHCP-Server verwenden.<br />(nur f&uuml;r erfahrene Anwender, Speichern f&uuml;r Beispiel-Konfiguration)'); ?></label></td>
	</tr>
	<tr>
		<td class="transp">&nbsp;</td>
		<td class="transp"><br />
			<input type="reset" value="<?php echo __('Verwerfen'); ?>" />
			<input type="submit" value="<?php echo __('Speichern'); ?>" />
		</td>
	</tr>
</tbody>
</table>
</form>

<?php


if ($action === 'save' && ! $new_dhcp_daemon_start) {
	$dhcpdconf = @gs_file_get_contents( '/etc/dhcp3/dhcpd.conf' );
	$dhcpdconf = preg_replace('/\r\n?/', "\n", $dhcpdconf);
	$dhcpdconf = trim(preg_replace('/^[ \t]*#[^\n]*\n?/m', '', $dhcpdconf));
	$dhcpdconf = preg_replace('/\n{3,}/', "\n\n", $dhcpdconf);
?>
<hr />
<p>
	<?php echo __('Bitte konfigurieren Sie Ihren vorhandenen DHCP-Server wie folgt (Beispiel f&uuml;r den ISC DHCPd 3)'); ?>
</p>

<textarea cols="50" rows="60" readonly="readonly" style="width:99%; padding:2px 0 2px 4px;"><?php
	echo htmlEnt($dhcpdconf), "\n";
	unset($dhcpdconf);
?>
</textarea>

<?php
}


?>

<hr />

<?php

echo '<div class="fl"><a href="', GS_URL_PATH ,'setup/?step=network">', __('zur&uuml;ck') ,'</a></div>' ,"\n";
echo '<div class="fr">';
if ($can_continue)
	echo '<a href="', GS_URL_PATH ,'setup/?step=phones-scan"><big>', __('weiter') ,'</big></a>';
else
	echo '<span style="color:#999;">', __('weiter') ,'</span>';
echo '</div>' ,"\n";
echo '<br class="nofloat" />' ,"\n";

?>
</div>

<script type="text/javascript">
try{
	gs_prevent_unsaved_changes( '<?php echo __('Sie haben noch nicht alle \u00C4nderungen gespeichert! Sie sollten zuerst speichern oder die \u00C4nderungen verwerfen.'); ?>' );
}catch(e){}
</script>
