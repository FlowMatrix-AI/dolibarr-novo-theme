#!/bin/bash
# Waits for Dolibarr init + demo data import to complete, then forces English
set -e

DB_HOST="${DOLI_DB_HOST:-db}"
DB_NAME="${DOLI_DB_NAME:-dolibarr}"
DB_USER="${DOLI_DB_USER:-dolibarr}"
DB_PASS="${DOLI_DB_PASSWORD:-dolibarr}"

echo "[novo-init] Waiting for Dolibarr to finish initializing..."
# Wait until Dolibarr's install/upgrade is done (lock file removed, users populated)
for i in $(seq 1 120); do
  COUNT=$(mariadb -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -sNe "SELECT COUNT(*) FROM llx_user" 2>/dev/null || echo 0)
  if [[ "$COUNT" -gt 5 ]]; then
    # Dolibarr creates demo users last — wait for any stragglers
    sleep 5
    # Verify count is stable (init finished writing)
    COUNT2=$(mariadb -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -sNe "SELECT COUNT(*) FROM llx_user" 2>/dev/null || echo 0)
    if [[ "$COUNT" -eq "$COUNT2" ]]; then
      break
    fi
  fi
  sleep 2
done

echo "[novo-init] Setting language to en_US for all users..."
mariadb -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" <<'SQL'
UPDATE llx_user SET lang = 'en_US';
UPDATE llx_user_param SET value = 'en_US' WHERE param = 'MAIN_LANG_DEFAULT';
UPDATE llx_const SET value = 'en_US' WHERE name = 'MAIN_LANG_DEFAULT';
DELETE FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT' AND value != 'en_US';
INSERT IGNORE INTO llx_const (name, entity, value, type, visible) VALUES ('MAIN_LANG_DEFAULT', 0, 'en_US', 'chaine', 0);
INSERT IGNORE INTO llx_const (name, entity, value, type, visible) VALUES ('MAIN_LANG_DEFAULT', 1, 'en_US', 'chaine', 0);
SQL
echo "[novo-init] Done. Log in (or refresh) to see English."
