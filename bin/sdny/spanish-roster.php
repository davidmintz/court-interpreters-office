#!/usr/bin/env php
<?php
/**
* Generates a roster of Spanish interpreters as JSON
*
* E.g., for use in interpreters public website. Prints to STDOUT so you can decide 
* where to redirect it, e.g., in a cron task.
*
* Note that it has to run as a user whose $HOME has a .my.cnf file with database
* connection parameters, and that the name of the database is presumed to be "office".
*
*/

$HOME = getenv('HOME');
$config_file  = "$HOME/.my.cnf";
$db = 'office';
try {
    $config = parse_ini_file($config_file);
    $host = $config['host']??'localhost';
	$db = new PDO("mysql:host={$host};dbname=$db",
		$config['user'], $config['password'],[
		 PDO::ATTR_ERRMODE =>  PDO::ERRMODE_EXCEPTION,
		 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $sql = 'SELECT DISTINCT CONCAT(lastname,", ",firstname) AS name, mobile_phone, office_phone, email
    FROM interpreters i JOIN people p ON i.id = p.id
    JOIN interpreters_languages il ON i.id = il.interpreter_id 
    JOIN language_credentials cred ON il.credential_id = cred.id
    JOIN languages l ON l.id = il.language_id 
    WHERE l.name = "Spanish" AND i.publish_public  AND cred.abbreviation = "AO" AND p.active
    AND state IN  ("NY","NJ","CT","PA") AND (mobile_phone <> "" OR office_phone <> "")  ORDER BY lastname, firstname';
    $result = $db->query($sql);
    $data = $result->fetchAll();
    if (! $result->rowCount()) {
		throw new \Exception("unexpected zero results size");
    }
    $json = json_encode($data);
    fwrite(STDOUT,$json);
} catch (\Exception $e) {
    fwrite(STDERR,$e->getMessage());
}
