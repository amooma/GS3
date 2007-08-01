<?php
/*
Copyright 2000-2005 John Lim (jlim@natsoft.com.my),
2006/2007, Philipp Kempgen <philipp.kempgen@amooma.de>,
amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
http://www.amooma.de/

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public License
(GNU/LGPL) as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this program; if not, write to the Free
Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
Boston, MA 02110-1301, USA.
*/


/*
  V4.61 24 Feb 2005  (c) 2000-2005 John Lim (jlim@natsoft.com.my). All rights reserved.
  Released under both BSD license and Lesser GPL library license. 
  Whenever there is any discrepancy between the two licenses, 
  the BSD license will take precedence.
  
  Set tabs to 4.
  
  Declares the ADODB Base Class for PHP5 "ADODB_BASE_RS", and supports iteration with 
  the ADODB_Iterator class.
  
  		$rs = $db->Execute("select * from adoxyz");
		foreach($rs as $k => $v) {
			echo $k; print_r($v); echo "<br>";
		}
		
		
	Iterator code based on http://cvs.php.net/cvs.php/php-src/ext/spl/examples/cachingiterator.inc?login=2
 */




if (!defined('YADB_DIR')) die();



/*

Base class for YADB record sets in PHP 5. Supports iterating over a record set with the YADB_RSIterator class.

Example:
$rs = $db->execute( 'SELECT * FROM my_table' );
foreach ($rs as $rowPos => $row) {
	echo $rowPos;  print_r( $row );  echo "<br />\n";
}

*/

class YADB_BaseRS implements IteratorAggregate
{
	function getIterator() {
		return new YADB_RSIterator( $this );
	}
	
	// no idea about this ...
	// does it belong to YADB_RSIterator ?
	/*
	function __toString()
	{
		return '[YADB record]';
	}
	*/
}


//class YADB_RSIterator implements Iterator
class YADB_RSIterator implements SeekableIterator, Countable
{	
	private $_rs;
	
	// required methods for Iterator:
	function rewind()   { $this->_rs->moveFirst();    }
	function current()  { return $this->_rs->_row;    }
	function key()      { return $this->_rs->_rowPos; }
	function next()     { $this->_rs->moveNext();     }
	function valid()    { return !$this->_rs->EOF;    }
	
	// required methods for Countable:
	function count()    { return $this->_rs->numRows(); }
	
	// required methods for SeekableIterator:
	function seek($pos) { $this->_rs->move($pos);     }
	
	
	// required methods for OuterIterator:
	//function getInnerIterator() { return $this; }
	//FIXME ???     return $this->_rs;   ?
	
	// required methods for CachingIterator:
	function __construct( $rs ) { $this->_rs = $rs; }
	//function hasNext()  { return !$this->_rs->EOF;    }
	/*
	function __call( $func, $params ) {
		return call_user_func_array(
			array( $this->_rs, $func ), $params
		);
	}
	*/
}


?>