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

SET @courthouse1 = (SELECT id FROM locations WHERE name = 'Some Courthouse');
SET @courthouse2 = (SELECT id FROM locations WHERE name = 'Other Courthouse');
SET @jail = (SELECT id FROM locations WHERE name = 'Some Detention Center');
#  INSERT INTO locations VALUES (27,1,1,'510',"duty magistrate",1);
/* nested locations */
INSERT INTO locations (type_id, parent_location_id,name) VALUES
(1,@courthouse1,'101'),(1,@courthouse1,'102'),(1,@courthouse1,'103'),(1,@courthouse1,'104'),
(1,@courthouse1,'201'),(1,@courthouse1,'202'),(1,@courthouse1,'510'),
(1,@courthouse1,'203'),(1,@courthouse1,'204'),
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
("Rodríguez Núñez","José Luis"),("Rodríguez Castro","Juan Felipe"),
("Rodríguez Peña","Carmen"),("Rodríguez Hernández","Heriberto"),
("Rodríguez Medina","Carlos"),("López Fuentes","Luis Manuel"),
('Berio','Luciano'), ('Nono','Luigi'),("Ponce","Manuel"),("Mintzovski","David");

/* some interpreters */
SET @interpreter_hat = (SELECT id FROM hats WHERE name = 'contract court interpreter');

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Pavlova','Yana','',@interpreter_hat,'interpreter',1,'russian_interpreter@example.org','917 123-4567');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Russian'),2
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Françoise','Marie Louise','',@interpreter_hat,'interpreter',1,'french_interpreter@example.org',
     '123 123-4567');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
VALUES (last_insert_id(),'','','','','','','');
INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'French'),3
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Silva','Jose Luiz','',@interpreter_hat,'interpreter',1,'portuguese_interpreter@example.org',
     '111 222-3210');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
VALUES (last_insert_id(),'','','','','','','');
INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Portuguese'),3
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Lau','Lily','',@interpreter_hat,'interpreter',1,'foochow_interpreter@example.org',
     '222 333-3210');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
VALUES (last_insert_id(),'','','','','','','');
INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Foochow'), 3
);
INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Cantonese'), 3
);
INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Mandarin'), 3
);

SET @spanish = (SELECT id FROM languages WHERE name = 'Spanish');


INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Mintzenberger','David','',@interpreter_hat,'interpreter',1,'hebrew_interpreter@example.org',
     '666 666-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
     VALUES (last_insert_id(),'','','','','','','');
INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id)
    VALUES (
    last_insert_id(), (SELECT id FROM languages WHERE name = 'Hebrew'),2
);
INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id)
    VALUES (
    last_insert_id(), @spanish, 1
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Hispanófona','Carmen','',@interpreter_hat,'interpreter',1,'spanish_interpreter_1@example.org',
     '666 666-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
          VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id)
      VALUES (last_insert_id(), @spanish, 1
);


INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Intérprete','Cristina','',@interpreter_hat,'interpreter',1,'spanish_interpreter_2@example.org',
     '666 321-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
          VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id)
      VALUES (last_insert_id(), @spanish, 1
);


INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Granados','Enrique','',@interpreter_hat,'interpreter',1,'spanish_interpreter_3@example.org',
     '789 555-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
      VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id)
      VALUES (last_insert_id(), @spanish, 1
);
INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id) VALUES (
     last_insert_id(), (SELECT id FROM languages WHERE name = 'Portuguese'),3
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Nadal','Rafael','',@interpreter_hat,'interpreter',1,'spanish_interpreter_4@example.org',
     '012 123-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
          VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id)
      VALUES (last_insert_id(), @spanish, 1
);

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('López','Jennifer','',@interpreter_hat,'interpreter',1,'spanish_interpreter_5@example.org',
     '444 555-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
          VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id)
      VALUES (last_insert_id(), @spanish, 1
);
INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('del Potro','Juan Martín','',@interpreter_hat,'interpreter',1,'spanish_interpreter_6@example.org',
     '444 555-7890');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
          VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id)
      VALUES (last_insert_id(), @spanish, 1
);
INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
 ('Codouni','Marwan','',@interpreter_hat,'interpreter',1,'arabic_interpreter_1@example.org',
     '321 321-4321');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
      VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id)
      VALUES (last_insert_id(), (SELECT id FROM languages WHERE name = 'Arabic'), 2
);

/** a staff interpreter **/

SET @staff_interp = (SELECT id FROM hats WHERE name = "staff court interpreter");
SET @admin = (SELECT id FROM roles WHERE name = "administrator");


INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Staffinterp','Sonia','',@staff_interp,'person',1,'sonia_staffinterp@some.uscourts.gov',
    '212 840-0084');
INSERT INTO interpreters (id,comments, address1,address2,city,state,zip,country)
          VALUES (last_insert_id(),'','','','','','','');

INSERT INTO interpreters_languages (interpreter_id, language_id, credential_id)
      VALUES (last_insert_id(), @spanish, 1
);
INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES (LAST_INSERT_ID(), @admin, 'boink','admin',1,NULL,NOW());

