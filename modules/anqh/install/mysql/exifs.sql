CREATE TABLE `exifs` (
  `id` int(11) NOT NULL auto_increment,
  `image_id` int(11) default NULL,
  `make` varchar(64) collate utf8_swedish_ci default NULL,
  `model` varchar(64) collate utf8_swedish_ci default NULL,
  `exposure` varchar(25) collate utf8_swedish_ci default NULL,
  `aperture` varchar(10) collate utf8_swedish_ci default NULL,
  `focal` varchar(10) collate utf8_swedish_ci default NULL,
  `iso` int(11) default NULL,
  `taken` datetime default NULL,
  `flash` varchar(64) collate utf8_swedish_ci default NULL,
  `program` varchar(64) collate utf8_swedish_ci default NULL,
  `metering` varchar(64) collate utf8_swedish_ci default NULL,
  `latitude` double default NULL,
  `latitude_ref` varchar(1) collate utf8_swedish_ci default NULL,
  `longitude` double default NULL,
  `longitude_ref` varchar(1) collate utf8_swedish_ci default NULL,
  `altitude` varchar(16) collate utf8_swedish_ci default NULL,
  `altitude_ref` varchar(16) collate utf8_swedish_ci default NULL,
  `lens` varchar(64) collate utf8_swedish_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `image_id` (`image_id`),
  CONSTRAINT `exifs_image_id` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;