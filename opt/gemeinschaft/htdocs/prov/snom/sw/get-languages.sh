#!/bin/sh

#####################################################################
#            Gemeinschaft - asterisk cluster gemeinschaft
# 
# $Revision$
# 
# Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
# http://www.amooma.de/
# Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
# Philipp Kempgen <philipp.kempgen@amooma.de>
# Peter Kozak <peter.kozak@amooma.de>
# 
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
# MA 02110-1301, USA.
#####################################################################

SNOM_SW_DIR="/opt/gemeinschaft/htdocs/prov/snom/sw/"
WGET="/usr/bin/wget"
WGET_ARGS="-q -a /dev/null -t 1 -T 300 -nc -c"
DOWNLOAD="${WGET} ${WGET_ARGS}"
SNOM_BASE_URL="http://provisioning.snom.com/config/snomlang-"

if [ ! -x ${WGET} ]; then
	echo "${WGET} not found!"
	exit 1
fi


#####################################################################
#  7.1.8
#####################################################################

LANGVERS='7.1.8'
echo "Fetching language files for firmware version \"${LANGVERS}\""
cd "${SNOM_SW_DIR}" || exit 1
if [ ! -d "lang-${LANGVERS}" ]; then
	mkdir "lang-${LANGVERS}"
fi
if [ ! -d "lang-${LANGVERS}" ]; then
	echo "Failed to create directory \"lang-${LANGVERS}\"!"
else
	cd "lang-${LANGVERS}"
	
	# GUI
	#
	BASE_URL="${SNOM_BASE_URL}${LANGVERS}/gui_lang_"
	
	echo -n "Fetching ${LANGVERS} gui DE ...  "
	${DOWNLOAD} ${BASE_URL}DE.xml && echo 'Done' || echo 'Failed'
	echo -n "Fetching ${LANGVERS} gui EN ...  "
	${DOWNLOAD} ${BASE_URL}EN.xml && echo 'Done' || echo 'Failed'
	echo -n "Fetching ${LANGVERS} gui UK ...  "
	${DOWNLOAD} ${BASE_URL}UK.xml && echo 'Done' || echo 'Failed'
	
	# Web
	#
	BASE_URL="${SNOM_BASE_URL}${LANGVERS}/web_lang_"
	
	echo -n "Fetching ${LANGVERS} web DE ...  "
	${DOWNLOAD} ${BASE_URL}DE.xml && echo 'Done' || echo 'Failed'
	echo -n "Fetching ${LANGVERS} web EN ...  "
	${DOWNLOAD} ${BASE_URL}EN.xml && echo 'Done' || echo 'Failed'
	
	
fi
echo ""



