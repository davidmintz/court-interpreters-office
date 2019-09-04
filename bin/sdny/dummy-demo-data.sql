-- MySQL dump 10.13  Distrib 5.7.27, for Linux (x86_64)
--
-- Host: localhost    Database: office_demo
-- ------------------------------------------------------
-- Server version	5.7.27-0ubuntu0.18.04.1

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
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anonymous_judges`
--

LOCK TABLES `anonymous_judges` WRITE;
/*!40000 ALTER TABLE `anonymous_judges` DISABLE KEYS */;
INSERT INTO `anonymous_judges` VALUES (2,'(not applicable)',NULL),(3,'(unknown)',NULL),(1,'magistrate',NULL);
/*!40000 ALTER TABLE `anonymous_judges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_event_log`
--

DROP TABLE IF EXISTS `app_event_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_event_log` (
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channel` varchar(60) NOT NULL DEFAULT '',
  `message` varchar(350) NOT NULL,
  `entity_id` varchar(32) DEFAULT NULL,
  `entity_class` varchar(250) NOT NULL DEFAULT '',
  `priority` tinyint(3) unsigned NOT NULL,
  `priority_name` varchar(12) NOT NULL,
  `extra` varchar(5000) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_event_log`
--

LOCK TABLES `app_event_log` WRITE;
/*!40000 ALTER TABLE `app_event_log` DISABLE KEYS */;
INSERT INTO `app_event_log` VALUES ('2019-09-03 13:47:32','security','user mintz@vernontbludgeon.com logged out','512','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-03 13:49:21','security','user admin authenticated from IP address: 127.0.0.1','1','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-03 13:51:30','persons','user <nobody> added a new person: Hoyt Gackersly','45','InterpretersOffice\\Entity\\Person',6,'INFO',''),('2019-09-03 13:51:30','users','user <nobody> added a new user','12','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-03 13:52:24','persons','user <nobody> added a new person: Deedee Bridgewater','46','InterpretersOffice\\Entity\\Person',6,'INFO',''),('2019-09-03 13:52:24','users','user <nobody> added a new user','13','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-03 13:53:33','persons','user <nobody> added a new person: Carmen Lundy','47','InterpretersOffice\\Entity\\Person',6,'INFO',''),('2019-09-03 13:53:33','users','user <nobody> added a new user','14','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-03 13:55:15','persons','user <nobody> added a new person: Serena Williams','48','InterpretersOffice\\Entity\\Person',6,'INFO',''),('2019-09-03 13:55:15','users','user <nobody> added a new user','15','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-03 13:56:14','persons','user <nobody> added a new person: Charles Parker','49','InterpretersOffice\\Entity\\Person',6,'INFO',''),('2019-09-03 13:56:14','users','user <nobody> added a new user','16','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-03 14:26:57','persons','user <nobody> added a new person: Allen Ginsberg','50','InterpretersOffice\\Entity\\Person',6,'INFO',''),('2019-09-03 14:26:57','users','user <nobody> added a new user','17','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-03 14:30:21','persons','user <nobody> added a new person: Iris Murdoch','51','InterpretersOffice\\Entity\\Person',6,'INFO',''),('2019-09-03 14:30:21','users','user <nobody> added a new user','18','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-03 14:31:55','persons','user <nobody> added a new person: Herbie Hancock','52','InterpretersOffice\\Entity\\Person',6,'INFO',''),('2019-09-03 14:31:55','users','user <nobody> added a new user','19','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-03 14:33:14','persons','user <nobody> added a new person: Ronald McDonald','53','InterpretersOffice\\Entity\\Person',6,'INFO',''),('2019-09-03 14:33:14','users','user <nobody> added a new user','20','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-04 11:02:54','security','login failed for user david from IP address 127.0.0.1, reason: [\"A record with the supplied identity could not be found.\"]',NULL,'InterpretersOffice\\Entity\\User',6,'INFO','{\"entity_id\":null}'),('2019-09-04 11:03:48','security','login failed for user david from IP address 127.0.0.1, reason: [\"A record with the supplied identity could not be found.\"]',NULL,'InterpretersOffice\\Entity\\User',6,'INFO','{\"entity_id\":null}'),('2019-09-04 11:03:54','security','user admin authenticated from IP address: 127.0.0.1','1','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-04 11:08:23','','no vault enabled, not encrypting any interpreter data',NULL,'',6,'INFO',''),('2019-09-04 11:08:23','interpreters','user admin added a new interpreter: Ángel Romero','54','InterpretersOffice\\Entity\\Interpreter',6,'INFO',''),('2019-09-04 11:08:23','interpreterlanguages','user admin added a new interpreterlanguage',NULL,'InterpretersOffice\\Entity\\InterpreterLanguage',6,'INFO','{\"entity_id\":null}'),('2019-09-04 11:09:03','users','user <nobody> added a new user','21','InterpretersOffice\\Entity\\User',6,'INFO',''),('2019-09-04 11:18:55','','no vault enabled, not encrypting any interpreter data',NULL,'',6,'INFO',''),('2019-09-04 11:18:55','interpreters','user admin added a new interpreter: Kristaps Porziņģis','55','InterpretersOffice\\Entity\\Interpreter',6,'INFO',''),('2019-09-04 11:18:55','interpreterlanguages','user admin added a new interpreterlanguage',NULL,'InterpretersOffice\\Entity\\InterpreterLanguage',6,'INFO','{\"entity_id\":null}'),('2019-09-04 11:22:34','','no vault enabled, not encrypting any interpreter data',NULL,'',6,'INFO',''),('2019-09-04 11:22:34','interpreters','user admin added a new interpreter: Enver Hoxha','56','InterpretersOffice\\Entity\\Interpreter',6,'INFO',''),('2019-09-04 11:22:34','interpreterlanguages','user admin added a new interpreterlanguage',NULL,'InterpretersOffice\\Entity\\InterpreterLanguage',6,'INFO','{\"entity_id\":null}'),('2019-09-04 11:24:59','','no vault enabled, not encrypting any interpreter data',NULL,'',6,'INFO',''),('2019-09-04 11:24:59','interpreters','user admin added a new interpreter: Artur Aleksanyan','57','InterpretersOffice\\Entity\\Interpreter',6,'INFO',''),('2019-09-04 11:24:59','interpreterlanguages','user admin added a new interpreterlanguage',NULL,'InterpretersOffice\\Entity\\InterpreterLanguage',6,'INFO','{\"entity_id\":null}'),('2019-09-04 13:50:10','persons','user <nobody> added a new person: Katelynn González','79','InterpretersOffice\\Entity\\Person',6,'INFO',''),('2019-09-04 13:50:10','users','user <nobody> added a new user','22','InterpretersOffice\\Entity\\User',6,'INFO','');
/*!40000 ALTER TABLE `app_event_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `availability_invitees`
--

