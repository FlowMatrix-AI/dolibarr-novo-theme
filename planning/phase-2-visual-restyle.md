# Phase 2 — Visual Restyle

**Goal:** Replace Eldy's visual identity with novo's design language. The theme should look distinctly modern while maintaining full Dolibarr compatibility.

**Exit criteria:** Before/after screenshots clearly show a different, cohesive visual system. All QA matrix pages pass smoke test.

---

## Design Decisions (Locked)

| Token | Value |
|-------|-------|
| Font | `system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif` |
| Primary | `#3b82f6` (blue-500) |
| Primary hover | `#2563eb` (blue-600) |
| Background | `#f8fafc` (slate-50) |
| Surface | `#ffffff` |
| Text | `#0f172a` (slate-900) |
| Text muted | `#64748b` (slate-500) |
| Border | `#e2e8f0` (slate-200) |
| Accent | `#8b5cf6` (violet-500) |
| Success | `#10b981` |
| Warning | `#f59e0b` |
| Danger | `#ef4444` |
| Radius cards | `8px` |
| Radius buttons | `6px` |
| Radius badges | `4px` |
| Radius modals | `12px` |
| Base font size | `14px` |
| Row height | `38px` |
| Card padding | `16px` |
| Section spacing | `24px` |

---

## Component Restyle Order

Work through these sequentially. Each is a commit (or small set of commits):

### 1. Global Layout & Typography

- `:root` variables updated to novo values
- Body background, font-family, base font-size, line-height
- Link colors and hover states
- Heading hierarchy (h1-h6 sizing/weight)
- Page container spacing

### 2. Top Menu Bar

- Background color → novo primary or dark surface
- Menu item styling (hover, active states)
- User menu / quick-action area
- Logo area sizing

### 3. Left Sidebar

- Background, text colors
- Menu item padding, hover/active indicators
- Active item highlight style
- Section dividers
- Submenu indentation

### 4. Tables & Lists

- `.liste_titre` header row styling
- `.pair` / `.impair` alternating rows
- Cell padding and vertical alignment
- Sort indicators
- Pagination controls
- Border treatment (subtle dividers vs full grid)

### 5. Forms & Inputs

- `.flat` input/select/textarea styling
- Focus states (ring/outline)
- Label spacing and weight
- Form layout (label + field alignment)
- Date picker styling
- Required field indicators

### 6. Buttons

- `.button`, `.butAction`, `.butActionDelete` styling
- Primary / secondary / danger variants
- Hover/active/disabled states
- Icon + text button alignment
- Button groups

### 7. Cards & Info Boxes

- `.info-box` dashboard widgets
- Card shadow, border, radius
- Header / body / footer sections
- Icon container styling

### 8. Badges & Status

- Badge radius, padding, font-size
- Status color mapping (draft, validated, closed, etc.)
- Pill vs rounded-rect variants

### 9. Dropdowns & Modals

- `.dropdown-menu` styling
- Menu item hover
- Dividers
- Modal overlay, container radius, shadow
- Modal header/footer

### 10. Misc Components

- Timeline
- Progress bars
- Tooltips
- Tabs (`.tabBar`, tab navigation)
- Breadcrumbs
- Alert/notification boxes (`setEventMessages` output)
- Login page

---

## Dark Mode

Implement using Eldy's existing PHP pattern:

```php
if (!empty($conf->global->THEME_DARKMODEENABLED)) {
    if ($conf->global->THEME_DARKMODEENABLED == 1) {
        // @media (prefers-color-scheme: dark) { ... }
    }
    if ($conf->global->THEME_DARKMODEENABLED == 2) {
        // @media not print { ... } (forced dark)
    }
}
```

Dark palette overrides all `--novo-*` variables with dark equivalents:

```
--novo-bg:        #0f172a (slate-900)
--novo-surface:   #1e293b (slate-800)
--novo-text:      #f1f5f9 (slate-100)
--novo-border:    #334155 (slate-700)
/* ... etc */
```

---

## QA Smoke Test

Each of these pages must be visually checked (light + dark):

- [ ] Login
- [ ] Dashboard (home)
- [ ] Third-party list
- [ ] Third-party card
- [ ] Invoice list
- [ ] Invoice card
- [ ] Product/service list
- [ ] User/admin settings
- [ ] Setup > Modules
- [ ] Any page with tabs

---

## Files Modified

| File | Changes |
|------|---------|
| `theme_vars.inc.php` | All PHP color/size vars updated to novo values |
| `global.inc.php` | `:root` vars, all component CSS restyled |
| `badges.inc.php` | Badge colors and shapes |
| `btn.inc.php` | Button variants |
| `dropdown.inc.php` | Dropdown appearance |
| `info-box.inc.php` | Card/widget styling |
| `progress.inc.php` | Progress bars |
| `timeline.inc.php` | Timeline component |
| `input-feedback.css` | Validation states |
| `thumb.png` | Updated theme thumbnail |

---

## Not In Scope

- Multiple palettes (M3 — only default palette here)
- Palette selection UI (M4)
- Structural layout changes (collapsible sidebar, etc.)
- Custom dashboard widgets
- JS behavior changes
