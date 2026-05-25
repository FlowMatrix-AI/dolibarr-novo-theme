# Phase 1 — Foundation & Boot

**Goal:** A working `theme/novo/` directory that Dolibarr recognizes and renders without errors, plus a dev environment for iterating.

**Exit criteria:** You can select "novo" in Setup > Display and all pages render identically to Eldy.

---

## Deliverables

### 1. Docker Compose Dev Environment

File: `docker-compose.dev.yml`

- Dolibarr v21 official image
- MariaDB
- Volume mounts:
  - `./dolibarr/theme/novo/` → `/var/www/html/theme/novo/`
  - `./dolibarr/custom/novoux/` → `/var/www/html/custom/novoux/` (empty for now)
- Env vars for auto-install (admin user, DB creds, skip installer wizard)
- Access at `http://localhost:8080`
- Edit CSS locally → refresh browser to see changes (no rebuild step)

### 2. Theme Scaffold

Copy Eldy v21 file structure into `dolibarr/theme/novo/`:

```
dolibarr/theme/novo/
  AUTHOR
  style.css.php
  theme_vars.inc.php
  global.inc.php
  badges.inc.php
  btn.inc.php
  dropdown.inc.php
  info-box.inc.php
  progress.inc.php
  timeline.inc.php
  emaillayout.inc.php
  flags-sprite.inc.php
  main_menu_fa_icons.inc.php
  input-feedback.css
  search-input.inc.css
  tooltips.inc.css
  thumb.png
  img/
  tinymce/
```

### 3. Rebrand Internal References

- Replace `eldy` → `novo` in PHP comments, function names, variable prefixes where appropriate
- Update `AUTHOR` file
- Keep all Eldy class names and CSS selectors intact (Dolibarr pages reference them)
- Do NOT rename CSS classes — only the theme identity

### 4. Introduce --novo-* CSS Custom Property Layer

In `global.inc.php`, add a `:root` block mapping `--novo-*` variables to Eldy's current hardcoded values:

```css
:root {
  --novo-primary: <?php echo $colorbackhmenu1; ?>;
  --novo-bg: <?php echo $colorbackbody; ?>;
  --novo-surface: #ffffff;
  --novo-text: <?php echo $colortexttitlenotab; ?>;
  --novo-border: <?php echo $colorborder; ?>;
  /* ... etc */
}
```

At this stage, the values map 1:1 to Eldy's — no visual change. This establishes the abstraction layer that M2 will restyle.

### 5. Verify

- [ ] `docker compose -f docker-compose.dev.yml up` starts cleanly
- [ ] Dolibarr auto-installs, login works
- [ ] Theme appears in Setup > Display as "novo"
- [ ] Selecting novo renders all pages without PHP errors or missing CSS
- [ ] Pages look identical to Eldy (no visual regression at this stage)

---

## Files Created/Modified This Phase

| File | Action |
|------|--------|
| `docker-compose.dev.yml` | Create |
| `dolibarr/theme/novo/*` | Create (copy from Eldy, rebrand) |
| `.env.dev` | Create (DB creds, Dolibarr config for Docker) |
| `thumb.png` | Replace with novo placeholder |

---

## Not In Scope

- Any visual styling changes (that's M2)
- Palette files
- Module code
- Preview site
- Build scripts
