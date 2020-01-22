UPDATE events SET req_by = 246, lastmod = lastmod WHERE req_by = 365 AND req_class = 5;
DELETE FROM request_users WHERE id = 365;
UPDATE request_users SET email = 'siobhan_atkins@nysd.uscourts.gov', group_id = 2 WHERE id = 246;

UPDATE events SET req_by = 256, lastmod = lastmod WHERE req_by = 355 AND req_class = 5;
DELETE FROM request_users WHERE id = 355;
UPDATE request_users SET group_id = 1, email = 'frank_cangelosi@nysd.uscourts.gov' WHERE id = 256;

UPDATE events SET req_by = 267, lastmod = lastmod WHERE req_by = 360 AND req_class = 5;
DELETE FROM request_users WHERE id = 360;
UPDATE request_users SET group_id = 1, email = 'gloria_daley@nysd.uscourts.gov' WHERE id = 267;
UPDATE request_users SET email = lower(REPLACE(email,"unverified.","")) WHERE email LIKE "unverified%";

DELETE cj FROM  clerks_judges cj LEFT JOIN request_users u ON cj.user_id = u.id WHERE u.id IS NULL;

/*
+----------+-----------+-------------------------------------------+-----+------------+------------------+--------+--------+
| lastname | firstname | email                                     | id  | last_login | flavor           | hat_id | active |
+----------+-----------+-------------------------------------------+-----+------------+------------------+--------+--------+
| Daley    | Gloria    | unverified.Gloria_Daley@nysd.uscourts.gov | 267 |          0 | [group unknown]  |      5 |      0 |
| Daley    | Gloria    | gloria_daley@nysd.uscourts.gov            | 360 |          0 | Courtroom Deputy |      5 |      0 |
+----------+-----------+-------------------------------------------+-----+------------+------------------+--------+--------+
2 rows in set (0.00 sec)


 */
-- UPDATE request_users SET email = null where id = 590;
-- UPDATE request_users SET email = 'jineen_forbes@nysp.uscourts.gov', mobile_phone = '9175777303',last_login = 1578949228 WHERE id = 281;
-- UPDATE events SET req_by = 281, lastmod = lastmod WHERE req_by = 590 AND req_class = 2;
-- UPDATE requests SET created_by = 281, modified = modified, last_modified_by = 281 WHERE created_by = 590;
-- DELETE FROM request_users WHERE id = 590;

-- do away with cynthia markley

-- DELETE cj FROM clerks_judges cj LEFT JOIN judges j ON j.judge_id = cj.judge_id WHERE j.judge_id IS NULL;
-- UPDATE events SET req_by = created_by, lastmod = lastmod, req_class = 3 WHERE req_by = 544 AND req_class = 5;
-- DELETE FROM request_users WHERE id = 544;
-- DELETE FROM clerks_judges WHERE user_id = 102;
-- DELETE cj FROM  clerks_judges cj LEFT JOIN request_users u ON cj.user_id = u.id WHERE u.id IS NULL;
