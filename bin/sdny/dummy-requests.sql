-- MySQL dump 10.13  Distrib 5.7.24, for Linux (x86_64)
--
-- Host: localhost    Database: office
-- ------------------------------------------------------
-- Server version	5.7.24-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requests` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `judge_id` smallint(5) unsigned DEFAULT NULL,
  `anonymous_judge_id` smallint(5) unsigned DEFAULT NULL,
  `event_type_id` smallint(5) unsigned NOT NULL,
  `language_id` smallint(6) unsigned NOT NULL,
  `docket` varchar(14) COLLATE utf8_unicode_ci NOT NULL,
  `location_id` smallint(5) unsigned DEFAULT NULL,
  `submitter_id` smallint(5) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `modified_by_id` smallint(5) unsigned NOT NULL,
  `comments` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `event_id` mediumint(8) unsigned DEFAULT NULL,
  `pending` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `cancelled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `extra_json_data` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_event_id` (`event_id`),
  KEY `evt_id` (`event_id`),
  KEY `submitter_id` (`submitter_id`),
  KEY `event_type_id` (`event_type_id`),
  KEY `location_id` (`location_id`),
  KEY `language_id` (`language_id`),
  KEY `modified_by_id` (`modified_by_id`),
  KEY `judge_id` (`judge_id`),
  KEY `anonymous_judge_id` (`anonymous_judge_id`),
  CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`),
  CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `requests_ibfk_3` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `requests_ibfk_4` FOREIGN KEY (`modified_by_id`) REFERENCES `users` (`id`),
  CONSTRAINT `requests_ibfk_5` FOREIGN KEY (`submitter_id`) REFERENCES `people` (`id`),
  CONSTRAINT `requests_ibfk_6` FOREIGN KEY (`judge_id`) REFERENCES `judges` (`id`),
  CONSTRAINT `requests_ibfk_7` FOREIGN KEY (`anonymous_judge_id`) REFERENCES `anonymous_judges` (`id`),
  CONSTRAINT `requests_ibfk_8` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20628 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requests`
--
-- WHERE:  pending

LOCK TABLES `requests` WRITE;
/*!40000 ALTER TABLE `requests` DISABLE KEYS */;
INSERT INTO `requests` VALUES (20624,'2019-02-01','08:30:00',2561,NULL,1,62,'2018-CR-0282',4,1071,'2019-01-22 17:27:37','2019-01-22 17:27:37',156,'USM#85604-054. Thank you\n[defendant names not found:]\nMorel-Moreta, Felix',NULL,1,0,''),(20625,'2019-01-30','10:00:00',2566,NULL,16,56,'2016-CR-0841',NULL,1239,'2019-01-23 07:35:55','2019-01-23 07:35:55',121,'',NULL,1,0,''),(20626,'2019-02-20','10:00:00',2566,NULL,41,62,'2015-CR-0401',NULL,1239,'2019-01-23 07:37:43','2019-01-23 07:37:43',121,'',NULL,1,0,''),(20627,'2019-01-31','11:00:00',2576,NULL,32,62,'2018-CR-0479',NULL,1034,'2019-01-23 08:25:56','2019-01-23 08:25:56',451,'',NULL,1,0,'');
/*!40000 ALTER TABLE `requests` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-01-23 10:11:31
