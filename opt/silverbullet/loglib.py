# -*- coding: utf-8 -*-
# AMOOMA Silver Bullet Log Library
# Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
# http://www.amooma.de/
# This software is provided under an open source (but NO GPL) license - use at your own risk.

import logging

def ldebug(entry):
	global logger
	logger.debug(entry)

def lwarn(entry):
	global logger
	logger.warning(entry)

def lerror(entry):
	global logger
	logger.error(entry)

def linfo(entry):
	global logger
	logger.info(entry)

def lcritic(entry):
	global logger
	logger.critical(entry)

def logsetup(logfile, loglevel=0, logformat = False):

	global logger

	logfileh = logging.FileHandler(logfile)
	
	if (loglevel == 0):
		logfileh.setLevel(logging.NOTSET)
		logger.setLevel(logging.NOTSET)
	elif (loglevel == 1):
		logfileh.setLevel(logging.CRITICAL)
		logger.setLevel(logging.CRITICAL)
	elif (loglevel == 2):
		logfileh.setLevel(logging.ERROR)
		logger.setLevel(logging.ERROR)
	elif (loglevel == 3):
		logfileh.setLevel(logging.WARNING)
		logger.setLevel(logging.WARNING)
	elif (loglevel == 4):
		logfileh.setLevel(logging.INFO)
		logger.setLevel(logging.INFO)
	elif (loglevel >= 5):
		logfileh.setLevel(logging.DEBUG)
		logger.setLevel(logging.DEBUG)

	if (not logformat):
		logformat = '%(asctime)s-%(name)s-%(levelname)s-%(message)s'
	
	try:
		format = logging.Formatter(logformat)
		logfileh.setFormatter(format)
	except:
		format = logging.Formatter('%(asctime)s-%(name)s-%(levelname)s-%(message)s')
		logfileh.setFormatter(format)
		
	logger.addHandler(logfileh)

logger = logging.getLogger('silver')

	