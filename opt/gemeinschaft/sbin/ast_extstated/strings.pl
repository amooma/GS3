
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
no  strict 'refs';
require 5.6.0;


sub ltrim($)
{
	my $str = shift;
	$str =~ s/^\s+//;
	return $str;
}
sub rtrim($)
{
	my $str = shift;
	$str =~ s/\s+$//;
	return $str;
}
sub trim($)
{
	my $str = shift;
	$str =~ s/^\s+//;
	$str =~ s/\s+$//;
	return $str;
}

