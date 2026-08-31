-- Enable modules needed for visual testing screenshots
-- Run against the dev Docker instance after first boot

INSERT IGNORE INTO llx_const (name, value, type, entity, visible)
VALUES
  ('MAIN_MODULE_SOCIETE', '1', 'chaine', 1, 0),
  ('MAIN_MODULE_FACTURE', '1', 'chaine', 1, 0),
  ('MAIN_MODULE_PROJET', '1', 'chaine', 1, 0),
  ('MAIN_MODULE_HOLIDAY', '1', 'chaine', 1, 0),
  ('MAIN_MODULE_PRODUCT', '1', 'chaine', 1, 0),
  ('MAIN_MODULE_AGENDA', '1', 'chaine', 1, 0),
  ('MAIN_MODULE_HRM', '1', 'chaine', 1, 0);

-- Activate the novoux module. These mirror the module_parts and const entries
-- that modNovoux::init() writes on a real activation:
--   _THEME  Dolibarr discovers the bundled theme; without it MAIN_THEME=novo
--           cannot resolve and Dolibarr silently falls back to eldy
--   _CSS    injects novo-inject.css.php, the palette/density/logo override
--           layer — without it that stylesheet is dead on every page
--
-- MAIN_MODULE_NOVOUX_HOOKS is deliberately not seeded: module_parts['hooks'] in
-- modNovoux.class.php uses the array-of-arrays form, which HookManager cannot
-- read, so ActionsNovoux does not fire on a real activation either. Seeding a
-- working value here would make the test environment diverge from production
-- and hide that bug rather than surface it.
INSERT IGNORE INTO llx_const (name, value, type, entity, visible)
VALUES
  ('MAIN_MODULE_NOVOUX', '1', 'chaine', 1, 0),
  ('MAIN_MODULE_NOVOUX_THEME', '1', 'chaine', 1, 0),
  ('MAIN_MODULE_NOVOUX_CSS', '/novoux/css/novo-inject.css.php', 'chaine', 1, 0),
  ('NOVOUX_PALETTE', 'default', 'chaine', 1, 0);

-- Set Novo as the active theme
UPDATE llx_const SET value = 'novo' WHERE name = 'MAIN_THEME' AND entity = 1;
INSERT IGNORE INTO llx_const (name, value, type, entity, visible)
VALUES ('MAIN_THEME', 'novo', 'chaine', 1, 0);

-- Per-user preferences take precedence over the global constant, and the demo
-- data ships an explicit MAIN_THEME=eldy for the admin user. Drop the override
-- so the theme under test is the one actually rendered.
DELETE FROM llx_user_param WHERE param = 'MAIN_THEME';
