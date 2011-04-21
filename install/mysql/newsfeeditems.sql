CREATE TABLE `newsfeeditems` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `stamp` int(11) NOT NULL,
  `class` varchar(64) collate utf8_swedish_ci default NULL,
  `type` varchar(64) collate utf8_swedish_ci default NULL,
  `data` text collate utf8_swedish_ci,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `newsfeeditem_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
