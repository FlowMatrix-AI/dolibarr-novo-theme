# Developing

## Prerequisites

- Docker & Docker Compose
- Node.js 20+
- A browser

## Dev Environment

```bash
docker compose -f docker-compose.dev.yml up -d
```

Access at http://localhost:8080 (admin / admin123). Theme files are volume-mounted read-only — edit locally, refresh browser to see changes.

## Repo Layout

```
tokens/              ← palette + density source-of-truth (JSON)
tokens/variants/     ← density override tokens (compact, spacious)
scripts/             ← build-palettes.js, package.sh, install-local.sh, init-dev-lang.*
dolibarr/
  theme/novo/        ← the theme (auto-discovered via module_parts['theme'])
    palettes/        ← generated palette CSS files
    variants/        ← generated density CSS files
    novo.js          ← theme JavaScript (dark toggle, sticky headers)
  custom/novoux/     ← companion config module
preview/             ← Vite static preview site
docs/                ← documentation
planning/            ← build plans (phases)
```

## Common Tasks

| Task | Command |
|------|---------|
| Regenerate palettes + variants | `node scripts/build-palettes.js` |
| Validate PHP syntax | `docker compose -f docker-compose.dev.yml exec web php -l /var/www/html/custom/novoux/theme/novo/global.inc.php` |
| Validate JS syntax | `node --check dolibarr/custom/novoux/theme/novo/novo.js` |
| Build preview site | `cd preview && npm ci && npm run build` |
| Package release zip | `./scripts/package.sh` |
| Install to local Dolibarr | `./scripts/install-local.sh /path/to/htdocs` |
| Reset dev DB language to English | `./scripts/init-dev-lang.sh` |
| Run smoke tests | `npm run test:smoke` |
| Run visual tests | `npm run test:visual` |
| Update visual baselines | `npm run test:visual:update` |

## Smoke Testing (Playwright, runs in CI)

Asserts the theme actually loads: `style.css.php`, `manifest.json.php`, and
`novo-inject.css.php` return the right status and content type, key pages render
with no PHP error output, and the `--novo-*` custom properties resolve in the
browser.

```bash
npm ci
npx playwright install chromium
docker compose -f docker-compose.dev.yml up -d
docker exec -i dolibarr-novo-theme-db-1 mariadb -udolibarr -pdolibarr dolibarr < scripts/seed-visual-test.sql
npm run test:smoke
```

