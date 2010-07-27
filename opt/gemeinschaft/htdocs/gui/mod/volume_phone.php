<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
* Author: Andreas Neugebauer <neugebauer@loca.net> - LocaNet oHG 
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
include_once( GS_DIR .'inc/gs-fns/gs_user_prov_params_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_prov_param_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_phonemodel_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_params_get.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

//this array contains the information about the different phone types volume settings
$volume_configs = array();


$phone_types = array();
if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	$phone_types['snom-300'    ] = array( 'name' => 'Snom 300', 'conf' => 'snom-3x');
	$phone_types['snom-320'    ] = array( 'name' => 'Snom 320', 'conf' => 'snom-3x');
	$phone_types['snom-360'    ] = array( 'name' => 'Snom 360', 'conf' => 'snom-3x');
	$phone_types['snom-370'    ] = array( 'name' => 'Snom 370', 'conf' => 'snom-3x');
	$phone_types['snom-820'    ] = array( 'name' => 'Snom 820', 'conf' => 'snom-3x');
	$phone_types['snom-821'    ] = array( 'name' => 'Snom 821', 'conf' => 'snom-3x');
	
	$snom3x = array();
	$snom3x['vol_handset' ] = array( 'desc' => __('H&ouml;rer'), 'min' => 0, 'max' => 15, 'default' => 8 ) ;
	$snom3x['vol_handset_mic' ] = array( 'desc' => __('H&ouml;rer Mikrofon'), 'min' => 1, 'max' => 8, 'default' => 5 ) ;
	$snom3x['vol_headset' ] = array( 'desc' => __('Headset'), 'min' => 0, 'max' => 15, 'default' => 8 ) ;
	$snom3x['vol_headset_mic' ] = array( 'desc' => __('Headset Mikrofon'), 'min' => 1, 'max' => 8, 'default' => 4 ) ;
	$snom3x['vol_speaker' ] = array( 'desc' => __('Geh&auml;uselautsprecher'), 'min' => 0, 'max' => 15, 'default' => 8 ) ;
	$snom3x['vol_speaker_mic' ] = array( 'desc' => __('Geh&auml;use Mikrofon'), 'min' => 1, 'max' => 8, 'default' => 6 ) ;
	$snom3x['vol_ringer' ] = array( 'desc' => __('Klingelton'), 'min' => 1, 'max' => 15, 'default' => 8 ) ;
	
	$volume_configs['snom-3x'] = $snom3x;
		
}
if (gs_get_conf('GS_ELMEG_PROV_ENABLED')) {
	
	$phone_types['elmeg-290'    ] = array( 'name' => 'Elmeg 290', 'conf' => 'elmeg');
	
	
	$elmeg = array();
	$elmeg['vol_handset' ] = array( 'desc' => __('H&ouml;rer'), 'min' => 0, 'max' => 15, 'default' => 8 ) ;
	$elmeg['vol_handset_mic' ] = array( 'desc' => __('H&ouml;rer Mikrofon'), 'min' => 1, 'max' => 8, 'default' => 5 ) ;
	$elmeg['vol_headset' ] = array( 'desc' => __('Headset'), 'min' => 0, 'max' => 15, 'default' => 8 ) ;
	$elmeg['vol_headset_mic' ] = array( 'desc' => __('Headset Mikrofon'), 'min' => 1, 'max' => 8, 'default' => 4 ) ;
	$elmeg['vol_speaker' ] = array( 'desc' => __('Geh&auml;uselautsprecher'), 'min' => 0, 'max' => 15, 'default' => 8 ) ;
	$elmeg['vol_speaker_mic' ] = array( 'desc' => __('Geh&auml;use Mikrofon'), 'min' => 1, 'max' => 8, 'default' => 6 ) ;
	$elmeg['vol_ringer' ] = array( 'desc' => __('Klingelton'), 'min' => 1, 'max' => 15, 'default' => 8 ) ;
	
	$volume_configs['elmeg'] = $elmeg;
		
}

if (gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
	$phone_types['aastra-51i'] =  array( 'name' =>'Aastra 51i', 'conf' => 'aastra5xi');
	$phone_types['aastra-53i'] =  array( 'name' =>'Aastra 53i', 'conf' => 'aastra5xi');
	$phone_types['aastra-55i'] =  array( 'name' =>'Aastra 55i', 'conf' => 'aastra5xi');
	$phone_types['aastra-57i'] =  array( 'name' =>'Aastra 57i', 'conf' => 'aastra5xi');
	
	$aastra = array();
	$aastra['handset tx gain' ] = array( 'desc' => __('H&ouml;rer Mikrofon'), 'min' => -10, 'max' => 10, 'default' => 0 ) ;
	$aastra['handset sidetone gain' ] = array( 'desc' => __('Eigenecho H&ouml;rer'), 'min' => -10, 'max' => 10, 'default' => 0 ) ;
	$aastra['headset tx gain' ] = array( 'desc' => __('Headset Mikrofon'), 'min' => -10, 'max' => 10, 'default' => 0 ) ;
	$aastra['headset sidetone gain' ] = array( 'desc' => __('Eigenecho Headset'), 'min' => -10, 'max' => 10, 'default' => 0 ) ;
	$aastra['handsfree tx gain' ] = array( 'desc' => __('Geh&auml;use Mikrofon'), 'min' => -10, 'max' => 10, 'default' => 0 ) ;
	
	$volume_configs['aastra5xi'] = $aastra;
}

