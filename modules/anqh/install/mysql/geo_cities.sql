CREATE TABLE `geo_cities` (
  `id` int(11) NOT NULL,
  `name` varchar(200) collate utf8_swedish_ci NOT NULL,
  `geo_country_id` int(11) NOT NULL,
  `latitude` double(8,4) default NULL,
  `longitude` double(8,4) default NULL,
  `population` int(11) default NULL,
  `geo_timezone_id` varchar(64) collate utf8_swedish_ci default NULL,
  `created` int(11) default NULL,
  `modified` int(11) default NULL,
  `i18n` text collate utf8_swedish_ci,
  PRIMARY KEY  (`id`),
  KEY `geo_country_id` (`geo_country_id`),
  KEY `geo_timezone_id` (`geo_timezone_id`),
  CONSTRAINT `geo_cities_geo_country_id` FOREIGN KEY (`geo_country_id`) REFERENCES `geo_countries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
