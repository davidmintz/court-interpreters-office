CREATE TABLE languages (id INTEGER NOT NULL, name VARCHAR(50) NOT NULL, comments VARCHAR(300) DEFAULT '' NOT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX unique_language ON languages (name);
CREATE TABLE verification_tokens (id VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, expiration DATETIME NOT NULL, PRIMARY KEY(id));
CREATE TABLE cancellation_reasons (id INTEGER NOT NULL, reason VARCHAR(40) NOT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX unique_cancel_reason ON cancellation_reasons (reason);
CREATE TABLE hats (id INTEGER NOT NULL, role_id SMALLINT UNSIGNED DEFAULT NULL, name VARCHAR(50) NOT NULL, anonymity INTEGER UNSIGNED DEFAULT 0 NOT NULL, is_judges_staff BOOLEAN NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_149C3D93D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_149C3D93D60322AC ON hats (role_id);
CREATE UNIQUE INDEX hat_idx ON hats (name);
CREATE TABLE judge_flavors (id INTEGER NOT NULL, flavor VARCHAR(60) NOT NULL, weight INTEGER NOT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX unique_judge_flavor ON judge_flavors (flavor);
CREATE TABLE users (id INTEGER NOT NULL, person_id SMALLINT UNSIGNED NOT NULL, role_id SMALLINT UNSIGNED NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(50) DEFAULT NULL, active BOOLEAN DEFAULT '0' NOT NULL, last_login DATETIME DEFAULT NULL, created DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_1483A5E9217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE UNIQUE INDEX UNIQ_1483A5E9217BBB47 ON users (person_id);
CREATE INDEX IDX_1483A5E9D60322AC ON users (role_id);
CREATE UNIQUE INDEX uniq_person_role ON users (person_id, role_id);
CREATE TABLE clerks_judges (user_id SMALLINT UNSIGNED NOT NULL, judge_id SMALLINT UNSIGNED NOT NULL, PRIMARY KEY(user_id, judge_id), CONSTRAINT FK_DB59EF06A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DB59EF06B7D66194 FOREIGN KEY (judge_id) REFERENCES judges (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_DB59EF06A76ED395 ON clerks_judges (user_id);
CREATE INDEX IDX_DB59EF06B7D66194 ON clerks_judges (judge_id);
CREATE TABLE locations (id INTEGER NOT NULL, type_id SMALLINT UNSIGNED NOT NULL, parent_location_id SMALLINT UNSIGNED DEFAULT NULL, name VARCHAR(60) NOT NULL, comments VARCHAR(200) DEFAULT '' NOT NULL, active BOOLEAN DEFAULT '1' NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_17E64ABAC54C8C93 FOREIGN KEY (type_id) REFERENCES location_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_17E64ABA6D6133FE FOREIGN KEY (parent_location_id) REFERENCES locations (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_17E64ABAC54C8C93 ON locations (type_id);
CREATE INDEX IDX_17E64ABA6D6133FE ON locations (parent_location_id);
CREATE UNIQUE INDEX unique_name_and_parent ON locations (name, parent_location_id);
CREATE TABLE roles (id INTEGER NOT NULL, name VARCHAR(40) NOT NULL, PRIMARY KEY(id));
CREATE TABLE interpreters_languages (interpreter_id SMALLINT UNSIGNED NOT NULL, language_id SMALLINT UNSIGNED NOT NULL, federal_certification BOOLEAN DEFAULT NULL, PRIMARY KEY(interpreter_id, language_id), CONSTRAINT FK_E0423968AD59FFB1 FOREIGN KEY (interpreter_id) REFERENCES interpreters (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E042396882F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_E0423968AD59FFB1 ON interpreters_languages (interpreter_id);
CREATE INDEX IDX_E042396882F1BAF4 ON interpreters_languages (language_id);
CREATE TABLE people (id INTEGER NOT NULL, hat_id SMALLINT UNSIGNED NOT NULL, email VARCHAR(50) DEFAULT NULL, lastname VARCHAR(50) NOT NULL, firstname VARCHAR(50) NOT NULL, middlename VARCHAR(50) DEFAULT '' NOT NULL, office_phone VARCHAR(20) DEFAULT '' NOT NULL, mobile_phone VARCHAR(20) DEFAULT '' NOT NULL, active BOOLEAN NOT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_28166A268C6A5980 FOREIGN KEY (hat_id) REFERENCES hats (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_28166A268C6A5980 ON people (hat_id);
CREATE UNIQUE INDEX hat_email_idx ON people (email, hat_id);
CREATE UNIQUE INDEX active_email_idx ON people (email, active);
CREATE TABLE judges (id SMALLINT UNSIGNED NOT NULL, default_location_id SMALLINT UNSIGNED DEFAULT NULL, flavor_id INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_1C5E0B5D2BE3238 FOREIGN KEY (default_location_id) REFERENCES locations (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1C5E0B5FDDA6450 FOREIGN KEY (flavor_id) REFERENCES judge_flavors (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1C5E0B5BF396750 FOREIGN KEY (id) REFERENCES people (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_1C5E0B5D2BE3238 ON judges (default_location_id);
CREATE INDEX IDX_1C5E0B5FDDA6450 ON judges (flavor_id);
CREATE TABLE defendant_names (id INTEGER NOT NULL, given_names VARCHAR(60) NOT NULL, surnames VARCHAR(60) NOT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX unique_deftname ON defendant_names (given_names, surnames);
CREATE TABLE defendants_events (defendant_id INTEGER UNSIGNED NOT NULL, event_id SMALLINT UNSIGNED NOT NULL, PRIMARY KEY(defendant_id, event_id), CONSTRAINT FK_DBDD36079960FFFB FOREIGN KEY (defendant_id) REFERENCES defendant_names (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DBDD360771F7E88B FOREIGN KEY (event_id) REFERENCES events (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_DBDD36079960FFFB ON defendants_events (defendant_id);
CREATE INDEX IDX_DBDD360771F7E88B ON defendants_events (event_id);
CREATE UNIQUE INDEX unique_defendant_event ON defendants_events (defendant_id, event_id);
CREATE TABLE holidays (id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE TABLE event_types (id INTEGER NOT NULL, category_id SMALLINT UNSIGNED NOT NULL, name VARCHAR(60) NOT NULL, comments VARCHAR(150) DEFAULT '' NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_182B381C12469DE2 FOREIGN KEY (category_id) REFERENCES event_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_182B381C12469DE2 ON event_types (category_id);
CREATE UNIQUE INDEX unique_event_type ON event_types (name);
CREATE TABLE interpreters (id SMALLINT UNSIGNED NOT NULL, home_phone VARCHAR(16) DEFAULT NULL, dob VARCHAR(125) DEFAULT NULL, ssn VARCHAR(255) DEFAULT NULL, security_clearance_date DATE DEFAULT NULL, fingerprint_date DATE DEFAULT NULL, oath_date DATE DEFAULT NULL, contract_expiration_date DATE DEFAULT NULL, comments VARCHAR(600) NOT NULL, address1 VARCHAR(60) NOT NULL, address2 VARCHAR(60) NOT NULL, city VARCHAR(40) NOT NULL, state VARCHAR(40) NOT NULL, zip VARCHAR(16) NOT NULL, country VARCHAR(16) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_4EBBDB02BF396750 FOREIGN KEY (id) REFERENCES people (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE UNIQUE INDEX unique_ssn ON interpreters (ssn);
CREATE TABLE anonymous_judges (id INTEGER NOT NULL, default_location_id SMALLINT UNSIGNED DEFAULT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_5BD10E2D2BE3238 FOREIGN KEY (default_location_id) REFERENCES locations (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_5BD10E2D2BE3238 ON anonymous_judges (default_location_id);
CREATE UNIQUE INDEX unique_anon_judge ON anonymous_judges (name, default_location_id);
CREATE TABLE court_closings (id INTEGER NOT NULL, holiday_id INTEGER DEFAULT NULL, date DATE NOT NULL, description_other VARCHAR(75) DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_F21F4FD1830A3EC0 FOREIGN KEY (holiday_id) REFERENCES holidays (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_F21F4FD1830A3EC0 ON court_closings (holiday_id);
CREATE TABLE event_categories (id INTEGER NOT NULL, category VARCHAR(20) NOT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX unique_event_category ON event_categories (category);
CREATE TABLE events (id INTEGER NOT NULL, language_id SMALLINT UNSIGNED NOT NULL, event_type_id SMALLINT UNSIGNED NOT NULL, judge_id SMALLINT UNSIGNED DEFAULT NULL, anonymous_judge_id SMALLINT UNSIGNED DEFAULT NULL, submitter_id SMALLINT UNSIGNED DEFAULT NULL, anonymous_submitter_id SMALLINT UNSIGNED DEFAULT NULL, location_id SMALLINT UNSIGNED DEFAULT NULL, cancellation_reason_id SMALLINT UNSIGNED DEFAULT NULL, created_by_id SMALLINT UNSIGNED NOT NULL, modified_by_id SMALLINT UNSIGNED DEFAULT NULL, date DATE NOT NULL, time TIME DEFAULT NULL, end_time TIME DEFAULT NULL, submission_date DATE NOT NULL, submission_time TIME DEFAULT NULL, docket VARCHAR(15) DEFAULT '' NOT NULL, comments VARCHAR(600) DEFAULT '' NOT NULL, admin_comments VARCHAR(600) DEFAULT '' NOT NULL, created DATETIME NOT NULL, modified DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_5387574A82F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5387574A401B253C FOREIGN KEY (event_type_id) REFERENCES event_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5387574AB7D66194 FOREIGN KEY (judge_id) REFERENCES judges (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5387574AFF915C63 FOREIGN KEY (anonymous_judge_id) REFERENCES anonymous_judges (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5387574A919E5513 FOREIGN KEY (submitter_id) REFERENCES people (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5387574A61A31DAE FOREIGN KEY (anonymous_submitter_id) REFERENCES hats (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5387574A64D218E FOREIGN KEY (location_id) REFERENCES locations (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5387574A8453C906 FOREIGN KEY (cancellation_reason_id) REFERENCES cancellation_reasons (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5387574AB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5387574A99049ECE FOREIGN KEY (modified_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_5387574A82F1BAF4 ON events (language_id);
CREATE INDEX IDX_5387574A401B253C ON events (event_type_id);
CREATE INDEX IDX_5387574AB7D66194 ON events (judge_id);
CREATE INDEX IDX_5387574AFF915C63 ON events (anonymous_judge_id);
CREATE INDEX IDX_5387574A919E5513 ON events (submitter_id);
CREATE INDEX IDX_5387574A61A31DAE ON events (anonymous_submitter_id);
CREATE INDEX IDX_5387574A64D218E ON events (location_id);
CREATE INDEX IDX_5387574A8453C906 ON events (cancellation_reason_id);
CREATE INDEX IDX_5387574AB03A8386 ON events (created_by_id);
CREATE INDEX IDX_5387574A99049ECE ON events (modified_by_id);
CREATE TABLE location_types (id INTEGER NOT NULL, type VARCHAR(60) NOT NULL, comments VARCHAR(200) DEFAULT '' NOT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX unique_type ON location_types (type);
CREATE TABLE interpreters_events (interpreter_id SMALLINT UNSIGNED NOT NULL, event_id SMALLINT UNSIGNED NOT NULL, created_by_id SMALLINT UNSIGNED NOT NULL, created DATETIME NOT NULL, PRIMARY KEY(interpreter_id, event_id), CONSTRAINT FK_590E5B07AD59FFB1 FOREIGN KEY (interpreter_id) REFERENCES interpreters (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_590E5B0771F7E88B FOREIGN KEY (event_id) REFERENCES events (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_590E5B07B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_590E5B07AD59FFB1 ON interpreters_events (interpreter_id);
CREATE INDEX IDX_590E5B0771F7E88B ON interpreters_events (event_id);
CREATE INDEX IDX_590E5B07B03A8386 ON interpreters_events (created_by_id);
CREATE UNIQUE INDEX unique_interp_event ON interpreters_events (interpreter_id, event_id);
