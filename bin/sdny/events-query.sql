
SELECT e.event_id AS id, e.event_date AS date, e.event_time AS time, e.end_time, e.docket, 
e.proceeding_id  AS event_type_id, p.type, e.language_id, l.name language, 
e.judge_id, j.lastname judge_lastname, j.firstname judge_firstname,

e.req_date, e.req_time, e.req_by, e.req_class,
e.created, e.created_by, e.lastmod AS modified, e.lastmod_by AS modified_by_id,
e.cancel_reason,
e.notes AS comments, e.admin_notes AS admin_comments
FROM events e 
JOIN proceedings p ON e.proceeding_id = p.proceeding_id 
JOIN languages l ON l.lang_id = e.language_id
JOIN judges j ON j.judge_id = e.judge_id

WHERE e.event_date BETWEEN '2017-11-01' AND '2018-01-31'

ORDER BY event_date, event_time;