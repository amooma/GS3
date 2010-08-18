# -*- coding: utf-8 -*-
# AMOOMA Silver Bullet AMI Library
# Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
# http://www.amooma.de/
# This software is provided under an open source (but NO GPL) license - use at your own risk.

from threading import Thread
from loglib import ldebug, linfo, lwarn, lerror, lcritic
from eventmanager import EventManager
from collections import deque
from time import sleep, time
from helper import array_value
import socket
import sys

def classes_list(class_type = 0):
	if (class_type == 1):
		return (AsteriskManager,)
	elif (class_type == 2):
		return (AsteriskHandler,)
	else:
		return None

class AsteriskManager(Thread):

	def __init__(self):
		Thread.__init__(self)
		self.daemon = True
		self.host = None
		self.port = None
		self.user = None
		self.password = None
		self.runthread = True
		self.sendlock = False
		self.pluginlist = None 
		self.em = EventManager()

	def config(self, conf):
		self.host = array_value(conf, ('asterisk','address'), '127.0.0.1')
		self.port = array_value(conf, ('asterisk','port'), 5038, int)
		self.user = array_value(conf, ('asterisk','user'), '')
		self.password = array_value(conf, ('asterisk','password'), '')

		return True
		
	def plugins(self, plugins):
		self.pluginlist = plugins
		self.em.plugins(plugins)

	def addevent(self, event):
		return self.em.add_handler_event(event, 1)

	def to_array(self, event):
		event_array = {}

		for line in event:
			keyword, delimeter, value = line.partition(": ")
			if (keyword):
				event_array[keyword] = value
		return event_array

	def send(self, send_string, blocking = True):
		if (blocking):
			while self.sendlock:
				sleep(0.1)

		self.sendlock = True

		try:
			self.mgsocket.send(send_string)
			self.sendlock = False
			return True

		except:
			self.sendlock = False
			return False

	def login(self, host, port, user, password):
		s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
		
		try:
			s.connect((host, port))
		except:
			return False

		send_string = "Action: Login\r\nUsername: %s\r\nSecret: %s\r\nEvents: on\r\n\r\n" % (user, password)

		s.settimeout(1)

		try:
			s.send(send_string)
		except:
			return False
			
		return s

	def run (self):
		ldebug('starting asterisk manager thread')
		
		while self.runthread:
			
			self.mgsocket = self.login(self.host, self.port, self.user, self.password)

			if (self.mgsocket == False):
				lerror('failed to open asterisk manager connection to %s:%d' % (self.host, self.port))
				if (self.runthread):
					sleep(1)
				continue
				
			ldebug('opening asterisk manager connection to %s:%d'  % (self.host, self.port))
			while self.runthread:

				try:
					data = self.mgsocket.recv(65634)
					timeout = False
				except:
					timeout = True

				if (timeout):
					continue


				events = data.split("\r\n\r\n")

				for event_line in events:
					if (not event_line):
						continue
					event = event_line.splitlines()
					event_array = self.to_array(event)

					self.addevent(event_array)

				if not data:
					break
			
			if (self.runthread):
				ldebug('lost connection to manager')
				sleep(1)
			if (self.mgsocket):
				self.mgsocket.close()

		ldebug('closing asterisk manager thread')

	def stop(self):
		self.runthread = False

