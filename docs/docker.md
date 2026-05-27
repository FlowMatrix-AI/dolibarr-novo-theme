# Docker Integration

## Bake into image

```dockerfile
FROM dolibarr/dolibarr:21

COPY dolibarr/theme/novo/ /var/www/html/theme/novo/
COPY dolibarr/custom/novoux/ /var/www/html/custom/novoux/
```

## Runtime override via volume

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

## Environment Variables

| Variable | Effect |
|----------|--------|
| `NOVOUX_PALETTE` | Selects built-in palette: `default`, `slate`, `blue`, `green`, `warm` |
| `NOVOUX_PRIMARY_COLOR` | Overrides primary brand color (hex, e.g. `#e11d48`) |
| `NOVOUX_DENSITY` | Display density: `compact`, `default`, `spacious` |
| `NOVOUX_LOGO_URL` | Client logo URL |
| `ALLOW_THEME_JS` | Set to `1` to enable `novo.js` (dark toggle, sticky headers) |

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
