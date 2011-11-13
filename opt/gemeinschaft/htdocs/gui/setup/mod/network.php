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
require_once( GS_DIR .'inc/find_executable.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
require_once( GS_DIR .'inc/ipaddr-fns.php' );

$saved = false;
$errors_html = array();
$action = (string)@$_REQUEST['action'];

if ($action === 'save2') {
	$save2_action = (string)@$_REQUEST['save2_action'];
	if ($save2_action === '') $action = '';
}


/*
$ifconfig = find_executable('ifconfig', array(
	'/sbin/', '/bin/', '/usr/sbin/', '/usr/bin/'
));
*/

?>

<script type="text/javascript" src="<?php echo GS_URL_PATH; ?>js/unsaved-changes.js"></script>

<div style="width:550px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">
<h1><?php echo __('Netzwerk'); ?></h1>
<?php

if ($action === '' || $action === 'save') {
	
	# determine our main address
	#
	$err=0; $out=array();
	@exec( '/opt/gemeinschaft/sbin/getnetifs/getipaddrs 2>>/dev/null', $out, $err );
	$addrs = array();
	if ($err === 0) {
		foreach ($out as $line) {
			$addrs[] = trim($line);
		}
	}
	if (($addr = @$_SERVER['SERVER_ADDR']      )) $addrs[] = $addr;
	if (($addr = gs_keyval_get('vlan_0_ipaddr'))) $addrs[] = $addr;
	$good_addrs = array();
	foreach ($addrs as $addr) {
		if (subStr($addr,0,4) === '127.'    ) continue;
		if (subStr($addr,0,8) === '169.254.') continue;
		$good_addrs[] = $addr;
	}
	unset($addrs);
	$current_ipaddr = (count($good_addrs) > 0 ? $good_addrs[0] : '');
	
	$current_netmask  = gs_keyval_get('vlan_0_netmask');
	$current_router   = gs_keyval_get('vlan_0_router');
	$current_dns1     = gs_keyval_get('vlan_0_dns1');
	$current_dns2     = gs_keyval_get('vlan_0_dns2');
	$current_ntp1     = gs_keyval_get('vlan_0_ntp1');
	$current_ntp2     = gs_keyval_get('vlan_0_ntp2');
	$current_ntp3     = gs_keyval_get('vlan_0_ntp3');
	$current_ntp4     = gs_keyval_get('vlan_0_ntp4');
	
	$form_ipaddr   = $current_ipaddr;
	$form_netmask  = $current_netmask;
	$form_router   = $current_router;
	$form_dns1     = $current_dns1;
	$form_dns2     = $current_dns2;
	$form_ntp1     = $current_ntp1;
	$form_ntp2     = $current_ntp2;
	$form_ntp3     = $current_ntp3;
	$form_ntp4     = $current_ntp4;
	
}


if ($action === 'save') {
	
	define( 'GS_VALIDATION_OK'    , 3 );
	define( 'GS_VALIDATION_EMPTY' , 2 );
	define( 'GS_VALIDATION_WARN'  , 1 );
	define( 'GS_VALIDATION_ERR'   , 0 );
	
	function _normalize_ip_addr( $addr )
	{
		$addr = preg_replace('/[^0-9.]/', '', $addr);
		if ($addr == '') return null;
		$addr_parts = explode('.', $addr);
		$addr = '';
		for ($i=0; $i<=3; ++$i) {
			$part = (int)lTrim(@$addr_parts[$i], '0 ');
			if     ($part > 255) $part = 255;
			elseif ($part <   0) $part =   0;
			$addr .= $part;
			if ($i < 3) $addr .= '.';
		}
		return $addr;
	}
	
	function _is_invalid_ip_addr_by_format( $addr )
	{
		return !preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $addr);
	}
	
	function _is_invalid_ip_addr_by_net( $addr )
	{
		return (
			   subStr($addr,0, 2) === '0.'
			|| subStr($addr,0, 3) === '39.'
			|| subStr($addr,0, 4) === '127.'
			|| subStr($addr,0, 6) === '128.0.'
			|| subStr($addr,0, 8) === '169.254.'
			|| subStr($addr,0, 8) === '191.255.'
			|| subStr($addr,0, 8) === '192.0.0.'
			|| subStr($addr,0, 8) === '192.0.2.'
			|| subStr($addr,0,10) === '192.88.99.'
			|| subStr($addr,0,12) === '223.255.255.'
			||(subStr($addr,3, 1) ===    '.'
			&& subStr($addr,0, 3)  >= '224')
		);
	}
	
	function _is_empty_ip_addr( $addr )
	{
		$addr = trim($addr);
		return (
			   $addr == ''
			|| preg_match('/^0*\.0*\.0*\.0*$/', $addr)
		);
	}
	
	function _input_validate_ipaddr( &$addr, &$errmsg )
	{
		$errmsg = '';
		if (_is_invalid_ip_addr_by_format( $addr )) {
			$errmsg = __('Ung&uuml;ltige IP-Adresse!');
			return GS_VALIDATION_ERR;
		}
		$addr = _normalize_ip_addr( $addr );
		if ($addr === null) {
			$errmsg = __('Ung&uuml;ltige IP-Adresse!');
			return GS_VALIDATION_EMPTY;
		}
		if (_is_invalid_ip_addr_by_net( $addr )) {
			$errmsg = __('Ung&uuml;ltige IP-Adresse!');
			return GS_VALIDATION_ERR;
		}
		if (preg_match('/255/', $addr)
		||  subStr($addr, -2) === '.0') {
			$errmsg = __('Ung&uuml;ltige IP-Adresse!');
			return GS_VALIDATION_ERR;
		}
		return GS_VALIDATION_OK;
	}
	
	function _input_validate_netmask( &$netmask, &$errmsg )
	{
		$errmsg = '';
		if (! in_array($netmask, array('/8', '/16', '/24'), true)) {
			$errmsg = __('Ung&uuml;ltige Netzmaske!');
			return GS_VALIDATION_ERR;
		}
		return GS_VALIDATION_OK;
	}
	
	function _input_validate_router( &$addr, &$errmsg )
	{
		$errmsg = '';
		if (_is_empty_ip_addr( $addr )) {
			$addr = '';
			$errmsg = __('Leere Router-Adresse!');
			return GS_VALIDATION_EMPTY;
		}
		if (_is_invalid_ip_addr_by_format( $addr )) {
			$errmsg = __('Ung&uuml;ltige Router-Adresse!');
			return GS_VALIDATION_ERR;
		}
		$addr = _normalize_ip_addr( $addr );
		if ($addr === null) {
			$errmsg = __('Ung&uuml;ltige Router-Adresse!');
			return GS_VALIDATION_EMPTY;
		}
		if (_is_invalid_ip_addr_by_net( $addr )) {
			$errmsg = __('Ung&uuml;ltige Router-Adresse!');
			return GS_VALIDATION_ERR;
		}
		if (preg_match('/255/', $addr)
		||  subStr($addr, -2) === '.0') {
			$errmsg = __('Ung&uuml;ltige Router-Adresse!');
			return GS_VALIDATION_ERR;
		}
		return GS_VALIDATION_OK;
	}
	
	function _input_validate_dns( &$addr, &$errmsg )
	{
		$errmsg = '';
		if (_is_empty_ip_addr( $addr )) {
			$addr = '';
			$errmsg = __('Leere DNS-Server-Adresse!');
			return GS_VALIDATION_EMPTY;
		}
		if (_is_invalid_ip_addr_by_format( $addr )) {
			$errmsg = __('Ung&uuml;ltige DNS-Server-Adresse!');
			return GS_VALIDATION_ERR;
		}
		$addr = _normalize_ip_addr( $addr );
		if ($addr === null) {
			$errmsg = __('Ung&uuml;ltige DNS-Server-Adresse!');
			return GS_VALIDATION_EMPTY;
		}
		if (_is_invalid_ip_addr_by_net( $addr )) {
			$errmsg = __('Ung&uuml;ltige DNS-Server-Adresse!');
			return GS_VALIDATION_ERR;
		}
		if (preg_match('/255/', $addr)
		||  subStr($addr, -2) === '.0') {
			$errmsg = __('Ung&uuml;ltige DNS-Server-Adresse!');
			return GS_VALIDATION_ERR;
		}
		return GS_VALIDATION_OK;
	}
	
	function _input_validate_ntp( &$addr, &$errmsg )
	{
		$errmsg = '';
		if (_is_empty_ip_addr( $addr )) {
			$addr = '';
			$errmsg = __('Leere NTP-Server-Adresse!');
			return GS_VALIDATION_EMPTY;
		}
		if (preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $addr)) {
			# is IP address
			$addr = _normalize_ip_addr( $addr );
			if ($addr === null) {
				$errmsg = __('Ung&uuml;ltige NTP-Server-Adresse!');
				return GS_VALIDATION_EMPTY;
			}
			if (_is_invalid_ip_addr_by_net( $addr )) {
				$errmsg = __('Ung&uuml;ltige NTP-Server-Adresse!');
				return GS_VALIDATION_ERR;
			}
			if (preg_match('/255/', $addr)
			||  subStr($addr, -2) === '.0') {
				$errmsg = __('Ung&uuml;ltige NTP-Server-Adresse!');
				return GS_VALIDATION_ERR;
			}
		} else {
			# is name
			$addr = preg_replace('[^a-z0-9.\-_]', '', strToLower($addr));
			/*
			$addrs = getHostByNameL( $addr );
			if (! is_array($addrs) || count($addrs) < 1) {
				$errmsg = 'NTP-Server-Adresse kann nicht aufgel&ouml;st werden!';
				return GS_VALIDATION_WARN;
				# might be resolvable with the new network settings
			}
			*/
		}
		return GS_VALIDATION_OK;
	}
	
	function _complain_html( $errmsg, $ignorable=false )
	{
		global $errors_html;
		$errors_html[] = '<p style="border:2px solid #f00; color: #b00; padding:0.3em; margin:0.4em 0 0.3em 0;">'. ($ignorable ? 'Warnung!' : 'Fehler!') .' '. $errmsg .'</p>';
	}
	
	
	
	$err_cnt  = 0;
	$warn_cnt = 0;
	
	$form_ipaddr =
		@$_REQUEST['ipaddr'][0] .'.'.
		@$_REQUEST['ipaddr'][1] .'.'.
		@$_REQUEST['ipaddr'][2] .'.'.
		@$_REQUEST['ipaddr'][3] ;
	$validation_result = _input_validate_ipaddr( $form_ipaddr, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		$err_cnt++;
		_complain_html( $errmsg );
	}
	
	$form_netmask = @$_REQUEST['netmask'];
	$validation_result = _input_validate_netmask( $form_netmask, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		$err_cnt++;
		_complain_html( $errmsg );
	}
	
	$form_router =
		@$_REQUEST['router'][0] .'.'.
		@$_REQUEST['router'][1] .'.'.
		@$_REQUEST['router'][2] .'.'.
		@$_REQUEST['router'][3] ;
	$validation_result = _input_validate_router( $form_router, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_EMPTY) {
			//$warn_cnt++;
			//_complain_html( $errmsg, true );
			$form_router = '';
		} else {
			$err_cnt++;
			_complain_html( $errmsg );
		}
	}
	
	$form_dns1 =
		@$_REQUEST['dns1'][0] .'.'.
		@$_REQUEST['dns1'][1] .'.'.
		@$_REQUEST['dns1'][2] .'.'.
		@$_REQUEST['dns1'][3] ;
	$validation_result = _input_validate_dns( $form_dns1, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		$err_cnt++;
		_complain_html( $errmsg .' (1)' );
	}
	
	$form_dns2 =
		@$_REQUEST['dns2'][0] .'.'.
		@$_REQUEST['dns2'][1] .'.'.
		@$_REQUEST['dns2'][2] .'.'.
		@$_REQUEST['dns2'][3] ;
	$validation_result = _input_validate_dns( $form_dns2, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_EMPTY) {
			$form_dns2 = '';
		} else {
			$err_cnt++;
			_complain_html( $errmsg .' (2)' );
		}
	}
	
	$form_ntp1 = @$_REQUEST['ntp1'];
	$validation_result = _input_validate_ntp( $form_ntp1, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_WARN) {
			$warn_cnt++;
			_complain_html( $errmsg .' (1)', true );
		} else {
			$err_cnt++;
			_complain_html( $errmsg .' (1)' );
		}
	}
	
	$form_ntp2 = @$_REQUEST['ntp2'];
	$validation_result = _input_validate_ntp( $form_ntp2, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_EMPTY) {
			$form_ntp2 = '';
		} elseif ($validation_result === GS_VALIDATION_WARN) {
			$warn_cnt++;
			_complain_html( $errmsg .' (2)', true );
		} else {
			$err_cnt++;
			_complain_html( $errmsg .' (2)' );
		}
	}
	
	$form_ntp3 = @$_REQUEST['ntp3'];
	$validation_result = _input_validate_ntp( $form_ntp3, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_EMPTY) {
			$form_ntp3 = '';
		} elseif ($validation_result === GS_VALIDATION_WARN) {
			$warn_cnt++;
			_complain_html( $errmsg .' (3)', true );
		} else {
			$err_cnt++;
			_complain_html( $errmsg .' (3)' );
		}
	}
	
	$form_ntp4 = @$_REQUEST['ntp4'];
	$validation_result = _input_validate_ntp( $form_ntp4, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_EMPTY) {
			$form_ntp4 = '';
		} elseif ($validation_result === GS_VALIDATION_WARN) {
			$warn_cnt++;
			_complain_html( $errmsg .' (4)', true );
		} else {
			$err_cnt++;
			_complain_html( $errmsg .' (4)' );
		}
	}
	
	
	if ($err_cnt < 1
	&&  ($warn_cnt < 1 || @$_REQUEST['dont_warn'])) {
		
		# some useful net calculations
		#
		
		$netmask_length = subStr($form_netmask, 1);  # first char is "/"
		$dotted_netmask = ipv4_mask_length_to_dotted($netmask_length);
		if (! $dotted_netmask) $dotted_netmask = '255.255.0.0';
		
		$dotted_network = ipv4_net_by_addr_and_mask( $form_ipaddr, $dotted_netmask );
		if ($dotted_network === null
		||  $dotted_network === false
		||  $dotted_network === '') {
			$dotted_network = '0.0.0.0';
		}
		
		$dotted_bcastaddr = ipv4_bcast_by_addr_and_mask( $form_ipaddr, $dotted_netmask );
		if ($dotted_network === null
		||  $dotted_network === false
		||  $dotted_network === '') {
			$dotted_network = '255.255.255.255';
		}
		
		
		# save the changes to the keyvals
		#
		
		//if ($form_ipaddr   !== $current_ipaddr )
		gs_keyval_set('vlan_0_ipaddr' , $form_ipaddr );
		
		if ($form_netmask  !== $current_netmask)
			gs_keyval_set('vlan_0_netmask', $form_netmask);
		if ($form_router   !== $current_router )
			gs_keyval_set('vlan_0_router' , $form_router );
		if ($form_dns1     !== $current_dns1   )
			gs_keyval_set('vlan_0_dns1'   , $form_dns1   );
		if ($form_dns2     !== $current_dns2   )
			gs_keyval_set('vlan_0_dns2'   , $form_dns2   );
		if ($form_ntp1     !== $current_ntp1   )
			gs_keyval_set('vlan_0_ntp1'   , $form_ntp1   );
		if ($form_ntp2     !== $current_ntp2   )
			gs_keyval_set('vlan_0_ntp2'   , $form_ntp2   );
		if ($form_ntp3     !== $current_ntp3   )
			gs_keyval_set('vlan_0_ntp3'   , $form_ntp3   );
		if ($form_ntp4     !== $current_ntp4   )
			gs_keyval_set('vlan_0_ntp4'   , $form_ntp4   );
		
		
		# generate /etc/network/interfaces
		#
		
		$interface_basename = trim(gs_keyval_get('vlan_0_interface_base'));
		if (! $interface_basename) $interface_basename = 'eth0';
		
		$conf = 'allow-hotplug '. $interface_basename ."\n";
		$conf.= 'iface '. $interface_basename .' inet static' ."\n";
		//$conf.= "\t". 'network '. $dotted_network ."\n";
		//$conf.= "\t". 'broadcast '. $dotted_bcastaddr ."\n";
		$conf.= "\t". 'netmask '. $dotted_netmask ."\n";
		$conf.= "\t". 'address '. $form_ipaddr ."\n";
		
		if ($form_router)
			$conf.= "\t". 'gateway '. $form_router ."\n";
		if ($form_dns1 || $form_dns2) {
			//$conf.= "\t". '# dns-* options are implemented by the resolvconf package, if installed' ."\n";
			$conf.= "\t". 'dns-nameservers';
			if ($form_dns1) $conf.= ' '.$form_dns1;
			if ($form_dns2) $conf.= ' '.$form_dns2;
			$conf.= "\n";
		}
		
		$conf.= "\n";
		$conf.= 'auto '. $interface_basename .':zc' ."\n";
		$conf.= 'iface '. $interface_basename .':zc inet static' ."\n";
		$conf.= "\t". 'address '. '169.254.1.131' ."\n";
		$conf.= "\t". 'netmask '. '255.255.0.0' ."\n";
		$conf.= "\n";
		
		
		$tpl_file = '/var/lib/gemeinschaft/setup/etc-network-interfaces-tpl';
		$err=0; $out=array();
		@exec( 'sudo chmod a+r '. qsa($tpl_file) .' 2>>/dev/null', $out, $err );
		$tpl = gs_file_get_contents($tpl_file);
		$tpl = preg_replace('/\r\n?/', "\n", $tpl);
		$tpl = trim(preg_replace('/^[ \t]*#[^\n]*\n?/m', '', $tpl));
		$tpl = preg_replace('/\n{3,}/', "\n\n", $tpl);
		$tpl = preg_replace('/^[ \t]*__GEMEINSCHAFT_INTERFACES__/m', $conf, $tpl);
		
		$data = '# AUTO-GENERATED BY GEMEINSCHAFT' ."\n";
		$data.= '# template:' ."\n";
		$data.= '# '. $tpl_file ."\n";
		$data.= "\n";
		$data.= $tpl ."\n";
		
		$cmd = 'echo -n '. qsa($data) .' > '. qsa('/etc/network/interfaces') .' 2>>/dev/null';
		$err=0; $out=array();
		@exec( 'sudo sh -c '. qsa($cmd) .' 2>>/dev/null', $out, $err );
		
		
		# generate /etc/resolv.conf
		#
		
		$have_bind =
			   file_exists('/etc/init.d/bind9')
			|| file_exists('/etc/init.d/bind');
		
		$conf = '';
		if ($have_bind) $conf.= 'nameserver 127.0.0.1' ."\n";
		if ($form_dns1) $conf.= 'nameserver '. $form_dns1 ."\n";
		if ($form_dns2) $conf.= 'nameserver '. $form_dns2 ."\n";
		
		$tpl_file = '/var/lib/gemeinschaft/setup/etc-resolv.conf-tpl';
		$err=0; $out=array();
		@exec( 'sudo chmod a+r '. qsa($tpl_file) .' 2>>/dev/null', $out, $err );
		$tpl = gs_file_get_contents($tpl_file);
		$tpl = preg_replace('/\r\n?/', "\n", $tpl);
		$tpl = trim(preg_replace('/^[ \t]*#[^\n]*\n?/m', '', $tpl));
		$tpl = preg_replace('/\n{3,}/', "\n\n", $tpl);
		$tpl = preg_replace('/^[ \t]*__GEMEINSCHAFT_RESOLVCONF__/m', $conf, $tpl);
		
		$data = '# AUTO-GENERATED BY GEMEINSCHAFT' ."\n";
		$data.= '# template:' ."\n";
		$data.= '# '. $tpl_file ."\n";
		$data.= "\n";
		$data.= $tpl ."\n";
		
		$cmd = 'echo -n '. qsa($data) .' > '. qsa('/etc/resolv.conf') .' 2>>/dev/null';
		$err=0; $out=array();
		@exec( 'sudo sh -c '. qsa($cmd) .' 2>>/dev/null', $out, $err );
		
		
		# generate /etc/default/ntp
		#
		
		$tpl_file = '/var/lib/gemeinschaft/setup/etc-default-ntp-tpl';
		$err=0; $out=array();
		@exec( 'sudo chmod a+r '. qsa($tpl_file) .' 2>>/dev/null', $out, $err );
		$tpl = gs_file_get_contents($tpl_file);
		$tpl = preg_replace('/\r\n?/', "\n", $tpl);
		$tpl = trim(preg_replace('/^[ \t]*#[^\n]*\n?/m', '', $tpl));
		$tpl = preg_replace('/\n{3,}/', "\n\n", $tpl);
		
		$data = '# AUTO-GENERATED BY GEMEINSCHAFT' ."\n";
		$data.= '# template:' ."\n";
		$data.= '# '. $tpl_file ."\n";
		$data.= "\n";
		$data.= $tpl ."\n";
		
		$cmd = 'echo -n '. qsa($data) .' > '. qsa('/etc/default/ntp') .' 2>>/dev/null';
		$err=0; $out=array();
		@exec( 'sudo sh -c '. qsa($cmd) .' 2>>/dev/null', $out, $err );
		
		
		# generate /etc/ntp.conf
		#
		
		$conf_s = '';
		if ($form_ntp1) $conf_s.= 'server '. $form_ntp1 .' iburst' ."\n";
		if ($form_ntp2) $conf_s.= 'server '. $form_ntp2 .' iburst' ."\n";
		if ($form_ntp3) $conf_s.= 'server '. $form_ntp3 .' iburst' ."\n";
		if ($form_ntp4) $conf_s.= 'server '. $form_ntp4 .' iburst' ."\n";
		# (ntp on Debian does not support the "dynamic" keyword)
		
		$conf_c = 'restrict '. $dotted_network .' mask '. $dotted_netmask .' nomodify noquery nopeer notrap' ."\n";
		$conf_c.= 'broadcast '. $dotted_bcastaddr ."\n";
		
		$tpl_file = '/var/lib/gemeinschaft/setup/etc-ntp.conf-tpl';
		$err=0; $out=array();
		@exec( 'sudo chmod a+r '. qsa($tpl_file) .' 2>>/dev/null', $out, $err );
		$tpl = gs_file_get_contents($tpl_file);
		$tpl = preg_replace('/\r\n?/', "\n", $tpl);
		$tpl = trim(preg_replace('/^[ \t]*#[^\n]*\n?/m', '', $tpl));
		$tpl = preg_replace('/\n{3,}/', "\n\n", $tpl);
		$tpl = preg_replace('/^[ \t]*__GEMEINSCHAFT_NTPCONF_SERVERS__/m', $conf_s, $tpl);
		$tpl = preg_replace('/^[ \t]*__GEMEINSCHAFT_NTPCONF_CLIENTS__/m', $conf_c, $tpl);
		
		$data = '# AUTO-GENERATED BY GEMEINSCHAFT' ."\n";
		$data.= '# template:' ."\n";
		$data.= '# '. $tpl_file ."\n";
		$data.= "\n";
		$data.= $tpl ."\n";
		
		$cmd = 'echo -n '. qsa($data) .' > '. qsa('/etc/ntp.conf') .' 2>>/dev/null';
		$err=0; $out=array();
		@exec( 'sudo sh -c '. qsa($cmd) .' 2>>/dev/null', $out, $err );
		
		
		# generate /etc/dhcp3/dhcpd.conf
		#
		
		$tpl_file = '/var/lib/gemeinschaft/setup/etc-dhcp3-dhcpd.conf-tpl';
		$err=0; $out=array();
		@exec( 'sudo chmod a+r '. qsa($tpl_file) .' 2>>/dev/null', $out, $err );
		$tpl = gs_file_get_contents($tpl_file);
		$tpl = preg_replace('/\r\n?/', "\n", $tpl);
		$tpl = trim(preg_replace('/^[ \t]*#[^\n]*\n?/m', '', $tpl));
		$tpl = preg_replace('/\n{3,}/', "\n\n", $tpl);
		
		$exclude_from_range = array( $dotted_network );
		
		$tpl = preg_replace('/__GEMEINSCHAFT_DHCPD_OPTION_NETMASK__/', 'option subnet-mask '. $dotted_netmask .';', $tpl);
		
		$tpl = preg_replace('/__GEMEINSCHAFT_DHCPD_OPTION_BCASTADDR__/', 'option broadcast-address '. $dotted_bcastaddr .';', $tpl);
		
		$arr = array();
		if ($form_router) {$arr[] = $form_router; $exclude_from_range[] = $form_router;}
		$tpl = preg_replace('/__GEMEINSCHAFT_DHCPD_OPTION_ROUTERS__/', (count($arr)>0 ? 'option routers '. $form_router .';' : ''), $tpl);
		
		$arr = array();
		if ($form_dns1) {$arr[] = $form_dns1; $exclude_from_range[] = $form_dns1;}
		if ($form_dns2) {$arr[] = $form_dns2; $exclude_from_range[] = $form_dns2;}
		$tpl = preg_replace('/__GEMEINSCHAFT_DHCPD_OPTION_DNSSERVERS__/', (count($arr)>0 ? 'option domain-name-servers '. implode(', ', $arr) .';' : ''), $tpl);
		
		$arr = array();
		$_arr = array($form_ipaddr, $form_ntp1, $form_ntp2, $form_ntp3, $form_ntp4);
		foreach ($_arr as $form_ntp_x) {
			if ($form_ntp_x) {
				if (preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $form_ntp_x)) {
					$exclude_from_range[] = $form_ntp_x;
				}
				if (preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $form_ntp_x)
				||  preg_match('/^[a-zA-Z]/', $form_ntp_x)) {
					# see http://bugs.donarmstrong.com/cgi-bin/bugreport.cgi?bug=78928
					# still not fixed in dhcp3-server 3.0.4-13 on Debian 4 although they say so
					$arr[] = $form_ntp_x;
				}
			}
		}
		$tpl = preg_replace('/__GEMEINSCHAFT_DHCPD_OPTION_NTPSERVERS__/', (count($arr)>0 ? 'option ntp-servers '. implode(', ', $arr) .';' : ''), $tpl);
		
		$tpl = preg_replace('/__GEMEINSCHAFT_DHCPD_SUBNET__/', $dotted_network, $tpl);
		$tpl = preg_replace('/__GEMEINSCHAFT_DHCPD_NETMASK__/', $dotted_netmask, $tpl);
		$tpl = preg_replace('/__GEMEINSCHAFT_DHCPD_PROVADDR__/', $form_ipaddr, $tpl);
		$tpl = preg_replace('/__GEMEINSCHAFT_DHCPD_PROVPORT__/', 80, $tpl);
		
		$dhcpd_ranges = array();
		$cmd = '/opt/gemeinschaft/sbin/net-subtract-gaps --net '. qsa($dotted_network .'/'. $netmask_length);
		$exclude_from_range = array_flip(array_flip($exclude_from_range));
		foreach ($exclude_from_range as $_ip) {
			$cmd.= ' --excl '. qsa($_ip .'/32');
		}
		$cmd.= ' --single';
		$err=0; $out=array();
		@exec( $cmd .' 2>>/dev/null', $out, $err );
		if ($err !== 0) {
			$dhcpd_ranges[] = 'range 192.168.1.10 192.168.1.254;';
		} else {
			
			foreach ($out as $line) {
				if (preg_match('/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) *- *([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', trim($line), $m)) {
					$dhcpd_ranges[] = 'range '. $m[1] .' '. $m[2] .';';
				}
			}
			if (count($dhcpd_ranges) < 1)
				$dhcpd_ranges[] = 'range 192.168.1.10 192.168.1.254;';
		}
		$tpl = preg_replace('/__GEMEINSCHAFT_DHCPD_RANGES__/', implode("\n\t", $dhcpd_ranges), $tpl);
		
		$data = '# AUTO-GENERATED BY GEMEINSCHAFT' ."\n";
		$data.= '# template:' ."\n";
		$data.= '# '. $tpl_file ."\n";
		$data.= "\n";
		$data.= $tpl ."\n";
		
		$cmd = 'echo -n '. qsa($data) .' > '. qsa('/etc/dhcp3/dhcpd.conf') .' 2>>/dev/null';
		$err=0; $out=array();
		@exec( 'sudo sh -c '. qsa($cmd) .' 2>>/dev/null', $out, $err );
		
		
		# update database table "hosts"
		#
		require_once( GS_DIR .'inc/db_connect.php' );
		$db = gs_db_master_connect();
		if ($db) {
			$tmp = $db->escape($form_ipaddr);
			switch ($GS_INSTALLATION_TYPE) {
				case 'gpbx': $tmp2 = 'GPBX'                  ; break;
				default    : $tmp2 = 'Gemeinschaft (single)' ;
			}
			$tmp2 = $db->escape($tmp2);
			@$db->execute( 'UPDATE `hosts` SET `host`=\''. $tmp .'\', `comment`=\''. $tmp2 .'\' WHERE `id`=1' );
			@$db->execute( 'UPDATE `hosts` SET `host`=\''. $tmp .'\', `comment`=\''. $tmp2 .'\'' );
		}
		
		# for the GPBX {
		if (@file_exists('/usr/local/bin/gpbx-db-dump')) {
			$err=0; $out=array();
			@exec( 'sudo /usr/local/bin/gpbx-db-dump save 2>>/dev/null', $out, $err );
		}
		# }
		
		
		/*
		$has_netif_changes =
			(  $form_ipaddr  !== $current_ipaddr
			|| $form_netmask !== $current_netmask
			);
		*/
		
		$saved = true;
		gs_keyval_set('setup_net_has_changes', 'yes');
	}
}


