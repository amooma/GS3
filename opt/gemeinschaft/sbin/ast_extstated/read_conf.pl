
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

use strict;
require 5.6.0;
use bytes; no locale; no utf8;
use warnings;
#use diagnostics;
require 'strings.pl';


sub read_conf()
{
	my $configfile = shift;
	
	my @asterisks = ();
	
	if (! -e $configfile) {
		my %asterisk = (
			'host'   => '127.0.0.1',
			'port'   => 5038,
			'user'   => 'extstated',
			'secret' => 'eSd58'
		);
		push(@asterisks, \%asterisk);
		return @asterisks;
	}
	
	my $ok = open(CONFIGFILE, $configfile);
	if (! $ok) {
		print STDERR "Failed to read config file \"$configfile\" - $!\n";
		exit 1;
	}
	my @config_data = <CONFIGFILE>;
	close(CONFIGFILE);
	
	my $section = "";
	
	foreach my $line (@config_data)
	{
		$line = trim($line);
		$line =~ s/\s*#.*//;
		if ($line eq "") {next;}
		
		if (substr($line,0,1) eq '[') {
			if ($line eq '[asterisks]') {$section = 'asterisks';}
			else {$section = "";}
			next;
		}
		if ($section eq 'asterisks') {
			my ($user, $secret, $host) = split(/:|@/, $line, 3);
			my %asterisk = (
				'host'   => $host,
				'port'   => 5038,
				'user'   => $user,
				'secret' => $secret
			);
			#print "$asterisk{'user'} : $asterisk{'secret'} @ $asterisk{'host'}\n";
			push(@asterisks, \%asterisk);
		}
	}
	return @asterisks;
}

