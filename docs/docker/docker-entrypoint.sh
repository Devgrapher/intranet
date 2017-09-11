#!/bin/sh
set -e

service cron start

# Run the original entrypoint
exec docker-php-entrypoint "$@"
