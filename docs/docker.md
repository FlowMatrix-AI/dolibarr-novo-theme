# Docker Integration

## Bake into image

```dockerfile
FROM dolibarr/dolibarr:21

COPY dolibarr/custom/novoux/ /var/www/html/custom/novoux/
```

After first boot, activate the theme via Setup > Display and enable the `novoux` module via Setup > Modules.

## Runtime override via volume

```yaml
services:
  dolibarr:
    image: my-dolibarr:latest
    volumes:
      - ./overrides/client.css:/var/www/html/custom/novoux/theme/novo/client-override.css:ro
    environment:
      - NOVOUX_PALETTE=green
      - NOVOUX_PRIMARY_COLOR=#059669
```

## Environment Variables

These are read from `llx_const` (set via NovouX admin or directly in the DB). They are **not** Docker env vars — set them through the Dolibarr admin UI or via SQL:

```sql
INSERT INTO llx_const (name, value, type, entity, visible)
VALUES ('NOVOUX_PALETTE', 'slate', 'chaine', 1, 0)
ON DUPLICATE KEY UPDATE value = 'slate';
```

| Constant | Values | Effect |
|----------|--------|--------|
| `NOVOUX_PALETTE` | `default`, `slate`, `blue`, `green`, `warm` | Color palette |
| `NOVOUX_PRIMARY_COLOR` | Hex (e.g. `#e11d48`) | Override primary brand color |
| `NOVOUX_ACCENT_COLOR` | Hex (e.g. `#ec4899`) | Override accent color |
| `NOVOUX_DANGER_COLOR` | Hex (e.g. `#dc2626`) | Override danger color |
| `NOVOUX_DENSITY` | `compact`, `default`, `spacious` | Spacing density |
| `NOVOUX_RADIUS` | `sharp`, `default`, `rounded`, `pill` | Border radius preset |
| `NOVOUX_DARK_MODE` | `disabled`, `auto`, `toggle`, `forced` | Dark mode behavior |
| `NOVOUX_LOGO_URL` | URL | Replace header logo |
| `NOVOUX_CUSTOM_CSS` | CSS text (max 4096 chars) | Inject arbitrary CSS |

## Multi-Tenant / Multi-Entity

Dolibarr constants are entity-scoped. Each entity can have different `NOVOUX_*` values:

```sql
-- Entity 1: blue palette
INSERT INTO llx_const (name, value, type, entity, visible)
VALUES ('NOVOUX_PALETTE', 'blue', 'chaine', 1, 0);

-- Entity 2: green palette
INSERT INTO llx_const (name, value, type, entity, visible)
VALUES ('NOVOUX_PALETTE', 'green', 'chaine', 2, 0);
```

## Dev Environment

```bash
docker compose -f docker-compose.dev.yml up -d
# Access at http://localhost:8080 (admin / admin123)
```

Theme files are volume-mounted read-only for hot reload during development.

To reset the dev database language to English:
```bash
./scripts/init-dev-lang.sh
```

## Production Notes

- Theme directory can be mounted `:ro` — it's read-only at runtime
- Module directory needs no write access (no generated files)
- `novo.js` is gated by `ALLOW_THEME_JS` — only loaded if the dark mode option is set to `toggle`
- For environments behind a CDN, bust CSS cache by bumping Dolibarr's "version" parameter or clearing CDN
