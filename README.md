# dolibarr-ui-skin

> **🚧 Work in Progress — not yet usable.** This theme is under active development and cannot be installed yet.

**Novo** — a modern, generic Dolibarr theme for v21+. Not branded to any company.

## What is this?

A clean, opinionated Dolibarr theme that replaces the default look with modern typography (Inter), better spacing, and a CSS custom property system — without touching core Dolibarr files.

Designed so operators (hosting providers, consultancies, etc.) can deploy it as-is and override colors/branding per client with a single CSS file at deploy time.

- **Theme:** `htdocs/theme/novo/` — full visual reskin, responsive, dark mode
- **Module:** `htdocs/custom/novoux/` — optional admin config for palette/logo (coming later)
- **Override model:** All styling uses `--novo-*` CSS variables. Drop a client override CSS file to rebrand without forking.

## Status

See [docs/PLAN.md](docs/PLAN.md) for the full project plan and decision log.

## License

GPL-3.0 — see [LICENSE](LICENSE)