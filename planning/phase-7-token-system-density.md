# Phase 7 — Extended Token System & Density Variants

**Goal:** Expand the design token architecture beyond colors into spacing, typography, density, and layout dimensions. Ship density variants (compact, default, spacious) that users can switch via NovouX without touching code.

**Exit criteria:** Token JSON schema includes spacing/typography/density/layout. Build script generates variant CSS. NovouX admin page has a density selector. Switching density visibly changes table row height, padding, font size, and sidebar width across all pages.

---

## Why This Phase Matters

Colors are only one axis of customization. In an ERP used 8 hours a day, **information density** is the most impactful user preference:
- Accountants want compact tables (50+ rows visible without scrolling)
- Managers want spacious, scannable dashboards
- Mobile users need larger touch targets

Right now, every spatial value (padding, margin, row-height, font-size, sidebar-width) is hardcoded in PHP/CSS. Making these token-driven unlocks a multiplier on top of palette switching — N palettes × M densities = N×M distinct configurations from one theme.

---

## Deliverables

### 1. Expanded Token Schema

New token structure (backwards-compatible with existing color-only JSON):

```json
{
  "name": "default",
  "label": "Default Blue",
  "colors": { "...existing..." },
  "dark": { "...existing..." },
  "spacing": {
    "unit": "4px",
    "xs": "4px",
    "sm": "8px",
    "md": "12px",
    "lg": "16px",
    "xl": "24px",
    "2xl": "32px"
  },
  "typography": {
    "font-family": "system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
    "font-size-xs": "11px",
    "font-size-sm": "12px",
    "font-size-base": "13px",
    "font-size-lg": "15px",
    "font-size-xl": "18px",
    "font-size-2xl": "22px",
    "line-height-tight": "1.25",
    "line-height-base": "1.5",
    "line-height-relaxed": "1.75",
    "font-weight-normal": "400",
    "font-weight-medium": "500",
    "font-weight-semibold": "600",
    "font-weight-bold": "700"
  },
  "layout": {
    "sidebar-width": "240px",
    "sidebar-collapsed-width": "60px",
    "header-height": "50px",
    "content-max-width": "1400px",
    "content-padding": "24px"
  },
  "density": {
    "row-height": "38px",
    "input-height": "34px",
    "button-padding-y": "8px",
    "button-padding-x": "16px",
    "cell-padding-y": "8px",
    "cell-padding-x": "12px",
    "card-padding": "16px",
    "section-gap": "24px"
  },
  "radii": {
    "none": "0px",
    "sm": "4px",
    "md": "6px",
    "lg": "8px",
    "xl": "12px",
    "full": "9999px"
  },
  "shadows": {
    "sm": "0 1px 2px 0 rgba(0,0,0,0.05)",
    "md": "0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05)",
    "lg": "0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.04)"
  }
}
```

### 2. Density Variant Files

New directory: `tokens/variants/`

```
tokens/
  default.json          ← palette (colors + base spatial values)
  slate.json
  blue.json
  green.json
  warm.json
  variants/
    density-compact.json
    density-spacious.json
    radius-sharp.json
    radius-rounded.json
```

**`density-compact.json`:**
```json
{
  "name": "compact",
  "label": "Compact",
  "type": "density",
  "overrides": {
    "typography": {
      "font-size-base": "12px",
      "line-height-base": "1.35"
    },
    "density": {
      "row-height": "30px",
      "input-height": "28px",
      "button-padding-y": "4px",
      "button-padding-x": "10px",
      "cell-padding-y": "4px",
      "cell-padding-x": "8px",
      "card-padding": "12px",
      "section-gap": "16px"
    },
    "layout": {
      "sidebar-width": "200px",
      "header-height": "44px"
    }
  }
}
```

**`density-spacious.json`:**
```json
{
  "name": "spacious",
  "label": "Spacious",
  "type": "density",
  "overrides": {
    "typography": {
      "font-size-base": "14px",
      "line-height-base": "1.6"
    },
    "density": {
      "row-height": "48px",
      "input-height": "40px",
      "button-padding-y": "10px",
      "button-padding-x": "20px",
      "cell-padding-y": "12px",
      "cell-padding-x": "16px",
      "card-padding": "24px",
      "section-gap": "32px"
    },
    "layout": {
      "sidebar-width": "260px",
      "header-height": "56px"
    }
  }
}
```

### 3. Expanded Build Script

Update `scripts/build-palettes.js` to:
- Parse the new schema (spacing, typography, density, radii, shadows)
- Generate `--novo-spacing-*`, `--novo-font-*`, `--novo-density-*`, `--novo-layout-*` CSS variables
- Generate variant CSS files in `dolibarr/theme/novo/variants/`
- Variant CSS only contains the overridden values (minimal diff)
- Maintain backwards compatibility — existing palette-only JSONs still work

Output structure:
```
dolibarr/theme/novo/
  palettes/
    default.css
    slate.css
    ...
  variants/
    density-compact.css
    density-spacious.css
    radius-sharp.css
    radius-rounded.css
```

### 4. Theme Integration

Update `global.inc.php` to:
- Emit all new `--novo-*` variables in `:root` block
- Load active variant CSS after palette CSS
- Read variant from `NOVOUX_DENSITY` constant or `$_SERVER['NOVOUX_DENSITY']`

### 5. Apply Tokens to CSS Rules

Refactor hardcoded values throughout `global.inc.php` and component `.inc.php` files to reference new tokens:

| Before | After |
|--------|-------|
| `padding: 8px 12px;` | `padding: var(--novo-density-cell-padding-y) var(--novo-density-cell-padding-x);` |
| `height: 38px;` | `height: var(--novo-density-row-height);` |
| `font-size: 13px;` | `font-size: var(--novo-font-size-base);` |
| `width: 240px;` (sidebar) | `width: var(--novo-layout-sidebar-width);` |

### 6. NovouX Density Selector

Add to `novoux/admin/setup.php`:
- Density radio group: Compact / Default / Spacious
- Stores `NOVOUX_DENSITY` constant
- `novo-inject.css.php` loads the appropriate variant CSS

---

## Token Naming Convention

All new variables follow: `--novo-{category}-{property}`

```
--novo-spacing-xs
--novo-spacing-sm
--novo-font-size-base
--novo-font-weight-medium
--novo-density-row-height
--novo-density-input-height
--novo-layout-sidebar-width
--novo-layout-header-height
--novo-radius-sm
--novo-shadow-md
```

---

## Migration Strategy

1. Add new tokens with current hardcoded values as defaults → zero visual change
2. Verify all pages still render identically
3. Introduce variant files that override specific values
4. Test each variant across QA matrix
5. Wire up NovouX selector

This ensures the token expansion is non-breaking.

---

## Dependencies

- Phase 6 (Deep Restyle) should land first — provides clean CSS to tokenize
- `build-palettes.js` needs extension (not rewrite)
- NovouX module changes required

## Risk

- Over-tokenization can make CSS hard to read/debug
- Mitigation: only tokenize values that genuinely vary between density settings
- Browser CSS variable performance is negligible at this scale (~50 vars)
