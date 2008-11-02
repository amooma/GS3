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
* Soeren Sprenger <soeren.sprenger@amooma.de>
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

////////////////////////////////////////////////////////////
///     MAKE SURE THAT THIS FILE IS VALID PHP CODE!      ///
////////////////////////////////////////////////////////////

# If you don't understand this file you don't need it.
# If you do, please edit the values very carefully and
# remove/comment-out this trigger_error statement:
trigger_error("\n PLEASE EDIT /etc/gemeinschaft/topology.php !\n", E_USER_ERROR);


  # RZ = Rechenzentrum = EDPC = Electronic Data Processing Center

  # Current EDPC:
$CUR_RZ = 'B';  # valid values: 'A' or 'B'

  # MySQL user account with "SUPER" privileges
$SUPER_MYSQL_USER = 'root';
$SUPER_MYSQL_PASS = 'c734fkjdh3kdn';

  # Database master server in EDPC A:
$DB_MASTER_SERVER1 = '192.168.23.3';
  # Database master server in EDPC B:
$DB_MASTER_SERVER2 = '192.168.23.74';

  # Database master servers (virtual IP addresses):
  # This IP address will be written to /etc/gemeinschaft/gemeinschaft.php
  # and will be used for the database replication:
$DB_MASTER_SERVER1_SERVICE_IP = '192.168.23.3';
$DB_MASTER_SERVER2_SERVICE_IP = '192.168.23.74';

  # Web/Provisioning server in EDPC A:
$WEB_MASTER_SERVER1          = '192.168.23.74';
$WEB_MASTER_SERVER1_VIRT_IFC = 'eth0:0';  # unused

  # Web/Provisioning server in EDPC B:
$WEB_MASTER_SERVER2          = '192.168.23.74';
$WEB_MASTER_SERVER2_VIRT_IFC = 'eth0:0';  # unused

  # Telephony servers in EDPC A:
$TE_MASTER_SERVER1 = '192.168.23.74';
$TE_MASTER_BACKUP1 = '192.168.23.74';

  # Telephony servers in EDPC B:
$TE_MASTER_SERVER2 = '192.168.23.74';
$TE_MASTER_BACKUP2 = '192.168.23.74';


