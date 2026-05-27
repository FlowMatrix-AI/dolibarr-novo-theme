# Phase 11 — Style Variants & Flavours

**Goal:** Ship curated "style variants" that go beyond density to fundamentally change the visual personality of Novo. Each variant is a complete aesthetic opinion — geometry, shadow behavior, border treatment, motion — that stacks on top of any palette.

**Exit criteria:** At least 3 distinct style variants ship. NovouX offers a "Style" selector. A user can combine any palette × density × style variant for a unique look. Preview site showcases all combinations.

---

## Why This Phase Matters

Palettes change color. Density changes spacing. But neither changes the **feel** — whether the UI looks "corporate and sharp" vs "soft and friendly" vs "bold and minimal." These are distinct design languages that share the same underlying component structure.

By offering style variants we make Novo viable across radically different brand personalities:
- A law firm wants sharp corners, strong borders, traditional weight
- A creative agency wants large radii, subtle borders, playful motion
- A tech startup wants zero borders, heavy shadows, glassmorphism

Same data, same layout, completely different personality.

---

## Planned Variants

### `flat` — Minimal & Clean
- Zero shadows everywhere (`--novo-shadow-*: none`)
- Single-pixel borders only (`1px solid var(--novo-border)`)
- No gradients anywhere
- Sharp transitions (100ms or instant)
- High contrast between surface and background
- Best for: data-heavy environments, projector displays, accessibility

### `elevated` — Material-Inspired
- Medium shadows on all surfaces (`--novo-shadow-md` on cards, menus)
- No visible borders (shadows provide separation)
- Subtle hover elevation (shadow increases on hover)
- Slightly larger radii than default
- 200ms ease transitions
- Best for: modern SaaS feel, users coming from Google Workspace

### `glass` — Translucent & Modern
- Semi-transparent surfaces: `background: rgba(255,255,255,0.7)` + `backdrop-filter: blur(12px)`
- Very subtle borders: `1px solid rgba(255,255,255,0.2)`
- No hard shadows — soft glows instead
- Larger radii (12–16px)
- Best for: modern OS-native feel, limited deployments (backdrop-filter performance)
- ⚠️ Mark as "experimental" — not all browsers/hardware handle well

### `brutalist` — Bold & Stark
- Sharp corners everywhere (0px radius)
- Thick borders (2px solid)
- No shadows
- High-contrast text
- Monospace or heavy font suggestion
- Instant transitions (no animation)
- Best for: developer-oriented teams, statement branding

### `soft` — Friendly & Approachable
- Large radii (12px cards, pill buttons)
- Very subtle shadows (`--novo-shadow-sm` everywhere)
- Warmer border colors (slightly tinted, not pure gray)
- Generous padding (+20% vs default)
- Smooth 250ms transitions with ease-out
- Best for: non-technical users, training environments

---

## Implementation

### Variant Token Files

Each variant is a JSON file in `tokens/variants/`:

```json
{
  "name": "elevated",
  "label": "Elevated (Material)",
  "type": "style",
  "overrides": {
    "radii": {
      "sm": "6px",
      "md": "8px",
      "lg": "12px",
      "xl": "16px"
    },
    "shadows": {
      "sm": "0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08)",
      "md": "0 4px 6px rgba(0,0,0,0.1), 0 2px 4px rgba(0,0,0,0.06)",
      "lg": "0 10px 25px rgba(0,0,0,0.12), 0 4px 10px rgba(0,0,0,0.08)",
      "hover": "0 14px 35px rgba(0,0,0,0.15), 0 6px 12px rgba(0,0,0,0.1)"
    },
    "borders": {
      "width": "0px",
      "color": "transparent"
    },
    "motion": {
      "transition-duration": "200ms",
      "transition-easing": "cubic-bezier(0.4, 0, 0.2, 1)"
    }
  }
}
```

### Generated CSS

`dolibarr/theme/novo/variants/style-elevated.css`:
```css
/* Generated from tokens/variants/elevated.json — do not edit */
:root {
  --novo-radius-sm: 6px;
  --novo-radius-md: 8px;
  --novo-radius-lg: 12px;
  --novo-radius-xl: 16px;
  --novo-shadow-sm: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.08);
  --novo-shadow-md: 0 4px 6px rgba(0,0,0,0.1), 0 2px 4px rgba(0,0,0,0.06);
  --novo-shadow-lg: 0 10px 25px rgba(0,0,0,0.12), 0 4px 10px rgba(0,0,0,0.08);
  --novo-border-width: 0px;
  --novo-border-color: transparent;
  --novo-transition: 200ms cubic-bezier(0.4, 0, 0.2, 1);
}
```

### Stacking Model

Variant CSS loads in this order (later wins):
1. Base theme (`global.inc.php` `:root`)
2. Palette (`palettes/blue.css`)
3. Density variant (`variants/density-compact.css`)
4. Style variant (`variants/style-elevated.css`)

Each layer only overrides what it needs. No conflicts because they address orthogonal concerns.

### NovouX Integration

Add "Style" selector to settings:
- Radio group or visual cards showing a mini-preview of each variant
- Stores `NOVOUX_STYLE` constant
- `novo-inject.css.php` loads appropriate variant file

### Combinatorial Matrix

With 5 palettes × 3 densities × 5 styles = **75 distinct configurations** from one theme codebase.

---

## Additional Variant Ideas (Future)

| Variant | Concept | Notes |
|---------|---------|-------|
| `retro` | Visible bevels, inset shadows, textured backgrounds | Nostalgic, could appeal to long-time Dolibarr users |
| `mono` | Single-color accent, rest is grayscale | High-end, editorial feel |
| `outline` | Wire-frame aesthetic, transparent backgrounds, strong outlines | Technical/engineering personality |
| `dashboard` | Optimized for the home page: larger cards, chart-friendly spacing | Could be page-specific rather than global |

---

## Preview Site Update

Expand the preview site to showcase the combinatorial matrix:
- Palette selector (row of swatches)
- Density selector (3 buttons)
- Style selector (5 cards with visual previews)
- Live preview updates on any change
- URL parameters encode selection for sharing: `?palette=green&density=compact&style=elevated`

---

## Dependencies

- Phase 7 (token expansion, `build-palettes.js` supporting variant types)
- Phase 9 (NovouX settings panel with tabs for style selection)
- Phase 6 CSS should be clean enough that variant overrides produce coherent results

## Risk

- Combinatorial explosion of testing: 75+ configs to QA
- Mitigation: automated visual regression tests (Phase 10 CI), focus manual testing on "hero" combinations
- `glass` variant depends on `backdrop-filter` — not available in older browsers
- Mitigation: mark experimental, provide `@supports` fallback to `elevated` style

## Testing Strategy

- Automated: Playwright screenshots of 5 key pages × all style variants, compared against golden images
- Manual: spot-check each variant in both light and dark mode
- Device testing: each variant at 1440px, 768px, 375px widths
