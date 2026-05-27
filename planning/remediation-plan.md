# Remediation Plan

Ordered work plan addressing gaps identified in [docs/audit.md](audit.md).
All unknowns have been researched and resolved. Each phase is directly executable.

---

## Phase A — Image Trim

**Goal:** Remove ~197 dead-weight PNGs that Dolibarr v21 never loads.

### Research Findings

Dolibarr v21's `img_picto()` function resolves icons in this priority order:
1. **FontAwesome** — if the picto name has no `.`/`/`/`@`, it strips `object_` prefix and maps to an FA icon class via `getImgPictoConv()`. No file loaded.
2. **PNG fallback** — only when an explicit file extension or path is in the name.

A grep of all `.php` and `.js` files in our theme shows **15 images** referenced by filename:

```
button_bg.png, calendar.png, edit.png, lock.png, logo_setup.svg,
nographyet.svg, object_paybox.png, object_paypal.png, object_stripe.png,
object_user.png, sort_asc.png, sort_asc_disabled.png, sort_desc.png,
sort_desc_disabled.png, working.gif
```

The remaining **197 files** (76 `object_*.png` + 121 others) are never loaded — FA handles them all.

### Execution

| Step | Action |
|------|--------|
| A1 | Keep the 15 files listed above + `favicon.ico` + `login_background.png` (referenced by CSS) = **~17 files** |
| A2 | `git rm` all other files from `dolibarr/theme/novo/img/` |
| A3 | Run Docker dev, verify no 404s in Network tab on: login, dashboard, list, card, setup, calendar |
| A4 | Verify `package.sh` still works (no glob changes needed — it zips the whole directory) |

**Acceptance:** `img/` drops from 212 to ~17 files. Release zip under 200 KB. Zero 404s.

**Decision:** No legacy branch needed — git history preserves the files. Simple `git rm`.

---

## Phase B — Security Hardening

**Goal:** Close identified CSS injection vectors in the custom CSS field.

### Research Findings (audit of `setup.php` and `novo-inject.css.php`)

**Already secure:**
- CSRF token verified: `$token != newToken()` check before processing ✅
- All GETPOST calls have type parameter (`'alpha'`, `'aZ09'`, `'restricthtml'`) ✅
- Color fields validated with `/^#[0-9a-fA-F]{6}$/` ✅
- Palette/density/radius/darkmode validated against whitelists ✅
- Logo URL validated with `FILTER_VALIDATE_URL` ✅
- `novo.js` uses only `createElement`, `classList`, hardcoded innerHTML — no user-controlled HTML ✅

**Gaps found in custom CSS sanitization (lines 149-155 of setup.php):**

| Vector | Currently blocked? | Risk |
|--------|-------------------|------|
| `<script>` tags | ✅ Yes (regex strip) | — |
| `expression()` | ✅ Yes (regex strip) | — |
| `url(javascript:...)` | ✅ Yes (replaced with `url(blocked:`) | — |
| `@import url(...)` | ❌ **Not blocked** | Can load external stylesheet with arbitrary content |
| `@import "..."` | ❌ **Not blocked** | Same — data exfil or style override from external domain |
| `-moz-binding` | ❌ Not blocked | Firefox XBL (deprecated since FF62, dead in practice) |
| `behavior:` | ❌ Not blocked | IE HTC files (dead — IE is EOL) |
| `url(data:...)` | ❌ Not blocked | Can embed SVG with scripts (blocked by CSP in practice) |

**Real risk:** Only `@import` is a live concern. The others are dead browser attack surfaces.

### Execution

| Step | Action |
|------|--------|
| B1 | Add `@import` stripping to `setup.php` sanitization block: `preg_replace('/@import\b/i', '', $customCss)` |
| B2 | Add `url(data:` blocking: `preg_replace('/url\s*\(\s*["\']?data:/i', 'url(blocked:', $customCss)` |
| B3 | Add `-moz-binding` and `behavior:` blocking (belt-and-suspenders, one line each) |
| B4 | In `novo-inject.css.php`, add output-time re-sanitization of custom CSS as defense-in-depth (same regex set) |
| B5 | Add CSP recommendation to `docs/developing.md`: `style-src 'self' 'unsafe-inline'; script-src 'self'` |
| B6 | Create test file `dolibarr/custom/novoux/test/phpunit/CssSanitizationTest.php` with known-bad inputs |

**Acceptance:** All vectors blocked. Defense-in-depth at both write and read time.

---

## Phase C — PHPUnit Tests

**Goal:** Automated test coverage for module lifecycle and settings.

### Research Findings

