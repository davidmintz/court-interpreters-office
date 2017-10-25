/*  
	import some AUSAs and defense attorneys for test purposes. 
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
	discr	
) 

SELECT  
	CASE class_id
		WHEN 1 THEN 5
		WHEN 8 THEN 4
	END as hat_id,
	email, 
	lastname, 
	firstname,
	phone,
	mobile_phone,
	active,
	"person"
FROM dev_interpreters.request_by

WHERE class_id IN (1,8);
