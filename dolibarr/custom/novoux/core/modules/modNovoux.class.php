<?php
/* Copyright (C) 2025 FlowMatrix-AI
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \defgroup   novoux     Module Novoux
 * \brief      Novoux companion module for the Novo theme.
 *
 * \file       htdocs/custom/novoux/core/modules/modNovoux.class.php
 * \ingroup    novoux
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 * Module descriptor for Novoux
 */
class modNovoux extends DolibarrModules
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;

		$this->db = $db;

		$this->numero = 500200;
		$this->rights_class = 'novoux';
		$this->family = 'interface';
		$this->module_position = '90';
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = "NovouzDescription";
		$this->descriptionlong = "NovouzDescriptionLong";
		$this->editor_name = 'FlowMatrix-AI';
		$this->editor_url = 'https://github.com/FlowMatrix-AI/dolibarr-ui-skin';
		$this->editor_squarred_logo = 'novoux_512.png@novoux';
		$this->version = '1.2.0';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto = 'fa-palette';

		$this->module_parts = array(
			'triggers' => 0,
			'login' => 0,
			'substitutions' => 0,
			'menus' => 0,
			'tpl' => 0,
			'barcode' => 0,
			'models' => 0,
			'printing' => 0,
			'theme' => 0,
			'css' => array(
				'/novoux/css/novo-inject.css.php',
			),
			'js' => array(),
			'hooks' => array(),
			'moduleforexternal' => 0,
			'websitetemplates' => 0,
		);

		$this->dirs = array();
		$this->config_page_url = array("setup.php@novoux");

		$this->hidden = false;
		$this->depends = array();
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->langfiles = array("novoux@novoux");

		$this->phpmin = array(7, 4);
		$this->need_dolibarr_version = array(21, 0);
		$this->need_javascript_ajax = 0;

		$this->warnings_activation = array();
		$this->warnings_activation_ext = array();

		// Tabs added to existing objects
		$this->tabs = array(
			'user:+novoux_prefs:NovouzThemePrefs:novoux@novoux:/novoux/user_prefs.php?id=__ID__',
		);

		// Constants set on module activation
		$this->const = array(
			0 => array('NOVOUX_PALETTE', 'chaine', 'default', 'Active palette name for Novo theme', 1, 'current', 0),
			1 => array('NOVOUX_PRIMARY_COLOR', 'chaine', '', 'Override primary brand color (hex)', 1, 'current', 0),
			2 => array('NOVOUX_LOGO_URL', 'chaine', '', 'Client logo URL override', 1, 'current', 0),
		);

		// No permissions, menus, or boxes needed
		$this->rights = array();
		$this->menu = array();
	}

	/**
	 * Function called when module is enabled
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		$result = $this->_load_tables('/install/mysql/', 'novoux');
		$sql = array();
		return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled
	 *
	 * @param string $options Options when disabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
