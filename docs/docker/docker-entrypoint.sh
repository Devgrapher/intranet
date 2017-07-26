#!/bin/sh
set -e

printenv | sed -r "s/'/\\\'/gm" | sed -r "s/^([^=]+=)(.*)\$/\1'\2'/gm" > /var/www/html/.env
service cron start

# Run the original entrypoint
exec docker-php-entrypoint "$@"
