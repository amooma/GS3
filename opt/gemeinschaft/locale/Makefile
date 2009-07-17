# Gemeinschaft
# $Revision$

DIR=$(shell pwd)
GEMEINSCHAFT_TAG_ROOT=../../..
LANGS=de_DE en_US
COPYRIGHT_HOLDER=Amooma GmbH, http://www.amooma.de
GETTEXT_DOMAIN=gemeinschaft-gui


help: _check-cwd
	@echo ""
	@echo "Note: Run make in /usr/src/gemeinschaft-trunk/opt/gemeinschaft/locale"
	@echo "(not /opt/gemeinschaft/locale)"
	@echo ""
	@echo "Targets:"
	@echo "  make update-po : Update the .po files"
	@echo "  make all       : Generate .php and .mo files"
	@echo ""

_check-cwd:
	@ if [ "x$${PWD}" = "x/opt/gemeinschaft/locale" ]; then \
		echo "Error."; \
		echo "Run make in /usr/src/gemeinschaft-trunk/opt/gemeinschaft/locale"; \
		echo "(not /opt/gemeinschaft/locale)"; \
		exit 1; \
	fi

_pot: _check-cwd
	@echo "### Generating the .pot (.po template) ..."
	@if [ "x`svn status -v ${DIR}/pot/${GETTEXT_DOMAIN}.pot | head -c 1`" != "x " ]; then \
		echo "### pot/${GETTEXT_DOMAIN}.pot has local modifications." ;\
		exit 1 ;\
	fi
	cd ${GEMEINSCHAFT_TAG_ROOT} \
	&& find . -type f \
		! -path '*/.svn/*' \
		! -path '*/_*' \
		! -path '* *' \
		! -path './patches/*' \
		-print0 \
	| xargs -0 grep -I -l '__(' \
	| xgettext -f - -L 'PHP' -d '${GETTEXT_DOMAIN}' -o '${DIR}/pot/${GETTEXT_DOMAIN}.pot' --copyright-holder='${COPYRIGHT_HOLDER}' --keyword='__' --from-code='UTF-8' -n -s --strict

sort-po: _check-cwd
	@for LANGDIR in ${LANGS} ;\
	do \
		echo "### Sorting strings in $${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po ..." ;\
		msgcat -n -s --strict -o "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" ;\
		cat "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" | uniq > "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po.tmp" ;\
		mv "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po.tmp" "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" ;\
	done

update-po: _check-cwd _pot
	@for LANGDIR in ${LANGS} ;\
	do \
		echo "### Merging ${GETTEXT_DOMAIN}.pot into $${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po ..." ;\
		msgmerge -N -U --add-location -s --strict "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" "pot/${GETTEXT_DOMAIN}.pot" ;\
		echo "### Removing duplicate empty lines in $${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po ..." ;\
		cat "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" | uniq > "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po.uniq" ;\
		mv "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po.uniq" "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" ;\
	done

gen-mo: _check-cwd
	@for LANGDIR in ${LANGS} ;\
	do \
		echo "### Generating $${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.mo ..." ;\
		msgfmt -c --strict --statistics -o "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.mo" "$${LANGDIR}/LC_MESSAGES/${GETTEXT_DOMAIN}.po" ;\
	done

gen-php: _check-cwd
	@echo "### Generating the .php files ..."
	./po-to-php.php

all: gen-mo gen-php


.PHONY: help _check-cwd _pot update-po sort-po gen-mo gen-php all

