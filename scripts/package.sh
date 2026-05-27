#!/bin/bash
# Builds novo-vX.Y.Z.zip for distribution
set -e

VERSION=$(node -p "require('./package.json').version")
OUTFILE="novo-${VERSION}.zip"

echo "Packaging novo v${VERSION}..."

zip -r "$OUTFILE" \
  dolibarr/theme/novo/ \
  dolibarr/custom/novoux/ \
  -x '*.DS_Store' -x '*/.git*' -x '__MACOSX*'

echo "Created ${OUTFILE} ($(du -h "$OUTFILE" | cut -f1))"
echo ""
echo "Install: extract into your Dolibarr htdocs/ directory"
echo "  unzip ${OUTFILE} -d /path/to/dolibarr/htdocs/"
