INSERT INTO `roles` VALUES (1,'submitter',''),(2,'manager',''),(3,'administrator',''),(4,'staff','');
INSERT INTO `hats` VALUES (1,2,'staff court interpreter',0),(2,2,'Interpreters Office staff',0),(3,NULL,'contract court interpreter',0),(4,NULL,'defense attorney',2),(5,NULL,'AUSA',2),(6,1,'Courtroom Deputy',0),(7,1,'Law Clerk',0),(8,1,'USPO',0),(9,1,'Pretrial Services Officer',0),(10,NULL,'paralegal',2),(11,NULL,'staff, US Attorneys Office',2),(12,NULL,'Pretrial',1),(13,NULL,'Magistrates',1),(14,NULL,'Judge',0);
INSERT INTO `location_types` VALUES (1,'courtroom',''),(2,'jail',''),(3,'holding cell',''),(4,'US Probation office',''),(5,'Pretrial Services office',''),(6,'interpreters office',''),(7,'courthouse',''),(8,'public area','');
INSERT INTO `event_categories` VALUES (1,'in'),(3,'not applicable'),(2,'out');
INSERT INTO `holidays` VALUES (1,'New Year\'s Day'),(2,'Martin Luther King Day'),(3,'Lincoln\'s Birthday'),(4,'President\'s Day'),(5,'Memorial Day'),(6,'Independence Day'),(7,'Labor Day'),(8,'Columbus Day'),(9,'Veterans\' Day'),(10,'Thanksgiving'),(11,'Christmas'),(12,'Election Day');
INSERT INTO `cancellation_reasons` VALUES (3,'belatedly adjourned'),(1,'defendant not produced'),(5,'forçe majeure'),(2,'no interpreter needed'),(7,'other'),(4,'party did not appear'),(6,'reason unknown');
INSERT INTO `judge_flavors` VALUES (1,'USDJ',0),(2,'USMJ',5),(3,'USBJ',10);
INSERT INTO `anonymous_judges` VALUES (2,'(not applicable)',NULL),(3,'(unknown)',NULL),(1,'magistrate',NULL);
