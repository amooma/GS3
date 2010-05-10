<?php
/*******************************************************************\
 * *            Gemeinschaft - asterisk cluster gemeinschaft
 * * 
 * * $Revision$
 * * 
 * * Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
 * * http://www.amooma.de/
 * * Sascha Daniels <sd@alternative-solution.de> 
 * * 
 * * This program is free software; you can redistribute it and/or
 * * modify it under the terms of the GNU General Public License
 * * as published by the Free Software Foundation; either version 2
 * * of the License, or (at your option) any later version.
 * * 
 * * This program is distributed in the hope that it will be useful,
 * * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * * GNU General Public License for more details.
 * * 
 * * You should have received a copy of the GNU General Public License
 * * along with this program; if not, write to the Free Software
 * * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * * MA 02110-1301, USA.
 * \*******************************************************************/
/*
 * Needs: login_user and login_pwd as parameter
 * Provides: twinkle profile
 * Example: 
 * wget "http://GS_PROV_HOST/gemeinschaft/prov/twinkle/?login_user=hans&login_pwd=123" -O ~/.twinkle/hans.cfg
 * twinkle -f hans
 * You should be online ;-)
 *
 */
define('GS_VALID', true);
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
require_once( GS_DIR .'lib/yadb/yadb.php' );
require_once( GS_DIR .'htdocs/gui/inc/pamal/pamal.php' );


