#!/usr/bin/env bash
# Fixture tests for check-dolibarr-releases.sh (#70). Every run is DRY_RUN=1
# against fixture files, so nothing here talks to GitHub.
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
tmp=$(mktemp -d)
trap 'rm -rf "$tmp"' EXIT
fail=0

printf '\t\t$this->max_dolibarr_version = array(24, 0, 0);\n' >"$tmp/desc.php"
cat >"$tmp/releases.json" <<'JSON'
[
  {"tag": "25.0.0", "date": "2027-02-20"},
  {"tag": "24.0.1", "date": "2026-09-07"},
  {"tag": "24.0.0", "date": "2026-08-20"},
  {"tag": "23.0.4", "date": "2026-08-20"},
  {"tag": "18.0.10", "date": "2026-05-07"},
  {"tag": "develop-nightly", "date": "2026-09-01"}
]
JSON

check() { # name, TODAY, issues-json, then expected/forbidden output lines
  local name=$1 today=$2 issues=$3
  shift 3
  printf '%s' "$issues" >"$tmp/issues.json"
  local out bad=0
  out=$(DRY_RUN=1 TODAY="$today" DESCRIPTOR="$tmp/desc.php" \
    RELEASES_JSON_FILE="$tmp/releases.json" ISSUES_JSON_FILE="$tmp/issues.json" \
    bash "$here/check-dolibarr-releases.sh")
  for expect in "$@"; do
    if [[ "$expect" == !* ]]; then
      if grep -qF -- "${expect#!}" <<<"$out"; then
        echo "FAIL [$name]: unexpected: ${expect#!}"
        bad=1
      fi
    elif ! grep -qF -- "$expect" <<<"$out"; then
      echo "FAIL [$name]: missing: $expect"
      bad=1
    fi
  done
  if [ "$bad" = "1" ]; then fail=1; else echo "ok   [$name]"; fi
}

check "opens one issue per release newer than max" 2027-03-01 '[]' \
  "--title Dolibarr 25.0.0 compatibility" \
  "--title Dolibarr 24.0.1 compatibility" \
  "deadline is **2027-10-20**" \
  "!Dolibarr 24.0.0 compatibility" \
  "!Dolibarr 23.0.4" \
  "!18.0.10" \
  "!develop-nightly"

check "is idempotent on existing issues, open or closed" 2027-03-01 \
  '[{"number":7,"title":"Dolibarr 25.0.0 compatibility","state":"OPEN","labels":[{"name":"dolibarr-compat"}]},
    {"number":5,"title":"Dolibarr 24.0.1 compatibility","state":"CLOSED","labels":[{"name":"dolibarr-compat"}]}]' \
  "tracked: Dolibarr 25.0.0 compatibility" \
  "tracked: Dolibarr 24.0.1 compatibility" \
  "!gh issue create" \
  "!--add-label"

check "escalates a major at 4 and 6 months, not yet 7" 2027-08-21 \
  '[{"number":7,"title":"Dolibarr 25.0.0 compatibility","state":"OPEN","labels":[{"name":"dolibarr-compat"}]},
    {"number":5,"title":"Dolibarr 24.0.1 compatibility","state":"OPEN","labels":[{"name":"dolibarr-compat"}]}]' \
  "gh issue edit 7 -R FlowMatrix-AI/dolibarr-novo-theme --add-label compat-4-months" \
  "gh issue edit 7 -R FlowMatrix-AI/dolibarr-novo-theme --add-label compat-6-months" \
  "!--add-label compat-7-months" \
  "!gh issue edit 5"

check "marks a major overdue at 8 months, once" 2027-10-20 \
  '[{"number":7,"title":"Dolibarr 25.0.0 compatibility","state":"OPEN","labels":[{"name":"dolibarr-compat"},{"name":"compat-4-months"},{"name":"compat-6-months"},{"name":"compat-7-months"}]}]' \
  "--add-label overdue" \
  "window closed on 2027-10-20" \
  "!--add-label compat-4-months"

check "does not escalate a closed issue" 2028-01-01 \
  '[{"number":7,"title":"Dolibarr 25.0.0 compatibility","state":"CLOSED","labels":[{"name":"dolibarr-compat"}]}]' \
  "!--add-label"

exit $fail
