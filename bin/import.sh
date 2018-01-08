#!/bin/bash

# a work almost in progress

#  the following are "elementary" tables:
#  location_types, event_categories, languages, judge_flavors, cancellation_reasons
#  anonymous_judges, holidays

KEY=$(cat ../config/dev.local.conf| sed -e 's/key *= *//')

mysql office < sql/interpreter_language_import.sql
