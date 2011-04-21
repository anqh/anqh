CREATE TABLE `forum_quotes` (
  `user_id` int(11) NOT NULL,
  `forum_topic_id` int(11) NOT NULL,
  `forum_post_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `created` int(11) default NULL,
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `forum_quotes_author_id` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `forum_quotes_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
