#!/usr/bin/env php
<?php
$db = require(__DIR__."/connect.php");

function format_docket($docket) {
    // expected format is e.g. YYYY[CR|MAG|CIV]NNNNN
    if (!$docket) { return ''; }

    if (! preg_match('/(\d{4})([A-Z]+)(\d{5})/', $docket, $matches)) {
         return false;
    }
    $return = "$matches[1]-$matches[2]-";
    if (0 === strpos($matches[3],'0')) {
        $return .= substr($matches[3],1);
    } else {
        $return .= $matches[3];
    }
    return $return;
}

$stmt = $db->query('SELECT n.*,
    u.name creator_username,
    u2.id new_creator_id,
    u4.id new_modified_by_id
    FROM dev_interpreters.docket_notes n
        LEFT JOIN dev_interpreters.users u ON u.user_id = n.created_by
        LEFT JOIN users u2 ON u.name = u2.username
        LEFT JOIN dev_interpreters.users u3 ON u3.user_id = n.updated_by
        LEFT JOIN users u4 ON u4.username = u3. name'
    );

$insert = $db->prepare("
    INSERT INTO docket_annotations(
        id,
        comment,
        created_by_id,
        modified_by_id,
        docket,
        priority,
        created,
        modified)
    VALUES (
        :id,
        :comment,
        :created_by_id,
        :modified_by_id,
        :docket,
        :priority,
        :created,
        :modified);
");
$i = 0;
while ($row = $stmt->fetch()) {

    $params = [];

    $docket = format_docket($row['docket']);
    if (! $docket) {
        echo "shit is NOT GOOD! bad docket found in ${$row['id']}\n";
        continue;
    } else {
        $params[':docket'] = $docket;
    }
    $params[':created_by_id'] = $row['new_creator_id'];
    foreach (['id','priority','created'] as $field) {
        $params[':'.$field] = $row[$field];
    }
    $params[':comment'] = $row['notes'];
    // all the updated_by columns say "19" --  which can't be right.
    $params[':modified_by_id'] = null;
    $params[':modified']   = null;

    $insert->execute($params);
    $i++;
}
echo "\nfinished inserting $i docket annotations\n";
//DELETE n.* FROM docket_annotations n LEFT JOIN events e ON n.docket = e.docket WHERE e.docket IS NULL
$deleted = $db->exec('DELETE n.* FROM docket_annotations n LEFT JOIN events e ON n.docket = e.docket WHERE e.docket IS NULL');
echo "deleted $deleted orphans\n";
