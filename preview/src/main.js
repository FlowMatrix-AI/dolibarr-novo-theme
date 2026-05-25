import { palettes } from './palettes.js';

// Apply palette to :root
function applyPalette(name, isDark) {
  const palette = palettes[name];
  if (!palette) return;

  const root = document.documentElement;
  const colors = isDark && palette.dark
    ? { ...palette.colors, ...palette.dark }
    : palette.colors;

  for (const [key, value] of Object.entries(colors)) {
    root.style.setProperty(`--novo-${key}`, value);
  }

  root.setAttribute('data-theme', isDark ? 'dark' : 'light');
}

// Populate palette selector
const select = document.getElementById('palette-select');
for (const [key, palette] of Object.entries(palettes)) {
  const opt = document.createElement('option');
  opt.value = key;
  opt.textContent = palette.label;
  select.appendChild(opt);
}

// Controls
let currentPalette = 'default';
let isDark = false;

select.addEventListener('change', (e) => {
  currentPalette = e.target.value;
  applyPalette(currentPalette, isDark);
});

document.getElementById('dark-toggle').addEventListener('change', (e) => {
  isDark = e.target.checked;
  applyPalette(currentPalette, isDark);
});

document.getElementById('color-picker').addEventListener('input', (e) => {
  document.documentElement.style.setProperty('--novo-primary', e.target.value);
});

// Render mock panels
document.getElementById('panel-login').innerHTML = `
  <h2>Login</h2>
  <div style="max-width: 280px; margin: 0 auto;">
    <input class="mock-input" type="text" placeholder="Username">
    <input class="mock-input" type="password" placeholder="Password">
    <button class="mock-btn mock-btn-primary" style="width:100%">Sign In</button>
  </div>
`;

document.getElementById('panel-dashboard').innerHTML = `
  <h2>Dashboard</h2>
  <div class="mock-info-box">
    <div class="mock-info-icon" style="background: var(--novo-primary); color: #fff;">📊</div>
    <div class="mock-info-content">
      <div class="label">Proposals</div>
      <div class="value">42</div>
    </div>
  </div>
  <div class="mock-info-box">
    <div class="mock-info-icon" style="background: var(--novo-success); color: #fff;">✓</div>
    <div class="mock-info-content">
      <div class="label">Invoices paid</div>
      <div class="value">€ 128,450</div>
    </div>
  </div>
  <div class="mock-info-box">
    <div class="mock-info-icon" style="background: var(--novo-warning); color: #fff;">⏳</div>
    <div class="mock-info-content">
      <div class="label">Late invoices</div>
      <div class="value">7</div>
    </div>
  </div>
`;

document.getElementById('panel-list').innerHTML = `
  <h2>List View</h2>
  <table class="mock-table">
    <thead>
      <tr><th>Ref</th><th>Client</th><th>Amount</th><th>Status</th></tr>
    </thead>
    <tbody>
      <tr><td>INV-001</td><td>Acme Corp</td><td>€ 1,250</td><td><span class="mock-badge mock-badge-success">Paid</span></td></tr>
      <tr><td>INV-002</td><td>Widget Co</td><td>€ 3,800</td><td><span class="mock-badge mock-badge-warning">Pending</span></td></tr>
      <tr><td>INV-003</td><td>Beta Ltd</td><td>€ 720</td><td><span class="mock-badge mock-badge-danger">Overdue</span></td></tr>
      <tr><td>PRO-014</td><td>Nova Inc</td><td>€ 5,200</td><td><span class="mock-badge mock-badge-primary">Draft</span></td></tr>
    </tbody>
  </table>
`;

document.getElementById('panel-card').innerHTML = `
  <h2>Record Card</h2>
  <div class="mock-tabs">
    <div class="mock-tab active">Details</div>
    <div class="mock-tab">Notes</div>
    <div class="mock-tab">Documents</div>
    <div class="mock-tab">Events</div>
  </div>
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.8125rem; margin-bottom: 1rem;">
    <div><strong>Reference:</strong> PRO-014</div>
    <div><strong>Date:</strong> 2026-05-25</div>
    <div><strong>Client:</strong> Nova Inc</div>
    <div><strong>Amount:</strong> € 5,200.00</div>
  </div>
  <div style="display: flex; gap: 0.5rem;">
    <button class="mock-btn mock-btn-primary">Validate</button>
    <button class="mock-btn mock-btn-secondary">Clone</button>
    <button class="mock-btn mock-btn-danger">Delete</button>
  </div>
`;

// Initial render
applyPalette(currentPalette, isDark);
