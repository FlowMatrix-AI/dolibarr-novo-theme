# Changelog

## v1.0.0 — Release

- Preview site: Vite static demo with palette switcher, dark mode toggle, live color picker
- GitHub Pages deployment workflow
- Packaging: `scripts/package.sh` produces install zip, `scripts/install-local.sh` for dev
- Docker integration documentation (`docs/docker.md`)
- README: full install/usage/branding documentation

## v0.3.0 — Palette System

- Token-driven palette architecture: `tokens/*.json` → `scripts/build-palettes.js` → `palettes/*.css`
- 5 built-in palettes: default (blue), slate, deep-blue, green (emerald), warm (amber)
- Each palette includes light and dark mode overrides
- Palette selection via `NOVOUX_PALETTE` env var or `llx_const` DB setting
- Sanitised palette loading with path traversal protection
- CI workflow checks generated CSS stays in sync with tokens
- `package.json` with `build:palettes` script
- Companion module `novoux` (htdocs/custom/novoux/):
  - Admin setup page for palette selection, primary color override, logo URL
  - CSS injection via `module_parts['css']` for runtime overrides
  - CSRF-protected form with input validation
  - English translations

## v0.2.0 — Visual Restyle

- Replace Eldy colour palette with novo design tokens (slate/blue primary)
- Add `--novo-*` CSS custom properties: primary, bg, surface, text, border, accent, success, warning, danger, radii, shadows
- System-ui font stack replaces arial/tahoma
- Buttons: flat design, no gradients, novo radius/shadow, proper hover/focus states
- Inputs/textareas: full border always, blue focus ring with `box-shadow`
- Info-boxes: surface bg, subtle border, hover shadow elevation
- Badges: smaller radius, font-weight 500, no border
- Dropdowns: default border-radius 6px
- Tab bars & sidebar: novo border colour
- Top menu: shadow instead of border-bottom
- Body: antialiased rendering, line-height 1.5
- Dark mode: full slate-based dark palette with `--novo-*` overrides
- Default border-radius changed from 0 to 6px
- theme_vars.inc.php: all PHP colour variables updated to novo palette

## v0.1.0 — Foundation

- Scaffold novo theme from Eldy v21 (Dolibarr develop branch)
- Rebrand identity: `$theme = 'novo'`, updated AUTHOR, file path comments
- Introduce `--novo-*` CSS custom property abstraction layer (maps to Eldy values)
- Docker Compose dev environment (Dolibarr v21 + MariaDB, volume mounts, demo data)
- Theme renders in Dolibarr with no PHP errors, selectable via Setup > Display
