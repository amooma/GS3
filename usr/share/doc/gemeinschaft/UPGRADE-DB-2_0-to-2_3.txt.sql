USE `asterisk`;

--
-- Table structure for table `cf_parallelcall`
--

CREATE TABLE IF NOT EXISTS `cf_parallelcall` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `_user_id` int(10) unsigned NOT NULL default '0',
  `number` varchar(20) character set ascii NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `cf_timerules`
--

CREATE TABLE IF NOT EXISTS `cf_timerules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ord` int(10) unsigned NOT NULL,
  `_user_id` int(10) unsigned NOT NULL,
  `d_mo` tinyint(1) unsigned NOT NULL default '1',
  `d_tu` tinyint(1) unsigned NOT NULL default '1',
  `d_we` tinyint(1) unsigned NOT NULL default '1',
  `d_th` tinyint(1) unsigned NOT NULL default '1',
  `d_fr` tinyint(1) unsigned NOT NULL default '1',
  `d_sa` tinyint(1) unsigned NOT NULL default '1',
  `d_su` tinyint(1) unsigned NOT NULL default '1',
  `h_from` time NOT NULL default '00:00:00',
  `h_to` time NOT NULL default '24:00:00',
  `target` varchar(20) character set ascii NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Table structure for table `gate_cids`
--

