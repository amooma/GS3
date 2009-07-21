#####################################################################
#                            Gemeinschaft
# 
# $Revision$
# 
# Copyright 2008, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
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

package GSProxy;

use strict;
use warnings;
use 5.008;
use mod_perl 2.0;
use Apache2::Filter ();         # $f
use Apache2::RequestRec ();     # $f->$r
#use Apache2::RequestUtil ();    # $r->dir_config()
use Apache2::URI ();            # $r->construct_server()
use Apache2::Log ();            # $log->info(), $log->debug()
use APR::Table ();              # $f->$r->headers_out->unset
                                # dir_config->get() and headers_out->get()
use Apache2::Const -compile => qw(OK DECLINED);

(my $package = __PACKAGE__) =~ s°::°/°g;

sub handler
{
	my $f   = shift;            # our filter object
	my $r   = $f->r;            # our request object
	my $log = $r->server->log;
	
	my $context;
	
	if (! defined $f->ctx) {
		# these are things we only want to do once no matter how
		# many times our filter is invoked per request
		
		# we only process HTML documents
		unless ($r->content_type =~ m°^text/(html|css)°i
		||      $r->content_type =~ m°^application/(x-javascript|xhtml\+xml)°i
		){
			$log->debug("### $package: Removing handler for document ", $r->uri ," (content-type ". $r->content_type .")");
			$f->ctx(1);  # define ctx so we don't get here again
			return Apache2::Const::DECLINED;
		}
		$log->debug("### $package: Modifying document ", $r->uri ," (content-type ". $r->content_type .")..");
		
		## parse the configuration options
		#my $level = $r->dir_config->get('CleanLevel') || 1;
		#
		#my %options = map { $_ => 1 } $r->dir_config->get('CleanOption');
		#
		## store the configuration
		#$context = {
		#	level   => $level,
		#	options => \%options,
		#	extra   => undef
		#};
		
		my $contact_scheme       = '';
		my $contact_addr         = '';
		my $contact_addr_pattern = '';
		$r->uri =~ m°^/(https?)/([^/]+)°;
		if (defined $1 && defined $2) {
			$contact_scheme       = $1;
			$contact_addr         = $2;
			$contact_addr_pattern = $contact_addr;
			#$contact_addr_pattern =~ s°([./])°\\$1°g;
			$contact_addr_pattern =~ s°[.]°\\.°g;
			$log->debug("### $package: Contact: $contact_scheme://$contact_addr");
			#$log->debug("### $package: Contact pattern: $contact_addr_pattern");
		} else {
			$log->info("### $package: Bad contact");
			$f->ctx(1);  # define ctx so we don't get here again
			return Apache2::Const::DECLINED;
		}
		
		my $scheme = 'http';
		$r->construct_url('/') =~ m°^(https?):°;
		$scheme = $1 if defined $1;
		my $proxy_base = $scheme.'://'. $r->construct_server() .'/'.$contact_scheme.'/'.$contact_addr;
		$log->debug("### $package: Proxy base: $proxy_base");
		
		# store the configuration
		$context = {
			'contact_scheme'       => $contact_scheme,
			'contact_addr'         => $contact_addr,
			'contact_addr_pattern' => $contact_addr_pattern,
			'proxy_base'           => $proxy_base,
			'leftover'             => undef
		};
		
		
		# output filters that alter content are responsible for removing
		# the Content-Length header
		$r->headers_out->unset('Content-Length');
		
		#$r->headers_out->set('Server', $package);
		#$r->headers_in->unset('X-Forwarded-Host');
		#$r->headers_in->unset('X-Forwarded-Server');
		
		
		# Catch redirects, adjust Location header
		if ($r->status() == 301  # Moved permanently
		||  $r->status() == 302  # Found
		||  $r->status() == 303  # See other
		||  $r->status() == 305  # Use proxy
		||  $r->status() == 306  # (unused, was Switch proxy)
		||  $r->status() == 307  # Moved temporarily
		){
			my $status_orig = $r->status();
			my $status_line_orig = $r->status_line();
			
			my $location = $r->headers_out->get('Location');
			my $location_error = 1;
			my $location_external = 0;
			if (defined $location) {
				if ($location =~ m°^/°) {
					# invalid according to RFC 2616 but popular and easy for us
					$r->headers_out->set('Location', $proxy_base.$location);
					$log->debug("### $package: Fixed a broken redirect we got from $contact_addr");
					$location_error = 0;
				}
				if ($location =~ m°^https?://$contact_addr_pattern/?(.*)°) {
					# an "absoluteURI" according to RFC 2396
					$r->headers_out->set('Location', $proxy_base.'/'.$1);
					$log->debug("### $package: Fixed a redirect we got from $contact_addr ($location -> $proxy_base/$1)");
					$location_error = 0;
				}
				
				#if ($location =~ m°^https?://([^/]+)°) {
				#	my $location_addr = $1;
				#	if ($location_addr =~ m°([^@\:]+)(?:[\:]([0-9]+))?$°) {
				#		my $location_host_or_ip = $1;
				#		my $location_port       = (0+$2) || 0;
				#		$log->debug("### PORT: $location_port");
				#		
				#		$log->debug("### $location_addr");
				#		
				#		$log->debug("### $package: Location out: ". $r->headers_out->get('Location') );
				#		if (defined $r->hostname) {
				#			$log->debug("### $package: proxyreq?: ". $r->proxyreq .", hostname: ". $r->filename .", status: ". $r->status .", statusline: ". $r->status_line );
				#		}
				#	}
				#}
				
				if ($location =~ m°^[a-z]+:°) {
					$location_external = 1;
				}
			}
			
			if ($location_error) {
				$log->info("### $package: Server $contact_scheme://$contact_addr sent a bad redirect to ". ($location_external ? "external " : "") ."location $location");
				$r->headers_out->unset('Location');
				$r->status_line('502 Bad Gateway');
				$r->status(502);
				
				if ($r->content_type =~ m°^text/html°i
				||  $r->content_type =~ m°^application/xhtml\+xml°i
				){
					$r->content_type('text/html');
					$f->print(
						"<html>\n",
						"<head>\n",
						"	<title>Error 502</title>\n",
						"</head>\n",
						"<body>\n",
						"<h1 align=\"center\">Error 502</h1>\n",
						"<p align=\"center\">\n",
						"	Got broken redirect ($status_orig) from server &quot;$contact_addr&quot;.<br>\n",
						"	Location header in an unsupported format.<br>\n",
						"	". ($location_external ? "(external location)" : "") ."\n",
						"</p>\n",
						"</body>\n",
						"</html>"
					);
				}
			}
			
			$f->seen_eos(1);  # ignore the content in the bucket brigade
			$f->ctx(1);  # define ctx so we don't get here again
			return Apache2::Const::OK;
		}
		$f->ctx(1);  # define ctx so we don't get here again
	}
	
	# retrieve the filter context, which was set up on the first invocation
	$context ||= $f->ctx;
	
	if ($context == 1) {
		#$log->debug("### Huh? We should be removed.");
		$f->ctx(1);
		return Apache2::Const::DECLINED;
	}
	
	my $proxy_base           = $context->{'proxy_base'};
	my $contact_scheme       = $context->{'contact_scheme'};
	my $contact_addr         = $context->{'contact_addr'};
	my $contact_addr_pattern = $context->{'contact_addr_pattern'};
	
	# Read the input and filter the content
	while ($f->read(my $buf, 8000)) {
		# prepend any data leftover from the last buffer or invocation
		if (defined $context->{'leftover'}) {
			$buf = $context->{'leftover'} . $buf;
		}
		$context->{'leftover'} = undef;
		if (($context->{'leftover'}) = $buf =~ m/((?:[\r\n]|\A)[^\r\n]+)$/o) {
			$buf = substr($buf, 0, -length($context->{'leftover'}));
			#next if length($buf) == 0;
		}
		next if length($buf) < 3;
		
		# prefix URLs beginning with "/":
		$buf =~ s°\b((?:href|src|action)\s*=\s*["']?)/°$1$proxy_base/°igo;
		
		# fix absolute URLs to the same server:
		#$buf =~ s°\b((?:href|src|action)\s*=\s*["']?)https?://$contact_pattern°$1$proxy_base°igo;
		#$buf =~ s°\b((?:href|src|action)\s*=\s*["']?)/(?!$contact_pattern)°$1$proxy_base/°igo;
		
		# prefix meta refresh URLs beginning with "/":
		$buf =~ s°(;\s*url\s*=\s*)/°$1$proxy_base/°igo;
		
		# fix absolute meta refresh URLs to the same server:
		#$buf =~ s°(;\s*url\s*=\s*)https?://$contact_addr_pattern°$1$proxy_base°igo;
		
		# fix URLs in CSS:
		$buf =~ s°((?:^|\s|:)url\(\s*["']?)/°$1$proxy_base/°go;
		
		$f->print($buf);
	}
	
	if ($f->seen_eos) {
		# end of the data stream
		# print any leftover data
		$f->print($context->{'leftover'}) if defined $context->{'leftover'};
	} else {
		# there's more data to come
		# store the filter context, including any leftover data
		$f->ctx($context) if defined $context;
	}
	
	return Apache2::Const::OK;
}

1;
