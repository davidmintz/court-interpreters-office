/* clear it out */
SET foreign_key_checks = 0;
TRUNCATE TABLE languages;
TRUNCATE TABLE locations;
TRUNCATE TABLE event_types;
TRUNCATE TABLE judges;
TRUNCATE TABLE interpreters;
TRUNCATE TABLE people;
TRUNCATE TABLE defendant_names;
SET foreign_key_checks = 1;

/*  insert some languages  */

INSERT INTO languages (name) VALUES ('Spanish'),('Foochow'),('Mandarin'),('Russian'),('Arabic'),
('Cantonese'),('Korean'),('French'),('Urdu'),('Punjabi'),('Hebrew'),('Pashto'),('Romanian'),('Bengali'),
('Turkish'),('Albanian'),('Georgian'),('Portuguese'),('Farsi'),('Somali');

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

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active) VALUES
 ('Bludgeon','Vernon','T.',@judge_hat,'judge',1);
INSERT INTO judges (id,default_location_id,flavor_id) VALUES (
     last_insert_id(), (SELECT id FROM locations WHERE name = '2B'),
     @usdj);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active) VALUES
 ('Judicious','Jane','T.',@judge_hat,'judge',1);
INSERT INTO judges (id,default_location_id,flavor_id) VALUES (
     last_insert_id(), (SELECT id FROM locations WHERE name = '2C'),
     @usdj);


INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active) VALUES
 ('Wiseburger','Wilma','T.',@judge_hat,'judge',1);
INSERT INTO judges (id,default_location_id,flavor_id) VALUES (
     last_insert_id(), (SELECT id FROM locations WHERE name = '2D'),
     @usdj);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active) VALUES
 ('Dorkendoofer','William','D.',@judge_hat,'judge',1);
INSERT INTO judges (id,default_location_id,flavor_id) VALUES (
     last_insert_id(), (SELECT id FROM locations WHERE name = '101'),
     @usdj);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active) VALUES
 ('Boinkleheimer','Ronda','B.',@judge_hat,'judge',1);
INSERT INTO judges (id,default_location_id,flavor_id) VALUES (
     last_insert_id(), (SELECT id FROM locations WHERE name = '201'),
     @usdj);

/* some defendant names */
INSERT INTO defendant_names (surnames, given_names) VALUES
('Olivero','Francisco'),('de los Ríos', 'Erika'),('García','Humberto'),
('García Lorca','Federico'),('García','Alfredo'),('Zhao','Zheng'),('Badofsky','Boris'),
('Albéniz','Isaac'),('Turina','Joaquín'),('Villalobos','Heitor'),('Ravel','Maurice'),
('Boulanger','Nadia'), ('Barrios','Agustín'), ('de los Santos','Alguien'),('Daza','Esteban'),
('de Narváez','Luis'),('de los Santos','Nadie'),('de los Zetas', 'Alguno'),
('Berio','Luciano'), ('Nono','Luigi');

/* some interpreters */
SET @interpreter_hat = (SELECT id FROM hats WHERE name = 'contract court interpreter');

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Pavlova','Yana','',@interpreter_hat,'interpreter',1,'russian_interpreter@example.org','917 123-4567');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Russian')
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Françoise','Marie Louise','',@interpreter_hat,'interpreter',1,'french_interpreter@example.org',
     '123 123-4567');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
VALUES (last_insert_id(),'','','','','','','');
INSERT INTO interpreters_languages (interpreter_id, language_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'French')
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Silva','Jose Luiz','',@interpreter_hat,'interpreter',1,'portuguese_interpreter@example.org',
     '111 222-3210');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
VALUES (last_insert_id(),'','','','','','','');
INSERT INTO interpreters_languages (interpreter_id, language_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Portuguese')
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Lau','Lily','',@interpreter_hat,'interpreter',1,'foochow_interpreter@example.org',
     '222 333-3210');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
VALUES (last_insert_id(),'','','','','','','');
INSERT INTO interpreters_languages (interpreter_id, language_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Foochow')
);
INSERT INTO interpreters_languages (interpreter_id, language_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Cantonese')
);
INSERT INTO interpreters_languages (interpreter_id, language_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Mandarin')
);

SET @spanish = (SELECT id FROM languages WHERE name = 'Spanish');


INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Mintzenberger','David','',@interpreter_hat,'interpreter',1,'hebrew_interpreter@example.org',
     '666 666-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
     VALUES (last_insert_id(),'','','','','','','');
INSERT INTO interpreters_languages (interpreter_id, language_id)
    VALUES (
    last_insert_id(), (SELECT id FROM languages WHERE name = 'Hebrew')
);
INSERT INTO interpreters_languages (interpreter_id, language_id, federal_certification)
    VALUES (
    last_insert_id(), @spanish, 1
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Hispanófona','Carmen','',@interpreter_hat,'interpreter',1,'spanish_interpreter_1@example.org',
     '666 666-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
          VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, federal_certification)
      VALUES (last_insert_id(), @spanish, 1
);


INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Intérprete','Cristina','',@interpreter_hat,'interpreter',1,'spanish_interpreter_2@example.org',
     '666 321-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
          VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, federal_certification)
      VALUES (last_insert_id(), @spanish, 1
);


INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Granados','Enrique','',@interpreter_hat,'interpreter',1,'spanish_interpreter_3@example.org',
     '789 555-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
      VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, federal_certification)
      VALUES (last_insert_id(), @spanish, 1
);
INSERT INTO interpreters_languages (interpreter_id, language_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Portuguese')
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Nadal','Rafael','',@interpreter_hat,'interpreter',1,'spanish_interpreter_4@example.org',
     '012 123-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
          VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, federal_certification)
      VALUES (last_insert_id(), @spanish, 1
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('López','Jennifer','',@interpreter_hat,'interpreter',1,'spanish_interpreter_5@example.org',
     '444 555-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
          VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, federal_certification)
      VALUES (last_insert_id(), @spanish, 1
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Codouni','Marwan','',@interpreter_hat,'interpreter',1,'arabic_interpreter_1@example.org',
     '321 321-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
      VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id)
      VALUES (last_insert_id(), (SELECT id FROM languages WHERE name = 'Arabic')
);
