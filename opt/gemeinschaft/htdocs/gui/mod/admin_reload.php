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
require_once( GS_DIR .'inc/quote_shell_arg.php' );
require_once( GS_DIR .'inc/find_executable.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

/*
$shutdown_enabled =
	   gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')
	&& gs_get_conf('GS_GUI_SHUTDOWN_ENABLED');
*/

?>
<script type="text/javascript">

	function disableKeys( submit ) {
		document.forms[1].ButtonDpReload.disabled=true;
		document.forms[2].ButtonAstReload.disabled=true;
		document.forms[submit].submit();
		
	}

</script>


<form method="post" action="<?php echo GS_URL_PATH; ?>" class="inline">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="dialplan-reload" />
<input type="submit"  value="<?php echo __('Dialplan neu laden'); ?>" name="ButtonDpReload" onClick="disableKeys(1)" />
</form>
&nbsp;

<form method="post" action="<?php echo GS_URL_PATH; ?>" class="inline">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="reload"  />
<input type="submit" value="<?php echo __('Asterisk neu laden'); ?>" name="ButtonAstReload" onClick="disableKeys(2)" />
</form>
&nbsp;

<?php /* if ($shutdown_enabled) { ?>
<form method="post" action="<?php echo GS_URL_PATH; ?>" class="inline">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="shutdown" />
<input type="submit" value="<?php echo __('Ausschalten'); ?>" />
</form>
&nbsp;
<?php } */ ?>

<br />
<hr size="1" />

<?php


$action = @$_REQUEST['action'];

if ($action === 'dialplan-reload') {
	@ob_flush(); @flush();
	echo '<pre style="margin:0.9em 0.1em; padding:0.3em; background:#eee;">';
	$err=0;
	@ob_implicit_flush(1);
	
	gs_log(GS_LOG_DEBUG, "Reloading local Asterisk dialplan");
	echo "Reloading <b>local</b> Asterisk dialplan\n";
	@ob_flush(); @flush();
	passThru( 'sudo '. qsa(GS_DIR.'sbin/start-asterisk') .' --dialplan', $err );
	echo "\n", '&rarr; <b>', ($err==0 ? 'OK':'ERR') ,'</b>' ,"\n\n";
	
	$rs = $DB->execute( 'SELECT `host` FROM `hosts` WHERE `is_foreign` = 0' );
	while ($r = $rs->fetchRow())
	{
		/*
		if ($r['host'] === '127.0.0.1') {  //FIXME
			gs_log(GS_LOG_DEBUG, "Reloading local Asterisk dialplan");
			echo "Reloading <b>local</b> Asterisk dialplan\n";
			@ob_flush(); @flush();
			passThru( 'sudo '. qsa(GS_DIR.'sbin/start-asterisk') .' --dialplan', $err );
		}
		else {
		*/
			gs_log(GS_LOG_DEBUG, "Reloading Asterisk dialplan on ". $r['host']);
			echo "Reloading Asterisk dialplan on <b>", $r['host'] ,"</b>\n";
			@ob_flush(); @flush();
			passThru( 'sudo ssh -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=8 -l root '. qsa($r['host']) .' '. qsa(GS_DIR.'sbin/start-asterisk') .' --dialplan', $err );
		/*
		}
		*/
		echo "\n", '&rarr; <b>', ($err==0 ? 'OK':'ERR') ,'</b>' ,"\n\n";
	}
	
	@ob_implicit_flush(0);
	echo '</pre>';
}

elseif ($action === 'reload') {
	@flush();
	echo '<pre style="margin:0.9em 0.1em; padding:0.3em; background:#eee;">';
	$err=0;
	@ob_implicit_flush(1);
	
	gs_log(GS_LOG_DEBUG, "Reloading local Asterisk");
	echo "Reloading <b>local</b> Asterisk\n";
	@ob_flush(); @flush();
	passThru( 'sudo '. qsa(GS_DIR.'sbin/start-asterisk'), $err );
	echo "\n", '&rarr; <b>', ($err==0 ? 'OK':'ERR') ,'</b>' ,"\n\n";
	
	$rs = $DB->execute( 'SELECT `host` FROM `hosts` WHERE `is_foreign` = 0' );
	while ($r = $rs->fetchRow())
	{
		/*
		if ($r['host'] === '127.0.0.1') {  //FIXME
			gs_log(GS_LOG_DEBUG, "Reloading local Asterisk");
			echo "Reloading <b>local</b> Asterisk\n";
			@ob_flush(); @flush();
			passThru( 'sudo '. qsa(GS_DIR.'sbin/start-asterisk'), $err );
		}
		else {
		*/
			gs_log(GS_LOG_DEBUG, "Reloading Asterisk on ". $r['host']);
			echo "Reloading Asterisk on <b>", $r['host'] ,"</b>\n";
			@ob_flush(); @flush();
			passThru( 'sudo ssh -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=8 -l root '. qsa($r['host']) .' '. qsa(GS_DIR.'sbin/start-asterisk') , $err );
		/*
		}
		*/
		echo "\n", '&rarr; <b>', ($err==0 ? 'OK':'ERR') ,'</b>' ,"\n\n";
	}
	
	@ob_implicit_flush(0);
	echo '</pre>';
}

/*
elseif ($action === 'shutdown' && $shutdown_enabled) {
?>
<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="shutdown2" />
<br />
<span class="text" style="font-size:1.8em; color:#e00;">
<?php echo __('Wollen Sie die Anlage wirklich ausschalten?'); ?>
</span><br />
<br />
<input type="checkbox" name="confirm" id="ipt-shutdown-confirm" value="yes" /> <label for="ipt-shutdown-confirm"><?php echo __('Ja, ich wei&szlig; was ich tue'); ?></label><br />
<br />
<a href="<?php echo gs_url($SECTION, $MODULE); ?>"><button type="button"><?php echo __('Abbrechen'); ?></button></a>
&nbsp;
<button type="submit" style="color:#a00;"><?php echo __('Runterfahren'); ?></button>
</form>
<?php
}

elseif ($action === 'shutdown2' && $shutdown_enabled) {
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
			
			@flush();
			@ob_implicit_flush(1);
			echo '<pre style="margin:0.9em 0.1em; padding:0.3em; background:#eee;">';
			
			// stop asterisk first so it can finish writing to mysql:
			$err=0;
			@passThru( 'sudo /etc/init.d/asterisk stop', $err );
			
			/ *
			if (file_exists( '/etc/init.d/mysql-ndb' )) {
				$err=0;
				@passThru( 'sudo /etc/init.d/mysql-ndb stop', $err );
			}
			if (file_exists( '/etc/init.d/mysql' )) {
				$err=0;
				@passThru( 'sudo /etc/init.d/mysql stop', $err );
			}
			if (file_exists( '/etc/init.d/mysqld' )) {
				$err=0;
				@passThru( 'sudo /etc/init.d/mysqld stop', $err );
			}
			* /
			
			$err=0;
			passThru( 'sudo '. $shutdown .' -h -P now 2>&1', $err );
			echo "\n";
			echo '&rarr; <b>', ($err===0 ? 'OK':'ERR ('.$err.')') ,'</b>';
			echo '</pre>';
		}
	}
}
*/


?>
