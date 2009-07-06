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
* 
 *******************************************************************
* 
* YADB is a small database abstraction layer (yet another ...)
* designed for speed and small memory footprint. It aims towards
* development of web applications and thus provides functions for
* the common tasks of adding/editing/deleting records through a web
* interface. Rows can be represented as objects for convenience.
* In fact the main goal is convenience with database abstraction
* being just a side-effect.
* 
* Might provide helper functions for easy creation of forms.
* 
* Requires PHP >= 4.3 with built in support for either the mysql
* or (preferably if you use MySQL >= 4.1) the mysqli extension.
* MySQL >= 4.1 is recommended. I recommend eAccelerator for
* additional speed improvements.
* 
* YADB triggers standard PHP errors, most of them of type
* E_USER_ERROR. Catch them with your own PHP error handler. YADB
* does intentionally *not* provides means of caching as you should
* do caching anyways - outside of YADB.
* 
* 
* General rules:
* - Don't run proprietary SQL queries or queries containing
*   non-standard SQL extensions through this API or otherwise
*   you wouldn't need YADB.
* - Wherever YADB provides functions for specific tasks (such as
*   transactions) use them instead of doing things "manually"
*   or you *will* experience unexpected behavior. To
*   avoid this we would have to parse every single SQL query
*   you execute which would *dramatically* decrease speed.
* - Do not run queries which are not transaction safe in YADB
*   transactions (eg. LOCK TABLES, database definition
*   language queries).
* -  Don't append a semicolon (;) to your queries.
* 
* 
* Example: Instantiate a new database connection:
* $db = YADB_newConnection('mysql');
* or
* $db = YADB_Connection::factory('mysql');
\*******************************************************************/

error_reporting(E_ALL);   // for debugging only


if (defined('YADB_DIR'))
	die("YADB_DIR must not be defined before inclusion.\n");

define('YADB_DIR', dirName(__FILE__) .'/');
define('YADB_VERS', 415); // = 0.04.15

/***********************************************************
* Columns flags:
***********************************************************/
define('YADB_FLAG_NOTNULL' ,  1 ); // 1<<0
define('YADB_FLAG_AUTOINC' ,  2 ); // 1<<1
define('YADB_FLAG_UNSIGNED',  4 ); // 1<<2
define('YADB_FLAG_BINARY'  ,  8 ); // 1<<3
define('YADB_FLAG_PKPART'  , 16 ); // 1<<4
                                   // column is part of a primary key
define('YADB_FLAG_PKCOL'   , 32 ); // 1<<5
                                   // column *is* the primary key
define('YADB_FLAG_TRUEPK'  , 51 ); // YADB_FLAG_PKCOL + YADB_FLAG_PKPART +
                                   // YADB_FLAG_AUTOINC + YADB_FLAG_NOTNULL
                                   // combined flag for comfortable testing

/***********************************************************
* Main meta types:
***********************************************************/
define('YADB_MTYPE_INT'  , 1 );
define('YADB_MTYPE_STR'  , 2 );
define('YADB_MTYPE_FLOAT', 3 ); // float, double, decimal
define('YADB_MTYPE_BOOL' , 4 );

/***********************************************************
* Meta sub types:
***********************************************************/
define('YADB_STYPE_INT_1'    ,  1 ); // tinyint
define('YADB_STYPE_INT_2'    ,  2 ); // smallint
define('YADB_STYPE_INT_3'    ,  3 ); // mediumint
define('YADB_STYPE_INT_4'    ,  4 ); // int
define('YADB_STYPE_INT_8'    ,  5 ); // bigint
define('YADB_STYPE_STR_1'    , 10 ); // 255 B
                                     // char, binary,
                                     // varchar/varbinary(<256),
                                     // tinytext/tinyblob
define('YADB_STYPE_STR_2'    , 11 ); // 64 KB - 4 B
                                     // varchar/varbinary(256-65532),
                                     // text, blob
define('YADB_STYPE_STR_3'    , 12 ); // 16 MB - 1 B
                                     // mediumtext, mediumblob
define('YADB_STYPE_STR_4'    , 13 ); // 4 GB - 1 B
                                     // longtext, longblob
define('YADB_STYPE_ENUM'     , 20 ); // enum (PHP: string)
define('YADB_STYPE_SET'      , 21 ); // set (PHP: string)
define('YADB_STYPE_FLOAT_4'  , 30 ); // float
define('YADB_STYPE_FLOAT_8'  , 31 ); // double
define('YADB_STYPE_FLOAT_DEC', 32 ); // decimal, numeric
define('YADB_STYPE_BOOL'     , 40 ); // boolean
define('YADB_STYPE_BITS'     , 41 ); // MySQL: bit(n) (PHP: int)
define('YADB_STYPE_DATE'     , 50 ); // (PHP: str)
define('YADB_STYPE_TIME'     , 51 ); // (PHP: str)
define('YADB_STYPE_DATETIME' , 52 ); // datetime, MySQL "timestamp"
                                     // (PHP: str)
define('YADB_STYPE_YEAR'     , 53 ); // 1901-2155 (PHP: int)
define('YADB_STYPE_TSTAMP'   , 54 ); // MySQL "timestamp" (PHP: str)
define('YADB_STYPE_GEO'      , 60 ); // MySQL: geometric (PHP: str?)

/***********************************************************
* "Brownies" specific constants:
***********************************************************/
/*
define( 'YADB_BROWN_REL_1'    ,  1 ); //
define( 'YADB_BROWN_REL_N'    ,  2 ); //
define( 'YADB_MTYPE_BROWN_OBJ', 100 ); // object
define( 'YADB_MTYPE_BROWN_ARR', 101 ); // array of objects
define( 'YADB_FLAG_BROWN_ARR' , 64 ); // ..01000000
                                      // whether array of brownie
                                      // objects has been fetched
*/


// workaround for converting PHP versions to numbers:
$tmp = (float)PHP_VERSION;
if     ($tmp >= 5.1999) define('YADB_PHPVER', 50200); // >= 5.2
elseif ($tmp >= 5.0   ) define('YADB_PHPVER', 50000); // >= 5.0
elseif ($tmp >  4.2999) define('YADB_PHPVER', 40300); // >= 4.3
// do not write >=4.3 here - strange floating point issue
//elseif ($tmp >  4.1999) define('YADB_PHPVER', 40200); // >= 4.2
//elseif ( strNatCmp(PHP_VERSION,'4.0.6') >= 0 )
//                        define('YADB_PHPVER', 40006);  // 4.0.6
//else
//	trigger_error('YaDB requires PHP 4.0.6 or better.', E_USER_ERROR);
else
	trigger_error('YADB: PHP >= 4.3 required.', E_USER_ERROR);


@ set_magic_quotes_runtime(0);
@ ini_set('magic_quotes_gpc', 0);


/***********************************************************
* Instantiates a new database connection
* Usage: $db = YADB_newConnection('mysql');
***********************************************************/

function & YADB_newConnection( $dbType )
{
	static $lastDb = '';  // last db driver loaded for circumvention of include_once() in many cases
	static $drivers = array( 'mysql', 'mysqli' );  // supported drivers
	
	$false = false;  // we must return a reference, not a value
	if (! in_array( $dbType, $drivers )) {
		// dbType not supported
		trigger_error( 'YADB: Driver "'. $dbType .'" not supported.', E_USER_WARNING );
		return $false;
	}
	if ($dbType !== $lastDb) {
		$file = YADB_DIR .'drv/'. $dbType .'.php';
		include_once $file;
		$lastDb = $dbType;
	}
	$class = 'YADB_Connection_'. $dbType;
	if ( class_exists($class) && class_exists('YADB_RecordSet_'. $dbType) ) {
		$dbConn = new $class( $dbType );
		if (is_object($dbConn) && ! $dbConn->_drvLoadErr)
			return $dbConn;
	}
	// problem tracking:
	if (! file_exists($file))
		trigger_error( 'YADB: Missing file: "'. $file .'".', E_USER_WARNING );
	else
		trigger_error( 'YADB: Syntax error in file: "'. $file .'".', E_USER_WARNING);
	trigger_error( 'YADB: Unable to load database driver "'. $dbType .'".', E_USER_WARNING );
	return $false;
}



/***********************************************************
* The main connection class. Use YADB_newConnection() to
* instantiate
***********************************************************/

