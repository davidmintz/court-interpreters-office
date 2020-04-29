/*
INSERT INTO defendant_names (id, given_names, surnames)
(SELECT deft_id, firstname, lastname FROM dev_interpreters.deft_names ORDER BY deft_id);
*/
SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ALLOW_INVALID_DATES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

INSERT INTO defendants_events (defendant_id,event_id) (SELECT deft_id, event_id FROM dev_interpreters.deft_events);

set @user_david = (SELECT id FROM office.users WHERE username = 'david');
use dev_interpreters;
INSERT INTO office.interpreters_events (interpreter_id, event_id, created, created_by_id)
(SELECT interp_id, ie.event_id, ie.created,  COALESCE(u2.id,@user_david) AS created_by FROM interp_events ie
    LEFT JOIN users u ON ie.created_by = u.user_id
    LEFT JOIN office.users u2 ON u.name = u2.username );

/* WHERE ie.event_id >= 111940 */
