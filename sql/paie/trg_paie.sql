-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_heure_update_tb;
/*CREATE TRIGGER pai_heure_update_tb
BEFORE UPDATE ON pai_heure FOR EACH ROW
BEGIN
	IF (OLD.duree_attente<>NEW.duree_attente) THEN
		SET NEW.heure_debut=addtime(NEW.heure_debut_theo,NEW.duree_attente);
	END IF;
END;*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_heure_update_ta;
CREATE TRIGGER pai_heure_update_ta
AFTER UPDATE ON pai_heure FOR EACH ROW
BEGIN
	IF (OLD.duree_attente<>NEW.duree_attente) THEN
/*		UPDATE pai_tournee t
		SET 	heure_debut = addtime(NEW.heure_debut_theo,NEW.duree_attente)
		WHERE t.heure_id=NEW.id;
*/
    -- On met à jour les heures d'attente
    -- Sauf sur les tournees splitées et les suivantes
    UPDATE pai_tournee pt
    SET pt.duree_attente=IF ((pt.tournee_org_id is null or pt.split_id is not null) and pt.ordre=1,NEW.duree_attente,'00:00:00')
    WHERE pt.heure_id=NEW.id
    and pt.date_extrait is null
    ;
	END IF;
END;
/*
A mettre à jour si changement de
- valeur de rémunération dans remplacement
- modele_tournee dans remplacement
- valeur de rémunération dans modèle
- employe ou remplacant dans modèle
- employe dans tournée

  select 
  case
  when mr.id is not null then 'Remplacement'
  when pt.employe_id=mtj.remplacant_id then 'Remplacant'
  when pt.employe_id=mtj.employe_id then 'Titulaire'
  else 'Ponctuel'
  end
  , pt.*,mtj.*,mt.*,mrj.*,mr.*
  from pai_tournee pt
  inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
  inner join modele_tournee mt on mtj.tournee_id=mt.id
  left outer join modele_remplacement_jour mrj on mrj.modele_tournee_id=mt.id and pt.jour_id=mrj.jour_id
  left outer join modele_remplacement mr on mrj.remplacement_id=mr.id and pt.employe_id=mr.employe_id and pt.date_distrib between mr.date_debut and mr.date_fin
  where pt.valrem<>
  case
  when mr.id is not null then mrj.valrem_moyen
  when pt.employe_id=mtj.remplacant_id then mtj.valrem_moyen
  when pt.employe_id=mtj.employe_id then mtj.valrem_moyen
  else mtj.valrem
  end
  and pt.date_distrib>'2016-12-01'
  */
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_tournee_insert_tb;
CREATE TRIGGER pai_tournee_insert_tb
BEFORE INSERT ON pai_tournee FOR EACH ROW
BEGIN
  SET NEW.date_creation=now();
  IF (NEW.jour_id is null or NEW.jour_id=0) THEN
	  SET NEW.jour_id=DAYOFWEEK(NEW.date_distrib);
  END IF;
  IF (NEW.typejour_id is null or NEW.typejour_id=0) THEN
	  SET NEW.typejour_id = pai_typejour_employe(NEW.date_distrib,NEW.employe_id);
  END IF;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_tournee_insert_ta;
