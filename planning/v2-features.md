# v2.0.0 Feature Plan

Two independent features that together constitute a major release.

**Branch:** `feature/v2` (or develop directly — decide at kickoff)
**Target:** When both G and H are merged and validated with visual tests

---

## Phase G — Per-User Preferences

**Goal:** Individual users choose their own palette and density, overriding admin defaults.

### Architecture

```
Precedence (highest wins):
  1. User param (llx_user_param: NOVOUX_USER_PALETTE / NOVOUX_USER_DENSITY)
  2. Admin const (llx_const: NOVOUX_PALETTE / NOVOUX_DENSITY)
  3. Hardcoded default ('default')
```

### Implementation Steps

| # | File | Change |
|---|------|--------|
| G1 | `css/novo-inject.css.php` | Before `getDolGlobalString('NOVOUX_PALETTE')`, check `$user->conf->NOVOUX_USER_PALETTE`. If non-empty, use it. Same for `NOVOUX_USER_DENSITY`. |
| G2 | `user_prefs.php` (new) | Standalone page: palette dropdown + density radio + "Reset to default" button. Saves via `dol_set_user_param($db, $conf, $user, array(...))`. |
| G3 | `lib/novoux.lib.php` | Add `novouXUserPrefsTab()` helper to register user-card tab. |
| G4 | `core/modules/modNovoux.class.php` | Add hook context `'user'` to `$this->module_parts['hooks']`. |
| G5 | `class/actions_novoux.class.php` (new) | Hook `addMoreActionsButtons` on user card — adds "Theme Preferences" tab linking to `user_prefs.php?id=$user->id`. |
| G6 | `admin/setup.php` | Add info note: "Users can override these defaults from their profile." |
| G7 | `test/phpunit/NovouXModuleTest.php` | Add tests: set user param → verify override; clear → verify fallback. |

### User-Facing Flow

1. User navigates to their profile → sees "Theme" tab (injected by hook)
2. Selects palette and/or density → saves
3. Next page load → their CSS output reflects personal choice
4. Admin's global setting remains unchanged for other users

### Constraints

- Permission check: users can only edit their own prefs (`$user->id == $id` or `$user->admin`)
- Palette value validated against files in `palettes/` directory (same as admin page)
- Density value validated against whitelist (`compact`, `default`, `spacious`)
- No new DB tables — uses existing `llx_user_param`

### Acceptance Criteria

- [ ] Two users on same instance use different palettes simultaneously
- [ ] Clearing user pref falls back to admin setting
- [ ] PHPUnit test proves precedence chain
- [ ] Visual test captures both states (requires second test user — seed SQL)

---

## Phase H — Sidebar Collapse

**Goal:** Left navigation collapses to a 48px icon rail with a persistent toggle.

### Architecture

```
State management:
  localStorage key: 'novo-sidebar-collapsed'
  Body class: 'novo-sidebar-collapsed'
  Admin gate: NOVOUX_SIDEBAR_COLLAPSE constant (checkbox in setup, default OFF)
```

### Implementation Steps

| # | File | Change |
|---|------|--------|
| H1 | `global.inc.php` | Add CSS block for `.novo-sidebar-collapsed` — sidebar width 48px, text hidden, icons centered. Transition: `width 0.2s ease`. |
| H2 | `global.inc.php` | `.novo-sidebar-collapsed #id-right` — adjust width calculation. |
| H3 | `global.inc.php` | `.novo-sidebar-collapsed .vmenu a .fa` — centered, larger. |
| H4 | `global.inc.php` | `.novo-sidebar-collapsed .side-nav:hover` — expand to full width temporarily (tooltip/flyout). |
| H5 | `novo.js` | Add `initSidebarCollapse()`: inject chevron toggle button at bottom of `#id-left`. |
| H6 | `novo.js` | On click: toggle body class + localStorage. Apply on page load in IIFE (before paint). |
| H7 | `admin/setup.php` | Add "Enable sidebar collapse" checkbox → `NOVOUX_SIDEBAR_COLLAPSE` constant. |
| H8 | `novo.js` | Gate: only run `initSidebarCollapse()` if `document.body.dataset.novoSidebarCollapse === '1'`. |
| H9 | `css/novo-inject.css.php` | If `NOVOUX_SIDEBAR_COLLAPSE` enabled, emit `body { --novo-sidebar-collapse-enabled: 1; }` and `<body data-novo-sidebar-collapse="1">` is set by the theme PHP. |

