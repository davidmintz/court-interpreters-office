INSERT INTO `roles` VALUES (1,'submitter',''),(2,'manager',''),(3,'administrator',''),(4,'staff','');
INSERT INTO `hats` VALUES (1,2,'staff court interpreter',0,0),(2,2,'Interpreters Office staff',0,0),(3,NULL,'contract court interpreter',0,0),(4,NULL,'defense attorney',2,0),(5,NULL,'AUSA',2,0),(6,1,'Courtroom Deputy',0,1),(7,1,'Law Clerk',0,1),(8,1,'USPO',0,0),(9,1,'Pretrial Services Officer',0,0),(10,NULL,'paralegal',2,0),(11,NULL,'staff, US Attorneys Office',2,0),(12,NULL,'Pretrial',1,0),(13,NULL,'Magistrates',1,0),(14,NULL,'Judge',0,0);
INSERT INTO `location_types` VALUES (1,'courtroom',''),(2,'jail',''),(3,'holding cell',''),(4,'US Probation office',''),(5,'Pretrial Services office',''),(6,'interpreters office',''),(7,'courthouse',''),(8,'public area','');
INSERT INTO `event_categories` VALUES (1,'in'),(3,'not applicable'),(2,'out');
INSERT INTO `holidays` VALUES (1,'New Year\'s Day'),(2,'Martin Luther King Day'),(3,'Lincoln\'s Birthday'),(4,'President\'s Day'),(5,'Memorial Day'),(6,'Independence Day'),(7,'Labor Day'),(8,'Columbus Day'),(9,'Veterans\' Day'),(10,'Thanksgiving'),(11,'Christmas'),(12,'Election Day');
INSERT INTO `cancellation_reasons` VALUES (3,'belatedly adjourned'),(1,'defendant not produced'),(5,'forçe majeure'),(2,'no interpreter needed'),(7,'other'),(4,'party did not appear'),(6,'reason unknown');
INSERT INTO `judge_flavors` VALUES (1,'USDJ',0),(2,'USMJ',5),(3,'USBJ',10);
INSERT INTO `anonymous_judges` VALUES (2,'(not applicable)',NULL),(3,'(unknown)',NULL),(1,'magistrate',NULL);
-- MySQL dump 10.13  Distrib 5.7.25, for Linux (x86_64)
--
-- Host: localhost    Database: office
-- ------------------------------------------------------
-- Server version	5.7.25-0ubuntu0.18.04.2

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
-- Dumping data for table `language_credentials`
--

LOCK TABLES `language_credentials` WRITE;
/*!40000 ALTER TABLE `language_credentials` DISABLE KEYS */;
INSERT INTO `language_credentials` VALUES (1,'AO','AOUSC-certified','Certified by the Administrative Office of the US Courts. Also known as federal certification, the certification exam has been administered for only three languages: Spanish, Navajo, and Haitian Creole. The exam is no longer offered for any language other than Spanish.'),(2,'PQ','Professionally Qualified','A designation created and defined by the AOUSC for languages having no federal court certification program.'),(3,'LS','Language-skilled','Created and defined by the AOUSC, LS is a level beneath PQ and is the default in the absence of PQ or AO');
/*!40000 ALTER TABLE `language_credentials` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-02-27 11:20:30
