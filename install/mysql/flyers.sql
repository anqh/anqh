CREATE TABLE `flyers` (
  `id` int(11) NOT NULL auto_increment,
  `event_id` int(11) default NULL,
  `image_id` int(11) NOT NULL,
  `stamp_begin` int(11) default NULL,
  `name` varchar(250) collate utf8_swedish_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`),
  KEY `image_id` (`image_id`),
  CONSTRAINT `flyers_image_id_fkey` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `flyers_event_id_fkey` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
