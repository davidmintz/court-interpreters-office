#!/usr/bin/env php
<?php
$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);
$old_db = new PDO('mysql:host=interpreters;dbname=interpreters', 'interpreters', 'ga%cker99',[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);

/* old => new */
$event_types = json_decode(file_get_contents('./event-type-map.json'),true);
$db->exec('DELETE FROM requests');

$requests_query = $old_db->query("SELECT * FROM requests ORDER BY id");

$insert = $db->prepare(
    'INSERT INTO REQUESTS VALUES(
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
while ($r = $requests_query->fetch(\PDO::FETCH_OBJ)) {
    $params = ['id'=>$r->id, 'date'=>$r->date,'time'=>$r->time,
        'language_id' => $r->language_id,
        'docket' => $r->docket,
        'created'=> $r->created,
    ];
    if (in_array($r->judge_id,array_keys($pseudo_judges))) {
        $params['anonymous_judge_id'] = $pseudo_judges[$r->judge_id];
        $params['judge_id'] = null;
    } else {
        if (! key_exists($r->judge_id,$judges)) {
            print_r($r);
            $shit++;
            continue;
        }
        $params['judge_id'] = $judges[$r->judge_id];
        $params['anonymous_judge_id'] = null;
    }

    $params = [];
    //print_r($r);
}
exit("OK shit happens $shit times\n");
