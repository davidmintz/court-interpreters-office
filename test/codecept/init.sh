#!/bin/bash

DIR=$(realpath $(dirname $0))
cd ${DIR}/../..

bin/admin-cli admin:user-pass david testing123
# DELETE FROM motd WHERE date IN ('2020-05-18','2020-05-13')
# INSERT INTO `motd` VALUES (4771,512,512,'2020-05-18','this is your MOTD for 2020-15-18','2020-05-07 20:43:01','2020-05-07 20:43:01');
# DELETE FROM motw WHERE week_of IN ('2020-05-18','2020-05-25')

ps aux | grep 'php -S localhost:8080' | grep -qv grep
if [ $? -ne 0 ]; then
    echo starting server...
    php -S localhost:8080 -t ./public
else
    echo server already running on port 8080
fi