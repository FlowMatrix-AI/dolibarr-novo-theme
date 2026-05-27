# Phase 9 — Advanced NovouX Configuration

**Goal:** Transform the NovouX companion module from a simple palette picker into a full theme configuration panel. Enable non-technical administrators to fine-tune every visual dimension of Novo without writing CSS.

**Exit criteria:** Admin panel exposes palette, density, radius style, font settings, dark mode behavior, and custom CSS. Per-entity isolation works. Settings export/import functions. A "reset to defaults" action exists.

---

## Why This Phase Matters

The current NovouX setup page has 3 controls: palette dropdown, hex color picker, logo URL. That's fine for v1.0 but undersells the theme's actual flexibility. Every `--novo-*` variable is runtime-configurable — we're just not exposing the knobs.

For operators managing multiple Dolibarr instances (multi-tenant, multi-client), a rich config panel eliminates the need to write CSS override files per deployment. It also becomes a selling point on Dolistore.

---

## Deliverables

### 1. Restructured Admin Page

Replace single flat form with **tabbed settings panel**:

| Tab | Controls |
|-----|----------|
| **Colors** | Palette selector, primary/accent/success/warning/danger color pickers, live preview swatch |
| **Typography** | Font family input, base font size slider (12–16px), heading weight selector |
| **Density** | Radio: Compact / Default / Spacious (loads from Phase 7 variants) |
| **Shape** | Border radius preset (Sharp / Default / Rounded / Pill), border style (none / subtle / strong) |
| **Dark Mode** | Radio: Auto (follows OS) / Force Light / Force Dark |
| **Layout** | Sidebar width slider (180–280px), header height (44–56px) |
| **Logo & Brand** | Logo upload (file, not just URL), favicon upload, login page background image |
| **Advanced** | Custom CSS textarea (sanitized), custom `<head>` injection (admin-only) |

### 2. Live Preview

- Add an `<iframe>` preview panel on the right side of the settings page
- Loads a static preview HTML (lightweight) that applies the current settings in real-time as the admin adjusts controls
- Uses `postMessage` to push variable changes into the iframe without page reload
- Optional: full-page preview link opens the actual Dolibarr home with a `?novoux_preview=1` parameter that temporarily applies uncommitted settings

### 3. Configuration Precedence

Formalize the cascade (from highest to lowest priority):

```
1. Per-user preference (localStorage via JS — Phase 8, cosmetic only)
2. NovouX database constants (admin-set, per-entity)
3. Environment variables (NOVOUX_PALETTE, NOVOUX_DENSITY, etc.)
4. Theme defaults (hardcoded in global.inc.php :root block)
```

`novo-inject.css.php` becomes the single point that reads all sources and emits the final override CSS.

### 4. Per-Entity Isolation

Already partially supported (constants use `$conf->entity`). Formalize:
- Each entity can have independent palette, density, logo, colors
- Global "master" settings apply as defaults, entity-level overrides take priority
- UI: if multicompany module active, show "Apply to: This entity / All entities" toggle

### 5. Export / Import

- **Export:** Download current settings as JSON file
- **Import:** Upload JSON, validate schema, apply
- Use case: configure one instance perfectly, replicate to 20 client instances
- JSON schema matches the token format (so exported settings are just a custom token file)

### 6. Reset to Defaults

- "Reset" button per section (colors, density, etc.)
- "Reset all" button with confirmation modal
- Deletes the relevant `NOVOUX_*` constants from `llx_const`

### 7. Settings Audit Log

- Record who changed what and when (simple log in `llx_events` or dedicated table)
- Displayed as a collapsible history at bottom of settings page
- Useful for multi-admin environments ("who changed the color to pink?")

---

## Updated `novo-inject.css.php`

Currently emits primary color + logo. After this phase:

```php
<?php
// Read all configuration constants
$palette = getDolGlobalString('NOVOUX_PALETTE', 'default');
$density = getDolGlobalString('NOVOUX_DENSITY', 'default');
$primary = getDolGlobalString('NOVOUX_PRIMARY_COLOR', '');
$accent = getDolGlobalString('NOVOUX_ACCENT_COLOR', '');
$radiusPreset = getDolGlobalString('NOVOUX_RADIUS', 'default');
$fontFamily = getDolGlobalString('NOVOUX_FONT_FAMILY', '');
$fontSize = getDolGlobalString('NOVOUX_FONT_SIZE', '');
$darkMode = getDolGlobalString('NOVOUX_DARK_MODE', 'auto');
$sidebarWidth = getDolGlobalString('NOVOUX_SIDEBAR_WIDTH', '');
$customCss = getDolGlobalString('NOVOUX_CUSTOM_CSS', '');

// Emit :root overrides
print ":root {\n";
if (!empty($primary)) print "  --novo-primary: {$primary};\n";
if (!empty($accent)) print "  --novo-accent: {$accent};\n";
// ... etc for each configurable token
print "}\n";

// Load density variant if non-default
// Load radius variant if non-default
// Emit custom CSS (sanitized)
```

---

## Database Constants (all per-entity)

| Constant | Type | Values |
|----------|------|--------|
| `NOVOUX_PALETTE` | string | `default`, `slate`, `blue`, `green`, `warm` |
| `NOVOUX_DENSITY` | string | `compact`, `default`, `spacious` |
| `NOVOUX_PRIMARY_COLOR` | string | `#hex` or empty |
| `NOVOUX_ACCENT_COLOR` | string | `#hex` or empty |
| `NOVOUX_SUCCESS_COLOR` | string | `#hex` or empty |
| `NOVOUX_WARNING_COLOR` | string | `#hex` or empty |
| `NOVOUX_DANGER_COLOR` | string | `#hex` or empty |
| `NOVOUX_RADIUS` | string | `sharp`, `default`, `rounded`, `pill` |
| `NOVOUX_FONT_FAMILY` | string | CSS font-family value or empty |
| `NOVOUX_FONT_SIZE` | string | `12px`–`16px` or empty |
| `NOVOUX_DARK_MODE` | string | `auto`, `light`, `dark` |
| `NOVOUX_SIDEBAR_WIDTH` | string | `180`–`280` (px) or empty |
| `NOVOUX_LOGO_URL` | string | URL or empty |
| `NOVOUX_CUSTOM_CSS` | text | Raw CSS (sanitized on save) |

---

## Security Considerations

- **Custom CSS textarea:** Strip `<script>`, `expression()`, `url(javascript:)`, `@import` pointing to external domains. Allow only CSS property declarations.
- **Custom head injection:** Only available to superadmin. Escaped on output.
- **Color inputs:** Validate against `^#[0-9a-fA-F]{6}$` pattern
- **Font family:** Whitelist common font stacks + allow freeform with sanitization
- **File uploads (logo/favicon):** Validate mimetype, size limit (< 500KB), store in Dolibarr's document directory

---

## Dependencies

- Phase 7 (density variants and expanded tokens exist for the admin to configure)
- Phase 8 (JS features for dark mode control to reference)
- Can be started independently for non-density settings (colors, logo, radius)

## Risk

- Feature creep — settings panel becomes overwhelming
- Mitigation: progressive disclosure (basic tab shown by default, advanced tabs collapsed)
- Over-configuration leads to ugly results
- Mitigation: presets remain prominent, free-form controls are clearly marked "Advanced"