/** lawyers **/
SET @atty = (SELECT id FROM hats WHERE name = "defense attorney");

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Litigious','Henry','',@atty,'person',1,'mister_lawyer@lawfirm.org',
    '222 333-6666');

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Edelbaum','Phillip','',@atty,'person',1,'edelbaum@philslawfirm.com', '222 333-6666');

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Vergara','Elizabeth','',@atty,'person',1,'vergara@somelawfirm.com', '234 234-2345');

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Thau','Roland','',@atty,'person',1,'roland@defenselawfirm.com', '234 234-2345');

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Bricker','Carrie','',@atty,'person',1,'bricker@herlawpractice.com', '234 666-2345');

SET @submitter = (SELECT id FROM roles WHERE name = "submitter");
SET @uspo = (SELECT id FROM hats WHERE name = "USPO");
SET @cdclerk = (SELECT id FROM hats WHERE name = "Courtroom Deputy");
SET @lawclerk = (SELECT id FROM hats WHERE name = "Law Clerk");

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Ramos','Lyvia','',@uspo,'person',1,'lyvia@some.uspd.uscourts.gov', '212 666-2345');


/**/
INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES ((SELECT MAX(id) FROM people), @submitter, 'boink','lyvia',1,NULL,NOW());
/**/

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Sternberg','Brian','',@uspo,'person',1,'sternberg@some.uspd.uscourts.gov', '212 777-2345');
INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES (LAST_INSERT_ID(), @submitter, 'boink','sternberg',1,NULL,NOW());

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Pérez','Graciela','',@uspo,'person',1,'perez@some.uspd.uscourts.gov', '212 333-3333');
INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES (LAST_INSERT_ID(), @submitter, 'boink','graciela',1,NULL,NOW());
INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Somebody','Susan','',@uspo,'person',1,'susan_somebody@some.uscourts.gov', '212 666-2345');
INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES (LAST_INSERT_ID(), @submitter, 'boink','susan',1,NULL,NOW());

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Schwartzberg','Mylie','',@lawclerk,'person',1,'mylie_schwartzberg@some.uscourts.gov', '212 666-9876');
INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES (LAST_INSERT_ID(), @submitter, 'boink','mylie',1,NULL,NOW());

INSERT INTO clerks_judges (user_id, judge_id) VALUES (LAST_INSERT_ID(),(SELECT id FROM people
WHERE discr = "judge" AND lastname = "Bludgeon"));

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Hartford','Amy','',@lawclerk,'person',1,'amy_hartford@some.uscourts.gov', '212 666-8899');
INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES (LAST_INSERT_ID(), @submitter, 'boink','amy',1,NULL,NOW());

INSERT INTO clerks_judges (user_id, judge_id) VALUES (LAST_INSERT_ID(),(SELECT id FROM people
WHERE discr = "judge" AND lastname = "Bludgeon"));

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Rojas','Esmeralda','',@cdclerk,'person',1,'esmeralda_rojas@some.uscourts.gov', '212 666-8899');

INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES (LAST_INSERT_ID(), @submitter, 'boink','esmeralda',1,NULL,NOW());

INSERT INTO clerks_judges (user_id, judge_id) VALUES (LAST_INSERT_ID(),(SELECT id FROM people
WHERE discr = "judge" AND lastname = "Dorkendoofer"));

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Zimmer','Zack','',@cdclerk,'person',1,'zack_zimmer@some.uscourts.gov', '212 666-1324');
INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES (LAST_INSERT_ID(), @submitter, 'boink','zack',1,NULL,NOW());

INSERT INTO clerks_judges (user_id, judge_id) VALUES (LAST_INSERT_ID(),(SELECT id FROM people
WHERE discr = "judge" AND lastname = "Boinkleheimer"));

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Ho','Ting','',@cdclerk,'person',1,'ting_ho@some.uscourts.gov', '212 666-4235');
INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES (LAST_INSERT_ID(), @submitter, 'boink','ting',1,NULL,NOW());

INSERT INTO clerks_judges (user_id, judge_id) VALUES (LAST_INSERT_ID(),(SELECT id FROM people
WHERE discr = "judge" AND lastname = "Wiseburger"));

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Montgomery','Wes','',@cdclerk,'person',1,'wes_montgomery@some.uscourts.gov', '212 666-7892');
INSERT INTO users (person_id, role_id, password, username, active, last_login, created)
VALUES (LAST_INSERT_ID(), @submitter, 'boink','wes',1,NULL,NOW());

INSERT INTO clerks_judges (user_id, judge_id) VALUES (LAST_INSERT_ID(),(SELECT id FROM people
WHERE discr = "judge" AND lastname = "Wiseburger"));

/* prosecutors */
SET @ausa = (SELECT id FROM hats WHERE name = "AUSA");
INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Jackson','Jane','',@ausa,'person',1,'jane.prosecutor@some.usdoj.gov', '212 666-6637');

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Fillmore','Millard','',@ausa,'person',1,'millard.fillmore@some.usdoj.gov', '212 666-6600');

INSERT INTO people (lastname, firstname, middlename, hat_id, discr, active, email, mobile_phone) VALUES
('Van Buren','Martin','',@ausa,'person',1,'martin.vanburen@some.usdoj.gov', '212 666-6601');

# anonymous judges
SET @ctroom = (SELECT id FROM locations WHERE name = "510");
UPDATE anonymous_judges SET default_location_id = @ctroom WHERE name = "magistrate";
