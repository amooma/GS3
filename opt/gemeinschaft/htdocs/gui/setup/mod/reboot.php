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

?>

<br />
<br />
<br />
<br />

<div style="width:550px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">

<h1><?php echo 'Neustart'; ?></h1>
<p align="center">
	<?php echo 'Das System wird jetzt neugestartet.'; ?><br />
	<?php echo 'Bitte haben Sie etwas Geduld. Trennen der Stromversorgung / Reset kann zu Datenverlust f&uuml;hren.'; ?>
</p>
<p align="center">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>img/wait-net.gif" />
</p>
<br />

</div>

<?php

if (@file_exists('/usr/sbin/gs-pre-shutdown')) {
	$err=0; $out=array();
	@exec( 'sudo gs-pre-shutdown 2>>/dev/null', $out, $err );
}

//@exec( 'sudo sh -c \'sleep 2; /opt/gemeinschaft/sbin/gpbx-pre-shutdown 1>>/dev/null 2>>/dev/null; /sbin/shutdown -r now 1>>/dev/null 2>>/dev/null &\' 0<&- 1>&- 2>&- &' );
@exec( 'sudo sh -c \'sleep 2; /sbin/shutdown -r now 1>>/dev/null 2>>/dev/null &\' 0<&- 1>&- 2>&- &' );

?>