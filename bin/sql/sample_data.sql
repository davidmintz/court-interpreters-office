
DELETE FROM `languages`;
INSERT INTO `languages` VALUES (1,'Spanish',''),(2,'Russian',''),(3,'French',''),(4,'Foochow',''),(5,'Arabic','');

DELETE FROM `judge_flavors`;
/*!40000 ALTER TABLE `judge_flavors` DISABLE KEYS */;
INSERT INTO `judge_flavors` VALUES (1,'USDJ'),(2,'USMJ');
/*!40000 ALTER TABLE `judge_flavors` ENABLE KEYS */;


DELETE FROM hats;
INSERT INTO `hats` VALUES 
(1,'staff Court Interpreter'),
(2,'staff, Interpreters Office'),
(3,'contract court interpreter'),
(4,'defense attorney'),
(5,'AUSA'),
(6,'Courtroom Deputy'),
(7,'Law Clerk'),
(8,'USPO'),
(9,'Pretrial Services Officer'),
(10,'paralegal'),
(11,'staff, US Attorneys Office');
DELETE FROM `anonymous_hats`;
INSERT INTO `anonymous_hats` VALUES (1,'Magistrate'),(2,'Pretrial'),(3,'defense attorney');
