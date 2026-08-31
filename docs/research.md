# Dolibarr Internals Reference

Research notes on Dolibarr's theme and module systems. Useful context for contributors unfamiliar with how Dolibarr works under the hood.

## Dolibarr Theme System

### How themes work

- Themes live at `htdocs/theme/<name>/`
- Dolibarr auto-discovers themes: any directory under `htdocs/theme/` with a `style.css.php` appears in Setup > Display
- No module descriptor required for a theme alone (only for the companion module)
- Test without activating globally: append `?theme=novo` to any URL

### Required files

| File | Role |
|------|------|
| `style.css.php` | Entry point. Outputs `Content-Type: text/css`. Loads vars, bootstraps Dolibarr env, includes sub-files. |
| `theme_vars.inc.php` | PHP variables: colors (RGB strings), font sizes, badge/status colors. Consumed by `global.inc.php`. |
| `global.inc.php` | Main CSS output. Defines `:root` CSS custom properties, then all component styles. |
| `badges.inc.php` | Badge/pill component styles |
| `btn.inc.php` | Button styles |
| `dropdown.inc.php` | Dropdown menu styles |
| `info-box.inc.php` | Info box / dashboard widget card styles |
| `progress.inc.php` | Progress bar styles |
| `timeline.inc.php` | Timeline component styles |
| `emaillayout.inc.php` | Email template styling |
| `flags-sprite.inc.php` | Country flag sprite sheet |
| `main_menu_fa_icons.inc.php` | FontAwesome icon mappings for menu items |
| `input-feedback.css` | Input validation feedback (pure CSS, no PHP) |
| `search-input.inc.css` | Search input styling (pure CSS) |
| `tooltips.inc.css` | Tooltip styling (pure CSS) |
| `thumb.png` | Theme thumbnail shown in Setup > Display |
| `AUTHOR` | Attribution file |

### Load chain

```
Browser requests: /theme/novo/style.css.php
  → defines ISLOADEDBYSTEELSHEET, NOLOGIN, etc.
  → require theme_vars.inc.php (PHP color vars)
  → require ../../main.inc.php (Dolibarr bootstrap)
  → require functions2.lib.php
  → sets Content-Type: text/css, Cache-Control headers
  → resolves $fontlist, RTL direction, image paths
  → include global.inc.php (outputs all CSS)
    → includes badges.inc.php, btn.inc.php, etc. via dol_buildpath()
```

### Key PHP variables available in CSS output

- `$conf->theme` — active theme name
- `$conf->global->THEME_DARKMODEENABLED` — dark mode setting (1=auto, 2=forced)
- `$conf->global->THEME_FONT_FAMILY` — font override
- `$langs->trans("DIRECTION")` — 'rtl' or 'ltr'
- `$user->conf->THEME_ELDY_TOPMENU_BACK1` — per-user color overrides (SkinEditor pattern)
- `$dol_hide_leftmenu`, `$dol_optimize_smallscreen` — responsive flags

### Dark mode pattern (Eldy v21)

```php
if (!empty($conf->global->THEME_DARKMODEENABLED)) {
    if ($conf->global->THEME_DARKMODEENABLED == 1) {
        // Wraps in @media (prefers-color-scheme: dark)
    }
    if ($conf->global->THEME_DARKMODEENABLED == 2) {
        // Wraps in @media not print (forced dark)
    }
}
```

Dark mode overrides ALL `:root` CSS custom properties with dark values.

---

## Dolibarr Module System (for novoux)

### Key docs

- Module development: https://wiki.dolibarr.org/index.php/Module_development
- Module template: https://github.com/Dolibarr/dolibarr/tree/develop/htdocs/modulebuilder/template
- Hooks system: https://wiki.dolibarr.org/index.php/Hooks_system
- Skins: https://wiki.dolibarr.org/index.php/Skins
- SkinEditor module: https://wiki.dolibarr.org/index.php/Module_SkinEditor
- Widget/box system: https://wiki.dolibarr.org/index.php/Widget_system

### Module file structure

```
htdocs/custom/novoux/
  core/modules/modNovoux.class.php    ← module descriptor (required)
  admin/setup.php                     ← config page
  css/novo-client.css.php             ← injected CSS
  langs/en_US/novoux.lang             ← translations
  lib/novoux.lib.php                  ← helper functions
```

