# FlowMatrix Dolibarr UI — Project Plan

## What This Is

A single, configurable modern Dolibarr theme + companion external module. Shipped as a public repo that can be installed into any Dolibarr instance via copy/rsync, and later consumed by a Docker image build for AWS ECR/ECS deployment.

This is **not** a Dolibarr fork. It touches zero core files. Everything lives in the standard extension points Dolibarr provides: `htdocs/theme/<name>/` and `htdocs/custom/<module>/`.

---

## Decided

### Architecture

- **One theme, configurable via palettes/tokens** — not multiple theme repos or per-client forks.
- **Two artifacts:**
  - `theme/novo/` — the visual system (CSS, layout, typography, spacing, dark/light)
  - `custom/novoux/` — the configuration module (palette selection, client branding, dashboard overrides, login customization, admin settings page)
- **CSS custom properties throughout** — all colors, radii, spacing, typography reference `--novo-*` variables. Palettes swap the variable values, not the rules.
- **Design tokens as the single source of truth** — JSON token files generate both the real Dolibarr palette CSS and the static preview site styles.
- **No core edits** — upgradeable, no merge conflicts with Dolibarr releases.

### Palette System

- 5 built-in palettes: `default`, `slate`, `blue`, `green`, `warm`
- Dark mode support (either as a toggle or via `prefers-color-scheme`)
- Per-client override: a small CSS file injected by the module that overrides `--novo-*` vars with client brand colors/logo

### Deployment Model (Now vs Later)

| Phase | Method                                                                         |
| ----- | ------------------------------------------------------------------------------ |
| Now   | Clone/rsync into a Dolibarr instance, or install as external module zip        |
| Later | `COPY` into a `dolibarr/dolibarr:21` Docker image, push to ECR, deploy via ECS |

The repo stays decoupled from the image. The image build just consumes a pinned release of this repo.

### GitHub Pages Preview

- Static HTML/CSS mock of key Dolibarr screens (login, dashboard, list view, record card)
- Consumes the same design tokens as the real theme
- Palette switcher + light/dark toggle in the preview UI
- Purpose: visual demo for clients/collaborators without spinning up a real Dolibarr

### Licensing

- Project itself: GPL-3.0 (compatible with Dolibarr ecosystem)
- Any code derived from reference themes must preserve attribution
- Reference repos (MD-UX, Kontava) are both GPL-3.0 — compatible for derivative work with proper notice

### Reference Repos & What We Take From Each

| Repo                          | What's useful                                                                                                                                                                         |
| ----------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **MD-UX** (`dolib-md-ux-ref`) | Visual direction: Material-inspired, responsive, clean spacing. Dark mode via `prefers-color-scheme` in `global.inc.php`. Modern font stack.                                          |
| **Kontava** (`kontava-ref`)   | Structural reference: full module + theme pattern, color extraction to a separate file (`kontava_color.php`), module descriptor, admin setup page, widget system, dashboard homepage. |

---

## Repo Structure (Target)

```
dolibarr-ui-skin/
  README.md
  LICENSE
  docs/
    PLAN.md              ← this file
    research.md

  dolibarr/
    theme/
      novo/
        theme_vars.inc.php
        style.css.php
        global.inc.php
        badges.inc.php
        btn.inc.php
        dropdown.inc.php
        info-box.inc.php
        progress.inc.php
        timeline.inc.php
        img/
        palettes/
          default.css
          slate.css
          blue.css
          green.css
          warm.css

    custom/
      novoux/
        core/
          modules/
            modNovoux.class.php
        admin/
          setup.php
        css/
          novo-client.css.php
        img/
        lib/
          novoux.lib.php
        langs/
          en_US/
          fr_FR/

  tokens/
    default.json
    slate.json
    blue.json
    green.json
    warm.json

  preview/
    index.html
    src/
    styles/

  scripts/
    install-local.sh
    build-palettes.sh
    package.sh

  .github/
    workflows/
      pages.yml
```

---

## Milestones

### M1 — Visual Spike

- Working theme folder (`theme/novo/`) that installs into Dolibarr v21+ and renders
- Structural fork of Eldy (complete CSS class coverage), progressively modernized
- Visual direction from MD-UX (spacing, responsive, Material-inspired)
- One palette (default), light mode only
- Before/after screenshots

