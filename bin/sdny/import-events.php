#!/usr/bin/env php
<?php
/**
 * for importing events from our old database to the new - a work in progress
 */

$db = require(__DIR__."/connect.php");

// first: make sure all our non-courthouse locations have been inserted

/* map old event-types to locations */
$old_event_types = $db->query("SELECT proceeding_id as id, type as name FROM dev_interpreters.proceedings
  WHERE type REGEXP 'supervision|probation'")->fetchAll(PDO::FETCH_KEY_PAIR);

// because we can't know for sure the ids (unless we decide to delete all the locations
// and re-insert them with known ids -- maybe we should), use unique strings as keys
// and ids as values
$locations = $db->query("SELECT CONCAT(name,IF(parent IS NOT NULL,CONCAT(' - ',parent),'')) AS
name, id FROM  view_locations WHERE category NOT IN ('courtroom', 'courthouse') ORDER BY name")
        ->fetchAll(PDO::FETCH_KEY_PAIR);

$locations[''] = null;
// old_event_type_id => new_location_id
$event_locations = create_event_location_map($old_event_types);
$hats = [
    1 => 5,  // AUSA
    2 => 8,   // USPO
    6 => 12,  // Pretrial
    7 => 13,  // Magistrates
    3 => 1,   // staff interpreter
    4 => 3,   // contract interpreter
    5 => null, // either law clerk or courtroom dep
    8 => 4,    // defense attorney
    9 => null, // "other"
    10 => 11,   // usao staff
];
/*
echo "there are ".count($old_event_types). " items in \$old_event_types\n";
echo "there are ".count($event_locations). " items in \$event_locations\n";
$str = file_get_contents('./event-location-map.json');
$data = \json_decode($str,\JSON_OBJECT_AS_ARRAY);
echo "there are ".count($data). " items in whatever\n";
exit;
*/

$event_types = \json_decode(file_get_contents(__DIR__.'/event-type-map.json'),\JSON_OBJECT_AS_ARRAY);
if (! $event_types) {
    printf("failed to load %s at %d\n",__DIR__.'/event-type-map.json',__LINE__);
    exit(1);
}
$judge_sql = "SELECT oj.judge_id old_id, j.id FROM people j JOIN hats ON j.hat_id = hats.id JOIN dev_interpreters.judges oj WHERE hats.name = 'Judge' AND j.lastname = oj.lastname AND j.firstname = oj.firstname";
$judges = $db->query($judge_sql)->fetchAll(PDO::FETCH_KEY_PAIR);

/*      +----------+----------------------+
        | judge_id | lastname             |
        +----------+----------------------+
        |       22 | magistrate/NYC       |
        |       75 | [unknown]            |
        |       82 | [not applicable]     |
        |       85 | magistrate/W. Plains |
        +----------+----------------------+
        SELECT aj.id, aj.name, l.name from anonymous_judges aj LEFT JOIN locations l ON aj.default_location_id = l.id;
        +----+------------------+--------------+
        | id | name             | name         |
        +----+------------------+--------------+
        |  2 | (not applicable) | NULL         |
        |  3 | (unknown)        | NULL         |
        |  4 | magistrate       | White Plains |
        |  1 | magistrate       | 5A           |
        +----+------------------+--------------+
 */

// this is very brittle and will fuck up if we so much as look at it wrong
                    // theirs => ours
$anonymous_judges = [22 => 1, 85 => 4, 82 => 2, 75 => 3,];

// start with 3 months worth of (old) events data
//$from = 'DATE_SUB(CURDATE(), INTERVAL 2 MONTH)';
//$to   = 'DATE_ADD(CURDATE(), INTERVAL 1 MONTH)';

$query = file_get_contents(__DIR__.'/events-query.sql');

$insert = 'INSERT INTO events (
            id
            language_id
            judge_id
            submitter_id
            location_id
            date
            time
            end_time
            docket
            comments
            admin_comments
            created
            modified
            event_type_id
            created_by_id
            anonymous_judge_id
            anonymous_submitter_id
            cancellation_reason_id
            modified_by_id
            submission_datetime
        VALUES(
            :id
            :language_id
            :judge_id
            :submitter_id
            :location_id
            :date
            :time
            :end_time
            :docket
            :comments
            :admin_comments
            :created
            :modified
            :event_type_id
            :created_by_id
            :anonymous_judge_id
            :anonymous_submitter_id
            :cancellation_reason_id
            :modified_by_id
            :submission_datetime
        )';

$db->exec('use dev_interpreters');
$stmt = $db->prepare($query);
$stmt->execute();
$db->exec('use office');

$count = 0;
while ($e = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $count++;
    $params = [];
    // the easy ones
    foreach(['id','date','time','end_time','language_id','comments','admin_comments'] as $column) {
        $params[":{$column}"]=$e[$column];
    }
    // event type is mapped
    $params[':event_type_id'] = $event_types[$e['event_type_id']];

    // event locations is maybe mapped
    if (key_exists($e['event_type_id'],$event_locations)) {
        $params[':location_id'] = $event_locations[$e['event_type_id']];
    } else {
        $params[':location_id'] = null;
    }
    // figure out the judge
    if (isset($judges[$e['judge_id']])) {
        // problem solved
        $params[':judge_id'] = $judges[$e['judge_id']];
    } elseif (isset($anonymous_judges[$e['judge_id']])) {
        // likewise solved
        $params[':anonymous_judge_id'] = $anonymous_judges[$e['judge_id']];
    } else {
        // oops
        printf("shit. could not find any judge for this event:\n%s",print_r($e,true));
        exit(1);
    }
    //print_r($e); print_r($params); echo "\n===================================\n";
    //if ($count == 200) { break; }

    // re-format the docket
    $docket = format_docket($e['docket']);
    if ($docket !== false) {
        $params[':docket'] = $docket;
    } else {
        printf("shit. could not format docket number for this event:\n%s",print_r($e,true));
    }

    echo "looking good at iteration $count\r"; usleep(2000);

    // figure out the submitter !!!
/*
SELECT rb.id , h.id hat_id, h.name hat FROM dev_interpreters.request_class rb JOIN hats h ON rb.type = h.name;
+----+--------+-------------+
| id | hat_id | hat         |
+----+--------+-------------+
|  1 |      5 | AUSA        |
|  2 |      8 | USPO        |
|  6 |     12 | Pretrial    |
|  7 |     13 | Magistrates |
+----+--------+-------------+
*/

    // figure out other meta:  created_by, modified_by_id

  }
    echo "\n";
    /*Array
(
    [id] => 110885
    [date] => 2017-11-01
    [time] => 10:00:00
    [end_time] => 11:15:00
    [docket] => 2015CR00401
    [event_type_id] => 2
    [type] => plea
    [language_id] => 62
    [language] => Spanish
    [judge_id] => 73
    [judge_lastname] => Daniels
    [judge_firstname] => George
    [req_date] => 2017-10-30
    [req_time] => 16:25:00
    [req_by] => 458
    [req_class] => 5
    [created] => 2017-10-30 16:26:07
    [created_by] => 27
    [modified] => 2017-11-01 11:16:31
    [modified_by_id] => 29
    [cancel_reason] => N/A
    [comments] => delayed
    [admin_comments] =>
)
*/



//$db->exec('use office');
function format_docket($docket) {
    // expected format is e.g. YYYY[CR|MAG|CIV]NNNNN
    if (!$docket) { return ''; }

    if (! preg_match('/(\d{2})([A-Z]+)(\d{5})/', $docket, $matches)) {
         return false;
    }
    $return = "$matches[1]-$matches[2]-";
    // not too sure about this
    if (0 === strpos($matches[3],'0')) {
        $return .= substr($matches[3],1);
    } else {
        $return .= $matches[3];
    }
    return $return;
}


/**
 * map old event types to locations
 *
 * this implementation is more aggressive about deciding what event
 * type goes with which location than the one found in import-event-types.php
 *
 * @global type $locations
 * @param array $old_event_types
 * @return array
 */
function create_event_location_map(Array $old_event_types) {

    global $locations;

    $event_locations = [];

    foreach ($old_event_types as $id => $type) {
        //printf("looking at %s\n",$type);
        switch ($id) {

        case 6:
            $key = 'MCC Manhattan';
            break;
        case 19:
            $key = 'MDC Brooklyn';
            break;
        case 20:
        case 35:  // 6th or 7th floor
        case 85:
        case 98:
            $key = 'Probation - 500 Pearl';
            break;
        case 28:
            $key = 'Westchester County Jail';
            break;
        case 34:
            $key = 'Rikers';
            break;
        case 37:
            $key = 'Probation - White Plains';
            break;
        case 41: // phone interviews
        case 89:
            $key = 'Interpreters Office - 500 Pearl';
            break;
        case 50:
            $key = 'Putnam County Jail';
            break;
        case 53:
        case 65;
        case 68:
        case 72:
            $key = '233 Broadway';
            break;
        case 57:
            $key = 'FCI Otisville';
            break;
        case 62: // probation "field interview"
            $key = '';
            break;
        case 66:
            $key = 'Queens PCF';
            break;
        case 67:
            $key = '4th floor cellblock - 500 Pearl';
            break;
        case 84:
            $key = 'Orange County CF';
            break;
        case 95:
        case 97:
            $key = 'Pretrial - 500 Pearl';
            break;
        default:
            printf("ERROR: could not find a mapping for proceeding '$type',id $id at %d\n",__LINE__);
            exit(1);
        }
        if (! key_exists($key,$locations)) {
            printf("ERROR: could not find a mapping for location '$key' at %d\n",__LINE__);
            exit(1);
        }
        //printf("'$type' => %s\n",$key?:'<none>');
        $event_locations[$id] = $locations[$key];
    }
    return $event_locations;
}
exit(0);




/*
+----+----------------------------------+
| id | name                             |
+----+----------------------------------+
|  6 | probation MCC Manhattan          |
| 19 | probation MDC Brooklyn           |
| 20 | 7th flr probation                |
| 28 | Valhalla probation               |
| 34 | Rikers probation                 |
| 35 | 6th flr probation                |
| 37 | White Plains probation           |
| 41 | probation phone interview        |
| 50 | Putnam County probation          |
| 53 | 233 Bway probation video         |
| 57 | Otisville probation              |
| 62 | probation field interview        |
| 66 | Queens PCF probation             |
| 65 | 233 Bdwy probation               |
| 67 | 4th flr cellblock probation      |
| 68 | PTS supervision, 233 Bway        |
| 72 | 233 Bway probation/supervision   |
| 84 | Goshen County probation          |
| 85 | probation interview 500 Pearl    |
| 89 | PTS supervision, phone           |
| 95 | PTS supervision, 500 Pearl       |
| 97 | PTS Supervision                  |
| 98 | probation supervision, 500 Pearl |
+----+----------------------------------+
*/

/*

*/
