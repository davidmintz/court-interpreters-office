#!/usr/bin/env php
<?php
require __DIR__.'/../../vendor/autoload.php';
use Zend\Log\Logger;
use Zend\Log\Writer\Stream as FileWriter;
$log = new Logger();
$log->addWriter(new FileWriter(__DIR__.'/log.dummy-data','w'));

if (!isset($argv[1])) {
    exit(sprintf("usage: %s <target-dummy-database> [source-database]\n",basename(__FILE__)));
} else {
    $dummy_database = $argv[1];
}
$source_database = isset($argv[2]) ? $argv[2] : 'office';

// echo  "using target database '$dummy_database', importing from '$source_database'\n";
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

$judge_map = json_decode(file_get_contents(__DIR__.'/judge-map.json'),true);
$submitter_map = json_decode(file_get_contents(__DIR__.'/submitter-map.json'),true);
$user_map = json_decode(file_get_contents(__DIR__.'/user-map.json'),true);
$event_map = json_decode(file_get_contents(__DIR__.'/event-map.json'),true);
$str_judge_ids = implode(',',array_keys($judge_map));

$request_query = $pdo_dummy->prepare('select r.*
FROM office.requests r
LEFT JOIN office.events oe ON r.event_id = oe.id
JOIN tmp_event_map em ON em.office_id = oe.id
');

$pdo_dummy->exec('DELETE FROM defendants_requests');
$pdo_dummy->exec('DELETE FROM requests');

$requests_insert = $pdo_dummy->prepare(
    'INSERT INTO requests (
        id,
        `date`,
        `time`,
        judge_id,
        anonymous_judge_id,
        event_type_id,
        language_id,
        docket,
        location_id,
        submitter_id,
        created,
        modified,
        modified_by_id,
        comments,
        event_id,
        pending,
        cancelled,
        extra_json_data,
    ) VALUES (
        :id,
        :date,
        :time,
        :judge_id,
        :anonymous_judge_id,
        :event_type_id,
        :language_id,
        :docket,
        :location_id,
        :submitter_id,
        :created,
        :modified,
        :modified_by_id,
        :comments,
        :event_id,
        :pending,
        :cancelled,
        :extra_json_data
    )'
);

$request_query->execute();
while ($row = $request_query->fetch()) {
    $params = [];
    $judge_id = isset($judge_map[$row->judge_id]) ? $judge_map[$row->judge_id] : null;
    if (! $judge_id) {
        printf("$row->id: no equivalent to judge_id $row->judge_id, anon-judge is %s\n",$row->anonymous_judge_id);
        continue;
    }
    $params['submitter_id'] = $submitter_map[$row->submitter_id];
    //$params['judge_id'] = isset($judge_map[$row->judge_id]) ? $judge_map[$row->judge_id] : null;
    foreach(['id','date','time','docket','created','modified', 'pending', 'cancelled','extra_json_data'] as $field) {
        $params[$field] = $row->$field;
    }
    $params['comments'] = '';

}
exit;

// $sql = "SELECT r.* FROM requests r LEFT JOIN events e ON r.event_id = e.id
// JOIN event_types t ON t.id = r.event_type_id
// JOIN languages l ON r.language_id = l.id
// JOIN $dummy_database.languages dummy_langs ON dummy_langs.name = l.name
// WHERE (r.date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
//     OR r.event_id IN ($evt_ids))
// AND r.judge_id IN ($str_judge_ids)
// AND t.name NOT REGEXP 'civil$|^telephone |^agents|atty/other|unspecified|settlement|^sight|court staff|^AUSA'";
//
// $requests_query = $pdo_source->prepare($sql);
// $requests_query->execute();
// $total = $requests_query->rowCount();
// $n = 0;
// while ($row = $requests_query->fetch()) {
//     foreach([] as $field){
//
//     }
//     if (key_exists($row->event_id,$event_map)) {
//         //printf("request id #%s exists as event id %s\n",$row->id,$event_map[$row->event_id]);
//         $n++;
//     }
// }
// echo "$n of $total\n";

// we repeat ourself, but...
// $dummy_judge_ids = $pdo_dummy->query('SELECT id from judges')->fetchAll(PDO::FETCH_COLUMN);
// $number_of_judges = count($dummy_judge_ids);
//
// // select our $number_of_judges most popular judges
// $judge_query = "SELECT j.id, j.lastname, COUNT(e.id) events
// FROM people j JOIN events e ON e.judge_id = j.id JOIN languages l ON e.language_id = l.id
// JOIN $dummy_database.languages dummy_langs ON dummy_langs.name = l.name
// WHERE docket <> '' AND e.date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
// GROUP BY j.id ORDER BY events desc limit $number_of_judges";
// $judge_ids = $pdo_source->query($judge_query)->fetchAll(PDO::FETCH_COLUMN);
// $str_judge_ids = implode(',',$judge_ids);

// json data created by create-dummy-events.php
$event_id_map = json_decode(file_get_contents(__DIR__.'/event-map.json'),true);
