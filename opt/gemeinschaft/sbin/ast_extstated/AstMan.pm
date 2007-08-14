package AstMan;

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
#no  strict 'refs';
require 5.6.0;
use Socket qw(:DEFAULT);
use POSIX ();
use Fcntl ();
use lib qw(/opt/gemeinschaft/sbin/ast_extstated); # set @INC
use Trim;
use Time::HiRes qw(usleep);
#use Thread qw(yield);
use bytes; no locale; no utf8;
#use Errno;  ####doku lesen
use integer; #????????????????
use warnings;
use diagnostics;
#use re 'debug';
use Config;
#$ENV{PERL_SIGNALS} = 'safe';

use constant {  # "\n" is platform dependant!
	CR   => "\x0D",
	LF   => "\x0A",
	CRLF => "\x0D\x0A"
};

# import threads if possible:
if ($Config{'usethreads'}) {
	require threads; import threads ();
	require threads::shared; import threads::shared ();
}

sub new
{
	my $prototype = $_[0];
	my $class = ref($prototype) || $prototype;
	my $self = {};
	
	$self->{'host'} = $_[1] || '127.0.0.1';
	$self->{'user'} = $_[2] || 'admin';
	$self->{'pass'} = $_[3] || 'secret';
	$self->{'port'} = $_[4] ||  5038;
	
	$self->{'proto'} = (getprotobyname('tcp'))[2];
	$self->{'buf'} = '';
	
	$self->{'debug'}     = 0;
	$self->{'connected'} = 0;
	$self->{'expectinit'}= 0;
	$self->{'loggedin'}  = 0;
	$self->{'failnum'}   = 0;
	$self->{'retrytimeout'} = 10;  # try to connect every ... seconds
	$self->{'idlesince'} = time();
	$self->{'lastpong'}  = time();
	
	$self->{'cb_auth'}        = \&_handle_auth;
	$self->{'cb_auth_failed'} = \&_handle_auth_failed;
	$self->{'cb_no_pong'}     = \&_handle_no_pong;
	$self->{'cb_shutdown'}    = \&_handle_shutdown;
	$self->{'cb_loggedin'}    = undef;
	$self->{'cb_response'}    = undef;
	$self->{'cb_event'}       = undef;
	$self->{'filter_events'}  = undef;
	
	#$self->{'has_tty'} = (-t STDIN && -t STDOUT);
	
	select(STDOUT); $| = 1;
	
	bless ($self, $class);
	
	#$SIG{'PIPE'} = \&handle_sigpipe;
	$SIG{'PIPE'} = 'IGNORE';
	
	return $self;
}

sub run
{
	#my $self = shift;
	my($self) = @_;
	$self->{'idlesince'} = time();
	$self->{'lastpong'}  = time();
		
	while (1) {
		$self->run_one_timeslice();
	}
}

