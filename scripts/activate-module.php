<?php
/**
 * Activate the novoux module in a dev or CI Dolibarr the way Dolibarr does.
 *
 * Run inside the container:
 *   docker compose -f docker-compose.dev.yml exec -T web php /var/www/html/custom/novoux/../../scripts/activate-module.php
 * or, more usually, via the wrapper in scripts/seed-dev.sh.
 *
 * This calls modNovoux::init() rather than hand-writing llx_const rows, because
 * the exact constants an activation writes are not obvious and guessing them has
 * been wrong more than once: the seed previously missed module_parts['css']
 * entirely, and wrote it as a bare string when Dolibarr stores a JSON array.
 * Anything derived from the module descriptor belongs here, not in SQL.
 *
 * Not for production — Dolibarr installs modules through its own admin UI.
 */

define('NOLOGIN', 1);
define('NOCSRFCHECK', 1);
define('NOTOKENRENEWAL', 1);

$docroot = getenv('DOLI_DOCUMENT_ROOT') ?: '/var/www/html';
require_once $docroot.'/master.inc.php';

dol_include_once('/novoux/core/modules/modNovoux.class.php');

if (!class_exists('modNovoux')) {
	fwrite(STDERR, "modNovoux not found — is the module deployed under a path dol_include_once can see?\n");
	exit(1);
}

$module = new modNovoux($db);

// Remove first so a re-run is idempotent and picks up descriptor changes.
$module->remove('noboxes');

if ($module->init('noboxes') <= 0) {
	fwrite(STDERR, 'novoux activation failed: '.$module->error."\n");
	exit(1);
}

echo "novoux activated\n";
