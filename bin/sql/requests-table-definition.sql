-- MySQL dump 10.13  Distrib 5.7.23, for Linux (x86_64)
--
-- Host: localhost    Database: office
-- ------------------------------------------------------
-- Server version	5.7.23-0ubuntu0.16.04.1

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
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
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
  CONSTRAINT `requests_ibfk_7` FOREIGN KEY (`anonymous_judge_id`) REFERENCES `anonymous_judges` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-08-14 15:53:04