- Dolibarr PHPUnit requires a **live database** — no mocking support
- Bootstrap: `master.inc.php` → reads `conf.php` → connects to DB → populates `$db`, `$conf`, `$user`, `$langs`
- Transaction safety: `$db->begin()` in `setUpBeforeClass()`, `$db->rollback()` in `tearDownAfterClass()`
- From test path `custom/novoux/test/phpunit/`, bootstrap path is `../../../../master.inc.php`
- Module enable: `new modNovoux($db)` → `->init()` → assert returns `1`
- Const read after write: must call `$conf->setValues($db)` to reload

### Execution

| Step | Action | Details |
|------|--------|---------|
| C1 | Create `dolibarr/custom/novoux/test/phpunit/NovouXModuleTest.php` | Bootstrap, transaction wrap, test enable/disable |
| C2 | Test module lifecycle | `init()` returns 1, `remove()` returns ≥0, re-`init()` returns 1 |
| C3 | Test constants | `dolibarr_set_const(NOVOUX_PALETTE, 'blue')` → `$conf->setValues($db)` → `getDolGlobalString('NOVOUX_PALETTE') === 'blue'` |
| C4 | Test color validation | Set invalid hex `#ZZZZZZ` — assert const unchanged |
| C5 | Test CSS truncation | Set 5000-char string, read back, assert length ≤ 4096 |
| C6 | Test CSS sanitization | Set `@import url(evil.css); body{color:red}` — assert `@import` stripped, `body{color:red}` preserved |
| C7 | Add `phpunit.xml` at repo root targeting `dolibarr/custom/novoux/test/phpunit/` |

### CI Integration

Add a `phpunit` job to `.github/workflows/ci.yml`:

```yaml
phpunit:
  runs-on: ubuntu-latest
  services:
    mysql:
      image: mariadb:10.6
      env:
        MYSQL_ROOT_PASSWORD: dolibarr
        MYSQL_DATABASE: dolibarr
      ports: ['3306:3306']
      options: --health-cmd="mysqladmin ping" --health-interval=5s --health-timeout=3s --health-retries=10
  steps:
    - uses: actions/checkout@v4
    - uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mysqli, gd, intl, zip
    - name: Install Dolibarr
      run: |
        git clone --depth=1 --branch=develop https://github.com/Dolibarr/dolibarr.git /tmp/dolibarr
        # Generate conf.php
        cat > /tmp/dolibarr/htdocs/conf/conf.php << 'EOF'
        <?php
        $dolibarr_main_url_root='http://localhost';
        $dolibarr_main_document_root='/tmp/dolibarr/htdocs';
        $dolibarr_main_data_root='/tmp/dolibarr/documents';
        $dolibarr_main_db_host='127.0.0.1';
        $dolibarr_main_db_port='3306';
        $dolibarr_main_db_name='dolibarr';
        $dolibarr_main_db_prefix='llx_';
        $dolibarr_main_db_user='root';
        $dolibarr_main_db_pass='dolibarr';
        $dolibarr_main_db_type='mysqli';
        EOF
        mkdir -p /tmp/dolibarr/documents
        cd /tmp/dolibarr/htdocs/install
        php step1.php set
        php step2.php set
        php step5.php set admin admin123
        # Link our module and theme
        ln -s $GITHUB_WORKSPACE/dolibarr/custom/novoux /tmp/dolibarr/htdocs/custom/novoux
        ln -s $GITHUB_WORKSPACE/dolibarr/theme/novo /tmp/dolibarr/htdocs/theme/novo
    - name: Run PHPUnit
      run: |
        cd /tmp/dolibarr
        php vendor/bin/phpunit htdocs/custom/novoux/test/phpunit/ || \
        ../../vendor/bin/phpunit htdocs/custom/novoux/test/phpunit/
```

**Acceptance:** CI runs PHPUnit on every push. All tests pass. Module lifecycle proven automated.

---

## Phase D — Real-World Visual Validation

**Goal:** Prove the theme renders correctly with multiple modules enabled.

### Approach

Use Playwright (Node) to drive a headless browser against the Docker dev instance, screenshot key pages in both light and dark modes.

### Execution

| Step | Action |
|------|--------|
| D1 | Create `tests/visual/playwright.config.js` with baseURL `http://localhost:8080` |
| D2 | Create `tests/visual/pages.spec.js` — login, then navigate to 10 pages (see list below) |
| D3 | Each page: screenshot in default mode + set `data-novo-scheme="dark"` + screenshot |
| D4 | Pages to test: login, home dashboard, third-party list, invoice card, project list, HRM leave list, setup page, user card, calendar, product card |
| D5 | Store baseline screenshots in `tests/visual/baseline/` (gitignored, generated on demand) |
| D6 | Add `npm run test:visual` script; runs `npx playwright test` with comparison mode |
| D7 | Test RTL: set `ar_SA` locale in Docker, screenshot same pages — document any issues |

### Docker Profile

Use existing `docker-compose.dev.yml` — enable extra modules via SQL on container start:

