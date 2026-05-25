# Changelog

All notable changes to this project are documented in this file.

Format based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
This project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/FlowMatrix-AI/dolibarr-ui-skin/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/FlowMatrix-AI/dolibarr-ui-skin/compare/v0.3.0...v1.0.0
[0.3.0]: https://github.com/FlowMatrix-AI/dolibarr-ui-skin/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/FlowMatrix-AI/dolibarr-ui-skin/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/FlowMatrix-AI/dolibarr-ui-skin/releases/tag/v0.1.0
