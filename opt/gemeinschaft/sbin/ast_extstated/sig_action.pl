use strict;
require 5.6.0;
use POSIX ();
use warnings;
use diagnostics;

sub sigaction_set
{
	my $signal    = $_[0];
	my $action    = $_[1];
	my $immediate = $_[2];
	our %SIG;
	
	if (! $immediate) {
		$SIG{ $signal } = $action;
	} else {
		# see man sigaction
		my $sigset = POSIX::SigSet->new();
		my $action = POSIX::SigAction->new($action, $sigset, &POSIX::SA_NODEFER);
		$action->safe(0);
		$signal = 'SIG'.uc($signal);
		POSIX::sigaction(POSIX->$signal(), $action);
		#	or die "Error setting SIG handler ($!)\n";
	}
}


1; # so the require or use succeeds
