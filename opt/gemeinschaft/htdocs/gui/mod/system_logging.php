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

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";





?>



<table cellspacing="1">
<tbody>
	<tr>
		<th><?php echo __('Log-Level'); ?>:</th>
		<td>
			<select name="log-level" style="min-width:140px;">
				<option value="FATAL">FATAL</option>
				<option value="WARNING">WARNING</option>
				<option value="NOTICE">NOTICE</option>
				<option value="DEBUG">DEBUG</option>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo __('Ziel'); ?>:</th>
		<td>
			<select name="log-target" style="min-width:140px;">
				<option value="files">Files</option>
				<option value="syslogsrv">Syslog server</option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td class="transp" style="width:100px;">&nbsp;</td>
		<td class="transp" style="width:180px;">&nbsp;</td>
	</tr>
	<tr>
		<th colspan="2">Syslog server:</th>
	</tr>
	<tr>
		<th><?php echo __('Host'); ?>:</th>
		<td>
			<input type="text" name="syslog-host" size="20" maxlength="25" value="" style="min-width:140px; max-width:100%;" />
		</td>
	</tr>
	<tr>
		<th><?php echo __('Port'); ?>:</th>
		<td>
			<input type="text" name="syslog-port" size="5" maxlength="5" value="514" readonly="readonly" disabled="disabled" />
		</td>
	</tr>
	<tr>
		<th>Transport:</th>
		<td>
			<?php
				$sylog_transp = 'udp';
				$sylog_transp = strToLower($sylog_transp);
			?>
			<input type="radio" name="syslog-transport" id="ipt-syslog-transport-udp" <?php if ($sylog_transp != 'tcp') echo 'checked="checked" '; ?>/>
			<label for="ipt-syslog-transport-udp">UDP</label>
			<input type="radio" name="syslog-transport" id="ipt-syslog-transport-tcp" <?php if ($sylog_transp == 'tcp') echo 'checked="checked" '; ?>/>
			<label for="ipt-syslog-transport-tcp">TCP</label>
		</td>
	</tr>
	
	
</tbody>
</table>