### CSS injection via module

Declare in module descriptor:
```php
$this->module_parts = array(
    'css' => array('/novoux/css/novo-client.css.php'),
);
```

This adds the CSS file to every page's `<head>` when the module is enabled.

### Config storage

Module writes to `llx_const` via:
```php
dolibarr_set_const($db, 'NOVOUX_PALETTE', 'default', 'chaine', 0, '', $conf->entity);
```

Read back via:
```php
$palette = getDolGlobalString('NOVOUX_PALETTE', 'default');
```

### Module descriptor key fields

```php
$this->numero = XXXXXX;                    // unique module ID (see "Module ID ranges" below)
$this->rights_class = 'novoux';
$this->family = 'interface';               // or 'other'
$this->module_position = '90';
$this->name = preg_replace('/^mod/i', '', get_class($this));
$this->description = "Modern theme configuration module";
$this->version = '0.1.0';
$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
$this->config_page_url = array("setup.php@novoux");
```

---

## Module ID ranges

The two wiki pages disagree, so this is the resolved answer — do not re-derive it.

| Range | Purpose |
|-------|---------|
| 0 – 94999 | Dolibarr core modules in the standard distribution |
| 95000 – 99999 | Community modules **with sources hosted in Dolibarr's own GitHub org** |
| 100000 – 499999 | "Reserved area for editors in need for an ID range" |
| > 500000 | No reservation needed — explicitly **not intended for distribution** |

[Modules - Packaging rules and Dolistore validation rules](https://wiki.dolibarr.org/index.php/Modules_-_Packaging_rules_and_Dolistore_validation_rules)
says to pick from 95000–99999. That is misleading for us. On
[List of modules id](https://wiki.dolibarr.org/index.php?title=List_of_modules_id)
the 95000–99999 block has no entries at all, while 100000–499999 holds roughly
200 allocations — including individual GitHub developers, not just companies
(for example `491300-491349: luigifab`). The distinguishing factor is where the
source lives: ours is in a FlowMatrix repository, not Dolibarr's.

**FlowMatrix reserves in 100000–499999.**

Two further rules from that page:

- *"DO NOT ADD RECORD HERE, PLEASE FILL HOLES FIRST!!!!"* — claim a gap between
  existing allocations rather than appending after the highest entry.
- Reserve a small block, not a single ID. The page gives conflicting advice in
  two places ("take a range of 20 numbers only the first time" versus "take
  ranges of 10 only please at once", warning that reservations over 10 may be
  reassigned without notice). **Take 10.**

Verify the gap is still free at the moment of editing — it is a wiki.

---

## Reference Theme Comparison

| Aspect | Eldy (v21 develop) | MD-UX | Kontava |
|--------|-------------------|-------|---------|
| Based on | Original | Eldy fork | Eldy fork |
| Font | `arial, tahoma, verdana, helvetica` | `roboto, arial, ...` | Same as Eldy |
| Top menu color | `rgb(38,60,92)` navy | Same | Green `#89BE2B` |
| Button color | Purple `rgb(116,96,170)` | Same | Green `#006633` |
| Dark mode | Yes (configurable) | Yes (prefers-color-scheme) | No |
| Responsive | Basic | Good (767px breakpoint, fixed sidebar) | Basic |
| Border radius | 3-5px | 2-4px | Same as Eldy |
| Module included | No | No | Yes (full module) |
| Target versions | Current develop | v14-v20 | v16+ |
| License | GPL-3.0 | GPL-3.0 | GPL-3.0 |

---

## Useful Dolibarr CSS Classes

Classes commonly used in Dolibarr pages (must be styled by any theme):

- `.liste_titre` — table header rows
- `.pair` / `.impair` — alternating table rows
- `.flat` — input fields (input, select, textarea)
- `.button` — submit buttons
- `.butAction` / `.butActionDelete` — action buttons
- `.tabBar` — tab content area
- `.fiche` — main content area
- `#id-left` — left sidebar container
- `#id-right` — main content container
- `.info-box` — dashboard widget boxes
- `.badge` — status badges
- `.dropdown-menu` — dropdown menus

For Docker setup, see [docker.md](docker.md).
