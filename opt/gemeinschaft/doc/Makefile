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

all: before tohtml topdf

before:
	@ if [ `which yum 2>>/dev/null` ]; then \
		echo '************************************************************'; \
		echo '*  Installing DocBook utils using yum ...                  *'; \
		echo '************************************************************'; \
		yum -y install docbook-dtds docbook-style-xsl docbook-utils-pdf; \
	else if [ `which up2date 2>>/dev/null` ]; then \
		echo '************************************************************'; \
		echo '*  Installing DocBook utils using up2date ...              *'; \
		echo '************************************************************'; \
		up2date -f -i docbook-dtds docbook-style-xsl docbook-utils-pdf; \
	else \
		echo '************************************************************'; \
		echo '*  No installer found. (yum, up2date)                      *'; \
		echo '************************************************************'; \
	fi; fi


tohtml:
	@ if [ ! -x /usr/bin/docbook2html ]; then \
		echo '************************************************************'; \
		echo '*  DocBook utils are not installed.                        *'; \
		echo '*  Please use "make before" first.                         *'; \
		echo '************************************************************'; \
		exit 1; \
	fi
	
	@ echo '************************************************************';
	@ echo '*  Making HTML ...                                         *';
	@ echo '************************************************************';
	
	@ echo "Resolving XIncludes ..."
	xmllint --xinclude --output docbook/HANDBUCH-full.xml docbook/HANDBUCH.xml
	
	@ echo "Transforming to HTML ..."
	mkdir -p html
	docbook2html -o html/ docbook/HANDBUCH-full.xml
	rm -f docbook/HANDBUCH-full.xml
	@ echo '************************************************************';
	@ echo '*  HTML done.                                              *';
	@ echo '************************************************************';


topdf:
	@ if [ ! -x /usr/bin/docbook2pdf ]; then \
		echo '************************************************************'; \
		echo '*  DocBook utils are not installed.                        *'; \
		echo '*  Please use "make before" first.                         *'; \
		echo '************************************************************'; \
		exit 1; \
	fi
	
	@ echo '************************************************************';
	@ echo '*  Making PDF ...                                          *';
	@ echo '************************************************************';
	
	@ echo "Resolving XIncludes ..."
	xmllint --xinclude --output docbook/HANDBUCH-full.xml docbook/HANDBUCH.xml
	
	@ echo "Transforming to PDF ..."
	mkdir -p pdf
	docbook2pdf -o pdf/ docbook/HANDBUCH-full.xml
	rm -f docbook/HANDBUCH-full.xml
	mv pdf/HANDBUCH-full.pdf pdf/HANDBUCH.pdf
	@ echo '************************************************************';
	@ echo '*  PDF done.                                               *';
	@ echo '************************************************************';


clean:
	rm -rf html/*
	rm -rf pdf/*
