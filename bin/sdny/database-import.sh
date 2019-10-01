#!/bin/bash -e

# https://www.tutorialspoint.com/unix_commands/getopt.htm
echo beginning at $(date);

TEMP=`getopt -o '' --long whole-enchilada,everything,recent,scrape-judges -- "$@"`
eval set -- "$TEMP"

while true ; do
    case "$1" in
        --whole-enchilada|--everything)
        FULL_DATABASE=1
        shift ;;
        --recent)
        RECENT_ONLY=1
        shift ;;
        --scrape-judges)
        SCRAPE_JUDGES=1
        shift ;;

        --) shift ; break ;;
        *) echo "Internal error!" ; exit 1 ;;
    esac
done

# may need $RED later
RED='\033[0;31m'
NC='\033[0m' # No Color
GREEN='\033[32m'
function OK {
	printf "${GREEN}OK${NC}\n"
}

# get into our application root directory
DIR=$(dirname $(realpath $0))
cd $DIR/../..

# run the database setup
echo -n "(re)creating database tables... "
mysql office < bin/sql/mysql-schema.sql
OK;
echo -n "loading some initial data..."
mysql office < bin/sql/initial-data.sql
OK;

# interpreters and languages, sans dob or ssn
# we can do that later
echo -n "importing interpreters and languages..."
mysql office < bin/sdny/interpreter-language-import.sql
OK;

echo  -n "importing (some) user accounts..."
mysql office < bin/sdny/import-users.sql
OK;

echo "importing staff users...";
bin/sdny/import-staff-users.php
OK;

echo  -n "importing AUSA and defense atty data..."
mysql office < bin/sdny/import-ausas-and-def-attys.sql
OK;

echo -n "inserting some locations..."
cat bin/sdny/parent-locations.sql bin/sdny/more-locations.sql | mysql office
OK;

if [[ ! -z $SCRAPE_JUDGES ]]; then
    echo -n "please wait, scraping judges and courtrooms from nysd.uscourts.gov..."
    /opt/www/interpreters/bin/scrape-complete-judge-directory.php > judges-courtrooms.json
    OK;
fi;


echo -n "inserting judges and courtrooms with (newly?) downloaded data..."
bin/sdny/import-judges-and-courtrooms.php < bin/sdny/judges-courtrooms.json
OK;


echo -n "inserting clerks-judges..."
cat bin/sdny/import-clerks-judges.sql | mysql office
OK;


echo "importing event-types..."
bin/sdny/import-event-types.php
OK;

echo "importing court closings..."
echo 'INSERT INTO court_closings (SELECT id, holiday_id, date, description_other FROM dev_interpreters.court_closings)'| mysql office
OK;

echo "importing defendant names..."
echo 'INSERT INTO defendant_names (id, given_names, surnames) (SELECT deft_id, firstname, lastname FROM dev_interpreters.deft_names ORDER BY deft_id)'|mysql office
OK;

if [[ ! -z $FULL_DATABASE ]];
    then echo "whole-enchilada|everything flag WAS set; gonna keep going."
    # separate, consecutive processes to limit memory consumption
	./bin/sdny/import-events.php --from 2001 --to 2004 && \
	./bin/sdny/import-events.php --from 2005 --to 2007 && \
	./bin/sdny/import-events.php --from 2008 --to 2010 && \
	./bin/sdny/import-events.php --from 2011 --to 2013 && \
	./bin/sdny/import-events.php --from 2014 --to 2016 && \
	./bin/sdny/import-events.php --from 2017 --to 2019
	./bin/sdny/import-events.php --from 2020
	OK;
	echo  -n "importing defendants_events and interpreters_events..."
	mysql office < bin/sdny/import-deft-and-interp-events.sql
    echo "returned exit status: $?"
	OK;
fi;
echo  "importing request records..."
bin/sdny/import-requests.php
OK;
echo "importing MOTD|MOTW..."
bin/sdny/motd-import.php
OK;
echo "success!"
echo completed at $(date);
exit 0;
