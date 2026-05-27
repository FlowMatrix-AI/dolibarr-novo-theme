# Contributing

## Getting Started

1. Clone the repo
2. Start the dev environment: `docker compose -f docker-compose.dev.yml up -d`
3. Access Dolibarr at http://localhost:8080 (admin / admin123)
4. Activate the novo theme: Setup > Display > select `novo`
5. Enable the novoux module: Setup > Modules > search "Novo" > Enable

See [docs/developing.md](docs/developing.md) for full dev workflow.

## Branch Strategy

- `main` is the release branch
- Create feature branches from `main`
- PRs must pass CI before merge

## Commit Messages

Format: `TYPE: Short description`

Types:
- `NEW` — new feature or capability
- `FIX` — bug fix
- `CLOSE` — resolves an issue
- `DOCS` — documentation only
- `CI` — CI/workflow changes
- `REFACTOR` — code change that neither fixes a bug nor adds a feature

Examples:
```
NEW: Add warm palette with amber/orange tokens
FIX: Dark mode flash on page load with slate palette
DOCS: Add multi-tenant setup instructions
```

## Code Style

### PHP

- Indent with **tabs** (not spaces)
- Follow PSR-12 otherwise
- Use Dolibarr functions (`getDolGlobalString()`, `GETPOST()`, `dol_escape_htmltag()`, `$db->escape()`)
- Never use raw `$_GET`/`$_POST` — always `GETPOST('field', 'type')`
- All forms must include CSRF token: `newToken()`

### CSS (via PHP)

- All visual values use `var(--novo-*)` custom properties
- Never hardcode colors — add a token if one is missing
- Test in light mode, dark mode, and all density levels
- Don't rename Dolibarr's existing CSS classes

### JavaScript

- Vanilla ES2020 only — no jQuery, no external libs
- IIFE pattern (no global scope pollution)
- Target < 5 KB total for `novo.js`
- Must degrade gracefully (no JS = no breakage)

### Tokens

- Source of truth: `tokens/*.json`
- Generated output: `dolibarr/theme/novo/palettes/*.css` and `variants/*.css`
- Always run `node scripts/build-palettes.js` after modifying tokens
- Commit both the token JSON and the generated CSS

## Adding a Palette

1. Copy `tokens/default.json` → `tokens/<name>.json`
2. Edit the `colors` and `dark` sections
3. Run `node scripts/build-palettes.js`
4. Commit both files
5. The palette auto-appears in the NovouX admin dropdown

## Testing

Run these before submitting a PR:

```bash
# PHP lint (requires Docker dev env running)
docker compose -f docker-compose.dev.yml exec web bash -c \
  "find /var/www/html/theme/novo/ /var/www/html/custom/novoux/ -name '*.php' -print0 | xargs -0 -n1 php -l"

# JS syntax
node --check dolibarr/theme/novo/novo.js

# Palette freshness
node scripts/build-palettes.js
git diff --exit-code dolibarr/theme/novo/palettes/ dolibarr/theme/novo/variants/
```

See the [QA Testing Checklist](docs/developing.md#qa-testing-checklist) for manual verification steps.

## What Not to Do

- Don't modify Dolibarr core files (anything outside `dolibarr/theme/novo/` and `dolibarr/custom/novoux/`)
- Don't add npm/Composer runtime dependencies
- Don't add external JS libraries to the theme
- Don't hardcode client-specific branding — use CSS variable overrides
- Don't break backwards compatibility of existing `NOVOUX_*` constants

## Releasing

See [Version Locations](docs/developing.md#version-locations) for files that need version bumps. Tag format: `vX.Y.Z`. The release workflow handles zip packaging and GitHub Release creation automatically.

## License

By contributing, you agree that your contributions will be licensed under GPL-3.0.
