# TODO — Novo Theme / NovouX Module

## DoliStore publishing

Tracked in **[epic #46](https://github.com/FlowMatrix-AI/dolibarr-novo-theme/issues/46)**
and its sub-issues. The plan used to live here; it is in the tracker now so there
is one source of truth. This file kept a stale module-ID range for months, which
is exactly the failure that split brings.

Reference material that outlives any single issue:

- Module ID ranges and why we reserve in 100000–499999 —
  `docs/research.md` → *Module ID ranges*.
- Packaging and validation rules —
  [wiki](https://wiki.dolibarr.org/index.php/Modules_-_Packaging_rules_and_Dolistore_validation_rules).

## DoliStore listing content (draft)

Final copy for [#52](https://github.com/FlowMatrix-AI/dolibarr-novo-theme/issues/52).
Screenshots are in `docs/screenshots/`.

**Title:** Novo — Modern Dolibarr Theme
**Category:** Interface / Skins
**Price:** Free (GPL-3.0)
**Compatibility:** Dolibarr 21.0.0 – 24.0.0, PHP ≥ 7.4

**Support line (required in the listing):**
> Community-supported, best-effort. Issues: https://github.com/FlowMatrix-AI/dolibarr-novo-theme/issues

**Demo:** https://flowmatrix-ai.github.io/dolibarr-novo-theme/

**Description:**
> Novo is a clean, modern theme for Dolibarr 21 to 24, installed as a single
> module with no changes to Dolibarr core.
>
> - 8 colour palettes — default, slate, blue, green, warm, rose, indigo, teal
> - Dark mode: follow the OS, force it, or give users an instant toggle
> - 3 display densities — compact, default, spacious
> - Collapsible sidebar that drops to an icon rail
> - Per-user preferences: each user can override the palette, density and
>   primary colour from their own User card
> - Rebrand with one CSS file — everything is driven by `--novo-*` custom
>   properties
> - Sticky table headers on long lists
> - English and French translations included
>
> Install the one zip through Setup → Modules → Deploy an external module, enable
> it, then pick `novo` under Setup → Display.

**Screenshots to upload:** `docs/screenshots/` — dashboard (light + dark),
third-party list, NovouX settings, login.

## Future work

Not scoped into #46.

- [ ] RTL support
- [ ] Demo instance URL for the store page, if the Pages preview proves too thin
