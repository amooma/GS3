#!/usr/bin/perl
#
# HASTENIX - Hawhaw Adapter for aSTErisk aNd voIceXml
# Copyright (C) 2007 Norbert Huffschmid
# Last modified: 30.7.2007
#
# You can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the License, or (at your option)
# any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
# GNU General Public License for more details.
# http://www.gnu.org/copyleft/gpl.html
#
# For further information about HASTENIX and HAWHAW please visit:
# http://www.hawhaw.de/
# http://www.hawhaw.de/faq.htm#Hastenix0

# Entabbed and formatted properly by Philipp Kempgen

use strict;
use warnings;

use 5.008; # perl 5.8 required for stable threading
           # thread support must be complied into perl!

use LWP::UserAgent;           # needed to get VoiceXML docs from remote host
use HTTP::Request::Common;    # needed to post recorded speech to remote host
use URI::Escape;              # needed to urlencode url's
use XML::Parser;              # needed to parse VoiceXML
use threads;                  # needed to create/play tts soundfiles
use Thread::Queue;            # needed to create/play tts soundfiles
use Digest::MD5 qw(md5_hex);  # needed to create unique subdirectory names from url



##############################################
#                                            #
#               CONFIGURATION                #
#                                            #
# modify here according your asterisk setup  #
#                                            #
##############################################

# define here the text-to-speech system installed on asterisk
my $TTS = 'festival';
#my $TTS = 'cepstral';
#my $TTS = 'generic';
#my $TTS = 'none';

# if you have installed cepstral voice(s), enter here the location of the swift
# executable
my $SWIFT = '/usr/local/bin/swift';

# cepstral voice to be used
my $SWIFT_SPEAKER = 'katrin';

# if you have installed festival, enter here the location of the text2wave executable
my $TEXT2WAVE = '/home/asterisk/download/festival/bin/text2wave';

# if you have installed any other tts system (generic):
# provide a unix command sequence that:
# - reads text input from stdin
# - writes output in 8kHz/16 bit wav-format to stdout
# the following command sequence works fine for a german MBROLA setup
#    iconv: convert utf-8 input to ascii
#    txt2pho: provide pho-input for the mbrola speech synthesizer
#    mbrola: provide speech output based on a german speech database
#    sox: convert mbrola speech output to 8kHz/16bit as needed for asterisk
my $GENERIC_TTS_COMMAND = 'iconv -f utf-8 | /opt/txt2pho/txt2pho | /opt/mbrola/mbrola /opt/mbrola/de1/de1 - - | sox -t raw -r 16000 -sw - -t wav -r 8000 -Uw -';

# cache subdirectory
my $CACHEDIR = '/var/lib/asterisk/sounds/hastenix_cache';

# number of days while cached sound files will be used (time-to-live)
# 0: do not cache at all
my $CACHE_TTL = 2;

# maximum number of bytes a remote web server is allowed to send
my $READLIMIT = 1000000;

# in case of fatal errors asterisk can play an announcement 'tt-somethingwrong.gsm'
# 0: no playback	1: playback
my $PLAYBACK_ON_ERRORS = 1;

# asterisk command for transfer handling
# transfer is triggered by HAWHAW class HAW_phone, resp. HAWHAW XML <phone>
# '*' will be replaced by the given destination defined in HAW_phone
# arbitrary asterisk commands can be defined to react on transfer requests,
# e.g. play busy tone or announcements
#my $DIALOUT = 'Dial SIP/*@sipgate-out|30';
#my $DIALOUT = 'Dial SIP/*';
my $DIALOUT = 'Busy'; # transfer not allowed
#my $DIALOUT = 'Playback demo-thanks'; # play announcement

# sound files are created, while speech output is done.
# the forerun variable specifies how many sound files are created in advance.
# too low values can cause unneccessary periods of silence.
# too high values will waste hard disk resources in case the caller hangs
# up before the whole text output is done.
my $FORERUN = 5;

##############################################
#                                            #
#            END OF CONFIGURATION            #
#                                            #
#       do not modify from here on ...       #
#                                            #
##############################################



# debug output is sent to stderr and is visible on the asterisk console
use constant DEBUG_LEVEL => 0; # 0: no debug output - each level increases verbosity

use constant SIGNATURE => 'HASTENIX V0.3';


# global variables

my %agi_input;              # agi data received from asterisk
my $vxmlDataRef;            # reference to collected VoiceXML data
my $wavdir;                 # location of sound file(s)
my $abort_flag : shared;    # used to kill sound_creator thread

