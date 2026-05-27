# Phase 10 — Repo Maturity, Ecosystem & Distribution

**Goal:** Professionalize the project for public distribution. Rename the repo, add a `theme_descriptor.php`, set up proper CI/CD, publish to Dolistore, create documentation site, and establish a contribution workflow.

**Exit criteria:** Repo renamed and redirecting. Theme installable via Dolistore. CI validates theme on Dolibarr v21+. Documentation site live. Contribution guidelines published.

---

## Why This Phase Matters

A theme that works locally is a project. A theme that's discoverable, installable, documented, and maintainable is a product. This phase bridges that gap. It also forces us to solve the "upstream compatibility" problem systematically rather than ad-hoc.

---

## Deliverables

### 1. Repo Rename

**Current:** `FlowMatrix-AI/dolibarr-ui-skin`
**Target:** `FlowMatrix-AI/dolibarr-novo`

Steps:
- Rename on GitHub (Settings → General → Repository name)
- GitHub auto-redirects old URLs indefinitely
- Update all internal references:
  - `package.json` name field
  - README badges and links
  - Docker Compose image labels
  - GitHub Pages CNAME (if any)
  - Preview site base URL in `vite.config.js`

### 2. Theme Descriptor

Create `dolibarr/theme/novo/theme_descriptor.php`:

```php
<?php
$theme_name = 'novo';
$theme_desc = 'Modern, configurable Dolibarr theme with CSS custom properties, dark mode, and palette switching.';
$theme_version = '1.1.0';
$theme_author = 'FlowMatrix-AI';
$theme_url = 'https://github.com/FlowMatrix-AI/dolibarr-novo';
$theme_min_dolibarr = '21.0.0';
$theme_min_php = '7.4';
```

This enables better discovery and metadata display in Dolibarr's theme selector.

### 3. Version Alignment

Synchronize versions across artifacts:
- Theme version (in `theme_descriptor.php`, `AUTHOR` file)
- NovouX module version (in `modNovoux.class.php`)
- `package.json` version
- Git tags

All should be `1.x.0` going forward. Use SemVer:
- MAJOR: breaking changes (e.g., removed palette, renamed token)
- MINOR: new features (new palette, new density variant, new NovouX settings)
- PATCH: bug fixes, upstream compatibility fixes

### 4. CI/CD Pipeline

GitHub Actions workflows:

**`.github/workflows/validate.yml`:**
- Trigger: PR to `main`
- Jobs:
  1. **Lint PHP** — `php -l` on all `.php` files
  2. **Lint CSS** — Stylelint on generated palette CSS
  3. **Build check** — Run `build-palettes.js`, verify output matches committed palettes
  4. **Integration test** — Spin up Dolibarr v21 Docker, install theme, hit key pages, assert no PHP errors in logs
  5. **Accessibility** — Run axe-core on preview site pages, fail on WCAG AA violations

**`.github/workflows/release.yml`:**
- Trigger: tag push `v*`
- Jobs:
  1. Build palette CSS
  2. Run `scripts/package.sh`
  3. Create GitHub Release with zip artifact
  4. Update GitHub Pages preview site
  5. (Future) Upload to Dolistore API

### 5. Dolistore Publication

- Package theme + module as installable zip per Dolistore format
- Create Dolistore listing with screenshots, description, requirements
- Version update workflow: tag → CI builds zip → upload to Dolistore

### 6. Upstream Compatibility Strategy

Document and automate the process for handling new Dolibarr releases:

**Process:**
1. When Dolibarr releases v22 (or v21.x with theme changes), diff their `eldy/global.inc.php` against our base
2. Identify conflicts with our modifications
3. Create compatibility branch, merge upstream changes
4. Run integration tests
5. Release patch version

**Tooling:**
- Script: `scripts/check-upstream.sh` — fetches latest Eldy, diffs against our base copy, reports conflicts
- CI job: weekly cron that runs the check and opens an issue if conflicts detected

### 7. Documentation Site

Static docs (GitHub Pages or similar):
- Installation guide (zip, git, Docker, Dolistore)
- Configuration reference (all NovouX settings)
- Palette gallery (visual showcase of all palettes × density × dark mode)
- Customization guide (how to create custom palettes, override variables)
- Developer guide (contributing, architecture, build process)
- Changelog (auto-generated from git tags)

Source: `docs/` directory, built with a static site generator (VitePress, Docusaurus, or plain HTML).

### 8. Contributing Guidelines

Create `CONTRIBUTING.md`:
- How to set up dev environment
- Branch naming convention (`feature/`, `fix/`, `docs/`)
- Commit message format
- PR template with QA checklist
- Code style requirements (tabs for PHP, etc.)
- How to add a new palette
- How to add a new NovouX setting

---

## Dependencies

- Phases 6–9 don't need to be complete — this phase can run in parallel for non-code items
- Rename should happen early (before too many external references accumulate)
- Dolistore publication requires at least one stable, well-tested release

## Risk

- Rename breaks existing clones/forks (mitigated by GitHub redirect)
- Dolistore review process may have requirements we haven't met
- Mitigation: review Dolistore submission guidelines before packaging
