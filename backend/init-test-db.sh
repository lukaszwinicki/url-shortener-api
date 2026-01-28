#!/bin/bash

set -e

DB_USER="shorturl"
DB_PASSWORD="shorturl_secret"
DB_NAME="shorturl_test"

echo "Dropping existing test database if exists..."
docker exec -e PGPASSWORD="$DB_PASSWORD" shorturl_postgres psql -U "$DB_USER" -d postgres -c "DROP DATABASE IF EXISTS $DB_NAME"

echo "Creating test database..."
docker exec -e PGPASSWORD="$DB_PASSWORD" shorturl_postgres psql -U "$DB_USER" -d postgres -c "CREATE DATABASE $DB_NAME"

echo "Creating database schema..."
docker exec shorturl_php php bin/console --env=test doctrine:schema:create

echo "Test database initialized successfully!"
