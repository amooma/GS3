<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2009, LocaNet oHG http://www.loca.net/
*
* Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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

# caution: earlier versions of Snom firmware do not like
# indented XML

define( 'GS_VALID', true );  /// this is a parent file

require_once( '../../inc/conf.php' );
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_numbers_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_vm_activate.php' );
require_once( GS_DIR .'inc/gs-fns/gs_user_watchedmissed.php' );
require_once( GS_DIR .'inc/gs-fns/gs_ami_events.php' );
require_once( GS_DIR .'inc/find_executable.php' );
require_once( GS_DIR .'inc/get-listen-to-ids.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
require_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );
include_once( GS_DIR .'inc/string.php' );

require_once(dirname(__FILE__).'/CFPropertyList-1.0.1/CFPropertyList.php');

//header( 'Content-Type: application/x-snom-xml; charset=utf-8' );
# the Content-Type header is ignored by the Snom
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

function _to_id3tag_ascii( $str )
{
	return preg_replace(
		'/[^\x20-\x5D\x5F-\x7E]/', '',  # remove "^"
		gs_utf8_decompose_to_ascii( $str ));
}

function xml2assoc( $xml )
{
	$tree = null;
	while( $xml->read() )
	{
		switch ( $xml->nodeType )
		{
			case XMLReader::END_ELEMENT: return $tree;
			case XMLReader::ELEMENT:
				$node = array( 'tag' => $xml->name, 'value' => $xml->isEmptyElement ? '' : xml2assoc( $xml ) );
				if( $xml->hasAttributes )
					while( $xml->moveToNextAttribute() )
						$node['attributes'][$xml->name] = $xml->value;
				$tree[] = $node;
			break;
			case XMLReader::TEXT:
			case XMLReader::CDATA:
				$tree .= $xml->value;
		}
	}
	return $tree;
}

function setForward( $userinfo, $source, $destination, $plist)
{
	$source = (int)$source;
	$destination = (int)$destination;
	
	switch ($source)
	{
		case 0:
			$gs_source = 'internal';
			$gs_case   = 'always';
			$timeout   = 0;
			break;
		case 1:
			$gs_source = 'internal';
			$gs_case   = 'busy';
			$timeout   = 0;
			break;
		case 2:
			$gs_source = 'internal';
			$gs_case   = 'unavail';
			$timeout   = $plist['timeout'];
			break;
		case 3:
			$gs_source = 'internal';
			$gs_case   = 'offline';
			$timeout   = 0;
			break;
		case 4:
			$gs_source = 'external';
			$gs_case   = 'always';
			$timeout   = 0;
			break;
		case 5:
			$gs_source = 'external';
			$gs_case   = 'busy';
			$timeout   = 0;
			break;
		case 5:
			$gs_source = 'external';
			$gs_case   = 'unavail';
			$timeout   = $plist['timeout'];
			break;
		case 7:
			$gs_source = 'external';
			$gs_case   = 'offline';
			$timeout   = 0;
			break;
		default:
			$gs_source = '';
			$gs_case   = '';
			$timeout   = 0;
	}
	
	switch ($destination)
	{
		case 1:
			$gs_type = 'std';
			$number  = $plist['standardNumber'];
			break;
		case 2:
			$gs_type = 'var';
			$number  = $plist['tempNumber'];
			break;
		case 3:
			$gs_type = 'vml';
			$number  = 'vm' . $userinfo['ext'];
			break;
		case 4:
			$gs_type = 'vml';
			$number  = 'vm*' . $userinfo['ext'];
		default:
			$gs_type = '';
			$number  = $plist['standardNumber'];
	}

	gs_log( GS_LOG_NOTICE, 'setting call forward from ' . $gs_source . ' in case of ' . $gs_case . ' to ' .$gs_type . ' (number=' . $number . ', timeout=' . $timeout . ')' );
	gs_callforward_set( $userinfo['user'], $gs_source, $gs_case, $gs_type, $number, $timeout );
	
	if ($gs_type == '')
		$gs_type = 'no';
		
	gs_callforward_activate( $userinfo['user'], $gs_source, $gs_case, $gs_type );
	
}

