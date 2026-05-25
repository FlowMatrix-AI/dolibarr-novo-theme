# Phase 4 — Companion Module (novoux)

**Goal:** An external Dolibarr module that provides an admin GUI for theme configuration — palette selection, brand color override, client logo. Non-technical admins can customize the theme from Dolibarr's interface.

**Exit criteria:** Module installs, enables, and provides a working setup page. Changing palette from the admin page immediately affects all users.

---

## Module Identity

| Field | Value |
|-------|-------|
| Internal name | `novoux` |
| Class name | `modNovoux` |
| Module ID | TBD (reserve on wiki before release) |
| Family | `interface` |
| Position | `90` |
| Min Dolibarr | `21.0.0` |
| Config page | `setup.php@novoux` |
| Rights class | `novoux` |

---

## File Structure

```
dolibarr/custom/novoux/
  core/
    modules/
      modNovoux.class.php       ← module descriptor
  admin/
    setup.php                   ← configuration page
  css/
    novo-inject.css.php         ← injected CSS (client overrides, logo)
  lib/
    novoux.lib.php              ← admin page tab helper
  langs/
    en_US/
      novoux.lang               ← translations
  img/
    object_novoux.png           ← module icon (32×32)
  sql/
    data.sql                    ← default constants on install
```

---

## Module Descriptor

Key declarations in `modNovoux.class.php`:

```php
$this->module_parts = array(
    'css' => array('/novoux/css/novo-inject.css.php'),
);

$this->config_page_url = array("setup.php@novoux");

// Constants set on module activation
$this->const = array(
    array('NOVOUX_PALETTE', 'chaine', 'default', 'Active palette name', 1),
    array('NOVOUX_PRIMARY_COLOR', 'chaine', '', 'Override primary color', 1),
    array('NOVOUX_LOGO_URL', 'chaine', '', 'Client logo URL', 1),
);
```

No permissions, menus, triggers, tabs, or boxes needed for this module.

---

## Admin Setup Page

Page at `admin/setup.php`:

### Settings

| Setting | Type | Storage | Default |
|---------|------|---------|---------|
| Palette | Dropdown (5 options) | `NOVOUX_PALETTE` | `default` |
| Primary color override | Color input (hex) | `NOVOUX_PRIMARY_COLOR` | empty (use palette default) |
| Client logo URL | Text input | `NOVOUX_LOGO_URL` | empty (use Dolibarr default) |

### Behavior

- Standard Dolibarr admin page pattern (load constants, form, save on submit)
- CSRF token via `newToken()`
- Input validation: palette must be one of known names, color must match `#[0-9a-fA-F]{6}`, logo URL sanitized
- Uses `dolibarr_set_const()` / `dolibarr_get_const()`
- Success/error messages via `setEventMessages()`

### Admin Page Tabs

```php
// lib/novoux.lib.php
function novoux_admin_prepare_head() {
    $head = array();
    $head[0] = array(
        dol_buildpath('/novoux/admin/setup.php', 1),
        $langs->trans("Settings"),
        'settings'
    );
    return $head;
}
```

---

## CSS Injection

`css/novo-inject.css.php` outputs dynamic CSS based on stored config:

```php
<?php
// Outputs text/css
// Loaded on every page via module_parts['css']

if (!defined('ISLOADEDBYSTEELSHEET')) {
    // Direct access protection
    header('Content-Type: text/css');
}

$primaryOverride = getDolGlobalString('NOVOUX_PRIMARY_COLOR');

if (!empty($primaryOverride)) {
    echo ":root {\n";
    echo "  --novo-primary: ".$primaryOverride.";\n";
    // Could compute hover shade here
    echo "}\n";
}

$logoUrl = getDolGlobalString('NOVOUX_LOGO_URL');
if (!empty($logoUrl)) {
    // Override login logo or header logo via CSS
    echo "#img_logo { content: url('".dol_escape_htmltag($logoUrl)."'); }\n";
}
```

---

## Config Precedence

```
Environment variable (NOVOUX_PALETTE, NOVOUX_PRIMARY_COLOR)
  ↓ overrides
llx_const DB value (set via admin page)
  ↓ overrides
Theme defaults (hardcoded in palette CSS)
```

In the theme's palette loading code (from M3), env vars already take priority. The module's injected CSS adds a final layer for color/logo overrides that the theme itself doesn't handle.

---

## Translations

`langs/en_US/novoux.lang`:

```
NovouzSetup=Novo UX Settings
NovouzAbout=About Novo UX
NovouzPalette=Color Palette
NovouzPrimaryColor=Primary Color Override
NovouzLogoUrl=Client Logo URL
NovouzPaletteHelp=Select the color palette for the Novo theme
NovouzPrimaryColorHelp=Override the primary brand color (hex format). Leave empty to use palette default.
NovouzLogoUrlHelp=URL to the client logo image. Leave empty to use Dolibarr default.
```

---

## Deliverables

| File | Action |
|------|--------|
| `dolibarr/custom/novoux/core/modules/modNovoux.class.php` | Create |
| `dolibarr/custom/novoux/admin/setup.php` | Create |
| `dolibarr/custom/novoux/css/novo-inject.css.php` | Create |
| `dolibarr/custom/novoux/lib/novoux.lib.php` | Create |
| `dolibarr/custom/novoux/langs/en_US/novoux.lang` | Create |
| `dolibarr/custom/novoux/img/object_novoux.png` | Create |
| `dolibarr/custom/novoux/sql/data.sql` | Create |
| `docker-compose.dev.yml` | Update (mount custom/novoux/) |

---

## Not In Scope

- Dashboard widgets / custom homepage
- Login page template override
- Collapsible sidebar JS
- Dark-mode manual toggle (stored per-user)
- Density mode selection
- Font selection
