<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Sebastian Ertz <gemeinschaft@swastel.eisfair.net>
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
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/log.php' );

class AMI {
        
	private $_socket;
	
	private function _check_socket() {
	        
		if (is_resource($this->_socket))
			return true;
		else
			return false;
                
	}
	
	public function ami_send_command( $command ) {
	        
		@fWrite( $this->_socket, $command, strLen($command) );
		@fFlush( $this->_socket );
		$data = array();
		while (! fEof($this->_socket)) {
			$tmp = @fgets( $this->_socket, 8192 );
			if ( strlen ( trim ( $tmp ) ) <= 0 ) {
				break;
			}
			list($first,$last) = explode(':', $tmp);
			$data[trim($first)] = trim($last);
			usleep(1000);  # sleep 0.001 secs
		}
		return $data;
		
	}
	
	// zum ausgeben des arrays
	public function ausgeben($data) {
		foreach($data as $name => $val) {
			echo $name .' => '. $val ."\n";
		}
	}
	
	
	public function ami_login($username, $password, $host, $port) {
		
		$this->_socket = fSockOpen($host, $port, $err, $errmsg, 2);
		
		if (! $this->_check_socket()) {
			gs_log( GS_LOG_WARNING, 'Connection to AMI on '. $host .' failed' );
			return false;
		}
		
		# check: is AMI?
		$tStart = time();
		$data = '';
		while (! fEof($this->_socket) && time() < $tStart+3) {
			$data .= @ fRead($this->_socket, 8192);
			if (@ preg_match('/[\\r\\n]/', $data)) break;
			usleep(1000);  # sleep 0.001 secs
		}
		
		if (! preg_match('/^Asterisk [^\/]+\/(\d(?:\.\d)?)/mis', $data, $m)) {
			gs_log ( GS_LOG_WARNING, 'Incompatible Asterisk Manager Interface on '. $host );
			$m = array( 1 => '0.0' );
		} else {
			if ($m[1] > '1.1') {
				# Asterisk 1.4: manager 1.0
				# Asterisk 1.6: manager 1.1
				gs_log( GS_LOG_WARNING, 'Asterisk manager interface on '. $host .' speaks a new protocol version ('. $m[1] .')' );
				# let's try anyway and hope to understand it			
			}
			
			$req = 'Action: Login' ."\r\n"
			        . 'Username: '. $username ."\r\n"
			        . 'Secret: '. $password ."\r\n"
			        . 'Events: off' ."\r\n"
			        . "\r\n";
			$data = $this->ami_send_command($req);
			if (strToLower($data['Message']) != 'authentication accepted') {
				gs_log( GS_LOG_WARNING, 'Authentication to AMI on '. $host .' failed' );
				return false;
			}
                        
		}
		return true;
	}
	
	public function ami_logout() {
	
		if (! $this->_check_socket()) return false;
		
		$data = $this->ami_send_command('Action: Logoff'."\r\n\r\n");
		
		if (strToLower($data['Response']) === 'goodbye') {
		        fClose($this->_socket);
			return true;
		} else {
			return false;
		}
		
	}
	
}

/*$ami = new AMI;
$ami->ami_login('admin', 'password', '127.0.0.1', 5038);

$data = $ami->ami_send_command('Action: Ping'."\r\n\r\n");
$ami->ausgeben($data);

$data = $ami->ami_send_command('Action: Queues'."\r\n\r\n");
$ami->ausgeben($data);

$ami->ami_logout();
*/

?>