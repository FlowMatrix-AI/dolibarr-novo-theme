# Phase 6 — Deep Restyle

**Goal:** Systematically restyle every major UI surface that Phase 2 only color-swapped. Move beyond token injection into the `:root` block and actually rewrite the CSS rules that shape tables, tabs, menus, forms, login, and record cards. The result should feel like a different product, not Eldy with different colors.

**Exit criteria:** Side-by-side with vanilla Eldy, every listed component is visibly redesigned. All pages in the QA matrix still function correctly. No regressions in dark mode or palette switching.

---

## Why This Phase Matters

Phase 2 was a "skin" — it swapped colors and typography via variables but left structural CSS (borders, spacing, layout, hover behavior, shadows) intact. Roughly 9000 of Eldy's ~9700 CSS lines are unchanged in Novo. That means:
- Tables still look like 2012 — thick top borders, no row radius, harsh alternating colors
- Tabs still use a background-color swap for active state instead of a modern underline indicator
- The left menu is visually flat with no active-state affordance
- Forms use inconsistent focus styles
- The login page is the same centered box from Dolibarr 5.0

This phase turns Novo from "Eldy with nicer colors" into a genuinely modern ERP interface.

---

## Deliverables

### 1. Tables & Lists (`global.inc.php` — Tables section)

**Current state:** Standard striped rows, thick colored top border on header, no radius.

**Target:**
- Remove legacy `border-top: 2px solid` on `.liste_titre` header → replace with subtle `background: var(--novo-surface)` + `font-weight: 600` + `border-bottom: 1px solid var(--novo-border)`
- Row hover: smooth `background-color` transition using `var(--novo-transition)`
- Alternating rows: reduce contrast — `var(--novo-bg)` / `var(--novo-surface)` (nearly imperceptible in light, meaningful in dark)
- Table wrapper: `border-radius: var(--novo-radius-lg)` on first/last cells of first/last rows via `:first-child` / `:last-child`
- Checked row state: subtle left border accent `3px solid var(--novo-primary)`
- Pagination: pill-style page numbers, active state uses primary
- Sort indicators: subtle opacity animation

**Files:** `global.inc.php` lines ~4418–6093

**Tokens used:** `--novo-surface`, `--novo-bg`, `--novo-border`, `--novo-radius-lg`, `--novo-transition`, `--novo-primary`

---

### 2. Tabs (`global.inc.php` — Tabs section)

**Current state:** Background-color based active state, square corners, classic card-tab look.

**Target:**
- Replace background-highlight model with underline indicator: `border-bottom: 2px solid var(--novo-primary)` on active tab
- Inactive tabs: transparent background, subtle hover (`background: var(--novo-bg)`)
- Tab row: single bottom border separating tabs from content
- Remove `border-radius` on individual tabs (flat strip), or use pill-style with `var(--novo-radius-md)`
- Smooth transition on hover/active state
- Tab text: `font-weight: 500` active, `400` inactive, color shift to primary on active

**Files:** `global.inc.php` lines ~4214–4305

---

### 3. Left Sidebar / vmenu

**Current state:** Flat list, color-only distinction for active item, no visual hierarchy.

**Target:**
- Active item: left accent bar `3px solid var(--novo-primary)` + subtle background tint
- Hover: `background: var(--novo-bg)` with transition
- Section headers (menu groups): `font-size: 11px`, `text-transform: uppercase`, `letter-spacing: 0.05em`, `color: var(--novo-text-muted)`
- Item padding increase: `10px 16px` for better touch targets
- Nested items: increased left padding, no bullet/icon change
- Scrollbar styling: thin, semi-transparent, rounded

**Files:** `global.inc.php` lines ~3931–4154

---

### 4. Top Menu / hmenu

**Current state:** Solid background color, basic text links with hover.

