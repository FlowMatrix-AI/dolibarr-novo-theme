-- Tidy the Dolibarr demo data for store/listing screenshots.
--
-- The demo dump ships placeholder third parties named aaa/bbb/ccc which look
-- unfinished in a screenshot. This renames them to plausible companies. It is
-- presentation only — run it after seed-dev.sh, never as part of the test seed,
-- because the smoke suite should exercise the stock demo data.

UPDATE llx_societe SET nom = 'Northwind Logistics'   WHERE nom = 'aaa'        LIMIT 1;
UPDATE llx_societe SET nom = 'Harbour Analytics'     WHERE nom = 'aaa'        LIMIT 1;
UPDATE llx_societe SET nom = 'Meridian Foods'        WHERE nom = 'aaaincash'  LIMIT 1;
UPDATE llx_societe SET nom = 'Lantern Design Studio' WHERE nom = 'aaainlux'   LIMIT 1;
UPDATE llx_societe SET nom = 'Coastal Engineering'   WHERE nom = 'bbb'        LIMIT 1;
UPDATE llx_societe SET nom = 'Ridgeway Consulting'   WHERE nom = 'bbb'        LIMIT 1;
UPDATE llx_societe SET nom = 'Alder & Finch Ltd'     WHERE nom = 'bbb'        LIMIT 1;
UPDATE llx_societe SET nom = 'Copperfield Retail'    WHERE nom = 'ccc'        LIMIT 1;

-- Catch any remaining placeholder rows the named updates above did not cover.
UPDATE llx_societe SET nom = CONCAT('Demo Client ', rowid)
 WHERE nom REGEXP '^[a-z]{3}$';

-- The demo enables ~100 modules, which overflows the top menu and truncates
-- every label ("Hom", "Mem", "Third"). That is upstream behaviour — eldy does
-- the same — but it reads as broken in a store screenshot. Disable the modules
-- that add a top-menu entry without being core business flow. The ones that
-- feed dashboard widgets (proposals, orders, invoices, contracts, tickets,
-- members, expenses, leaves) stay enabled.
UPDATE llx_const SET value = '0' WHERE entity = 1 AND name IN (
  'MAIN_MODULE_TAKEPOS', 'MAIN_MODULE_SELLYOURSAAS', 'MAIN_MODULE_WEBSITE',
  'MAIN_MODULE_COLLAB', 'MAIN_MODULE_MODULEBUILDER', 'MAIN_MODULE_AI',
  'MAIN_MODULE_BOOKCAL', 'MAIN_MODULE_ALUMNI', 'MAIN_MODULE_KNOWLEDGEMANAGEMENT',
  'MAIN_MODULE_RECRUITMENT', 'MAIN_MODULE_EVENTORGANIZATION',
  'MAIN_MODULE_PARTNERSHIP', 'MAIN_MODULE_MARKETPLACE', 'MAIN_MODULE_WEBPORTAL',
  'MAIN_MODULE_EXTERNALSITE', 'MAIN_MODULE_OPENSURVEY', 'MAIN_MODULE_DAV',
  'MAIN_MODULE_ASSET', 'MAIN_MODULE_MRP', 'MAIN_MODULE_HRM', 'MAIN_MODULE_ECM',
  'MAIN_MODULE_ADHERENT', 'MAIN_MODULE_DON', 'MAIN_MODULE_AAA'
);
