/*  insert some languages  */

INSERT INTO languages (name) VALUES ('Spanish'),('Foochow'),('Mandarin'),('Russian'),('Arabic'),('Cantonese'),('Korean'),('French'),('Urdu'),('Punjabi'),('Hebrew'),('Pashto'),('Romanian'),('Bengali'),('Turkish'),('Albanian'),('Georgian'),('Portuguese'),('Farsi'),('Somali');

/* insert some locations */
/*
+----+--------------------------+----------+
| id | type                     | comments |
+----+--------------------------+----------+
|  1 | courtroom                |          |
|  2 | jail                     |          |
|  3 | holding cell             |          |
|  4 | US Probation office      |          |
|  5 | Pretrial Services office |          |
|  6 | interpreters office      |          |
|  7 | courthouse               |          |
|  8 | public area              |          |
+----+--------------------------+----------+
 */
/* parent locations */
INSERT INTO locations (type_id, parent_location_id,name) VALUES
(7, NULL, 'Some Courthouse'),(7, NULL, 'Other Courthouse'),(2,NULL,'Some Detention Center');

SET @courthouse1 = (SELECT id FROM locations WHERE name = 'Big Courthouse');
SET @courthouse2 = (SELECT id FROM locations WHERE name = 'Other Courthouse');
SET @jail = (SELECT id FROM locations WHERE name = 'Some Detention Center');

/* nested locations */
INSERT INTO locations (type_id, parent_location_id,name) VALUES
(1,@courthouse1,'101'),(1,@courthouse1,'102'),(1,@courthouse1,'103'),(1,@courthouse1,'104'),
(1,@courthouse1,'201'),(1,@courthouse1,'202'),(1,@courthouse1,'203'),(1,@courthouse1,'204'),
(1,@courthouse2,'2A'),(1,@courthouse2,'2B'),(1,@courthouse2,'2C'),(1,@courthouse2,'2D'),
(1,@courthouse2,'4A'),(1,@courthouse2,'4B'),(1,@courthouse2,'4C'),(1,@courthouse2,'4D'),
(1,@courthouse2,'5A'),(1,@courthouse2,'5B'),(1,@courthouse2,'5C'),(1,@courthouse2,'5D'),
(3,@courthouse1,'the holding cell'),
(6,@courthouse1,'your Interpreters Office'),
(4,@courthouse2,'your Probation Office');

/* proceedings a/k/a event-types */
/*
+----+----------------+
| id | category       |
+----+----------------+
|  1 | in             |
|  2 | out            |
|  3 | not applicable |
+----+----------------+
 */
INSERT INTO event_types (name,category_id ) VALUES
 ('conference',1),('atty/client interview',2),
 ('sentence', 1), ('plea', 1), ('presentment', 1), ('arraignment', 1),
 ('probation interview',2),('trial',1),('bail hearing',1),('suppression hearing',1),
 ('document translation',3);

SET @usdj = (SELECT id FROM judge_flavors WHERE flavor = 'USDJ');
 /* some judges */
SET @judge_hat = (SELECT id FROM hats WHERE name = 'Judge');

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active) VALUES
 ('Harshbarger','Thaddeus','Q.',@judge_hat,'judge',1);
INSERT INTO judges (id,default_location_id,flavor_id) VALUES (
     last_insert_id(), (SELECT id FROM locations WHERE name = '2A'),
     @usdj
);
