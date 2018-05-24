// gs 3.3 private phonebook modifications
DROP TABLE IF EXISTS `pb_prv_previous`;
RENAME TABLE `pb_prv` TO `pb_prv_previous`;

CREATE TABLE IF NOT EXISTS `pb_prv` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `firstname` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `lastname` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `number` varchar(25) CHARACTER SET ascii NOT NULL DEFAULT '',
  `ptype` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT 'cell,work,home',
  `pref` int(2) unsigned NOT NULL DEFAULT '9',
  `card_id` int(10) unsigned NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uid_vcard` (`user_id`,`card_id`),
  KEY `uid_lastname_firstname_pref` (`user_id`,`lastname`(15),`firstname`(10),`pref`,`ptype`),
  KEY `cloud_card_id` (`card_id`),
  KEY `uid_number_pref` (`user_id`,`number`(10),`pref`,`ptype`),
  KEY `uid_firstname_lastname_pref` (`user_id`,`firstname`(10),`lastname`(10),`pref`,`ptype`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `pb_prv` (`id`, `user_id`, `firstname`, `lastname`, `number`) 
  SELECT `id`, `user_id`, `firstname`, `lastname`, `number` FROM `pb_prv_previous`;
  
ALTER TABLE `pb_prv` ADD CONSTRAINT `pb_prv_ibfk_1` FOREIGN KEY (`user_id`) 
  REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT ;

// import voip phone book from cloud for preparing the xml to phone functionality
CREATE TABLE IF NOT EXISTS `pb_cloud` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `url` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `login` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varbinary(64) NOT NULL,
  `frequency` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1d',
  `ctag` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `last_remote_modified` datetime NOT NULL,
  `next_poll` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `message` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `error_count` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `public` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_url_login` (`user_id`,`url`(255),`login`),
  KEY `next_poll` (`next_poll`),
  KEY `uid_login_url` (`user_id`,`login`,`url`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `pb_cloud` ADD FOREIGN KEY ( `user_id` ) 
  REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT ;

// holds the vcards
CREATE TABLE IF NOT EXISTS `pb_cloud_card` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cloud_id` int(10) unsigned NOT NULL,
  `vcard_id` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `etag` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `vcard` text COLLATE utf8_unicode_ci NOT NULL,
  `last_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cloud_id_vcard_id` (`cloud_id`,`vcard_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `pb_cloud_card` ADD FOREIGN KEY ( `cloud_id` ) 
  REFERENCES `pb_cloud` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;
ALTER TABLE `pb_prv` ADD FOREIGN KEY ( `card_id` ) 
  REFERENCES `pb_cloud_card` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

// add categories (i.e like family, company etc.)
CREATE TABLE IF NOT EXISTS `pb_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `category` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_catid` (`user_id`,`category`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `pb_category` ADD FOREIGN KEY ( `user_id` ) 
  REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

// connect a phone book entry to a category
CREATE TABLE IF NOT EXISTS `pb_prv_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `cat_id` int(10) unsigned NOT NULL,
  `card_id` int(10) unsigned NOT NULL,
  `prv_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid_catid_cardid` (`user_id`,`cat_id`,`card_id`),
  KEY `uid_prvid` (`user_id`,`prv_id`),
  KEY `uid_cardid` (`user_id`,`card_id`),
  KEY `card_id` (`card_id`),
  KEY `prv_id` (`prv_id`),
  KEY `catid_uid` (`cat_id`,`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `pb_prv_category` ADD FOREIGN KEY ( `user_id` ) 
  REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;
ALTER TABLE `pb_prv_category` ADD FOREIGN KEY ( `cat_id` ) 
  REFERENCES `pb_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;
ALTER TABLE `pb_prv_category` ADD FOREIGN KEY ( `card_id` ) 
  REFERENCES `pb_cloud_card` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;
ALTER TABLE `pb_prv_category` ADD FOREIGN KEY ( `prv_id` ) 
  REFERENCES `pb_prv` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

// set new modules active
INSERT INTO `group_members` VALUES (6, 3005);
// needed dependency for public cloud entries
INSERT INTO `users` VALUES ('1', 'public-abook', '', '', '', '', '', '1', '1', NULL, '', NULL, NULL, NULL, '');