sub run_one_timeslice
{
	#my $self = shift;
	my($self) = @_;
	
	# if not connected, connect
	#
	if (! $self->{'connected'}) {
		my $ret = $self->_connect();
		if ($ret) {
			$self->{'idlesince'}  = time();
			$self->{'lastpong'}   = time();
			$self->{'expectinit'} = 1;
		} else {
			my $sleep = $self->_failed_get_sleep();
			print STDERR "Could not connect to $self->{'host'} - $! - sleep $sleep\n";
			sleep($sleep);
		}
		return;
	}
	
	# init connection?
	#
	if ($self->{'expectinit'}) {
		&usleep(200000);  # 0.2 s
		my $pkt;
		my $buf;
		my $bytes_read;
		while (1) {
			&usleep(200000);  # 0.2 s
			$bytes_read = sysread($self->{'socket'}, $buf, 100);
			if (defined($bytes_read) && $bytes_read > 0) {
				$pkt .= $buf;
				redo;
			}
			if ($pkt =~ /^Asterisk.*\n/io) {
				$self->{'expectinit'} = 0;
				if ($self->{'debug'}) {
					print "PKT: $pkt\n";
					print "-> LOG IN ...\n";
				}
				undef $pkt;
				undef $buf;
				$self->{'cb_auth'}( \$self );
				last;
			} else {
				print STDERR "We expected \"Asterisk Call Manager/1.0\" but got:\n$pkt\n";
				undef $pkt;
				undef $buf;
				$self->_reset_conn();
				sleep($self->{'retrytimeout'});
			}
		}
		return;
	}
	
	# ping pong
	#
	#print "    PING: ", (time() - $self->{'idlesince'}), "  PONG: ", (time() - $self->{'lastpong'}), "\n";
	if (time() - $self->{'idlesince'} > 9) {
		$self->_ping();
		$self->{'idlesince'} = time();
	}
	if (time() - $self->{'lastpong'} > 12) {
		print "-> HANDLE NO PONG ...\n" if $self->{'debug'};
		$self->{'cb_no_pong'}( \$self );
		$self->{'lastpong'} = time();
	}
	
	
	# read data
	#
	my $line;
	my $socket = $self->{'socket'};
	
	#do {
	#	if (eof($socket)) {
	#		&usleep(100000);  # 0.1 s
	#	}
	#	$line = <$socket>;
	#	$self->{'buf'} .= $line;
	#} until ($line eq "");
	
	my $buf;
	my $bytes_read;
	my $has_more_data = 1;
	while (1) {
		$bytes_read = sysread($socket, $buf, 1000);
		if (defined($bytes_read)) {
			if ($bytes_read == 0) {
				# Remote socket closed connection ???
				# close socket
				#print ".";
				&usleep(100000);  # 0.1 s
				last;
			} else {
				$has_more_data = 1;
				$self->{'buf'} .= $buf;
			}
		} else {
			$has_more_data = 0;
			last;
		}
	}
	
	# do we have a complete packet in the buffer?
	#
	#print "BUF:\n$buf\n";
	$self->{'buf'} =~ m/^(.*?)\r\n\r\n/so;
	my $pkt = $1 || '';
	my $len = length($pkt);
	&Trim::rtrim( $pkt );
	if ($pkt eq '') {
		&usleep(100000);  # 0.1 s
		return;
	}
	$self->{'buf'} = &Trim::ltrim(substr($self->{'buf'}, $len));
	print "PKT:\n$pkt\n" if $self->{'debug'};
	
	# handle packet
	#
	$self->{'idlesince'} = time();
	$self->{'lastpong'}  = time();  # count any packet as a pong
	
	if ($pkt =~ m/^Event:\s*([^\r]+)/io) {
		my $eventname = lc($1||'');
		if ($eventname eq 'shutdown') {
			# we better disconnect to prevent a broken pipe
			print "-> HANDLE CLOSE ...\n" if $self->{'debug'};
			$self->{'cb_shutdown'}( \$self );
		}
		print "-> HANDLE EVENT ...\n" if $self->{'debug'};
		#print "GOT AN EVENT: $eventname\n";
		if (defined($self->{'cb_event'})) {
			if (defined($self->{'filter_events'})
			&& ! exists $self->{'filter_events'}{$eventname}) {
				# nobody is interested in this event
				return;
			}
			$self->{'cb_event'}( \$self, \$pkt, \$eventname );
		}
	}
	elsif ($pkt =~ m/^Response:\s*([^\r]+)/io) {
		my $respname = lc($1||'');
		$pkt =~ m/^Message:\s*([^\r]+)/mi;
		my $message  = lc($1||'');
		if ($respname eq 'pong') {
			print "-> GOT PONG\n" if $self->{'debug'};
			$self->{'lastpong'} = time();
		}
		elsif ($message eq 'authentication failed') {
			print "-> HANDLE AUTH FAILED ...\n" if $self->{'debug'};
			$self->{'cb_auth_failed'}( \$self );
		}
		elsif ($message eq 'authentication accepted') {
			print "-> HANDLE AUTH ACCEPTED ...\n" if $self->{'debug'};
			$self->{'loggedin'} = 1;
			print "Logged in to $self->{'host'}\n";
			if (defined($self->{'cb_loggedin'})) {
				$self->{'cb_loggedin'}( \$self );
			}
		}
		else {
			print "-> HANDLE RESPONSE ...\n" if $self->{'debug'};
			if (defined($self->{'cb_response'})) {
				$self->{'cb_response'}( \$self, \$pkt, \$respname, \$message );
			}
		}
	}
	#elsif ($self->{'buf'} =~ /^Asterisk/i) {
	#	print "-> LOG IN ...\n" if $self->{'debug'};
	#	$self->{'cb_auth'}( \$self );
	#}
	else {
		print STDERR "Got bad packet:\n$pkt\nWe were expecting \"Event\" or \"Response\".\n";
	}
	
	#$self->{'buf'} = '';
	
	# sleep a bit
	#
	if (! $has_more_data) {
		&usleep(10000);  # 0.01 s
	}
}

#sub _fracsleep
#{
#	#my $self = shift;
#	my($self, $time) = @_;
#	select(undef, undef, undef, $time||0);
#}