class YADB_Connection
{
	/**
	*  general:
	*/
	var $_dbType          = '';
	var $_conn            = null;  /// the connection resource
	var $_host            = null;
	var $_port            = null;
	var $_socket          = null;
	var $_user            = null;
	var $_pwd             = null;
	var $_drvOpts         = array();
	var $_drvLoadErr      = false; /// the driver must set this to true right after instantiation if it cannot run because of a missing extension or other problems
	
	/**
	*  DBMS specific:
	*/
	var $_replaceQuote    = '\\\'';/// string to use to replace single-quotes
	var $_metaDbsSQL      = null;
	var $_metaTblsSQL     = null;
	var $_metaColsSQL     = null;
	var $_namesQuote      = '`';   /// string to quote columns names
	//var $_hasInsertID     = false; /// supports autoincrement ID
	//var $_hasAffRows      = false; /// supports affected rows for update/delete
	//var $_poorAffRows     = false; /// affected rows not working or unreliable
	//var $_hasLimit        = false; /// support pgsql/mysql SELECT * FROM TABLE LIMIT 10
	//var $_hasTop          = false; /// support mssql/access SELECT TOP 10 * FROM TABLE
	//var $_hasMoveFirst    = false; /// has ability to run moveFirst(), scrolling backwards
	//var $_hasTrans        = false; /// has (native) transactions
	var $_hasPrepare      = false; /// can prepare queries and execute prepared queries
	var $_hasBindParams   = false; /// true if driver can bind input array to plain SQL queries (not statements). if false binding will be emulated
	var $_hasExecMulti    = false; /// true if driver allows executeMulti()
	
	/**
	* runtime vars:
	*/
	//var $_lastInsertID    = false;
	//var $_countRecs       = false; /// count records enabled?
	var $_transOff        = 0;     /// temporarily disable transaction (/ _startTrans() ?)
	var $_transCnt        = 0;     /// level of nested transactions
	var $_transErr        = false; /// must be set to true if an SQL error occurs within a nested transaction so the outermost transaction will auto-rollback
	var $_queryErrFn      = null;  /// if set to a method name this function will be called if a query fails. this is used as a monitor for nested transactions
	
	var $_customQueryCb   = null; /// if set this function will be called before executing a query. if the function returns false the query will not even be executed but fail
	var $_customQueryErrCb= null;
	var $_customAttrs     = array(); /// custom attributes which can be set by the user to identify the connection
	
	
	/***********************************************************
	* The constructor (PHP 4). Calls __construct().
	***********************************************************/
	
	function YADB_Connection( $dbType )
	{
		//$this->__construct( $dbType );
		
		if ( strToLower(get_class($this))==='yadb_connection' )
			die('YADB: YADB_Connection is an abstract class - cannot instantiate. Use YADB_newConnection() or ::factory().');
		$this->_dbType = $dbType;
	}
	
	/***********************************************************
	* The constructor (PHP 5).
	***********************************************************/
	
	/*
	function __construct( $dbType )
	{
		if ( strToLower(get_class($this))==='yadb_connection' )
			die('YADB: YADB_Connection is an abstract class - cannot instantiate. Use YADB_newConnection() or ::factory().');
		$this->_dbType = $dbType;
	}
	*/
	
	
	function & factory( $dbType )
	{
		return YADB_newConnection( $dbType );
	}
	
	
	/***********************************************************
	* Getters/setters for custom attributes
	***********************************************************/
	
	function setCustomAttr( $name, $val )
	{
		$this->_customAttrs[$name] = $val;
	}
	
	function getCustomAttr( $name )
	{
		return array_key_exists($name, $this->_customAttrs)
			? $this->_customAttrs[$name]
			: null;
	}
	
	function getCustomAttrs()
	{
		return $this->_customAttrs;
	}
	
	
	/***********************************************************
	* Returns the SQL to check if a field (or expression) is
	* null (no value).
	***********************************************************/
	
	function getIsNullSql( $fieldName, $withColQuotes=false )
	{
		$q = $withColQuotes ? $this->_namesQuote : '';
		return sprintF( ' (%s%s%2\%s IS NULL) ', $q, $fieldName );
	}
	
	
	/***********************************************************
	* Returns the SQL to check if a field (or expression) is
	* NOT null.
	***********************************************************/
	
	function getIsNotNullSql( $fieldName, $withColQuotes=false )
	{
		$q = $withColQuotes ? $this->_namesQuote : '';
		return sprintF( ' (%s%s%2\%s IS NOT NULL) ', $q, $fieldName );
	}
	
	
	/***********************************************************
	* Switches counting of records on/off
	***********************************************************/
	/*
	function setCountRecs( $enable=true ) {
		$this->_countRecs = (bool)$enable;
	}
	*/
	
	
	/***********************************************************
	* Returns server version in an array with 3 elements:
	* "orig" is the native version string, eg. "3.23.56 ppc",
	* "vstr" parses a version out of "orig" in x.x format (see
	* _findVers()), eg. "3.23.56"
	* "vint" is an integer in MMmmss format (major, minor, sub),
	* eg. (0)32356 or 40100
	***********************************************************/
	
	function serverVers() {
		return array( 'orig' => '', 'vstr' => '', 'vint' => null );
	}
	
	
	/***********************************************************
	* Returns client lib version
	***********************************************************/
	
	function clientVers() {
		return array( 'orig' => '', 'vstr' => '', 'vint' => null );
	}
	
	
	/***********************************************************
	* Returns host info (eg. "localhost via unix socket")
	***********************************************************/
	
	function hostInfo() {
		return '';
	}
	
	
	/***********************************************************
	* Returns protocol version, depends on the driver
	***********************************************************/
	
	function protoVers() {
		return null;
	}
	
	
	/***********************************************************
	* Finds version string in a string
	***********************************************************/
	
	function _findVers( $str )
	{
		if ( preg_match('/[0-9]+(\.[0-9]+)+/', $str, $arr) )
			return $arr[0];
		return '';
	}
	
	
	/***********************************************************
	* Connect to database
	* $host can be "host|ip[:port]" or ":/path/to/socket"
	* $options is an associative array of options to be inter-
	* preted by the driver (PHP). Drivers are supposed to
	* implement "timeout" (connect timeout).
	***********************************************************/
	
	function connect( $host=null, $user=null, $pwd=null, $db=null, $options=array() )
	{
		if (subStr($host,0,1) !== ':') {
			$tmp = explode(':', $host, 2);
			$this->_host = $tmp[0];
			$this->_port = (@ isSet($tmp[1])) ? abs((int)$tmp[1]) : 0;
			$this->_socket = null;
		} else {
			$this->_host = null;
			$this->_socket = subStr($host,1);
		}
		$this->_user  = $user;
		$this->_pwd   = $pwd;
		$this->_db    = $db;
		$this->_drvOpts = $options;
		
		if ($this->_connect()) return true;
		
		$this->_conn = null;
		//trigger_error( 'YADB: Could not connect to "'. $host .'" as user "'. $user .'" '. ($this->_pwd ? 'using password' : 'without password') .', database "'. $this->_db .'".', E_USER_WARNING );
		trigger_error( 'YADB: Could not connect to mysql://'. $user . ($this->_pwd ? '' : ':') .'@'. $host .'/'. $this->_db , E_USER_WARNING );
		return false;
	}
	
	
	/***********************************************************
	* Really connect to database. To be overridden by driver.
	***********************************************************/
	
	function _connect() { return false; }
	
	
	/***********************************************************
	* Change DB. Not supported by all databases.
	***********************************************************/
	
	function changeDb( $dbName )
	{
		if ($this->_changeDb( $dbName )) {
			$this->_db = $dbName;
			return true;
		}
		trigger_error( 'YADB: Selecting database "'. $dbName .'" failed.', E_USER_WARNING );
		return false;
	}
	
	
	/***********************************************************
	* Really change the database. To be overridden by driver.
	***********************************************************/
	
	function _changeDb( $dbName ) { return false; }
	
	
	/***********************************************************
	* Escapes quotes in a string. Respects
	* get_magic_quotes_gpc(). This undoes the stupidity of
	* magic quotes for GPC.
	*
	* Example: $db->escape("Don't bother");
	*
	* //Returns quoted string to be sent back to the database
	* //enclosed in single quotes.
	***********************************************************/
	
