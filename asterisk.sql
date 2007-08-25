-- MySQL dump 10.9
--
-- Host: localhost    Database: asterisk
-- ------------------------------------------------------
-- Server version	4.1.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
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
  `calldate` datetime NOT NULL default '0000-00-00 00:00:00',
  `clid` varchar(50) collate latin1_general_ci NOT NULL default '',
  `src` varchar(50) collate latin1_general_ci NOT NULL default '',
  `dst` varchar(50) collate latin1_general_ci NOT NULL default '',
  `dcontext` varchar(50) collate latin1_general_ci NOT NULL default '',
  `channel` varchar(50) collate latin1_general_ci NOT NULL default '',
  `dstchannel` varchar(50) collate latin1_general_ci NOT NULL default '',
  `lastapp` varchar(50) collate latin1_general_ci NOT NULL default '',
  `lastdata` varchar(80) collate latin1_general_ci NOT NULL default '',
  `duration` mediumint(8) unsigned NOT NULL default '0',
  `billsec` mediumint(8) unsigned NOT NULL default '0',
  `disposition` varchar(20) collate latin1_general_ci NOT NULL default '',
  `amaflags` tinyint(3) unsigned NOT NULL default '0',
  `accountcode` varchar(20) collate latin1_general_ci NOT NULL default '',
  `userfield` varchar(255) collate latin1_general_ci NOT NULL default '',
  KEY `calldate` (`calldate`),
  KEY `accountcode` (`accountcode`),
  KEY `src_disposition` (`src`,`disposition`(4)),
  KEY `dst_disposition` (`dst`,`disposition`(4))
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_cdr`
--


/*!40000 ALTER TABLE `ast_cdr` DISABLE KEYS */;
LOCK TABLES `ast_cdr` WRITE;
INSERT INTO `ast_cdr` VALUES ('2007-05-10 12:37:45','\"Homer Simpson\" <2001>','2001','h','default','Local/*800001@default-2de1,2','','NoOp','Finish if-to-internal-users-self-79',1,0,'NO ANSWER',3,'',''),('2007-05-10 12:37:45','\"Homer Simpson\" <2001>','2001','h','to-internal-users-self','SIP/2001-0a004f38','Local/*800001@default-2de1,1','NoOp','Finish if-to-internal-users-self-79',4,3,'ANSWERED',3,'','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `ast_cdr` ENABLE KEYS */;

--
-- Table structure for table `ast_queue_members`
--

DROP TABLE IF EXISTS `ast_queue_members`;
CREATE TABLE `ast_queue_members` (
  `queue_name` varchar(20) character set ascii NOT NULL default '',
  `_queue_id` int(10) unsigned NOT NULL default '0',
  `interface` varchar(25) character set ascii NOT NULL default '',
  `_user_id` int(10) unsigned NOT NULL default '0',
  `penalty` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`queue_name`,`interface`),
  UNIQUE KEY `queue_id_user_id` (`_queue_id`,`_user_id`),
  KEY `_user_id` (`_user_id`),
  KEY `interface` (`interface`(15))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_queue_members`
--


