-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS groupe_tournee_insert_tb;
CREATE TRIGGER groupe_tournee_insert_tb
BEFORE INSERT ON groupe_tournee
FOR EACH ROW
BEGIN
  SET NEW.date_creation=NOW();
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS groupe_tournee_update_tb;
CREATE TRIGGER groupe_tournee_update_tb
BEFORE UPDATE ON groupe_tournee
FOR EACH ROW
BEGIN
  SET NEW.date_modif=NOW();
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS groupe_tournee_update_ta;
CREATE TRIGGER groupe_tournee_update_ta
AFTER UPDATE ON groupe_tournee
FOR EACH ROW
BEGIN
  IF (OLD.code<>NEW.code or OLD.depot_id<>NEW.depot_id or OLD.flux_id<>NEW.flux_id) THEN
    UPDATE modele_tournee mt
    SET code=(SELECT CONCAT(d.code,f.code,NEW.code,mt.numero) 
              FROM depot d,ref_flux f
              WHERE d.id=NEW.depot_id AND f.id=NEW.flux_id)
    WHERE NEW.id=mt.groupe_id;
/* -- Déjà fait dans le trigger modele_tournee_update_ta
    UPDATE modele_tournee_jour mtj
    SET CODE=(SELECT CONCAT(NEW.CODE,j.code) 
              FROM modele_tournee mt,ref_jour j 
              WHERE mtj.tournee_id=mt.id AND NEW.id=mt.groupe_id AND j.id=mtj.jour_id)
    WHERE mtj.tournee_id IN (SELECT id 
                              FROM modele_tournee mt
                              WHERE NEW.id=mt.groupe_id);*/
  END IF;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- modele_tournee
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS modele_tournee_insert_tb;
CREATE TRIGGER modele_tournee_insert_tb
BEFORE INSERT ON modele_tournee FOR EACH ROW
BEGIN
  SET NEW.date_creation=NOW();
  SET NEW.code=(SELECT CONCAT(d.code,f.code,gt.code,NEW.numero) 
                FROM groupe_tournee gt
                INNER JOIN depot d ON gt.depot_id=d.id
                INNER JOIN ref_flux f ON gt.flux_id=f.id
                WHERE gt.id=NEW.groupe_id)
                ;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS modele_tournee_update_tb;
CREATE TRIGGER modele_tournee_update_tb
BEFORE UPDATE ON modele_tournee FOR EACH ROW
BEGIN
  SET NEW.date_modif=NOW();
  IF (OLD.groupe_id<>NEW.groupe_id OR OLD.numero<>NEW.numero) THEN
    SET NEW.code=(SELECT CONCAT(d.code,f.code,gt.code,NEW.numero) 
                  FROM groupe_tournee gt
                  INNER JOIN depot d ON gt.depot_id=d.id
                  INNER JOIN ref_flux f ON gt.flux_id=f.id
                  WHERE gt.id=NEW.groupe_id)
                  ;
  END IF;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS modele_tournee_update_ta;
CREATE TRIGGER modele_tournee_update_ta
AFTER UPDATE ON modele_tournee FOR EACH ROW
BEGIN
  IF (OLD.code<>NEW.code) THEN
    UPDATE modele_tournee_jour mtj
    SET code=(SELECT CONCAT(NEW.code,j.code) 
              FROM ref_jour j 
              WHERE j.id=mtj.jour_id)
    WHERE NEW.id=mtj.tournee_id;
  END IF;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- modele_tournee_jour
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS modele_tournee_jour_insert_tb;
CREATE TRIGGER modele_tournee_jour_insert_tb
BEFORE INSERT ON modele_tournee_jour
FOR EACH ROW
BEGIN
  SET NEW.code = (SELECT CONCAT(mt.code,j.code) FROM modele_tournee mt, ref_jour j WHERE mt.id=NEW.tournee_id AND j.id=NEW.jour_id);
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS modele_tournee_jour_update_tb;
CREATE TRIGGER modele_tournee_jour_update_tb
BEFORE UPDATE ON modele_tournee_jour
FOR EACH ROW
BEGIN
  IF (OLD.jour_id<>NEW.jour_id) THEN
    SET NEW.code = (SELECT CONCAT(mt.code,j.code) FROM modele_tournee mt, ref_jour j WHERE mt.id=NEW.tournee_id AND j.id=NEW.jour_id);
  END IF;
  IF (OLD.code<>NEW.code) THEN
    UPDATE tournee_detail
    SET modele_tournee_jour_code=NEW.code
    , source_modification='modele_tournee_jour_update_tb'
    , date_modification=now()
    WHERE  modele_tournee_jour_code=OLD.code;
  END IF;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- etalon_tournee
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
/*
DROP TRIGGER IF EXISTS etalon_tournee_insert_tb;
CREATE TRIGGER etalon_tournee_insert_tb
BEFORE INSERT ON etalon_tournee
FOR EACH ROW
BEGIN
  SET NEW.duree_tournee=subtime(subtime(NEW.duree,NEW.duree_reperage),NEW.duree_supplement);
  SET NEW.duree_nuit = pai_heure_nuit(NEW.heure_debut,NEW.duree);
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS etalon_tournee_update_tb;
CREATE TRIGGER etalon_tournee_update_tb
BEFORE UPDATE ON etalon_tournee
FOR EACH ROW
BEGIN
  IF (time_to_sec(OLD.duree)<>time_to_sec(NEW.duree)) THEN
    SET NEW.duree_tournee=subtime(subtime(NEW.duree,NEW.duree_reperage),NEW.duree_supplement);
    SET NEW.duree_nuit = pai_heure_nuit(NEW.heure_debut,NEW.duree);
  END IF;
END;
*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS etalon_delete_tb;
CREATE TRIGGER etalon_delete_tb
BEFORE DELETE ON etalon FOR EACH ROW
BEGIN
  if OLD.date_validation is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Etalon validé';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS etalon_tournee_delete_tb;
CREATE TRIGGER etalon_tournee_delete_tb
BEFORE DELETE ON etalon_tournee FOR EACH ROW
BEGIN
declare date_validation datetime;
  set date_validation=(select date_validation from etalon where id=OLD.etalon_id);
  if date_validation is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Etalon validé';
  end if;
END;