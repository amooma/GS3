#! /usr/bin/perl
#
# Upload a given queue_log file to a partition of the queue_log table.
#
# $Revision: 5318 $
#
# modified from the original version (c) 2005-2008 Loway to support 
# agents in Gemeinschaft
#
# Copyright 2008, LocaNet oHG
# Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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
#
# usage:
# queueLoader.pl [flags] /my/queue_log/file partition_name /my/activity/log
#
# Known flags:
#  -h host       : host name
#  -d database   : database name
#  -u user       : user name
#  -p password   : password
#
#

use strict;
use DBI;
use Getopt::Std;

my %options;
getopts( "h:u:p:d:", \%options );

my $mysql_host = $options{h} || "10.10.3.5";
my $mysql_db   = $options{d} || "log_code";
my $mysql_user = $options{u} || "ldap";
my $mysql_pass = $options{p} || "ldappo";

my $dbh    = undef;
my $dberr  = 1;       # setto a 0 quando tutto va bene

my $file	  = $ARGV[0] || "/var/log/asterisk/queue_log";
my $partition = $ARGV[1] || "P000";
my $importLog = $ARGV[2] || "/var/log/asterisk/qloader.log";

my $pidfile   = "/var/run/qloader.pid";

my $log_every_num = 100;

my $timezone_offset  = 0 * 3600;  # in seconds
my $heartbeat_delay  = 15 * 60;   # in seconds
my $use_subqueue     = 0;         # 0 no; 1 yes
my $split_subq_name  = 0;         # 0 no; 1 yes - turn a subqueue name from 'xxx/yyy" to "xxx"
my $rewriteToAgent   = 1;         # 0 no; 1 yes
my @channelsToAgent  = ( 'Local', 'SIP' );
my $dbAgentRewrite   = 0;         # 0 no; 1 yes - rewrite according to rules in table qlog_rewrite

# =================================================================
# 
#                          Preamble
#
# =================================================================


syslog( "QueueMetrics MySQL loader - " . '$Revision: 5318 $' );
syslog( "Partition $partition - PID $$ - TZ offset: $timezone_offset s. - Heartbeat after $heartbeat_delay s." );
#syslog( "H: $mysql_host D: $mysql_db U: $mysql_user P: $mysql_pass" );

savePID();

# controlla il valore massimo del timestamp
my $highWaterMark = scalarQuery( "SELECT max(time_id) FROM queue_log WHERE partition='$partition' AND verb != 'HEARTBEAT'" );
if ( $highWaterMark eq '' ) { $highWaterMark = 0; };

syslog( "Ignoring all timestamps below $highWaterMark ");

# =================================================================
# 
#                      Main import loop
#
# =================================================================
my $nLowLines = 0;
my $nImportLines = 0;
my $tstLastInsert = 0;
my $last_query = time();

my %agentToSipMap = {};

open FX, $file or die "$! $file";
while ( 1 ) {
	while ( <FX> ) {
		# ottiene la riga
		# aggiusta il primo valore con l'offset orario
		my @rowdata  = ((split /\|/, $_), ( "", "", "", "", "", "", "")) ;
		$rowdata[0] = ($rowdata[0] * 1.0) + $timezone_offset;
		
		my $tst = $rowdata[0];
		
		if ($rowdata[4] eq 'ADDMEMBER') {
			chomp($rowdata[5]);
			$agentToSipMap{$rowdata[5]} = $rowdata[3];
		}
                
		if ($rowdata[4] eq 'REMOVEMEMBER') {
			chomp($rowdata[5]);
			undef $agentToSipMap{$rowdata[5]};
		}
		
		if ($use_subqueue) {
			$rowdata[2] = rewriteSubqueue( @rowdata );
		}
	
		if ( $rewriteToAgent ) {
			if ($agentToSipMap{$rowdata[3]}) {
				$rowdata[3] = $agentToSipMap{$rowdata[3]};
			}
		}
		
		if ( $dbAgentRewrite ) {
			$rowdata[3] = agentRewriteByDB( $rowdata[3] );
		}
			
		if ( $tst < $highWaterMark ) {
			# salta le righe obsolete
			skipRow( @rowdata );			
		} 
		elsif ( $tst == $highWaterMark ) {
			# faccio il check se del primo o del secondo tipo....			
			if ( ! checkExistingRow( @rowdata ) ) {
				insertRow( @rowdata );				
			} else {
				skipRow( @rowdata );
			}
						
		} else {
			# timestamp ancora ignoto
			insertRow( @rowdata );
			
		}
		
	}
	sleep 1;
	checkHeartBeat();
}


# =================================================================
# 
#                      queue_log data handling
#
# =================================================================

