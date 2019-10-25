UPDATE events e
JOIN hats h
ON e.anonymous_submitter_id = h.id
JOIN users u ON u.id = e.created_by_id
JOIN people p ON u.person_id = p.id
SET e.submitter_id = p.id,
e.anonymous_submitter_id = NULL
WHERE h.name = "Magistrates"
AND e.submitter_id IS NULL
AND e.comments REGEXP '([[:<:]]|^)mag(i?s(trates?)?)? +cal(; *|endar)?([[:<:]]|$)';
