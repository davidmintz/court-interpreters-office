UPDATE request_users SET email = null where id = 590;
UPDATE request_users SET email = 'jineen_forbes@nysp.uscourts.gov', mobile_phone = '9175777303',last_login = 1578949228 WHERE id = 281;
UPDATE events SET req_by = 281, lastmod = lastmod WHERE req_by = 590 AND req_class = 2;
UPDATE requests SET created_by = 281, modified = modified, last_modified_by = 281 WHERE created_by = 590;
DELETE FROM request_users WHERE id = 590;

-- do away with cynthia markley

DELETE cj FROM clerks_judges cj LEFT JOIN judges j ON j.judge_id = cj.judge_id WHERE j.judge_id IS NULL;
UPDATE events SET req_by = created_by, lastmod = lastmod, req_class = 3 WHERE req_by = 544 AND req_class = 5;
DELETE FROM request_users WHERE id = 544;
DELETE FROM clerks_judges WHERE user_id = 102;
DELETE cj FROM  clerks_judges cj LEFT JOIN request_users u ON cj.user_id = u.id WHERE u.id IS NULL;

-- PHP Notice:  Undefined offset: 1 in /opt/www/court-interpreters-office/bin/sdny/import-judges-and-courtrooms.php on line 73
-- PHP Warning:  preg_split() expects parameter 2 to be string, array given in /opt/www/court-interpreters-office/bin/sdny/import-judges-and-courtrooms.php on line 89
-- PHP Notice:  Undefined index:  in /opt/www/court-interpreters-office/bin/sdny/import-judges-and-courtrooms.php on line 103
-- location insert FAILED: SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'name' cannot be null
-- insert FAILED: SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'firstname' cannot be null

-- PHP Notice:  Undefined offset: 1 in /opt/www/court-interpreters-office/bin/sdny/import-judges-and-courtrooms.php on line 73
-- PHP Warning:  preg_split() expects parameter 2 to be string, array given in /opt/www/court-interpreters-office/bin/sdny/import-judges-and-courtrooms.php on line 89
-- PHP Notice:  Undefined index:  in /opt/www/court-interpreters-office/bin/sdny/import-judges-and-courtrooms.php on line 103
-- location insert FAILED: SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'name' cannot be null
-- insert FAILED: SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'firstname' cannot be null
