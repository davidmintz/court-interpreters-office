#!/usr/bin/env php
<?php
/**
 * Work in progress.
 *
 * Another attempt to generate and insert a few thousand dummy records for demo
 * purposes, using real data from the SDNY interpreters (current) production
 * database, but swapping real names for fake ones. We assume the .my.cnf in
 * $HOME has a username and password good for both.
 *
 * prerequisites:
 *  cat sql/mysql-schema.sql sql/initial-data.sql sql/dummy_data.sql | mysql office_demo;
 *
 */
require __DIR__.'/../../vendor/autoload.php';
if (!isset($argv[1])) {
    exit(sprintf("usage: %s <target-database> [source-database]\n",basename(__FILE__)));
} else {
    $office_database = $argv[1];
}
$interpreters_database = isset($argv[2]) ? $argv[2] : 'dev_interpreters';

// echo "using target database '$office_database', importing from '$interpreters_database'\n";
// echo "connecting...\n";

$config_file = getenv('HOME').'/.my.cnf';
$config = parse_ini_file($config_file);
try {
    $pdo_office = new \PDO("mysql:host=localhost;dbname=$office_database",$config['user'],$config['password'],[
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
    ]);
    $pdo_interpreters =  new \PDO("mysql:host=localhost;dbname=$interpreters_database",$config['user'],$config['password'],[
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
    ]);
} catch (\Exception $e) {
    exit("connection failed. ".$e->getMessage() . "\n");
}
$office_languages = $pdo_office->query('SELECT name,id from languages')->fetchAll(PDO::FETCH_KEY_PAIR);
$event_types = $pdo_office->query('SELECT name,id from event_types')->fetchAll(PDO::FETCH_KEY_PAIR);
/*
Array
(
    [arraignment] => 6
    [atty/client interview] => 2
    [bail hearing] => 9
    [conference] => 1
    [document translation] => 11
    [plea] => 4
    [presentment] => 5
    [pretrial services intake] => 12
    [probation interview] => 7
    [sentence] => 3
    [suppression hearing] => 10
    [trial] => 8
)
+----+------------------+
| id | type             |
+----+------------------+
|  1 | AUSA             |
|  2 | USPO             |
|  3 | staff interp     |
|  4 | freelance interp |
|  5 | ctroom staff     |
|  6 | Pretrial         |
|  7 | Magistrates      |
|  8 | defense atty     |
|  9 | other            |
| 10 | USAO staff       |
+----+------------------+

/*

most frequently used judge_ids:

 138,73,128,30,101,49,35,111,108,9,23,133,22
*/
define('MAG_SDNY',22);
$sdny_judges = [138,73,128,30,101,49,35,111,108,9,23,133,134];
$dummy_judges = $pdo_office->query('SELECT id from judges')->fetchAll(PDO::FETCH_COLUMN);

// their_id => our_id
$judge_map = array_combine($sdny_judges,$dummy_judges);
$event_insert = 'INSERT INTO events (
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
    "",
    "",
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

)';
$events_select = "SELECT e.*,
    office_lang.name language,
    t.type type,
    t.proceeding_id event_type_id,
    j.lastname judge,
    j.judge_id,
    COALESCE (user.lastname, req_by.lastname) as submitter_lastname,
    COALESCE (user.firstname, req_by.firstname) as submitter_firstname,
    COALESCE (user.id, req_by.id) as submitter_id,
    rc.type AS hat
    FROM events e
    JOIN request_class rc ON e.req_class = rc.id
    LEFT JOIN request_by req_by ON (e.req_by = req_by.id AND e.req_class = req_by.class_id)
    LEFT JOIN request_users user ON (e.req_by = user.id AND e.req_class IN (2,5,6))
    JOIN languages lang ON e.language_id = lang.lang_id
    JOIN proceedings t ON t.proceeding_id = e.proceeding_id
    JOIN judges j ON e.judge_id = j.judge_id
    LEFT JOIN $office_database.event_types office_t ON t.type = office_t.name
    JOIN $office_database.languages office_lang ON lang.name = office_lang.name
    WHERE e.event_date > DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
    AND (office_t.name IS NOT NULL OR t.proceeding_id IN (5,6,19)) /* */
    AND (e.req_class <> 9)
    AND j.judge_id IN (138,73,128,30,101,49,35,111,108,9,23,133,134,22)
    AND e.docket <> ''";

$stmt = $pdo_interpreters->prepare($events_select);
$stmt->execute(); //$count = $stmt->rowCount();//exit("count is $count\n");
while($e = $stmt->fetch()) {
    // assemble query parameters
    $params['language_id'] = $office_languages[$e->language];
    if ($e->judge_id == MAG_SDNY) {
        $params['anonymous_judge_id'] = 1;
        $params['judge_id'] = null;
    } else {
        if (! isset($judge_map[$e->judge_id])) {
            echo "we don't have $e->judge_id ?\n";
            continue;
        }
        $params['judge_id'] = $judge_map[$e->judge_id];
        $params['anonymous_judge_id'] = null;
    }
    // figure out submitter id
    if ($e->hat == 'ctroom staff') {

    }
    printf("type is %s, id %d\n",$e->type,$e->event_type_id);
    //print_r($params);
}


function format_docket($docket) {
    // expected format is e.g. YYYY[CR|MAG|CIV]NNNNN

    if (! preg_match('/(\d{4})([A-Z]+)(\d{5})/', $docket, $matches)) {
         return false;
    }
    $return = "$matches[1]-$matches[2]-";
    // not too sure about this
    if (0 === strpos($matches[3],'0')) {
        $return .= substr($matches[3],1);
    } else {
        $return .= $matches[3];
    }
    return $return;
}
