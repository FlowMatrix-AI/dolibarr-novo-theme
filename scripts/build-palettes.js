#!/usr/bin/env node
/**
 * build-palettes.js — Reads tokens/*.json and generates dolibarr/theme/novo/palettes/*.css
 */
const fs = require('fs');
const path = require('path');

const TOKENS_DIR = path.join(__dirname, '..', 'tokens');
const OUTPUT_DIR = path.join(__dirname, '..', 'dolibarr', 'theme', 'novo', 'palettes');

fs.mkdirSync(OUTPUT_DIR, { recursive: true });

const tokenFiles = fs.readdirSync(TOKENS_DIR).filter(f => f.endsWith('.json'));

for (const file of tokenFiles) {
  const token = JSON.parse(fs.readFileSync(path.join(TOKENS_DIR, file), 'utf8'));
  const lines = [`/* Generated from tokens/${file} — do not edit */`];

  // Light mode
  lines.push(':root {');
  for (const [key, value] of Object.entries(token.colors)) {
    lines.push(`  --novo-${key}: ${value};`);
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
