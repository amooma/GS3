#!/bin/bash
# (c) 2009-2010 AMOOMA GmbH - http://www.amooma.de
###################################################################
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
# MA 02110-1301, USA.
###################################################################


GEMEINSCHAFT_VERS="${GEMEINSCHAFT_VERS:-3.2_ubuntu_24.04_alpha}"

#GEMEINSCHAFT_TGZ_URL_DIR="https://github.com/amooma/GS3/tarball"
GEMEINSCHAFT_CLONE_URL_DIR="${GEMEINSCHAFT_CLONE_URL_DIR:-https://github.com/amooma/GS3.git}"

GEMEINSCHAFT_SIEMENS_VERS="trunk-r00358"
GEMEINSCHAFT_SIEMENS_TGZ_IN_TGZ_DIR="misc/provisioning/siemens"
# File: ${GEMEINSCHAFT_SIEMENS_TGZ_IN_TGZ_DIR}/gemeinschaft-siemens-${GEMEINSCHAFT_SIEMENS_VERS}.tgz

GEMEINSCHAFT_SOUNDS_DE_WAV_VERS="current"
GEMEINSCHAFT_SOUNDS_DE_WAV_TGZ_IN_TGZ_DIR="misc/voiceprompts"
# File: ${GEMEINSCHAFT_SOUNDS_DE_WAV_TGZ_IN_TGZ_DIR}/gemeinschaft-sounds-de-wav-${GEMEINSCHAFT_SOUNDS_DE_WAV_VERS}.tar.gz

#ASTERISK_SOUNDS_DE_ALAW_VERS="current"
ASTERISK_SOUNDS_DE_ALAW_TGZ_IN_TGZ_DIR="misc/voiceprompts"
# File: ${ASTERISK_SOUNDS_DE_ALAW_TGZ_IN_TGZ_DIR}/asterisk-core-sounds-de-alaw.tar.gz

# language
L2=`echo $LANG | head -c 2 | tr 'A-Z' 'a-z'`
if [ -z $L2 ]; then L2='xx'; fi


err()
{
	ERRMSG="$*"
	echo '' >&2
	echo '*****************************************************************' >&2
	echo '' >&2
	if [ "$L2" == "de" ]; then
		echo '  FEHLER!' >&2
	else
		echo '  ERROR!' >&2
	fi
	if [ ! -z "$ERRMSG" ]; then echo -e "$ERRMSG" >&2 ; fi
	echo '' >&2
	echo '*****************************************************************' >&2
	echo '' >&2
	exit 1
}

trap "(echo ''; echo '***** ABORTED!') >&2; exit 130" INT TERM QUIT HUP
trap "err; exit 1" ERR


# check system
#
. /etc/os-release 2>>/dev/null || true
if [ "x${ID}" != "xubuntu" ] || [ "x${VERSION_ID}" != "x26.04" ]; then
	if [ "$L2" == "de" ]; then
		err "  Ihr System ist nicht Ubuntu 26.04."
	else
		err "  Your system is not Ubuntu 26.04."
	fi
fi
if [ "`id -un`" != "root" ]; then
	if [ "$L2" == "de" ]; then
		err "  Dieses Skript muss als Benutzer \"root\" ausgeführt werden."
	else
		err "  This script must be run as user \"root\"."
	fi
fi


# set PATH
#
export PATH="/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin:${PATH}"



if ( which asterisk 1>>/dev/null 2>>/dev/null ); then
	if [ "$L2" == "de" ]; then
		err "  Auf diesem System ist bereits eine andere, möglicherweise\n" \
			"  nicht kompatible Version von Asterisk installiert."
	else
		err "  This system already has a version of Asterisk which might\n" \
			"  not be compatible."
	fi
fi