	function escape( $str )
	{
		if (!get_magic_quotes_gpc()) {
			if ($this->_replaceQuote[0]==='\\')
				$str = str_replace( array('\\',"\0"), array('\\\\',"\\\0"), $str );
			return str_replace( '\'', $this->_replaceQuote, $str );
		}
		
		// undo magic quotes for " :
		$str = str_replace( '\\"', '"' , $str );
		
		if ($this->_replaceQuote==='\\\'') return $str;
		// ' already quoted, no need to change anything
		else {  // change \' to '' for sybase/mssql
			$str = str_replace( '\\\\', '\\', $str );
			return str_replace( '\\\'', $this->_replaceQuote, $str );
		}
	}
	
	
	/***********************************************************
	* Escapes variables for using them in an SQL query. Strings
	* are escaped using escape() and enclosed in single quotes.
	* (Single quotes are ANSI standard, double quotes are not.)
	* Other types remain mostly unchanged.
	***********************************************************/
	
	function quote( $val )
	{
		/*
		$type = getType( $mixed );
		switch ($type) {
			case 'string':
				return '\''. $this->escape( $mixed ) .'\'';  break;
			case 'integer':
				return (string)$mixed;  break;
			case 'boolean':
				return (string)(int)$mixed;  break;
			case 'double':
				return str_replace(',','.',$mixed);  break;
				// locales fix so 1.1 does not get converted to 1,1
			case 'NULL':
				return 'NULL';  break;
			default:  // array, object or unknown type
				return null;
		}
		*/
		if     (is_string ($val))
			return '\''. $this->escape( $val ) .'\'';
		elseif (is_integer($val))  return (string)$val;
		elseif (is_double ($val))  return str_replace(',','.',$val);
		// locales fix so 1.1 does not get converted to 1,1
		elseif (is_null   ($val))  return 'NULL';
		elseif (is_bool   ($val))  return (string)(int)$val;
		else                       return null;
		// array, object, resource or unknown type
	}
	
	
	/***********************************************************
	* Return character set of the connection. Depends on the
	* driver / extension. To be overridden by the driver.
	***********************************************************/
	
	function getCharSet() { return false; }
	
	function getCollation() { return false; }
	
	
	/*
	function getCharSets() { return array(); }
	*/
	
	
	/***********************************************************
	* Returns true if connected to database.
	***********************************************************/
	
	function isConnected() {
    	return !empty($this->_conn);
	}
	
	
	/***********************************************************
	* Returns true if the driver can prepare statements.
	***********************************************************/
	
	function hasPrepare() {
		return $this->_hasPrepare;
	}
	
	
	/***********************************************************
	* Closes database connection
	***********************************************************/
	
	function close()
	{
		$ret = @ $this->_close();
		$ignore = null;
		$this->_conn = $ignore;
		if (!$ret) {
			trigger_error( 'YADB: Could not close the connection. Reason unknown.', E_USER_NOTICE );
			return false;
		}
		return $ret;
	}
	
	
	/***********************************************************
	* Really closes database connection
	***********************************************************/
	
	function _close() { return true; }
	
	/***********************************************************
	* Destructor. Only Recognized by PHP 5 but does not disturb
	* PHP 4.
	***********************************************************/
	function __destruct()
	{
		$this->close();
	}
	
	
	
	
	/***********************************************************
	* Do a SELECT, getting $nRows rows from $offset (1-based).
	* 
	* Simulates MySQL's "SELECT * FROM table LIMIT $offset,
	* $nRows",
	* PostgreSQL's "SELECT * FROM table LIMIT $nRows OFFSET
	* $offset" and
	* "SELECT TOP ..." for Microsoft databases (when $this->
	* _hasTop is set).
	* 
	* Fails with LIMIT or TOP clause already present in query.
	* Does not work with prepared statements.   //FIXME  ???
	* 
	* selectLimit() must be used in favour of execute() for
	* SELECTs for true database abstraction. When selecting
	* (WHERE ...) on a non-unique column and you know the
	* number of returned rows in advance this is also faster
	* for most DBMSs than without limit.
	*
	* This must be implemented by the driver.
	***********************************************************/
	/*
	function & selectLimit( $query, $nRows=-1, $offset=-1, $inputArr=null )
	{
		$ret = $this->execute( $query, $inputArr );
	}
	*/
	
	
	/***********************************************************
	* Modifies the query so that only a limited number of rows
	* are returned.
	* 
	* MySQL: SELECT ... LIMIT offset,nRows
	* Postgres: SELECT ... LIMIT nRows OFFSET offset
	* Microsoft DBs: SELECT TOP nRows ...
	* 
	* Use $nRows=-1 for no restiction on the number of records
	* or $offset=-1 for no offset (offset is 0-based).
	* 
	* $calcAll gives a hint to drivers which support it that
	* right after executing the query we'll ask for the number
	* of records that would have been returned *without* the
	* limit clause. (This is faster than executing the query
	* once again without the limit clause.)
	* 
	* Does not work with queries already including the limit
	* clause nor with very special queries that would need
	* complicated parsing.
	***********************************************************/
	
	function getSelectLimitSql( $sql, $nRows=-1, $offset=-1, $calcAll=false )
	{
		return $sql;
	}
	
	
	/***********************************************************
	* See getSelectLimitSql().
	* Returns -1 if unknown.
	***********************************************************/
	
	function numFoundRows() { return -1; }
	
	
	
	/***********************************************************
	* Prepares an SQL query into a prepared statement.
	***********************************************************/
	
	function & prepare()
	{
		$false = false;
		return $false;
	}
	
	
	
	function setQueryCb( $fn )
	{
		$this->_customQueryCb = $fn;
	}
	
	function setQueryErrCb( $fn )
	{
		$this->_customQueryErrCb = $fn;
	}
	
	
	function & execute( $sql, $inputArr=null )
	{
		if ($inputArr) {
			if (! $this->_hasBindParams) {
				if (! $sql = @ $this->_emulateVarBind( $sql, $inputArr ))
					return false;
				$rs = $this->_execute( $sql );
			} else
				$rs = $this->_execute( $sql, $inputArr );
		} else
			$rs = $this->_execute( $sql );
		
		if (is_object($rs) && is_array($rs->_row)) {
			if (count($rs->_row) < $rs->_numCols) {
				$caller_info = '';
				$bt = debug_backtrace();
				if (is_array($bt) && array_key_exists(0,$bt)) {
					$caller_info = @$bt[0]['file'] . ':'. @$bt[0]['line'];
				}
				trigger_error( 'YADB: Non-unique column names in a query are not supported. Fix your query. ('. $caller_info .')', E_USER_WARNING );
			}
		}
		
		return $rs;
	}
	
	
	// will only receive $inputArr if driver supports binding of
	// params to plain SQL queries (_hasBindParams=true)
	function & _execute( $sql, $inputArr=null )
	{
		$rs = @ $this->_queryMon( $sql, $inputArr );
		
		if (!$rs) {
			// query failed. error handling:
			
			$fn = $this->_queryErrFn;
			if ($fn) // handler for query errors
				@ $this->$fn();
			
			$fn = $this->_customQueryErrCb;
			if ($fn) // custom handler for query errors
				@ $fn( /*&*/$this, $sql, $inputArr );
			
			$false = false;
			return $false;
		}
		
		if ($rs === true) {
			// return simplified record set for INSERT/UPDATE/
			// DELETE queries which just return true on success
			//$rs = new YADB_RecordSet_empty;
			//return $rs;
			
			// what do we need the simplified record set for? we
			// can as well just return true
			$true = true;
			return $true;
		}
		
		// return real record set for SELECT queries:
		$rsClass = 'YADB_RecordSet_'. $this->_dbType;
		$rs = new $rsClass( $rs, $this, $sql );
		return $rs;
	}
	
	
	/***********************************************************
	* Wrapper for _query(). Calls the $_customQueryCb callback
	* if set.
	***********************************************************/
	
	function _queryMon( $sql, $inputArr=null )
	{
		$fn = $this->_customQueryCb;
		if (function_exists($fn)) {
			if (@ $fn( /*&*/$this, $sql, $inputArr ) === false) {
				trigger_error( 'YADB: Custom callback made the query fail deliberately.', E_USER_NOTICE );
				return false;
			}
		}
		return /*$ret =*/ $this->_query( $sql, $inputArr );
		/*
		if (! $ret) {
			trigger_error( sPrintF(
				'YADB: SQL error %s / %s %s "%s" in query: %s -',
				$this->getLastErrorCode(),
				$this->_dbType,
				$this->getLastNativeError(),
				$this->getLastNativeErrorMsg(),
				str_replace(array("\n","\t"), array('\n','\t'), $sql)
				), E_USER_WARNING );
		}
		*/
		/*return $ret;*/
	}
	