sub _failed_get_sleep
{
	#my $self = shift;
	my($self) = @_;
	
	my $sleep = $self->{'retrytimeout'};
	if ($self->{'failnum'} < 10) {
		$self->{'failnum'}++;
		if ($self->{'failnum'} > 3) {
			$sleep *= 3;
		}
	} else {
		$sleep *= 9;
	}
	return $sleep;
}

sub _ping
{
	#my $self = shift;
	my($self) = @_;
	#print "SELF: $self\n";
	
	print "Ping $self->{'host'} ...\n" if $self->{'debug'};
	
	return $self->_send(
		'Action: Ping' .CRLF.
		CRLF);
}

sub _handle_no_pong
{
	#my $self = ${(shift)};
	my($self) = ${$_[0]};
	
	my $sleep = $self->{'retrytimeout'};
	print STDERR "No Pong in ", (time()-$self->{'lastpong'}), " secs for $self->{'host'} - sleep $sleep\n";
	
	$self->_reset_conn();
	sleep($sleep);
}

sub _handle_auth
{
	#my $self = ${(shift)};
	my($self) = ${$_[0]};
	#print "SELF: $self\n";
	
	print "Logging in to $self->{'host'} ...\n";
	
	$self->_send(
		'Action: Login' .CRLF.
		'Username: '. $self->{'user'} .CRLF.
		'Secret: '. $self->{'pass'} .CRLF.
		'Events: on' .CRLF.
		CRLF);
}

sub _handle_auth_failed
{
	#my $self = ${(shift)};
	my($self) = ${$_[0]};
	#print "SELF: $self\n";
	
	my $sleep = 300;
	print STDERR "Auth failed for $self->{'host'} - sleep $sleep\n";
	
	$self->_reset_conn();
	sleep($sleep);
}

sub _reset_conn
{
	#my $self = shift;
	my($self) = @_;
	
	close($self->{'socket'});
	$self->{'loggedin'}   = 0;
	$self->{'connected'}  = 0;
	$self->{'expectinit'} = 0;
	$self->{'failnum'}    = 0;
	$self->{'idlesince'}  = time();
	$self->{'lastpong'}   = time();
	$self->{'buf'}        = '';
}

#sub _rtrim()
#{
#	my $self = shift;
#	my $str = shift;
#	$str =~ s/\s+$//;
#	return $str;
#}
#
#sub _ltrim()
#{
#	my $self = shift;
#	my $str = shift;
#	$str =~ s/^\s+//;
#	return $str;
#}

sub _connect
{
	#my $self = shift;
	my($self) = @_;
	#print "SELF: $self\n";
	
	$self->{'iaddr'} = Socket::inet_aton( $self->{'host'} );
	if (! defined($self->{'iaddr'})) {
		print STDERR "Could not resolve hostname \"$self->{'host'}\"\n";
		return 0;
	}
	#print "IADDR: ", $self->{'iaddr'}, "\n";
	#print "AADDR: ", Socket::inet_ntoa($self->{'iaddr'}), "\n";
	$self->{'paddr'} = Socket::sockaddr_in( $self->{'port'}, $self->{'iaddr'} );
	#print "PADDR: ", $self->{'paddr'}, "\n";
	
	# create socket
	#
	my $ret = socket(my $socket, Socket::PF_INET, Socket::SOCK_STREAM, $self->{'proto'});
	if (! $ret) {
		print STDERR "Could not create socket for $self->{'host'} - $!\n";
		return 0;
	}
	#setsockopt($socket, SOL_SOCKET, Socket::SO_SNDTIMEO, pack('LL', 15, 0) );
	#$ret = getsockopt($socket, SOL_SOCKET, SO_RCVTIMEO);
	#print "SO_RCVTIMEO: >", unpack('LL', $ret), "<\n";
	$ret = setsockopt($socket, SOL_SOCKET, SO_RCVTIMEO, pack('LL', 5, 0) );
	#$ret = getsockopt($socket, SOL_SOCKET, SO_RCVTIMEO);
	#print "SO_RCVTIMEO: >", unpack('LL', $ret), "<\n";
	
	# connect
	#
	#print "CONNECT...\n";
	#$ret = connect($socket, $self->{'paddr'});
	$ret = 0;
	eval {
		local $SIG{'ALRM'} = sub { die('alarm clock'); };
		alarm 10;
		$ret = connect($socket, $self->{'paddr'});
		alarm 0;
	};
	if ($@ && $@ =~ /alarm clock/) {
		print STDERR "Could not connect to $self->{'host'} - Timeout\n";
		return 0;
	}
	if (! $ret) {
		print STDERR "Could not connect to $self->{'host'} - $!\n";
		return 0;
	}
	print "Connected to $self->{'host'}\n";
	
	binmode($socket);
	fcntl($socket, Fcntl::F_SETFL(), Fcntl::O_NONBLOCK());  # make non-blocking
	select($socket); $| = 1; select(STDOUT);
	$self->{'socket'} = $socket;
	$self->{'connected'}  = 1;
	$self->{'loggedin'}   = 0;
	$self->{'expectinit'} = 0;
	$self->{'failnum'}    = 0;
	$self->{'idlesince'}  = time();
	$self->{'lastpong'}   = time();
	$self->{'buf'}        = '';
	return 1;
}

