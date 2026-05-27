<?php
/* Copyright (C) 2025 FlowMatrix-AI
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/novoux/user_prefs.php
 * \ingroup    novoux
 * \brief      Per-user theme preferences (palette + density)
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
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';

// Load translation files
$langs->loadLangs(array("users", "novoux@novoux"));

// Get target user
$id = GETPOSTINT('id');
if (empty($id)) {
	$id = $user->id;
}

// Security: users can only edit their own prefs, unless admin
if ($id != $user->id && !$user->admin) {
	accessforbidden();
}

// Load target user object for tabs
$targetuser = new User($db);
$targetuser->fetch($id);

// Available palettes
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

$availableDensities = array('default', 'compact', 'spacious');

/*
 * Actions
 */

$action = GETPOST('action', 'aZ09');

if ($action == 'update') {
	$token = GETPOST('token', 'alpha');
	if (!$token || $token != newToken()) {
		accessforbidden('Invalid CSRF token');
	}

	$palette = GETPOST('NOVOUX_USER_PALETTE', 'alpha');
	if (!empty($palette) && !in_array($palette, $availablePalettes)) {
		$palette = '';
	}

	$density = GETPOST('NOVOUX_USER_DENSITY', 'alpha');
	if (!empty($density) && !in_array($density, $availableDensities)) {
		$density = '';
	}

	dol_set_user_param($db, $conf, $targetuser, array(
		'NOVOUX_USER_PALETTE' => $palette,
		'NOVOUX_USER_DENSITY' => $density,
	));

	setEventMessages($langs->trans("RecordModifiedSuccessfully"), null, 'mesgs');
	header('Location: '.$_SERVER['PHP_SELF'].'?id='.$id);
	exit;
}

if ($action == 'reset') {
	$token = GETPOST('token', 'alpha');
	if (!$token || $token != newToken()) {
		accessforbidden('Invalid CSRF token');
	}

	dol_set_user_param($db, $conf, $targetuser, array(
		'NOVOUX_USER_PALETTE' => '',
		'NOVOUX_USER_DENSITY' => '',
	));

	setEventMessages($langs->trans("RecordModifiedSuccessfully"), null, 'mesgs');
	header('Location: '.$_SERVER['PHP_SELF'].'?id='.$id);
	exit;
}

/*
 * View
 */

$title = $langs->trans("NovouzUserPrefs");
llxHeader('', $title);

// User tabs
$head = user_prepare_head($targetuser);
print dol_get_fiche_head($head, 'novoux_prefs', $langs->trans("User"), -1, 'user');

// Reload user params to show current values
$targetuser->loadPersonalConf();
$currentPalette = isset($targetuser->conf->NOVOUX_USER_PALETTE) ? $targetuser->conf->NOVOUX_USER_PALETTE : '';
$currentDensity = isset($targetuser->conf->NOVOUX_USER_DENSITY) ? $targetuser->conf->NOVOUX_USER_DENSITY : '';

// Show admin defaults for reference
$adminPalette = getDolGlobalString('NOVOUX_PALETTE', 'default');
$adminDensity = getDolGlobalString('NOVOUX_DENSITY', 'default');

print '<div class="underbanner clearboth"></div>';
print '<div class="fichecenter">';

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$id.'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="id" value="'.$id.'">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("NovouzUserPrefs").'</td></tr>';

// Palette
print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("NovouzPalette").'</td><td>';
print '<select name="NOVOUX_USER_PALETTE" class="flat minwidth200">';
print '<option value="">'.$langs->trans("Default").' ('.$adminPalette.')</option>';
foreach ($availablePalettes as $p) {
	$selected = ($currentPalette === $p) ? ' selected' : '';
	print '<option value="'.dol_escape_htmltag($p).'"'.$selected.'>'.ucfirst(dol_escape_htmltag($p)).'</option>';
}
print '</select>';
print '</td></tr>';

// Density
print '<tr class="oddeven"><td>'.$langs->trans("NovouzDensity").'</td><td>';
print '<select name="NOVOUX_USER_DENSITY" class="flat minwidth200">';
print '<option value="">'.$langs->trans("Default").' ('.$adminDensity.')</option>';
foreach ($availableDensities as $d) {
	$selected = ($currentDensity === $d) ? ' selected' : '';
	print '<option value="'.dol_escape_htmltag($d).'"'.$selected.'>'.ucfirst(dol_escape_htmltag($d)).'</option>';
}
print '</select>';
print '</td></tr>';

print '</table>';

print '<div class="center">';
print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
print ' &nbsp; ';
print '<a class="button button-cancel" href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=reset&token='.newToken().'">'.$langs->trans("ResetToDefault").'</a>';
print '</div>';

print '</form>';

print '</div>';

print dol_get_fiche_end();

llxFooter();
$db->close();
