/**
 * novo.js — Theme JavaScript for Novo (Dolibarr theme)
 * Loaded when ALLOW_THEME_JS constant is set.
 *
 * Features:
 * - Dark mode toggle (Auto/Dark/Light) with localStorage persistence
 * - Sticky table headers for large lists
 */
(function () {
	'use strict';

	var SCHEME_KEY = 'novo-color-scheme';
	var MODES = ['auto', 'dark', 'light'];
	var ICONS = {
		auto: 'fa-circle-half-stroke',
		dark: 'fa-moon',
		light: 'fa-sun'
	};

	// --- Dark Mode Toggle ---

	function getStoredScheme() {
		try { return localStorage.getItem(SCHEME_KEY); } catch (e) { return null; }
	}

	function setStoredScheme(mode) {
		try {
			if (mode === 'auto') {
				localStorage.removeItem(SCHEME_KEY);
			} else {
				localStorage.setItem(SCHEME_KEY, mode);
			}
		} catch (e) { /* storage unavailable */ }
	}

	function applyScheme(mode) {
		var html = document.documentElement;
		if (mode === 'dark') {
			html.setAttribute('data-novo-scheme', 'dark');
		} else if (mode === 'light') {
			html.setAttribute('data-novo-scheme', 'light');
		} else {
			html.removeAttribute('data-novo-scheme');
		}
	}

	function getCurrentMode() {
		var stored = getStoredScheme();
		if (stored === 'dark' || stored === 'light') return stored;
		return 'auto';
	}

	function cycleMode(current) {
		var idx = MODES.indexOf(current);
		return MODES[(idx + 1) % MODES.length];
	}

	function updateButtonIcon(btn, mode) {
		var icon = btn.querySelector('i, span.fas, span.fa');
		if (!icon) return;
		// Remove all mode icons
		Object.values(ICONS).forEach(function (cls) { icon.classList.remove(cls); });
		icon.classList.add(ICONS[mode] || ICONS.auto);
		btn.title = 'Color scheme: ' + mode.charAt(0).toUpperCase() + mode.slice(1);
	}

	function initDarkToggle() {
		// Apply stored preference immediately (already done in <head> ideally, but ensure here)
		var mode = getCurrentMode();
		applyScheme(mode);

		// Find injection target — login_block_other in top-right
		var target = document.querySelector('.login_block_other');
		if (!target) return;

		// Create toggle button
		var btn = document.createElement('div');
		btn.className = 'inline-block login_block_elem';
		btn.style.cssText = 'cursor:pointer;padding:0 8px;vertical-align:middle;';
		btn.innerHTML = '<span class="fas ' + (ICONS[mode] || ICONS.auto) + '" style="font-size:1.1em;opacity:0.8;"></span>';
		btn.title = 'Color scheme: ' + mode.charAt(0).toUpperCase() + mode.slice(1);

		btn.addEventListener('click', function () {
			mode = cycleMode(mode);
			applyScheme(mode);
			setStoredScheme(mode);
			updateButtonIcon(btn, mode);
		});

		// Insert before the first child of login_block_other
		target.insertBefore(btn, target.firstChild);
	}

	// --- Sticky Table Headers ---

	function initStickyHeaders() {
		var tables = document.querySelectorAll('table.liste');
		var minRows = 8;

		for (var i = 0; i < tables.length; i++) {
			var table = tables[i];

			// Skip tables inside modals
			if (table.closest('.ui-dialog')) continue;

			// Count data rows (tbody tr)
			var tbody = table.querySelector('tbody');
			var rowCount = tbody ? tbody.querySelectorAll('tr').length : table.querySelectorAll('tr').length;

			if (rowCount >= minRows) {
				table.classList.add('novo-sticky');
			}
		}
	}

	// --- Init ---

	function novoInit() {
		initDarkToggle();
		initStickyHeaders();
	}

	// Apply scheme early to prevent flash
	applyScheme(getCurrentMode());

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', novoInit);
	} else {
		novoInit();
	}
})();
