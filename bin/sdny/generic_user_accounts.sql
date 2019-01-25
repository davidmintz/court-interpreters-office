SELECT DISTINCT email, lastname, firstname FROM people p 
JOIN users u ON p.id = u.person_id JOIN roles r ON r.id = u.role_id
JOIN clerks_judges cj ON cj.user_id = u.id
WHERE u.active AND r.name = "submitter"
AND email LIKE '%intern%' OR email LIKE '%chambers%';