sub _send
{
	#my $self = shift;
	#my $data = shift;
	my($self, $data) = @_;
	
	if ($self->{'connected'}==0) {return 0;}
	
	#my $socket = $self->{'socket'};
	#my $ret = print({$self->{'socket'}} $data);
	#if (! $ret) {
	#	if ($! != POSIX::EPIPE()) {
	#		# this is not just a broken pipe
	#		print STDERR "I/O error: $!\n";
	#	}
	#	my $sleep = 5;
	#	print STDERR "Connection to $self->{'host'} dropped - sleep $sleep\n";
	#	$self->_reset_conn();
	#	sleep($sleep);
	#	return 0;
	#}
	my $ret = syswrite($self->{'socket'}, $data);
	if (defined $ret && $ret > 0) {  # some data written
		#substr($data, 0, $ret) = '';
	} elsif ($! == POSIX::EAGAIN() || $! == POSIX::EWOULDBLOCK()) {
		# please try again later
		return 0;
	} else {
		my $sleep = 5;
		print STDERR "Failed to write data to $self->{'host'} - $! - sleep $sleep\n";
		$self->_reset_conn();
		sleep($sleep);
		return 0;
	}
	return 1;
}

sub send
{
	#my $self = shift;
	#my $data = shift;
	my($self, $data) = @_;
	
	if ($self->{'loggedin'}==0) {return 0;}
	
	return $self->_send($data);
}

sub request_extstate
{
	#my $self = shift;
	#my $ext = shift;
	#my $context = shift || 'default';
	my($self, $ext, $context) = @_;
	
	return $self->send(
		'Action: ExtensionState' .CRLF.
		'Exten: '. $ext .CRLF.
		'Context: '. ($context||'default') .CRLF.
		CRLF);
}

sub request_channels
{
	my($self, $ext, $context) = @_;
	
	return $self->send(
		'Action: Status' .CRLF.
		CRLF);
}

sub request_hints
{
	my($self, $ext, $context) = @_;
	
	return $self->send(
		'Action: Command' .CRLF.
		'Command: core show hints' .CRLF.
		'ActionID: hints' .CRLF.
		CRLF);
}

sub _handle_shutdown
{
	#my $self = ${(shift)};
	my($self) = ${$_[0]};
	
	my $sleep = 5;
	print STDERR "$self->{'host'} is shutting down - sleep $sleep\n";
	$self->_reset_conn();
	sleep($sleep);
}

sub debug {
	#my $self = shift;
	my($self, $debug) = @_;
	
	if (defined($debug)) {
		$self->{'debug'} = $debug ? 1 : 0;
	}
	if ($self->{'debug'}) {
		select(STDOUT); $| = 1;
	}
	return $self->{'debug'};
}

sub register_loggedin_cb
{
	#my $self = shift;
	#my $cb   = shift;
	my($self, $cb) = @_;
	
	if (defined($cb)) {
		$self->{'cb_loggedin'} = $cb;
		return 1;
	}
	return 0;
}

sub register_response_cb
{
	#my $self = shift;
	#my $cb   = shift;
	my($self, $cb) = @_;
	
	if (defined($cb)) {
		$self->{'cb_response'} = $cb;
		return 1;
	}
	return 0;
}

sub register_event_cb
{
	#my $self   = shift;
	#my $cb     = shift;
	##my @events = @${(shift)};
	#my $events = ${(shift)};
	my($self, $cb, $events) = @_;
	my @events = sort @$events;
	
	my %events_hash = ();
	foreach my $evtname (@events) {
		#print "want evt: $evtname\n";
		$events_hash{ lc($evtname) } = 1;
	}
	
	if (defined($cb)) {
		$self->{'cb_event'}      = $cb;
		$self->{'filter_events'} = \%events_hash;
		return 1;
	}
	return 0;
}

sub DESTROY
{
	#my $self = shift;
	my($self) = @_;
	
	if ($self->{'connected'}) {
		close($self->{'socket'});
	}
}

#sub handle_sigpipe
#{
#	print "CONNECTION DROPPED\n";
#}


1; # so the require or use succeeds
