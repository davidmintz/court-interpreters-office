#!/usr/bin/env php
<?php
$db = require(__DIR__."/connect.php");
$db->exec("use dev_interpreters");
$sql=
'SELECT DISTINCT user_id, name, email, interpreter_id FROM users LEFT JOIN events
ON (events.lastmod_by = user_id OR events.created_by = user_id)
WHERE COALESCE(lastmod_by, created_by)
IS NOT NULL OR (req_class = 3 AND req_by = users.user_id)';

$person_insert = $db->prepare(
    'INSERT INTO people (lastname, firstname, email, hat_id, active, discr)'
        . ' VALUES (:lastname, :firstname, :email,:hat_id, :discr)'
);
$user_insert = $db->prepare( 'INSERT INTO users (person_id,role_id,username,password,created, active '
        . ' VALUES (:person_id, :role_id, :username, :password, NOW(), active');

/** @var $person_lookup \PDOStatement */
$person_lookup = $db->prepare('SELECT id FROM office.people WHERE email = :email');

define('STAFF_INTERPRETER',1);
define('OFFICE_STAFF',2);
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

/*
+---------+-----------+--------------------------------------+----------------+
| user_id | name      | email                                | interpreter_id |
+---------+-----------+--------------------------------------+----------------+
|       1 | david     | david@davidmintz.org                 |            117 |
|       0 | eileen    | eileen_levine@nysd.uscourts.gov      |           NULL |
|       5 | pat       | patricia_lelandais@nysd.uscourts.gov |           NULL |
|       8 | fnulnu    | NULL                                 |           NULL |
|       4 | nancy     | nancy_festinger@nysd.uscourts.gov    |            200 |
|       2 | elena     | elena_rich@nysd.uscourts.gov         |            197 |
|       6 | paula     | paula_gold@nysd.uscourts.gov         |            198 |
|       3 | mirta     | mirta_hess@nysd.uscourts.gov         |            199 |
|      10 | tatiana   | tatiana_kaliakina@nysd.uscourts.gov  |           NULL |
|      12 | brandon   | brandon_skolnik@nysd.uscourts.gov    |           NULL |
|      11 | peter     | peter_anderson@nysd.uscourts.gov     |            548 |
|      16 | marilyn   | marilyn_ong@nysd.uscourts.gov        |           NULL |
|      13 | evon      | evon_simpson@nysd.uscourts.gov       |           NULL |
|      18 | jordan    | jordan_fox@nysd.uscourts.gov         |            825 |
|      19 | francisco | francisco_olivero@nysd.uscourts.gov  |            840 |
|      22 | humberto  | humberto_garcia@nysd.uscourts.gov    |            862 |
|      25 | erika     | erika_de_los_rios@nysd.uscourts.gov  |            881 |
|      27 | cristina  | cristina_cortes@nysd.uscourts.gov    |           NULL |
|      26 | jill      | jill_@nysd.uscourts.gov              |           NULL |
|      21 | maria     |                                      |           NULL |
|      20 | alexandra | alexandra.gold@yahoo.com             |           NULL |
|      29 | pierre    | pierre_neptune@nysd.uscourts.gov     |           NULL |
+---------+-----------+--------------------------------------+----------------+
*/
$users = $db->query($sql)->fetchAll();

// make some shit up
$people = [
    'pat' => [
        ':lastname' => 'Lelandais',
        ':firstname' =>'Pat',
        ':email' => '',
    ],

];
foreach ($users as $user) {

    if ($user['interpreter_id']) { // staff interpreter
        printf("$user[name] is a staff interpreter, person id is %s\n",$user['interpreter_id']);
    } else {
        if ($user['email']) {
            $person_lookup->execute(['email'=>$user['email']]);
            $id = $person_lookup->fetchColumn();
            if (! $id) {
                printf("need to insert a person for %s?\n",$user['name']);
            }
        }
    }
}
