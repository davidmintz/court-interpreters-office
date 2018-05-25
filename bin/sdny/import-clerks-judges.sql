/* run this against the "office" database after importing judges and users */
INSERT INTO clerks_judges (
    SELECT u.id, p.id FROM  dev_interpreters.judges j
    JOIN people p ON j.lastname = p.lastname AND j.firstname = p.firstname AND
    p.discr = "judge"
    LEFT JOIN dev_interpreters.clerks_judges cj ON j.judge_id = cj.judge_id
    JOIN dev_interpreters.request_users ru ON cj.user_id = ru.id
    JOIN people p2 ON p2.email = ru.email
    JOIN users u on p2.id = u.person_id
);
