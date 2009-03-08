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
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/get-listen-to-ips.php' );


/***********************************************************
*    sets a user's ringtone
***********************************************************/

function gs_ringtone_set( $user, $src, $bellcore, $change_file=false, $file=null )
{
	if (! preg_match( '/^[a-zA-Z\d]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	
	if (! in_array( $src, array('internal','external'), true ))
		return new GsError( 'Source must be internal|external.' );
	
	$bellcore = (int)$bellcore;
	if ($bellcore < 0 || $bellcore > 10)
		return new GsError( 'Bellcore must be between 1 and 10 or 0 for silent.' );
	
	if (! $change_file) {
		$file = null;
	} else {
		if (! $file) {
			# to remove a custom ringer
			$file = null;
		} else {
			$file = @realPath($file);
			if (! @file_exists( $file )) {
				$file = @realPath( @$_ENV['PWD'] .'/'. $file);
				if (! @file_exists( $file ))
					return new GsError( 'File not found.' );
			}
			//if (strToLower(subStr($file,-4)) != '.mp3')
			//	return new GsError( 'File is not an mp3.' );
		}
	}
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );
	
	# make sure there is an entry in the db and set the bellcore ringer
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) `num` FROM `ringtones` WHERE `user_id`='. $user_id .' AND `src`=\''. $src .'\'' );
	if ($num < 1) {
		$ok = $db->execute( 'INSERT INTO `ringtones` (`user_id`, `src`, `bellcore`, `file`) VALUES ('. $user_id .', \''. $src .'\', '. $bellcore .', NULL)' );
	} else {
		$ok = $db->execute( 'UPDATE `ringtones` SET `bellcore`='. $bellcore .' WHERE `user_id`='. $user_id .' AND `src`=\''. $src .'\'' );
	}
	if (! $ok) return new GsError( 'DB error.' );
	
	if (! $change_file) return true;
	
	
	# are we the web server?
	#
	if (! gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
		$our_host_ips = @gs_get_listen_to_ips();
		if (! is_array($our_host_ips))
			return new GsError( 'Failed to get our host IPs.' );
		$we_are_the_webserver = in_array( GS_PROV_HOST, $our_host_ips );
	} else {
		$we_are_the_webserver = true;
	}
	
	# remove old ringer from htdocs/prov/ringtones/ dir
	#
	if ($we_are_the_webserver) {
		# local
		@exec( 'sudo rm -rf '. GS_DIR .'htdocs/prov/ringtones/'. $user .'-'. subStr($src,0,3) .'-* 1>>/dev/null 2>>/dev/null' );
	} else {
		# remotely
		$cmd = 'rm -rf /opt/gemeinschaft/htdocs/prov/ringtones/'. $user .'-'. subStr($src,0,3) .'-* 1>>/dev/null 2>>/dev/null &';
		@exec( 'sudo ssh -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa( 'root@'. GS_PROV_HOST ) .' '. qsa($cmd) .' 1>>/dev/null 2>>/dev/null' );
	}
	
	# just remove custom ringer?
	#
	if (! $file) {
		$ok = $db->execute( 'UPDATE `ringtones` SET `file`=NULL WHERE `user_id`='. $user_id .' AND `src`=\''. $src .'\'' );
		if (! $ok) return new GsError( 'DB error.' );
		return true;
	}
	
	# convert sound file to the formats needed for each phone type
	#
	
	$to_sox_format = array(  # to make sox understand the format
		'alaw' => 'al',
		'ulaw' => 'ul'
	);
	$pinfo = pathInfo($file);
	//$base = $pinfo['basename'];
	$ext = strToLower( @$pinfo['extension'] );
	if (array_key_exists($ext, $to_sox_format))
		$ext = $to_sox_format[$ext];
	$rand = base_convert(rand(1296,46655), 10, 36);  # 100(36) - zzz(36)
	$tmpbase = '/tmp/gs-ring-'. $user .'-'. $rand;
	$infile  = $tmpbase .'-in.' . $ext;
	$outbase = $tmpbase .'-out';
	$ok = @copy( $file, $infile );
	@chmod($infile, 0666);
	if (! $ok)
		return new GsError( 'Failed to copy file to "'. $infile .'".' );
	
	include_once( GS_DIR .'inc/phone-capability.php' );
	
	$phone_types = glob( GS_DIR .'htdocs/prov/*/capability.php' );
	if (! is_array($phone_types)) $phone_types = array();
	for ($i=0; $i<count($phone_types); ++$i) {
		$phone_types[$i] = baseName(dirName($phone_types[$i]));
	}
	gs_log(GS_LOG_DEBUG, 'Ringtone conversion: Found phone types: '.implode(', ',$phone_types) );
	
	$errors = array();
	$new_ringer_basename = $user .'-'. subStr($src,0,3) .'-'. $rand;
	foreach ($phone_types as $phone_type) {
		include_once( GS_DIR .'htdocs/prov/'. $phone_type .'/capability.php' );
		$class = 'PhoneCapability_'. $phone_type;
		if (! class_exists($class)) {
			gs_log(GS_LOG_WARNING, $phone_type .': Class broken.' );
			$errors[] = $phone_type .': Class broken.';
			continue;
		}
		$PhoneCapa = new $class;
		
		$outfile = $PhoneCapa->conv_ringtone( $infile, $outbase );
		if (isGsError($outfile)) {
			gs_log(GS_LOG_WARNING, 'Ringtone conversion: '. $phone_type .': '. $outfile->getMsg() );
			$errors[] = $phone_type .': '. $outfile->getMsg();
		} elseif ($outfile === null) {
			gs_log(GS_LOG_DEBUG, 'Ringtone conversion: '. $phone_type .': Not implemented.' );
			continue;
		} elseif (! $outfile) {
			gs_log(GS_LOG_WARNING, 'Ringtone conversion: '. $phone_type .': Failed to convert file.' );
			$errors[] = $phone_type .': '. 'Failed to convert file.';
			continue;
		}
		if (! file_exists($outfile)) {
			gs_log(GS_LOG_WARNING, 'Ringtone conversion: '. $phone_type .': Failed to convert file.' );
			$errors[] = $phone_type .': '. 'Failed to convert file.';
			continue;
		}
		gs_log(GS_LOG_DEBUG, 'Ringtone conversion: '. $phone_type .': Converted.' );
		@chmod($outfile, 0666);
		
		$pinfo = pathInfo($outfile);
		$ext = strToLower( @$pinfo['extension'] );
		$newbase = $new_ringer_basename .'-'. $phone_type .'.'. $ext;
		
		if ($phone_type === 'siemens'
		&& ! gs_get_conf('GS_SIEMENS_PROV_PREFER_HTTP')) {
			# if this is a Siemens phone, push the file on the FTP server
			@copy( $infile, '/tmp/'.$newbase );  //FIXME - why?
			$ok = $PhoneCapa->_upload_ringtone('/tmp/'.$newbase);
			if (! $ok)
				gs_log(GS_LOG_WARNING, 'Failed to upload ringtone to FTP server.');
			if (is_file('/tmp/'.$newbase)) @unlink( '/tmp/'.$newbase );
		}
		else {
			if ($we_are_the_webserver) {
				# local
				//rename( $outfile, GS_DIR .'htdocs/prov/ringtones/'. $newbase );
				@exec( 'sudo mv '. qsa($outfile) .' '. qsa( GS_DIR .'htdocs/prov/ringtones/'. $newbase ), $out, $err );
			} else {
				# remotely
				@exec( 'sudo scp -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa($outfile) .' '. qsa( 'root@'. GS_PROV_HOST .':/opt/gemeinschaft/htdocs/prov/ringtones/'. $newbase ) .' >>/dev/null 2>>/dev/null', $out, $err );
				//@exec( 'sudo rm -f '. qsa($outfile) .' >>/dev/null 2>&1' );
				@unlink( $outfile );
			}
			if ($err != 0) {
				gs_log(GS_LOG_WARNING, 'Failed to mv ringtone.');
			}
		}
	}
	if (is_file($infile)) @unlink( $infile );
	@exec('rm -rf '. $tmpbase .'-* 1>>/dev/null 2>>/dev/null &');
	
	if (count($errors) > 0) {
		return new GsError( "Failed to convert ringtone for some or all phone types: ". implode(", ", $errors) );
	}
	$ok = $db->execute( 'UPDATE `ringtones` SET `file`=\''. $db->escape($new_ringer_basename) .'\' WHERE `user_id`='. $user_id .' AND `src`=\''. $src .'\'' );
	if (! $ok) return new GsError( 'DB error.' );
	
	return true;
	
	
	
	
	// OLD STUFF:
	
	/*
	# remove old ringer
	#
	$files = @glob( GS_DIR .'htdocs/prov/ringtones/'. $user .'/'. $src .'-*' );
	if (is_array($files)) {
		foreach ($files as $f) {
			unlink();
		}
	}
	die();
	
	
	shell_exec( 'rm -f /opt/ast/htdocs/prov/ringtones/'. $ext .'-*' );
	
	# get SIP name
	#
	$ext = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	if (! $ext)
		return new GsError( 'DB error.' );
	
	
	if ($file) {
		
		$rand = rand(10000,99999).time();
		
		shell_exec( 'mpg123 -m -r 8000 -w - -n 500 -q \''. $file .'\' > \'/opt/gemeinschaft/htdocs/prov/ringtones/'. $rand .'.wav\'' );
		shell_exec( 'sox \'/opt/gemeinschaft/htdocs/prov/ringtones/'. $rand .'.wav\' -r 8000 -c 1 -w \'/opt/gemeinschaft/htdocs/prov/ringtones/'. $ext .'-'. time() .'.wav\'' );
		shell_exec( 'rm \'/opt/gemeinschaft/htdocs/prov/ringtones/'. $rand .'.wav\'' );
		
	} else {
		//shell_exec( 'rm -f /opt/gemeinschaft/htdocs/prov/ringtones/'. $ext .'-*' );
	}
	
	return true;
	*/
}


?>