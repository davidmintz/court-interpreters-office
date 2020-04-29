/*
CREATE TABLE `locations` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` smallint(5) unsigned NOT NULL,
  `parent_location_id` smallint(5) unsigned DEFAULT NULL,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `comments` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name_and_parent` (`name`,`parent_location_id`),
  KEY `IDX_17E64ABAC54C8C93` (`type_id`),
  KEY `IDX_17E64ABA6D6133FE` (`parent_location_id`),
  CONSTRAINT `FK_17E64ABA6D6133FE` FOREIGN KEY (`parent_location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `FK_17E64ABAC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `location_types` (`id`)
)
*/
SET @PEARL = (SELECT id FROM locations WHERE name = '500 Pearl');
SET @FOLEY = (SELECT id FROM locations WHERE name = '40 Foley');
SET @WPLAINS = (SELECT id FROM locations WHERE name = 'White Plains');
SET @TYPE_COURTROOM = (SELECT id FROM location_types WHERE type = 'courtroom');
INSERT INTO `locations` VALUES
(NULL,3,@PEARL,'4th floor cellblock','',1),
(NULL,8,@PEARL,'cafeteria','',1),
(NULL,6,@PEARL,'Interpreters Office','room 280',1),
(NULL,4,@PEARL,'Probation','6th and 7th floors',1),
(NULL, @TYPE_COURTROOM, @PEARL,'5A','5th floor Magistrate courtroom',1),
(NULL,5,@PEARL,'Pretrial','5th floor',1),
(NULL,4,@WPLAINS,'Probation','',1),
(NULL,3,@FOLEY,'3rd floor cellblock','',1);
