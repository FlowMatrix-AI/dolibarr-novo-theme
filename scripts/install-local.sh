#!/bin/bash
# Install novo theme + novoux module into a local Dolibarr instance
set -e

TARGET=${1:?"Usage: install-local.sh /path/to/dolibarr/htdocs"}

if [[ ! -d "$TARGET" ]]; then
  echo "Error: $TARGET does not exist"
  exit 1
fi

rsync -av --delete dolibarr/custom/novoux/ "$TARGET/custom/novoux/"

echo ""
echo "Installed. Enable NovouX module, then select 'novo' in Setup > Display."
