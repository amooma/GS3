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

require_once( GS_DIR .'lib/phpmailer/src/Exception.php' );
require_once( GS_DIR .'lib/phpmailer/src/PHPMailer.php' );
require_once( GS_DIR .'lib/phpmailer/src/SMTP.php' );


# returns a PHPMailer object set up for sending directly to a mail
# exchanger
#
function gs_mailer_new()
{
	# Our sender is root@`hostname` and the hostname of a PBX often
	# has no dots, which PHPMailer's default validator would reject.
	PHPMailer\PHPMailer\PHPMailer::$validator = function( $address ) {
		return (bool)preg_match('/^[^@\s<>]+@[^@\s<>]+$/D', $address);
	};
	
	$mail = new PHPMailer\PHPMailer\PHPMailer();
	$mail->CharSet = 'utf-8';
	$mail->setLanguage( strToLower(subStr( (string)gs_get_conf('GS_INTL_LANG') ,0,2)) );
	
	# We talk to the recipient's mail exchanger, whose certificate rarely
	# matches its host name. Encrypt if it offers STARTTLS but do not
	# fail the delivery because of the certificate.
	$mail->SMTPOptions = array( 'ssl' => array(
		'verify_peer'       => false,
		'verify_peer_name'  => false,
		'allow_self_signed' => true
	));
	
	return $mail;
}
