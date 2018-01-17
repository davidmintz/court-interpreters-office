INSERT INTO `roles` VALUES (1,'submitter'),(2,'manager'),(3,'administrator'),(4,'staff');
INSERT INTO `hats` (id, name, can_be_anonymous, role_id) VALUES (1,'staff court interpreter',0,2),(2,'Interpreters Office staff',0,2),(3,'contract court interpreter',0,NULL),(4,'defense attorney',1,NULL),(5,'AUSA',0,NULL),(6,'Courtroom Deputy',0,1),(7,'Law Clerk',0,1),(8,'USPO',0,1),(9,'Pretrial Services Officer',0,1),(10,'paralegal',0,NULL),(11,'staff, US Attorneys Office',0,NULL),(12,'Pretrial',1,NULL),(13,'Magistrates',1,NULL),(14,'Judge',0,NULL);
INSERT INTO `location_types` VALUES (1,'courtroom',''),(2,'jail',''),(3,'holding cell',''),(4,'US Probation office',''),(5,'Pretrial Services office',''),(6,'interpreters office',''),(7,'courthouse',''),(8,'public area','');
INSERT INTO `event_categories` VALUES (1,'in'),(3,'not applicable'),(2,'out');
INSERT INTO `holidays` VALUES (1,'New Year\'s Day'),(2,'Martin Luther King Day'),(3,'Lincoln\'s Birthday'),(4,'President\'s Day'),(5,'Memorial Day'),(6,'Independence Day'),(7,'Labor Day'),(8,'Columbus Day'),(9,'Veterans\' Day'),(10,'Thanksgiving'),(11,'Christmas'),(12,'Election Day');
INSERT INTO `cancellation_reasons` VALUES (3,'belatedly adjourned'),(1,'defendant not produced'),(5,'forçe majeure'),(2,'no interpreter needed'),(7,'other'),(4,'party did not appear'),(6,'reason unknown');
INSERT INTO `judge_flavors` VALUES (3,'USBJ'),(1,'USDJ'),(2,'USMJ');
INSERT INTO `anonymous_judges` VALUES (2,'(not applicable)',NULL),(3,'(unknown)',NULL),(1,'magistrate',8);
