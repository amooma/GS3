#!/bin/bash -x

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
SNOM_BASE_URL="http://downloads.snom.com/fw/"
SNOM_BASE_URL_6TO7="http://provisioning.snom.com/from6to7/"

if [ ! -x ${WGET} ]; then
	echo "${WGET} not found!"
	exit 1
fi

cd "${SNOM_SW_DIR}" || exit 1

#####################################################################
#  8.7.5.17
#####################################################################
MODELS="snom300
snom320
snom360
snom370
snom821
snom870
snom710
snom720
snom725
snom715
snom760"

function get_firmware {
if [[ $i == snom3* ]];
then
	VERS='8.7.5.17-SIP-f'
else
	VERS='8.7.5.17-SIP-r'
fi
VERSFILE='08.07.05.17'

MODEL=$1
echo -n "Fetching firmware ${VERS} for ${MODEL} ...        "
${DOWNLOAD} ${SNOM_BASE_URL}${MODEL}-${VERS}.bin && echo 'Done' ||
echo 'Failed'
ln -s ${MODEL}-${VERS}.bin ${MODEL}-${VERSFILE}.bin
}
for i in $MODELS;
	do get_firmware $i
done
