#! /usr/bin/perl

#
# Upload a given queue_log file to a partition of the queue_log table.
# $Id: qloader.pl,v 1.18 2010/01/20 10:17:30 marcos Exp $
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
# ATTENTION  ATTENTION  ATTENTION  ATTENTION  ATTENTION  ATTENTION  
# If this file does not seem to work from the shell, do a 
#        dos2unix queueLoader.pl
# to set things right.
# ATTENTION  ATTENTION  ATTENTION  ATTENTION  ATTENTION  ATTENTION  
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
my $breakloop = $ARGV[3] || "0";

my $pidfile   = "/var/run/qloader.pid";

my $log_every_num = 100;
my $log_queries   = 0;            # set to 1 to log all queries 

my $timezone_offset  = 0 * 3600;  # in seconds
my $heartbeat_delay  = 15 * 60;   # in seconds
my $use_subqueue     = 0;         # 0 no; 1 yes
my $split_subq_name  = 0;         # 0 no; 1 yes - turn a subqueue name from 'xxx/yyy" to "xxx"
my $rewriteToAgent   = 0;         # 0 no; 1 yes
my @channelsToAgent  = ( 'Local', 'SIP' );
my $dbAgentRewrite   = 0;         # 0 no; 1 yes - rewrite according to rules in table qlog_rewrite


# =================================================================
# 
#                          Preamble
#
# =================================================================


syslog( "QueueMetrics MySQL loader - " . '$Revision: 1.18 $' );
syslog( "Partition $partition - PID $$ - TZ offset: $timezone_offset s. - Heartbeat after $heartbeat_delay s." ) if ($breakloop eq "0");
syslog( "Partition $partition - PID $$ - TZ offset: $timezone_offset s." ) unless ($breakloop eq "0");
#syslog( "H: $mysql_host D: $mysql_db U: $mysql_user P: $mysql_pass" );

savePID() if ($breakloop eq "0");

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

open FX, $file or die "$! $file";
while ( 1 ) {
	while ( <FX> ) {
		# innanzi tutto, controllo di avere una riga completa
		# se non c'e', loop
		if ( index( $_, "\n") < 0 ) {
			seek(FX, -length($_), 1);
			syslog( "Incomplete line read: pushing back '$_'" );
			sleep 1;
			next;
		}
		
		# ottiene la riga
		# aggiusta il primo valore con l'offset orario
		syslogquery( "Row: " . $_ );
		my @rowdata  = ((split /\|/, $_), ( "", "", "", "", "", "", "")) ;

                # skip all lines with an invalid offset
                next unless ($rowdata[0] =~ m/^\d{10}$/);

		$rowdata[0] = ($rowdata[0] * 1.0) + $timezone_offset;

		my $tst = $rowdata[0];
		
		if ($use_subqueue) {
			$rowdata[2] = rewriteSubqueue( @rowdata );
		}
	
		if ( $rewriteToAgent ) {
			$rowdata[3] = rewriteToAgent( $rowdata[3] );
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
    
    	# Terminate the loop if requested by the command line parameters
    	unless ($breakloop eq "0")
    	{
        	syslog("Exiting at the end of file, as requested by user parameters");
        	last;
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
		syslog( "Skipping line without verb at $tst $cid $que $age " );
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
	             WHERE partition = '$partition' 
	               and time_id=$tst
	               and call_id = $cid
	               and queue=$que
	               and agent=$age 
	               and verb=$verb 
	               and data1=$d1
	               and data2=$d2
	               ";

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
	
	syslogquery( "Attempting query: $query" );
	
	while ( $dberr > 0 ) {
		reconnectDb();		
	}	
	
	my $sth = $dbh->prepare($query);
	$sth->execute();
	
	
	
	if ( length( $sth->errstr ) > 0 ) {
		syslogquery( "ERROR: " . $sth->errstr );
		reconnectDb();
		syslogquery( "Retrying query: " . $query );
		$sth = $dbh->prepare($query);
		$sth->execute() or die $sth->errstr;			
		syslogquery( "Query run: " . $query );
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
	syslogquery( "Returns: " . $retval );
	return $retval;
	
}

sub reconnectDb {
	# tenta la riconnessione
	# manda una query
	# riprova finch� il risultato della query non � corretto
	my $expval = 2006;
	my $retval = -1;
	while ( $expval != $retval ) {
		syslog( "Now connecting to DB $mysql_db on $mysql_host as user $mysql_user with password $mysql_pass" );
		$dbh = DBI->connect( "DBI:mysql:dbname=$mysql_db;host=$mysql_host", $mysql_user, $mysql_pass,
		       {
		       	#RaiseError => 1,
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
	# se l'update � su 0 righe, faccio la insert
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

sub syslogquery {
	my ($s) = @_;
	if ( $log_queries > 0 ) {
		syslogger( "Q", $s );
	}
}


sub syslogger {
	my ($l, $s) = @_;
	$s =~ s/\n/ /gm;
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



#
# $Log: qloader.pl,v $
# Revision 1.18  2010/01/20 10:17:30  marcos
# #792: Overflow on timestamps have to invalidate the line.
#
# Revision 1.17  2009/03/30 08:14:38  lenz-mobile
# Bug #670: handling of incomplete rows.
#
# Revision 1.16  2009/03/27 10:05:21  lenz-mobile
# Minor fixes in logging
#
# Revision 1.15  2009/03/27 08:58:24  lenz-mobile
# Better syncing algorithm
#
# Revision 1.14  2009/03/27 08:51:51  lenz-mobile
# Extra-verbose query logging
#
# Revision 1.13  2009/02/04 11:50:44  marcos
# SavePid is not required for not daemon mode.
#
# Revision 1.12  2009/01/22 17:22:59  marcos
# #586: NFON issue #6: Partial database update of already uploaded queue data activities
#
# Revision 1.11  2008/05/16 14:16:39  lenz
# Ver 1.11
#
# Revision 1.10  2008/05/16 14:15:28  lenz
# Parameters on the command line.
#
# Revision 1.9  2008/01/19 16:29:57  lenz
# Database-driven agent rewriting.
# Minor fixes to Perl database access code.
#
# Revision 1.8  2007/08/05 11:12:56  lenz
# Bug #179: Ignoring HEARTBEAT record when finding High Water Mark
#
# Revision 1.7  2007/05/07 16:10:10  lenz
# Documentazione.
# Spente caratteristiche Fancy nella versione distribuita.
#
# Revision 1.6  2007/04/01 16:49:02  lenz
#
# Toglie il nome della coda se espresso nel formato '0279/mia coda' per la riscrittura dinamica.
# Tolto il log su STDOUT.
#
# Revision 1.5  2007/03/15 16:48:17  lenz
# Versione corretta.
#
# Revision 1.4  2007/03/12 11:35:13  lenz
# Riscrittura canali e subqueue
#
# Revision 1.3  2007/02/16 11:15:40  lenz
# Added heartbeat and whitespace trimming.
#
# Revision 1.2  2006/09/08 23:58:53  lenz
# Automagic timezone offset.
#
# Revision 1.1  2006/08/22 16:04:50  lenz
# First CVS version.
#
#
#

