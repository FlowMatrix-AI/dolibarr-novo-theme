# Phase 9 — NovouX Polish & Extra Controls

**Goal:** Expand the NovouX admin panel with practical controls that expose already-configurable token axes, and add a `theme_descriptor.php` for metadata.

**Exit criteria:** Admin can set accent color, danger color, radius preset, dark mode behavior, and custom CSS — all reflected immediately in the UI. Theme descriptor file exists with correct version.

---

## Deliverables

### 1. Additional Color Pickers

Add to admin form (same pattern as existing primary color picker):

| Setting | Constant | Default | Affects |
|---------|----------|---------|---------|
| Accent color | `NOVOUX_ACCENT_COLOR` | `#a78bfa` | `--novo-accent` |
| Danger color | `NOVOUX_DANGER_COLOR` | `#ef4444` | `--novo-danger` |

Validation: same hex regex as primary (`/^#[0-9a-fA-F]{6}$/`).

Wire into `novo-inject.css.php` — emit `:root { --novo-accent: ...; --novo-danger: ...; }` when set.

### 2. Radius Preset Dropdown

| Preset | Values (sm / md / lg / xl) |
|--------|---------------------------|
| Sharp | 2px / 3px / 4px / 6px |
| Default | 4px / 6px / 8px / 12px |
| Rounded | 8px / 12px / 16px / 24px |
| Pill | 50px / 50px / 50px / 50px |

Stored as `NOVOUX_RADIUS` constant (value: `sharp`, `default`, `rounded`, `pill`).

`novo-inject.css.php` emits the four `--novo-radius-*` overrides based on selected preset.

### 3. Dark Mode Behavior Selector

Replace the raw `ALLOW_THEME_JS` checkbox with a combined "Dark Mode" dropdown:

| Option | Effect |
|--------|--------|
| Disabled | `THEME_DARKMODEENABLED=0`, `ALLOW_THEME_JS` unchanged |
| Auto (follow OS) | `THEME_DARKMODEENABLED=1`, no JS toggle needed |
| Toggle (user choice) | `ALLOW_THEME_JS=1`, shows toggle button in top-right |
| Force Dark | `THEME_DARKMODEENABLED=2` |

This replaces the current separate checkbox — more intuitive for admins.

### 4. Custom CSS Textarea

- Constant: `NOVOUX_CUSTOM_CSS`
- Textarea in admin form (monospace, 10 rows)
- Sanitized: strip `<script>`, `expression()`, `url(javascript:)`, `@import` with external URLs
- Emitted at the very end of `novo-inject.css.php` (highest priority in cascade)
- Max 4 KB limit (stored in `llx_const` value field)

### 5. Theme Descriptor

Create `dolibarr/theme/novo/theme_descriptor.php`:

```php
<?php
$theme_name = 'novo';
$theme_desc = 'Modern, configurable Dolibarr theme with design tokens, dark mode, and density variants.';
$theme_version = '1.1.0';
$theme_author = 'FlowMatrix-AI';
$theme_url = 'https://github.com/FlowMatrix-AI/dolibarr-ui-skin';
$theme_min_dolibarr = '21.0.0';
$theme_min_php = '7.4';
```

---

## Files Modified

| File | Change |
|------|--------|
| `novoux/admin/setup.php` | Add accent picker, danger picker, radius dropdown, dark mode selector, custom CSS textarea |
| `novoux/css/novo-inject.css.php` | Emit accent, danger, radius, custom CSS overrides |
| `novoux/langs/en_US/novoux.lang` | New lang keys |
| `theme/novo/theme_descriptor.php` | New file |

---

## Out of Scope

- Live iframe preview (too complex for the payoff)
- Export/import (Dolibarr already has constant management)
- Audit log (Dolibarr events system suffices)
- Per-user preferences page (localStorage toggle already covers dark mode)
- Tabbed admin layout (current flat form is clear enough)
