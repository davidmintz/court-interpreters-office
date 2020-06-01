---
layout: default
title: data model | documentation | InterpretersOffice.org
---

<h2>The data model</h2>

<span class="text-monospace">InterpretersOffice</span> is intended for use with MySQL or MariaDB for production,
though it also includes a suite of tests that run against SQLite3.

The database consists of the following tables, most of which are mapped to Doctrine entity classes:

```
MariaDB [office]> show tables;
+------------------------+
| Tables_in_office       |
+------------------------+
| anonymous_judges       |
| app_event_log          |
| banned                 |
| cancellation_reasons   |
| category               |
| clerks_judges          |
| court_closings         |
| defendant_names        |
| defendants_events      |
| defendants_requests    |
| docket_annotations     |
| event_categories       |
| event_types            |
| events                 |
| hats                   |
| holidays               |
| interpreters           |
| interpreters_events    |
| interpreters_languages |
| judge_flavors          |
| judges                 |
| language_credentials   |
| languages              |
| location_types         |
| locations              |
| motd                   |
| motw                   |
| people                 |
| requests               |
| roles                  |
| rotation_substitutions |
| rotations              |
| task_rotation_members  |
| tasks                  |
| users                  |
| verification_tokens    |
| view_locations         |
+------------------------+
```

The **events** entity is central; it represents as event that requires the services of one or more 
court interpreters. The core of <span class="text-monospace">InterpretersOffice</span> is CRUD operations 
on these entities.

```
CREATE TABLE `events` (
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `language_id` smallint(5) unsigned NOT NULL,
    `judge_id` smallint(5) unsigned DEFAULT NULL,
    `submitter_id` smallint(5) unsigned DEFAULT NULL,
    `location_id` smallint(5) unsigned DEFAULT NULL,
    `date` date NOT NULL,
    `time` time DEFAULT NULL,
    `end_time` time DEFAULT NULL,
    `docket` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    `comments` varchar(600) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    `admin_comments` varchar(600) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    `created` datetime NOT NULL,
    `modified` datetime DEFAULT NULL,
    `event_type_id` smallint(5) unsigned NOT NULL,
    `created_by_id` smallint(5) unsigned NOT NULL,
    `anonymous_judge_id` smallint(5) unsigned DEFAULT NULL,
    `anonymous_submitter_id` smallint(5) unsigned DEFAULT NULL,
    `cancellation_reason_id` smallint(5) unsigned DEFAULT NULL,
    `modified_by_id` smallint(5) unsigned DEFAULT NULL,
    `submission_date` date NOT NULL,
    `submission_time` time DEFAULT NULL,
    `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `IDX_5387574A82F1BAF4` (`language_id`),
    KEY `IDX_5387574AB7D66194` (`judge_id`),
    KEY `IDX_5387574A919E5513` (`submitter_id`),
    KEY `IDX_5387574A64D218E` (`location_id`),
    KEY `IDX_5387574AFF915C63` (`anonymous_judge_id`),
    KEY `IDX_5387574A61A31DAE` (`anonymous_submitter_id`),
    KEY `IDX_5387574A8453C906` (`cancellation_reason_id`),
    KEY `IDX_5387574AB03A8386` (`created_by_id`),
    KEY `IDX_5387574A99049ECE` (`modified_by_id`),
    KEY `IDX_5387574A401B253C` (`event_type_id`),
    KEY `docket_idx` (`docket`),
    CONSTRAINT `FK_5387574A401B253C` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`),
    CONSTRAINT `FK_5387574A61A31DAE` FOREIGN KEY (`anonymous_submitter_id`) REFERENCES `hats` (`id`),
    CONSTRAINT `FK_5387574A64D218E` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
    CONSTRAINT `FK_5387574A82F1BAF4` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
    CONSTRAINT `FK_5387574A8453C906` FOREIGN KEY (`cancellation_reason_id`) REFERENCES `cancellation_reasons` (`id`),
    CONSTRAINT `FK_5387574A919E5513` FOREIGN KEY (`submitter_id`) REFERENCES `people` (`id`),
    CONSTRAINT `FK_5387574A99049ECE` FOREIGN KEY (`modified_by_id`) REFERENCES `users` (`id`),
    CONSTRAINT `FK_5387574AB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`),
    CONSTRAINT `FK_5387574AB7D66194` FOREIGN KEY (`judge_id`) REFERENCES `judges` (`id`),
    CONSTRAINT `FK_5387574AFF915C63` FOREIGN KEY (`anonymous_judge_id`) REFERENCES `anonymous_judges` (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=122912 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

An *event* has attributes that include, among other things: date, time, language, judge, docket number, location, event-type, 
and the reason for a possible cancellation. 

The *date* and *time* columns refer to the date and time the event is scheduled to take place. They are stored in separate 
fields rather than a single timestamp for a reason:  sometimes users need to create a date with a null time because they know the date, but not the time at which the event will take place, so 
they need to create a placeholder event and add the time later. 

The point of the *language_id* column is self-evident. Note that on some rare occasions, a single court proceeding requires interpreters of more than one language. 
In such cases a separate event is created for each language.

The *judge_id* field is set to a non-null value when the identity of the judge is significant, which normally means it's 
a District Court Judge as opposed to Magistrate. Many proceedings take place before the on-duty Magistrate, and 
<span class="text-monospace">InterpretersOffice</span> does not bother to record the Magistrate's identity because it is 
basically random and does not help to identify the case in a longitudinal sense. In case of a generic or anonymous judge, 
the *anonymous_judge_id* field is populated instead. It's worth noting that one and only one of either the 
*judge_id* or the *anonymous_judge_id* must be null.

The *docket* column contains a docket number (string) in a consistent format that is enforced by the application.

The *location_id* column refers to the place where the event takes place -- for in-court proceedings, a courtroom. 

The *event-type* (represented by the *event_type_id* column) refers to the name of the court proceeding (e.g., pretrial conference)
or ancillary event (e.g., attorney-client interview.)

Belated cancellation is such a common occurrence that <span class="text-monospace">InterpretersOffice</span> also treats the 
reasons for a cancellation as an attribute *cancellation_reason_id*, which is left as null if not applicable.

The *comments* and *admin_comments* are for just that: writing any comments or observations that are relevant to 
the event. The difference is that *comments* are intended to be viewed by anyone who has read access to the 
interpreters schedule, i.e., any court employee and any contract interpreter who is on site; 
*admin_comments*  are intended only for the eyes of Interpreters Office staff.

The columns *submitter_id* and *anonymous_submitter_id* refer to the person (or type of person) who submitted 
the request for an interpreter. The former points to a record in the *people* table; it is populated when the 
identity of the person is required (more about that later). The latter points to the generic type or job description 
of person submitting the request, and is used when the identity of the person making the request 
is not of interest. It points to a record in the *hats* table. The reasoning here is that when a request 
is submitted for certain types of event-types, such as in-court proceedings and USPO PSI interviews, the identity 
of the submitter is useful, if not essential, in order to carry out the assignment or negotiate details around 
it. In other cases -- e.g., when things are busy and the phone is ringing with requests for intake interviews 
for new arrests -- the name of the person calling is not particularly important, only the department -- e.g., 
Pretrial or Magistrates.

[to be continued]









