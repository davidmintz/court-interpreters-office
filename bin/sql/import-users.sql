/*  
	import some users for test purposes. 
	you can use 'mysql office --force < import-users.sql  
*/

INSERT INTO people (

	hat_id,
	email,
	lastname,
	firstname,
	office_phone,
	mobile_phone,
	active,
	/*last_login,*/
	discr
	
) 
/* group_id => hat_id */ /* might as well say group_id + 5 */
SELECT  
	CASE group_id
		WHEN 1 THEN 6 /* Courtroom Deputy */
		WHEN 2 THEN 7 /* Law Clerk */
		WHEN 3 THEN 8 /* USPO */
		WHEN 4 THEN 9 /* PTSO */
	END
	AS hat_id,
	email,
	lastname,
	firstname,
	phone,
	mobile_phone,
	active,
	/*FROM_UNIXTIME(last_login),*/
	"person"

FROM dev_interpreters.request_users 
	WHERE group_id <> 5
	AND active
;

INSERT INTO users (
	person_id,
	role_id,
	username,
	password,
	active)
SELECT 
	id,
	1, /* role is "submitter" */
	lower(email), /* temp(?) username */	
	"shit", /* temp password */	
	active
FROM people WHERE hat_id IN (6,7,8,9);
	
/*
UPDATE T1, T2,
[INNER JOIN | LEFT JOIN] T1 ON T1.C1 = T2. C1
SET T1.C2 = T2.C2, 
    T2.C3 = expr
WHERE condition
*/

UPDATE users 
	JOIN people ON users.person_id = people.id
	JOIN dev_interpreters.request_users old_users ON  people.email = old_users.email
SET users.password = old_users.password, 
users.last_login = IF(old_users.last_login,FROM_UNIXTIME(old_users.last_login),NULL)
WHERE old_users.active;
