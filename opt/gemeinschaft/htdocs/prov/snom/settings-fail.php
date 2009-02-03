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

#
# used by phones after factory reset if settings.php fails
# or if DHCP configuration is broken
#

define( 'GS_VALID', true );  /// this is a parent file

@header( 'Content-Type: text/plain; charset=utf-8' );
@header( 'Expires: 0' );
@header( 'Pragma: no-cache' );
@header( 'Cache-Control: private, no-cache, must-revalidate' );
@header( 'Vary: *' );
@ob_start();

require_once( dirName(__FILE__) .'/../../../inc/conf.php' );

/*
print_r($_SERVER);

$prov_url_snom =
	(array_key_exists('HTTPS', $_SERVER) ? 'https':'http') .
	'://'.

echo dirName($_SERVER['SCRIPT_NAME']);
*/
?>

reboot_after_nr$: 10
ethernet_detect$: on
ethernet_replug$: reboot
dhcp!: on
pnp_config$: on
admin_mode$: off
admin_mode_password$: 0000
admin_mode_password_confirm$: 0000
logon_wizard$: off
update_policy$: auto_update
firmware_interval$: 35
firmware_status$: <?php echo GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/'; ?>sw-update.php?m=$mac&u=
redundant_fkeys$: off
text_softkey$: off
#syslog_server$: <?php echo /*//FIXME - GS_SYSLOG_SERVER,*/ "\n"; ?>

language!: Deutsch
web_language!: Deutsch
tone_scheme!: GER
timezone!: GER+1
time_24_format!: on
date_us_format!: off

<?php
if (@ob_get_length() > 0) {
	@header( 'Content-Length: '. (int)ob_get_length() );
	@ob_end_flush();
	exit(0);
}
?>