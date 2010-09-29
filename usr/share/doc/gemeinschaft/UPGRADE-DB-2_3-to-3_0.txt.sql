USE `asterisk`;

--
-- Update structure for table `queue_cf_timerules`
--

ALTER TABLE `queue_cf_timerules`
 ADD `id` int(10) unsigned NOT NULL auto_increment FIRST;

ALTER TABLE `queue_cf_timerules`
 ADD PRIMARY KEY  (`id`);


 