function returnCFNumber ( $input, $vmtarget )
{
	switch ( $input )
	{
		case 'no':
			return 0;
			break;
		case 'std':
			return 1;
			break;
		case 'var':
			return 2;
			break;
		case 'vml':
			if ( substr($vmtarget, 0, 3 ) == "vm*" )
				return 4;
			else
				return 3;
			break;
		default:
			return 0;
	}
}

$_REQUEST['login_user'] = @$_SERVER['PHP_AUTH_USER'];
$_REQUEST['login_pwd'] = @$_SERVER['PHP_AUTH_PW'];

require_once( GS_DIR .'htdocs/gui/inc/pamal/pamal.php' );

$methods =  explode( ',', GS_GUI_AUTH_METHOD );
array_walk( $methods, 'gs_trim_value' );

foreach ( $methods as &$method ) {
	$PAM = new PAMAL( $method );
	$user = $PAM->getUser();
	if ( $user )
		break;
}
unset( $method );

if (!$user) {
	header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    die('Unauthorized');
}

$userinfo = gs_user_get($user);

$DB = gs_db_slave_connect();

$GS_INSTALLATION_TYPE_SINGLE = gs_get_conf('GS_INSTALLATION_TYPE_SINGLE');
if (! $GS_INSTALLATION_TYPE_SINGLE) {
	# find host
	#
	$rs = $DB->execute(
'SELECT `u`.`host_id` `id`, `h`.`host`
FROM
	`users` `u` LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`)
WHERE
	`u`.`id`='. (int)@$userinfo['id']
	);
	$host = $rs->fetchRow();
	if (! $host)
		die( 'Failed to get host.' );

	$our_host_ids = @gs_get_listen_to_ids();
	if (! is_array($our_host_ids))
		die( 'Failed to get our host IDs.' );

	$user_is_on_this_host = in_array($host['id'], $our_host_ids, true);
} else {
	$user_is_on_this_host = true;
}

if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
{

	switch ($_GET['action']) {

		case 'redirect':
			$tmpfname = tempnam("/tmp", "gs-iphone");
			$handle = fopen($tmpfname, "w");
			$stdin = fopen('php://input', "r");
			while ($chunk = fread($stdin, 1024)) {
				fwrite($handle, $chunk);
			}
			fclose($handle);
			fclose($stdin);
			
			$plist = new CFPropertyList( $tmpfname, CFPropertyList::FORMAT_XML );
			$assoc = $plist->toArray();
			unlink($tmpfname);
			
			if (!$assoc['timeout']) {
				gs_log( GS_LOG_WARNING, 'could not parse Plist from device' );
				header( "HTTP/1.0 500 Internal Server Error" );
				exit;
			}

			setForward( $userinfo, 0, $assoc['sourceAction'][0], $assoc);
			setForward( $userinfo, 1, $assoc['sourceAction'][1], $assoc);
			setForward( $userinfo, 2, $assoc['sourceAction'][2], $assoc);
			setForward( $userinfo, 3, $assoc['sourceAction'][3], $assoc);
			setForward( $userinfo, 4, $assoc['sourceAction'][4], $assoc);
			setForward( $userinfo, 5, $assoc['sourceAction'][5], $assoc);
			setForward( $userinfo, 6, $assoc['sourceAction'][6], $assoc);
			setForward( $userinfo, 7, $assoc['sourceAction'][7], $assoc);
			
			if ( GS_BUTTONDAEMON_USE == true ) {
				gs_diversion_changed_ui( $userinfo['ext'] );
			}
			
			break;

		case 'dial':
			gs_log( GS_LOG_DEBUG, 'dial number ' . $_POST['number']);
			break;
			
		case 'vm_play':
			gs_log( GS_LOG_DEBUG, 'delete vm message '. $_GET['id']);
			$rs = $DB->execute(
'SELECT
	`id`, `host_id`, `folder` `fld`, `file`, `orig_time` `ts`, `dur`, `cidnum`, `cidname`, `listened_to`
FROM `vm_msgs`
WHERE
	`user_id`=' . (int)@$userinfo['id'] . '
	AND `id` =' . (int)@$_GET['id']
			);
			$r = $rs->fetchRow();

			$cmd = GS_DIR .'sbin/vm-local-del '. qsa( @$userinfo['ext'] ) .' '. qsa($r['fld']) .' '. qsa($r['file']);
			$err=0; $out=array();
			if ($user_is_on_this_host) {
				# user is on this host
				@exec( 'sudo '. $cmd .' 2>>/dev/null', $out, $err );
			} else {
				# user is not on this host
				@exec( GS_DIR .'sbin/remote-exec-do '. qsa($host['host']) .' '. qsa($cmd) .' 10 2>>/dev/null', $out, $err );
			}
			gs_log( GS_LOG_DEBUG, "executed " . $cmd );
			
			break;

		default:
			// do nothing
		
	}

}
else
{
	switch ($_GET['action']) {
		
		case 'calls_out':
		case 'calls_in':
		case 'calls_missed':
		
			$typearr = split('_', $_GET['action']);
			$type = $typearr[1];
		
			$DB = gs_db_slave_connect();
			$rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	MAX(`d`.`timestamp`) `ts`, `d`.`number`, `d`.`remote_name`, `d`.`read`,
	`u`.`id` `r_uid`, `u`.`lastname` `r_ln`, `u`.`firstname` `r_fn`
FROM
	`dial_log` `d` LEFT JOIN
	`users` `u` ON (`u`.`id`=`d`.`remote_user_id`)
WHERE
	`d`.`user_id`='. $userinfo['id'] .' AND
	`d`.`type`=\''. $DB->escape($type) .'\' AND
	`d`.`timestamp`>'. (time()-GS_PROV_DIAL_LOG_LIFE) .' AND
	`d`.`number` <> \''. $DB->escape( $userinfo['ext'] ) .'\'
GROUP BY `d`.`number`
ORDER BY `ts` DESC'
			);

			echo '<?xml version="1.0" encoding="UTF-8"?>',"\n";
			echo '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">',"\n";
			echo '<plist version="1.0">',"\n";
			echo '<array>',"\n";
			while ($r = $rs->fetchRow()) {
				if (strlen($r['r_fn']) > 0) {
					$firstname = $r['r_fn'];
					$lastname = $r['r_ln'];
				} else if (strlen($r['remote_name']) >0) {
					$firstname = '';
					$lastname = $r['remote_name'];
				} else {
					$firstname = '';
					$lastname = '';
				}
				echo '	<dict>',"\n";
				echo '		<key>timeStamp</key>',"\n";
				echo '		<integer>', $r['ts'], '</integer>',"\n";
				echo '		<key>firstName</key>',"\n";
				echo '		<string>', htmlEnt($firstname), '</string>',"\n";
				echo '		<key>lastName</key>',"\n";
				echo '		<string>', htmlEnt($lastname), '</string>',"\n";
				echo '		<key>telephoneNumber</key>',"\n";
				if (strlen($r['number']) > 0)
					echo '		<string>', $r['number'], '</string>',"\n";
				else
					echo '		<string>anonym</string>',"\n";
				if ($_GET['action'] == 'calls_missed') {
					echo '		<key>read</key>',"\n";
					echo '		<integer>', $r['read'], '</integer>',"\n";
				}
				echo '	</dict>',"\n";
			}

			echo '</array>',"\n";
			echo '</plist>',"\n";
			
			if ($_GET['action'] == 'calls_missed') {
				gs_user_watchedmissed( $userinfo['id'] );
				if ( GS_BUTTONDAEMON_USE == true ) {
					gs_buttondeamon_missedcalls( $userinfo['ext'] );
				}
				
			}
			
			break;
		
		case 'redirect':

			$forwards = gs_callforward_get( $userinfo['user'] );
			$numbers = gs_user_external_numbers_get( $userinfo['user'] );

			echo '<?xml version="1.0" encoding="UTF-8"?>',"\n";
			echo '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">',"\n";
			echo '<plist version="1.0">',"\n";
			echo '<dict>',"\n";
			echo '	<key>timeout</key>',"\n";
			echo '	<integer>', $forwards['internal']['unavail']['timeout'], '</integer>',"\n";
			echo '	<key>standardNumber</key>',"\n";
			echo '	<string>', $forwards['internal']['always']['number_std'], '</string>',"\n";
			echo '	<key>tempNumber</key>',"\n";
			echo '	<string>', $forwards['internal']['always']['number_var'], '</string>',"\n";
			echo '	<key>externalNumbers</key>',"\n";
			echo '	<array>',"\n";
				foreach($numbers as $number ) {
			echo '		<string>', $number, '</string>',"\n";
				}
			echo '	</array>',"\n";
			echo '	<key>sourceAction</key>',"\n";
			echo '	<array>',"\n";
			echo '		<integer>', returnCFNumber( $forwards['internal']['always']['active'], $forwards['internal']['always']['number_vml']  ), '</integer>',"\n";
			echo '		<integer>', returnCFNumber( $forwards['internal']['busy']['active'], $forwards['internal']['busy']['number_vml']    ), '</integer>',"\n";
			echo '		<integer>', returnCFNumber( $forwards['internal']['unavail']['active'], $forwards['internal']['unavail']['number_vml'] ), '</integer>',"\n";
			echo '		<integer>', returnCFNumber( $forwards['internal']['offline']['active'], $forwards['internal']['offline']['number_vml'] ), '</integer>',"\n";
			echo '		<integer>', returnCFNumber( $forwards['external']['always']['active'], $forwards['external']['always']['number_vml']  ), '</integer>',"\n";
			echo '		<integer>', returnCFNumber( $forwards['external']['busy']['active'], $forwards['external']['busy']['number_vml']    ), '</integer>',"\n";
			echo '		<integer>', returnCFNumber( $forwards['external']['unavail']['active'], $forwards['external']['unavail']['number_vml'] ), '</integer>',"\n";
			echo '		<integer>', returnCFNumber( $forwards['external']['offline']['active'], $forwards['external']['offline']['number_vml'] ), '</integer>',"\n";
			echo '	</array>',"\n";
			echo '</dict>',"\n";
			echo '</plist>',"\n";

			break;
			
		case 'pb_priv':
		
			$DB = gs_db_slave_connect();
			$rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	`id`, `lastname`, `firstname`, `number`
FROM
	`pb_prv` 
WHERE   
	`user_id`='. $DB->escape($userinfo['id']).'
	ORDER BY `lastname`, `firstname`'
				);
			echo '<?xml version="1.0" encoding="UTF-8"?>',"\n";
			echo '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">',"\n";
			echo '<plist version="1.0">',"\n";
			echo '<array>',"\n";
			while ($r = $rs->fetchRow()) {
				echo '	<dict>',"\n";
				echo '		<key>firstName</key>',"\n";
				echo '		<string>', htmlEnt($r['firstname']), '</string>',"\n";
				echo '		<key>lastName</key>',"\n";
				echo '		<string>', htmlEnt($r['lastname']), '</string>',"\n";
				echo '		<key>telephoneNumber</key>',"\n";
				echo '		<string>', $r['number'], '</string>',"\n";
				echo '	</dict>',"\n";
			}

			echo '</array>',"\n";
			echo '</plist>',"\n";
			
			break;
			
		case 'pb_common':
		
			$DB = gs_db_slave_connect();
			$rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	`u`.`firstname` `firstname`, `u`.`lastname` `lastname`, `s`.`name` `number`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`nobody_index` IS NULL AND `u`.`pb_hide` != 1
ORDER BY `u`.`lastname`, `u`.`firstname`'
				);

			echo '<?xml version="1.0" encoding="UTF-8"?>',"\n";
			echo '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">',"\n";
			echo '<plist version="1.0">',"\n";
			echo '<array>',"\n";
			while ($r = $rs->fetchRow()) {
				echo '	<dict>',"\n";
				echo '		<key>firstName</key>',"\n";
				echo '		<string>', htmlEnt($r['firstname']), '</string>',"\n";
				echo '		<key>lastName</key>',"\n";
				echo '		<string>', htmlEnt($r['lastname']), '</string>',"\n";
				echo '		<key>telephoneNumber</key>',"\n";
				echo '		<string>', $r['number'], '</string>',"\n";
				echo '	</dict>',"\n";
			}
			echo '</array>',"\n";
			echo '</plist>',"\n";
			
			break;

		case 'pb_imported':
		
			$DB = gs_db_slave_connect();
			$rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	`firstname`, `lastname`, `number`
FROM
	`pb_ldap`
ORDER BY `lastname`, `firstname`, `user`, `number` DESC'
				);

			echo '<?xml version="1.0" encoding="UTF-8"?>',"\n";
			echo '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">',"\n";
			echo '<plist version="1.0">',"\n";
			echo '<array>',"\n";
			while ($r = $rs->fetchRow()) {
				echo '	<dict>',"\n";
				echo '		<key>firstName</key>',"\n";
				echo '		<string>', htmlEnt($r['firstname']), '</string>',"\n";
				echo '		<key>lastName</key>',"\n";
				echo '		<string>', htmlEnt($r['lastname']), '</string>',"\n";
				echo '		<key>telephoneNumber</key>',"\n";
				echo '		<string>', $r['number'], '</string>',"\n";
				echo '	</dict>',"\n";
			}

			echo '</array>',"\n";
			echo '</plist>',"\n";
			
			break;
			
		case 'vm_list':
			
			$rs = $DB->execute(
'SELECT
	`id`, `host_id`, `folder` `fld`, `file`, `orig_time` `ts`, `dur`, `cidnum`, `cidname`, `listened_to`
FROM `vm_msgs`
WHERE
	`user_id`=' . (int)@$userinfo['id'] . '
ORDER BY `orig_time`'
			);

			echo '<?xml version="1.0" encoding="UTF-8"?>',"\n";
			echo '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">',"\n";
			echo '<plist version="1.0">',"\n";
			echo '<array>',"\n";
			while ($r = $rs->fetchRow()) {
				echo '	<dict>',"\n";
				echo '		<key>id</key>',"\n";
				echo '		<integer>', $r['id'], '</integer>',"\n";
				echo '		<key>cidName</key>',"\n";
				echo '		<string>', htmlEnt($r['cidname']), '</string>',"\n";
				echo '		<key>cidNumber</key>',"\n";
				echo '		<string>', $r['cidnum'], '</string>',"\n";
				echo '		<key>timeStamp</key>',"\n";
				echo '		<integer>', $r['ts'], '</integer>',"\n";
				echo '		<key>length</key>',"\n";
				echo '		<integer>', $r['dur'], '</integer>',"\n";
				echo '		<key>listened_to</key>',"\n";
				echo '		<integer>', $r['listened_to'], '</integer>',"\n";
				echo '	</dict>',"\n";
			}

			echo '</array>',"\n";
			echo '</plist>',"\n";
		
			break;
			
		case 'vm_play':
			$DB = gs_db_master_connect();

			$rs = $DB->execute(
'SELECT
	`id`, `host_id`, `folder` `fld`, `file`, `orig_time` `ts`, `dur`, `cidnum`, `cidname`, `listened_to`
FROM `vm_msgs`
WHERE
	`user_id`=' . (int)@$userinfo['id'] . '
	AND `id` =' . (int)@$_GET['id']
			);
			$r = $rs->fetchRow();
			
			gs_log( GS_LOG_NOTICE, 'playing message file ' . $r['file'] . ' (id = ' . $_GET['id'] . ')');
			
			$vm_dir = '/var/spool/asterisk/voicemail/';
			$origorigfile = $vm_dir .'default/'. $userinfo['ext'] .'/'. $r['fld'] .'/'. $r['file'] .'.alaw';
			$tmpfile_base = '/tmp/gs-vm-'. preg_replace('/[^0-9]/', '', $userinfo['ext']) .'-'. $r['fld'] .'-'. $r['file'];
			$outfile = $tmpfile_base .'.mp3';

			# delete files like /tmp/gs-vm-* with mtime < time()-10 minutes
			#
			@exec( 'find \'/tmp/\' -maxdepth 1 -name \'gs-vm-*\' -type f -mmin +10 | xargs rm -f 1>>/dev/null 2>>/dev/null' );

			# get file from remote host if necessary
			#

			if (! $user_is_on_this_host) {
				# user is on a different host
				# copy the original file to this host:
				$origfile = $tmpfile_base.'.alaw';
				$cmd = 'sudo scp -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa('root@'. $host['host'] .':'. $origorigfile) .' '. qsa($origfile);
				$err=0; $out=array();
				@exec( $cmd .' 1>>/dev/null 2>>/dev/null', $out, $err );
				if ($err != 0) {
					gs_log( GS_LOG_WARNING, "Could not get voicemail \"$origorigfile\" from node \"".$info['host']."\"." );
					_not_found( 'Could not get file from remote node.' );
				}
			} else {
				# user is on this host
				if (! file_exists($origorigfile)) {
					gs_log( GS_LOG_WARNING, "Voicemail \"$origorigfile\" not found on this node." );
					_not_found( 'File not found on this node.' );
				}
				$origfile = $origorigfile;
			}

			$err=0; $out=array();
			@exec( 'sudo chmod a+r '. qsa($origfile) .' 1>>/dev/null 2>>/dev/null', $out, $err );
			if ($err != 0) {
				gs_log( GS_LOG_WARNING, 'Can\'t read \"$origfile\".' );
				_server_error( 'Failed to convert file.' );
			}
			
			$id3_artist  = $r['cidnum'] . ($r['cidname'] != '' ? ' ('.$r['cidname'].')' : '');
			$id3_album   = subStr(_to_id3tag_ascii( $userinfo['ext']),0,30);
			$id3_title   = date('Y-m-d H:i', $r['ts']) .' - '. $id3_artist;
			$id3_artist  = subStr(_to_id3tag_ascii( $id3_artist  ),0,30);
			$id3_title   = subStr(_to_id3tag_ascii( $id3_title   ),0,30);
			$id3_comment = '';
			/*
			if ($info['orig_mbox'] != $ext
			&&  $info['orig_mbox'] != '') {
				$id3_comment .= '<< '.$info['orig_mbox'];
			}
			*/
			$id3_comment = subStr(_to_id3tag_ascii( $id3_comment ),0,28);

			$sox  = find_executable('sox', array(
				'/usr/bin/', '/usr/local/bin/', '/usr/sbin/', '/usr/local/sbin/' ));
			if (! $sox) {
				gs_log( GS_LOG_WARNING, 'sox - command not found.' );
				_server_error( 'Failed to convert file.' );
			}
			$lame = find_executable('lame', array(
				'/usr/local/bin/', '/usr/bin/', '/usr/local/sbin/', '/usr/sbin/' ));
			if (! $lame) {
				gs_log( GS_LOG_WARNING, 'lame - command not found.' );
				_server_error( 'Failed to convert file.' );
			}

			$cmd = $sox.' -q -t al '. qsa($origfile) .' -r 8000 -c 1 -s -b 16 -t wav - 2>>/dev/null | '.$lame.' --preset fast standard -m m -a -b 32 -B 96 --quiet --ignore-tag-errors --tt '. qsa($id3_title) .' --ta '. qsa($id3_artist) .' --tl '. qsa($id3_album) .' --tc '. qsa($id3_comment) .' --tg 101 - '. qsa($outfile) .' 2>&1 1>>/dev/null';
			# (ID3 tag genre 101 = "Speech")
			$err=0; $out=array();
			@exec( $cmd, $out, $err );
			if ($err != 0) {
				gs_log( GS_LOG_WARNING, 'Failed to convert voicemail file to '.$fmt.'. ('.trim(implode(' - ',$out)).')' );
				_server_error( 'Failed to convert file.' );
			}
			
			$DB->execute(
'UPDATE
	`vm_msgs` SET `listened_to`=1
WHERE
	`id`='. (int)@$_GET['id']
			);

			@header( 'Content-Type: audio/mpeg' );
			$fake_filename = preg_replace('/[^0-9a-z\-_.]/i', '', 'vm_'. $userinfo['ext'] .'_'. date('Ymd_Hi', $r['ts']) .'_'. subStr(md5(date('s', $r['ts']).$r['cidnum']),0,4) .'.mp3' );
			@header( 'Content-Disposition: '.($attach ? 'attachment':'inline').'; filename="'.$fake_filename.'"' );
			@header( 'Transfer-Encoding: identity' );
			@header( 'Content-Length: '. (int)@fileSize($outfile) );
			@readFile( $outfile );
			@ob_start();  # so there's no output after the content
			@ob_clean();
			
			break;
			
		default:
			// do nothing
		
	}

}

?>