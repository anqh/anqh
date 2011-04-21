CREATE TABLE `events_tags` (
  `event_id` int(11) default NULL,
  `tag_id` int(11) default NULL,
  KEY `event_id` (`event_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `events_tags_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `events_tags_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
