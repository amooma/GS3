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
* Requires PHP mysql extension!
* 
* Supported driver options for connect():
* reuse = true,  to re-use an existing connection
***********************************************************/

if (!defined('YADB_DIR')) die("No direct access\n");

ini_set('mysql.trace_mode', 0);
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
		//parent::__construct( $dbtype );
		parent::YADB_Connection( $dbtype );
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
		ini_set('mysql.connect_timeout',
			(array_key_exists('timeout', $this->_drvOpts) ? $this->_drvOpts['timeout'] : 10) );
		
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
				$this->_host !== null &&
				$this->_host !== '' &&
				$this->_host !== 'localhost'
			) $clientFlags += MYSQL_CLIENT_COMPRESS;
			*/
			
			if (! @$this->_drvOpts['reuse']) {
				$this->_conn = @ mysql_connect( $host, $this->_user, $this->_pwd, true, $clientFlags );
				// always force a new connection because we might
				// experience unexpected behaviour if we re-used
				// an existing one - especially with transactions
				// or different character sets
			} else {
				$this->_conn = @ mysql_pConnect( $host, $this->_user, $this->_pwd, $clientFlags );
			}
			
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
			if ($this->replaceQuote[0]==='\\')
				$str = str_replace( array('\\',"\0"), array('\\\\',"\\\0"), $str );
			return str_replace( '\'', $this->replaceQuote, $str );
		}
		// undo magic quotes for "
		return str_replace('\\"','"',$str);
	}
	
	
	function getCharSet()
	{
		// do not use mysql_client_encoding() (PHP 4.3) as it does
		// not seem to get updated if the connection charset changes
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
		return is_resource($this->_conn) ? @ mysql_close($this->_conn) : true;
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
		if ($calcAll && strToUpper(subStr($sql,0,6))==='SELECT')
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
		if (!$rs) {
			trigger_error( 'YADB: Query failed: '. str_replace(array("\n","\t"), array('\n','\t'), $sql) .';', E_USER_WARNING );
		}
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
		
		if ($this->_drvSrvVers >= 40005) {
			$ret = $this->_execute( 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE' ) && $ret;
			// http://dev.mysql.com/doc/refman/4.1/en/set-transaction.html
		}
		
		/*
		if ($this->_drvSrvVers >= 40108) {
			$ret = $this->_execute( 'START TRANSACTION WITH CONSISTENT SNAPSHOT' ) && $ret;
			// http://dev.mysql.com/doc/refman/4.1/en/commit.html
		} elseif ($this->_drvSrvVers >= 40011) {
			$ret = $this->_execute( 'START TRANSACTION' ) && $ret;
		}
		*/
		if ($this->_drvSrvVers >= 40011) {
			$ret = $this->_execute( 'START TRANSACTION /*!40108 WITH CONSISTENT SNAPSHOT */' ) && $ret;
			// comment will be executed on MySQl >= 4.1.8 only
		} else {
			$ret = $this->_execute( 'BEGIN' ) && $ret;
		}
		
		return $ret;
	}
	
	function _commitTrans()
	{
		if ($this->_transOff > 0) return true; // we have an outermost transaction
		
		if ($this->_transCnt > 0) --$this->_transCnt;
		$ret = $this->_execute( 'COMMIT /*!50003 AND NO CHAIN NO RELEASE */' );
		$this->_execute( 'SET autocommit=1' );
		if ($this->_drvSrvVers >= 40005)
			$this->_execute( 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ' );
		return $ret;
	}
	
	function _rollbackTrans()
	{
		if ($this->_transOff > 0) return true; // we have an outermost transaction
		
		if ($this->_transCnt > 0) --$this->_transCnt;
		$ret = $this->_execute( 'ROLLBACK /*!50003 AND NO CHAIN NO RELEASE */' );
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
		
		//if ($metaType==='I' && $colMeta['pri'])
		//	$metaType = $colMeta['autoinc'] ? 'R' : 'P';
		//if ($metaType==='C' && $colMeta['bin'])
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
	
	
	function getLastNativeError()
	{
		return $this->_conn ? @mysql_errno( $this->_conn ) : @mysql_errno();
	}
	
	function getLastNativeErrorMsg()
	{
		return $this->_conn ? @mysql_error( $this->_conn ) : @mysql_error();
	}
	
	function _errInfo( $err_code )
	{
		# http://dev.mysql.com/doc/refman/5.0/en/error-messages-server.html
		switch ($err_code) {
		case 1000: return array('HY000', 'ER_HASHCHK');
		case 1001: return array('HY000', 'ER_NISAMCHK');
		case 1002: return array('HY000', 'ER_NO');
		case 1003: return array('HY000', 'ER_YES');
		case 1004: return array('HY000', 'ER_CANT_CREATE_FILE');
		case 1005: return array('HY000', 'ER_CANT_CREATE_TABLE');
		case 1006: return array('HY000', 'ER_CANT_CREATE_DB');
		case 1007: return array('HY000', 'ER_DB_CREATE_EXISTS');
		case 1008: return array('HY000', 'ER_DB_DROP_EXISTS');
		case 1009: return array('HY000', 'ER_DB_DROP_DELETE');
		case 1010: return array('HY000', 'ER_DB_DROP_RMDIR');
		case 1011: return array('HY000', 'ER_CANT_DELETE_FILE');
		case 1012: return array('HY000', 'ER_CANT_FIND_SYSTEM_REC');
		case 1013: return array('HY000', 'ER_CANT_GET_STAT');
		case 1014: return array('HY000', 'ER_CANT_GET_WD');
		case 1015: return array('HY000', 'ER_CANT_LOCK');
		case 1016: return array('HY000', 'ER_CANT_OPEN_FILE');
		case 1017: return array('HY000', 'ER_FILE_NOT_FOUND');
		case 1018: return array('HY000', 'ER_CANT_READ_DIR');
		case 1019: return array('HY000', 'ER_CANT_SET_WD');
		case 1020: return array('HY000', 'ER_CHECKREAD');
		case 1021: return array('HY000', 'ER_DISK_FULL');
		case 1022: return array('23000', 'ER_DUP_KEY');
		case 1023: return array('HY000', 'ER_ERROR_ON_CLOSE');
		case 1024: return array('HY000', 'ER_ERROR_ON_READ');
		case 1025: return array('HY000', 'ER_ERROR_ON_RENAME');
		case 1026: return array('HY000', 'ER_ERROR_ON_WRITE');
		case 1027: return array('HY000', 'ER_FILE_USED');
		case 1028: return array('HY000', 'ER_FILSORT_ABORT');
		case 1029: return array('HY000', 'ER_FORM_NOT_FOUND');
		case 1030: return array('HY000', 'ER_GET_ERRNO');
		case 1031: return array('HY000', 'ER_ILLEGAL_HA');
		case 1032: return array('HY000', 'ER_KEY_NOT_FOUND');
		case 1033: return array('HY000', 'ER_NOT_FORM_FILE');
		case 1034: return array('HY000', 'ER_NOT_KEYFILE');
		case 1035: return array('HY000', 'ER_OLD_KEYFILE');
		case 1036: return array('HY000', 'ER_OPEN_AS_READONLY');
		case 1037: return array('HY001', 'ER_OUTOFMEMORY');
		case 1038: return array('HY001', 'ER_OUT_OF_SORTMEMORY');
		case 1039: return array('HY000', 'ER_UNEXPECTED_EOF');
		case 1040: return array('08004', 'ER_CON_COUNT_ERROR');
		case 1041: return array('HY000', 'ER_OUT_OF_RESOURCES');
		case 1042: return array('08S01', 'ER_BAD_HOST_ERROR');
		case 1043: return array('08S01', 'ER_HANDSHAKE_ERROR');
		case 1044: return array('42000', 'ER_DBACCESS_DENIED_ERROR');
		case 1045: return array('28000', 'ER_ACCESS_DENIED_ERROR');
		case 1046: return array('3D000', 'ER_NO_DB_ERROR');
		case 1047: return array('08S01', 'ER_UNKNOWN_COM_ERROR');
		case 1048: return array('23000', 'ER_BAD_NULL_ERROR');
		case 1049: return array('42000', 'ER_BAD_DB_ERROR');
		case 1050: return array('42S01', 'ER_TABLE_EXISTS_ERROR');
		case 1051: return array('42S02', 'ER_BAD_TABLE_ERROR');
		case 1052: return array('23000', 'ER_NON_UNIQ_ERROR');
		case 1053: return array('08S01', 'ER_SERVER_SHUTDOWN');
		case 1054: return array('42S22', 'ER_BAD_FIELD_ERROR');
		case 1055: return array('42000', 'ER_WRONG_FIELD_WITH_GROUP');
		case 1056: return array('42000', 'ER_WRONG_GROUP_FIELD');
		case 1057: return array('42000', 'ER_WRONG_SUM_SELECT');
		case 1058: return array('21S01', 'ER_WRONG_VALUE_COUNT');
		case 1059: return array('42000', 'ER_TOO_LONG_IDENT');
		case 1060: return array('42S21', 'ER_DUP_FIELDNAME');
		case 1061: return array('42000', 'ER_DUP_KEYNAME');
		case 1062: return array('23000', 'ER_DUP_ENTRY');
		case 1063: return array('42000', 'ER_WRONG_FIELD_SPEC');
		case 1064: return array('42000', 'ER_PARSE_ERROR');
		case 1065: return array('42000', 'ER_EMPTY_QUERY');
		case 1066: return array('42000', 'ER_NONUNIQ_TABLE');
		case 1067: return array('42000', 'ER_INVALID_DEFAULT');
		case 1068: return array('42000', 'ER_MULTIPLE_PRI_KEY');
		case 1069: return array('42000', 'ER_TOO_MANY_KEYS');
		case 1070: return array('42000', 'ER_TOO_MANY_KEY_PARTS');
		case 1071: return array('42000', 'ER_TOO_LONG_KEY');
		case 1072: return array('42000', 'ER_KEY_COLUMN_DOES_NOT_EXITS');
		case 1073: return array('42000', 'ER_BLOB_USED_AS_KEY');
		case 1074: return array('42000', 'ER_TOO_BIG_FIELDLENGTH');
		case 1075: return array('42000', 'ER_WRONG_AUTO_KEY');
		case 1076: return array('HY000', 'ER_READY');
		case 1077: return array('HY000', 'ER_NORMAL_SHUTDOWN');
		case 1078: return array('HY000', 'ER_GOT_SIGNAL');
		case 1079: return array('HY000', 'ER_SHUTDOWN_COMPLETE');
		case 1080: return array('08S01', 'ER_FORCING_CLOSE');
		case 1081: return array('08S01', 'ER_IPSOCK_ERROR');
		case 1082: return array('42S12', 'ER_NO_SUCH_INDEX');
		case 1083: return array('42000', 'ER_WRONG_FIELD_TERMINATORS');
		case 1084: return array('42000', 'ER_BLOBS_AND_NO_TERMINATED');
		case 1085: return array('HY000', 'ER_TEXTFILE_NOT_READABLE');
		case 1086: return array('HY000', 'ER_FILE_EXISTS_ERROR');
		case 1087: return array('HY000', 'ER_LOAD_INFO');
		case 1088: return array('HY000', 'ER_ALTER_INFO');
		case 1089: return array('HY000', 'ER_WRONG_SUB_KEY');
		case 1090: return array('42000', 'ER_CANT_REMOVE_ALL_FIELDS');
		case 1091: return array('42000', 'ER_CANT_DROP_FIELD_OR_KEY');
		case 1092: return array('HY000', 'ER_INSERT_INFO');
		case 1093: return array('HY000', 'ER_UPDATE_TABLE_USED');
		case 1094: return array('HY000', 'ER_NO_SUCH_THREAD');
		case 1095: return array('HY000', 'ER_KILL_DENIED_ERROR');
		case 1096: return array('HY000', 'ER_NO_TABLES_USED');
		case 1097: return array('HY000', 'ER_TOO_BIG_SET');
		case 1098: return array('HY000', 'ER_NO_UNIQUE_LOGFILE');
		case 1099: return array('HY000', 'ER_TABLE_NOT_LOCKED_FOR_WRITE');
		case 1100: return array('HY000', 'ER_TABLE_NOT_LOCKED');
		case 1101: return array('42000', 'ER_BLOB_CANT_HAVE_DEFAULT');
		case 1102: return array('42000', 'ER_WRONG_DB_NAME');
		case 1103: return array('42000', 'ER_WRONG_TABLE_NAME');
		case 1104: return array('42000', 'ER_TOO_BIG_SELECT');
		case 1105: return array('HY000', 'ER_UNKNOWN_ERROR');
		case 1106: return array('42000', 'ER_UNKNOWN_PROCEDURE');
		case 1107: return array('42000', 'ER_WRONG_PARAMCOUNT_TO_PROCEDURE');
		case 1108: return array('HY000', 'ER_WRONG_PARAMETERS_TO_PROCEDURE');
		case 1109: return array('42S02', 'ER_UNKNOWN_TABLE');
		case 1110: return array('42000', 'ER_FIELD_SPECIFIED_TWICE');
		case 1111: return array('HY000', 'ER_INVALID_GROUP_FUNC_USE');
		case 1112: return array('42000', 'ER_UNSUPPORTED_EXTENSION');
		case 1113: return array('42000', 'ER_TABLE_MUST_HAVE_COLUMNS');
		case 1114: return array('HY000', 'ER_RECORD_FILE_FULL');
		case 1115: return array('42000', 'ER_UNKNOWN_CHARACTER_SET');
		case 1116: return array('HY000', 'ER_TOO_MANY_TABLES');
		case 1117: return array('HY000', 'ER_TOO_MANY_FIELDS');
		case 1118: return array('42000', 'ER_TOO_BIG_ROWSIZE');
		case 1119: return array('HY000', 'ER_STACK_OVERRUN');
		case 1120: return array('42000', 'ER_WRONG_OUTER_JOIN');
		case 1121: return array('42000', 'ER_NULL_COLUMN_IN_INDEX');
		case 1122: return array('HY000', 'ER_CANT_FIND_UDF');
		case 1123: return array('HY000', 'ER_CANT_INITIALIZE_UDF');
		case 1124: return array('HY000', 'ER_UDF_NO_PATHS');
		case 1125: return array('HY000', 'ER_UDF_EXISTS');
		case 1126: return array('HY000', 'ER_CANT_OPEN_LIBRARY');
		case 1127: return array('HY000', 'ER_CANT_FIND_DL_ENTRY');
		case 1128: return array('HY000', 'ER_FUNCTION_NOT_DEFINED');
		case 1129: return array('HY000', 'ER_HOST_IS_BLOCKED');
		case 1130: return array('HY000', 'ER_HOST_NOT_PRIVILEGED');
		case 1131: return array('42000', 'ER_PASSWORD_ANONYMOUS_USER');
		case 1132: return array('42000', 'ER_PASSWORD_NOT_ALLOWED');
		case 1133: return array('42000', 'ER_PASSWORD_NO_MATCH');
		case 1134: return array('HY000', 'ER_UPDATE_INFO');
		case 1135: return array('HY000', 'ER_CANT_CREATE_THREAD');
		case 1136: return array('21S01', 'ER_WRONG_VALUE_COUNT_ON_ROW');
		case 1137: return array('HY000', 'ER_CANT_REOPEN_TABLE');
		case 1138: return array('22004', 'ER_INVALID_USE_OF_NULL');
		case 1139: return array('42000', 'ER_REGEXP_ERROR');
		case 1140: return array('42000', 'ER_MIX_OF_GROUP_FUNC_AND_FIELDS');
		case 1141: return array('42000', 'ER_NONEXISTING_GRANT');
		case 1142: return array('42000', 'ER_TABLEACCESS_DENIED_ERROR');
		case 1143: return array('42000', 'ER_COLUMNACCESS_DENIED_ERROR');
		case 1144: return array('42000', 'ER_ILLEGAL_GRANT_FOR_TABLE');
		case 1145: return array('42000', 'ER_GRANT_WRONG_HOST_OR_USER');
		case 1146: return array('42S02', 'ER_NO_SUCH_TABLE');
		case 1147: return array('42000', 'ER_NONEXISTING_TABLE_GRANT');
		case 1148: return array('42000', 'ER_NOT_ALLOWED_COMMAND');
		case 1149: return array('42000', 'ER_SYNTAX_ERROR');
		case 1150: return array('HY000', 'ER_DELAYED_CANT_CHANGE_LOCK');
		case 1151: return array('HY000', 'ER_TOO_MANY_DELAYED_THREADS');
		case 1152: return array('08S01', 'ER_ABORTING_CONNECTION');
		case 1153: return array('08S01', 'ER_NET_PACKET_TOO_LARGE');
		case 1154: return array('08S01', 'ER_NET_READ_ERROR_FROM_PIPE');
		case 1155: return array('08S01', 'ER_NET_FCNTL_ERROR');
		case 1156: return array('08S01', 'ER_NET_PACKETS_OUT_OF_ORDER');
		case 1157: return array('08S01', 'ER_NET_UNCOMPRESS_ERROR');
		case 1158: return array('08S01', 'ER_NET_READ_ERROR');
		case 1159: return array('08S01', 'ER_NET_READ_INTERRUPTED');
		case 1160: return array('08S01', 'ER_NET_ERROR_ON_WRITE');
		case 1161: return array('08S01', 'ER_NET_WRITE_INTERRUPTED');
		case 1162: return array('42000', 'ER_TOO_LONG_STRING');
		case 1163: return array('42000', 'ER_TABLE_CANT_HANDLE_BLOB');
		case 1164: return array('42000', 'ER_TABLE_CANT_HANDLE_AUTO_INCREMENT');
		case 1165: return array('HY000', 'ER_DELAYED_INSERT_TABLE_LOCKED');
		case 1166: return array('42000', 'ER_WRONG_COLUMN_NAME');
		case 1167: return array('42000', 'ER_WRONG_KEY_COLUMN');
		case 1168: return array('HY000', 'ER_WRONG_MRG_TABLE');
		case 1169: return array('23000', 'ER_DUP_UNIQUE');
		case 1170: return array('42000', 'ER_BLOB_KEY_WITHOUT_LENGTH');
		case 1171: return array('42000', 'ER_PRIMARY_CANT_HAVE_NULL');
		case 1172: return array('42000', 'ER_TOO_MANY_ROWS');
		case 1173: return array('42000', 'ER_REQUIRES_PRIMARY_KEY');
		case 1174: return array('HY000', 'ER_NO_RAID_COMPILED');
		case 1175: return array('HY000', 'ER_UPDATE_WITHOUT_KEY_IN_SAFE_MODE');
		case 1176: return array('HY000', 'ER_KEY_DOES_NOT_EXITS');
		case 1177: return array('42000', 'ER_CHECK_NO_SUCH_TABLE');
		case 1178: return array('42000', 'ER_CHECK_NOT_IMPLEMENTED');
		case 1179: return array('25000', 'ER_CANT_DO_THIS_DURING_AN_TRANSACTION');
		case 1180: return array('HY000', 'ER_ERROR_DURING_COMMIT');
		case 1181: return array('HY000', 'ER_ERROR_DURING_ROLLBACK');
		case 1182: return array('HY000', 'ER_ERROR_DURING_FLUSH_LOGS');
		case 1183: return array('HY000', 'ER_ERROR_DURING_CHECKPOINT');
		case 1184: return array('08S01', 'ER_NEW_ABORTING_CONNECTION');
		case 1185: return array('HY000', 'ER_DUMP_NOT_IMPLEMENTED');
		case 1186: return array('HY000', 'ER_FLUSH_MASTER_BINLOG_CLOSED');
		case 1187: return array('HY000', 'ER_INDEX_REBUILD');
		case 1188: return array('HY000', 'ER_MASTER');
		case 1189: return array('08S01', 'ER_MASTER_NET_READ');
		case 1190: return array('08S01', 'ER_MASTER_NET_WRITE');
		case 1191: return array('HY000', 'ER_FT_MATCHING_KEY_NOT_FOUND');
		case 1192: return array('HY000', 'ER_LOCK_OR_ACTIVE_TRANSACTION');
		case 1193: return array('HY000', 'ER_UNKNOWN_SYSTEM_VARIABLE');
		case 1194: return array('HY000', 'ER_CRASHED_ON_USAGE');
		case 1195: return array('HY000', 'ER_CRASHED_ON_REPAIR');
		case 1196: return array('HY000', 'ER_WARNING_NOT_COMPLETE_ROLLBACK');
		case 1197: return array('HY000', 'ER_TRANS_CACHE_FULL');
		case 1198: return array('HY000', 'ER_SLAVE_MUST_STOP');
		case 1199: return array('HY000', 'ER_SLAVE_NOT_RUNNING');
		case 1200: return array('HY000', 'ER_BAD_SLAVE');
		case 1201: return array('HY000', 'ER_MASTER_INFO');
		case 1202: return array('HY000', 'ER_SLAVE_THREAD');
		case 1203: return array('42000', 'ER_TOO_MANY_USER_CONNECTIONS');
		case 1204: return array('HY000', 'ER_SET_CONSTANTS_ONLY');
		case 1205: return array('HY000', 'ER_LOCK_WAIT_TIMEOUT');
		case 1206: return array('HY000', 'ER_LOCK_TABLE_FULL');
		case 1207: return array('25000', 'ER_READ_ONLY_TRANSACTION');
		case 1208: return array('HY000', 'ER_DROP_DB_WITH_READ_LOCK');
		case 1209: return array('HY000', 'ER_CREATE_DB_WITH_READ_LOCK');
		case 1210: return array('HY000', 'ER_WRONG_ARGUMENTS');
		case 1211: return array('42000', 'ER_NO_PERMISSION_TO_CREATE_USER');
		case 1212: return array('HY000', 'ER_UNION_TABLES_IN_DIFFERENT_DIR');
		case 1213: return array('40001', 'ER_LOCK_DEADLOCK');
		case 1214: return array('HY000', 'ER_TABLE_CANT_HANDLE_FT');
		case 1215: return array('HY000', 'ER_CANNOT_ADD_FOREIGN');
		case 1216: return array('23000', 'ER_NO_REFERENCED_ROW');
		case 1217: return array('23000', 'ER_ROW_IS_REFERENCED');
		case 1218: return array('08S01', 'ER_CONNECT_TO_MASTER');
		case 1219: return array('HY000', 'ER_QUERY_ON_MASTER');
		case 1220: return array('HY000', 'ER_ERROR_WHEN_EXECUTING_COMMAND');
		case 1221: return array('HY000', 'ER_WRONG_USAGE');
		case 1222: return array('21000', 'ER_WRONG_NUMBER_OF_COLUMNS_IN_SELECT');
		case 1223: return array('HY000', 'ER_CANT_UPDATE_WITH_READLOCK');
		case 1224: return array('HY000', 'ER_MIXING_NOT_ALLOWED');
		case 1225: return array('HY000', 'ER_DUP_ARGUMENT');
		case 1226: return array('42000', 'ER_USER_LIMIT_REACHED');
		case 1227: return array('42000', 'ER_SPECIFIC_ACCESS_DENIED_ERROR');
		case 1228: return array('HY000', 'ER_LOCAL_VARIABLE');
		case 1229: return array('HY000', 'ER_GLOBAL_VARIABLE');
		case 1230: return array('42000', 'ER_NO_DEFAULT');
		case 1231: return array('42000', 'ER_WRONG_VALUE_FOR_VAR');
		case 1232: return array('42000', 'ER_WRONG_TYPE_FOR_VAR');
		case 1233: return array('HY000', 'ER_VAR_CANT_BE_READ');
		case 1234: return array('42000', 'ER_CANT_USE_OPTION_HERE');
		case 1235: return array('42000', 'ER_NOT_SUPPORTED_YET');
		case 1236: return array('HY000', 'ER_MASTER_FATAL_ERROR_READING_BINLOG');
		case 1237: return array('HY000', 'ER_SLAVE_IGNORED_TABLE');
		case 1238: return array('HY000', 'ER_INCORRECT_GLOBAL_LOCAL_VAR');
		case 1239: return array('42000', 'ER_WRONG_FK_DEF');
		case 1240: return array('HY000', 'ER_KEY_REF_DO_NOT_MATCH_TABLE_REF');
		case 1241: return array('21000', 'ER_OPERAND_COLUMNS');
		case 1242: return array('21000', 'ER_SUBQUERY_NO_1_ROW');
		case 1243: return array('HY000', 'ER_UNKNOWN_STMT_HANDLER');
		case 1244: return array('HY000', 'ER_CORRUPT_HELP_DB');
		case 1245: return array('HY000', 'ER_CYCLIC_REFERENCE');
		case 1246: return array('HY000', 'ER_AUTO_CONVERT');
		case 1247: return array('42S22', 'ER_ILLEGAL_REFERENCE');
		case 1248: return array('42000', 'ER_DERIVED_MUST_HAVE_ALIAS');
		case 1249: return array('01000', 'ER_SELECT_REDUCED');
		case 1250: return array('42000', 'ER_TABLENAME_NOT_ALLOWED_HERE');
		case 1251: return array('08004', 'ER_NOT_SUPPORTED_AUTH_MODE');
		case 1252: return array('42000', 'ER_SPATIAL_CANT_HAVE_NULL');
		case 1253: return array('42000', 'ER_COLLATION_CHARSET_MISMATCH');
		case 1254: return array('HY000', 'ER_SLAVE_WAS_RUNNING');
		case 1255: return array('HY000', 'ER_SLAVE_WAS_NOT_RUNNING');
		case 1256: return array('HY000', 'ER_TOO_BIG_FOR_UNCOMPRESS');
		case 1257: return array('HY000', 'ER_ZLIB_Z_MEM_ERROR');
		case 1258: return array('HY000', 'ER_ZLIB_Z_BUF_ERROR');
		case 1259: return array('HY000', 'ER_ZLIB_Z_DATA_ERROR');
		case 1260: return array('HY000', 'ER_CUT_VALUE_GROUP_CONCAT');
		case 1261: return array('01000', 'ER_WARN_TOO_FEW_RECORDS');
		case 1262: return array('01000', 'ER_WARN_TOO_MANY_RECORDS');
		case 1263: return array('22004', 'ER_WARN_NULL_TO_NOTNULL');
		case 1264: return array('22003', 'ER_WARN_DATA_OUT_OF_RANGE');
		case 1265: return array('01000', 'WARN_DATA_TRUNCATED');
		case 1266: return array('HY000', 'ER_WARN_USING_OTHER_HANDLER');
		case 1267: return array('HY000', 'ER_CANT_AGGREGATE_2COLLATIONS');
		case 1268: return array('HY000', 'ER_DROP_USER');
		case 1269: return array('HY000', 'ER_REVOKE_GRANTS');
		case 1270: return array('HY000', 'ER_CANT_AGGREGATE_3COLLATIONS');
		case 1271: return array('HY000', 'ER_CANT_AGGREGATE_NCOLLATIONS');
		case 1272: return array('HY000', 'ER_VARIABLE_IS_NOT_STRUCT');
		case 1273: return array('HY000', 'ER_UNKNOWN_COLLATION');
		case 1274: return array('HY000', 'ER_SLAVE_IGNORED_SSL_PARAMS');
		case 1275: return array('HY000', 'ER_SERVER_IS_IN_SECURE_AUTH_MODE');
		case 1276: return array('HY000', 'ER_WARN_FIELD_RESOLVED');
		case 1277: return array('HY000', 'ER_BAD_SLAVE_UNTIL_COND');
		case 1278: return array('HY000', 'ER_MISSING_SKIP_SLAVE');
		case 1279: return array('HY000', 'ER_UNTIL_COND_IGNORED');
		case 1280: return array('42000', 'ER_WRONG_NAME_FOR_INDEX');
		case 1281: return array('42000', 'ER_WRONG_NAME_FOR_CATALOG');
		case 1282: return array('HY000', 'ER_WARN_QC_RESIZE');
		case 1283: return array('HY000', 'ER_BAD_FT_COLUMN');
		case 1284: return array('HY000', 'ER_UNKNOWN_KEY_CACHE');
		case 1285: return array('HY000', 'ER_WARN_HOSTNAME_WONT_WORK');
		case 1286: return array('42000', 'ER_UNKNOWN_STORAGE_ENGINE');
		case 1287: return array('HY000', 'ER_WARN_DEPRECATED_SYNTAX');
		case 1288: return array('HY000', 'ER_NON_UPDATABLE_TABLE');
		case 1289: return array('HY000', 'ER_FEATURE_DISABLED');
		case 1290: return array('HY000', 'ER_OPTION_PREVENTS_STATEMENT');
		case 1291: return array('HY000', 'ER_DUPLICATED_VALUE_IN_TYPE');
		case 1292: return array('22007', 'ER_TRUNCATED_WRONG_VALUE');
		case 1293: return array('HY000', 'ER_TOO_MUCH_AUTO_TIMESTAMP_COLS');
		case 1294: return array('HY000', 'ER_INVALID_ON_UPDATE');
		case 1295: return array('HY000', 'ER_UNSUPPORTED_PS');
		case 1296: return array('HY000', 'ER_GET_ERRMSG');
		case 1297: return array('HY000', 'ER_GET_TEMPORARY_ERRMSG');
		case 1298: return array('HY000', 'ER_UNKNOWN_TIME_ZONE');
		case 1299: return array('HY000', 'ER_WARN_INVALID_TIMESTAMP');
		case 1300: return array('HY000', 'ER_INVALID_CHARACTER_STRING');
		case 1301: return array('HY000', 'ER_WARN_ALLOWED_PACKET_OVERFLOWED');
		case 1302: return array('HY000', 'ER_CONFLICTING_DECLARATIONS');
		case 1303: return array('2F003', 'ER_SP_NO_RECURSIVE_CREATE');
		case 1304: return array('42000', 'ER_SP_ALREADY_EXISTS');
		case 1305: return array('42000', 'ER_SP_DOES_NOT_EXIST');
		case 1306: return array('HY000', 'ER_SP_DROP_FAILED');
		case 1307: return array('HY000', 'ER_SP_STORE_FAILED');
		case 1308: return array('42000', 'ER_SP_LILABEL_MISMATCH');
		case 1309: return array('42000', 'ER_SP_LABEL_REDEFINE');
		case 1310: return array('42000', 'ER_SP_LABEL_MISMATCH');
		case 1311: return array('01000', 'ER_SP_UNINIT_VAR');
		case 1312: return array('0A000', 'ER_SP_BADSELECT');
		case 1313: return array('42000', 'ER_SP_BADRETURN');
		case 1314: return array('0A000', 'ER_SP_BADSTATEMENT');
		case 1315: return array('42000', 'ER_UPDATE_LOG_DEPRECATED_IGNORED');
		case 1316: return array('42000', 'ER_UPDATE_LOG_DEPRECATED_TRANSLATED');
		case 1317: return array('70100', 'ER_QUERY_INTERRUPTED');
		case 1318: return array('42000', 'ER_SP_WRONG_NO_OF_ARGS');
		case 1319: return array('42000', 'ER_SP_COND_MISMATCH');
		case 1320: return array('42000', 'ER_SP_NORETURN');
		case 1321: return array('2F005', 'ER_SP_NORETURNEND');
		case 1322: return array('42000', 'ER_SP_BAD_CURSOR_QUERY');
		case 1323: return array('42000', 'ER_SP_BAD_CURSOR_SELECT');
		case 1324: return array('42000', 'ER_SP_CURSOR_MISMATCH');
		case 1325: return array('24000', 'ER_SP_CURSOR_ALREADY_OPEN');
		case 1326: return array('24000', 'ER_SP_CURSOR_NOT_OPEN');
		case 1327: return array('42000', 'ER_SP_UNDECLARED_VAR');
		case 1328: return array('HY000', 'ER_SP_WRONG_NO_OF_FETCH_ARGS');
		case 1329: return array('02000', 'ER_SP_FETCH_NO_DATA');
		case 1330: return array('42000', 'ER_SP_DUP_PARAM');
		case 1331: return array('42000', 'ER_SP_DUP_VAR');
		case 1332: return array('42000', 'ER_SP_DUP_COND');
		case 1333: return array('42000', 'ER_SP_DUP_CURS');
		case 1334: return array('HY000', 'ER_SP_CANT_ALTER');
		case 1335: return array('0A000', 'ER_SP_SUBSELECT_NYI');
		case 1336: return array('0A000', 'ER_STMT_NOT_ALLOWED_IN_SF_OR_TRG');
		case 1337: return array('42000', 'ER_SP_VARCOND_AFTER_CURSHNDLR');
		case 1338: return array('42000', 'ER_SP_CURSOR_AFTER_HANDLER');
		case 1339: return array('20000', 'ER_SP_CASE_NOT_FOUND');
		case 1340: return array('HY000', 'ER_FPARSER_TOO_BIG_FILE');
		case 1341: return array('HY000', 'ER_FPARSER_BAD_HEADER');
		case 1342: return array('HY000', 'ER_FPARSER_EOF_IN_COMMENT');
		case 1343: return array('HY000', 'ER_FPARSER_ERROR_IN_PARAMETER');
		case 1344: return array('HY000', 'ER_FPARSER_EOF_IN_UNKNOWN_PARAMETER');
		case 1345: return array('HY000', 'ER_VIEW_NO_EXPLAIN');
		case 1346: return array('HY000', 'ER_FRM_UNKNOWN_TYPE');
		case 1347: return array('HY000', 'ER_WRONG_OBJECT');
		case 1348: return array('HY000', 'ER_NONUPDATEABLE_COLUMN');
		case 1349: return array('HY000', 'ER_VIEW_SELECT_DERIVED');
		case 1350: return array('HY000', 'ER_VIEW_SELECT_CLAUSE');
		case 1351: return array('HY000', 'ER_VIEW_SELECT_VARIABLE');
		case 1352: return array('HY000', 'ER_VIEW_SELECT_TMPTABLE');
		case 1353: return array('HY000', 'ER_VIEW_WRONG_LIST');
		case 1354: return array('HY000', 'ER_WARN_VIEW_MERGE');
		case 1355: return array('HY000', 'ER_WARN_VIEW_WITHOUT_KEY');
		case 1356: return array('HY000', 'ER_VIEW_INVALID');
		case 1357: return array('HY000', 'ER_SP_NO_DROP_SP');
		case 1358: return array('HY000', 'ER_SP_GOTO_IN_HNDLR');
		case 1359: return array('HY000', 'ER_TRG_ALREADY_EXISTS');
		case 1360: return array('HY000', 'ER_TRG_DOES_NOT_EXIST');
		case 1361: return array('HY000', 'ER_TRG_ON_VIEW_OR_TEMP_TABLE');
		case 1362: return array('HY000', 'ER_TRG_CANT_CHANGE_ROW');
		case 1363: return array('HY000', 'ER_TRG_NO_SUCH_ROW_IN_TRG');
		case 1364: return array('HY000', 'ER_NO_DEFAULT_FOR_FIELD');
		case 1365: return array('22012', 'ER_DIVISION_BY_ZERO');
		case 1366: return array('HY000', 'ER_TRUNCATED_WRONG_VALUE_FOR_FIELD');
		case 1367: return array('22007', 'ER_ILLEGAL_VALUE_FOR_TYPE');
		case 1368: return array('HY000', 'ER_VIEW_NONUPD_CHECK');
		case 1369: return array('HY000', 'ER_VIEW_CHECK_FAILED');
		case 1370: return array('42000', 'ER_PROCACCESS_DENIED_ERROR');
		case 1371: return array('HY000', 'ER_RELAY_LOG_FAIL');
		case 1372: return array('HY000', 'ER_PASSWD_LENGTH');
		case 1373: return array('HY000', 'ER_UNKNOWN_TARGET_BINLOG');
		case 1374: return array('HY000', 'ER_IO_ERR_LOG_INDEX_READ');
		case 1375: return array('HY000', 'ER_BINLOG_PURGE_PROHIBITED');
		case 1376: return array('HY000', 'ER_FSEEK_FAIL');
		case 1377: return array('HY000', 'ER_BINLOG_PURGE_FATAL_ERR');
		case 1378: return array('HY000', 'ER_LOG_IN_USE');
		case 1379: return array('HY000', 'ER_LOG_PURGE_UNKNOWN_ERR');
		case 1380: return array('HY000', 'ER_RELAY_LOG_INIT');
		case 1381: return array('HY000', 'ER_NO_BINARY_LOGGING');
		case 1382: return array('HY000', 'ER_RESERVED_SYNTAX');
		case 1383: return array('HY000', 'ER_WSAS_FAILED');
		case 1384: return array('HY000', 'ER_DIFF_GROUPS_PROC');
		case 1385: return array('HY000', 'ER_NO_GROUP_FOR_PROC');
		case 1386: return array('HY000', 'ER_ORDER_WITH_PROC');
		case 1387: return array('HY000', 'ER_LOGGING_PROHIBIT_CHANGING_OF');
		case 1388: return array('HY000', 'ER_NO_FILE_MAPPING');
		case 1389: return array('HY000', 'ER_WRONG_MAGIC');
		case 1390: return array('HY000', 'ER_PS_MANY_PARAM');
		case 1391: return array('HY000', 'ER_KEY_PART_0');
		case 1392: return array('HY000', 'ER_VIEW_CHECKSUM');
		case 1393: return array('HY000', 'ER_VIEW_MULTIUPDATE');
		case 1394: return array('HY000', 'ER_VIEW_NO_INSERT_FIELD_LIST');
		case 1395: return array('HY000', 'ER_VIEW_DELETE_MERGE_VIEW');
		case 1396: return array('HY000', 'ER_CANNOT_USER');
		case 1397: return array('XAE04', 'ER_XAER_NOTA');
		case 1398: return array('XAE05', 'ER_XAER_INVAL');
		case 1399: return array('XAE07', 'ER_XAER_RMFAIL');
		case 1400: return array('XAE09', 'ER_XAER_OUTSIDE');
		case 1401: return array('XAE03', 'ER_XAER_RMERR');
		case 1402: return array('XA100', 'ER_XA_RBROLLBACK');
		case 1403: return array('42000', 'ER_NONEXISTING_PROC_GRANT');
		case 1404: return array('HY000', 'ER_PROC_AUTO_GRANT_FAIL');
		case 1405: return array('HY000', 'ER_PROC_AUTO_REVOKE_FAIL');
		case 1406: return array('22001', 'ER_DATA_TOO_LONG');
		case 1407: return array('42000', 'ER_SP_BAD_SQLSTATE');
		case 1408: return array('HY000', 'ER_STARTUP');
		case 1409: return array('HY000', 'ER_LOAD_FROM_FIXED_SIZE_ROWS_TO_VAR');
		case 1410: return array('42000', 'ER_CANT_CREATE_USER_WITH_GRANT');
		case 1411: return array('HY000', 'ER_WRONG_VALUE_FOR_TYPE');
		case 1412: return array('HY000', 'ER_TABLE_DEF_CHANGED');
		case 1413: return array('42000', 'ER_SP_DUP_HANDLER');
		case 1414: return array('42000', 'ER_SP_NOT_VAR_ARG');
		case 1415: return array('0A000', 'ER_SP_NO_RETSET');
		case 1416: return array('22003', 'ER_CANT_CREATE_GEOMETRY_OBJECT');
		case 1417: return array('HY000', 'ER_FAILED_ROUTINE_BREAK_BINLOG');
		case 1418: return array('HY000', 'ER_BINLOG_UNSAFE_ROUTINE');
		case 1419: return array('HY000', 'ER_BINLOG_CREATE_ROUTINE_NEED_SUPER');
		case 1420: return array('HY000', 'ER_EXEC_STMT_WITH_OPEN_CURSOR');
		case 1421: return array('HY000', 'ER_STMT_HAS_NO_OPEN_CURSOR');
		case 1422: return array('HY000', 'ER_COMMIT_NOT_ALLOWED_IN_SF_OR_TRG');
		case 1423: return array('HY000', 'ER_NO_DEFAULT_FOR_VIEW_FIELD');
		case 1424: return array('HY000', 'ER_SP_NO_RECURSION');
		case 1425: return array('42000', 'ER_TOO_BIG_SCALE');
		case 1426: return array('42000', 'ER_TOO_BIG_PRECISION');
		case 1427: return array('42000', 'ER_M_BIGGER_THAN_D');
		case 1428: return array('HY000', 'ER_WRONG_LOCK_OF_SYSTEM_TABLE');
		case 1429: return array('HY000', 'ER_CONNECT_TO_FOREIGN_DATA_SOURCE');
		case 1430: return array('HY000', 'ER_QUERY_ON_FOREIGN_DATA_SOURCE');
		case 1431: return array('HY000', 'ER_FOREIGN_DATA_SOURCE_DOESNT_EXIST');
		case 1432: return array('HY000', 'ER_FOREIGN_DATA_STRING_INVALID_CANT_CREATE');
		case 1433: return array('HY000', 'ER_FOREIGN_DATA_STRING_INVALID');
		case 1434: return array('HY000', 'ER_CANT_CREATE_FEDERATED_TABLE');
		case 1435: return array('HY000', 'ER_TRG_IN_WRONG_SCHEMA');
		case 1436: return array('HY000', 'ER_STACK_OVERRUN_NEED_MORE');
		case 1437: return array('42000', 'ER_TOO_LONG_BODY');
		case 1438: return array('HY000', 'ER_WARN_CANT_DROP_DEFAULT_KEYCACHE');
		case 1439: return array('42000', 'ER_TOO_BIG_DISPLAYWIDTH');
		case 1440: return array('XAE08', 'ER_XAER_DUPID');
		case 1441: return array('22008', 'ER_DATETIME_FUNCTION_OVERFLOW');
		case 1442: return array('HY000', 'ER_CANT_UPDATE_USED_TABLE_IN_SF_OR_TRG');
		case 1443: return array('HY000', 'ER_VIEW_PREVENT_UPDATE');
		case 1444: return array('HY000', 'ER_PS_NO_RECURSION');
		case 1445: return array('HY000', 'ER_SP_CANT_SET_AUTOCOMMIT');
		case 1446: return array('HY000', 'ER_MALFORMED_DEFINER');
		case 1447: return array('HY000', 'ER_VIEW_FRM_NO_USER');
		case 1448: return array('HY000', 'ER_VIEW_OTHER_USER');
		case 1449: return array('HY000', 'ER_NO_SUCH_USER');
		case 1450: return array('HY000', 'ER_FORBID_SCHEMA_CHANGE');
		case 1451: return array('23000', 'ER_ROW_IS_REFERENCED_2');
		case 1452: return array('23000', 'ER_NO_REFERENCED_ROW_2');
		case 1453: return array('42000', 'ER_SP_BAD_VAR_SHADOW');
		case 1454: return array('HY000', 'ER_TRG_NO_DEFINER');
		case 1455: return array('HY000', 'ER_OLD_FILE_FORMAT');
		case 1456: return array('HY000', 'ER_SP_RECURSION_LIMIT');
		case 1457: return array('HY000', 'ER_SP_PROC_TABLE_CORRUPT');
		case 1458: return array('42000', 'ER_SP_WRONG_NAME');
		case 1459: return array('HY000', 'ER_TABLE_NEEDS_UPGRADE');
		case 1460: return array('42000', 'ER_SP_NO_AGGREGATE');
		case 1461: return array('42000', 'ER_MAX_PREPARED_STMT_COUNT_REACHED');
		case 1462: return array('HY000', 'ER_VIEW_RECURSIVE');
		case 1463: return array('42000', 'ER_NON_GROUPING_FIELD_USED');
		case 1464: return array('HY000', 'ER_TABLE_CANT_HANDLE_SPKEYS');
		case 1465: return array('HY000', 'ER_NO_TRIGGERS_ON_SYSTEM_SCHEMA');
		case 1466: return array('HY000', 'ER_REMOVED_SPACES');
		case 1467: return array('HY000', 'ER_AUTOINC_READ_FAILED');
		case 1468: return array('HY000', 'ER_USERNAME');
		case 1469: return array('HY000', 'ER_HOSTNAME');
		case 1470: return array('HY000', 'ER_WRONG_STRING_LENGTH');
		case 1471: return array('HY000', 'ER_NON_INSERTABLE_TABLE');
		case 1472: return array('HY000', 'ER_ADMIN_WRONG_MRG_TABLE');
		case 1473: return array('HY000', 'ER_TOO_HIGH_LEVEL_OF_NESTING_FOR_SELECT');
		case 1474: return array('HY000', 'ER_NAME_BECOMES_EMPTY');
		case 1475: return array('HY000', 'ER_AMBIGUOUS_FIELD_TERM');
		case 1476: return array('HY000', 'ER_LOAD_DATA_INVALID_COLUMN');
		case 1477: return array('HY000', 'ER_LOG_PURGE_NO_FILE');
		default  : return array('HY000', '');  # general unmapped error
		}
	}
	
	// return standardized ANSI SQL / ODBC error code
	function getLastErrorCode()
	{
		$err  = $this->getLastNativeError();
		$info = $this->_errInfo( $err );
		return $info[0];  
	}
	
	
	function __toString()
	{
		return '['.get_class($this).']';
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
						case YADB_MTYPE_STR:
															  break;  // is a string already
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
		if (is_resource( $this->_rs ))
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
			$t = strToLower($fldObj->type);
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
					//$types[$fldObj->name] = YADB_MTYPE_INT;   break;
					$types[$i] = YADB_MTYPE_INT;   break;
				case 'string':
				case 'date':
					//$types[$fldObj->name] = YADB_MTYPE_STR;   break;
					$types[$i] = YADB_MTYPE_STR;   break;
				case 'real':
					//$types[$fldObj->name] = YADB_MTYPE_FLOAT; break;
					$types[$i] = YADB_MTYPE_FLOAT; break;
				case 'date':
				case 'datetime':
				case 'timestamp':
					//$types[$fldObj->name] = YADB_MTYPE_STR;   break;
					$types[$i] = YADB_MTYPE_STR;   break;
				case 'year':
					//$types[$fldObj->name] = YADB_MTYPE_INT;   break;
					$types[$i] = YADB_MTYPE_INT;   break;
				default:
					// should not be necessary, but you never know
					//$types[$fldObj->name] = $fldObj->numeric ?
					//	YADB_MTYPE_FLOAT : YADB_MTYPE_STR;
					$types[$i] = $fldObj->numeric ?
						YADB_MTYPE_FLOAT : YADB_MTYPE_STR;
					break;
			}
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