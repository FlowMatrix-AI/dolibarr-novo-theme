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

	$head = array();

	$head[0] = array(
		dol_buildpath('/novoux/admin/setup.php', 1),
		$langs->trans("Settings"),
		'settings',
		'',
		'',
		1
	);

	complete_head_from_modules($conf, $langs, null, $head, 0, 'novoux@novoux');
	complete_head_from_modules($conf, $langs, null, $head, 1, 'novoux@novoux');

	return $head;
}