	/***********************************************************
	* The most low-level function which really sends queries
	* to the database. Returns a result resource which is
	* specific to the driver or false on error. The driver
	* should simply return true for inserts/updates/deletes
	* for lower overhead. _queryMon() is the only function
	* which is allowed to call _query().
	***********************************************************/
	
	function _query( $sql, $inputArr=null )
	{
		trigger_error( 'YADB: Query failed. (Dummy function in abstract class)', E_USER_WARNING );
		return false;
	}
	
	
	// Standard transaction monitor. Will be called by _execute()
	// if query fails. Makes the transaction fail and be rolled
	// back.
	function _transMonitor()
	{
		$this->_transErr = true;
	}
	
	
	
	
	
	
	/*
	function _exec( $sql, $inputArr=null )
	{
		if (is_array($inputArr)) {
			$sql = $this->_emulateVarBind( $sql, $inputArr );
			if (empty($sql)) return false;
		}
		return $this->_queryMon( $sql );
	}
	*/
	
	
	
	
	
	
	/***********************************************************
	* See execute().
	* or _queryMon() but tells the driver that we're not
	* interested in a full result set but only in the value
	* of one field. Returns the value or null on error.
	* Attention: The return value null can either be the value
	* of the field or indicate an error!
	* //FIXME
	***********************************************************/
	
	function executeGetOne( $sql, $inputArr=null )
	{
		$rs = $this->execute( $sql, $inputArr );
		if ($rs) {
			if ($rs === true) return true;
			if (! $rs->EOF) {
				//$ret = reset( $rs->getRow() );
				$ret = $rs->getRow();
				$ret = reset( $ret );
				$rs->close();
				return $ret;
			}
		}
		return false;
	}
	
	
	/***********************************************************
	* Like _queryMon() but requires the query to be a prepared
	* statement.
	***********************************************************/
	
	function _queryPrep( $stmt, $inputArr=null )
	{
		if (! is_a( $stmt, 'YADB_Statement' )) {
			trigger_error( 'YADB: Not a prepared statement.', E_USER_NOTICE );
			return false;
		}
		// This is deprecated as of PHP 5 (but still supported).
		// But we cannot use the PHP 5 way of soing this (using
		// the instanceOf type operator as it will cause a parse
		// error in PHP 4.
		
		return $this->_queryMon( $stmt->getSql(), $inputArr );
	}
	
	
	/***********************************************************
	* Like _queryGetOne() but requires the query to be a
	* prepared statement.
	***********************************************************/
	
	/*function _queryPrepGetOne( $stmt, $inputArr=null )
	{
		if (! is_a( $stmt, 'YADB_Statement' )) {
			trigger_error( 'YADB: Not a prepared statement.', E_USER_NOTICE );
			return false;
		}
		
		return $this->_queryGetOne( $stmt->getSql(), $inputArr );
	}*/
	
	
	/***********************************************************
	* "Inserts" variables into a query "template". Can be used
	* if driver does not natively support parameter binding.
	* Returns SQL on success or false on error.
	***********************************************************/
	
	function _emulateVarBind( $sql, $inputArr=null )
	{
		if (is_array($inputArr)) {
			$sqlArr = explode('?',$sql);
			$sql = '';
			$i = 0;
			foreach($inputArr as $v) {
				$sql .= $sqlArr[$i];
				// quote only strings:
				if ($v===null)
					trigger_error( 'YADB: Binding of NULL values probably not supported by driver.', E_USER_NOTICE );
				$sql .= $this->quote($v);
				++$i;
			}
			if (@ isSet($sqlArr[$i])) $sql .= $sqlArr[$i];
			if ($i+1 !== count($sqlArr)) {
				trigger_error( 'YADB: Input array does not match "?".', E_USER_WARNING );
				return false;
			}
		}
		return $sql;
	}
	
	
	
	/***********************************************************
	* Locks one (or more) rows at DBMS level. Will escalate and
	* lock the table if row locking is not supported.
	* $table: name of the table to lock
	* $where: where clause to use, eg. "WHERE id=3". If left
	* empty will escalate to table lock.
	***********************************************************/
	/*
	function lockRows( $table, $where )
	{
	}
	*/
	
	
	
	
	function setCharSet( $charset, $collation=null )
	{ return false; }
	
	
	/*
	function _findSchema( &$table, &$schema )
	{
		if (!$schema && ($at=strPos($table,'.')) !== false) {
			$schema = subStr($table,0,$at);
			$table = subStr($table,$at+1);
		}
	}
	*/
	
	
	/***********************************************************
	* Returns information about columns in a table.
	***********************************************************/
	
	function & colsMeta( $table )
	{
		$false = false;
		return $false;
	}
	
	/***********************************************************
	* Used by colsMeta().
	* 
	* C = character
	* X = binary
	* I = integer
	* P = like I and is primary key
	* R = like P and is auto-increment (/serial/oid/_rowid)
	* N = numeric/floating point
	* B = boolean
	***********************************************************/
	
	function _colMetaType( &$colMeta ) {}
	
	
	/**
	 *	C for Character < 250 chars
	 *	X for teXt (>= 250 chars)
	 *	B for Binary
	 * 	N for Numeric or floating point
	 *	D for Date
	 *	T for Timestamp
	 * 	L for Logical/boolean
	 *	I for Integer
	 *	R for autoincRement counter/integer
	 */
	 /*
	function _fieldTypeMeta( $type, $maxLen, $dbms='', $isPrimaryKey=null, $isBinary=null, $isDateTime=null )
	{
		// array is faster than switch statement...
		static $typeMap = array(
			'VARCHAR'   => 'C',
			'VARCHAR2'  => 'C',
			'CHAR'      => 'C',
			'C'         => 'C',
			'STRING'    => 'C',
			'NCHAR'     => 'C',
			'NVARCHAR'  => 'C',
			'VARYING'   => 'C',
			'BPCHAR'    => 'C',
			'CHARACTER' => 'C',
			'INTERVAL'  => 'C',  // Postgres
			##
			'LONGCHAR'  => 'X',
			'TEXT'      => 'X',
			'NTEXT'     => 'X',
			'M'         => 'X',
			'X'         => 'X',
			'CLOB'      => 'X',
			'NCLOB'     => 'X',
			'LVARCHAR'         => 'X',
			##
			'BLOB'             => 'B',
			'IMAGE'            => 'B',
			'BINARY'           => 'B',
			'VARBINARY'        => 'B',
			'LONGBINARY'       => 'B',
			'B'                => 'B',
			##
			'YEAR'             => 'D', // MySQL
			'DATE'             => 'D',
			'D'                => 'D',
			##
			'TIME'             => 'T',
			'TIMESTAMP'        => 'T',
			'DATETIME'         => 'T',
			'TIMESTAMPTZ'      => 'T',
			'T'                => 'T',
			##
			'BOOL'             => 'L',
			'BOOLEAN'          => 'L',
			'BIT'              => 'L',
			'L'                => 'L',
			##
			'COUNTER'          => 'R',
			'R'                => 'R',
			'SERIAL'           => 'R', // IFX
			'INT IDENTITY'     => 'R',
			##
			'INT'              => 'I',
			'INT2'             => 'I',
			'INT4'             => 'I',
			'INT8'             => 'I',
			'INTEGER'          => 'I',
			'INTEGER UNSIGNED' => 'I',
			'SHORT'            => 'I',
			'TINYINT'          => 'I',
			'SMALLINT'         => 'I',
			'I'                => 'I',
			##
			'LONG'             => 'N', // Interbase: numeric, oci8: blob
			'BIGINT'           => 'N', // this is bigger than PHP 32-bit integers
			'DECIMAL'          => 'N',
			'DEC'              => 'N',
			'REAL'             => 'N',
			'DOUBLE'           => 'N',
			'DOUBLE PRECISION' => 'N',
			'SMALLFLOAT'       => 'N',
			'FLOAT'            => 'N',
			'NUMBER'           => 'N',
			'NUM'              => 'N',
			'NUMERIC'          => 'N',
			'MONEY'            => 'N',
			## informix 9.2
			'SQLINT'           => 'I',
			'SQLSERIAL'        => 'I',
			'SQLSMINT'         => 'I',
			'SQLSMFLOAT'       => 'N',
			'SQLFLOAT'         => 'N',
			'SQLMONEY'         => 'N',
			'SQLDECIMAL'       => 'N',
			'SQLDATE'          => 'D',
			'SQLVCHAR'         => 'C',
			'SQLCHAR'          => 'C',
			'SQLDTIME'         => 'T',
			'SQLINTERVAL'      => 'N',
			'SQLBYTES'         => 'B',
			'SQLTEXT'          => 'X'
		);
		$type = strToUpper($type);
		$tMap = @ isSet($typeMap[$type]) ? $typeMap[$type] : 'N';
		switch ($tMap) {
			case 'C':
				// if char field too long, return it as text field:
				if ($maxLen > 100) return 'X';
				return 'C';
			case 'I':
				if ($isPrimaryKey===true) return 'R';
				return 'I';
			case 'B':
				if ($isBinary===false) return 'X';
				return 'B';
			case 'D':
				if ($isDateTime===true) return 'T';
				return 'D';
			default:
				if ($type==='LONG' && $dbms==='oci8') return 'B';
				return $tMap;
		}
		
		
	}
	*/
	
	
	
