<?php
/* Copyright (C) 2025 FlowMatrix-AI
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/novoux/admin/setup.php
 * \ingroup    novoux
 * \brief      Novoux module setup page
 */

// Load Dolibarr environment
$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/novoux/lib/novoux.lib.php');

// Load translation files
$langs->loadLangs(array("admin", "novoux@novoux"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Available palettes (read from tokens directory)
$availablePalettes = array();
$tokensDir = dol_buildpath('/novoux/theme/novo/palettes', 0);
if (is_dir($tokensDir)) {
	$files = scandir($tokensDir);
	foreach ($files as $file) {
		if (preg_match('/^([a-z0-9_-]+)\.css$/', $file, $m)) {
			$availablePalettes[] = $m[1];
		}
	}
}
if (empty($availablePalettes)) {
	$availablePalettes = array('default', 'slate', 'blue', 'green', 'warm', 'rose', 'indigo', 'teal');
}

/*
 * Actions
 */

$action = GETPOST('action', 'aZ09');

if ($action == 'update') {
	$token = GETPOST('token', 'alpha');
	if (!$token || $token != newToken()) {
		accessforbidden('Invalid CSRF token');
	}

	$error = 0;

	// Palette
	$palette = GETPOST('NOVOUX_PALETTE', 'alpha');
	if (!in_array($palette, $availablePalettes)) {
		$palette = 'default';
	}
	dolibarr_set_const($db, 'NOVOUX_PALETTE', $palette, 'chaine', 0, '', $conf->entity);

	// Primary color override
	$primaryColor = GETPOST('NOVOUX_PRIMARY_COLOR', 'alpha');
	if (!empty($primaryColor) && !preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor)) {
		setEventMessages($langs->trans("ErrorBadColor"), null, 'errors');
		$error++;
	}
	if (!$error) {
		dolibarr_set_const($db, 'NOVOUX_PRIMARY_COLOR', $primaryColor, 'chaine', 0, '', $conf->entity);
	}

	// Accent color override
	$accentColor = GETPOST('NOVOUX_ACCENT_COLOR', 'alpha');
	if (!empty($accentColor) && !preg_match('/^#[0-9a-fA-F]{6}$/', $accentColor)) {
		setEventMessages($langs->trans("ErrorBadColor"), null, 'errors');
		$error++;
	}
	if (!$error) {
		dolibarr_set_const($db, 'NOVOUX_ACCENT_COLOR', $accentColor, 'chaine', 0, '', $conf->entity);
	}

	// Danger color override
	$dangerColor = GETPOST('NOVOUX_DANGER_COLOR', 'alpha');
	if (!empty($dangerColor) && !preg_match('/^#[0-9a-fA-F]{6}$/', $dangerColor)) {
		setEventMessages($langs->trans("ErrorBadColor"), null, 'errors');
		$error++;
	}
	if (!$error) {
		dolibarr_set_const($db, 'NOVOUX_DANGER_COLOR', $dangerColor, 'chaine', 0, '', $conf->entity);
	}

	// Density
	$density = GETPOST('NOVOUX_DENSITY', 'alpha');
	if (!in_array($density, array('default', 'compact', 'spacious'))) {
		$density = 'default';
	}
	dolibarr_set_const($db, 'NOVOUX_DENSITY', $density, 'chaine', 0, '', $conf->entity);

	// Radius preset
	$radius = GETPOST('NOVOUX_RADIUS', 'alpha');
	if (!in_array($radius, array('sharp', 'default', 'rounded', 'pill'))) {
		$radius = 'default';
	}
	dolibarr_set_const($db, 'NOVOUX_RADIUS', $radius, 'chaine', 0, '', $conf->entity);

	// Dark mode behavior
	$darkMode = GETPOST('NOVOUX_DARK_MODE', 'alpha');
	if (!in_array($darkMode, array('disabled', 'auto', 'toggle', 'forced'))) {
		$darkMode = 'disabled';
	}
	dolibarr_set_const($db, 'NOVOUX_DARK_MODE', $darkMode, 'chaine', 0, '', $conf->entity);
	switch ($darkMode) {
		case 'auto':
			dolibarr_set_const($db, 'THEME_DARKMODEENABLED', '1', 'chaine', 0, '', $conf->entity);
			dolibarr_set_const($db, 'ALLOW_THEME_JS', '0', 'chaine', 0, '', $conf->entity);
			break;
		case 'toggle':
			dolibarr_set_const($db, 'THEME_DARKMODEENABLED', '1', 'chaine', 0, '', $conf->entity);
			dolibarr_set_const($db, 'ALLOW_THEME_JS', '1', 'chaine', 0, '', $conf->entity);
			break;
		case 'forced':
			dolibarr_set_const($db, 'THEME_DARKMODEENABLED', '2', 'chaine', 0, '', $conf->entity);
			dolibarr_set_const($db, 'ALLOW_THEME_JS', '0', 'chaine', 0, '', $conf->entity);
			break;
		default: // disabled
			dolibarr_set_const($db, 'THEME_DARKMODEENABLED', '0', 'chaine', 0, '', $conf->entity);
			dolibarr_set_const($db, 'ALLOW_THEME_JS', '0', 'chaine', 0, '', $conf->entity);
			break;
	}

	// Logo URL
	$logoUrl = GETPOST('NOVOUX_LOGO_URL', 'alpha');
	$logoUrl = trim($logoUrl);
	if (!empty($logoUrl) && !filter_var($logoUrl, FILTER_VALIDATE_URL)) {
		setEventMessages($langs->trans("ErrorBadUrl"), null, 'errors');
		$error++;
	}
	if (!$error) {
		dolibarr_set_const($db, 'NOVOUX_LOGO_URL', $logoUrl, 'chaine', 0, '', $conf->entity);
	}

	// Sidebar collapse
	$sidebarCollapse = GETPOST('NOVOUX_SIDEBAR_COLLAPSE', 'alpha');
	dolibarr_set_const($db, 'NOVOUX_SIDEBAR_COLLAPSE', ($sidebarCollapse ? '1' : '0'), 'chaine', 0, '', $conf->entity);
	// Sidebar collapse requires theme JS
	if ($sidebarCollapse) {
		dolibarr_set_const($db, 'ALLOW_THEME_JS', '1', 'chaine', 0, '', $conf->entity);
	}

	// Custom CSS
	$customCss = GETPOST('NOVOUX_CUSTOM_CSS', 'restricthtml');
	$customCss = preg_replace('/<\/?script[^>]*>/i', '', $customCss);
	$customCss = preg_replace('/expression\s*\(/i', '', $customCss);
	$customCss = preg_replace('/url\s*\(\s*["\']?javascript:/i', 'url(blocked:', $customCss);
	$customCss = preg_replace('/@import\b/i', '', $customCss);
	$customCss = preg_replace('/url\s*\(\s*["\']?data:/i', 'url(blocked:', $customCss);
	$customCss = preg_replace('/-moz-binding\s*:/i', '-blocked:', $customCss);
	$customCss = preg_replace('/behavior\s*:/i', 'blocked:', $customCss);
	if (strlen($customCss) > 4096) {
		$customCss = substr($customCss, 0, 4096);
		setEventMessages($langs->trans("NovouzCustomCssTruncated"), null, 'warnings');
	}
	dolibarr_set_const($db, 'NOVOUX_CUSTOM_CSS', $customCss, 'chaine', 0, '', $conf->entity);

	if (!$error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	}
}

/*
 * View
 */

$page_name = "NovouzSetup";
llxHeader('', $langs->trans($page_name), '', '', 0, 0, '', '', '', 'mod-novoux page-admin-setup');

// Subheader
$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Tabs
$head = novoux_admin_prepare_head();
print dol_get_fiche_head($head, 'settings', $langs->trans($page_name), -1, 'fa-palette');

// Info: per-user overrides
print info_admin($langs->trans("NovouzUserPrefsInfo"));

// Form
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>';

// Palette selection
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzPalette").'</td>';
print '<td>';
print '<select name="NOVOUX_PALETTE" class="flat minwidth200">';
$currentPalette = getDolGlobalString('NOVOUX_PALETTE', 'default');
foreach ($availablePalettes as $p) {
	$selected = ($p == $currentPalette) ? ' selected' : '';
	print '<option value="'.dol_escape_htmltag($p).'"'.$selected.'>'.ucfirst(dol_escape_htmltag($p)).'</option>';
}
print '</select>';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzPaletteHelp").'</span>';
print '</td>';
print '</tr>';

// Primary color override
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzPrimaryColor").'</td>';
print '<td>';
$currentColor = getDolGlobalString('NOVOUX_PRIMARY_COLOR', '');
print '<input type="color" name="NOVOUX_PRIMARY_COLOR" value="'.dol_escape_htmltag($currentColor ? $currentColor : '#3b82f6').'" class="flat">';
print ' <input type="text" name="NOVOUX_PRIMARY_COLOR" value="'.dol_escape_htmltag($currentColor).'" class="flat minwidth150" placeholder="#3b82f6">';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzPrimaryColorHelp").'</span>';
print '</td>';
print '</tr>';

// Accent color override
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzAccentColor").'</td>';
print '<td>';
$currentAccent = getDolGlobalString('NOVOUX_ACCENT_COLOR', '');
print '<input type="color" name="NOVOUX_ACCENT_COLOR" value="'.dol_escape_htmltag($currentAccent ? $currentAccent : '#8b5cf6').'" class="flat">';
print ' <input type="text" name="NOVOUX_ACCENT_COLOR" value="'.dol_escape_htmltag($currentAccent).'" class="flat minwidth150" placeholder="#8b5cf6">';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzAccentColorHelp").'</span>';
print '</td>';
print '</tr>';

// Danger color override
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzDangerColor").'</td>';
print '<td>';
$currentDanger = getDolGlobalString('NOVOUX_DANGER_COLOR', '');
print '<input type="color" name="NOVOUX_DANGER_COLOR" value="'.dol_escape_htmltag($currentDanger ? $currentDanger : '#ef4444').'" class="flat">';
print ' <input type="text" name="NOVOUX_DANGER_COLOR" value="'.dol_escape_htmltag($currentDanger).'" class="flat minwidth150" placeholder="#ef4444">';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzDangerColorHelp").'</span>';
print '</td>';
print '</tr>';

// Density
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzDensity").'</td>';
print '<td>';
$currentDensity = getDolGlobalString('NOVOUX_DENSITY', 'default');
$densityOptions = array('compact' => 'Compact', 'default' => 'Default', 'spacious' => 'Spacious');
foreach ($densityOptions as $dval => $dlabel) {
	$checked = ($dval == $currentDensity) ? ' checked' : '';
	print '<label style="margin-right: 16px; cursor: pointer;"><input type="radio" name="NOVOUX_DENSITY" value="'.dol_escape_htmltag($dval).'"'.$checked.'> '.$dlabel.'</label>';
}
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzDensityHelp").'</span>';
print '</td>';
print '</tr>';

// Radius preset
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzRadius").'</td>';
print '<td>';
$currentRadius = getDolGlobalString('NOVOUX_RADIUS', 'default');
print '<select name="NOVOUX_RADIUS" class="flat minwidth200">';
$radiusOptions = array('sharp' => 'Sharp (2–6px)', 'default' => 'Default (4–12px)', 'rounded' => 'Rounded (8–24px)', 'pill' => 'Pill (50px)');
foreach ($radiusOptions as $rval => $rlabel) {
	$selected = ($rval == $currentRadius) ? ' selected' : '';
	print '<option value="'.dol_escape_htmltag($rval).'"'.$selected.'>'.$rlabel.'</option>';
}
print '</select>';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzRadiusHelp").'</span>';
print '</td>';
print '</tr>';

// Dark mode behavior
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzDarkMode").'</td>';
print '<td>';
$currentDark = getDolGlobalString('NOVOUX_DARK_MODE', 'disabled');
$darkOptions = array(
	'disabled' => $langs->trans("NovouzDarkDisabled"),
	'auto' => $langs->trans("NovouzDarkAuto"),
	'toggle' => $langs->trans("NovouzDarkToggle"),
	'forced' => $langs->trans("NovouzDarkForced"),
);
print '<select name="NOVOUX_DARK_MODE" class="flat minwidth200">';
foreach ($darkOptions as $dval => $dlabel) {
	$selected = ($dval == $currentDark) ? ' selected' : '';
	print '<option value="'.dol_escape_htmltag($dval).'"'.$selected.'>'.dol_escape_htmltag($dlabel).'</option>';
}
print '</select>';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzDarkModeHelp").'</span>';
print '</td>';
print '</tr>';

// Sidebar collapse
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzSidebarCollapse").'</td>';
print '<td>';
$currentSidebar = getDolGlobalString('NOVOUX_SIDEBAR_COLLAPSE', '0');
$checked = ($currentSidebar == '1') ? ' checked' : '';
print '<input type="checkbox" name="NOVOUX_SIDEBAR_COLLAPSE" value="1"'.$checked.'>';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzSidebarCollapseHelp").'</span>';
print '</td>';
print '</tr>';

// Logo URL
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzLogoUrl").'</td>';
print '<td>';
$currentLogo = getDolGlobalString('NOVOUX_LOGO_URL', '');
print '<input type="url" name="NOVOUX_LOGO_URL" value="'.dol_escape_htmltag($currentLogo).'" class="flat minwidth400" placeholder="https://example.com/logo.png">';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzLogoUrlHelp").'</span>';
print '</td>';
print '</tr>';

// Custom CSS
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzCustomCss").'</td>';
print '<td>';
$currentCss = getDolGlobalString('NOVOUX_CUSTOM_CSS', '');
print '<textarea name="NOVOUX_CUSTOM_CSS" rows="8" class="flat" style="width:100%;max-width:600px;font-family:monospace;font-size:12px;">'.dol_escape_htmltag($currentCss).'</textarea>';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzCustomCssHelp").'</span>';
print '</td>';
print '</tr>';

print '</table>';

print '<br><div class="center">';
print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
print '</div>';

print '</form>';

print dol_get_fiche_end();

llxFooter();
$db->close();
