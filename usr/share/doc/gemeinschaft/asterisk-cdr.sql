-- ----------------------------------------------------------------------------
--   Gemeinschaft database
--   This file was created with
--   mysqldump --opt --skip-extended-insert --databases asterisk > asterisk.sql
--   (that's what usr/share/doc/gemeinschaft/get-database-dump.php does)
--   
--   $Revision: 5709 $
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
-- Dumping data for table `ast_cdr`
--

LOCK TABLES `ast_cdr` WRITE;
/*!40000 ALTER TABLE `ast_cdr` DISABLE KEYS */;
/*!40000 ALTER TABLE `ast_cdr` ENABLE KEYS */;
UNLOCK TABLES;

-- Dump completed on 2008-11-10  12:00:00
