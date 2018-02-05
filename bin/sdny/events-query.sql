/*
+----+------------------+
| id | type             |
+----+------------------+
|  1 | AUSA             |
|  2 | USPO             |
|  3 | staff interp     |
|  4 | freelance interp |
|  5 | ctroom staff     |
|  6 | Pretrial         |
|  7 | Magistrates      |
|  8 | defense atty     |
|  9 | other            |
| 10 | USAO staff       |
+----+------------------+

*/
SELECT e.event_id AS id, e.event_date AS date, e.event_time AS time, e.end_time, e.docket, 
e.proceeding_id  AS event_type_id, p.type, e.language_id, l.name language, 
e.judge_id, j.lastname judge_lastname, j.firstname judge_firstname,

e.req_date submission_date , e.req_time submission_time, e.req_by submitter_id, 
e.req_class submitter_hat_id,
rc.type AS submitter_hat,
g.flavor AS submitter_group,
CASE
    WHEN e.req_by = 0
    THEN "[anonymous]"
    WHEN ru.id IS NOT NULL
    THEN CONCAT(ru.lastname, ", ",ru.firstname)
    WHEN e.req_class IN (1,6,7,8,9,10)
    THEN CONCAT(rb.lastname, ", ",rb.firstname)
    WHEN e.req_class = 4
    THEN CONCAT(i.lastname, ", ",i.firstname)
    WHEN e.req_class = 3
    THEN u.name 
END AS submitter_name,
g.id AS submitter_group_id,
e.created, e.created_by, e.lastmod AS modified, e.lastmod_by AS modified_by_id,
e.cancel_reason, e.notes AS comments, e.admin_notes AS admin_comments


FROM events e 


JOIN proceedings p ON e.proceeding_id = p.proceeding_id 
JOIN languages l ON l.lang_id = e.language_id
JOIN judges j ON j.judge_id = e.judge_id
LEFT JOIN request_class rc ON rc.id = e.req_class
LEFT JOIN requests r ON r.event_id = e.event_id
LEFT JOIN request_users ru ON (ru.id = e.req_by AND e.req_class IN (2,5,6))
LEFT JOIN request_by rb ON (e.req_by = rb.id AND e.req_class IN (1,6,7,8,9,10))
LEFT JOIN interpreters i ON (e.req_by = i.interp_id AND e.req_class = 4)
LEFT JOIN groups g ON g.id = ru.group_id
LEFT JOIN users u ON (u.user_id = e.req_by AND e.req_class = 3 )
WHERE e.event_date BETWEEN @from AND @to

ORDER BY event_date, event_time;