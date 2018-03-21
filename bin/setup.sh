#!/usr/bin/env bash

set -e

mysql -uroot -h 127.0.0.1 -e "create database intranet;"

vendor/bin/phinx migrate -e local -v
vendor/bin/phinx seed:run -s Teams -s Users -s Rooms -s Policies -s Recipients -s Posts -e local -v
