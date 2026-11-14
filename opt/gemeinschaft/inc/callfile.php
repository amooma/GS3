<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
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


/***********************************************************
*  Hands a call file to the local Asterisk without sudo.
*  The outgoing and tmp spool directories are writable by
*  everyone (install.sh). The file is copied into tmp first
*  and then renamed into outgoing, so Asterisk only ever sees
*  a complete file. The source file is removed.
*  Returns 0 on success like the "sudo mv" it replaces.
***********************************************************/

function gs_callfile_move( $src, $spoolfile )
{
	$tmp = '/var/spool/asterisk/tmp/'. baseName($spoolfile) .'.part';
	if (! @copy( $src, $tmp )) {
		@unlink( $src );
		return 1;
	}
	@unlink( $src );
	@chmod( $tmp, 00666 );
	if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
		# Asterisk changes the mtime of the file to schedule retries
		@chown( $tmp, 'asterisk' );
		@chgrp( $tmp, 'asterisk' );
	}
	if (! @rename( $tmp, $spoolfile )) {
		@unlink( $tmp );
		return 1;
	}
	return 0;
}

?>