if ($action !== 'save2') {
	if (! $saved) {
		if ( $action === '' || $action === 'save') {
			echo '<p>' ,"\n";
			switch ($GS_INSTALLATION_TYPE) {
				case 'gpbx':
					echo __('Bitte stellen Sie die gew&uuml;nschte IP-Adresse der GPBX ein. Geben Sie au&szlig;erdem die Netzmaske Ihres Netzwerkes sowie die Adresse Ihres Routers, Nameservers und Zeitservers an.') ,"\n";
					echo __('Die Netzwerkkarte der GPBX wird dann auf diese Werte eingestellt.') ,"\n";
					break;
				default:
					echo __('Bitte stellen Sie die gew&uuml;nschte IP-Adresse des Gemeinschafts-Servers ein. Geben Sie au&szlig;erdem die Netzmaske Ihres Netzwerkes sowie die Adresse Ihres Routers, Nameservers und Zeitservers an.') ,"\n";
					echo __('Die Netzwerkkarte wird dann auf diese Werte eingestellt.') ,"\n";
					break;
			}
			echo '</p>' ,"\n";
			echo '<hr />' ,"\n";
		}
		$disabled = '';
	} else {
		echo '<p>' ,"\n";
		echo __('Die Netzwerk-Einstellungen wurden gespeichert, sind aber noch nicht aktiv.') ,"\n";
		echo '</p>' ,"\n";
		echo '<hr />' ,"\n";
		$disabled = ' disabled="disabled"';
	}
	echo '<br />' ,"\n";
}


