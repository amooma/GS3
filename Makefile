# Gemeinschaft
# $Revision$


# Installiert die fuer die GPBX (auf Debian 4) benoetigten
# Pakete und prueft, ob ide nicht als Pakete vorhandene
# Software installiert ist.

check-gpbx-dependencies:
	
	@echo '*************** Check if we are on Debian ***************'
	cat /etc/debian_version | grep '4\.'
	
	@echo '*************** Check if we have Zaptel ***************'
	test -d /proc/zaptel/
	
	@echo '*************** Check if we have Asterisk ***************'
	which asterisk
	`which asterisk` -V | grep '1\.4'
	
	@echo '*************** Check if we have Asterisk Addons ***************'
	test -f /usr/lib/asterisk/modules/cdr_addon_mysql.so
	
	@echo '*************** Check if we have Lame ***************'
	which lame
	
	@echo '*************** Remove colliding packages ***************'
	apt-get remove resolvconf asterisk-classic asterisk-bristuff
	
	@echo '*************** Install required packages ***************'
	apt-get install \
		coreutils \
		lsb-base \
		grep \
		findutils \
		sudo \
		expect \
		cron \
		logrotate \
		ntp \
		ntp-simple \
		ntpdate \
		apache2 \
		libapache2-mod-php5 \
		php5 \
		php5-cli \
		php5-ldap \
		php5-mysql \
		mysql-server \
		mysql-client \
		perl \
		perl-modules \
		libnet-daemon-perl \
		libnet-netmask-perl \
		libio-interface-perl \
		libio-socket-multicast-perl \
		bind9 \
		net-tools \
		hostname \
		wget \
		iputils-ping \
		iputils-arping \
		openssh-client \
		openssh-server \
		dhcp3-server \
		mpg123 \
		sox \
		mailx \
		sipsak \
		avahi-daemon \
		avahi-utils \
		ifupdown \
		nmap \
		curl
	@#apt-get install postfix

