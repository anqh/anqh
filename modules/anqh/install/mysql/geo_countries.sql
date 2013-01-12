CREATE TABLE `geo_countries` (
  `id` int(11) NOT NULL,
  `name` varchar(200) collate utf8_swedish_ci NOT NULL,
  `code` varchar(2) collate utf8_swedish_ci NOT NULL,
  `currency` varchar(3) collate utf8_swedish_ci default NULL,
  `population` int(11) default NULL,
  `created` int(11) default NULL,
  `modified` int(11) default NULL,
  `i18n` text collate utf8_swedish_ci,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
