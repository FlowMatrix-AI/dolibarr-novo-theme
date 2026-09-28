#!/usr/bin/env bash
# Track Dolibarr releases against the DoliStore 8-month update rule (#70).
#
# The DoliStore provider terms let others publish a port of a module whose
# author has not updated it within 8 months of a Dolibarr release, so the clock
# runs from Dolibarr's release date, not ours. For every Dolibarr release newer
# than max_dolibarr_version this opens ONE tracking issue (idempotent, keyed on
# the dolibarr-compat label and the version in the title). Open issues for a
# major release are escalated at 4, 6 and 7 months and marked overdue at 8.
# Issues are never closed here: the last step, the store upload, cannot be
# observed from GitHub.
#
# Environment:
#   GITHUB_REPOSITORY   repo to file issues in (default FlowMatrix-AI/dolibarr-novo-theme)
#   DRY_RUN=1           print the gh writes instead of running them
#   TODAY               YYYY-MM-DD, for tests (default: today, UTC)
#   DESCRIPTOR          path to modNovoux.class.php
#   RELEASES_JSON_FILE  fixture instead of the Dolibarr/dolibarr releases API
#   ISSUES_JSON_FILE    fixture instead of this repo's dolibarr-compat issues
set -euo pipefail

REPO="${GITHUB_REPOSITORY:-FlowMatrix-AI/dolibarr-novo-theme}"
DESCRIPTOR="${DESCRIPTOR:-dolibarr/custom/novoux/core/modules/modNovoux.class.php}"
TODAY="${TODAY:-$(date -u +%F)}"
DRY_RUN="${DRY_RUN:-0}"
LABEL="dolibarr-compat"

max=$(sed -nE 's/.*max_dolibarr_version = array\(([0-9]+), *([0-9]+), *([0-9]+)\).*/\1.\2.\3/p' "$DESCRIPTOR")
if [ -z "$max" ]; then
  echo "could not read max_dolibarr_version from $DESCRIPTOR" >&2
  exit 1
fi
echo "max_dolibarr_version: $max"

# Dolibarr's GitHub releases, not Docker Hub: the image lags (24.0.1 had no
# dolibarr/dolibarr image nine days after release).
if [ -n "${RELEASES_JSON_FILE:-}" ]; then
  releases=$(cat "$RELEASES_JSON_FILE")
else
  releases=$(gh api "repos/Dolibarr/dolibarr/releases?per_page=50" \
    --jq '[.[] | select(.draft | not) | select(.prerelease | not) | {tag: .tag_name, date: .published_at[0:10]}]')
fi

if [ -n "${ISSUES_JSON_FILE:-}" ]; then
  issues=$(cat "$ISSUES_JSON_FILE")
else
  issues=$(gh issue list -R "$REPO" --label "$LABEL" --state all --limit 500 \
    --json number,title,state,labels)
fi

run() {
  if [ "$DRY_RUN" = "1" ]; then
    echo "DRY-RUN: $*"
  else
    "$@"
  fi
}

# a > b as versions.
newer() {
  [ "$1" != "$2" ] && [ "$(printf '%s\n%s\n' "$1" "$2" | sort -V | tail -n1)" = "$1" ]
}

labels_ensured=0
ensure_labels() {
  [ "$labels_ensured" = "1" ] && return
  run gh label create "$LABEL" -R "$REPO" --force --color 5319e7 \
    --description "A Dolibarr release the listed max version does not cover"
  for l in compat-4-months compat-6-months compat-7-months; do
    run gh label create "$l" -R "$REPO" --force --color fbca04 \
      --description "Dolibarr major released this long ago and not yet ported"
  done
  run gh label create overdue -R "$REPO" --force --color b60205 \
    --description "Past the DoliStore 8-month update window"
  labels_ensured=1
}

title_for() { echo "Dolibarr $1 compatibility"; }

body_for() {
  local v=$1 date=$2 deadline=$3
  if [ -n "$deadline" ]; then
    cat <<BODY
Dolibarr **$v** was released on **$date**. \`max_dolibarr_version\` is $max.

This is a **major** release. The DoliStore provider terms let others publish a port of a module whose author has not updated it within 8 months of a Dolibarr release, so the deadline is **$deadline**. This issue gets escalation labels at 4, 6 and 7 months and \`overdue\` at 8.

- [ ] Add $v to the CI smoke matrix and get a green run
- [ ] Bump \`max_dolibarr_version\` in \`modNovoux.class.php\`
- [ ] Cut a release
- [ ] Upload it to DoliStore, then close this issue

Opened by \`scripts/check-dolibarr-releases.sh\` (#70). It never closes issues: the store upload cannot be seen from GitHub.
BODY
  else
    cat <<BODY
Dolibarr **$v** was released on **$date**. \`max_dolibarr_version\` is $max.

Patch release: low priority, and no DoliStore deadline. It is still tracked because the listing states an exact maximum version.

- [ ] Smoke-test against $v
- [ ] Bump \`max_dolibarr_version\` in \`modNovoux.class.php\`
- [ ] Include it in the next release and upload to DoliStore, then close this issue

Opened by \`scripts/check-dolibarr-releases.sh\` (#70). It never closes issues: the store upload cannot be seen from GitHub.
BODY
  fi
}

while IFS=$'\t' read -r v date; do
  [[ "$v" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]] || continue
  newer "$v" "$max" || continue
  title=$(title_for "$v")
  if jq -e --arg t "$title" 'any(.[]; .title == $t)' <<<"$issues" >/dev/null; then
    echo "tracked: $title"
    continue
  fi
  deadline=""
  if [[ "$v" =~ \.0\.0$ ]]; then
    deadline=$(date -u -d "$date + 8 months" +%F)
  fi
  ensure_labels
  run gh issue create -R "$REPO" --label "$LABEL" --title "$title" --body "$(body_for "$v" "$date" "$deadline")"
done < <(jq -r '.[] | [.tag, .date] | @tsv' <<<"$releases")

# Escalate open issues for major releases.
while IFS=$'\t' read -r number title have; do
  v=$(sed -nE 's/^Dolibarr ([0-9]+\.0\.0) compatibility$/\1/p' <<<"$title")
  [ -n "$v" ] || continue
  date=$(jq -r --arg v "$v" '.[] | select(.tag == $v) | .date' <<<"$releases")
  [ -n "$date" ] || continue
  for step in "4 compat-4-months" "6 compat-6-months" "7 compat-7-months" "8 overdue"; do
    months=${step%% *}
    label=${step#* }
    due=$(date -u -d "$date + $months months" +%F)
    [[ "$TODAY" < "$due" ]] && continue
    [[ " $have " == *" $label "* ]] && continue
    ensure_labels
    run gh issue edit "$number" -R "$REPO" --add-label "$label"
    if [ "$label" = "overdue" ]; then
      msg="Dolibarr $v was released on $date. The 8-month DoliStore update window closed on $due and Novo UX has not been ported."
    else
      msg="Dolibarr $v was released on $date, $months months ago. The DoliStore update deadline is $(date -u -d "$date + 8 months" +%F)."
    fi
    run gh issue comment "$number" -R "$REPO" --body "$msg"
  done
done < <(jq -r '.[] | select(.state == "OPEN") | [.number, .title, ([.labels[].name] | join(" "))] | @tsv' <<<"$issues")
