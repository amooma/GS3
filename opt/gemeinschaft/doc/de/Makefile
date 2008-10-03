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

xmllint=$(shell which xmllint)
xmlto=$(shell which xmlto)
docbook2html=$(shell which docbook2html)
docbook2pdf=$(shell which docbook2pdf)
man_orig=$(wildcard docbook/scripts/*.xml)
man_man=$(patsubst %.xml,%.man,$(patsubst docbook/scripts/%,man/%,$(man_orig)))

help:
	@ echo "Targets:"
	@ echo "  make html-jw  : Generate HTML pages using jade/jw"
	@ echo "  make html-xsl : Generate HTML pages using xmlto"
	@ echo "  make html     : Generate HTML pages using the preferred conversion (= html-xsl)"
	@ echo "  make pdf-jw   : Generate PDF using jade/jw"
	@ echo "  make pdf      : Generate PDF using the preferred conversion (= pdf-jw)"
	@ echo "  make man-xsl  : Generate man pages using xmlto"
	@ echo "  make man      : Generate man pages using the preferred conversion (= man-xsl)"
	@ echo ""


_xmllint:
	@ if [ -z "${xmllint}" ]; then \
		echo '************************************************************'; \
		echo '*  xmllint not found.                                      *'; \
		echo '*  Please install DocBook Utils.                           *'; \
		if [ `which aptitude 2>>/dev/null` ]; then \
			echo '*  ( sudo aptitude install docbook-xml docbook-xsl docbook-utils xsltproc xmlto )'; \
		else \
		if [ `which apt-get 2>>/dev/null` ]; then \
			echo '*  ( sudo apt-get install docbook-xml docbook-xsl docbook-utils xsltproc xmlto )'; \
		else \
		if [ `which yum 2>>/dev/null` ]; then \
			echo '*  ( sudo yum install docbook-utils docbook-dtds docbook-style-xsl )'; \
		else \
		if [ `which up2date 2>>/dev/null` ]; then \
			echo '*  ( sudo up2date -i docbook-utils docbook-dtds docbook-style-xsl )'; \
		fi; fi; fi; fi; \
		echo '************************************************************'; \
		exit 1; \
	fi


_xmlto:
	@ if [ -z "${xmlto}" ]; then \
		echo '************************************************************'; \
		echo '*  xmlto not found.                                        *'; \
		echo '*  Please install xmlto.                                   *'; \
		if [ `which aptitude 2>>/dev/null` ]; then \
			echo '*  ( sudo aptitude install xsltproc xmlto )                *'; \
		else \
		if [ `which apt-get 2>>/dev/null` ]; then \
			echo '*  ( sudo apt-get install xsltproc xmlto )                 *'; \
		else \
		if [ `which yum 2>>/dev/null` ]; then \
			echo '*  ( sudo yum install xmlto )                              *'; \
		else \
		if [ `which up2date 2>>/dev/null` ]; then \
			echo '*  ( sudo up2date -i xmlto )                               *'; \
		fi; fi; fi; fi; \
		echo '************************************************************'; \
		exit 1; \
	fi


_docbook2html:
	@ if [ -z "${docbook2html}" ]; then \
		echo '************************************************************'; \
		echo '*  docbook2html not found.                                 *'; \
		echo '*  Please install DocBook Utils.                           *'; \
		if [ `which aptitude 2>>/dev/null` ]; then \
			echo '*  ( sudo aptitude install docbook-xml docbook-xsl docbook-utils xsltproc xmlto )'; \
		else \
		if [ `which apt-get 2>>/dev/null` ]; then \
			echo '*  ( sudo apt-get install docbook-xml docbook-xsl docbook-utils xsltproc xmlto )'; \
		else \
		if [ `which yum 2>>/dev/null` ]; then \
			echo '*  ( sudo yum install docbook-utils docbook-dtds docbook-style-xsl )'; \
		else \
		if [ `which up2date 2>>/dev/null` ]; then \
			echo '*  ( sudo up2date -i docbook-utils docbook-dtds docbook-style-xsl )'; \
		fi; fi; fi; fi; \
		echo '************************************************************'; \
		exit 1; \
	fi


_docbook2pdf:
	@ if [ -z "${docbook2pdf}" ]; then \
		echo '************************************************************'; \
		echo '*  docbook2pdf not found.                                  *'; \
		echo '*  Please install DocBook Utils.                           *'; \
		if [ `which aptitude 2>>/dev/null` ]; then \
			echo '*  ( sudo aptitude install docbook-xml docbook-xsl docbook-utils xsltproc xmlto )'; \
		else \
		if [ `which apt-get 2>>/dev/null` ]; then \
			echo '*  ( sudo apt-get install docbook-xml docbook-xsl docbook-utils xsltproc xmlto )'; \
		else \
		if [ `which yum 2>>/dev/null` ]; then \
			echo '*  ( sudo yum install docbook-utils docbook-utils-pdf docbook-dtds docbook-style-xsl )'; \
		else \
		if [ `which up2date 2>>/dev/null` ]; then \
			echo '*  ( sudo up2date -i docbook-utils docbook-utils-pdf docbook-dtds docbook-style-xsl )'; \
		fi; fi; fi; fi; \
		echo '************************************************************'; \
		exit 1; \
	fi


docbook/_HANDBUCH-one-file.xml: _xmllint
	@ echo "Resolving XIncludes in docbook/HANDBUCH.xml ..."
	${xmllint} --xinclude --output docbook/_HANDBUCH-one-file.xml docbook/HANDBUCH.xml


html-jw: docbook/_HANDBUCH-one-file.xml _docbook2html
	@ echo '************************************************************';
	@ echo '*  Making HTML ...                                         *';
	@ echo '************************************************************';
	
	@ echo "Transforming to HTML ..."
	mkdir -p html
	${docbook2html} -e no-idref -o html/ docbook/_HANDBUCH-one-file.xml
	rm -f docbook/_HANDBUCH-one-file.xml
	@ echo '************************************************************';
	@ echo '*  HTML done.                                              *';
	@ echo '************************************************************';


html-xsl: docbook/_HANDBUCH-one-file.xml _xmlto
	@ echo '************************************************************';
	@ echo '*  Making HTML ...                                         *';
	@ echo '************************************************************';
	
	@ echo "Transforming to HTML ..."
	mkdir -p html
	${xmlto} -o html/ xhtml docbook/_HANDBUCH-one-file.xml
	rm -f docbook/_HANDBUCH-one-file.xml
	@ echo '************************************************************';
	@ echo '*  HTML done.                                              *';
	@ echo '************************************************************';


html: html-xsl


pdf-jw: docbook/_HANDBUCH-one-file.xml _docbook2pdf
	@ echo '************************************************************';
	@ echo '*  Making PDF ...                                          *';
	@ echo '************************************************************';
	
	@ echo "Transforming to PDF ..."
	mkdir -p pdf
	${docbook2pdf} -e no-idref -o pdf/ docbook/_HANDBUCH-one-file.xml
	rm -f docbook/_HANDBUCH-one-file.xml
	mv pdf/_HANDBUCH-one-file.pdf pdf/HANDBUCH.pdf
	@ echo '************************************************************';
	@ echo '*  PDF done.                                               *';
	@ echo '************************************************************';


pdf: pdf-jw


$(man_man): man/%.man: docbook/scripts/%.xml
	@ echo "*** $@ $* ..."
	cd "$(dir $<)" && ${xmllint} --xinclude --output $(patsubst %.xml,_%.xml.one-file,$*.xml) $*.xml
	${xmlto} --skip-validation -o man/ man docbook/scripts/$(patsubst %.xml,_%.xml.one-file,$*.xml)


_man-xsl: _xmllint _xmlto $(man_man)


man-xsl: _xmllint _xmlto
	@ echo '************************************************************';
	@ echo '*  Making man pages ...                                    *';
	@ echo '************************************************************';
	
	@ echo "Transforming to man pages ..."
	mkdir -p man
	$(MAKE) _man-xsl
	@ echo '************************************************************';
	@ echo '*  Man pages done.                                         *';
	@ echo '************************************************************';


man: man-xsl


clean:
	rm -f docbook/_HANDBUCH-one-file.xml
	rm -rf docbook/scripts/_*.xml.one-file
	rm -rf html/*
	rm -rf pdf/*
	rm -rf man/*


.PHONY: help _xmllint _xmlto _docbook2html _docbook2pdf html-jw html-xsl html pdf _man-xsl man-xsl man

