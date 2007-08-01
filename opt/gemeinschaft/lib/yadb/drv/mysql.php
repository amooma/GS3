<?php

/***********************************************************
* Requires PHP mysql extension!
* 
* Supported driver options for connect():
* reuse = true,  to re-use an existing connection
***********************************************************/

/*
* Copyright 2006/2007, Philipp Kempgen <philipp.kempgen@amooma.de>,
* amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/;
* 
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public License
* (GNU/LGPL) as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Lesser General Public License for more details.
* 
* You should have received a copy of the GNU Lesser General Public
* License along with this program; if not, write to the Free
* Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
* Boston, MA 02110-1301, USA.
*/

if (!defined('YADB_DIR')) die();

@ ini_set( 'mysql.trace_mode', 0 );
// must be off or else SELECT FOUND_ROWS() always returns 0


class YADB_Connection_mysql extends YADB_Connection
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
	
	
	function YADB_Connection_mysql( $dbtype )
	{
		parent::__construct( $dbtype );
		if (!extension_loaded('mysql')) {
			trigger_error( 'YADB: mysql extension not available.', E_USER_WARNING );
			$this->_drvLoadErr = true;
		}
	}
	
	
	function serverVers()
	{
		// cache for faster execution:
		if (is_array($this->_drvSrvVersArr))
			return $this->_drvSrvVersArr;
		
		$orig = @ mysql_get_server_info($this->_conn);
			$vstr = $this->_findVers($orig);
			$tmp = explode('.', $vstr, 3);
			$vint = (int)@$tmp[0]*10000 + (int)@$tmp[1]*100 + (int)@$tmp[2];
		$vArr = array(
			'orig' => $orig,
			'vstr' => $vstr,
			'vint' => $vint
		);
		$this->_drvSrvVersArr = $vArr;
		return $vArr;
	}
	
	
	function clientVers()
	{
		$orig = @ mysql_get_client_info();
		$vstr = $this->_findVers($orig);
		$tmp = explode('.', $vstr, 3);
		$vint = (int)@$tmp[0]*10000 + (int)@$tmp[1]*100 + (int)@$tmp[2];
		return array(
			'orig' => $orig,
			'vstr' => $vstr,
			'vint' => $vint
		);
	}
	
	
	function hostInfo()
	{
		return @ mysql_get_host_info( $this->_conn );
	}
	
	
	function protoVers()
	{
		return @ mysql_get_proto_info( $this->_conn );
	}
	
	
	function _connect()
	{
		if (empty($this->_socket)) {
			$host = $this->_host;
			if (!empty($this->_port))  $host .= ':'. $this->_port;
		} else
			$host = ':'. $this->_socket;
		
		if (YADB_PHPVER >= 40300) {
			$clientFlags = 0;
			// no connection compression for localhost:
			/*
			if (
				$this->_host != null &&
				$this->_host != '' &&
				$this->_host != 'localhost'
			) $clientFlags += MYSQL_CLIENT_COMPRESS;
			*/
			
			if (! @$this->_drvOpts['reuse'])
				$this->_conn = @ mysql_connect( $host, $this->_user, $this->_pwd, true, $clientFlags );
			// always force a new connection because we might
			// experience unexpected behaviour if we re-used
			// an existing one - especially with transactions
			// or different character sets
			else
				$this->_conn = @ mysql_pConnect( $host, $this->_user, $this->_pwd, $clientFlags );
			
		} elseif (YADB_PHPVER >= 40200) {
			if (! @$this->_drvOpts['reuse'])
				$this->_conn = @ mysql_connect( $host, $this->_user, $this->_pwd, true );
			else
				$this->_conn = @ mysql_pConnect( $host, $this->_user, $this->_pwd );
		
		} else {
			if (! @$this->_drvOpts['reuse'])
				$this->_conn = @ mysql_connect( $host, $this->_user, $this->_pwd );
			else
				$this->_conn = @ mysql_pConnect( $host, $this->_user, $this->_pwd );
		}
		
		if (! $this->_conn) return false;
		
		if (!empty($this->_db))  // select db if necessary:
			if (! $this->changeDb($this->_db)) return false;
		
		// get and store server version so we know capabilities:
		$this->serverVers();
		// stores version array in _drvSrvVersArr
		$this->_drvSrvVers = $this->_drvSrvVersArr['vint'];
		if ($this->_drvSrvVers < 40100)
			trigger_error( 'YADB: MySQL server version is '. $this->_drvSrvVers .', at least 4.1 is recommended.', E_USER_NOTICE );
		
		return true;
	}
	
	
	function _changeDb( $dbName )
	{
		return (bool)@ mysql_select_db( $dbName, $this->_conn );
	}
	
	
	function escape( $str )
	{
		if (!get_magic_quotes_gpc()) {
			if (is_resource($this->_conn)) {
				if (YADB_PHPVER >= 40300)
					return @ mysql_real_escape_string( $str, $this->_conn );
			}
			return @ mysql_escape_string( $str );
			
			// the 3 lines in following do not seem to be needed (?)
			if ($this->replaceQuote[0]=='\\')
				$str = str_replace( array('\\',"\0"), array('\\\\',"\\\0"), $str );
			return str_replace( '\'', $this->replaceQuote, $str );
		}
		// undo magic quotes for "
		return str_replace('\\"','"',$str);
	}
	
	
	function getCharSet()
	{
		// do not use mysql_client_encoding() (PHP 4.3) as it
		// does not seems to get updated if the connection
		// charset changes
		return @ $this->executeGetOne( 'SELECT @@character_set_connection' );
	}
	
	function getCollation()
	{
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
	function setCharSet( $charset, $collation=null )
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
	
	
	
	function _close()
	{
		return @ mysql_close($this->_conn);
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
		if ($calcAll && strToUpper(subStr($sql,0,7))=='SELECT ')
			$sql = 'SELECT SQL_CALC_FOUND_ROWS'. subStr($sql,6);
		return $sql . $lim;
	}
	
	
	// see getSelectLimitSql()
	function numFoundRows()
	{
		$ret = $this->executeGetOne( 'SELECT FOUND_ROWS()' );
		if ($ret === false || $ret < 0) return -1;
		return (int)$ret;
	}
	
	
	
	// do not even call this function internally except from
	// _execute()
	function _query( $sql, $inputArr=null )
	{
		$rs = @ mysql_query( $sql, $this->_conn );
		// returns a result resource for SELECT, SHOW, DESCRIBE,
		// EXPLAIN.
		// returns true for UPDATE, DELETE, DROP etc.
		// returns false on error.
		if (!$rs)
			trigger_error( 'YADB: Query failed.', E_USER_WARNING );
		return $rs;
	}
	
	
	
	// warning: transactions in non-transaction-safe tables cannot
	// be rolled back. schema definitions cannot be rolled back,
	// even worse: they do an implicit COMMIT
	function _startTrans()
	{
		if ($this->_transOff > 0) return true; // we already have an outermost transaction
		++$this->_transCnt;
		$ret = $this->_execute( 'SET autocommit=0' );
		if ($this->_drvSrvVers >= 40005)
			$ret = $this->_execute( 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE' ) && $ret;
			// http://dev.mysql.com/doc/refman/4.1/en/set-transaction.html
		/*
		if ($this->_drvSrvVers >= 40108)
			$ret = $this->_execute( 'START TRANSACTION WITH CONSISTENT SNAPSHOT' ) && $ret;
			// http://dev.mysql.com/doc/refman/4.1/en/commit.html
		elseif ($this->_drvSrvVers >= 40011)
			$ret = $this->_execute( 'START TRANSACTION' ) && $ret;
		*/
		if ($this->_drvSrvVers >= 40011)
			$ret = $this->_execute( 'START TRANSACTION /*!40108 WITH CONSISTENT SNAPSHOT */' ) && $ret;
			// "comment" will be executed on MySQl >= 4.1.8 only
		else
			$ret = $this->_execute( 'BEGIN' ) && $ret;
		return $ret;
	}
	
	function _commitTrans()
	{
		if ($this->_transOff > 0) return true; // we have an outermost transaction
		if ($this->_transCnt > 0) --$this->_transCnt;
		$ret = $this->_execute( 'COMMIT' );
		$this->_execute( 'SET autocommit=1' );
		if ($this->_drvSrvVers >= 40005)
			$this->_execute( 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ' );
		return $ret;
	}
	
	function _rollbackTrans()
	{
		if ($this->_transOff > 0) return true; // we have an outermost transaction
		if ($this->_transCnt > 0) --$this->_transCnt;
		$ret = $this->_execute( 'ROLLBACK' );
		/* "If you issue a ROLLBACK statement after updating a non-transactional table within a transaction, an ER_WARNING_NOT_COMPLETE_ROLLBACK warning occurs." */
		$this->_execute( 'SET autocommit=1' );
		if ($this->_drvSrvVers >= 40005)
			$this->_execute( 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ' );
		return $ret;
	}
	
	
	
	function & colsMeta( $table )
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
				$col['notnull'] = ($row['Null'] != 'YES');
				$col['pri']     = ($row['Key'] == 'PRI');
				$col['autoinc'] = (strPos($row['Extra'], 'auto_increment') !== false);
				$col['bin']     = (strPos($type, 'bin') !== false || strPos($type, 'blob') !== false);
				$col['unsig']   = (strPos($type, 'unsigned') !== false);
				*/
				if ($row['Null'] != 'YES')
					$col['flg'] |= YADB_FLAG_NOTNULL;
				if ($row['Key'] == 'PRI') {
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
				
				$col['def'] = ($row['Default']=='' || $row['Default']=='NULL') ? '' : $row['Default'];
				if (!($col['flg'] & YADB_FLAG_NOTNULL) && $col['def']=='')
					$col['def'] = null;
				
				$mt = $this->_colMetaType( $col );
				$col['mty'] = $mt[0];
				// correct sub type for varchar/varbinary if
				// necessary:
				if (($col['nty']=='VARCHAR'
					|| $col['nty']=='VARBINARY')
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
	
	
	function _colMetaType( &$colMeta )
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
		
		//if ($metaType=='I' && $colMeta['pri'])
		//	$metaType = $colMeta['autoinc'] ? 'R' : 'P';
		//if ($metaType=='C' && $colMeta['bin'])
		//	$metaType = 'X';
		
		// correct sub type for varchar/varbinary
		
		//return $metaType;
	}
	
	
	
	/*
	function createSequence( $seqName, $startId=1 )
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
	
	function dropSequence( $seqName )
	{
		$seqTbl = 'yadb_seq';
		$seqName = strToLower(trim($seqName));
		return $this->_execute( 'DELETE FROM `'. $seqTbl .'` WHERE `seq`=\''. $this->escape($seqName) .'\'' );
	}
	
	function genSequenceId( $seqName, $startId=1 )
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
	
	
	function getLastInsertId()
	{
		return @ mysql_insert_id( $this->_conn );
	}
	
	
}









class YADB_RecordSet_mysql extends YADB_RecordSet
{
	var $_canSeek   = true;
	var $_drvColTypesPHP = null;
	
	function _numRows()
	{
		$n = @ mysql_num_rows( $this->_rs );
		return ($n !== false && $n >= 0) ? $n : -1;
	}
	
	function _numCols()
	{
		return @ mysql_num_fields( $this->_rs );
	}
	
	function _fetchRow()
	{
		$row = @ mysql_fetch_assoc( $this->_rs );
		if (!is_array($row)) {
			$this->_row = null;
			$this->EOF = true;
			return false;
		}
		// as MySQL returns all values as strings we need to
		// correct fields types:
		if ( is_array($this->_drvColTypesPHP)
			|| @ $this->_drvFetchColTypes() ) {
			// if col types already cached or able to get them:
			// correct types, else leave all values as strings
			foreach ($row as $col => $val) {
				$t = $this->_drvColTypesPHP[$col];
				switch ($t) {
					case YADB_MTYPE_INT:
						$row[$col] = (int)$row[$col];  break;
					case YADB_MTYPE_STR:  break;  // already is a string
					case YADB_MTYPE_FLOAT:
						$row[$col] = (double)$row[$col];  break;
					case YADB_MTYPE_BOOL:
						$row[$col] = (bool)$row[$col];  break;
				}
			}
		}
		$this->_row =& $row;
		return true;
	}
	
	// exactly the same as YADB_RecordSet->moveNext() but possible
	// speedup to move this to the child class?
	function moveNext()
	{
		if (!$this->EOF) {
			++$this->_rowPos;
			if ($this->_fetchRow()) return true;
		}
		return false;
	}
	
	function _move( $rowNum )
	{
		return @ mysql_data_seek( $this->_rs, $rowNum );
	}
	
	function _close()
	{
		@ mysql_free_result( $this->_rs );
		$this->_rs = null;
		return true;
	}
	
	
	// this is needed for _fetchRow() to cast values to correct
	// PHP type as MySQL returns all values as strings.
	// stores types in _drvColTypesPHP
	function _drvFetchColTypes()
	{
		$types = array();
		for ($i=0; $i<$this->_numCols; ++$i) {
			$fldObj = @ mysql_fetch_field( $this->_rs, $i );
			if (!is_object($fldObj)) {
				trigger_error( 'YADB: Could not get column type.', E_USER_WARNING );
				return false;
			}
			$t = $fldObj->type;
			// 'int'|'string'|'blob'|'real'|'date'|'timestamp'
			// |'datetime'|'time'|'year' ...
			// determine corresponding PHP type:
			// (integer, string, double, boolean)
			// boolean will never happen as MySQL (PHP mysql ext.)
			// does not have a boolean type. (MySQL >= 4.1 (?)
			// has a BIT type which can hold multiple bits but
			// reports it as an int which is acceptable
			switch ($t) {  // common types first:
				case 'int':
					$types[$fldObj->name] = YADB_MTYPE_INT; break;
				case 'string':
				case 'date':
					$types[$fldObj->name] = YADB_MTYPE_STR; break;
				case 'real':
					$types[$fldObj->name] = YADB_MTYPE_FLOAT; break;
				case 'date':
				case 'datetime':
					$types[$fldObj->name] = YADB_MTYPE_STR; break;
				case 'year':
				case 'timestamp':
					$types[$fldObj->name] = YADB_MTYPE_INT; break;
				default:
					// should not be necessary, but you never know
					$types[$fldObj->name] = $fldObj->numeric ?
						YADB_MTYPE_FLOAT : YADB_MTYPE_STR;
					break;
			}
		}
		$this->_drvColTypesPHP =& $types;
		return true;
	}
	
	
	
}



?>