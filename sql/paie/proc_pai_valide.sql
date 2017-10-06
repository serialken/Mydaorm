/*
ATTENTION ==> repasser "Modele de tournée introuvable" en bloquant danss pai_ref_erreur !!!!!!

set @id=null;
call pai_valide_activite(@id,null,null,null,null);
set @id=null;
call pai_valide_activite(@id,null,null,null,null);
set @id=null;
call pai_valide_tournee(@id,18,2,null,null);
call pai_valide_tournee(@id,null,null,null,null);
set @id=null;
call pai_valide_produit(@id,null,null,null,null,null);
set @id=null;
call pai_valide_reclamation(@id,null,null,null,null);
select * from depot
select * from pai_int_log where idtrt=1 order by id desc limit 1000;
--truncate table pai_journal;
delete from pai_int_log where idtrt=1;

select * from pai_journal where erreur_id in (40,41)
kill 3760772
*/



-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_delete_employe;
CREATE PROCEDURE `pai_valide_delete_employe`(IN _rubrique varchar(2), IN _depot_id INT, IN _flux_id INT, IN _employe_id INT)
BEGIN
	DELETE pj
  FROM pai_journal pj
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
	WHERE pj.date_extrait is null
  AND pe.rubrique=_rubrique
	AND (pj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pj.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pj.employe_id=_employe_id OR (_employe_id IS NULL AND pj.employe_id IS NOT NULL))
  AND pj.tournee_id is null
  AND pj.activite_id is null
	;
  call pai_valide_logger('pai_valide_delete_employe', 'pai_valide_delete_employe');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_delete;
CREATE PROCEDURE `pai_valide_delete`(IN _rubrique varchar(2), IN _depot_id INT, IN _flux_id INT)
BEGIN
	DELETE pj
  FROM pai_journal pj
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
	WHERE pj.date_extrait is null
  AND pe.rubrique=_rubrique
	AND (pj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pj.flux_id=_flux_id OR _flux_id IS NULL)
  ;
  call pai_valide_logger('pai_valide_delete', 'pai_valide_delete');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide;
CREATE PROCEDURE `pai_valide`(INOUT _validation_id INT, IN _anneemois VARCHAR(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE',concat_ws('*',_validation_id,_anneemois,_depot_id,_flux_id));
	END IF;
  
    call pai_valide_tournee(_validation_id,_depot_id,_flux_id,null,null);
--    call pai_valide_activite(_validation_id,_depot_id,_flux_id,null,null);
--    call pai_valide_rh_vehicule(_validation_id,_depot_id,_flux_id);
    call pai_valide_rh_mensuel(_validation_id,_anneemois,null);
    call pai_valide_rh_6jour(_validation_id,_anneemois,null);
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- valide les tournées d'un modele à partir d'une date de début
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_modele;
CREATE PROCEDURE `pai_valide_modele`(INOUT _validation_id INT, IN _date_debut DATE, IN _modele_tournee_jour_id INT)
BEGIN
  DECLARE v_finished INTEGER DEFAULT 0;
  DECLARE _tournee_id INT;
 
  -- declare cursor for employee email
  DEClARE _cursor CURSOR FOR
  SELECT pt.id
  FROM pai_tournee pt
  WHERE pt.modele_tournee_jour_id=_modele_tournee_jour_id
  AND pt.date_distrib>=_date_debut
  AND pt.date_extrait is null;
   
  -- declare NOT FOUND handler
  DECLARE CONTINUE HANDLER
  FOR NOT FOUND SET v_finished = 1;

  IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_MODELE',concat_ws('*',_validation_id,_date_debut,_modele_tournee_jour_id));
  END IF;
     
  OPEN _cursor;
  _loop: LOOP
    FETCH _cursor INTO _tournee_id;
    IF v_finished = 1 THEN
      LEAVE _loop;
    END IF;
    call pai_valide_logger('pai_valide_tournee',concat_ws('*',_validation_id,_tournee_id));
    call pai_valide_tournee(_validation_id,null,null,null,_tournee_id);
  END LOOP _loop;
  CLOSE _cursor;
END;
 
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pai_valide_reclamation;
CREATE PROCEDURE `pai_valide_reclamation`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _tournee_id INT)
BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_RECLAMATION',concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib,_tournee_id));
	END IF;
  
	CALL pai_valide_rh_tournee(_validation_id, _depot_id, _flux_id, _date_distrib, _tournee_id);

  CALL pai_valide_delete_tournee('RE', _depot_id, _flux_id, _date_distrib,_tournee_id);

	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 31,_validation_id,pt.depot_id,pt.flux_id,pr.anneemois,pt.date_distrib,pt.employe_id ,pt.id,NULL,
                      (select group_concat(if (c.imputation_paie,c.id,concat('-',c.id))separator ',')
                            from crm_detail c
                            inner join crm_demande cd on c.crm_demande_id=cd.id and cd.crm_categorie_id=1 -- seulement les reclamations
                            where c.pai_tournee_id=pt.id and c.societe_id=pr.societe_id
                            -- and c.imputation_paie=true
                            group by c.societe_id,c.pai_tournee_id
                        )
  from pai_tournee pt
  INNER JOIN pai_reclamation pr on pt.id=pr.tournee_id
  where pr.date_extrait is null
  AND pt.tournee_org_id is not null and pt.split_id is null
	AND (pt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pt.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pt.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (pt.id=_tournee_id OR _tournee_id IS NULL)
  ;
  call pai_valide_logger('pai_valide_reclamation', 'Reclamation sur tournee splitee');
 END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