CREATE TRIGGER pai_tournee_insert_ta
AFTER INSERT ON pai_tournee FOR EACH ROW
BEGIN
  IF (NEW.tournee_org_id is null or NEW.split_id is not null) THEN
    IF (NEW.employe_id is not null AND TIME_TO_SEC(NEW.duree_retard)>0) THEN
    	INSERT INTO pai_activite(tournee_id,jour_id,typejour_id,depot_id,flux_id,activite_id,employe_id,transport_id,utilisateur_id,date_distrib,heure_debut,duree,nbkm_paye,date_creation)
    	VALUES(NEW.id,NEW.jour_id,NEW.typejour_id,NEW.depot_id,NEW.flux_id,-1,NEW.employe_id,NEW.transport_id,NEW.utilisateur_id,NEW.date_distrib,null,NEW.duree_retard,0,NEW.date_creation)
      ;
    END IF;

    IF (NEW.employe_id is not null AND TIME_TO_SEC(NEW.duree_retard)<TIME_TO_SEC(NEW.duree_attente)) THEN
    	INSERT INTO pai_activite(tournee_id,jour_id,typejour_id,depot_id,flux_id,activite_id,employe_id,transport_id,utilisateur_id,date_distrib,heure_debut,duree,nbkm_paye,date_creation)
    	VALUES(NEW.id,NEW.jour_id,NEW.typejour_id,NEW.depot_id,NEW.flux_id,-2,NEW.employe_id,NEW.transport_id,NEW.utilisateur_id,NEW.date_distrib,null,SEC_TO_TIME(TIME_TO_SEC(NEW.duree_attente)-TIME_TO_SEC(NEW.duree_retard)),0,NEW.date_creation)
      ;
    END IF;
  END IF;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_tournee_update_tb;
CREATE TRIGGER pai_tournee_update_tb
BEFORE UPDATE ON pai_tournee FOR EACH ROW
BEGIN
/*
  if OLD.date_extrait is not null and NEW.date_extrait is not null then
--    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = concat_ws(' ','La tournée',NEW.code,'du','est extraite');
--    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = NEW.date_distrib;
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La tournée est extraite';
  end if;
*/
	IF (coalesce(NEW.employe_id,0)<>coalesce(OLD.employe_id,0)) THEN
	  SET NEW.typejour_id = pai_typejour_employe(NEW.date_distrib,NEW.employe_id);
  END IF;
  IF (NEW.tournee_org_id is not null and NEW.split_id is null) THEN
    SET NEW.duree_attente='00:00:00';
  ELSEIF (NEW.ordre>1) THEN
    SET NEW.duree_attente='00:00:00';
  ELSEIF (OLD.ordre<>NEW.ordre and NEW.ordre=1) OR (coalesce(OLD.tournee_org_id,0)<>coalesce(NEW.tournee_org_id,0) AND (NEW.tournee_org_id is null or NEW.split_id is not null)) THEN
      SET NEW.duree_attente= (select h.duree_attente from pai_heure h where h.id=NEW.heure_id);
  END IF;
END;
	

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_tournee_update_ta;
CREATE TRIGGER pai_tournee_update_ta
AFTER UPDATE ON pai_tournee FOR EACH ROW
BEGIN
/* 10/03/2016 Ne sert à rien car duree_attente est mis à jour par le triggerpai_tournee_update_tb
  -- On supprime les activités sur la tournées splittée
  IF (NEW.tournee_org_id is not null and NEW.split_id is null) THEN
    DELETE pj FROM pai_journal pj
    INNER JOIN pai_activite pa on pj.activite_id=pa.id
		WHERE pa.tournee_id=NEW.id
    AND pa.activite_id<0;

    DELETE pa FROM pai_activite pa
		WHERE pa.tournee_id=NEW.id
    AND pa.activite_id<0;
  END IF;
  */
  IF (OLD.duree_retard<>NEW.duree_retard OR OLD.duree_attente<>NEW.duree_attente OR coalesce(NEW.employe_id,0)<>coalesce(OLD.employe_id,0)) THEN
    DELETE pj FROM pai_journal pj
    INNER JOIN pai_activite pa on pj.activite_id=pa.id
		WHERE pa.tournee_id=OLD.id
    AND pa.activite_id in (-1,-2)
    ;
		DELETE pa
    FROM pai_activite pa
		WHERE pa.tournee_id=OLD.id
    AND pa.activite_id in (-1,-2)
    ;
    IF (NEW.tournee_org_id is null or NEW.split_id is not null) THEN
      IF (NEW.employe_id is not null AND TIME_TO_SEC(NEW.duree_retard)>0) THEN
      	INSERT INTO pai_activite(tournee_id,jour_id,typejour_id,depot_id,flux_id,activite_id,employe_id,transport_id,utilisateur_id,date_distrib,heure_debut,duree,nbkm_paye,date_creation)
      	VALUES(NEW.id,NEW.jour_id,NEW.typejour_id,NEW.depot_id,NEW.flux_id,-1,NEW.employe_id,NEW.transport_id,NEW.utilisateur_id,NEW.date_distrib,null,NEW.duree_retard,0,NEW.date_creation)
        ;
      END IF;

      IF (NEW.employe_id is not null AND TIME_TO_SEC(NEW.duree_retard)<TIME_TO_SEC(NEW.duree_attente)) THEN
      	INSERT INTO pai_activite(tournee_id,jour_id,typejour_id,depot_id,flux_id,activite_id,employe_id,transport_id,utilisateur_id,date_distrib,heure_debut,duree,nbkm_paye,date_creation)
      	VALUES(NEW.id,NEW.jour_id,NEW.typejour_id,NEW.depot_id,NEW.flux_id,-2,NEW.employe_id,NEW.transport_id,NEW.utilisateur_id,NEW.date_distrib,null,SEC_TO_TIME(TIME_TO_SEC(NEW.duree_attente)-TIME_TO_SEC(NEW.duree_retard)),0,NEW.date_creation)
        ;
      END IF;
    END IF;
  END IF;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_tournee_delete_tb;
