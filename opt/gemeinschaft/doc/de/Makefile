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

# this Makefile is not nice but sort of works  :)


all: prereq html pdf

prereq:
	@ if [ `which yum 2>>/dev/null` ]; then \
		# CentOS \
		echo '************************************************************'; \
		echo '*  Installing DocBook utils using yum ...                  *'; \
		echo '************************************************************'; \
		yum -y install docbook-dtds docbook-style-xsl docbook-utils-pdf; \
	\
	else if [ `which up2date 2>>/dev/null` ]; then \
		# RedHat \
		echo '************************************************************'; \
		echo '*  Installing DocBook utils using up2date ...              *'; \
		echo '************************************************************'; \
		up2date -f -i docbook-dtds docbook-style-xsl docbook-utils-pdf; \
	\
	else if [ `which apt-get 2>>/dev/null` ]; then \
		# Debian \
		echo '************************************************************'; \
		echo '*  Installing DocBook utils using apt-get ...              *'; \
		echo '************************************************************'; \
		apt-get -y install docbook-xml docbook-xsl docbook-utils xsltproc xmlto; \
	\
	else \
		echo '************************************************************'; \
		echo '*  No package installer found. (yum, up2date, apt-get)     *'; \
		echo '************************************************************'; \
	fi; fi; fi


html: prereq
	@ if [ ! -x /usr/bin/docbook2html ]; then \
		echo '************************************************************'; \
		echo '*  DocBook utils are not installed.                        *'; \
		echo '*  Please use "make prereq" first.                         *'; \
		echo '************************************************************'; \
		exit 1; \
	fi
	
	@ echo '************************************************************';
	@ echo '*  Making HTML ...                                         *';
	@ echo '************************************************************';
	
	@ echo "Resolving XIncludes ..."
	xmllint --xinclude --output docbook/HANDBUCH-complete.xml docbook/HANDBUCH.xml
	
	@ echo "Transforming to HTML ..."
	mkdir -p html
	docbook2html -e no-idref -o html/ docbook/HANDBUCH-complete.xml
	rm -f docbook/HANDBUCH-complete.xml
	@ echo '************************************************************';
	@ echo '*  HTML done.                                              *';
	@ echo '************************************************************';


pdf: prereq
	@ if [ ! -x /usr/bin/docbook2pdf ]; then \
		echo '************************************************************'; \
		echo '*  DocBook utils are not installed.                        *'; \
		echo '*  Please use "make prereq" first.                         *'; \
		echo '************************************************************'; \
		exit 1; \
	fi
	
	@ echo '************************************************************';
	@ echo '*  Making PDF ...                                          *';
	@ echo '************************************************************';
	
	@ echo "Resolving XIncludes ..."
	xmllint --xinclude --output docbook/HANDBUCH-complete.xml docbook/HANDBUCH.xml
	
	@ echo "Transforming to PDF ..."
	mkdir -p pdf
	docbook2pdf -e no-idref -o pdf/ docbook/HANDBUCH-complete.xml
	rm -f docbook/HANDBUCH-complete.xml
	mv pdf/HANDBUCH-complete.pdf pdf/HANDBUCH.pdf
	@ echo '************************************************************';
	@ echo '*  PDF done.                                               *';
	@ echo '************************************************************';


clean:
	rm -rf html/*
	rm -rf pdf/*


.PHONY: all prereq html pdf

