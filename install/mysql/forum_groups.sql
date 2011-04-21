CREATE TABLE `forum_groups` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(32) collate utf8_swedish_ci NOT NULL,
  `sort` smallint(6) NOT NULL default '0',
  `author_id` int(11) default NULL,
  `description` varchar(250) collate utf8_swedish_ci default NULL,
  `created` int(11) default NULL,
  `status` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `forum_groups_author_id` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
