CREATE TABLE `logins` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `username` text collate utf8_swedish_ci,
  `ip` varchar(15) collate utf8_swedish_ci default NULL,
  `hostname` text collate utf8_swedish_ci,
  `success` smallint(6) default '0',
  `password` int(11) default '0',
  `stamp` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=136 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
