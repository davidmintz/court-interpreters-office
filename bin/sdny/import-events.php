#!/usr/bin/env php
<?php
/**
 * for importing events from our old database to the new
 */
require(__DIR__.'/../../vendor/autoload.php');
use Zend\Console\Getopt;

$opts = new Getopt(
   [
       'from|f=i' => "(starting) year from which to import events",
       'to|t-i'   => "optional ending year of range (inclusive)",
       'begin-after-id-i' => "optional event id to begin after",
       'import-only-s' => "event id(s) to import, one or more comma-separated",
       'refresh-related-entities' => "whether to purge and reload defendants_events and interpreters_events",
   ]
);
try { $opts->parse(); }
catch (\Exception $e) {
    echo $e->getUsageMessage();
    exit(1);
}

//$opts->{'refresh-related-entities'}

$ids_to_import = $opts->{'import-only'};

if (! $ids_to_import and ! $opts->from) {
    echo $opts->getUsageMessage();
    exit(1);
}
if (! $ids_to_import) {
    if ($opts->from < 2001 or $opts->from > date('Y')+2) {
        echo $opts->getUsageMessage();
        echo "$opts->from is not a valid year.\n";
        exit(1);
    }
    $from = $opts->from;
    if ($opts->to) {
        if ($opts->to < $from) {
            echo $opts->getUsageMessage();
            echo "--to year must be later than $from\n";
            exit(1);
        }
    }
}
$id_to_begin_after = $opts->{'begin-after-id'};
/** @var $db \PDO */
$db = require(__DIR__."/connect.php");
$now = date("M-d-y H:i:s");
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

$cancellations = array_flip(['N/A','deft not produced','no interpreter needed','adjourned w/o notice','party did not appear','forçe majeure','reason unknown','other']);

// good to know: WHERE REGEXP '.*mag(i?s(trates?)?)? +cal'

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
//
$shit = 'SELECT l.name location ,aj.id magistrate_id from anonymous_judges aj LEFT JOIN locations l ON aj.default_location_id = l.id WHERE l.name IS NOT NULL';
$magistrates = $db->query($shit)->fetchAll(PDO::FETCH_KEY_PAIR);
$anonymous_judges = [
    22 => $magistrates['5A'], // magistrate NY
    85 => $magistrates['White Plains'], // magistrate White Plains
    82 => 2,
    75 => 3,
];
/*SELECT lastname,judge_id FROM dev_interpreters.judges WHERE lastname REGEXP 'applicable|unknown|magistrate';
+----------+----------------------+
| judge_id | lastname             |
+----------+-----------------------
|       22 | magistrate/NYC
|       75 | [unknown]
|       82 | [not applicable]
|       85 | magistrate/W. Plains
+----------+----------------------+
*/
// default creator is me
$USER_ID_DAVID = $db->query('select id from users where username = "david"')->fetchColumn();

$user_person_sql = 'select DISTINCT p.id FROM people p JOIN users u ON p.id = u.person_id WHERE p.active = :active AND p.email = :email';
$user_person_stmt = $db->prepare($user_person_sql);
$submitter_person_sql =  'SELECT p.id FROM people p WHERE hat_id = :hat_id AND lastname = :lastname AND firstname = :firstname';
$submitter_person_stmt = $db->prepare($submitter_person_sql);

// start with 3 months worth of (old) events data
//$from = 'DATE_SUB(CURDATE(), INTERVAL 2 MONTH)';
//$to   = 'DATE_ADD(CURDATE(), INTERVAL 1 MONTH)';

$query = file_get_contents(__DIR__.'/events-query.sql');

$insert_sql = 'INSERT INTO events (
            id,
            language_id,
            judge_id,
            submitter_id,
            location_id,
            date,
            time,
            end_time,
            docket,
            comments,
            admin_comments,
            created,
            modified,
            event_type_id,
            created_by_id,
            anonymous_judge_id,
            anonymous_submitter_id,
            cancellation_reason_id,
            modified_by_id,
            submission_date,
            submission_time)
        VALUES(
            :id,
            :language_id,
            :judge_id,
            :submitter_id,
            :location_id,
            :date,
            :time,
            :end_time,
            :docket,
            :comments,
            :admin_comments,
            :created,
            :modified,
            :event_type_id,
            :created_by_id,
            :anonymous_judge_id,
            :anonymous_submitter_id,
            :cancellation_reason_id,
            :modified_by_id,
            :submission_date,
            :submission_time
        )';

