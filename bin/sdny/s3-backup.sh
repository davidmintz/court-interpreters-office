#!/bin/bash

# dumps a database to s3 storage

if [ -z "$1" ]; then
  echo "usage: $0 <database_name>"
  exit 1
fi

DATABASE=$1
LOG=/opt/backup/cron.log

S3_OPTIONS="--ca-certs=/etc/ssl/certs/ca-certificates.crt --config=$HOME/.dh-sdny.s3cfg" 


if [ $DATABASE == 'office' ];
	then
		OPTIONS="--ignore-table=interpreters.event_view"
	else OPTIONS="";
fi

FILE="${DATABASE}.$(date +%FT%H%M).sql.gz"

mysqldump --defaults-file=$HOME/.my.cnf $OPTIONS $DATABASE | gzip - > $FILE

if [ $? != 0 ] ; then
	echo "[`date`] mysqldump failed" | tee -a $LOG
	exit 1;
fi;

s3cmd $S3_OPTIONS put $FILE  s3://sdny/
if [ $? != 0 ] ; then
	echo "[`date`] s3 transfer failed" | tee -a $LOG
	exit 1;
fi;
SIZE=$(du -h $FILE | cut -f1);
rm $FILE;
echo "[`date`] transferred $FILE ($SIZE) to s3 storage" >> $LOG
exit 0