# some control parameters for xml parser
my $isPrompt;
my $isAudio;
my $isCatch;
my $isRecord;
my $prompt_text;
my $audio_text;
my $audio_src;
my $form_id;

# http user agent
# local instances of user agent for each request can result in sporadic 500 errors!!!
my $userAgent = LWP::UserAgent->new;
$userAgent->agent(SIGNATURE);
$userAgent->parse_head(0);
$userAgent->max_size($READLIMIT);

$|=1; # force immediate output at print commands


sub init_vxmlData
{
	my @promptArray = ();
	my %linkArray = ();
	my %transferArray = ();
	my %recordArray = ();
	my %vxmlData = (
		isValid   => 0,
		timeout   => 0,
		redirect  => "",
		prompts   => \@promptArray,
		links     => \%linkArray,
		transfers => \%transferArray,
		record    => \%recordArray,
	);
	$vxmlDataRef = \%vxmlData;
	
	$isPrompt = 0;
	$isAudio = 0;
	$isCatch = 0;
	$isRecord = 0;
}

sub xmlStart
{
	my ($pointer, $startTag, %attribs) = @_;
	
	print STDERR "XML parser - start tag: <$startTag>\n" if (DEBUG_LEVEL >= 2);
	
	if ($startTag eq "vxml") {
		if (defined $attribs{'version'} && ($attribs{'version'} eq '2.0')) {
			$vxmlDataRef->{'isValid'} = 1;
		}
	}
	
	if ($startTag eq "prompt") {
		$isPrompt = 1; # true
		$prompt_text = "";
		
		if (defined $attribs{'timeout'}) {
			# link delay such as:
			# <field><prompt timeout="10s"/><noinput><exit/></noinput></field>
			my $timeout = $attribs{'timeout'};
			chop($timeout); # remove 's'
			$vxmlDataRef->{'timeout'} = $timeout;
		}
	}
	
	if ($startTag eq "audio") {
		$isAudio = 1; # true
		$audio_text = "";
		
		if ($prompt_text) {
			# store preceeding prompt text
			my %prompt = ( text => $prompt_text );
			push(@{$vxmlDataRef->{'prompts'}}, \%prompt);
			$prompt_text = "";
		}
		
		if (defined $attribs{'src'}) {
			$audio_src = $attribs{'src'};
		} else {
			$audio_src = "";
		}
	}
	
	if ($startTag eq "link") {
		if (defined $attribs{'next'} && defined $attribs{'dtmf'}) {
			$vxmlDataRef->{'links'}->{$attribs{'dtmf'}} = $attribs{'next'};
		}
	}
	
	if (($startTag eq "form") && defined $attribs{'id'}) {
		# save form id (needed for transfer element which should follow immediately)
		$form_id = $attribs{'id'};
	}
	
	if ($startTag eq "transfer") {
		$vxmlDataRef->{'transfers'}->{$form_id} = $attribs{'dest'};
	}
	
	if ($startTag eq "catch") {
		$isCatch = 1; # true
	}
	
	if (($startTag eq "goto") && !$isCatch) {
		# <goto> element outside of <catch> is used by HAWHAW for redirection only
		$vxmlDataRef->{'redirect'} = $attribs{'next'};
	}
	
	if ($startTag eq "record") {
		$isRecord = 1; # true
		$vxmlDataRef->{'record'}->{'name'} = $attribs{'name'};
		
		if (defined $attribs{'beep'} && ($attribs{'beep'} eq 'true')) {
			$vxmlDataRef->{'record'}->{'beep'} = 1;
		} else {
			$vxmlDataRef->{'record'}->{'beep'} = 0;
		}
		
		if (defined $attribs{'maxtime'}) {
			$vxmlDataRef->{'record'}->{'maxtime'} = $attribs{'maxtime'};
			chop($vxmlDataRef->{'record'}->{'maxtime'}); # remove trailing 's'(econd)
		}
		
		if (defined $attribs{'finalsilence'}) {
			$vxmlDataRef->{'record'}->{'finalsilence'} = $attribs{'finalsilence'};
			chop($vxmlDataRef->{'record'}->{'finalsilence'}); # remove trailing s
		}
	}
	
	if (($startTag eq "submit") && $isRecord){
		$vxmlDataRef->{'record'}->{'url'} = $attribs{'next'};
	}
}

