#!/usr/bin/env bash
set -e

DOCKER_TAG=${DOCKER_TAG:-latest}
DEFAULT_TAG=$(git rev-parse --short HEAD) # commit hash

docker login -u ${DOCKER_USER} -p ${DOCKER_PASS}

echo "Bulilding ridibooks/intranet..."
docker build -t ridibooks/intranet:${DEFAULT_TAG} .
echo "Builded ridibooks/intranet"

echo "Pushing ridibooks/intranet..."
docker tag ridibooks/intranet:${DEFAULT_TAG} ridibooks/intranet:${DOCKER_TAG}
docker push ridibooks/intranet
echo "Pushed ridibooks/intranet"
