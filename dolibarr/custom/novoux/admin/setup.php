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
$tokensDir = DOL_DOCUMENT_ROOT.'/theme/novo/palettes';
if (is_dir($tokensDir)) {
	$files = scandir($tokensDir);
	foreach ($files as $file) {
		if (preg_match('/^([a-z0-9_-]+)\.css$/', $file, $m)) {
			$availablePalettes[] = $m[1];
		}
	}
}
if (empty($availablePalettes)) {
	$availablePalettes = array('default', 'slate', 'blue', 'green', 'warm');
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

// Logo URL
print '<tr class="oddeven">';
print '<td>'.$langs->trans("NovouzLogoUrl").'</td>';
print '<td>';
$currentLogo = getDolGlobalString('NOVOUX_LOGO_URL', '');
print '<input type="url" name="NOVOUX_LOGO_URL" value="'.dol_escape_htmltag($currentLogo).'" class="flat minwidth400" placeholder="https://example.com/logo.png">';
print '<br><span class="opacitymedium small">'.$langs->trans("NovouzLogoUrlHelp").'</span>';
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