sub xmlEnd
{
	my ($pointer, $endTag) = @_;
	my %prompt;
	
	print STDERR "XML parser - end tag: <$endTag>\n" if (DEBUG_LEVEL >= 2);
	
	if ($endTag eq "prompt") {
		
		if ($prompt_text) {
			%prompt = ( text => $prompt_text );
			push(@{$vxmlDataRef->{'prompts'}}, \%prompt);
		}
		
		$isPrompt = 0; # false
	}
	
	if ($endTag eq "audio") {
		
		if ($audio_text) {
			$prompt{'text'} = $audio_text;
		}
		
		if ($audio_src) {
			$prompt{'src'} = $audio_src;
		}
		
		push(@{$vxmlDataRef->{'prompts'}}, \%prompt);
		
		$isAudio = 0; # false
	}
	
	if ($endTag eq "catch") {
		$isCatch = 0; # false
	}
	
	if ($endTag eq "record") {
		$isRecord = 0; # false
	}
}

sub xmlChar
{
	my ($pointer, $char) = @_;
	
	print STDERR "XML parser - character(s): $char\n" if (DEBUG_LEVEL >= 2);
	
	if ($isPrompt && !$isAudio) {
		$prompt_text .= $char;
	}
	
	if ($isAudio) {
		$audio_text .= $char;
	}
}

sub create_cache_dir
{
	my ($url, $headfile) = @_;
	
	my $url_hash = md5_hex($url);
	
	if (!-d $CACHEDIR) {
		# cache directory not existing ==> create it
		mkdir($CACHEDIR) || die "cannot create cache directory $CACHEDIR";
	}
	
	my $sounddir = $CACHEDIR . '/' . $url_hash;
	
	$headfile = $sounddir . '/' . $headfile;
	
	if (-d $sounddir) {
		unless (-e $headfile) {
			
			# hash directory exists, but no headfile is existing
			# can happen if caching has been disabled on document level
			
			print STDERR "missing headfile: delete all files in directory $sounddir\n" if (DEBUG_LEVEL >= 1);
			
			# delete all files in cache directory
			opendir(DIR, $sounddir);
			my @cachefiles = readdir(DIR);
			closedir(DIR);
			foreach (@cachefiles) {
				unlink($sounddir . "/" . $_); # delete all files of directory
			}
		}
	}
	else {
		# hash directory not existing ==> create it
		print STDERR "create cache directory $sounddir\n" if (DEBUG_LEVEL >= 1);
		mkdir($sounddir) || die "cannot create cache directory for $url";
	}
	
	if ((-e $headfile) && (-C $headfile > $CACHE_TTL)) {
		print STDERR "cache exists but has expired! deleting ...\n" if (DEBUG_LEVEL >= 1);
		
		# delete all files in cache directory
		opendir(DIR, $sounddir);
		my @cachefiles = readdir(DIR);
		closedir(DIR);
		foreach (@cachefiles) {
			unlink($sounddir . "/" . $_); # delete all files of directory
		}
	}
	
	return $sounddir;
}

sub retrieve_VoiceXML
{
	my (%http_req) = @_;
	
	$wavdir = &create_cache_dir($http_req{'url'}, 'cache.vxml');
	my $vxml_file = $wavdir . '/cache.vxml';
	
	my $content = "";
	
	if (-e $vxml_file) {
		print STDERR "read VoiceXML document from cache\n" if (DEBUG_LEVEL >= 1);
		open(VXMLFILE, "<$vxml_file") || die "cannot open $vxml_file";
		read(VXMLFILE, $content, $READLIMIT);
		close(VXMLFILE);
	}
	else {
		my $http_response;
		my $url = $http_req{'url'};
		
		if (index($url, "?") < 0) {
			 $url .= '?' # begin of query parameters
		}
		else {
			 $url .= '&' # add to query parameters
		}
		
		# add agi input parameters
		while (my ($key, $value) = each %agi_input) {
			$url .= uri_escape('agi_' . $key) . '=' . uri_escape($value) . '&';
		}
		chop($url); # remove trailing '&'
		
		if ($http_req{'method'} eq 'get') {
			# send GET request
			print STDERR "get VoiceXML document from (remote) webserver:\n" .
				$url . "\n" if (DEBUG_LEVEL >= 1);
			my $http_request = HTTP::Request->new(GET => $url);
			$http_request->header(accept => 'application/voicexml+xml');
			$http_response = $userAgent->request($http_request);
		}
		else {
			# send POST request
			print STDERR "post file and retrieve VoiceXML response from (remote) webserver:\n" .
			$url . "\n" if (DEBUG_LEVEL >= 1);
			$http_response = $userAgent->request(POST $url,
				Content_Type => 'form-data',
				Accept => 'application/voicexml+xml',
				Content => [ $http_req{'name'} => [$http_req{'filename'}]]
			);
		}
		
		$content = $http_response->content;
		
		unless ($http_response->is_success) {
			print "EXEC Playback tt-somethingwrong\n" if $PLAYBACK_ON_ERRORS;
			die "got " . $http_response->status_line . " response for " . $http_req{'url'};
		}
		
		if (length($content) >= $READLIMIT) {
			print "EXEC Playback tt-somethingwrong\n" if $PLAYBACK_ON_ERRORS;
			die "maximum document length exceeded";
		}
		
		if ((index($content, "<meta http-equiv=\"Expires\"") < 0)
		&&  ($http_req{'method'} eq 'get')) {
			# write received VoiceXML data into cache
			open(VXMLFILE, ">$vxml_file");
			print VXMLFILE $content;
			close(VXMLFILE);
		}
		else {
			print STDERR "VoiceXML document must not be cached.\n" if (DEBUG_LEVEL >= 1);
		}
	}
	
	print STDERR "$content" if (DEBUG_LEVEL >= 2);
	
	return($content);
}

