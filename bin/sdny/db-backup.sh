#!/bin/bash 

# dumps mysql databases, rotating out files older than 
# 7 days for daily, 6 weeks for weekly. suitable for cron
# to do: make target directory an option
# to do: sync backup directory with s3  
# author # david@davidmintz.org
# with help from https://stackoverflow.com/questions/192249/how-do-i-parse-command-line-arguments-in-bash

set -e
USAGE='usage: db-backup.sh --frequency <daily|weekly> <database> [other_database...]'
BACKUP_DIR="/opt/backup";
POSITIONAL=();
while [[ $# -gt 0 ]]
do
    key="$1"

    case $key in
        -f|--frequency)
        OPTION_FREQUENCY="$2"
        shift # past argument
        shift # past value
        ;;
        -h|--help)
        echo $USAGE
        exit 0
        shift # past argument
        shift # past value
        ;;
        *)    # unknown option
        POSITIONAL+=("$1") # save it in an array for later
        shift # past argument
        ;;
    esac
done
set -- "${POSITIONAL[@]}" # restore positional parameters
if [ -z $OPTION_FREQUENCY ] ; then 
    echo "missing required option -f|--frequency"; 
    echo $USAGE
    exit 1; 
fi;

# half-assed validation. note to self: learn a more elegant technique
for option in daily weekly; do    
    if [ "$OPTION_FREQUENCY" == "$option" ]; then        
        FREQUENCY=$option
        break;
    fi;
done;
if [ -z $FREQUENCY ] ; then 
    echo "invalid option -f|--frequency: $OPTION_FREQUENCY. must be either 'daily; or 'weekly'";
    echo $USAGE
    exit 1; 
fi;
# echo doing backup of type: $FREQUENCY
if [ ${#POSITIONAL[*]} == 0 ]; then
    echo "missing required argument: at least one database name"; exit 1;
    echo $USAGE
fi;

path="${BACKUP_DIR}/${FREQUENCY}"

if [ ! -d $path ]; then
    mkdir -p $path
fi;

for database in ${POSITIONAL[*]}; do 
    FILE="${database}.$(date +%FT%H%M).sql.gz"
    echo -n "dumping to $FILE... "
    mysqldump --defaults-file=$HOME/.my.cnf --single-transaction ${database} | gzip - > $path/$FILE
    echo done.
done;

# no more than 7 days of history for daily; 6 weeks for weekly
if [ ${FREQUENCY} == "daily" ]; then
    DAYS=7
else
    DAYS=42
fi;
cd $path;
TO_DELETE=$(find .  -maxdepth 1 -mtime +${DAYS});

if [ -z $TO_DELETE ]; then 
    echo no backups older than ${DAYS} to rotate out; 
else 
    for file in $TO_DELETE; do
        echo removing $file;
        rm $file;
    done;
fi;

exit 0