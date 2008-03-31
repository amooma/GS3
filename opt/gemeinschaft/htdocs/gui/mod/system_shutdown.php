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
require_once( GS_DIR .'inc/find_executable.php' );

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
<input type="hidden" name="action" value="shutdown" />
<input type="submit" value="<?php echo __('Ausschalten'); ?>" />
</form>
&nbsp;

<form method="post" action="<?php echo GS_URL_PATH; ?>" class="inline">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="reboot" />
<input type="submit" value="<?php echo __('Neustarten'); ?>" />
</form>
&nbsp;

<br />
<hr size="1" />

<?php


$action = @$_REQUEST['action'];

if ($action === 'shutdown') {
?>
<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="shutdown2" />
<br />
<span class="text" style="font-size:1.8em; color:#e00;">
<?php echo __('Wollen Sie die Anlage wirklich ausschalten?'); ?>
</span><br />
<br />
<input type="checkbox" name="confirm" id="ipt-shutdown-confirm" value="yes" />
<label for="ipt-shutdown-confirm"><?php echo __('Ja, ausschalten'); ?></label><br />
<br />
<a href="<?php echo gs_url($SECTION, $MODULE); ?>"><button type="button"><?php echo __('Abbrechen'); ?></button></a>
&nbsp;
<button type="submit" style="color:#a00;"><?php echo __('Ausschalten'); ?></button>
</form>
<?php
}

elseif ($action === 'reboot') {
?>
<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="reboot2" />
<br />
<span class="text" style="font-size:1.8em; color:#e00;">
<?php echo __('Wollen Sie die Anlage wirklich neustarten?'); ?>
</span><br />
<br />
<input type="checkbox" name="confirm" id="ipt-shutdown-confirm" value="yes" />
<label for="ipt-shutdown-confirm"><?php echo __('Ja, neustarten'); ?></label><br />
<br />
<a href="<?php echo gs_url($SECTION, $MODULE); ?>"><button type="button"><?php echo __('Abbrechen'); ?></button></a>
&nbsp;
<button type="submit" style="color:#a00;"><?php echo __('Neustarten'); ?></button>
</form>
<?php
}

else {
	$action = @$_POST['action'];
	
	if ($action === 'shutdown2'
	||  $action === 'reboot2'  )
	{
		if (@$_REQUEST['confirm'] === 'yes') {
			$shutdown = find_executable('shutdown',
				array('/sbin/', '/bin/') );
			if (! $shutdown) {
				echo 'shutdown not found.' ,"\n";
			} else {
				
				if (@file_exists('/usr/sbin/gs-pre-shutdown')) {
					$err=0; $out=array();
					@exec( 'sudo /usr/sbin/gs-pre-shutdown 2>>/dev/null', $out, $err );
				}
				
				if ($action === 'shutdown2')
					$shutdown_args = ' -h -P now';
				else
					$shutdown_args = ' -r now';
				
				@flush();
				@ob_implicit_flush(1);
				echo '<pre style="margin:0.9em 0.1em; padding:0.3em; background:#eee;">';
				
				$err=0;
				passThru( 'sudo '. $shutdown .' '. $shutdown_args .' 2>&1', $err );
				echo "\n";
				echo '&rarr; <b>', ($err===0 ? 'OK':'ERR ('.$err.')') ,'</b>';
				
				echo '</pre>';
			}
		}
	}

}


?>
