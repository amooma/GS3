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
SNOM_BASE_URL="http://provisioning.snom.com/download/fw/"
SNOM_BASE_URL_6TO7="http://provisioning.snom.com/from6to7/"

if [ ! -x ${WGET} ]; then
	echo "${WGET} not found!"
	exit 1
fi

cd "${SNOM_SW_DIR}" || exit 1


#####################################################################
#  misc
#####################################################################

echo -n "Fetching Linux image snom360-3.25-l ...               "
${DOWNLOAD} ${SNOM_BASE_URL}snom360-3.25-l.bin && echo 'Done' || echo 'Failed'

echo -n "Fetching RootFS snom360-ramdiskToJffs2-3.36 ...       "
${DOWNLOAD} ${SNOM_BASE_URL}snom360-ramdiskToJffs2-3.36-br.bin && echo 'Done' || echo 'Failed'

echo -n "Fetching 6to7 Linux image snom360-3.38-l ...          "
${DOWNLOAD} ${SNOM_BASE_URL_6TO7}snom360-3.38-l.bin && echo 'Done' || echo 'Failed'

echo -n "Fetching 6to7 firmware snom360-update6to7-7.1.6 ...   "
${DOWNLOAD} ${SNOM_BASE_URL_6TO7}snom360-update6to7-7.1.6-bf.bin && echo 'Done' || echo 'Failed'


#####################################################################
#  6.5.10
#####################################################################

VERS='6.5.10-SIP-j'

MODEL='snom360'
echo -n "Fetching firmware ${VERS} for ${MODEL} ...        "
${DOWNLOAD} ${SNOM_BASE_URL}${MODEL}-${VERS}.bin && echo 'Done' || echo 'Failed'


#####################################################################
#  6.5.12-beta
#####################################################################

VERS='6.5.12-beta-SIP-j'

MODEL='snom360'
echo -n "Fetching firmware ${VERS} for ${MODEL} ...   "
${DOWNLOAD} ${SNOM_BASE_URL}${MODEL}-${VERS}.bin && echo 'Done' || echo 'Failed'


#####################################################################
#  7.1.24
#####################################################################

VERS='7.1.24-SIP-f'

MODEL='snom370'
echo -n "Fetching firmware ${VERS} for ${MODEL} ...        "
${DOWNLOAD} ${SNOM_BASE_URL}${MODEL}-${VERS}.bin && echo 'Done' || echo 'Failed'

MODEL='snom360'
echo -n "Fetching firmware ${VERS} for ${MODEL} ...        "
${DOWNLOAD} ${SNOM_BASE_URL}${MODEL}-${VERS}.bin && echo 'Done' || echo 'Failed'


#####################################################################
#  8.4.18
#####################################################################

VERS='8.4.18-SIP-f'
VERSFILE='08.04.18'

MODEL='snom370'
echo -n "Fetching firmware ${VERS} for ${MODEL} ...        "
${DOWNLOAD} ${SNOM_BASE_URL}${MODEL}-${VERS}.bin && echo 'Done' || echo 'Failed'
ln -s ${MODEL}-${VERS}.bin ${MODEL}-${VERSFILE}.bin

MODEL='snom360'
echo -n "Fetching firmware ${VERS} for ${MODEL} ...        "
${DOWNLOAD} ${SNOM_BASE_URL}${MODEL}-${VERS}.bin && echo 'Done' || echo 'Failed'
ln -s ${MODEL}-${VERS}.bin ${MODEL}-${VERSFILE}.bin

MODEL='snom320'
echo -n "Fetching firmware ${VERS} for ${MODEL} ...        "
${DOWNLOAD} ${SNOM_BASE_URL}${MODEL}-${VERS}.bin && echo 'Done' || echo 'Failed'
ln -s ${MODEL}-${VERS}.bin ${MODEL}-${VERSFILE}.bin

MODEL='snom300'
echo -n "Fetching firmware ${VERS} for ${MODEL} ...        "
${DOWNLOAD} ${SNOM_BASE_URL}${MODEL}-${VERS}.bin && echo 'Done' || echo 'Failed'
ln -s ${MODEL}-${VERS}.bin ${MODEL}-${VERSFILE}.bin


echo ""

