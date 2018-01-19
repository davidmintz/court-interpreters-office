CREATE TEMPORARY TABLE possibly_defunct SELECT r.id, max(e.req_date) AS last  FROM request_by r 
JOIN request_class h ON r.class_id = h.id JOIN events e ON (e.req_by = r.id AND h.id = e.req_class) 
WHERE r.active = 'Y' GROUP BY r.id ORDER BY last;

UPDATE request_by r JOIN possibly_defunct p ON r.id = p.id SET ACTIVE = 'N' WHERE p.last < DATE_SUB(CURDATE(), INTERVAL 5 YEAR); 