sub skipRow {
	my ($tst, $cid, $que, $age, $verb, $d1, $d2, $d3) = @_;
	$nLowLines+=1;
	if (( $nLowLines % $log_every_num ) == 0 ) { syslog( "Skipped $nLowLines lines so far..." ) };	
}



sub insertRow {
	my ($tst, $cid, $que, $age, $verb, $d1, $d2, $d3) = @_;
	
	# salta le linee che non hanno verbo
	if ( length($verb) == 0 ) {
		return;
	}
	
	($tst, $cid, $que, $age, $verb, $d1, $d2, $d3)  = map { myQuote($_) } ($tst, $cid, $que, $age, $verb, $d1, $d2, $d3);
		
	my $sql = " INSERT INTO `queue_log` 
		( `partition` , `time_id` , `call_id` , `queue` , `agent` , `verb` , `data1` , `data2` , `data3` , `data4` ) 
		VALUES 
		( '$partition', $tst, $cid, $que, $age, $verb, $d1, $d2, $d3, '' );";
	
	execQuery( $sql );
	
	$nImportLines +=1;
	if (( $nImportLines % $log_every_num ) == 0 ) { syslog( "Loaded $nImportLines lines so far..." ) };
}


sub checkExistingRow {
	my ($tst, $cid, $que, $age, $verb, $d1, $d2, $d3) = @_;
	($tst, $cid, $que, $age, $verb, $d1, $d2, $d3)  = map { myQuote($_) } ($tst, $cid, $que, $age, $verb, $d1, $d2, $d3);

	my $sql = "SELECT COUNT(*) 
			FROM queue_log
			WHERE partition = '$partition' and time_id=$tst
			and call_id = $cid and verb=$verb ";

	my $nRows = scalarQuery( $sql ) * 1.0;
	
	if ( $nRows == 0 ) {	
		return 0;         # false
	} else {
		return 1;         # true
	}

}


sub checkHeartBeat() {
	my $now = time();
	if ( $heartbeat_delay > 0 ) {
		if ( ( $now - $last_query ) > $heartbeat_delay ) {
			syslog( "Heart is still beating... Imported: $nImportLines lines." );
			my $adj_time = $now + $timezone_offset;
			my $sql = " INSERT INTO queue_log
					(partition, time_id, call_id, queue, agent, verb ) VALUES
					( '$partition', $adj_time , 'NONE', 'NONE', 'NONE', 'HEARTBEAT' ) ";
			
			execQuery( $sql );		
		}
	}
}

#
# riscrittura subqueue
#

sub rewriteSubqueue() {
	my ($tst, $cid, $que, $age, $verb, $d1, $d2, $d3) = @_;
	my $sq;
	if ( uc($que) ne 'NONE' && uc($cid) ne 'NONE' ) {
		if ( uc($verb) eq 'ENTERQUEUE' ) {
			$sq = getSubQueueName($d1);			
		} else {
			$sq = getSubQueue( $partition, $cid );
		}		
		updateCall( $partition, $cid, $que, $sq, $verb, $tst );
		
		if ( length($sq) > 0 ) {
			return "$que.$sq";
		} else {
			return $que;
		}	
	} else {
		return "NONE";
	}
}

sub getSubQueue() {
	my ( $part, $cid ) = @_;
	return scalarQuery( "SELECT subqueue FROM qlog_opencalls "
				." WHERE callId='$cid' AND partition = '$part' " );
}

sub updateCall() {
	my ( $part, $cid, $queue, $sq, $verb, $tst ) = @_;
	updateOrInsert( 
		"UPDATE qlog_opencalls 
		SET queue = '$queue', subqueue='$sq', lastVerb='$verb', lastTst = '$tst', lastUpd = NOW()
		WHERE callId='$cid' AND partition = '$part' ",
		"INSERT INTO qlog_opencalls
		( callId, partition, queue, subqueue, lastVerb, lastTst, lastUpd ) values
		( '$cid', '$part', '$queue', '$sq', '$verb', '$tst', NOW() ) "
	);
}

