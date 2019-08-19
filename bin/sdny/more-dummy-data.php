#!/usr/bin/env php
<?php
/**
 * prerequisites:
 *  cat sql/mysql-schema.sql sql/initial-data.sql sql/dummy_data.sql | mysql office_demo;
 *
 */
require __DIR__.'/../../vendor/autoload.php';
if (!isset($argv[1])) {
    exit(sprintf("usage: %s <database>\n",basename(__FILE__)));
} else {
    $database = $argv[1];
}


$config_file = getenv('HOME').'/.my.cnf';
$config = parse_ini_file($config_file);
$pdo = new \PDO("mysql:host=localhost;dbname=$database",$config['user'],$config['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
]);
// hash all the passwords

$hash = password_hash("boink",PASSWORD_DEFAULT);
$pdo->execute("UPDATE users SET password='$hash'");

// create a string of events from pretrial all the way through sentencing

$year = date('Y');
$mag_num = "$year-MAG-4321";
$docket = "$year-CR-1234";
$deft = $pdo->query('SELECT * from defendant_names WHERE surnames LIKE "Rodr%" LIMIT 1')->fetch();
$event_types = $pdo->query('SELECT name,id from event_types')->fetchAll(PDO::FETCH_KEY_PAIR);
$languages = $pdo->query('SELECT name,id from languages')->fetchAll(PDO::FETCH_KEY_PAIR);
$locations = $pdo->query('SELECT name,id from locations')->fetchAll(PDO::FETCH_KEY_PAIR);

$hats =  $pdo->query('SELECT name, id from hats')->fetchAll(PDO::FETCH_KEY_PAIR);
$defense_attys = $pdo->query('SELECT lastname, p.id from people p JOIN hats h ON p.hat_id = h.id WHERE h.name LIKE "%defense%"')->fetchAll(PDO::FETCH_KEY_PAIR);
$magistrate = $pdo->query(
    'SELECT id FROM anonymous_judges WHERE name = "magistrate"'
)->fetchColumn();

$spanish_contractors =  $pdo->query('SELECT DISTINCT lastname, p.id from people p
    JOIN interpreters_languages il ON p.id = il.interpreter_id
    WHERE il.language_id = '.$languages['Spanish'])->fetchAll(PDO::FETCH_KEY_PAIR);

$event_insert = $pdo->prepare(

    "INSERT INTO events (
        language_id,
        judge_id,
        submitter_id,
        location_id,
        `date`,
        `time`,
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
        deleted
    ) VALUES (
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
        :deleted
    )"
);
$interp_event_insert = $pdo->prepare('INSERT INTO interpreters_events (interpreter_id,event_id,created_by_id,created) VALUES (:interpreter_id, :event_id,:created_by_id,:created)');
$deft_event_insert = $pdo->prepare('INSERT INTO defendants_events (event_id, defendant_id) VALUES (:event_id, :defendant_id)');
$admin = $pdo->query(
    'SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name LIKE "admin%" LIMIT 1'
)->fetchColumn();

/* pretrial intake interview */

$last_monday = new \DateTime('last Monday');
$params = [
    'language_id' => $languages['Spanish'],
    'judge_id' => null,
    'submitter_id' => null,
    'location_id' => $locations['Pretrial Services'],
    'date' => $last_monday->format('Y-m-d'),
    'time' => '10:45:00',
    'end_time' => '11:22:00',
    'docket' => $mag_num,
    'comments' => '',
    'admin_comments' => '',
    'created' => (new \DateTime('last Monday 10:36 a.m.'))->format('Y-m-d H:i:s'),
    'modified' => (new \DateTime('last Monday 11:23 a.m.'))->format('Y-m-d H:i:s'),
    'event_type_id' => $event_types['pretrial services intake'],
    'created_by_id' => $admin,
    'modified_by_id' => $admin,
    'anonymous_judge_id' => $magistrate,
    'anonymous_submitter_id' => $hats['Pretrial'],
    'cancellation_reason_id' => null,
    'submission_date' => $last_monday->format('Y-m-d'),
    'submission_time' => '10:35:00',
    'deleted' => 0,
];

$pdo->beginTransaction();


$event_insert->execute($params);
$event_id = $pdo->lastInsertId();
$deft_event_insert->execute(['defendant_id'=>$deft['id'],'event_id'=>$event_id]);
$interp_event_insert->execute(
    [
        'interpreter_id' => $spanish_contractors['del Potro'],
        'event_id' => $event_id,
        'created' => (new \DateTime('last Monday 10:36 a.m.'))->format('Y-m-d H:i:s'),
        'created_by_id' => $admin,
    ]
);

/* atty-client interview */

$params['submitter_id'] = $defense_attys['Edelbaum'];
$params['anonymous_submitter_id'] = null;
$params['time'] = '11:45:00';
$params['end_time'] = '12:15:00';
$params['created'] = (new \DateTime('last Monday 11:32 a.m.'))->format('Y-m-d H:i:s');
$params['modified'] = (new \DateTime('last Monday 12:16 p.m.'))->format('Y-m-d H:i:s');
$params['submission_time'] = '11:29:00';
$params['location_id' ] = $locations['the holding cell'];
$params['event_type_id'] = $event_types['atty/client interview'];
$event_insert->execute($params);
$event_id = $pdo->lastInsertId();
$deft_event_insert->execute(['defendant_id'=>$deft['id'],'event_id'=>$event_id]);
$interp_event_insert->execute(
    [
        'interpreter_id' => $spanish_contractors['del Potro'],
        'event_id' => $event_id,
        'created' => (new \DateTime('last Monday 11:32 a.m.'))->format('Y-m-d H:i:s'),
        'created_by_id' => $admin,
    ]
);

/* presentment */

$params['submitter_id'] = null;
$params['anonymous_submitter_id'] = $hats['Magistrates'];
$params['time'] = '13:20:00';
$params['end_time'] = '13:26:00';
$params['created'] = (new \DateTime('last Monday 1:18 p.m.'))->format('Y-m-d H:i:s');
$params['modified'] = (new \DateTime('last Monday 1:27 p.m.'))->format('Y-m-d H:i:s');
$params['submission_time'] = '12:53:00';
$params['location_id' ] = $locations['510'];
$params['event_type_id'] = $event_types['presentment'];
$event_insert->execute($params);
$event_id = $pdo->lastInsertId();
$deft_event_insert->execute(['defendant_id'=>$deft['id'],'event_id'=>$event_id]);
$interp_event_insert->execute(
    [
        'interpreter_id' => $spanish_contractors['López'],
        'event_id' => $event_id,
        'created' => (new \DateTime('last Monday 1:19 p.m.'))->format('Y-m-d H:i:s'),
        'created_by_id' => $admin,
    ]
);

/* another atty/client ... */

$pdo->commit();







?>
