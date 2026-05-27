# Changelog

All notable changes to this project are documented in this file.

Format based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
This project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-05-27

### Added

- **Phase G — Per-User Theme Preferences**
  - Users can override admin palette, density, and primary color from their User card → Theme tab
  - Preferences stored in `llx_user_param` via `dol_set_user_param()`
  - Precedence: user param > admin constant > theme default
  - "Reset to Default" button clears all personal overrides
  - Tab registered on user card via module descriptor `$this->tabs`
- **Phase H — Collapsible Sidebar**
  - Left menu collapses to 48px icon rail via toggle button
  - Hover flyout restores full 240px sidebar (fixed positioning, shadow)
  - State persisted in localStorage, applied early to prevent layout shift
  - Gated by `NOVOUX_SIDEBAR_COLLAPSE` admin constant
  - Hook class (`ActionsNovoux`) injects `data-novo-sidebar-collapse` body attribute
  - Hidden on mobile (<768px) where sidebar is off-canvas
- **Phase D — Playwright Visual Validation**
  - Visual regression test infrastructure with Chromium screenshots
  - 1% pixel diff threshold, 1280×900 viewport
  - Docker-based test environment
- **Phase C — PHPUnit Tests & CI**
  - Unit tests for NovouX module (enable/disable, admin page, CSS injection)
  - GitHub Actions CI with MariaDB 10.6 service
  - `install.forced.php` for headless Dolibarr setup
- **Phase F — Packaging Metadata**
  - `scripts/package.sh` builds distributable zip with checksums
  - Install documentation in `docs/developing.md`

### Fixed

- **Phase A — Image Trim**: Removed ~200 dead/unused image files
- **Phase B — Security Hardening**: Enhanced CSS sanitization in custom CSS textarea
- **Phase E — Preview Banner**: Added development/preview banner to demo site
- CI: PHPUnit path resolution with copied files (not symlinks)
- CI: Headless install without step1.php

## [1.1.0] - 2026-05-27

### Added

- **Phase 9 — NovouX Admin Polish**
  - Accent color picker (hex + visual) with `--novo-accent` override
  - Danger color picker (hex + visual) with `--novo-danger` override
  - Radius preset selector (sharp/default/rounded/pill) — overrides `--novo-radius-*` tokens
  - Dark mode behavior dropdown (disabled/auto/toggle/forced) — replaces ALLOW_THEME_JS checkbox
  - Custom CSS textarea (sanitized, truncated at 4096 chars) for admin-defined overrides
  - `theme_descriptor.php` for theme metadata discovery
- **Phase 8 — Theme JavaScript** (`novo.js`, 3.5 KB)
  - Dark mode toggle button injected into the top-right user menu, cycles Auto → Dark → Light
  - User preference persisted in `localStorage` under key `novo-color-scheme`
  - CSS attribute selectors `html[data-novo-scheme="dark"|"light"]` override all colour variables
    instantly — works independently of `THEME_DARKMODEENABLED`
  - `html[data-novo-scheme="light"]` forces light even if OS prefers dark
  - Sticky table headers: `table.liste` with ≥ 8 rows gets position-sticky `<thead>` offset by header height
  - Graceful degradation: if JS disabled, dark mode still follows OS preference via existing `@media`
  - Gated behind `ALLOW_THEME_JS` constant (checkbox in NovouX admin)
- **Phase 7 — Extended Token System & Density Variants**
  - Token schema expanded: `spacing`, `typography`, `density`, `layout` categories added to `tokens/default.json`
  - Density variants: `tokens/variants/density-compact.json` and `density-spacious.json`
  - Build script generates `dolibarr/theme/novo/variants/density-{compact,spacious}.css`
  - `--novo-spacing-*`, `--novo-typography-*`, `--novo-density-*`, `--novo-layout-*` CSS custom properties declared in `:root`
  - NovouX admin page has density radio group (Compact / Default / Spacious)
  - `novo-inject.css.php` dynamically loads selected density variant CSS
- **Phase 6 — Deep Restyle** (full CSS rewrite of major UI surfaces)
  - Tables: removed legacy 2px colored top-border, added subtle header background, smooth row hover
    transitions, reduced alternating-row contrast, rounded first/last cell corners, checked-row left accent
  - Tabs: replaced background-highlight model with bottom-border underline indicator, transparent inactive
    background, single separator border between tabs and content
  - Top menu: cleaner header bar with shadow instead of border-bottom, tighter spacing
  - Left sidebar: active-item highlight with primary-colour left border, reduced padding
  - Forms & inputs: consistent focus rings, unified border radius, aligned field-label spacing
  - Login page: modernised gradient, centered card with shadow and novo radius, refined spacing
  - Record cards: surface background, subtle border, hover shadow lift
  - Buttons: refined padding/height to match density tokens
  - Modals / dialog boxes: surface background, radius, shadow-lg
  - Status badges: updated spacing and font-weight

