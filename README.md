# Novo — Modern Dolibarr Theme

A clean, configurable Dolibarr theme for v21+. Token-driven design system with palettes, density variants, dark mode toggle, and zero core edits.

[Live Preview](https://flowmatrix-ai.github.io/dolibarr-novo-theme/) · [Changelog](CHANGELOG.md) · [Architecture](docs/architecture.md)

## Features

- Modern flat design with system-ui font stack and refined component styling
- 8 built-in color palettes (default, slate, blue, green, warm, rose, indigo, teal)
- Dark mode: auto (OS preference), forced, or instant JS toggle (Auto/Dark/Light)
- 3 density levels: compact, default, spacious — switch without code changes
- Design tokens (JSON) drive all palettes and density variants
- All styling uses `--novo-*` CSS custom properties — rebrand with one file
- Sticky table headers for large lists (≥ 8 rows)
- Collapsible sidebar (icon rail with hover flyout)
- Per-user theme preferences (palette, density, color overrides)
- French and English translations included
- Companion module `novoux` for admin-level configuration
- Zero changes to Dolibarr core — pure theme + external module

## Install

### From DoliStore (recommended)

1. Go to **Setup > Modules > Deploy an external module**
2. Upload `module_novoux-VERSION.zip` (see [releases](https://github.com/FlowMatrix-AI/dolibarr-novo-theme/releases))
3. Enable the module under **Setup > Modules**
4. Go to **Setup > Display** and select `novo` as the skin

### From zip (manual)

```bash
# Download the latest release zip
unzip module_novoux-VERSION.zip -d /path/to/dolibarr/htdocs/custom/
```

### From source

```bash
git clone https://github.com/FlowMatrix-AI/dolibarr-novo-theme.git
cd dolibarr-novo-theme
cp -r dolibarr/custom/novoux /path/to/dolibarr/htdocs/custom/
```

### Docker

```dockerfile
FROM dolibarr/dolibarr:21
COPY dolibarr/custom/novoux/ /var/www/html/custom/novoux/
```

See [docs/docker.md](docs/docker.md) for full Docker integration docs.

## Activate

1. Enable the `novoux` module under **Setup > Modules** (category: Interface)
2. Go to **Setup > Display** and select `novo` as the skin
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
| `rose` | `#e11d48` | Bold, energetic |
| `indigo` | `#4f46e5` | Modern SaaS feel |
| `teal` | `#0d9488` | Fresh, health/wellness |

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

| Requirement | Supported |
|-------------|-----------|
| Dolibarr | 21.0.0 – 24.0.0 |
| PHP | ≥ 7.4 |
| Browser | Any modern (Chrome, Firefox, Safari, Edge) |

Both ends of the Dolibarr range are exercised on every push and pull request by
the CI compatibility matrix (21.0.0, 22, 23, 24), so the range is measured
rather than assumed.