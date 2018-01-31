#!/usr/bin/env php
<?php
$db = require(__DIR__."/connect.php");
$db->exec("use dev_interpreters");
$sql=
'SELECT DISTINCT user_id, name, email, interpreter_id FROM users LEFT JOIN events 
ON (events.lastmod_by = user_id OR events.created_by = user_id) 
WHERE COALESCE(lastmod_by, created_by) 
IS NOT NULL OR (req_class = 3 AND req_by = users.user_id)';

$staff_users = $db->query($sql)->fetchAll();

print_r($staff_users);