CREATE TRIGGER pai_tournee_delete_tb
BEFORE DELETE ON pai_tournee FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tournée extraite';
  end if;

  DELETE pj FROM pai_journal pj
  INNER JOIN pai_activite pa on pj.activite_id=pa.id
	WHERE pa.tournee_id=OLD.id
  ;
	DELETE pa FROM pai_activite pa
	WHERE pa.tournee_id=OLD.id
  ;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_activite_insert_tb;
CREATE TRIGGER pai_activite_insert_tb
BEFORE INSERT ON pai_activite FOR EACH ROW
BEGIN
  SET NEW.date_creation=now();
  IF (NEW.jour_id is null or NEW.jour_id=0) THEN
	  SET NEW.jour_id=DAYOFWEEK(NEW.date_distrib);
  END IF;
  IF (NEW.typejour_id is null or NEW.typejour_id=0) THEN
	  SET NEW.typejour_id = pai_typejour_employe(NEW.date_distrib,NEW.employe_id);
  END IF;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_activite_update_tb;
CREATE TRIGGER pai_activite_update_tb
BEFORE UPDATE ON pai_activite FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null and NEW.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Activité extraite';
  end if;
	IF (coalesce(NEW.employe_id,0)<>coalesce(OLD.employe_id,0)) THEN
	  SET NEW.typejour_id = pai_typejour_employe(NEW.date_distrib,NEW.employe_id);
  END IF;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_activite_delete_tb;
CREATE TRIGGER pai_activite_delete_tb
BEFORE DELETE ON pai_activite FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Activité extraite';
  end if;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_produit_insert_tb;
CREATE TRIGGER pai_produit_insert_tb
BEFORE INSERT ON pai_prd_tournee FOR EACH ROW
BEGIN
  SET NEW.date_creation=now();
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_produit_update_tb;
CREATE TRIGGER pai_produit_update_tb
BEFORE UPDATE ON pai_prd_tournee FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null and NEW.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Produit extrait';
  end if;
--  SET NEW.date_modif=now();
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_produit_delete_tb;
CREATE TRIGGER pai_produit_delete_tb
BEFORE DELETE ON pai_prd_tournee FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Produit extrait';
  end if;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_reclamation_update_tb;
CREATE TRIGGER pai_reclamation_update_tb
BEFORE UPDATE ON pai_reclamation FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null and NEW.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Reclamation extraite';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_reclamation_delete_tb;
CREATE TRIGGER pai_reclamation_delete_tb
BEFORE DELETE ON pai_reclamation FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Reclamation extraite';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_incident_update_tb;
CREATE TRIGGER pai_incident_update_tb
BEFORE UPDATE ON pai_incident FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null and NEW.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Incident extrait';
  end if;