	/***********************************************************
	* Starts a transaction. Must be followed by _commitTrans()
	* or _rollbackTrans().
	* Returns true on success or false if database does not
	* support transactions. In the latter case all fallowing
	* queries will be auto-committed. Nonetheless you must(?)
	* use _commitTrans()
	***********************************************************/
	
	function _startTrans() { return false; }
	
	
	/***********************************************************
	* Commits a transaction. Returns true on success or false
	* if committing failed for some reason.
	* If database does not support transactions always return
	* true as data is always auto-commited.
	***********************************************************/
	
	function _commitTrans() { return true; }
	
	
	/***********************************************************
	* Rollback a transaction. Returns true on success or false
	* if rollback failed for some reason.
	* If database does not support transactions always return
	* false as data is always auto-commited.
	***********************************************************/
	
	function _rollbackTrans() { return false; }
	
	
	/***********************************************************
	* Starts a transaction. Must be followed by completeTrans()
	* or rollbackTrans().
	* Returns true on success or false if database does not
	* support transactions. In the latter case all fallowing
	* queries will be auto-committed. Nonetheless you must(?)
	* use completeTrans().
	* 
	* -  startTrans()/competeTrans() is nestable, unlike
	*    _startTrans()/_competeTrans()
	* -  completeTrans() auto-detects SQL errors and will roll-
	*    back on errors, commit otherwise
	* -  _startTrans()/_competeTrans() inside a startTrans()/
    *    competeTrans() block is disabled
	***********************************************************/
	
	function startTrans()
	{
		if ($this->_transOff > 0) {
			// we already have an outermost transaction
			++$this->_transOff;
			return true;
		}
		
		$this->_queryErrFn = '_transMonitor';
		$this->_transErr = false;
		
		if ($this->_transCnt > 0)
			trigger_error( 'YADB: Bad transaction: startTrans() called within _startTrans().', E_USER_WARNING );
		$ret = $this->_startTrans();
		$this->_transOff = 1;
		return $ret;
	}
	
	
	/***********************************************************
	* Used together with startTrans() to end a transaction.
	* Monitors connection for SQl errors and if $autoComplete
	* is set to true will commit or rollback as appropriate -
	* if set to false rollback is forced.
	* 
	* Returns true on commit, false on rollback (not on error!).
	***********************************************************/
	
	function completeTrans( $autoComplete=true )
	{
		if ($this->_transOff > 1) {
			// we have an outermost transaction and this is not
			// the outermost one
			--$this->_transOff;
			return true;
		}
		
		$this->_queryErrFn = null;
		
		$this->_transOff = 0;
		if (!$this->_transErr && $autoComplete) {
			// no SQL errors have occured during the transaction
			// and we are asked to commit
			if (! $this->_commitTrans() ) {
				$this->_transErr = true;
				trigger_error( 'YADB: Committing transaction failed.', E_USER_WARNING );
			}
		} else {
			// either SQL errors have occured during the transaction
			// or we are asked to rollback even if no SQL error
			// detected
			$this->_rollbackTrans();
		}
		return !$this->_transErr;
	}
	
	
	/***********************************************************
	* Can be used inside a startTrans()/completeTrans() block if
	* you want the outermost transaction to fail and be rolled
	* back.
	***********************************************************/
	
	function failTrans()
	{
		if ($this->_transOff < 1)
			trigger_error( 'YADB: failTrans() outside of startTrans()/completeTrans().', E_USER_WARNING );
		$this->_transErr = true;
	}
	
	
	/***********************************************************
	* Performs multiple queries separated by a semicolon at
	* once. Use hasExecuteMulti() to know if supported.
	* Some DBMSs don't return a result set at all, some return
	* multiple result sets. Use hasMoreResultSets() and
	* getNextResultSet().
	* 
	* Behaves like execute() and _execute():
	* Returns a YADB result set or false.
	***********************************************************/
	
	function & executeMulti( $sql )
	{
		$false = false;
		return $false;
	}
	
	/***********************************************************
	* Returns true if driver allows executeMulti()
	***********************************************************/
	
	function hasExecuteMulti() {
		return $this->_hasExecMulti;
	}
	
	
	/***********************************************************
	* Some DBMSs return more than one result set for
	* executeMulti().
	* Returns true if more result sets are available.
	***********************************************************/
	
	function hasMoreResultSets() { return false; }
	
	
	/***********************************************************
	* See executeMulti().
	* 
	* Behaves like execute() and _execute():
	* Returns a YADB result set or false.
	***********************************************************/
	
	function & getNextResultSet()
	{
		$rs = @ $this->_getNextResultSet();
		
		if (!$rs) {
			// no more result sets or query failed (?)
			$false = false;
			return $false;
		}
		
		if ($rs === true) {
			// return simplified record set for INSERT/UPDATE/
			// DELETE queries which just return true on success
			//$rs = new YADB_RecordSet_empty;
			//return $rs;
			
			// what do we need the simplified record set for? we
			// can as well just return true
			$true = true;
			return $true;
		}
		
		// return real record set for SELECT queries:
		$rsClass = 'YADB_RecordSet_'. $this->_dbType;
		$rs = new $rsClass( $rs, $this, $sql );
		return $rs;
	}
	
	
	/***********************************************************
	* Returns next result set. Depends on the driver.
	***********************************************************/
	
	function _getNextResultSet() { return null; }
	
	
	/***********************************************************
	* Returns the column name escaped and enclosed in
	* _namesQuote.
	***********************************************************/
	
	function quoteCol( $columnName )
	{
		return $this->_namesQuote . $this->escape($columnName) . $this->_namesQuote;
	}
	
	
	/***********************************************************
	* Just to be on the safe side.
	***********************************************************/
	function __toString()
	{
		return '['.get_class($this).']';
	}
	
	
	/***********************************************************
	* Returns a record object or false on error.
	* 
	* Table row must have a meta-type "P" or "R" column -
	* primary key of type integer (any), optionally with auto-
	* increment.
	* 
	* Use $pkVal < 0 to get a new (empty) record object.
	***********************************************************/
	
	function & loadObject( $table, $pkVal )
	{
		$false = false;
		if ($pkVal==0) {
			trigger_error( 'YADB: Could not create object. 0 is not allowed for the primary key.', E_USER_NOTICE );
			return $false;
		}
		$cols = $this->colsMeta( $table );
		$pkCol = null;
		foreach ($cols as $colName => $colMeta) {
			if ($colMeta['mty'] === YADB_MTYPE_INT
				&& ($colMeta['mty'] & YADB_FLAG_TRUEPK)) {
				$pkCol = $colName;
				break;
			}
		}
		if ($pkCol==null) {
			trigger_error( 'YADB: Could not create object. No auto-inc primary key integer present in table "'. $this->escape($table) .'".', E_USER_NOTICE );
			return $false;
		}
		
		if ($pkVal > 0) {
			$rs = @ $this->execute( 'SELECT * FROM '. $this->quoteCol($table) .' WHERE '. $this->quoteCol($pkCol) .'='. (int)$pkVal );
			if (!$rs || !is_object($rs)) {
				trigger_error( 'YADB: Could not create object. Query failed.', E_USER_NOTICE );
				return $false;
			}
			if ($rs->EOF) {
				//echo 'SELECT * FROM '. $this->quoteCol($table) .' WHERE '. $this->quoteCol($pkCol) .'='. (int)$pkVal;
				trigger_error( 'YADB: Could not create object. Not found.', E_USER_NOTICE );
				return $false;
			}
			
			$row = $rs->getRow();
			$rs->close();
			foreach ($cols as $col => $colMeta) {
				if (isSet($row[$col]))
					$cols[$col]['val'] = $row[$col];
				else
					$cols[$col]['val'] = null;
				$cols[$col]['changed'] = false;
			}
			unset( $row );
		} else {
			foreach ($cols as $col => $colMeta) {
				$cols[$col]['val'] = $cols[$col]['def'];
				$cols[$col]['changed'] = false;
			}
		}
		
		$recObj = new YADB_RecordObject( $this, $table, $pkCol, $pkVal, $cols );
		
		unset( $cols );
		return $recObj;
	}
	
	
	/*
	function genSequenceId( $seqName, $startId=1 )
	{ return false; }
	
	function createSequence( $seqName, $startId=1 )
	{ return false; }
	
	function dropSequence( $seqName )
	{ return false; }
	*/
	
	
	function getLastInsertId() { return false; }
	
	
	function getLastNativeError()
	{
		return null;
	}
	
