CREATE TABLE `prov_siemens` (
  `mac_addr` char(12) character set ascii NOT NULL,
  `device` varchar(64) character set ascii NOT NULL,
  `t_last_contact` int(10) unsigned NOT NULL default '0',
  `type` varchar(25) character set ascii NOT NULL,
  `sw_vers` varchar(15) character set ascii NOT NULL,
  `t_sw_deployed` int(10) unsigned NOT NULL default '0',
  `t_ldap_deployed` int(10) unsigned NOT NULL default '0',
  `t_logo_deployed` int(10) unsigned NOT NULL default '0',
  KEY `mac_addr` (`mac_addr`),
  KEY `sw_vers` (`sw_vers`(12)),
  KEY `t_last_contact` (`t_last_contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

