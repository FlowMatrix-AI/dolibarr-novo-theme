# Architecture

## Overview

Two artifacts, zero core edits:

```
dolibarr/custom/novoux/theme/novo/     ← visual system (CSS via PHP + JS)
dolibarr/custom/novoux/  ← config module (admin GUI + CSS injection)
```

## Theme File Chain

```
Browser → /theme/novo/style.css.php
  ├─ defines ISLOADEDBYSTEELSHEET, NOLOGIN
  ├─ require theme_vars.inc.php       (PHP color vars as RGB strings)
  ├─ require ../../main.inc.php       (Dolibarr bootstrap, $conf, $db)
  ├─ require global.inc.php           (:root vars + all component CSS)
  │     ├─ :root block (tokens: colors, spacing, typography, density, layout)
  │     ├─ @media prefers-color-scheme: dark block (if THEME_DARKMODEENABLED)
  │     ├─ html[data-novo-scheme] selectors (JS-driven dark/light override)
  │     ├─ sticky table header rules
  │     ├─ component sections (body, menus, tables, tabs, forms, login, …)
  │     └─ palette CSS included (if non-default palette active)
  ├─ require badges.inc.php
  ├─ require btn.inc.php
  ├─ require dropdown.inc.php
  ├─ require info-box.inc.php
  ├─ require progress.inc.php
  └─ require timeline.inc.php
```

Additionally, when `ALLOW_THEME_JS` is set:
```
<head> → <script src="/theme/novo/novo.js?..."></script>
```

Output is `Content-Type: text/css` — PHP generates CSS dynamically.

## Design Token Flow

```
tokens/default.json             (colors, spacing, typography, density, layout)
tokens/variants/*.json          (density overrides: compact, spacious)
       │
       ▼
scripts/build-palettes.js
       │
       ├──▶ dolibarr/custom/novoux/theme/novo/palettes/*.css   (color palette overrides)
       └──▶ dolibarr/custom/novoux/theme/novo/variants/*.css   (density variant overrides)
```

## CSS Custom Properties

All novo styling uses `--novo-*` prefixed variables declared in `:root`:

| Category | Variables | Purpose |
|----------|-----------|---------|
| Colors | `--novo-primary`, `--novo-primary-hover`, `--novo-bg`, `--novo-surface`, `--novo-text`, `--novo-text-muted`, `--novo-border`, `--novo-accent`, `--novo-success/warning/danger` | Visual identity |
| Radii | `--novo-radius-sm/md/lg/xl` | Border radii (4/6/8/12px) |
| Shadows | `--novo-shadow-sm/md/lg` | Elevation |
| Spacing | `--novo-spacing-xs/sm/md/lg/xl/2xl` | Padding & margins |
| Typography | `--novo-typography-font-size-{xs…2xl}`, `--novo-typography-line-height-{tight,base,relaxed}` | Text sizing |
| Density | `--novo-density-row-height`, `--novo-density-cell-padding-*`, `--novo-density-input-height` | Table/form density |
| Layout | `--novo-layout-sidebar-width`, `--novo-layout-header-height`, `--novo-layout-content-max-width` | Structural dimensions |
| Font | `--novo-font` | Font stack |
| Transition | `--novo-transition` | Default transition timing |

## CSS Cascade & Specificity

The cascade order (later = higher priority):

```
1. :root { }                         ← base tokens from global.inc.php
2. Palette CSS (early load)          ← overrides :root color tokens
3. Body/component rules              ← consume var() references
4. @media (prefers-color-scheme)     ← OS-driven dark mode
5. html[data-novo-scheme="dark"]     ← JS-driven dark override (higher specificity)
6. Palette CSS (late load)           ← re-asserts palette over inline PHP colors
7. novo-inject.css.php               ← module runtime overrides (primary color, density)
```

## Config Precedence

```
novo-inject.css.php (module_parts['css'] — loaded every page)
  ↓ overrides
NovouX admin settings (llx_const: NOVOUX_PALETTE, NOVOUX_PRIMARY_COLOR, NOVOUX_DENSITY)
  ↓ overrides
Theme defaults (hardcoded in :root + palette CSS)
```

## Module Structure

```
dolibarr/custom/novoux/
├─ core/modules/modNovoux.class.php   (descriptor, constants, module_parts)
├─ admin/setup.php                    (palette/color/density/logo/JS admin form)
├─ css/novo-inject.css.php            (runtime CSS overrides)
├─ lib/novoux.lib.php                 (admin tab helper)
├─ langs/en_US/novoux.lang            (translations)
├─ img/object_novoux.png              (module icon)
└─ sql/data.sql                       (placeholder)
```

## Dark Mode

Three independent mechanisms (any combination works):

| Mechanism | Trigger | How it works |
|-----------|---------|--------------|
| OS preference | `THEME_DARKMODEENABLED=1` | `@media (prefers-color-scheme: dark)` block in CSS |
| Forced dark | `THEME_DARKMODEENABLED=2` | `@media not print` block (always applies) |
| JS toggle | `ALLOW_THEME_JS=1` | `novo.js` sets `html[data-novo-scheme]` attr; CSS selectors override vars |

The JS toggle takes precedence over OS preference because attribute selectors have higher specificity than `@media` rules. When set to "Auto", the attribute is removed and OS preference (if enabled) takes over.

User choice stored in `localStorage('novo-color-scheme')` — persists across sessions, per-browser.

## Theme JavaScript (`novo.js`)

Loaded via `main.inc.php` when `ALLOW_THEME_JS` constant is set. Vanilla ES2020, IIFE, < 5KB.

