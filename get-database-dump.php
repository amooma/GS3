#!/usr/bin/php -q
<?php

#
# nothing special - just to save some typing
#

passThru( 'mysqldump --opt --skip-extended-insert --databases asterisk' );
echo "\n";

?>