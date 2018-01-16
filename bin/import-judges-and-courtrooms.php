<?php
/**
 * imports judges and courtrooms using JSON data provided as stdin
 */

$json = file_get_contents("php://stdin");
$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);

$data = json_decode($json,JSON_OBJECT_AS_ARRAY);
if (! $data) {
    exit("failed decoding json from stdin\n");
}
const TYPE_COURTHOUSE = 7;
const TYPE_COURTROOM = 1;

$hat_id = $db->query('SELECT id from hats WHERE name = "Judge"')->fetchColumn();
$flavors = $db->query('SELECT flavor, id FROM judge_flavors')->fetchAll(PDO::FETCH_KEY_PAIR);

$courthouses =  $db->query('SELECT name,id FROM locations WHERE type_id = '.TYPE_COURTHOUSE)->fetchAll(PDO::FETCH_KEY_PAIR);

$courthouses['300 Quarropas'] = $courthouses['White Plains']; // alias


$person_insert = $db->prepare(
    'INSERT INTO people (hat_id,lastname,firstname,middlename,active,discr) 
        VALUES (:hat_id,:lastname,:firstname,:middlename,1, "judge")'
);

$judge_insert = $db->prepare(
        'INSERT INTO judges (id,default_location_id, flavor_id) '
        . 'VALUES (:id,:default_location_id,:flavor_id)');

$location_insert = $db->prepare(
    'INSERT INTO locations (type_id, parent_location_id,name,active) '
        . 'VALUES (:type_id,:parent_location_id,:name,1)'
);

$location_select = $db->prepare('SELECT id FROM locations WHERE name = :name and type_id = :type_id');

/*mysql> explain locations;
+--------------------+----------------------+------+-----+---------+----------------+
| Field              | Type                 | Null | Key | Default | Extra          |
+--------------------+----------------------+------+-----+---------+----------------+
| id                 | smallint(5) unsigned | NO   | PRI | NULL    | auto_increment |
| type_id            | smallint(5) unsigned | NO   | MUL | NULL    |                |
| parent_location_id | smallint(5) unsigned | YES  | MUL | NULL    |                |
| name               | varchar(60)          | NO   | MUL | NULL    |                |
| comments           | varchar(200)         | NO   |     |         |                |
| active             | tinyint(1)           | NO   |     | 1       |                |
+--------------------+----------------------+------+-----+---------+----------------+
*/

//[Swain, Laura Taylor] => 17C, 500 Pearl
//$USDJ = $data['District'];
foreach ($data as $flavor) {
    
    foreach ($flavor as $name => $location) {

        list($lastname,$given_names) = preg_split('/, +/',$name);    
        if (strstr($given_names,' ')) {
            list($firstname,$middlename) = preg_split('/ +/',$given_names);
        } else {
            $firstname = $given_names; 
            $middlename = '';
        }
        list($courtroom, $courthouse) = preg_split('/, +/',$location);
            //printf("courtroom: %s, courthouse %s\n",$courtroom, $courthouse);
            //continue;

        try {

            $location_insert->execute([
                ':type_id'=>  TYPE_COURTROOM,
                ':name' => $courtroom,
                ':parent_location_id' => $courthouses[$courthouse],
            ]);
            $location_id =  $db->query('SELECT last_insert_id()')->fetchColumn();
            echo "inserted new location $courtroom with id $location_id\n";

        } catch (Exception $e) {

            printf("caught %s: error code %d\n%s\n",get_class($e),$e->getCode(),$e->getMessage());

            $location_select->execute(['name'=>$courtroom,'type_id'=>TYPE_COURTROOM]);   //.TYPE_COURTROOM);
            $location_id = $location_select->fetchColumn();
            echo "found location ($courtroom) id $location_id already existing\n";
        }

        try {
            $person_insert->execute(
                compact('hat_id','lastname','firstname','middlename')
            );
            $id = $db->query('SELECT last_insert_id()')->fetchColumn();
            printf("judge %s added to people id %s\n",$lastname,$id);
            $judge_insert->execute([
                ':id'=>$id,
                ':default_location_id'=>$location_id,
                ':flavor_id' => $flavors['USDJ'],
             ]);

        } catch (Exception $e) {
            echo "insert threw exception: ".$e->getMessage();
            echo "\n";
        }
    }
}
/*
 mysql> explain people;
+--------------+----------------------+------+-----+---------+----------------+
| Field        | Type                 | Null | Key | Default | Extra          |
+--------------+----------------------+------+-----+---------+----------------+
| id           | smallint(5) unsigned | NO   | PRI | NULL    | auto_increment |
| hat_id       | smallint(5) unsigned | NO   | MUL | NULL    |                |
| email        | varchar(50)          | YES  | MUL | NULL    |                |
| lastname     | varchar(50)          | NO   |     | NULL    |                |
| firstname    | varchar(50)          | NO   |     | NULL    |                |
| middlename   | varchar(50)          | NO   |     |         |                |
| office_phone | varchar(20)          | NO   |     |         |                |
| mobile_phone | varchar(20)          | NO   |     |         |                |
| active       | tinyint(1)           | NO   |     | NULL    |                |
| discr        | varchar(255)         | NO   |     | NULL    |                |
+--------------+----------------------+------+-----+---------+----------------+
10 rows in set (0.00 sec)

mysql> explain judges;
+---------------------+----------------------+------+-----+---------+-------+
| Field               | Type                 | Null | Key | Default | Extra |
+---------------------+----------------------+------+-----+---------+-------+
| id                  | smallint(5) unsigned | NO   | PRI | NULL    |       |
| default_location_id | smallint(5) unsigned | YES  | MUL | NULL    |       |
| flavor_id           | int(11)              | NO   | MUL | NULL    |       |
+---------------------+----------------------+------+-----+---------+-------+
3 rows in set (0.00 sec)

mysql> explain locations;
+--------------------+----------------------+------+-----+---------+----------------+
| Field              | Type                 | Null | Key | Default | Extra          |
+--------------------+----------------------+------+-----+---------+----------------+
| id                 | smallint(5) unsigned | NO   | PRI | NULL    | auto_increment |
| type_id            | smallint(5) unsigned | NO   | MUL | NULL    |                |
| parent_location_id | smallint(5) unsigned | YES  | MUL | NULL    |                |
| name               | varchar(60)          | NO   | MUL | NULL    |                |
| comments           | varchar(200)         | NO   |     |         |                |
| active             | tinyint(1)           | NO   |     | 1       |                |
+--------------------+----------------------+------+-----+---------+----------------+
6 rows in set (0.00 sec)


 */