if (gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
	$phone_types['siemens-os20'] =  array( 'name' => 'Siemens OpenStage 20', 'conf' => 'siemens');
	$phone_types['siemens-os40'] =  array( 'name' => 'Siemens OpenStage 40', 'conf' => 'siemens');
	$phone_types['siemens-os60'] =  array( 'name' => 'Siemens OpenStage 60', 'conf' => 'siemens');
	$phone_types['siemens-os80'] =  array( 'name' => 'Siemens OpenStage 80', 'conf' => 'siemens');
	
	$siemens = array();
	$siemens['line-rollover-volume' ] = array( 'desc' => __('Anklopfton'), 'min' => 1, 'max' => 8, 'default' => 2 ) ;
	
	$volume_configs['siemens'] = $siemens;
}

$action = @$_REQUEST['action'];
if (! in_array($action, array('', 'save', 'save-and-resync'), true))
	$action = '';


$phone_type = preg_replace('/[^a-z0-9\-]/', '', @$_REQUEST['phone_type']);

if ($phone_type == '') {
	$ret = $phone_type  = gs_user_phonemodel_get( @$_SESSION['sudo_user']['name'] );
	if (isGsError($ret)) {
		echo  $ret->getMsg();
	}
	else if( $phone_type == "none" )
		$phone_type = '';
	else if( ! array_key_exists ( $phone_type, $phone_types ))
		 $phone_type = '';
}

if ($phone_type == '') {
	if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
		if     (array_key_exists('snom-300', $phone_types)) $phone_type = 'snom-300';
		elseif (array_key_exists('snom-320', $phone_types)) $phone_type = 'snom-320';
		elseif (array_key_exists('snom-360', $phone_types)) $phone_type = 'snom-360';
		elseif (array_key_exists('snom-370', $phone_types)) $phone_type = 'snom-370';
		elseif (array_key_exists('snom-820', $phone_types)) $phone_type = 'snom-820';
		elseif (array_key_exists('snom-821', $phone_types)) $phone_type = 'snom-821';
	}
	else if (gs_get_conf('GS_ELMEG_PROV_ENABLED')) {
		if (array_key_exists('elmeg-290', $phone_types)) $phone_type = 'elmeg-290';
	}
	else if (gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
		if     (array_key_exists('aastra-51i', $phone_types)) $phone_type = 'aastra-51i';
		elseif (array_key_exists('aastra-53i', $phone_types)) $phone_type = 'aastra-53i';
		elseif (array_key_exists('aastra-55i', $phone_types)) $phone_type = 'aastra-55i';
		elseif (array_key_exists('aastra-57i', $phone_types)) $phone_type = 'aastra-57i';
	} 
	else if (gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
		if     (array_key_exists('siemens-os20', $phone_types)) $phone_type = 'siemens-os20';
		elseif (array_key_exists('siemens-os40', $phone_types)) $phone_type = 'siemens-os40';
		elseif (array_key_exists('siemens-os60', $phone_types)) $phone_type = 'siemens-os60';
		elseif (array_key_exists('siemens-os80', $phone_types)) $phone_type = 'siemens-os80';
	} 

}



if ( isset( $volume_configs[ $phone_types[$phone_type]['conf']] ) ) {
        $volume_config =  $volume_configs[ $phone_types[$phone_type]['conf']];

        $prov_params = gs_prov_params_get(  $_SESSION['sudo_user']['name'], $phone_type );

        foreach ( $prov_params as $prov_parm_key => $prov_parm_value  ) {
        
        	if ( array_key_exists( $prov_parm_key, $volume_config )  && isset ( $prov_parm_value[-1])) {
        		
        		$value =  (int)$prov_parm_value[-1];
        		if( $value < $volume_config[$prov_parm_key]['min'] || $value > $volume_config[$prov_parm_key]['max'] ) {
        			continue;
        		}
        		$volume_config[$prov_parm_key]['value'] = $value;
        	}
        
        }


        $parms = gs_user_prov_params_get( $_SESSION['sudo_user']['name'], $phone_type );



	foreach ( $parms as $parm ) {

		if ( $parm['phone_type'] == $phone_type && array_key_exists ( $parm['param'], $volume_config ) ) {
			$volume_config[$parm['param']]['value'] = $parm['value'];
		}
	}
}

