
SET @PEARL = (SELECT id FROM locations WHERE name = '500 Pearl');
SET @FOLEY = (SELECT id FROM locations WHERE name = '40 Foley');
SET @WPLAINS = (SELECT id FROM locations WHERE name = 'White Plains');

INSERT INTO `locations` VALUES (NULL,3,@PEARL,'4th floor cellblock','',1),
(NULL,8,@PEARL,'cafeteria','',1),
(NULL,6,@PEARL,'Interpreters Office','room 280',1),
(NULL,4,@PEARL,'Probation','6th and 7th floors',1),
(NULL,5,@PEARL,'Pretrial','5th floor',1),
(NULL,3,@WPLAINS,'Probation','',1),
(NULL,3,@FOLEY,'3rd floor cellblock','',1);
