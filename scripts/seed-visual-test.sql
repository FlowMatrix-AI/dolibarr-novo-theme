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

-- Set Novo as the active theme
UPDATE llx_const SET value = 'novo' WHERE name = 'MAIN_THEME' AND entity = 1;
INSERT IGNORE INTO llx_const (name, value, type, entity, visible)
VALUES ('MAIN_THEME', 'novo', 'chaine', 1, 0);
