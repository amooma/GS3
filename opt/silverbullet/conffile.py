# -*- coding: utf-8 -*-
# AMOOMA Silver Bullet Config File Library
# Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
# http://www.amooma.de/
# This software is provided under an open source (but NO GPL) license - use at your own risk.

import re

class ConfigFile():

	def getconfig(self, filename):
		try:
			fd = open(filename, 'r')
			line = True
			section = False
			config = {}
			for line in fd:
				line = line.strip()
				if (not line):
					continue
				if (line[0] == ';'):
					continue
					
				ret = re.match('\[(\w*)\]', line)
				if ret:
					 section = re.sub('\W','',ret.group())
					 config[section] = {}
					 continue
				
				if (section):
					key, sep, value = line.partition('=')
					if (sep == '='):
						config[section][key.strip()] = value.strip()
		except:
			return False
					
		fd.closed

		return config

