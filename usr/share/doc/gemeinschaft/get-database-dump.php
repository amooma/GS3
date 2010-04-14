#!/usr/bin/php -q
<?php

#
# nothing special - just to save some typing
#

passThru( 'mysqldump --opt --skip-extended-insert --databases asterisk'
	. '| sed -e \'s/DEFINER *= *[^ ]*/DEFINER=CURRENT_USER()/g\'' );
echo "\n";

?>