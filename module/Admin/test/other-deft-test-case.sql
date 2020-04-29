DELETE FROM defendants_events WHERE defendant_id IN  (25481,25482);
DELETE FROM events WHERE id IN (122520, 122521);
DELETE FROM defendants_requests WHERE request_id = 225577;
DELETE FROM requests WHERE id = 22577;
DELETE FROM defendant_names WHERE id IN (25481,25482);

INSERT INTO `defendant_names` VALUES (25481,'José','Hernández Medina'),(25482,'José Luis','Hernández Medina');
INSERT INTO `events` VALUES (122520,62,2634,117,32,'2020-03-25',NULL,NULL,'2021-CR-0123','','','2020-03-16 15:30:42','2020-03-16 15:30:42',16,512,NULL,NULL,NULL,512,'2020-03-16','15:30:00',0),(122521,62,2634,117,NULL,'2020-03-19','14:30:00',NULL,'2021-CR-0123','','','2020-03-16 15:32:04','2020-03-16 15:32:04',16,512,NULL,NULL,NULL,512,'2020-03-16','15:30:00',0);
INSERT INTO `defendants_events` VALUES (122520,25481),(122521,25482);

INSERT INTO `requests` VALUES (22577,'2020-04-08','10:00:00',2634,NULL,42,62,'2021-CR-0123',62,1084,'2020-03-16 15:45:17','2020-03-16 15:45:17',297,'',NULL,1,0,'');
INSERT INTO `defendants_requests` VALUES (25481,22577);