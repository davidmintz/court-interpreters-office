#!/bin/bash
cd "$(dirname "$0")"
mysql -u root test_office < sql/doctrine-mysql-create-tables.sql 
