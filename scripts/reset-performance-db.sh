#!/bin/bash

# This script stops and removes the container and volume for the performance test.

DB_CONTAINER_NAME="mariadb_performance_test"
DB_VOLUME_NAME="eloquent_translatable_mariadb_data"

echo "Stopping and removing container '${DB_CONTAINER_NAME}'..."
docker stop "${DB_CONTAINER_NAME}" > /dev/null 2>&1 || true
docker rm "${DB_CONTAINER_NAME}" > /dev/null 2>&1 || true

echo "Removing volume '${DB_VOLUME_NAME}'..."
docker volume rm "${DB_VOLUME_NAME}" > /dev/null 2>&1 || true

echo "Performance test database has been reset."
