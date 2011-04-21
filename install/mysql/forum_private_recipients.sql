CREATE TABLE `forum_private_recipients` (
  `forum_topic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `forum_area_id` int(11) default NULL,
  `unread` int(11) default '0',
  KEY `forum_topic_id` (`forum_topic_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `forum_private_recipients_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `forum_private_recipients_topic_id_fkey` FOREIGN KEY (`forum_topic_id`) REFERENCES `forum_private_topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
