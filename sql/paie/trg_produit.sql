/*
INSERT into pai_ref_postepaie_supplement(produit_id) select p.id from produit p where p.type_id in (2,3);
*/

DROP TRIGGER IF EXISTS produit_insert_ta;
CREATE TRIGGER produit_insert_ta
AFTER INSERT ON produit FOR EACH ROW
BEGIN
  IF (NEW.type_id in (2,3)) THEN
    INSERT into pai_ref_postepaie_supplement(produit_id) values(NEW.id);
  END IF;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS produit_update_tb;
CREATE TRIGGER produit_update_tb
BEFORE UPDATE ON produit FOR EACH ROW
BEGIN
  IF (OLD.type_id in (2,3) AND NEW.type_id not in (2,3)) THEN
    DELETE from pai_ref_postepaie_supplement where produit_id=OLD.id;
  END IF;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS produit_update_ta;
CREATE TRIGGER produit_update_ta
AFTER UPDATE ON produit FOR EACH ROW
BEGIN
  IF (OLD.type_id not in (2,3) AND NEW.type_id in (2,3)) THEN
    INSERT into pai_ref_postepaie_supplement(produit_id) values(NEW.id);
  END IF;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS produit_delete_tb;
CREATE TRIGGER produit_delete_tb
BEFORE DELETE ON produit FOR EACH ROW
BEGIN
  DELETE from pai_ref_postepaie_supplement where produit_id=OLD.id;
END;