$db->exec('use dev_interpreters');
$count_sql = '';
if (! $ids_to_import) {
    $query .= "WHERE YEAR(event_date) ";

    if ($opts->to) {
        $what =  " BETWEEN $from AND $opts->to";
        $query .= $what;
        $count_sql = "SELECT COUNT(*) `total` FROM events e WHERE YEAR(event_date) $what";
        printf("\nfetching events for years %s through %s\n",$from,$opts->to);
    } else {
        printf("\nfetching events for year %s\n",$from);
        $query .= " = $from";
        $count_sql = "SELECT COUNT(*) `total` FROM events e WHERE YEAR(event_date) = $from ";
    }
    if ($id_to_begin_after) {
        $query .= ' AND e.event_id > '.$id_to_begin_after;
        $count_sql .=  ' AND e.event_id > '.$id_to_begin_after;
    }
} else {
    $query .= " WHERE e.event_id IN ($ids_to_import)";
}

$query .= " ORDER BY e.event_id";
if ($count_sql) {
    $total = $db->query($count_sql)->fetchColumn();
} else {
    $total = null;
}
$stmt = $db->prepare($query);
$stmt->execute();
$db->exec('use office');
$event_insert = $db->prepare($insert_sql);
//$fucked = 0;
$count = 0;
$submitter_cache = [];

