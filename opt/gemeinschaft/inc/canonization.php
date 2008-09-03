<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
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

defined('GS_VALID') or die('No direct access.');

require_once( dirName(__FILE__) .'/../inc/conf.php' );
include_once( GS_DIR .'inc/pcre_check.php' );
include_once( GS_DIR .'inc/log.php' );


class CanonicalPhoneNumber
{
	var $orig = '';
	var $norm = '';
	var $intl = '';
	var $natl = '';
	var $locl = '';
	var $in_prv_branch = false;
	var $extn = '';
	var $is_special = false;
	var $is_call_by_call = false;
	var $dial = '';
	var $errt = '';
	
	var $_INTL = '';
	var $_CNTR = '';
	var $_NATL = '';
	var $_NINT = false;
	var $_AREA = '';
	var $_LOCL = '';
	var $_CBCP = '';
	var $_SPCL = '//';
	
	/* constructor:
	 */
	function CanonicalPhoneNumber( $number )
	{
		$this->_INTL = $this->_cnf('GS_CANONIZE_INTL_PREFIX' , '00' );
		if ($this->_INTL === '+' || $this->_INTL == '')
			$this->_INTL = '00';
		$this->_CNTR = $this->_cnf('GS_CANONIZE_COUNTRY_CODE', '49' );
		$this->_NATL = $this->_cnf('GS_CANONIZE_NATL_PREFIX' , '0'  );
		$this->_NINT = gs_get_conf('GS_CANONIZE_NATL_PREFIX_INTL', false);
		$this->_AREA = $this->_cnf('GS_CANONIZE_AREA_CODE'   , '251');
		$this->_LOCL = $this->_cnf('GS_CANONIZE_LOCAL_BRANCH', '99999999');
		$this->_CBCP = $this->_cnf('GS_CANONIZE_CBC_PREFIX'  , '010');
		$this->_SPCL = gs_get_conf('GS_CANONIZE_SPECIAL', '//');
		if (! is_valid_pcre($this->_SPCL)) {
			gs_log( GS_LOG_WARNING, 'Your GS_CANONIZE_SPECIAL pattern is not a valid PCRE' );
			$this->_SPCL = '/^1(:1[0-9]{1,5}|9222)/';
			# 110, 112, 116116, 118.*, 19222 etc.
		}
		
		//$n = preg_replace('/[^0-9A-Z+*\-\/ ]/', '', strToUpper(trim( $number )));
		//$n = preg_replace('/[ \/\-]/', ' ', $n);
		$n = preg_replace('/[^0-9A-Z+*]/', '', strToUpper(trim( $number )));
		// "+" is allowed as the first char only:
		$n = preg_replace('/(?!^)\+/', '', $n);
		$this->orig  = $n;
		if ($this->orig != '') {
			$this->norm  = $this->_canonize();
			$this->_to_intl();
			$this->_to_natl();
			$this->_to_locl();
			$this->_check_prv_branch();
			if ($this->is_call_by_call) $this->in_prv_branch = false;
			
			if ($this->is_call_by_call) {
				$this->dial = '';
				$this->errt = 'cbc';
			} elseif ($this->in_prv_branch) {
				$this->dial = $this->extn;
				$this->errt = 'self';
			} elseif ($this->is_special) {
				$this->dial = $this->orig;
			} else {
				$this->dial = $this->natl;
			}
		} else {
			$this->errt = 'empty';
		}
	}
	
	function _cnf( $k, $default=null )
	{
		return preg_replace('/[^0-9*]/', '', gs_get_conf($k, $default));
	}
	
	function unvanitize( $number )
	{
		return preg_replace(
			array('/[ABC]/', '/[DEF]/', '/[GHI]/', '/[JKL]/', '/[MNO]/', '/[PQRS]/', '/[TUV]/', '/[WXYZ]/'),
			array('2','3','4','5','6','7','8','9'),
			$number
		);
	}
	
	function _canonize()
	{
		$n = $this->orig;
		
		$n = preg_replace('/[^0-9A-Z+*]/', '', strToUpper(trim( $n )));
		if (preg_match('/[A-Z]/', $n))
			$n = $this->unvanitize( $n );
		
		if (subStr($n,0,strLen($this->_CBCP)) === $this->_CBCP && $this->_CBCP != '') {
			$this->is_call_by_call = true;
		}
		
		$tmp = preg_replace('/[^0-9+*#]/', '', $this->_INTL);
		$n = preg_replace('/^'.$tmp.'/', '+', $n);
		
		# test if in international format (i.e. with country code):
		if (subStr($n,0,1) === '+') {
			return $n;
		}
		
		# test if special number (emergency etc.):
		if (preg_match('/^[0-9]+$/', $n)
		&&  preg_match( $this->_SPCL, $n ))
		{
			$this->is_special = true;
			return;
		}
		
		# test if in national format (i.e. with national prefix and area code):
		if (subStr($n,0,strLen($this->_NATL)) === $this->_NATL) {
			return '+'. $this->_CNTR . subStr($n,strLen($this->_NATL));
		}
		
		# test if in local format with own private branch:
		if (subStr($n,0,strLen($this->_LOCL)) === $this->_LOCL) {
			return '+'. $this->_CNTR . $this->_AREA . $n;
		}
		
		# test if might be local:
		if (strLen($n) >= 2)
			return '+'. $this->_CNTR . $this->_AREA . $n;
		
		# assume private own branch:
		return '+'. $this->_CNTR . $this->_AREA . $this->_LOCL . $n;;
	}
	
	function _to_intl()
	{
		if (subStr($this->norm, 0, 1) === '+')
			$this->intl = $this->_INTL . subStr($this->norm, 1);
		else
			$this->intl = $this->norm;
	}
	
	function _to_natl()
	{
		$tmp = '+'. $this->_CNTR;
		if (subStr($this->norm, 0, strLen($tmp)) === $tmp)
			$this->natl = $this->_NATL . subStr($this->norm, strLen($tmp));
		else
			$this->natl = $this->intl;
	}
	
	function _to_locl()
	{
		$tmp = '+'. $this->_CNTR . $this->_AREA;
		if (subStr($this->norm, 0, strLen($tmp)) === $tmp)
			$this->locl = subStr($this->norm, strLen($tmp));
		else
			$this->locl = $this->natl;
	}
	
	function _check_prv_branch()
	{
		# test if own local branch in international format:
		$tmp = '+'. $this->_CNTR . ($this->_NINT ? $this->_NATL : '') . $this->_AREA . $this->_LOCL;
		if (subStr($this->norm, 0, strLen($tmp)) === $tmp) {
			$this->in_prv_branch = true;
			$this->extn = subStr($this->norm, strLen($tmp));
		} else {
			$this->in_prv_branch = false;
			$this->extn = '';
		}
	}
	
}


?>