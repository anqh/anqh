CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_group_id` int(11) DEFAULT NULL,
  `name` varchar(32) COLLATE utf8_swedish_ci DEFAULT NULL,
  `description` varchar(250) COLLATE utf8_swedish_ci DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tag_group_id` (`tag_group_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `tags_author_id` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tags_tag_group_id` FOREIGN KEY (`tag_group_id`) REFERENCES `tag_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
