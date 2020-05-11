#!/bin/bash

DIR=$(realpath $(dirname $0))
cd ${DIR}/../..

bin/admin-cli admin:user-pass david testing123

ps aux | grep 'php -S localhost:8080' | grep -qv grep
if [ $? -ne 0 ]; then
    echo starting server...
    php -S localhost:8080 -t ./public
else
    echo server already running on port 8080
fi