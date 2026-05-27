#!/bin/bash
# Builds module_novoux-X.Y.Z.zip for DoliStore distribution
# The zip contains novoux/ at root — deploy via Dolibarr admin or extract to htdocs/custom/
set -e

VERSION=$(node -p "require('./package.json').version")
OUTFILE="module_novoux-${VERSION}.zip"
STAGING=$(mktemp -d)

echo "Packaging novoux v${VERSION} for DoliStore..."

# Copy module + theme into staging with correct root
cp -r dolibarr/custom/novoux "$STAGING/novoux"

# Remove dev/test files not needed in distribution
rm -rf "$STAGING/novoux/test"
rm -f "$STAGING/novoux/.gitkeep"

# Build the zip with novoux/ at root
cd "$STAGING"
zip -r "$OLDPWD/$OUTFILE" novoux/ \
  -x '*.DS_Store' -x '*/.git*' -x '__MACOSX*'
cd "$OLDPWD"

# Cleanup
rm -rf "$STAGING"

echo "Created ${OUTFILE} ($(du -h "$OUTFILE" | cut -f1))"
echo ""
echo "Install options:"
echo "  1. Dolibarr admin: Setup > Modules > Deploy external module (upload zip)"
echo "  2. Manual: unzip ${OUTFILE} -d /path/to/dolibarr/htdocs/custom/"
echo ""
echo "After install: Setup > Display > select 'novo' theme, then enable NovouX module"