class AsteriskHandler(Thread):

	def __init__(self):
		Thread.__init__(self)
		self.daemon = True
		self.runthread = True
		self.idlesleep = 0.01
		self.eventpipe = deque()
		self.em = EventManager()
		self.ami = None
		self.queuereadflag = False
		self.queuestats = {}
		self.extstates = {}
		self.channels = {}
		self.status_expire = 60
		self.status_timeout = 1
		
	def config(self, conf):		
		return True

	def plugins(self, plugins):
		self.pluginlist = plugins
		self.em.plugins(plugins)

		self.ami = array_value(plugins, ('AsteriskManager', 'object'), self.ami)

	def push_event(self, event):
		return self.em.add_handler_event(event, 2)
	
	def add_event(self, event):
		return self.eventpipe.appendleft(event)

	def send(self, send_string):
	
		return self.ami.send(send_string)

	def extension_status(self, exten):
		if (not exten):
			return None

		idlesleep = 0.1
		timeout = int(self.status_timeout / idlesleep)

		if (self.extstates.has_key(exten) and ((time() - self.extstates[exten]['time']) < self.status_expire)):
			state = int(self.extstates[exten]['status'])

		else:
			ldebug('retrieving status of extension %s' % exten)
			message = "Action: ExtensionState\r\nExten: %s\r\n\r\n" % exten
			try:
				self.send(message)
			except:
				return None

			wait = 0
			while (wait < timeout):
				if (self.extstates.has_key(exten)):
					state = int(self.extstates[exten]['status'])
					break
				else:
					sleep(idlesleep)
					state = None
					wait += 1
		
		return state

	def queue_status(self, queue):
		if (not queue):
			return None

		idlesleep = 0.1
		timeout = int(self.status_timeout / idlesleep)

		if (self.queuestats.has_key(queue) and ((time() - self.queuestats[queue]['time']) < self.status_expire)):
			status = self.queuestats[queue]

		else:
			if (self.queuereadflag == False):
				ldebug('retrieving status of queue %s' % queue)
				message = "Action: QueueStatus\r\nQueue: %s\r\n\r\n" % queue

				try:
					self.send(message)
				except:
					return None

			wait = 0
			while (wait < timeout):
				if (self.queuestats.has_key(queue)):
					status = self.queuestats[queue]
					ldebug('got new status of queue %s' % queue)
					break
				else:
					ldebug('waiting for update of queue %s' % queue)
					sleep(idlesleep)
					status = None
					wait += 1

			if (status == None):
				lwarn('updating of queue %s failed after timeout' % queue)

		return status
		
	def get_channels(self):	
		return self.channels

	def run(self):
		ret = self.em.add_event_handler('AsteriskHandler', 1)
		while self.runthread:
			if (self.eventpipe):
				event = self.eventpipe.pop()

			else:
				event = False
				sleep(self.idlesleep)
				continue

			push_event = {}

			if (array_value(event, 'Message') == "Extension Status"):
				exinfo = {}
				exten = array_value(event, 'Exten')
				exinfo['status'] = array_value(event, 'Status', 255, int)
				exinfo['time'] = array_value(event, '_time', 0, int)
				if (exten):
					self.extstates[exten]=exinfo
					push_event['type'] = 1
					push_event['status'] = exinfo['status']
					push_event['time'] = exinfo['time']
					push_event['ext'] = exten
			elif (array_value(event,"Message") == "Queue status will follow"):
				queue_stats_time = int(event['_time'])
				self.queuereadflag = queue_stats_time
				continue

			if (not event.has_key("Event")):
				continue

			if (event["Event"] == "ExtensionStatus"):
				exinfo = {}
				exten = array_value(event, 'Exten')
				exinfo['status'] = array_value(event, 'Status', 255, int)
				exinfo['time'] = array_value(event, '_time', 0, int)
				if (exten):
					self.extstates[exten]=exinfo
					push_event['type'] = 1
					push_event['status'] = exinfo['status']
					push_event['time'] = exinfo['time']
					push_event['ext'] = exten
			
			elif (event["Event"] == "QueueStatusComplete"):
				queue_stats_time = int(event['_time'])
				self.queuereadflag = False

			elif (event["Event"] == "QueueParams"):
				queue = array_value(event, 'Queue')

				if (not queue):
					continue

				queue_entry = {}
				queue_stat = {}

				queue_stat['calls'] 		= array_value(event, 'Calls', None, int)
				queue_stat['completed'] 	= array_value(event, 'Completed', None, int)
				queue_stat['abandoned'] 	= array_value(event, 'Abandoned', None, int)
				queue_stat['maxlen'] 		= array_value(event, 'Max', None, int)
				queue_stat['holdtime']		= array_value(event, 'Holdtime', None, int)
				queue_stat['weight']		= array_value(event, 'Weight', None, int)
				queue_stat['servicelevel']	= array_value(event, 'ServiceLevel', None, int)
				queue_stat['serviceperf']	= array_value(event, 'ServicelevelPerf', None, float)

				queue_entry['time'] = int(event['_time'])
				queue_entry['status'] = queue_stat
				queue_entry['members'] = []

				self.queuestats[queue] = queue_entry
	
			elif (event["Event"] == "QueueMember"):
				queue = array_value(event, 'Queue')
				if (not queue):
					continue
					
				queue_member = {}

				queue_member['status']		= array_value(event, 'Status', None, int)
				queue_member['penalty']		= array_value(event, 'Penalty', None, int)
				queue_member['name']		= array_value(event, 'Name', None, int)
				queue_member['membership']	= array_value(event, 'Membership', None, str)
				queue_member['location']	= array_value(event, 'Location', None, int)
				queue_member['lastcall']	= array_value(event, 'LastCall', None, int)
				queue_member['paused']		= array_value(event, 'Paused', None, int)
				queue_member['callstaken']	= array_value(event, 'CallsTaken', None, int)
			
				self.queuestats[queue]['members'].append(queue_member)

			elif (event["Event"] == "Join" or event["Event"] == "Leave"):
				queue = array_value(event, 'Queue', False, str)
				count = array_value(event, 'Count', False, int)

				if (not queue):
					continue
				if (not count):
					continue
			
				if (self.queuestats.has_key(queue)):
					self.queuestats[queue]['status']['calls'] = count
					
			elif (event["Event"] == "Newchannel"):
				channelinfo = {}
				chan_id = array_value(event, 'Uniqueid')
				
				channelinfo['channel'] = array_value(event, 'Channel')
				channelinfo['status'] = array_value(event, 'ChannelState', 255, int)
				channelinfo['time'] = time()
				channelinfo['starttime'] = channelinfo['time']
				channelinfo['ext'] = array_value(event, 'Exten')
				channelinfo['cidnum'] = array_value(event, 'CallerIDNum')
				channelinfo['cidname'] = array_value(event, 'CallerIDName')
				
				if (chan_id):
					self.channels[chan_id]=channelinfo
					
			elif (event["Event"] == "ChannelUpdate"):
				chan_id = array_value(event, 'Uniqueid')
				
				if (chan_id):
					if (not self.channels.has_key(chan_id)):
						self.channels[chan_id]={}
					self.channels[chan_id]['channel'] = array_value(event, 'Channel')
					self.channels[chan_id]['time'] = time()
					self.channels[chan_id]['channeltype'] = array_value(event, 'Channeltype')
					
			elif (event["Event"] == "Newstate"):
				chan_id = array_value(event, 'Uniqueid')
				
				if (chan_id):
					if (not self.channels.has_key(chan_id)):
						self.channels[chan_id]={}
					self.channels[chan_id]['channel'] = array_value(event, 'Channel')
					self.channels[chan_id]['time'] = time()
					self.channels[chan_id]['cidnum'] = array_value(event, 'CallerIDNum')
					self.channels[chan_id]['cidname'] = array_value(event, 'CallerIDName')
					self.channels[chan_id]['status'] = array_value(event, 'ChannelState', 255, int)
					
			elif (event["Event"] == "NewCallerid"):
				chan_id = array_value(event, 'Uniqueid')
				
				if (chan_id):
					if (not self.channels.has_key(chan_id)):
						self.channels[chan_id]={}
					self.channels[chan_id]['channel'] = array_value(event, 'Channel')
					self.channels[chan_id]['time'] = time()
					self.channels[chan_id]['cidnum'] = array_value(event, 'CallerIDNum')
					self.channels[chan_id]['cidname'] = array_value(event, 'CallerIDName')
					
			elif (event["Event"] == "Bridge"):
				chan_id1 = array_value(event, 'Uniqueid1')
				chan_id2 = array_value(event, 'Uniqueid2')
				
				if (chan_id1 and chan_id2):
					if (not self.channels.has_key(chan_id1)):
						self.channels[chan_id1]={}
					self.channels[chan_id1]['channel'] = array_value(event, 'Channel1')
					self.channels[chan_id1]['time'] = time()
					self.channels[chan_id1]['cidnum'] = array_value(event, 'CallerID1')
					self.channels[chan_id1]['bridgestate'] = array_value(event, 'Bridgestate')
					self.channels[chan_id1]['bridgetype'] = array_value(event, 'Bridgetype')
					self.channels[chan_id1]['bridgechannel'] = array_value(event, 'Channel2')
					self.channels[chan_id1]['bridgechanid'] = chan_id2
					
					if (not self.channels.has_key(chan_id2)):
						self.channels[chan_id2]={}
					self.channels[chan_id2]['channel'] = array_value(event, 'Channel2')
					self.channels[chan_id2]['time'] = time()
					self.channels[chan_id2]['cidnum'] = array_value(event, 'CallerID2')
					self.channels[chan_id2]['bridgestate'] = array_value(event, 'Bridgestate')
					self.channels[chan_id2]['bridgetype'] = array_value(event, 'Bridgetype')
					self.channels[chan_id2]['bridgechannel'] = array_value(event, 'Channel1')
					self.channels[chan_id2]['bridgechanid'] = chan_id1
					
			elif (event["Event"] == "Hangup"):
				channelinfo = {}
				chan_id = array_value(event, 'Uniqueid')
				
				channelinfo['channel'] = array_value(event, 'Channel')
				channelinfo['status'] = array_value(event, 'ChannelState', 255, int)
				channelinfo['time'] = time()
				channelinfo['cidnum'] = array_value(event, 'CallerIDNum')
				channelinfo['cidname'] = array_value(event, 'CallerIDName')
				channelinfo['cause'] = array_value(event, 'Cause', 255, int)
				
				if (chan_id):
					self.channels[chan_id]=channelinfo
					
					try:
						del(self.channels[chan_id])
					except:
						lwarn('channel id "%s" not in list' % chan_id);

			if (push_event):
				self.push_event(push_event)
			
		ldebug('closing asterisk handler thread')

	def stop(self):
		self.runthread = False

