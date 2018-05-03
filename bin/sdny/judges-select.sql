SELECT CONCAT(p.lastname, ", ",p.firstname) judge, rm.name courtoom, ct.name courthouse FROM people p JOIN judges j ON p.id = j.id LEFT JOIN locations rm ON j.default_location_id = rm.id LEFT JOIN locations ct ON rm.parent_location_id = ct.id where p.active ORDER BY judge;

