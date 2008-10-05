#!/bin/sh
#
# Prints SVN log and diff incrementally between two specific
# revisions.
#
# (c) Philipp Kempgen
# GNU/GPL

USAGE="Usage:  $0 <file|dir|URL> <revision> [<revision-to>]\n"

if [ -z $1 ]; then
	echo -e "$USAGE" >&2
	exit 1
fi
ARG_FILE=$1

if [ -z $2 ]; then
	echo -e "$USAGE" >&2
	exit 1
fi
ARG_REV=$2

if [ ! -z $3 ]; then
	ARG_REV_FROM=$ARG_REV
	ARG_REV_TO=$3
else
	ARG_REV_FROM=$ARG_REV
	ARG_REV_TO=$ARG_REV
fi

SVN=`which svn`
if [ -z "$SVN" ]; then
	echo "svn command not found." >&2
	exit 1
fi

for (( REV_TO = $ARG_REV_FROM; $REV_TO <= $ARG_REV_TO; REV_TO++ ))
do
	echo ""
	echo "########################################################################"
	
	REV_FROM=$(( $REV_TO - 1 ))
	$SVN log -r "${REV_TO}" "${ARG_FILE}"
	[ "x$?" != "x0" ] && exit $?
	echo ""
	$SVN diff --notice-ancestry -r "${REV_FROM}:${REV_TO}" "${ARG_FILE}"
	[ "x$?" != "x0" ] && exit $?
	
	echo ""
	echo ""
done