$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
while ($e = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //if ($e['id'] != 4011) { continue; }
    $count++;
    $params = [];
    $meta_notes = '';
    // the easy ones
    foreach(['id','date','time','end_time','language_id','comments','admin_comments','submission_date','submission_time','created','modified'] as $column) {
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
        $params[':anonymous_judge_id'] = null;
    } elseif (isset($anonymous_judges[$e['judge_id']])) {
        // likewise solved
        $params[':anonymous_judge_id'] = $anonymous_judges[$e['judge_id']];
        $params[':judge_id'] = null;
    } else {
        // oops
        printf("shit. could not find any judge for this event:\n%s",print_r($e,true));
        exit(1);
    }

    // re-format the docket
    $docket = format_docket($e['docket']);
    if ($docket !== false) {
        $params[':docket'] = $docket;
    } else {
        printf("shit. could not format docket number for this event:\n%s",print_r($e,true));
    }
    // figure out the submitter !!! ///////////////////////////////////////////
    /** this is so twisted, it's really embarassing. */
    if ($e['submitter']===NULL) {
        //$fucked++;
        $meta_notes .= 'original request submitter unknown/unidentified. ';
        //printf("cannot determine submitter for event id %d\n",$e['id']);
        // try to use creator as submitter
        if ($e['created_by_id']) {
            $meta_notes .= 'using event creator as fallback';
            $submitter = $users[$e['created_by']];
            $params[':submitter_id'] = $submitter['person_id'];

        } else {
            $meta_notes .= 'event creator unknown, using admin user david as fallback.';
            $params[':submitter_id'] = $users['david']['person_id'];
        }

    } elseif ($e['submitter']=='[anonymous]') {

        // anonymous submitter. what is the submitter hat?
        $hat_id = $e['submitter_hat_id'];
        if (isset($hats[$hat_id])) {
            $params[':anonymous_submitter_id'] = $hats[$hat_id];
            $params[':submitter_id']  = NULL;
            //printf("at %d SHIT IS NOW submitter id %s, anon submitter is %s\n", __LINE__,$params[':submitter_id']?:"NULL",$params[':anonymous_submitter_id']?:"NULL");
        } else {
            // try to use original creator as submitter
            // otherwise fall back on me
            if ($e['created_by_id']) {
                if (key_exists($e['created_by'],$users)) {
                    $user = $users[$e['created_by']];
                    $params[':submitter_id'] = $user['person_id'];
                } else {
                    $params[':submitter_id'] = $users['david']['person_id'];
                }
            } else {
                $params[':submitter_id'] = $users['david']['person_id'];
                $params[':anonymous_submitter_id'] = null;
            }
            //printf("\nthe submitter id is now: %s\n",$params[':submitter_id']?:"NULL");
            //printf("at %d SHIT IS NOW submitter id %s, anon submitter is %s\n", __LINE__,$params[':submitter_id']?:"NULL",$params[':anonymous_submitter_id']?:"NULL");

        }
        if (! in_array($e['submitter_hat'],['Pretrial','Magistrates','defense atty'])) {
            $meta_notes .= sprintf("metadata formerly was: submitted by unidentified %s.",
                     $e['submitter_hat'] ?: 'person'
            );
        }


    } else { // submitter is non-anonymous, and not NULL

        if (! key_exists($e['submitter_hat_id'],$hats)) {
            echo "FUCK? no hat equivalent to {$e['submitter_hat_id']}\n";
            exit(1);
        }

        if ($e['submitter_group'] == 'Pretrial Services Officer') {
            $submitter_hat_id = 9;
        } elseif ($e['submitter_group'] == 'Law Clerk') {
            $submitter_hat_id = 7;
        } elseif ($e['submitter_group'] == '[group unknown]') {
            // we will have to eat it
            $params[':submitter_id']  = isset($users['created_by']) ?
                    $users['created_by']['person_id']
                    : $users['david']['person_id'];
            $submitter_hat_id = null;
        } else {
            $submitter_hat_id = $hats[$e['submitter_hat_id']];
        }
        //printf("\nSHIT IS RUNNING AT %d with {$e['id']}\n",__LINE__);
        if (null === $submitter_hat_id) {

            if ($e['submitter_group'] == '[group unknown]') {
                // same deal: fall back on creator if possible
                if ($e['created_by'] && key_exists($e['created_by'],$users)) {
                    $user = $users[$e['created_by']];
                    $params[':submitter_id'] = $user['person_id'];
                } else {
                    $params[':submitter_id'] = $users['david']['person_id'];
                }
                $meta_notes .= sprintf("metadata formerly was: submitted by %s.",
                $e['submitter']);

            } else {
                // submitter group is NOT "unknown", therefore submitter
                // should be in the database

                $key = sprintf("%d-%d",$e['submitter_hat_id'],$e['submitter_id']);
                if (key_exists($key,$submitter_cache)) {
                    $params[':submitter_id'] = $submitter_cache[$key];
                    //printf("found %s in cache\n",$e['submitter']);
                } else {
                    $user_person_stmt->execute([
                       ':active'=>$e['submitter_active'],
                       ':email' => $e['submitter_email']
                    ]);
                    $rows = $user_person_stmt->fetchAll();
                    $row_count = count($rows);
                    if ($row_count > 1) {
                        printf("ambiguous identity in event id %d\n",$e['id']);
                        print_r($e); print_r($rows);
                        exit(1);
                    }
                    if (! $row_count) {
                        printf("could not locate submitter for event id %d: %s\n",
                                $e['id'],print_r($e,true));
                        exit(1);
                    }
                    $id = $rows[0]['id'];
                    $submitter_cache[$key]=$id;
                    $params[':submitter_id'] = $id;
                    //printf("queried db for %s, cached as $key\n",$e['submitter']);
                }
            }

        } else { // i.e., submitter_hat_id is NOT NULL

            if (1 == $submitter_hat_id) {
                // a staff user
                $user = explode('; ',$e['submitter'])[0];
                if ('eileen'==$user) {
                    print_r($e); exit(1);
                } else {
                    //echo "submitter seems to be: $user\n"; continue;
                    $params[':submitter_id'] = $users[$user]['person_id'];
                }

            } else {

                if ($e['submitter_group'] !== '[group unknown]') {
                    // then it has NOT yet been resolved
                    $key = sprintf("%d-%d",$e['submitter_hat_id'],$e['submitter_id']);
                    if (key_exists($key, $submitter_cache)) {
                        $params[':submitter_id'] = $submitter_cache[$key];
                    } else {
                        //lastname: Chan; firstname: Andrew
                        preg_match('/lastname: (.+); firstname: (.*)$/',$e['submitter'],$n);
                        if (!$n) {
                            echo "FUCK????\n"; echo $e['submitter'],"\n";
                            print_r($e);
                            exit(1);
                        }

                        $shit = [':lastname' => $n[1], ':firstname' => $n[2], ':hat_id' => $submitter_hat_id];
                        $submitter_person_stmt->execute($shit);
                        $data = $submitter_person_stmt->fetchAll();
                        $size = count($data);
                        if ($size > 1) {
                            printf("%d: ambiguous identity for submitter, event id %d: %s\n",
                            __LINE__,
                            $e['id'],print_r($e,true));
                            exit(1);
                            //$fucked++; continue;
                        } elseif (!$size) {
                            printf("%d: no identity found for submitter, event id %d: %s\n",
                                __LINE__,
                                $e['id'],print_r($e,true));
                            echo "QUERY IS:\n$submitter_person_sql\nPARAMS ARE: ";
                            print_r($shit); exit(1);
                        }
                        $params[':submitter_id'] = $data[0]['id'];
                        $submitter_cache[$key] =  $data[0]['id'];
                    }
                } else {
                    // yes? we must have been thinking something here
                }
            }
        }
    }
    if (isset($params[':submitter_id'])) {
        $params[':anonymous_submitter_id'] = null;
    }
    /// ------------   end figure out submitter identity ///////////////////////

    // figure out other meta:  created_by_id, modified_by_id
    if (! $e['created_by_id'] or ! key_exists($e['created_by'], $users)) {
        $meta_notes .= "\nidentity of original creator unknown --DMz $now";
        $params[':created_by_id'] = $USER_ID_DAVID;
    } else {
        $params[':created_by_id'] = $users[$e['created_by']]['user_id'];
    }
    if (! key_exists($e['modified_by'], $users) or !$e['modified_by_id']  ) {
        $meta_notes .= "\nidentity of original last-updated-by unknown --DMz $now";
        $params[':modified_by_id'] = $USER_ID_DAVID;
    } else {
         $params[':modified_by_id'] = $users[$e['modified_by']]['user_id'];
    }

    if ($e['cancel_reason'] == 'N/A') {
        $params[':cancellation_reason_id'] = NULL;
    } else {
        $params[':cancellation_reason_id'] = $cancellations[$e['cancel_reason']];
        if ($e['cancel_reason'] == 'forçe majeure') {
            $params[':cancellation_reason_id'] = 5; // trust me
        }
    }

    if ($meta_notes) {
        $meta_notes = trim($meta_notes);
        if ($params[':admin_comments']) {
            $params[':admin_comments'] .= "\n\n".$meta_notes;
        } else {
            $params[':admin_comments'] = $meta_notes;
        }
    }
    if (! ($params[':anonymous_submitter_id'] === null xor $params[':submitter_id'] === null)) {
        printf("shit failed anon-submitter XOR test at %d, parameters %s, data %s\n",
                __LINE__,print_r($params,true),print_r($e,true));
        exit(1);
    }
    if (! ($params[':anonymous_judge_id'] === null xor $params[':judge_id'] === null)) {
        printf("shit failed anon-judge XOR test at %d, parameters %s, data %s\n",
                __LINE__,print_r($params,true),print_r($e,true));
        exit(1);
    }

    try {
        $event_insert->execute($params);
    } catch (Exception $ex) {
        printf("shit. insert failed with event %d, data %s, params %s\nexception: %s\n",
               $e['id'], print_r($e,true),print_r($params,true), $ex->getMessage()
        );
        echo "YOU HAVE ".count($params). " parameters\n";
        exit(1);
    }
    $progress = "looking good at iteration $count";
    if ($total) {
        $progress .= " of $total";
    }
    echo "$progress\r";
}

