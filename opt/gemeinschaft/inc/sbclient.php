<?php
/*
* SilverBullet Client Library
* Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*/

class SBClient {

	private $PREAMBLE		= 0x4953;
	
	private $M_CLOSE		= 0x00;
	private $M_PING			= 0x02;
	private $M_EXTGROUPSET		= 0x60;
	private $M_EXTGROUPSTAT  	= 0x62;
	private $M_QUEUEGROUPSET	= 0x70;
	private $M_QUEUEGROUPCALLS  	= 0x72;

	private $R_NACK			= 0x01;
	private $R_ACK			= 0x03;
	private $R_EXTGROUPSET		= 0x61;
	private $R_EXTGROUPSTAT  	= 0x63;
	private $R_QUEUEGROUPSET	= 0x71;
	private $R_QUEUEGROUPCALLS  	= 0x73;
	
	var $gsserversocket;
	
	function SBClient($server_ip, $server_port, $timeout = 1)
	{
		$this->connect($server_ip, $server_port, $timeout);

	}
	
	function connect($server_ip, $server_port, $timeout = 1)
	{
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_option($sock,SOL_SOCKET, SO_RCVTIMEO, array('sec'=>$timeout, 'usec'=>0));
		socket_connect($sock, $server_ip, $server_port);

		if (! is_resource($sock)) {
			return false;
		} else {
			$this->gsserversocket = $sock;
			return true;
		}
       
	}

	function close()
	{
		if (! is_resource($this->gsserversocket)) {
			return false;
		} else {
			socket_close($this->gsserversocket);
		}
	}

	function sendmsg($msg_type, $data)
	{
		$msg_len = strlen($data);
		$msg_data = pack('SSS', $this->PREAMBLE, $msg_type, $msg_len);
		$msg_data .= $data;

		if (! is_resource($this->gsserversocket)) {
			return false;
		} else {
			return socket_write($this->gsserversocket, $msg_data);
		}
	}

	function sendmsg_ping()
	{
		return $this->sendmsg($this->M_PING, chr(0x00));
	}

	function sendmsg_extgroupset($group_id, $extensions, $sequence = 0)
	{
		$data = pack('CC', $group_id, $sequence);
		$data .= implode(chr(0x00), $extensions);

		return $this->sendmsg($this->M_EXTGROUPSET, $data);
	}

	function ret_extgroupstat($data)
	{
		$ext_states = array();
		$ext_group = array();
		$group_id = ord($data[0]);
		$sequence = ord($data[1]);
		for ($i = 2; $i < strlen($data); ++$i) {
			$ext_states[] = ord($data[$i]);
		}

		$ext_group['group'] = $group_id;
		$ext_group['sequence'] = $sequence;
		$ext_group['states'] = $ext_states;
		
		return $ext_group;
	}

	function sendmsg_queuegroupset($group_id, $queues, $sequence = 0)
	{
		$data = pack('CC', $group_id, $sequence);
		$data .= implode(chr(0x00), $queues);

		return $this->sendmsg($this->M_QUEUEGROUPSET, $data);
	}

	function ret_queuegroupcalls($data)
	{
		$queue_states = array();
		$queue_group = array();
		$group_id = ord($data[0]);
		$sequence = ord($data[1]);
		for ($i = 2; $i < strlen($data); $i=$i+2) {
			$upacked = unpack('Ss', substr($data, $i, 2)); 
			$queue_states[] = $upacked['s'];
		}

		$queue_group['group'] = $group_id;
		$queue_group['sequence'] = $sequence;
		$queue_group['states'] = $queue_states;

		return $queue_group;
	}

	function receive() {
		if (! is_resource($this->gsserversocket)) {
			return false;
		}

		$messages = array();
		
		while ($data = socket_read($this->gsserversocket, 6)) {
				if (strlen($data) == 6) {
					$header = unpack('Sp/St/Sl', $data);

					if ($header['p'] != $this->PREAMBLE) continue;
					
					$message = array();
					$message['type'] = $header['t'];
					$message['length'] = $header['l'];
					if ($header['l'] > 0) {
						$data = socket_read($this->gsserversocket, $header['l']);
						if (strlen($data) != $header['l']) {
							continue;
						}
					} else {
						$data = false;
						continue;
					}

					if ($message['type'] == $this->R_EXTGROUPSET) {
						$message['data'] = $this->ret_extgroupstat($data);
					}
					if ($message['type'] == $this->R_EXTGROUPSTAT) {
						$message['data'] = $this->ret_extgroupstat($data);
					}
					if ($message['type'] == $this->R_QUEUEGROUPSET) {
						$message['data'] = $this->ret_queuegroupcalls($data);
					}
					if ($message['type'] == $this->R_QUEUEGROUPCALLS) {
						$message['data'] = $this->ret_queuegroupcalls($data);
					}
					
					
					$messages[] = $message;
				}
		}

		return $messages;
	
	}
}

?>