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
\*******************************************************************/
if (!defined('YADB_DIR')) die("No direct access\n");


/**
 * Class to work with an MPTT (modified preorder tree traversal) table
 * aka nested sets.
 * MPTT trees are - compared to adjacency lists - fast for retrieval
 * but slow for writes.
 */
class YADB_MPTT
{
	var $_db_conn;
	var $_db_table;
	var $_db_table_esc;
	var $_db_left           = 'lft';
	var $_db_left_esc       = 'lft';
	var $_db_right          = 'rgt';
	var $_db_right_esc      = 'rgt';
	var $_db_primary_id     = 'id';
	var $_db_primary_id_esc = 'id';
	var $_use_transactions  = true;
	
	/*
	function __construct( $conn, $table, $left, $right, $id )
	{
		return $this->YADB_MPTT( $conn, $table, $left, $right, $id );
	}
	*/
	
	# constructor for PHP 4
	function YADB_MPTT( $conn, $table, $left, $right, $id )
	{
		if (! $conn || ! $conn instanceof YADB_Connection) {
			trigger_error('First parameter passed must be a YaDB database connection.', E_USER_WARNING);
			return;
		}
		if (! $conn->isConnected()) {
			trigger_error('YaDB database connection is not connected.', E_USER_WARNING);
			return;
		}
		$this->_db_conn = $conn;
		
		if (! is_string($table)) {
			trigger_error('Invalid table.', E_USER_WARNING);
			return;
		}
		$this->_db_table = $table;
		
		if (is_string($left))
			$this->_db_left       = $left;
		if (is_string($right))
			$this->_db_right      = $right;
		if (is_string($id))
			$this->_db_primary_id = $id;
		
		$this->_db_table_esc      = $this->_db_conn->escape( $this->_db_table      );
		$this->_db_left_esc       = $this->_db_conn->escape( $this->_db_left       );
		$this->_db_right_esc      = $this->_db_conn->escape( $this->_db_right      );
		$this->_db_primary_id_esc = $this->_db_conn->escape( $this->_db_primary_id );
	}
	
	function use_transactions( $use_transactions )
	{
		$this->_use_transactions = (bool)$use_transactions;
	}
	
	function _db_query( $sql, $multi_rows=false )
	{
		$rs = $this->_db_conn->execute( $sql );
		if (! $rs) return false;
		
		if ($multi_rows) {
			$rows = array();
			while ($row = $rs->fetchRow()) {
				$rows[] = $row;
			}
			return $rows;
		}
		else {
			$row = $rs->fetchRow();
			return $row ? $row : null;
		}
	}
	