```sql
INSERT INTO llx_const (name, value, type, entity) VALUES
('MAIN_MODULE_PROJET', '1', 'chaine', 1),
('MAIN_MODULE_HRM', '1', 'chaine', 1),
('MAIN_MODULE_ACCOUNTING', '1', 'chaine', 1),
('MAIN_MODULE_PRODUCT', '1', 'chaine', 1);
```

**Acceptance:** 20 baseline screenshots exist (10 pages × 2 modes). Any future change can be visually diffed.

---

## Phase E — Preview Site

**Decision: Keep as-is.**

### Rationale

The preview is only **8 tracked files** (2 HTML, 2 JS, 1 CSS, 1 config, 1 lock, 1 gitignore). The 222-file count was from untracked `node_modules/`. This is lightweight. The GH Pages deployment provides immediate visual marketing value and the `pages.yml` workflow is already working.

### Execution

| Step | Action |
|------|--------|
| E1 | Add a disclaimer banner to `preview/index.html`: "This is a design preview. For the live theme, install in Dolibarr." |
| E2 | Ensure `preview/styles/novo-preview.css` stays in sync with actual `--novo-*` variables — add a CI check or manual note |
| E3 | No other changes needed |

**Acceptance:** Banner visible on GH Pages. No maintenance burden.

---

## Phase F — Packaging & Distribution

**Goal:** Make the zip installable for end users and prepare for potential DoliStore listing.

### Research Findings

- **No XML descriptor needed** — the PHP `modNovoux.class.php` IS the descriptor
- DoliStore requires module ID in range **95000–99999** (ours is `500200` — valid for non-DoliStore distribution but needs reservation if listing)
- `editor_url` must be a valid external URL (currently empty)
- Need `editor_squarred_logo` field pointing to a 512×512 PNG in `img/`
- Zip structure must have module directory at root: `novoux/admin/`, `novoux/core/`, etc.
- **Our current zip structure is wrong for DoliStore** — we use `dolibarr/custom/novoux/` and `dolibarr/theme/novo/` paths. DoliStore expects `novoux/` at root.
- Theme-as-module pattern: set `$this->module_parts['theme'] = 1` and ship theme files inside the module directory at `novoux/theme/novo/`
- **Alternative:** Ship as a dual-artifact (theme zip + module zip) or bundled with install docs

### Decision: Keep current layout, defer DoliStore

The current `package.sh` produces a zip that extracts directly into `htdocs/`. This is **correct for direct download** from GitHub Releases. DoliStore has additional structural requirements that would force reorganizing the repo.

**Ship v1.2 via GitHub Releases** (current workflow). Revisit DoliStore only if there's user demand.

### Execution

| Step | Action |
|------|--------|
| F1 | Set `$this->editor_url = 'https://github.com/FlowMatrix-AI/dolibarr-ui-skin'` in modNovoux |
| F2 | Add a 512×512 module logo PNG at `dolibarr/custom/novoux/img/novoux_512.png` |
| F3 | Add `$this->editor_squarred_logo = 'novoux_512.png@novoux'` |
| F4 | Add compatibility note to README: "Requires Dolibarr ≥ 21.0, PHP ≥ 7.4" |
| F5 | Verify `package.sh` output: extract into a fresh Dolibarr htdocs/, confirm theme appears in Setup > Display and module in Setup > Modules |
| F6 | Add `INSTALL.md` (5-line quick start: download zip, extract, enable theme, enable module, configure) |

**Acceptance:** `package.sh` zip installs cleanly on a vanilla Dolibarr v21 instance. README has compatibility matrix. Editor metadata populated.

---

## Phase G — Feature: Per-User Preferences

**Goal:** Let individual users choose their own palette and density without admin intervention.

### Research Findings

- Dolibarr stores per-user settings in `llx_user_param` table
- Read: `getDolUserString('KEY_NAME')` or `$user->conf->KEY_NAME`
- Write: `dol_set_user_param($db, $conf, $user, array('KEY' => 'value'))`
- `novo-inject.css.php` has access to `$user` after including `main.inc.php`
- Precedence chain: user param → entity const → hardcoded default

### Execution

| Step | Action |
|------|--------|
| G1 | In `novo-inject.css.php`: before reading `NOVOUX_PALETTE` from global const, check `$user->conf->NOVOUX_USER_PALETTE` — if set, use that instead |
| G2 | Same for density: check `$user->conf->NOVOUX_USER_DENSITY` |
| G3 | Create `dolibarr/custom/novoux/user_prefs.php` — a small page that shows palette + density dropdowns, saves via `dol_set_user_param()` |
| G4 | Register as a user tab via hook `addMoreActionsButtons` on user card OR add to `novo.js` as a floating gear icon |
| G5 | Add "Reset to default" option that deletes the user param |
| G6 | Document precedence: User pref (`llx_user_param`) > Admin setting (`llx_const`) > Default |
| G7 | Add PHPUnit test: set user param, verify CSS output uses it; clear param, verify fallback |

