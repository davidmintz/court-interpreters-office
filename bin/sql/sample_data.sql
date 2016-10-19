-- MySQL dump 10.13  Distrib 5.7.15, for Linux (x86_64)
--
-- Host: localhost    Database: office
-- ------------------------------------------------------
-- Server version	5.7.13-0ubuntu0.16.04.2

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
-- Dumping data for table `anonymous_hats`
--

LOCK TABLES `anonymous_hats` WRITE;
/*!40000 ALTER TABLE `anonymous_hats` DISABLE KEYS */;
INSERT INTO `anonymous_hats` VALUES (1,'Magistrate'),(2,'Pretrial');
/*!40000 ALTER TABLE `anonymous_hats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cancellation_reasons`
--

LOCK TABLES `cancellation_reasons` WRITE;
/*!40000 ALTER TABLE `cancellation_reasons` DISABLE KEYS */;
INSERT INTO `cancellation_reasons` VALUES (2,'adjourned without notice'),(1,'defendant not produced'),(5,'forçe majeure'),(6,'other'),(3,'party did not appear'),(4,'unknown');
/*!40000 ALTER TABLE `cancellation_reasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `defendant_names`
--

LOCK TABLES `defendant_names` WRITE;
/*!40000 ALTER TABLE `defendant_names` DISABLE KEYS */;
/*!40000 ALTER TABLE `defendant_names` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `event_categories`
--

LOCK TABLES `event_categories` WRITE;
/*!40000 ALTER TABLE `event_categories` DISABLE KEYS */;
INSERT INTO `event_categories` VALUES (1,'in'),(3,'not applicable'),(2,'out');
/*!40000 ALTER TABLE `event_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `event_types`
--

LOCK TABLES `event_types` WRITE;
/*!40000 ALTER TABLE `event_types` DISABLE KEYS */;
INSERT INTO `event_types` VALUES (1,1,'pretrial conference',''),(2,1,'sentence',''),(3,2,'attorney/client interview',''),(4,1,'plea',''),(5,1,'presentment',''),(6,1,'arraignment',''),(7,2,'pretrial services',''),(8,2,'probation PSI interview','');
/*!40000 ALTER TABLE `event_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `hats`
--

LOCK TABLES `hats` WRITE;
/*!40000 ALTER TABLE `hats` DISABLE KEYS */;
INSERT INTO `hats` VALUES (1,'staff Court Interpreter',0),(2,'staff, Interpreters Office',0),(3,'contract court interpreter',0),(4,'defense attorney',1),(5,'AUSA',0),(6,'Courtroom Deputy',0),(7,'Law Clerk',0),(8,'USPO',0),(9,'Pretrial Services Officer',0),(10,'paralegal',0),(11,'staff, US Attorneys Office',0),(12,'Pretrial',1),(13,'Magistrates',1);
/*!40000 ALTER TABLE `hats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `interpreters`
--

LOCK TABLES `interpreters` WRITE;
/*!40000 ALTER TABLE `interpreters` DISABLE KEYS */;
/*!40000 ALTER TABLE `interpreters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `interpreters_languages`
--

LOCK TABLES `interpreters_languages` WRITE;
/*!40000 ALTER TABLE `interpreters_languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `interpreters_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `judge_flavors`
--

LOCK TABLES `judge_flavors` WRITE;
/*!40000 ALTER TABLE `judge_flavors` DISABLE KEYS */;
INSERT INTO `judge_flavors` VALUES (1,'USDJ'),(2,'USMJ');
/*!40000 ALTER TABLE `judge_flavors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `judges`
--

LOCK TABLES `judges` WRITE;
/*!40000 ALTER TABLE `judges` DISABLE KEYS */;
/*!40000 ALTER TABLE `judges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'Spanish',''),(2,'Russian',''),(3,'French',''),(4,'Foochow',''),(5,'Arabic','');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `location_types`
--

LOCK TABLES `location_types` WRITE;
/*!40000 ALTER TABLE `location_types` DISABLE KEYS */;
INSERT INTO `location_types` VALUES (1,'courtroom',''),(2,'jail',''),(3,'holding cell',''),(4,'US Probation office',''),(5,'Pretrial Services office',''),(6,'interpreters office',''),(7,'courthouse',''),(8,'public area','');
/*!40000 ALTER TABLE `location_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
INSERT INTO `locations` VALUES (1,7,NULL,'500 Pearl',''),(2,7,NULL,'40 Foley',''),(3,2,NULL,'MCC',''),(4,2,NULL,'MDC',''),(5,4,1,'7th floor',''),(7,5,1,'5th floor',''),(8,3,1,'4th floor',''),(9,1,2,'618','');
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `people`
--

LOCK TABLES `people` WRITE;
/*!40000 ALTER TABLE `people` DISABLE KEYS */;
/*!40000 ALTER TABLE `people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-10-19 16:05:21
