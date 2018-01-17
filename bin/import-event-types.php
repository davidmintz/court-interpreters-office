#!/usr/bin/env php
<?php
/**
 *
 */

$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);
$old_db = new PDO('mysql:host=localhost;dbname=dev_interpreters', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);
/*
 mysql> explain event_types;
+-------------+----------------------+------+-----+---------+----------------+
| Field       | Type                 | Null | Key | Default | Extra          |
+-------------+----------------------+------+-----+---------+----------------+
| id          | smallint(5) unsigned | NO   | PRI | NULL    | auto_increment |
| category_id | smallint(5) unsigned | NO   | MUL | NULL    |                |
| name        | varchar(60)          | NO   | UNI | NULL    |                |
| comments    | varchar(150)         | NO   |     |         |                |
+-------------+----------------------+------+-----+---------+----------------+
 */

$event_categories = $db->query('SELECT category, id FROM event_categories')->fetchAll(\PDO::FETCH_KEY_PAIR);
$event_type_insert = $db->prepare('INSERT INTO event_types (name, category_id,comments) VALUES(:name,:category_id,:comments)');

$event_types_query = $old_db->query('SELECT proceeding_id, type, category, comments, display FROM proceedings ORDER BY type');

// one canonical kind of probation PSI interview, etc
// i.e., no variations based on location
foreach( [
    'probation PSI interview' => 'ID_PROBATION_PSI',
    "PTS supervison interview"  => 'ID_PTS_SUPERVISION',
    'probation supervision interview' => 'ID_PROBATION_SUPERVISION',] as $type => $varname) {
    try {
     $event_type_insert->execute([
        'name' => $type,'category_id' => $event_categories['out'],
         'comments' => '',
     ]);
     ${$varname} = $db->query('SELECT LAST_INSERT_ID()')->fetchColumn(); 
    } catch (PDOException $e) {
         if ($e->getCode() == 23000) {
             echo("'$type' already exists\n");
             ${$varname} = $db->query("SELECT id FROM event_types WHERE name = '$type'")->fetchColumn();             
             continue;
         } else {
             throw $e;
         }     
    }    
}
// old => new
$event_type_map = [];
while ($type = $event_types_query->fetch(PDO::FETCH_ASSOC)) {
    if (stristr($type['type'],'probation')) {
        $this_id = stristr($type['type'],'supervision') ? $ID_PROBATION_SUPERVISION :  $ID_PROBATION_PSI;
        $event_type_map[$type['proceeding_id']] = $this_id;
        //or 1 === preg_match('/PTS.+supervision/i', $type['type'])) {
        printf("skipping event type: %s\n",$type['type']);
        continue;
    }
    if ( 1 === preg_match('/PTS.+supervision/i', $type['type'])) {
        $event_type_map[$type['proceeding_id']] = $ID_PTS_SUPERVISION;
        printf("skipping event type: %s\n",$type['type']);
        continue;
    }   
    if (! $type['display']) {
        // it is deprecated. make a note so we can deal with it later
        if ($type['comments']) {
            $type['comments'] .= "\n\n";
        }
        $type['comments']  .= "this type is DEPRECATED (display = 0)";
    }
    if ('n/a' == $type['category']) {
        $type['category'] = 'not applicable';
    }
    try {
        printf("inserting '%s' ... ",$type['type']);
        $event_type_insert->execute([
            'name' => $type['type'],
            'category_id' => $event_categories[$type['category']],
            'comments' => $type['comments']
        ]);
        $event_type_map[$type['proceeding_id']] =$db->query('SELECT LAST_INSERT_ID()')->fetchColumn();
        echo "OK\n";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // to do: prepare, parameterize!
            $event_type_map[$type['proceeding_id']] = $db->query("SELECT id FROM event_types WHERE name = '{$type['type']}'")->fetchColumn(); 
            printf("'%s' is a duplicate, moving on\n",$type['type']);
        } else {
            printf("insertion of '%s' FAILED: %s\n",$type['type'], $e->getMessage());
        }        
    }    
}
$map = json_encode($event_type_map);
$path = __DIR__ . '/event-type-map.json';
$result = file_put_contents($path,$map);
if (false === $result) {
    echo "SHIT. failed writing \$event_type_map to $path\n";
    exit(1);
} elseif (0 === $result) {
    echo "SHIT. no data contained in our \$event_type_map";
    exit(1);
}
exit(0);




