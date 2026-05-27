# Install Novo Theme + NovouX Module

## Quick Start (DoliStore / Deploy)

1. Download `module_novoux-*.zip` from [Releases](https://github.com/FlowMatrix-AI/dolibarr-novo-theme/releases) or DoliStore
2. In Dolibarr go to **Setup → Modules → Deploy an external module** and upload the zip
3. Enable the NovouX module (category: Interface)
4. Go to **Setup → Display** and select `novo` as the theme
5. Configure palette, density, and colors via **Setup → Modules → NovouX → Setup**

## Manual Install

```bash
unzip module_novoux-*.zip -d /path/to/dolibarr/htdocs/custom/
```

Then follow steps 3–5 above.

## Requirements

- Dolibarr ≥ 21.0
- PHP ≥ 7.4

## What Gets Installed

```
htdocs/custom/novoux/
├── theme/novo/          ← Theme CSS, JS, images (auto-discovered via module_parts)
├── admin/               ← Admin setup page
├── class/               ← Hook actions
├── core/modules/        ← Module descriptor
├── css/                 ← Runtime CSS injection
├── langs/               ← Translations
├── lib/                 ← Helper functions
└── user_prefs.php       ← Per-user preference tab
```

## Uninstall

1. Disable the NovouX module in Setup → Modules
2. Switch to another theme in Setup → Display
3. Remove `htdocs/custom/novoux/`
