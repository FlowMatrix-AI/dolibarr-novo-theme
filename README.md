# Novo — Modern Dolibarr Theme

A clean, configurable Dolibarr theme for v21+. Token-driven design system with palettes, density variants, dark mode toggle, and zero core edits.

[Live Preview](https://flowmatrix-ai.github.io/dolibarr-novo-theme/) · [Changelog](CHANGELOG.md) · [Architecture](docs/architecture.md)

## Features

- Modern flat design with system-ui font stack and refined component styling
- 5 built-in color palettes (default blue, slate, deep blue, green, warm)
- Dark mode: auto (OS preference), forced, or instant JS toggle (Auto/Dark/Light)
- 3 density levels: compact, default, spacious — switch without code changes
- Design tokens (JSON) drive all palettes and density variants
- All styling uses `--novo-*` CSS custom properties — rebrand with one file
- Sticky table headers for large lists (≥ 8 rows)
- Companion module `novoux` for admin-level configuration
- Zero changes to Dolibarr core — pure theme + external module

## Install

### From zip

```bash
# Download the latest release zip
unzip novo-1.1.0.zip -d /path/to/dolibarr/htdocs/
```

### From source

```bash
git clone https://github.com/FlowMatrix-AI/dolibarr-novo-theme.git
cd dolibarr-novo-theme
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
3. (Optional) Set palette, density, and colors via the novoux admin page
4. (Optional) Set dark mode to "Toggle" in novoux settings for the instant dark/light switcher

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
docker compose -f docker-compose.dev.yml up -d
# http://localhost:8080 (admin / admin123)
```

```bash
node scripts/build-palettes.js  # Regenerate palette + density CSS from tokens
```

See [docs/developing.md](docs/developing.md) for full development guide.

## License

GPL-3.0 — see [LICENSE](LICENSE)

## Compatibility

| Requirement | Minimum |
|-------------|---------|
| Dolibarr | ≥ 21.0 |
| PHP | ≥ 7.4 |
| Browser | Any modern (Chrome, Firefox, Safari, Edge) |