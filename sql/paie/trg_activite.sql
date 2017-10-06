/*
INSERT into pai_ref_postepaie_activite(activite_id,typejour_id) 
select a.id,t.id
from ref_activite a,ref_typejour t
where not exists(select null from pai_ref_postepaie_activite r where r.activite_id=a.id and r.typejour_id=t.id);
*/

DROP TRIGGER IF EXISTS activite_insert_ta;
CREATE TRIGGER activite_insert_ta
AFTER INSERT ON ref_activite FOR EACH ROW
BEGIN
  INSERT into pai_ref_postepaie_activite(activite_id,typejour_id) 
  SELECT NEW.id,t.id
  FROM ref_typejour t;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS activite_delete_tb;
CREATE TRIGGER activite_delete_tb
BEFORE DELETE ON ref_activite FOR EACH ROW
BEGIN
  DELETE from pai_ref_postepaie_activite where activite_id=OLD.id;
END;