	/*
	function export()
	{
		$qry = 'SELECT a.{$this->_db_primary_id}, a.{$this->db_left}, a.{$this->db_right}, COUNT(b.{$this->db_primary_id}) ';
		$qry .= 'FROM {$this->db_table} a LEFT JOIN {$this->db_table} b ON (b.{$this->db_left} < a.{$this->db_left} AND b.{$this->db_right} > a.{$this->db_right}) ';
		$qry .= 'GROUP BY a.{$this->db_left} ORDER BY a.{$this->db_left} ASC';
		
		$rows = $this->db_query($qry, true);
		
		// export the tree
		echo '<table>';
		echo '<th>Export of Tree Structure for Table ['. $this->db_table .']</th>';
		if ($rows) {
			foreach($rows as $row) {
				echo '<tr>';
				echo '<td>'. str_repeat('&nbsp;|&nbsp;-&nbsp;-&nbsp;-&nbsp;', $row[3]) . $row[0] .' ('. $row[1] .', '. $row[2] .')</td>';
				echo '</tr>';
			}
		}
		
		echo '</table>';
	}
	*/
	
	
	function create_root_node()
	{
		$ok = $this->_db_conn->execute(
			'INSERT INTO `'. $this->_db_table_esc .'` SET '.
				'`'. $this->_db_primary_id_esc .'`=NULL, '.
				'`'. $this->_db_left_esc  .'`=1, '.
				'`'. $this->_db_right_esc .'`=2'
			);
		if ($ok) {
			$new_node_id = $this->_db_conn->getLastInsertId();
			if ($new_node_id < 1) {
				$ok = false;
				$new_node_id = null;
			}
		} else {
			$new_node_id = null;
		}
		return $ok ? $new_node_id : false;
	}
	
	
	function find_root_node()
	{
		$rs = $this->_db_conn->execute(
			'SELECT * '.
			'FROM `'. $this->_db_table_esc .'` '.
			'ORDER BY `'. $this->_db_left_esc .'` ASC '.
			'LIMIT 1'
			);
		$root = $rs->getRow();
		if (! $root) {
			$id = $this->create_root_node();
			if ($id) {
				$rs = $this->_db_conn->execute(
					'SELECT * '.
					'FROM `'. $this->_db_table_esc .'` '.
					'ORDER BY `'. $this->_db_left_esc .'` ASC '.
					'LIMIT 1'
					);
				$root = $rs->getRow();
			}
		}
		return $root ? $root : null;
	}
	
	
	function get_node( $id=null )
	{
		if ($id > 0) {
			$rs = $this->_db_conn->execute(
				'SELECT * '.
				'FROM `'. $this->_db_table_esc .'` '.
				'WHERE `'. $this->_db_primary_id_esc .'`='. ((int)$id)
				);
			$branch_node = $rs->getRow();
		} else {
			$branch_node = $this->find_root_node();
		}
		return $branch_node ? $branch_node : null;
	}
	
	
	function get_tree_as_list( $branch_id=null )
	{
		$branch_node = $this->get_node( $branch_id );
		if (! $branch_node) return false;
		
		$rs = $this->_db_conn->execute(
			'SELECT * '.
			'FROM `'. $this->_db_table_esc .'` '.
			'WHERE (`'. $this->_db_left_esc .'` BETWEEN '.
				(int)$branch_node[$this->_db_left] .' AND '.
				(int)$branch_node[$this->_db_right] .') '.
			'ORDER BY `'. $this->_db_left_esc .'` ASC'
			);
		$right = array();
		$list = array();
		$level = 0;
		while ($r = $rs->fetchRow()) {
			//print_r($r);
			if (count($right) > 0) {
				# remove a node from the stack?
				while ($right[count($right)-1] < $r[$this->_db_right]) {
					array_pop($right);
					--$level;
				}
			}
			
			# push node onto the list
			$r['__mptt_level'] = $level;
			array_push($list, $r);
			
			# add node to the stack
			$right[] = $r[$this->_db_right];
			++$level;
		}
		return $list;
	}
	
	
	/*
	function print_tree( $branch_id=null, $title_row )
	{
		$branch_node = $this->get_node( $branch_id );
		if (! $branch_node) return false;
		
		$rs = $this->_db_conn->execute(
			'SELECT * '.
			'FROM `'. $this->_db_table_esc .'` '.
			'WHERE (`'. $this->_db_left_esc .'` BETWEEN '.
				(int)$branch_node[$this->_db_left] .' AND '.
				(int)$branch_node[$this->_db_right] .') '.
			'ORDER BY `'. $this->_db_left_esc .'` ASC'
			);
		$right = array();
		while ($r = $rs->fetchRow()) {
			if (count($right) > 0) {
				# remove a node from the stack?
				while ($right[count($right)-1] < $r[$this->_db_right]) {
					array_pop($right);
				}
			}
			
			# print
			echo str_repeat('  ', count($right)), @$r[$title_row] ,"\n";
			
			# add node to the stack
			$right[] = $r[$this->_db_right];
		}
	}
	*/
	function print_tree( $branch_id=null, $title_row )
	{
		$list = $this->get_tree_as_list( $branch_id );
		if (! is_array($list)) return;
		foreach ($list as $node) {
			echo str_repeat('  ', $node['__mptt_level']), @$node[$title_row] ,"\n";
		}
	}
	
	
	function get_path_to_node( $id, $with_node=true )
	{
		$node = $this->get_node( $id );
		if (! $node) return false;
		$eq = $with_node ? '=':'';
		$rs = $this->_db_conn->execute(
			'SELECT * '.
			'FROM `'. $this->_db_table_esc .'` '.
			'WHERE '.
				'`'. $this->_db_left_esc  .'`<'.$eq. (int)$node[$this->_db_left ] .' AND '.
				'`'. $this->_db_right_esc .'`>'.$eq. (int)$node[$this->_db_right] .' '.
			'ORDER BY `'. $this->_db_left_esc .'` ASC'
			);
		$ancestors = array();
		while ($r = $rs->fetchRow()) {
			$ancestors[] = $r;
		}
		return $ancestors;
	}
	
	
	function num_descendants( $id )
	{
		$node = $this->get_node( $id );
		if (! $node) return false;
		return ((int)$node[$this->_db_right] - (int)$node[$this->_db_left] - 1) / 2;
	}
	
	
	function quick_sanity_check()
	{
		$root = $this->get_node( null );
		if (! $root) return false;
		
		$num_nodes = $this->_db_conn->executeGetOne(
			'SELECT COUNT(*) '.
			'FROM `'. $this->_db_table_esc .'`'
			);
		if ($root[$this->_db_right] != $num_nodes*2)
			return false;
		
		$n = $this->_db_conn->executeGetOne(
			'SELECT COUNT(*) '.
			'FROM `'. $this->_db_table_esc .'` '.
			'WHERE `'. $this->_db_right_esc  .'` > '. ($num_nodes*2)
			);
		if ($n != 0) return false;
		
		$n = $this->_db_conn->executeGetOne(
			'SELECT COUNT(*) '.
			'FROM `'. $this->_db_table_esc .'` '.
			'WHERE `'. $this->_db_left_esc  .'` < 1'
			);
		if ($n != 0) return false;
		
		$n = $this->_db_conn->executeGetOne(
			'SELECT COUNT(*) '.
			'FROM `'. $this->_db_table_esc .'` '.
			'WHERE `'. $this->_db_left_esc  .'` >= `'. $this->_db_right_esc .'`'
			);
		if ($n != 0) return false;
		
		return true;
	}
	
	
	function insert_as_last_child( $parent_id, $data )
	{
		if (! is_array($data)) return false;
		
		if ($this->_use_transactions) {
			$this->_db_conn->startTrans();
			$this->_db_conn->execute(
				'SELECT `'. $this->_db_primary_id_esc .'`, `'. $this->_db_left_esc .'`,`'. $this->_db_right_esc .'` '.
				'FROM `'. $this->_db_table_esc .'` '.
				'FOR UPDATE'
				);
		}
		
		$parent = $this->get_node( $parent_id );
		if (! $parent) {
			if ($this->_use_transactions)
				$this->_db_conn->completeTrans(false);
			return false;
		}
		
		# make space
		$query =
			'UPDATE `'. $this->_db_table_esc .'` SET '.
				'`'. $this->_db_right_esc .'`=`'. $this->_db_right_esc .'`+2 '.
			'WHERE `'. $this->_db_right_esc .'`>'. ((int)$parent[$this->_db_right] - 1) .' '.
			'/*!40000 ORDER BY `'. $this->_db_right_esc .'` DESC */';
		$this->_db_conn->execute($query);
		$query =
			'UPDATE `'. $this->_db_table_esc .'` SET '.
				'`'. $this->_db_left_esc .'`=`'. $this->_db_left_esc .'`+2 '.
			'WHERE `'. $this->_db_left_esc .'`>'. ((int)$parent[$this->_db_right] - 1) .' '.
			'/*!40000 ORDER BY `'. $this->_db_left_esc .'` DESC */';
		$this->_db_conn->execute($query);
		
		# insert node
		$set = '';
		foreach ($data as $col => $val) {
			if ($col !== $this->_db_primary_id
			&&  $col !== $this->_db_left
			&&  $col !== $this->_db_right
			&&  subStr($col,0,7) !== '__mptt_')
			{
				$set.= ', `'. $this->_db_conn->escape($col) .'`='. $this->_db_conn->quote($val);
			}
		}
		$query =
			'INSERT INTO `'. $this->_db_table_esc .'` SET '.
				'`'. $this->_db_primary_id_esc .'`=NULL, '.
				'`'. $this->_db_left_esc  .'`='. ((int)$parent[$this->_db_right]  ) .', '.
				'`'. $this->_db_right_esc .'`='. ((int)$parent[$this->_db_right]+1) .' '.
				$set;
		$ok = $this->_db_conn->execute($query);
		if ($ok) {
			$new_node_id = $this->_db_conn->getLastInsertId();
			if ($new_node_id < 1) {
				$ok = false;
				$new_node_id = null;
			}
		} else {
			$new_node_id = null;
		}
		
		if ($this->_use_transactions) {
			if (! $this->_db_conn->completeTrans())
				$ok = false;
		}
		@$this->_db_conn->execute( 'OPTIMIZE TABLE `'. $this->_db_table_esc .'`' );
		return $ok ? $new_node_id : false;
	}
	
