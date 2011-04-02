CREATE TABLE `image_comments` (
  `id` int(11) NOT NULL auto_increment,
  `image_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `created` int(11) default NULL,
  `comment` varchar(300) collate utf8_swedish_ci NOT NULL,
  `private` smallint(6) default NULL,
  PRIMARY KEY  (`id`),
  KEY `image_id` (`image_id`),
  KEY `user_id` (`user_id`,`author_id`),
  KEY `user_id_2` (`user_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `image_comments_author_id_new` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `image_comments_image_id_new` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `image_comments_user_id_new` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
