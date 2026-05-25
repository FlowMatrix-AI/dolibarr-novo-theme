-- Force English language on fresh dev install (demo data defaults to French)
UPDATE llx_user SET lang = 'en_US' WHERE lang IS NULL OR lang = '' OR lang = 'fr_FR';
DELETE FROM llx_const WHERE name = 'MAIN_LANG_DEFAULT';
INSERT INTO llx_const (name, entity, value, type, visible) VALUES ('MAIN_LANG_DEFAULT', 0, 'en_US', 'chaine', 0);