# setup basic stuff
#
[ -t 1 ] && clear || true
echo ""
echo "***         Now we start to install and setup stuff we need for"
echo "***         Gemeinschaft. Better get yourself a cup of coffee."
cat <<\HEREDOC

                   )
                  (
                      )
              ,.----------.
             ((|          |
            .--\          /--.
           '._  '========'  _.'
              `""""""""""""`

HEREDOC
#sleep 1

#echo "***"
#echo "***  Setting up basic stuff ..."
#echo "***"
export DEBIAN_FRONTEND=noninteractive
APT_INSTALL="apt-get -y install"


# very cheap hack to wait for the DHCP-client
#
COUNTER=0
while [  $COUNTER -lt 5 ]; do
    echo -n "."
    sleep 1
    let COUNTER=COUNTER+1 
done
echo ""
#make local directories
LOCAL_DIRS="vm-rec sys-rec sounds htdocs/prov/ringtones/"
LOCAL_PATH="/opt/gemeinschaft-local"
for i in $LOCAL_DIRS;
		do
			echo $LOCAL_PATH/$i
			test -d $LOCAL_PATH/$i || mkdir -p $LOCAL_PATH/$i;
		done



# update package lists
#
echo ""
echo "***"
echo "***  Updating package lists ..."
echo "***"
apt-get update
${APT_INSTALL} software-properties-common
add-apt-repository -y universe
apt-get update


# install and configure local nameserver
#
echo ""
echo "***"
echo "***  Installing local caching nameserver ..."
echo "***"

${APT_INSTALL} dnsutils wget ca-certificates net-tools
# install dnsutils so we can use dig later
#aptitude clean


# wait for internet access
#
echo "Checking Internet access ..."
while ! ( wget -O - -T 30 --spider http://archive.ubuntu.com/ >>/dev/null ); do sleep 5; done
MY_MAC_ADDR=`LANG=C ifconfig | grep -oE '[0-9a-fA-F]{1,2}\:[0-9a-fA-F]{1,2}\:[0-9a-fA-F]{1,2}\:[0-9a-fA-F]{1,2}\:[0-9a-fA-F]{1,2}\:[0-9a-fA-F]{1,2}' | head -n 1`


# install basic stuff
#
echo ""
echo "***"
echo "***  Installing basic stuff ..."
echo "***"
${APT_INSTALL} \
	coreutils grep findutils sudo wget curl cron openssl patch \
	expect dialog logrotate hostname net-tools iputils-ping netcat-openbsd \
	udev psmisc dnsutils iputils-arping pciutils bzip2 \
	vim less git

# now that we have vim, enable syntax highlighting by default:
if ( which vim 1>>/dev/null 2>>/dev/null ); then
	sed -i -r -e 's/^"(syntax) on/\1 on/' /etc/vim/vimrc || true
fi

# set EDITOR to "vim"
if ( which vim 1>>/dev/null 2>>/dev/null ); then
	echo "" >> /root/.bashrc || true
	echo "export EDITOR=\"vim\"" >> /root/.bashrc || true
	echo "" >> /root/.bashrc || true
	#if [ "x${SHELL}" = "x/bin/bash" ]; then
	#	source /root/.bashrc
	#fi
fi

# and add ls colors and some useful bash aliases:
cat <<\HEREDOC >> /root/.bashrc

export LS_OPTIONS='--color=auto'
eval "`dircolors`"
alias ls='ls $LS_OPTIONS'
alias l='ls $LS_OPTIONS -lF'
alias ll='ls $LS_OPTIONS -lFA'

HEREDOC
#if [ "x${SHELL}" = "x/bin/bash" ]; then
#	source /root/.bashrc
#fi

WGET="wget"
WGET_ARGS="-c -T 60"
DOWNLOAD="${WGET} ${WGET_ARGS}"


# set up lang enviroment
#
echo ""
echo "***"
echo "***  Setting up language environment ..."
echo "***"
if ( ! which locale-gen 1>>/dev/null 2>>/dev/null ); then
	${APT_INSTALL} locales
elif [ ! -e /usr/share/i18n/locales/. ]; then
	${APT_INSTALL} locales
elif [ ! -e /usr/share/locale/. ]; then
	${APT_INSTALL} locales
fi
if [ -e /etc/locale.gen ]; then
	grep -e "^de_DE\.UTF-8 UTF-8" /etc/locale.gen || echo "de_DE.UTF-8 UTF-8" >> /etc/locale.gen
	grep -e "^en_US\.UTF-8 UTF-8" /etc/locale.gen || echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen
fi
if ( type locale-gen 2>>/dev/null ); then
	locale-gen
else
	echo "WARNING: locale-gen not found!" >&2
fi


# time is kept by systemd-timesyncd
#
timedatectl set-ntp true || true


echo ""
echo "***"
echo "***  Installing Asterisk ..."
echo "***"
${APT_INSTALL} asterisk asterisk-modules asterisk-config unixodbc odbc-mariadb
# the package starts Asterisk with its stock configuration:
systemctl stop asterisk || true

chown --recursive asterisk:asterisk /var/lib/asterisk
chown --recursive asterisk:asterisk /var/log/asterisk
chown --recursive asterisk:asterisk /var/run/asterisk
chown --recursive asterisk:asterisk /var/spool/asterisk
chmod --recursive u=rwX,g=rX,o= /var/lib/asterisk
chmod --recursive u=rwX,g=rX,o= /var/log/asterisk
chmod --recursive u=rwX,g=rX,o= /var/run/asterisk
# /var/run is a tmpfs:
echo "d /run/asterisk 0750 asterisk asterisk -" > /etc/tmpfiles.d/asterisk.conf
chmod --recursive u=rwX,g=rX,o= /var/spool/asterisk
chown --recursive root:asterisk /etc/asterisk
chmod --recursive u=rwX,g=rX,o= /etc/asterisk

# create directory for call-files
#
mkdir -p /var/spool/asterisk/outgoing
chmod a+rwx /var/spool/asterisk/outgoing
chmod a+rwx /var/spool/asterisk/tmp

# sudo permissions for Asterisk
#
echo "asterisk  ALL=(ALL)  NOPASSWD: ALL" > /etc/sudoers.d/gemeinschaft-asterisk
chmod 0440 /etc/sudoers.d/gemeinschaft-asterisk


# install lame
#
${APT_INSTALL} lame


# install misc packages
#
echo ""
echo "***"
echo "***  Installing other packages ..."
echo "***"
${APT_INSTALL} \
	perl libnet-daemon-perl libnet-netmask-perl libio-interface-perl libio-socket-multicast-perl \
	sipsak \
	mysql-client mysql-server \
	apache2 \
	php8.5-cli libapache2-mod-php8.5 php8.5-mysql php8.5-ldap php8.5-mbstring \
	php8.5-curl php8.5-xml php8.5-soap \
	python3 python3-mysqldb \
	sox libsox-fmt-all mpg123
#aptitude clean


# install music on hold (MOH) for Asterisk
#
echo ""
echo "***"
echo "***  Installing music on hold (MOH) for Asterisk ..."
echo "***"

#cd /var/lib/asterisk/moh/
mkdir -p /usr/share/asterisk/moh
cd /usr/share/asterisk/moh/
for fmt in alaw; do
	#F=asterisk-moh-freeplay-${fmt}
	F=asterisk-moh-opsound-${fmt}-current
	${DOWNLOAD} https://downloads.asterisk.org/pub/telephony/sounds/${F}.tar.gz
	tar -xzf ${F}.tar.gz
	rm ${F}.tar.gz || true
done


# install Gemeinschaft
#
echo ""
echo "***"
echo "***  Installing Gemeinschaft ..."
echo "***"
cd /opt/

# Get tarball from GitHub {
#
git clone -b ${GEMEINSCHAFT_VERS} ${GEMEINSCHAFT_CLONE_URL_DIR} 
#${DOWNLOAD} "${GEMEINSCHAFT_TGZ_URL_DIR}/${GEMEINSCHAFT_VERS}" -O amooma-GS3.tar.gz
#tar -xvzf amooma-GS3*.tar.gz
#rm -f amooma-GS3*.tar.gz
mv GS3 \
   gemeinschaft-${GEMEINSCHAFT_VERS}
echo -n ${GEMEINSCHAFT_VERS} > gemeinschaft-${GEMEINSCHAFT_VERS}/etc/gemeinschaft/.gemeinschaft-version
mv "gemeinschaft-${GEMEINSCHAFT_VERS}" \
   "gemeinschaft-source-${GEMEINSCHAFT_VERS}"
ln -snf gemeinschaft-source-${GEMEINSCHAFT_VERS} gemeinschaft-source
#
# Get tarball from GitHub }

# main Gemeinschaft dir link
#
cd /opt/
rm -rf gemeinschaft 2>>/dev/null || true
ln -snf gemeinschaft-source/opt/gemeinschaft gemeinschaft


# fix MOH location for Debian:
#
#sed -i -r -e 's#^( *;? *directory *= *)/var/lib/asterisk(/moh)#\1/usr/share/asterisk\2#g' /etc/asterisk/musiconhold.conf


# install German voice prompts for Asterisk
#
echo ""
echo "***"
echo "***  Installing German voice prompts for Asterisk ..."
echo "***"

mkdir -p /usr/share/asterisk/sounds
cd /usr/share/asterisk/sounds/
[ -e de ] && rm -rf de || true

# Get tarball from within Gemeinschaft {
cp "/opt/gemeinschaft-source/${ASTERISK_SOUNDS_DE_ALAW_TGZ_IN_TGZ_DIR}/asterisk-core-sounds-de-alaw.tar.gz" ./
# Get tarball from within Gemeinschaft }

tar -xzf asterisk-core-sounds-de-alaw.tar.gz
rm -f asterisk-core-sounds-de-alaw.tar.gz



# voice prompts for Gemeinschaft
#
echo "Installing Voiceprompts for Gemeinschaft ..."
[ -e /opt/gemeinschaft-local/sounds ]
cd /opt/gemeinschaft-local/sounds
if [ -e de-DE ]; then
	rm -rf de-DE || true
fi
if [ -e de-DE-tts ]; then
	rm -rf de-DE-tts || true
fi

# Get tarball from within Gemeinschaft {
cp "/opt/gemeinschaft-source/${GEMEINSCHAFT_SOUNDS_DE_WAV_TGZ_IN_TGZ_DIR}/gemeinschaft-sounds-de-wav-${GEMEINSCHAFT_SOUNDS_DE_WAV_VERS}.tar.gz" ./
# Get tarball from within Gemeinschaft }

tar -xzf gemeinschaft-sounds-de-wav-${GEMEINSCHAFT_SOUNDS_DE_WAV_VERS}.tar.gz
rm -f gemeinschaft-sounds-de-wav-${GEMEINSCHAFT_SOUNDS_DE_WAV_VERS}.tar.gz || true
if [ -e de-DE ]; then
	mv de-DE de-DE-tts
fi
ln -s de-DE-tts de-DE
#if [ -e de-DE-tts ]; then
#	ln -snf de-DE-tts de-DE
#fi
#cd de-DE-tts
#/opt/gemeinschaft/sbin/sounds-wav-to-alaw.sh || true
# //FIXME: "sox: invalid option -- w"
# see man sox. -b 16 ? -b 8 ?
#rm *.wav || true
cd



# MySQL: add gemeinschaft user
#
echo ""
echo "***"
echo "***  Creating Gemeinschaft database ..."
echo "***"
# The queries were written for the permissive mode of old MySQL versions
# and we create triggers as a user without the SUPER privilege.
cat <<\HEREDOC > /etc/mysql/mysql.conf.d/gemeinschaft.cnf
[mysqld]
sql_mode = ""
log_bin_trust_function_creators = 1
HEREDOC
systemctl restart mysql

GEMEINSCHAFT_DB_PASS=`head -c 20 /dev/urandom | md5sum -b - | cut -d ' ' -f 1 | head -c 30`
for GS_DB_HOST in localhost 127.0.0.1 %; do
	mysql --batch -e "CREATE USER IF NOT EXISTS 'gemeinschaft'@'${GS_DB_HOST}' IDENTIFIED BY '${GEMEINSCHAFT_DB_PASS}'; GRANT ALL ON \`asterisk\`.* TO 'gemeinschaft'@'${GS_DB_HOST}';"
done
mysql --batch -e "FLUSH PRIVILEGES;"
mysql --batch --user=gemeinschaft --password="${GEMEINSCHAFT_DB_PASS}" -h 127.0.0.1 -e "SELECT 'test'" > /dev/null

# Gemeinschaft database
#
cd /opt/gemeinschaft-source/usr/share/doc/gemeinschaft/
sed -i -e 's/DEFINER *= *[^ ]*/DEFINER=CURRENT_USER()/g' asterisk.sql
mysql --batch < asterisk.sql
cd


# Apache configuration
#
echo ""
echo "***"
echo "***  Setting up Apache web server ..."
echo "***"
cd /etc/apache2/conf-available/
ln -snf /opt/gemeinschaft-source/etc/apache2/conf.d/gemeinschaft.conf gemeinschaft.conf
a2enconf gemeinschaft
cd /etc/apache2/sites-available/
ln -snf /opt/gemeinschaft-source/etc/apache2/sites-available/gemeinschaft gemeinschaft.conf
a2dissite 000-default
a2ensite gemeinschaft
a2enmod rewrite
a2enmod alias
a2enmod mime
a2enmod php8.5
a2enmod headers

systemctl restart apache2

# sudo permissions for Apache
#
echo "www-data  ALL=(ALL)  NOPASSWD: ALL" > /etc/sudoers.d/gemeinschaft-apache
chmod 0440 /etc/sudoers.d/gemeinschaft-apache


# configure Asterisk
#
echo ""
echo "***"
echo "***  Setting up Gemeinschaft ..."
echo "***"
cd /etc/
mv asterisk asterisk.DEBIAN
cp -r /opt/gemeinschaft-source/etc/asterisk ./
#ln -snf /opt/gemeinschaft-source/etc/asterisk

## Replace astdatadir "/var/lib/asterisk" by "/usr/share/asterisk"
## (the default on Debian):
#sed -i -r -e 's#^(astdatadir\s*).*#\1=> /usr/share/asterisk#' /etc/asterisk/asterisk.conf || true
#
## Replace astrundir "/var/run" by "/var/run/asterisk"
## (the default on Debian):
#sed -i -r -e 's#^(astrundir\s*).*#\1=> /var/run/asterisk#' /etc/asterisk/asterisk.conf || true


# change owner of /opt/gemeinschaft/etc/asterisk/* to asterisk
chown -h -R asterisk:asterisk /etc/asterisk

# add Apache user (www-data) to the Asterisk group (asterisk) so
# voicemails can be played via the web GUI:
adduser www-data asterisk
systemctl restart apache2


# configure Gemeinschaft
#
cd /etc/
#ln -snf /opt/gemeinschaft-source/etc/gemeinschaft
mkdir -p /etc/gemeinschaft
cd /etc/gemeinschaft
if [ ! -e gemeinschaft.php ]; then
	cp /opt/gemeinschaft-source/etc/gemeinschaft/gemeinschaft.php ./
fi
cp /opt/gemeinschaft-source/etc/gemeinschaft/.gemeinschaft-version ./  || true
mkdir -p /etc/gemeinschaft/asterisk
cd /etc/gemeinschaft/asterisk
cp -R /opt/gemeinschaft-source/etc/gemeinschaft/asterisk/* ./
if [ -e /etc/gemeinschaft/asterisk/manager.conf.d-available/phonesuite.conf ]; then
	AMI_PASS=`head -c 20 /dev/urandom | md5sum -b - | cut -d ' ' -f 1 | head -c 9`
	sed -i "s/^\(\s*secret\s*=\s*\)[^; \t]*\(.*\)/\1${AMI_PASS}\2/g" /etc/gemeinschaft/asterisk/manager.conf.d-available/phonesuite.conf
fi

# find IP address
#
MY_IP_ADDR=`LANG=C ifconfig | grep inet | grep -v 'inet6' | grep -v '127\.0\.0\.1' | head -n 1 | grep -oE '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}' | grep -v '^255' | head -n 1`
if [ "x$?" != "x0" ] || [ -z ${MY_IP_ADDR} ]; then
	echo "***** Failed to find your IP address." 2>&1
	MY_IP_ADDR="192.168.1.130"
fi
MY_NETMASK=`LANG=C ifconfig | grep inet | grep -v 'inet6' | grep -v '127\.0\.0\.1' | head -n 1 | grep -io 'mask.*' | grep -oE '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}' | grep '^255' | sort -n | head -n 1`
if [ "x$?" != "x0" ] || [ -z ${MY_NETMASK} ]; then
	echo "***** Failed to find your netmask." 2>&1
	MY_NETMASK="255.0.0.0"
fi

# configure gemeinschaft.php - IP address, DB password etc.
#
sed -i "s/\(^[\s#\/]*\$INSTALLATION_TYPE\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'single';/g" /etc/gemeinschaft/gemeinschaft.php


sed -i "s/\(^[\s#\/]*\$DB_MASTER_HOST\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'127.0.0.1';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i  "s/\(^[\s#\/]*\$DB_SLAVE_HOST\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'127.0.0.1';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i "s/\(^[\s#\/]*\$DB_MASTER_USER\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'gemeinschaft';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i  "s/\(^[\s#\/]*\$DB_SLAVE_USER\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'gemeinschaft';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i "s/\(^[\s#\/]*\$DB_MASTER_PWD\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'${GEMEINSCHAFT_DB_PASS}';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i  "s/\(^[\s#\/]*\$DB_SLAVE_PWD\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'${GEMEINSCHAFT_DB_PASS}';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i "s/\(^[\s#\/]*\$DB_MASTER_DB\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'asterisk';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i  "s/\(^[\s#\/]*\$DB_SLAVE_DB\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'asterisk';/g" /etc/gemeinschaft/gemeinschaft.php

sed -i "s/\(^[\s#\/]*\$PROV_HOST\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'${MY_IP_ADDR}';/g" /etc/gemeinschaft/gemeinschaft.php

sed -i "s/\(^[\s#\/]*\$CALL_INIT_FROM_NET\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'${MY_IP_ADDR}\/${MY_NETMASK}';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i "s/\(^[\s#\/]*\$MONITOR_FROM_NET\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'${MY_IP_ADDR}\/${MY_NETMASK}';/g" /etc/gemeinschaft/gemeinschaft.php

sed -i "s/\(^[\s#\/]*\$EMAIL_DELIVERY\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'sendmail';/g" /etc/gemeinschaft/gemeinschaft.php

sed -i "s/\(^[\s#\/]*\$LOG_GMT\s*=\s*\)[^a-z0-9]*\s*;/\1false;/g" /etc/gemeinschaft/gemeinschaft.php

sed -i "s/\(^[\s#\/]*\)\(\$FAX_ENABLED\s*=\s*\)\([A-Za-z0-9']\)*\s*;/\2true;/g" /etc/gemeinschaft/gemeinschaft.php
sed -i "s/\(^[\s#\/]*\)\(\$FAX_PREFIX\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\2'*96';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i "s/\(^[\s#\/]*\)\(\$FAX_TSI_PREFIX\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\2'';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i "s/\(^[\s#\/]*\)\(\$FAX_TSI\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\2 @\$CANONIZE_NATL_PREFIX.@\$CANONIZE_AREA_CODE.@\$CANONIZE_LOCAL_BRANCH.'0';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i "s/\(^[\s#\/]*\)\(\$FAX_HYLAFAX_HOST\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\2'127.0.0.1';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i "s/\(^[\s#\/]*\)\(\$FAX_HYLAFAX_PORT\s*=\s*\)\([0-9']\)*\s*;/\2 4559;/g" /etc/gemeinschaft/gemeinschaft.php

HFAXADM_PASS=`head -c 20 /dev/urandom | md5sum -b - | cut -d ' ' -f 1 | head -c 12`

sed -i "s/\(^[\s#\/]*\)\(\$FAX_HYLAFAX_ADMIN\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\2'hfaxadm';/g" /etc/gemeinschaft/gemeinschaft.php
sed -i "s/\(^[\s#\/]*\)\(\$FAX_HYLAFAX_PASS\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\2'${HFAXADM_PASS}';/g" /etc/gemeinschaft/gemeinschaft.php

#sed -i -e 's/^\s*[^#].*//g' /opt/gemeinschaft/etc/listen-to-ip
#( echo ; echo $MY_IP_ADDR ; echo ) >> /opt/gemeinschaft/etc/listen-to-ip

mysql --batch --user=gemeinschaft --password="${GEMEINSCHAFT_DB_PASS}" -e "USE \`asterisk\`; UPDATE \`hosts\` SET \`host\`='${MY_IP_ADDR}' WHERE \`id\`=1;" || true
mysql --batch --user=gemeinschaft --password="${GEMEINSCHAFT_DB_PASS}" -e "USE \`asterisk\`; UPDATE \`hosts\` SET \`host\`='${MY_IP_ADDR}';" || true


# gemeinschaft-siemens installation here because gemeinschaft has to be configured before generating the SSL cert


# install gemeinschaft-siemens addon
#
echo "Installing Siemens addon for Gemeinschaft (Openstage provisoning) ..."
cd /opt/

# Get tarball from within Gemeinschaft {
cp "/opt/gemeinschaft-source/${GEMEINSCHAFT_SIEMENS_TGZ_IN_TGZ_DIR}/gemeinschaft-siemens-${GEMEINSCHAFT_SIEMENS_VERS}.tgz" ./
# Get tarball from within Gemeinschaft }

tar -xzf gemeinschaft-siemens-${GEMEINSCHAFT_SIEMENS_VERS}.tgz
rm -f gemeinschaft-siemens-${GEMEINSCHAFT_SIEMENS_VERS}.tgz
mv gemeinschaft-siemens-${GEMEINSCHAFT_SIEMENS_VERS} gemeinschaft-siemens-source-${GEMEINSCHAFT_SIEMENS_VERS}
ln -snf gemeinschaft-siemens-source-${GEMEINSCHAFT_SIEMENS_VERS} gemeinschaft-siemens-source
cd /opt/
ln -snf /opt/gemeinschaft-siemens-source/opt/gemeinschaft-siemens gemeinschaft-siemens
cd


# configure gemeinschaft-siemens
#
${APT_INSTALL} openssl 
a2enmod rewrite 
a2enmod ssl

cd /etc/apache2/
[ -e ssl ] && rm -rf ssl || true
ln -snf /opt/gemeinschaft-siemens-source/doc/etc-apache2-ssl ssl
cd /etc/apache2/ssl/
# OpenSSL 3 rejects 1024 bit keys by default
sed -i -e 's/^\([[:space:]]*\)1024$/\12048/' gen-cert.sh
./gen-cert.sh >>/dev/null
chown root:root openstage-*.pem
chmod 640 openstage-*.pem
cd /etc/apache2/sites-available/
sed -e 's/^\([[:space:]]*\)SSLCipherSuite .*/\1SSLCipherSuite DEFAULT:@SECLEVEL=1/' \
    -e 's#^</VirtualHost>#\t<Directory /opt/gemeinschaft/htdocs/prov/siemens>\n\t\tRequire all granted\n\t</Directory>\n</VirtualHost>#' \
    /opt/gemeinschaft-siemens-source/doc/httpd-vhost.conf.example > gemeinschaft-siemens.conf
a2ensite gemeinschaft-siemens
systemctl restart apache2
cd


# documentation
#
cd /usr/share/doc
ln -snf /opt/gemeinschaft-source/usr/share/doc/gemeinschaft


# log dir
#
mkdir -p /var/log/gemeinschaft
chmod a+rwx /var/log/gemeinschaft


# logrotate rules
#
cd /etc/logrotate.d/
ln -snf /opt/gemeinschaft-source/etc/logrotate.d/asterisk
ln -snf /opt/gemeinschaft-source/etc/logrotate.d/gemeinschaft


# web dir
#
cd /var/www/
ln -snf /opt/gemeinschaft-source/var/www/gemeinschaft
ln -snf /opt/gemeinschaft-source/var/www/.htaccess


# misc
#
cd /var/lib/
ln -snf /opt/gemeinschaft-source/var/lib/gemeinschaft


# gs-sip-ua-config-responder fuer Snom
#
if [ -e /opt/gemeinschaft-source/etc/init.d/gs-sip-ua-config-responder ]; then
	cd /etc/init.d/
	ln -snf /opt/gemeinschaft-source/etc/init.d/gs-sip-ua-config-responder
	update-rc.d gs-sip-ua-config-responder defaults 92 8
	invoke-rc.d gs-sip-ua-config-responder start
fi


# Gemeinschaft/Asterisk extension state daemon
#
if [ -e /opt/gemeinschaft-source/etc/init.d/gs-extstated ]; then
	ln -snf /opt/gemeinschaft-source/etc/init.d/gs-extstated /etc/init.d/gs-extstated
	update-rc.d gs-extstated defaults 92 08
	invoke-rc.d gs-extstated start
fi


# cron jobs
#
cd /etc/cron.d/
ln -snf /opt/gemeinschaft-source/etc/cron.d/gs-cc-guardian || true
ln -snf /opt/gemeinschaft-source/etc/cron.d/gs-queuelog-to-db || true
ln -snf /opt/gemeinschaft-source/etc/cron.d/gs-queues-refresh || true
cd


# fix permissions
chown -h asterisk:asterisk /opt/gemeinschaft/vm-rec
chmod 0777 /opt/gemeinschaft/vm-rec
chmod 0777 /opt/gemeinschaft/sys-rec


# remove build environment 
#
echo ""
echo "***"
echo "***  Removing build environment  ..."
echo "***"
apt-get clean


# add /opt/gemeinschaft/scripts to PATH
#
echo "" >> /root/.bashrc || true
echo "export PATH=\"\$PATH:/opt/gemeinschaft/scripts\"" >> /root/.bashrc || true
echo "" >> /root/.bashrc || true
#if [ "x${SHELL}" = "x/bin/bash" ]; then
#	source /root/.bashrc
#fi


# motd
#
(
echo "***"
echo "***    _____                                              _____"
echo "***   (.---.)             GEMEINSCHAFT $(printf "% -7s" $GEMEINSCHAFT_VERS)           (.---.)"
echo "***    /:::\\ _.-----------------------------------------._/:::\\"
echo "***    -----                                              -----"
echo "***"
echo "***   Need help with Gemeinschaft? We have an excellent free mailinglist"
echo "***   and offer the best support and consulting money can buy. Have a"
echo "***   look at http://www.gemeinschaft.de for more information."
echo "***"
) > /etc/motd.static
[ -e /etc/motd ] && rm -rf /etc/motd || true
ln -s /etc/motd.static /etc/motd


# fax installation starts here
#
HF_CONF_SRC="/usr/share/doc/gemeinschaft/misc/fax-integration"


echo ""
echo "***"
echo "***  Installing IAXmodem ..."
echo "***"
${APT_INSTALL} iaxmodem

# iaxmodem config
#
cp "${HF_CONF_SRC}/ttyIAX0" /etc/iaxmodem/ttyIAX0
cp "${HF_CONF_SRC}/ttyIAX1" /etc/iaxmodem/ttyIAX1

# add iaxmodem entries to iax.conf
#
#cat "${HF_CONF_SRC}/iax.conf.template" >> /etc/asterisk/iax.conf

# start one faxgetty per modem (units are written after HylaFax is installed)


echo ""
echo "***"
echo "***  Installing HylaFax ..."
echo "***"
# hylafax-server (/usr/sbin/faxsetup) needs /usr/sbin/sendmail but
# does not depend on mail-transfer-agent
if ( ! which postfix 1>>/dev/null 2>>/dev/null ); then
	echo "postfix postfix/main_mailer_type select Internet Site" | debconf-set-selections
	echo "postfix postfix/mailname string `hostname -f`" | debconf-set-selections
	${APT_INSTALL} postfix
fi
${APT_INSTALL} hylafax-server

if [ ! -e /etc/hylafax/getty-link ]; then
	ln -sn /usr/sbin/faxgetty /etc/hylafax/getty-link
fi

# make tmp directory world accessible
chmod 777 /var/spool/hylafax/tmp/

# sudo permissions for FaxDispatch
#
echo "uucp ALL = NOPASSWD: /bin/chgrp" > /etc/sudoers.d/gemeinschaft-hylafax
chmod 0440 /etc/sudoers.d/gemeinschaft-hylafax

# run HylaFax config script
#
#/usr/sbin/faxsetup -nointeractive
# is run automatically by post-install script of hylafax-server

# fax modem config
#
# (/etc/hylafax is bind-mounted to /var/spool/hylafax/etc by the services)
cp "${HF_CONF_SRC}/config.ttyIAX0" /etc/hylafax/config.ttyIAX0
cp "${HF_CONF_SRC}/config.ttyIAX0" /etc/hylafax/config.ttyIAX1

# fax daemon config
#
cp "${HF_CONF_SRC}/hfaxd.conf" /etc/hylafax/hfaxd.conf

# fax dispatch config
#
cp "${HF_CONF_SRC}/FaxDispatch" /etc/hylafax/

# make hylafax run on boot
#
systemctl enable hylafax.service faxq.service hfaxd.service

# faxgetty services
#
for tty in ttyIAX0 ttyIAX1; do
	cat <<HEREDOC > /etc/systemd/system/faxgetty-${tty}.service
[Unit]
Description=HylaFAX faxgetty for ${tty}
After=iaxmodem.service

[Service]
BindPaths=/etc/hylafax:/var/spool/hylafax/etc
WorkingDirectory=/var/spool/hylafax
Restart=always
RestartSec=30
ExecStart=/usr/sbin/faxgetty ${tty}

[Install]
WantedBy=multi-user.target
HEREDOC
	systemctl enable faxgetty-${tty}.service
done


# delete example users, queues etc.
#
for user in "anna" "hans" "lisa" "peter"; do
	/opt/gemeinschaft/scripts/gs-user-del --user="${user}" || true
done
for queue in "5000"; do
	/opt/gemeinschaft/scripts/gs-queue-del --queue="${queue}" || true
done
mysql --batch --user=gemeinschaft --password="${GEMEINSCHAFT_DB_PASS}" -e \
	"USE \`asterisk\`; DELETE FROM \`phones\`;" 1>>/dev/null 2>>/dev/null || true


# Add sample admin and user
#

ADMIN_NAME="admin"
ADMIN_FNAME="System"
ADMIN_LNAME="Administrator"
ADMIN_EXTEN="9999"
PIN=$(( RANDOM % 10000 ))
ADMIN_PIN=`printf "%04d\n" "$PIN"`

USER_NAME="user"
USER_FNAME="Ordinary"
USER_LNAME="User"
USER_EXTEN="9998"
PIN=$(( RANDOM % 10000 ))
USER_PIN=`printf "%04d\n" "$PIN"`

#TODO: Loop on error!

# Add admin account
/opt/gemeinschaft/scripts/gs-user-add \
	--user="$ADMIN_NAME" \
	--ext="$ADMIN_EXTEN" \
	--pin="$ADMIN_PIN" \
	--firstname="$ADMIN_FNAME" \
	--lastname="$ADMIN_LNAME" \
	--language="de" \
	--email="" \
	--host=1 || true

# Add user account
/opt/gemeinschaft/scripts/gs-user-add \
	--user="$USER_NAME" \
	--ext="$USER_EXTEN" \
	--pin="$USER_PIN" \
	--firstname="$USER_FNAME" \
	--lastname="$USER_LNAME" \
	--language="de" \
	--email="" \
	--host=1 || true


# add admin to GUI_SUDO_ADMINS:
#sed -i "s/\(^[\s#\/]*\$GUI_SUDO_ADMINS\s*=\s*\)\([\"']\)[^\"']*[\"']\s*;/\1'${ADMIN_NAME}';/g" /etc/gemeinschaft/gemeinschaft.php
# add "admin" account in group system:
/opt/gemeinschaft/scripts/gs-group-member-add --group admins --member $ADMIN_NAME


# get SIP passwords:

ADMIN_SIPPW=$( mysql --user=gemeinschaft --password=${GEMEINSCHAFT_DB_PASS} -h localhost -D asterisk -A -B --raw -N -s -s -s -e "SELECT \`secret\` FROM \`ast_sipfriends\` WHERE \`name\`='$ADMIN_EXTEN'" | awk  '{print $1}' );
if [ -z $ADMIN_SIPPW ]; then ADMIN_SIPPW='x'; fi

USER_SIPPW=$( mysql --user=gemeinschaft --password=${GEMEINSCHAFT_DB_PASS} -h localhost -D asterisk -A -B --raw -N -s -s -s -e "SELECT \`secret\` FROM \`ast_sipfriends\` WHERE \`name\`='$USER_EXTEN'" | awk  '{print $1}' );
if [ -z $USER_SIPPW ]; then USER_SIPPW='x'; fi



# hardening
#
# Dieses Skript ist dazu bestimmt Gemeinschaft zu installieren.
# Das System muß vom Administrator der lokalen Infrastruktur
# entsprechend ggf. zusätzlich abgesichert werden.
# Ebenso muß der Administrator wie auf jedem System üblich die
# Mail-Zustellung korrekt konfigurieren.
# Das System ist nicht dazu bestimmt ohne weitere Absicherung im
# öffentlichen Internet betrieben zu werden.
# Trotzdem wollen wir hier ein Grund-Maß an Sicherheit bieten
# soweit die möglich ist.

# snort
#${APT_INSTALL} snort


# portsentry (detect port scans)
#${APT_INSTALL} portsentry

# Silver-Bullet 
cd /opt/
rm -rf silverbullet 2>>/dev/null || true
ln -snf gemeinschaft-source/opt/silverbullet silverbullet

mkdir -p /etc/silverbullet
cd /etc/silverbullet
if [ ! -e silverbullet.conf ]; then
	cp /opt/gemeinschaft-source/etc/silverbullet/silverbullet.conf ./
fi


if [ -e /opt/gemeinschaft-source/etc/init.d/silverbullet ]; then
	cd /etc/init.d/
	ln -snf /opt/gemeinschaft-source/etc/init.d/silverbullet
	update-rc.d silverbullet defaults 92 8
	invoke-rc.d silverbullet start
fi



echo ""
echo "***"
echo "***  Restarting services ..."
echo "***"

systemctl stop hfaxd.service faxq.service || true
/etc/init.d/iaxmodem stop || true

/etc/init.d/asterisk stop || true
/opt/gemeinschaft/sbin/gs-ast-dialplan-gen

# start services
#
invoke-rc.d iaxmodem start || true
systemctl start hylafax.service faxq.service hfaxd.service || true
systemctl start faxgetty-ttyIAX0.service faxgetty-ttyIAX1.service || true
invoke-rc.d asterisk start || true

# updating authentication file
#
/opt/gemeinschaft/sbin/gs-hylafax-auth-update || true



logger "Gemeinschaft ${GEMEINSCHAFT_VERS} has just been installed."


# warning
#
[ -t 1 ] && clear || true
echo "Security Warning"
echo "================"
echo ""
echo "Never ever run this system outside of a safe intranet "
echo "environment without doing some serious security auditing!"
echo "See /etc/gemeinschaft/gemeinschaft.php"
echo ""


# let's do some ASCII art
#
(
echo "**************************************************************************"
echo "***                 G E M E I N S C H A F T   ${GEMEINSCHAFT_VERS}"
echo "***"
echo "***   Use your admin account \"${ADMIN_NAME}\" and PIN \"${ADMIN_PIN}\" to log into the GUI at"
echo "***     http://${MY_IP_ADDR}/gemeinschaft/"
echo "***"
echo "***   Use these SIP accounts to setup your first two phones:"
echo "***"
echo "***                      (.---.)                 (.---.)"
echo "***                       /:::\\ _.--------------._/:::\\"
echo "***                       -----                   -----"
echo "***"
echo "***     SIP Username :    $(printf "% -5s" $ADMIN_EXTEN)                   $(printf "% -5s" $USER_EXTEN)"
echo "***     SIP Password :    $(printf "% -20s" $ADMIN_SIPPW)    $(printf "% -20s" $USER_SIPPW)"
echo "***     SIP Server   :    $(printf "% -15s" $MY_IP_ADDR)         $(printf "% -15s" $MY_IP_ADDR)"
echo "***"
echo "***   Find mailinglists and more info at"
echo "***     http://www.gemeinschaft.de"
echo "**************************************************************************"
) > /tmp/gemeinschaft-beispiel-user.txt

[ -t 1 ] && clear || true
cat /tmp/gemeinschaft-beispiel-user.txt

# Fixing permissions of cronjobs
chmod 0600 /opt/gemeinschaft-source/etc/cron.d/*

# make bash re-read .bashrc:
#
if [ -t 0 ] && [ "x${SHELL}" = "x/bin/bash" ]; then
	exec ${SHELL}
fi

cd
exit 0

