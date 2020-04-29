/*
 *  SQL for importing interpreters, languages and interpreters_languages
 */

/* FIRST purge interpreters_languages, interpreters, people and users

DELETE FROM interpreters_languages;
DELETE FROM languages;
DELETE FROM users;
DELETE FROM interpreters;

DELETE FROM people WHERE hat_id IN (1,3);
*/
/* DELETE i, p FROM interpreters i JOIN people p ON i.id = p.id ;*/


INSERT INTO languages (SELECT * FROM dev_interpreters.languages);

/* INSERT INTO people table */
INSERT INTO people
(id, lastname, firstname, middlename, office_phone, mobile_phone, email, active, hat_id,discr)

(SELECT interp_id, lastname, firstname, middlename, office, mobile,
    IF(email <> "",email, NULL),
    IF(active = 'Y', 1, 0),
    IF(freelance = "Y", 3, 1),
    "interpreter"
FROM dev_interpreters.interpreters ORDER BY interp_id);

/* be more strict about how "staff interpreter" is defined */
UPDATE people SET hat_id = 3 WHERE hat_id = 1 AND id NOT IN
    (SELECT interp_id FROM dev_interpreters.interpreters
    WHERE lastname IN ('Garcia','Rich','de los Rios','Anderson','Fox','Hess','Gold','Festinger','Mintz','Olivero')
    AND freelance = 'N');
/* followed by interpreters table */
SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ALLOW_INVALID_DATES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

INSERT INTO interpreters (
id, security_clearance_date, contract_expiration_date,fingerprint_date,
bop_form_submission_date, oath_date, solicit_availability,publish_public,
comments, address1, address2, city, state, zip, home_phone, country
)
(SELECT interpreters.interp_id,
    IF (security_clearance <> "0000-00-00",security_clearance,NULL),
    IF (contract_expiration = "0000-00-00",NULL,contract_expiration),
    IF (fingerprinted = "0000-00-00",NULL,fingerprinted),
    IF (bop_forms_submitted = "0000-00-00",NULL,bop_forms_submitted),
    IF (oath = "0000-00-00",NULL,oath),
    IF (availability_invitees.interp_id IS NULL, 0, 1),
    IF (publish_public = "Y",1,0),
    notes, address1, address2, city, state, zip, home, "United States"

FROM dev_interpreters.interpreters
LEFT JOIN dev_interpreters.availability_invitees ON  dev_interpreters.interpreters.interp_id = dev_interpreters.availability_invitees.interp_id
ORDER BY dev_interpreters.interpreters.interp_id);



INSERT INTO interpreters_languages (interpreter_id,language_id, credential_id) (SELECT interp_id, lang_id,
    CASE rating
       WHEN '' THEN null
       WHEN 'AO' THEN 1
       WHEN 'PQ' THEN 2
       WHEN 'LS' THEN 3
    END
    FROM dev_interpreters.interp_languages
);

/* that should do it for interpreters, languages and interpreters_languages */
