# Phase 9 — NovouX Polish & Extra Controls

**Goal:** Expand the NovouX admin panel with practical controls that expose already-configurable token axes, and add a `theme_descriptor.php` for metadata.

**Exit criteria:** Admin can set accent color, danger color, radius preset, dark mode behavior, and custom CSS — all reflected immediately in the UI. Theme descriptor file exists with correct version.

---

## Deliverables

### 1. Additional Color Pickers

Add to admin form (same pattern as existing primary color picker):

| Setting | Constant | Default | Variable overridden |
|---------|----------|---------|---------------------|
| Accent color | `NOVOUX_ACCENT_COLOR` | (empty = use palette) | `--novo-accent` |
| Danger color | `NOVOUX_DANGER_COLOR` | (empty = use palette) | `--novo-danger` |

Validation: same hex regex as primary (`/^#[0-9a-fA-F]{6}$/`).

**In `setup.php` — Actions section**, after the primary color block:
```php
// Accent color override
$accentColor = GETPOST('NOVOUX_ACCENT_COLOR', 'alpha');
if (!empty($accentColor) && !preg_match('/^#[0-9a-fA-F]{6}$/', $accentColor)) {
    setEventMessages($langs->trans("ErrorBadColor"), null, 'errors');
    $error++;
}
if (!$error) {
    dolibarr_set_const($db, 'NOVOUX_ACCENT_COLOR', $accentColor, 'chaine', 0, '', $conf->entity);
}

// Danger color override
$dangerColor = GETPOST('NOVOUX_DANGER_COLOR', 'alpha');
if (!empty($dangerColor) && !preg_match('/^#[0-9a-fA-F]{6}$/', $dangerColor)) {
    setEventMessages($langs->trans("ErrorBadColor"), null, 'errors');
    $error++;
}
if (!$error) {
    dolibarr_set_const($db, 'NOVOUX_DANGER_COLOR', $dangerColor, 'chaine', 0, '', $conf->entity);
}
```

**In `setup.php` — View section**, after primary color row:
```php
// Accent color
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzAccentColor").'</td>';
print '<td>';
$currentAccent = getDolGlobalString('NOVOUX_ACCENT_COLOR', '');
print '<input type="color" name="NOVOUX_ACCENT_COLOR" value="'.dol_escape_htmltag($currentAccent ? $currentAccent : '#8b5cf6').'" class="flat">';
print ' <input type="text" name="NOVOUX_ACCENT_COLOR" value="'.dol_escape_htmltag($currentAccent).'" class="flat minwidth150" placeholder="#8b5cf6">';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzAccentColorHelp").'</span>';
print '</td>';
print '</tr>';

// Danger color
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzDangerColor").'</td>';
print '<td>';
$currentDanger = getDolGlobalString('NOVOUX_DANGER_COLOR', '');
print '<input type="color" name="NOVOUX_DANGER_COLOR" value="'.dol_escape_htmltag($currentDanger ? $currentDanger : '#ef4444').'" class="flat">';
print ' <input type="text" name="NOVOUX_DANGER_COLOR" value="'.dol_escape_htmltag($currentDanger).'" class="flat minwidth150" placeholder="#ef4444">';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzDangerColorHelp").'</span>';
print '</td>';
print '</tr>';
```

**In `novo-inject.css.php`**, after primary color block:
```php
// Accent color override
$accentOverride = getDolGlobalString('NOVOUX_ACCENT_COLOR', '');
if (!empty($accentOverride) && preg_match('/^#[0-9a-fA-F]{6}$/', $accentOverride)) {
    print ":root { --novo-accent: ".$accentOverride."; }\n";
}

// Danger color override
$dangerOverride = getDolGlobalString('NOVOUX_DANGER_COLOR', '');
if (!empty($dangerOverride) && preg_match('/^#[0-9a-fA-F]{6}$/', $dangerOverride)) {
    print ":root { --novo-danger: ".$dangerOverride."; }\n";
}
```

---

### 2. Radius Preset Dropdown

| Preset | `--novo-radius-sm` | `--novo-radius-md` | `--novo-radius-lg` | `--novo-radius-xl` |
|--------|--------------------|--------------------|--------------------|--------------------|
| `sharp` | 2px | 3px | 4px | 6px |
| `default` | 4px | 6px | 8px | 12px |
| `rounded` | 8px | 12px | 16px | 24px |
| `pill` | 50px | 50px | 50px | 50px |

Stored as `NOVOUX_RADIUS` constant.

**In `setup.php` — Actions section**, after density:
```php
// Radius preset
$radius = GETPOST('NOVOUX_RADIUS', 'alpha');
if (!in_array($radius, array('sharp', 'default', 'rounded', 'pill'))) {
    $radius = 'default';
}
dolibarr_set_const($db, 'NOVOUX_RADIUS', $radius, 'chaine', 0, '', $conf->entity);
```