### Paint-Flash Prevention

The collapse state must be applied **before first paint**. Two options:

- **Option A (preferred):** In `global.inc.php`, emit an inline `<script>` that reads localStorage and adds the class to `<html>` immediately. This runs synchronously before body renders.
- **Option B:** In `novo.js` IIFE (top of file, runs before DOMContentLoaded), apply class. Slight flash risk if script is deferred.

Recommendation: Use Option A — a 3-line inline script in the theme's `<head>` output.

### CSS Details

```css
/* Collapsed state */
.novo-sidebar-collapsed .side-nav { width: 48px; overflow: hidden; }
.novo-sidebar-collapsed .vmenu .blockvmenu .menu_titre a span,
.novo-sidebar-collapsed .vmenu .blockvmenu .menu_contenu a span { display: none; }
.novo-sidebar-collapsed .vmenu .fa { font-size: 1.2em; margin: 0 auto; display: block; text-align: center; }
.novo-sidebar-collapsed #id-right { margin-left: 48px; width: calc(100% - 48px); }

/* Hover expand (flyout) */
.novo-sidebar-collapsed .side-nav:hover { width: 240px; position: absolute; z-index: 100; box-shadow: var(--novo-shadow-lg); }
.novo-sidebar-collapsed .side-nav:hover .vmenu .blockvmenu a span { display: inline; }

/* Transition */
.side-nav, #id-right { transition: width 0.2s ease, margin-left 0.2s ease; }
```

### Toggle Button

Injected via JS at bottom of `#id-left`:
```html
<button class="novo-sidebar-toggle" aria-label="Toggle sidebar" title="Toggle sidebar">
  <i class="fas fa-chevron-left"></i>
</button>
```
When collapsed, icon rotates to `fa-chevron-right`.

### Constraints

- Must not conflict with Dolibarr's existing responsive hide (hamburger menu at <768px)
- At `<768px`, sidebar collapse is irrelevant (already hidden) — disable toggle
- Accessible: `aria-expanded` attribute on button, focus-visible ring
- Print: always expand sidebar (or hide entirely — match Dolibarr default)

### Acceptance Criteria

- [ ] Sidebar collapses to 48px icon rail on toggle click
- [ ] Icons remain visible and clickable in collapsed state
- [ ] Hover over collapsed sidebar shows full menu as flyout
- [ ] State persists across page loads (localStorage)
- [ ] No layout shift on page load (class applied before paint)
- [ ] Works in both MAIN_MENU_INVERT=0 and MAIN_MENU_INVERT=1 modes
- [ ] Disabled by default (requires admin opt-in)
- [ ] Visual test snapshots: collapsed and expanded states
- [ ] Responsive: toggle hidden below 768px

---

## Sequencing

```
G (per-user prefs) and H (sidebar) are independent.
They can be developed in parallel on separate branches.

Suggested order if serial:
  G first — simpler, touches fewer files, validates hook pattern
  H second — more CSS/JS, benefits from G's test infrastructure
```

## Testing Strategy

| Type | Coverage |
|------|----------|
| PHPUnit | G: user param precedence, validation. H: constant gate check. |
| Playwright | G: screenshot with alternate palette. H: collapsed + expanded states, hover flyout. |
| Manual | G: two browser sessions, different users. H: responsive breakpoints, print. |

## Migration Notes

- No database migrations needed (both use existing `llx_const` + `llx_user_param`)
- No breaking changes to existing settings — purely additive
- Users upgrading from v1.x will see no difference until they opt in

---

## Release Checklist

- [ ] Both features merged to main
- [ ] All PHPUnit tests pass in CI
- [ ] Visual baselines updated with new states
- [ ] `docs/developing.md` updated with new features
- [ ] Version bumped to `2.0.0` in all 5 locations
- [ ] CHANGELOG.md updated
- [ ] Tag `v2.0.0` → release.yml generates zip
- [ ] README screenshots updated showing new capabilities
