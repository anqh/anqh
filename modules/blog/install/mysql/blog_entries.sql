CREATE TABLE `blog_entries` (
  `id` int(11) NOT NULL auto_increment,
  `author_id` int(11) NOT NULL,
  `name` varchar(250) collate utf8_swedish_ci default NULL,
  `content` text collate utf8_swedish_ci,
  `created` int(11) default NULL,
  `view_count` int(11) default '0',
  `modified` int(11) default NULL,
  `modify_count` int(11) default '0',
  `comment_count` int(11) default '0',
  `new_comment_count` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `blog_entries_author_id` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
