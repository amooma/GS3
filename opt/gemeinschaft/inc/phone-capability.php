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


class PhoneCapability
{
	var $ringtones = array
	(
		# Bellcore-dr (original):
		#
		 1 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 2 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 3 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 4 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 5 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		
		# Silent:
		 0 => array('settings'=>'Silent'  , 'alertinfo'=>'Silent'       )
	);
	
	function conv_ringtone( $file )
	{
		// phone specific. sox and mpg123 can be used.
		// must return null if not implemented, false on error,
		// or the name of the generated outfile on success.
		return null;  # not implemented
	}

	function get_firmware_files()
	{
		return null;  # not implemented
	}
}



/*
<Bellcore-dr1> Bellcore priority 1
<Bellcore-dr2> Bellcore priority 2 - "Long-Long"
<Bellcore-dr3> Bellcore priority 3 - "Short-Short-Long"
<Bellcore-dr4> Bellcore priority 4 - "Short-Long-Short"
<Bellcore-dr5> Bellcore priority 5 - "Short - Ringsplash"

Bellcore-BusyVerify, Bellcore-Stutter, Bellcore-MsgWaiting, Bellcore-None,
Bellcore-Inside, Bellcore-Outside (default), Bellcore-Reminder, ... ?

*****

Snom supports Bellcore-dr[1-10] and Silent. Internal names:
Ringer[1-10] / Silent

in settings (eg.): Ringer2
in Alert-Info:     <http://127.0.0.1/Bellcore-dr2> | <Bellcore-dr2>

or
Alert-Info: alert-internal | alert-external | alert-group

*****

Bellcore priority and Call Waiting tones per GR-506-CORE and GR-526-CORE

Bellcore priority 1 - standard phone ring cycle
Call Waiting Tone used with this Ring: Call Waiting priority 1
Alert Info string used : <Bellcore-dr1>
Number of States = 3;
State1 = ToneOn;
State1 Length = 2000;
State1 Freq1 = 725;
State1 Freq2 = 750;
State2 = ToneOff;
State2 Length = 4000;
State3 = ToneRepeat;
State3 Reps = 0;

Bellcore priority 2 - "Long-Long"
Call Waiting Tone used with this Ring: Call Waiting priority 2
Alert Info string used : <Bellcore-dr2>
Number of States = 5;
State1 = ToneOn;
State1 Length = 800;
State1 Freq = 725
State1 Freq2 = 750;
State2 = ToneOff;
State2 Length = 400;
State3 = ToneOn;
State3 Length = 800;
State3 Freq1 = 725;
State3 Freq2 = 750;
State4 = ToneOff;
State4 Length = 4000;
State5 = ToneRepeat;
State5 Reps = 0;

Bellcore priority 3 - "Short-Short-Long"
Call Waiting Tone used with this Ring: Call Waiting priority 3
Alert Info string used : <Bellcore-dr3>
Number of States = 7;
State1 = ToneOn;
State1 Length = 400;
State1 Freq1 = 725;
State1 Freq2 = 750;
State2 = ToneOff;
State2 Length = 200;
State3 = ToneOn;
State3 Length = 400;
State3 Freq1 = 725;
State3 Freq2 = 750;
State4 = ToneOff;
State4 Length = 200;
State5 = ToneOn;
State5 Length = 800;
State5 Freq1 = 725;
State5 Freq2 = 750;
State6 = ToneOff;
State6 Length = 4000;
State7 = ToneRepeat;
State7 Reps = 0;

Bellcore priority 4 - "Short-Long-Short"
Call Waiting Tone used with this Ring: Call Waiting priority 4
Alert Info string used : <Bellcore-dr4>
Number of States = 7;
State1 = ToneOn;
State1 Length = 300;
State1 Freq1 = 725;
State1 Freq2 = 750;
State2 = ToneOff;
State2 Length = 200;
State3 = ToneOn;
State3 Length = 1000;
State3 Freq1 = 725;
State3 Freq2 = 750;
State4 = ToneOff;
State4 Length = 200;
State5 = ToneOn;
State5 Length = 300;
State5 Freq1 = 725;
State5 Freq2 = 750;
State6 = ToneOff;
State6 Length = 4000;
State7 = ToneRepeat;
State7 Reps = 0;

Bellcore priority 5 - Short - "Ringsplash"
Call Waiting Tone used with this Ring: Call Waiting priority 4
Alert Info string used : <Bellcore-dr5>
Number of States = 1;
State1 = ToneOn;
State1 Length = 500;
State1 Freq1 = 725;
State1 Freq2 = 750;

Call Waiting tones from Bellcore specs
Bellcore Call Waiting priority 1 - standard Call Waiting cycle
Number of States = 1;
State1 = ToneOn;
State1 Length = 300;
State1 Freq1 = 440;
State1 Freq2 = 440;

Bellcore Call Waiting priority 2 - Call Waiting Priority 2 cycle
Number of States = 3;
State1 = ToneOn;
State1 Length = 100;
State1 Freq1 = 440;
State1 Freq2 = 440;
State2 = ToneOff;
State2 Length = 100;
State3 = ToneOn;
State3 Length = 100;
State3 req1 = 440;
State3 Freq2 = 440;

Bellcore Call Waiting priority 3 - Call Waiting Priority 3 cycle
Number of States = 5;
State1 = ToneOn;
State1 Length = 100;
State1 Freq1 = 440;
State1 Freq2 = 440;
State2 = ToneOff;
State2 Length = 100;
State3 = ToneOn;
State3 Length = 100;
State3 Freq1 = 440;
State3 Freq2 = 440;
State4 = ToneOff;
State4 Length = 100;
State5 = ToneOn;
State5 Length = 100;
State5 Freq1 = 440;
State5 Freq2 = 440;

Bellcore Call Waiting priority 4 - Call Waiting Priority 4 cycle
Number of States = 5;
State1 = ToneOn;
State1 Length = 100;
State1 Freq1 = 440;
State1 Freq2 = 440;
State2 State = ToneOff;
State2 Length = 100;
State3 = ToneOn;
State3 Length = 300;
State3 Freq1 = 440;
State3 Freq2 = 440;
State4 = ToneOff;
State4 Length = 100;
State5 = ToneOn;
State5 Length = 100;
State5 Freq1 = 440;
State5 Freq2 = 440;

*/


?>