$PAM = new PAMAL( GS_GUI_AUTH_METHOD );
$user = $PAM->getUser();
if ($user) {
	$puser = gs_user_get($user);
	header("Content-Type: text/plain");
	header("Content-Disposition: attachment; filename=".$user.'-'.$puser['host'].".cfg");
	$DB = @gs_db_slave_connect();
	$secret = $DB->executeGetOne('SELECT `secret` FROM `ast_sipfriends` WHERE `_user_id`='.$puser['id']);
	echo '#USER',"\n";
	echo 'user_name=',$puser['ext'],"\n";
	echo 'user_domain=',$puser['host'],"\n";
	echo 'user_display=',$puser['firstname'], ', ',$puser['lastname'],"\n";
	echo 'user_organization=',"\n";
	echo 'auth_realm=',"\n";
	echo 'auth_name=',$puser['ext'],"\n";
	echo 'auth_pass=',$secret,"\n";
	echo '# SIP SERVER',"\n";
	echo 'outbound_proxy='.$puser['host'],"\n";
	echo 'all_requests_to_proxy=no',"\n";
	echo 'non_resolvable_to_proxy=no',"\n";
	echo 'registrar=',$puser['host'],"\n";
	echo 'register_at_startup=yes',"\n";
	echo 'registration_time=3600',"\n";
	echo 'reg_add_qvalue=no',"\n";
	echo 'reg_qvalue=1',"\n";
	echo '',"\n";
	echo '# RTP AUDIO',"\n";
	echo 'codecs=g711a,g711u,gsm',"\n";
	echo 'ptime=20',"\n";
	echo 'out_far_end_codec_pref=yes',"\n";
	echo 'in_far_end_codec_pref=yes',"\n";
	echo 'speex_nb_payload_type=97',"\n";
	echo 'speex_wb_payload_type=98',"\n";
	echo 'speex_uwb_payload_type=99',"\n";
	echo 'speex_bit_rate_type=cbr',"\n";
	echo 'speex_vad=yes',"\n";
	echo 'speex_dtx=no',"\n";
	echo 'speex_penh=yes',"\n";
	echo 'speex_complexity=2',"\n";
	echo 'ilbc_payload_type=96',"\n";
	echo 'ilbc_mode=30',"\n";
	echo 'g726_16_payload_type=102',"\n";
	echo 'g726_24_payload_type=103',"\n";
	echo 'g726_32_payload_type=104',"\n";
	echo 'g726_40_payload_type=105',"\n";
	echo 'g726_packing=rfc3551',"\n";
	echo 'dtmf_transport=auto',"\n";
	echo 'dtmf_payload_type=101',"\n";
	echo 'dtmf_duration=100',"\n";
	echo 'dtmf_pause=40',"\n";
	echo 'dtmf_volume=10',"\n";
	echo '# SIP PROTOCOL',"\n";
	echo 'hold_variant=rfc3264',"\n";
	echo 'check_max_forwards=no',"\n";
	echo 'allow_missing_contact_reg=yes',"\n";
	echo 'registration_time_in_contact=yes',"\n";
	echo 'compact_headers=no',"\n";
	echo 'encode_multi_values_as_list=yes',"\n";
	echo 'use_domain_in_contact=no',"\n";
	echo 'allow_sdp_change=no',"\n";
	echo 'allow_redirection=yes',"\n";
	echo 'ask_user_to_redirect=yes',"\n";
	echo 'max_redirections=5',"\n";
	echo 'ext_100rel=supported',"\n";
	echo 'ext_replaces=yes',"\n";
	echo 'referee_hold=no',"\n";
	echo 'referrer_hold=yes',"\n";
	echo 'allow_refer=yes',"\n";
	echo 'ask_user_to_refer=yes',"\n";
	echo 'auto_refresh_refer_sub=no',"\n";
	echo 'attended_refer_to_aor=no',"\n";
	echo 'send_p_preferred_id=no',"\n";
	echo '',"\n";
	echo '# Transport/NAT',"\n";
	echo 'sip_transport=auto',"\n";
	echo 'sip_transport_udp_threshold=1300',"\n";
	echo 'nat_public_ip=',"\n";
	echo 'stun_server=',"\n";
	echo 'persistent_tcp=yes',"\n";
	echo '',"\n";
	echo '# TIMERS',"\n";
	echo 'timer_noanswer=30',"\n";
	echo 'timer_nat_keepalive=30',"\n";
	echo 'timer_tcp_ping=30',"\n";
	echo '',"\n";
	echo '# ADDRESS FORMAT',"\n";
	echo 'display_useronly_phone=yes',"\n";
	echo 'numerical_user_is_phone=no',"\n";
	echo 'remove_special_phone_symbols=yes',"\n";
	echo 'special_phone_symbols=-()/.',"\n";
	echo '',"\n";
	echo '# RING TONES',"\n";
	echo 'ringtone_file=',"\n";
	echo 'ringback_file=',"\n";
	echo '',"\n";
	echo '# SCRIPTS',"\n";
	echo 'script_incoming_call=',"\n";
	echo 'script_in_call_answered=',"\n";
	echo 'script_in_call_failed=',"\n";
	echo 'script_outgoing_call=',"\n";
	echo 'script_out_call_answered=',"\n";
	echo 'script_out_call_failed=',"\n";
	echo 'script_local_release=',"\n";
	echo 'script_remote_release=',"\n";
	echo '',"\n";
	echo '# NUMBER CONVERSION',"\n";
	echo '',"\n";
	echo '# SECURITY',"\n";
	echo 'zrtp_enabled=no',"\n";
	echo 'zrtp_goclear_warning=yes',"\n";
	echo 'zrtp_sdp=yes',"\n";
	echo 'zrtp_send_if_supported=no',"\n";
	echo '',"\n";
	echo '# MWI',"\n";
	echo 'mwi_sollicited=no',"\n";
	echo 'mwi_user=',"\n";
	echo 'mwi_server=',"\n";
	echo 'mwi_via_proxy=no',"\n";
	echo 'mwi_subscription_time=3600',"\n";
	echo 'mwi_vm_address=',"\n";
	echo '',"\n";
	echo '# INSTANT MESSAGE',"\n";
	echo 'im_max_sessions=10',"\n";
	echo 'im_send_iscomposing=yes',"\n";
	echo '',"\n";
	echo '# PRESENCE',"\n";
	echo 'pres_subscription_time=3600',"\n";
	echo 'pres_publication_time=3600',"\n";
	echo 'pres_publish_startup=yes',"\n";

} else {
	_not_allowed();
}


function _not_found( $errmsg='' )
{
	@header( 'HTTP/1.0 404 Not Found', true, 404 );
	@header( 'Status: 404 Not Found' , true, 404 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Not found.');
	exit(1);
}

function _not_allowed( $errmsg='' )
{
	@header( 'HTTP/1.0 403 Forbidden', true, 403 );
	@header( 'Status: 403 Forbidden' , true, 403 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Not authorized.');
	exit(1);
}

?>