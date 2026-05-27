<?php
/* Copyright (C) 2025 FlowMatrix-AI
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/novoux/class/actions_novoux.class.php
 * \ingroup    novoux
 * \brief      Hook actions for the Novoux module
 */

/**
 * Class ActionsNovoux
 *
 * Hook class for injecting Novo theme runtime attributes.
 */
class ActionsNovoux
{
	/**
	 * @var DoliDB Database handler
	 */
	public $db;

	/**
	 * @var string Hook results
	 */
	public $resprints = '';

	/**
	 * @var array Hook results array
	 */
	public $results = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Hook: printMainArea — inject sidebar collapse data attribute on body.
	 * Fires after <body> opens, before DOMContentLoaded.
	 *
	 * @param array  $parameters Hook parameters
	 * @param object $object     Current object (unused)
	 * @param string $action     Current action (unused)
	 * @return int               0 = OK
	 */
	public function printMainArea($parameters, &$object, &$action)
	{
		global $conf;

		if (getDolGlobalString('NOVOUX_SIDEBAR_COLLAPSE') == '1') {
			$this->resprints = '<script>document.body.dataset.novoSidebarCollapse="1";</script>'."\n";
		}

		return 0;
	}
}
