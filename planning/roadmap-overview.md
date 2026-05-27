# Novo — Roadmap Overview

## What This Is

A modern, generic Dolibarr theme (`novo`) + companion external module (`novoux`). Not branded to any company — designed as a clean, opinionated base that operators can customize per-client via CSS variable overrides at deploy time.

This is **not** a Dolibarr fork. Zero core files touched. Everything lives in standard extension points: `htdocs/theme/novo/` and `htdocs/custom/novoux/`.

---

## Phases

### Shipped (v1.0.0)

| Phase | Name | Scope |
|-------|------|-------|
| 1 | [Foundation & Boot](phase-1-foundation.md) | Docker dev env, Eldy copy → novo, `--novo-*` var layer, verify it renders |
| 2 | [Visual Restyle](phase-2-visual-restyle.md) | Component-by-component restyle, default palette, dark mode, QA pass |
| 3 | [Palette System](phase-3-palette-system.md) | Token JSON → generated palette CSS, 5 palettes, CI check, client override mechanism |
| 4 | [Companion Module](phase-4-companion-module.md) | novoux module: admin setup page, palette picker, CSS injection, config precedence |
| 5 | [Preview, Package & Ship](phase-5-preview-package-ship.md) | GitHub Pages preview site, zip packaging, Docker docs, v1.0 release |

### Completed (v1.1)

| Phase | Name | Scope |
|-------|------|-------|
| 6 | [Deep Restyle](phase-6-deep-restyle.md) | Full CSS rewrite of tables, tabs, menus, forms, login, cards, modals |
| 7 | [Token System & Density](phase-7-token-system-density.md) | Expand tokens to spacing/typography/layout, density variants (compact/spacious) |

### In Progress (v1.1)

| Phase | Name | Scope |
|-------|------|-------|
| 8 | [Theme JavaScript](phase-8-theme-javascript.md) | `novo.js` — dark mode toggle + sticky table headers. Gated by `ALLOW_THEME_JS`. |

### Next (v1.1 release)

| Phase | Name | Scope |
|-------|------|-------|
| 9 | NovouX Settings Expansion | Add color pickers (accent, danger), dark mode selector, radius presets. Practical admin controls. |
| 10 | Ship & Stabilize | `theme_descriptor.php`, versioning, package script, README with screenshots, GH Actions lint, tag v1.1.0. |

### Deferred (v1.2+ — after real-world feedback)

| Phase | Name | Scope | Prerequisite |
|-------|------|-------|--------------|
| 8b | Sidebar Collapse | JS-driven sidebar collapse/expand with CSS transition support | Phase 8 stable |
| 11 | [Style Variants](phase-11-style-variants.md) | Visual personality variants (flat, elevated, soft) | Density validated in production |
| 12 | [User Prefs & Multi-Tenant](phase-12-user-prefs-multitenant.md) | Per-user display preferences, multi-entity branding | Phase 8+9 stable |
| — | Live Preview & Export | Iframe preview in settings, config export/import | Phase 9 stable |
| — | Dolistore & CI/CD | Full CI pipeline, Dolistore listing, automated releases | v1.1 shipped |

---

## Architecture

- **One theme, configurable via palettes/tokens** — not multiple repos or forks
- **Two artifacts:** `theme/novo/` (visual system) + `custom/novoux/` (config module)
- **CSS custom properties throughout** — `--novo-*` variables. Palettes swap values, not rules.
- **Design tokens as source of truth** — JSON → generated CSS + preview site styles
- **No core edits** — upgradeable, no merge conflicts

---

## Deployment Model

| Stage | Method |
|-------|--------|
| Dev/now | Clone/rsync into Dolibarr, or install as external module zip |
| Production | `COPY` into base Dolibarr Docker image. Per-client CSS overrides injected at build/runtime via volume/configmap. |

Repo stays decoupled from operator infrastructure. Client-specific branding (colors, logo) lives outside this repo — a single CSS file per client that overrides `--novo-*` variables.

---

## Repo Structure (Target)

```
dolibarr-ui-skin/
  README.md
  LICENSE
  docs/
    research.md
  planning/
    roadmap-overview.md       ← this file
    phase-1-foundation.md
    phase-2-visual-restyle.md
    phase-3-palette-system.md
    phase-4-companion-module.md
    phase-5-preview-package-ship.md

  dolibarr/
    theme/novo/
      style.css.php
      theme_vars.inc.php
      global.inc.php
      badges.inc.php
      btn.inc.php
      dropdown.inc.php
      info-box.inc.php
      progress.inc.php
      timeline.inc.php
      img/
      palettes/
        default.css
        slate.css
        blue.css
        green.css
        warm.css

    custom/novoux/
      core/modules/modNovoux.class.php
      admin/setup.php
      css/novo-inject.css.php
      lib/novoux.lib.php
      langs/en_US/novoux.lang
      img/

  tokens/
    default.json
    slate.json
    blue.json
    green.json
    warm.json

  preview/
    index.html
    src/
    styles/
    vite.config.js

  scripts/
    install-local.sh
    build-palettes.js
    package.sh

  .github/workflows/
    ci.yml
    pages.yml

  docker-compose.dev.yml
```

