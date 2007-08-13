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

