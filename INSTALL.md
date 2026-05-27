# Install Novo Theme + NovouX Module

## Quick Start

1. Download the latest `novo-*.zip` from [Releases](https://github.com/FlowMatrix-AI/dolibarr-ui-skin/releases)
2. Extract into your Dolibarr `htdocs/` directory:
   ```bash
   unzip novo-*.zip -d /path/to/dolibarr/htdocs/
   ```
3. Go to **Setup → Display** and select `novo` as the theme
4. Go to **Setup → Modules**, search "NovouX", and enable it
5. Configure palette, density, and colors via **Setup → Modules → NovouX → Setup**

## Requirements

- Dolibarr ≥ 21.0
- PHP ≥ 7.4

## What Gets Installed

```
htdocs/
├── theme/novo/          ← Theme CSS, JS, images
└── custom/novoux/       ← Companion config module
```

## Uninstall

1. Disable the NovouX module in Setup → Modules
2. Switch to another theme in Setup → Display
3. Remove `htdocs/theme/novo/` and `htdocs/custom/novoux/`
