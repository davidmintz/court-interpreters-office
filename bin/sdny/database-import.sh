#!/bin/bash -e


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
OK;
# load some initial data
mysql office < bin/sql/initial-data.sql
OK;

# interpreters and languages, sans dob or ssn
echo -n "importing interpreters and languages..."
mysql office < bin/sdny/interpreter-language-import.sql
OK;

echo  -n "importing (some) user accounts..."
mysql office < bin/sdny/import-users.sql
OK;

echo  -n "importing AUSA and defense atty data..."
mysql office < bin/sdny/import-ausas-and-def-attys.sql
OK;

echo -n "inserting some locations..."
cat bin/sdny/parent-locations.sql bin/sdny/more-locations.sql | mysql office
OK;

#echo -n "please wait, scraping judges and courtrooms from nysd.uscourts.gov..."
#/opt/www/interpreters/bin/scrape-complete-judge-directory.php > judges-courtrooms.json
#OK;

echo -n "inserting judges and courtrooms with (newly?) downloaded data..."
bin/sdny/import-judges-and-courtrooms.php < bin/sdny/judges-courtrooms.json
OK;

echo "importing event-types..."
bin/sdny/import-event-types.php
OK;

echo
echo "all done for now."
