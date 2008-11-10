<?php
/*******************************************************************\
*                               YaDB
* 
* Copyright 2006-2008, Philipp Kempgen <philipp.kempgen@amooma.de>,
* amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301, USA.
\*******************************************************************/

/***********************************************************
* Requires PHP mysqli extension (PHP >= 5)!
* 
* Supported driver options for connect():
* -none-
***********************************************************/

#####################################################################
#
#                        !!!  WARNING  !!!
#
#   There seem to be stability problems with the mysqli extension.
#   Sometimes Apache + mod_php + mysqli does not even output
#   response headers.
#
#####################################################################

if (!defined('YADB_DIR')) die("No direct access\n");

ini_set('mysql.trace_mode', 0);
// must be off or else SELECT FOUND_ROWS() always returns 0
ini_set('mysqli.reconnect', true);


class YADB_Connection_mysqli extends YADB_Connection
{
	/**
	* DBMS specific:
	*/
	var $_replaceQuote    = '\\\'';
	var $_metaDbsSQL      = 'SHOW DATABASES';  // this would be the command but we use the driver's native command anyway
	var $_metaTblsSQL     = 'SHOW TABLES';
	var $_metaColsSQL     = 'SHOW COLUMNS FROM `%s`';
	var $_namesQuote      = '`';
	//var $_hasInsertID     = true;
	//var $_hasAffRows      = true;
	//var $_poorAffRows     = true;
	//var $_hasLimit        = true;
	//var $_hasMoveFirst    = true;
	//$_hasTrans must stay false because we do not yet know if the db supports transactions
	//$_hasPrepare must stay false because we do not yet know if the db supports prepared statements
	//var $_hasExecMulti must stay false because we do not yet know if the db supports executeMulti() (only mysqli and only if not disabled)
	
