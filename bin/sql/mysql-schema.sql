-- MySQL dump 10.16  Distrib 10.1.44-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: office
-- ------------------------------------------------------
-- Server version	10.1.44-MariaDB-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_comment_backup`
--

DROP TABLE IF EXISTS `admin_comment_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_comment_backup` (
  `event_id` mediumint(8) unsigned NOT NULL,
  `admin_comments` varchar(600) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `username` varchar(24) NOT NULL DEFAULT '',
  `firstname` varchar(40) NOT NULL,
  `lastname` varchar(40) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `password` varchar(16) NOT NULL,
  `created` datetime NOT NULL,
  `last_login` int(10) unsigned NOT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `map_to_userid` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxUsername` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `app_event_log`
--

DROP TABLE IF EXISTS `app_event_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_event_log` (
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channel` varchar(60) NOT NULL DEFAULT '',
  `message` varchar(250) NOT NULL,
  `entity_id` varchar(32) DEFAULT NULL,
  `entity_class` varchar(250) NOT NULL DEFAULT '',
  `priority` tinyint(3) unsigned NOT NULL,
  `priority_name` varchar(12) NOT NULL,
  `extra` varchar(5000) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `banned`
--

DROP TABLE IF EXISTS `banned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banned` (
  `interpreter_id` smallint(5) unsigned NOT NULL,
  `person_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`interpreter_id`,`person_id`),
  KEY `IDX_9B490DB6AD59FFB1` (`interpreter_id`),
  KEY `IDX_9B490DB6217BBB47` (`person_id`),
  CONSTRAINT `FK_9B490DB6217BBB47` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`),
  CONSTRAINT `FK_9B490DB6AD59FFB1` FOREIGN KEY (`interpreter_id`) REFERENCES `interpreters` (`id`)
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
  UNIQUE KEY `date` (`date`),
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
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
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
  `defendant_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`event_id`,`defendant_id`),
  KEY `IDX_DBDD360771F7E88B` (`event_id`),
  KEY `IDX_DBDD36079960FFFB` (`defendant_id`),
  CONSTRAINT `defendants_events_ibfk_1` FOREIGN KEY (`defendant_id`) REFERENCES `defendant_names` (`id`),
  CONSTRAINT `fk_deft_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `defendants_requests`
--

DROP TABLE IF EXISTS `defendants_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `defendants_requests` (
  `defendant_id` mediumint(8) unsigned NOT NULL,
  `request_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`request_id`,`defendant_id`),
  KEY `defendant_id` (`defendant_id`),
  CONSTRAINT `defendants_requests_ibfk_1` FOREIGN KEY (`defendant_id`) REFERENCES `defendant_names` (`id`),
  CONSTRAINT `defendants_requests_ibfk_2` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `docket_annotations`
--

DROP TABLE IF EXISTS `docket_annotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docket_annotations` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `comment` varchar(600) COLLATE utf8_unicode_ci NOT NULL,
  `created_by_id` smallint(5) unsigned NOT NULL,
  `modified_by_id` smallint(5) unsigned DEFAULT NULL,
  `docket` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `priority` smallint(5) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AD94F56AB03A8386` (`created_by_id`),
  KEY `IDX_AD94F56A99049ECE` (`modified_by_id`),
  KEY `docket_idx` (`docket`),
  CONSTRAINT `FK_AD94F56A99049ECE` FOREIGN KEY (`modified_by_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_AD94F56AB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`)
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
-- Table structure for table `event_meta_comments`
--

DROP TABLE IF EXISTS `event_meta_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_meta_comments` (
  `id` mediumint(8) unsigned NOT NULL,
  `comments` varchar(600) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
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
  KEY `docket_idx` (`docket`),
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
  `solicit_availability` tinyint(1) NOT NULL DEFAULT '0',
  `comments` varchar(600) COLLATE utf8_unicode_ci NOT NULL,
  `address1` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `address2` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `bop_form_submission_date` date DEFAULT NULL,
  `publish_public` tinyint(1) NOT NULL DEFAULT '1',
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
  `sent_confirmation_email` tinyint(1) unsigned NOT NULL DEFAULT '0',
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
  `credential_id` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`interpreter_id`,`language_id`),
  KEY `IDX_E0423968AD59FFB1` (`interpreter_id`),
  KEY `IDX_E042396882F1BAF4` (`language_id`),
  KEY `FK_E04239682558A7A5` (`credential_id`),
  CONSTRAINT `FK_E04239682558A7A5` FOREIGN KEY (`credential_id`) REFERENCES `language_credentials` (`id`),
  CONSTRAINT `FK_E042396882F1BAF4` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `FK_E0423968AD59FFB1` FOREIGN KEY (`interpreter_id`) REFERENCES `interpreters` (`id`) ON DELETE CASCADE
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
-- Table structure for table `language_credentials`
--

DROP TABLE IF EXISTS `language_credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `language_credentials` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `abbreviation` varchar(15) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(400) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`),
  UNIQUE KEY `unique_abbrev` (`abbreviation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
-- Table structure for table `motd`
--

DROP TABLE IF EXISTS `motd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motd` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `created_by_id` smallint(5) unsigned NOT NULL,
  `modified_by_id` smallint(5) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `content` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_idx` (`date`),
  KEY `IDX_AA0F656CB03A8386` (`created_by_id`),
  KEY `IDX_AA0F656C99049ECE` (`modified_by_id`),
  CONSTRAINT `FK_AA0F656C99049ECE` FOREIGN KEY (`modified_by_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_AA0F656CB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `motw`
--

DROP TABLE IF EXISTS `motw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motw` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `created_by_id` smallint(5) unsigned NOT NULL,
  `modified_by_id` smallint(5) unsigned DEFAULT NULL,
  `week_of` date NOT NULL,
  `content` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `week_idx` (`week_of`),
  KEY `created_by_id` (`created_by_id`),
  KEY `modified_by_id` (`modified_by_id`),
  CONSTRAINT `motw_ibfk_1` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`),
  CONSTRAINT `motw_ibfk_2` FOREIGN KEY (`modified_by_id`) REFERENCES `users` (`id`)
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
  UNIQUE KEY `hat_active_email_idx` (`email`,`hat_id`,`active`),
  KEY `IDX_28166A268C6A5980` (`hat_id`),
  CONSTRAINT `FK_28166A268C6A5980` FOREIGN KEY (`hat_id`) REFERENCES `hats` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `comments` varchar(1000) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
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
-- Table structure for table `rotation_substitutions`
--

DROP TABLE IF EXISTS `rotation_substitutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rotation_substitutions` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` smallint(5) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `duration` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `rotation_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subst_idx` (`date`,`rotation_id`,`duration`),
  KEY `IDX_727F3401217BBB47` (`person_id`),
  KEY `IDX_727F3401326CE1FB` (`rotation_id`),
  CONSTRAINT `FK_727F3401217BBB47` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`),
  CONSTRAINT `FK_727F3401326CE1FB` FOREIGN KEY (`rotation_id`) REFERENCES `rotations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rotations`