### M2 — Palette System + Dark Mode

- Token files → generated palette CSS
- Dark mode support
- All 5 palettes functional

### M3 — External Module

- Module descriptor, enable/disable via Dolibarr admin
- Admin setup page for palette selection + client logo
- CSS injection of client overrides
- Login page branding

### M4 — GitHub Pages Preview

- Static mock pages consuming shared tokens
- Palette switcher, dark/light toggle
- Deployed via GitHub Actions

### M5 — Deployment Packaging

- `scripts/package.sh` producing an installable zip
- Install script for rsync-to-Dolibarr workflow
- Documentation for Docker image integration

---

## Locked Decisions (Strategic)

| #   | Decision                 | Resolution                                                                          |
| --- | ------------------------ | ----------------------------------------------------------------------------------- |
| 1   | Repo name                | `dolibarr-ui-skin` — generic, not brand-locked                                      |
| 2   | Theme folder name        | `novo` — Latin for "new", short, distinctive, no ecosystem conflicts                |
| 3   | Module internal name     | `novoux` — signals UX/config companion to the theme                                 |
| 4   | CSS variable prefix      | `--novo-*` — e.g. `var(--novo-brand-primary)`, `var(--novo-surface)`                |
| 5   | Base starting point      | Structural fork of Eldy v21 (complete CSS coverage), MD-UX as visual reference only |
| 6   | Target Dolibarr versions | v21+ only. Older versions deferred unless specific client need arises               |

---

## Open Decisions

### Visual / Design

7. **Font stack** — Which primary font:
   - Inter
   - IBM Plex Sans
   - Geist
   - Source Sans Pro
   - System font stack only (no web fonts)

8. **Default palette colors** — What's the FlowMatrix brand primary?
   - Currently undefined. Need hex values for:
     - Primary
     - Secondary/accent
     - Background
     - Surface
     - Text

9. **Border radius style** — How rounded:
   - Subtle (4-6px)
   - Medium (8-12px)
   - Pill-style (16px+)
   - Sharp (0-2px)

10. **Density** — Default spacing density:
    - Comfortable (more whitespace, modern SaaS feel)
    - Compact (denser, more data visible)
    - Configurable per-user?

11. **Sidebar style** — Left menu treatment:
    - Full sidebar (always visible on desktop)
    - Collapsible sidebar (icon-only when collapsed)
    - Keep Dolibarr's existing sidebar logic, just restyle

12. **Dark mode approach**:
    - `prefers-color-scheme` only (auto, follows OS)
    - Manual toggle (stored in user preference)
    - Both (auto default, manual override)

### Technical / Implementation

13. **Preview site tech** — What to build the static preview with:
    - Plain HTML + CSS (simplest)
    - Vite + vanilla (fast, handles token generation)
    - Astro (static-first, component support)
    - React/Next (overkill?)

14. **Token format and build** — How to generate palette CSS from tokens:
    - Simple shell script (jq + template)
    - Style Dictionary
    - Custom Node script
    - Hand-maintained (no build, just write CSS directly)

15. **How to handle Dolibarr's PHP-in-CSS pattern** — `style.css.php` generates CSS via PHP. Options:
    - Keep the PHP generation pattern (Dolibarr-native, works with their caching)
    - Output pure CSS files and bypass PHP generation where possible
    - Hybrid: PHP for Dolibarr integration points, pure CSS for FlowMatrix additions

16. **Module configuration storage** — Where client settings live:
    - Dolibarr `llx_const` table (standard module approach)
    - Environment variables (better for Docker/12-factor)
    - Both (env vars override DB values)

17. **Dashboard overrides** — How much to customize the landing page:
    - Just restyle existing Dolibarr dashboard
    - Provide a custom homepage (like Kontava does)
    - Widget system with FlowMatrix-designed cards
    - Defer entirely to M3+

18. **What to include from reference repos** — Specifically:
    - Take MD-UX's responsive CSS as a starting point?
    - Take Kontava's module descriptor structure?
    - Vendor them in `third_party/` or just use as study references?

### Process

19. **When to test against real Dolibarr** — Do you have a local Dolibarr instance running, or should we set up Docker Compose for dev?

20. **Public release timing** — Ship incremental work publicly from day one, or develop privately until M1 is done?
