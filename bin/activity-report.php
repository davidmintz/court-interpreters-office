#!/usr/bin/env php
<?php
/**
* Generates a report as JSON for use in interpreters public site's FAQ file
*
* Prints to STDOUT so you can decide where to redirect it, e.g., in a cron task. 
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
		 PDO::ATTR_ERRMODE =>  PDO::ERRMODE_EXCEPTION
	]);
	

	$sql = 'select count(interpreters_events.event_id) AS `total`,
	languages.name AS language FROM interpreters_events JOIN events ON interpreters_events.event_id = events.id
	JOIN languages ON languages.id = events.language_id
	WHERE cancellation_reason_id IS NULL
	AND events.date >= "2001-06-01" AND events.date < CURDATE()
	AND languages.name <> "CART" /* fuck CART, it is NOT a language */
	GROUP BY language ORDER BY `total` DESC';
    $result = $db->query($sql);
    $data = $result->fetchAll(PDO::FETCH_ASSOC);

	if (! $result->rowCount()) {
		throw new \Exception("unexpected zero results size");
    }
    /* this will be removed */
    if (false){
		// create HTML table for FAQ about most-often used languages
	ob_start();?>
	<table id="language-usage">
		<thead>
			<th>language</th>
			<th>events</th>
		</thead>
		<tbody>
	<?php while ($row = $result->fetchObject()): ?>
			<tr>
				<td><?php echo $row->language?></td>
				<td class="numeric"><?php echo $row->total?></td>
			</tr>
	<?php endwhile; ?>
		</tbody>
	</table>
        <?php
	$table = ob_get_clean();

    }

	// write out a JSON data structure for recent activity report

	$date = new DateTime('last Friday');
	$last_friday = $date->format('Y-m-d');
	$where = "events.date BETWEEN DATE_SUB('$last_friday', INTERVAL 25 DAY) AND '$last_friday'";
	$sql = "SELECT COUNT(*) as `total`, SUM(IF(events.cancellation_reason_id IS NOT NULL,1,0)) AS `cancelled` 
    FROM interpreters_events JOIN events ON interpreters_events.event_id = events.id WHERE $where";

	$stmt = $db->query($sql);
	$result = $stmt->fetchObject();
	$stmt = $db->query("SELECT COUNT(distinct events.language_id) FROM events WHERE $where");
	$result->languages = $stmt->fetchColumn();
	$result->interval = '4 weeks';
	$result->end_date = $last_friday;
	$result->language_usage_data = $data;
    $json = json_encode($result);
    fwrite(STDOUT,$json);


} catch (\Exception $e) {

	echo $e->getMessage() . " in " . basename(__FILE__);
}
