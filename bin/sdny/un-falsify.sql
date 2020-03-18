UPDATE events e
JOIN hats h
ON e.anonymous_submitter_id = h.id
JOIN users u ON u.id = e.created_by_id
JOIN people p ON u.person_id = p.id
SET e.submitter_id = p.id,
e.anonymous_submitter_id = NULL
WHERE h.name = "Magistrates"
AND e.submitter_id IS NULL
AND e.comments REGEXP  '([[:<:]]|^)mag\.?(i?s(trates?)?)? *cal(endar)?(; *)?([[:>:]]|\.|$)';
-- AND e.comments REGEXP "[[:<:]]mag(is(trates?)?)? *cal(endar)?[[:>:]]" 
--  "[[:<:]]mag(is(trates?)?)? *cal(endar)?[[:>:]]" 
-- https://www.regular-expressions.info/wordboundaries.html
-- The POSIX standard defines [[:<:]] as a start-of-word boundary, and [[:>:]] as an end-of-word boundary. 
-- Though the syntax is borrowed from POSIX bracket expressions, these tokens are word boundaries that have 
-- nothing to do with and cannot be used inside character classes. Tcl and GNU also support POSIX word boundaries. 
-- PCRE supports POSIX word boundaries starting with version 8.34. Boost supports them in all its grammars.