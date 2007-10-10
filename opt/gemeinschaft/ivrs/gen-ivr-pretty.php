#!/usr/bin/php -q
<?php


function gs_gen_ivr_pretty( $ext, $ivr, $name='main', $level=0 )
{
	if (! is_array($ivr)) return '';
	
	static $indentstr = '        ';
	$indent = str_repeat($indentstr, $level*2);
	
	$ret = '';
	if (is_array(@$ivr['dp'])) {
		foreach ($ivr['dp'] as $step) {
			if (! preg_match('/^([a-z]+)\s+(.*)/', $step, $m)) continue;
			$cmd     = $m[1];
			$cmddata = $m[2];
			switch ($cmd) {
				case 'play':
					$ret.= $indent. "PLAY ". baseName($cmddata) ."\n";
					break;
				case 'app':
					$ret.= $indent. "APP  $cmddata\n";
					break;
				case 'hangup':
					//$ret.= $indent. "(hangup)\n";
					break;
			}
		}
	}
	if (! is_array(@$ivr['options'])) {
		//$ret.= $indent. "(hangup)\n";
	} else {
		//$ret.= $indent. "(wait)\n";
		foreach ($ivr['options'] as $newext => $newivr) {
			$ret.= $indentstr.$indent. "\n";
			//$ret.= $indentstr.$indent. $newext ."\n";
			$ret.= $indentstr.$indent. str_pad($newext,4,' ') .' ';
			$ret2= gs_gen_ivr_pretty( $newext, $newivr, $name."-".str_replace( array('#', '*'), array('pound', 'star'), $newext), $level+1 );
			$ret.= preg_replace('/^[ ]{'.(strLen($indentstr)*2+strLen($indent)-3).'}/', '', $ret2);
		}
	}
	
	return $ret;
}



$ivr = null;
include( '/opt/gemeinschaft/ivrs/ivr-99.php' );
$ret = gs_gen_ivr_pretty( '99', @$ivr, '99' );

echo $ret, "\n";


?>