else {
	$volume_config =  false;
}

#####################################################################
# save {
#####################################################################

if ($action === 'save' || $action === 'save-and-resync') {

	if ( is_array ( $volume_config ) && $phone_type != '' ) {
	
		foreach ($volume_config as $param => $data) {
			$encparam = preg_replace( '/\s/', '%', $param);
			if ( isset (  $_REQUEST[$encparam]) ) {
			
				$akt_value = @$_REQUEST[$encparam];
				if ( $akt_value >= $data['min'] && $akt_value <= $data['max'] ) {
				
					$volume_config[$param]['value'] = $akt_value;
					
					$ret = gs_user_prov_param_set( $_SESSION['sudo_user']['name'], $phone_type, $param, null, $akt_value );
					if (isGsError($ret)) {
						echo  __('Fehler beim schreinen des Parameters ' ) . $param . __(' in die Datenbank: ') .  $ret->getMsg();
					}
				}
				else {
					echo  __('Der Parameters ' ) . $param . __(' ist au&szlig;erhalb des zul&aumlssigen Bereiches. ');	
				}
			}
		}
	}	

	if ($action === 'save-and-resync') {
		include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );
		$ret = gs_prov_phone_checkcfg_by_user( @$_SESSION['sudo_user']['name'], false );
		if (isGsError($ret) || ! $ret) {
			// does not happen
			echo '<div class="errorbox">', __('Fehler beim Aktualisieren des Telefons') ,'</div>' ,"\n";
		}
	}

	$action = '';  # view
}
#####################################################################
# save }
#####################################################################

#####################################################################
# view {
#####################################################################
if ($action == '') {

?>

<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<table cellspacing="1">
<tbody>
<tr>
	<th><?php echo __('Telefon-Typ'); ?></th>
	<td>
		<select name="phone_type">
<?php
foreach ($phone_types as $phone_type_name => $phone_type_detail) {
	echo '<option value="', $phone_type_name ,'"';
	if ($phone_type_name === $phone_type)
		echo ' selected="selected"';
	echo '>', htmlEnt($phone_type_detail['name']) ,'</option>' ,"\n";
}
?>
		</select>
	</td>
	<td class="transp">
		<input type="submit" value="<?php echo __('Zeigen'); ?>" />
	</td>
</tr>
</tbody>
</table>
</form>
<?php
echo '<small>(', __('Vor dem Wechsel ggf. &Auml;nderungen speichern!') ,')</small><br />' ,"\n";
?>

<hr />
<br />
<div style="max-width:600px;">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/info.png" class="fl" />
	<p style="margin-left:22px;">
		<?php echo __('Die Einstellungen, die hier vorgenommen werden k&ouml;nnen, werden nach dem Neustart des Endger&auml;tes aktiv. Einzelne Einstellungen k&ouml;nnen bis zum n&auml;chsten Neustart auch am Endgerät ge&auml;ndert werden.'); ?>
	</p>
</div>

<?php
echo '<h3 style="margin:0; padding:1px 0; font-size:100%;">', htmlEnt($phone_types[$phone_type]['name']) ,'</h3>', "\n";
?>
<p />

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="phone_type" value="<?php echo $phone_type; ?>" />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="2" class="c m" style="padding:0.6em;"><?php echo htmlEnt($phone_types[$phone_type]['name']) ?></th>
	
</tr>
	
<tr>
	<th><?php echo __('Funktion'); ?></th>

	<th><?php echo __('Lautst&auml;rke'); ?></th>
</tr>
</thead>
<tbody>
<?php
foreach ($volume_config as $param => $data) {

$id = preg_replace( '/\s/', '%', $param);
//$id=$param;
?>
<tr>
	<td style="width:180px;"><?php echo $data['desc']; ?></td>
	<td style="width:115px;">
		<select name="<?php echo $id; ?>" class="r">
<?php

$akt_value= $data['default'];
if ( isset ($data['value'] ) )
	$akt_value = $data['value'];

for ($i=$data['min']; $i<=$data['max']; $i++) {
	
	$sel = ($i == $akt_value) ? ' selected="selected"' : '';
	$label = ( $i <= 9 ) ? '&nbsp;&nbsp;' . $i : $i;
	echo '<option value="', $i , '"', $sel, '>', $label, '&nbsp;</option>', "\n";
}
?>
		</select>
	</td>

</tr>
<?php
}
?>

<tr>
	<td colspan="3" class="quickchars r">
		<br />
		<br />
		<button type="submit" name="action" value="save">
			<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			<?php echo __('Speichern'); ?>
		</button>
		<button type="submit" name="action" value="save-and-resync">
			<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			<?php echo __('Speichern und Telefon aktualisieren'); ?>
		</button>
	</td>
</tr>
</tbody>
</table>
</form>
<?php

}
#####################################################################
# view }
#####################################################################
?>
