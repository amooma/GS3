#!/usr/bin/php -q
<?php
/*******************************************************************\
*	Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2015, Markus Neubauer, Zeitblomstr. 29, 81735 München, Germany,
* http://www.std-soft.com/
* Markus Neubauer <markus.neubauer@email-online.org>
* 
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

define('GS_VALID', true); // arriving nativ here

require_once( dirName(__FILE__) .'/../inc/conf.php' );
$CARDDAV_LOG = 'cloud_connector.log';
$FIFO = '';
$process_name = 'vcard-checker';
include_once( GS_DIR .'inc/log.php' );

if (defined('STDIN' )) @fClose(STDIN );
if (defined('STDOUT')) @fClose(STDOUT);
if (defined('STDERR')) @fClose(STDERR);

include_once( GS_DIR .'lib/yadb/yadb.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'lib/carddav/CardDAV.php' );
require_once( GS_DIR .'lib/XML/xml2array.php' );
require_once( GS_DIR .'inc/db_connect.php' );


// vCard DAV connect
function gs_vcards_connect( $url=null, $login=null, $pass=null ) {
  global $CARDDAV_LOG, $FIFO;

	if (! class_exists('carddav_backend')) {
		gs_log( GS_LOG_FATAL, getmypid() .': This PHP does not support carddav (lib CardDav.php missing)!', $CARDDAV_LOG, $FIFO );
		exit(1);
	}

	$carddav_conn = new carddav_backend($url);
	$carddav_conn->set_auth($login, $pass);
	
	if ( $carddav_conn->check_connection() ) {
		gs_log(GS_LOG_DEBUG, getmypid() .': connected to CardDAV '. $url .' for user ' . $login, $CARDDAV_LOG, $FIFO );
		return $carddav_conn;
	} else {
		gs_log(GS_LOG_FATAL, getmypid() .': Could not connect to CardDAV '. $url .' for user ' . $login, $CARDDAV_LOG, $FIFO );
		return false;
	}
}

// update gs records
function gs_vcard_update( $vcr, $head, $vcard, $lvc=array() ) {
  global $CARDDAV_LOG, $FIFO, $DB, $CANONIZE_INTL_PREFIX, $CANONIZE_COUNTRY_CODE, $CANONIZE_NATL_PREFIX;

	$last_modified = date("Y-m-d H:i:s", strtotime( $head['d:propstat']['d:prop']['d:getlastmodified'] ));
	$etag = str_replace('"', '', $head['d:propstat']['d:prop']['d:getetag']);

	// remove phone numbers on existing vcards -> update is difficult due to aligment with name/phone number
	if ( ! empty($lvc) ) {
		$DB->execute('DELETE FROM `pb_prv` WHERE `user_id`=' . $vcr['user_id'] . ' and `card_id`=' . $lvc['id']);
		$DB->execute('DELETE FROM `pb_prv_category` WHERE `user_id`=' . $vcr['user_id'] . ' and `card_id`=' . $lvc['id']);
	}
	else {
		$DB->execute('INSERT INTO `pb_cloud_card` ' .
				' (`cloud_id`,`vcard_id`,`etag`,`vcard`,`last_modified`) ' .
				' VALUES(' . $vcr['id'] . ',\'' . $head['vcard_id'] . '\',\'' . $etag . '\',\'' . mysql_real_escape_string($vcard) . '\',\'' . $last_modified . '\')');
		$lvc = $DB->execute( 'SELECT `id`,`cloud_id`,`vcard_id`,`etag`,`last_modified`' .
				' FROM `pb_cloud_card` ' .
				' WHERE `cloud_id`=' . $vcr['id'] . 
					' and `vcard_id`=\'' . $head['vcard_id'] . '\''
				)->fetchRow();
	}

	$tmp_vcard = explode("\n", $vcard);
	
	$n = array();
	$acat = array();
	$fn   = '';
	$ln   = '';
	foreach ( $tmp_vcard as $value ) {
		if ( empty( $value ) ) continue;

		$elem = substr($value, strpos($value, ':') + 1 );
		$key  = substr($value, 0, strpos($value, ':') );
		if     ( 'N' == $key ) {
			$elem = trim($elem);
			$aname = explode(';', $elem);
			$ln = array_shift ( $aname );
			for ($i = count($aname); $i >= 0; $i--) {
				if ( empty($aname[$i]) ) continue;
				$fn .= ' ' . $aname[$i];
			}
			$fn = ltrim( $fn );
		}
		if ( empty( $ln) ) {
			$ln = $fn;
			$fn='';
		}

		elseif ( 'TEL' == substr($key, 0, 3) ) {
			$elem = trim($elem);
			$ptype =  strtolower( substr($key, strpos($key, '=') + 1) );
			
			if ( '+' == substr($elem, 0, 1) ) 
				$elem = GS_CANONIZE_INTL_PREFIX . substr($elem, 1);
				
			$number = preg_replace('/\D/', '', $elem);

			if ( GS_CANONIZE_INTL_PREFIX . GS_CANONIZE_COUNTRY_CODE == substr($number, 0, strlen(GS_CANONIZE_INTL_PREFIX . GS_CANONIZE_COUNTRY_CODE) ) ) 
				$number = GS_CANONIZE_NATL_PREFIX . substr($number, strlen(GS_CANONIZE_INTL_PREFIX . GS_CANONIZE_COUNTRY_CODE));

			// insert number
			$n[$number] = $ptype;
		}

		elseif ( 'CATEGORIES' == $key ) {
			$elem = trim($elem);
			$acat = explode( ',', $elem );
		}
	}
	
	// loop over phone numbers
	foreach ( $n as $number => $ptype ) {

		// check_duplicates from within a vcard
		if ( $prc = $DB->execute('SELECT `id` FROM `pb_prv` WHERE `user_id`=' . $vcr['user_id'] . ' and `firstname`=\'' . $fn . '\' and `lastname`=\'' . $ln . '\' and `number`=\'' . $number . '\' and `card_id`=0')->fetchRow() )
			$DB->execute('DELETE FROM `pb_prv` WHERE `id`=' . $prc['id']);	

		// add a phone record
		$DB->execute('INSERT INTO `pb_prv` ' .
			' (`user_id`,`firstname`,`lastname`,`number`,`ptype`,`card_id`) ' .
			' VALUES(' . $vcr['user_id'] .',\'' . $fn .'\',\'' . $ln .'\',\'' . $number .'\',\'' . $ptype .'\',' . $lvc['id'] . ')');

		// loop over categories
		if ( ! empty( $acat) ) {
			$prc = $DB->execute( 'SELECT `id` FROM `pb_prv` WHERE `user_id`=' . $vcr['user_id'] . ' and `firstname`=\'' . $fn  . '\' and `lastname`=\'' . $ln  . '\' and `number`=\'' . $number . '\' and `ptype`=\'' . $ptype . '\' and `card_id`=' . $lvc['id'])->fetchRow();
			foreach ( $acat as $cat ) {
				if ( empty($cat) ) continue;
				if ( ! $crc = $DB->execute( 'SELECT `id` FROM `pb_category` WHERE `user_id`=' . $vcr['user_id'] . ' and `category`=\'' . $cat  . '\'')->fetchRow() ) {
					// insert rec
					$DB->execute('INSERT INTO `pb_category` ' .
						' (`user_id`,`category`) ' .
						' VALUES(' . $vcr['user_id'] .',\'' . $cat .'\')');
					$crc = $DB->execute( 'SELECT `id` FROM `pb_category` WHERE `user_id`=' . $vcr['user_id'] . ' and `category`=\'' . $cat  . '\'')->fetchRow();
				}
				// correct xref
				$DB->execute('INSERT INTO `pb_prv_category` ' .
					' (`user_id`,`cat_id`,`card_id`,`prv_id`) ' .
					' VALUES(' . $vcr['user_id'] .',' . $crc['id'] .',' . $lvc['id'] .',' . $prc['id'] . ')');
				}
		}
	}

}


// fetch vcards for a cloud entry
function gs_vcards_update( $vcr ) {
  global $CARDDAV_LOG, $FIFO, $DB;

	$carddav_conn = gs_vcards_connect( $vcr['url'], $vcr['login'], $vcr['pass'] );
	if ( ! is_object( $carddav_conn ) || $carddav_conn === false ) {
		return false;
	}

	$a = new XMLThing();
	$vchead = $a->parse($carddav_conn->get(false,true));
	$vchead = $vchead['d:multistatus']['d:response'];
	$head   = array_shift ( $vchead );
	
	// skip rest if not modified between, depending on header etag or lastmodified
	if ( isset( $head['d:propstat']['d:prop']['cs:getctag'] ) ) {
		// ref etag: https://tools.ietf.org/html/rfc6352
		$last_ctag = str_replace('"', '', $head['d:propstat']['d:prop']['cs:getctag']);
		if ( $last_ctag == $vcr['last_ctag'] ) {
			gs_log(GS_LOG_DEBUG, getmypid() .': no update, ctag='. $last_ctag, $CARDDAV_LOG, $FIFO );
			return true;
		}
	} else  $last_ctag = '';

	if ( isset($head['d:propstat']['d:prop']['d:getlastmodified']) ) {
		$last_modified = date("Y-m-d H:i:s", strtotime( $head['d:propstat']['d:prop']['d:getlastmodified'] ));
		if ( $last_modified <= $vcr['last_remote_modified'] ) {
			gs_log(GS_LOG_DEBUG, getmypid() .': no update, last modified='. $last_modified, $CARDDAV_LOG, $FIFO );
			return true;
		}
	} else $last_modified = $vcr['modified'];

	gs_log(GS_LOG_DEBUG, getmypid() .': updating..., last modified='. $last_modified, $CARDDAV_LOG, $FIFO );
	// update ctag and last_modified immed
	$DB->execute('UPDATE `pb_cloud` SET `ctag`=\'' . $last_ctag . '\', `last_remote_modified`=\'' . $last_modified . '\' WHERE `id`=' . $vcr['id']);
	
	// get unseen records
	gs_log(GS_LOG_DEBUG, getmypid() .': fetch unseen', $CARDDAV_LOG, $FIFO );
	$unseen=array();
	$unrc = $DB->execute('SELECT `id`, `vcard_id`,`etag`, `last_modified` FROM `pb_cloud_card` WHERE `cloud_id`=' . $vcr['id']);
	while ( $urc = $unrc->fetchRow() ) {
		$unseen[$urc['vcard_id']] = array(
			'id'		=> $urc['id'],
			'etag'		=> $urc['etag'],
			'last_modified' => $urc['last_modified']
			);
	}
	if ( empty($unseen) ) gs_log(GS_LOG_DEBUG, getmypid() .': no unseen, adding ...', $CARDDAV_LOG, $FIFO );

	gs_log(GS_LOG_DEBUG, getmypid() .': updating vcards...', $CARDDAV_LOG, $FIFO );
	
	// iterate through all of them
	for ($i = 0; $i < count($vchead); $i++) {

		$vcard_id=str_replace('.vcf', null, substr($vchead[$i]['d:href'], strrpos($vchead[$i]['d:href'], '/') + 1) );
		
		$vchead[$i]['vcard_id'] =  $vcard_id;
		
		// check for existing vcard
		if ( isset($unseen[$vcard_id]) ) {
			$lvc = $unseen[$vcard_id];
			// avoid deleting
			unset($unseen["$vcard_id"]);

			// skip if not modified
			$last_modified = date("Y-m-d H:i:s", strtotime( $vchead[$i]['d:propstat']['d:prop']['d:getlastmodified'] ));
			$last_etag = str_replace('"', '', $vchead[$i]['d:propstat']['d:prop']['d:getetag']);
			if ( $last_etag == $lvc['etag'] && $last_modified == $lvc['last_modified'] ) continue;

			gs_log(GS_LOG_DEBUG, getmypid() .': updating '. $vcard_id, $CARDDAV_LOG, $FIFO );

			$vchead[$i]['d:propstat']['d:prop']['d:getlastmodified'] = $last_modified;

			// modified remote get the new vcard
			$vcard = $carddav_conn->get_vcard($vcard_id . '.vcf');
			gs_vcard_update( $vcr, $vchead[$i], $vcard, $lvc );

		} else {

			gs_log(GS_LOG_DEBUG, getmypid() .': adding '. $vcard_id, $CARDDAV_LOG, $FIFO );
			// missing local add new vcard
			$vcard = $carddav_conn->get_vcard($vcard_id . '.vcf');			
			gs_vcard_update( $vcr, $vchead[$i], $vcard );
		}
	}

	// delete no more existing recs
	foreach ($unseen as $vcard_id => $value) {
		gs_log(GS_LOG_DEBUG, getmypid() .': deleting '. $vcard_id, $CARDDAV_LOG, $FIFO );
		$DB->execute('DELETE FROM `pb_prv_category` WHERE `user_id`=' . $vcr['user_id'] . ' and `card_id`=' . $value['id']);
		$DB->execute('DELETE FROM `pb_prv` WHERE `user_id`=' . $vcr['user_id'] . ' and `card_id`=' . $value['id']);
		$DB->execute('DELETE FROM `pb_cloud_card` WHERE `id`=' . $value['id']);
	}

	return true;
}

$DB = gs_db_master_connect();


// remain in loop, doing a record time by time (avoid race cond in dup exec)
while ( true ) {
	$vcrc = @ $DB->execute(
		'SELECT
		`id`, `user_id`, `url`, `login`, cast(des_decrypt(`pass`,`login`) as char(16)) as pass, `frequency`, `ctag`, `last_remote_modified`, `modified`
		FROM `pb_cloud` 
		WHERE `next_poll` < NOW() ORDER BY `next_poll`
		LIMIT 1'
		);
	if ( $vcr = $vcrc->fetchRow() ) {
		gs_log(GS_LOG_NOTICE, getmypid() .': Check vCards: '. $vcr['login'] . ': ' .  $vcr['url'], $CARDDAV_LOG, $FIFO );
	} else {
		break; //the loop
	}
	
	// update next check immediately to avoid concurrent execs in timeout situations
	$hdm2text = array(
		'h' => 'hour',
		'd' => 'day', 
		'm' => 'month' 
	);
	$period = substr($vcr['frequency'], -1);
	$freq = substr($vcr['frequency'], 0, -1);
	if ( $freq > 1 ) $more = 's'; 
	else $more = '';

	$next_poll = date_format(date_add( date_create(), DateInterval::createFromDateString("$freq $hdm2text[$period]" . "$more") ), "Y-m-d H:i:s");

	$DB->execute('UPDATE `pb_cloud` SET `next_poll`=\'' . $next_poll . '\' WHERE `id`=' . $vcr['id']);

	if ( gs_vcards_update( $vcr )  === true ) {
		// all went ok
		gs_log(GS_LOG_NOTICE, getmypid() .': Check vCards completed normal.', $CARDDAV_LOG, $FIFO );

	} else {
		// any failure
		gs_log(GS_LOG_WARNING, getmypid() .': Check vCards failed for '. $vcr['login'] . ': ' .  $vcr['url'], $CARDDAV_LOG, $FIFO );
	}
}

?>
