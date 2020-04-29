/*
	import AUSAs, defense attorneys, and USAO staff.
	you can use 'mysql office --force < import-users.sql

track down inexact duplicates:

SELECT DISTINCT hat_id, h.name hat,lastname, firstname, email, p.id FROM people p JOIN hats h ON p.hat_id = h.id
WHERE CONCAT(lastname,"|",firstname) IN  ( SELECT  CONCAT(lastname,"|",firstname)
FROM people GROUP BY lastname,firstname,hat_id  HAVING COUNT(*) > 1) ORDER BY lastname, firstname ;

SELECT lastname, firstname, h.name hat, hat_id, email, p.id FROM people p JOIN hats h ON p.hat_id = h.id  WHERE CONCAT(lastname,"|",firstname) IN  ( SELECT  CONCAT(lastname,"|",firstname)  FROM people GROUP BY lastname,firstname HAVING COUNT(*) > 1) ORDER BY lastname, firstname;

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

SELECT
	CASE class_id
		WHEN 1 THEN 5
		WHEN 8 THEN 4
        WHEN 10 THEN 11
	END as hat_id,
	email,
	lastname,
	firstname,
	phone,
	mobile_phone,
	IF (active = "Y",1,0) AS active,
	"person"
FROM dev_interpreters.request_by

WHERE class_id IN (1,8,10)
	/*AND lastname = "Bachrach"*/
;
