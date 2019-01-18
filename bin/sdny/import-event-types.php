#!/usr/bin/env php
<?php
/**
 * imports the old event-types into the new database, and creates a JSON data file
 * mapping old_id => new_id for later reference
 */

$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);
$old_db = new PDO('mysql:host=localhost;dbname=dev_interpreters', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);
/*
 mysql> explain event_types;
+-------------+----------------------+------+-----+---------+----------------+
| Field       | Type                 | Null | Key | Default | Extra          |
+-------------+----------------------+------+-----+---------+----------------+
| id          | smallint(5) unsigned | NO   | PRI | NULL    | auto_increment |
| category_id | smallint(5) unsigned | NO   | MUL | NULL    |                |
| name        | varchar(60)          | NO   | UNI | NULL    |                |
| comments    | varchar(150)         | NO   |     |         |                |
+-------------+----------------------+------+-----+---------+----------------+
 */

$event_categories = $db->query('SELECT category, id FROM event_categories')->fetchAll(\PDO::FETCH_KEY_PAIR);
$event_type_insert = $db->prepare('INSERT INTO event_types (name, category_id,comments) VALUES(:name,:category_id,:comments)');

$event_types_query = $old_db->query('SELECT proceeding_id, type, category, comments, display FROM proceedings ORDER BY type');

// one canonical kind of probation PSI interview, etc
// i.e., no variations based on location
foreach( [
    'probation PSI interview' => 'ID_PROBATION_PSI',
    "PTS supervision interview"  => 'ID_PTS_SUPERVISION',
    'probation supervision interview' => 'ID_PROBATION_SUPERVISION',] as $type => $varname) {
    try {
     $event_type_insert->execute([
        'name' => $type,'category_id' => $event_categories['out'],
         'comments' => '',
     ]);
     ${$varname} = $db->query('SELECT LAST_INSERT_ID()')->fetchColumn();
    } catch (PDOException $e) {
         if ($e->getCode() == 23000) {
             echo("'$type' already exists\n");
             ${$varname} = $db->query("SELECT id FROM event_types WHERE name = '$type'")->fetchColumn();
             continue;
         } else {
             throw $e;
         }
    }
}
$sql="select CONCAT(name, IF(parent IS NOT NULL,CONCAT('-',parent),'')) AS name, id FROM view_locations WHERE category REGEXP 'jail|probation|pretrial|cell|courthouse' ORDER BY name;";
$locations = $db->query($sql)->fetchAll(\PDO::FETCH_KEY_PAIR);
// old => new
$event_type_map = [];
// old-event => (new) location
$event_location_map = [];