	function getLastNativeErrorMsg()
	{
		return 'Unknown error';
	}
	
	function getLastErrorCode()
	{
		return 'HY000';  # general unmapped error
	}
	
	
	
}




/***********************************************************
* The class for prepared statements.
***********************************************************/

class YADB_Statement
{
	var $_sql   = '';   /// plain SQL
	var $_drv   = '';   /// YADB driver used to prepare the stmt
	var $_stmt  = null; /// prepared statement. depends on driver
	function YADB_Statement( $sql, $driver, $stmt )
	{
		$this->_sql  = $sql;
		$class = 'YADB_Connection_'. $driver;
		if (! class_exists($class) ) {
			trigger_error( 'YADB: Failed to create statement. Driver does not exist.', E_USER_WARNING );
			return;
		}
		if (empty($stmt)) {
			trigger_error( 'YADB: Failed to create statement (is empty).', E_USER_WARNING );
			return;
		}
		$this->_drv  = $driver;
		$this->_stmt = $stmt;
	}
	function getSql()    { return $this->_sql;  }
	function getDriver() { return $this->_drv;  }
	function getStmt()   { return $this->_stmt; }
	
	function __toString()
	{
		return '['.get_class($this).']';
	}
}





/***********************************************************
* Lightweight record set when there are no records to be
* returned.
***********************************************************/
/*
class YADB_RecordSet_empty
{
	var $EOF       = true;
	var $_numRows  = 0;
	var $_row      = null;
	var $_conn     = null;
	//function EOF() { return true; }
	function numRows() { return 0; }
	function numCols() { return 0; }
	function fetchRow() { return false; }
	function close() { return true; }
	
	function __toString()
	{
		return '['.get_class($this).']';
	}
}
*/




/***********************************************************
* Include the base record set.
***********************************************************/

if       (YADB_PHPVER >= 50200) {
	include( YADB_DIR .'inc/base_rs_php52.php' );
} elseif (YADB_PHPVER >= 50000) {
	include( YADB_DIR .'inc/base_rs_php50.php' );
} else {
	include( YADB_DIR .'inc/base_rs_php4.php' );
}


/***********************************************************
* Record set class that represents the data set returned by
* the database.
***********************************************************/

class YADB_RecordSet extends YADB_BaseRS
{
	var $_conn      = null;   /// the parent YADB connection instance
	var $_rs        = null;   /// the result resource
	var $_sql       = null;   /// the SQL query used to generate this result set
	var $_row       = null;   /// holds the current row data
	var $_canSeek   = false;  /// indicates if seek (random access to a specific row in the recordset) is supported. see move() and _move().
	var $EOF        = false;  /// indicates if the current record position is after the last record; public but do not write
	var $_numRows   = -1;   /// number of rows or -1 if unknown
	var $_numCols   = -1;   /// number of columns
	var $_rowPos    = -1;     /// current record position
	var $_colsMeta  = null;   /// column meta info
	
	/***********************************************************
	* Constructor
	* $rs is the result resource returned by YADB_Connection->
	* _query().
	* Sub-classes should call this constructor in their own
	* constructor (if they have one) like so:
	* $this->YADB_RecordSet( $rs );   or
	* parent::YADB_RecordSet( $rs );
	***********************************************************/
	function YADB_RecordSet( &$rs, &$conn, $sql )
	{
		$this->_rs = $rs;
		$this->_conn = $conn;
		$this->_sql = $sql;
		if ($this->_rs) {
			// init record set:
			$this->_numRows = $this->_numRows();
			$this->_numCols = $this->_numCols();
		} else {
			$this->_numRows = 0;
			$this->_numCols = 0;
			//$this->_EOF       = true;
		}
		if ( !($this->_numRows===0) && $this->_numCols > 0 && $this->_rowPos === -1 ) {
			// if the rs has rows (even if count is unknown) and
			// cols and the record position is at the beginning
			// (before the first row)
			$this->_rowPos = 0;
			//if ( $this->_eof = ($this->_fetch()===false) ) {
			if ( $this->_fetchRow()===false ) {
				$this->_numRows = 0;  // _numRows could be null
			}
		} else
			$this->EOF = true;
	}
	
	/***********************************************************
	* Returns the number of rows in the recordset or -1 if
	* unknown.
	***********************************************************/
	function _numRows() { return -1; }
	
	/***********************************************************
	* Returns the number of columns in the recordset. Some
	* databases will set this to 0 if no records are returned.
	***********************************************************/
	function _numCols() { return 0; }
	
	/***********************************************************
	* Returns true if no more records in record set.
	* 
	* Technically: Returns the value of _eof which will be set
	* to true by _fetchRow() if no more rows.
	***********************************************************/
	//function EOF() { return $this->_eof; }
	
	/***********************************************************
	* Returns the number of rows in the recordset or null if
	* unknown. Technically: _numRows() is used to set $this->
	* _numRows once and this function returns that value.
	***********************************************************/
	function numRows() { return $this->_numRows; }
	
	/***********************************************************
	* Returns the number of columns in the recordset.
	* Technically: _numCols() is used to set $this->_numCols
	* once and this function returns that value.
	***********************************************************/
	function numCols() { return $this->_numCols; }
	
	/***********************************************************
	* Returns the number of the current row in the record set.
	***********************************************************/
	function rowPos()
	{
		return $this->_rowPos;
	}
	
	/***********************************************************
	* Fetches the next row from the record set into _row.
	* Returns true or false if no more rows.
	***********************************************************/
	function _fetchRow()
	{
		$this->_row = null;
		return false;
	}
	
	/***********************************************************
	* Returns the current row as an array (or null if EOF) and
	* fetches the next one. Like getRow() and moveNext().
	***********************************************************/
	function & fetchRow()
	{
		if (!$this->EOF) {
			$row = $this->_row;
			++$this->_rowPos;
			//if (!$this->_fetchRow()) $this->_eof = true;
			$this->_fetchRow();
			return $row;
		}
		$false = false;
		return $false;
	}
	
	// returns a row filled with the default or empty values
	// for each field of the correct type
	/*
	function getEmptyDefaultRow()
	{
		//FIXME
	}
	*/
	
	/***********************************************************
	* Moves to the next row. Returns true if not EOF.
	***********************************************************/
	function moveNext()
	{
		if (!$this->EOF) {
			++$this->_rowPos;
			if ($this->_fetchRow()) return true;
		}
		//$this->_eof = true;
		return false;
	}
	
	/***********************************************************
	* Moves to a specific row in the record set. Some databases
	* do not support access to previous rows (no scrolling
	* backwards).
	* $rowNum is the number of the row to move to (0-based).
	* Returns true if not EOF.
	***********************************************************/
	function move( $rowNum )
	{
		if ($rowNum ===$this->_rowPos) return true;
		if ($rowNum >= $this->_numRows || $rowNum < 0) return false;
		
		//$this->_eof = false;
		
		if ($this->_canSeek) {
			if ($this->_move($rowNum)) {
				$this->_rowPos = $rowNum;
				return ($this->EOF = !$this->_fetchRow());
			} else
				return false;
		} else {
			if ($rowNum < $this->_rowPos) return false;
			// cannot seek and cannot emulate scrolling backwards
			// emulate scrolling forwards:
			while (!$this->EOF && $this->_rowPos < $rowNum) {
				++$this->_rowPos;
				$this->_fetchRow();
			}
			return !($this->EOF);
		}
		
	}
	
