#!/usr/bin/env php
<?php
/**
 * for importing events from our old database to the new - a work in progress
 */
require(__DIR__.'/../../vendor/autoload.php');
use Zend\Console\Getopt;

$opts = new Getopt(
   [
       'from|f=i' => "(starting) year from which to import events",
       'to|t-i'   => "ending year"
   ]
);
try { $opts->parse(); }
catch (\Exception $e) {
    echo $e->getUsageMessage();
    exit;
}
if (! $opts->from) {
    echo $opts->getUsageMessage();
    exit(1);
} else {
    if ($opts->from < 2001 or $opts->from > date('Y')+2) {
        echo $opts->getUsageMessage();
        echo "$opts->from is not a valid year.\n";
        exit(1);
    }
}
$from = $opts->from;
if ($opts->to) {
    if ($opts->to < $from) {
        echo $opts->getUsageMessage();
        echo "--to year must be later than $from\n";
        exit(1);
    }
}
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
// old => new
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
$users = [];
$user_data = $db
        ->query('select username, role_id, id as user_id, person_id, du.user_id as old_user_id from users JOIN dev_interpreters.users du ON du.name = username where users.role_id <> 1')
        ->fetchAll();
foreach($user_data as $u) {
    $username = $u['username'];
    unset($u['username']);
    $users[$username] = $u;
}
unset($user_data);

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

// default creator is me
$ID_DAVID = $db->query('select id from users where username = "david"')->fetchColumn();

//printf("david is %d\n",$ID_DAVID);exit(0);

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
$query .= "WHERE YEAR(event_date) ";
if ($opts->to) {
    $query .= " BETWEEN $from AND $opts->to";
    printf("fetching events for years %s through %s\n",$from,$opts->to);
} else {
    printf("fetching events for year %s\n",$from);
    $query .= " = $from";
}
$query .= " ORDER BY e.event_id";
exit;
$stmt = $db->prepare($query);
$stmt->execute();
$db->exec('use office');
$fucked = 0;
$count = 0;
$submitter_cache = [];
$person_sql = 'select p.id FROM people p WHERE p.hat_id = :hat_id AND lastname = :lastname AND firstname = :firstname';
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
while ($e = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $count++;
    $params = [];
    $meta_notes = '';
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
    // figure out the submitter !!!
    /*Array
(
    [id] => 57559
    [date] => 2009-01-12
    [time] => 12:00:00
    [end_time] =>
    [docket] => 2009CR00012
    [event_type_id] => 4
    [type] => sentence
    [language_id] => 62
    [language] => Spanish
    [judge_id] => 14
    [judge_lastname] => Kaplan
    [judge_firstname] => Lewis
    [submission_date] => 2009-01-06
    [submission_time] => 10:14:00
    [submitter_id] => 32
    [submitter_hat_id] => 5
    [submitter_hat] => ctroom staff
    [submitter_group] => Courtroom Deputy
    [submitter] => Mohan, Andy
    [submitter_group_id] => 1
    [created] => 2009-01-06 10:15:42
    [created_by] => 0
    [modified] => 2009-01-12 11:02:42
    [modified_by_id] => 2
    [cancel_reason] => N/A
    [comments] =>
    [admin_comments] =>
)
*/
    //print_r($e); //print_r($params); echo "\n===================================\n";
    //if ($count == 200) { break; }
    //printf("submitter: %s; id: %d; req_class id: %d\n",$e['submitter'] ?: "NULL",$e['submitter_id'],$e['submitter_hat_id']);
    // re-format the docket
    $docket = format_docket($e['docket']);
    if ($docket !== false) {
        $params[':docket'] = $docket;
    } else {
        printf("shit. could not format docket number for this event:\n%s",print_r($e,true));
    }
    if ($e['submitter']===NULL) {
        $fucked++;
        $meta_notes .= 'original request submitter unknown/unidentified. ';
        printf("cannot determine submitter for event id %d\n",$e['id']);
        // try to use creator as submitter
        if ($e['created_by_id']) {
            $meta_notes .= 'using event creator as fallback';
            $submitter = $users[$e['created_by']];
            $params[':submitter_id'] =$submitter['person_id'];
            
        } else {
            $meta_notes .= 'event creator unknown, using admin user david as fallback.';
            $params[':submitter_id'] = $users['david']['person_id'];
        }
        //echo "$meta_notes\n";
        /*(
    [id] => 1120
    [date] => 2001-07-05
    [time] => 09:00:00
    [end_time] => 
    [docket] => 2000CR01033
    [event_type_id] => 19
    [type] => probation MDC Brooklyn
    [language_id] => 62
    [language] => Spanish
    [judge_id] => 6
    [judge_lastname] => Preska
    [judge_firstname] => Loretta
    [submission_date] => 2001-07-02
    [submission_time] => 10:03:00
    [submitter_id] => 164
    [submitter_hat_id] => 1
    [submitter_hat] => AUSA
    [submitter_group] => 
    [submitter] => 
    [submitter_group_id] => 
    [created] => 2001-07-03 10:07:08
    [created_by_id] => 0
    [created_by] => eileen
    [modified] => 2003-03-03 11:14:00
    [modified_by_id] => 5
    [modified_by] => pat
    [cancel_reason] => N/A
    [comments] => 
    [admin_comments] => 
)
*/
       
    } elseif ($e['submitter']=='[anonymous]') {
        // what is the submitter hat?
        $hat_id = $e['submitter_hat_id'];
        if (isset($hats[$hat_id])) {
            $params[':anonymous_submitter_id'] = $hats[$hat_id];
            $params[':submitter_id']  = NULL;
        } else {
            // try to use original creator as submitter
            // otherwise fall back on me
            if ($e['created_by']) {
                
            } else {
                $params[':submitter_id'] = $ID_DAVID;
            }
            
            if ($e['admin_comments']) {
                $params[':admin_comments'] .="\n";
            }
            $params[':admin_comments'] .= "metadata formerly was: submitted by unidentified courtroom personnel";
        }
    } else {
        // figure out the person id!
        printf("figuring out person based on req_by = %d, req_class = %d a/k/a %s\n",
            $e['submitter_id'], $e['submitter_hat_id'], $e['submitter']);
        //printf("hat %s, hat id %d, group %s\n",);
    }
    //echo "looking good at iteration $count\r"; usleep(1000);


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
    //echo "\n";
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

printf("count: %d; fucked: %d\n",$count,$fucked);
printf("memory usage %.2f MB\n",memory_get_usage()/1000000);
printf("peak memory usage %.2f MB\n",memory_get_peak_usage()/1000000);




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
