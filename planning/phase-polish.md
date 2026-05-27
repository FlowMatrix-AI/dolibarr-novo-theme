# Phase: Polish & Content (while waiting for DoliStore wiki)

Quick-win items we can ship now without the wiki/DoliStore account.

## Deliverables

### 1. French Translation (`langs/fr_FR/novoux.lang`)
- Translate all 38 keys from `en_US/novoux.lang`
- Dolibarr is French-origin — having fr_FR is important for store credibility

### 2. New Palettes: Rose, Indigo, Teal
- `tokens/rose.json` — pink/rose primary (#e11d48), warm feel
- `tokens/indigo.json` — deep indigo (#4f46e5), modern SaaS
- `tokens/teal.json` — teal/cyan (#0d9488), fresh/health
- Run `node scripts/build-palettes.js` to generate CSS
- Update admin setup.php palette list (auto-detected from filesystem, no change needed)

### 3. About Page
- Standard Dolibarr module "About" tab with version, author, links, license
- Already have `novoux_admin_prepare_head()` returning tabs — add 'about' tab

## Release Plan

One commit + tag `v2.2.0` covering all three items.
