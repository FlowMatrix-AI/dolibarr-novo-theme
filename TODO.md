# TODO — Novo Theme / NovouX Module

## DoliStore Publishing (blocked on wiki account)

The Dolibarr wiki signup captcha is currently broken. Once resolved:

1. **Create wiki account** at https://wiki.dolibarr.org
2. **Reserve a module ID** (range 95000–99999) on https://wiki.dolibarr.org/index.php?title=List_of_modules_id
3. **Update `$this->numero`** in `dolibarr/custom/novoux/core/modules/modNovoux.class.php` (currently `500200`)
4. **Create DoliStore seller account** at https://www.dolistore.com
5. **Build release zip**: `bash scripts/package.sh` → produces `module_novoux-2.1.0.zip`
6. **Test deploy locally**: Dolibarr admin → Setup → Modules → Deploy external module → upload zip
7. **Submit to DoliStore**: Upload zip, fill product page (English required), set status "Request approval"
8. **Wait for validation** (~10 days per wiki docs)

## Pre-Submission Checklist

- [x] Zip name: `module_novoux-VERSION.zip`
- [x] Module root `novoux/` at zip root
- [x] `module_parts['theme'] = 1` declared
- [x] Theme at `novoux/theme/novo/` (auto-discovered by Dolibarr)
- [x] Full `main.inc.php` include pattern (CONTEXT_DOCUMENT_ROOT + SCRIPT_FILENAME)
- [x] Works from `htdocs/custom/novoux/` (standard deploy path)
- [x] No core file modifications — hooks only
- [x] `en_US` language file present and complete
- [x] GPL-3.0 license
- [x] `$this->editor_url` set
- [ ] Module ID reserved (95000–99999) — **BLOCKED**
- [ ] Tested via "Deploy external module" in live Dolibarr instance
- [ ] DoliStore product page created with English description
- [ ] Screenshots/demo GIF for store listing

## DoliStore Listing Content (draft)

**Title:** Novo — Modern Dolibarr Theme  
**Category:** Interface / Skins  
**Price:** Free (GPL-3.0)  
**Compatibility:** Dolibarr ≥ 21.0, PHP ≥ 7.4  

**Description:**
> A clean, token-driven theme for Dolibarr with 8 color palettes, dark mode toggle,
> 3 density levels, collapsible sidebar, and per-user preferences. Zero core edits.
> Install via one zip — theme and companion module bundled together.

## Future Work

- [ ] RTL support
- [ ] Dolibarr 22 compatibility testing
- [ ] Store listing screenshots (login, dashboard, list view, dark mode)
- [ ] Demo instance URL for store page
