-- MySQL dump 10.13  Distrib 5.7.22, for Linux (x86_64)
--
-- Host: localhost    Database: office
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu0.16.04.1

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
-- Table structure for table `anonymous_judges`
--

DROP TABLE IF EXISTS `anonymous_judges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anonymous_judges` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `default_location_id` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_anon_judge` (`name`,`default_location_id`),
  KEY `IDX_5BD10E2D2BE3238` (`default_location_id`),
  CONSTRAINT `anonymous_judges_ibfk_1` FOREIGN KEY (`default_location_id`) REFERENCES `locations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cancellation_reasons`
--

DROP TABLE IF EXISTS `cancellation_reasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cancellation_reasons` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `reason` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cancel_reason` (`reason`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `name` char(120) NOT NULL DEFAULT '',
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `supercat_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clerks_judges`
--

DROP TABLE IF EXISTS `clerks_judges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clerks_judges` (
  `user_id` smallint(5) unsigned NOT NULL,
  `judge_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`judge_id`),
  KEY `IDX_DB59EF06A76ED395` (`user_id`),
  KEY `IDX_DB59EF06B7D66194` (`judge_id`),
  CONSTRAINT `FK_DB59EF06A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_DB59EF06B7D66194` FOREIGN KEY (`judge_id`) REFERENCES `judges` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `court_closings`
--

DROP TABLE IF EXISTS `court_closings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `court_closings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holiday_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `description_other` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F21F4FD1830A3EC0` (`holiday_id`),
  CONSTRAINT `FK_F21F4FD1830A3EC0` FOREIGN KEY (`holiday_id`) REFERENCES `holidays` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `defendant_names`
--

DROP TABLE IF EXISTS `defendant_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `defendant_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `given_names` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `surnames` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_deftname` (`given_names`,`surnames`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `defendants_events`
--

DROP TABLE IF EXISTS `defendants_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `defendants_events` (
  `event_id` mediumint(8) unsigned NOT NULL,
  `defendant_id` int(11) NOT NULL,
  PRIMARY KEY (`event_id`,`defendant_id`),
  KEY `IDX_DBDD360771F7E88B` (`event_id`),
  KEY `IDX_DBDD36079960FFFB` (`defendant_id`),
  CONSTRAINT `fk_deft_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_deft_name` FOREIGN KEY (`defendant_id`) REFERENCES `defendant_names` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_categories`
--

DROP TABLE IF EXISTS `event_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_event_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_types`
--

DROP TABLE IF EXISTS `event_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_types` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` smallint(5) unsigned NOT NULL,
  `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `comments` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_event_type` (`name`),
  KEY `IDX_182B381C12469DE2` (`category_id`),
  CONSTRAINT `FK_182B381C12469DE2` FOREIGN KEY (`category_id`) REFERENCES `event_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` smallint(5) unsigned NOT NULL,
  `judge_id` smallint(5) unsigned DEFAULT NULL,
  `submitter_id` smallint(5) unsigned DEFAULT NULL,
  `location_id` smallint(5) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `docket` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments` varchar(600) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `admin_comments` varchar(600) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `event_type_id` smallint(5) unsigned NOT NULL,
  `created_by_id` smallint(5) unsigned NOT NULL,
  `anonymous_judge_id` smallint(5) unsigned DEFAULT NULL,
  `anonymous_submitter_id` smallint(5) unsigned DEFAULT NULL,
  `cancellation_reason_id` smallint(5) unsigned DEFAULT NULL,
  `modified_by_id` smallint(5) unsigned DEFAULT NULL,
  `submission_date` date NOT NULL,
  `submission_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5387574A82F1BAF4` (`language_id`),
  KEY `IDX_5387574AB7D66194` (`judge_id`),
  KEY `IDX_5387574A919E5513` (`submitter_id`),
  KEY `IDX_5387574A64D218E` (`location_id`),
  KEY `IDX_5387574AFF915C63` (`anonymous_judge_id`),
  KEY `IDX_5387574A61A31DAE` (`anonymous_submitter_id`),
  KEY `IDX_5387574A8453C906` (`cancellation_reason_id`),
  KEY `IDX_5387574AB03A8386` (`created_by_id`),
  KEY `IDX_5387574A99049ECE` (`modified_by_id`),
  KEY `IDX_5387574A401B253C` (`event_type_id`),
  CONSTRAINT `FK_5387574A401B253C` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`),
  CONSTRAINT `FK_5387574A61A31DAE` FOREIGN KEY (`anonymous_submitter_id`) REFERENCES `hats` (`id`),
  CONSTRAINT `FK_5387574A64D218E` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `FK_5387574A82F1BAF4` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `FK_5387574A8453C906` FOREIGN KEY (`cancellation_reason_id`) REFERENCES `cancellation_reasons` (`id`),
  CONSTRAINT `FK_5387574A919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `people` (`id`),
  CONSTRAINT `FK_5387574A99049ECE` FOREIGN KEY (`modified_by_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_5387574AB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_5387574AB7D66194` FOREIGN KEY (`judge_id`) REFERENCES `judges` (`id`),
  CONSTRAINT `FK_5387574AFF915C63` FOREIGN KEY (`anonymous_judge_id`) REFERENCES `anonymous_judges` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hats`
--

DROP TABLE IF EXISTS `hats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hats` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` smallint(5) unsigned DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `anonymity` int(10) unsigned NOT NULL DEFAULT '0',
  `is_judges_staff` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hat_idx` (`name`),
  KEY `IDX_149C3D93D60322AC` (`role_id`),
  CONSTRAINT `FK_149C3D93D60322AC` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interpreters`
--

DROP TABLE IF EXISTS `interpreters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interpreters` (
  `id` smallint(5) unsigned NOT NULL,
  `home_phone` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dob` varchar(125) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ssn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `security_clearance_date` date DEFAULT NULL,
  `fingerprint_date` date DEFAULT NULL,
  `oath_date` date DEFAULT NULL,
  `contract_expiration_date` date DEFAULT NULL,
  `comments` varchar(600) COLLATE utf8_unicode_ci NOT NULL,
  `address1` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `address2` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ssn` (`ssn`),
  CONSTRAINT `FK_4EBBDB02BF396750` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interpreters_events`
--

DROP TABLE IF EXISTS `interpreters_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interpreters_events` (
  `interpreter_id` smallint(5) unsigned NOT NULL,
  `event_id` mediumint(8) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `created_by_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`interpreter_id`,`event_id`),
  UNIQUE KEY `unique_interp_event` (`interpreter_id`,`event_id`),
  KEY `IDX_590E5B07AD59FFB1` (`interpreter_id`),
  KEY `IDX_590E5B07B03A8386` (`created_by_id`),
  KEY `IDX_590E5B0771F7E88B` (`event_id`),
  CONSTRAINT `FK_590E5B07AD59FFB1` FOREIGN KEY (`interpreter_id`) REFERENCES `interpreters` (`id`),
  CONSTRAINT `FK_590E5B07B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interpreters_languages`
--

DROP TABLE IF EXISTS `interpreters_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interpreters_languages` (
  `interpreter_id` smallint(5) unsigned NOT NULL,
  `language_id` smallint(5) unsigned NOT NULL,
  `federal_certification` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`interpreter_id`,`language_id`),
  KEY `IDX_E0423968AD59FFB1` (`interpreter_id`),
  KEY `IDX_E042396882F1BAF4` (`language_id`),
  CONSTRAINT `FK_E042396882F1BAF4` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `FK_E0423968AD59FFB1` FOREIGN KEY (`interpreter_id`) REFERENCES `interpreters` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `judge_flavors`
--

DROP TABLE IF EXISTS `judge_flavors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `judge_flavors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flavor` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `weight` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_judge_flavor` (`flavor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `judges`
--

DROP TABLE IF EXISTS `judges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `judges` (
  `id` smallint(5) unsigned NOT NULL,
  `default_location_id` smallint(5) unsigned DEFAULT NULL,
  `flavor_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1C5E0B5D2BE3238` (`default_location_id`),
  KEY `IDX_1C5E0B5FDDA6450` (`flavor_id`),
  CONSTRAINT `FK_1C5E0B5BF396750` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1C5E0B5D2BE3238` FOREIGN KEY (`default_location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `FK_1C5E0B5FDDA6450` FOREIGN KEY (`flavor_id`) REFERENCES `judge_flavors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `comments` varchar(300) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_language` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `location_types`
--

DROP TABLE IF EXISTS `location_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location_types` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `comments` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `hat_id` smallint(5) unsigned NOT NULL,
  `email` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `middlename` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `office_phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mobile_phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL,
  `discr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hat_email_idx` (`email`,`hat_id`),
  UNIQUE KEY `active_email_idx` (`email`,`active`),
  KEY `IDX_28166A268C6A5980` (`hat_id`),
  CONSTRAINT `FK_28166A268C6A5980` FOREIGN KEY (`hat_id`) REFERENCES `hats` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `comments` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` smallint(5) unsigned NOT NULL,
  `role_id` smallint(5) unsigned NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `person_id` (`person_id`,`role_id`),
  UNIQUE KEY `unique_username` (`username`),
  KEY `IDX_1483A5E9D60322AC` (`role_id`),
  CONSTRAINT `FK_1483A5E9D60322AC` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `view_locations`
--

DROP TABLE IF EXISTS `view_locations`;
/*!50001 DROP VIEW IF EXISTS `view_locations`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `view_locations` AS SELECT 
 1 AS `id`,
 1 AS `type_id`,
 1 AS `parent_location_id`,
 1 AS `name`,
 1 AS `comments`,
 1 AS `active`,
 1 AS `parent`,
 1 AS `category`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `view_locations`
--

/*!50001 DROP VIEW IF EXISTS `view_locations`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `view_locations` AS select `locations`.`id` AS `id`,`locations`.`type_id` AS `type_id`,`locations`.`parent_location_id` AS `parent_location_id`,`locations`.`name` AS `name`,`locations`.`comments` AS `comments`,`locations`.`active` AS `active`,`parent`.`name` AS `parent`,`type`.`type` AS `category` from ((`locations` left join `locations` `parent` on((`locations`.`parent_location_id` = `parent`.`id`))) join `location_types` `type` on((`locations`.`type_id` = `type`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-06-07 17:06:35
