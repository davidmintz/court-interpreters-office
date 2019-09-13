<?php

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
// printf("You have %d events\n",  $events_q->rowCount());
// printf("You have %d elements in the array\n",  count($event_id_map));
$docket_language_cache = [];
$get_defts_by_language = $pdo_dummy->prepare(
    'SELECT d.id FROM defendant_names d WHERE d.language_hint LIKE :hint ORDER BY RAND()'
);
$deft_event_insert = $pdo_dummy->prepare('INSERT INTO defendants_events (event_id, defendant_id) VALUES (:event_id,:defendant_id)');
$inserts = 0;
$total =  count($event_id_map);
$skipped = 0;
while($row = $events_q->fetch()) {
    $names_needed = $n_deft_events[$row->event_id];
    if (0 == $names_needed) {
        $skipped++;
        continue;
    }
    $key = "{$row->docket}-{$row->language}";
    if (key_exists($key, $docket_language_cache)) {
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
            }
        }

    } else {
        $get_defts_by_language->execute(['hint'=>$row->language,]);
        $ids = $get_defts_by_language->fetchAll(PDO::FETCH_COLUMN);
        $names_found = count($ids);
        $cache = [];
        // printf("found %s of %s needed for $key\n",$names_found,$names_needed);
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
            $log->info(sprintf("still short by %s names\n",$names_needed - $names_found),['cache_key'=>$key]);
        }
        $docket_language_cache[$key] = $cache;
    }
    printf("inserted $inserts deft-event rows, skipped $skipped for $total events\r");
}
file_put_contents(__DIR__.'/docket-language-defts.json',json_encode($docket_language_cache));

return true;
