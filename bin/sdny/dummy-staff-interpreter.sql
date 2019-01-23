
LOCK TABLES `people` WRITE;
INSERT INTO `people` VALUES (NULL,1,'john_somebody@some.uscourts.gov','Somebody','John','','','',1,'interpreter');
UNLOCK TABLES;

LOCK TABLES `interpreters` WRITE;
INSERT INTO `interpreters` VALUES (LAST_INSERT_ID(),'',NULL,NULL,NULL,NULL,NULL,NULL,'','','','','','','United States');
UNLOCK TABLES;

LOCK TABLES `interpreters_languages` WRITE;
INSERT INTO `interpreters_languages` VALUES (LAST_INSERT_ID(),62,1);
UNLOCK TABLES;
