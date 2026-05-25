# Developing

## Prerequisites

- Docker & Docker Compose
- Node.js 20+
- A browser

## Dev Environment

```bash
docker compose -f docker-compose.dev.yml --env-file .env.dev up -d
```

Access at http://localhost:8080 (admin / admin123). Theme files are volume-mounted — edit and refresh.

## Repo Layout

```
tokens/          ← palette source-of-truth (JSON)
scripts/         ← build-palettes.js, package.sh, install-local.sh
dolibarr/
  theme/novo/    ← the theme (PHP + CSS)
  custom/novoux/ ← companion module
preview/         ← Vite static preview site
docs/            ← documentation
planning/        ← archived build plans
```

## Common Tasks

| Task | Command |
|------|---------|
| Regenerate palettes | `node scripts/build-palettes.js` |
| Build preview site | `cd preview && npm ci && npm run build` |
| Package release zip | `./scripts/package.sh` |
| Install to local Dolibarr | `./scripts/install-local.sh /path/to/htdocs` |

## Adding a Palette

1. Create `tokens/<name>.json` (copy an existing one, change colours)
2. Run `node scripts/build-palettes.js`
3. Update `preview/src/palettes.js` with the new palette
4. Commit both the token and the generated CSS

## Modifying Theme CSS

All visual changes go in the `.inc.php` files under `dolibarr/theme/novo/`. Key rules:

- Use `var(--novo-*)` for all colours, radii, shadows
- Don't rename Dolibarr's existing CSS classes (pages reference them)
- Test in both light and dark mode
- PHP colour variables in `theme_vars.inc.php` are legacy — used by Dolibarr's SkinEditor and chart system

## CI

- **Palette freshness**: `ci.yml` runs `build-palettes.js` and fails if generated files differ from committed
- **Pages deploy**: `pages.yml` builds and deploys preview site on push to `main`