	/***********************************************************
	* Really moves to a specific row in the record set.
	* $rowNum is the number of the row to move to (0-based).
	* Returns true or false.
	***********************************************************/
	function _move( $rowNum )
	{
		return false;
	}
	
	/***********************************************************
	* Moves to the first row in the record set. Many databases
	* do NOT support this. Returns true or false.
	***********************************************************/
	function moveFirst()
	{
		if ($this->_rowPos === 0) return true;
		return $this->move(0);
	}
	
	/***********************************************************
	* Moves to the last row in the record set.
	* Returns true or false.
	***********************************************************/
	function moveLast()
	{
		if (!($this->_numRows===null) && $this->_numRows >= 0)
			return $this->move( $this->_numRows - 1 );
		while (!$this->EOF) {
			$row = $this->_row;  // store row because we will move behind the last one
			$this->moveNext();
		}
		$this->_row = $row;
		return true;
	}
	
	/***********************************************************
	* Returns the current row as an array (or null if EOF).
	***********************************************************/
	function & getRow() {
		return $this->_row;
	}
	
	/***********************************************************
	* Returns the value of a field in the current row by
	* column name or null if field does not exists.
	***********************************************************/
	function getField( $colName )
	{
		return @ $this->_row[$colName];
	}
	
	/***********************************************************
	* Clean up record set. Returns true or false. Any sub-
	* sequent operations on this result set object are likely
	* to fail.
	***********************************************************/
	function close()
	{
		// do NOT free the connection object as this seems to
		// globally free the object and not merely the reference,
		// so dont't do this:
		// $this->_conn = null;
		$ret = $this->_close();
		$ignore = null;
		$this->_conn = $ignore;
		$this->_rs   = $ignore;
		return $ret;
	}
	
	/***********************************************************
	* Clean up record set. Returns true or false.
	***********************************************************/
	function _close() { return true; }
	
	/***********************************************************
	* Destructor. Only Recognized by PHP 5 but does not disturb
	* PHP 4.
	***********************************************************/
	function __destruct()
	{
		$this->close();
	}
	
	/***********************************************************
	* Returns a multi-dimensional array with meta information
	* about the columns in the record set or null if no meta
	* info can be fetched.
	***********************************************************/
	/*
	function getColsMeta()
	{
		if (!is_array($this->_colsMeta)) $this->_fetchColsMeta();
		return $this->_colsMeta;
	}
	*/
	
	/***********************************************************
	* Retrieves meta info about the columns and stores it in
	* _colsMeta. Returns true or false.
	***********************************************************/
	/*
	function _fetchColsMeta() { return false; }
	*/
	
	/***********************************************************
	* Just to be on the safe side.
	***********************************************************/
	function __toString()
	{
		return '['.get_class($this).']';
	}
	
}






/***********************************************************
* Record object class. Used by YADB_Connection->
* loadObject().
***********************************************************/

class YADB_RecordObject
{
	var $_conn   = null;
	var $_table  = null;
	var $_pkCol  = null;
	var $_pkVal  = null;
	var $_fields = null;
	
	function YADB_RecordObject( &$conn, $table, $pkCol, $pkVal, $fields )
	{
		// we don't do any checking here for quick creation of
		// the object
		$this->_conn   = $conn;
		$this->_table  = $table;
		$this->_pkCol  = $pkCol;
		$this->_pkVal  = $pkVal;
		$this->_fields = $fields;
	}
	
	function genHtmlInputName( $prop )
	{
		return 'yadb[obj]['. $this->_table .']['. $this->_pkVal .']['. $prop .']';
	}
	
	function simpleHtmlForm()
	{
		foreach ($this->_fields as $colName => $colMeta) {
			if ($colName !== $this->_pkCol) {
				echo '<label for="', $this->genHtmlInputName($colName), '">', $colName, '</label> &nbsp; ', "\n";
				echo '<input type="text" name="', $this->genHtmlInputName($colName),'" value="', $colMeta['val'],'" /><br />', "\n";
			}
		}
	}
	
	function print_r()
	{
		if (isSet($_SERVER['REQUEST_METHOD'])) {
			// show as pre-formatted text in browser
			echo '<pre>';
			$is_web = true;
		} else $is_web = false;
		echo "\n", 'YADB record object,  table: ', $this->_table, ', ', $this->_pkCol, ' = ', $this->_pkVal, '    ';
		echo '(', $this->genHtmlInputName('...'), ")\n";
		echo "(\n";
		$maxColName = 0;
		$maxColVal  = 0;
		foreach ($this->_fields as $colName => $colMeta) {
			if (strLen($colName) > $maxColName)
				$maxColName = strLen($colName);
			if (strLen($colMeta['val']) > $maxColVal)
				$maxColVal = strLen($colMeta['val']);
		}
		$maxColVal += 5;
		
		foreach ($this->_fields as $colName => $colMeta) {
			$v = $colMeta['val'];
			if     (is_integer($v)) $v = ' '. (string)$v;
			elseif (is_string ($v)) $v = '"'. addSlashes($v) .'"';
			elseif (is_null   ($v)) $v = ' NULL';
			elseif (is_double ($v)) {
				if ((double)(int)$v != $v) $v = (string)$v;
				else                       $v = (string)$v .'.0';
			}
			elseif (is_bool   ($v)) $v = $v ? ' true':' false';
			else                    $v = ' ?';
			
			/*
			// not a bottleneck function -> no need to speed up
			// this switch statement (see knowledge base):
			$t = getType($val);
			switch ($t) {
				case 'integer': $val = ' '. (string)$val; break;
				case 'string' : $val = '"'. addSlashes($val) .'"'; break;
				case 'NULL'   : $val = ' NULL'; break;
				case 'bool'   : $val = $val ? ' true' : ' false'; break;
				case 'float'  :
					if ((double)(int)$val != $val) $val = (string)$val;
					else $val = (string)$val .= '.0';
					break;
				default       : $val =
			}
			*/
			echo "  [ ", str_pad($colName, $maxColName), ' ] ', str_pad(YADB_mainTypeToStr($colMeta),5), ' ', str_pad($v, $maxColVal), ($colMeta['changed'] ? ' (update)':''), "\n";
		}
		echo $is_web ? ")</pre>\n" : ")\n";
	}
	
	/*
	function loadFromHttpRequest()
	{
		foreach ($_REQUEST['yadb']['obj'][$this->_table] as $pkVal => $props) {
			foreach ($props as $prop => $propVal) {
				if ($prop !== $this->_pkCol) {
					@ $this->setProp( $prop );
				}
			}
		}
	}
	*/
	
	function get( $prop )
	{
		if (isSet($this->_fields[$prop]))
			return $this->_fields[$prop]['val'];
		return null;
	}
	
	function set( $prop, $val )
	{
		if (! isSet($this->_fields[$prop])) {
			trigger_error( 'YADB: Undefined property "'. $prop .'".', E_USER_WARNING );
			return false;
		}
		
		if (! ($val===null)) {
			if ($prop == $this->_pkCol) {
				trigger_error( 'YADB: Cannot set primary key.', E_USER_WARNING );
				return false;
			}
			switch ($this->_fields[$prop]['mty']) {
				case YADB_MTYPE_INT  : $val =    (int)$val; break;
				case YADB_MTYPE_FLOAT: $val = (double)$val; break;
				case YADB_MTYPE_STR  : $val = (string)$val; break;
				case YADB_MTYPE_BOOL : $val =   (bool)$val; break;
			}
			$change = true;
		} else {
			if ($this->_fields[$prop]['flg'] & YADB_FLAG_PKPART) {
				trigger_error( 'YADB: Cannot set primary key to NULL.', E_USER_WARNING );
				return false;
			}
			if ($this->_fields[$prop]['flg'] & YADB_FLAG_NOTNULL) {
				trigger_error( 'YADB: Field does not allow NULL.', E_USER_WARNING );
				return false;
			}
			$change = true;
		}
		
		if ( $change && ! ($val===$this->_fields[$prop]['val']) ) {
			$this->_fields[$prop]['val']     = $val;
			$this->_fields[$prop]['changed'] = true;
		}
		return true;
	}
	
	
	/***********************************************************
	* Saves the record object to the database.
	* 
	* With $unset=false returns true or false on error.
	* With $unset=true returns the ID of the row or false on
	* error and destroys the record object instance.
	***********************************************************/
	
