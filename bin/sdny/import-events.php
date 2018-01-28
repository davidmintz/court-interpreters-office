#!/usr/bin/env php
<?php
/**
 * for importing events from our old database to the new - a work in progress
 */

$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);

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
$event_types = \json_decode(file_get_contents(__DIR__.'/event-type-map.json'));
if (! $event_types) {
    printf("failed to load \$event_types at %d\n",__LINE__); exit(1);
}

// start with 3 months worth of (old) events data

$from = 'DATE_SUB(CURDATE(), INTERVAL 2 MONTH)';
$to   = 'DATE_ADD(CURDATE(), INTERVAL 1 MONTH)';

$languages = $db->query(
    'SELECT dev_interpreters.languages.lang_id as old_id, l.id AS new_id 
    FROM dev_interpreters.languages JOIN languages l 
    ON dev_interpreters.languages.name = l.name')
    ->fetchAll(PDO::FETCH_KEY_PAIR);

//$db->exec('use dev_interpreters');
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

while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $params = [];
    print_r($event);
    break;    
}

$db->exec('use office');



function create_event_location_map(Array $old_event_types) {
    
    global $locations;
    
    $event_locations = [];
    
    foreach ($old_event_types as $id => $type) {
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
        //printf("saving event-type '$type' as location %s\n",$key?:'<none>');        
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