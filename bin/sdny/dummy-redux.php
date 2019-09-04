#!/usr/bin/env php
<?php
/*

*/
$sql = 'INSERT INTO event_types (SELECT * FROM office.event_types WHERE id IN (13,15,18,19,20,23,24,26,30,36,50,51,5))';
require __DIR__.'/../../vendor/autoload.php';
if (!isset($argv[1])) {
    exit(sprintf("usage: %s <target-dummy-database> [source-database]\n",basename(__FILE__)));
} else {
    $dummy_database = $argv[1];
}
$source_database = isset($argv[2]) ? $argv[2] : 'office';

// echo "using target database '$dummy_database', importing from '$source_database'\n";
// echo "connecting...\n";

$config_file = getenv('HOME').'/.my.cnf';
$config = parse_ini_file($config_file);
try {
    $pdo_dummy = new \PDO("mysql:host=localhost;dbname=$dummy_database",$config['user'],$config['password'],[
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
    ]);
    $pdo_source =  new \PDO("mysql:host=localhost;dbname=$source_database",$config['user'],$config['password'],[
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
    ]);
} catch (\Exception $e) {
    exit("connection failed. ".$e->getMessage() . "\n");
}

$dummy_judge_ids = $pdo_dummy->query('SELECT id from judges')->fetchAll(PDO::FETCH_COLUMN);
$number_of_judges = count($dummy_judge_ids);
//echo $number_of_judges,"\n";exit();
// select our $number_of_judges most popular judges
$judge_query = "SELECT j.id, j.lastname, COUNT(e.id) events
FROM people j JOIN events e ON e.judge_id = j.id JOIN languages l ON e.language_id = l.id
JOIN $dummy_database.languages dummy_langs ON dummy_langs.name = l.name
WHERE docket <> '' AND e.date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
GROUP BY j.id ORDER BY events desc limit $number_of_judges";
//exit($judge_query);
$judge_ids = $pdo_source->query($judge_query)->fetchAll(PDO::FETCH_COLUMN);
//$judge_map = array_combine($judge_ids,$dummy_judge_ids);
//print_r($judge_ids); exit();
$dummy_langs = $pdo_dummy->query('SELECT name, id from languages')->fetchAll(PDO::FETCH_KEY_PAIR);

/** this needs work here... */
$event_types = $pdo_dummy->query('select et.id dummy_id, et.name dummy_name, oet.id o_id from event_types et JOIN office.event_types oet ON et.name = oet.name order by o_id')
    ->fetchAll(PDO::FETCH_ASSOC);
//print_r($event_types);//exit();
$type_map = array_combine(
    array_column($event_types, 'o_id'),
    array_column($event_types, 'dummy_id')
);
//print_r($type_map);
$insert_sql =
'INSERT INTO events (
    language_id,
    judge_id,
    submitter_id,
    location_id,
    date,
    time,
    end_time,
    docket,
    comments,
    admin_comments,
    created,
    modified,
    event_type_id,
    created_by_id,
    anonymous_judge_id,
    anonymous_submitter_id,
    cancellation_reason_id,
    modified_by_id,
    submission_date,
    submission_time,
    deleted)
VALUES
    (:id,
    :language_id,
    :judge_id,
    :submitter_id,
    :location_id,
    :date,
    :time,
    :end_time,
    :docket,
    :comments,
    :admin_comments,
    :created,
    :modified,
    :event_type_id,
    :created_by_id,
    :anonymous_judge_id,
    :anonymous_submitter_id,
    :cancellation_reason_id,
    :modified_by_id,
    :submission_date,
    :submission_time,
    :deleted)';
$str_judge_ids = implode(',',$judge_ids);
// $str_event_type_ids = implode(',',array_keys($event_types));
// exit($str_event_type_ids);
$generic_bail_id = $pdo_dummy->query('SELECT id FROM event_types WHERE name LIKE "bail%"')->fetch(PDO::FETCH_COLUMN);
$dummy_types = $pdo_dummy->query('SELECT name,id FROM event_types')->fetchAll(PDO::FETCH_KEY_PAIR);

/*
print_r($dummy_types); exit;
[appt/subst of counsel] => 52
   [arraignment] => 6
   [atty/client interview] => 2
   [bail hearing] => 9
   [bond] => 13
   [competency hearing] => 15
   [conference] => 1
   [Curcio hearing] => 18
   [deferred prosecution] => 19
   [detention hearing] => 20
   [document translation] => 11
   [Fatico] => 23
   [Habeas] => 24
   [identity hearing] => 26
   [motions/oral argument] => 30
   [plea] => 4
   [presentment] => 5
   [pretrial services intake] => 12
   [pro se (civil)] => 36
   [probation interview] => 7
   [probation supervision interview] => 53
   [PTS supervision interview] => 54
   [sentence] => 3
   [suppression hearing] => 10
   [trial] => 8
   [vop hearing] => 50
   [vsr hearing] => 51

 */

$event_select =
    "SELECT e.*,l.name language, t.name event_type,
    dummy_langs.id AS dummy_lang_id,
    COALESCE(j.lastname, aj.name) judge
    FROM events e
    JOIN event_types t ON t.id = e.event_type_id
    LEFT JOIN people j ON j.id = e.judge_id
    LEFT JOIN anonymous_judges aj ON e.anonymous_judge_id = aj.id
    LEFT JOIN locations aj_locations ON aj.default_location_id = aj_locations.id
    JOIN languages l ON e.language_id = l.id
    JOIN $dummy_database.languages dummy_langs ON dummy_langs.name = l.name
    WHERE e.date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
    AND (e.judge_id IN ($str_judge_ids) OR (aj.name = 'magistrate' AND aj_locations.name = '5A'))
    AND t.name NOT REGEXP 'civil$|^telephone |^agents|atty/other|unspecified|settlement|^sight|court staff|^AUSA'
    AND e.docket <> '' ORDER BY e.created";
$stmt = $pdo_source->prepare($event_select);
$stmt->execute();
$count = $stmt->rowCount();
echo ("total: $count\n");
while ($e = $stmt->fetch()) {

    if (! isset($type_map[$e->event_type_id])) {
        if (preg_match('/^bail/', $e->event_type)) {
            // echo "adding $e->event_type...\n";
            $type_map[$e->event_type_id] = $generic_bail_id;
        } elseif (preg_match('/suppression/',$e->event_type)) {
            // echo "adding $e->event_type...\n";
            $dummy_id = key(preg_grep('/suppression/',array_keys($dummy_types)));
            $type_map[$e->event_type_id] = $dummy_id;
        } elseif (preg_match('/^pretrial services/',$e->event_type)) {
            $dummy_id = key(preg_grep('/^pretrial services/',array_keys($dummy_types)));
            $type_map[$e->event_type_id] = $dummy_id;
        } else {
            echo "can't map event-type: $e->event_type\n";
        }
    }
    $params['event_type_id'] = $type_map[$e->event_type_id];
    $params = ['language_id' => $e->dummy_lang_id];
    $params['judge_id'] = $e->judge_id ?: null;
    $params['anonymous_judge_id'] = $e->anonymous_judge_id ?: null;
    $params['comments'] = '';
    $params['admin_comments'] = '';
    foreach(['date','time','docket','created','modified',
    'submission_date','submission_time',
    'deleted', 'end_time'] as $field) {
        $params[$field] = $e->$field;
    }
    // still to do:
    /*
    created_by_id,
    submitter_id,
    location_id,
    created_by_id,
    modified_by_id,
    anonymous_submitter_id,
     */
}
exit(0);
