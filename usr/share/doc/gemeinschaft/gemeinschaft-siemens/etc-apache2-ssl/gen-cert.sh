#/bin/bash

#####################################################################
#            Gemeinschaft - asterisk cluster gemeinschaft
# 
# $Revision: 188 $
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

if ! which openssl 1>>/dev/null ; then
	echo "openssl not found"
	exit 1
fi

GS_PROV_HOST=`/opt/gemeinschaft/sbin/gs-get-conf PROV_HOST`

# generate private key
openssl genrsa \
	-out openstage-key.pem \
	1024

# generate certificate signing request
openssl req \
	-new \
	-batch \
	-key openstage-key.pem \
	-out openstage-csr.pem \
	-subj "/C=DE/O=Gemeinschaft/OU=Gemeinschaft/CN=${GS_PROV_HOST}"

# print certificate signing request
openssl req \
	-noout \
	-text \
	-in openstage-csr.pem

# generate certificate
openssl x509 \
	-req \
	-days 3650 \
	-in      openstage-csr.pem \
	-signkey openstage-key.pem \
	-out     openstage-crt.pem

# print certificate
openssl x509 \
	-noout \
	-text \
	-in openstage-crt.pem

echo '###################################################################'
echo '#  Done.                                                          #'
echo '#  Make sure only root can read the private key                   #'
echo '#    chown root:root openstage-key.pem                            #'
echo '#    chmod 644 openstage-key.pem                                  #'
echo '#  Now move the files to /etc/apache2/ssl/ (Debian)               #'
echo '#  or /etc/httpd/ssl/ (RedHat)                                    #'
echo '#    mkdir /etc/apache2/ssl                                       #'
echo '#    mv openstage-* /etc/apache2/ssl/                             #'
echo '#  and configure Apache like this                                 #'
echo '#    SSLCertificateFile    /etc/apache2/ssl/openstage-crt.pem     #'
echo '#    SSLCertificateKeyFile /etc/apache2/ssl/openstage-key.pem     #'
echo '###################################################################'

