#!/bin/bash

LOG=/var/log/vault_audit.log
echo [$(date )] Begin monitoring $LOG ...
while inotifywait -q -e modify $LOG; do

	path=$(tail -n1 $LOG | jq .request.path);
	echo "path requested: $path"

done
