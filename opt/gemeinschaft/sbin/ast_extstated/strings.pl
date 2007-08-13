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

