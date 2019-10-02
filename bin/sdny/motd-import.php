#!/usr/bin/env php
<?php
/**
 * imports the old event-types into the new database, and creates a JSON data file
 * mapping old_id => new_id for later reference
 */

$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
]);
$old_db = new PDO('mysql:host=localhost;dbname=dev_interpreters', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
]);

$user_map = $db->query('SELECT them.user_id, u.id  FROM users u JOIN dev_interpreters.users them ON u.username = them.name')
    ->fetchAll(\PDO::FETCH_KEY_PAIR);

$david = $db->query('SELECT u.id  FROM users u WHERE username = "david"')->fetchColumn();

$insert = $db->prepare('INSERT INTO motd (date, created, created_by_id, modified, modified_by_id, content)
VALUES (:date, :created, :created_by_id, :modified, :modified_by_id, :content)');
$db->exec('TRUNCATE TABLE motd');
$db->exec('TRUNCATE TABLE motw');
$query = $old_db->prepare('SELECT * FROM motd ORDER BY date');
$query->execute();
$count = 0;
$total = $query->rowCount();
$now = (new \DateTime())->format("Y-m-d h:i:s");
while ($row = $query->fetch()) {
    $params = ['date' => $row->date,'content'=> $row->message];
    if (!$row->created_by) {
        $params['created_by_id'] = $david;
    }
    if (! $row->created) {
        $params['created'] = $now;
    } else {
        $params['created'] = $row->created;
    }
    if (!$row->updated && ($row->created_by != $row->updated_by)) {
        echo "oops, no value in 'updated' for $row->date...\n";
    }
    if (!$row->created_by) {
        //echo "oops, no created_by...\n";
        //$row->created_by = $david;
        $params['created_by_id'] = $david;
    } else {
        $params['created_by_id'] = $user_map[$row->created_by] ?? $david;
    }
    if ($row->created && $row->updated && $row->created != $row->updated && !$row->updated_by) {
        //echo "oops. odd one at $row->date\n";
        $params['modified'] = $now;
        $params['modified_by_id'] = $david;
    } else {
        $params['modified'] = $row->updated;
        $params['modified_by_id'] = $user_map[$row->updated_by] ?? $david;
    }
    if ($params['modified'] < $params['created']) {
        $params['modified'] = $now;
        $params['modified_by_id'] = $david;
    }
    $params['date'] = $row->date;
    $insert->execute($params);
    $count++;
    echo "inserted $count MOTDs of $total\r";
}
echo "\ndone.\n";
$insert = $db->prepare('INSERT INTO motw (week_of, created, created_by_id, modified, modified_by_id, content)
VALUES (:week_of, :created, :created_by_id, :modified, :modified_by_id, :content)');
$query = $old_db->prepare('SELECT * FROM motw ORDER BY week_of');
$query->execute();
$count = 0;
$total = $query->rowCount();
while ($row = $query->fetch()) {
    $params = ['week_of' => $row->week_of,'content'=> $row->message];
    if (!$row->created_by) {
        $params['created_by_id'] = $david;
    }
    if (! $row->created) {
        $params['created'] = $now;
    } else {
        $params['created'] = $row->created;
    }
    if (!$row->updated && ($row->created_by != $row->updated_by)) {
        echo "oops, no value in 'updated' for $row->date...\n";
    }
    if (!$row->created_by) {
        //echo "oops, no created_by...\n";
        //$row->created_by = $david;
        $params['created_by_id'] = $david;
    } else {
        $params['created_by_id'] = $user_map[$row->created_by] ?? $david;
    }
    if ($row->created && $row->updated && $row->created != $row->updated && !$row->updated_by) {
        //echo "oops. odd one at $row->date\n";
        $params['modified'] = $now;
        $params['modified_by_id'] = $david;
    } else {
        $params['modified'] = $row->updated;
        $params['modified_by_id'] = $user_map[$row->updated_by] ?? $david;
    }
    if ($params['modified'] < $params['created']) {
        $params['modified'] = $now;
        $params['modified_by_id'] = $david;
    }
    $params['week_of'] = $row->week_of;
    $insert->execute($params);
    $count++;
    echo "inserted $count MOTWs of $total\r";
}