DROP TABLE IF EXISTS `availability_invitees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `availability_invitees` (
  `interp_id` smallint(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `availability_invitees`
--

LOCK TABLES `availability_invitees` WRITE;
/*!40000 ALTER TABLE `availability_invitees` DISABLE KEYS */;
/*!40000 ALTER TABLE `availability_invitees` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cancellation_reasons`
--

LOCK TABLES `cancellation_reasons` WRITE;
/*!40000 ALTER TABLE `cancellation_reasons` DISABLE KEYS */;
INSERT INTO `cancellation_reasons` VALUES (3,'belatedly adjourned'),(1,'defendant not produced'),(5,'forçe majeure'),(2,'no interpreter needed'),(7,'other'),(4,'party did not appear'),(6,'reason unknown');
/*!40000 ALTER TABLE `cancellation_reasons` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `clerks_judges`
--

LOCK TABLES `clerks_judges` WRITE;
/*!40000 ALTER TABLE `clerks_judges` DISABLE KEYS */;
INSERT INTO `clerks_judges` VALUES (6,2),(7,2),(8,5),(9,6),(10,4),(11,4),(12,7),(13,9),(14,10),(15,8),(16,13),(17,1),(18,11),(19,12),(20,3);
/*!40000 ALTER TABLE `clerks_judges` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `court_closings`
--

LOCK TABLES `court_closings` WRITE;
/*!40000 ALTER TABLE `court_closings` DISABLE KEYS */;
/*!40000 ALTER TABLE `court_closings` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `defendant_names`
--

LOCK TABLES `defendant_names` WRITE;
/*!40000 ALTER TABLE `defendant_names` DISABLE KEYS */;
INSERT INTO `defendant_names` VALUES (13,'Agustín','Barrios'),(5,'Alfredo','García'),(14,'Alguien','de los Santos'),(18,'Alguno','de los Zetas'),(7,'Boris','Badofsky'),(23,'Carlos','Rodríguez Medina'),(21,'Carmen','Rodríguez Peña'),(28,'David','Mintzovski'),(2,'Erika','de los Ríos'),(15,'Esteban','Daza'),(4,'Federico','García Lorca'),(1,'Francisco','Olivero'),(10,'Heitor','Villalobos'),(22,'Heriberto','Rodríguez Hernández'),(3,'Humberto','García'),(8,'Isaac','Albéniz'),(9,'Joaquín','Turina'),(19,'José Luis','Rodríguez Núñez'),(20,'Juan Felipe','Rodríguez Castro'),(25,'Luciano','Berio'),(26,'Luigi','Nono'),(16,'Luis','de Narváez'),(24,'Luis Manuel','López Fuentes'),(27,'Manuel','Ponce'),(11,'Maurice','Ravel'),(12,'Nadia','Boulanger'),(17,'Nadie','de los Santos'),(6,'Zheng','Zhao');
/*!40000 ALTER TABLE `defendant_names` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `defendants_events`
--

LOCK TABLES `defendants_events` WRITE;
/*!40000 ALTER TABLE `defendants_events` DISABLE KEYS */;
INSERT INTO `defendants_events` VALUES (9,23),(10,23),(11,23);
/*!40000 ALTER TABLE `defendants_events` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `defendants_requests`
--

LOCK TABLES `defendants_requests` WRITE;
/*!40000 ALTER TABLE `defendants_requests` DISABLE KEYS */;
INSERT INTO `defendants_requests` VALUES (5,2),(5,4),(5,6),(5,8),(23,1),(23,3),(23,5),(23,7);
/*!40000 ALTER TABLE `defendants_requests` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_categories`
--

LOCK TABLES `event_categories` WRITE;
/*!40000 ALTER TABLE `event_categories` DISABLE KEYS */;
INSERT INTO `event_categories` VALUES (1,'in'),(3,'not applicable'),(2,'out');
/*!40000 ALTER TABLE `event_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_emails`
--

DROP TABLE IF EXISTS `event_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_emails` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` mediumint(8) unsigned DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `recipient_id` smallint(5) unsigned DEFAULT NULL,
  `email` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comments` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `event_emails_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL,
  CONSTRAINT `event_emails_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_emails`
--

LOCK TABLES `event_emails` WRITE;
/*!40000 ALTER TABLE `event_emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_emails` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_types`
--

LOCK TABLES `event_types` WRITE;
/*!40000 ALTER TABLE `event_types` DISABLE KEYS */;
INSERT INTO `event_types` VALUES (1,1,'conference',''),(2,2,'atty/client interview',''),(3,1,'sentence',''),(4,1,'plea',''),(5,1,'presentment',''),(6,1,'arraignment',''),(7,2,'probation PSI interview',''),(8,1,'trial',''),(9,1,'bail hearing',''),(10,1,'suppression hearing',''),(11,3,'document translation',''),(12,2,'pretrial services intake',''),(13,2,'bond',''),(15,1,'competency hearing',''),(18,1,'Curcio hearing',''),(19,1,'deferred prosecution',''),(20,1,'detention hearing',''),(23,1,'Fatico',''),(24,1,'Habeas',''),(26,1,'identity hearing',''),(30,1,'motions/oral argument',''),(36,1,'pro se (civil)',''),(50,1,'vop hearing',''),(51,1,'vsr hearing',''),(52,1,'appt/subst of counsel',''),(53,2,'probation supervision interview',''),(54,2,'PTS supervision interview','');
/*!40000 ALTER TABLE `event_types` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (9,1,NULL,NULL,28,'2019-08-12','10:45:00','11:22:00','2019-MAG-4321','','','2019-08-12 10:36:00','2019-08-12 11:23:00',12,1,1,12,NULL,1,'2019-08-12','10:35:00',0),(10,1,NULL,21,25,'2019-08-12','11:45:00','12:15:00','2019-MAG-4321','','','2019-08-12 11:32:00','2019-08-12 12:16:00',2,1,1,NULL,NULL,1,'2019-08-12','11:29:00',0),(11,1,NULL,NULL,10,'2019-08-12','13:20:00','13:26:00','2019-MAG-4321','','','2019-08-12 13:18:00','2019-08-12 13:27:00',5,1,1,13,NULL,1,'2019-08-12','12:53:00',0);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `foo`
--

DROP TABLE IF EXISTS `foo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `foo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `foo`
--

LOCK TABLES `foo` WRITE;
/*!40000 ALTER TABLE `foo` DISABLE KEYS */;
/*!40000 ALTER TABLE `foo` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hats`
--

LOCK TABLES `hats` WRITE;
/*!40000 ALTER TABLE `hats` DISABLE KEYS */;
INSERT INTO `hats` VALUES (1,2,'staff court interpreter',0,0),(2,2,'Interpreters Office staff',0,0),(3,NULL,'contract court interpreter',0,0),(4,NULL,'defense attorney',2,0),(5,NULL,'AUSA',2,0),(6,1,'Courtroom Deputy',0,1),(7,1,'Law Clerk',0,1),(8,1,'USPO',0,0),(9,1,'Pretrial Services Officer',0,0),(10,NULL,'paralegal',2,0),(11,NULL,'staff, US Attorneys Office',2,0),(12,NULL,'Pretrial',1,0),(13,NULL,'Magistrates',1,0),(14,NULL,'Judge',0,0);
/*!40000 ALTER TABLE `hats` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (1,'New Year\'s Day'),(2,'Martin Luther King Day'),(3,'Lincoln\'s Birthday'),(4,'President\'s Day'),(5,'Memorial Day'),(6,'Independence Day'),(7,'Labor Day'),(8,'Columbus Day'),(9,'Veterans\' Day'),(10,'Thanksgiving'),(11,'Christmas'),(12,'Election Day');
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `interpreters`
--

LOCK TABLES `interpreters` WRITE;
/*!40000 ALTER TABLE `interpreters` DISABLE KEYS */;
INSERT INTO `interpreters` VALUES (14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(18,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(19,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(20,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(21,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(22,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(23,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(24,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(25,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(26,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(54,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States'),(55,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States'),(56,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States'),(57,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States'),(58,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(59,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(60,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(61,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(62,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(63,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(64,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(65,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(66,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(67,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(68,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(69,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(70,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(71,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(72,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(73,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(74,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(75,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(76,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(77,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(78,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','');
/*!40000 ALTER TABLE `interpreters` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `interpreters_events`
--

LOCK TABLES `interpreters_events` WRITE;
/*!40000 ALTER TABLE `interpreters_events` DISABLE KEYS */;
INSERT INTO `interpreters_events` VALUES (16,11,'2019-08-12 10:36:00',1),(17,9,'2019-08-12 10:36:00',1),(17,10,'2019-08-12 10:36:00',1);
/*!40000 ALTER TABLE `interpreters_events` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `interpreters_languages`
--

LOCK TABLES `interpreters_languages` WRITE;
/*!40000 ALTER TABLE `interpreters_languages` DISABLE KEYS */;
INSERT INTO `interpreters_languages` VALUES (18,1,1),(19,1,1),(20,1,1),(21,1,1),(22,1,1),(23,1,1),(24,1,1),(26,1,1),(54,1,1),(14,4,2),(18,24,2),(25,5,2),(55,11,2),(58,8,2),(59,27,2),(60,7,2),(61,32,2),(62,9,2),(63,10,2),(64,30,2),(65,20,2),(66,12,2),(67,13,2),(68,25,2),(69,23,2),(70,26,2),(71,14,2),(72,33,2),(73,18,2),(74,28,2),(75,16,2),(76,15,2),(77,22,2),(78,17,2),(15,21,3),(16,31,3),(17,2,3),(17,3,3),(17,19,3),(21,31,3),(56,29,3),(57,6,3);
/*!40000 ALTER TABLE `interpreters_languages` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `judge_flavors`
--

LOCK TABLES `judge_flavors` WRITE;
/*!40000 ALTER TABLE `judge_flavors` DISABLE KEYS */;
INSERT INTO `judge_flavors` VALUES (1,'USDJ',0),(2,'USMJ',5),(3,'USBJ',10);
/*!40000 ALTER TABLE `judge_flavors` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `judges`
--

LOCK TABLES `judges` WRITE;
/*!40000 ALTER TABLE `judges` DISABLE KEYS */;
INSERT INTO `judges` VALUES (1,19,1),(2,20,1),(3,21,1),(4,22,1),(5,4,1),(6,8,1),(7,13,1),(8,14,1),(9,15,1),(10,24,1),(11,28,1),(12,16,1),(13,18,1);
/*!40000 ALTER TABLE `judges` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `language_credentials`
--

LOCK TABLES `language_credentials` WRITE;
/*!40000 ALTER TABLE `language_credentials` DISABLE KEYS */;
INSERT INTO `language_credentials` VALUES (1,'AO','AOUSC-certified','Certified by the Administrative Office of the US Courts. Also known as federal certification, the certification exam has been administered for only three languages: Spanish, Navajo, and Haitian Creole. The exam is no longer offered for any language other than Spanish.'),(2,'PQ','Professionally Qualified','A designation created and defined by the AOUSC for languages having no federal court certification program.'),(3,'LS','Language-skilled','Created and defined by the AOUSC, LS is a level beneath PQ and is the default in the absence of PQ or AO');
/*!40000 ALTER TABLE `language_credentials` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'Spanish',''),(2,'Foochow',''),(3,'Mandarin',''),(4,'Russian',''),(5,'Arabic',''),(6,'Armenian',''),(7,'Burmese',''),(8,'Dutch',''),(9,'Fulani',''),(10,'Ga',''),(11,'Latvian',''),(12,'Lithuanian',''),(13,'Mandingo',''),(14,'Sinhala',''),(15,'Ukrainian',''),(16,'Twi',''),(17,'Yoruba',''),(18,'Taishanese',''),(19,'Cantonese',''),(20,'Korean',''),(21,'French',''),(22,'Urdu',''),(23,'Punjabi',''),(24,'Hebrew',''),(25,'Pashto',''),(26,'Romanian',''),(27,'Bengali',''),(28,'Turkish',''),(29,'Albanian',''),(30,'Georgian',''),(31,'Portuguese',''),(32,'Farsi',''),(33,'Somali','');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location_types`
--

LOCK TABLES `location_types` WRITE;
/*!40000 ALTER TABLE `location_types` DISABLE KEYS */;
INSERT INTO `location_types` VALUES (1,'courtroom',''),(2,'jail',''),(3,'holding cell',''),(4,'US Probation office',''),(5,'Pretrial Services office',''),(6,'interpreters office',''),(7,'courthouse',''),(8,'public area','');
/*!40000 ALTER TABLE `location_types` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
INSERT INTO `locations` VALUES (1,7,NULL,'Some Courthouse','',1),(2,7,NULL,'Other Courthouse','',1),(3,2,NULL,'Some Detention Center','',1),(4,1,1,'101','',1),(5,1,1,'102','',1),(6,1,1,'103','',1),(7,1,1,'104','',1),(8,1,1,'201','',1),(9,1,1,'202','',1),(10,1,1,'510','',1),(11,1,1,'203','',1),(12,1,1,'204','',1),(13,1,1,'403','',1),(14,1,1,'504','',1),(15,1,1,'603','',1),(16,1,1,'704','',1),(17,1,1,'803','',1),(18,1,1,'804','',1),(19,1,2,'2A','',1),(20,1,2,'2B','',1),(21,1,2,'2C','',1),(22,1,2,'2D','',1),(23,1,2,'4A','',1),(24,1,2,'4B','',1),(25,1,2,'4C','',1),(26,1,2,'4D','',1),(27,1,2,'5A','',1),(28,1,2,'5B','',1),(29,1,2,'5C','',1),(30,1,2,'5D','',1),(31,3,1,'the holding cell','',1),(32,6,1,'your Interpreters Office','',1),(33,4,2,'your Probation Office','',1),(34,5,1,'Pretrial Services','',1);
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `people`
--

LOCK TABLES `people` WRITE;
/*!40000 ALTER TABLE `people` DISABLE KEYS */;
INSERT INTO `people` VALUES (1,14,NULL,'Harshbarger','Thaddeus','Q.','','',1,'judge'),(2,14,NULL,'Bludgeon','Vernon','T.','','',1,'judge'),(3,14,NULL,'Judicious','Jane','T.','','',1,'judge'),(4,14,NULL,'Wiseburger','Wilma','T.','','',1,'judge'),(5,14,NULL,'Dorkendoofer','William','D.','','',1,'judge'),(6,14,NULL,'Boinkleheimer','Ronda','B.','','',1,'judge'),(7,14,NULL,'Corcoran','Lawrence','B.','','',1,'judge'),(8,14,NULL,'Hunrichs','Lisa','M.','','',1,'judge'),(9,14,NULL,'Peña','Petronila','','','',1,'judge'),(10,14,NULL,'Marx','Selma','K.','','',1,'judge'),(11,14,NULL,'Borodin','Alexander','S.','','',1,'judge'),(12,14,NULL,'McRae','Carmen','T.','','',1,'judge'),(13,14,NULL,'Davis','Miles','T.','','',1,'judge'),(14,3,'russian_interpreter@example.org','Pavlova','Yana','','','917 123-4567',1,'interpreter'),(15,3,'french_interpreter@example.org','Françoise','Marie Louise','','','123 123-4567',1,'interpreter'),(16,3,'portuguese_interpreter@example.org','Silva','Jose Luiz','','','111 222-3210',1,'interpreter'),(17,3,'foochow_interpreter@example.org','Lau','Lily','','','222 333-3210',1,'interpreter'),(18,3,'hebrew_interpreter@example.org','Mintzenberger','David','','','666 666-4321',1,'interpreter'),(19,3,'spanish_interpreter_1@example.org','Hispanófona','Carmen','','','666 666-4321',1,'interpreter'),(20,3,'spanish_interpreter_2@example.org','Intérprete','Cristina','','','666 321-4321',1,'interpreter'),(21,3,'spanish_interpreter_3@example.org','Granados','Enrique','','','789 555-4321',1,'interpreter'),(22,3,'spanish_interpreter_4@example.org','Nadal','Rafael','','','012 123-4321',1,'interpreter'),(23,3,'spanish_interpreter_5@example.org','López','Jennifer','','','444 555-4321',1,'interpreter'),(24,3,'spanish_interpreter_6@example.org','del Potro','Juan Martín','','','444 555-7890',1,'interpreter'),(25,3,'arabic_interpreter_1@example.org','Codouni','Marwan','','','321 321-4321',1,'interpreter'),(26,1,'sonia_staffinterp@some.uscourts.gov','Staffinterp','Sonia','','','212 840-0084',1,'interpreter'),(27,4,'mister_lawyer@lawfirm.org','Litigious','Henry','','','222 333-6666',1,'person'),(28,4,'edelbaum@philslawfirm.com','Edelbaum','Phillip','','','222 333-6666',1,'person'),(29,4,'vergara@somelawfirm.com','Vergara','Elizabeth','','','234 234-2345',1,'person'),(30,4,'roland@defenselawfirm.com','Thau','Roland','','','234 234-2345',1,'person'),(31,4,'bricker@herlawpractice.com','Bricker','Carrie','','','234 666-2345',1,'person'),(32,8,'lyvia@some.uspd.uscourts.gov','Ramos','Lyvia','','','212 666-2345',1,'person'),(33,8,'sternberg@some.uspd.uscourts.gov','Sternberg','Brian','','','212 777-2345',1,'person'),(34,8,'perez@some.uspd.uscourts.gov','Pérez','Graciela','','','212 333-3333',1,'person'),(35,8,'susan_somebody@some.uscourts.gov','Somebody','Susan','','','212 666-2345',1,'person'),(36,7,'mylie_schwartzberg@some.uscourts.gov','Schwartzberg','Mylie','','','212 666-9876',1,'person'),(37,7,'amy_hartford@some.uscourts.gov','Hartford','Amy','','','212 666-8899',1,'person'),(38,6,'esmeralda_rojas@some.uscourts.gov','Rojas','Esmeralda','','','212 666-8899',1,'person'),(39,6,'zack_zimmer@some.uscourts.gov','Zimmer','Zack','','','212 666-1324',1,'person'),(40,6,'ting_ho@some.uscourts.gov','Ho','Ting','','','212 666-4235',1,'person'),(41,6,'wes_montgomery@some.uscourts.gov','Montgomery','Wes','','','212 666-7892',1,'person'),(42,5,'jane.prosecutor@some.usdoj.gov','Jackson','Jane','','','212 666-6637',1,'person'),(43,5,'millard.fillmore@some.usdoj.gov','Fillmore','Millard','','','212 666-6600',1,'person'),(44,5,'martin.vanburen@some.usdoj.gov','Van Buren','Martin','','','212 666-6601',1,'person'),(45,6,'hoyt_gackersley@some.uscourts.gov','Gackersly','Hoyt','','','',1,'person'),(46,6,'deedee_bridgewater@some.uscourts.gov','Bridgewater','Deedee','','','',1,'person'),(47,7,'carmen_lundy@some.uscourts.gov','Lundy','Carmen','','','',1,'person'),(48,6,'serena_williams@some.uscourts.gov','Williams','Serena','','','',1,'person'),(49,7,'charlie_parker@nysd.uscourts.gov','Parker','Charles','','','',1,'person'),(50,7,'allen_ginsberg@some.uscourts.gov','Ginsberg','Allen','','','',1,'person'),(51,6,'iris_murdoch@some.uscourts.gov','Murdoch','Iris','','','',1,'person'),(52,6,'herbie_hancock@some.uscourts.gov','Hancock','Herbie','','','',1,'person'),(53,6,'ronald_mcdonald@some.uscourts.gov','McDonald','Ronald','','','',1,'person'),(54,1,'angel_romero@some.uscourts.gov','Romero','Ángel','','','',1,'interpreter'),(55,3,'latvian_interpreter@somewhere.com','Porziņģis','Kristaps','','','',1,'interpreter'),(56,3,'evner@example.com','Hoxha','Enver','','','',1,'interpreter'),(57,3,'artur@other.example.org','Aleksanyan','Artur','','','',1,'interpreter'),(58,3,'van_eyck@awesomepainters.com','van Eyeck','Jan','','','',1,'interpreter'),(59,3,'haimanti@rakshit.com','Rakshit','Haimanti','','','',1,'interpreter'),(60,3,'aye.kyi@example.org','Aye','Kyi','','','',1,'interpreter'),(61,3,'habibollah@example.org','Qa\'ani','Habibollah','','','',1,'interpreter'),(62,3,'usman@example.org','dan Fodio','Usman','','','',1,'interpreter'),(63,3,'ayi-kushi@example.org','Kushi','Ayi','','','',1,'interpreter'),(64,3,'giorgi-avalishvili@example.org','Avalishvili','Giorgi','','','',1,'interpreter'),(65,3,'shim-eun-jin@example.org','Eun-Jin','Shim','','','',1,'interpreter'),(66,3,'dainius-adomaitis@example.org','Adomaitis','Dainius','','','',1,'interpreter'),(67,3,'sitta-umaru turay@example.org','Umaru Turay','Sitta','','','',1,'interpreter'),(68,3,'mirwais-hotak@example.org','Hotak','Mirwais','','','',1,'interpreter'),(69,3,'nimrat-khaira@example.org','Khaira','Nimrat','','','',1,'interpreter'),(70,3,'nadia@example.org','Comăneci','Nadia','','','',1,'interpreter'),(71,3,'prasanna-vithanaga@example.org','Vithanaga','Prasanna','','','',1,'interpreter'),(72,3,'ali amaan-nuruddin@example.org','Nuruddin','Ali Amaan','','','',1,'interpreter'),(73,3,'wan-chi-keung@example.org','Chi-keung','Wan','','','',1,'interpreter'),(74,3,'duygu-asena@example.org','Asena','Duygu','','','',1,'interpreter'),(75,3,'nana-wiafe-akenten@example.org','Wiafe-Akenten','Nana','','','',1,'interpreter'),(76,3,'roman-virastyuk@example.org','Virastyuk','Roman','','','',1,'interpreter'),(77,3,'rahman-abbas@example.org','Abbas','Rahman','','','',1,'interpreter'),(78,3,'lamidi-fakeye@example.org','Fakeye','Lamidi','','','',1,'interpreter'),(79,2,'katylnn@some.uscourts.gov','González','Katelynn','','','',1,'person');
/*!40000 ALTER TABLE `people` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requests`
--

LOCK TABLES `requests` WRITE;
/*!40000 ALTER TABLE `requests` DISABLE KEYS */;
INSERT INTO `requests` VALUES (1,'2019-09-16','10:00:00',5,NULL,1,1,'2019-CR-0123',4,31,'2019-08-19 10:32:05','2019-08-19 10:32:05',8,'',NULL,1,0,''),(2,'2019-09-23','15:00:00',2,NULL,6,1,'2019-CR-0234',13,29,'2019-08-19 10:32:05','2019-08-19 10:32:05',6,'',NULL,1,0,''),(3,'2019-10-01','10:00:00',5,NULL,1,1,'2019-CR-0123',4,38,'2019-09-03 09:43:43','2019-09-03 09:43:43',8,'',NULL,1,0,''),(4,'2019-10-08','15:00:00',2,NULL,6,1,'2019-CR-0234',13,36,'2019-09-03 09:43:43','2019-09-03 09:43:43',6,'',NULL,1,0,''),(5,'2019-10-01','10:00:00',5,NULL,1,1,'2019-CR-0123',4,38,'2019-09-03 09:44:04','2019-09-03 09:44:04',8,'',NULL,1,0,''),(6,'2019-10-08','15:00:00',2,NULL,6,1,'2019-CR-0234',13,36,'2019-09-03 09:44:04','2019-09-03 09:44:04',6,'',NULL,1,0,''),(7,'2019-10-01','10:00:00',5,NULL,1,1,'2019-CR-0123',4,38,'2019-09-03 10:26:11','2019-09-03 10:26:11',8,'',NULL,1,0,''),(8,'2019-10-08','15:00:00',2,NULL,6,1,'2019-CR-0234',13,36,'2019-09-03 10:26:11','2019-09-03 10:26:11',6,'',NULL,1,0,'');
/*!40000 ALTER TABLE `requests` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'submitter',''),(2,'manager',''),(3,'administrator',''),(4,'staff','');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,26,3,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','admin',1,'2019-09-04 11:03:54','2019-09-03 10:26:09'),(2,32,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','lyvia',1,NULL,'2019-09-03 10:26:09'),(3,33,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','sternberg',1,NULL,'2019-09-03 10:26:10'),(4,34,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','graciela',1,NULL,'2019-09-03 10:26:10'),(5,35,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','susan',1,NULL,'2019-09-03 10:26:10'),(6,36,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','mylie',1,NULL,'2019-09-03 10:26:10'),(7,37,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','amy',1,NULL,'2019-09-03 10:26:10'),(8,38,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','esmeralda',1,NULL,'2019-09-03 10:26:10'),(9,39,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','zack',1,NULL,'2019-09-03 10:26:10'),(10,40,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','ting',1,NULL,'2019-09-03 10:26:10'),(11,41,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','wes',1,NULL,'2019-09-03 10:26:10'),(12,45,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','hoyt',1,NULL,'2019-09-03 13:51:30'),(13,46,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','deedee',1,NULL,'2019-09-03 13:52:24'),(14,47,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','carmen',1,NULL,'2019-09-03 13:53:33'),(15,48,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','serena',1,NULL,'2019-09-03 13:55:15'),(16,49,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','charlie',1,NULL,'2019-09-03 13:56:14'),(17,50,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','allen',1,NULL,'2019-09-03 14:26:57'),(18,51,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','iris',1,NULL,'2019-09-03 14:30:21'),(19,52,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','herbie',1,NULL,'2019-09-03 14:31:55'),(20,53,1,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','ronald',1,NULL,'2019-09-03 14:33:14'),(21,54,2,'$2y$10$G7yNKmteeId2JO0eK3vb/e/jdScyr.bqVthCj8UEwVuIhYmU/B.Pq','angel',1,NULL,'2019-09-04 11:09:03'),(22,79,2,'$2y$10$/IJoq6YeCfIqcE95RBlvd.z.tSkbwQf..acnCj.XttGu5QKp6ShUK','katelynn',1,NULL,'2019-09-04 13:50:10');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `verification_tokens`
--

LOCK TABLES `verification_tokens` WRITE;
/*!40000 ALTER TABLE `verification_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `verification_tokens` ENABLE KEYS */;
UNLOCK TABLES;

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
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
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

-- Dump completed on 2019-09-04 13:50:27
