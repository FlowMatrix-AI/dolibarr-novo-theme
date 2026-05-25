# Phase 5 — Preview, Package & Ship

**Goal:** Make the project presentable, distributable, and installable. A live preview site for stakeholders, a zip package for traditional installs, and documentation for Docker deployments.

**Exit criteria:** v1.0 tagged. Preview site live on GitHub Pages. Anyone can install from zip or integrate into a Docker build.

---

## A. GitHub Pages Preview Site

### Purpose

Visual demo of the novo theme for clients/stakeholders without requiring a running Dolibarr instance. Shows key UI patterns across all palettes in light and dark mode.

### Tech

- **Vite + vanilla HTML/CSS/JS** (no framework)
- Consumes the same `tokens/*.json` as the real theme
- Build outputs to `dist/` → deployed via GitHub Actions

### Pages/Sections

| Mock | What it demonstrates |
|------|---------------------|
| Login | Branding, form styling, palette color |
| Dashboard | Cards, info-boxes, layout |
| List view | Table styling, pagination, filters |
| Record card | Tabs, form fields, buttons, badges |

### Interactive Controls

- Palette switcher (dropdown: default, slate, blue, green, warm)
- Light / dark toggle
- Optional: custom primary color picker (live preview)

### File Structure

```
preview/
  index.html
  src/
    main.js
    palettes.js        ← generated from tokens by build-palettes.js
    components/
      login.html
      dashboard.html
      list.html
      card.html
  styles/
    novo-preview.css   ← mirrors real theme variables
  public/
    favicon.ico
  vite.config.js
  package.json
```

### Deployment

`.github/workflows/pages.yml`:

```yaml
on:
  push:
    branches: [main]
    paths: ['preview/**', 'tokens/**']

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
      - run: cd preview && npm ci && npm run build
      - uses: actions/deploy-pages@v4
```

---

## B. Packaging

### Install Zip

`scripts/package.sh` produces a zip that follows Dolibarr's expected layout:

```bash
#!/bin/bash
# Builds novo-vX.Y.Z.zip
VERSION=$(node -p "require('./package.json').version")
zip -r "novo-${VERSION}.zip" \
  dolibarr/theme/novo/ \
  dolibarr/custom/novoux/ \
  -x '*.DS_Store'
```

The zip extracts to:
```
dolibarr/
  theme/novo/...
  custom/novoux/...
```

User copies contents into their Dolibarr `htdocs/` directory.

### Install Script

`scripts/install-local.sh` — convenience for dev/test:

```bash
#!/bin/bash
# Usage: ./scripts/install-local.sh /path/to/dolibarr/htdocs
TARGET=${1:?Usage: install-local.sh /path/to/dolibarr/htdocs}
rsync -av --delete dolibarr/theme/novo/ "$TARGET/theme/novo/"
rsync -av --delete dolibarr/custom/novoux/ "$TARGET/custom/novoux/"
echo "Installed. Select 'novo' in Setup > Display."
```

---

## C. Docker Integration Documentation

Add `docs/docker.md`:

### Bake into image

```dockerfile
FROM dolibarr/dolibarr:21
COPY dolibarr/theme/novo/ /var/www/html/theme/novo/
COPY dolibarr/custom/novoux/ /var/www/html/custom/novoux/
# Optional: inject client override
COPY client-overrides/acme.css /var/www/html/theme/novo/client-override.css
```

### Runtime override via volume

```yaml
services:
  dolibarr:
    image: my-dolibarr:latest
    volumes:
      - ./overrides/client.css:/var/www/html/theme/novo/client-override.css:ro
    environment:
      - NOVOUX_PALETTE=green
      - NOVOUX_PRIMARY_COLOR=#059669
```

### Env vars

| Variable | Effect |
|----------|--------|
| `NOVOUX_PALETTE` | Selects built-in palette (overrides DB) |
| `NOVOUX_PRIMARY_COLOR` | Overrides primary color (hex) |
| `NOVOUX_LOGO_URL` | Client logo URL |

---

## D. Release Checklist

- [ ] All QA matrix pages pass (light + dark, all 5 palettes)
- [ ] `npm run build:palettes` produces no diff
- [ ] Module installs cleanly on fresh Dolibarr v21
- [ ] Preview site builds and deploys
- [ ] CHANGELOG updated
- [ ] README: remove WIP banner, add install instructions, screenshots
- [ ] Tag `v1.0.0`
- [ ] Create GitHub Release with zip attached
- [ ] License and attribution files complete

---

## Deliverables

| File | Action |
|------|--------|
| `preview/*` | Create (entire preview site) |
| `.github/workflows/pages.yml` | Create |
| `scripts/package.sh` | Create |
| `scripts/install-local.sh` | Create |
| `docs/docker.md` | Create |
| `CHANGELOG.md` | Create |
| `README.md` | Update (remove WIP, add docs) |

---

## Not In Scope (Future)

- DoliStore submission
- Automated testing (Playwright visual regression)
- Multi-language preview site
- Video walkthrough
