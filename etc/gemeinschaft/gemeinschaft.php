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

/***********************************************************
*        MAKE SURE THAT THIS FILE IS VALID PHP CODE!
*                        Please run
*           /opt/gemeinschaft/sbin/gs-configtest
*                 after making any changes.
***********************************************************/



/***********************************************************
*    TYPE OF INSTALLATION
***********************************************************/

$INSTALLATION_TYPE      = 'single';
  # the type of the current installation.
  # "gpbx"    : GPBX
  # "single"  : single-server installation (Gemeinschaft,
  #             Asterisk and MySQL on one server)
  # "cluster" : cluster setup
  # This is just a hint to the GUI and various other scripts.
  # Changing it breaks things but does not change the type of
  # your installation!



/***********************************************************
*    DATABASE
***********************************************************/

//----------------------[  Master  ]----------------------//

$DB_MASTER_HOST         = '192.168.1.130';
$DB_MASTER_USER         = 'gemeinschaft';
$DB_MASTER_PWD          = '';
$DB_MASTER_DB           = 'asterisk';

//----------------------[  Slave  ]-----------------------//

$DB_SLAVE_HOST          = '127.0.0.1';
$DB_SLAVE_USER          = 'gemeinschaft';
$DB_SLAVE_PWD           = '';
$DB_SLAVE_DB            = 'asterisk';

//--------------------[  CDR Master  ]--------------------//

//$DB_CDR_MASTER_HOST     = '192.168.1.130';
//$DB_CDR_MASTER_USER     = 'cdr';
//$DB_CDR_MASTER_PWD      = '';
//$DB_CDR_MASTER_DB       = 'cdr';
  # if DB_CDR_MASTER_HOST is not set the normal master DB will
  # be used

//-----------------------[  Misc  ]-----------------------//

$DB_MASTER_TRANSACTIONS = true;     # use transactions?



/***********************************************************
*    ...
***********************************************************/

# Where is the rest of the config?: It has been moved to the
# database! Please use the GUI or the scripts for configuration.


