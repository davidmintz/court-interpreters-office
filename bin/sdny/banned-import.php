#!/usr/bin/env php
<?php
/** imports the banned-interpreter data from the old database to the new */

/** @var \PDO $pdo */
$pdo = require(__DIR__.'/connect.php');

$sql = 'SELECT DISTINCT j.judge_id, p.id
FROM dev_interpreters.judges j
-- to try to slurp up all of them, remove this JOIN
JOIN dev_interpreters.interpreters_judges_exclusions b ON b.judge_id = j.judge_id
LEFT JOIN people p
ON (j.lastname = p.lastname AND (INSTR(p.firstname, j.firstname) OR INSTR(j.firstname,p.firstname)))
WHERE p.discr = "judge" AND j.judge_id NOT IN (85) ORDER BY j.lastname';

$judge_map = $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
$sql = 'SELECT DISTINCT r.id AS old_id, p.id AS new_id FROM dev_interpreters.request_users AS r JOIN dev_interpreters.interpreters_uspos_exclusions b ON r.id = b.uspo_id LEFT JOIN people p ON r.email = p.email';
$uspo_map = $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);

// happily, the interpreter ids are the same.

$insert_stmt = $pdo->prepare("INSERT INTO banned (interpreter_id, person_id) VALUES (:interpreter, :person)");

$banned_by_judges = $pdo->query('SELECT * FROM dev_interpreters.interpreters_judges_exclusions b WHERE b.judge_id NOT IN (85)');

$n = 0;
 while ($record = $banned_by_judges->fetch(PDO::FETCH_OBJ)) {
     $params = [
         ':interpreter' => $record->interpreter_id,
         ':person' => $judge_map[$record->judge_id]
     ];
     $insert_stmt->execute($params);
     $n++;
 }

$banned_by_uspo = $pdo->query('SELECT * FROM dev_interpreters.interpreters_uspos_exclusions b');
while ($record = $banned_by_uspo->fetch(PDO::FETCH_OBJ)) {
    $params = [
        ':interpreter' => $record->interpreter_id,
        ':person' => $uspo_map[$record->uspo_id]
    ];
    $insert_stmt->execute($params);
    $n++;
}
echo "completed $n inserts of banned-interpreter records\n";
