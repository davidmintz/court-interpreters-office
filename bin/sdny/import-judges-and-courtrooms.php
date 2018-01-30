#!/usr/bin/env php
<?php
/**
 * imports judges and courtrooms using JSON data coming from stdin
 */

$json = file_get_contents("php://stdin");
$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);

$data = json_decode($json,JSON_OBJECT_AS_ARRAY);
if (! $data) {
    exit("failed decoding json from stdin\n");
}
const TYPE_COURTHOUSE = 7;
const TYPE_COURTROOM = 1;
$VERBOSITY = false;

function debug($message) {
    
    global $VERBOSITY;
    if ($VERBOSITY) {
        echo "$message\n";
    }
}

$hat_id = $db->query('SELECT id from hats WHERE name = "Judge"')->fetchColumn();
$flavors = $db->query('SELECT flavor, id FROM judge_flavors')->fetchAll(PDO::FETCH_KEY_PAIR);
// translations
$flavors['District'] = $flavors['USDJ'];
$flavors['Magistrate'] = $flavors['USMJ'];

$active = 1;
$courthouses =  $db->query('SELECT name,id FROM locations WHERE type_id = '.TYPE_COURTHOUSE)->fetchAll(PDO::FETCH_KEY_PAIR);
$courthouses['300 Quarropas'] = $courthouses['White Plains'];  // alias

$courtrooms = $db->query('SELECT CONCAT(name,"-",parent) AS location, id FROM view_locations WHERE category = "courtroom"')
        ->fetchAll(PDO::FETCH_KEY_PAIR);

$person_insert = $db->prepare(
    'INSERT INTO people (hat_id,lastname,firstname,middlename,active,discr) 
        VALUES (:hat_id,:lastname,:firstname,:middlename,:active, "judge")'
);

$judge_insert = $db->prepare(
        'INSERT INTO judges (id,default_location_id, flavor_id) '
        . 'VALUES (:id,:default_location_id,:flavor_id)');

$location_insert = $db->prepare(
    'INSERT INTO locations (type_id, parent_location_id,name,active) '
        . 'VALUES (:type_id,:parent_location_id,:name,1)'
);

$location_select = $db->prepare('SELECT id FROM locations WHERE name = :name and type_id = :type_id');

$judge_select = $db->prepare('SELECT p.id, p.lastname, p.firstname, p.middlename, '
        . 'f.flavor, f.id AS flavor_id, l.id AS location_id, l.name as location, '
        . 'pl.name as parent_location FROM people p JOIN judges j ON p.id = j.id '
        . 'JOIN judge_flavors f ON f.id = j.flavor_id '
        . 'LEFT JOIN locations l ON j.default_location_id = l.id '
        . 'LEFT JOIN locations pl ON l.parent_location_id = pl.id '
        . 'WHERE lastname = :lastname AND firstname = :firstname '
        . 'AND middlename = :middlename ');

$judge_update = null;

