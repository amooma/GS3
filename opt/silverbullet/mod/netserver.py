# -*- coding: utf-8 -*-
# AMOOMA Silver Bullet Network Server Library
# Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
# http://www.amooma.de/
# This software is provided under an open source (but NO GPL) license - use at your own risk.

from threading import Thread
from loglib import ldebug, linfo, lwarn, lerror, lcritic
from eventmanager import EventManager
from collections import deque
from time import sleep
from struct import pack, unpack
from helper import array_value
import socket

def classes_list(class_type = 0):
	if (class_type == 1):
		return (NetServer,)
	elif (class_type == 2):
		return (NetServer,)
	else:
		return None

class NetClientThread(Thread):

	PREAMBLE 		= 0x4953
	M_CLOSE			= 0x00
	M_PING			= 0x02
	M_NACK			= 0x04
	M_ACK			= 0x06
	M_LOGIN_PLAINTEXT	= 0x20
	M_EXTGROUPSET		= 0x60
	M_EXTGROUPSTAT  	= 0x62
	M_EXTGROUPSUBSCRIBE	= 0x64
	M_QUEUEGROUPSET		= 0x70
	M_QUEUEGROUPCALLS  	= 0x72
	M_CHANNELSGET  		= 0x80
	M_CALLFORWARDGET	= 0x100

	R_NACK			= 0x01
	R_ACK			= 0x03
	R_LOGIN_PLAINTEXT	= 0x21
	R_EXTGROUPSET		= 0x61
	R_EXTGROUPSTAT  	= 0x63
	R_EXTGROUPSUBSCRIBE	= 0x65
	R_EXTGROUPUPDATE	= 0x67
	R_QUEUEGROUPSET		= 0x71
	R_QUEUEGROUPCALLS  	= 0x73
	R_CALLFORWARDGET	= 0x101
	
	def __init__(self, netsocket, address, auth, clients, client_key):
		Thread.__init__(self)
		self.daemon = True
		self.netsocket = netsocket
		self.clientaddress = address
		self.runthread = True
		self.auth = auth
		self.clients = clients
		self.manager = None
		self.client_key = client_key
		self.clients[self.client_key]['socket'] = netsocket
		self.clients[self.client_key]['user'] = []
		self.conf = None
		self.event_subscriptions = None
		self.extgroup_subscriptions = {}
		self.exten_groups = None
		self.queue_groups = None
		
	def config(self, conf):
		if (not type(conf) is dict):
			return False

		self.conf = conf

		if (not self.conf.has_key('auth')):
			self.conf['auth'] = []
		if (not self.conf['auth'].has_key('user')):
			self.conf['auth']['user'] = False

		return True

	def plugins(self, plugins):
		self.pluginlist = plugins

		if (plugins['AsteriskHandler']['object']):
			self.manager = plugins['AsteriskHandler']['object']

	def events(self, event_subscriptions):
		self.event_subscriptions = event_subscriptions
		if (not event_subscriptions.has_key(1)):
			event_subscriptions[1] = []
		event_subscriptions[1].append(self.client_key)

	def add_event(self, event):
		if (event['type'] == 1):
			for extgroup in self.extgroup_subscriptions:
				sequence = self.extgroup_subscriptions[extgroup]
				if (event['ext'] in self.exten_groups[extgroup]):
					self.msg_extstat(event['ext'], event['status'], sequence)
					self.extgroup_subscriptions[extgroup] = (sequence + 1) % 256

	def send(self, data):
		try:
			self.netsocket.send(data)
			return True
		except:
			return False

	def msg(self, msg_type, data = ''):
		msg_len = len(data)
		msg_data = pack("HHH", NetClientThread.PREAMBLE, msg_type, msg_len)
		msg_data += data
		ldebug('sending packet %d length %d to client' % (msg_type, msg_len))
		self.send(msg_data)

	def ack(self, msg_type):
		msg_data = pack("H", msg_type)
		self.msg(NetClientThread.R_ACK, msg_data)

	def nack(self, msg_type):
		msg_data = pack("H", msg_type)
		self.msg(NetClientThread.R_NACK, msg_data)


	def msg_login_plaintext(self, data):
		if (not type(self.conf) is dict):
			linfo('no user authentification allowed')
			self.nack(NetClientThread.M_LOGIN_PLAINTEXT)
			return 0

		if (not self.conf.has_key('auth')):
			linfo('no user authentification allowed')
			self.nack(NetClientThread.M_LOGIN_PLAINTEXT)
			return 0

		if (not self.conf['auth'].has_key('user')):
			linfo('no user authentification allowed')
			self.nack(NetClientThread.M_LOGIN_PLAINTEXT)
			return 0

		try:

			user = data[2:2+ord(data[0])]
			password = data[2+ord(data[0]):2+ord(data[0])+ord(data[1])]

		except:
			linfo('user authentification failed')
			self.nack(NetClientThread.M_LOGIN_PLAINTEXT)
			return 0

		linfo('user authentification not implemented')
		self.nack(NetClientThread.M_LOGIN_PLAINTEXT)
		return 0

	def msg_extgroupset(self, ext_groups, data):

		ext_group = []
		ext_states = ''

		group_id = ord(data[0])
		sequence = data[1]
		extensions = data[2:].split(chr(0x00))

		for exten in extensions:
			ext_group.append(exten)

		ext_groups[group_id] = ext_group

		for ext in ext_groups[group_id]:
			state = self.manager.extension_status(ext)
			if (state == None or state < 0):
				state = 255

			ext_states += (chr(state))

		self.msg(NetClientThread.R_EXTGROUPSET, chr(group_id)+sequence+ext_states)

		return 0

	def msg_extgroupstat(self, ext_groups, group_id, sequence):

		ext_states = ''

		if (not ext_groups.has_key(group_id)):
			self.nack(0x62)
			return 0

		for ext in ext_groups[group_id]:

			state = self.manager.extension_status(ext)

			if (state == None or state < 0):
				state = 255

			ext_states += (chr(state))

		self.msg(NetClientThread.R_EXTGROUPSTAT, chr(group_id)+sequence+ext_states)

	def msg_extgroupsubscribe(self, ext_groups, group_id, sequence):

		if (not ext_groups.has_key(group_id)):
			self.nack(0x65)
			return 0
			
		self.extgroup_subscriptions[group_id] = ord(sequence)

		self.msg(NetClientThread.R_EXTGROUPSUBSCRIBE, chr(group_id)+sequence)

	def msg_extstat(self, extension, status, sequence):
		ldebug("SEND UPDATE MESSAGE status %d for %s with sequence %d" % (status, extension, sequence))
		self.msg(NetClientThread.R_EXTGROUPUPDATE, chr(sequence)+chr(status)+extension)

	def msg_queuegroupset(self, queue_groups, data):

		queue_group = []

		group_id = ord(data[0])
		sequence = data[1]
		queues = data[2:].split(chr(0x00))

		for queue in queues:
			queue_group.append(queue)

		queue_groups[group_id] = queue_group

		queue_group_calls = ''

		for queue in queue_groups[group_id]:
			status = self.manager.queue_status(queue)
			if (status == None or status['status'] == None):
				self.nack(0x72)
				return 0
			if (status['status'].has_key('calls')):
				calls = status['status']['calls']
				if (calls == None or calls < 0):
					calls = 65535
			else:
				calls = 65535

			queue_calls = pack("H", calls)
			queue_group_calls += queue_calls

		self.msg(NetClientThread.R_QUEUEGROUPSET, chr(group_id)+sequence+queue_group_calls)

		return 0

	def msg_queuegroupcalls(self, queue_groups, group_id, sequence):
		if (not queue_groups.has_key(group_id)):
			self.nack(0x72)
			return 0

		queue_group_calls = ''

		for queue in queue_groups[group_id]:
			status = self.manager.queue_status(queue)
			if (status == None or status['status'] == None):
				self.nack(0x72)
				return 0
			if (status['status'].has_key('calls')):
				calls = status['status']['calls']
				if (calls == None or calls < 0):
					calls = 65535
			else:
				calls = 65535

			queue_calls = pack("H", calls)
			queue_group_calls += queue_calls

		self.msg(NetClientThread.R_QUEUEGROUPCALLS, chr(group_id)+sequence+queue_group_calls)
		
	def msg_channels_get(self, channel):
		channels = self.manager.get_channels()
		
		ldebug(channels)
	  
		self.nack(NetClientThread.M_CHANNELSGET)

	def msg_callforward_get(self, user_id):
		self.nack(NetClientThread.M_CALLFORWARDGET)

	def ret_nack(self, data):
		if (len(data) != 2):
			linfo('malformed NACK packet')
			return False
		packet_type, = unpack("H", data[0:2])
		ldebug('packet %d not acknowledged by client' % packet_type)

	def ret_ack(self, data):
		if (len(data) != 2):
			linfo('malformed ACK packet')
			return False
		packet_type, = unpack("H", data[0:2])
		ldebug('packet %d acknowledged by client' % packet_type)


	def run(self):
		self.netsocket.settimeout(60)
		exten_groups = {}
		queue_groups = {}
		self.exten_groups = exten_groups
		self.queue_groups = queue_groups
		address, port = self.clientaddress
		ldebug('opening connection to %s:%d' % (address, port))
		while self.runthread:
			data = []
			try:
				data = self.netsocket.recv(6)
			except:
				timeout = True
				self.runthread = False

			if (len(data) == 6):
				try:
					msg_ident, = unpack("H", data[0:2])
					msg_type,  = unpack("H", data[2:4])
					msg_len,   = unpack("H", data[4:6])

					if (msg_ident == NetClientThread.PREAMBLE):
						if (msg_len > 0):
							data = self.netsocket.recv(msg_len)
						else:
							data = ''
					else:
						linfo('malformed packet - length:%d' % len(data))
				except:
					linfo('packet error - length:%d' % len(data))
					continue

				ldebug('received packet type:%d length:%d' % (msg_type, len(data)))

				if (msg_len != len(data)):
					self.nack(msg_type)
					continue

				if (msg_type == NetClientThread.M_CLOSE):
					self.runthread = False
					break
				elif (msg_type == NetClientThread.M_PING):
					self.ack(msg_type)
					
				elif (msg_type == NetClientThread.M_LOGIN_PLAINTEXT):
					if (msg_len > 1):
						self.msg_login_plaintext(data)
					else:
						self.nack(msg_type)
						
				elif (msg_type == NetClientThread.M_EXTGROUPSET):
					if (msg_len > 1):
						self.msg_extgroupset(exten_groups, data)
					else:
						self.nack(msg_type)
				elif (msg_type == NetClientThread.M_EXTGROUPSTAT):
					if (msg_len == 2):
						self.msg_extgroupstat(exten_groups, ord(data[0]), data[1])
					else:
						self.nack(msg_type)
				
				elif (msg_type == NetClientThread.M_EXTGROUPSUBSCRIBE):
					if (msg_len == 2):
						self.msg_extgroupsubscribe(exten_groups, ord(data[0]), data[1])
					else:
						self.nack(msg_type)
				elif (msg_type == NetClientThread.M_QUEUEGROUPSET):
					if (msg_len > 1):
						self.msg_queuegroupset(queue_groups, data)
					else:
						self.nack(msg_type)
				elif (msg_type == NetClientThread.M_QUEUEGROUPCALLS):
					if (msg_len == 2):
						self.msg_queuegroupcalls(queue_groups, ord(data[0]), data[1])
					else:
						self.nack(msg_type)
				elif (msg_type == NetClientThread.M_CHANNELSGET):
					self.msg_channels_get(None)	
				elif (msg_type == NetClientThread.M_CALLFORWARDGET):
					self.msg_callforward_get(None)

				else:
					self.nack(msg_type)

			if not data: break

		ldebug('closing client connection to %s:%d' % (address, port))
		self.netsocket.close()
		for subscription in self.event_subscriptions:
			if (self.client_key in self.event_subscriptions[subscription]):
				self.event_subscriptions[subscription].remove(self.client_key)
		del self.clients[self.client_key]
		

	def stop(self):
	     self.runthread = False

