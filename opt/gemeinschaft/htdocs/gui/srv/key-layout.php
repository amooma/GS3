<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Sebastian Ertz <gemeinschaft@swastel.eisfair.net
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

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );
require_once( GS_DIR .'inc/gs-fns/gs_keys_get.php' );
require_once( GS_DIR .'inc/log.php' );
require_once( GS_DIR .'lib/fpdf/fpdf.php' );

function _not_allowed( $errmsg='' )
{
	@header( 'HTTP/1.0 403 Forbidden', true, 403 );
	@header( 'Status: 403 Forbidden' , true, 403 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Not authorized.');
	exit(1);
}

function _server_error( $errmsg='' )
{
	@header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
	@header( 'Status: 500 Internal Server Error' , true, 500 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Internal Server Error.');
	exit(1);
}

function _not_found( $errmsg='' )
{
	@header( 'HTTP/1.0 404 Not Found', true, 404 );
	@header( 'Status: 404 Not Found' , true, 404 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Not found.');
	exit(1);
}

function _not_modified( $etag='', $attach=false, $fake_filename='' )
{
	header( 'HTTP/1.0 304 Not Modified', true, 304 );
	header( 'Status: 304 Not Modified', true, 304 );
	if (! empty($etag))
		header( 'ETag: '. $etag );
	if (! empty($fake_filename))
		header( 'Content-Disposition: '.($attach ? 'attachment':'inline').'; filename="'.$fake_filename.'"' );
	exit(0);
}

function _get_key_label($knump)
{
	global $softkeys;
	$key_defs = @$softkeys['f'.$knump];
	if ( is_array($key_defs) ) {
		if (array_key_exists('slf', $key_defs)) {
			if (strlen($key_defs['slf']['label']) > 12)
				$key_defs['slf']['label'] = substr($key_defs['slf']['label'], 0, 10) . '...';
			return utf8_decode($key_defs['slf']['label']);
		} elseif (array_key_exists('inh', $key_defs)) {
			if (strlen($key_defs['inh']['label']) > 12)
				$key_defs['inh']['label'] = substr($key_defs['inh']['label'], 0, 10) . '...';
			return utf8_decode($key_defs['inh']['label']);
		}
	}
	
	return;
}


##############################
# Class
##############################
class PDF extends FPDF {

	function Header() {
		$this->SetFont('Arial', 'B', 15);
		$label = __('Tastenbeschriftung') .' '. __('von') .' '. $this->firstname .' '. $this->lastname .' ('. $this->ext .')';
		$this->Cell(0, 10, utf8_decode($label), 0, 0, 'C');
		//$this->Ln(20);
	}

	function Footer() {
		$this->SetY(-15);
		$this->SetFont('Arial', '', 8);
		$this->Cell(0, 10 ,__('Seite'). ' '.$this->PageNo().' / {nb}', 0, 0, 'C');
	}

	function SetDash( $black=null, $white=null ) {
		if ($black !== null )
			$s = sprintf('[%.3F %.3F] 0 d', $black * $this->k, $white * $this->k);
		else
			$s = '[] 0 d';
		$this->_out($s);
	}
	
}	
	


$phone_types = array();
if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	$enabled_models = preg_split('/[,\\s]+/', gs_get_conf('GS_PROV_MODELS_ENABLED_SNOM'));
	if (in_array('*', $enabled_models) || in_array('320', $enabled_models))
		$phone_types['snom-320'] = 'Snom 320';
	if (in_array('*', $enabled_models) || in_array('360', $enabled_models))
		$phone_types['snom-360'] = 'Snom 360';
	if (in_array('*', $enabled_models) || in_array('370', $enabled_models))
		$phone_types['snom-370'] = 'Snom 370';
}
if (gs_get_conf('GS_GRANDSTREAM_PROV_ENABLED')) {
	$enabled_models = preg_split('/[,\\s]+/', gs_get_conf('GS_PROV_MODELS_ENABLED_GRANDSTREAM'));
	if (in_array('*', $enabled_models) || in_array('gxp2000', $enabled_models))
		$phone_types['grandstream-gxp2000'] = 'Grandstream GXP 2000';
	if (in_array('*', $enabled_models) || in_array('gxp2010', $enabled_models))
		$phone_types['grandstream-gxp2010'] = 'Grandstream GXP 2010';
	if (in_array('*', $enabled_models) || in_array('gxp2020', $enabled_models))
		$phone_types['grandstream-gxp2020'] = 'Grandstream GXP 2020';
}


# get phone_type
$phone_type = preg_replace('/[^a-z0-9\-]/', '', @$_REQUEST['phone_type']);

$user_id = preg_replace('/[^0-9]/', '', @$_REQUEST['user_id']);

if ( $phone_type == '' || ! array_key_exists($phone_type, $phone_types) ) { // TODO ? FIXME
	_not_allowed();
}

if ( $user_id == ''  ) { // TODO ? FIXME
	_not_allowed();
}

# get DB infos
$rs = $DB->execute( 'SELECT `u`.`user`, `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE `u`.`id`='. $user_id );
$user = $rs->fetchRow();

$user_name = $user['user'];

# PDF erzeugen
$pdf=new PDF('P', 'mm', 'A4');

$pdf->AliasNbPages();

# PDF Variablen setzen
$pdf->firstname = $user['fn' ];
$pdf->lastname  = $user['ln' ];
$pdf->ext       = $user['ext'];


foreach ($phone_types as $phone_type_name => $phone_type_title) {
	if ($phone_type_name === $phone_type) {
	
		$softkeys = null;
		$GS_Softkeys = gs_get_key_prov_obj( $phone_type_name );
		$GS_Softkeys->set_user( $user_name );
		$GS_Softkeys->retrieve_keys( $phone_type_name );
		$softkeys = $GS_Softkeys->get_keys();
				
		$pdf->AddPage();
		$pdf->SetFont('Arial', '', 8);
		//$pdf->SetFillColor(224, 235, 255);
		$pdf->SetFillColor(100, 149, 237);
		$pdf->SetTextColor(  0,   0,   0);

		$key_levels = null;
		if (subStr($phone_type_name,0,4)==='snom') {
			$phone_layout = 'snom';
			
			$key_levels = array(
				0 => array('from'=> 0, 'to'=>11, 'title'=>$phone_type_title             , 'X'=> 10, 'Y'=>30, 'width'=>19),
				1 => array('from'=>12, 'to'=>32, 'title'=>__('Erweiterungs-Modul') .' 1', 'X'=> 75, 'Y'=>30, 'width'=>19),
				2 => array('from'=>33, 'to'=>53, 'title'=>__('Erweiterungs-Modul') .' 2', 'X'=>140, 'Y'=>30, 'width'=>19),
				3 => array('from'=>54, 'to'=>74, 'title'=>__('Erweiterungs-Modul') .' 3', 'X'=> 10, 'Y'=>30, 'width'=>19),
				4 => array('from'=>75, 'to'=>95, 'title'=>__('Erweiterungs-Modul') .' 4', 'X'=> 75, 'Y'=>30, 'width'=>19)
			);
			
		} elseif (subStr($phone_type_name,0,11)==='grandstream') {
			$phone_layout = 'grandstream';

			$key_levels = array(
				0 => array('from'=> 0, 'to'=> 6, 'title'=>$phone_type_title                                   , 'X'=>10, 'Y'=>30, 'width'=>30),
				1 => array('from'=> 0, 'to'=>13, 'title'=>$phone_type_title.' '.__('Erweiterungs-Modul') .' 1', 'X'=>10, 'Y'=>30, 'width'=>24),
				2 => array('from'=> 0, 'to'=>13, 'title'=>$phone_type_title.' '.__('Erweiterungs-Modul') .' 2', 'X'=>10, 'Y'=>30, 'width'=>24)
			);
			
			switch ($phone_type_name) {
			case 'grandstream-gxp2000':
				$key_levels[1]['width'] = 14.7;
				$key_levels[2]['width'] = 14.7;
				break;
			case 'grandstream-gxp2010':
				$key_levels[0]['to']    =  8;
				$key_levels[0]['width'] = 24;
				break;
			}
			
		}

		/*$pdf->SetDash(0.5,1); //TODO Schnittlinien
		$pdf->Line(10+20, 30+15, 10+20, 30+10);
		$pdf->SetDash();*/
		

		foreach ($key_levels as $key_level_idx => $key_level_info) {

			$fill = false;

			switch ($phone_layout) {
				case 'snom':
					switch ($key_level_idx) {
						case 0: $left =  0; $right =  6; $fill = false; break;
						case 1: $left = 12; $right = 23; $fill = true;  break;
						case 2: $left = 33; $right = 44; $fill = true;  break;
						case 3: $left = 54; $right = 65; $fill = true;  break;
						case 4: $left = 75; $right = 86; $fill = true;  break;
					}
					break;
			}		
			
			$pdf->SetXY($key_level_info['X'], $key_level_info['Y']);
			
			# Title
			$pdf->Cell(1, 6, $key_level_info['title']);
			$pdf->Ln(10);
			
			for ($i=$key_level_info['from']; $i<=$key_level_info['to']; ++$i) {

				if ($phone_layout === 'snom') {
					$knum  = ($i%2===($key_level_idx+1)%2 ? $left : $right);
					$knump = str_pad($knum, 3, '0', STR_PAD_LEFT);
				} else {
					$knum  = $i;
					$knump = str_pad($knum, 4, '0', STR_PAD_LEFT);
				}
				
				$keynum = $knum + 1; //TODO ? FIXME
				
				if ($phone_layout==='snom') {
					$pdf->SetX($key_level_info['X']);
					
					# set border (L = Left, R = Right, T = Top, B = Bottom)
					$border = 'LR';
					if ($i==$key_level_info['from']) $border .= 'T';
					if ($i==$key_level_info['to'  ]) $border .= 'B';
					
					$width  = $key_level_info['width'];
					$height = 6;
					
					# Table
					switch ($key_level_idx) {
						case 0:
						case 2:
						case 4:
							if ($i%2)
								$align = 'L';
							else
								$align = 'R';
							break;
						case 1:
						case 3:
							if ($i%2)
								$align = 'R';
							else
								$align = 'L';
							break;
					}
					$pdf->Cell($width, $height, ($i%2===($key_level_idx+1)%2 ? $keynum : ''), 1      , 0, 'C'   , false);
					$pdf->Cell($width, $height, _get_key_label($knump)                      , $border, 0, $align, $fill);
					$pdf->Cell($width, $height, ($i%2===($key_level_idx+1)%2 ? '' : $keynum), 1      , 0, 'C'   , false);
					
					$pdf->Ln();
		                        
					if ($i%2===($key_level_idx+1)%2) ++$left;
					else ++$right;
					
				} elseif ($phone_layout==='grandstream') {
				
					$pdf->SetX($key_level_info['X']);

					# set border (L = Left, R = Right, T = Top, B = Bottom)
					$border = 'LR';
					if ($i==$key_level_info['from']) $border .= 'T';
					if ($i==$key_level_info['to'  ]) $border .= 'B';
					
					$width = $key_level_info['width'];

					if ($key_level_idx == 0) {
						$height = 10 + ($i%2 ? 1 : 0);
						
						# Spalte 1
						$pdf->Cell(20, $height, $keynum               , 1      , 0, 'C', false);
						$pdf->Cell($width, $height, _get_key_label($knump), $border, 0, 'C', $fill);

						if ($phone_type_name === 'grandstream-gxp2010') {
							# Spalte 2 (only gxp2010)
							$knump = str_pad($knum+9, 4, '0', STR_PAD_LEFT);
							$pdf->Cell($width, $height, _get_key_label($knump), $border, 0, 'C', !$fill);
							$pdf->Cell(20    , $height, $keynum + 9           , 1      , 0, 'C', false );
						}
						
					} elseif ($key_level_idx >= 1 ) {
						if ($phone_type_name === 'grandstream-gxp2000') {
							if ( $i == $key_level_info['from'] || $i == $key_level_info['to'] )
								$height = 10;
							else
								$height = 13;						
						} else {
							if ( $i == $key_level_info['from'] || $i == $key_level_info['to'] )
								$height = 9.35;
							else
								$height = 11.5;
						}
						
						$knum = $knum + 100 + ($key_level_idx-1) * 56;
						$keynum = $knum + 1 - 100;
						
						# Spalte 1
						$knump = str_pad($knum     , 4, '0', STR_PAD_LEFT);
						$pdf->Cell(20    , $height, $keynum               , 1      , 0, 'C', false);
						$pdf->Cell($width, $height, _get_key_label($knump), $border, 0, 'C', $fill);
						
						# Spalte 2
						$knump = str_pad($knum + 14, 4, '0', STR_PAD_LEFT);
						$pdf->Cell($width, $height, _get_key_label($knump), $border, 0, 'C', !$fill);
						$pdf->Cell(20    , $height, $keynum + 14          , 1      , 0, 'C', false );
						
						# Spalte 3
						$knump = str_pad($knum + 28, 4, '0', STR_PAD_LEFT);
						$pdf->Cell(20    , $height, $keynum + 28          , 1      , 0, 'C', false );
						$pdf->Cell($width, $height, _get_key_label($knump), $border, 0, 'C', $fill);

						# Spalte 4
						$knump = str_pad($knum + 42, 4, '0', STR_PAD_LEFT);
						$pdf->Cell($width, $height, _get_key_label($knump), $border, 0, 'C', !$fill);
						$pdf->Cell(20    , $height, $keynum + 42          , 1      , 0, 'C', false );

					}
					
					$pdf->Ln();
					
				}

				$fill = !$fill;
			}
			
			if ($phone_layout === 'snom' && $key_level_idx == 2) {
				$pdf->AddPage();
			}
			
			if ($phone_layout === 'grandstream' && $key_level_idx < 2 ) {
				$pdf->AddPage();
			}
		}
		
	}
}



$pdf->SetCreator('Gemeinschaft');
$pdf->SetAuthor('Gemeinschaft');	
$pdf->Output( __('Tastenbeschriftung').'_'.$phone_type.'_'.$user_name.'.pdf', 'D');

?>