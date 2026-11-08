#!/bin/bash

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
# Usage: get-firmware.sh [model ...]
#   without arguments all models are fetched, otherwise only the
#   given ones, e.g.:  get-firmware.sh D815   (or snomD815, d815)
#####################################################################

ONLY_MODELS="$*"

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

# normalize a model name: case-insensitive, "snom" prefix optional
# (snomD815, D815, d815 -> d815)
function model_key {
	local k="${1,,}"
	echo "${k#snom}"
}

# returns 0 if the model was requested on the command line (or no
# model was given at all)
function wanted {
	[ -z "${ONLY_MODELS}" ] && return 0
	for m in ${ONLY_MODELS}; do
		[ "$(model_key "$m")" == "$(model_key "$1")" ] && return 0
	done
	return 1
}

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
	do wanted $i && get_firmware $i
done


#####################################################################
#  10.x (D7xx / D8xx)
#####################################################################
# Since firmware 10 Snom uses a directory per version and https only:
#   https://downloads.snom.com/fw/<version>/bin/<model>-<version>-SIP-r.<ext>
# D8xx images are .swu, D7xx/D3xx/D1xx images are .bin.
# sw-update.php looks for <model>-<normalized version>.<ext>,
# e.g. snomD815-10.01.226.13.swu
# Versions / checksums: https://service.snom.com/ -> Release notes

CURL="/usr/bin/curl"
V10_VERS='10.1.226.13'
V10_VERSFILE='10.01.226.13'

# model  extension  sha256 (from the Snom release notes)
V10_MODELS="snomD815 swu bc14cab8dce63974c51a5a84e58e83e7f6d6f1426b944902f369c212aeba2fcb"

function get_firmware_v10 {
	MODEL=$1
	EXT=$2
	SHA256=$3
	FILE="${MODEL}-${V10_VERS}-SIP-r.${EXT}"
	URL="https://downloads.snom.com/fw/${V10_VERS}/bin/${FILE}"
	echo -n "Fetching firmware ${V10_VERS} for ${MODEL} ...        "
	if [ ! -x ${CURL} ]; then
		echo "Failed (${CURL} not found)"
		return 1
	fi
	if [ ! -s "${FILE}" ]; then
		# download to a temporary file, a phone must never get a
		# partial image
		if ! ${CURL} -s -f -L --retry 2 -m 1800 -o "${FILE}.part" "${URL}"; then
			rm -f "${FILE}.part"
			echo 'Failed (download)'
			return 1
		fi
		mv "${FILE}.part" "${FILE}"
	fi
	if [ -n "${SHA256}" ] && [ "$(sha256sum "${FILE}" | cut -d' ' -f1)" != "${SHA256}" ]; then
		rm -f "${FILE}"
		echo 'Failed (SHA-256 mismatch, file deleted)'
		return 1
	fi
	ln -sf "${FILE}" "${MODEL}-${V10_VERSFILE}.${EXT}"
	echo 'Done'
}

echo "${V10_MODELS}" | while read MODEL EXT SHA256; do
	[ -z "${MODEL}" ] && continue
	wanted ${MODEL} && get_firmware_v10 ${MODEL} ${EXT} ${SHA256}
done

# complain about requested models this script does not know
ALL_MODELS="${MODELS} $(echo "${V10_MODELS}" | cut -d' ' -f1)"
for m in ${ONLY_MODELS}; do
	found=0
	for a in ${ALL_MODELS}; do
		[ "$(model_key "$m")" == "$(model_key "$a")" ] && found=1
	done
	[ $found -eq 0 ] && echo "Unknown model \"$m\", known models: $(echo ${ALL_MODELS})"
done
