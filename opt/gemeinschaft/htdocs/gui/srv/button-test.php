<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php

function htmlEnt( $str )
{
	return htmlSpecialChars( $str, ENT_QUOTES, 'UTF-8' );
}

function getButtonValHack()
{
	if (! array_key_exists('whichform', $_REQUEST)) return null;
	$whichform = $_REQUEST['whichform'];
	
	$buttonval_key = 'buttonval';
	if ($whichform == 4) {
		if (array_key_exists('buttonval4a', $_REQUEST))
			$buttonval_key = 'buttonval4a';
		elseif (array_key_exists('buttonval4b', $_REQUEST))
			$buttonval_key = 'buttonval4b';
	}
	
	if (array_key_exists($buttonval_key, $_REQUEST)
	&& subStr($_REQUEST[$buttonval_key],0,5)==='test-')
	{
		return $_REQUEST[$buttonval_key];
	}
	elseif (array_key_exists($buttonval_key, $_REQUEST)
	&& preg_match('/test-[0-9a-z]+/', $_REQUEST[$buttonval_key], $m))
	{
		return $m[0];
	}
}
$clicked_button_val = getButtonValHack();

?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="de-de" xml:lang="de-de">
<head><title>Gemeinschaft &lt;button&gt; test</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- for stupid MSIE: -->
<!--[if lt IE 8]><style type="text/css">button {behavior: url("../js/msie-button-fix.htc.php?msie-sucks=.htc");}</style><![endif]-->
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<style type="text/css">
	body {
		font-family: sans-serif;
		font-size: 11pt;
	}
	h3 {
		margin: 0.9em 0 0.1em 0;
		padding: 0;
	}
	form {
		background: #ddd;
		border: 1px solid #ccc;
		padding: 0.3em 0.5em;
	}
	button {
		width: 10em;
	}
	.ok  {color: #0b0;}
	.err {color: #e30;}
</style>
</head>

<body>



<h3>Formular 1</h3>
<form method="post">
	<input type="hidden" name="whichform" value="1" />
	<?php
		$buttonval = 'test-1a';
	?>
	<input type="hidden" name="buttonval" value="<?php echo htmlEnt($buttonval); ?>" />
	<input type="submit" value="Button 1a" />
	&emsp;&emsp;&emsp; Sollwert: &emsp; <tt><?php echo htmlEnt($buttonval); ?></tt>
	<?php
		if (@$_REQUEST['whichform'] == 1) {
			echo '&emsp;&emsp;&emsp; Istwert: &emsp; <tt>'. htmlEnt(@$_REQUEST['buttonval']) .'</tt>',"\n";
			echo '&emsp;&emsp;&emsp; ';
			if (@$_REQUEST['buttonval'] == $buttonval)
				echo '<b class="ok">OK</b>';
			else
				echo '<b class="err">Fehler!</b>';
		}
	?>
</form>



<h3>Formular 2</h3>
<form method="post">
	<input type="hidden" name="whichform" value="2" />
	<?php
		$buttonval = 'test-2a';
	?>
	<button type="submit" name="buttonval" value="<?php echo htmlEnt($buttonval); ?>">Button <div>2a</div> <!-- val:<?php echo htmlEnt($buttonval); ?> --></button>
	&emsp;&emsp;&emsp; Sollwert: &emsp; <tt><?php echo htmlEnt($buttonval); ?></tt>
	<?php
		if (@$_REQUEST['whichform'] == 2) {
			echo '&emsp;&emsp;&emsp; Istwert: &emsp; <tt>'. htmlEnt(@$_REQUEST['buttonval']) .'</tt>',"\n";
			echo '&emsp;&emsp;&emsp; ';
			if (@$_REQUEST['buttonval'] == $buttonval)
				echo '<b class="ok">OK</b>';
			else
				echo '<b class="err">Fehler!</b>';
		}
	?>
</form>



<h3>Formular 3</h3>
<form method="post">
	<input type="hidden" name="whichform" value="3" />
	<?php
		$buttonval = 'test-3a';
	?>
	<button type="submit" name="buttonval" value="<?php echo htmlEnt($buttonval); ?>">Button <div>3a</div> <!-- val:<?php echo htmlEnt($buttonval); ?> --></button>
	&emsp;&emsp;&emsp; Sollwert: &emsp; <tt><?php echo htmlEnt($buttonval); ?></tt>
	<?php
		if (@$_REQUEST['whichform'] == 3
		&& $clicked_button_val == $buttonval) {
			echo '&emsp;&emsp;&emsp; Istwert: &emsp; <tt>'. htmlEnt(@$_REQUEST['buttonval']) .'</tt>',"\n";
			echo '&emsp;&emsp;&emsp; ';
			if (@$_REQUEST['buttonval'] == $buttonval)
				echo '<b class="ok">OK</b>';
			else
				echo '<b class="err">Fehler!</b>';
		}
	?>
	<br />
	<?php
		$buttonval = 'test-3b';
	?>
	<button type="submit" name="buttonval" value="<?php echo htmlEnt($buttonval); ?>">Button <div>3b</div> <!-- val:<?php echo htmlEnt($buttonval); ?> --></button>
	&emsp;&emsp;&emsp; Sollwert: &emsp; <tt><?php echo htmlEnt($buttonval); ?></tt>
	<?php
		if (@$_REQUEST['whichform'] == 3
		&& $clicked_button_val == $buttonval) {
			echo '&emsp;&emsp;&emsp; Istwert: &emsp; <tt>'. htmlEnt(@$_REQUEST['buttonval']) .'</tt>',"\n";
			echo '&emsp;&emsp;&emsp; ';
			if (@$_REQUEST['buttonval'] == $buttonval)
				echo '<b class="ok">OK</b>';
			else
				echo '<b class="err">Fehler!</b>';
		}
	?>
</form>



<h3>Formular 4</h3>
<form method="post">
	<input type="hidden" name="whichform" value="4" />
	<?php
		$buttonval = 'test-4a';
	?>
	<button type="submit" name="buttonval4a" value="<?php echo htmlEnt($buttonval); ?>">Button <div>4a</div> <!-- val:<?php echo htmlEnt($buttonval); ?> --></button>
	&emsp;&emsp;&emsp; Sollwert: &emsp; <tt><?php echo htmlEnt($buttonval); ?></tt>
	<?php
		if (@$_REQUEST['whichform'] == 4
		&& $clicked_button_val == $buttonval) {
			echo '&emsp;&emsp;&emsp; Istwert: &emsp; <tt>'. htmlEnt(@$_REQUEST['buttonval4a']) .'</tt>',"\n";
			echo '&emsp;&emsp;&emsp; ';
			if (@$_REQUEST['buttonval4a'] == $buttonval)
				echo '<b class="ok">OK</b>';
			else
				echo '<b class="err">Fehler!</b>';
		}
	?>
	<br />
	<?php
		$buttonval = 'test-4b';
	?>
	<button type="submit" name="buttonval4b" value="<?php echo htmlEnt($buttonval); ?>" onclick="this.style.color='transparent'; this.innerHTML='buttonValue'; this.form.submit();">Button <div>4b</div> <!-- val:<?php echo htmlEnt($buttonval); ?> --></button>
	&emsp;&emsp;&emsp; Sollwert: &emsp; <tt><?php echo htmlEnt($buttonval); ?></tt>
	<?php
		if (@$_REQUEST['whichform'] == 4
		&& $clicked_button_val == $buttonval) {
			echo '&emsp;&emsp;&emsp; Istwert: &emsp; <tt>'. htmlEnt(@$_REQUEST['buttonval4b']) .'</tt>',"\n";
			echo '&emsp;&emsp;&emsp; ';
			if (@$_REQUEST['buttonval4b'] == $buttonval)
				echo '<b class="ok">OK</b>';
			else
				echo '<b class="err">Fehler!</b>';
		}
	?>
</form>




<br />
<p>
Wert: &emsp; <tt><?php echo htmlEnt($clicked_button_val); ?></tt>
</p>




<hr />
<pre><?php
htmlEnt(print_r($_REQUEST));
?></pre>

</body>
</html>
