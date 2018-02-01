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

FROM dev_interpreters.interpreters ORDER BY interp_id);



INSERT INTO interpreters_languages (SELECT interp_id, lang_id,
    CASE fed_cert
       WHEN 'N/A' THEN null
       WHEN 'Y' THEN 1
       WHEN 'N' THEN 0
    END
    FROM dev_interpreters.interp_languages
);

/* that should do it for interpreters, languages and interpreters_languages */
