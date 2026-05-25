# Novo — Modern Dolibarr Theme

A clean, configurable Dolibarr theme for v21+. CSS variable-based, dark mode ready, palette switchable.

[Live Preview](https://flowmatrix-ai.github.io/dolibarr-ui-skin/) · [Changelog](CHANGELOG.md)

## Features

- Modern flat design with system-ui font stack
- 5 built-in color palettes (blue, slate, deep blue, green, warm)
- Dark mode support (auto via `prefers-color-scheme` or forced)
- All styling uses `--novo-*` CSS custom properties — rebrand with one file
- Companion module `novoux` for admin-level palette/color/logo configuration
- Zero changes to Dolibarr core — pure theme + external module

## Install

### From zip

```bash
# Download the latest release zip
unzip novo-1.0.0.zip -d /path/to/dolibarr/htdocs/
```

### From source

```bash
git clone https://github.com/FlowMatrix-AI/dolibarr-ui-skin.git
cd dolibarr-ui-skin
./scripts/install-local.sh /path/to/dolibarr/htdocs
```

### Docker

```dockerfile
FROM dolibarr/dolibarr:21
COPY dolibarr/theme/novo/ /var/www/html/theme/novo/
COPY dolibarr/custom/novoux/ /var/www/html/custom/novoux/
```

See [docs/docker.md](docs/docker.md) for full Docker integration docs.

## Activate

1. Go to **Setup > Display** and select `novo` as the skin
2. (Optional) Enable the `novoux` module under **Setup > Modules** for admin GUI
3. (Optional) Set palette via the novoux admin page or env var `NOVOUX_PALETTE=green`

## Palettes

| Name | Primary | Character |
|------|---------|-----------|
| `default` | `#3b82f6` | Clean, neutral, professional |
| `slate` | `#475569` | Subdued, corporate |
| `blue` | `#1d4ed8` | Deeper blue, high contrast |
| `green` | `#059669` | Fresh, eco/finance feel |
| `warm` | `#d97706` | Warm, creative/agency feel |

## Per-Client Branding

Override `--novo-*` variables with a single CSS file:

```css
:root {
  --novo-primary: #e11d48;
  --novo-primary-hover: #be123c;
}
```

Load via volume mount, module CSS injection, or baked into image.

## Development

```bash
cp .env.dev.example .env.dev
docker compose -f docker-compose.dev.yml --env-file .env.dev up -d
# http://localhost:8080 (admin / admin123)
```

```bash
node scripts/build-palettes.js  # Regenerate palette CSS from tokens
```

## License

GPL-3.0 — see [LICENSE](LICENSE)