---
layout: default
title: data model | documentation | InterpretersOffice.org
---

<h2 class="my-4">The entity-relationship model</h2>

The entity-relationship model, as represented in the relational database structure, attempts to model the real-world entities that 
court interpreters deal with in running their office: contract interpreters, staff interpreters, judges, languages, places, types of proceedings, 
users (of the application), and more generally, people who contact the Interpreters to request services.

Like the real world, the entity-relationship model of <span class="text-monospace">InterpretersOffice</span> is complex.

<span class="text-monospace">InterpretersOffice</span> is intended for use with MySQL or MariaDB for production,
but also includes a suite of tests that run against SQLite3. The database consists 
of the following tables, most of which are mapped to Doctrine entity classes:

<pre class="text-white bg-dark p-2">
  <code>
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

  </code>
</pre>

The number and complexity of the relationships makes it difficult to diagram, but [one we have attempted to create](https://dbdiagram.io/d/5ed5abe839d18f5553001627) with 
the online diagram tool provided by [dbdiagram.io](https://dbdiagram.io) shows most of the entity relationships involved the <span class="text-monospace">InterpretersOffice</span>'s
core functions.

### The `events` table

The  [<span class="text-monospace text-nowrap">InterpretersOffice\Entity\Event</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Entity/Event.php) entity is central; it represents an event that requires the services of one or more 
court interpreters. The core of <span class="text-monospace">InterpretersOffice</span> is CRUD operations 
on these entities. It is also the most complex entity, with several attributes that are entities unto 
themselves.

<pre class="text-white bg-dark pl-2 py-1">
  <code>
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
</code>
</pre>



An  <span class="text-monospace">Event</span> has attributes that include, among other things: date, time, language, judge, docket number, location, event-type, 
and the reason for a possible cancellation. 

The *date* and *time* columns refer to the date and time the event is scheduled to take place. They are stored in separate 
fields rather than a single timestamp for a reason:  sometimes users need to create a date with a null time because they know the date, 
but not the time at which the event will take place, so 
they need to create a placeholder event and add the time later. 

The *end_time* is a field that users with maximum adminstrative privileges can enable or disable. When enabled, it is used to store the 
time at which an event actually ended -- not a speculative or aspirational time at which it is hoped or predicted that it will end. 
This feature was added when in the Southern District of New York the interpreters were asked to keep track of the hours and minutes spent 
on interpreting assignments, in addition to existing reporting requirements. It has proven useful, however, as an indicator of 
whether an event is over or still in progress. On busy days it helps administrators keep track of where interpreters are (or at least,
where they are not).

The point of the *language_id* column is self-evident. Note that on some rare occasions, a single court proceeding requires interpreters of more than one language. 
In such cases a separate event is created for each language.

The *judge_id* field is set to a non-null value when the identity of the judge is significant, which normally means it's 
a District Court Judge as opposed to Magistrate. Many proceedings take place before the on-duty Magistrate, and 
<span class="text-monospace">InterpretersOffice</span> does not bother to record the Magistrate's identity because it is 
basically random and does not help to identify the case in a longitudinal sense. In case of a generic or anonymous judge, 
the *anonymous_judge_id* field is populated instead. It's worth noting that one and only one of either the 
*judge_id* or the *anonymous_judge_id* must be null. We should also point out that the [<span class="text-monospace">Judge</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Entity/Judge.php)
entity is a subclass of [<span class="text-monospace">Person</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Entity/Person.php) with a couple of peculiar properties, such as a 
default [<span class="text-monospace text-nowrap">InterpretersOffice\Entity\Location</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Entity/Location.php) and a 
[<span class="text-monospace">InterpretersOffice\Entity\JudgeFlavor</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Entity/JudgeFlavor.php)

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
it. In other cases -- e.g., when things are busy and the phone is ringing with requests for interpreters for intake interviews 
for new arrests -- the name of the person calling is not particularly important, only the department -- e.g., 
Pretrial or Magistrates.

As with the *judge_id* and *anonymous_judge_id* columns, one and only one of either the  *submitter_id* and *anonymous_submitter_id* must be null. 
Note that in both of these cases, in addition to the validation rules applied by the form handling process, the <span class="text-monospace">InterpretersOffice\Entity\Event</span> class has a 
a [Doctrine lifecycle callback](https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/events.html#lifecycle-callbacks) to enforce this 
constraint.

The columns *created*, *modified*, *created_by_id*, and *modified_by_id* are metadata columns for recording who created the event record and when, and who was the 
last user to update it and when. Note that the foreign-key relationship of *created_by_id* and *modified_by_id* is with the **users** table, rather than 
with **people**. The reason is that the data is always manipulated by users, meaning people who are both authenticated and authorized, whereas a 
request for an interpreter can be and often is received from a person who does not have any user account in this system. 
(Of course, the **users** and **people** tables have a foreign-key relationship. A user is a person, but not necessarily vice-versa. More about this in the section on authentication and authorization.)

The column *deleted* is essentially a boolean flag indicating whether the record has been deleted by a user -- soft deletion, in other words. This feature was added 
by popular demand. At present there is no facility for viewing or undeleting records that have been soft-deleted. It merely prevents users from physically deleting 
event records and provides a way to restore them, if only by means of the command-line client.

### entities in a 1-M relationship with `events`

**anonymous_judges**, **judges**, **event_types**,  **languages**, **locations**, **cancellation_reasons**, **people**, **hats** and **users** are all tables (mapped to 
entity classes) with which the **events** table has a one-to-many relationship. As mentioned earlier, an  <span class="text-monospace text-nowrap">Event</span>  entity has either a named judge or an anonymous, generic judge; 
it also has a *location* where it takes place (possibly null), a reason why it was cancelled (*cancellation_reason_id* also possibly null), a language, and an event-type. It also has either a 
named or anonymous/generic submitter. The former is related to the *people* table; the latter, to the *hats* table. 

At this writing, the *hats*, *cancellation_reasons* and *anonymous_judges* cannot be updated by the user; they are basically hard-coded at installation time (this may change 
in a future version). The remaining entities are per force exposed to the users because they need to maintain their own lists of languages, 
locations, judges, event-types, etc., using interfaces provided by <span class="text-monospace">InterpretersOffice</span>. These end up populating the options of the 
select elements of the form used for creating and updating events.

Some entities with which the <span class="text-monospace text-nowrap">InterpretersOffice\Entity\Event</span> has a 1-M relationship likewise have 1-M relationships with other entities. 
One example is the <span class="text-monospace text-nowrap">InterpretersOffice\Entity\Location</span>, which has an attribute *location_type* which is 
represented by the <span class="text-monospace">InterpretersOffice\Entity\LocationType</span>
entity, providing a basic taxonomy for location entities.  A location can be one of several types:

<pre class="text-white bg-dark pl-2">
<code>
MariaDB [office]> select * from location_types;
+----+--------------------------+----------+
| id | type                     | comments |
+----+--------------------------+----------+
|  1 | courtroom                |          |
|  2 | jail                     |          |
|  3 | holding cell             |          |
|  4 | US Probation office      |          |
|  5 | Pretrial Services office |          |
|  6 | interpreters office      |          |
|  7 | courthouse               |          |
|  8 | public area              |          |
+----+--------------------------+----------+
</code>
</pre>

The <span class="text-monospace">Location</span> entity has a self-referencing foreign key called *parent_location_id*. This is to say that up to one level of nesting is supported: 
a location can be in another (parent) location, or it can be a parent location and have no parent. This is useful for modeling courtrooms and courthouses.

Similarly to the <span class="text-monospace">Location</span> entity, <span class="text-monospace text-nowrap">InterpretersOffice\Entity\EventType</span> has a property that is itself
represented by the entity <span class="text-monospace text-nowrap">InterpretersOffice\Entity\EventCategory</span>, a minimal class mapped to the **event_categories** data table,which 
is also populated when the database is first initialized and never modified thereafter. An event-type has one of three categories: "in", "out", or "not applicable." Respectively, 
these mean in-court, as in an official on-the-record proceeding; out-of-court, as in ancillary events like PSI interviews and attorney-client meetings; and neither of the above, 
which is applicable in less frequent cases such as document translation. One of the administrative reasons for tracking this datum in federal court is that the 
Administrative Office of the US Courts requires District Courts to report it.

There are other examples of this type of entity relationship, but this should suffice as an overview. For further details the 
[source code for the entity classes](https://github.com/davidmintz/court-interpreters-office/tree/master/module/InterpretersOffice/src/Entity) in the application's main 
module is a good resource.


### entities in a M-M relationship with `events`

One of the most important pieces of the model is of course the *interpreter.* An event can have multiple interpreters, and interpreters can be assigned, over time, to 
thousands of events. Accordingly, there is an association class [<span class="text-monospace text-nowrap">InterpretersOffice\Entity\InterpreterEvent</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Entity/InterpreterEvent.php)
 which is mapped to a join table called **interpreters_events.** In addition to the IDs of the interpreter and the event, this table holds metadata about the assignment -- who assigned the interpreter to the event, and when -- as well as a 
field indicating whether the interpreter has been sent a confirmation notice via email.

For statistical reporting and other administrative purposes, a court interpreting "event" is frequently a misnomer. What we're really talking about, in many 
cases, is *interpreter-events*. A sentencing hearing involving one non-English language in front of a judge is represented by one row in the events table, but 
there may well be two rows in *interpreters_events* that are related to that event record. In such a case, for reporting purposes, that single court proceeding is 
counted as two interpreter-events, not one. 

Another critically important element of the model -- the *raison d'être* of the entire court interpreting profession -- is the person who does not speak the language of 
the Court and for whom the interpreters are interpreting. For lack of a better term, <span class="text-monospace">InterpretersOffice</span> labels them **defendants** 
(and stores their surnames and given names in an eponymous table). In federal court interpreting, the non-English speaker is usually, 
though not always, a defendant in a criminal proceeding. Although they are in fact people, the data about them is stored in a table separate
from the *people* table for a couple of good reasons, the most compelling of which is that unlike systems used by the immigration or prison authorities,
 <span class="text-monospace">InterpretersOffice</span> *does not try to track the actual identity of defendants.*  The names of defendants involved in 
 court interpreted events are primarily used as an attribute to help distinguish one event from another and avoid under- or over-counting. 
 We therefore "recycle" names when they recur, rather than trying to maintain a separate record for each and every individual for whom the interpreters interpret.

 Hereagain, as with the interpreters, an event can have multiple defendants and defendants are nearly always associated with multiple events.
 Hence the many-to-many relationship. The *defendants_events* table has only the two columns *event_id* and *defendant_id* and no corresponding association class. 
 The `$defendants` property is simply mapped in the <span class="text-monospace">InterpretersOffice\Entity\Event</span> class as a many-to-many 
 relationship with the <span class="text-monospace text-nowrap">InterpretersOffice\Entity\Defendant</span> entity.

### people, interpreters and languages

Interpreters have one or more working languages, and most of our languages are associated with more than one interpreter. Consequently, the interpreter-language is modeled
by the <span class="text-monospace text-nowrap">InterpretersOffice\Entity\InterpreterLanguage</span> entity, which is mapped to an *interpreters_languages* table. The 
<span class="text-monospace text-nowrap">InterpreterLanguage</span> entity in turn has a 1-M relationship with a 
<span class="text-monospace text-nowrap">InterpretersOffice\Entity\LanguageCredential</span> entity whose underlying table is initialized at setup time, and 
currently cannot be updated by the user. The purpose of tracking the <span class="text-monospace text-nowrap">LanguageCredential</span> in the federal court system 
is that the AO uses it to set compensation levels for contract interpreters, and also requires this data in quarterly reports.

The  <span class="text-monospace text-nowrap">InterpretersOffice\Entity\Interpreter</span>  entity is a subclass 
of <span class="text-monospace text-nowrap">InterpretersOffice\Entity\Person</span>. This is implemented using 
Doctrine's [class table inheritance](https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/inheritance-mapping.html#class-table-inheritance). 


### people and hats, users and roles

A user account "has" an associated person and the corresponding entity classes are designed accordingly. This is to say that a user is always associated with a person,
but not vice-versa. A user entity "has" a role entity, the hard-coded roles being "submitter", "administrator", "manager" and "staff". The "submitter" role is for 
users who are authorized only to create and manage their requests for interpreting services, and read (but not edit) the Interpreters' schedule. The "administrator" role 
can do any administrative action involving the interpreters data and application configuration. The "manager" role can do nearly everything the "administrator" can do, the 
exceptions being a few advanced administrative actions and escalation of user privileges beyond its own level. The "staff" role is the least privileged non-submitter role, 
intended for cases such as interns or assistants assigned to the Interpreters office on a temporary basis.

A user account is associated with a person, as noted above, and the <span class="text-monospace text-nowrap">InterpretersOffice\Entity\Person</span> entity has 
a particular <span class="text-monospace text-nowrap">InterpretersOffice\Entity\Hat</span>. Primarily used for classifying and identifying people 
for contact management purposes, a <span class="text-monospace text-nowrap">Hat</span> may be constrained (by foreign key relationship) to a 
particular <span class="text-monospace text-nowrap">InterpretersOffice\Entity\Role</span>. The point is to constrain user-roles to people with particular 
functions (hats, if you will) in the organization. Thus a user who has the "submitter" role has to be (associated with) 
a <span class="text-monospace text-nowrap">Person</span> entity whose hat is either "Courtroom Deputy," "Law Clerk", "USPO", or "Pretrial Services Officer."

Some users are associated with people, hence <span class="text-monospace text-nowrap">Hat</span>s, 
that report to a particular <span class="text-monospace text-nowrap">Judge</span> -- and in rare cases, possibly more than one judge. And Judges 
invariably have multiple clerks, i.e., Law Clerks and/or a Courtroom Deputy Clerk. Therefore you have a many-to-many relationship, which 
is represented by a **clerks_judges** join table.

Complicated? Yes. A great deal of thought has gone into this design and, complicated though it is, this was the simplest model 
I could come up with to separate and manage these various concepts and their relationships.

The foregoing explanation is not exhaustive; it deals with most of the entities found in the main <span class="text-monospace text-nowrap">InterpretersOffice</span> 
module, which are in the <span class="text-monospace text-nowrap">InterpretersOffice\Entity</span> namespace. There are other entity classes in the 
<span class="text-monospace text-nowrap">InterpretersOffice\Requests</span>, <span class="text-monospace text-nowrap">InterpretersOffice\Admin\Notes</span>, and 
<span class="text-monospace text-nowrap">InterpretersOffice\Admin\Rotation</span> modules. While all three of these modules are technically optional, they 
exist because users want them and they're unlikely to change their minds.



<div>
<!-- 
  div 
 | app_event_log          |
 | banned                 |
 | cancellation_reasons   |
 |
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

 -->

</div>
 