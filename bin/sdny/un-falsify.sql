SELECT u.username, h.name hat, e.submitter_id, comments FROM events e JOIN users u
ON u.id = e.created_by_id JOIN hats h ON e.anonymous_submitter_id = h.id
AND h.name = "Magistrates" AND e.comments REGEXP '\\bmag(i?s(trates?)?)? +cal'
 ORDER BY e.id DESC LIMIT 125;


/* UPDATE events SET .... */
