<?php
/** @var  \PDO $pdo */
$pdo = require __DIR__.'/connect.php';
$q = 'SELECT * FROM rotation_substitutions';

$update = $pdo->prepare('UPDATE rotation_substitutions SET rotation_id = :rotation_id WHERE id = :id');
$find_rotation = $pdo->prepare(
    'SELECT id FROM rotations WHERE task_id = :task_id AND start_date <= :date ORDER BY start_date DESC limit 1'
);

$select = $pdo->prepare($q);
$select->execute();

while ($row = $select->fetch()) {
    $find_rotation->execute([
        'task_id' => $row['task_id'],
        'date' => $row['date'],
    ]);
    $rotation_id = $find_rotation->fetch(\PDO::FETCH_COLUMN);
    $update->execute(['rotation_id'=>$rotation_id,'id'=>$row['id']]);
}
