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

// we repeat ourself, but...
$dummy_judge_ids = $pdo_dummy->query('SELECT id from judges')->fetchAll(PDO::FETCH_COLUMN);
$number_of_judges = count($dummy_judge_ids);

// select our $number_of_judges most popular judges
$judge_query = "SELECT j.id, j.lastname, COUNT(e.id) events
FROM people j JOIN events e ON e.judge_id = j.id JOIN languages l ON e.language_id = l.id
JOIN $dummy_database.languages dummy_langs ON dummy_langs.name = l.name
WHERE docket <> '' AND e.date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
GROUP BY j.id ORDER BY events desc limit $number_of_judges";
$judge_ids = $pdo_source->query($judge_query)->fetchAll(PDO::FETCH_COLUMN);
$str_judge_ids = implode(',',$judge_ids);

// json data created by create-dummy-events.php
$event_id_map = json_decode(file_get_contents(__DIR__.'/event-map.json'),true);

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

// loop through OUR events and try to keep consistent name - docket
$events_q = $pdo_dummy->prepare(
'SELECT e.id event_id, e.docket, l.name language FROM events e
JOIN languages l ON l.id = e.language_id ORDER BY e.date, e.id'
);
$events_q->execute();
printf("You have %d events\n",  $events_q->rowCount());
printf("You have %d elements in the array\n",  count($event_id_map));

//$reverse_id_map = array_flip($event_id_map);
$docket_language_cache = [];
$get_defts_by_language = $pdo_dummy->prepare(
    'SELECT d.id FROM defendant_names d WHERE d.language_hint LIKE :hint ORDER BY RAND()'
);
// -- LEFT JOIN defendants_events de ON d.id = de.defendant_id
// -- WHERE d.language_hint LIKE :hint AND de.event_id IS NULL ORDER BY RAND()'
$deft_event_insert = $pdo_dummy->prepare('INSERT INTO defendants_events (event_id, defendant_id) VALUES (:event_id,:defendant_id)');
$inserts = 0;
$total =  count($event_id_map);
$skipped = 0;
while($row = $events_q->fetch()) {
    // how many defts do we need?
    // our event id is $row->event_id, theirs is {$reverse_id_map[$row->event_id]}
    // shit needs %d names\n",$n_deft_events[$row->event_id]);
    $names_needed = $n_deft_events[$row->event_id];
    if (0 == $names_needed) {
        //echo "no deft-events, skipping event id $row->event_id\n";
        $skipped++;
        continue;
    }
    $key = "{$row->docket}-{$row->language}";
    if (key_exists($key, $docket_language_cache)) {
        //printf("we have $key: %s\n",print_r($docket_language_cache[$key],true));
        $ids = $docket_language_cache[$key];
        shuffle($ids);
        $names_found = count($ids);
        //printf("need $names_needed, we have cached %d\n",$names_found);
        if ($names_found >= $names_needed) {
            for ($i = 0; $i < $names_needed; $i++) {
                $id = $ids[$i];
                $params = ['event_id'=>$row->event_id,'defendant_id'=>$id];
                $deft_event_insert->execute($params);
                $inserts++;
            }
        } else {
            //printf("need $names_needed, but we have cached: %d\n",$names_found);
            $get_defts_by_language->execute(['hint'=>$row->language,]);
            $more_ids = $get_defts_by_language->fetchAll(PDO::FETCH_COLUMN);
            // Compares array1 against one or more other arrays and
            // returns the values in array1 that are not present in any
            // of the other arrays.
            // printf("cached ids are currently: %s\n",print_r($ids,true));
            $more_ids = array_diff($more_ids,$ids);
            $ids = $ids + $more_ids;

            if (count($ids) >= $names_needed) {
                $cache = [];
                $ids = array_values($ids);
                for ($i = 0; $i < $names_needed; $i++) {
                    $id = $ids[$i];
                    $params = ['event_id'=>$row->event_id,'defendant_id'=>$id];
                    $deft_event_insert->execute($params);
                    $inserts++;
                    $cache[] = $id;
                }
                $docket_language_cache[$key] = $cache;
                // printf("and now: cached ids are currently: %s\n",print_r($cache,true));
            } else {
                // printf("used cache and db, still short by %s names\n", $names_needed - count($ids));
            }
        }

    } else {
        $get_defts_by_language->execute(['hint'=>$row->language,]);
        $ids = $get_defts_by_language->fetchAll(PDO::FETCH_COLUMN);
        $names_found = count($ids);
        $cache = [];
        // printf("found %s of %s needed for $key\n",$names_found,$names_needed);
        // print_r($ids);
        if ($names_found >= $names_needed) {
            for ($i = 0; $i < $names_needed; $i++) {
                $id = $ids[$i];
                $params = ['event_id'=>$row->event_id,'defendant_id'=>$id];
                $deft_event_insert->execute($params);
                $inserts++;
                $cache[] = $id;
            }
        } else {
            for ($i = 0; $i < $names_found; $i++) {
                $id = $ids[$i];
                $params = ['event_id'=>$row->event_id,'defendant_id'=>$id];
                $deft_event_insert->execute($params);
                $inserts++;
                $cache[] = $id;
            }
            //printf("still short by %s names\n",$names_needed - $names_found);
        }
        $docket_language_cache[$key] = $cache;
    }
    printf("inserted $inserts, skipped $skipped of $total\r");
    file_put_contents(__DIR__.'/docket-language-defts.json',json_encode($docket_language_cache));
}
