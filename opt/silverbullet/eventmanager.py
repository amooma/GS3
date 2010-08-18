# -*- coding: utf-8 -*-
# AMOOMA Silver Bullet Event Manager Library
# Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
# http://www.amooma.de/
# This software is provided under an open source (but NO GPL) license - use at your own risk.
from loglib import lerror
from time import time

class EventManager():
	def __init__(self):
		self.handler_thread = None
		
	def plugins(self, plugins):
		if (plugins.has_key('EventHandler') and (0 in plugins['EventHandler']['type'])):
			self.handler_thread = plugins['EventHandler']['object']
		else:
			self.handler_thread = None

	def add_event_handler(self, handler, eventtype):
		if (not self.handler_thread):
			lerror('EventManager: no handler thread')
			return False

		return self.handler_thread.add_handler(handler, eventtype)
	
	def add_handler_event(self, event, eventtype):
		if (not self.handler_thread):
			return False

		#if (not self.handler_thread.has_handler(eventtype)):
		#	return False

		try:
			event['_type'] = eventtype
			event['_time'] = time()
		except:
			return False

		return self.handler_thread.add_event(event)