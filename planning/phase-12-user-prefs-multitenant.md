# Phase 12 — Per-User Preferences & Multi-Tenant Polish

**Goal:** Allow individual users to override the admin-set theme configuration for their own session. Support multi-tenant deployments where each entity has a fully independent visual identity.

**Exit criteria:** Users have a "My Display" preferences page (or widget). Per-user settings persist across sessions. Multi-entity deployments show distinct branding per entity. Zero cross-entity leakage.

---

## Why This Phase Matters

An ERP has two audiences with conflicting needs:
- **Admins** want consistency and brand control
- **Power users** want personal comfort (their preferred density, dark mode, maybe a different accent color)

Additionally, Dolibarr's multi-entity feature means a single installation may serve multiple companies — each needing distinct branding without separate deployments.

---

## Deliverables

### 1. User Preference Storage

Store per-user overrides in `llx_user_param` (Dolibarr's standard user pref table):

| Key | Values |
|-----|--------|
| `NOVOUX_USER_DARK_MODE` | `auto`, `light`, `dark` |
| `NOVOUX_USER_DENSITY` | `compact`, `default`, `spacious` |
| `NOVOUX_USER_SIDEBAR` | `expanded`, `collapsed` |
| `NOVOUX_USER_FONT_SIZE` | `12`–`16` or empty (use system) |

**Not per-user:** palette, primary color, logo, style variant (these are brand decisions, admin-only).

### 2. User Preferences UI

Two options (implement both):

**A) Settings page** (`/custom/novoux/user/preferences.php`):
- Accessible from user menu dropdown
- Simple form: dark mode toggle, density selector, font size slider
- Save button writes to `llx_user_param`
- "Use system defaults" option to clear overrides

**B) Quick-access widget** (via Phase 8 JS):
- Small gear icon in the top bar
- Dropdown with dark mode / density switches
- Persists to server via AJAX call (no page reload)
- Falls back to localStorage if JS disabled

### 3. Precedence Update

Extended cascade:
```
1. Per-user DB preference (llx_user_param) — highest for allowed settings
2. Per-user localStorage (JS quick toggle, cosmetic)
3. NovouX entity constants (admin-set)
4. Environment variables
5. Theme defaults
```

`novo-inject.css.php` reads user prefs in addition to global constants:
```php
// Per-user density override (if the admin allows it)
if (getDolGlobalString('NOVOUX_ALLOW_USER_PREFS', '1')) {
    $userDensity = $user->conf->NOVOUX_USER_DENSITY ?? '';
    if (!empty($userDensity)) {
        $density = $userDensity;
    }
}
```

### 4. Admin Control Over User Prefs

NovouX settings page gets a "User permissions" section:
- ☑ Allow users to change dark mode
- ☑ Allow users to change density
- ☐ Allow users to change font size
- ☐ Allow users to change sidebar state

Admin can lock any axis to enforce brand consistency while allowing comfort adjustments.

### 5. Multi-Entity Branding

Formalize what's already partially supported:

- Each entity stores its own `NOVOUX_*` constants (via `$conf->entity`)
- Add to NovouX setup: explicit "Entity settings" section showing which entity you're configuring
- Support different logos, palettes, and even style variants per entity
- Login page shows the correct entity's branding (tricky — requires detecting entity before auth)

**Login page entity detection:**
- Option A: URL-based (`/entity1/`, `/entity2/` via rewrite rules)
- Option B: Subdomain-based (`entity1.erp.company.com`)
- Option C: Single login → entity selection → branding applied after auth
- Recommend Option C for simplicity (admin configures, users see it post-login)

### 6. "White Label" Mode

For operators reselling Dolibarr:
- Hide "Novo" branding from the theme selector (show custom name)
- NovouX can set a custom theme display name
- Footer credit text configurable
- All visible references to "Novo" or "FlowMatrix" removable

---

## Technical Implementation

### User Pref Reading in CSS

`novo-inject.css.php` currently runs on every page load (it's a `module_parts['css']` file). It already has access to `$user` (loaded by the time CSS is requested).

```php
// After global constants
if (getDolGlobalString('NOVOUX_ALLOW_USER_PREFS')) {
    // Dark mode
    $userDark = getUserPref($user, 'NOVOUX_USER_DARK_MODE');
    if ($userDark === 'dark') {
        // Emit dark mode overrides outside @media query (forced)
    }
    
    // Density
    $userDensity = getUserPref($user, 'NOVOUX_USER_DENSITY');
    if ($userDensity && $userDensity !== 'default') {
        $variantFile = DOL_DOCUMENT_ROOT.'/theme/novo/variants/density-'.$userDensity.'.css';
        if (file_exists($variantFile)) {
            readfile($variantFile);
        }
    }
}
```

### AJAX Endpoint for JS Quick-Toggle

`/custom/novoux/ajax/save_user_pref.php`:
- Accepts POST with key + value
- Validates against allowed keys/values
- Writes to `llx_user_param`
- Returns JSON success/error
- CSRF token required

---

## Dependencies

- Phase 7 (density variants exist)
- Phase 8 (JS framework for quick-toggle)
- Phase 9 (NovouX settings panel with tabs)

## Risk

- Per-user CSS generation adds overhead (one DB query per page load)
- Mitigation: cache user prefs in session (`$_SESSION['novoux_user_prefs']`), invalidate on pref change
- Multi-entity branding at login is architecturally complex
- Mitigation: defer login-page branding to a later minor release, focus on post-auth entity branding first
