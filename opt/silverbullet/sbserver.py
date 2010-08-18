# -*- coding: utf-8 -*-
# AMOOMA Silver Bullet Server
# Copyright 2010, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
# http://www.amooma.de/
# This software is provided under an open source (but NO GPL) license - use at your own risk.

from signal import signal, SIGHUP, SIGTERM
from sys import argv, stderr, modules
from getopt import getopt
from loglib import ldebug, linfo, lwarn, lerror, lcritic, logsetup
from conffile import ConfigFile
from time import sleep
from imp import find_module, load_module
from eventhandler import EventHandler

import os

def sig_handler(number, frame):
	global run_daemon

	ldebug('signal %d received ' % number)

	if (number == 15):
		ldebug('shutdown')
		run_daemon = False
	else:
		ldebug('signal %d ignored' % number)

def start_daemon(stdio = "/dev/null"):
	try:
		ret = os.fork()
	except OSError, err:
		lcritic('on daemon start error: (%d) %s ' % (err.errno, err.strerror))
		return 1

	if (ret == 0):
		os.setsid()
		try:
			ret = os.fork()
		except OSError, err:
			lcritic('on daemon start error: (%d) %s ' % (err.errno, err.strerror))
			return 1

		if (ret != 0):
			os._exit(0)
	else:

		os._exit(0)

	os.open(stdio, os.O_RDWR)
	os.dup2(0, 1)
	os.dup2(0, 2)

	return 0


def start_config_default(conf):
	general = {}
	general['daemon'] = 'yes'
	general['name'] = os.path.basename(argv[0])
	general['config_file'] = '/etc/silverbullet/silverbullet.conf'
	general['modulepath'] = '/opt/silverbullet/mod/'

	log = {}
	log['logfile'] = '/var/log/silverbullet.log'
	log['loglevel'] = 5
	log['logformat'] = '%(asctime)s-%(name)s-%(levelname)s-%(message)s'

	modules = {}
	modules['listeners'] = ''
	modules['handlers'] = ''

	netserver = {}
	netserver['address'] = '127.0.0.1'
	netserver['port'] = 18771
	

	conf['general'] = general
	conf['log'] = log
	conf['modules'] = modules
	conf['netserver'] = netserver
	
	return True

def start_config_params(conf):
	try:
		opts, args = getopt(argv[1:], 'f:')

	except:
		stderr.write('error in command line options\n\n')
		return False

	for opt, arg in opts:
		if (opt == "-f"):
			conf['general']['config_file'] = arg.strip()
			break
	return True

def start_config_get(conf):
	configuration = ConfigFile()
	conff = configuration.getconfig(conf['general']['config_file'])

	if (not conff):
		return False
	
	for section in conff:
		if (not conf.has_key(section)):
			 conf[section] = {}
			
		for item in conff[section]:			
			conf[section][item] = conff[section][item]

	return True

def start_config_check(conf):
	return True

def start_log(conf):
	logsetup(conf['log']['logfile'], conf['log']['loglevel'], conf['log']['logformat'])

def start_modules(conf):
	ldebug('loading modules')

	modules_listeners = conf['modules']['listeners'].split(',')
	for key in range(0, len(modules_listeners)):
		modules_listeners[key] = modules_listeners[key].strip()
	modules_handlers = conf['modules']['handlers'].split(',')
	for key in range(0, len(modules_handlers)):
		modules_handlers[key] = modules_handlers[key].strip()
	modules_all = modules_listeners + modules_handlers

	plugins = {}
	for module_name in modules_all:

		if (modules.has_key(module_name)):
			continue

		ldebug('loading module %s' % module_name)
		try:
			module_file, module_path, module_info = find_module(module_name, conf['general']['modulepath'].split(','))
		except:
			lerror('unable to load module: %s' % module_name)
			continue

		try:
			module_object = load_module(module_name, module_file, module_path, module_info)
		finally:
			if (module_file):
				module_file.close()

		if (module_name in modules_listeners):
			try:
				for module_class in module_object.classes_list(1):
					plugins[module_class.__name__] = {}
					plugins[module_class.__name__]['type'] = [1,]
					ldebug('loading listener %s' % module_class.__name__)
					plugins[module_class.__name__]['object'] = module_class()
			except:
				lerror('unable to load listeners of module: %s' % module_name)

		if (module_name in modules_handlers):
			try:
				for module_class in module_object.classes_list(2):
					if (plugins.has_key(module_class.__name__)):
						plugins[module_class.__name__]['type'].append(2)
						continue

					plugins[module_class.__name__] = {}
					plugins[module_class.__name__]['type'] = [2,]
					
					ldebug('loading handler %s' % module_class.__name__)
					plugins[module_class.__name__]['object'] = module_class()
			except:
				lerror('unable to load handlers of module: %s' % module_name)
			
	return plugins

def sbserver():
	global run_daemon

	run_daemon = True

	conf = {}
	if (not start_config_default(conf)):
		return 1
	if (not start_config_params(conf)):
		return 1
	if (not start_config_get(conf)):
		return 1
	start_log(conf)
	linfo('starting %s process' % conf['general']['name'])
	if (not start_config_check(conf)):
		return 1
	
	signal(SIGHUP, sig_handler)
	signal(SIGTERM, sig_handler)

	if (conf['general']['daemon'] == 'yes'):
		ldebug('%s process starting in daemon mode' % conf['general']['name'])
		ret = start_daemon()

		if (ret != 0):
			lcritic('failed to start %s daemon' % conf['general']['name'])
			os.exit(ret)

	ldebug('started %s process with pid: %d' % (conf['general']['name'], os.getpid()))
	eventhandler = EventHandler()
	plugins = start_modules(conf)
	plugins['EventHandler'] = {}
	plugins['EventHandler']['type'] = (0,)
	plugins['EventHandler']['object'] = eventhandler

	for plugin in plugins:
		if (0 in plugins[plugin]['type']):
			ldebug('start system plugin %s' % plugin)
			plugins[plugin]['object'].config(conf)
			plugins[plugin]['object'].plugins(plugins)
			plugins[plugin]['object'].start()
		elif (1 in plugins[plugin]['type']):
			ldebug('start listener plugin %s' % plugin)
			plugins[plugin]['object'].config(conf)
			plugins[plugin]['object'].plugins(plugins)
			plugins[plugin]['object'].start()
		elif (2 in plugins[plugin]['type']):
			ldebug('start handler plugin %s' % plugin)
			plugins[plugin]['object'].config(conf)
			plugins[plugin]['object'].plugins(plugins)
			plugins[plugin]['object'].start()

	while (run_daemon):
		sleep(0.5)
		
	for plugin in plugins:
		if (2 in plugins[plugin]['type']):
			ldebug('stop handler plugin %s' % plugin)
			plugins[plugin]['object'].stop()
		if (1 in plugins[plugin]['type']):
			ldebug('stop listener plugin %s' % plugin)
			plugins[plugin]['object'].stop()
		if (0 in plugins[plugin]['type']):
			ldebug('stop system plugin %s' % plugin)
			plugins[plugin]['object'].stop()
	sleep(1)
			
	linfo('exiting %s process' % conf['general']['name'])
	return 1


if __name__ == '__main__':
	ret = sbserver()
	exit(ret)
