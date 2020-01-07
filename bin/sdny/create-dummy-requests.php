<?php
// require __DIR__.'/../../vendor/autoload.php';
// use Laminas\Log\Logger;
// use Laminas\Log\Writer\Stream as FileWriter;
// $log = new Logger();
// $log->addWriter(new FileWriter(__DIR__.'/log.dummy-data','a'));
//
// if (!isset($argv[1])) {
//     exit(sprintf("usage: %s <target-dummy-database> [source-database]\n",basename(__FILE__)));
// } else {
//     $dummy_database = $argv[1];
// }
// $source_database = isset($argv[2]) ? $argv[2] : 'office';
//
// $config_file = getenv('HOME').'/.my.cnf';
// $config = parse_ini_file($config_file);
// try {
//     $pdo_dummy = new \PDO("mysql:host=localhost;dbname=$dummy_database",$config['user'],$config['password'],[
//         \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
//     ]);
//     $pdo_source =  new \PDO("mysql:host=localhost;dbname=$source_database",$config['user'],$config['password'],[
//         \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
//     ]);
// } catch (\Exception $e) {
//     exit("connection failed. ".$e->getMessage() . "\n");
// }

$judge_map = json_decode(file_get_contents(__DIR__.'/judge-map.json'),true);

$get_another_submitter_by_judge = $pdo_dummy->prepare(
    'SELECT u.id FROM users u JOIN people p ON u.person_id = p.id
    JOIN clerks_judges cj ON cj.user_id = u.id
    WHERE p.id <> :submitter_id AND cj.judge_id = :judge_id LIMIT 1'
);

$request_query = $pdo_dummy->prepare('select r.*,
em.dummy_id AS dummy_event_id,
e.submitter_id dummy_submitter_id,
e.location_id AS dummy_location_id,
e.event_type_id AS dummy_type_id,
e.id AS dummy_event_id,
e.language_id AS dummy_language_id,
mod_by_p.id AS req_mod_by_person_id,
j.lastname dummy_judge,
s.lastname submitter,
c.id submitter_user_id
FROM office.requests r
LEFT JOIN office.users mod_by_u ON r.modified_by_id = mod_by_u.id
LEFT JOIN office.people mod_by_p ON mod_by_u.person_id = mod_by_p.id
LEFT JOIN office.events oe ON r.event_id = oe.id
JOIN tmp_event_map em ON em.office_id = oe.id
LEFT JOIN events e ON em.dummy_id = e.id
LEFT JOIN people j ON e.judge_id = j.id
LEFT JOIN people s ON s.id = e.submitter_id
LEFT JOIN users c ON s.id = c.person_id');

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
        extra_json_data
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
printf("total requests: %s\n",$request_query->rowCount());
$shit = 0;
$inserts = 0;
while ($row = $request_query->fetch()) {
    $params = [];
    $judge_id = isset($judge_map[$row->judge_id]) ? $judge_map[$row->judge_id] : null;
    if (! $judge_id) {
        printf("$row->id: no equivalent to judge_id $row->judge_id, anon-judge is %s\n",$row->anonymous_judge_id);
        continue;
    }
    $params['judge_id'] = $judge_id;
    $params['anonymous_judge_id'] = null;
    $params['submitter_id'] = $submitter_map[$row->submitter_id];
    foreach(['id','date','time','docket','created','modified', 'pending', 'cancelled','extra_json_data'] as $field) {
        $params[$field] = $row->$field;
    }
    $params['comments'] = '';
    if (!$row->dummy_event_id) {
        echo "WTF??? no equivalent event was found\n";
        print_r($row);
        exit();
    }
    if ($row->req_mod_by_person_id != $row->submitter_id) {
        //printf("request %s: mod by other than its creator: $row->req_mod_by_person_id\n",$row->id);
        $get_another_submitter_by_judge->execute([
            'submitter_id'=>$params['submitter_id'],
            'judge_id' => $judge_id]);
        $mod_by = $get_another_submitter_by_judge->fetch(PDO::FETCH_COLUMN);
        if ($mod_by) {
            $params['modified_by_id'] = $mod_by;
        } else {
            $log->info("oops, no luck finding another user id ($row->dummy_judge)");
            $params['modified_by_id'] = $row->submitter_user_id;
            $shit++;
        }
    } else {
        $params['modified_by_id'] = $row->submitter_user_id;
    }
    if ($row->location_id) {
        $params['location_id'] = $row->dummy_location_id;
    } else {
        $params['location_id'] = null;
    }
    if (! $row->dummy_type_id) {
        echo "SHIT, no event type id?\n";
        print_r($params); exit;
    }
    $params['event_type_id'] = $row->dummy_type_id;
    $params['event_id'] = $row->dummy_event_id;
    $params['language_id'] = $row->dummy_language_id;
    try {
        $requests_insert->execute($params);
        $inserts++;
    } catch (\Exception $e) {
        print_r($params);
        exit($e->getMessage());
    }

}
echo "weird ones: $shit; completed: $inserts\n";
$pdo_dummy->exec(
    'INSERT INTO defendants_requests (SELECT de.defendant_id, r.id request_id
        FROM requests r JOIN events e ON r.event_id = e.id
        JOIN defendants_events de ON de.event_id = e.id)');
return true;