sub get_soundfile
{
	my ($url) = @_;
	
	$url =~ /([^\/]*)$/;
	my $soundfile_name = $1; # everything after last slash character
	
	my $sounddir = &create_cache_dir($url, $soundfile_name);
	my $sound_file = $sounddir . '/' . $soundfile_name;
	
	if (-e $sound_file) {
		print STDERR "use cache sound file $sound_file\n" if (DEBUG_LEVEL >= 1);
	}
	else {
		print STDERR "get sound file from (remote) webserver:\n$url\n" if (DEBUG_LEVEL >= 1);
		
		my $http_request = HTTP::Request->new(GET => $url);
		$http_request->header(accept => '*/*');
		my $http_response = $userAgent->request($http_request);
		
		my $sounddata = $http_response->content; # binary sound data
		
		unless ($http_response->is_success) {
			print STDERR "got " . $http_response->status_line . " response\n" if (DEBUG_LEVEL >= 1);
			return("");
		}
		
		if (length($sounddata) >= $READLIMIT) {
			print STDERR "maximum sound file size exceeded\n" if (DEBUG_LEVEL >= 1);
			return("");
		}
		
		# write received sound data into cache
		open(SOUNDFILE, ">$sound_file");
		print SOUNDFILE $sounddata;
		close(SOUNDFILE);
	}
	
	# remove extension from soundfile name
	my $soundfile_name_without_ext = $soundfile_name;
	$soundfile_name_without_ext =~ s/\.\w*$//;
	
	return($sounddir . '/' . $soundfile_name_without_ext);
}

sub determine_global_url
{
	my ($url, $previous_url) = @_;
	my $global_url;
	
	if ($url =~ /^http/) {
		# url already fully qualified
		$global_url =$url;
	}
	else {
		# parse previous url or die in case of invalid format
		# e.g. previous_url: http://www.foo.com/mypath/mypage.php
		unless ($previous_url =~ /^(https?:\/\/[^\/]*)(.*\/)/) {
			print "EXEC Playback tt-somethingwrong\n" if $PLAYBACK_ON_ERRORS;
			die('invalid url: ' . $previous_url);
		}
		
		my $prot_host_part = $1; # => "http://www.foo.com"
		my $path_part = $2; # => "/mypath/"
		
		if ($url =~ /^\//) {
			# absolute url
			$global_url = $prot_host_part . $url;
		}
		else {
			#relative url
			$global_url = $prot_host_part . $path_part . $url;
		}
	}
	
	return($global_url);
}

sub record
{
	my $filename = $wavdir . "/rec_" . $agi_input{'uniqueid'};
	my $format = "gsm";
	my $escape_digits = "1234567890*#";
	
	my $timeout = 180000; # default 3 min.
	if (defined($vxmlDataRef->{'record'}->{'maxtime'})) {
		$timeout = $vxmlDataRef->{'record'}->{'maxtime'} * 1000;
	}
	
	my $beep = "";
	if (defined($vxmlDataRef->{'record'}->{'beep'}) &&
		 ($vxmlDataRef->{'record'}->{'beep'} == 1)) {
		$beep = 'beep';
	}
	
	my $silence = "s=5";
	if (defined($vxmlDataRef->{'record'}->{'finalsilence'})) {
		$silence = "s=" . $vxmlDataRef->{'record'}->{'finalsilence'};
	}
	
	my $record_cmd = "RECORD FILE $filename $format $escape_digits $timeout $beep $silence\n";
	print STDERR "RECORD: execute $record_cmd\n" if (DEBUG_LEVEL >= 1);
	
	print $record_cmd;
	my $stdin = <STDIN>;
	print STDERR "result of RECORD FILE: " . $stdin . "\n" if (DEBUG_LEVEL >= 1);
	
	return("$filename.$format");
}