	function insert( $parent_id, $data )
	{
		return $this->insert_as_last_child( $parent_id, $data );
	}
	
	
	function delete( $id, $keep_descendants=false )
	{
		if ($this->_use_transactions) {
			$this->_db_conn->startTrans();
			$this->_db_conn->execute(
				'SELECT `'. $this->_db_primary_id_esc .'`, `'. $this->_db_left_esc .'`,`'. $this->_db_right_esc .'` '.
				'FROM `'. $this->_db_table_esc .'` '.
				'FOR UPDATE'
				);
		}
		
		$node = $this->get_node( $id );
		if (! $node) {
			if ($this->_use_transactions)
				$this->_db_conn->completeTrans(false);
			return false;
		}
		if ($node[$this->_db_left] == 1) {
			# cannot delete root node
			if ($this->_use_transactions)
				$this->_db_conn->completeTrans(false);
			return false;
		}
		
		if (! $keep_descendants) {
			# delete the node including descendants
			$this->_db_conn->execute(
				'DELETE FROM `'. $this->_db_table_esc .'` '.
				'WHERE '.
					'`'. $this->_db_left_esc  .'` >= '. ((int)$node[$this->_db_left ]) .' AND '.
					'`'. $this->_db_right_esc .'` <= '. ((int)$node[$this->_db_right])
				);
			
			# remove the hole
			$diff = (int)$node[$this->_db_right] - (int)$node[$this->_db_left] + 1;
			$this->_db_conn->execute(
				'UPDATE `'. $this->_db_table_esc .'` SET '.
					'`'. $this->_db_left_esc .'` = `'. $this->_db_left_esc .'`-'. $diff .' '.
				'WHERE '.
					'`'. $this->_db_right_esc .'` > '. ((int)$node[$this->_db_right]) .' AND '.
					'`'. $this->_db_left_esc . '` > '. ((int)$node[$this->_db_right]) .' '.
				'/*!40000 ORDER BY `'. $this->_db_left_esc .'` ASC */'
				);
			$this->_db_conn->execute(
				'UPDATE `'. $this->_db_table_esc .'` SET '.
					'`'. $this->_db_right_esc .'` = `'. $this->_db_right_esc .'`-'. $diff .' '.
				'WHERE '.
					'`'. $this->_db_right_esc .'` > '. ((int)$node[$this->_db_right]) .' '.
				'/*!40000 ORDER BY `'. $this->_db_right_esc .'` ASC */'
				);
			
			$ok = true;
		}
		else {
			# delete only the node
			$this->_db_conn->execute(
				'DELETE FROM `'. $this->_db_table_esc .'` '.
				'WHERE '.
					'`'. $this->_db_primary_id_esc .'` = '. ((int)$id)
				);
			
			# fix the descendants
			$this->_db_conn->execute(
				'UPDATE `'. $this->_db_table_esc .'` SET '.
					'`'. $this->_db_left_esc  .'` = `'. $this->_db_left_esc  .'`-1, '.
					'`'. $this->_db_right_esc .'` = `'. $this->_db_right_esc .'`-1 '.
					//'`'. $this->_db_level_esc .'` = `'. $this->_db_level_esc .'`-1 '.
				'WHERE '.
					'`'. $this->_db_left_esc  .'` >= '. ((int)$node[$this->_db_left ]) .' AND '.
					'`'. $this->_db_right_esc .'` <= '. ((int)$node[$this->_db_right]) .' '.
				'/*!40000 ORDER BY `'. $this->_db_left_esc .'` ASC */'
				);
			
			# remove the hole
			$this->_db_conn->execute(
				'UPDATE `'. $this->_db_table_esc .'` SET '.
					'`'. $this->_db_left_esc .'` = `'. $this->_db_left_esc .'`-2 '.
				'WHERE '.
					'`'. $this->_db_right_esc .'` > '. ((int)$node[$this->_db_right] - 1) .' AND '.
					'`'. $this->_db_left_esc  .'` > '. ((int)$node[$this->_db_right] - 1) .' '.
				'/*!40000 ORDER BY `'. $this->_db_left_esc .'` ASC */'
				);
			$this->_db_conn->execute(
				'UPDATE `' . $this->_db_table_esc .'` SET '.
					'`'. $this->_db_right_esc .'` = `'. $this->_db_right_esc .'`-2 '.
				'WHERE '.
					'`'. $this->_db_right_esc .'` > '. ((int)$node[$this->_db_right] - 1) .' '.
				'/*!40000 ORDER BY `'. $this->_db_right_esc .'` ASC */'
				);
			
			$ok = true;
		}
		
		if ($this->_use_transactions) {
			if (! $this->_db_conn->completeTrans())
				$ok = false;
		}
		@$this->_db_conn->execute( 'OPTIMIZE TABLE `'. $this->_db_table_esc .'`' );
		return $ok;
	}
	
}


