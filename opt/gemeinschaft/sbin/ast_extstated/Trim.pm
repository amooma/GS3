package Trim;

use strict;
use warnings;

use Exporter;

our @ISA = qw( Exporter );
our @EXPORT = qw( rtrim ltrim trim );


sub rtrim {
	@_ = @_ ? @_ : $_ if defined wantarray;
	for (@_ ? @_ : $_) { s/\s+$//; }
	return wantarray ? @_ : "@_";
}

sub ltrim {
	@_ = @_ ? @_ : $_ if defined wantarray;
	for (@_ ? @_ : $_) { s/^\s+//o; }
	return wantarray ? @_ : "@_";
}

sub trim {
	@_ = @_ ? @_ : $_ if defined wantarray;
	for (@_ ? @_ : $_) { s/^\s+//o; s/\s+$//o; }
	return wantarray ? @_ : "@_";
}

1;
