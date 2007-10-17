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

######################################################
##
##   ALL STRINGS IN HERE NEED TO BE TRANSLATED!
##
######################################################

defined('GS_VALID') or die('No direct access.');
require_once( GS_DIR .'inc/quote_shell_arg.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

?>

<form method="post" action="<?php echo GS_URL_PATH; ?>" class="inline">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="dialplan-reload" />
<input type="submit" value="<?php echo __('Reload Dialplan'); ?>" />
</form>

<form method="post" action="<?php echo GS_URL_PATH; ?>" class="inline">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="reload" />
<input type="submit" value="<?php echo __('Reload Asterisk'); ?>" />
</form>

<br />
<hr size="1" />

<pre style="margin:0.9em 0.1em; padding:0.3em; background:#eee;">
<?php


@flush();
@ob_implicit_flush(1);
$action = @$_REQUEST['action'];

if ($action == 'dialplan-reload') {
	$err=0;
	passThru( 'sudo '. qsa(GS_DIR.'sbin/start-asterisk') .' --dialplan', $err );
	echo "\n";
	echo '&rarr; <b>', ($err===0 ? 'OK' : 'ERR'), '</b>';
}
elseif ($action == 'reload') {
	$err=0;
	passThru( 'sudo '. qsa(GS_DIR.'sbin/start-asterisk'), $err );
	echo "\n";
	echo '&rarr; <b>', ($err===0 ? 'OK' : 'ERR'), '</b>';
}


?>
</pre>