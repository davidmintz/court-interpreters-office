#!/usr/bin/env python3

import xlsxwriter, json, xlrd, sys
from datetime import date
from dateutil import parser

# previously compiled data has to be piped in
report = json.loads(sys.stdin.read())

# date boundaries for report
summary = report.pop("_summary")
dates = summary["dates"]
errors = 0

date_from = parser.parse(dates["from"]).date()
date_to   = parser.parse(dates["to"]).date()
# excel filename is hard-coded for now...
book = xlrd.open_workbook("excel/CONTRACTOR_PAYMENTS_FY_20.xlsx")
sheet = book.sheet_by_index(0)
col_map = {
    11 : "in_cost", 12 : "in_expenses",
    14 : "out_cost", 15 : "out_expenses"
}
for i in range(2,sheet.nrows):
    cell = sheet.cell(i,2)

    if not (cell.value):
        continue
    d = int(cell.value)
    date_tuple = xlrd.xldate_as_tuple(d,book.datemode)
    date_obj = date(*date_tuple[:3]);
    if not date_obj >= date_from <= date_to:
        continue
    # now we have a row of payment data
    row = sheet.row(i)
    language = row[1].value.strip()
    if language not in report.keys():
        errors += 1
        print("ERROR: language {0} in spreadsheet matches no language in your database"
            .format(language))
        continue
    if language == "Spanish":
        language = "Spanish/contract"
    
    for col, expense in col_map.items():
        if row[col].value:
            report[language][expense] += row[col].value
# print(json.dumps(report))

workbook = xlsxwriter.Workbook('hello.xlsx')
worksheet = workbook.add_worksheet()

row = 1
for language in sorted(report.keys()):
    if language == "totals":
        continue

    if (language == "Spanish/staff") :
        continue
    numbers = report[language]
    if "in_events" not in numbers:
        numbers["in_events"] = 0
    if "out_events" not in numbers:
        numbers["out_events"] = 0
    worksheet.write(row, 0, language);
    worksheet.write(row,1,numbers["in_events"])
    worksheet.write(row,2,numbers["in_cost"])
    worksheet.write(row,3,numbers["in_expenses"])
    worksheet.write(row,4,numbers["out_events"])
    worksheet.write(row,5,numbers["out_cost"])
    worksheet.write(row,6,numbers["out_expenses"])
    row += 1
    # print(data[language])

workbook.close()
