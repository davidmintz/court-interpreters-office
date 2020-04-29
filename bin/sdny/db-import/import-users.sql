/*
	import users from old database "request_users"
*/

INSERT INTO people (

	hat_id,
	email,
	lastname,
	firstname,
	office_phone,
	mobile_phone,
	active,
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
	"person"

FROM dev_interpreters.request_users
	WHERE group_id <> 5; /* the "unknown" category, strongly deprecated */


INSERT INTO users (
	person_id,
	role_id,
	username,
	password,
	active,
	created,
	last_login)
SELECT
	p.id,
	1, /* role is "submitter" */
	lower(p.email), /* temporary username */
	ru.password, /* copy the password */
	p.active,
	ru.created,
	IF(ru.last_login,FROM_UNIXTIME(ru.last_login),NULL)
FROM people p JOIN dev_interpreters.request_users ru ON p.email = ru.email WHERE hat_id IN (6,7,8,9);

/*
UPDATE T1, T2,
[INNER JOIN | LEFT JOIN] T1 ON T1.C1 = T2. C1
SET T1.C2 = T2.C2,
    T2.C3 = expr
WHERE condition
*/

-- UPDATE users
-- 	JOIN people ON users.person_id = people.id
-- 	JOIN dev_interpreters.request_users old_users ON  people.email = old_users.email
-- SET 
-- -- users.password = old_users.password, users.created = old_users.created,
-- users.last_login = IF(old_users.last_login,FROM_UNIXTIME(old_users.last_login),NULL)
-- /*WHERE old_users.active*/
-- ;