No baselines, so unlike the visual suite this runs in CI on every push and PR.
It exists because `php -l` only checks syntax and never executes `style.css.php`
— which is how [#12](https://github.com/FlowMatrix-AI/dolibarr-novo-theme/issues/12)
(a fatal on every theme request) shipped.

To also exercise the second supported install root (`htdocs/novoux/` alongside
`htdocs/custom/novoux/`, both required by Dolibarr's packaging rules), deploy the
module there and set `TEST_SECOND_ROOT=1`:

```bash
docker cp dolibarr/custom/novoux dolibarr-novo-theme-web-1:/var/www/html/novoux
TEST_SECOND_ROOT=1 npm run test:smoke
```

## Visual Testing (Playwright)

Automated screenshot comparison against 10 key Dolibarr pages in both light and dark modes (21 test cases total).

### First-Time Setup

```bash
npm install
npx playwright install chromium          # downloads ~290 MB browser binary
docker compose -f docker-compose.dev.yml up -d
# Wait for Dolibarr to finish initializing (~30s on first boot)
docker exec -i dolibarr-novo-theme-db-1 mariadb -udolibarr -pdolibarr dolibarr < scripts/seed-visual-test.sql
```

> **Note:** The DB container does not expose port 3306 to the host. Use `docker exec` to run SQL, not `mysql -h127.0.0.1`.

### Generate Baselines

```bash
npm run test:visual:update
```

This creates PNG snapshots in `tests/visual/snapshots/` (gitignored). Run this once, and again whenever you intentionally change the theme's appearance.

### Compare Against Baselines

```bash
npm run test:visual
```

Fails if any page differs by more than 1% of pixels from the baseline. Use `--update-snapshots` to accept new appearance.

### Pages Tested

Login, Dashboard, Third-Party List, Invoice List, Project List, HRM Leave List, User Card, Product List, Setup Display, NovouX Setup, Calendar — each in light and dark mode.

## Adding a Palette

1. Create `tokens/<name>.json` (copy an existing one, change colours in the `colors` and `dark` sections)
2. Run `node scripts/build-palettes.js`
3. Commit both the token JSON and the generated `palettes/<name>.css`
4. The palette will appear automatically in NovouX admin (reads filenames from `palettes/` directory)

## Adding a Density Variant

1. Create `tokens/variants/density-<name>.json` with overrides for `spacing`, `typography`, `density`, `layout`
2. Run `node scripts/build-palettes.js`
3. Commit the token JSON and generated `variants/density-<name>.css`
4. Add the variant key to the density radio group in `novoux/admin/setup.php`

## Modifying Theme CSS

All visual changes go in the `.inc.php` files under `dolibarr/custom/novoux/theme/novo/`. Key rules:

- Use `var(--novo-*)` for all colours, radii, shadows, spacing
- Don't rename Dolibarr's existing CSS classes (pages reference them)
- Test in light mode, dark mode (both OS-preference and JS-toggle), and all density levels
- PHP colour variables in `theme_vars.inc.php` are legacy — used by Dolibarr's SkinEditor and chart system
- The `:root` block in `global.inc.php` defines all tokens; palettes/variants override subsets

## Modifying Theme JavaScript

`dolibarr/custom/novoux/theme/novo/novo.js` is loaded when `ALLOW_THEME_JS = 1` (set via NovouX admin or directly in `llx_const`).

Rules:
- Vanilla ES2020 only — no jQuery, no external libs
- IIFE pattern (no global scope pollution)
- Target < 5 KB to keep page weight minimal
- Must degrade gracefully (all features CSS-fallback safe)
- Test: `node --check dolibarr/custom/novoux/theme/novo/novo.js`
- To test in browser: enable `ALLOW_THEME_JS` in Setup > Home > Other or via NovouX admin checkbox

## Dark Mode Testing

Three dark mode mechanisms exist (see [architecture.md](architecture.md) for details):

1. **OS preference** — Set `THEME_DARKMODEENABLED=1` in Dolibarr Setup > Display. Toggle via browser DevTools (Rendering > Emulate prefers-color-scheme).
2. **Forced dark** — Set `THEME_DARKMODEENABLED=2`.
3. **JS toggle** — Enable `ALLOW_THEME_JS`, then click the toggle icon in the top-right menu.

The JS toggle overrides OS preference. When set to "Auto", it removes the override and defers to the OS.

## NovouX Module Development

The companion module lives at `dolibarr/custom/novoux/`. It's volume-mounted into the container at `/var/www/html/custom/novoux/`.

To test changes:
1. Edit files locally
2. Go to http://localhost:8080/custom/novoux/admin/setup.php
3. If module isn't activated: Setup > Modules > search "Novo" > Enable

## Version Locations

When cutting a release, update the version string in **all** of these files:

| File | Field/Line | Purpose |
|------|-----------|---------|
| `package.json` | `"version"` | npm/scripts, used by `scripts/package.sh` for zip filename |
| `dolibarr/custom/novoux/theme/novo/theme_descriptor.php` | `$theme_version` | Dolibarr theme metadata |
| `dolibarr/custom/novoux/core/modules/modNovoux.class.php` | `$this->version` | Module version shown in Dolibarr admin |
| `CHANGELOG.md` | `## [X.Y.Z]` section header + footer links | Release notes |
| `README.md` | zip filename in install example | User-facing docs |

The CI does **not** enforce version consistency — this is a manual step during release.

## QA Testing Checklist

Before tagging a release, verify in the Docker dev environment:

**Theme rendering:**
- Fresh `docker compose up`, activate novo via Setup > Display
- Home dashboard, a list page (e.g. Third Parties), a card page (e.g. Third Party detail)
- Login page renders correctly with novo styling

**Palettes:**
- Switch to each palette (default, slate, blue, green, warm) — colors change on refresh
- Dark mode looks correct with each palette

**Density:**
- Compact: rows visibly shorter, table fits more data
- Default: baseline
- Spacious: rows taller, more breathing room

**Dark mode (all 4 options):**
- Disabled: always light regardless of OS
- Auto: respects OS preference (test via DevTools > Rendering > Emulate prefers-color-scheme)
- Toggle: button appears in top-right, cycles Auto/Dark/Light, persists across refresh
- Forced: always dark

**Admin controls (NovouX setup page):**
- Accent color: change to e.g. `#ec4899`, verify accent-colored elements update
- Danger color: change to e.g. `#dc2626`, verify danger buttons update
- Radius preset: each option visibly changes card/button roundness
- Custom CSS: add `body { border-top: 3px solid red; }`, verify it appears

**Module lifecycle:**
- Disable novoux module → theme still renders (just without runtime overrides)
- Re-enable → settings preserved and applied

**Browser compat:**
- Chrome (latest)
- Firefox (latest)

## CI

- **`ci.yml`**: PHP lint (all `.php` in `dolibarr/`), JS check (`node --check novo.js`), palette + variant freshness
- **`pages.yml`**: builds and deploys preview site on push to `main` (when `preview/` or `tokens/` change)
- **`release.yml`**: tag push → builds zip → creates GitHub Release with auto-generated notes
