SELECT u.username, COUNT(r.id)
FROM users u
JOIN people p ON u.person_id = p.id
JOIN requests r  
WHERE r.submitter_id = p.id OR r.modified_by_id = u.id
GROUP BY u.username
LIMIT 250;
/* WHERE r.submitter_id */