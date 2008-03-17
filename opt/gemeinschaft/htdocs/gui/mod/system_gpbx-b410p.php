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
include_once( GS_DIR .'inc/keyval.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

//echo "<br />\n";

echo '<p class="text"><small>', __('&Auml;nderungen ben&ouml;tigen ggf. einen Neustart der Anlage.') ,'</small></p>' ,"\n";


$err=0; $out=array();
@exec('lspci -n 2>>/dev/null', $out, $err);
if ($err !== 0) {
	echo 'Error.<br />' ,"\n";
	return;
}
if (! preg_match('/[ \t]d161:b410/i', implode("\n",$out))) {
	echo __('BRI-ISDN-Karte nicht gefunden.') ,'<br />' ,"\n";
	return;
}


if (@$_REQUEST['action'] === 'save') {
	
	$cards_bri_1_ports_te_ptp  = array();
	$cards_bri_1_ports_te_ptmp = array();
	$ports_protocols = array(1=>'ptp', 2=>'ptp', 3=>'ptp', 4=>'ptp');
	for ($port=1; $port<=4; ++$port) {
		switch (@$_REQUEST['card-1-port-'.$port.'-prot']) {
			case 'ptp' : $ports_protocols[$port] = 'ptp' ; break;
			case 'ptmp': $ports_protocols[$port] = 'ptmp'; break;
		}
	}
	foreach ($ports_protocols as $port => $protocol) {
		if ($protocol === 'ptp')
			$cards_bri_1_ports_te_ptp[] = $port;
		if ($protocol === 'ptmp')
			$cards_bri_1_ports_te_ptmp[] = $port;
	}
	$cards_bri_1_ports_te_ptp  = implode(',', $cards_bri_1_ports_te_ptp );
	$cards_bri_1_ports_te_ptmp = implode(',', $cards_bri_1_ports_te_ptmp);
	
	gs_keyval_set( 'cards_bri_1_ports_te_ptp' , $cards_bri_1_ports_te_ptp  );
	gs_keyval_set( 'cards_bri_1_ports_te_ptmp', $cards_bri_1_ports_te_ptmp );
	
	# write /etc/misdn-init.conf
	if (@file_exists  ('/usr/local/bin/gpbx-cards-conf')
	&&  @is_executable('/usr/local/bin/gpbx-cards-conf')) {
		@exec( '/usr/local/bin/gpbx-cards-conf conf 1>>/dev/null 2>>/dev/null' );
	}
	
	# restart mISDN:
	@exec( '/etc/init.d/misdn-init restart 1>>/dev/null 2>>/dev/null' );
	
	# reload Asterisk:
	@exec( '/opt/gemeinschaft/sbin/start-asterisk 1>>/dev/null 2>>/dev/null' );
	
}



?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="save" />

<?php

echo '<h3>', 'Digium Wildcard B410p' ,'</h3>' ,"\n";

$src = GS_URL_PATH . 'img/cards/digium-b410p.svg';
echo '
<object
	id="pci-card-1"
	type="image/svg+xml"
	width="80px"
	height="394px"
	data="', $src ,'"
	style="border:0; margin-right:1em;"
	class="fl"
>
	<param name="src" value="', $src ,'">
	', __('Ihr Browser kann die SVG-Datei nicht anzeigen.') ,'
</object>
';



$ports_protocols = array(1=>'ptp', 2=>'ptp', 3=>'ptp', 4=>'ptp');

//$err=0; $out=array();
//@exec('/opt/gemeinschaft/sbin/gs-get-keyval cards_bri_1_ports_te_ptp 2>>/dev/null', $out, $err);
//if (preg_match_all('/[0-9]+/', @$out[0], $m)) {
if (preg_match_all('/[0-9]+/', gs_keyval_get('cards_bri_1_ports_te_ptp'), $m)) {
	foreach ($m[0] as $port) {
		$port = (int)$port;
		if (array_key_exists($port, $ports_protocols)) {
			$ports_protocols[$port] = 'ptp';
		}
	}
}
//$err=0; $out=array();
//@exec('/opt/gemeinschaft/sbin/gs-get-keyval cards_bri_1_ports_te_ptmp 2>>/dev/null', $out, $err);
//if (preg_match_all('/[0-9]+/', @$out[0], $m)) {
if (preg_match_all('/[0-9]+/', gs_keyval_get('cards_bri_1_ports_te_ptmp'), $m)) {
	foreach ($m[0] as $port) {
		$port = (int)$port;
		if (array_key_exists($port, $ports_protocols)) {
			$ports_protocols[$port] = 'ptmp';
		}
	}
}


$asterisk_misdn_ports = array();
$err=0; $out=array();
@exec( 'asterisk -rx \'misdn show stacks\' | grep -E \'Port [0-9]+\' 2>>/dev/null', $out, $err );
/*
  * Port 1 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0
  * Port 2 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0
  * Port 3 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0
  * Port 4 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0
*/
/*
$out = array(
'  * Port 1 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0',
'  * Port 2 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0',
'  * Port 3 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0',
'  * Port 4 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0'
);
$err = 0;
*/
if ($err === 0) {
	foreach ($out as $line) {
		$line = strToLower($line);
		if (! preg_match('/ port[\s:]*([0-9]+)/', $line, $m)) continue;
		$port = (int)lTrim($m[1],'0');
		
		if (preg_match('/ type[\s:]*(te|nt)/', $line, $m)) {
			$asterisk_misdn_ports[$port]['type'] = strToLower($m[1]);
		} else {
			$asterisk_misdn_ports[$port]['type'] = null;
		}
		if (preg_match('/ prot\.[\s:]*(ptp|pmp)/', $line, $m)) {
			$asterisk_misdn_ports[$port]['protocol'] = strToLower($m[1]);
			if ($asterisk_misdn_ports[$port]['protocol'] === 'pmp')
				$asterisk_misdn_ports[$port]['protocol'] = 'ptmp';
		} else {
			$asterisk_misdn_ports[$port]['protocol'] = null;
		}
		if (preg_match('/ l1link[\s:]*(down|up)/', $line, $m)) {
			$asterisk_misdn_ports[$port]['l1link'] = strToLower($m[1]);
		} else {
			$asterisk_misdn_ports[$port]['l1link'] = null;
		}
		if (preg_match('/ l2link[\s:]*(down|up)/', $line, $m)) {
			$asterisk_misdn_ports[$port]['l2link'] = strToLower($m[1]);
		} else {
			$asterisk_misdn_ports[$port]['l2link'] = null;
		}
		if (preg_match('/ blocked[\s:]*(0|1)/', $line, $m)) {
			$asterisk_misdn_ports[$port]['blocked'] = strToLower($m[1]);
		} else {
			$asterisk_misdn_ports[$port]['blocked'] = null;
		}
	}
}




echo '<table cellspacing="1" class="phonebook" style="margin-top:40px;">' ,"\n";
echo '<thead>' ,"\n";
echo '<tr>' ,"\n";
echo '<th>', __('Port') ,'</th>' ,"\n";
echo '<th width="80">', __('Protokoll') ,' <sup>[1]</sup></th>' ,"\n";
echo '<th width="250">', __('Status') ,'</th>' ,"\n";
echo '</tr>' ,"\n";
echo '</thead>' ,"\n";

echo '<tbody>' ,"\n";

for ($port=1; $port<=4; ++$port) {
	echo '<tr height="60" class="', ($port % 2 === 0 ? 'even':'odd') ,'">' ,"\n";
	
	echo '<td style="vertical-align:middle;" class="r">';
	if ($port === 1)
		echo '<b>', $port ,'</b>';
	else
		echo $port;
	echo '</td>' ,"\n";
	
	echo '<td style="vertical-align:middle;">' ,"\n";
	echo '<input type="radio" name="card-1-port-',$port,'-prot" id="card-1-port-',$port,'-prot-ptp" value="ptp"', (@$ports_protocols[$port]==='ptp' ? ' checked="checked"' : '') ,' />', "\n";
	echo ' <label for="card-1-port-',$port,'-prot-ptp">PtP</label><br />' ,"\n";
	echo '<input type="radio" name="card-1-port-',$port,'-prot" id="card-1-port-',$port,'-prot-ptmp" value="ptmp"', (@$ports_protocols[$port]==='ptmp' ? ' checked="checked"' : '') ,' />', "\n";
	echo ' <label for="card-1-port-',$port,'-prot-ptmp">PtMP</label>' ,"\n";
	echo '</td>' ,"\n";
	
	echo '<td style="vertical-align:middle;">' ,"\n";
	if (! array_key_exists($port, $asterisk_misdn_ports)) {
		echo '?';
	} else {
		$asterisk_misdn_port_info = $asterisk_misdn_ports[$port];
		echo 'Pr.:&nbsp;';
		switch ($asterisk_misdn_port_info['protocol']) {
			case 'ptp' : echo 'PtP' ; break;
			case 'ptmp': echo 'PtMP'; break;
			default    : echo '?'   ;
		}
		//echo '<br />',"\n";
		echo ',&nbsp; ',"\n";
		echo 'L1:&nbsp;';
		switch ($asterisk_misdn_port_info['l1link']) {
			case 'down': echo '<span style="color:#e00;">down</span>'; break;
			case 'up'  : echo '<b style="color:#0c0;">up</b>'        ; break;
			default    : echo '?'   ;
		}
		//echo '<br />',"\n";
		echo ',&nbsp; ',"\n";
		echo 'L2:&nbsp;';
		switch ($asterisk_misdn_port_info['l2link']) {
			case 'down': echo '<span style="color:#e00;">down</span>'; break;
			case 'up'  : echo '<b style="color:#0c0;">up</b>'        ; break;
			default    : echo '?'   ;
		}
	}
	echo '</td>' ,"\n";
	
	echo '</tr>' ,"\n";
}
echo '<tr>' ,"\n";
echo '<td class="transp r" colspan="3">' ,"\n";
echo '<br />',"\n";
echo '<input type="submit" value="', __('Speichern') ,'" />' ,"\n";
echo '</td>' ,"\n";
echo '</tr>' ,"\n";

echo '</tbody>' ,"\n";
echo '</table>' ,"\n";

echo '<br style="clear:left;" />' ,"\n";

echo '</form>' ,"\n";



echo '<p class="text"><small>', __('Bitte beachten Sie, da&szlig; die Karte bei der GPBX bauartbedingt &quot;auf dem Kopf liegend&quot; eingebaut ist. Port 1 befindet sich also (von der R&uuml;ckseite betrachtet) an der rechten Geh&auml;useseite.') ,' ', __('Bitte verwenden Sie die Ports in der angegebenen Reihenfolge, also mit 1 beginnend.') ,'</small></p>' ,"\n";

echo '<p class="text"><small><sup>[1]</sup> ', __('PtP (Point-to-Point, Punkt-zu-Punkt) bei einem Mehrger&auml;teanschlu&szlig;, PtMP (Point-to-MultiPoint, Punkt-zu-MultiPunkt) bei einem Anlagenanschlu&szlig;.') ,'</small></p>' ,"\n";



?>
