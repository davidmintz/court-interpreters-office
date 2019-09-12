/* clear it out */
SET foreign_key_checks = 0;
TRUNCATE TABLE languages;
TRUNCATE TABLE locations;
TRUNCATE TABLE event_types;
TRUNCATE TABLE judges;
TRUNCATE TABLE interpreters;
TRUNCATE TABLE people;
TRUNCATE TABLE defendant_names;
TRUNCATE TABLE interpreters_languages;
TRUNCATE TABLE clerks_judges;
TRUNCATE TABLE users;
SET foreign_key_checks = 1;

-- ALTER TABLE defendant_names ADD COLUMN  `language_hint` varchar(100) COLLATE utf8_unicode_ci DEFAULT 'Spanish';
CREATE TABLE `tmp_event_map` (
 `office_id` mediumint(8) unsigned NOT NULL,
 `dummy_id` mediumint(8) unsigned NOT NULL,
 UNIQUE KEY `idx` (`office_id`,`dummy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'Spanish',''),(2,'Foochow',''),(3,'Mandarin',''),(4,'Russian',''),(5,'Arabic',''),(6,'Armenian',''),(7,'Burmese',''),(8,'Dutch',''),(9,'Fulani',''),(10,'Ga',''),(11,'Latvian',''),(12,'Lithuanian',''),(13,'Mandingo',''),(14,'Sinhala',''),(15,'Ukrainian',''),(16,'Twi',''),(17,'Yoruba',''),(18,'Taishanese',''),(19,'Cantonese',''),(20,'Korean',''),(21,'French',''),(22,'Urdu',''),(23,'Punjabi',''),(24,'Hebrew',''),(25,'Pashto',''),(26,'Romanian',''),(27,'Bengali',''),(28,'Turkish',''),(29,'Albanian',''),(30,'Georgian',''),(31,'Portuguese',''),(32,'Farsi',''),(33,'Somali',''),(34,'Python alert(\"bad shit\")','');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-11 19:53:04
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
-- Dumping data for table `locations`
--
-- WHERE:  parent_id IS NULL

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
-- Dumping data for table `locations`
--
-- WHERE:  parent_location_id IS NULL

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
INSERT INTO `locations` VALUES (1,7,NULL,'Some Courthouse','',1),(2,7,NULL,'Other Courthouse','',1),(3,2,NULL,'Some Detention Center','',1),(35,7,NULL,'alert(document.cookie); Shithole','alert(\"evil!\");',1);
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-11 19:53:46
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
-- Dumping data for table `locations`
--
-- WHERE:  parent_location_id IS NOT NULL

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
INSERT INTO `locations` VALUES (4,1,1,'101','',1),(5,1,1,'102','',1),(6,1,1,'103','',1),(7,1,1,'104','',1),(8,1,1,'201','',1),(9,1,1,'202','',1),(10,1,1,'510','',1),(11,1,1,'203','',1),(12,1,1,'204','',1),(13,1,1,'403','',1),(14,1,1,'504','',1),(15,1,1,'603','',1),(16,1,1,'704','',1),(17,1,1,'803','',1),(18,1,1,'804','',1),(19,1,2,'2A','',1),(20,1,2,'2B','',1),(21,1,2,'2C','',1),(22,1,2,'2D','',1),(23,1,2,'4A','',1),(24,1,2,'4B','',1),(25,1,2,'4C','',1),(26,1,2,'4D','',1),(27,1,2,'5A','',1),(28,1,2,'5B','',1),(29,1,2,'5C','',1),(30,1,2,'5D','',1),(31,3,1,'the holding cell','',1),(32,6,1,'your Interpreters Office','',1),(33,4,2,'your Probation Office','',1),(34,5,1,'Pretrial Services','',1);
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-11 19:53:52
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
-- Dumping data for table `event_types`
--

LOCK TABLES `event_types` WRITE;
/*!40000 ALTER TABLE `event_types` DISABLE KEYS */;
INSERT INTO `event_types` VALUES (1,1,'conference',''),(2,2,'atty/client interview',''),(3,1,'sentence',''),(4,1,'plea',''),(5,1,'presentment',''),(6,1,'arraignment',''),(7,2,'probation PSI interview',''),(8,1,'trial',''),(9,1,'bail hearing',''),(10,1,'suppression hearing',''),(11,3,'document translation',''),(12,2,'pretrial services intake',''),(13,2,'bond',''),(15,1,'competency hearing',''),(18,1,'Curcio hearing',''),(19,1,'deferred prosecution',''),(20,1,'detention hearing',''),(23,1,'Fatico',''),(24,1,'Habeas',''),(26,1,'identity hearing',''),(30,1,'motions/oral argument',''),(36,1,'pro se (civil)',''),(50,1,'vop hearing',''),(51,1,'vsr hearing',''),(52,1,'appt/subst of counsel',''),(53,2,'probation supervision interview',''),(54,2,'PTS supervision interview','');
/*!40000 ALTER TABLE `event_types` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-11 19:54:27
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
-- Dumping data for table `people`
--

LOCK TABLES `people` WRITE;
/*!40000 ALTER TABLE `people` DISABLE KEYS */;
INSERT INTO `people` VALUES (1,14,NULL,'Harshbarger','Thaddeus','Q.','','',1,'judge'),(2,14,NULL,'Bludgeon','Vernon','T.','','',1,'judge'),(3,14,NULL,'Judicious','Jane','T.','','',1,'judge'),(4,14,NULL,'Wiseburger','Wilma','T.','','',1,'judge'),(5,14,NULL,'Dorkendoofer','William','D.','','',1,'judge'),(6,14,NULL,'Boinkleheimer','Ronda','B.','','',1,'judge'),(7,14,NULL,'Corcoran','Lawrence','B.','','',1,'judge'),(8,14,NULL,'Hunrichs','Lisa','M.','','',1,'judge'),(9,14,NULL,'Peña','Petronila','','','',1,'judge'),(10,14,NULL,'Marx','Selma','K.','','',1,'judge'),(11,14,NULL,'Borodin','Alexander','S.','','',1,'judge'),(12,14,NULL,'McRae','Carmen','T.','','',1,'judge'),(13,14,NULL,'Davis','Miles','T.','','',1,'judge'),(14,3,'russian_interpreter@example.org','Pavlova','Yana','','','917 123-4567',1,'interpreter'),(15,3,'french_interpreter@example.org','Françoise','Marie Louise','','','123 123-4567',1,'interpreter'),(16,3,'portuguese_interpreter@example.org','Silva','Jose Luiz','','','111 222-3210',1,'interpreter'),(17,3,'foochow_interpreter@example.org','Lau','Lily','','','222 333-3210',1,'interpreter'),(18,3,'hebrew_interpreter@example.org','Mintzenberger','David','','','666 666-4321',1,'interpreter'),(19,3,'spanish_interpreter_1@example.org','Hispanófona','Carmen','','','666 666-4321',1,'interpreter'),(20,3,'spanish_interpreter_2@example.org','Intérprete','Cristina','','','666 321-4321',1,'interpreter'),(21,3,'spanish_interpreter_3@example.org','Granados','Enrique','','','789 555-4321',1,'interpreter'),(22,3,'spanish_interpreter_4@example.org','Nadal','Rafael','','','012 123-4321',1,'interpreter'),(23,3,'spanish_interpreter_5@example.org','López','Jennifer','','','444 555-4321',1,'interpreter'),(24,3,'spanish_interpreter_6@example.org','del Potro','Juan Martín','','','444 555-7890',1,'interpreter'),(25,3,'arabic_interpreter_1@example.org','Codouni','Marwan','','','3213214321',1,'interpreter'),(26,1,'sonia_staffinterp@some.uscourts.gov','Staffinterp','Sonia','','','212 840-0084',1,'interpreter'),(27,4,'mister_lawyer@lawfirm.org','Litigious','Henry','','','222 333-6666',1,'person'),(28,4,'edelbaum@philslawfirm.com','Edelbaum','Phillip','','','222 333-6666',1,'person'),(29,4,'vergara@somelawfirm.com','Vergara','Elizabeth','','','234 234-2345',1,'person'),(30,4,'roland@defenselawfirm.com','Thau','Roland','','','234 234-2345',1,'person'),(31,4,'bricker@herlawpractice.com','Bricker','Carrie','','','234 666-2345',1,'person'),(32,8,'lydia@some.uspd.uscourts.gov','Ramos','Lydia','','','2126662345',1,'person'),(33,8,'sternberg@some.uspd.uscourts.gov','Sternberg','Brian','','','212 777-2345',1,'person'),(34,8,'perez@some.uspd.uscourts.gov','Pérez','Graciela','','','212 333-3333',1,'person'),(35,8,'susan_somebody@some.uscourts.gov','Somebody','Susan','','','212 666-2345',1,'person'),(36,7,'mylie_schwartzberg@some.uscourts.gov','Schwartzberg','Mylie','','','212 666-9876',1,'person'),(37,7,'amy_hartford@some.uscourts.gov','Hartford','Amy','','','212 666-8899',1,'person'),(38,6,'esmeralda_rojas@some.uscourts.gov','Rojas','Esmeralda','','','212 666-8899',1,'person'),(39,6,'zack_zimmer@some.uscourts.gov','Zimmer','Zack','','','212 666-1324',1,'person'),(40,6,'ting_ho@some.uscourts.gov','Ho','Ting','','','212 666-4235',1,'person'),(41,6,'wes_montgomery@some.uscourts.gov','Montgomery','Wes','','','212 666-7892',1,'person'),(42,5,'jane.prosecutor@some.usdoj.gov','Jackson','Jane','','','212 666-6637',1,'person'),(43,5,'millard.fillmore@some.usdoj.gov','Fillmore','Millard','','','212 666-6600',1,'person'),(44,5,'martin.vanburen@some.usdoj.gov','Van Buren','Martin','','','212 666-6601',1,'person'),(45,6,'hoyt_gackersley@some.uscourts.gov','Gackersly','Hoyt','','','',1,'person'),(46,6,'deedee_bridgewater@some.uscourts.gov','Bridgewater','Deedee','','','',1,'person'),(47,7,'carmen_lundy@some.uscourts.gov','Lundy','Carmen','','','',1,'person'),(48,6,'serena_williams@some.uscourts.gov','Williams','Serena','','','',1,'person'),(49,7,'charlie_parker@nysd.uscourts.gov','Parker','Charles','','','',1,'person'),(50,7,'allen_ginsberg@some.uscourts.gov','Ginsberg','Allen','','','',1,'person'),(51,6,'iris_murdoch@some.uscourts.gov','Murdoch','Iris','','','',1,'person'),(52,6,'herbie_hancock@some.uscourts.gov','Hancock','Herbie','','','',1,'person'),(53,6,'ronald_mcdonald@some.uscourts.gov','McDonald','Ronald','','','',1,'person'),(54,1,'angel_romero@some.uscourts.gov','Romero','Ángel','','','',1,'interpreter'),(55,3,'latvian_interpreter@somewhere.com','Porziņģis','Kristaps','','','',1,'interpreter'),(56,3,'evner@example.com','Hoxha','Enver','','','',1,'interpreter'),(57,3,'artur@other.example.org','Aleksanyan','Artur','','','',1,'interpreter'),(58,3,'van_eyck@awesomepainters.com','van Eyck','Jan','','','',1,'interpreter'),(59,3,'haimanti@rakshit.com','Rakshit','Haimanti','','','',1,'interpreter'),(60,3,'aye.kyi@example.org','Aye','Kyi','','','',1,'interpreter'),(61,3,'habibollah@example.org','Qa\'ani','Habibollah','','','',1,'interpreter'),(62,3,'usman@example.org','dan Fodio','Usman','','','',1,'interpreter'),(63,3,'ayi-kushi@example.org','Kushi','Ayi','','','',1,'interpreter'),(64,3,'giorgi-avalishvili@example.org','Avalishvili','Giorgi','','','',1,'interpreter'),(65,3,'shim-eun-jin@example.org','Eun-Jin','Shim','','','',1,'interpreter'),(66,3,'dainius-adomaitis@example.org','Adomaitis','Dainius','','','',1,'interpreter'),(67,3,'sitta-umaru turay@example.org','Umaru Turay','Sitta','','','',1,'interpreter'),(68,3,'mirwais-hotak@example.org','Hotak','Mirwais','','','',1,'interpreter'),(69,3,'nimrat-khaira@example.org','Khaira','Nimrat','','','',1,'interpreter'),(70,3,'nadia@example.org','Comăneci','Nadia','','','',1,'interpreter'),(71,3,'prasanna-vithanaga@example.org','Vithanaga','Prasanna','','','',1,'interpreter'),(72,3,'ali amaan-nuruddin@example.org','Nuruddin','Ali Amaan','','','',1,'interpreter'),(73,3,'wan-chi-keung@example.org','Chi-keung','Wan','','','',1,'interpreter'),(74,3,'duygu-asena@example.org','Asena','Duygu','','','',1,'interpreter'),(75,3,'nana-wiafe-akenten@example.org','Wiafe-Akenten','Nana','','','',1,'interpreter'),(76,3,'roman-virastyuk@example.org','Virastyuk','Roman','','','',1,'interpreter'),(77,3,'rahman-abbas@example.org','Abbas','Rahman','','','',1,'interpreter'),(78,3,'lamidi-fakeye@example.org','Fakeye','Lamidi','','','',1,'interpreter'),(79,2,'katylnn@some.uscourts.gov','González','Katelynn','','','',1,'person'),(80,9,'john_somebody@pts.uscourts.gov','Somebody','John','','','',1,'person'),(81,11,'milagros@not.usdoj.gov','Núñez','Milagros','','','',1,'person'),(82,3,'oleg@the.americans.com','Burov','Oleg','','','',1,'interpreter'),(86,3,'ahmad_almuhajir@example.com','al-Muhajir','Ahmad','','','',1,'interpreter'),(87,3,'mebo.nutsubidze@example.org','Nutsubidze','Mebo','','','',1,'interpreter'),(88,3,'chaim-zemach@example.org','Zemach','Chaim','','','',1,'interpreter'),(89,3,'gong-jin hyuk@example.org','Jin Hyuk','Gong','','','',1,'interpreter'),(90,3,'mark-zuckerberg@example.org','Zuckerberg','Mark','','','',1,'interpreter'),(91,3,'ahmad-shah baba@example.org','Shah Baba','Ahmad','','','',1,'interpreter'),(92,3,'sagopa-kajmer@example.org','Kajmer','Sagopa','','','',1,'interpreter'),(93,3,'anna-fedorova@example.org','Fedorova','Anna','','','',1,'interpreter'),(94,3,'nodar_k@example.org','Kumaritashvili','Nodar','','','',1,'interpreter'),(95,3,'arkady@example.org','Zotov','Arkady','','','',1,'interpreter'),(96,6,'nikki_minaj@some.uscourts.gov','Minaj','Nikki','','','',1,'person'),(97,7,'brad_pitt@some.uscourts.gov','Pitt','Brad','','','',1,'person'),(98,7,'angel_merkel@some.uscourts.gov','Merkel','Angela','','','',1,'person'),(99,7,'elvis_presley@some.uscourts.gov','Presley','Elvis','','','',1,'person'),(100,7,'kim_kardashian@some.uscourts.gov','Kardashian','Kim','','','',1,'person');
/*!40000 ALTER TABLE `people` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-11 19:54:46
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
-- Dumping data for table `judges`
--

LOCK TABLES `judges` WRITE;
/*!40000 ALTER TABLE `judges` DISABLE KEYS */;
INSERT INTO `judges` VALUES (1,19,1),(2,20,1),(3,21,1),(4,22,1),(5,4,1),(6,8,1),(7,13,1),(8,14,1),(9,15,1),(10,24,1),(11,28,1),(12,16,1),(13,18,1);
/*!40000 ALTER TABLE `judges` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-11 19:54:57
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
-- Dumping data for table `interpreters`
--

LOCK TABLES `interpreters` WRITE;
/*!40000 ALTER TABLE `interpreters` DISABLE KEYS */;
INSERT INTO `interpreters` VALUES (14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(18,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(19,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(20,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(21,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(22,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(23,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(24,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(25,'',NULL,NULL,NULL,NULL,NULL,NULL,'<script>alert(\"xss attack?\");</script>','','','','','',''),(26,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(54,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States'),(55,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States'),(56,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States'),(57,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States'),(58,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(59,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(60,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(61,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(62,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(63,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(64,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(65,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(66,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(67,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(68,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(69,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(70,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(71,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(72,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(73,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(74,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(75,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(76,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(77,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(78,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(82,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States'),(86,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(87,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(88,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(89,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(90,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(91,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(92,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(93,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','',''),(94,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States'),(95,'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States');
/*!40000 ALTER TABLE `interpreters` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-11 19:55:04
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
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,26,3,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','admin',1,'2019-09-11 14:05:18','2019-09-03 10:26:09'),(2,32,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','lyvia',1,NULL,'2019-09-03 10:26:09'),(3,33,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','sternberg',1,NULL,'2019-09-03 10:26:10'),(4,34,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','graciela',1,NULL,'2019-09-03 10:26:10'),(5,35,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','susan',1,NULL,'2019-09-03 10:26:10'),(6,36,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','mylie',1,NULL,'2019-09-03 10:26:10'),(7,37,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','amy',1,NULL,'2019-09-03 10:26:10'),(8,38,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','esmeralda',1,NULL,'2019-09-03 10:26:10'),(9,39,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','zack',1,NULL,'2019-09-03 10:26:10'),(10,40,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','ting',1,NULL,'2019-09-03 10:26:10'),(11,41,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','wes',1,'2019-09-11 13:56:29','2019-09-03 10:26:10'),(12,45,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','hoyt',1,NULL,'2019-09-03 13:51:30'),(13,46,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','deedee',1,NULL,'2019-09-03 13:52:24'),(14,47,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','carmen',1,'2019-09-11 11:43:12','2019-09-03 13:53:33'),(15,48,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','serena',1,NULL,'2019-09-03 13:55:15'),(16,49,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','charlie',1,NULL,'2019-09-03 13:56:14'),(17,50,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','allen',1,NULL,'2019-09-03 14:26:57'),(18,51,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','iris',1,NULL,'2019-09-03 14:30:21'),(19,52,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','herbie',1,'2019-09-11 10:05:07','2019-09-03 14:31:55'),(20,53,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','ronald',1,NULL,'2019-09-03 14:33:14'),(21,54,2,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','angel',1,NULL,'2019-09-04 11:09:03'),(22,79,2,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','katelynn',1,NULL,'2019-09-04 13:50:10'),(23,80,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','john',1,NULL,'2019-09-04 16:44:39'),(24,96,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','nikki',1,NULL,'2019-09-10 14:35:09'),(25,97,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','brad',1,NULL,'2019-09-10 14:36:04'),(26,98,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','angela',1,NULL,'2019-09-10 14:38:16'),(27,99,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','elvis',1,NULL,'2019-09-10 15:26:25'),(28,100,1,'$2y$10$Gedp/atzvbShJSSIegzuPuRMSpp5u6sf5YoArdC9qI6rFZciftxGG','kim',1,NULL,'2019-09-10 16:10:54');
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

-- Dump completed on 2019-09-11 19:55:23
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
-- Dumping data for table `interpreters_languages`
--

LOCK TABLES `interpreters_languages` WRITE;
/*!40000 ALTER TABLE `interpreters_languages` DISABLE KEYS */;
INSERT INTO `interpreters_languages` VALUES (18,1,1),(19,1,1),(20,1,1),(21,1,1),(22,1,1),(23,1,1),(24,1,1),(26,1,1),(54,1,1),(14,4,2),(18,24,2),(25,5,2),(55,11,2),(58,8,2),(59,27,2),(60,7,2),(61,32,2),(62,9,2),(63,10,2),(64,30,2),(65,20,2),(66,12,2),(67,13,2),(68,25,2),(69,23,2),(70,26,2),(71,14,2),(72,33,2),(73,18,2),(74,28,2),(75,16,2),(76,15,2),(77,22,2),(78,17,2),(82,4,2),(86,5,2),(87,30,2),(88,24,2),(89,20,2),(90,3,2),(91,25,2),(92,28,2),(93,15,2),(94,30,2),(95,4,2),(15,21,3),(16,31,3),(17,2,3),(17,3,3),(17,19,3),(21,31,3),(56,29,3),(57,6,3);
/*!40000 ALTER TABLE `interpreters_languages` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-11 19:55:34
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
-- Dumping data for table `clerks_judges`
--

LOCK TABLES `clerks_judges` WRITE;
/*!40000 ALTER TABLE `clerks_judges` DISABLE KEYS */;
INSERT INTO `clerks_judges` VALUES (6,2),(7,2),(8,5),(9,6),(10,4),(11,4),(12,7),(13,9),(14,10),(15,8),(16,13),(17,1),(18,11),(19,12),(20,3),(24,10),(25,6),(26,3),(27,3),(28,7);
/*!40000 ALTER TABLE `clerks_judges` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-11 19:55:46
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
-- Dumping data for table `defendant_names`
--

LOCK TABLES `defendant_names` WRITE;
/*!40000 ALTER TABLE `defendant_names` DISABLE KEYS */;
INSERT INTO `defendant_names` VALUES (1,'Francisco','Olivero','Spanish'),(2,'Erika','de los Ríos','Spanish'),(3,'Humberto','García','Spanish'),(4,'Federico','García Lorca','Spanish'),(5,'Alfredo','García','Spanish'),(6,'Zheng','Zhao','Mandarin, Foochow, Cantonese'),(7,'Boris','Badofsky','Russian'),(8,'Isaac','Albéniz','Spanish'),(9,'Joaquín','Turina','Spanish'),(10,'Heitor','Villalobos','Portuguese'),(11,'Maurice','Ravel','French'),(12,'Nadia','Boulanger','French'),(13,'Agustín','Barrios','Spanish'),(14,'Alguien','de los Santos','Spanish'),(15,'Esteban','Daza','Spanish'),(16,'Luis','de Narváez','Spanish'),(17,'Nadie','de los Santos','Spanish'),(18,'Alguno','de los Zetas','Spanish'),(19,'José Luis','Rodríguez Núñez','Spanish'),(20,'Juan Felipe','Rodríguez Castro','Spanish'),(21,'Carmen','Rodríguez Peña','Spanish'),(22,'Heriberto','Rodríguez Hernández','Spanish'),(23,'Carlos','Rodríguez Medina','Spanish'),(24,'Luis Manuel','López Fuentes','Spanish'),(25,'Luciano','Berio','Spanish, Italian'),(26,'Luigi','Nono','Spanish, Italian'),(27,'Manuel','Ponce','Spanish'),(28,'David','Mintzovski','Russian'),(29,'Nina','Krivola','Russian'),(30,'El Chapo','Guzmán','Spanish'),(31,'Nombre','Y Apellido','Spanish'),(32,'Urdu','Placeholder','Urdu'),(33,'Turkish','Placeholder','Turkish'),(35,'Spanish','Placeholder','Spanish'),(41,'Arabic','Placeholder','Arabic'),(47,'Mandarin','Placeholder','Mandarin'),(61,'Georgian','Placeholder','Georgian'),(65,'Russian','Placeholder','Russian'),(68,'Cantonese','Placeholder','Cantonese'),(157,'Pashto','Placeholder','Pashto'),(176,'Hebrew','Placeholder','Hebrew'),(411,'Korean','Placeholder','Korean'),(418,'Romanian','Placeholder','Romanian'),(490,'French','Placeholder','French'),(570,'Foochow','Placeholder','Foochow'),(637,'Bengali','Placeholder','Bengali'),(903,'Armenian','Placeholder','Armenian'),(2084,'Somali','Placeholder','Somali'),(2210,'Dutch','Placeholder','Dutch'),(2313,'Portuguese','Placeholder','Portuguese'),(2631,'Latvian','Placeholder','Latvian'),(3330,'Ukrainian','Placeholder','Ukrainian'),(3667,'Ga','Placeholder','Ga'),(4143,'Punjabi','Placeholder','Punjabi'),(4195,'Lithuanian','Placeholder','Lithuanian'),(4206,'Twi','Placeholder','Twi'),(4281,'Mandingo','Placeholder','Mandingo'),(4368,'Yoruba','Placeholder','Yoruba'),(4418,'Burmese','Placeholder','Burmese'),(4796,'Sinhala','Placeholder','Sinhala'),(9747,'Farsi','Placeholder','Farsi'),(14685,'Rafael','Trujillo','Spanish'),(14686,'Juan Antonio','Pinzón','Spanish'),(14687,'Wilfredo','del Campo','Spanish'),(14688,'Carlos Alberto','Pereira Lepe','Spanish, Portuguese'),(14689,'Francisco','Hernández Piolín','Spanish'),(14690,'Carlos','Barahona','Spanish'),(14691,'Ramón','Argandoña','Spanish'),(14692,'Alex','Rodríguez','Spanish'),(14693,'Joaquín','Rodrigo','Spanish'),(14694,'José Luis','Y Appellidos','Spanish'),(14695,'Manuel','Peña','Spanish'),(14696,'Patricio','Cáceres Estrada','Spanish'),(14697,'Nicanor','Parra','Spanish'),(14698,'Ernesto','Guevara','Spanish'),(14699,'José Carlos','Apellido Medina','Spanish'),(14700,'Narciso','Yepes','Spanish'),(14701,'Pseudónimo','Apellidos Aquí','Spanish'),(14702,'Enrique','Octavo','Spanish'),(14703,'Luis','Catorce','Spanish'),(14704,'Juan','Veintitrés','Spanish'),(14705,'Romanian','Placeholder II','Romanian'),(14706,'Romanian','Placeholder III','Romanian'),(14707,'Romanian','Placeholder IV','Romanian'),(14708,'Romanian','Placeholder V','Romanian'),(14709,'Foochow','Placeholder II','Foochow'),(14710,'Foochow','Placeholder III','Foochow'),(14711,'Foochow','Placeholder IV','Foochow'),(14712,'Mandarin','Placeholder II','Mandarin'),(14713,'Mandarin','Placeholder III','Mandarin'),(14714,'Mandarin','Placeholder IV','Mandarin'),(14715,'Mandarin','Placeholder V','Mandarin'),(14716,'Latvian','Placeholder II','Latvian'),(14717,'Latvian','Placeholder III','Latvian'),(14718,'Latvian','Placeholder IV','Latvian'),(14719,'Latvian','Placeholder V','Latvian');
/*!40000 ALTER TABLE `defendant_names` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-11 19:56:26
