#!/bin/bash -e

# get into our own directory
DIR=$(dirname $(realpath $0))
cd $DIR
mysqldump office -d | grep -v 'SQL SECURITY DEFINER' |  \
 sed  's/ AUTO_INCREMENT=[0-9]*//g'  > sql/mysql-schema.sql
