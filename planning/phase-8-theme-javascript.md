# Phase 8 — Theme JavaScript & Interactive Enhancements

**Goal:** Add an optional JavaScript layer (`novo.js`) that provides UX improvements impossible with CSS alone. Keep it minimal, non-breaking, and gated behind `ALLOW_THEME_JS`.

**Exit criteria:** `novo.js` loads when enabled. Dark mode toggle works without page reload. Sticky table headers function. All features degrade gracefully when JS is disabled or the constant is unset.

---

## Why This Phase Matters

CSS handles appearance. But some high-value UX patterns require DOM awareness:
- **Dark mode toggle** — users want instant switching, not a settings page round-trip
- **Sticky table headers** — critical for long lists (ERP's primary view)
- **Sidebar collapse** — reclaim screen real estate on demand
- **Density quick-switch** — per-session comfort without admin access

Dolibarr already has the hook: setting `ALLOW_THEME_JS` causes `main.inc.php` to load `htdocs/theme/{name}/{name}.js`. We just need to provide the file.

---

## Deliverables

### 1. `dolibarr/theme/novo/novo.js`

Lightweight, vanilla JS (no framework, no jQuery dependency). IIFE pattern for isolation.

```
dolibarr/theme/novo/novo.js
├── Dark mode toggle
├── Sticky table headers
├── Sidebar collapse/expand
├── Density quick-switch (sessionStorage)
└── Keyboard shortcuts (optional)
```

Target: **< 8KB minified**. No build step required (plain ES2020, all Dolibarr-supported browsers handle it).

---

### 2. Dark Mode Toggle

**Behavior:**
- Inject a toggle button into the top-right user menu area
- Click cycles: Auto → Light → Dark → Auto
- Stores preference in `localStorage` key `novo-color-scheme`
- On load: check localStorage → if set, add `data-novo-scheme="dark"` or `"light"` to `<html>`
- CSS already handles dark via `prefers-color-scheme` — we add:
  ```css
  html[data-novo-scheme="dark"] { /* same dark vars */ }
  html[data-novo-scheme="light"] { /* force light even if OS is dark */ }
  ```
- Icon: moon/sun toggle (FontAwesome already available in Dolibarr)

**Why not a PHP/module approach?** Round-trip to server = page reload = poor UX for a cosmetic toggle.

---

### 3. Sticky Table Headers

**Behavior:**
- Detect `.liste` tables with > N rows (configurable, default 15)
- Apply `position: sticky; top: {header-height}; z-index: 10` to `thead tr`
- Handle the Dolibarr quirk where filter row is a separate `<tr>` in `<thead>` — make both sticky
- Add subtle bottom shadow on header when scrolled past
- Disable on print media

**Fallback:** If JS disabled, tables scroll normally (current behavior).

---

### 4. Sidebar Collapse/Expand

**Behavior:**
- Add a collapse chevron button at the bottom of the sidebar (or top)
- Collapsed state: sidebar shrinks to icon-width (`var(--novo-layout-sidebar-collapsed-width)`, ~60px)
- Menu labels hidden, only icons visible
- Stores state in `localStorage` key `novo-sidebar-collapsed`
- CSS class `novo-sidebar-collapsed` on `<body>` drives the transition
- Smooth width transition using `var(--novo-transition)`
- On mobile (< 768px): sidebar becomes overlay instead of collapsing

**CSS additions to `global.inc.php`:**
```css
body.novo-sidebar-collapsed #id-left { width: var(--novo-layout-sidebar-collapsed-width); }
body.novo-sidebar-collapsed #id-left .vmenu span.mainmenu-label { display: none; }
body.novo-sidebar-collapsed #id-right { margin-left: var(--novo-layout-sidebar-collapsed-width); }
```

---

### 5. Density Quick-Switch

**Behavior:**
- Small control in the top bar (or bottom-right corner) with 3 options: compact / default / spacious
- Swaps `<link>` element pointing to variant CSS, or toggles CSS class on `<body>`
- Stores in `sessionStorage` (resets on logout, doesn't persist across devices)
- This is a **user-level** convenience — the NovouX admin setting remains the system default

**Implementation note:** If Phase 7 lands density as CSS variables in a separate stylesheet, the toggle simply swaps which variant file is loaded.

---

### 6. NovouX Setting: Enable/Disable Theme JS

Add to `novoux/admin/setup.php`:
- Checkbox: "Enable theme JavaScript enhancements"
- Stores `ALLOW_THEME_JS` in Dolibarr constants (this is the native Dolibarr mechanism)
- Subsettings (shown when enabled):
  - ☑ Dark mode toggle
  - ☑ Sticky table headers
  - ☑ Sidebar collapse button
  - ☐ Keyboard shortcuts (Ctrl+B toggle sidebar, Ctrl+D toggle dark)

---

## Architecture

```
novo.js (entry point)
│
├── novoInit()          → runs on DOMContentLoaded
│   ├── initDarkToggle()
│   ├── initStickyHeaders()
│   ├── initSidebarCollapse()
│   └── initDensitySwitch()
│
└── Feature detection: each init checks for its DOM targets
    and silently skips if not found (no errors on pages without tables, etc.)
```

### Principles
- **No jQuery** — Dolibarr includes it, but we don't depend on it
- **No mutation of Dolibarr DOM structure** — only add classes, attributes, and inject minimal UI controls
- **No network requests** — all state in localStorage/sessionStorage
- **Respect `prefers-reduced-motion`** — skip transitions if set
- **CSP-safe** — no inline event handlers, no `eval`, nonce-compatible

---

## CSS Additions Required

Add to `global.inc.php` (or a new `novo-js.inc.php` loaded conditionally):

```css
/* Dark mode override via JS toggle */
html[data-novo-scheme="dark"] { /* duplicate dark vars from @media block */ }
html[data-novo-scheme="light"] { /* force all light vars */ }

/* Sidebar collapse */
body.novo-sidebar-collapsed #id-left { ... }
body.novo-sidebar-collapsed #id-right { ... }
transition: width var(--novo-transition), margin-left var(--novo-transition);

/* Sticky headers */
.novo-sticky-header thead tr { position: sticky; top: var(--novo-layout-header-height); z-index: 10; }
.novo-sticky-header thead tr.novo-scrolled { box-shadow: var(--novo-shadow-sm); }
```

---

## Dependencies

- Phase 6 (clean CSS foundation to enhance)
- Phase 7 (density tokens/variants for quick-switch to reference)
- Can start dark mode toggle and sticky headers independently of Phase 7

## Risk

- Conflicts with Dolibarr's own jQuery plugins or future JS changes
- Mitigation: namespace everything under `novo*`, don't touch Dolibarr's DOM IDs/classes beyond reading them
- Performance: all operations are O(1) DOM reads on page load, no intervals/observers unless specifically needed (sticky scroll listener is throttled)

## Testing

- Test with `ALLOW_THEME_JS` on and off — off must produce zero console errors
- Test all features with `prefers-reduced-motion: reduce`
- Verify no layout shift (CLS) from JS modifications
- Test on Firefox, Chrome, Safari (Dolibarr's supported browsers)
