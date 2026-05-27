#!/usr/bin/env node
/**
 * build-palettes.js — Reads tokens/*.json and generates dolibarr/theme/novo/palettes/*.css
 * Also reads tokens/variants/*.json and generates dolibarr/theme/novo/variants/*.css
 */
const fs = require('fs');
const path = require('path');

const TOKENS_DIR = path.join(__dirname, '..', 'tokens');
const VARIANTS_DIR = path.join(TOKENS_DIR, 'variants');
const OUTPUT_DIR = path.join(__dirname, '..', 'dolibarr', 'theme', 'novo', 'palettes');
const VARIANTS_OUTPUT_DIR = path.join(__dirname, '..', 'dolibarr', 'theme', 'novo', 'variants');

fs.mkdirSync(OUTPUT_DIR, { recursive: true });
fs.mkdirSync(VARIANTS_OUTPUT_DIR, { recursive: true });

// Categories that map to --novo-{category}-{key} CSS variables
const SPATIAL_CATEGORIES = ['spacing', 'typography', 'density', 'layout'];

/**
 * Generate CSS variable lines from a spatial category object
 */
function emitSpatialVars(category, obj, indent = '  ') {
  const lines = [];
  for (const [key, value] of Object.entries(obj)) {
    lines.push(`${indent}--novo-${category}-${key}: ${value};`);
  }
  return lines;
}

// --- Build palette CSS files ---
const tokenFiles = fs.readdirSync(TOKENS_DIR).filter(f => f.endsWith('.json'));

for (const file of tokenFiles) {
  const token = JSON.parse(fs.readFileSync(path.join(TOKENS_DIR, file), 'utf8'));
  const lines = [`/* Generated from tokens/${file} — do not edit */`];

  // Light mode: novo tokens + dolibarr variable overrides + spatial tokens
  lines.push(':root {');
  for (const [key, value] of Object.entries(token.colors)) {
    lines.push(`  --novo-${key}: ${value};`);
  }
  if (token.dolibarr) {
    lines.push('');
    lines.push('  /* Dolibarr UI variable overrides */');
    for (const [key, value] of Object.entries(token.dolibarr)) {
      lines.push(`  --${key}: ${value};`);
    }
  }
  // Spatial categories (spacing, typography, density, layout)
  for (const cat of SPATIAL_CATEGORIES) {
    if (token[cat]) {
      lines.push('');
      lines.push(`  /* ${cat} */`);
      lines.push(...emitSpatialVars(cat, token[cat]));
    }
  }
  lines.push('}');

  // Dark mode overrides
  if (token.dark && Object.keys(token.dark).length > 0) {
    lines.push('');
    lines.push('@media (prefers-color-scheme: dark) {');
    lines.push('  :root {');
    for (const [key, value] of Object.entries(token.dark)) {
      lines.push(`    --novo-${key}: ${value};`);
    }
    lines.push('  }');
    lines.push('}');
  }

  lines.push('');
  const outPath = path.join(OUTPUT_DIR, `${token.name}.css`);
  fs.writeFileSync(outPath, lines.join('\n'));
  console.log(`  ✓ ${token.name}.css`);
}

console.log(`\nGenerated ${tokenFiles.length} palette files.`);

// --- Build variant CSS files ---
if (fs.existsSync(VARIANTS_DIR)) {
  const variantFiles = fs.readdirSync(VARIANTS_DIR).filter(f => f.endsWith('.json'));
  let variantCount = 0;

  for (const file of variantFiles) {
    const variant = JSON.parse(fs.readFileSync(path.join(VARIANTS_DIR, file), 'utf8'));
    const lines = [`/* Generated from tokens/variants/${file} — do not edit */`];
    lines.push(':root {');

    if (variant.overrides) {
      for (const cat of SPATIAL_CATEGORIES) {
        if (variant.overrides[cat]) {
          lines.push(`  /* ${cat} overrides */`);
          lines.push(...emitSpatialVars(cat, variant.overrides[cat]));
        }
      }
    }

    lines.push('}');
    lines.push('');

    const outName = file.replace('.json', '.css');
    const outPath = path.join(VARIANTS_OUTPUT_DIR, outName);
    fs.writeFileSync(outPath, lines.join('\n'));
    console.log(`  ✓ variants/${outName}`);
    variantCount++;
  }

  if (variantCount > 0) {
    console.log(`\nGenerated ${variantCount} variant files.`);
  }
}
