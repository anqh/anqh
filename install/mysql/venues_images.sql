CREATE TABLE `venues_images` (
  `id` int(11) NOT NULL auto_increment,
  `venue_id` int(11) default NULL,
  `image_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  CONSTRAINT `venues_images_image_id` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `venues_images_venue_id` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
