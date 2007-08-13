
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
