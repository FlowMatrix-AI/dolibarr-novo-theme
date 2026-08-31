#!/usr/bin/env bash
# Seed a running dev/CI Dolibarr: demo modules, novoux activation, Novo theme.
# Safe to re-run.
set -euo pipefail

COMPOSE="${COMPOSE:-docker compose -f docker-compose.dev.yml}"
DB=$($COMPOSE ps -q db)
WEB=$($COMPOSE ps -q web)

if [ -z "$DB" ] || [ -z "$WEB" ]; then
	echo "dev stack is not running — start it with: $COMPOSE up -d" >&2
	exit 1
fi

echo "Seeding constants and demo modules..."
docker exec -i "$DB" mariadb -udolibarr -pdolibarr dolibarr < scripts/seed-visual-test.sql

# Activation goes through modNovoux::init() rather than hand-written SQL so the
# stored constants are exactly what a real activation produces.
echo "Activating novoux..."
docker cp scripts/activate-module.php "$WEB:/tmp/activate-module.php" >/dev/null
docker exec "$WEB" php /tmp/activate-module.php
docker exec "$WEB" rm -f /tmp/activate-module.php

echo "Done."
