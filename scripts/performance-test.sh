#!/bin/bash

set -e

DB_CONTAINER_NAME="mariadb_performance_test"
DB_VOLUME_NAME="eloquent_translatable_mariadb_data"
DB_PASSWORD="password"
DB_DATABASE="eloquent_translatable_test"
DB_PORT="3307"

# --- Initial Setup ---
# Clean up result files from previous runs
rm -f tests/Feature/Performance/baseline_results.json
rm -f tests/Feature/Performance/performance_summary.json

# Ensures the container exists and is running.
if [ ! "$(docker ps -a -q -f name=${DB_CONTAINER_NAME})" ]; then
    echo "Container '${DB_CONTAINER_NAME}' not found. Creating it for the first time."
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
    echo "Found existing container. Starting it if not running..."
    docker start "${DB_CONTAINER_NAME}" > /dev/null
fi


# Function to run the performance test for a single package
run_test_for_package() {
    local test_filter=$1
    local package_name=$(echo "$test_filter" | sed 's/PerformanceTest//')

    echo "========================================================================"
    echo " PREPARING FAIR ENVIRONMENT FOR: $package_name"
    echo "========================================================================"

    # Restart the container to flush database server caches for a fair test
    # echo "Restarting database container to reset server state..."
    # docker restart "${DB_CONTAINER_NAME}" > /dev/null

    # Wait for the database to become healthy
    echo "Waiting for the database container to become healthy..."
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

    echo "Database is ready. Running performance tests for $package_name..."
    vendor/bin/pest -c phpunit.performance.xml --filter "$test_filter"
    echo ""
}

# --- Main Execution ---
run_test_for_package "AaixTranslatablePerformanceTest"
run_test_for_package "AstrotomicTranslatablePerformanceTest"
run_test_for_package "SpatieTranslatablePerformanceTest"

# --- Final Summary Table ---
php scripts/generate-summary.php

echo "All tests finished."
echo "You can stop the container manually with: docker stop ${DB_CONTAINER_NAME}"
echo "========================================================================"
