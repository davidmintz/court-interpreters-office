#!/bin/bash -e

# get the most recent dump of "office" database from s3 storage and load into 
# office_staging without trashing user passwords that may have been set on "staging".
# note that this will only work as well as 'mysql office_staging'

uri=$(s3cmd -c /home/david/.dh-sdny.s3cfg ls s3://sdny/office*|tail -n 1| awk '{print $4}')
echo downloading $uri...
s3cmd -c /home/david/.dh-sdny.s3cfg get $uri
file=$(basename $uri)
echo creating and loading tmp user table...
sql='CREATE TABLE `tmp_users` (
  `id` smallint(5) unsigned NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
echo $sql | mysql office_staging
echo 'INSERT INTO tmp_users (SELECT id, username, password FROM users)'|mysql office_staging

echo dumping $file to office_staging database...
zcat $file | mysql office_staging

echo restoring passwords from tmp table...
sql='UPDATE users u, tmp_users t SET u.password = t.password WHERE u.id = t.id AND u.password <> t.password';
echo $sql | mysql office_staging
echo cleaning up...
echo 'DROP TABLE tmp_users'| mysql office_staging
rm $file
echo done
exit 0