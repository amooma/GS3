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

class CronRule
{
	var $r = array(
		'min' => '',
		'hr'  => '',
		'day' => '',
		'mon' => '',
		'dow' => ''
	);
	var $e = array(
		'min' => array(),
		'hr'  => array(),
		'day' => array(),
		'mon' => array(),
		'dow' => array()
	);
	var $err = false;
	var $err_msg = '';
	
	function _val_to_int( &$key, &$val )
	{
		static $dows = array(
			'mo' => 1, 'tu' => 2, 'we' => 3, 'th' => 4, 'fr' => 5, 'sa' => 6, 'su' => 0,
			'mon'=> 1, 'tue'=> 2, 'wed'=> 3, 'thu'=> 4, 'fri'=> 5, 'sat'=> 6, 'sun'=> 0
		);
		static $months = array(
			'jan'=> 1, 'feb'=> 2, 'mar'=> 3, 'apr'=> 4, 'may'=> 5, 'jun'=> 6,
			'jul'=> 7, 'aug'=> 8, 'sep'=> 9, 'oct'=>10, 'nov'=>11, 'dec'=>12
		);
		if (! preg_match('/[^0-9]/S', $val)) {
			$val = (int)$val;
			return true;
		}
		else {
			switch ($key) {
				case 'dow':
					$val = strToLower($val);
					if (array_key_exists($val, $dows)) {
						$val = $dows[$val];
						return true;
					} else {
						$this->err = true;
						$this->err_msg = 'Invalid rule. "'.$val.'" not valid for '.$key.'.';
						return false;
					}
					break;
				case 'mon':
					$val = strToLower($val);
					if (array_key_exists($val, $months)) {
						$val = $months[$val];
						return true;
					} else {
						$this->err = true;
						$this->err_msg = 'Invalid rule. "'.$val.'" not valid for '.$key.'.';
						return false;
					}
					$val = (int)$val;
					return true;
					break;
				default:
					$this->err = true;
					$this->err_msg = 'Invalid rule. "'.$val.'" not valid for '.$key.'.';
					return false;
			}
		}
	}
	
	function set_rule( $cron_rule )
	{
		$this->err = false;
		$this->err_msg = '';
		$parts = preg_split('/[ \\t]+/', $cron_rule, 6);
		if (count($parts) !== 5) {
			$this->err = true;
			$this->err_msg = 'Invalid rule. Wrong number of parts.';
			return false;
		}
		list( $this->r['min'], $this->r['hr'], $this->r['day'], $this->r['mon'], $this->r['dow'] ) = $parts;
		
		foreach ($this->r as $key => $def) {
			switch ($key) {
				case 'min': $kf = 0; $kt = 59; break;
				case 'hr' : $kf = 0; $kt = 23; break;
				case 'day': $kf = 1; $kt = 31; break;
				case 'mon': $kf = 1; $kt = 12; break;
				case 'dow': $kf = 0; $kt =  6; break;
			}
			if ($def === '*') {  # optimization
				for ($i=$kf; $i<=$kt; ++$i) $this->e[$key][$i] = true;
				continue;
			} else {
				for ($i=$kf; $i<=$kt; ++$i) $this->e[$key][$i] = false;
			}
			
			$ranges = explode(',', $def);
			foreach ($ranges as $range) {
				@list($v, $divisor) = explode('/', $range);
				$divisor = (int)$divisor;
				if ($divisor > 0) {
					if ($v !== '*' && $v !== '') {
						$this->err = true;
						$this->err_msg = 'Invalid rule. "/" without "*".';
						return false;
					}
					if ($kf === 0) {  # optimization
						for ($i=$kf; $i<=$kt; $i+=$divisor) {
							$this->e[$key][$i] = true;
						}
					} else {
						for ($i=$kf; $i<=$kt; ++$i) {
							if ($i % $divisor === 0) $this->e[$key][$i] = true;
						}
					}
				}
				else {
					@list($vf, $vt) = explode('-', $range);
					if ($vt !== null) {
						if (! $this->_val_to_int( $key, $vf )) return false;
						if (! $this->_val_to_int( $key, $vt )) return false;
						if ($vt < $vf) {
							$this->err = true;
							$this->err_msg = 'Invalid range "'.$vf.'-'.$vt.'".';
							return false;
						}
						for ($i=$vf; $i<=$vt; $i++) {
							if (array_key_exists($i, $this->e[$key])) {
								$this->e[$key][$i] = true;
							}
						}
					}
					else {
						if ($vf === '*') {
							for ($i=$kf; $i<=$kt; ++$i) $this->e[$key][$i] = true;
						}
						else {
							if (! $this->_val_to_int( $key, $vf )) return false;
							if (array_key_exists($vf, $this->e[$key])) {
								$this->e[$key][$vf] = true;
							}
						}
					}
				}
			}
			
			
		}
		
		
		return true;
	}
	
	function validate_time( $time=null )
	{
		if ($this->err) {
			$this->err_msg = 'Invalid rule. Cannot validate time.';
		}
		if ($time === null) $time = time();
		/*
		$d = getDate($time);
		if (! @$this->e['dow'][$d['wday'   ]]
		||  ! @$this->e['hr' ][$d['hours'  ]]
		||  ! @$this->e['min'][$d['minutes']]
		||  ! @$this->e['day'][$d['mday'   ]]
		||  ! @$this->e['mon'][$d['mon'    ]]) return false;
		*/
		$d = localTime($time, true);
		if (! @$this->e['dow'][$d['tm_wday']  ]
		||  ! @$this->e['hr' ][$d['tm_hour']  ]
		||  ! @$this->e['min'][$d['tm_min' ]  ]
		||  ! @$this->e['day'][$d['tm_mday']  ]
		||  ! @$this->e['mon'][$d['tm_mon' ]+1]) return false;
		return true;
	}
}


/*
# Example:
$c = new CronRule();
$ok = $c->set_rule( '* 01-6,8,3 2 4-Oct Mon-Wed,0' );
if (! $ok) {
	echo $c->err_msg ,"\n";
} else {
	$ok = $c->validate_time();
	echo ($ok ? 'MATCH' : 'TIME DOES NOT MATCH') ,"\n";
	unset($c);
}
*/

?>