while ($type = $event_types_query->fetch(PDO::FETCH_ASSOC)) {
    $do_insert = true;
    $is_probation_or_pts = false;
    if (stristr($type['type'],'probation')) {
        $this_id = stristr($type['type'],'supervision') ? $ID_PROBATION_SUPERVISION :  $ID_PROBATION_PSI;
        $event_type_map[$type['proceeding_id']] = $this_id;
        printf("not inserting event type: %s\n",$type['type']);
        $do_insert = false;
        $is_probation_or_pts = true;
    }
    if ( 1 === preg_match('/PTS.+supervision/i', $type['type'])) {
        $event_type_map[$type['proceeding_id']] = $ID_PTS_SUPERVISION;
        printf("not inserting event type: %s\n",$type['type']);
        $do_insert = false;
        $is_probation_or_pts = true;
    }
    /* map old event types to new locations */
    $location_id = null;
    if ($is_probation_or_pts and ! key_exists($type['proceeding_id'],$event_location_map)) {
        switch($type['type']) {
        case 'probation MCC Manhattan':
            $location_id = $locations['MCC Manhattan'];
            break;
        case 'probation MDC Brooklyn':
            $location_id = $locations['MDC Brooklyn'];
            break;
        case '7th flr probation':
        case '6th flr probation':
        case 'probation interview 500 Pearl':
        case 'probation supervision, 500 Pearl':
            $location_id = $locations['Probation-500 Pearl'];
            /* to do: make a note of which floor */
            break;
        case 'Valhalla probation':
            $location_id = $locations['Westchester County Jail'];
            break;
        case 'Rikers probation':
            $location_id = $locations['Rikers'];
            break;
        case 'White Plains probation':
            $locations['Probation-White Plains'];
            break;
        case 'probation phone interview':
            // assume interpreters office?
            break;
        case 'Putnam County probation':
            $location_id = $locations['Putnam County Jail'];
            break;
        case '233 Bway probation video':
        case '233 Bway probation/supervision':
        case '233 Bdwy probation':
        case 'PTS supervision, 233 Bway':
            $location_id = $locations['233 Broadway'];
            break;
        case 'Otisville probation':
            $location_id = $locations['FCI Otisville'];
            break;
        case 'probation field interview':
            // by definition we have no clue
            break;
        case 'Queens PCF probation':
            $location_id = $locations['Queens PCF'];
            break;
        case '4th flr cellblock probation':
            $location_id = $locations['4th floor cellblock-500 Pearl'];
            break;
        case 'Goshen County probation':
            $location_id = $locations['Orange County CF'];
            break;
        case 'PTS supervision, phone':
            // assume interpreters office?
            break;
        case 'PTS supervision, 500 Pearl':
            $location_id = $locations['500 Pearl'];
            break;
        case 'PTS supervision':
            // really not sure what|where this means
            break;
        default:
            printf("WARNING: can't figure out location for '%s'\n",$type['type']);
        }
        if ($location_id) {
            $event_location_map[$type['proceeding_id']] = $location_id;
        }
    }
    if (0 === strpos($type['type'],'TIP')) { // it is TIP
        if (stristr($type['type'],'500 Pearl')) {
            $location_id = $locations['500 Pearl'];
            $type['type'] = 'TIP';
        } else {
            $location_id = $locations['White Plains'];
        }
        $event_location_map[$type['proceeding_id']] = $location_id;
    }

    if (! $do_insert) {
        continue;
    }
    if (! $type['display']) {
        // it is deprecated. make a note so we can deal with it later
        if ($type['comments']) {
            $type['comments'] .= "\n\n";
        }
        $type['comments']  .= "this type is DEPRECATED (display = 0)";
    }
    if ('n/a' == $type['category']) {
        $type['category'] = 'not applicable';
    }

    try {
        //printf("inserting '%s' ... ",$type['type']);
        $event_type_insert->execute([
            'name' => $type['type'],
            'category_id' => $event_categories[$type['category']],
            'comments' => $type['comments']
        ]);
        $event_type_map[$type['proceeding_id']] =$db->query('SELECT LAST_INSERT_ID()')->fetchColumn();

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // to do: prepare, parameterize!
            $event_type_map[$type['proceeding_id']] = $db->query("SELECT id FROM event_types WHERE name = '{$type['type']}'")->fetchColumn();
            printf("'%s' is a duplicate, moving on\n",$type['type']);
        } else {
            printf("insertion of '%s' FAILED: %s\n",$type['type'], $e->getMessage());
        }
    }
}
$map = json_encode($event_type_map);
$path = __DIR__ . '/event-type-map.json';
$result = file_put_contents($path,$map);
if (false === $result) {
    echo "SHIT. failed writing \$event_type_map to $path\n";
    exit(1);
} elseif (0 === $result) {
    echo "SHIT. no data contained in our \$event_type_map";
    exit(1);
}
$event_locations = json_encode($event_location_map);
$path = __DIR__ . '/event-location-map.json';
$result = file_put_contents($path,$event_locations);
if (false === $result) {
    echo "SHIT. failed writing \$event_locations to $path\n";
    exit(1);
} elseif (0 === $result) {
    echo "SHIT. no data contained in our \$event_locations";
    exit(1);
}
exit(0);
