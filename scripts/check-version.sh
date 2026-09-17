#!/usr/bin/env bash
# Fail if the version strings a release depends on disagree.
# With an argument (a tag such as v2.4.0), also require the tag to match.
set -euo pipefail
cd "$(dirname "$0")/.."

pkg=$(node -p "require('./package.json').version")
lock=$(node -p "require('./package-lock.json').version")
mod=$(sed -n "s/.*\$this->version = '\([^']*\)'.*/\1/p" dolibarr/custom/novoux/core/modules/modNovoux.class.php)
theme=$(sed -n "s/^\$theme_version = '\([^']*\)'.*/\1/p" dolibarr/custom/novoux/theme/novo/theme_descriptor.php)

fail=0
for pair in "package-lock.json:$lock" "modNovoux.class.php:$mod" "theme_descriptor.php:$theme"; do
	if [ "${pair#*:}" != "$pkg" ]; then
		echo "::error::${pair%%:*} has version '${pair#*:}', package.json has '$pkg'"
		fail=1
	fi
done

if [ $# -gt 0 ] && [ "${1#v}" != "$pkg" ]; then
	echo "::error::tag '$1' does not match package.json version '$pkg'"
	fail=1
fi

[ $fail -eq 0 ] && echo "version $pkg consistent"
exit $fail
