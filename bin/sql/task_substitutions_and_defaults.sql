


SELECT s.id, t.name AS task,
(SELECT p.firstname FROM people p
    JOIN task_rotation_members m ON p.id = m.person_id
    JOIN rotations r ON m.rotation_id = r.id
WHERE m.rotation_order =  FLOOR(DATEDIFF(s.date,r.start_date)/7) %
    (SELECT COUNT(*) FROM task_rotation_members m
    WHERE m.rotation_id = s.rotation_id)
    AND m.rotation_id = s.rotation_id
) AS `default`,
p.firstname AS assigned
FROM rotation_substitutions s
JOIN people p ON s.person_id = p.id
JOIN rotations r ON r.id = s.rotation_id JOIN tasks t ON r.task_id = t.id
ORDER BY s.date;

--
-- this gets some task substitutions and the respective default assignments.
-- very damn hard (if not impossible) without a temporary table, at least for me,
-- unless the substition table has the rotation_id
--
--

-- CREATE TEMPORARY TABLE tmp_subs (
--     task varchar(25),
--     task_id smallint,
--     duration varchar(10),
--     `date` date,
--     assigned varchar(20),
--     rotation_start date,
--     rotation_id smallint)
--
-- SELECT t.name AS task, t.id task_id, rs.duration, rs.date,
-- p.firstname assigned,
-- (SELECT r.start_date FROM rotations r WHERE rs.date >= r.start_date AND r.task_id = rs.task_id
--     ORDER BY r.start_date DESC LIMIT 1) AS rotation_start,
-- (SELECT r.id FROM rotations r WHERE rs.date >= r.start_date AND r.task_id = rs.task_id
--         ORDER BY r.start_date DESC LIMIT 1) AS rotation_id
-- FROM rotation_substitutions rs
-- JOIN tasks t ON t.id = rs.task_id
-- JOIN people p ON p.id = rs.person_id
-- WHERE rs.date >= '2019-06-01';
--
-- SELECT s.task, s.duration, s.date,
-- (
--     SELECT p.firstname FROM people p JOIN task_rotation_members m ON p.id = m.person_id
--     WHERE m.rotation_order =
--     FLOOR(DATEDIFF(s.date,s.rotation_start)/7) % (SELECT COUNT(*) FROM task_rotation_members m
--     WHERE m.rotation_id = s.rotation_id)
--     AND m.rotation_id = s.rotation_id
-- )  AS `default`,
-- s.assigned
--
-- FROM tmp_subs AS s ORDER BY s.task, s.date;
