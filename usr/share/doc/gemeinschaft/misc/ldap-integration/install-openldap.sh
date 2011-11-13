#!/bin/bash

if [ ! -e db.ldif ] ; then
	echo "db.ldif missing in current directory. Aborting."
	exit
fi

if [ ! -e base.ldif ] ; then
	echo "base.ldif missing in current directory. Aborting."
	exit
fi

# create temporary directory
tempdir=$(mktemp -d)

# install OpenLDAP
aptitude -y install slapd ldap-utils

# create random password
plain_password=$(slappasswd -g)
password=$(slappasswd -s ${plain_password})


if [ "${password}" = "" ] ; then
	echo "Something is wrong with the LDAP password. Aborting."
	exit
fi

# prepare schema
ldapadd -Y EXTERNAL -H ldapi:/// -f /etc/ldap/schema/cosine.ldif
ldapadd -Y EXTERNAL -H ldapi:/// -f /etc/ldap/schema/inetorgperson.ldif
ldapadd -Y EXTERNAL -H ldapi:/// -f /etc/ldap/schema/nis.ldif
ldapadd -Y EXTERNAL -H ldapi:/// -f /etc/ldap/schema/misc.ldif
ldapadd -Y EXTERNAL -H ldapi:/// -f /etc/ldap/schema/openldap.ldif

# replace password placeholders with generated password hash
echo "$(eval "echo \"$(cat db.ldif)\"")" > ${tempdir}/db.ldif
echo "$(eval "echo \"$(cat base.ldif)\"")" > ${tempdir}/base.ldif

# create directory root, administrator and phonebook OUs
ldapadd -Y EXTERNAL -H ldapi:/// -f ${tempdir}/db.ldif
ldapadd -x -D cn=admin,dc=gemeinschaft,dc=local -w ${plain_password} -f ${tempdir}/base.ldif

rm -fr ${tempdir}

echo ""
echo ""
echo "=========================================================================="
echo ""
echo "Authentication information for this OpenLDAP instance"
echo ""
echo "DN:       cn=admin,dc=gemeinschaft,dc=local"
echo "Password: ${plain_password}"
echo ""
echo "=========================================================================="
echo ""
echo ""

echo "OpenLDAP setup finished."
