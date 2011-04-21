CREATE TABLE `galleries_images` (
  `gallery_id` int(11) NOT NULL,
  `image_id` int(11) NOT NULL,
  KEY `gallery_id` (`gallery_id`),
  KEY `image_id` (`image_id`),
  CONSTRAINT `galleries_images_image_id` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `galleries_images_gallery_id` FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