/*!40000 ALTER TABLE `ast_queue_members` DISABLE KEYS */;
LOCK TABLES `ast_queue_members` WRITE;
INSERT INTO `ast_queue_members` VALUES ('5000',1,'SIP/2001',23,0),('5000',1,'SIP/2002',24,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `ast_queue_members` ENABLE KEYS */;

--
-- Table structure for table `ast_queues`
--

DROP TABLE IF EXISTS `ast_queues`;
CREATE TABLE `ast_queues` (
  `_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(20) character set ascii NOT NULL default '',
  `_host_id` mediumint(8) unsigned NOT NULL default '1',
  `_title` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `musicclass` varchar(50) character set ascii default NULL,
  `announce` varchar(10) character set ascii default NULL,
  `context` varchar(50) character set ascii default NULL,
  `timeout` smallint(5) unsigned default NULL,
  `autopause` varchar(5) character set ascii default NULL,
  `setinterfacevar` varchar(5) character set ascii default NULL,
  `monitor_join` tinyint(1) unsigned default NULL,
  `monitor_format` varchar(50) character set ascii default NULL,
  `periodic_announce_frequency` tinyint(3) unsigned default NULL,
  `announce_frequency` tinyint(3) unsigned default NULL,
  `announce_round_seconds` tinyint(3) unsigned default NULL,
  `announce_holdtime` varchar(5) character set ascii default NULL,
  `retry` tinyint(3) unsigned default NULL,
  `wrapuptime` tinyint(3) unsigned default NULL,
  `maxlen` tinyint(3) unsigned default NULL,
  `servicelevel` smallint(5) unsigned default NULL,
  `strategy` varchar(20) character set ascii default NULL,
  `joinempty` varchar(10) character set ascii default NULL,
  `leavewhenempty` varchar(10) character set ascii default NULL,
  `eventmemberstatus` tinyint(1) unsigned default NULL,
  `eventwhencalled` tinyint(1) unsigned default NULL,
  `reportholdtime` tinyint(1) unsigned default NULL,
  `ringinuse` varchar(5) character set ascii default NULL,
  `memberdelay` tinyint(3) unsigned default NULL,
  `weight` tinyint(3) unsigned NOT NULL default '0',
  `timeoutrestart` tinyint(1) unsigned default NULL,
  PRIMARY KEY  (`_id`),
  UNIQUE KEY `name` (`name`),
  KEY `host_name` (`_host_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_queues`
--


/*!40000 ALTER TABLE `ast_queues` DISABLE KEYS */;
LOCK TABLES `ast_queues` WRITE;
INSERT INTO `ast_queues` VALUES (1,'5000',1,'Support-Schlange','default',NULL,NULL,10,'no','yes',NULL,NULL,60,90,NULL,'yes',5,NULL,5,NULL,'rrmemory','strict','strict',NULL,NULL,NULL,'no',NULL,0,NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `ast_queues` ENABLE KEYS */;

--
-- Table structure for table `ast_sipfriends`
--

DROP TABLE IF EXISTS `ast_sipfriends`;
CREATE TABLE `ast_sipfriends` (
  `_user_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(25) character set ascii NOT NULL default '',
  `secret` varchar(16) character set ascii NOT NULL default '1234',
  `type` enum('friend','user','peer') character set ascii NOT NULL default 'friend',
  `host` varchar(50) collate latin1_general_ci NOT NULL default 'dynamic',
  `defaultip` varchar(15) character set ascii default NULL,
  `context` varchar(50) character set ascii NOT NULL default 'from-internal-users',
  `callerid` varchar(80) collate latin1_general_ci NOT NULL default '',
  `mailbox` varchar(25) character set ascii NOT NULL default '',
  `callgroup` varchar(20) character set ascii NOT NULL default '1',
  `pickupgroup` varchar(20) character set ascii NOT NULL default '1',
  `setvar` varchar(50) character set ascii NOT NULL default '',
  `call-limit` tinyint(3) unsigned NOT NULL default '20',
  `subscribecontext` varchar(50) character set ascii NOT NULL default 'default',
  `regcontext` varchar(50) character set ascii default NULL,
  `ipaddr` varchar(15) character set ascii default NULL,
  `port` varchar(5) character set ascii default NULL,
  `regseconds` int(10) unsigned NOT NULL default '0',
  `username` varchar(25) character set ascii default NULL,
  `regserver` varchar(50) character set ascii default NULL,
  `fullcontact` varchar(100) character set ascii default NULL,
  PRIMARY KEY  (`_user_id`),
  UNIQUE KEY `name` (`name`),
  KEY `host` (`host`(25)),
  KEY `mailbox` (`mailbox`(20)),
  KEY `context` (`context`(25))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_sipfriends`
--


/*!40000 ALTER TABLE `ast_sipfriends` DISABLE KEYS */;
LOCK TABLES `ast_sipfriends` WRITE;
INSERT INTO `ast_sipfriends` VALUES (5,'950001','2602729062','friend','dynamic',NULL,'from-internal-nobody','Namenlos-5 <950001>','','1','1','__user_id=5;__user_name=950001',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(6,'950002','7581463327','friend','dynamic',NULL,'from-internal-nobody','Namenlos-6 <950002>','','1','1','__user_id=6;__user_name=950002',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(7,'950003','2099129726','friend','dynamic',NULL,'from-internal-nobody','Namenlos-7 <950003>','','1','1','__user_id=7;__user_name=950003',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(8,'950004','4751258926','friend','dynamic',NULL,'from-internal-nobody','Namenlos-8 <950004>','','1','1','__user_id=8;__user_name=950004',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(9,'950005','7458905728','friend','dynamic',NULL,'from-internal-nobody','Namenlos-9 <950005>','','1','1','__user_id=9;__user_name=950005',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(10,'950006','4040752142','friend','dynamic',NULL,'from-internal-nobody','Namenlos-10 <950006>','','1','1','__user_id=10;__user_name=950006',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(11,'950007','5827043803','friend','dynamic',NULL,'from-internal-nobody','Namenlos-11 <950007>','','1','1','__user_id=11;__user_name=950007',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(12,'950008','7012962864','friend','dynamic',NULL,'from-internal-nobody','Namenlos-12 <950008>','','1','1','__user_id=12;__user_name=950008',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(13,'950009','7583683190','friend','dynamic',NULL,'from-internal-nobody','Namenlos-13 <950009>','','1','1','__user_id=13;__user_name=950009',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(14,'950010','6879527634','friend','dynamic',NULL,'from-internal-nobody','Namenlos-14 <950010>','','1','1','__user_id=14;__user_name=950010',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(22,'2000','5826899294','friend','dynamic',NULL,'from-internal-users','Bart Simpson <2000>','2000','1','1','__user_id=22;__user_name=2000',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(23,'2001','4813474487','friend','dynamic',NULL,'from-internal-users','Homer Simpson <2001>','2001','1','1','__user_id=23;__user_name=2001',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(24,'2002','6907087521','friend','dynamic',NULL,'from-internal-users','Marge Simpson <2002>','2002','1','1','__user_id=24;__user_name=2002',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(25,'2003','9293349941','friend','dynamic',NULL,'from-internal-users','Lisa Simpson <2003>','2003','1','1','__user_id=25;__user_name=2003',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(28,'950011','7364863263482634','friend','dynamic',NULL,'from-internal-nobody','Namenlos-28 <950011>','','1','1','__user_id=28;__user_name=950011',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(29,'950012','7364863263482634','friend','dynamic',NULL,'from-internal-nobody','Namenlos-29 <950012>','','1','1','__user_id=29;__user_name=950012',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL),(30,'950013','3707760381117896','friend','dynamic',NULL,'from-internal-nobody','Namenlos-13 <950013>','','1','1','__user_id=30;__user_name=950013',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `ast_sipfriends` ENABLE KEYS */;

--
-- Table structure for table `ast_voicemail`
--

DROP TABLE IF EXISTS `ast_voicemail`;
CREATE TABLE `ast_voicemail` (
  `_uniqueid` int(10) unsigned NOT NULL auto_increment,
  `_user_id` int(10) unsigned default NULL,
  `mailbox` varchar(10) character set ascii NOT NULL default '',
  `context` varchar(50) character set ascii NOT NULL default 'default',
  `password` varchar(10) character set ascii NOT NULL default '0000',
  `email` varchar(50) character set ascii NOT NULL default '',
  `fullname` varchar(50) collate latin1_general_ci NOT NULL default '',
  `tz` varchar(25) character set ascii default 'germany',
  `attach` enum('no','yes') character set ascii NOT NULL default 'no',
  `delete` enum('no','yes') character set ascii NOT NULL default 'no',
  PRIMARY KEY  (`_uniqueid`),
  UNIQUE KEY `context_mailbox` (`context`,`mailbox`),
  KEY `fullname` (`fullname`(20)),
  KEY `_user_id` (`_user_id`),
  KEY `mailbox_context` (`mailbox`,`context`(20))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_voicemail`
--


/*!40000 ALTER TABLE `ast_voicemail` DISABLE KEYS */;
LOCK TABLES `ast_voicemail` WRITE;
INSERT INTO `ast_voicemail` VALUES (9,22,'2000','default','123','','Bart Simpson','germany','no','no'),(10,23,'2001','default','123','','Homer Simpson','germany','no','no'),(11,24,'2002','default','123','','Marge Simpson','germany','no','no'),(12,25,'2003','default','123','','Lisa Simpson','germany','no','no');
UNLOCK TABLES;
/*!40000 ALTER TABLE `ast_voicemail` ENABLE KEYS */;

--
-- Table structure for table `call_completion_waiting`
--

DROP TABLE IF EXISTS `call_completion_waiting`;
CREATE TABLE `call_completion_waiting` (
  `from_ext` varchar(15) character set ascii NOT NULL default '',
  `from_host_id` mediumint(8) unsigned NOT NULL default '0',
  `from_user_id` int(10) unsigned default NULL,
  `to_ext` varchar(15) character set ascii NOT NULL default '',
  `to_host_id` mediumint(8) unsigned NOT NULL default '0',
  `to_user_id` int(10) unsigned default NULL,
  `t_init` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`from_ext`,`to_ext`),
  UNIQUE KEY `to_from` (`to_ext`,`from_ext`),
  KEY `t_init` (`t_init`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `call_completion_waiting`
--


/*!40000 ALTER TABLE `call_completion_waiting` DISABLE KEYS */;
LOCK TABLES `call_completion_waiting` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `call_completion_waiting` ENABLE KEYS */;

--
-- Table structure for table `callblocking`
--

DROP TABLE IF EXISTS `callblocking`;
CREATE TABLE `callblocking` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `regexp` varchar(40) character set ascii NOT NULL default '',
  `pin` varchar(10) character set ascii collate ascii_bin NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_regex` (`user_id`,`regexp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `callblocking`
--


/*!40000 ALTER TABLE `callblocking` DISABLE KEYS */;
LOCK TABLES `callblocking` WRITE;
INSERT INTO `callblocking` VALUES (1,23,'11.*99','111'),(2,24,'^[0]','222'),(3,24,'^0190','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `callblocking` ENABLE KEYS */;

--
-- Table structure for table `callforwards`
--

DROP TABLE IF EXISTS `callforwards`;
CREATE TABLE `callforwards` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `source` enum('internal','external') character set ascii NOT NULL default 'internal',
  `case` enum('always','busy','unavail','offline') character set ascii NOT NULL default 'always',
  `timeout` tinyint(3) unsigned NOT NULL default '20',
  `number_std` varchar(50) character set ascii NOT NULL default '',
  `number_var` varchar(50) character set ascii NOT NULL default '',
  `active` enum('no','std','var') character set ascii NOT NULL default 'no',
  PRIMARY KEY  (`user_id`,`source`,`case`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `callforwards`
--


/*!40000 ALTER TABLE `callforwards` DISABLE KEYS */;
LOCK TABLES `callforwards` WRITE;
INSERT INTO `callforwards` VALUES (23,'internal','unavail',20,'90001','','std'),(24,'internal','always',0,'888','8','no'),(24,'internal','busy',0,'888','8','no'),(24,'internal','unavail',18,'888','8','no'),(24,'internal','offline',0,'888','8','no'),(24,'external','always',0,'99999','66','no'),(24,'external','busy',0,'99999','66','no'),(24,'external','unavail',18,'99999','66','no'),(24,'external','offline',0,'99999','66','no');
UNLOCK TABLES;
/*!40000 ALTER TABLE `callforwards` ENABLE KEYS */;

--
-- Table structure for table `callwaiting`
--

DROP TABLE IF EXISTS `callwaiting`;
CREATE TABLE `callwaiting` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `callwaiting`
--


/*!40000 ALTER TABLE `callwaiting` DISABLE KEYS */;
LOCK TABLES `callwaiting` WRITE;
INSERT INTO `callwaiting` VALUES (24,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `callwaiting` ENABLE KEYS */;

--
-- Table structure for table `clir`
--

DROP TABLE IF EXISTS `clir`;
CREATE TABLE `clir` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `internal_restrict` enum('no','once','yes') character set ascii NOT NULL default 'no',
  `external_restrict` enum('no','once','yes') character set ascii NOT NULL default 'no',
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `clir`
--


/*!40000 ALTER TABLE `clir` DISABLE KEYS */;
LOCK TABLES `clir` WRITE;
INSERT INTO `clir` VALUES (5,'no','no'),(6,'no','no'),(7,'no','no'),(8,'no','no'),(23,'no','no'),(24,'no','no');
UNLOCK TABLES;
/*!40000 ALTER TABLE `clir` ENABLE KEYS */;

--
-- Table structure for table `conferences`
--

DROP TABLE IF EXISTS `conferences`;
CREATE TABLE `conferences` (
  `ext` varchar(10) character set latin1 collate latin1_general_ci NOT NULL default '',
  `host_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ext`),
  KEY `host_ext` (`host_id`,`ext`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `conferences`
--


/*!40000 ALTER TABLE `conferences` DISABLE KEYS */;
LOCK TABLES `conferences` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `conferences` ENABLE KEYS */;

--
-- Table structure for table `dial_log`
--

DROP TABLE IF EXISTS `dial_log`;
CREATE TABLE `dial_log` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `type` enum('in','out','missed') character set ascii NOT NULL default 'out',
  `timestamp` int(10) unsigned NOT NULL default '0',
  `number` varchar(50) character set ascii NOT NULL default '',
  `remote_name` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `remote_user_id` int(10) unsigned default NULL,
  KEY `timestamp` (`timestamp`),
  KEY `user_timestamp` (`user_id`,`timestamp`),
  KEY `user_type_number_timestamp` (`user_id`,`type`,`number`(10),`timestamp`),
  KEY `user_type_timestamp` (`user_id`,`type`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `dial_log`
--


/*!40000 ALTER TABLE `dial_log` DISABLE KEYS */;
LOCK TABLES `dial_log` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `dial_log` ENABLE KEYS */;


--
-- Table structure for table `gate_grps`
--

DROP TABLE IF EXISTS `gate_grps`;
CREATE TABLE `gate_grps` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `title` varchar(50) collate utf8_unicode_ci NOT NULL,
  `type` varchar(20) character set ascii NOT NULL default 'balance',
  PRIMARY KEY  (`id`),
  KEY `title` (`title`(8))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `gate_grps`
--


/*!40000 ALTER TABLE `gate_grps` DISABLE KEYS */;
INSERT INTO `gate_grps` VALUES (5,'SIP-ISDN-GWs intern','balance'),(6,'ISDN (PRI)','balance'),(7,'GSM-GW T-Mobile','balance'),(8,'GSM-GW Vodafone','balance'),(9,'SIP-GW (sipgate.de)','balance'),(10,'SIP-GW (dus.net)','balance');
/*!40000 ALTER TABLE `gate_grps` ENABLE KEYS */;

--
-- Table structure for table `gates`
--

DROP TABLE IF EXISTS `gates`;
CREATE TABLE `gates` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `grp_id` smallint(5) unsigned NOT NULL,
  `type` varchar(10) character set ascii NOT NULL default 'sip',
  `name` varchar(25) character set ascii NOT NULL,
  `title` varchar(50) collate utf8_unicode_ci NOT NULL,
  `allow_out` tinyint(1) unsigned NOT NULL default '1',
  `allow_in` tinyint(1) unsigned NOT NULL,
  `dialstr` varchar(50) character set ascii NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`(10)),
  KEY `grp_title` (`grp_id`,`title`(10)),
  KEY `grp_allow_out` (`grp_id`,`allow_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `gates`
--


/*!40000 ALTER TABLE `gates` DISABLE KEYS */;
INSERT INTO `gates` VALUES (5,6,'zap','gw_5_zaptel_span_1','Zaptel Span 1',1,0,'Zap/r1/{number}'),(6,6,'zap','gw_6_zaptel_span_2','Zaptel Span 2',1,0,'Zap/r2/{number}'),(7,5,'sip','gw_7_sip_isdn_intern_a','SIP-ISDN intern A',1,0,'SIP/{number}@{peer}'),(8,5,'sip','gw_8_sip_isdn_intern_b','SIP-ISDN intern B',1,0,'SIP/{number}@{peer}'),(9,8,'sip','gw_9_sip_gsm_vodafone','SIP-GSM Vodafone',1,1,'SIP/{number}@{peer}');
/*!40000 ALTER TABLE `gates` ENABLE KEYS */;

--
-- Table structure for table `hosts`
--

DROP TABLE IF EXISTS `hosts`;
CREATE TABLE `hosts` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `host` varchar(50) character set ascii NOT NULL default '',
  `comment` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `hosts`
--


/*!40000 ALTER TABLE `hosts` DISABLE KEYS */;
LOCK TABLES `hosts` WRITE;
INSERT INTO `hosts` VALUES (1,'192.168.1.130','ast 1'),(2,'192.168.1.140','ast 2');
UNLOCK TABLES;
/*!40000 ALTER TABLE `hosts` ENABLE KEYS */;

--
-- Table structure for table `instant_messaging`
--

DROP TABLE IF EXISTS `instant_messaging`;
CREATE TABLE `instant_messaging` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `type` varchar(25) character set ascii NOT NULL default '',
  `contact` varchar(80) character set ascii NOT NULL default '',
  PRIMARY KEY  (`user_id`,`type`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `instant_messaging`
--


/*!40000 ALTER TABLE `instant_messaging` DISABLE KEYS */;
LOCK TABLES `instant_messaging` WRITE;
INSERT INTO `instant_messaging` VALUES (1,'jabber','homer@jabber.simpson');
UNLOCK TABLES;
/*!40000 ALTER TABLE `instant_messaging` ENABLE KEYS */;

--
-- Table structure for table `phones`
--

DROP TABLE IF EXISTS `phones`;
CREATE TABLE `phones` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(30) character set ascii NOT NULL default '',
  `mac_addr` varchar(12) character set ascii NOT NULL default '',
  `user_id` int(10) unsigned default NULL,
  `nobody_index` mediumint(8) unsigned NOT NULL default '0',
  `added` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `mac_addr` (`mac_addr`),
  KEY `user_id` (`user_id`),
  KEY `added` (`added`),
  KEY `type` (`type`),
  KEY `nobody_index` (`nobody_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `phones`
--


/*!40000 ALTER TABLE `phones` DISABLE KEYS */;
LOCK TABLES `phones` WRITE;
INSERT INTO `phones` VALUES (1,'snom360','000413233C9F',NULL,0,0),(2,'snom360','000413231C76',24,0,0),(3,'snom360','000413233483',11,0,0),(7,'snom360','001122334455',28,0,1174112992),(8,'snom360','0004132308A4',25,0,1174119746),(9,'snom360','000413000000',30,13,1177010534);
UNLOCK TABLES;
/*!40000 ALTER TABLE `phones` ENABLE KEYS */;

--
-- Table structure for table `pickupgroups`
--

DROP TABLE IF EXISTS `pickupgroups`;
CREATE TABLE `pickupgroups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `pickupgroups`
--


/*!40000 ALTER TABLE `pickupgroups` DISABLE KEYS */;
LOCK TABLES `pickupgroups` WRITE;
INSERT INTO `pickupgroups` VALUES (1,'Homer und Marge');
UNLOCK TABLES;
/*!40000 ALTER TABLE `pickupgroups` ENABLE KEYS */;

--
-- Table structure for table `pickupgroups_users`
--

DROP TABLE IF EXISTS `pickupgroups_users`;
CREATE TABLE `pickupgroups_users` (
  `group_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  KEY `group_id` (`group_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `pickupgroups_users`
--


/*!40000 ALTER TABLE `pickupgroups_users` DISABLE KEYS */;
LOCK TABLES `pickupgroups_users` WRITE;
INSERT INTO `pickupgroups_users` VALUES (1,24),(1,23),(1,22);
UNLOCK TABLES;
/*!40000 ALTER TABLE `pickupgroups_users` ENABLE KEYS */;

--
-- Table structure for table `queue_callforwards`
--

DROP TABLE IF EXISTS `queue_callforwards`;
CREATE TABLE `queue_callforwards` (
  `queue_id` int(10) unsigned NOT NULL default '0',
  `source` enum('internal','external') character set ascii NOT NULL default 'internal',
  `case` enum('always','full','timeout','empty') character set ascii NOT NULL default 'always',
  `timeout` tinyint(3) unsigned NOT NULL default '20',
  `number_std` varchar(50) character set ascii NOT NULL default '',
  `number_var` varchar(50) character set ascii NOT NULL default '',
  `active` enum('no','std','var') character set ascii NOT NULL default 'no',
  PRIMARY KEY  (`queue_id`,`source`,`case`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `queue_callforwards`
--


/*!40000 ALTER TABLE `queue_callforwards` DISABLE KEYS */;
LOCK TABLES `queue_callforwards` WRITE;
INSERT INTO `queue_callforwards` VALUES (1,'external','always',20,'2001','','std'),(1,'external','full',0,'','123','var');
UNLOCK TABLES;
/*!40000 ALTER TABLE `queue_callforwards` ENABLE KEYS */;

--
-- Table structure for table `queue_log`
--

DROP TABLE IF EXISTS `queue_log`;
CREATE TABLE `queue_log` (
  `queue_id` int(10) unsigned default NULL,
  `timestamp` int(10) unsigned NOT NULL default '0',
  `event` varchar(25) character set ascii NOT NULL default '',
  `reason` varchar(10) character set ascii default NULL,
  `ast_call_id` varchar(20) character set ascii default NULL,
  `user_id` int(10) unsigned default NULL,
  `caller` varchar(50) collate utf8_unicode_ci default NULL,
  `pos` mediumint(8) unsigned default NULL,
  `origpos` mediumint(8) unsigned default NULL,
  `waittime` mediumint(8) unsigned default NULL,
  `logindur` int(10) unsigned default NULL,
  `calldur` mediumint(8) unsigned default NULL,
  `info` varchar(50) character set ascii default NULL,
  KEY `queue_timestamp` (`queue_id`,`timestamp`),
  KEY `queue_event_timestamp` (`queue_id`,`event`,`timestamp`),
  KEY `queue_event_reason_timestamp` (`queue_id`,`event`,`reason`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `queue_log`
--


/*!40000 ALTER TABLE `queue_log` DISABLE KEYS */;
LOCK TABLES `queue_log` WRITE;
INSERT INTO `queue_log` VALUES (NULL,1172387005,'QUEUESTART',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(1,1172387014,'_ENTER',NULL,'1172387014.0',NULL,'2000',NULL,NULL,NULL,NULL,NULL,NULL),(1,1172387017,'_CONNECT',NULL,'1172387014.0',23,NULL,NULL,NULL,3,NULL,NULL,NULL),(1,1172387036,'_COMPLETE','AGENT','1172387014.0',23,NULL,NULL,NULL,3,NULL,19,NULL),(1,1172387045,'_ENTER',NULL,'1172387045.2',NULL,'2000',NULL,NULL,NULL,NULL,NULL,NULL),(1,1172387048,'_CONNECT',NULL,'1172387045.2',24,NULL,NULL,NULL,3,NULL,NULL,NULL),(1,1172387066,'_COMPLETE','TRANSFER','1172387045.2',24,NULL,NULL,NULL,3,NULL,18,'2001@default');
UNLOCK TABLES;
/*!40000 ALTER TABLE `queue_log` ENABLE KEYS */;

--
-- Table structure for table `ringtones`
--

DROP TABLE IF EXISTS `ringtones`;
CREATE TABLE `ringtones` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `src` enum('internal','external') collate latin1_general_ci NOT NULL default 'internal',
  `bellcore` tinyint(3) unsigned NOT NULL default '1',
  `file` varchar(40) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`user_id`,`src`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ringtones`
--


/*!40000 ALTER TABLE `ringtones` DISABLE KEYS */;
LOCK TABLES `ringtones` WRITE;
INSERT INTO `ringtones` VALUES (23,'internal',NULL,'somefile'),(23,'external',2,NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `ringtones` ENABLE KEYS */;

--
-- Table structure for table `routes`
--

DROP TABLE IF EXISTS `routes`;
CREATE TABLE `routes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `active` tinyint(1) unsigned NOT NULL default '1',
  `ord` int(10) unsigned NOT NULL,
  `pattern` varchar(30) character set ascii NOT NULL,
  `d_mo` tinyint(1) unsigned NOT NULL default '1',
  `d_tu` tinyint(1) unsigned NOT NULL default '1',
  `d_we` tinyint(1) unsigned NOT NULL default '1',
  `d_th` tinyint(1) unsigned NOT NULL default '1',
  `d_fr` tinyint(1) unsigned NOT NULL default '1',
  `d_sa` tinyint(1) unsigned NOT NULL default '1',
  `d_su` tinyint(1) unsigned NOT NULL default '1',
  `h_from` time NOT NULL default '00:00:00',
  `h_to` time NOT NULL default '24:00:00',
  `gw_grp_id_1` smallint(5) unsigned NOT NULL default '0',
  `gw_grp_id_2` smallint(5) unsigned NOT NULL default '0',
  `gw_grp_id_3` smallint(5) unsigned NOT NULL default '0',
  `descr` varchar(150) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ord` (`ord`),
  KEY `active_mo` (`active`,`d_mo`,`ord`),
  KEY `active_tu` (`active`,`d_tu`,`ord`),
  KEY `active_we` (`active`,`d_we`,`ord`),
  KEY `active_th` (`active`,`d_th`,`ord`),
  KEY `active_fr` (`active`,`d_fr`,`ord`),
  KEY `active_sa` (`active`,`d_sa`,`ord`),
  KEY `active_su` (`active`,`d_su`,`ord`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `routes`
--


/*!40000 ALTER TABLE `routes` DISABLE KEYS */;
INSERT INTO `routes` VALUES (5,1,3,'^11[0-7]$',1,1,1,1,1,1,0,'00:00:00','24:00:00',6,7,9,'Notrufnummern etc.'),(6,1,4,'^19222$',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,7,9,'Notruf Rettungsdienst'),(7,1,14,'^0900',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'Mehrwertnummern'),(8,1,8,'^118',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'Auskünfte (u.U. teuer, können vermitteln!)'),(9,1,10,'^09009',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Mehrwertnummern (Dialer)'),(10,1,12,'^09005',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Mehrwertnummern (\"Erwachsenenunterhaltung\")'),(11,1,16,'^0902',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Televoting (14 ct/Anruf)'),(12,1,18,'^019[1-4]',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Internet-Zugänge'),(13,1,20,'^070[01]',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'private Vanity-Nummern'),(14,1,22,'^080[01]',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'Mehrwertnummern (kostenlos)'),(15,1,24,'^01805',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Mehrwertnummern (Hotlines/\"Erwachsenenunterhaltung)'),(16,1,26,'^01802001033',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Handvermittlung ins Ausland (teuer)'),(17,1,28,'^0180',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'Mehrwertnummern'),(18,1,30,'^0137',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Televoting (25-100 ct/Anruf)'),(19,1,32,'^012x',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Innovative Dienste (teuer)'),(20,1,34,'^032x',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'ortsunabhängig, unklare Tarifierung, GSM vermeiden'),(21,1,36,'^0151',1,1,1,1,1,1,1,'00:00:00','24:00:00',7,8,6,'T-Mobile D1'),(22,1,38,'^016[01489]',1,1,1,1,1,1,1,'00:00:00','24:00:00',7,8,6,'T-Mobile D1'),(23,1,40,'^017[015]',1,1,1,1,1,1,1,'00:00:00','24:00:00',7,8,6,'T-Mobile D1'),(24,1,42,'^0152',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'Vodafone D2'),(25,1,44,'^0162',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'Vodafone D2'),(26,1,46,'^017[234]',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'Vodafone D2'),(27,1,48,'^015[57]',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'E-Plus'),(28,1,50,'^0163',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'E-Plus'),(29,1,52,'^017[78]',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'E-Plus'),(30,1,54,'^0156',1,1,1,1,1,1,1,'00:00:00','24:00:00',7,8,6,'MobilCom'),(31,1,56,'^0159',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'O2'),(32,1,58,'^017[69]',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'O2'),(33,1,60,'^0150',1,1,1,1,1,1,1,'00:00:00','24:00:00',7,8,6,'Group3G'),(34,1,62,'^01[5-7]',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'andere Handy-Gespräche'),(35,1,64,'^0zxx',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,10,0,'Ortsnetze'),(36,1,66,'^00',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,9,0,'international'),(37,1,68,'^',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,9,0,'alles andere');
/*!40000 ALTER TABLE `routes` ENABLE KEYS */;

--
-- Table structure for table `softkeys`
--

DROP TABLE IF EXISTS `softkeys`;
CREATE TABLE `softkeys` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `phone` varchar(20) NOT NULL default '',
  `key` varchar(10) NOT NULL default '',
  `number` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`phone`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

--
-- Dumping data for table `softkeys`
--


/*!40000 ALTER TABLE `softkeys` DISABLE KEYS */;
LOCK TABLES `softkeys` WRITE;
INSERT INTO `softkeys` VALUES (23,'snom','f1',''),(23,'snom','f10',''),(23,'snom','f11','2211'),(23,'snom','f3','44'),(23,'snom','f8','99');
UNLOCK TABLES;
/*!40000 ALTER TABLE `softkeys` ENABLE KEYS */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` varchar(20) character set ascii NOT NULL default '',
  `pin` varchar(10) character set ascii collate ascii_bin NOT NULL default '',
  `firstname` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `lastname` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `honorific` varchar(30) collate utf8_unicode_ci NOT NULL default '',
  `email` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `nobody_index` mediumint(8) unsigned default NULL,
  `host_id` mediumint(8) unsigned default '1',
  `current_ip` varchar(15) character set ascii default NULL,
  `user_comment` varchar(200) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`),
  KEY `lastname_firstname` (`lastname`(15),`firstname`(15)),
  KEY `firstname_lastname` (`firstname`(15),`lastname`(15)),
  KEY `nobody_index` (`nobody_index`),
  KEY `host_id` (`host_id`),
  KEY `current_ip` (`current_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--


/*!40000 ALTER TABLE `users` DISABLE KEYS */;
LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES (5,'nobody-00001','','','','','',1,1,'192.168.1.249',''),(6,'nobody-00002','','','','','',2,1,NULL,''),(7,'nobody-00003','','','','','',3,1,'192.168.1.202',''),(8,'nobody-00004','','','','','',4,1,NULL,''),(9,'nobody-00005','','','','','',5,1,'192.168.1.202',''),(10,'nobody-00006','','','','','',6,1,'192.168.1.247',''),(11,'nobody-00007','','','','','',7,1,NULL,''),(12,'nobody-00008','','','','','',8,1,NULL,''),(13,'nobody-00009','','','','','',9,1,'192.168.1.202',''),(14,'nobody-00010','','','','','',10,1,'192.168.1.201',''),(22,'47110001','123','Bart','Simpson','','',NULL,1,NULL,''),(23,'47110002','123','Homer','Simpson','','',NULL,2,'192.168.1.247',''),(24,'47110003','123','Marge','Simpson','','',NULL,1,'192.168.1.249',''),(25,'47110004','123','Lisa','Simpson','','',NULL,1,'192.168.1.247',''),(28,'nobody-00011','','','','','',11,1,NULL,''),(29,'nobody-00012','','','','','',12,1,'192.168.1.201',''),(30,'nobody-00013','','','','','',13,1,'192.168.1.109','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

--
-- Table structure for table `users_external_numbers`
--

DROP TABLE IF EXISTS `users_external_numbers`;
CREATE TABLE `users_external_numbers` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `number` varchar(25) character set latin1 collate latin1_general_ci NOT NULL default '',
  PRIMARY KEY  (`user_id`,`number`),
  KEY `number` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users_external_numbers`
--


/*!40000 ALTER TABLE `users_external_numbers` DISABLE KEYS */;
LOCK TABLES `users_external_numbers` WRITE;
INSERT INTO `users_external_numbers` VALUES (23,'001701234567'),(23,'950001');
UNLOCK TABLES;
/*!40000 ALTER TABLE `users_external_numbers` ENABLE KEYS */;

--
-- Table structure for table `pb_ldap`
--

DROP TABLE IF EXISTS `pb_ldap`;
CREATE TABLE `pb_ldap` (
  `user` varchar(20) character set ascii NOT NULL default '',
  `lastname` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `firstname` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `number` varchar(25) character set ascii NOT NULL default '',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `user_number` (`user`,`number`),
  KEY `updated` (`updated`),
  KEY `lastname_firstname` (`lastname`(15),`firstname`(15),`number`(7)),
  KEY `firstname_lastname` (`firstname`(15),`lastname`(10),`number`(7)),
  KEY `number` (`number`,`lastname`(15),`firstname`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `pb_ldap`
--


/*!40000 ALTER TABLE `pb_ldap` DISABLE KEYS */;
LOCK TABLES `pb_ldap` WRITE;
INSERT INTO `pb_ldap` VALUES ('012345','TEST','HANS','123','2007-05-24 07:28:28');
UNLOCK TABLES;
/*!40000 ALTER TABLE `pb_ldap` ENABLE KEYS */;

--
-- Table structure for table `pb_prv`
--

DROP TABLE IF EXISTS `pb_prv`;
CREATE TABLE `pb_prv` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `firstname` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `lastname` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `number` varchar(25) character set ascii NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `uid_lastname_firstname` (`user_id`,`lastname`(15),`firstname`(10)),
  KEY `uid_firstname_lastname` (`user_id`,`firstname`(10),`lastname`(10)),
  KEY `uid_number` (`user_id`,`number`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `pb_prv`
--


/*!40000 ALTER TABLE `pb_prv` DISABLE KEYS */;
LOCK TABLES `pb_prv` WRITE;
INSERT INTO `pb_prv` VALUES (32,23,'','Testuser1','1234'),(33,23,'','Testuser2','12345'),(34,23,'','Testuser3-1','654321'),(35,23,'','Testuser4','123456'),(36,23,'','Testuser5','123456'),(37,23,'','Testuser6','123456'),(41,23,'','Testuser10','123456'),(42,23,'','Testuser11','123456'),(43,23,'','Testuser12','123456'),(44,23,'','Testuser13','123456'),(45,23,'','Testuser14','123456'),(46,23,'','Testuser15','123456'),(47,23,'','Testuser16-1','123456'),(48,23,'','Testuser17','123456'),(49,23,'','Testuser18','123456'),(50,23,'','Testuser19','123456'),(51,23,'','Testuser20','123456'),(52,23,'','Testuser99','1234'),(53,23,'HANS','TEST','1234'),(54,23,'','abc','123'),(56,23,'','abc3','123'),(57,23,'PETER','TEST','555');
UNLOCK TABLES;
/*!40000 ALTER TABLE `pb_prv` ENABLE KEYS */;

--
-- Table structure for table `vm`
--

DROP TABLE IF EXISTS `vm`;
CREATE TABLE `vm` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `internal_active` tinyint(1) unsigned NOT NULL default '0',
  `external_active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `vm`
--


/*!40000 ALTER TABLE `vm` DISABLE KEYS */;
LOCK TABLES `vm` WRITE;
INSERT INTO `vm` VALUES (5,0,0),(6,0,0),(22,0,0),(23,0,1),(24,0,0),(25,0,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `vm` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