if ($opts->{'refresh-related-entities'}) {
    /** @var $db \PDO */
    $db->beginTransaction();
    echo("\npurging defendants_events...");
    try {
        $db->exec('DELETE FROM defendants_events');
        $db->exec('DELETE FROM defendant_names');
        echo "importing defendant names...\n";
        $db->exec('INSERT INTO defendant_names (id, given_names, surnames)
        (SELECT deft_id, firstname, lastname FROM dev_interpreters.deft_names ORDER BY deft_id)');
        echo "importing defendants_events...\n";
        $db->exec('INSERT INTO defendants_events (defendant_id,event_id) (SELECT deft_id, event_id FROM dev_interpreters.deft_events)');
        $db->commit();
        echo "finished defendants_events.\n";
    } catch (\Exception $e) {
        $db->rollBack();
        printf("shit. failed with exception %s\n",$e->getMessage());
    }
    try {
        echo "attempting interpreter_events import. purging interpreters_events...";
        $db->beginTransaction();
        $db->exec('DELETE FROM interpreters_events');
        $db->exec('use dev_interpreters');
        echo "importing interpreters_events...\n";
        $db->exec("INSERT INTO office.interpreters_events (interpreter_id, event_id, created, created_by_id)
        (SELECT interp_id, ie.event_id, ie.created,  COALESCE(u2.id,$USER_ID_DAVID) AS created_by
            FROM interp_events ie
            LEFT JOIN users u ON ie.created_by = u.user_id
            LEFT JOIN office.users u2 ON u.name = u2.username)");
        $db->commit();
        echo "finished importing interpreters_events.\n";
    } catch (\Exception $e) {
        $db->rollBack();
        printf("shit. failed with exception %s\n",$e->getMessage());
    }
}
/* set @user_david = (SELECT id FROM office.users WHERE username = 'david');
use dev_interpreters;
INSERT INTO office.interpreters_events (interpreter_id, event_id, created, created_by_id)
(SELECT interp_id, ie.event_id, ie.created,  COALESCE(u2.id,@user_david) AS created_by FROM interp_events ie
    LEFT JOIN users u ON ie.created_by = u.user_id
    LEFT JOIN office.users u2 ON u.name = u2.username );*/
printf("\nevent inserts: %d\n",$count);
printf("memory usage %.2f MB\n",memory_get_usage()/1000000);
printf("peak memory usage %.2f MB\n",memory_get_peak_usage()/1000000);




//$db->exec('use office');
function format_docket($docket) {
    // expected format is e.g. YYYY[CR|MAG|CIV]NNNNN
    if (!$docket) { return ''; }

    if (! preg_match('/(\d{4})([A-Z]+)(\d{5})/', $docket, $matches)) {
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