**In `setup.php` — View section**, after density row:
```php
// Radius preset
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzRadius").'</td>';
print '<td>';
$currentRadius = getDolGlobalString('NOVOUX_RADIUS', 'default');
print '<select name="NOVOUX_RADIUS" class="flat minwidth200">';
$radiusOptions = array('sharp' => 'Sharp (2–6px)', 'default' => 'Default (4–12px)', 'rounded' => 'Rounded (8–24px)', 'pill' => 'Pill (50px)');
foreach ($radiusOptions as $rval => $rlabel) {
    $selected = ($rval == $currentRadius) ? ' selected' : '';
    print '<option value="'.dol_escape_htmltag($rval).'"'.$selected.'>'.$rlabel.'</option>';
}
print '</select>';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzRadiusHelp").'</span>';
print '</td>';
print '</tr>';
```

**In `novo-inject.css.php`**, after density variant load:
```php
// Radius preset
$radiusPreset = getDolGlobalString('NOVOUX_RADIUS', 'default');
$radiusMap = array(
    'sharp'   => array('2px', '3px', '4px', '6px'),
    'rounded' => array('8px', '12px', '16px', '24px'),
    'pill'    => array('50px', '50px', '50px', '50px'),
);
if (isset($radiusMap[$radiusPreset])) {
    $r = $radiusMap[$radiusPreset];
    print ":root {\n";
    print "  --novo-radius-sm: ".$r[0].";\n";
    print "  --novo-radius-md: ".$r[1].";\n";
    print "  --novo-radius-lg: ".$r[2].";\n";
    print "  --novo-radius-xl: ".$r[3].";\n";
    print "}\n";
}
// 'default' emits nothing (use theme defaults)
```

---

### 3. Dark Mode Behavior Selector

Replace the existing `ALLOW_THEME_JS` checkbox with a unified dropdown that controls both `THEME_DARKMODEENABLED` and `ALLOW_THEME_JS`:

| Option value | Label | Constants set |
|--------------|-------|---------------|
| `disabled` | Disabled (always light) | `THEME_DARKMODEENABLED=0`, `ALLOW_THEME_JS` unchanged |
| `auto` | Auto (follow OS preference) | `THEME_DARKMODEENABLED=1` |
| `toggle` | User toggle (button in top bar) | `THEME_DARKMODEENABLED=1`, `ALLOW_THEME_JS=1` |
| `forced` | Force dark | `THEME_DARKMODEENABLED=2` |

**In `setup.php` — Actions section**, replace the `ALLOW_THEME_JS` block:
```php
// Dark mode behavior
$darkMode = GETPOST('NOVOUX_DARK_MODE', 'alpha');
if (!in_array($darkMode, array('disabled', 'auto', 'toggle', 'forced'))) {
    $darkMode = 'disabled';
}
dolibarr_set_const($db, 'NOVOUX_DARK_MODE', $darkMode, 'chaine', 0, '', $conf->entity);
// Set the underlying Dolibarr constants
switch ($darkMode) {
    case 'auto':
        dolibarr_set_const($db, 'THEME_DARKMODEENABLED', '1', 'chaine', 0, '', $conf->entity);
        break;
    case 'toggle':
        dolibarr_set_const($db, 'THEME_DARKMODEENABLED', '1', 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($db, 'ALLOW_THEME_JS', '1', 'chaine', 0, '', $conf->entity);
        break;
    case 'forced':
        dolibarr_set_const($db, 'THEME_DARKMODEENABLED', '2', 'chaine', 0, '', $conf->entity);
        break;
    default: // disabled
        dolibarr_set_const($db, 'THEME_DARKMODEENABLED', '0', 'chaine', 0, '', $conf->entity);
        break;
}
// Ensure ALLOW_THEME_JS is off when not in toggle mode
if ($darkMode !== 'toggle') {
    dolibarr_set_const($db, 'ALLOW_THEME_JS', '0', 'chaine', 0, '', $conf->entity);
}
```

**In `setup.php` — View section**, replace the `ALLOW_THEME_JS` checkbox row:
```php
// Dark mode
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzDarkMode").'</td>';
print '<td>';
$currentDark = getDolGlobalString('NOVOUX_DARK_MODE', 'disabled');
$darkOptions = array(
    'disabled' => $langs->trans("NovouzDarkDisabled"),
    'auto' => $langs->trans("NovouzDarkAuto"),
    'toggle' => $langs->trans("NovouzDarkToggle"),
    'forced' => $langs->trans("NovouzDarkForced"),
);
print '<select name="NOVOUX_DARK_MODE" class="flat minwidth200">';
foreach ($darkOptions as $dval => $dlabel) {
    $selected = ($dval == $currentDark) ? ' selected' : '';
    print '<option value="'.dol_escape_htmltag($dval).'"'.$selected.'>'.dol_escape_htmltag($dlabel).'</option>';
}
print '</select>';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzDarkModeHelp").'</span>';
print '</td>';
print '</tr>';
```

