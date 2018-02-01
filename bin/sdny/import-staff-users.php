#!/usr/bin/env php
<?php
$db = require(__DIR__."/connect.php");
$db->exec("use dev_interpreters");

$person_insert = $db->prepare(
    'INSERT INTO people (lastname, firstname, email, hat_id, active, discr)'
        . ' VALUES (:lastname, :firstname, :email, :hat_id, :active, :discr)'
);
$user_insert = $db->prepare( 
         'INSERT INTO users (person_id, role_id, username, password, created, active) '
        . ' VALUES (:person_id, :role_id, :username, :password, NOW(), :active)'
);


define('HAT_STAFF_INTERPRETER',1);
define('HAT_OFFICE_STAFF',2);

// supply data that's maybe not available in the old db
$staff = [
    'pat' => [
        'lastname' => 'Lelandais',
        'firstname' =>'Pat',
        'email' => 'patricia_lelandais@nysd.uscourts.gov',
        'active' => 0,
        'role_id'   => 2,
    ],
    'alexandra' => [
        'lastname' => 'Alexandra',
        'firstname' =>'Gold',
        'email' => 'alexandra.gold@yahoo.com',
        'active' => 0,
        'role_id'   => 4,
    ],
    'jill' => [
        'lastname' => 'Jill',
        'firstname' =>'Something',
        'email' => 'jill_something@nysd.uscourts.gov',
        'active' => 0,
        'role_id'   => 2,
    ],
    'cristina' => [
        'lastname' => 'Cortes',
        'firstname' =>'Cristina',
        'email' => 'cristina_cortes@nysd.uscourts.gov',
        'active' => 1,
        'role_id'   => 2,
    ],
    'pierre' => [
        'lastname' => 'Neptune',
        'firstname' =>'Pierre',
        'email' => 'pierre_neptune@nysd.uscourts.gov',
        'active' => 0,
        'role_id'   => 2,
        
    ],
    'tatiana' => [
        'lastname' => 'Kaliakina',
        'firstname' =>'Tatiana',
        'email' => 'tatiana_kaliakina@nysd.uscourts.gov',
        'active' => 0,
        'role_id'   => 2,
    ],
    'katelynn' => [
        'lastname' => 'Perry',
        'firstname' =>'Katelynn',
        'email' => 'katelynn_perry@nysd.uscourts.gov',
        'active' => 1,
        'role_id'   => 2,
    ],
];


$sql=
'SELECT DISTINCT user_id, u.name, p.email person_email, u.email user_email, interpreter_id, p.id person_id, u.active FROM users u LEFT JOIN events ON (events.lastmod_by = user_id OR events.created_by = user_id) LEFT JOIN office.people p ON p.email = u.email;';

