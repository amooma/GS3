# -*- coding: utf-8 -*-
# AMOOMA Silver Bullet Channel Event Logger
# Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
# http://www.amooma.de/
# This software is provided under an open source (but NO GPL) license - use at your own risk.

from threading import Thread
from loglib import ldebug, linfo, lwarn, lerror, lcritic
from eventmanager import EventManager
from collections import deque
from time import sleep

def classes_list(class_type = 0):
	if (class_type == 2):
		return (ChannelEventLogger,)
	else:
		return None

class ChannelEventLogger(Thread):

	def __init__(self):
		Thread.__init__(self)
		self.daemon = True
		self.runthread = True
		self.idlesleep = 0.01
		self.eventpipe = deque()
		self.em = EventManager()

	def config(self, conf):
		return True

	def plugins(self, plugins):
		self.pluginlist = plugins
		self.em.plugins(plugins)

	def add_event(self, event):
		self.eventpipe.appendleft(event)

	def send(self, send_string):
		return True

	def run(self):
		ret = self.em.add_event_handler('ChannelEventLogger', 1)
		ldebug('starting CEL thread')
		while self.runthread:
			if (self.eventpipe):
				event = self.eventpipe.pop()

			else:
				event = False
				sleep(self.idlesleep)
				continue

			ldebug('CEL: %s' % event)

		ldebug('closing CEL thread')

	def stop(self):
		self.runthread = False

