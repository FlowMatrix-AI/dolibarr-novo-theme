<?php
/* Copyright (C) 2025 FlowMatrix-AI
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/novoux/admin/about.php
 * \ingroup    novoux
 * \brief      About page of module NovouX
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


/*
 * View
 */

$page_name = "NovouxAbout";

llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Tabs
$head = novoux_admin_prepare_head();
print dol_get_fiche_head($head, 'about', $langs->trans($page_name), -1, 'fa-palette');

// Module info
dol_include_once('/novoux/core/modules/modNovoux.class.php');
$module = new modNovoux($db);

print '<div class="fichecenter">';

print '<div class="underbanner clearboth"></div>';
print '<table class="border centpercent tableforfield">';

print '<tr><td class="titlefield">'.$langs->trans("Version").'</td><td>'.$module->version.'</td></tr>';
print '<tr><td>'.$langs->trans("Author").'</td><td>'.$module->editor_name.'</td></tr>';
print '<tr><td>'.$langs->trans("Website").'</td><td><a href="'.$module->editor_url.'" target="_blank" rel="noopener noreferrer">'.$module->editor_url.'</a></td></tr>';
print '<tr><td>'.$langs->trans("License").'</td><td>GPL-3.0-or-later</td></tr>';
print '<tr><td>'.$langs->trans("Compatibility").'</td><td>Dolibarr &ge; 21.0 &mdash; PHP &ge; 7.4</td></tr>';

print '</table>';

print '<br>';
print '<h3>'.$langs->trans("Description").'</h3>';
print '<p>'.$langs->trans("NovouxDescriptionLong").'</p>';

print '<h3>'.$langs->trans("Features").'</h3>';
print '<ul>';
print '<li>8 colour palettes (default, slate, blue, green, warm, rose, indigo, teal)</li>';
print '<li>Dark mode (auto / toggle / forced)</li>';
print '<li>3 density levels (compact, default, spacious)</li>';
print '<li>Collapsible sidebar</li>';
print '<li>Custom CSS injection</li>';
print '<li>Per-user theme preferences</li>';
print '</ul>';

print '</div>';

print dol_get_fiche_end();

llxFooter();
$db->close();
