#!/usr/bin/env php
<?php
$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);

/* old => new */
$event_types = json_decode(file_get_contents(__DIR__.'/event-type-map.json'),true);
$event_locations = json_decode(file_get_contents(__DIR__.'/event-location-map.json'),true);

$db->exec('DELETE FROM requests');

$requests_query = $db->query(
    "SELECT r.*, e.id new_event_id, e.location_id
    FROM dev_interpreters.requests r
    LEFT JOIN office.events e ON r.event_id = e.id
    ORDER BY id"
);
$total = $db->query(
    'SELECT COUNT(*) FROM dev_interpreters.requests'
)->fetchColumn();

$insert = $db->prepare(
    'INSERT INTO requests VALUES(
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
        :pending
    )'
);
$judges = $db->query(
    'SELECT oj.judge_id, j.id FROM dev_interpreters.judges AS oj, people j
    WHERE oj.lastname = j.lastname AND j.discr = "judge"
    AND oj.firstname = j.firstname'
)->fetchAll(\PDO::FETCH_KEY_PAIR);
// $old => $new
$shit = 0;
$pseudo_judges = [
    75 => 3,
    82 => 2,
];

// map the old request_users ids to new user and person ids
$users = [];
$res = $db->query(
    'SELECT ru.id, u.id user_id,u.person_id FROM users u JOIN people p ON u.person_id = p.id
    JOIN dev_interpreters.request_users ru ON ru.email = p.email'
);

while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
    $id = $row['id'];
    unset($row['id']);
    $users[$id] = $row;
}

$count = 0;

while ($r = $requests_query->fetch(\PDO::FETCH_OBJ)) {
    // these can be copied directly, no translation required
    $params = [
        'id'=>$r->id,
        'date'=>$r->date,
        'time'=>$r->time,
        'language_id' => $r->language_id,
        'docket' => $r->docket,
        'created'=> $r->created,
        'comments' => $r->comments,
        'modified' => $r->modified,
    ];
    // the judge
    if (key_exists($r->judge_id,$pseudo_judges)) {

        $params['anonymous_judge_id'] = $pseudo_judges[$r->judge_id];
        $params['judge_id'] = null;

    } else {
        if (! key_exists($r->judge_id,$judges)) {
            echo "request id $r->id: judge id $r->judge_id not found in \$judges array keys\n";
            $shit++;
            continue;
        }
        $params['judge_id'] = $judges[$r->judge_id];
        $params['anonymous_judge_id'] = null;
    }

    // next:  the users/metadata
    // the 'created_by' will become the 'submitter', and refers to a person
    if (! key_exists($r->created_by, $users)) {
        $shit++;
        echo "request id $r->id: created_by id $r->created_by not found in \$users array keys\n";
        continue;
    }
    if (! key_exists($r->last_modified_by, $users)) {
        $shit++;
        echo "request id $r->id: last_modified_by id $r->last_modified_by not found in \$users array keys\n";
        continue;
    }
    $params['submitter_id'] = $users[$r->created_by]['person_id'];
    $params['modified_by_id'] = $users[$r->last_modified_by]['user_id'];

    // the event type
    if (! key_exists($r->proceeding_id,$event_types)) {
        $shit++;
        echo "request id $r->id: proceeding id $r->proceeding_id not found in \$event_types array\n";
        continue;
    }
    $params['event_type_id'] = $event_types[$r->proceeding_id];

    // finally, `event_id`, `pending` and `location_id`
    $params['pending'] = 'pending' == $r->status ? 1 : 0;

    if ($r->new_event_id) {
        $params['event_id'] = $r->new_event_id;
        if ($r->location_id) {
            $params['location_id'] = $r->location_id;
        } else {
            $params['location_id'] = null;
        }
    } else {
        $params['event_id'] = null;
        // make an attempt to figure out location
        if (key_exists($r->proceeding_id,$event_locations)) {
            $params['location_id'] = $event_locations[$r->proceeding_id];
        } else {
            $params['location_id'] = null;
        }
    }
    try {
        $insert->execute($params);
        printf("inserted %d of %d\r",++$count,$total);

    } catch (\Exception $e) {
        echo "fuck, ",$e->getMessage(),"\nyou have ", count($params),
            " parameters:" ;
        print_r($params);
        exit();
    }
}
echo "\n";
try {
    $stmt = $db->prepare('INSERT INTO defendants_requests (defendant_id, request_id)
    (SELECT defendant_id, request_id FROM dev_interpreters.defendants_requests)');
    $stmt->execute();
    printf("inserted %d rows into defendants_requests\n",$stmt->rowCount());
} catch (\Exception $e) {
    printf("shit: %s\n",$e->getMessage());
    exit();
}
exit("$count request records OK, shit happens $shit times\n");
