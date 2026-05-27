<?php
/* Copyright (C) 2025 FlowMatrix-AI
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/novoux/css/novo-inject.css.php
 * \ingroup    novoux
 * \brief      Injected CSS for runtime overrides (primary color, logo)
 */

if (!defined('ISLOADEDBYSTEELSHEET')) {
	if (!defined('NOTOKENRENEWAL')) {
		define('NOTOKENRENEWAL', '1');
	}
	if (!defined('NOREQUIREMENU')) {
		define('NOREQUIREMENU', '1');
	}
	if (!defined('NOREQUIREHTML')) {
		define('NOREQUIREHTML', '1');
	}
	if (!defined('NOREQUIREAJAX')) {
		define('NOREQUIREAJAX', '1');
	}

	$res = 0;
	if (!$res && file_exists("../../main.inc.php")) {
		$res = @include "../../main.inc.php";
	}
	if (!$res && file_exists("../../../main.inc.php")) {
		$res = @include "../../../main.inc.php";
	}
	if (!$res) {
		die("Include of main fails");
	}

	header('Content-Type: text/css');
}

// Per-user preference overrides (user param > admin const > default)
$palette = '';
if (!empty($user->conf->NOVOUX_USER_PALETTE)) {
	$palette = $user->conf->NOVOUX_USER_PALETTE;
}
if (empty($palette)) {
	$palette = getDolGlobalString('NOVOUX_PALETTE', 'default');
}
// Sanitize to prevent path traversal
$palette = preg_replace('/[^a-z0-9_-]/', '', $palette);
$paletteFile = DOL_DOCUMENT_ROOT.'/theme/novo/palettes/'.$palette.'.css';
if (file_exists($paletteFile)) {
	readfile($paletteFile);
}

// Load density variant (user param > admin const > default)
$density = '';
if (!empty($user->conf->NOVOUX_USER_DENSITY)) {
	$density = $user->conf->NOVOUX_USER_DENSITY;
}
if (empty($density)) {
	$density = getDolGlobalString('NOVOUX_DENSITY', 'default');
}
$density = preg_replace('/[^a-z0-9_-]/', '', $density);
if ($density !== 'default') {
	$variantFile = DOL_DOCUMENT_ROOT.'/theme/novo/variants/density-'.$density.'.css';
	if (file_exists($variantFile)) {
		readfile($variantFile);
	}
}

// Radius preset
$radiusPreset = getDolGlobalString('NOVOUX_RADIUS', 'default');
$radiusMap = array(
	'sharp'   => array('2px', '3px', '4px', '6px'),
	'rounded' => array('8px', '12px', '16px', '24px'),
	'pill'    => array('50px', '50px', '50px', '50px'),
);
if (isset($radiusMap[$radiusPreset])) {
	$r = $radiusMap[$radiusPreset];
	print ":root {\n";
	print "  --novo-radius-sm: ".$r[0].";\n";
	print "  --novo-radius-md: ".$r[1].";\n";
	print "  --novo-radius-lg: ".$r[2].";\n";
	print "  --novo-radius-xl: ".$r[3].";\n";
	print "}\n";
}

// Primary color override
$primaryOverride = getDolGlobalString('NOVOUX_PRIMARY_COLOR', '');
if (!empty($primaryOverride) && preg_match('/^#[0-9a-fA-F]{6}$/', $primaryOverride)) {
	print ":root {\n";
	print "  --novo-primary: ".$primaryOverride.";\n";
	print "}\n";
}

// Accent color override
$accentOverride = getDolGlobalString('NOVOUX_ACCENT_COLOR', '');
if (!empty($accentOverride) && preg_match('/^#[0-9a-fA-F]{6}$/', $accentOverride)) {
	print ":root { --novo-accent: ".$accentOverride."; }\n";
}

// Danger color override
$dangerOverride = getDolGlobalString('NOVOUX_DANGER_COLOR', '');
if (!empty($dangerOverride) && preg_match('/^#[0-9a-fA-F]{6}$/', $dangerOverride)) {
	print ":root { --novo-danger: ".$dangerOverride."; }\n";
}

// Logo URL override
$logoUrl = getDolGlobalString('NOVOUX_LOGO_URL', '');
if (!empty($logoUrl) && filter_var($logoUrl, FILTER_VALIDATE_URL)) {
	print "#img_logo { content: url('".dol_escape_htmltag($logoUrl)."'); max-height: 40px; }\n";
}

// Custom CSS (admin-defined, sanitized on save — re-sanitized here as defense-in-depth)
$customCss = getDolGlobalString('NOVOUX_CUSTOM_CSS', '');
if (!empty($customCss)) {
	$customCss = preg_replace('/<\/?script[^>]*>/i', '', $customCss);
	$customCss = preg_replace('/expression\s*\(/i', '', $customCss);
	$customCss = preg_replace('/url\s*\(\s*["\']?javascript:/i', 'url(blocked:', $customCss);
	$customCss = preg_replace('/@import\b/i', '', $customCss);
	$customCss = preg_replace('/url\s*\(\s*["\']?data:/i', 'url(blocked:', $customCss);
	$customCss = preg_replace('/-moz-binding\s*:/i', '-blocked:', $customCss);
	$customCss = preg_replace('/behavior\s*:/i', 'blocked:', $customCss);
	print "/* Custom CSS */\n";
	print $customCss."\n";
}
