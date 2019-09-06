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


$event_id_map = [];
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
$event_types = $pdo_dummy->query('select et.id dummy_id, et.name dummy_name, oet.id o_id from event_types et JOIN office.event_types oet ON et.name = oet.name order by o_id')
    ->fetchAll(PDO::FETCH_ASSOC);
$type_map = array_combine(
    array_column($event_types, 'o_id'),
    array_column($event_types, 'dummy_id')
);
$event_insert = $pdo_dummy->prepare(
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
    (:language_id,
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
    :deleted)');

$str_judge_ids = implode(',',$judge_ids);


/*
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
    "SELECT e.*,l.name language, t.name event_type, tc.category,
    dummy_langs.id AS dummy_lang_id,
    COALESCE(j.lastname, aj.name) judge,
    IF(submitter.id IS NOT NULL,CONCAT(submitter.lastname,', ',submitter.firstname),anon_submitter.name) AS submitter,
    submitter_hat.name submitter_hat,
    submitter_hat.id submitter_hat_id,
    creator.username creator,
    creator_hat.id creator_hat_id,
    creator_p.id creator_person_id,
    modifier_hat.id modifier_hat_id,
    loc_type.id location_type_id
    FROM events e
    JOIN event_types t ON t.id = e.event_type_id
    JOIN event_categories tc ON t.category_id = tc.id
    JOIN users creator ON e.created_by_id = creator.id
    JOIN roles creator_role on creator_role.id = creator.role_id
    JOIN people creator_p ON creator_p.id = creator.person_id
    JOIN hats creator_hat ON creator_hat.id = creator_p.hat_id
    LEFT JOIN locations loc ON e.location_id = loc.id
    LEFT JOIN location_types loc_type ON loc.type_id = loc_type.id
    JOIN users modifier ON e.modified_by_id = modifier.id
    JOIN roles modifier_role on modifier_role.id = modifier.role_id
    JOIN people modifier_p ON modifier_p.id = modifier.person_id
    JOIN hats modifier_hat ON modifier_hat.id = modifier_p.hat_id
    LEFT JOIN hats anon_submitter ON anon_submitter.id = e.anonymous_submitter_id
    LEFT JOIN people submitter ON e.submitter_id = submitter.id
    LEFT JOIN hats submitter_hat ON submitter.hat_id = submitter_hat.id
    LEFT JOIN people j ON j.id = e.judge_id
    LEFT JOIN anonymous_judges aj ON e.anonymous_judge_id = aj.id
    LEFT JOIN locations aj_locations ON aj.default_location_id = aj_locations.id
    JOIN languages l ON e.language_id = l.id
    JOIN $dummy_database.languages dummy_langs ON dummy_langs.name = l.name

    WHERE e.date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
    AND (e.judge_id IN ($str_judge_ids) OR (aj.name = 'magistrate'
        AND aj_locations.name = '5A'))
    AND t.name NOT REGEXP 'civil$|^telephone |^agents|atty/other|unspecified|settlement|^sight|court staff|^AUSA'
    AND e.docket <> '' ORDER BY e.created";

$events_stmt = $pdo_source->prepare($event_select);
$events_stmt->execute();
$count = $events_stmt->rowCount();
$submitter_map = [];
$user_id_map = [];
$generic_bail_id = $pdo_dummy->query('SELECT id FROM event_types WHERE name LIKE "bail%"')->fetch(PDO::FETCH_COLUMN);

$dummy_types = $pdo_dummy->query('SELECT name,id FROM event_types')->fetchAll(PDO::FETCH_KEY_PAIR);
$judge_location_map = $pdo_dummy->query('SELECT id, default_location_id FROM judges')->fetchAll(PDO::FETCH_KEY_PAIR);
$other_location_map = [];

$get_judge_staff = $pdo_dummy->prepare('SELECT p.id FROM people p JOIN users u ON p.id = u.person_id
    JOIN clerks_judges cj ON u.id = cj.user_id WHERE cj.judge_id = :judge_id ORDER BY RAND() LIMIT 1');

$get_one_by_hat = $pdo_dummy->prepare('select p.id FROM people p
    WHERE p.hat_id = :hat_id ORDER BY rand() LIMIT 1');

$get_random_office_user = $pdo_dummy->prepare(
    'SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id
    JOIN people p ON u.person_id = p.id JOIN hats h ON p.hat_id = h.id
    AND h.id = :hat_id
    WHERE r.name <> "submitter"
    ORDER BY RAND() LIMIT 1'
);
$get_random_location_by_type = $pdo_dummy->prepare(
    'SELECT loc.id FROM locations loc WHERE loc.type_id = :type_id ORDER BY RAND() LIMIT 1'
);
$magistrate_courtroom_id = $pdo_dummy->query(
    'SELECT default_location_id FROM anonymous_judges WHERE name = "magistrate"')
    ->fetch(PDO::FETCH_COLUMN);
$i = 0;
echo "\n";
while ($e = $events_stmt->fetch()) {
    if (! isset($type_map[$e->event_type_id])) {
        $dummy_id = null;
        if (preg_match('/^bail/', $e->event_type)) {
            $type_map[$e->event_type_id] = $generic_bail_id;
        } elseif (preg_match('/suppression/',$e->event_type)) {
            $key = current(preg_grep('/suppression/',array_keys($dummy_types)));
            $dummy_id = $dummy_types[$key];

        } elseif (preg_match('/^pretrial services/',$e->event_type)) {
            $key = current(preg_grep('/^pretrial services/',array_keys($dummy_types)));
            $dummy_id = $dummy_types[$key];
        }
        if ($dummy_id) {
            $type_map[$e->event_type_id] = $dummy_id;
        }
    }
    if (!isset($type_map[$e->event_type_id])) {
        print_r($e);
        exit("\ncan't figure out event type mapping for $e->event_type\n");
    }
    $params = ['language_id' => $e->dummy_lang_id];
    $params['event_type_id'] = $type_map[$e->event_type_id];
    $params['judge_id'] = $e->judge_id ?  $judge_map[$e->judge_id]   : null;
    $params['anonymous_judge_id'] = $e->anonymous_judge_id ?: null;
    $params['comments'] = '';
    $params['admin_comments'] = '';
    foreach(['date','time','docket','created','modified',
    'submission_date','submission_time','anonymous_submitter_id',
    'deleted', 'end_time','cancellation_reason_id'] as $field) {
        $params[$field] = $e->$field;
    }
    // created by ...
    if (! isset($user_id_map[$e->created_by_id])) {
        $get_random_office_user->execute(['hat_id'=>$e->creator_hat_id]);
        $dummy_id = $get_random_office_user->fetch(PDO::FETCH_COLUMN);
        $user_id_map[$e->created_by_id] = $dummy_id;
    }
    $params['created_by_id'] = $user_id_map[$e->created_by_id];

    // last modified by ...
    if ($e->modified_by_id == $e->created_by_id) {
        $params['modified_by_id'] = $params['created_by_id'];
    } else {
        if (! isset($user_id_map[$e->modified_by_id])) {
            $get_random_office_user->execute(['hat_id'=>$e->modifier_hat_id]);
            $dummy_id = $get_random_office_user->fetch(PDO::FETCH_COLUMN);
            $user_id_map[$e->modified_by_id] = $dummy_id;
        }
        $params['modified_by_id'] = $user_id_map[$e->modified_by_id];
    }
    // location...
    if ($e->category == "in") {
        if ($params['judge_id']) {
            $params['location_id'] = $judge_location_map[$params['judge_id']];
        } elseif ($e->judge == 'magistrate') {
            $params['location_id'] = $magistrate_courtroom_id;
        }
    } elseif ($e->location_id) {
        if (! isset($other_location_map[$e->location_id])) {
            $get_random_location_by_type->execute(['type_id'=>$e->location_type_id]);
            $dummy_id = $get_random_location_by_type->fetch(PDO::FETCH_COLUMN);
            $other_location_map[$e->location_id] = $dummy_id;
        }
        $params['location_id'] = $other_location_map[$e->location_id];
    } else {
        $params['location_id'] = null;
    }
    // submitter ...
    if ($e->anonymous_submitter_id) {
        $params['submitter_id'] = null;
    } else {
        if (isset($submitter_map[$e->submitter_id])) {
            $params['submitter_id'] = $submitter_map[$e->submitter_id];
        } else {
            if ($e->creator_person_id == $e->submitter_id) {
                $get_random_office_user->execute(['hat_id'=>$e->creator_hat_id]);
                $dummy_id = $get_random_office_user->fetch(PDO::FETCH_COLUMN);
            } elseif ($e->judge_id and $e->category == "in"
            and in_array($e->submitter_hat,['Law Clerk','Courtroom Deputy'])) {
                // get the a clerk for this judge
                $get_judge_staff->execute(['judge_id'=>$params['judge_id']]);
                $dummy_id = $get_judge_staff->fetch(PDO::FETCH_COLUMN);
            } else {
                // get a random person of the same hat
                $get_one_by_hat->execute(['hat_id'=>$e->submitter_hat_id]);
                $dummy_id = $get_one_by_hat->fetch(PDO::FETCH_COLUMN);
            }
            if (! $dummy_id) {
                printf("\nshit, cannot come up with \$dummy_id, params: %s
                data: %s",print_r($params,true),print_r($e,true));
                exit;
            }
            $submitter_map[$e->submitter_id] = $dummy_id;
            $params['submitter_id'] = $submitter_map[$e->submitter_id];
        }
    }
    if (!$params['anonymous_submitter_id'] and !$params['submitter_id']) {
        printf("\nshit! no submitter or anon_submitter. params : %s\n data: %s",
            print_r($params,true),print_r($e,true));
        exit(1);
    }
    try {
        $event_insert->execute($params);
        $event_id_map[$e->id] = $pdo_dummy->lastInsertId();
        printf("inserted %d of %d event records\r",++$i,$count);
    } catch (\Exception $ex) {
        exit("fuck: ".$ex->getMessage()
            ."\nparameters: ".print_r($params,true)
            ."\ndata: ".print_r($e,true)
        );
    }
}
file_put_contents('event-map.json',json_encode($event_id_map));
unset($events_stmt);
echo "\n";
//==================================================================//
$ie_query = $pdo_source->prepare(
    "SELECT ie.*, e.language_id, l.name language
    FROM interpreters_events ie
    JOIN events e ON ie.event_id = e.id
    JOIN languages l ON e.language_id = l.id
    JOIN event_types t ON t.id = e.event_type_id
    JOIN $dummy_database.languages dummy_langs ON dummy_langs.name = l.name
    LEFT JOIN anonymous_judges aj ON e.anonymous_judge_id = aj.id
    LEFT JOIN locations aj_locations ON aj.default_location_id = aj_locations.id
    WHERE e.date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
    AND (e.judge_id IN ($str_judge_ids) OR (aj.name = 'magistrate'
        AND aj_locations.name = '5A'))
    AND t.name NOT REGEXP 'civil$|^telephone |^agents|atty/other|unspecified|settlement|^sight|court staff|^AUSA'
    AND e.docket <> '' ORDER BY ie.event_id, ie.interpreter_id"
);
$ie_query->execute();
$count = $ie_query->rowCount();
$n = 0;
$interpreter_query = $pdo_dummy->query(
    'SELECT il.*, l.name language, i.lastname FROM interpreters_languages il
    JOIN languages l ON il.language_id = l.id
    JOIN people i ON i.id = il.interpreter_id'
);
$dummy_interpreters = [];
while ($data = $interpreter_query->fetch()) {
    if (isset($dummy_interpreters[$data->language])) {
        $dummy_interpreters[$data->language][] = $data->interpreter_id;
    } else {
        $dummy_interpreters[$data->language] = [$data->interpreter_id];
    }
}
$ie_insert = $pdo_dummy->prepare(
    'INSERT INTO interpreters_events (interpreter_id,event_id,created,created_by_id)
    VALUES (:interpreter_id,:event_id,:created,:created_by_id)'
);
$interpreter_map = [];
$previous = null;
$failed = 0;
$bailed = 0;
while ($ie = $ie_query->fetch()) {
    $params = [];
    if (!isset($interpreter_map[$ie->interpreter_id])) {
        $index = array_rand($dummy_interpreters[$ie->language]);
        $interpreter_map[$ie->interpreter_id] = $dummy_interpreters[$ie->language][$index];
    }
    $params['interpreter_id'] = $interpreter_map[$ie->interpreter_id];
    $params['event_id'] =$event_id_map[$ie->event_id];
    $params['created_by_id'] = $user_id_map[$ie->created_by_id];
    $params['created'] = $ie->created;
    if ($previous == [$params['event_id'],$params['interpreter_id']]) {
        // try again
        $log->debug("trying to avoid duplicate entry error",$params);
        $number_of_interpreters = count($dummy_interpreters[$ie->language]);
        if ($number_of_interpreters == 1) {
            $log->warn("do we need another $ie->language interpreter?",['data'=>$ie,'params'=>$params]);
            continue;
        } else {
            $attempts = 0;
            while ($params['interpreter_id']  == $previous[1]) {
                $log->debug("looking for another $ie->language interpreter");
                $index = array_rand($dummy_interpreters[$ie->language]);
                $params['interpreter_id'] = $dummy_interpreters[$ie->language][$index];
                if (++$attempts > 10) {
                    $log->warn("infinite loop? giving up on dummy {$event_id_map[$ie->event_id]}, event $ie->event_id");
                    $bailed++;
                    continue 2;
                }
            }
        }
    }
    try {
        $ie_insert->execute($params);
        $previous = [$params['event_id'],$params['interpreter_id']];
        printf("inserted %d of $count ie records\r",++$n);
    } catch (\PDOException $x) {
        // one more try
        if (empty($one_more)) {
            $one_more = $pdo_dummy->prepare(
                'SELECT i.id FROM interpreters i JOIN interpreters_languages il ON i.id = il.interpreter_id
                JOIN languages l ON l.id = il.language_id WHERE l.name = :language AND i.id NOT IN
                (SELECT interpreter_id FROM interpreters_events WHERE event_id = :event_id)
                ORDER BY RAND() LIMIT 1');    // LEFT JOIN ...WHERE x IS NULL would work too
        }
        $one_more->execute(['language'=>$ie->language,'event_id'=>$params['event_id']]);
        $id = $one_more->fetch(PDO::FETCH_COLUMN);
        if ($id) {
            $params['interpreter_id'] = $id;
            try {
                $ie_insert->execute($params);
                $previous = [$params['event_id'],$params['interpreter_id']];
                printf("inserted %d of $count ie records\r",++$n);

            } catch (\PDOException $z) {
                $log->warn("more bad news: ". $z->getMessage());
            }
        } else {
            $log->err($x->getMessage(),['data'=>$ie,'params'=>$params]);
            $failed++;
            continue;
        }
    }
}
echo "\ncompleted $n of $count. $failed failed, $bailed bailed\n";
unset($ie_query);

if ($n == $count) {
    //unlink('./event-map.json');
}

// figure out number of defts per event
$n_deft_q = $pdo_source->prepare("select e.id event_id,
COUNT(de.defendant_id) defts FROM events e
LEFT JOIN defendants_events de ON e.id = de.event_id
JOIN languages l ON e.language_id = l.id
JOIN event_types t ON e.event_type_id = t.id
JOIN $dummy_database.languages dummy_langs ON dummy_langs.name = l.name
LEFT JOIN anonymous_judges aj ON e.anonymous_judge_id = aj.id
LEFT JOIN locations aj_locations ON aj.default_location_id = aj_locations.id
WHERE e.date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
AND (e.judge_id IN ($str_judge_ids) OR (aj.name = 'magistrate'
    AND aj_locations.name = '5A'))
AND t.name NOT REGEXP 'civil$|^telephone |^agents|atty/other|unspecified|settlement|^sight|court staff|^AUSA'
AND e.docket <> ''
GROUP BY e.id");
$n_deft_q->execute();
$n_deft_events = [];
while ($row = $n_deft_q->fetch()) {
    $n_deft_events[$event_id_map[$row->event_id]] = $row->defts;
}
$deft_insert = $pdo_dummy->prepare(
'INSERT INTO defendants_names (surnames, given_names,language_hint)
VALUES (:surnames, :given_names, :hint)');

$name_docket_map = [];
// loop through our events and try to keep consistent name - docket
$events_q = $pdo_dummy->prepare(
'SELECT e.id event_id, e.docket, l.name language FROM events e
JOIN languages l ON l.id = e.language_id ORDER BY e.date, e.id'
);
$events_q->execute();

$get_random_deft_by_language = $pdo_dummy->prepare(
    'SELECT d.id FROM defendant_names d LEFT JOIN defendants_events de ON d.id = de.defendant_id
    WHERE d.language_hint LIKE :hint AND de.event_id <> :event_id ORDER BY RAND() LIMIT 8'
);
$deft_event_insert = $pdo_dummy->prepare('INSERT INTO defendants_events (event_id, defendant_id) VALUES (:event_id,:defendant_id)');
$inserts = 0;
$count = $events_q->rowCount();
echo "\nthere are $count events in $dummy_database\n";
$no_name = 0;
while ($row = $events_q->fetch()) {
    $num_defts = $n_deft_events[$row->event_id];
    if ($num_defts == 0) {
        // why bother
        $no_name++;
        continue;
    }
    $params = []; $deft_id = null;
    $key = "{$row->docket}-{$row->language}";

    // single-defendant events
    if ($num_defts == 1) {
        if (isset($name_docket_map[$key])) {
            // $shit = print_r($name_docket_map[$key],true);
            // echo "\n\n"; var_dump($name_docket_map); echo "SHIT: $shit";
            // exit();
            $defts = $name_docket_map[$key];
            if ($defts) {
                //echo "\n\nkey is $key\n"; var_dump($defts); print_r( $name_docket_map); exit;
                $deft_id = $defts[array_rand($defts,1)];
                echo __LINE__ .": here is your deft id: ";
                var_dump($deft_id); //exit;
            } else {
                $deft_id = false;
            }
            if (is_array($defts) && !count($defts)) {
                echo  __LINE__ .": empty array of \$defts for $key: this is fucked up!\n";
                exit();
            }
        } else { // $name_docket_map[$key] is NOT set
            // get one, and save it for later

            echo "fucking language? $row->language ... ";
            $get_random_deft_by_language->execute(['hint' => "%{$row->language}%", 'event_id' => $row->event_id]);
            $deft_id = $get_random_deft_by_language->fetch(PDO::FETCH_COLUMN);
            //var_dump($deft_id); exit;
            if (!$deft_id) {
                $log->warn("FUCK? can't come up with a deft id for $key",['data'=>$row]);
                echo "can't find a deft id at ".__LINE__."\n";
                var_dump($deft_id);
            } else {
                echo "we have a fucking deft id: $deft_id ...\n";
                $name_docket_map[$key] = [ $deft_id ];
            }
        }
        if (! $deft_id) {
            $log->warn("still can't come up with a deft id at ".__LINE__,
            ['data'=> $row, 'deft_id result type'=>gettype($deft_id)]);

        } else {
            $params = ['defendant_id'=> $deft_id, 'event_id' => $row->event_id,];
            try {
                echo "insert attempt at ".__LINE__. "...";
                $deft_event_insert->execute($params);
                $inserts++;
                echo "BRAVO!!\n";
            } catch (\PDOException $e) {
                echo "FUCK!\n";
                $log->err($e->getMessage(),['params'=>$params, 'data'=>$row, 'key'=> $key, 'line'=>__LINE__]);
                throw $e;
            }
        }
    } else { // multi defts

        if (isset($name_docket_map[$key])) {
            if (! is_array($name_docket_map[$key]) or ! count($name_docket_map[$key])) {
                throw new RuntimeException(
                    sprintf("$key is set, but value is type %s and count is %s",
                    gettype($name_docket_map[$key])),
                    is_array($name_docket_map[$key]) ? count($name_docket_map[$key]): "n/a"
                );
            }
            // YES we have seen it before
            $n_existing = count($name_docket_map[$key]);
            if ($num_defts <= $n_existing) {
                $done = 0;
                foreach($name_docket_map[$key] as $deft_id) {
                    $params = ['defendant_id'=> $deft_id, 'event_id' => $row->event_id,];
                    try {
                        $deft_event_insert->execute($params);
                        $inserts++;
                        $done++;
                    } catch (\PDOException $e) {
                        $log->err($e->getMessage(),['notes'=>'trying to add multi deft_events at '.__LINE__,
                          'params'=>$params, 'data'=>$row, 'key'=> $key]);
                        continue 1; // so we're clear about that
                    }
                }
                $still_needed = $num_defts - $done;
                if ($still_needed) {
                    $log->debug("still need $still_needed more",['key'=>$key,'data'=>$row]);
                }
            } else {
                $still_needed = $num_defts - $n_existing;
                $log->debug("to do: still need $still_needed more",['key'=>$key,'total_defts'=>$num_defts]);
            }
        } else { // we have NOT seen it before
            $name_docket_map[$key] = [];
            // take a good look at this
            $get_random_deft_by_language->execute(['hint' => "%{$row->language}%", 'event_id' => $row->event_id]);
            $defts = $get_random_deft_by_language->fetchAll(PDO::FETCH_COLUMN);
            $n_found = count($defts);
            if ($n_found == $num_defts) {
                // yay
                $done = 0;
                foreach ($defts as $deft_id) {
                    try {
                        if (!isset($name_docket_map[$key])) {
                            $name_docket_map[$key] = [];
                        }
                        $params = ['event_id'=>$row->event_id,'defendant_id' => $deft_id,'hint' => $row->language,];
                        $deft_event_insert->execute($params);
                        $inserts++;
                        $done++;
                        $name_docket_map[$key][] = $deft_id;
                    } catch (\PDOException $e) {
                        $log->err($e->getMessage(),['notes'=>'trying to add multi deft_events at '.__LINE__,  'params'=>$params, 'data'=>$row, 'key'=> $key]);
                        unset($name_docket_map[$key]);
                        continue 1; // so we're clear about that
                    }
                }

            } else {
                $log->debug("to do: still need more names",['key'=>$key,'data'=>$row, 'found'=> $defts]);
            }

        }
    }

    echo("skipped $no_name, inserted $inserts of $count\r");
}

exit(0);

function insert_fake_interpreters(Array $interpreters) {
    global $pdo_dummy,$dummy_langs;
    //$interpreters = [
        // 'Bengali' => ['Rakshit','Haimanti','haimanti@rakshit.com'],
        // "Burmese"=>  ['Aye','Kyi','aye.kyi@example.org'],
        // "Farsi"=>   ["Qa'ani",'Habibollah','habibollah@example.org'],
        // "Fulani"=>  ['dan Fodio','Usman','usman@example.org'],
        // "Ga"=>  ['Kushi','Ayi'],
        // "Georgian"=>    ["Avalishvili",'Giorgi'],//Pachulia, Zaza
        // "Korean"=>  ['Eun-Jin','Shim'],
        // "Lithuanian"=>['Adomaitis','Dainius'],
        // "Mandingo"=>['Umaru Turay','Sitta'],
        // "Pashto"=>['Hotak','Mirwais'],
        // "Punjabi"=>['Khaira','Nimrat'],
        // "Romanian"=>['Comăneci','Nadia','nadia@example.org'],
        // "Sinhala"=>['Vithanaga','Prasanna'],
        // "Somali"=>['Nuruddin','Ali Amaan'],
        // "Taishanese"=>['Chi-keung','Wan'],
        // "Turkish"=>['Asena','Duygu'],
        // "Twi"=>['Wiafe-Akenten','Nana'],
        // "Ukrainian"=>['Virastyuk','Roman'],
        // "Urdu"=>['Abbas','Rahman'],
        // "Yoruba"=>["Fakeye",'Lamidi'],
    //];
    $person_insert = $pdo_dummy->prepare(
        "INSERT INTO people (hat_id, email, lastname, firstname, discr, active)
            VALUES (3,:email,:lastname,:firstname, 'interpreter', 1 )"
    );
    $interp_insert = $pdo_dummy->prepare(
        "INSERT INTO interpreters (id,comments,address1,address2,city,state,zip,country)
        VALUES  (last_insert_id(),'','','','','','','')"
    );
    $il_insert = $pdo_dummy->prepare(
        "INSERT INTO interpreters_languages VALUES (last_insert_id(),:language_id,2)"
    );
    foreach ($interpreters as $lang => $data) {
        //$params = ['language_id'=>$dummy_langs[$lang]];
        $params['lastname'] = $data[0];
        $params['firstname'] = $data[1];
        if (isset($data[2])) {
            $params['email'] = $data[2];
        } else {
            $params['email'] = strtolower("$data[1]-$data[0]@example.org");
        }
        try {
            $person_insert->execute($params);
            $interp_insert->execute();
            $il_insert->execute(['language_id'=>$dummy_langs[$lang]]);
        } catch (\Exception $e) {
            print_r($data);
            exit("fuck. language $lang".$e->getMessage());
        }
        /*
        INSERT INTO people (hat_id, email, lastname, firstname, discr, active)
            VALUES (3,'van_eyck@awesomepainters.com','van Eyeck','Jan', 'interpreter', 1 );
        INSERT INTO interpreters (id,comments,address1,address2,city,state,zip,country) VALUES (last_insert_id(),'','','','','','','');
        INSERT INTO interpreters_languages VALUES (last_insert_id(),(SELECT id FROM languages WHERE name = 'Dutch'),2);
        */
    }
}

//unset($ie_query);
//
// $defts_query = $pdo_source->prepare(
//     "SELECT de.*, d.given_names, d.surnames, e.language_id, l.name language
//     FROM defendants_events de
//     JOIN events e ON de.event_id = e.id
//     JOIN defendant_names d ON d.id = de.defendant_id
//     JOIN languages l ON e.language_id = l.id
//     JOIN event_types t ON t.id = e.event_type_id
//     JOIN $dummy_database.languages dummy_langs ON dummy_langs.name = l.name
//     LEFT JOIN anonymous_judges aj ON e.anonymous_judge_id = aj.id
//     LEFT JOIN locations aj_locations ON aj.default_location_id = aj_locations.id
//     WHERE e.date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
//     AND (e.judge_id IN ($str_judge_ids) OR (aj.name = 'magistrate'
//         AND aj_locations.name = '5A'))
//     AND t.name NOT REGEXP 'civil$|^telephone |^agents|atty/other|unspecified|settlement|^sight|court staff|^AUSA'
//     AND e.docket <> '' ORDER BY ie.event_id, ie.interpreter_id");

exit(0);
