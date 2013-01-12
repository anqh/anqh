CREATE TABLE `sessions` (
  `id` varchar(40) COLLATE utf8_swedish_ci NOT NULL,
  `last_active` int(11) DEFAULT NULL,
  `contents` text COLLATE utf8_swedish_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
