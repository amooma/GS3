# -*- coding: utf-8 -*-
# AMOOMA Silver Bullet Event Handler Library
# Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
# http://www.amooma.de/
# This software is provided under an open source (but NO GPL) license - use at your own risk.

# Event Types:
# 0 reserved
# 1 raw asterisk manager interface event
# 2 processed asterisk event
#

from threading import Thread
from loglib import ldebug, linfo, lwarn, lerror, lcritic
from random import randint
from time import sleep, time
from collections import deque

class EventHandler(Thread):

	def __init__(self):
		Thread.__init__(self)
		self.runthread = True
		self.eventpipe = None
		self.idlesleep = 0.001
		self.eventpipe = deque()
		self.handlers = {}
		self.handlers[1] = {}
		self.handlers[2] = {}
		self.conf = None
		self.pluginlist = None
		
	def config(self, conf):
		ldebug('eventhandler: set configuration')
		self.conf = conf
		
		return True

	def plugins(self, plugins):
		self.pluginlist = plugins

	def add_handler(self, handler, eventtype):
		try:
			self.handlers[eventtype][handler] = self.pluginlist[handler]['object']
			ldebug("event handler %s registered for event type: %d" % (handler, eventtype))
			return True
		except:
			return False

	def has_handler(self, handler):
		return self.handlers.has_key(handler)

	def add_event(self, event):
		self.eventpipe.appendleft(event)

	def run(self):
		while self.runthread:
			if (self.eventpipe):
				event = self.eventpipe.pop()
	
			else:
				event = False
				sleep(self.idlesleep)
				continue

			for handler in self.handlers[event['_type']]:
				self.handlers[event['_type']][handler].add_event(event)
				
			#if (event['_type'] == 2):
			#	ldebug('2event: %s' % event)
				
		ldebug('closing event handler thread')
				
	def stop(self):
		self.runthread = False