/*  update pai_incident pi
  inner join pai_tournee pt on pi.tournee_id=pt.id
  set pi.date_distrib=pt.date_distrib,
  pi.employe_id=pt.employe_id
  where pi.date_extrait is null*/
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_incident_delete_tb;
CREATE TRIGGER pai_incident_delete_tb
BEFORE DELETE ON pai_incident FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Incident extrait';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_majoration_update_tb;
CREATE TRIGGER pai_majoration_update_tb
BEFORE UPDATE ON pai_majoration FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null and NEW.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Majoration extraite';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_majoration_delete_tb;
CREATE TRIGGER pai_majoration_delete_tb
BEFORE DELETE ON pai_majoration FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Majoration extraite';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_hchs_update_tb;
CREATE TRIGGER pai_hchs_update_tb
BEFORE UPDATE ON pai_hchs FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null and NEW.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'HCHS extraite';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_hchs_delete_tb;
CREATE TRIGGER pai_hchs_delete_tb
BEFORE DELETE ON pai_hchs FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'HCHS extraite';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_hg_update_tb;
CREATE TRIGGER pai_hg_update_tb
BEFORE UPDATE ON pai_hg FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null and NEW.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'HG extraite';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_hg_delete_tb;
CREATE TRIGGER pai_hg_delete_tb
BEFORE DELETE ON pai_hg FOR EACH ROW
BEGIN
  if OLD.date_extrait is not null then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'HG extraite';
  end if;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_ref_ferie_insert_tb;
CREATE TRIGGER pai_ref_ferie_insert_tb
BEFORE INSERT ON pai_ref_ferie FOR EACH ROW
BEGIN
DECLARE _date_debut date;
  SELECT pm.date_debut into _date_debut from pai_mois pm where pm.flux_id=NEW.societe_id;
  if NEW.jfdate<_date_debut then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Impossible d insérer sur une période cloturée';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_ref_ferie_insert_ta;
CREATE TRIGGER pai_ref_ferie_insert_ta
AFTER INSERT ON pai_ref_ferie FOR EACH ROW
BEGIN
  update pai_tournee pt
  inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
  set pt.typejour_id=pai_typejour_societe(pt.date_distrib,epd.societe_id)
  where pt.date_distrib=NEW.jfdate
  and epd.societe_id=NEW.societe_id
  ;
  update pai_activite pa
  inner join emp_pop_depot epd on pa.employe_id=epd.employe_id and pa.date_distrib between epd.date_debut and epd.date_fin
  set pa.typejour_id=pai_typejour_societe(pa.date_distrib,epd.societe_id)
  where pa.date_distrib=NEW.jfdate
  and epd.societe_id=NEW.societe_id
  ;
  call recalcul_horaire(@validation_id,null,NEW.societe_id,NEW.jfdate,null);
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_ref_ferie_delete_tb;
CREATE TRIGGER pai_ref_ferie_delete_tb
BEFORE DELETE ON pai_ref_ferie FOR EACH ROW
BEGIN
DECLARE _date_debut date;
  SELECT pm.date_debut into _date_debut from pai_mois pm where pm.flux_id=OLD.societe_id;
  if OLD.jfdate<_date_debut then
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Impossible de supprimer sur une période cloturée';
  end if;
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS pai_ref_ferie_delete_ta;
CREATE TRIGGER pai_ref_ferie_delete_ta
AFTER DELETE ON pai_ref_ferie FOR EACH ROW
BEGIN
  update pai_tournee pt
  inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
  set pt.typejour_id=pai_typejour_societe(pt.date_distrib,epd.societe_id)
  where pt.date_distrib=OLD.jfdate
  and epd.societe_id=OLD.societe_id
  ;
  update pai_activite pa
  inner join emp_pop_depot epd on pa.employe_id=epd.employe_id and pa.date_distrib between epd.date_debut and epd.date_fin
  set pa.typejour_id=pai_typejour_societe(pa.date_distrib,epd.societe_id)
  where pa.date_distrib=OLD.jfdate
  and epd.societe_id=OLD.societe_id
  ;
  call recalcul_horaire(@validation_id,null,OLD.societe_id,OLD.jfdate,null);
END;
