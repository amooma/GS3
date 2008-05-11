# Gemeinschaft
# $Revision$

DIR=$(shell pwd)
GEMEINSCHAFT_TAG_ROOT=../../..
LANGS=de_DE en_US
COPYRIGHT_HOLDER=Amooma GmbH, http://www.amooma.de
GETTEXT_DOMAIN=gemeinschaft-gui


targets:
	@echo
	@echo "Targets:"
	@echo "make update-po : Update the .po files"
	@echo "make all       : Generate .php and .mo files"
	@echo

_pot:
	@echo "### Generating the .pot (.po template) ..."
	cd ${GEMEINSCHAFT_TAG_ROOT} \
	&& find . -type f \
		! -path '*/.svn/*' \
		! -path '*/_*' \
		! -path '* *' \
		-print0 \
	| xargs -0 grep -I -l '__(' \
	| xgettext -f - -L 'PHP' -d '${GETTEXT_DOMAIN}' -o '${DIR}/pot/${GETTEXT_DOMAIN}.pot' --copyright-holder='${COPYRIGHT_HOLDER}' --keyword='__' --from-code='UTF-8' -n -s --strict

sort-po:
	@for LANGDIR in ${LANGS} ;\
	do \
		echo "### Sorting strings in $${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po ..." ;\
		msgcat -n -s --strict -o "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" ;\
	done

update-po: _pot
	@for LANGDIR in ${LANGS} ;\
	do \
		echo "### Merging ${GETTEXT_DOMAIN}.pot into $${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po ..." ;\
		msgmerge -N -U --add-location -s --strict "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" "pot/${GETTEXT_DOMAIN}.pot" ;\
	done

gen-mo:
	@for LANGDIR in ${LANGS} ;\
	do \
		echo "### Generating $${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.mo ..." ;\
		msgfmt -c --strict --statistics -o "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.mo" "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" ;\
	done

gen-php:
	@echo "### Generating the .php files ..."
	./po-to-php.php

all: gen-mo gen-php


.PHONY: targets _pot update-po gen-mo gen-php all