### Changed

- Palette CSS now overrides both `--novo-*` and legacy `--color*` Dolibarr variables (full coverage)
- `novo-inject.css.php` applies palette early (prevents flash) and again late (overrides inline PHP colours)
- Dark mode variable block duplicated into JS-driven `html[data-novo-scheme]` selectors for instant switching

### Fixed

- Login page gradient override: `!important` on `.login_center` background to beat inline style from `login.tpl.php`
- Palette not applied inline: moved inline override injection above late-palette load
- Contrast issues on dark backgrounds in several palette combos
- `novo-inject.css.php` now correctly loads selected palette at runtime (was missing)
- PHP pass-by-reference error on NovouX admin page
- Dev environment language defaulting to French after fresh init

## [1.0.0] - 2026-05-25

### Added

- Preview site (Vite static demo) with palette switcher, dark mode toggle, live color picker
- GitHub Pages deployment workflow (`.github/workflows/pages.yml`)
- Packaging script (`scripts/package.sh`) produces distributable zip
- Local install helper (`scripts/install-local.sh`)
- Docker integration documentation (`docs/docker.md`)
- Full README with install, usage, and branding instructions

## [0.3.0] - 2026-05-25

### Added

- Token-driven palette architecture (`tokens/*.json` → `scripts/build-palettes.js` → `palettes/*.css`)
- 5 built-in palettes: default (blue), slate, deep-blue, green (emerald), warm (amber)
- Dark mode overrides in each palette
- Palette selection via `NOVOUX_PALETTE` environment variable or `llx_const` DB setting
- CI workflow (`.github/workflows/ci.yml`) checks generated palette CSS stays in sync with tokens
- `package.json` with `build:palettes` script
- Companion module `novoux` (`dolibarr/custom/novoux/`):
  - Module descriptor with `module_parts['css']` injection
  - Admin setup page for palette selection, primary color override, and logo URL
  - CSRF-protected form with input validation (hex color, URL format)
  - CSS injection file (`novo-inject.css.php`) for runtime overrides
  - English translations (`langs/en_US/novoux.lang`)

## [0.2.0] - 2026-05-25

### Added

- `--novo-*` CSS custom properties: primary, bg, surface, text, border, accent, success, warning, danger, radii, shadows, font, transition
- Dark mode with full slate-based palette overriding `--novo-*` vars (via `THEME_DARKMODEENABLED`)
- Blue focus ring (`box-shadow: 0 0 0 3px rgba(59,130,246,0.1)`) on inputs/textareas

### Changed

- Eldy colour palette replaced with novo design tokens (slate/blue primary)
- Font stack: `system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`
- Buttons: flat design, no gradients, `var(--novo-radius-md)`, font-weight 500
- Inputs: always full border (removed conditional `THEME_SHOW_BORDER_ON_INPUT`), novo radius
- Info-boxes: surface bg, subtle border, hover shadow elevation
- Badges: `var(--novo-radius-sm)`, font-weight 500, no border
- Tab bars and sidebar: border uses `var(--novo-border)`
- Top menu: `box-shadow` replaces `border-bottom`
- Body: antialiased rendering, `line-height: 1.5`, `color: var(--novo-text)`
- Default `$borderradius` changed from 0 to 6px
- All PHP colour variables in `theme_vars.inc.php` updated to novo palette

## [0.1.0] - 2026-05-25

### Added

- Novo theme scaffolded from Dolibarr Eldy v21 (develop branch)
- Docker Compose dev environment (Dolibarr v21 + MariaDB 11, volume mounts, demo data)
- `--novo-*` CSS custom property abstraction layer (initially mapped 1:1 to Eldy values)
- Theme identity: `$theme = 'novo'`, updated AUTHOR, file path comments

[Unreleased]: https://github.com/FlowMatrix-AI/dolibarr-novo-theme/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/FlowMatrix-AI/dolibarr-novo-theme/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/FlowMatrix-AI/dolibarr-novo-theme/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/FlowMatrix-AI/dolibarr-novo-theme/compare/v0.3.0...v1.0.0
[0.3.0]: https://github.com/FlowMatrix-AI/dolibarr-novo-theme/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/FlowMatrix-AI/dolibarr-novo-theme/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/FlowMatrix-AI/dolibarr-novo-theme/releases/tag/v0.1.0
