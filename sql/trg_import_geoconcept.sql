DROP TRIGGER IF EXISTS import_geoconcept_insert_tb;
CREATE TRIGGER import_geoconcept_insert_tb BEFORE INSERT ON import_geoconcept
FOR EACH ROW SET NEW.date_optim=NOW();
