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

# Recursively converts all *.wav files in the current directory
# to .alaw


#dir=/var/lib/asterisk/sounds/


if [ ! -x /usr/bin/sox ]
then
	echo "/usr/bin/sox not found!"
	echo ""
	exit 1
fi

#cd ${dir}
echo ""
echo "Converting all *.wav in ${dir} to *.alaw ..."
echo "-----------------------------------------------------------------"

cnt=0
for i in `find . -name '*.wav' -print | sort`
do
	base=$(dirname $i)/$(basename $i .wav)
	echo "${base}"
	sox -t wav ${i} -r 8000 -c 1 -2  -t al ${base}.al
	#sox ${i} -t raw -r 6000 -2 -c 1 - | sox -t raw -r 8000 -sw -c 1 - ${base}.al pitch -200
	#sox ${i} -r 8000 -c 1 -2 ${base}.al echo 1 0.6 150 0.6
	#sox ${i} -r 8000 -c 1 -2 ${base}.al phaser 0.6 0.6 4 0.6 2
	mv ${base}.al ${base}.alaw
	chmod a-x,a+r,go-w ${i} ${base}.alaw
	cnt=$(( $cnt + 1 ))
done
echo "-----------------------------------------------------------------"
echo "Done. ${cnt} files converted."
echo ""