$users = $db->query($sql)->fetchAll();
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
$db->exec('use office');
foreach ($users as $user) {
    $params = [];
    if (in_array($user['name'],['maria','eileen','fnulnu'])) {
        printf("NOTICE: skipping hopeless user name: %s\n",$user['name']);
        continue;
    }
    if ($user['interpreter_id']) { // staff interpreter
        // staff interpreter, with person id $user['interpreter_id']);
        // do the user insert, role depending on who it is
        printf("creating ONLY user (not person) for %s, active %s...",$user['name'], $user['active'] ? "YES":"NO");
        /* // VALUES (:person_id, :role_id, :username, :password, NOW(), :active');*/
        $params = [
            ':person_id' =>  $user['interpreter_id'],
            ':username' => $user['name'],
            ':active' =>  $user['active'], 
            ':role_id' =>  ($user['name'] == 'david' ? 3 : 2),
            ':password'  => password_hash('boink',PASSWORD_DEFAULT),
         ];
        try {
            $user_insert->execute($params);
            printf("OK\n");
        } catch (Exception $e) {
            if (23000 == $e->getCode()) {
                printf("\nWARNING: moving on despite '%s' while inserting user %s at %d\n",
                        $e->getMessage(),$user['name'],__LINE__);
            } else {
                printf("caught exception %s at %d with user data %s\n",$e->getMessage(),__LINE__,print_r($params,true));
                exit(1);
            }
        }
    } else {
        if ($user['person_id']) {
            printf("\n%s has a fucking person id %d!!!\n",$user['name'],$user['person_id']);
            // then the user should already exist in people
            // BUT should ALSO exist as an separate, extinct person-user (inactive)
            // with an 'interpreter staff' hat|office staff role 
            
            //... or not ???????
            //SELECT p.lastname, p.firstname, h.name hat,r.name role FROM people p JOIN users u ON p.id = u.person_id JOIN hats h ON p.hat_id = h.id JOIN roles r ON r.id = u.role_id WHERE r.name <> "submitter";

            try {
                printf("creating INACTIVE user for: %s ...",$user['name']);                
                // we can get the data from the people table
                //$data = $db->query('select * from people where id = '.$user['person_id'])->fetch();
                /*
                $params = [
                            ':email' => $user['user_email'] ?: $data['email'],
                            ':active' => 0,
                            ':discr' => 'person',
                            ':lastname' => $data['lastname'],
                            ':firstname' => $data['firstname'],
                            ':hat_id'   => HAT_OFFICE_STAFF
                        ];
                $person_insert->execute($params);
                $id = $db->query('SELECT LAST_INSERT_ID()')->fetchColumn();
                 */
                $params = [
                    //VALUES (:person_id, :role_id, :username, :password, NOW(), :active')
                    ':person_id' => $user['person_id'],
                    ':role_id' => 4,
                    ':username' => $user['name'],
                    ':password' => password_hash('boink',PASSWORD_DEFAULT),
                    ':active' => 0,                    
                ];
                $user_insert->execute($params);
                echo "OK\n";
            } catch (Exception $e) {
                if (23000 == $e->getCode()) {
                printf("\nWARNING: moving on despite '%s' while inserting user %s at %d\n",
                        $e->getMessage(),$user['name'],__LINE__);
                } else {
                    printf("while doing person query followed by person|user insert, caught exception \"%s\"\nat %d with params %s\n",$e->getMessage(),__LINE__,print_r($params,true));
                    exit(1);
                }
            }
            
        } else {

           printf("creating NEW %s person-user for %s...", $user['active'] ? "ACTIVE":"INACTIVE", $user['name'] );
           if (! isset($staff[$user['name']])) {
               printf("we have no data for user $user[name] at %d\n",__LINE__);
               exit(1);
           }
           $data = $staff[$user['name']];
           try {
                $params = [ // VALUES (:lastname, :firstname, :email,:hat_id, :discr, :active)'
                    ':email' => $user['user_email'] ?: $data['email'],
                     ':active' => 0,
                     ':discr' => 'person',
                     ':lastname' => $data['lastname'],
                     ':firstname' => $data['firstname'],
                     ':hat_id'   => HAT_OFFICE_STAFF];
                
                $person_insert->execute($params);
                $id = $db->query('SELECT LAST_INSERT_ID()')->fetchColumn();
                $params = [
                //VALUES (:person_id, :role_id, :username, :password, NOW(), :active');
                    ':person_id' => $id,
                    ':role_id' => $data['role_id'],
                    ':username' => $user['name'],
                    ':password' => password_hash('boink',PASSWORD_DEFAULT),
                    ':active' => 0,                    
                ];
                $user_insert->execute($params);
                echo "OK\n";
           } catch (Exception $e) {
                printf("while doing person query followed by person|user insert, caught exception %s at %d with params %s\n",$e->getMessage(),__LINE__,print_r($params,true));
                exit(1);
           }           
        }
    }        
}


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
|      12 | brandon   | brandon_skolnik@nysd.uscourts.gov   | brandon_skolnik@nysd.uscourts.gov    |           NULL |      1127 |
|      11 | peter     | Peter_Anderson@nysd.uscourts.gov    | peter_anderson@nysd.uscourts.gov     |            548 |       548 |
|      16 | marilyn   | marilyn_ong@nysd.uscourts.gov       | marilyn_ong@nysd.uscourts.gov        |           NULL |      1078 |
|      13 | evon      | evon_simpson@nysd.uscourts.gov      | evon_simpson@nysd.uscourts.gov       |           NULL |      1013 |
|      18 | jordan    | NULL                                | jordan_fox@nysd.uscourts.gov         |            825 |      NULL |
|      19 | francisco | francisco_olivero@nysd.uscourts.gov | francisco_olivero@nysd.uscourts.gov  |            840 |      1371 |
|      22 | humberto  | NULL                                | humberto_garcia@nysd.uscourts.gov    |            862 |      NULL |
|      25 | erika     | NULL                                | erika_de_los_rios@nysd.uscourts.gov  |            881 |      NULL |
|      27 | cristina  | NULL                                | cristina_cortes@nysd.uscourts.gov    |           NULL |      NULL |
|      26 | jill      | NULL                                | jill_@nysd.uscourts.gov              |           NULL |      NULL |
|      21 | maria     | NULL                                |                                      |           NULL |      NULL |
|      20 | alexandra | NULL                                | alexandra.gold@yahoo.com             |           NULL |      NULL |
|      29 | pierre    | NULL                                | pierre_neptune@nysd.uscourts.gov     |           NULL |      NULL |
|      30 | katelynn  | NULL                                | katelynn_perry@nysd.uscourts.gov     |           NULL |      NULL |
+---------+-----------+-------------------------------------+--------------------------------------+----------------+-----------+
23 rows in set (1.96 sec)

*/