class NetSocket():

	def __init__(self, port=None, address=None):
		self.daemon = True
		self.port = int(port)
		self.address = address

	def listen(self):
		serversocket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
		serversocket.setsockopt( socket.SOL_SOCKET, socket.SO_REUSEADDR, 1 )

		try:
			serversocket.bind((self.address, self.port))
		except ValueError, serror:
			lerror('listening socket address error: %s ' % serror)
			return False
		except socket.error, serror:
			lerror('listening socket error (%d): %s ' % (serror[0], serror[1]))
			return False
		except:
			lerror('listening socket error')
			return False

		serversocket.listen(5)
		ldebug('server listening on %s:%d' % (self.address, self.port))

		return serversocket

class NetServer(Thread):

	def __init__(self, port=None, address=None):
		Thread.__init__(self)
		self.daemon = True
		self.runthread = True
		self.port = port
		self.address = address
		self.pluginlist = None
		self.clients = {}
		self.event_subscriptions = {}
		self.em = EventManager()
		
	def config(self, conf):
		if (not conf.has_key('netserver')):
			return False
		if (not conf['netserver'].has_key('address')):
			return False
		if (not conf['netserver'].has_key('port')):
			return False
		if (not conf['netserver'].has_key('accept')):
			conf['netserver']['accept'] = '0.0.0.0/0'
		if (not conf['netserver'].has_key('reject')):
			conf['netserver']['reject'] = '0.0.0.0/32'
		if (not conf['netserver'].has_key('allow')):
			conf['netserver']['allow'] = '0.0.0.0/32'
		self.address = conf['netserver']['address']
		self.port = conf['netserver']['port']
		self.net_accept = {}
		self.net_reject = {}
		self.net_allow = {}

		ipcalc = IPCalc()
		
		for network_str in conf['netserver']['accept'].split(','):
			network, bitmask = ipcalc.netsplit(network_str)
			self.net_accept[network] = bitmask;

		for network_str in conf['netserver']['reject'].split(','):
			network, bitmask = ipcalc.netsplit(network_str)
			self.net_reject[network] = bitmask;
	
		for network_str in conf['netserver']['allow'].split(','):
			network, bitmask = ipcalc.netsplit(network_str)
			self.net_allow[network] = bitmask;
		
		return True

	def plugins(self, plugins):
		self.pluginlist = plugins
		self.em.plugins(plugins)
		
	def add_event(self, event):
		if (not self.event_subscriptions.has_key(event['type'])):
			return False

		for client in self.event_subscriptions[event['type']]:
			try:
				self.clients[client]['thread'].add_event(event)
			except:
				lerror('unable to pass event to client thread')

	def run(self):
		listen = NetSocket(self.port, self.address)
		
		serversocket = listen.listen()
		if (not serversocket):
			lerror('cannot start server process')
			return 1
		
		ret = self.em.add_event_handler('NetServer', 2)
		ipcalc = IPCalc()
		while (self.runthread):
			try:
				clientsocket, address = serversocket.accept()

			except socket.error, serror:
				lerror('socket error (%d): %s ' % (serror[0], serror[1]))
				continue
			except:
				lerror('socket error')
				continue

			addr_int = ipcalc.toint(address[0])
			accept_ip = False
			for network in self.net_accept.keys():
				bitmask = self.net_accept[network]
				if (ipcalc.innet(addr_int, network, bitmask)):
					accept_ip = True
					break

			if (not accept_ip):
				ldebug('ip rejected')
				continue

			for network in self.net_reject.keys():
				bitmask = self.net_reject[network]
				if (ipcalc.innet(addr_int, network, bitmask)):
					accept_ip = False
					break

			if (not accept_ip):
				linfo('ip rejected')
				continue

			auth_ip = False

			for network in self.net_allow.keys():
				bitmask = self.net_allow[network]
				if (ipcalc.innet(addr_int, network, bitmask)):
					auth_ip = True
					ldebug('trusted ip %s' % address[0])
					break

			self.clients[address] = {}
			ct = NetClientThread(clientsocket, address, auth_ip, self.clients, address)
			ct.plugins(self.pluginlist)
			ct.events(self.event_subscriptions)
			ct.start()
			self.clients[address]['thread'] = ct
			ldebug('connected clients: %d' % len(self.clients))

		ldebug('exiting netserver thread')
		if (self.clients):
			ldebug('closing  %d client connections' % len(self.clients))
			for client in self.clients:
				try:
					ldebug('purge %s from client list' % client)
					self.clients[client]['thread'].stop()
				except:
					pass

	def stop(self):
		self.runthread = False

class IPCalc():

	def bitmask(self, bits):
		bits = int(bits)
		if (bits == 0):
			return bits

		bitmask = (int(2) << bits -1) -1

		return bitmask << (32 - bits)

	def toint(self, address_str):
		ret = address_str.split('.')

		try:
			address = int(ret[3])
			address += (int(ret[2]) << 8)
			address += (int(ret[1]) << 16)
			address += (int(ret[0]) << 24)

		except:
			return None

		return address

	def innet(self, address, network, bitmask):

		return ((address & bitmask) == (network & bitmask))

	def netsplit(self, netaddr_str):
		try:
			network_str, netmask_str = netaddr_str.split('/')

		except:
			return None

		network = self.toint(network_str)

		if (netmask_str.find('.') >= 0):
			bitmask = self.toint(netmask_str)
		else:
			bitmask = self.bitmask(netmask_str)

		return network, bitmask
