# Phase 8 — Theme JavaScript: Dark Toggle & Sticky Headers

**Goal:** Ship `novo.js` — a small vanilla-JS file that adds two high-value UX features impossible with CSS alone: instant dark mode toggling and sticky table headers.

**Exit criteria:** `novo.js` loads when `ALLOW_THEME_JS` is set. Dark mode toggles without page reload and persists across sessions. Table headers stick on scroll for large lists. Everything degrades gracefully when JS is disabled.

---

## Scope (In / Out)

**In this phase:**
- `dolibarr/theme/novo/novo.js` — vanilla ES2020, IIFE, < 5KB, no dependencies
- Dark mode toggle button (injects into top-right user menu area)
- CSS additions for `html[data-novo-scheme="dark"|"light"]` selectors
- Sticky table headers for `.liste` tables
- NovouX admin checkbox to enable `ALLOW_THEME_JS`

**Deferred to Phase 8b:**
- Sidebar collapse/expand (complex layout mutation)
- Density quick-switch (admin setting suffices for now)
- Keyboard shortcuts

---

## How Dolibarr Loads Theme JS

`htdocs/main.inc.php` line 2133:
```php
if (getDolGlobalString('ALLOW_THEME_JS')) {
    $theme_js = dol_buildpath('/theme/'.$conf->theme.'/'.$conf->theme.'.js', 0);
    // ... loads as <script> with nonce
}
```

Our file: `htdocs/theme/novo/novo.js`

---

## Deliverable 1: `novo.js` Structure

```js
(function() {
  'use strict';

  function novoInit() {
    initDarkToggle();
    initStickyHeaders();
  }

  // ... feature functions ...

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', novoInit);
  } else {
    novoInit();
  }
})();
```

Principles:
- No jQuery dependency (Dolibarr includes it, but we don't use it)
- No DOM structure mutation beyond adding classes/attributes and injecting the toggle button
- No network requests — all state in localStorage
- Respects `prefers-reduced-motion`
- CSP-safe: no inline handlers, no eval

---

## Deliverable 2: Dark Mode Toggle

**Behavior:**
1. On load: read `localStorage.getItem('novo-color-scheme')`
2. If `'dark'` → set `document.documentElement.dataset.novoScheme = 'dark'`
3. If `'light'` → set to `'light'`
4. If absent → remove attribute (OS preference via `prefers-color-scheme` takes over)
5. Inject toggle button into `.login_block` (top-right user area) or `#tmenu_tooltip` area
6. Click cycles: Auto → Dark → Light → Auto
7. Icon: FontAwesome `fa-circle-half-stroke` / `fa-moon` / `fa-sun` (already available in Dolibarr)

**CSS required** (added to `global.inc.php`):
```css
/* JS-driven dark mode — overrides @media prefers-color-scheme */
html[data-novo-scheme="dark"] {
  /* same variable values as the existing @media (prefers-color-scheme: dark) block */
}
html[data-novo-scheme="light"] {
  /* force light vars even when OS is dark */
}
```

This is a duplication of the existing dark-mode variables into attribute selectors so JS can force the mode regardless of OS setting.

**Fallback:** If JS disabled or `ALLOW_THEME_JS` off, dark mode still works via `@media (prefers-color-scheme: dark)` as today. No regression.

---

## Deliverable 3: Sticky Table Headers

**Behavior:**
1. Find all `table.liste` elements
2. For tables with more than 8 visible rows, add class `novo-sticky`
3. CSS handles the rest:

```css
table.novo-sticky thead tr {
  position: sticky;
  top: var(--novo-layout-header-height);
  z-index: 10;
  background: var(--colorbacktitle1);
}
table.novo-sticky thead tr + tr {
  /* Filter row — also sticky, stacked below header */
  position: sticky;
  top: calc(var(--novo-layout-header-height) + var(--novo-density-row-height));
  z-index: 9;
}
```

4. Add subtle bottom shadow when scrolled past (via IntersectionObserver or scroll listener, throttled)
5. Disable on `@media print`

**Edge cases:**
- Dolibarr tables have a filter row as a second `<tr>` in `<thead>` — both must be sticky
- Tables inside modals: skip (detect by parent `.ui-dialog`)
- Mobile: sticky is useful but `top` offset differs (no top menu) — use `0px` when no `#id-top` visible

---

## Deliverable 4: NovouX Enable Setting

Add to `novoux/admin/setup.php`:
- Checkbox: "Enable theme JavaScript (dark toggle, sticky headers)"
- On save: `dolibarr_set_const($db, 'ALLOW_THEME_JS', $value ? '1' : '', ...)`
- Warning text: "Requires page reload to take effect"

---

## Implementation Plan

1. Create `dolibarr/theme/novo/novo.js` with both features
2. Add CSS selectors for `html[data-novo-scheme]` and `table.novo-sticky` to `global.inc.php`
3. Add NovouX checkbox for `ALLOW_THEME_JS`
4. Test: enable via NovouX → reload → verify toggle works and headers stick
5. Test: disable → reload → verify zero console errors and no visual change

---

## Testing Matrix

| Scenario | Expected |
|----------|----------|
| `ALLOW_THEME_JS` off | No `<script>` tag emitted, no toggle visible, no errors |
| JS enabled, OS dark | Toggle shows "Auto", theme is dark |
| Click toggle → Dark | Immediate dark, localStorage set, persists on reload |
| Click toggle → Light | Immediate light even though OS is dark |
| Click toggle → Auto | Clears localStorage, reverts to OS preference |
| Table with 20 rows | Header sticks on scroll |
| Table with 5 rows | No sticky applied |
| Print mode | Sticky disabled, full table renders |
| `prefers-reduced-motion` | No transition animations on toggle |

---

## Dependencies

- Phase 6 ✅ (clean CSS foundation)
- Phase 7 ✅ (density tokens for header-height var)
- Dolibarr's existing dark mode CSS block in `global.inc.php`

## Risk

- Dark mode variable duplication (attribute selector vs media query) — must stay in sync
- Mitigation: both blocks reference the same PHP variables; consider extracting to a shared include
- Sticky headers may conflict with Dolibarr's own scroll handling on some pages
- Mitigation: only apply to `table.liste`, skip tables inside modals/popups
