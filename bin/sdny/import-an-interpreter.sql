/*
 *  SQL for importing an interpreter and related events, languages
 */


/* INSERT INTO people table */
INSERT INTO people
(id, lastname, firstname, middlename, office_phone, mobile_phone, email, active, hat_id,discr)

(SELECT interp_id, lastname, firstname, middlename, office, mobile,
    IF(email <> "",email, NULL),
    IF(active = 'Y', 1, 0),
    IF(freelance = "Y", 3, 1),
    "interpreter"
FROM dev_interpreters.interpreters WHERE interp_id = :id);

INSERT INTO interpreters (
id, security_clearance_date, contract_expiration_date,fingerprint_date, oath_date,
comments, address1, address2, city, state, zip, home_phone, country
)
(SELECT interp_id,
    IF (security_clearance = "0000-00-00",NULL,security_clearance),
    IF (contract_expiration = "0000-00-00",NULL,contract_expiration),
    IF (fingerprinted = "0000-00-00",NULL,fingerprinted),
    IF (oath = "0000-00-00",NULL,oath),
    notes, address1, address2, city, state, zip, home, "United States"

FROM dev_interpreters.interpreters ORDER BY interp_id DESC LIMIT 1);



INSERT INTO interpreters_languages (SELECT interp_id, lang_id,
    CASE fed_cert
       WHEN 'N/A' THEN null
       WHEN 'Y' THEN 1
       WHEN 'N' THEN 0
    END
    FROM dev_interpreters.interp_languages WHERE interp_id = :id
);
set @user_david = (SELECT id FROM office.users WHERE username = 'david');
use dev_interpreters;
INSERT INTO office.interpreters_events (interpreter_id, event_id, created, created_by_id)
    (SELECT interp_id, ie.event_id, ie.created,  COALESCE(u2.id,@user_david) AS created_by
    FROM interp_events ie
        LEFT JOIN users u ON ie.created_by = u.user_id
        LEFT JOIN office.users u2 ON u.name = u2.username WHERE ie.interp_id = :id);


/* that should do it for interpreters, languages and interpreters_languages */