---

## Locked Decisions

### Strategic

| # | Decision | Resolution |
|---|----------|------------|
| 1 | Repo name | `dolibarr-ui-skin` → rename to `dolibarr-novo` in Phase 10 |
| 2 | Theme folder name | `novo` — Latin for "new", short, distinctive |
| 3 | Module internal name | `novoux` — UX/config companion to the theme |
| 4 | CSS variable prefix | `--novo-*` |
| 5 | Base starting point | Copy Eldy v21 structure, replace styling progressively |
| 6 | Target Dolibarr | v21+ only |

### Visual / Design

| # | Decision | Resolution |
|---|----------|------------|
| 7 | Font stack | M1: system stack. M2+: self-hosted Inter variable font + system fallback. |
| 8 | Default palette | `#3b82f6` primary, slate neutral system |
| 9 | Border radius | Cards 8px, buttons 6px, badges 4px, modals 12px |
| 10 | Density | Balanced (14px/38px rows/16px padding/24px spacing) |
| 11 | Sidebar | Restyle in Phase 6. Collapsible in Phase 8 (JS). |
| 12 | Dark mode | `prefers-color-scheme` auto in M2. Manual JS toggle in Phase 8. |

### Default Palette

```
--novo-primary:         #3b82f6   (blue-500)
--novo-primary-hover:   #2563eb   (blue-600)
--novo-bg:              #f8fafc   (slate-50)
--novo-surface:         #ffffff
--novo-text:            #0f172a   (slate-900)
--novo-text-muted:      #64748b   (slate-500)
--novo-border:          #e2e8f0   (slate-200)
--novo-accent:          #8b5cf6   (violet-500)
--novo-success:         #10b981
--novo-warning:         #f59e0b
--novo-danger:          #ef4444
```

### Technical / Implementation

| # | Decision | Resolution |
|---|----------|------------|
| 13 | Preview site | Vite + vanilla → GitHub Pages |
| 14 | Token build | Node script (~50 LOC). Generated CSS committed. CI verifies freshness. |
| 15 | PHP-in-CSS | Keep Dolibarr's `style.css.php` → `global.inc.php` pattern |
| 16 | Config precedence | Env vars → `llx_const` DB → theme defaults |
| 17 | Dashboard | Restyle existing via CSS. Custom widgets post-M5. |
| 18 | Reference repos | Study only — no code copied |

### Process

| # | Decision | Resolution |
|---|----------|------------|
| 19 | Dev testing | Docker Compose with Dolibarr v21 + MariaDB + volume mounts |
| 20 | Public timing | Public from day one. WIP banner until M5 ships. |

---

## Non-Goals (Hard Boundaries)

- No core Dolibarr file edits — ever
- No replacement frontend (no React/Vue/SPA)
- No business logic changes
- No per-client theme forks — overrides are CSS variables only
- No custom PHP templates unless CSS + hooks are genuinely insufficient
- No external JS frameworks in the theme
- No Composer/npm runtime dependencies to use the theme

---

## Compatibility & QA Matrix

| Component | Version |
|-----------|---------|
| Dolibarr | v21.x |
| PHP | Matches official v21 Docker image |
| Database | MariaDB |
| Browsers | Current Chrome, Firefox, Safari |
| Layout | Desktop-first, mobile sanity pass |

### Smoke-Test Pages

- Login
- Dashboard (home)
- Third-party list + card
- Invoice list + card
- Product/service list
- User/admin settings
- Module setup page (novoux)

---

## Licensing

- GPL-3.0 (compatible with Dolibarr ecosystem)
- Reference repos (MD-UX, Kontava) both GPL-3.0
- No code copied from references — study only

---

## Future Vision (Post-M5)

Not committed, but potential directions:

- Collapsible/drawer sidebar via module JS injection
- Custom dashboard homepage with modern widget system
- Login page redesign
- Manual dark-mode toggle with per-user preference
- Density modes (comfortable / compact)
- Layout restructuring where hooks/templates allow
- Template overrides for key pages (only where CSS is insufficient)