sub getSubQueueName() {
	my ( $name ) = @_;
	if ( ($split_subq_name == 1) && ($name =~ /^(.+?)\//) ) {
		return $1;
	}
	return $name;
	
}


#
# da canale ad Agent
#
sub rewriteToAgent() {
	my ($channel) =  @_ ;
	my $ctype;
	
	if ( uc($channel) eq 'NONE' ) {
		return 'NONE';
	}
	
	foreach $ctype ( @channelsToAgent ) {
		if ($channel =~ /^${ctype}\/([a-zA-Z0-9_]+)/i ) {
			return "Agent/$1";
		}
	}	
	return $channel;
}


sub agentRewriteByDB {
	my ( $agent ) = @_;
	
	if ( uc($agent) eq "NONE" ) {
		return "NONE";
	}

	my $newAgent = scalarQuery( "SELECT ag_rewritten FROM qlog_rewrite WHERE ag_from = '$agent'" );
	
	if ( length( $newAgent ) > 0 ) {
		return $newAgent;
	} else {
		return $agent;
	}	
}





# =================================================================
# 
#                      Mysql database handling
#
# =================================================================



sub execQuery {
	my ( $sql ) = @_;
	execSafeQuery( $sql, 0 );
}

sub scalarQuery {
	my ( $sql ) = @_;
	my $r = execSafeQuery( $sql, 1 );
	#syslog( "$sql -> $r" );
	return $r;
}


sub execSafeQuery {
	# returnScalar: 
	# 0: nulla
	# 1: scalare
	# 2: rows affected
	
	my ($query, $returnScalar) = @_;
	my $retval = '';
	
	while ( $dberr > 0 ) {
		reconnectDb();		
	}	
	
	my $sth = $dbh->prepare($query);
	$sth->execute();
	
	if ( length( $sth->errstr ) > 0 ) {
		reconnectDb();
		$sth = $dbh->prepare($query);
		$sth->execute() or die $sth->errstr;;			
	}
	
	if ( $returnScalar == 1 ) {
		( $retval ) = $sth->fetchrow_array;
		
		$sth->finish();
		
		if ( !defined( $retval ) ) {
			$retval = "";
		}
		
	} elsif ( $returnScalar == 2 ) {
		$retval = $sth->rows;
		if ( !defined( $retval ) ) {
			$retval = 0;
		}
	}


	$last_query = time();	
	return $retval;
	
}

sub reconnectDb {
	# tenta la riconnessione
	# manda una query
	# riprova finchè il risultato della query non è corretto
	my $expval = 2006;
	my $retval = -1;
	while ( $expval != $retval ) {
		syslog( "Now connecting to DB $mysql_db on $mysql_host as user $mysql_user with password $mysql_pass" );
		$dbh = DBI->connect( "DBI:mysql:dbname=$mysql_db;host=$mysql_host", $mysql_user, $mysql_pass,
			{
				PrintError => 1,
				ShowErrorStatement => 1,
				HandleError => \&errDb
			}
		);
		
		# testa la connessione
		if ( $dbh ) {
			my $sql = "SELECT $expval AS X";
			my $sth = $dbh->prepare($sql);
			$sth->execute();
			if ( length( $sth->errstr ) > 0 ) {
				syserr( "Error on query: waiting 15s before reattempting to connect" ); 
				sleep 15;
			} else {
				($retval) = $sth->fetchrow_array;
				$sth->finish;
			}
		} else {
			syserr( "Waiting 15s before reattempting to connect" ); 
			sleep 15;
		}
		
	}
	$dberr = 0;
	
}

sub errDb {
	$dberr = 1;
	my ( $msg, $h, $ret ) = @_;
	syserr( "---ERROR FOUND--" );
	syserr( "Error type: " . $h->{Type} );
	syserr( " Statement: " . $h->{Statement} );
	syserr( "     Error: " . $h->errstr );
	return 1;
}

sub myQuote {
	my ($v) = @_;

	while ( $dberr > 0 ) {
		reconnectDb();		
	}	

	if (length($v) == 0 ) {
		return "''";
	} else {
		return $dbh->quote( trim($v) );
	}
}

sub trim {
	my $string = shift;
	$string =~ s/^\s+//;
	$string =~ s/\s+$//;
	return $string;
}

sub updateOrInsert() {
	# se l'update è su 0 righe, faccio la insert
	my ( $sqlUpdate, $sqlInsert ) = @_;
	
	my $nRows = execSafeQuery( $sqlUpdate, 2 );
	if ( $nRows == 0 ) {
		execSafeQuery( $sqlInsert, 0 );
	};
}



# =================================================================
# 
#                      Error logging
#
# =================================================================


sub syslog {
	my ($s) = @_;
	syslogger( " ", $s );
}

sub syserr {
	my ($s) = @_;
	syslogger( "E", $s );
}

sub syslogger {
	my ($l, $s) = @_;
#	print "$l|$s\n";
	open EL, ">>$importLog" or die "$! $importLog";
	print EL "$l|" . (scalar localtime) . "|$s\n";
	close EL;
}


sub savePID {
	open FP, ">$pidfile" or die "$! $pidfile";
	print FP $$;
	close FP;
}
