<?php
/**
 *  a one-off for fixing defendant names with empty firstname and a lastname in
 *  the form "lastname, firstname," shit that was put in there before validation
 *  was improved
 */



$TARGET_DB = 'dev_interpreters';
/** @var $db \PDO */
$db = require(__DIR__.'/connect.php');
$sql = 'SELECT deft_id,lastname FROM deft_names WHERE firstname = "" AND lastname like "%,%"';
$stmt = $db->query($sql);
$update_deft_events= $db->prepare('UPDATE deft_events SET deft_id = :to WHERE deft_id = :from');
$update_deft_requests = $db->prepare('UPDATE defendants_requests SET defendant_id = :to WHERE defendant_id = :from');
$find_existing = $db->prepare('SELECT * FROM deft_names WHERE lastname = :lastname and firstname = :firstname');

$update_name = $db->prepare("UPDATE deft_names SET lastname = :lastname, firstname = :firstname WHERE deft_id = :id");

$stmt->execute();
$count = $stmt->rowCount();
printf("found %d fucked up names\n",$count);

$totals = [
    'edits'=>0,
    'replacements'=>0,
];

while ($fucked = $stmt->fetch()) {
    $name = preg_split('/\s*,\s*/',$fucked['lastname']);
    if (count($name) > 2) {
        trigger_error("with record $fucked[deft_id] shit has too many elements: ".print_r($name,true));
        continue;
    }

    echo("\nexamining:  \"$fucked[lastname]\"\n");

    $find_existing->execute(['lastname'=>$name[0],'firstname'=>$name[1]]);
    $existing_name = $find_existing->fetch();
    if ($existing_name) {
        printf("found existing: %s, %s\n",$existing_name['lastname'],$existing_name['firstname']);
        echo("\nreplacing...");
        $params = ['from'=>$fucked['deft_id'],'to'=>$existing_name['deft_id']];
        try {
            $update_deft_events->execute($params);
            $deft_events_affected = $update_deft_events->rowCount();
            $update_deft_requests->execute($params);
            $deft_requests_affected = $update_deft_requests->rowCount();
            printf("updated %d deft_events and %d deft_requests rows",
                $deft_events_affected, $deft_requests_affected);

            $totals['replacements'] += ($deft_requests_affected + $deft_events_affected);

        } catch (\Exception $e) {
            printf("\nshit. caught exception %s: %s\n",get_class($e),$e->getMessage());
            continue;
        }
    } else {
        echo "no match found, editing... ";
        try {
            $update_name->execute([
                'lastname'=>$name[0],'firstname'=> $name[1],
                'id'=>$fucked['deft_id']// += $update_name->rowCount();
            ]);
            $totals['edits']++;
            echo "OK\n";
        } catch (\Exception $e) {
            printf("\nshit. caught exception %s: %s\n",get_class($e),$e->getMessage());
            continue;
        }
    }
}
print_r($totals);
echo "\ndone\n";
echo "you can now run:\n  DELETE d FROM deft_names d LEFT JOIN deft_events de ON d.deft_id = de.deft_id LEFT JOIN defendants_requests dr ON d.deft_id = dr.defendant_id WHERE de.deft_id IS NULL AND dr.defendant_id IS NULL;";
echo "\non interpreters db\n";
