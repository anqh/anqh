CREATE TABLE `blog_comments` (
  `id` int(11) NOT NULL auto_increment,
  `blog_entry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `created` int(11) default NULL,
  `comment` varchar(300) collate utf8_swedish_ci NOT NULL,
  `private` smallint(6) DEFAULT 0 NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `blog_entry_id` (`blog_entry_id`),
  KEY `user_id` (`user_id`,`author_id`),
  KEY `user_id_2` (`user_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `blog_comments_author_id` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blog_comments_blog_entry_id` FOREIGN KEY (`blog_entry_id`) REFERENCES `blog_entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blog_comments_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