**Target:**
- Background: solid `var(--novo-surface)` with subtle `box-shadow: var(--novo-shadow-sm)` as separator
- Menu items: remove text-transform, use `font-weight: 500`, `padding: 8px 14px`
- Active item: underline indicator (consistent with tabs) or background pill
- User menu area: avatar circle (if available), dropdown with shadow
- Mobile: hamburger trigger, slide-in overlay

**Files:** `global.inc.php` lines ~3201–3931

---

### 5. Forms & Inputs

**Current state:** Basic `.flat` class inputs with inconsistent sizing and focus behavior.

**Target:**
- All text inputs, selects, textareas: consistent `height: 34px` (inputs), `padding: 6px 10px`
- Border: `1px solid var(--novo-border)`
- Focus: `outline: none; border-color: var(--novo-primary); box-shadow: 0 0 0 3px rgba(59,130,246,0.15)`
- Labels: `font-weight: 500`, `color: var(--novo-text)`, `margin-bottom: 4px`
- Required field indicator: replace asterisk hack with subtle left-border accent or dot
- Disabled state: `background: var(--novo-bg)`, `color: var(--novo-text-muted)`, `cursor: not-allowed`
- Select dropdowns: custom arrow icon, consistent padding
- Textarea: min-height, resize handle styling

**Files:** `global.inc.php` lines ~384–694, scattered form rules

---

### 6. Login Page

**Current state:** Generic centered box, no theme personality.

**Target:**
- Full-height centered layout with `background: var(--novo-bg)`
- Login card: `background: var(--novo-surface)`, `border-radius: var(--novo-radius-xl)`, `box-shadow: var(--novo-shadow-lg)`
- Logo area: centered above form, palette-aware
- Inputs: larger (40px height), pill-radius or standard radius
- Submit button: full-width, primary colored, prominent
- Footer links (forgot password, etc.): muted color
- Subtle background pattern or gradient (optional, CSS-only)

**Files:** `global.inc.php` lines ~3579–3689 (Login section)

---

### 7. Record Cards / Fiche Pages

**Current state:** Basic bordered containers, title bars with colored top border.

**Target:**
- Card container: `background: var(--novo-surface)`, `border-radius: var(--novo-radius-lg)`, `box-shadow: var(--novo-shadow-sm)`
- Card header (fiche title): clean typography, no colored top-border → subtle bottom separator
- "Info" boxes within cards: consistent padding, icon alignment
- Action buttons area (`.tabsAction`): visual separation from content, centered on mobile
- Linked object cards: subtle nesting visual (inset shadow or lighter bg)

**Files:** Various sections in `global.inc.php`

---

### 8. Badges & Status Indicators

**Current state:** Already restyled in `badges.inc.php` but could be more cohesive.

**Target:**
- Consistent height/padding across all badge types
- Status dots (small circle) variant for inline use
- Ensure contrast ratios meet WCAG AA in both light and dark mode
- Animate status transitions (e.g., draft → validated)

**Files:** `badges.inc.php`

---

### 9. Modals & Dialogs

**Current state:** jQuery UI dialogs with default styling.

**Target:**
- Backdrop: `rgba(0,0,0,0.5)` with `backdrop-filter: blur(2px)`
- Modal card: `border-radius: var(--novo-radius-xl)`, `box-shadow: var(--novo-shadow-lg)`
- Header: title + close button, bottom border separator
- Footer: right-aligned buttons with proper spacing
- Animation: fade + slight scale on open

**Files:** `global.inc.php` dialog/modal sections

---

## Approach

1. Work component-by-component (each is a separate commit)
2. Test each in both light + dark mode, all 5 palettes
3. Check responsive behavior at 1440px, 1024px, 768px, 375px
4. Run full QA matrix page-by-page after completing all 9 components

## Dependencies

- Phase 5 complete (current state) ✓
- No new tokens required (uses existing `--novo-*` variables)
- No NovouX changes needed
- No JS required

## Risk

- Upstream Eldy changes could conflict on next Dolibarr version bump
- Mitigation: keep changes in clearly-commented blocks, document line ranges
