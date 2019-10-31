#!/usr/bin/env php
<?php

$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);
$old_db = new PDO('mysql:host=localhost;dbname=dev_interpreters', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);

const SATURDAY_DUTY = 1;
const SCHEDULING_MANAGER = 2;
const STAFF_COURT_INTERPRETER = 1;

$tasks = [
    SATURDAY_DUTY => [
        'Saturday duty','','WEEK'
    ],
    SCHEDULING_MANAGER => [
        'Scheduling','','WEEK'
    ],
];
// populate "tasks" table
$sql = 'INSERT INTO tasks (id, name, description, duration) VALUES (:id,:name,:description,:duration)';
$task_ins = $db->prepare($sql);
foreach ($tasks as $id => $data) {
    try {
        $params = ['id'=>$id, 'name'=>$data[0],'description'=>$data[1],'duration'=>$data[2]];
        $task_ins->execute($params);
    } catch (\PDOException $e) {
        if (23000 != $e->getCode()) {
            throw $e;
        }
    }
}

// select task_rotation data

$sql = 'SELECT * FROM task_rotations ORDER BY task_id, start_date';
$old_rotations = $old_db->query($sql);

$rotation_ins = $db->prepare(
    'INSERT INTO rotations (id, task_id, start_date) VALUES (:id,:task_id,:start_date)'
);
/*
+----------------+----------------------+------+-----+---------+-------+
| Field          | Type                 | Null | Key | Default | Extra |
+----------------+----------------------+------+-----+---------+-------+
| rotation_id    | smallint(5) unsigned | NO   | PRI | NULL    |       |
| person_id      | smallint(5) unsigned | NO   | PRI | NULL    |       |
| rotation_order | smallint(5) unsigned | NO   |     | NULL    |       |
+----------------+----------------------+------+-----+---------+-------+
*/
$rotation_member_ins = $db->prepare(
    'INSERT INTO task_rotation_members (rotation_id, person_id, rotation_order)
    VALUES (:rotation_id,:person_id,:rotation_order)'
);

$i = 0;
$rotations_inserted = 0;
$rotation_members_inserted = 0;
$sql = 'SELECT lower(p.firstname), p.id FROM people p WHERE p.hat_id = '.STAFF_COURT_INTERPRETER;
// name => id
$person_map = $db->query($sql)->fetchAll(\PDO::FETCH_KEY_PAIR);

while ($row = $old_rotations->fetch(\PDO::FETCH_OBJ)) {
    try {
        $rotation_ins->execute(['id'=>++$i,'task_id'=>$row->task_id, 'start_date'=>$row->start_date]);
        $rotations_inserted++;
    } catch (\PDOException $e) {
        if (23000 != $e->getCode()) {
            throw $e;
        }
    }
    $names = json_decode($row->rotation);
    foreach($names as $order=>$name) {
        $params = [
            'rotation_id'=>$i,
            'rotation_order' => $order,
            'person_id' => $person_map[strtolower($name)],
        ];
        try {
            $rotation_member_ins->execute($params);
            $rotation_members_inserted++;
        } catch (\PDOException $e) {
            if (23000 != $e->getCode()) {
                throw $e;
            }
        }
    }

}
printf("rotations imported: %d\n",$rotations_inserted);
printf("rotation_members imported: %d\n",$rotation_members_inserted);

$old_subs = $old_db->query('SELECT * FROM rotation_substitutions');
$sub_ins = $db->prepare('INSERT INTO rotation_substitutions (id, task_id, person_id, date, duration)
    VALUES (:id, :task_id, :person_id, :date, :duration)');
$i = 0;
$subs_inserted = 0;
while ($row = $old_subs->fetch(\PDO::FETCH_OBJ)) {
    $params = [
        'id'=>++$i,
        'task_id'=>$row->task_id,
        'person_id' =>$person_map[strtolower($row->who)],
        'date' => $row->date,
        'duration' => $row->duration,
    ];
    try {
        $sub_ins->execute($params);
        $subs_inserted++;
    } catch (\PDOException $e) {
        if (23000 != $e->getCode()) {
            throw $e;
        }
    }
}
printf("substitutions imported: %d\n",$subs_inserted);


/*
mysql> explain rotation_substitutions;
+-----------+----------------------+------+-----+---------+----------------+
| Field     | Type                 | Null | Key | Default | Extra          |
+-----------+----------------------+------+-----+---------+----------------+
| id        | smallint(5) unsigned | NO   | PRI | NULL    | auto_increment |
| task_id   | smallint(5) unsigned | YES  | MUL | NULL    |                |
| person_id | smallint(5) unsigned | YES  | MUL | NULL    |                |
| date      | date                 | NO   |     | NULL    |                |
| duration  | varchar(5)           | NO   |     | NULL    |                |
+-----------+----------------------+------+-----+---------+----------------+
5 rows in set (0.00 sec)

mysql> explain dev_interpreters.rotation_substitutions;
+----------+----------------------------+------+-----+---------+----------------+
| Field    | Type                       | Null | Key | Default | Extra          |
+----------+----------------------------+------+-----+---------+----------------+
| id       | mediumint(8) unsigned      | NO   | PRI | NULL    | auto_increment |
| date     | date                       | NO   | MUL | NULL    |                |
| task_id  | mediumint(8) unsigned      | NO   |     | NULL    |                |
| duration | enum('WEEK','DAY','MONTH') | NO   |     | DAY     |                |
| who      | varchar(30)                | NO   |     | NULL    |                |
+----------+----------------------------+------+-----+---------+----------------+
*/


/*
mysql> explain task_rotations;
+------------+-----------------------+------+-----+---------+-------+
| Field      | Type                  | Null | Key | Default | Extra |
+------------+-----------------------+------+-----+---------+-------+
| task_id    | mediumint(8) unsigned | NO   | PRI | NULL    |       |
| start_date | date                  | NO   | PRI | NULL    |       |
| rotation   | varchar(300)          | YES  |     | NULL    |       |
+------------+-----------------------+------+-----+---------+-------+
3 rows in set (0.00 sec)
mysql> explain rotation_substitutions;
+-----------+----------------------+------+-----+---------+----------------+
| Field     | Type                 | Null | Key | Default | Extra          |
+-----------+----------------------+------+-----+---------+----------------+
| id        | smallint(5) unsigned | NO   | PRI | NULL    | auto_increment |
| task_id   | smallint(5) unsigned | YES  | MUL | NULL    |                |
| person_id | smallint(5) unsigned | YES  | MUL | NULL    |                |
| date      | date                 | NO   |     | NULL    |                |
| duration  | varchar(5)           | NO   |     | NULL    |                |
+-----------+----------------------+------+-----+---------+----------------+
5 rows in set (0.00 sec)

mysql> explain dev_interpreters.rotation_substitutions;
+----------+----------------------------+------+-----+---------+----------------+
| Field    | Type                       | Null | Key | Default | Extra          |
+----------+----------------------------+------+-----+---------+----------------+
| id       | mediumint(8) unsigned      | NO   | PRI | NULL    | auto_increment |
| date     | date                       | NO   | MUL | NULL    |                |
| task_id  | mediumint(8) unsigned      | NO   |     | NULL    |                |
| duration | enum('WEEK','DAY','MONTH') | NO   |     | DAY     |                |
| who      | varchar(30)                | NO   |     | NULL    |                |
+----------+----------------------------+------+-----+---------+----------------+
5 rows in set (0.00 sec)
*/

exit(0);
