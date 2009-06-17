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

xmllint = $(shell which xmllint)
xmlto = $(shell which xmlto)
xsltproc = $(shell which xsltproc)
docbook2html = $(shell which docbook2html)
docbook2pdf = $(shell which docbook2pdf)
tidy = $(shell which tidy)

docbook_sources = $(wildcard docbook/*.xml)
man_orig = $(wildcard docbook/scripts/*.xml)
man_man = $(patsubst %.xml,%.man,$(patsubst docbook/scripts/%,man/%,$(man_orig)))

build-log-validate-log = build-logs/validate.log
build-log-html-chunk-xsl-log = build-logs/html-chunk-xsl.log
build-log-pdf-jw-log = build-logs/pdf-jw.log
build-log-man-xsl-log = build-logs/man-xsl.log
build-log-sep = ====================================================================

docbook-xsl-images = /usr/share/xml/docbook/stylesheet/nwalsh/images


help:
	@ echo ""
#	@ echo "Targets:"
#	@ echo "  make html-jw  : Generate HTML pages using jade/jw"
#	@ echo "  make html-xsl : Generate HTML pages using xmlto"
#	@ echo "  make html     : Generate HTML pages using the preferred conversion (= html-xsl)"
#	@ echo "  make pdf-jw   : Generate PDF using jade/jw"
#	@ echo "  make pdf      : Generate PDF using the preferred conversion (= pdf-jw)"
#	@ echo "  make man-xsl  : Generate man pages using xmlto"
#	@ echo "  make man      : Generate man pages using the preferred conversion (= man-xsl)"
#	@ echo "  make installation-trunk-single-html : Generate installation-trunk-single"
#	@ echo ""
	@ echo " ## Targets:"
	@ echo ""
	@ echo " make html ....................: Generate HTML pages"
	@ echo " make pdf .....................: Generate PDF"
	@ echo " make man .....................: Generate man pages"
	@ echo " make clean ...................: Remove all generated files"
	@ echo ""
	@ echo " ## More targets:"
	@ echo ""
	@ echo " make validate ................: Validate DocBook sources"
	@ echo "                                 (always before commit!)"
	@ echo " make validate-html-chunk-xsl .: Validate the output of"
	@ echo "                                 html-chunk-xsl"
#	@ echo " make validate-html-chunk-xsl-tidy"
#	@ echo "                                 : Validate the output of"
#	@ echo "                                   html-chunk-xsl-tidy"
	@ echo ""
	@ echo " make build-log-validate ......: Run \`make validate\` and"
	@ echo "                                 write $(build-log-validate-log)"
	@ echo " make build-log-html-chunk-xsl : Run \`make html-chunk-xsl\` and"
	@ echo "                                 write $(build-log-html-chunk-xsl-log)"
	@ echo " make build-log-pdf-jw ........: Run \`make pdf-jw\` and"
	@ echo "                                 write $(build-log-pdf-jw-log)"
	@ echo " make build-log-man-xsl .......: Run \`make man-xsl\` and"
	@ echo "                                 write $(build-log-man-xsl-log)"
	@ echo " make build-log-all ...........: All build logs"
	@ echo ""
	@ echo " make installation-trunk-single-html"
	@ echo "                               : Generate installation-trunk-single"
	@ echo ""


#.LOW_RESOLUTION_TIME: tmp tmp/_tmp_dir-stamp tmp/_have-xmllint-stamp


# We use a tmp directory for various make timestamps and
# intermediate files in order not to clutter the main directory.
# If a target needs this directory it has to depend on
# tmp/_tmp_dir-stamp.
# The downside of the tmp directory is that whenever we create
# a file in tmp and thus modify the directory's mtime we have
# to reset the directory's mtime to the time when it was created:
# touch -d "`cat tmp/_tmp_dir-stamp`" tmp

tmp:
	mkdir -p tmp
	sleep 1

tmp/_tmp_dir-stamp: tmp
	date '+%Y-%m-%d %H:%M:00 %z' > tmp/_tmp_dir-stamp
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp


tmp/_have-xmllint-stamp: tmp/_tmp_dir-stamp
	@ echo "Checking for xmllint ..."
	@ if [ -z "$(xmllint)" ]; then \
		echo '############################################################'; \
		echo '#  xmllint not found.                                      #'; \
		echo '#  Please install DocBook Utils.                           #'; \
		if [ `which aptitude 2>>/dev/null` ]; then \
			echo '#  ( sudo aptitude install docbook-xml docbook-xsl docbook-utils xsltproc xmlto )'; \
		else \
		if [ `which apt-get 2>>/dev/null` ]; then \
			echo '#  ( sudo apt-get install docbook-xml docbook-xsl docbook-utils xsltproc xmlto )'; \
		else \
		if [ `which yum 2>>/dev/null` ]; then \
			echo '#  ( sudo yum install docbook-utils docbook-dtds docbook-style-xsl )'; \
		else \
		if [ `which up2date 2>>/dev/null` ]; then \
			echo '#  ( sudo up2date -i docbook-utils docbook-dtds docbook-style-xsl )'; \
		fi; fi; fi; fi; \
		echo '############################################################'; \
		exit 1; \
	fi
	date > tmp/_have-xmllint-stamp
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp


tmp/_have-xmlto-stamp: tmp/_tmp_dir-stamp
	@ echo "Checking for xmlto ..."
	@ if [ -z "$(xmlto)" ]; then \
		echo '############################################################'; \
		echo '#  xmlto not found.                                        #'; \
		echo '#  Please install xmlto.                                   #'; \
		if [ `which aptitude 2>>/dev/null` ]; then \
			echo '#  ( sudo aptitude install xsltproc xmlto docbook-xml docbook-xsl )'; \
		else \
		if [ `which apt-get 2>>/dev/null` ]; then \
			echo '#  ( sudo apt-get install xsltproc xmlto docbook-xml docbook-xsl )'; \
		else \
		if [ `which yum 2>>/dev/null` ]; then \
			echo '#  ( sudo yum install xmlto docbook-dtds docbook-style-xsl )'; \
		else \
		if [ `which up2date 2>>/dev/null` ]; then \
			echo '#  ( sudo up2date -i xmlto docbook-dtds docbook-style-xsl )'; \
		fi; fi; fi; fi; \
		echo '############################################################'; \
		exit 1; \
	fi
	date > tmp/_have-xmlto-stamp
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp


tmp/_have-xsltproc-stamp: tmp/_tmp_dir-stamp
	@ echo "Checking for xsltproc ..."
	@ if [ -z "$(xsltproc)" ]; then \
		echo '############################################################'; \
		echo '#  xmlto not found.                                        #'; \
		echo '#  Please install xmlto.                                   #'; \
		if [ `which aptitude 2>>/dev/null` ]; then \
			echo '#  ( sudo aptitude install xsltproc )'; \
		else \
		if [ `which apt-get 2>>/dev/null` ]; then \
			echo '#  ( sudo apt-get install xsltproc )'; \
		fi; fi; \
		echo '############################################################'; \
		exit 1; \
	fi
	date > tmp/_have-xsltproc-stamp
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp


tmp/_have-docbook2html-stamp: tmp/_tmp_dir-stamp
	@ echo "Checking for docbook2html ..."
	@ if [ -z "$(docbook2html)" ]; then \
		echo '############################################################'; \
		echo '#  docbook2html not found.                                 #'; \
		echo '#  Please install DocBook Utils.                           #'; \
		if [ `which aptitude 2>>/dev/null` ]; then \
			echo '#  ( sudo aptitude install docbook-utils )                 #'; \
		else \
		if [ `which apt-get 2>>/dev/null` ]; then \
			echo '#  ( sudo apt-get install docbook-utils )                  #'; \
		else \
		if [ `which yum 2>>/dev/null` ]; then \
			echo '#  ( sudo yum install docbook-utils )                      #'; \
		else \
		if [ `which up2date 2>>/dev/null` ]; then \
			echo '#  ( sudo up2date -i docbook-utils )                       #'; \
		fi; fi; fi; fi; \
		echo '############################################################'; \
		exit 1; \
	fi
	date > tmp/_have-docbook2html-stamp
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp


tmp/_have-docbook2pdf-stamp: tmp/_tmp_dir-stamp
	@ echo "Checking for docbook2pdf ..."
	@ if [ -z "$(docbook2pdf)" ]; then \
		echo '############################################################'; \
		echo '#  docbook2pdf not found.                                  #'; \
		echo '#  Please install DocBook Utils.                           #'; \
		if [ `which aptitude 2>>/dev/null` ]; then \
			echo '#  ( sudo aptitude install docbook-xml docbook-xsl docbook-utils xsltproc xmlto )'; \
		else \
		if [ `which apt-get 2>>/dev/null` ]; then \
			echo '#  ( sudo apt-get install docbook-xml docbook-xsl docbook-utils xsltproc xmlto )'; \
		else \
		if [ `which yum 2>>/dev/null` ]; then \
			echo '#  ( sudo yum install docbook-utils docbook-utils-pdf docbook-dtds docbook-style-xsl )'; \
		else \
		if [ `which up2date 2>>/dev/null` ]; then \
			echo '#  ( sudo up2date -i docbook-utils docbook-utils-pdf docbook-dtds docbook-style-xsl )'; \
		fi; fi; fi; fi; \
		echo '############################################################'; \
		exit 1; \
	fi
	date > tmp/_have-docbook2pdf-stamp
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp


tmp/_have-tidy-stamp: tmp/_tmp_dir-stamp
	@ echo "Checking for tidy ..."
	@ if [ -z "$(tidy)" ]; then \
		echo '############################################################'; \
		echo '#  tidy not found.                                         #'; \
		echo '#  Please install DocBook Utils.                           #'; \
		if [ `which aptitude 2>>/dev/null` ]; then \
			echo '#  ( sudo aptitude install tidy )'; \
		else \
		if [ `which apt-get 2>>/dev/null` ]; then \
			echo '#  ( sudo apt-get install tidy )'; \
		else \
		if [ `which yum 2>>/dev/null` ]; then \
			echo '#  ( sudo yum install tidy )'; \
		else \
		if [ `which up2date 2>>/dev/null` ]; then \
			echo '#  ( sudo up2date -i tidy )'; \
		fi; fi; fi; fi; \
		echo '############################################################'; \
		exit 1; \
	fi
	date > tmp/_have-tidy-stamp
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp


tmp/_docbook-valid-stamp: $(docbook_sources) tmp/_have-xmllint-stamp
	@ echo '############################################################';
	@ echo '#  Validating DocBook sources ...                          #';
	@ echo '############################################################';
	$(xmllint) --xinclude --postvalid --noout \
		docbook/HANDBUCH.xml
	@ echo '############################################################';
	@ echo '#  DocBook sources are valid.                              #';
	@ echo '############################################################';
	date > tmp/_docbook-valid-stamp
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp

validate: tmp/_docbook-valid-stamp


tmp/docbook-HANDBUCH-one-file.xml: $(docbook_sources) tmp/_docbook-valid-stamp tmp/_have-xmllint-stamp
	@ echo "Resolving XIncludes in docbook/HANDBUCH.xml ..."
	$(xmllint) \
		--xinclude \
		--output tmp/docbook-HANDBUCH-one-file.xml \
		docbook/HANDBUCH.xml
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp
	@ echo "tmp/docbook-HANDBUCH-one-file.xml DONE"


#html-chunk-jw: tmp/docbook-HANDBUCH-one-file.xml tmp/_have-docbook2html-stamp
#	@ echo '############################################################';
#	@ echo '#  Making HTML ...                                         #';
#	@ echo '############################################################';
#	
#	@ echo "Transforming to HTML ..."
#	mkdir -p html-chunk-jw
#	$(docbook2html) -e no-idref -o html-chunk-jw/ tmp/docbook-HANDBUCH-one-file.xml
#	@ echo '############################################################';
#	@ echo '#  HTML done.                                              #';
#	@ echo '############################################################';


#html-chunk-xsl: tmp/docbook-HANDBUCH-one-file.xml tmp/_have-xmlto-stamp
#	@ echo '############################################################';
#	@ echo '#  Making HTML ...                                         #';
#	@ echo '############################################################';
#	
#	@ echo "Transforming to HTML ..."
#	mkdir -p html-chunk
#	$(xmlto) --skip-validation \
#		-o html-chunk/ \
#		--stringparam 'use.id.as.filename=1' \
#		--stringparam 'html.ext=.html' \
#		--stringparam 'html.stylesheet=css/style.css' \
#		--stringparam 'admon.graphics=1' \
#		--stringparam 'admon.textlabel=1' \
#		--stringparam 'toc.section.depth=2' \
#		--stringparam 'toc.max.depth=1' \
#		--stringparam 'generate.section.toc.level=10' \
#		--stringparam 'toc.list.type=dl' \
#		--stringparam 'chapter.autolabel=1' \
#		--stringparam 'section.autolabel=1' \
#		--stringparam 'section.autolabel.max.depth=2' \
#		--stringparam 'section.label.includes.component.label=1' \
#		--stringparam 'appendix.autolabel=A' \
#		--stringparam 'autotoc.label.in.hyperlink=0' \
#		--stringparam 'preface.autolabel=i' \
#		--stringparam 'chunker.output.indent=no' \
#		--stringparam 'chunk.first.sections=1' \
#		--stringparam 'html.extra.head.links=1' \
#		--stringparam 'ulink.target=_blank' \
#		--stringparam 'css.decoration=0' \
#		--stringparam 'generate.index=1' \
#		--stringparam 'id.warnings=0' \
#		--stringparam 'chunker.output.encoding=UTF-8' \
#		--stringparam 'chunker.output.omit-xml-declaration=yes' \
#		--stringparam 'generate.manifest=0' \
#		--stringparam 'manifest=HTML.manifest' \
#		--stringparam 'manifest.in.base.dir=0' \
#		--stringparam 'navig.graphics=1' \
#		xhtml \
#		tmp/docbook-HANDBUCH-one-file.xml
#	touch -d "`cat tmp/_tmp_dir-stamp`" tmp
#	@ echo '############################################################';
#	@ echo '#  HTML done.                                              #';
#	@ echo '############################################################';


html-chunk-xsl/index.html: docbook/HANDBUCH.xml tmp/_docbook-valid-stamp ../xsl/docbook-html-chunk.xsl tmp/_have-xsltproc-stamp tmp/_have-tidy-stamp
	@ echo '############################################################';
	@ echo '#  Making HTML (chunk, XSLT) ...                           #';
	@ echo '############################################################';
	
	@ echo "Transforming to HTML ..."
	mkdir -p html-chunk-xsl
#	( $(xsltproc) \
#		--xinclude \
#		--novalid \
#		--stringparam 'current.docid' 'foo' \
#		-o "html-chunk-xsl/" \
#		"../xsl/docbook-html-chunk.xsl" \
#		"docbook/HANDBUCH.xml" \
#		2>&1 \
#		; e=$$? ; echo $$e ; [ $$e == 0 -o $$e == 1 ] \
#		) | tee tmp/html-chunk-xsl-xsltproc-full.log
	$(xsltproc) \
		--xinclude \
		--novalid \
		--stringparam 'current.docid' 'foo' \
		-o "html-chunk-xsl/" \
		"../xsl/docbook-html-chunk.xsl" \
		"docbook/HANDBUCH.xml"
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp
#	cat tmp/html-chunk-xsl-xsltproc-full.log | grep -v '^Writing ' > tmp/html-chunk-xsl-xsltproc-warn.log
	touch -d "`cat tmp/_tmp_dir-stamp`" tmp
#	@ echo '#'
#	@ echo '# xsltproc full log     : tmp/html-chunk-xsl-xsltproc-full.log'
#	@ echo '# xsltproc warnings log : tmp/html-chunk-xsl-xsltproc-warn.log'
#	@ echo '#'
	@ echo '############################################################';
	@ echo '#  HTML (chunk, XSLT) done.                                #';
	@ echo '############################################################';
	
	@ echo "Tidying HTML ..."
	for html_file in `ls html-chunk-xsl/*.html`; do \
		echo "Tidying `basename $$html_file` ..." ;\
		$(tidy) \
			--add-xml-decl no --assume-xml-procins yes --doctype auto \
			--input-xml no --output-xhtml yes \
			--join-classes no --logical-emphasis no --merge-divs no --merge-spans no \
			--ncr yes --numeric-entities no \
			--quote-marks yes --quote-nbsp yes \
			--show-errors 100 --show-warnings no \
			--indent-spaces 2 --tab-size 2 --vertical-space no \
			--markup yes --sort-attributes none \
			--wrap 180 --wrap-attributes no \
			--wrap-asp no --wrap-php no --wrap-script-literals no --wrap-sections no \
			--char-encoding utf8 --input-encoding utf8 --output-encoding utf8 --output-bom no \
			--tidy-mark no \
			-quiet -asxhtml -indent -access 0 \
			--indent auto \
			-output $$html_file.tidied \
			$$html_file \
			; e=$$? ; [ $$e == 0 -o $$e == 1 ] || exit 1 ;\
		mv $$html_file.tidied $$html_file ;\
	done
	@ echo '############################################################';
	@ echo '#  HTML (chunk, tidy) done.                                #';
	@ echo '############################################################';
	
	@ echo "Copying CSS stylesheet ..."
	cp -rLT --preserve=timestamps --strip-trailing-slashes \
			"../css" "html-chunk-xsl/css" ;\
	
	@ echo "Copying DocBook images ..."
	@ if [ -e "$(docbook-xsl-images)" ]; then \
		cp -rLT --preserve=timestamps --strip-trailing-slashes \
			"$(docbook-xsl-images)" "html-chunk-xsl/dbkimg" ;\
	else \
		echo "Did not find DocBook images at" ;\
		echo "\"$(docbook-xsl-images)\"!" ;\
	fi


html-chunk-xsl: html-chunk-xsl/index.html


html-chunk-xsl-out := $(wildcard html-chunk-xsl/*.html)
#html-chunk-xsl-tidy-out := $(patsubst html-chunk-xsl/%.html,html-chunk-xsl-tidied/%.html,$(wildcard html-chunk-xsl/*.html))
#
#html-chunk-xsl-tidied:
#	mkdir -p html-chunk-xsl-tidied
#
#html-chunk-xsl-tidied/%.html: html-chunk-xsl/%.html tmp/_have-tidy-stamp html-chunk-xsl-tidied html-chunk-xsl/index.html $(html-chunk-xsl-out)
#	@#cp $< $@
#	$(tidy) \
#		--add-xml-decl no --assume-xml-procins yes --doctype auto \
#		--input-xml no --output-xhtml yes \
#		--join-classes no --logical-emphasis no --merge-divs no --merge-spans no \
#		--ncr yes --numeric-entities no \
#		--quote-marks yes --quote-nbsp yes \
#		--show-errors 100 --show-warnings yes \
#		--indent-spaces 2 --tab-size 2 --vertical-space no \
#		--markup yes --sort-attributes none \
#		--wrap 180 --wrap-attributes no \
#		--wrap-asp no --wrap-php no --wrap-script-literals no --wrap-sections no \
#		--char-encoding utf8 --input-encoding utf8 --output-encoding utf8 --output-bom no \
#		--tidy-mark no \
#		-quiet -asxhtml -indent -access 0 \
#		--indent auto \
#		-output $@ \
#		$< \
#		; e=$$? ; [ $$e == 0 -o $$e == 1 ]
#
#html-chunk-xsl-tidied/index.html: html-chunk-xsl-tidied html-chunk-xsl $(html-chunk-xsl-out) $(html-chunk-xsl-tidy-out)
#	@ echo '############################################################';
#	@ echo '#  HTML (chunk, tidy) done.                                #';
#	@ echo '############################################################';
#
#html-chunk-xsl-tidy: html-chunk-xsl/index.html $(html-chunk-xsl-tidy-out)
#
#html: html-chunk-xsl-tidy

html: html-chunk-xsl


validate-html-chunk-xsl: tmp/_have-xmllint-stamp
	@ for file in $(html-chunk-xsl-out); do \
		echo "Validating $$file ..." ;\
		$(xmllint) --postvalid --noout $$file ;\
	done;

#validate-html-chunk-xsl-tidy: tmp/_have-xmllint-stamp
#	@ for file in $(html-chunk-xsl-tidy-out); do \
#		echo "Validating $$file ..." ;\
#		$(xmllint) --postvalid --noout $$file ;\
#	done;

build-log-validate:
	mkdir -p `dirname $(build-log-validate-log)`
	rm $(build-log-validate-log) || true
	touch $(build-log-validate-log)
	$(MAKE) clean
	@ \
		echo $(build-log-sep)              | tee -ia $(build-log-validate-log) ;\
		echo "= $@:   make validate"       | tee -ia $(build-log-validate-log) ;\
		echo $(build-log-sep)              | tee -ia $(build-log-validate-log) ;\
		$(MAKE) validate             2>&1  | tee -ia $(build-log-validate-log) ;\
		([ $$? == 0 ] && echo -e "\\\\ \n ==>  OK" || echo -e "\\\\ \n ==>  FAILED" ) \
		                                   | tee -ia $(build-log-validate-log) ;\
		echo ""                            | tee -ia $(build-log-validate-log) ;\
		\
		echo $(build-log-sep)              | tee -ia $(build-log-validate-log) ;\
		echo "= $@:   done"                | tee -ia $(build-log-validate-log) ;\
		echo $(build-log-sep)              | tee -ia $(build-log-validate-log) ;\
		true
	$(MAKE) clean

build-log-html-chunk-xsl:
	mkdir -p `dirname $(build-log-html-chunk-xsl-log)`
	rm $(build-log-html-chunk-xsl-log) || true
	touch $(build-log-html-chunk-xsl-log)
	$(MAKE) clean
	# make validate before so the log does not contain the output of make validate
	$(MAKE) validate
	@ \
		echo $(build-log-sep)              | tee -ia $(build-log-html-chunk-xsl-log) ;\
		echo "= $@:   make html-chunk-xsl" | tee -ia $(build-log-html-chunk-xsl-log) ;\
		echo $(build-log-sep)              | tee -ia $(build-log-html-chunk-xsl-log) ;\
		$(MAKE) html                 2>&1  | tee -ia $(build-log-html-chunk-xsl-log) ;\
		([ $$? == 0 ] && echo -e "\\\\ \n ==>  OK" || echo -e "\\\\ \n ==>  FAILED" ) \
		                                   | tee -ia $(build-log-html-chunk-xsl-log) ;\
		echo ""                            | tee -ia $(build-log-html-chunk-xsl-log) ;\
		\
		echo $(build-log-sep)              | tee -ia $(build-log-html-chunk-xsl-log) ;\
		echo "= $@:   done"                | tee -ia $(build-log-html-chunk-xsl-log) ;\
		echo $(build-log-sep)              | tee -ia $(build-log-html-chunk-xsl-log) ;\
		true
	$(MAKE) clean

build-log-pdf-jw:
	mkdir -p `dirname $(build-log-pdf-jw-log)`
	rm $(build-log-pdf-jw-log) || true
	touch $(build-log-pdf-jw-log)
	$(MAKE) clean
	# make validate before so the log does not contain the output of make validate
	$(MAKE) validate
	@ \
		echo $(build-log-sep)              | tee -ia $(build-log-pdf-jw-log) ;\
		echo "= $@:   make pdf-jw"         | tee -ia $(build-log-pdf-jw-log) ;\
		echo $(build-log-sep)              | tee -ia $(build-log-pdf-jw-log) ;\
		$(MAKE) pdf-jw               2>&1  | tee -ia $(build-log-pdf-jw-log) ;\
		([ $$? == 0 ] && echo -e "\\\\ \n ==>  OK" || echo -e "\\\\ \n ==>  FAILED" ) \
		                                   | tee -ia $(build-log-pdf-jw-log) ;\
		echo ""                            | tee -ia $(build-log-pdf-jw-log) ;\
		\
		echo $(build-log-sep)              | tee -ia $(build-log-pdf-jw-log) ;\
		echo "= $@:   done"                | tee -ia $(build-log-pdf-jw-log) ;\
		echo $(build-log-sep)              | tee -ia $(build-log-pdf-jw-log) ;\
		true
	$(MAKE) clean

build-log-man-xsl:
	mkdir -p `dirname $(build-log-man-xsl-log)`
	rm $(build-log-man-xsl-log) || true
	touch $(build-log-man-xsl-log)
	$(MAKE) clean
	# make validate before so the log does not contain the output of make validate
	$(MAKE) validate
	@ \
		echo $(build-log-sep)              | tee -ia $(build-log-man-xsl-log) ;\
		echo "= $@:   make man-xsl"        | tee -ia $(build-log-man-xsl-log) ;\
		echo $(build-log-sep)              | tee -ia $(build-log-man-xsl-log) ;\
		$(MAKE) man-xsl              2>&1  | tee -ia $(build-log-man-xsl-log) ;\
		([ $$? == 0 ] && echo -e "\\\\ \n ==>  OK" || echo -e "\\\\ \n ==>  FAILED" ) \
		                                   | tee -ia $(build-log-man-xsl-log) ;\
		echo ""                            | tee -ia $(build-log-man-xsl-log) ;\
		\
		echo $(build-log-sep)              | tee -ia $(build-log-man-xsl-log) ;\
		echo "= $@:   done"                | tee -ia $(build-log-man-xsl-log) ;\
		echo $(build-log-sep)              | tee -ia $(build-log-man-xsl-log) ;\
		true
	$(MAKE) clean

build-log-all:
	$(MAKE) build-log-validate            || true
	$(MAKE) build-log-html-chunk-xsl      || true
	$(MAKE) build-log-pdf-jw              || true
	$(MAKE) build-log-man-xsl             || true






installation-trunk-single-html: installation-trunk-single.html
installation-trunk-single.html: docbook/installation-trunk-single-debian.xml
	@ if [ ! -f "/etc/debian_version" ]; then \
		echo '############################################################'; \
		echo '#  Currently not implemented for distributions other than Debian.'; \
		echo '############################################################'; \
		exit 1; \
	fi
	@ if [ -z `which xsltproc` ]; then \
		echo '############################################################'; \
		echo '#  xsltproc not found.'; \
		echo '#  ( aptitude install xsltproc )'; \
		echo '############################################################'; \
		exit 1; \
	fi
	@ if [ ! -f "/usr/share/xml/docbook/stylesheet/nwalsh/xhtml/onechunk.xsl" ]; then \
		echo '############################################################'; \
		echo '#  DocBook stylesheets not found.'; \
		echo '#  ( aptitude install docbook-xsl docbook-xml )'; \
		echo '############################################################'; \
		exit 1; \
	fi
	# 'chunker.output.indent' 'yes' breaks <pre>
	$(xsltproc) --nonet --novalid --xinclude \
		--stringparam 'base.dir' './' \
		--stringparam 'use.id.as.filename' '1' \
		--stringparam 'root.filename' 'installation-trunk-single' \
		--stringparam 'html.ext' '.html' \
		--stringparam 'html.stylesheet' 'css/style.css' \
		--stringparam 'admon.graphics' '0' \
		--stringparam 'admon.textlabel' '1' \
		--stringparam 'toc.section.depth' '2' \
		--stringparam 'toc.max.depth' '8' \
		--stringparam 'generate.section.toc.level' '10' \
		--stringparam 'chapter.autolabel' '1' \
		--stringparam 'section.autolabel' '1' \
		--stringparam 'section.autolabel.max.depth' '2' \
		--stringparam 'section.label.includes.component.label' '1' \
		--stringparam 'appendix.autolabel' 'A' \
		--stringparam 'autotoc.label.separator' ' - ' \
		--stringparam 'autotoc.label.in.hyperlink' '1' \
		--stringparam 'preface.autolabel' 'i' \
		--stringparam 'chunker.output.indent' 'no' \
		--stringparam 'html.extra.head.links' '1' \
		--stringparam 'ulink.target' '_blank' \
		--stringparam 'css.decoration' '0' \
		--stringparam 'generate.index' '0' \
		--stringparam 'id.warnings' '0' \
		'/usr/share/xml/docbook/stylesheet/nwalsh/xhtml/onechunk.xsl' \
		'docbook/installation-trunk-single-debian.xml'


pdf/Gemeinschaft-Handbuch.pdf: tmp/docbook-HANDBUCH-one-file.xml tmp/_have-docbook2pdf-stamp
	@ echo '############################################################';
	@ echo '#  Making PDF ...                                          #';
	@ echo '############################################################';
	
	@ echo "Transforming to PDF ..."
	mkdir -p pdf
	$(docbook2pdf) -e no-idref -o pdf/ tmp/docbook-HANDBUCH-one-file.xml
	mv pdf/docbook-HANDBUCH-one-file.pdf pdf/Gemeinschaft-Handbuch.pdf
	@ echo '############################################################';
	@ echo '#  PDF done.                                               #';
	@ echo '############################################################';

pdf-jw: pdf/Gemeinschaft-Handbuch.pdf

pdf: pdf-jw


$(man_man): man/%.man: docbook/scripts/%.xml
	@ echo "### $@ $* ..."
	cd "$(dir $<)" && $(xmllint) --xinclude --output $(patsubst %.xml,_%.xml.one-file,$*.xml) $*.xml
	$(xmlto) --skip-validation -o man/ man docbook/scripts/$(patsubst %.xml,_%.xml.one-file,$*.xml)
	@ echo ""


_man-xsl: tmp/_have-xmllint-stamp tmp/_have-xmlto-stamp $(man_man)


man-xsl: tmp/_have-xmllint-stamp tmp/_have-xmlto-stamp
	@ echo '############################################################';
	@ echo '#  Making man pages ...                                    #';
	@ echo '############################################################';
	
	@ echo "Transforming to man pages ..."
	mkdir -p man
	$(MAKE) _man-xsl
	@ echo '############################################################';
	@ echo '#  Man pages done.                                         #';
	@ echo '############################################################';


man: man-xsl


clean:
	rm -rf tmp || true
	rm -f tmp/docbook-HANDBUCH-one-file.xml || true
	rm -rf docbook/scripts/_*.xml.one-file || true
	rm -rf html/* || true
	rm -rf html-chunk-xsl/* || true
#	rm -rf html-chunk-xsl-tidy/* || true
	rm -rf pdf/* || true
	rm -rf man/* || true


.PHONY: help html-jw html-chunk-xsl html pdf _man-xsl man-xsl man

