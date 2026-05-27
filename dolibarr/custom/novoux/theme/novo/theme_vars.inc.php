<?php
/* Copyright (C) 2004-2017	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2006		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2007-2017	Regis Houssin			<regis.houssin@inodbox.com>
 * Copyright (C) 2011		Philippe Grand			<philippe.grand@atoo-net.com>
 * Copyright (C) 2012		Juanjo Menent			<jmenent@2byte.es>
 * Copyright (C) 2018       Ferran Marcet           <fmarcet@2byte.es>
 * Copyright (C) 2021-2023  Anthony Berton          <anthony.berton@bb2a.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FI8TNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/theme/novo/theme_vars.inc.php
 *	\brief      File to declare variables of CSS style sheet
 *  \ingroup    core
 *
 *  To include file, do this:
 *              $var_file = DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/theme_vars.inc.php';
 *              if (is_readable($var_file)) include $var_file;
 */

global $theme_bordercolor, $theme_datacolor, $theme_bgcolor, $theme_bgcoloronglet;
$theme_bordercolor = array(226, 232, 240); // slate-200
$theme_datacolor = array(array(59, 130, 246), array(139, 92, 246), array(245, 158, 11), array(16, 185, 129), array(236, 72, 153), array(99, 102, 241), array(14, 165, 233), array(244, 63, 94), array(100, 116, 139), array(34, 197, 94), array(168, 85, 247), array(6, 182, 212), array(249, 115, 22), array(132, 204, 22));
if (!defined('ISLOADEDBYSTEELSHEET')) {	// File is run after an include of a php page, not by the style sheet, if the constant is not defined.
	if (getDolGlobalString('MAIN_OPTIMIZEFORCOLORBLIND')) { // user is loaded by dolgraph.class.php
		if (getDolGlobalString('MAIN_OPTIMIZEFORCOLORBLIND') == 'flashy') {
			$theme_datacolor = array(array(157, 56, 191), array(0, 147, 183), array(250, 190, 30), array(221, 75, 57), array(0, 166, 90), array(140, 140, 220), array(190, 120, 120), array(190, 190, 100), array(115, 125, 150), array(100, 170, 20), array(150, 135, 125), array(85, 135, 150), array(150, 135, 80), array(150, 80, 150));
		} else {
			// for now we use the same configuration for all types of color blind
			$theme_datacolor = array(array(248, 220, 1), array(9, 85, 187), array(42, 208, 255), array(0, 0, 0), array(169, 169, 169), array(253, 102, 136), array(120, 154, 190), array(146, 146, 55), array(0, 52, 251), array(196, 226, 161), array(222, 160, 41), array(85, 135, 150), array(150, 135, 80), array(150, 80, 150));
		}
	}
}

$theme_bgcolor = array(hexdec('F4'), hexdec('F4'), hexdec('F4'));
$theme_bgcoloronglet = array(hexdec('DE'), hexdec('E7'), hexdec('EC'));

// Colors — Novo palette
$colorbackbody = '248,250,252'; // --novo-bg slate-50 #f8fafc
$colorbackhmenu1 = '15,23,42'; // topmenu — slate-900
$colorbackvmenu1 = '255,255,255'; // vmenu — white surface
$colortopbordertitle1 = '226,232,240'; // --novo-border slate-200
$colorbacktitle1 = '241,245,249'; // slate-100 #f1f5f9
$colorbacktabcard1 = '255,255,255'; // card — white
$colorbacktabactive = '241,245,249'; // slate-100
$colorbacklineimpair1 = '255,255,255'; // line impair
$colorbacklineimpair2 = '255,255,255'; // line impair
$colorbacklinepair1 = '248,250,252'; // line pair — slate-50
$colorbacklinepair2 = '248,250,252'; // line pair — slate-50
$colorbacklinepairhover = '241,245,249'; // line hover — slate-100
$colorbacklinepairchecked = '219,234,254'; // line checked — blue-100
$colorbacklinebreak = '241,245,249'; // line break — slate-100
$colortexttitlenotab = '59,130,246'; // --novo-primary blue-500 #3b82f6
$colortexttitlenotab2 = '139,92,246'; // --novo-accent violet-500
$colortexttitle = '30,41,59'; // slate-800
$colortexttitlelink = '59,130,246'; // blue-500
$colortext = '15,23,42'; // --novo-text slate-900
$colortextlink = '59,130,246'; // blue-500
$fontsize = '0.875em'; // 14px base
$fontsizesmaller = '0.75em';
$topMenuFontSize = '0.875em';
$toolTipBgColor = 'rgba(15, 23, 42, 0.95)'; // dark tooltip
$toolTipFontColor = '#f1f5f9'; // light text on dark tooltip
$butactionbg = '59, 130, 246'; // --novo-primary blue-500
$textbutaction = '255, 255, 255';

// text color — Novo status palette
$textSuccess   = '#10b981'; // emerald-500
$colorblind_deuteranopes_textSuccess = '#37de5d';
$textWarning   = '#f59e0b'; // amber-500
$textDanger    = '#ef4444'; // red-500
$colorblind_deuteranopes_textWarning = $textWarning;


// Badges colors — Novo
$badgePrimary   = '#3b82f6'; // blue-500
$badgeSecondary = '#64748b'; // slate-500
$badgeInfo      = '#6366f1'; // indigo-500
$badgeSuccess   = '#10b981'; // emerald-500
$badgeWarning   = '#f59e0b'; // amber-500
$badgeDanger    = '#ef4444'; // red-500
$badgeDark      = '#1e293b'; // slate-800
$badgeLight     = '#f1f5f9'; // slate-100

// badge color adjustment for color blind
$colorblind_deuteranopes_badgeSuccess   = '#37de5d'; //! text color black
$colorblind_deuteranopes_badgeSuccess_textColor7 = '#000';
$colorblind_deuteranopes_badgeWarning   = '#e4e411';
$colorblind_deuteranopes_badgeDanger    = $badgeDanger; // currently not tested with a color blind people so use default color

/* default color for status : After a quick check, somme status can have opposite function according to objects
*  So this badges status uses default value according to theme eldy status img
*  TODO: use color definition vars above for define badges color status X -> example $badgeStatusValidate, $badgeStatusClosed, $badgeStatusActive ....
*/
$badgeStatus0 = '#cbd3d3'; // draft
$badgeStatus1 = '#bc9526'; // validated
$badgeStatus1b = '#bc9526'; // validated
$badgeStatus2 = '#9c9c26'; // approved
$badgeStatus3 = '#bca52b';
$badgeStatus4 = '#25a580'; // Color ok
$badgeStatus4b = '#25a580'; // Color ok
$badgeStatus5 = '#cad2d2';
$badgeStatus6 = '#cad2d2';
$badgeStatus7 = '#25a580';
$badgeStatus8 = '#994013';
$badgeStatus9 = '#e7f0f0';
$badgeStatus10 = '#993013';
$badgeStatus11 = '#15a540';

// status color adjustment for color blind
$colorblind_deuteranopes_badgeStatus4 = $colorblind_deuteranopes_badgeStatus7 = $colorblind_deuteranopes_badgeSuccess; //! text color black
$colorblind_deuteranopes_badgeStatus_textColor4 = $colorblind_deuteranopes_badgeStatus_textColor7 = '#000';
$colorblind_deuteranopes_badgeStatus1 = $colorblind_deuteranopes_badgeWarning;
$colorblind_deuteranopes_badgeStatus_textColor1 = '#000';
