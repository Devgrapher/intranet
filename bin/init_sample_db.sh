#!/bin/bash
set -e

composer install
cp .env.sample .env
mysql -uroot -h 127.0.0.1 -e "create database intranet;"
vendor/bin/phinx migrate -e local -v
vendor/bin/phinx seed:run -s Teams -s Users -s Rooms -s Policies -s Recipients -e local -v