	/**
	* custom driver vars:
	*/
	var $_drvSrvVersArr   = null;
	var $_drvSrvVers      = null;
	
	
	function YADB_Connection_mysqli( $dbtype ) #DONE
	{
		//parent::__construct( $dbtype );
		parent::YADB_Connection( $dbtype );
		if (!extension_loaded('mysqli')) {
			trigger_error( 'YADB: mysqli extension not available.', E_USER_WARNING );
			$this->_drvLoadErr = true;
		}
	}
	
	
	function serverVers() #DONE
	{
		// cache for faster execution:
		if (is_array($this->_drvSrvVersArr))
			return $this->_drvSrvVersArr;
		
		$orig = @ mysqli_get_server_info($this->_conn);
		$vstr = $this->_findVers($orig);
		$vint = @ mysqli_get_server_version($this->_conn);
		$vArr = array(
			'orig' => $orig,
			'vstr' => $vstr,
			'vint' => $vint
		);
		$this->_drvSrvVersArr = $vArr;
		return $vArr;
	}
	
	
	function clientVers() #DONE
	{
		$orig = @ mysqli_get_client_info($this->_conn);
		$vstr = $this->_findVers($orig);
		$vint = @ mysqli_get_client_version($this->_conn);
		return array(
			'orig' => $orig,
			'vstr' => $vstr,
			'vint' => $vint
		);
	}
	
	
	function hostInfo() #DONE
	{
		return @ mysqli_get_host_info( $this->_conn );
	}
	
	
	function protoVers() #DONE
	{
		return @ mysqli_get_proto_info( $this->_conn );
	}
	
	
	function _connect() #DONE
	{
		if (YADB_PHPVER < 50000) return false;
		
		$this->_conn = @ mysqli_init();
		if (is_null($this->_conn)) {
			// mysqli_init() only fails if insufficient memory
			trigger_error( 'YADB: mysqli_init() failed (insufficient memory).', E_USER_WARNING );
			return false;
		}
		
		if (empty($this->_socket)) {
			$host = $this->_host;
			$port = $this->_port;
			$sock = null;
		} else {
			$host = null;
			$port = null;
			$sock = $this->_socket;
		}
		
		$clientFlags = 0;
		if (@array_key_exists('ssl', $this->_drvOpts)
		&&  $this->_drvOpts['ssl'] )
		{
			$clientFlags += MYSQLI_CLIENT_SSL;
			mysqli_ssl_set( $this->_conn,
				$this->_drvOpts['ssl_key'   ],
				$this->_drvOpts['ssl_cert'  ],
				$this->_drvOpts['ssl_ca'    ],
				$this->_drvOpts['ssl_capath'],
				$this->_drvOpts['ssl_cipher']
			);
		}
		if (@array_key_exists('compress', $this->_drvOpts)
		&&  $this->_drvOpts['compress']
		&&  ! empty($host)
		&&  $host !== 'localhost'
		&&  $host !== '127.0.0.1'
		&&  empty($sock) )
		{
			$clientFlags += MYSQLI_CLIENT_COMPRESS;
		}
		
		mysqli_options($this->_conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
		
		if (! @ mysqli_real_connect( $this->_conn,
			$host,
			$this->_user,
			$this->_pwd,
			(!empty($this->_db) ? $this->_db : null),
			$port,
			$sock,
			$clientFlags ))
		{
			// try without SSL:
			$clientFlags -= MYSQLI_CLIENT_SSL;
			mysqli_ssl_set( $this->_conn, null,null,null,null,null );
			if (! @ mysqli_real_connect( $this->_conn,
				$host,
				$this->_user,
				$this->_pwd,
				(!empty($this->_db) ? $this->_db : null),
				$port,
				$sock,
				$clientFlags ))
			{
				return false;
			}
		}
		
		if (function_exists('mysqli_disable_rpl_parse'))
			@ mysqli_disable_rpl_parse($this->_conn);
		
		// get and store server version so we know capabilities:
		$this->serverVers();
		// stores version array in _drvSrvVersArr
		$this->_drvSrvVers = $this->_drvSrvVersArr['vint'];
		if ($this->_drvSrvVers < 40101)
			trigger_error( 'YADB: MySQL server version is '. $this->_drvSrvVers .', at least 4.1.1 is recommended.', E_USER_NOTICE );
		
		if (function_exists('mysqli_disable_rpl_parse'))
			@ mysqli_disable_rpl_parse($this->_conn);
		
		return true;
	}
	
	
	function _changeDb( $dbName ) #DONE
	{
		return (bool)@ mysqli_select_db( $this->_conn, $dbName );
	}
	
	
	function escape( $str ) #DONE
	{
		if (!get_magic_quotes_gpc()) {
			if (is_resource($this->_conn))
				return @ mysqli_real_escape_string( $this->_conn, $str );
			
			return @ mysqli_escape_string( $str );
			
			// the 3 lines in following do not seem to be needed (?)
			if ($this->replaceQuote[0]==='\\')
				$str = str_replace( array('\\',"\0"), array('\\\\',"\\\0"), $str );
			return str_replace( '\'', $this->replaceQuote, $str );
		}
		// undo magic quotes for "
		return str_replace('\\"','"',$str);
	}
	
	
	function getCharSet() #DONE
	{
		// do not use mysqli_character_set_name() or mysqli_get_charset()
		// as it does not seem to get updated if the connection charset
		// changes
		return @ $this->executeGetOne( 'SELECT @@character_set_connection' );
	}
	
	function getCollation() #DONE
	{
		// do not use mysqli_character_set_name() - see getCharSet()
		return @ $this->executeGetOne( 'SELECT @@collation_connection' );
	}
	
	/*
	function getMetaCharSet( $charset )
	{
		//FIXME
		static $meta = array(
			'latin1'     => 'cp-1252',
			'cp12..'     => 'cp12...',
			'ISO-8859-1' => 'cp12...',
			//FIXME
			// cp12... is a superset of ISO-8859-1. MySQL doesn't
			// distinguish between these two
			'utf8'       => 'UTF-8'
		);
		$charset = strToLower($charset);
		return (@ isSet($meta[$charset])) ? $meta[$charset] : null;
	}
	*/
	
	// $collation=null means default collation for the default
	// database (or default collation for the character set)
	function setCharSet( $charset, $collation=null ) #DONE
	{
		$ret = $this->_execute( 'SET NAMES \''. $this->escape($charset) .'\'' );
		if ($collation && $ret)
			$ret = $this->_execute( 'SET collation_connection=\''. $this->escape($collation) .'\'' );
		return $ret;
	}
	
	/*
	collations to use for various languages in charset utf8:
	german,    utf8_unicode_ci    "§" (szlig) = "ss",
	french                        "€" (auml) = A,
	                              "…" (auml) = O,
	                              "†" (auml) = U
	                              DIN-1, "dictionary collation"
	german,    utf8_general_ci    s.o., aber "§" (szlig) = "s"
	french                        und schneller
	
	
	http://dev.mysql.com/doc/refman/5.1/en/charset-unicode-sets.html
	*/
	
	
	
	function _close() #DONE
	{
		return is_resource($this->_conn) ? @ mysqli_close($this->_conn) : true;
	}
	
	
	
	function getSelectLimitSql( $sql, $nRows=-1, $offset=-1, $calcAll=false )
	{
		//FIXME
		$lim = ' LIMIT ';
		$lim .= ($offset >= 0) ? ((int)$offset .',') : '';
		$lim .= ($nRows >= 0) ? (int)$nRows : 0;
		
		/*
		SELECT SQL_CALC_FOUND_ROWS * FROM ...  tells MySQL to calculate how many rows there would be in the result set, disregarding any LIMIT clause. The number of rows can then be retrieved with SELECT FOUND_ROWS()
		http://dev.mysql.com/doc/refman/5.1/en/select.html
		http://dev.mysql.com/doc/refman/5.1/en/information-functions.html
		*/
		if ($calcAll && strToUpper(subStr($sql,0,7))==='SELECT ')
			$sql = 'SELECT SQL_CALC_FOUND_ROWS'. subStr($sql,6);
		return $sql . $lim;
	}
	
	
	// see getSelectLimitSql()
	function numFoundRows() #DONE
	{
		$ret = $this->executeGetOne( 'SELECT FOUND_ROWS()' );
		if ($ret === false || $ret < 0) return -1;
		return (int)$ret;
	}
	
	
	
	// do not even call this function internally except from
	// _execute()
	function _query( $sql, $inputArr=null ) #DONE
	{
		$rs = @ mysqli_query( $this->_conn, $sql, MYSQLI_STORE_RESULT );
		// returns a result resource for SELECT, SHOW, DESCRIBE,
		// EXPLAIN.
		// returns true for UPDATE, DELETE, DROP etc.
		// returns false on error.
		if (!$rs)
			trigger_error( 'YADB: Query failed: '. str_replace(array("\n","\t"), array('\n','\t'), $sql) .';', E_USER_WARNING );
		return $rs;
	}
	
	
	
	// warning: transactions in non-transaction-safe tables cannot
	// be rolled back. schema definitions cannot be rolled back,
	// even worse: they do an implicit COMMIT
	function _startTrans() #DONE
	{
		if ($this->_transOff > 0) return true; // we already have an outermost transaction
		
		++$this->_transCnt;
		
		$ret = @ mysqli_autocommit( $this->_conn, false );
		
		if ($this->_drvSrvVers >= 40005) {
			$this->_execute( 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE' );
			// http://dev.mysql.com/doc/refman/4.1/en/set-transaction.html
		}
		
		if ($this->_drvSrvVers >= 40011) {
			$ret = $this->_execute( 'START TRANSACTION /*!40108 WITH CONSISTENT SNAPSHOT */' ) && $ret;
			// http://dev.mysql.com/doc/refman/4.1/en/commit.html
			// comment will be executed on MySQl >= 4.1.8 only
		} else {
			$ret = $this->_execute( 'BEGIN' ) && $ret;
		}
		
		return $ret;
	}
	
	function _commitTrans() #DONE
	{
		if ($this->_transOff > 0) return true; // we have an outermost transaction
		
		if ($this->_transCnt > 0) --$this->_transCnt;
		// can we use mysqli_commit() ?
		$ret = $this->_execute( 'COMMIT /*!50003 AND NO CHAIN NO RELEASE */' );
		@ mysqli_autocommit( $this->_conn, true );
		if ($this->_drvSrvVers >= 40005)
			$this->_execute( 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ' );
		return $ret;
	}
	
	function _rollbackTrans() #DONE
	{
		if ($this->_transOff > 0) return true; // we have an outermost transaction
		
		if ($this->_transCnt > 0) --$this->_transCnt;
		// can we use mysqli_rollback() ?
		$ret = $this->_execute( 'ROLLBACK /*!50003 AND NO CHAIN NO RELEASE */' );
		/* "If you issue a ROLLBACK statement after updating a non-transactional table within a transaction, an ER_WARNING_NOT_COMPLETE_ROLLBACK warning occurs." */
		@ mysqli_autocommit( $this->_conn, true );
		if ($this->_drvSrvVers >= 40005)
			$this->_execute( 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ' );
		return $ret;
	}
	
	
	
	function & colsMeta( $table ) #TODO
	{
		$rs =& $this->_execute(sPrintF( $this->_metaColsSQL, $this->escape($table) ));
		$colsMeta = false;
		if (is_object($rs)) {
			//if (! $rs->EOF) {
			$colsMeta = array();
			$numPKCols = 0;
			while (! $rs->EOF) {
				$row = $rs->fetchRow();
				$col = array(
					'mty' => 0,  // main meta type
					'sty' => 0,  // meta sub type
					'flg' => 0,  // flags
					'len' => -1, // max. length unknown
					'dec' => 0,  // decimals (aka scale)
					'def' => null
				);
				$t = $row['Type'];
				
				// split type into "type(length)":
				if (preg_match("/^([^(]+)\((\d+)\)/", $t, $m)) {
					$col['nty'] = strToUpper($m[1]);
					$col['len'] = is_numeric($m[2]) ? (int)$m[2] : -1;
				} elseif (preg_match("/^([^(]+)\((\d+),(\d+)\)/", $t, $m)) {
					$col['nty'] = strToUpper($m[1]);
					$col['len'] = is_numeric($m[2]) ? (int)$m[2] : -1;
					$col['dec'] = is_numeric($m[3]) ? (int)$m[3] : -1;
				} elseif (preg_match("/^(enum)\(([^)]*)\)$/i", $t, $m)) {
					$col['nty'] = strToUpper($m[1]);
					$col['mty'] = YADB_MTYPE_STR;
					$col['sty'] = YADB_STYPE_ENUM;
					$col['len'] = max(array_map('strlen',explode(',',$m[2]))) - 2;
					//$col['len']  = ($col['len']==0 ? 1 : $col['len']);
				} elseif (preg_match("/^(set)\(([^)]*)\)$/i", $t, $m)) {
					$col['nty'] = strToUpper($m[1]);
					$col['mty'] = YADB_MTYPE_STR;
					$col['sty'] = YADB_STYPE_SET;
					$col['len'] = max(array_map('strlen',explode(',',$m[2]))) - 2;
					//$col['len']  = ($col['len']==0 ? 1 : $col['len']);
				} else {
					$col['nty'] = strToUpper($t);
					//$col['len'] = -1;
				}
				
				/*
				$col['notnull'] = ($row['Null'] !== 'YES');
				$col['pri']     = ($row['Key'] === 'PRI');
				$col['autoinc'] = (strPos($row['Extra'], 'auto_increment') !== false);
				$col['bin']     = (strPos($type, 'bin') !== false || strPos($type, 'blob') !== false);
				$col['unsig']   = (strPos($type, 'unsigned') !== false);
				*/
				if ($row['Null'] !== 'YES')
					$col['flg'] |= YADB_FLAG_NOTNULL;
				if ($row['Key'] === 'PRI') {
					$col['flg'] |= YADB_FLAG_PKPART
						| YADB_FLAG_PKCOL;
					// assume that the column is the only one which
					// the pri.key consists of. if not revoke later
					++$numPKCols;
				}
				if (strPos($row['Extra'], 'auto_increment') !== false)
					$col['flg'] |= YADB_FLAG_AUTOINC;
				if (strPos($t, 'unsigned') !== false)
					$col['flg'] |= YADB_FLAG_UNSIGNED;
				elseif (strPos($t, 'bin' ) !== false
					|| strPos($t, 'blob') !== false)
					$col['flg'] |= YADB_FLAG_BINARY;
				
				$col['def'] = ($row['Default']=='' || $row['Default']==='NULL') ? '' : $row['Default'];
				if (!($col['flg'] & YADB_FLAG_NOTNULL) && $col['def']=='')
					$col['def'] = null;
				
				$mt = $this->_colMetaType( $col );
				$col['mty'] = $mt[0];
				// correct sub type for varchar/varbinary if
				// necessary:
				if (($col['nty']==='VARCHAR'
					|| $col['nty']==='VARBINARY')
					&& $col['len'] > 255)
					$col['sty'] = YADB_STYPE_STR_2;
				else
					$col['sty'] = $mt[1];
				
				// correct default value according to type:
				switch ($col['mty']) {
					case YADB_MTYPE_INT:
						$col['def'] =    (int)$col['def']; break;
					case YADB_MTYPE_FLOAT:
						$col['def'] = (double)$col['def']; break;
					case YADB_MTYPE_BOOL:
						$col['def'] =   (bool)$col['def']; break;
					// no need to cast string to string
				}
				
				unset( $col['nty'] );  // native type no longer needed
				$colsMeta[$row['Field']] = $col;
			}
			if ($numPKCols > 1) {
				// pri.key consists of more than one column
				foreach ($colsMeta as $col => $colMeta)
					if ($colMeta['flg'] & YADB_FLAG_PKCOL)
						$colMeta['flg'] &~ YADB_FLAG_PKCOL;
			}
			//}
			$rs->close();
		}
		return $colsMeta;
	}
	
	
	function _colMetaType( &$colMeta ) #DONE?
	{
		static $tMap = array(
			'INT'       => array(YADB_MTYPE_INT, YADB_STYPE_INT_4),
			'INTEGER'   => array(YADB_MTYPE_INT, YADB_STYPE_INT_4),
			'TINYINT'   => array(YADB_MTYPE_INT, YADB_STYPE_INT_1),
			'SMALLINT'  => array(YADB_MTYPE_INT, YADB_STYPE_INT_2),
			'MEDIUMINT' => array(YADB_MTYPE_INT, YADB_STYPE_INT_3),
			'BIGINT'    => array(YADB_MTYPE_INT, YADB_STYPE_INT_8),
			'YEAR'      => array(YADB_MTYPE_INT, YADB_STYPE_YEAR),
			'BIT'       => array(YADB_MTYPE_INT, YADB_STYPE_BITS),
			##
			'STRING'    => array(YADB_MTYPE_STR, YADB_STYPE_STR_1),
			'CHAR'      => array(YADB_MTYPE_STR, YADB_STYPE_STR_1),
			'VARCHAR'   => array(YADB_MTYPE_STR, YADB_STYPE_STR_1),
			'TINYTEXT'  => array(YADB_MTYPE_STR, YADB_STYPE_STR_1),
			'MEDIUMTEXT'=> array(YADB_MTYPE_STR, YADB_STYPE_STR_3),
			'TEXT'      => array(YADB_MTYPE_STR, YADB_STYPE_STR_2),
			'LONGTEXT'  => array(YADB_MTYPE_STR, YADB_STYPE_STR_4),
			'ENUM'      => array(YADB_MTYPE_STR, YADB_STYPE_ENUM),
			'SET'       => array(YADB_MTYPE_STR, YADB_STYPE_SET),
			'DATE'      => array(YADB_MTYPE_STR, YADB_STYPE_DATE),
			'DATETIME'  => array(YADB_MTYPE_STR, YADB_STYPE_DATETIME),
			'TIME'      => array(YADB_MTYPE_STR, YADB_STYPE_TIME),
			'TIMESTAMP' => array(YADB_MTYPE_STR, YADB_STYPE_TSTAMP),
			##
			'DECIMAL'   => array(YADB_MTYPE_FLOAT, YADB_STYPE_FLOAT_DEC),
			'FLOAT'     => array(YADB_MTYPE_FLOAT, YADB_STYPE_FLOAT_4),
			'DOUBLE'    => array(YADB_MTYPE_FLOAT, YADB_STYPE_FLOAT_8),
			##
			'BINARY'    => array(YADB_MTYPE_STR, YADB_STYPE_STR_1),
			'VARBINARY' => array(YADB_MTYPE_STR, YADB_STYPE_STR_1),
			'TINYBLOB'  => array(YADB_MTYPE_STR, YADB_STYPE_STR_1),
			'MEDIUMBLOB'=> array(YADB_MTYPE_STR, YADB_STYPE_STR_3),
			'BLOB'      => array(YADB_MTYPE_STR, YADB_STYPE_STR_2),
			'LONGBLOB'  => array(YADB_MTYPE_STR, YADB_STYPE_STR_4),
			'GEOMETRIC' => array(YADB_MTYPE_STR, YADB_STYPE_GEO),
			//FIXME ???
			##
			'BOOL'      => array(YADB_MTYPE_BOOL, YADB_STYPE_BOOL),
			'BOOLEAN'   => array(YADB_MTYPE_BOOL, YADB_STYPE_BOOL)
		);
		
		//if (!is_array($colMeta)) return false;
		
		$t = strToUpper(@ $colMeta['nty']);  // native type
		//$metaType = @ isSet($tMap[$sqlType])
		//	? $tMap[$sqlType] : array(0,0);
		//$colMeta['mty'] = $metaType[0];
		//$colMeta['sty'] = $metaType[1];
		
		return ( @ isSet($tMap[$t]) ? $tMap[$t] : array(0,0) );
		
		//if ($metaType==='I' && $colMeta['pri'])
		//	$metaType = $colMeta['autoinc'] ? 'R' : 'P';
		//if ($metaType==='C' && $colMeta['bin'])
		//	$metaType = 'X';
		
		// correct sub type for varchar/varbinary
		
		//return $metaType;
	}
	
	
	
	/*
	function createSequence( $seqName, $startId=1 ) #TODO
	{
		// See http://www.mysql.com/doc/M/i/Miscellaneous_functions.html
		// Reference on Last_Insert_ID on the recommended way to simulate sequences
		$seqTbl = 'yadb_seq';
		$seqName = strToLower(trim($seqName));
		
		$this->_execute( 'CREATE TABLE `'. $seqTbl .'` (`seq` CHAR(50) BINARY, `id` INT NOT NULL, PRIMARY KEY (`seq`)) Type=InnoDB' );
		// we prefer InnoDB here but if MySQL was compiled without
		// it we'll get a MyISAM table.
		return $this->_execute( 'INSERT INTO `'. $seqTbl .'` VALUES (\''. $this->escape($seqName) .'\', '. (string)((int)$startId-1) .')' );
	}
	
	function dropSequence( $seqName ) #TODO
	{
		$seqTbl = 'yadb_seq';
		$seqName = strToLower(trim($seqName));
		return $this->_execute( 'DELETE FROM `'. $seqTbl .'` WHERE `seq`=\''. $this->escape($seqName) .'\'' );
	}
	
	function genSequenceId( $seqName, $startId=1 ) #TODO
	{
		$seqTbl = 'yadb_seq';
		$seqName = strToLower(trim($seqName));
		$getNext = 'UPDATE `'. $seqTbl .'` SET `id`=LAST_INSERT_ID(`id`+1) WHERE `seq`=\''. $this->escape($seqName) .'\'';
		$rs = $this->_execute( $getNext );
		$affRows = (int)@ mysql_affected_rows( $this->_conn );
		if ( ! $rs || $affRows < 1 ) {
			$this->createSequence( $seqName, $startId );
			$rs = @ $this->_execute( $getNext );
		}
		$nextId = @ mysql_insert_id( $this->_conn );
		/*
		Note that mysql_insert_id() is not the same as the MySQL
		function LAST_INSERT_ID(). The first returns the value that
		was inserted into the primary key column by the last query,
		be it auto-incremented or set in the query to a non-magic
		value. The latter always returns the last generated auto-
		increment value; LAST_INSERT_ID() does not change if you
		use a non-magical value (not NULL or 0).
		Anyway, it wouldn't matter in this case.
		*//*!
		if (is_object($rs)) $rs->close();
		return $nextId;
	}
	*/
	
	
	function getLastInsertId() #DONE
	{
		return @ mysqli_insert_id( $this->_conn );
	}
	
	
	function __toString()
	{
		return '['.get_class($this).']';
	}
	
}









class YADB_RecordSet_mysqli extends YADB_RecordSet
{
	var $_canSeek   = true;
	var $_drvColTypesPHP = null;
	
	function _numRows() #DONE
	{
		$n = @ mysqli_num_rows( $this->_rs );
		return ($n !== false && $n >= 0) ? $n : -1;
	}
	
	function _numCols() #DONE
	{
		return @ mysqli_num_fields( $this->_rs );
	}
	
	function _fetchRow() #DONE
	{
		$row = @ mysqli_fetch_assoc( $this->_rs );
		if (!is_array($row)) {
			$this->_row = null;
			$this->EOF = true;
			return false;
		}
		// as MySQL returns all values as strings we need to
		// correct fields types:
		if (is_array($this->_drvColTypesPHP)
		||  @ $this->_drvFetchColTypes()) {
			// if col types already cached or able to get them:
			// correct types, else leave all values as strings
			$i=0;
			foreach ($row as $col => $val) {
				if ($row[$col] !== null) {
					//$t = @$this->_drvColTypesPHP[$col];
					$t = @$this->_drvColTypesPHP[$i];
					switch ($t) {
						case YADB_MTYPE_INT:
							$row[$col] =    (int)$row[$col];  break;
						case YADB_MTYPE_STR:                  break;  // is a string already
						case YADB_MTYPE_FLOAT:
							$row[$col] = (double)$row[$col];  break;
						case YADB_MTYPE_BOOL:
							$row[$col] =   (bool)$row[$col];  break;
					}
				}
				++$i;
			}
		}
		$this->_row =& $row;
		return true;
	}
	
	// exactly the same as YADB_RecordSet->moveNext() but possible
	// speedup to move this to the child class?
	function moveNext() #DONE
	{
		if (!$this->EOF) {
			++$this->_rowPos;
			if ($this->_fetchRow()) return true;
		}
		return false;
	}
	
	function _move( $rowNum ) #DONE
	{
		return @ mysqli_data_seek( $this->_rs, $rowNum );
	}
	
	function _close() #DONE
	{
		if (is_resource( $this->_rs ))
			@ mysqli_free_result( $this->_rs );
		$this->_rs = null;
		return true;
	}
	
	
	// this is needed for _fetchRow() to cast values to correct
	// PHP type as MySQL returns all values as strings.
	// stores types in _drvColTypesPHP
	function _drvFetchColTypes() #DONE
	{
		static $mysqli_types_to_yadb_mtypes = array
		(
			// common types first
			
			  1 =>  YADB_MTYPE_INT,   # TINYINT
			  2 =>  YADB_MTYPE_INT,   # SMALLINT
			  9 =>  YADB_MTYPE_INT,   # MEDIUMINT
			  3 =>  YADB_MTYPE_INT,   # INTEGER
			  8 =>  YADB_MTYPE_INT,   # BIGINT - too big for PHP!
			
			253 =>  YADB_MTYPE_STR,   # VARCHAR
			254 =>  YADB_MTYPE_STR,   # CHAR
			
			 10 =>  YADB_MTYPE_STR,   # DATE
			 14 =>  YADB_MTYPE_STR,   # DATE
			 12 =>  YADB_MTYPE_STR,   # DATETIME
			 11 =>  YADB_MTYPE_STR,   # TIME
			  7 =>  YADB_MTYPE_STR,   # TIMESTAMP
			
			  4 =>  YADB_MTYPE_FLOAT, # FLOAT
			  5 =>  YADB_MTYPE_FLOAT, # DOUBLE
			  0 =>  YADB_MTYPE_FLOAT, # DECIMAL
			246 =>  YADB_MTYPE_FLOAT, # DECIMAL
			
			249 =>  YADB_MTYPE_STR,   # TINYBLOB
			250 =>  YADB_MTYPE_STR,   # MEDIUMBLOB
			252 =>  YADB_MTYPE_STR,   # BLOB
			251 =>  YADB_MTYPE_STR,   # LONGBLOB
			
			247 =>  YADB_MTYPE_STR,   # ENUM
			248 =>  YADB_MTYPE_STR,   # SET
			
			 16 =>  YADB_MTYPE_INT,   # BIT - multiple bits!
			 13 =>  YADB_MTYPE_INT,   # YEAR
			
			255 =>  YADB_MTYPE_STR    # GEOMETRY - complex type!
		);
		
		$types = array();
		$fldObjs = @ mysqli_fetch_fields( $this->_rs );
		$i=0;
		foreach ($fldObjs as $fldObj) {
			if (!is_object($fldObj)) {
				trigger_error( 'YADB: Could not get column type.', E_USER_WARNING );
				return false;
			}
			$t = (int)$fldObj->type;
			// determine corresponding PHP type:
			// (integer, string, double, boolean)
			if (array_key_exists($t, $mysqli_types_to_yadb_mtypes)) {
				//$types[$fldObj->name] = $mysqli_types_to_yadb_mtypes[$t];
				$types[$i] = $mysqli_types_to_yadb_mtypes[$t];
			} else {
				// should not be necessary, but you never know
				//$types[$fldObj->name] = $fldObj->numeric ?
				//	YADB_MTYPE_FLOAT : YADB_MTYPE_STR;
				//$types[$i] = $fldObj->numeric ?
				//	YADB_MTYPE_FLOAT : YADB_MTYPE_STR;
				//FIXME - with mysqli the field metadata object does not
				//have a "numeric" property. the necessary information
				//might be in "flags"
				//for now:
				$types[$i] = YADB_MTYPE_STR;
			}
			++$i;
		}
		$this->_drvColTypesPHP =& $types;
		return true;
	}
	
	
	function __toString()
	{
		return '['.get_class($this).']';
	}
	
}



?>