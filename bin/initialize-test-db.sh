#!/bin/bash
cd "$(dirname "$0")"
mysql -u root test_office < sql/doctrine-mysql-create-tables.sql
echo -n "database version: "
echo 'SELECT VERSION();'|mysql -s
