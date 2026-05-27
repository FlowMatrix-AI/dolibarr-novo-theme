# Project Audit — v1.1.0 (2026-05-27)

Unbiased assessment of the Novo theme project after the v1.1.0 release.

---

## Inventory

| Component | Files | Notes |
|-----------|-------|-------|
| Theme (`dolibarr/theme/novo/`) | 14 PHP, 212 img, 1 JS, 5 palette CSS, 2 density CSS | Main deliverable |
| Module (`dolibarr/custom/novoux/`) | 8 files | Admin GUI + CSS injection |
| Token pipeline (`tokens/`, `scripts/build-palettes.js`) | 7 JSON + 1 Node script | Build-time only |
| Preview site (`preview/`) | 222 files (Vite) | Deployed to GH Pages |
| CI/CD (`.github/workflows/`) | 3 workflows | ci, release, pages |
| Docs (`docs/`) | 4 files + CONTRIBUTING + CHANGELOG + README | Consolidated post-v1.1 |
| Scripts (`scripts/`) | 5 files | build, install, package, dev init |

---

## Gaps

### 1. No Automated Tests

- CI only does: PHP lint (`php -l`), JS syntax check (`node --check`), palette freshness (diff build output).
- Zero PHPUnit tests for the module (install, uninstall, const CRUD, access control).
- Zero visual regression tests.
- Zero integration tests verifying CSS output given various `llx_const` configurations.

**Risk:** Any refactor or Dolibarr upgrade could silently break the module with no safety net.

### 2. Image Bloat (212 PNGs)

- These are copies of the eldy theme's image set.
- Dolibarr v21 uses FontAwesome for nearly all icons; these PNGs are legacy fallbacks.
- Most will never render in a default v21 installation.
- They add ~2 MB to the release zip for negligible value.

**Risk:** Wasted download size, maintenance noise in diffs, gives impression of unmaintained legacy code.

### 3. Real-World Validation is Thin

- Testing has been limited to Docker dev with default module set.
- Not validated with: HRM, Manufacturing, POS, Website builder, multi-company, RTL locales.
- Third-party modules that inject their own CSS/JS may conflict with `--novo-*` overrides or `novo.js`.
- Dark mode has not been tested with modules that use inline styles or hardcoded colors.

**Risk:** Users enabling the theme on a production instance with many modules may hit visual regressions immediately.

### 4. Preview Site ≠ Theme Validation

- The 222-file Vite demo renders styled HTML components in isolation.
- It does **not** prove the theme works inside Dolibarr — it's a marketing asset, not a QA tool.
- Maintaining 222 files for a demo that doesn't test the actual product is overhead.

**Risk:** False confidence. The preview looking good doesn't mean the theme works.

### 5. Token Pipeline Over-Engineering

- 7 JSON token files + a Node build script produce 7 small CSS files (5 palettes + 2 density variants).
- Total output is ~350 lines of CSS across all generated files.
- No runtime consumer of the JSON — the build output is static CSS checked into git.
- If palettes are added infrequently (quarterly at most), hand-authored CSS is simpler.

**Not a bug**, but adds a build dependency (Node.js) for marginal benefit. Worth questioning whether the abstraction layer earns its keep.

### 6. DoliStore / Distribution Unknowns

- Unclear whether the packaging layout matches DoliStore expectations.
- Theme lives at `htdocs/theme/novo/` (standard) but the module at `htdocs/custom/novoux/` (standard for external modules) — this two-artifact model may confuse users expecting a single zip install.
- No `module_descriptor.xml` or marketplace metadata file exists.
- Version compatibility matrix (which Dolibarr versions are supported?) is not formally declared.

**Risk:** If the goal is marketplace distribution, packaging and metadata gaps will block listing.

### 7. Missing User-Facing Features (vs. Competition)

Compared to kontava and md-ux themes in this workspace:

| Feature | Novo | Kontava | MD-UX |
|---------|------|---------|-------|
| Per-user palette/density | No (admin-global only) | No | No |
| Sidebar collapse | No | Yes (canvas system) | No |
| Dashboard widgets | No | Yes (box system) | No |
| Multi-entity theming | Partial (per-entity const) | Yes | No |
| Custom login page | Basic gradient | Full branding | Material |

### 8. Security Surface Not Audited

- `setup.php` accepts color hex values and custom CSS from POST — sanitization exists (truncation, `dol_escape_htmltag`) but no formal review against XSS in CSS context (`expression()`, `url()`, `@import`).
- `novo.js` reads from `localStorage` — low risk but no CSP header guidance documented.
- No CSRF token verification audit beyond standard Dolibarr `newToken()` usage.

---

## Strengths (for balance)

- Clean separation: theme + module, zero core edits.
- Token-driven architecture makes theming systematic.
- Dark mode implementation is solid (OS pref + JS toggle + forced modes).
- Density system is unique in the Dolibarr ecosystem.
- CI catches basic regressions on every push.
- Documentation is comprehensive for the project's size.
- Release automation works end-to-end (tag → zip → GH Release).
