# Phase 3 — Palette System

**Goal:** A token-driven palette system that makes novo configurable without touching CSS. Switch palettes by changing one config value. Enable per-client branding with a single external CSS file.

**Exit criteria:** All 5 built-in palettes render correctly. A client override CSS file can rebrand the entire UI by swapping `--novo-*` variables.

---

## Architecture

```
tokens/              ← source of truth (JSON)
  default.json
  slate.json
  blue.json
  green.json
  warm.json

scripts/
  build-palettes.js  ← reads tokens, writes palette CSS

dolibarr/theme/novo/palettes/   ← generated output (committed to repo)
  default.css
  slate.css
  blue.css
  green.css
  warm.css
```

### Token JSON Format

```json
{
  "name": "default",
  "label": "Default Blue",
  "colors": {
    "primary": "#3b82f6",
    "primary-hover": "#2563eb",
    "bg": "#f8fafc",
    "surface": "#ffffff",
    "text": "#0f172a",
    "text-muted": "#64748b",
    "border": "#e2e8f0",
    "accent": "#8b5cf6",
    "success": "#10b981",
    "warning": "#f59e0b",
    "danger": "#ef4444"
  },
  "dark": {
    "bg": "#0f172a",
    "surface": "#1e293b",
    "text": "#f1f5f9",
    "text-muted": "#94a3b8",
    "border": "#334155"
  }
}
```

### Generated Palette CSS

Each `palettes/<name>.css` contains only variable overrides:

```css
/* Generated from tokens/slate.json — do not edit */
:root {
  --novo-primary: #475569;
  --novo-primary-hover: #334155;
  --novo-bg: #f8fafc;
  /* ... */
}
```

---

## Built-in Palettes

| Name | Primary | Character |
|------|---------|-----------|
| `default` | `#3b82f6` blue-500 | Clean, neutral, professional |
| `slate` | `#475569` slate-600 | Subdued, corporate |
| `blue` | `#1d4ed8` blue-700 | Deeper blue, high contrast |
| `green` | `#059669` emerald-600 | Fresh, eco/finance feel |
| `warm` | `#d97706` amber-600 | Warm, creative/agency feel |

---

## Palette Loading Mechanism

In `theme_vars.inc.php` or `global.inc.php`, after the default `:root` block:

```php
// Load active palette override
$palette = getDolGlobalString('NOVOUX_PALETTE', 'default');
// Or override from env
if (!empty($_SERVER['NOVOUX_PALETTE'])) {
    $palette = $_SERVER['NOVOUX_PALETTE'];
}
if ($palette !== 'default') {
    $palettefile = __DIR__.'/palettes/'.$palette.'.css';
    if (file_exists($palettefile)) {
        include $palettefile;
    }
}
```

This means:
- No module needed — theme alone can switch palettes via `llx_const` or env var
- Module (M4) just provides the admin GUI for setting this value

---

## Per-Client Override

A client-specific CSS file overrides `--novo-*` vars with brand colors:

```css
/* client: acme-corp — loaded via novoux module or volume mount */
:root {
  --novo-primary: #e11d48;      /* Acme brand red */
  --novo-primary-hover: #be123c;
  --novo-accent: #0ea5e9;
}
```

This file lives OUTSIDE this repo — in the client's deploy config. Two loading paths:

1. **Module path:** novoux injects it via `module_parts['css']` pointing to a configurable path
2. **Docker path:** volume-mounted or baked into image at `/var/www/html/theme/novo/client-override.css`

---

## Build Script

`scripts/build-palettes.js` (~50 LOC Node script):

- Reads all `tokens/*.json`
- Generates `dolibarr/theme/novo/palettes/<name>.css` for each
- Also generates `preview/src/palettes.js` (for the preview site in M5)

Run: `node scripts/build-palettes.js`

### CI Check

In `.github/workflows/ci.yml`:

```yaml
- run: node scripts/build-palettes.js
- run: git diff --exit-code dolibarr/theme/novo/palettes/
```

Fails if generated files are stale — ensures tokens and CSS stay in sync.

---

## Deliverables

| File | Action |
|------|--------|
| `tokens/*.json` (×5) | Create |
| `scripts/build-palettes.js` | Create |
| `dolibarr/theme/novo/palettes/*.css` (×5) | Generated |
| `dolibarr/theme/novo/global.inc.php` | Add palette loading logic |
| `.github/workflows/ci.yml` | Create (palette freshness check) |
| `package.json` | Create (minimal — just defines `build:palettes` script) |

---

## Not In Scope

- Admin UI for palette selection (M4)
- Login page customization
- Custom logo upload handling
- Preview site consuming tokens (M5)