---

### 4. Custom CSS Textarea

Stored as `NOVOUX_CUSTOM_CSS`. Sanitized to prevent XSS.

**Sanitization rules (applied before save):**
- Strip `<script` tags and `</script>` (case-insensitive)
- Strip `expression(` (IE legacy XSS vector)
- Strip `url(javascript:` 
- Strip `@import` lines with external URLs (allow `@import url('/local/...')`)
- Max 4096 bytes (truncate with warning)

**In `setup.php` — Actions section:**
```php
// Custom CSS
$customCss = GETPOST('NOVOUX_CUSTOM_CSS', 'restricthtml');
$customCss = preg_replace('/<\/?script[^>]*>/i', '', $customCss);
$customCss = preg_replace('/expression\s*\(/i', '', $customCss);
$customCss = preg_replace('/url\s*\(\s*["\']?javascript:/i', 'url(blocked:', $customCss);
if (strlen($customCss) > 4096) {
    $customCss = substr($customCss, 0, 4096);
    setEventMessages($langs->trans("NovouzCustomCssTruncated"), null, 'warnings');
}
dolibarr_set_const($db, 'NOVOUX_CUSTOM_CSS', $customCss, 'chaine', 0, '', $conf->entity);
```

**In `setup.php` — View section**, after dark mode row:
```php
// Custom CSS
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzCustomCss").'</td>';
print '<td>';
$currentCss = getDolGlobalString('NOVOUX_CUSTOM_CSS', '');
print '<textarea name="NOVOUX_CUSTOM_CSS" rows="8" class="flat" style="width:100%;max-width:600px;font-family:monospace;font-size:12px;">'.dol_escape_htmltag($currentCss).'</textarea>';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzCustomCssHelp").'</span>';
print '</td>';
print '</tr>';
```

**In `novo-inject.css.php`**, at the very end (highest cascade priority):
```php
// Custom CSS (admin-defined, sanitized on save)
$customCss = getDolGlobalString('NOVOUX_CUSTOM_CSS', '');
if (!empty($customCss)) {
    print "/* Custom CSS */\n";
    print $customCss."\n";
}
```

---

### 5. Theme Descriptor

Create `dolibarr/theme/novo/theme_descriptor.php`:

```php
<?php
/* Copyright (C) 2025 FlowMatrix-AI */
$theme_name = 'novo';
$theme_desc = 'Modern, configurable Dolibarr theme with design tokens, dark mode, and density variants.';
$theme_version = '1.1.0';
$theme_author = 'FlowMatrix-AI';
$theme_url = 'https://github.com/FlowMatrix-AI/dolibarr-ui-skin';
$theme_min_dolibarr = '21.0.0';
$theme_min_php = '7.4';
```

---

## Language Keys to Add (`novoux.lang`)

```
NovouzAccentColor=Accent Color Override
NovouzAccentColorHelp=Override the accent/highlight color (hex). Leave empty to use palette default.
NovouzDangerColor=Danger Color Override
NovouzDangerColorHelp=Override the danger/error color (hex). Leave empty to use palette default.
NovouzRadius=Border Radius Style
NovouzRadiusHelp=Controls the roundness of cards, buttons, and inputs across the theme.
NovouzDarkMode=Dark Mode
NovouzDarkModeHelp=Controls how dark mode is applied. Toggle adds a button in the top bar for users to switch.
NovouzDarkDisabled=Disabled (always light)
NovouzDarkAuto=Auto (follow OS preference)
NovouzDarkToggle=User toggle (button in top bar)
NovouzDarkForced=Force dark
NovouzCustomCss=Custom CSS
NovouzCustomCssHelp=Custom CSS rules appended after all theme styles. Max 4 KB. Use to fine-tune without editing files.
NovouzCustomCssTruncated=Custom CSS was truncated to 4 KB limit.
```

---

## Files Modified

| File | Change |
|------|--------|
| `novoux/admin/setup.php` | Add accent/danger pickers, radius dropdown, dark mode selector (replaces JS checkbox), custom CSS textarea |
| `novoux/css/novo-inject.css.php` | Emit accent, danger, radius, custom CSS overrides |
| `novoux/langs/en_US/novoux.lang` | 12 new lang keys |
| `theme/novo/theme_descriptor.php` | **New file** — theme metadata |

---

## Implementation Order

1. Create `theme_descriptor.php` (standalone, no dependencies)
2. Add accent + danger color pickers (copy primary color pattern)
3. Add radius preset dropdown + emit logic in inject CSS
4. Replace ALLOW_THEME_JS checkbox with dark mode behavior selector
5. Add custom CSS textarea + sanitization
6. Add all lang keys
7. PHP lint all modified files
8. Test in Docker: verify all new settings persist and apply correctly

---

## Out of Scope

- Live iframe preview
- Export/import settings JSON
- Tabbed admin layout
- Audit log
- Per-user preferences page