Features:
- **Dark mode toggle**: injects icon button into `.login_block_other`, cycles Auto → Dark → Light
- **Sticky table headers**: auto-detects `table.liste` with ≥ 8 rows, adds `novo-sticky` class

Degrades gracefully: if JS disabled, dark mode still follows `@media` rules.

## Density System

Three variants affect spatial tokens without changing colors:

| Variant | Row height | Cell padding | Font size | Sidebar width |
|---------|-----------|--------------|-----------|---------------|
| Compact | 32px | 4px 8px | 12px | 200px |
| Default | 38px | 8px 12px | 13px | 240px |
| Spacious | 46px | 12px 16px | 14px | 260px |

Selected via NovouX admin radio group → `NOVOUX_DENSITY` constant → `novo-inject.css.php` loads `variants/density-{value}.css`.

## Per-Client Branding

Override `--novo-*` vars without forking:

1. **Module path**: novoux injects via `module_parts['css']`; set `NOVOUX_PRIMARY_COLOR` in admin
2. **Docker path**: volume-mount a CSS file that overrides `:root` vars
3. **Multi-tenant**: set different `NOVOUX_*` constants per entity
4. **Build path**: bake client CSS into image at build time

## NovouX Settings Reference

All settings are stored in `llx_const` and read via `getDolGlobalString()`.

| Constant | Type | Default | Effect |
|----------|------|---------|--------|
| `NOVOUX_PALETTE` | string | `default` | Active palette filename (without `.css`) |
| `NOVOUX_PRIMARY_COLOR` | hex | (none) | Overrides `--novo-primary` |
| `NOVOUX_ACCENT_COLOR` | hex | (none) | Overrides `--novo-accent` |
| `NOVOUX_DANGER_COLOR` | hex | (none) | Overrides `--novo-danger` |
| `NOVOUX_DENSITY` | string | `default` | `compact`, `default`, or `spacious` |
| `NOVOUX_RADIUS` | string | `default` | Radius preset (see below) |
| `NOVOUX_DARK_MODE` | string | `disabled` | Dark mode behavior (see below) |
| `NOVOUX_LOGO_URL` | url | (none) | Replaces `#img_logo` src |
| `NOVOUX_CUSTOM_CSS` | text | (none) | Raw CSS injected last (max 4096 chars) |

### Radius Presets

| Preset | `--novo-radius-sm` | `--novo-radius-md` | `--novo-radius-lg` | `--novo-radius-xl` |
|--------|-------|-------|-------|-------|
| `sharp` | 2px | 3px | 4px | 6px |
| `default` | 4px | 6px | 8px | 12px |
| `rounded` | 8px | 12px | 16px | 24px |
| `pill` | 50px | 50px | 50px | 50px |

### Dark Mode Behavior

| Option | `THEME_DARKMODEENABLED` | `ALLOW_THEME_JS` | Result |
|--------|------------------------|-------------------|--------|
| `disabled` | 0 | 0 | Always light |
| `auto` | 1 | 0 | Follows OS preference via `@media` |
| `toggle` | 1 | 1 | JS button cycles Auto → Dark → Light |
| `forced` | 2 | 0 | Always dark |

### Custom CSS Sanitization

Input is sanitized on save:
- Strips `<script` tags
- Strips `expression(` (IE CSS expressions)
- Strips `url(javascript:` (XSS vector)
- Truncated at 4096 bytes

## Sticky Table Headers

Rules applied by `novo.js` when `ALLOW_THEME_JS` is enabled:

- Only `table.liste` with ≥ 8 `<tbody>` rows
- Tables inside `.ui-dialog` (modals) are skipped
- Filter row (second `<tr>` in `<thead>`) also sticks, stacked below header row
- `top` offset accounts for `#id-top` header height
- Mobile (no `#id-top` visible): `top: 0px`
- `@media print`: sticky disabled
- Class `novo-sticky` added to table for CSS targeting

## Decisions & Constraints

| Decision | Rationale |
|----------|-----------|
| Dolibarr v21+ only | Uses CSS custom properties, `module_parts`, v21 theme hooks |
| Zero core edits | Survivable across Dolibarr upgrades |
| No external JS frameworks | Keep page weight < 5 KB added |
| No Composer/npm runtime deps | Theme + module must install from zip |
| No per-client forks | Override via CSS variables or `llx_const` only |
| Vanilla ES2020 (no transpile) | All target browsers support it natively |
| Module ID: 500200 | Needs wiki reservation before Dolistore listing |
| Module family: `interface` | Appropriate for UI-only modules |
| Min PHP: matches v21 Docker image | Currently PHP 8.1 |

## CI & Release

### CI (`ci.yml`)

Runs on push/PR to `main`:
1. PHP lint — all `.php` files under `dolibarr/`
2. JS syntax — `node --check dolibarr/custom/novoux/theme/novo/novo.js`
3. Palette/variant freshness — rebuild and `git diff --exit-code`

### Release (`release.yml`)

Triggered by tags matching `v*`:
1. Rebuilds palettes (ensures zip contains fresh output)
2. Creates zip: `module_novoux-X.Y.Z.zip` containing `novoux/` at root (DoliStore format)
3. Creates GitHub Release with zip attached + auto-generated release notes

## Future Ideas

| Idea | Scope | Prerequisite |
|------|-------|--------------|
| Sidebar collapse | JS-driven collapse/expand with CSS transitions | v1.1 stable |
| Style variants | Visual personality variants (flat, elevated, soft) as separate token layer | User demand |
| Per-user preferences | Server-side per-user density/dark pref (beyond localStorage) | NovouX stable in production |
| Dolistore listing | Package for Dolistore marketplace | v1.1 released + real-world feedback |
