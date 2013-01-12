CREATE TABLE `api_requests` (
  `id` int(11) NOT NULL auto_increment,
  `ip` varchar(15) collate utf8_swedish_ci default NULL,
  `created` int(11) default NULL,
  `request` text collate utf8_swedish_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
