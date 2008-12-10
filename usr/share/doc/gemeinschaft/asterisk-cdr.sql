-- ----------------------------------------------------------------------------
--   Tables for separate CDR database
--   This file was created with
--   mysqldump --opt --skip-extended-insert -d --databases asterisk --tables ast_cdr > asterisk-cdr.sql
--   CREATE DATABASE and USE statements were added manually.
--   
--   $Revision$
-- ----------------------------------------------------------------------------


-- MySQL dump 10.11
--
-- Host: localhost    Database: asterisk
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch3-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `asterisk`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `asterisk` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE `asterisk`;

--
-- Table structure for table `ast_cdr`
--

DROP TABLE IF EXISTS `ast_cdr`;
CREATE TABLE `ast_cdr` (
  `_id` int(10) unsigned NOT NULL auto_increment,
  `calldate` datetime NOT NULL default '0000-00-00 00:00:00',
  `uniqueid` varchar(32) character set ascii collate ascii_bin NOT NULL,
  `clid` varchar(80) collate utf8_unicode_ci NOT NULL default '',
  `src` varchar(30) collate ascii_general_ci NOT NULL default '',
  `dst` varchar(30) collate ascii_general_ci NOT NULL default '',
  `dcontext` varchar(50) collate ascii_general_ci NOT NULL default '',
  `channel` varchar(60) collate ascii_general_ci NOT NULL default '',
  `dstchannel` varchar(60) collate ascii_general_ci NOT NULL default '',
  `lastapp` varchar(30) collate ascii_general_ci NOT NULL default '',
  `lastdata` varchar(80) collate ascii_general_ci NOT NULL default '',
  `duration` mediumint(8) unsigned NOT NULL default '0',
  `billsec` mediumint(8) unsigned NOT NULL default '0',
  `disposition` varchar(15) collate ascii_general_ci NOT NULL default '',
  `amaflags` tinyint(3) unsigned NOT NULL default '0',
  `accountcode` varchar(25) collate ascii_general_ci NOT NULL default '',
  `userfield` varchar(255) collate ascii_general_ci NOT NULL default '',
  PRIMARY KEY  (`_id`),
  KEY `calldate` (`calldate`),
  KEY `accountcode` (`accountcode`),
  KEY `src_disposition` (`src`(25),`disposition`(4)),
  KEY `dst_disposition` (`dst`(25),`disposition`(4)),
  KEY `uniqueid` (`uniqueid`(25))
) ENGINE=MyISAM DEFAULT CHARSET=ascii COLLATE=ascii_general_ci;

--
-- Table structure for table `itemized_bill`
--

DROP TABLE IF EXISTS `itemized_bill`;
CREATE TABLE `itemized_bill` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `start` datetime NOT NULL,
  `dur` mediumint(8) unsigned NOT NULL default '0',
  `is_out` tinyint(1) unsigned NOT NULL,
  `ext` varchar(10) character set ascii NOT NULL,
  `remote` varchar(25) character set ascii NOT NULL,
  `tariff_zone` char(4) character set ascii NOT NULL,
  `units` mediumint(8) unsigned NOT NULL,
  `charge` float NOT NULL default '0',
  `cur` char(3) character set ascii NOT NULL,
  `vat` float NOT NULL,
  `cdr_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `start_ext_remote_dur` (`start`,`ext`,`remote`,`dur`),
  KEY `ext` (`ext`),
  KEY `cdr_id` (`cdr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