foreach ($data as $flavor => $judge) {
    
    foreach ($judge as $name => $location) {
        
        // e.g: [Swain, Laura Taylor] => 17C, 500 Pearl
        list($lastname,$given_names) = preg_split('/, +/',$name);    
        if (strstr($given_names,' ')) {
            list($firstname,$middlename) = preg_split('/ +/',$given_names);
        } else {
            $firstname = $given_names; 
            $middlename = '';
        } 
        // BUT!
        if (preg_match('/^[A-Z]\. +\S+$/',"$firstname $middlename")) {
            $firstname .= " $middlename";
            $middlename = '';            
        }
        
        list($courtroom, $courthouse) = preg_split('/, +/',$location);
        // check the location
        if ($courthouse == '300 Quarropas') {
            $courthouse = 'White Plains';
        }
        $key = "$courtroom-$courthouse";
        $location_id = key_exists($key,$courtrooms) ? $courtrooms[$key] : false;
        if (! $location_id) {
            // location not found, needs to be inserted
            debug(sprintf("inserting new location at line %d",__LINE__));
            try {
                $location_insert->execute([
                    ':type_id'=>  TYPE_COURTROOM,
                    ':name' => $courtroom,
                    ':parent_location_id' => $courthouses[$courthouse],
                ]);
                $location_id =  $db->query('SELECT last_insert_id()')->fetchColumn();
                echo "inserted new location $courtroom with id $location_id\n";
                $courtrooms[$key] = $location_id;

            } catch (PDOException $e) {
                printf("location insert FAILED: %s\n",$e->getMessage());
            }
        }
        // see if the judge already exists
        $judge_select->execute(compact('lastname','firstname','middlename'));
        $judge_found = $judge_select->fetch(PDO::FETCH_ASSOC);
        
        if ($judge_found) {
            debug(sprintf("founding existing judge %s at line %d",$judge_found['lastname'],__LINE__));
            // check the flavor            
            if ($flavors[$flavor] == $judge_found['flavor_id']) {
                // we very likely have this one already in the db, so no insert
            } else {
                printf("WARNING: found %s, %s %s %s in the database but input data says $flavor\n",
                        $judge_found['lastname'],$judge_found['firstname'],
                        $judge_found['middlename'],  $judge_found['flavor']
                );
            }
            // see if location needs an update
            if ($judge_found['location'] != $courtroom 
                    or $judge_found['parent_location'] != $courthouse) {
                if (!$judge_update) {
                    $judge_update = $db->prepare(
                       'UPDATE judges SET default_location_id = :location_id '
                        . ' WHERE id = :id');
                }
                debug(sprintf("updating courtroom for judge %s at line %d",$judge_found['lastname'],__LINE__));
                $judge_update->execute([
                    'id'=>$judge_found['id'],'location_id'=>$location_id]
                );                
            }
        } else {
            // judge NOT found, needs to be inserted
            try {
                debug("inserting new judge ($lastname) at ".__LINE__);
                $person_insert->execute(
                    compact('hat_id','lastname','firstname','middlename','active')
                );
                $id = $db->query('SELECT last_insert_id()')->fetchColumn();

                $judge_insert->execute([
                    ':id'=>$id,
                    ':default_location_id'=>$location_id,
                    ':flavor_id' => $flavors[$flavor],
                ]);
                printf("judge %s added to people, judges with id %s\n",$lastname,$id);

            } catch (PDOException $e) {
                printf("insert FAILED: %s\n",$e->getMessage());
            }
        }        
    }
}
/* now, the dead judges */

$old_db = new PDO('mysql:host=localhost;dbname=dev_interpreters', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);

// find judges from old database that are NOT in the new one
$judge_sql = 'SELECT lastname, firstname, middlename, flavor, IF(judges.active="Y",1,0) AS active '
        . 'FROM dev_interpreters.judges WHERE CONCAT(lastname,"-",firstname) '
        . 'NOT IN (SELECT CONCAT(lastname,"-",firstname) FROM office.people p '
        . 'WHERE p.discr = "judge") AND firstname <> ""';

$results = $old_db->query($judge_sql,PDO::FETCH_ASSOC);

while ($j = $results->fetch()) {
    extract($j);
    $active = $active == 'Y' ? 1 : 0 ;
    if (!$flavor) {
        $flavor = $flavors['USBJ'];
    }
    try {
        debug("inserting inactive judge ($lastname) at ".__LINE__);
        $person_insert->execute(
            compact('hat_id','lastname','firstname','middlename','active')
        );
        $id = $db->query('SELECT last_insert_id()')->fetchColumn();
        $judge_insert->execute([
            ':id'=>$id,
            ':default_location_id'=>NULL,
            ':flavor_id' => $flavors[$flavor],
        ]);
    } catch (PDOException $e) {
        printf("shit: %s\n",$e->getMessage());
    }        
}

exit(0);