	function save( $unset=true )
	{
		if ($this->_pkVal > 0) {
			$sql = 'UPDATE '. $this->_conn->quoteCol( $this->_table ) .' ';
			$isFirst = true;
			foreach ($this->_fields as $colName => $field) {
				if ($field['changed']) {
					if ($isFirst) { $sql.='SET '; $isFirst=false; }
					else $sql .= ', ';
					$sql .= $this->_conn->quoteCol( $colName ) .'='. $this->_conn->quote( $field['val'] );
				}
			}
			if (!$isFirst)
				$sql .= ' WHERE '. $this->_conn->quoteCol( $this->_pkCol ) .'='. $this->_conn->quote( $this->_pkVal );
			else $sql = false;  // no fields were changed
		} else {
			$sql = 'INSERT INTO '. $this->_conn->quoteCol( $this->_table ) .' (';
			$isFirst = true;
			foreach ($this->_fields as $colName => $field) {
				if ($isFirst) { $values = ') VALUES ('; $isFirst=false; }
				else { $sql .= ',';  $values .= ', '; }
				$sql .= $this->_conn->quoteCol( $colName );
				if ($colName !== $this->_pkCol)
					$values .= $this->_conn->quote( $field['val'] );
				else
					$values .= $this->_conn->quote( null );
			}
			$sql .= $values .')';
		}
		
		if ($sql===false)
			$rowId = $this->_pkVal;
		else {
			$saved = $this->_conn->_execute( $sql );
			if ($saved) {
				// on UPDATE row id remains, on INSERT get new one:
				if ($this->_pkVal > 0)  $rowId = $this->_pkVal;
				else $rowId = $this->_conn->getLastInsertId();
			} else
				$rowId = false;
		}
		if ($rowId > 0) {
			$this->_pkVal = $rowId;
			$this->_fields[$this->_pkCol]['val'] = $rowId;
		}
		if ($unset) {
			$ret = ($rowId > 0) ? $rowId : false;
			$this->close();
			return $ret;
		} else
			return ($rowId > 0);
	}
	
	
	function delete( $unset=true )
	{
		if ($this->_pkVal > 0) {
			$ok = $this->_conn->_execute( 'DELETE FROM '. $this->_conn->quoteCol( $this->_table ) .' WHERE '. $this->_conn->quoteCol( $this->_pkCol ) .'='. $this->_conn->quote( $this->_pkVal ) );
			if ($unset) @ $this->close();
			return $ok;
		} else
			return false;
	}
	
	
	function close()
	{
		$ignore = null;
		$this->_conn = $ignore;
		unset( $this->_table );
		unset( $this->_pkCol );
		unset( $this->_pkVal );
		unset( $this->_fields);
	}
	
	function loadFromHttpRequest()
	{
		if ($this->_pkVal < 0) $this->_pkVal = (string)$this->_pkVal;
		// we cannot access the array entry with a negative number
		// what's the reason for that???
		foreach ($_REQUEST['yadb']['obj'][$this->_table][$this->_pkVal] as $prop => $val)
			if ($prop !== $this->_pkCol)  $this->set( $prop, $val );
	}
	
	
	/***********************************************************
	* Just to be on the safe side.
	***********************************************************/
	function __toString()
	{
		return '['.get_class($this).']';
	}
}




function YADB_debug_backtrace()
{
	if (function_exists('debug_backtrace')) {
		$bt = debug_backtrace();
		$indent = 0;
		for ($i=count($bt)-1; $i>0; --$i) {
			// $i=0 is this function
			//echo str_repeat('    ',$indent), ($indent>0) ? '`-  ':'';
			echo str_repeat(' ',$indent), ($indent>0) ? '':'';
			
			if (isSet($bt[$i]['class']) && $bt[$i]['class'] != '')
				echo $bt[$i]['class'], ' ', $bt[$i]['type'], ' ';
			echo $bt[$i]['function'], '( ';
			for ($j=0; $j<count($bt[$i]['args'])-1; ++$j)
				echo $bt[$i]['args'][$j], ', ';
			echo $bt[$i]['args'][$j], ' )';
			echo '    from  ', (subStr( $bt[$i]['file'], 0, strLen(YADB_DIR) ) === YADB_DIR) ? subStr( $bt[$i]['file'], strLen(YADB_DIR) ) : $bt[$i]['file'], ', ', $bt[$i]['line'];
			echo "\n";
			++$indent;
		}
	}
}



/*
// this function is about 80 x slower than native addition
// (without GMP (GNU MP lib) available)
function bigint_add( $intStr1, $intStr2 )
{
	if (function_exists( 'gmp_add' ))
		return gmp_strval( gmp_add( $intStr1, $intStr2 ) );
	
	$res = array();
	$c = max( strLen( $intStr1 ), strLen( $intStr2 ) );
	$intStr1 = str_pad( $intStr1, $c, '0', STR_PAD_LEFT );
	$intStr2 = str_pad( $intStr2, $c, '0', STR_PAD_LEFT );
	for ($i=1; $i<=$c; ++$i)
		$res[] = subStr($intStr1, -$i, 1) + subStr($intStr2, -$i, 1);
	for ($i=0; $i<$c-1; ++$i)
		{ if ($res[$i] > 9) { $res[$i+1] +=1; $res[$i] -=10; } }
	kRSort( $res );
	return implode('', $res);
}
*/


function & YADB_httpRecordObjects( $table )
{
	if (!isSet( $_REQUEST['yadb']['obj'][$table] ) ||
		!is_array( $_REQUEST['yadb']['obj'][$table] )) {
		$false = false; return $false;
	}
	$objList = array();
	foreach ($_REQUEST['yadb']['obj'][$table] as $pkVal => $props) {
		$objList[] = (int)$pkVal;
	}
	return $objList;
}

// like YADB_httpRecordObjects() but assumes that there is only
// *one* object in the request.
// returns its ID (value of the pri.key) or null
function YADB_httpRecordObject( $table )
{
	if (! $arr = YADB_httpRecordObjects( $table ) ) return false;
	$id = @ reset( $arr );
	return ($id !== false ? $id : null);
}

// returns a string to be used in a URL to identify the object.
// this is a dirty shortcut!
// example:  yadb[obj][mytable][22][id]=22
// example:  yadb[obj][mytable][22][_]=2
/*
function YADB_genObjCallRequest( $table, $pkVal, $pkCol='id' )
{
	//return 'yadb[obj]['. $table .']['. $pkVal .']['. $pkCol .']='. $pkVal;
	return 'yadb[obj]['. $table .']['. $pkVal .'][_]=1';
	// this is fake but it doesn't matter
}
*/



function YADB_mainTypeToStr( $colMeta )
{
	static $tMap = array(
		 1 => 'int',
		 2 => 'str',
		 3 => 'float',
		 4 => 'bool'
	);
	$mt = $colMeta['mty'];
	//$ret = (@ isSet($tMap[$mt])) ? $tMap[$mt] : '';
	$ret = @ $tMap[$mt];
	//$ret .= '('. $colMeta['len'];
	//$ret .= ')';
	return $ret;
}

function YADB_subTypeToStr( $colMeta )
{
	static $tMap = array(
		 1 => 'INT_1',
		 2 => 'INT_2',
		 3 => 'INT_3',
		 4 => 'INT_4',
		 5 => 'INT_5',
		10 => 'STR_1',
		11 => 'STR_2',
		12 => 'STR_3',
		13 => 'STR_4',
		20 => 'ENUM',
		21 => 'SET',
		30 => 'FLOAT_4',
		31 => 'FLOAT_8',
		32 => 'FLOAT_DEC',
		40 => 'BOOL',
		41 => 'BITS',
		50 => 'DATE',
		51 => 'TIME',
		52 => 'DATETIME',
		53 => 'YEAR',
		54 => 'TSTAMP',
		60 => 'GEO'
	);
	$st = $colMeta['sty'];
	//$ret = (@ isSet($tMap[$st])) ? $tMap[$st] : '';
	$ret = @ $tMap[$st];
	//$ret .= '('. $colMeta['len'];
	//$ret .= ')';
	return $ret;
}


/*
$db = YADB_Connection::factory('mysql');
$db->connect( '', 'root', '', 'pwoffice2' );
if ( $mand = $db->loadObject('mand',2) )
	$mand->print_r();
*/

?>