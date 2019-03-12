#!/usr/bin/env python3

import configparser, pymysql.cursors, sys, os
from xlrd import open_workbook,xldate_as_tuple
import argparse, json
from os.path import getmtime, isfile
from datetime import date

parser = argparse.ArgumentParser(description='generates JSON documents for interpreter-languages')
parser.add_argument("--spreadsheet", help="path to spreadsheet containing data to parse", required=True)
parser.add_argument("--name-of-worksheet",help="name of worksheet to parse", required=True)
parser.add_argument("--output-file",help="path to output JSON file (defaults to STDOUT)")

args = parser.parse_args()
worksheet = args.name_of_worksheet
spreadsheet = args.spreadsheet
outfile = args.output_file

config_file = "/home/david/.my.cnf";

database = "dev_interpreters"

if not os.access(spreadsheet,os.R_OK):
        print("ERROR: could not open spreadsheet at ",spreadsheet);
        sys.exit(1)

config = configparser.ConfigParser()
config.sections()
config.read(config_file);
credentials = config["client"]

# database connection
connection = pymysql.connect(
        #host='localhost',
        user=credentials['user'], password=credentials['password'], db=database,
        #cursorclass=pymysql.cursors.DictCursor
        )

# collect all our interpreter_ids and names from the database
cursor = connection.cursor()
cursor.execute("select CONCAT(lastname,', ',firstname) name, interp_id from interpreters ORDER BY lastname")
interpreters = dict(cursor.fetchall());
cursor.execute("SELECT name,lang_id id FROM languages")
language_map = dict(cursor.fetchall())

# update query for interpreter database
# If args is a list or tuple, %s can be used as a placeholder in the query.
# If args is a dict, %(name)s can be used as a placeholder in the query.
interp_update = "UPDATE interp_languages SET rating = %(rating)s WHERE interp_id = %(interp_id)s AND lang_id = %(language_id)s";


# open the spreadsheet, and iterate through it
book = open_workbook(spreadsheet)
sheet = book.sheet_by_name(worksheet)
records = dict();

for i in range(1,sheet.nrows):

        name = sheet.cell(i,0).value.strip()
        lang =  sheet.cell(i,1).value.strip()
        classification = sheet.cell(i,2).value.strip()
        # we're expecting "lastname, firstname"
        if ("," not in name):
            print("can't use malformed name {} at row {}".format(name,i))
            continue

        if "\n" in lang and "\n" in classification:
             # multiple values stuffed into one cell
             languages = lang.split();
             classifications = classification.split();
             shit = dict(zip(languages,classifications))
        else:
            if lang not in language_map:
                # the spelling has to be identical for this to work
                 print("interpreter {}: language NOT FOUND: {} at row {}".format(name,lang,i))
                 continue

            shit = {lang:classification}

        if (name in interpreters):
            # all good
            id = interpreters[name]
            if name not in records:
                records[name] = {"id":id, "languages": shit}
            else:
                records[name]["languages"][lang] = classification
        else:
            # try a little harder
            (lastname, firstname) = name.split(",")
            query = "SELECT interp_id id, lastname, firstname FROM interpreters WHERE lastname LIKE %s"
            cursor.execute(query,(lastname+"%"))
            result = cursor.fetchall();
            if len(result) == 1:
                if name in records:
                    del records[name]
                name = "{}, {}".format(result[0][1],result[0][2])
                records[name] = {"id": result[0][0],"languages": shit}

            elif len(result) == 0:
                print("name NOT FOUND: {}, {} at row {}".format(lastname, firstname,i))
            else:
                print("searching for \"{}, {}\": multiple results found for lastname {} at row {}:".format(lastname,firstname,lastname,i),result)
        # try again
        # if (name in records):
        #     interp_id = records[name]["id"];
        #     for language in records[name]["languages"].keys():
        #         language_id = language_map[language]
        #         rating = records[name]["languages"][language]
        #         print("running {}: language {}, id {}, rating {}".format(name,language, language_id,rating))
        #         cursor.execute(interp_update,{"language_id":language_id,"rating":rating,"interp_id":interp_id})

cursor.close()
connection.close()
json_data = json.dumps(records)
if (outfile):
    with open(outfile,"w") as f:
        f.write(json_data)
    print("data was written out to {}".format(outfile))
else:
    print(json_data)

print("==========================")
