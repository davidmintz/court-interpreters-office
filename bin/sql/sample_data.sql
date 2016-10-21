INSERT INTO `cancellation_reasons` VALUES (2,'adjourned without notice'),(1,'defendant not produced'),(5,'forçe majeure'),(6,'other'),(3,'party did not appear'),(4,'unknown');
INSERT INTO `event_categories` VALUES (1,'in'),(3,'not applicable'),(2,'out');
INSERT INTO `event_types` VALUES (1,1,'pretrial conference',''),(2,1,'sentence',''),(3,2,'attorney/client interview',''),(4,1,'plea',''),(5,1,'presentment',''),(6,1,'arraignment',''),(7,2,'pretrial services',''),(8,2,'probation PSI interview','');
INSERT INTO `roles` VALUES (1,'submitter'),(2,'manager'),(3,'administrator')
INSERT INTO `hats` (id,name,can_be_anonymous,role_id) VALUES (1,'staff Cou Interpreter',0,2),(2,'staff, Interpreters Office',0,2),(3,'contract court interpreter',0,NULL),(4,'defense attorney',1,NULL),(5,'AUSA',0,NULL),(6,'Courtroom Deputy',0,1),(7,'Law Clerk',0,1),(8,'USPO',0,1),(9,'Pretrial Services Officer',0,1),(10,'paralegal',0,NULL),(11,'staff, US Attorneys Office',0,NULL),(12,'Pretrial',1,NULL),(13,'Magistrates',1,NULL);
INSERT INTO `judge_flavors` VALUES (1,'USDJ'),(2,'USMJ');
INSERT INTO `languages` VALUES (1,'Spanish',''),(2,'Russian',''),(3,'French',''),(4,'Foochow',''),(5,'Arabic','');
INSERT INTO `location_types` VALUES (1,'courtroom',''),(2,'jail',''),(3,'holding cell',''),(4,'US Probation office',''),(5,'Pretrial Services office',''),(6,'interpreters office',''),(7,'courthouse',''),(8,'public area','');
INSERT INTO `locations` VALUES (1,7,NULL,'500 Pearl',''),(2,7,NULL,'40 Foley',''),(3,2,NULL,'MCC',''),(4,2,NULL,'MDC',''),(5,4,1,'7th floor',''),(7,5,1,'5th floor',''),(8,3,1,'4th floor',''),(9,1,2,'618','');
