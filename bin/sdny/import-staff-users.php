#!/usr/bin/env php
<?php
$db = require(__DIR__."/connect.php");
$db->exec("use dev_interpreters");

$person_insert = $db->prepare(
    'INSERT INTO people (lastname, firstname, email, hat_id, active, discr)'
        . ' VALUES (:lastname, :firstname, :email,:hat_id, :discr)'
);
$user_insert = $db->prepare( 'INSERT INTO users (person_id,role_id,username,password,created, active '
        . ' VALUES (:person_id, :role_id, :username, :password, NOW(), active');


define('HAT_STAFF_INTERPRETER',1);
define('HAT_OFFICE_STAFF',2);

// make some shit up
$people = [
    'pat' => [
        ':lastname' => 'Lelandais',
        ':firstname' =>'Pat',
        ':email' => 'patricia_lelandais@nysd.uscourts.gov',
        ':active' => 0,
    ],
    'alexandra' => [
        ':lastname' => 'Alexandra',
        ':firstname' =>'Gold',
        ':email' => 'alexandra.gold@yahoo.com',
        ':active' => 0,
    ],
    'jill' => [
        ':lastname' => 'Jill',
        ':firstname' =>'Something',
        ':email' => 'jill_something@nysd.uscourts.gov',
        ':active' => 0,
    ],

];

/*
+----+---------------+----------+
| id | name          | comments |
+----+---------------+----------+
|  1 | submitter     |          |
|  2 | manager       |          |
|  3 | administrator |          |
|  4 | staff         |          |
+----+---------------+----------+
*/
$sql=
'SELECT DISTINCT user_id, u.name, p.email person_email, u.email user_email, interpreter_id, p.id person_id, u.active FROM users u LEFT JOIN events ON (events.lastmod_by = user_id OR events.created_by = user_id) LEFT JOIN office.people p ON p.email = u.email;';

$users = $db->query($sql)->fetchAll();

/*
SELECT DISTINCT user_id, u.name, p.email person_email, u.email user_email, interpreter_id, p.id person_id FROM users u LEFT JOIN events ON (events.lastmod_by = user_id OR events.created_by = user_id) LEFT JOIN office.people p ON p.email = u.email;
+---------+-----------+-------------------------------------+--------------------------------------+----------------+-----------+
| user_id | name      | person_email                        | user_email                           | interpreter_id | person_id |
+---------+-----------+-------------------------------------+--------------------------------------+----------------+-----------+
|       1 | david     | david@davidmintz.org                | david@davidmintz.org                 |            117 |       117 |
|       0 | eileen    | eileen_levine@nysd.uscourts.gov     | eileen_levine@nysd.uscourts.gov      |           NULL |      1033 |
|       5 | pat       | NULL                                | patricia_lelandais@nysd.uscourts.gov |           NULL |      NULL |
|       8 | fnulnu    | NULL                                | NULL                                 |           NULL |      NULL |
|       4 | nancy     | NULL                                | nancy_festinger@nysd.uscourts.gov    |            200 |      NULL |
|       2 | elena     | NULL                                | elena_rich@nysd.uscourts.gov         |            197 |      NULL |
|       6 | paula     | NULL                                | paula_gold@nysd.uscourts.gov         |            198 |      NULL |
|       3 | mirta     | NULL                                | mirta_hess@nysd.uscourts.gov         |            199 |      NULL |
|      10 | tatiana   | NULL                                | tatiana_kaliakina@nysd.uscourts.gov  |           NULL |      NULL |
|      12 | brandon   | brandon_skolnik@nysd.uscourts.gov   | brandon_skolnik@nysd.uscourts.gov    |           NULL |      1128 |
|      11 | peter     | Peter_Anderson@nysd.uscourts.gov    | peter_anderson@nysd.uscourts.gov     |            548 |       548 |
|      11 | peter     | peter_anderson@nysd.uscourts.gov    | peter_anderson@nysd.uscourts.gov     |            548 |      1116 |
|      16 | marilyn   | marilyn_ong@nysd.uscourts.gov       | marilyn_ong@nysd.uscourts.gov        |           NULL |      1078 |
|      13 | evon      | evon_simpson@nysd.uscourts.gov      | evon_simpson@nysd.uscourts.gov       |           NULL |      1013 |
|      18 | jordan    | NULL                                | jordan_fox@nysd.uscourts.gov         |            825 |      NULL |
|      19 | francisco | francisco_olivero@nysd.uscourts.gov | francisco_olivero@nysd.uscourts.gov  |            840 |      1372 |
|      22 | humberto  | NULL                                | humberto_garcia@nysd.uscourts.gov    |            862 |      NULL |
|      25 | erika     | NULL                                | erika_de_los_rios@nysd.uscourts.gov  |            881 |      NULL |
|      27 | cristina  | NULL                                | cristina_cortes@nysd.uscourts.gov    |           NULL |      NULL |
|      26 | jill      | NULL                                | jill_@nysd.uscourts.gov              |           NULL |      NULL |
|      21 | maria     | NULL                                |                                      |           NULL |      NULL |
|      20 | alexandra | NULL                                | alexandra.gold@yahoo.com             |           NULL |      NULL |
|      29 | pierre    | NULL                                | pierre_neptune@nysd.uscourts.gov     |           NULL |      NULL |
|      30 | katelynn  | NULL                                | katelynn_perry@nysd.uscourts.gov     |           NULL |      NULL |
+---------+-----------+-------------------------------------+--------------------------------------+----------------+-----------+
24 rows in set (1.96 sec)
*/


foreach ($users as $user) {
    if (in_array($user['name'],['maria','eileen','fnulnu'])) {
        printf("skipping hopeless user name: %s\n",$user['name']);
        continue;
    }
    if ($user['interpreter_id']) { // staff interpreter
        //staff interpreter, person id is $user['interpreter_id']);
        // so do the user insert, role depending on who it is
        printf("create ONLY user (not person) for %s, active %s\n",$user['name'], $user['active'] ? "YES":"NO");
    } else {
        $data = print_r($user,true);
        if ($user['person_id']) {
            // then the user should already exist in people
            // but should ALSO exist as an extinct person-user (inactive)
            // with an 'interpreter staff' hat|office staff role            
            printf("need to create INACTIVE person-user for: %s\n",$user['name']);
        } else {
           printf("need to create NEW  %s person-user for %s\n", $user['active'] ? "ACTIVE":"INACTIVE", $user['name'] );
        }
     }
        
}
