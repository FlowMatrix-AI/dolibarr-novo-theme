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

-- The novoux module itself is NOT activated here. Its constants are derived
-- from the module descriptor and are easy to get wrong by hand, so activation
-- runs through modNovoux::init() in scripts/activate-module.php instead.

-- Set Novo as the active theme
UPDATE llx_const SET value = 'novo' WHERE name = 'MAIN_THEME' AND entity = 1;
INSERT IGNORE INTO llx_const (name, value, type, entity, visible)
VALUES ('MAIN_THEME', 'novo', 'chaine', 1, 0);

-- Per-user preferences take precedence over the global constant, and the demo
-- data ships an explicit MAIN_THEME=eldy for the admin user. Drop the override
-- so the theme under test is the one actually rendered.
DELETE FROM llx_user_param WHERE param = 'MAIN_THEME';

-- Exercise the sidebar-collapse hook (ActionsNovoux::printMainArea only emits
-- when this is set), so the smoke suite can assert the hook actually fires.
INSERT IGNORE INTO llx_const (name, value, type, entity, visible)
VALUES ('NOVOUX_SIDEBAR_COLLAPSE', '1', 'chaine', 1, 0);
UPDATE llx_const SET value = '1' WHERE name = 'NOVOUX_SIDEBAR_COLLAPSE' AND entity = 1;