CREATE TABLE IF NOT EXISTS `gate_cids` (
  `grp_id` smallint(5) unsigned NOT NULL,
  `cid_int` varchar(16) character set ascii NOT NULL,
  `cid_ext` varchar(30) character set ascii NOT NULL,
  PRIMARY KEY  (`grp_id`,`cid_int`),
  CONSTRAINT `gate_cids_ibfk_1` FOREIGN KEY (`grp_id`) REFERENCES `gate_grps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(20) character set ascii NOT NULL,
  `title` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `type` varchar(20) character set ascii NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `title` (`title`(25)),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
INSERT INTO `groups` VALUES (1,'admins','Admins','user');
INSERT INTO `groups` VALUES (2,'users','All Users','user');
INSERT INTO `groups` VALUES (3,'hosts','All Hosts','host');
INSERT INTO `groups` VALUES (4,'queues','All Queues','queue');
INSERT INTO `groups` VALUES (5,'user_gui','User GUI','module_gui');
INSERT INTO `groups` VALUES (6,'admin_gui','Admin GUI','module_gui');
INSERT INTO `groups` VALUES (7,'wakeup_call_gui','Wakeup call extension','module_gui');
INSERT INTO `groups` VALUES (8,'room state gui','Room state extension','module_gui');
UNLOCK TABLES;


--
-- Table structure for table `group_connections`
--

CREATE TABLE IF NOT EXISTS `group_connections` (
  `type` varchar(20) character set ascii NOT NULL default '',
  `group` mediumint(8) unsigned NOT NULL,
  `key` varchar(20) character set ascii NOT NULL default 'id',
  `connection` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`type`,`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `group_connections`
--

LOCK TABLES `group_connections` WRITE;
INSERT INTO `group_connections` VALUES ('mysql',2,'id','SELECT `id` FROM `users` WHERE `nobody_index` IS NULL');
INSERT INTO `group_connections` VALUES ('mysql',3,'id','SELECT `id` FROM `hosts` WHERE `host` != \'\'');
INSERT INTO `group_connections` VALUES ('mysql',4,'id','SELECT `_id` AS `id` FROM `ast_queues`');
UNLOCK TABLES;



--
-- Table structure for table `group_includes`
--

CREATE TABLE IF NOT EXISTS `group_includes` (
  `group` mediumint(8) unsigned NOT NULL,
  `member` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`group`,`member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Table structure for table `group_members`
--

CREATE TABLE IF NOT EXISTS  `group_members` (
  `group` mediumint(8) unsigned NOT NULL,
  `member` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`group`,`member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `group_members`
--

LOCK TABLES `group_members` WRITE;
INSERT INTO `group_members` VALUES (5,1000);         
INSERT INTO `group_members` VALUES (5,1001);         
INSERT INTO `group_members` VALUES (5,2000);         
INSERT INTO `group_members` VALUES (5,2001);         
INSERT INTO `group_members` VALUES (5,3000);         
INSERT INTO `group_members` VALUES (5,3001);         
INSERT INTO `group_members` VALUES (5,3002);         
INSERT INTO `group_members` VALUES (5,3003);         
INSERT INTO `group_members` VALUES (5,3004);         
INSERT INTO `group_members` VALUES (5,4000);         
INSERT INTO `group_members` VALUES (5,4001);         
INSERT INTO `group_members` VALUES (5,4002);         
INSERT INTO `group_members` VALUES (5,4003);         
INSERT INTO `group_members` VALUES (5,5000);         
INSERT INTO `group_members` VALUES (5,5001);         
INSERT INTO `group_members` VALUES (5,6000);         
INSERT INTO `group_members` VALUES (5,6001);         
INSERT INTO `group_members` VALUES (5,6002);         
INSERT INTO `group_members` VALUES (5,7000);         
INSERT INTO `group_members` VALUES (5,7001);         
INSERT INTO `group_members` VALUES (5,7002);         
INSERT INTO `group_members` VALUES (5,7003);         
INSERT INTO `group_members` VALUES (5,8000);         
INSERT INTO `group_members` VALUES (5,8001);         
INSERT INTO `group_members` VALUES (5,9000);         
INSERT INTO `group_members` VALUES (5,9001);         
INSERT INTO `group_members` VALUES (5,10000);        
INSERT INTO `group_members` VALUES (5,10001);        
INSERT INTO `group_members` VALUES (5,11000);        
INSERT INTO `group_members` VALUES (5,11001);        
INSERT INTO `group_members` VALUES (5,11002);        
INSERT INTO `group_members` VALUES (5,11003);        
INSERT INTO `group_members` VALUES (5,11004);        
INSERT INTO `group_members` VALUES (5,11005);        
INSERT INTO `group_members` VALUES (5,12000);        
INSERT INTO `group_members` VALUES (5,12001);        
INSERT INTO `group_members` VALUES (5,12002);        
INSERT INTO `group_members` VALUES (5,12003);        
INSERT INTO `group_members` VALUES (5,12004);        
INSERT INTO `group_members` VALUES (5,13000);        
INSERT INTO `group_members` VALUES (5,13001);        
INSERT INTO `group_members` VALUES (5,14000);        
INSERT INTO `group_members` VALUES (5,14001);        
INSERT INTO `group_members` VALUES (5,14002);        
INSERT INTO `group_members` VALUES (5,14003);        
INSERT INTO `group_members` VALUES (5,19000);        
INSERT INTO `group_members` VALUES (5,19001);
INSERT INTO `group_members` VALUES (5,20000);        
INSERT INTO `group_members` VALUES (5,20001);        
INSERT INTO `group_members` VALUES (6,6003);
INSERT INTO `group_members` VALUES (6,6004);
INSERT INTO `group_members` VALUES (6,6005);         
INSERT INTO `group_members` VALUES (6,15000);        
INSERT INTO `group_members` VALUES (6,15001);        
INSERT INTO `group_members` VALUES (6,15002);        
INSERT INTO `group_members` VALUES (6,15003);        
INSERT INTO `group_members` VALUES (6,15004);        
INSERT INTO `group_members` VALUES (6,15005);        
INSERT INTO `group_members` VALUES (6,15006);        
INSERT INTO `group_members` VALUES (6,15007);        
INSERT INTO `group_members` VALUES (6,15008);        
INSERT INTO `group_members` VALUES (6,15009);        
INSERT INTO `group_members` VALUES (6,15010);
INSERT INTO `group_members` VALUES (6,15011);
INSERT INTO `group_members` VALUES (6,15012);
INSERT INTO `group_members` VALUES (6,15013);
INSERT INTO `group_members` VALUES (6,15014);
INSERT INTO `group_members` VALUES (6,15015);
INSERT INTO `group_members` VALUES (6,15016);
INSERT INTO `group_members` VALUES (6,16000);
INSERT INTO `group_members` VALUES (6,16001);
INSERT INTO `group_members` VALUES (6,16002);
INSERT INTO `group_members` VALUES (6,16003);
INSERT INTO `group_members` VALUES (6,16004);
INSERT INTO `group_members` VALUES (6,16005);
INSERT INTO `group_members` VALUES (6,17000);
INSERT INTO `group_members` VALUES (6,17001);
INSERT INTO `group_members` VALUES (6,17002);
INSERT INTO `group_members` VALUES (6,17003);
INSERT INTO `group_members` VALUES (6,17004);
INSERT INTO `group_members` VALUES (6,17005);
INSERT INTO `group_members` VALUES (6,17006);
INSERT INTO `group_members` VALUES (6,17007);
INSERT INTO `group_members` VALUES (6,17008);
INSERT INTO `group_members` VALUES (6,18000);
INSERT INTO `group_members` VALUES (6,18001);
INSERT INTO `group_members` VALUES (6,18002);
INSERT INTO `group_members` VALUES (6,18003);
INSERT INTO `group_members` VALUES (6,18004);
INSERT INTO `group_members` VALUES (6,18005);
INSERT INTO `group_members` VALUES (6,18006);
INSERT INTO `group_members` VALUES (6,18007);
INSERT INTO `group_members` VALUES (6,18008);
INSERT INTO `group_members` VALUES (6,18009);
INSERT INTO `group_members` VALUES (6,18010);
INSERT INTO `group_members` VALUES (6,18011);
INSERT INTO `group_members` VALUES (6,18012);
INSERT INTO `group_members` VALUES (6,18013);
INSERT INTO `group_members` VALUES (6,18014);
INSERT INTO `group_members` VALUES (6,18015);
INSERT INTO `group_members` VALUES (7,22000);
INSERT INTO `group_members` VALUES (7,22001);
INSERT INTO `group_members` VALUES (8,21000);
INSERT INTO `group_members` VALUES (8,21001);
UNLOCK TABLES;

--
-- Table structure for table `group_permissions`
--

CREATE TABLE IF NOT EXISTS `group_permissions` (
  `type` varchar(20) character set ascii NOT NULL default '',
  `group` mediumint(8) unsigned NOT NULL,
  `permit` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`type`,`group`,`permit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `group_permissions`
--

LOCK TABLES `group_permissions` WRITE;
INSERT INTO `group_permissions` VALUES ('call_stats',2,2);
INSERT INTO `group_permissions` VALUES ('roaming',2,2);
INSERT INTO `group_permissions` VALUES ('forward',2,2);
INSERT INTO `group_permissions` VALUES ('forward_vmconfig',2,2);
INSERT INTO `group_permissions` VALUES ('clir_set',2,2);
INSERT INTO `group_permissions` VALUES ('clip_set',2,2);
INSERT INTO `group_permissions` VALUES ('callwaiting_set',2,2);
INSERT INTO `group_permissions` VALUES ('queue_member',2,2);
INSERT INTO `group_permissions` VALUES ('agent',2,2);
INSERT INTO `group_permissions` VALUES ('ringtone_set',2,2);
INSERT INTO `group_permissions` VALUES ('dnd_set',2,2);
INSERT INTO `group_permissions` VALUES ('call_stats',2,4);
INSERT INTO `group_permissions` VALUES ('forward_queues',2,4);
INSERT INTO `group_permissions` VALUES ('phonebook_user',2,2);
INSERT INTO `group_permissions` VALUES ('wakeup_call',2,2);
INSERT INTO `group_permissions` VALUES ('room_state',2,2);
INSERT INTO `group_permissions` VALUES ('sudo_user',1,2);
INSERT INTO `group_permissions` VALUES ('display_module_gui',1,6);
INSERT INTO `group_permissions` VALUES ('display_module_gui',1,7);
INSERT INTO `group_permissions` VALUES ('display_module_gui',1,8);
INSERT INTO `group_permissions` VALUES ('display_module_gui',2,5);
UNLOCK TABLES;

--
-- Table structure for table `queue_cf_parallelcall`
--

CREATE TABLE IF NOT EXISTS `queue_cf_parallelcall` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `_queue_id` int(10) unsigned NOT NULL default '0',
  `number` varchar(20) character set ascii NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `queue_cf_timerules`
--

CREATE TABLE IF NOT EXISTS `queue_cf_timerules` (
  `_queue_id` int(10) unsigned NOT NULL,
  `ord` int(10) unsigned NOT NULL,
  `d_mo` tinyint(1) unsigned NOT NULL default '1',
  `d_tu` tinyint(1) unsigned NOT NULL default '1',
  `d_we` tinyint(1) unsigned NOT NULL default '1',
  `d_th` tinyint(1) unsigned NOT NULL default '1',
  `d_fr` tinyint(1) unsigned NOT NULL default '1',
  `d_sa` tinyint(1) unsigned NOT NULL default '1',
  `d_su` tinyint(1) unsigned NOT NULL default '1',
  `h_from` time NOT NULL default '00:00:00',
  `h_to` time NOT NULL default '24:00:00',
  `target` varchar(20) character set ascii NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table `queue_vm_rec_messages`
--

CREATE TABLE IF NOT EXISTS  `queue_vm_rec_messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `_queue_id` int(10) unsigned NOT NULL default '0',
  `vm_rec_file` varchar(80) character set utf8 collate utf8_unicode_ci NOT NULL,
  `vm_comment` varchar(180) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



--
-- Table structure for table `room_state`
--


CREATE TABLE IF NOT EXISTS `asterisk`.`room_state` (
 `extension` VARCHAR( 16 ) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL ,
 `state` TINYINT( 1 ) UNSIGNED NOT NULL ,
 PRIMARY KEY ( `extension` )
 ) ENGINE = MYISAM;


--
-- Table structure for table `vm_rec_messages`
--

CREATE TABLE IF NOT EXISTS `vm_rec_messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `_user_id` int(10) unsigned NOT NULL default '0',
  `vm_rec_file` varchar(80) character set utf8 collate utf8_unicode_ci NOT NULL,
  `vm_comment` varchar(180) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


--
-- Table structure for table `wakeup_calls`
--

CREATE TABLE IF NOT EXISTS `asterisk`.`wakeup_calls` (
 `target` VARCHAR( 16 ) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL ,
 `hour` TINYINT( 2 ) UNSIGNED NOT NULL ,
 `minute` TINYINT( 2 ) UNSIGNED NOT NULL ,
 PRIMARY KEY ( `target` )
 ) ENGINE = MYISAM;


--
-- Update `callforwards`
--

ALTER TABLE `callforwards` CHANGE
 `active` `active` ENUM( 'no', 'std', 'var', 'vml', 'ano', 'trl', 'par' ) 
 CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'no';
 
ALTER TABLE `callforwards`
 ADD `vm_rec_id` INT( 10 ) UNSIGNED NULL DEFAULT NULL  AFTER `number_vml`;


--
-- Update `queue_callforwards`
--

ALTER TABLE `queue_callforwards` ADD
 `number_vml` VARCHAR( 50 ) CHARACTER SET ascii COLLATE ascii_general_ci 
 NOT NULL AFTER `number_var`;


ALTER TABLE `queue_callforwards` ADD 
`vm_rec_id` INT( 10 ) UNSIGNED NULL DEFAULT NULL  AFTER `number_vml`;

 ALTER TABLE `queue_callforwards` CHANGE 
 `active` `active` ENUM( 'no', 'std', 'var', 'vml', 'trl', 'par' )
  CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'no';

