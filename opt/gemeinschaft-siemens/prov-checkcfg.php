<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
*                    Add-on Siemens provisioning
* 
* $Revision: 356 $
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

include_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/get-listen-to-ids.php' );


// REALLY PRIVATE! CAREFUL WITH PARAMS - NO VALIDATION!
function _gs_siemens_prov_phone_checkcfg_by_ip_do_siemens( $ip, $reboot=true, $pre_sleep=0 )
{
	if (_gs_prov_phone_checkcfg_exclude_ip( $ip )) return;
	
	$cmd =
	  'wget -q -O /dev/null -o /dev/null -b --tries=1'
	. ' --timeout=10 --retry-connrefused --no-http-keep-alive'
	. ' --user-agent='. escapeShellArg('Jakarta Commons-HttpClient/3.0')
	. ' --header='. escapeShellArg('Content-type: application/x-www-form-urlencoded; charset=utf-8')
	. ' --post-data='. escapeShellArg('ContactMe=true')
	. ' '. escapeShellArg('http://'. $ip .':8085/contact_dls.html/ContactDLS');
	# Sometimes the phone just ignores the ContactMe request so we need
	# to send this twice to be sure. But then again 2 requests within 1
	# second would cause the phone's web server to die.
	# So don't just do  for ($i=0; $i<2; ++$i) @exec($cmd, $out, $err);
	# Edit: Retrying more than once a minute or so is very likely to
	# crash the phone.
	
	/* FIXME: does not work via SSH
	if (! gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
		$listen_to_ids = gs_get_listen_to_ids();
		if (is_array($listen_to_ids) && count($listen_to_ids)>0) {
			$cmd = 'ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root '. qsa( GS_PROV_HOST ) .' '. qsa($cmd);
		}
		unset($listen_to_ids);
	}
	*/
	
	$ign_out = '1>>/dev/null 2>>/dev/null';
	if ($pre_sleep < 1) {
		@ exec(                $cmd                      , $out, $err );
	} else {
		$s = (int)$pre_sleep;
		@ exec( '(sleep '. ($s   ) .'; '. $cmd .') '. $ign_out .' &', $out, $err );
	}
}

?>