**Acceptance:** Two users on the same instance can use different palettes simultaneously.

---

## Phase H — Feature: Sidebar Collapse

**Goal:** Collapsible left navigation with icon-only collapsed state.

### Research Findings

- Sidebar DOM: `<div class="side-nav">` → `<div id="id-left">` → `<div class="vmenu">` (240px)
- Content area: `<div id="id-right">` with `calc(100% - 270px)` width
- `MAIN_MENU_INVERT` only affects top menu — sidebar still renders (collapse works in both modes)
- Dolibarr has a binary responsive hide (`body.sidebar-collapse` + hamburger) but no icon-only state
- Use class `body.novo-sidebar-collapsed` to avoid conflicts

### Execution

| Step | Action |
|------|--------|
| H1 | Add CSS rules to `global.inc.php` for `.novo-sidebar-collapsed .side-nav` → width 48px, overflow hidden |
| H2 | `.novo-sidebar-collapsed .vmenu` → hide text labels, show only FA icons |
| H3 | `.novo-sidebar-collapsed #id-right` → recalculate width |
| H4 | Add CSS transition: `transition: width 0.2s ease` on `.side-nav` and `#id-right` |
| H5 | In `novo.js`: add `initSidebarCollapse()` — inject toggle button (chevron icon) at bottom of `#id-left` |
| H6 | On click: toggle `body.novo-sidebar-collapsed`, save state to `localStorage` key `novo-sidebar-collapsed` |
| H7 | On page load: read localStorage, apply class immediately (before paint — add to `<head>` inline script or apply in IIFE at top of novo.js) |
| H8 | On hover of collapsed sidebar: show full-width tooltip or expand temporarily |
| H9 | Gate behind NovouX constant `NOVOUX_SIDEBAR_COLLAPSE` (checkbox in admin) — default disabled |

**Acceptance:** Sidebar collapses to 48px icon rail. Expands on toggle. State persists. No layout shift on page load.

---

## Phase I — Token Pipeline

**Decision: Keep, but document clearly.**

### Rationale

- 377 lines of JSON → 363 lines of CSS via 112-line build script
- The abstraction is marginal for 5 palettes but becomes valuable if:
  - Users request custom palette generation (planned for per-user prefs)
  - We add more variants (high-contrast, colorblind-safe)
- Removing it saves nothing meaningful (one 112-line script + 7 JSON files)
- The CI already verifies freshness — no "forgot to rebuild" risk

### Execution

| Step | Action |
|------|--------|
| I1 | Add `"build": "node scripts/build-palettes.js"` to root `package.json` scripts |
| I2 | Document in `docs/developing.md`: "After editing `tokens/*.json`, run `npm run build` to regenerate palette CSS" |
| I3 | Add a README header comment to each generated CSS file: `/* Generated from tokens/*.json — do not edit by hand */` |
| I4 | No structural changes |

**Acceptance:** `npm run build` is documented and obvious. Generated files are marked.

---

## Sequencing & Dependencies

```
A (image trim)  ─────────────┐
B (security fix) ────────────┤
I (token docs) ──────────────┤── can all be done in one commit/PR
E (preview banner) ──────────┘
         │
         ▼
C (PHPUnit tests)  ────────────── requires CI workflow changes
         │
         ▼
D (visual validation) ─────────── requires working Docker + Playwright
         │
         ▼
F (packaging & metadata) ──────── requires A done (smaller zip)
         │
         ▼
G (per-user prefs) ────────────── independent feature, after C for test coverage
H (sidebar collapse) ──────────── independent feature, after D for visual validation
```

### Effort Estimates

| Phase | Size | Parallel? |
|-------|------|-----------|
| A | Small (git rm + verify) | Yes — with B, E, I |
| B | Small (4 regex additions + output-time check) | Yes — with A, E, I |
| I | Tiny (add npm script + comments) | Yes — with A, B, E |
| E | Tiny (add banner to HTML) | Yes — with A, B, I |
| C | Medium (test file + CI workflow) | After A+B committed |
| D | Medium (Playwright setup + 20 screenshots) | After C |
| F | Small (metadata + verify install) | After A |
| G | Medium (new page, injection logic, tests) | After C |
| H | Medium-Large (CSS + JS + localStorage) | After D |

---

## Suggested Release Plan

| Version | Contains |
|---------|----------|
| **v1.2.0** | Phases A + B + E + I (hardening, trim, docs) |
| **v1.3.0** | Phases C + F (tests in CI, packaging metadata) |
| **v1.4.0** | Phase D (visual validation baseline established) |
| **v2.0.0** | Phases G + H (per-user prefs, sidebar collapse — breaking feature additions) |