--

DROP TABLE IF EXISTS `rotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rotations` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` smallint(5) unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D19D71198DB60186` (`task_id`),
  CONSTRAINT `FK_D19D71198DB60186` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task_rotation_members`
--

DROP TABLE IF EXISTS `task_rotation_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_rotation_members` (
  `rotation_id` smallint(5) unsigned NOT NULL,
  `person_id` smallint(5) unsigned NOT NULL,
  `rotation_order` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`rotation_id`,`person_id`),
  KEY `IDX_FEC6F7C4326CE1FB` (`rotation_id`),
  KEY `IDX_FEC6F7C4217BBB47` (`person_id`),
  CONSTRAINT `FK_FEC6F7C4217BBB47` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`),
  CONSTRAINT `FK_FEC6F7C4326CE1FB` FOREIGN KEY (`rotation_id`) REFERENCES `rotations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(400) COLLATE utf8_unicode_ci NOT NULL,
  `duration` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `frequency` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `day_of_week` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`)
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
  `failed_login_attempts` tinyint(1) unsigned NOT NULL DEFAULT '0',
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
-- Table structure for table `verification_tokens`
--

DROP TABLE IF EXISTS `verification_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `verification_tokens` (
  `id` varchar(32) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expiration` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `view_locations`
--

DROP TABLE IF EXISTS `view_locations`;
/*!50001 DROP VIEW IF EXISTS `view_locations`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `view_locations` (
  `id` tinyint NOT NULL,
  `type_id` tinyint NOT NULL,
  `parent_location_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `comments` tinyint NOT NULL,
  `active` tinyint NOT NULL,
  `parent` tinyint NOT NULL,
  `category` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `view_locations`
--

/*!50001 DROP TABLE IF EXISTS `view_locations`*/;
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

-- Dump completed on 2020-07-22 20:59:22
