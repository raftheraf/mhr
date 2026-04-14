-- MHR Migration 0001
-- Data: 2026-04-14
-- Descrizione: utente_abilitato in mhr_sources da mediumint(9) a varchar(255)
--              per supportare associazione multipla di camerieri allo stesso tavolo
-- #database_type: common

ALTER TABLE `#prefix#sources`
  MODIFY COLUMN `utente_abilitato` VARCHAR(255) NOT NULL DEFAULT '';
