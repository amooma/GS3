CREATE TABLE IF NOT EXISTS `pb_prv` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `firstname` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `lastname` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `number` varchar(25) CHARACTER SET ascii NOT NULL DEFAULT '',
  `ptype` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT 'cell,work,home',
  `vcard_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uid_lastname_firstname` (`user_id`,`lastname`(15),`firstname`(10)),
  KEY `uid_firstname_lastname` (`user_id`,`firstname`(10),`lastname`(10)),
  KEY `uid_number` (`user_id`,`number`(10)),
  KEY `uid_vcard` (`user_id`,`vcard_id`),
  KEY `uid_cat_lastname_firstname` (`user_id`,`cat_id`,`lastname`,`firstname`),
  KEY `uid_cat_firstname_lastname` (`user_id`,`cat_id`,`firstname`,`lastname`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `pb_cloud` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `url` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `login` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varbinary(64) NOT NULL,
  `frequency` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1d',
  `next_poll` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `next_poll` (`next_poll`),
  KEY `url` (`user_id`,`url`(255)),
  KEY `login` (`user_id`,`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `pb_prv`  ADD `ptype` VARCHAR(16) NOT NULL COMMENT 'cell,work,home';
ALTER TABLE `pb_prv` ADD `vcard_id` int(11) NOT NULL;
ALTER TABLE `pb_prv` ADD `cat_id` int(11) NOT NULL;
ALTER TABLE `pb_prv` ADD `modified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `pb_prv` ADD INDEX `uid_vcard` (`user_id`,`vcard_id`);
ALTER TABLE `pb_prv` ADD INDEX `uid_cat_lastname_firstname` (`user_id`,`cat_id`,`lastname`,`firstname`);
ALTER TABLE `pb_prv` ADD INDEX `uid_cat_firstname_lastname` (`user_id`,`cat_id`,`firstname`,`lastname`);
