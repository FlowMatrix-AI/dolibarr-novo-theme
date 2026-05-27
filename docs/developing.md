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
  theme/novo/        ← the theme (PHP + CSS + JS)
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
| Validate PHP syntax | `docker compose -f docker-compose.dev.yml exec web php -l /var/www/html/theme/novo/global.inc.php` |
| Validate JS syntax | `node --check dolibarr/theme/novo/novo.js` |
| Build preview site | `cd preview && npm ci && npm run build` |
| Package release zip | `./scripts/package.sh` |
| Install to local Dolibarr | `./scripts/install-local.sh /path/to/htdocs` |
| Reset dev DB language to English | `./scripts/init-dev-lang.sh` |

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

All visual changes go in the `.inc.php` files under `dolibarr/theme/novo/`. Key rules:

- Use `var(--novo-*)` for all colours, radii, shadows, spacing
- Don't rename Dolibarr's existing CSS classes (pages reference them)
- Test in light mode, dark mode (both OS-preference and JS-toggle), and all density levels
- PHP colour variables in `theme_vars.inc.php` are legacy — used by Dolibarr's SkinEditor and chart system
- The `:root` block in `global.inc.php` defines all tokens; palettes/variants override subsets

## Modifying Theme JavaScript

`dolibarr/theme/novo/novo.js` is loaded when `ALLOW_THEME_JS = 1` (set via NovouX admin or directly in `llx_const`).

Rules:
- Vanilla ES2020 only — no jQuery, no external libs
- IIFE pattern (no global scope pollution)
- Target < 5 KB to keep page weight minimal
- Must degrade gracefully (all features CSS-fallback safe)
- Test: `node --check dolibarr/theme/novo/novo.js`
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

Admin settings stored in `llx_const`:
- `NOVOUX_PALETTE` — active palette name (matches filename in `palettes/`)
- `NOVOUX_PRIMARY_COLOR` — hex colour override
- `NOVOUX_DENSITY` — `compact`, `default`, or `spacious`
- `NOVOUX_LOGO_URL` — custom logo URL
- `ALLOW_THEME_JS` — enables `novo.js` loading

## CI

- **Palette freshness**: `ci.yml` runs `build-palettes.js` and fails if generated files differ from committed
- **Pages deploy**: `pages.yml` builds and deploys preview site on push to `main`
