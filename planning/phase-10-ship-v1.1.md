# Phase 10 — Ship v1.1 (CI, Packaging, Release)

**Goal:** Make Novo release-ready with automated validation, proper versioning, and a clean GitHub Release. Tag `v1.1.0`.

**Exit criteria:** CI passes on every PR. A tagged push produces a downloadable zip. Versions are aligned across all artifacts.

---

## Deliverables

### 1. Expand CI Workflow (`.github/workflows/ci.yml`)

Current state: only checks palette freshness. Expand to cover all validation:

```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  validate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'

      # PHP lint — all theme and module PHP files
      - name: PHP lint
        run: |
          find dolibarr/ -name '*.php' -print0 | xargs -0 -n1 php -l

      # JS syntax check
      - name: JS check
        run: node --check dolibarr/theme/novo/novo.js

      # Palette & variant freshness
      - name: Build palettes
        run: node scripts/build-palettes.js
      - name: Check generated files are up to date
        run: |
          git diff --exit-code dolibarr/theme/novo/palettes/
          git diff --exit-code dolibarr/theme/novo/variants/
```

Note: keeps it simple — no Docker, no integration tests, no stylelint (CSS is PHP-generated).

### 2. Release Workflow (`.github/workflows/release.yml`)

New file — triggered on version tags:

```yaml
name: Release

on:
  push:
    tags: ['v*']

permissions:
  contents: write

jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'

      # Ensure palettes are fresh
      - run: node scripts/build-palettes.js

      # Build zip
      - name: Package
        run: |
          VERSION="${GITHUB_REF_NAME#v}"
          zip -r "novo-${VERSION}.zip" \
            dolibarr/theme/novo/ \
            dolibarr/custom/novoux/ \
            -x '*.DS_Store' -x '*/.git*'
          echo "ZIPFILE=novo-${VERSION}.zip" >> "$GITHUB_ENV"

      # Create GitHub Release
      - name: Create Release
        uses: softprops/action-gh-release@v2
        with:
          files: ${{ env.ZIPFILE }}
          generate_release_notes: true
          draft: false
          prerelease: false
```

### 3. Version Alignment

Bump all version references to `1.1.0`:

| File | Field | Current | Target |
|------|-------|---------|--------|
| `package.json` | `"version"` | `"1.0.0"` | `"1.1.0"` |
| `dolibarr/theme/novo/theme_descriptor.php` | `$theme_version` | (new in Phase 9) | `'1.1.0'` |
| `dolibarr/custom/novoux/core/modules/modNovoux.class.php` | `$this->version` | `'0.3.0'` | `'1.1.0'` |
| `CHANGELOG.md` | Section header | `## [Unreleased]` | `## [1.1.0] - YYYY-MM-DD` |

Also add link at bottom of CHANGELOG:
```
[1.1.0]: https://github.com/FlowMatrix-AI/dolibarr-ui-skin/compare/v1.0.0...v1.1.0
```

### 4. Package Script Audit

Current `scripts/package.sh` is adequate but verify:
- [x] Reads version from `package.json` ✓
- [x] Includes `dolibarr/theme/novo/` and `dolibarr/custom/novoux/` ✓
- [x] Excludes `.DS_Store` and `.git` ✓
- [ ] Should also exclude `__MACOSX/` (add `-x '__MACOSX*'`)
- [ ] Verify zip internal paths: should unzip as `dolibarr/theme/novo/` and `dolibarr/custom/novoux/` (user extracts into htdocs parent)

Update if needed:
```bash
zip -r "$OUTFILE" \
  dolibarr/theme/novo/ \
  dolibarr/custom/novoux/ \
  -x '*.DS_Store' -x '*/.git*' -x '__MACOSX*'
```

### 5. Final QA Checklist

Before tagging, manually verify in Docker environment:

**Theme rendering:**
- [ ] Fresh `docker compose up`, activate novo theme via Setup > Display
- [ ] Home dashboard, a list page (e.g. Third parties), a card page (e.g. Third party detail)
- [ ] Login page renders correctly with novo styling

**Palettes:**
- [ ] Switch to each palette (default, slate, blue, green, warm) — colors change immediately
- [ ] Verify dark mode looks correct with each palette

**Density:**
- [ ] Compact: rows visibly shorter, table fits more data
- [ ] Default: baseline
- [ ] Spacious: rows taller, more breathing room

**Dark mode (all 4 selector options):**
- [ ] Disabled: always light regardless of OS
- [ ] Auto: respects OS preference (test via DevTools emulation)
- [ ] Toggle: button appears in top-right, cycles Auto/Dark/Light, persists across refresh
- [ ] Forced: always dark

**New Phase 9 features:**
- [ ] Accent color: change to e.g. `#ec4899`, verify buttons/links using accent update
- [ ] Danger color: change to e.g. `#dc2626`, verify danger buttons update
- [ ] Radius preset: each option visibly changes card/button roundness
- [ ] Custom CSS: add `body { border-top: 3px solid red; }`, verify it appears

**Module lifecycle:**
- [ ] Disable novoux module → theme still renders (just without runtime overrides)
- [ ] Re-enable → settings preserved and applied

**Browser compat:**
- [ ] Chrome (latest)
- [ ] Firefox (latest)

### 6. Tag & Release

```bash
# Ensure everything committed
git status

# Tag
git tag -a v1.1.0 -m "Release v1.1.0

Phases 6-9: Deep restyle, token/density system, dark mode toggle,
sticky headers, admin color/radius/dark controls, custom CSS."

# Push
git push origin main --tags
```

The release workflow will automatically create the GitHub Release with the zip.

---

## Implementation Order

1. Expand `ci.yml` with PHP lint + JS check + variant freshness
2. Create `release.yml`
3. Audit and fix `scripts/package.sh` if needed
4. Bump versions in `package.json`, `modNovoux.class.php` (theme_descriptor already done in Phase 9)
5. Move CHANGELOG `[Unreleased]` → `[1.1.0] - <date>`
6. Run full QA checklist
7. Fix any issues found
8. Commit version bumps
9. Tag and push

---

## Out of Scope (Future / v1.2+)

- Dolistore listing
- Repo rename
- Documentation site
- Accessibility audit (axe-core)
- Integration tests with Docker in CI
- Multi-Dolibarr-version matrix testing
