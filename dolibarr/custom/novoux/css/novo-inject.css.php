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

// Primary color override
$primaryOverride = getDolGlobalString('NOVOUX_PRIMARY_COLOR', '');
if (!empty($primaryOverride) && preg_match('/^#[0-9a-fA-F]{6}$/', $primaryOverride)) {
	print ":root {\n";
	print "  --novo-primary: ".$primaryOverride.";\n";
	print "}\n";
}

// Logo URL override
$logoUrl = getDolGlobalString('NOVOUX_LOGO_URL', '');
if (!empty($logoUrl) && filter_var($logoUrl, FILTER_VALIDATE_URL)) {
	print "#img_logo { content: url('".dol_escape_htmltag($logoUrl)."'); max-height: 40px; }\n";
}
