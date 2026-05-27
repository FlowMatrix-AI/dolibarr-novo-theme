# Phase 10 — Ship v1.1 (CI, Packaging, Release)

**Goal:** Make Novo release-ready with automated validation, proper versioning, and a clean GitHub Release. Tag `v1.1.0`.

**Exit criteria:** CI passes on every PR. A tagged push produces a downloadable zip. Versions are aligned across all artifacts. README has current screenshots.

---

## Deliverables

### 1. GitHub Actions — Validation Workflow

**`.github/workflows/validate.yml`** (trigger: PR to `main`)

Jobs:
1. **PHP lint** — `find dolibarr/ -name '*.php' -exec php -l {} \;`
2. **JS check** — `node --check dolibarr/theme/novo/novo.js`
3. **Palette freshness** — run `node scripts/build-palettes.js`, fail if `git diff --exit-code` shows changes
4. **CSS validity** — basic check that generated palette/variant CSS files parse without error

Keep it simple. No Docker spin-up, no integration tests (those are manual for now).

### 2. GitHub Actions — Release Workflow

**`.github/workflows/release.yml`** (trigger: tag push `v*`)

Steps:
1. Checkout code
2. Run `node scripts/build-palettes.js` (ensure freshness)
3. Run `./scripts/package.sh` to produce zip
4. Create GitHub Release with:
   - Release notes from CHANGELOG.md (extract current version section)
   - Attach zip as artifact
   - Mark as latest

### 3. Version Alignment

Synchronize version string `1.1.0` across:

| File | Field |
|------|-------|
| `package.json` | `"version"` |
| `dolibarr/theme/novo/theme_descriptor.php` | `$theme_version` |
| `dolibarr/custom/novoux/core/modules/modNovoux.class.php` | `$this->version` |
| `CHANGELOG.md` | Section header `## [1.1.0] - YYYY-MM-DD` |

### 4. Package Script Audit

Verify `scripts/package.sh`:
- Produces `novo-<version>.zip` with correct internal paths
- Includes both `theme/novo/` and `custom/novoux/`
- Excludes dev files (`.git`, `node_modules`, `planning/`, `preview/`, `tokens/`, `scripts/`, `docs/`)
- Zip structure matches what Dolibarr expects for manual install

### 5. Final QA Pass

Before tagging:
- [ ] Fresh Docker `up`, activate theme, verify all pages render
- [ ] Test all 5 palettes + all 3 densities
- [ ] Dark mode: OS auto, JS toggle (all 3 states), forced dark
- [ ] Radius preset changes apply correctly
- [ ] Custom CSS textarea works
- [ ] Module enable/disable doesn't break theme
- [ ] Verify on Firefox and Chrome

### 6. Tag & Release

```bash
git tag -a v1.1.0 -m "Release v1.1.0 — Deep restyle, tokens, density, dark toggle"
git push origin v1.1.0
```

---

## Files Created/Modified

| File | Change |
|------|--------|
| `.github/workflows/validate.yml` | New — PR validation |
| `.github/workflows/release.yml` | New — release automation |
| `package.json` | Version bump |
| `modNovoux.class.php` | Version bump |
| `theme_descriptor.php` | Version field (from Phase 9) |
| `CHANGELOG.md` | Move Unreleased → `[1.1.0]` with date |
| `scripts/package.sh` | Audit/fix if needed |

---

## Out of Scope (Future / v1.2+)

- Dolistore listing (revisit after real-world feedback)
- Repo rename (decision to make later)
- Style variants (flat/elevated/glass — no demand signal yet)
- Per-user preferences page (localStorage covers dark mode)
- Sidebar collapse JS (Phase 8b — if requested)
- Documentation site (README + docs/ is sufficient)
