#!/bin/sh

# (c) Philipp Kempgen
# GNU/GPL

if [ -z $1 ]; then
	echo "Arg. 1 must be the file/folder or URL"
	exit 1
fi
ARG_FILE=$1

if [ -z $2 ]; then
	echo "Arg. 2 must be the revision"
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


for (( REV_TO = $ARG_REV_FROM; $REV_TO <= $ARG_REV_TO; REV_TO++ ))
do
	echo ""
	echo "########################################################################"
	
	REV_FROM=$(( $REV_TO - 1 ))
	svn log -r "${REV_TO}" "${ARG_FILE}"
	echo ""
	svn diff --notice-ancestry -r "${REV_FROM}:${REV_TO}" "${ARG_FILE}"
	
	echo ""
	echo ""
done

