USE `asterisk`;

--
-- Update structure for table `queue_cf_timerules`
--

ALTER TABLE `queue_cf_timerules`
 ADD `id` int(10) unsigned NOT NULL auto_increment FIRST;

ALTER TABLE `queue_cf_timerules`
 ADD PRIMARY KEY  (`id`);


--
-- Update structure for table `ast_queue_members`
--


ALTER TABLE `ast_queue_members`
 DROP PRIMARY KEY;

ALTER TABLE `ast_queue_members`
 ADD PRIMARY KEY (`uniqueid`);

ALTER TABLE `ast_queue_members`
 ADD UNIQUE KEY `queue_name_interface` (`queue_name`,`interface`);



--
-- Since: QueueMon
--

SET character_set_client = utf8;
CREATE TABLE `monitor` (
  `user_id` int(10) unsigned NOT NULL,
  `type` tinyint(2) unsigned NOT NULL default '1',
  `display_x` smallint(4) unsigned NOT NULL default '0',
  `display_y` smallint(4) unsigned NOT NULL default '0',
  `columns` tinyint(2) unsigned NOT NULL default '2',
  `update` smallint(4) unsigned NOT NULL default '2',
  `reload` smallint(4) unsigned NOT NULL default '120',
  PRIMARY KEY  (`user_id`, `type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci;

SET character_set_client = utf8;
CREATE TABLE `monitor_colors` (
  `user_id` int(10) unsigned NOT NULL,
  `type` tinyint(2) unsigned NOT NULL default '1',
  `status` tinyint(3) unsigned NOT NULL default '2',
  `color` varchar(20) collate utf8_unicode_ci NOT NULL default '#fff',
  PRIMARY KEY  (`user_id`, `type`, `status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci;

SET character_set_client = utf8;
CREATE TABLE `monitor_queues` (
  `user_id` int(10) unsigned NOT NULL,
  `queue_id` int(10) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '1',
  `display_columns` tinyint(2) unsigned NOT NULL default '2',
  `display_width` smallint(4) unsigned NOT NULL default '500',
  `display_height` smallint(4) unsigned NOT NULL default '150',
  `display_calls` smallint(5) unsigned NOT NULL default '15',
  `display_answered` smallint(5) unsigned NOT NULL default '15',
  `display_abandoned` smallint(5) unsigned NOT NULL default '15',
  `display_timeout` smallint(5) unsigned NOT NULL default '15',
  `display_wait_max` smallint(5) unsigned NOT NULL default '15',
  `display_wait_min` smallint(5) unsigned NOT NULL default '15',
  `display_wait_avg` smallint(5) unsigned NOT NULL default '15',
  `display_call_max` smallint(5) unsigned NOT NULL default '15',
  `display_call_min` smallint(5) unsigned NOT NULL default '15',
  `display_call_avg` smallint(5) unsigned NOT NULL default '15',
  PRIMARY KEY  (`user_id`, `queue_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci;