if ($action === '' || $action === 'save') {
?>

<form method="post" action="<?php echo GS_URL_PATH ,'setup/?step=network'; ?>">
<input type="hidden" name="action" value="save" />
<table cellspacing="1">
<tbody>
<tr>
	<th width="100"><?php echo __('IP-Adresse'); ?></th>
	<td>
<?php
		$ipaddr_parts = explode('.', $form_ipaddr);
		for ($i=0; $i<=3; ++$i) {
			$part = (int)lTrim(@$ipaddr_parts[$i], '0 ');
			echo '<input type="text" name="ipaddr[',$i,']" size="3" maxlength="3" class="r pre" value="', $part ,'"', $disabled ,' />';
			if ($i < 3) echo '.';
		}
		echo ' &nbsp; <small>(', '<a target="_blank" href="', __('http://de.wikipedia.org/wiki/IP-Adresse') ,'">?</a>' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo __('Netzmaske'); ?></th>
	<td>
<?php
		echo '<select name="netmask" class="pre"', $disabled ,'>' ,"\n";
		$options = array(
			 8 => '255.0.0.0',
			16 => '255.255.0.0',
			24 => '255.255.255.0'
		);
		foreach ($options as $net => $mask) {
			$net = '/'.$net;
			echo '<option value="',$net,'"', ($net===$form_netmask ? ' selected="selected"' : '') ,'>',$net,' (',$mask,')</option>' ,"\n";
		}
		echo '</select>' ,"\n";
		echo ' &nbsp; <small>(', '<a target="_blank" href="', __('http://de.wikipedia.org/wiki/Netzmaske') ,'">?</a>' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo __('Router'); ?></th>
	<td>
<?php
		if (trim($form_router) != '') {
			$router_parts = explode('.', $form_router);
			for ($i=0; $i<=3; ++$i) {
				$router_parts[$i] = (int)lTrim(@$router_parts[$i], '0 ');
			}
		} else {
			$router_parts = array('','','','');
		}
		for ($i=0; $i<=3; ++$i) {
			echo '<input type="text" name="router[',$i,']" size="3" maxlength="3" class="r pre" value="', @$router_parts[$i] ,'"', $disabled ,' />';
			if ($i < 3) echo '.';
		}
		echo ' &nbsp; <small>(', '<a target="_blank" href="', __('http://de.wikipedia.org/wiki/Router') ,'">?</a>' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo __('DNS-Server') ,' (1)'; ?></th>
	<td>
<?php
		if (trim($form_dns1) != '') {
			$dns1_parts = explode('.', $form_dns1);
			for ($i=0; $i<=3; ++$i) {
				$dns1_parts[$i] = (int)lTrim(@$dns1_parts[$i], '0 ');
			}
		} else {
			$dns1_parts = array('','','','');
		}
		for ($i=0; $i<=3; ++$i) {
			echo '<input type="text" name="dns1[',$i,']" size="3" maxlength="3" class="r pre" value="', @$dns1_parts[$i] ,'"', $disabled ,' />';
			if ($i < 3) echo '.';
		}
?>
	</td>
</tr>
<tr>
	<th><?php echo __('DNS-Server') ,' (2)'; ?></th>
	<td>
<?php
		if (trim($form_dns2) != '') {
			$dns2_parts = explode('.', $form_dns2);
			for ($i=0; $i<=3; ++$i) {
				$dns2_parts[$i] = (int)lTrim(@$dns2_parts[$i], '0 ');
			}
		} else {
			$dns2_parts = array('','','','');
		}
		for ($i=0; $i<=3; ++$i) {
			echo '<input type="text" name="dns2[',$i,']" size="3" maxlength="3" class="r pre" value="', @$dns2_parts[$i] ,'"', $disabled ,' />';
			if ($i < 3) echo '.';
		}
		echo ' &nbsp; <small>(', __('optional') ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo __('NTP-Server') ,' (1)'; ?></th>
	<td>
<?php
		echo '<input type="text" name="ntp1" size="30" maxlength="50" class="pre" value="', $form_ntp1 ,'"', $disabled ,' />' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo __('NTP-Server') ,' (2)'; ?></th>
	<td>
<?php
		echo '<input type="text" name="ntp2" size="30" maxlength="50" class="pre" value="', $form_ntp2 ,'"', $disabled ,' />' ,"\n";
		echo ' &nbsp; <small>(', __('optional') ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo __('NTP-Server') ,' (3)'; ?></th>
	<td>
<?php
		echo '<input type="text" name="ntp3" size="30" maxlength="50" class="pre" value="', $form_ntp3 ,'"', $disabled ,' />' ,"\n";
		echo ' &nbsp; <small>(', __('optional') ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo __('NTP-Server') ,' (4)'; ?></th>
	<td>
<?php
		echo '<input type="text" name="ntp4" size="30" maxlength="50" class="pre" value="', $form_ntp4 ,'"', $disabled ,' />' ,"\n";
		echo ' &nbsp; <small>(', __('optional') ,')</small>' ,"\n";
?>
	</td>
</tr>
<?php if (! $saved) { ?>
<tr>
	<td class="transp">&nbsp;</td>
	<td class="transp">
<?php
	if ($action === 'save') {
		if (@is_array($errors_html) && count($errors_html) > 0) {
			foreach ($errors_html as $error_html) {
				echo $error_html ,"\n";
			}
		} else {
			echo '<br />',"\n";
		}
		if ($err_cnt < 1 && $warn_cnt > 0) {
			echo '<input type="checkbox" name="dont_warn" id="ipt-dont_warn" value="1" /> <label for="ipt-dont_warn">', __('Warnungen ignorieren') ,'</label><br />' ,"\n";
		}
	} else {
		echo '<br />',"\n";
	}
?>
		<input type="reset" value="<?php echo __('Verwerfen'); ?>" style="margin-top:0.3em;" />
		<input type="submit" value="<?php echo __('Speichern'); ?>" style="margin-top:0.3em;" />
	</td>
</tr>
<?php } ?>
</tbody>
</table>
</form>
<?php
}