sub sound_creator_thread
{
	my ($soundfile_queue, $prompts, $url) = @_;
	my $counter = 0;
	
	foreach my $prompt (@{$prompts}) {
		
		if (defined($prompt->{src})) {
			# must retrieve sound file here!!!
			my $soundfile_url = &determine_global_url($prompt->{src}, $url);
			my $soundfile = &get_soundfile($soundfile_url);
			
			if ($soundfile) {
				$prompt->{text} = ""; # text of no importance any more
				print STDERR "enqueue: $soundfile\n" if (DEBUG_LEVEL >= 1);
				$soundfile_queue->enqueue($soundfile);
			}
		}
		
		my $text = $prompt->{text};
		if (length($text) > 0)
		{
			my $wavname = $wavdir . "/" . $counter;
			my $wavfile = $wavname . ".wav";
			unless (-e $wavfile) {
				
				# no wav-file existing for given prompt => create it
				
				print STDERR "create wav-file $wavfile (tts: $TTS)\n" if (DEBUG_LEVEL >= 1);
				
				if ($TTS eq 'cepstral') {
					# use cepstral/swift to create wav-file
					
					system($SWIFT .
						" -p audio/channels=1,audio/sampling-rate=8000" .
						" -o " . $wavfile .
						" -n " . $SWIFT_SPEAKER .
						" -e utf-8" .
						" \" $text\"" ); # leading hyphen breaks cepstral => leading blank
				}
				
				if ($TTS eq 'festival') {
					# use festival/text2wave to create wav-file
					system("echo \"$text\" | $TEXT2WAVE" .
						" -f -" .
						" -F 8000" .
						" -o " . $wavfile);
				}
				
				if ($TTS eq 'generic') {
					# use generic tts system
					system("echo \"$text\" | $GENERIC_TTS_COMMAND > $wavfile");
				}
			}
			
			print STDERR "enqueue: $wavname\n" if (DEBUG_LEVEL >= 1);
			$soundfile_queue->enqueue($wavname);
		}
		
		$counter++;
		
		print STDERR "items in queue: " . $soundfile_queue->pending() . "\n" if (DEBUG_LEVEL >= 2);
		while (($abort_flag == 0) && ($soundfile_queue->pending() > $FORERUN)) {
			print STDERR "wait a second ...\n" if (DEBUG_LEVEL >= 2);
			sleep(1);
		}
		
		if ($abort_flag == 1) {
			return;
		}
	}
	
	$soundfile_queue->enqueue(""); # done indication
}

