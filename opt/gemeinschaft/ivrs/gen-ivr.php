#!/usr/bin/php -q
<?php


function gs_gen_ivr( $ext, $ivr, $name='main', $level=0 )
{
	if (! is_array($ivr)) return '';
	
	//$ret = "\n";
	$ret = "";
	$ret.= "[ivr-$name]\n";
	if ($level == 0) {
		$ret.= "exten => s,1,Answer()\n";
		$ret.= "exten => s,n,Wait(1)\n";
	} else {
		$ret.= "exten => s,1,Wait(0.5)\n";
	}
	if (is_array(@$ivr['dp'])) {
		foreach ($ivr['dp'] as $step) {
			if (! preg_match('/^([a-z]+)\s+(.*)/', $step, $m)) continue;
			$cmd     = $m[1];
			$cmddata = $m[2];
			switch ($cmd) {
				case 'play':
					$ret.= "exten => s,n,Background($cmddata)\n";
					break;
				case 'app':
					$ret.= "exten => s,n,$cmddata\n";
					break;
				case 'hangup':
					$ret.= "exten => s,n,Hangup()\n";
					break;
			}
		}
	}
	if (! is_array(@$ivr['options'])) {
		$ret.= "exten => s,n,Hangup()\n";
	} else {
		$ret.= "exten => s,n,WaitExten(10)\n";
		$ret.= "\n";
		foreach ($ivr['options'] as $newext => $newivr) {
			$ret.= "exten => $newext,1,Goto(ivr-$name-". str_replace( array('#', '*'), array('pound', 'star'), $newext) .",s,1)\n";
		}
		foreach ($ivr['options'] as $newext => $newivr) {
			$ret.= "\n";
			$ret.= gs_gen_ivr( $newext, $newivr, $name."-".str_replace( array('#', '*'), array('pound', 'star'), $newext), $level+1 );
		}
	}
	
	
	return $ret;
}



$ivr = null;
include( '/opt/gemeinschaft/ivrs/ivr-99.php' );
$ret = gs_gen_ivr( '99', @$ivr, '99' );

echo "[ivrs]\n";
echo 'exten => 99,1,Macro(dial-log-store,${user_name},out,${EXTEN})' ,"\n";
echo 'exten => 99,n,Goto(ivr-99,s,1)' ,"\n";
echo "\n";
echo $ret, "\n";


?>