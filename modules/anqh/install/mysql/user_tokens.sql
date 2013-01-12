CREATE TABLE `user_tokens` (
  `id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_agent` varchar(40) COLLATE utf8_swedish_ci DEFAULT NULL,
  `token` varchar(32) COLLATE utf8_swedish_ci DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `expires` int(11) DEFAULT NULL,
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_tokens_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
