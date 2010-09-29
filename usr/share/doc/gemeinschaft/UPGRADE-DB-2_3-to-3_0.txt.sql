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
