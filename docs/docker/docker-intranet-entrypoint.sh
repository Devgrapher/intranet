#!/bin/sh
set -e

service cron start

# Run the base image entrypoint
exec /docker-entrypoint.sh "$@"