/*
include_once('./yadb.php');
$db = YADB_newConnection( 'mysql' );
$db->connect( '127.0.0.1', 'root', '', 'asterisk' );

$mptt = new YADB_MPTT($db, 'example_table', 'lft', 'rgt', 'id');

//$mptt->print_tree( null, 'title' );

$list = $mptt->get_tree_as_list( null );
if (is_array($list)) {
	$is_root = true;
	foreach ($list as $node) {
		if ($is_root) {  # skip root node
			$is_root = false;
			continue;
		}
		echo str_repeat('  ', $node['__mptt_level']), $node['title'] ,' (#', $node['id'] ,', ', $node['lft'] ,'-', $node['rgt'] ,')' ,"\n";
	}
}
echo "\n";

//$id = $mptt->insert(15, array('title'=>'Test-Kind'));
//var_dump($id);

//$ok = $mptt->delete(14, true);
//var_dump($ok);

$list = $mptt->get_tree_as_list( null );
if (is_array($list)) {
	$is_root = true;
	foreach ($list as $node) {
		if ($is_root) {  # skip root node
			$is_root = false;
			continue;
		}
		echo str_repeat('  ', $node['__mptt_level']), $node['title'] ,' (#', $node['id'] ,', ', $node['lft'] ,'-', $node['rgt'] ,')' ,"\n";
	}
}
echo "\n";

echo 'Structure is ', ($mptt->quick_sanity_check() ? 'valid':'INVALID') ,"\n";
*/


?>