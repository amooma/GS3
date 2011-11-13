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
include_once( GS_DIR .'inc/mb_str_pad.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";




function _pretty_gs_const_name( $str )
{
	return mb_convert_case(str_replace('_',' ', $str), MB_CASE_TITLE);
}

$cs = array(
	'Canonization' => array(
		'CANONIZE_INTL_PREFIX',
		'CANONIZE_COUNTRY_CODE',
		'CANONIZE_NATL_PREFIX_INTL',
		'CANONIZE_NATL_PREFIX',
		'CANONIZE_AREA_CODE',
		'CANONIZE_LOCAL_BRANCH',
		'CANONIZE_OUTBOUND',
		'CANONIZE_SPECIAL',
		'CANONIZE_CBC_PREFIX'
	),
	'DB Master' => array(
		'DB_MASTER_HOST',
		'DB_MASTER_USER',
		//'DB_MASTER_PWD',
		'DB_MASTER_DB',
		'DB_MASTER_TRANSACTIONS'
	),
	'DB Slave' => array(
		'DB_SLAVE_HOST',
		'DB_SLAVE_USER',
		'DB_SLAVE_DB',
		//'DB_SLAVE_PWD'
	),
	'Dialplan' => array(
		'DP_DIALTIMEOUT_IN',
		'DP_PRV_CALL_PREFIX'
	),
	'External Numbers' => array(
		'EXTERNAL_NUMBERS_BACKEND',
		'EXTERNAL_NUMBERS_LDAP_PROP'
	),
	'Fax' => array(
		'FAX_ENABLED',
		'FAX_PREFIX',
		'FAX_TSI',
		'FAX_TSI_PREFIX',
		'FAX_HYLAFAX_HOST',
		'FAX_HYLAFAX_PORT',
		'FAX_HYLAFAX_ADMIN',
		'FAX_HYLAFAX_PASS'
	),
	'GUI' => array(
		'GUI_AUTH_METHOD',
		'GUI_SUDO_ADMINS',
		'GUI_SUDO_EXTENDED',
		'GUI_NUM_RESULTS',
		'GUI_MON_PEERS_ENABLED',
		'GUI_MON_NOQUEUEBLUE',
		'GUI_QUEUE_SHOW_NUM_CALLS',
		'GUI_QUEUE_INFO_FROM_DB'
	),
	'Intl' => array(
		'INTL_LANG',
		'INTL_USE_GETTEXT',
		'INTL_ASTERISK_LANG'
	),
	'LDAP' => array(
		'LDAP_HOST',
		'LDAP_PORT',
		'LDAP_PROTOCOL',
		'LDAP_SSL',
		'LDAP_BINDDN',
		//'LDAP_PWD',
		'LDAP_SEARCHBASE'
	),
	'LDAP Properties' => array(
		'LDAP_PROP_UID',
		'LDAP_PROP_USER',
		'LDAP_PROP_FIRSTNAME',
		'LDAP_PROP_LASTNAME',
		'LDAP_PROP_PHONE'
	),
	'Logging' => array(
		'LOG_FILE',
		'LOG_LEVEL',
		'LOG_GMT'
	),
	'Misc' => array(
		'CALL_INIT_FROM_NET',
		'CC_TIMEOUT',
		'MONITOR_FROM_NET',
		'NOBODY_CID_NAME',
		'NOBODY_EXTEN_PATTERN',
		'USERCOMMENT_OFFTIME'
	),
	'Phonebooks' => array(
		'PB_IMPORTED_ENABLED',
		'PB_IMPORTED_ORDER',
		'PB_IMPORTED_TITLE',
		'PB_INTERNAL_TITLE',
		'PB_PRIVATE_TITLE'
	),
	'Provisioning' => array(
		'PROV_SCHEME',
		'PROV_HOST',
		'PROV_PORT',
		'PROV_PATH',
		'PROV_AUTO_ADD_PHONE',
		'PROV_AUTO_ADD_PHONE_HOST',
		'PROV_DIAL_LOG_LIFE'
	),
	'Provisioning Snom' => array(
		'SNOM_PROV_ENABLED',
		'SNOM_PROV_HTTP_USER',
		//'SNOM_PROV_HTTP_PASS',
		'SNOM_PROV_PB_NUM_RESULTS',
		'SNOM_PROV_FW_UPDATE',
		'SNOM_PROV_FW_BETA',
		'SNOM_PROV_FW_6TO7'
	),
	'Provisioning Aastra' => array(
		'AASTRA_PROV_ENABLED'
	)
);



?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="save" />

<?php
echo 'Ver&auml;nderungen sind momentan nicht m&ouml;glich.<br />',"\n";

echo '<table cellspacing="1" style="margin-left:4em;">' ,"\n";
echo '<tbody>' ,"\n";
foreach ($cs as $catkey => $cat) {
	//echo '<fieldset>' ,"\n";
	//echo '<legend>', _pretty_gs_const_name($catkey) ,'</legend>' ,"\n";
	
	echo '<tr>' ,"\n";
	echo '<th colspan="2" class="transp"><br /><h3>', $catkey ,'</h3></th>' ,"\n";
	echo '</tr>' ,"\n";
	
	foreach ($cat as $key) {
		echo '<tr>' ,"\n";
		echo '<td><label for="ipt-GS_', htmlEnt($key) ,'">', _pretty_gs_const_name($key) ,'</label></td>' ,"\n";
		echo '<td class="transp">';
		$val = gs_get_conf('GS_'.$key);
		$type = getType($val);
		switch ($type) {  // FIXME
			case 'boolean':
				echo '<input type="checkbox" name="GS_', htmlEnt($key) ,'" id="ipt-GS_', htmlEnt($key) ,'" ', ($val ? 'checked="checked"' : '') ,' disabled="disabled" />';
				break;
			case 'integer':
			case 'string':
			default:
				echo '<input type="text" name="GS_', htmlEnt($key) ,'" id="ipt-GS_', htmlEnt($key) ,'" value="', htmlEnt($val) ,'" size="30" class="_admincfg" disabled="disabled" />';
				break;
		}
		echo '</td>' ,"\n";
		echo '</tr>' ,"\n";
	}
	
	//echo '</fieldset>' ,"\n";
}
echo '</tbody>' ,"\n";
echo '</table>' ,"\n";

echo '<br />', "\n";
echo '<input type="submit" value="', __('Speichern') ,'" disabled="disabled" />',"\n";

?>

</form>

<br />
<br />


<?php
/*
$cs2 = get_defined_constants();
$gscs = array();
foreach ($cs2 as $k => $v) {
	if (subStr($k,0,3) === 'GS_') {
		$gscs[subStr($k,3)] = $v;
	}
}
kSort($gscs);
echo "<pre>\n";
foreach ($gscs as $k => $v) {
	$found = false;
	foreach ($cs as $cat) {
		foreach ($cat as $key) {
			//if ($key == $k) continue;
			if ($k == $key) $found = true;
		}
	}
	if (! $found)
		echo "$k = $v\n";
}
echo "</pre>\n";
*/
?>