if ($action !== 'save2') {
	if ($saved) {
?>

<p align="center">
	<?php echo __('Wie wollen Sie fortfahren?'); ?>
</p>
<div style="margin:0 4em;">
	
	<form method="post" action="<?php echo GS_URL_PATH ,'setup/?step=network'; ?>">
	<input type="hidden" name="action" value="save2" />
	
	<input type="radio" name="save2_action" id="ipt-save2_action-back" value="" />
	<label for="ipt-save2_action-back"><?php echo __('Einstellungen nochmal ver&auml;ndern'); ?></label><br />
	
	<input type="radio" name="save2_action" id="ipt-save2_action-reboot" value="reboot" checked="checked" />
	<label for="ipt-save2_action-reboot"><?php echo __('Einstellungen &uuml;bernehmen und System neustarten'); ?></label><br />
	
	<input type="radio" name="save2_action" id="ipt-save2_action-shutdown" value="shutdown" />
	<label for="ipt-save2_action-shutdown"><?php echo __('Einstellungen &uuml;bernehmen und System herunterfahren'); ?></label><br />
	
	<div align="right">
	<input type="submit" value="<?php echo __('OK'); ?>" />
	</div>
	
	</form>
</div>

<?php
	}
?>

<br />
<hr />
<?php
	
	$can_continue =
		(  trim(gs_keyval_get('setup_net_has_changes')) !== 'yes'
		&& $action !== 'save2' );
	
	switch ($GS_INSTALLATION_TYPE) {
		# "system-check" unnecessary for the GPBX
		case 'gpbx': $prev_step = 'user'       ; break;
		default    : $prev_step = 'system-check'; break;
	}
	echo '<div class="fl"><a href="', GS_URL_PATH ,'setup/?step=',$prev_step ,'">', __('zur&uuml;ck') ,'</a></div>' ,"\n";
	echo '<div class="fr">';
	if ($can_continue)
		echo '<a href="', GS_URL_PATH ,'setup/?step=dhcpd"><big>', __('weiter') ,'</big></a>';
	else
		echo '<span style="color:#999;">', __('weiter') ,'</span>';
	echo '</div>' ,"\n";
	echo '<br class="nofloat" />' ,"\n";

}

