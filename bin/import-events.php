<?php
/**
 * for importing events - work in progress
 */

$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);
// first: make sure all our non-courthouse locations have been inserted

/* map old event-types to locations */

/*  SELECT dev_interpreters.proceeding_id as id, type as name FROM proceedings
  WHERE type REGEXP 'supervision|probation' ;
*/
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
mysql> SELECT id, CONCAT(name,IF(parent IS NOT NULL,CONCAT(' - ',parent),'')) AS 
name FROM  view_locations WHERE category NOT IN ("courtroom","courthouse") ORDER BY name;
+-----+---------------------------------+
| id  | name                            |
+-----+---------------------------------+
| 503 | 233 Broadway                    |
| 507 | 3rd floor cellblock - 40 Foley  |
|   4 | 4th floor cellblock - 500 Pearl |
|   5 | cafeteria - 500 Pearl           |
|   7 | Interpreters Office - 500 Pearl |
|  10 | MCC Manhattan                   |
|   9 | MDC Brooklyn                    |
| 504 | Pretrial - 500 Pearl            |
| 502 | Probation - 500 Pearl           |
| 506 | Probation - White Plains        |
| 509 | Queens PCF                      |
| 508 | Rikers                          |
| 505 | Westchester County Jail         |
+-----+---------------------------------+
13 rows in set (0.00 sec)

