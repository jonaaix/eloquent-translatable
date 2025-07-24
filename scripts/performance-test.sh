#!/bin/bash

set -e

DB_CONTAINER_NAME="mariadb_performance_test"
DB_VOLUME_NAME="eloquent_translatable_mariadb_data"
DB_PASSWORD="password"
DB_DATABASE="eloquent_translatable_test"
DB_PORT="3307"

if [ ! "$(docker ps -a -q -f name=${DB_CONTAINER_NAME})" ]; then
    echo "Container not found. Creating new persistent container with explicit healthcheck..."
    docker run -d --name "${DB_CONTAINER_NAME}" \
        -v "${DB_VOLUME_NAME}:/var/lib/mysql" \
        -p "${DB_PORT}:3306" \
        -e MARIADB_ROOT_PASSWORD="${DB_PASSWORD}" \
        -e MARIADB_DATABASE="${DB_DATABASE}" \
        --health-cmd="healthcheck.sh --connect --innodb_initialized" \
        --health-interval=5s \
        --health-timeout=3s \
        --health-retries=5 \
        mariadb:lts > /dev/null
else
    echo "Found existing container. Starting it..."
    docker start "${DB_CONTAINER_NAME}" > /dev/null
fi

echo "Waiting for the database container to become healthy..."

# Wait for the container's official health check to pass.
SECONDS=0
until [ "$(docker inspect --format='{{json .State.Health}}' "${DB_CONTAINER_NAME}")" != "null" ] && [ "$(docker inspect --format='{{.State.Health.Status}}' "${DB_CONTAINER_NAME}")" = "healthy" ]; do
    if [ $SECONDS -gt 60 ]; then
        echo "Database container did not become healthy within 60 seconds. Aborting."
        echo "Dumping container logs for debugging:"
        docker logs "${DB_CONTAINER_NAME}"
        exit 1
    fi
    sleep 1
done

echo "Database is ready. Running performance tests..."

vendor/bin/pest -c phpunit.performance.xml --testsuite="Performance Tests"

echo "Tests finished. Please stop the container manually with: docker stop ${DB_CONTAINER_NAME}"
