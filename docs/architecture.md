# Architecture

## Overview

Two artifacts, zero core edits:

```
dolibarr/theme/novo/     ← visual system (CSS via PHP)
dolibarr/custom/novoux/  ← config module (admin GUI + CSS injection)
```

## Theme File Chain

```
Browser → /theme/novo/style.css.php
  ├─ defines ISLOADEDBYSTEELSHEET, NOLOGIN
  ├─ require theme_vars.inc.php       (PHP color vars as RGB strings)
  ├─ require ../../main.inc.php       (Dolibarr bootstrap, $conf, $db)
  ├─ require global.inc.php           (:root vars + all component CSS)
  │     └─ includes palettes/<name>.css  (if non-default palette active)
  ├─ require badges.inc.php
  ├─ require btn.inc.php
  ├─ require dropdown.inc.php
  ├─ require info-box.inc.php
  ├─ require progress.inc.php
  └─ require timeline.inc.php
```

Output is `Content-Type: text/css` — PHP generates CSS dynamically.

## Design Token Flow

```
tokens/*.json          (source of truth)
       │
       ▼
scripts/build-palettes.js
       │
       ▼
dolibarr/theme/novo/palettes/*.css   (committed, :root overrides)
preview/src/palettes.js              (preview site consumes same data)
```

## CSS Custom Properties

All novo styling uses `--novo-*` prefixed variables declared in `:root`:

| Variable | Purpose |
|----------|---------|
| `--novo-primary` | Primary brand colour (buttons, links, focus rings) |
| `--novo-primary-hover` | Primary hover state |
| `--novo-bg` | Page background |
| `--novo-surface` | Card/panel background |
| `--novo-text` | Body text colour |
| `--novo-text-muted` | Secondary text |
| `--novo-border` | Default border colour |
| `--novo-accent` | Accent/highlight colour |
| `--novo-success/warning/danger` | Status colours |
| `--novo-radius-sm/md/lg/xl` | Border radii (4/6/8/12px) |
| `--novo-shadow-sm/md/lg` | Box shadows |
| `--novo-font` | Font stack |
| `--novo-transition` | Default transition timing |

## Config Precedence

```
Environment variable (NOVOUX_PALETTE, NOVOUX_PRIMARY_COLOR)
  ↓ overrides
llx_const DB value (set via novoux admin page)
  ↓ overrides
Theme defaults (hardcoded in :root + palette CSS)
```

## Module Structure

```
dolibarr/custom/novoux/
├─ core/modules/modNovoux.class.php   (descriptor, constants, module_parts)
├─ admin/setup.php                    (palette/color/logo admin form)
├─ css/novo-inject.css.php            (runtime CSS overrides)
├─ lib/novoux.lib.php                 (admin tab helper)
├─ langs/en_US/novoux.lang            (translations)
├─ img/object_novoux.png              (module icon)
└─ sql/data.sql                       (placeholder)
```

## Dark Mode

Controlled by Dolibarr's `THEME_DARKMODEENABLED` constant:
- `1` = follow `prefers-color-scheme`
- `2` = forced dark (all media)

The dark mode block in `global.inc.php` overrides both legacy `--color*` vars and `--novo-*` vars with slate-dark values.

## Per-Client Branding

Override `--novo-*` vars without forking:

1. **Module path**: novoux injects via `module_parts['css']`; set `NOVOUX_PRIMARY_COLOR` in admin
2. **Docker path**: volume-mount a CSS file that overrides `:root` vars
3. **Build path**: bake client CSS into image at build time
