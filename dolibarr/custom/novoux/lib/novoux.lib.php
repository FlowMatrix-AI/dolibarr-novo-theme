<?php
/* Copyright (C) 2025 FlowMatrix-AI
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/novoux/lib/novoux.lib.php
 * \ingroup    novoux
 * \brief      Library for novoux admin pages
 */

/**
 * Prepare admin pages header
 *
 * @return array head array with tabs
 */
function novoux_admin_prepare_head()
{
	global $langs, $conf;

	$langs->load("novoux@novoux");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/novoux/admin/setup.php', 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'novoux@novoux');

	return $head;
}