else {
	if ($save2_action === 'reboot') {
?>

<p align="center">
	<?php echo __('Das System wird jetzt neugestartet.'); ?><br />
	<?php echo __('Bitte haben Sie etwas Geduld. Vorzeitiges Ausschalten kann zu Datenverlust f&uuml;hren.'); ?>
</p>
<p align="center">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>img/wait-net.gif" />
</p>
<br />

<?php
	}
	elseif ($save2_action === 'shutdown') {
?>

<p align="center">
	<?php echo __('Das System wird jetzt heruntergefahren.'); ?><br />
	<?php echo __('Bitte haben Sie etwas Geduld. Vorzeitiges Ausschalten kann zu Datenverlust f&uuml;hren.'); ?>
</p>
<p align="center">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>img/wait-net.gif" />
</p>
<br />

<?php
	}
}


?>
</div>

<?php

if ($action === 'save2') {
	if ($save2_action === 'reboot'
	||  $save2_action === 'shutdown') {
		
		# nach dem Aendern der Netzwerkeinstellungen muss Asterisk neu
		# gestartet werden
		//@exec( 'sudo /opt/gemeinschaft/sbin/start-asterisk 1>>/dev/null 2>>/dev/null' );
		# vorher muss das evtl. geanderte Netzwerk-Interface (eth0)
		# per ifup --force eth0 geupdated werden
		
		
		
		gs_keyval_set('setup_net_has_changes', 'no');
		
		if (@file_exists('/usr/sbin/gs-pre-shutdown')) {
			$err=0; $out=array();
			@exec( 'sudo /usr/sbin/gs-pre-shutdown 2>>/dev/null', $out, $err );
		}
		
		if     ($save2_action==='reboot'  ) $cmd = '/sbin/shutdown -r now';
		elseif ($save2_action==='shutdown') $cmd = '/sbin/shutdown -h -P now';
		//@exec( 'sudo sh -c \'sleep 2; /opt/gemeinschaft/sbin/gpbx-pre-shutdown 1>>/dev/null 2>>/dev/null; '. $cmd .' 1>>/dev/null 2>>/dev/null &\' 0<&- 1>&- 2>&- &' );
		@exec( 'sudo sh -c \'sleep 2; '. $cmd .' 1>>/dev/null 2>>/dev/null &\' 0<&- 1>&- 2>&- &' );
		
	}
}

?>

<script type="text/javascript">
try{
	gs_prevent_unsaved_changes( '<?php echo __('Sie haben noch nicht alle \u00C4nderungen gespeichert! Sie sollten zuerst speichern oder die \u00C4nderungen verwerfen.'); ?>' );
}catch(e){}
</script>