---
layout: default
title: data model | documentation | InterpretersOffice.org
---

<h2>The data model</h2>

<span class="text-monospace">InterpretersOffice</span> is intended for use with MySQL or MariaDB for production,
though it also includes a suite of tests that run against SQLite3.

The database consists of the following tables:

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

