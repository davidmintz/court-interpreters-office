
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

WHERE id = :id ;
