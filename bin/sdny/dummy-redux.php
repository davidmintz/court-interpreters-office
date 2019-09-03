#!/usr/bin/env php
<?php

require __DIR__.'/../../vendor/autoload.php';
if (!isset($argv[1])) {
    exit(sprintf("usage: %s <target-dummy-database> [source-database]\n",basename(__FILE__)));
} else {
    $dummy_database = $argv[1];
}
$source_database = isset($argv[2]) ? $argv[2] : 'office';

// echo "using target database '$office_database', importing from '$interpreters_database'\n";
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

// select our $number_of_judges most popular judges
$judge_query = "SELECT j.id, j.lastname, COUNT(e.id) events
FROM people j JOIN events e ON e.judge_id = j.id JOIN languages l ON e.language_id = l.id
JOIN $dummy_database.languages dummy_langs ON dummy_langs.name = l.name
WHERE docket <> '' AND e.date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
GROUP BY j.id ORDER BY events desc limit $number_of_judges";
$judge_ids = $pdo_source->query($judge_query)->fetchAll(PDO::FETCH_COLUMN);
$judge_map = array_combine($judge_ids,$dummy_judge_ids);

$dummy_langs = $pdo_dummy->query('SELECT name, id from languages')->fetchAll(PDO::FETCH_KEY_PAIR);
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
    AND (e.judge_id IN ($str_judge_ids)
        OR (aj.name = 'magistrate' AND aj_locations.name = '5A'))
    AND e.docket <> '' ORDER BY e.created";
$stmt = $pdo_source->prepare($event_select);
$stmt->execute();
$count = $stmt->rowCount();
echo ("total: $count\n");
while ($e = $stmt->fetch()) {

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
    event_type_id,
    created_by_id,
    submitter_id,
    location_id,
    created_by_id,
    modified_by_id,
    anonymous_submitter_id,
     */
}
exit(0);