sub background_player
{
	my ($soundfile_queue, $url) = @_;
	my $sound_name;
	
	do {
		$sound_name = $soundfile_queue->dequeue();
		print STDERR "dequeue: $sound_name\n" if (DEBUG_LEVEL >= 1);
		
		if ($sound_name) {
			# continue with playing sound files ...
			print "EXEC Background " . $sound_name . "\n";
		}
		else {
			# wait a little bit for user input
			print "WAIT FOR DIGIT " . $vxmlDataRef->{'timeout'} * 1000 . "\n";
		}
		
		my $stdin = <STDIN>; # read asterisk's response
		print STDERR "asterisk response: $stdin\n" if (DEBUG_LEVEL >= 1);
		
		if (!$stdin) {
			print STDERR "no STDIN response from asterisk - eof.\n" if (DEBUG_LEVEL >= 1);
			return(""); # caller probably went onhook ...
		}
		
		my $response = $stdin;
		if ($response =~ /^(\d+) result=(\d+)/) {
			if (($1 eq '200') && ($2 ne '0')) {
				my $button = chr($2);
				if (defined $vxmlDataRef->{'links'}->{$button}) {
					
					# dtmf key has been pressed and there is a link available for this key ...
					
					print STDERR "DTMF key: $button\n" if (DEBUG_LEVEL >= 1);
					
					my $dest; # next destination
					
					if (substr($vxmlDataRef->{'links'}->{$button},0,1) eq '#') {
						# transfer link
						my $form_id = substr($vxmlDataRef->{'links'}->{$button},1); # remove #
						$dest =	 $vxmlDataRef->{'transfers'}->{$form_id};
					}
					else {
						# http(s) link
						$dest = &determine_global_url($vxmlDataRef->{'links'}->{$button}, $url);
					}
					
					my %http_req = ( url => $dest, method => 'get' );
					return(\%http_req);
				}
			}
		}
	} while ($sound_name);
	
	if ($vxmlDataRef->{'redirect'}) {
		# perform redirection to another url
		print STDERR "performing redirection ...\n" if (DEBUG_LEVEL >= 1);
		my %http_req = (
			url    => determine_global_url($vxmlDataRef->{'redirect'}, $url),
			method => 'get'
		);
		return(\%http_req);
	}
	
	if (defined($vxmlDataRef->{'record'}->{'name'})
	&&  defined($vxmlDataRef->{'record'}->{'url'})) {
		
		# create recording ...
		my $soundfile = record();
		
		# ... and send it to web server via post method
		my %http_req = (
			url      => determine_global_url($vxmlDataRef->{'record'}->{'url'}, $url),
			method   => 'post',
			filename => $soundfile,
			name     => $vxmlDataRef->{'record'}->{'name'}
		);
		
		return(\%http_req);
	}
	
	return(""); # we are done - no succeeding url available
}

sub handle_url
{
	my (%http_req) = @_;
	# perl 5.8.0 claims that this hash is leaking - seems to be a perl bug ...
	
	# retrieve VoiceXML data from remote host or from cache
	my $content = &retrieve_VoiceXML(%http_req);
	
	# remove quote entities
	$content =~ s/&quot;//g;
	
	&init_vxmlData(); # data will be collected during parse callbacks
	
	# parse VoiceXML
	my $xmlParser = new XML::Parser();
	$xmlParser->setHandlers(Start => \&xmlStart, End => \&xmlEnd, Char => \&xmlChar);
	$xmlParser->parse($content);
	
	unless ($vxmlDataRef->{'isValid'}) {
		print "EXEC Playback tt-somethingwrong\n" if $PLAYBACK_ON_ERRORS;
		die('no valid VoiceXML document received');
	}
	
	# create queue where sound_creator sends information about created sound files
	# towards background player (producer/consumer pattern)
	my $queue = new Thread::Queue();
	
	$abort_flag = 0; # reset flag
	
	my $sound_creator = threads->new(
		\&sound_creator_thread,
		$queue, $vxmlDataRef->{'prompts'},
		$http_req{'url'}
	);
	
	my $result = &background_player($queue, $http_req{'url'});
	
	$abort_flag = 1; # terminate sound_creator thread in case he's still alive
	$sound_creator->join();
	
	if (!$result) {
		print STDERR "no further destination available - we're done!\n" if (DEBUG_LEVEL >= 1);
		return; # we're done ...
	}
	
	my %next_dest = %{$result};
	
	if ($next_dest{'url'} =~ /^http/) {
		&handle_url(%next_dest); # be recursive ...
	}
	else {
		# must be <transfer> link
		my $dial_cmd = $DIALOUT;
		$next_dest{'url'} =~ /:(.*)/; # remove protocol part, e.g. tel: or sip:
		my $dial_target = $1;
		$dial_cmd =~ s/\*/$dial_target/; # replace '*' in $DIALOUT with $dial_target
		print STDERR "DIALOUT: execute $dial_cmd\n" if (DEBUG_LEVEL >= 1);
		
		print "EXEC $dial_cmd\n";
		my $stdin = <STDIN>;
		print STDERR "result of EXEC Dial: " . $stdin . "\n" if (DEBUG_LEVEL >= 1);
	}
}



# start of main program

# read asterisk agi input parameters
while (<STDIN>) {
	chomp;
	last unless length($_);
	if (/^agi_(\w+)\:\s+(.*)$/) {
		$agi_input{$1} = $2;
	}
}

# dump agi params
while (my ($key, $value) = each %agi_input) {
	print STDERR "$key => $value\n" if (DEBUG_LEVEL >= 2);
}

print "EXEC Answer\n";
my $stdin=<STDIN>;
print STDERR "answer response: $stdin\n" if (DEBUG_LEVEL >= 1);

my %http_req = ( url => $ARGV[0], method => 'get');

&handle_url(%http_req);
