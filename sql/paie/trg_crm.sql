/* initialisation
    delete from pai_reclamation where date_extrait is null;
    set @TRIGGER_CRM_RECALCUL_MAJORATION=false;
    update crm_detail set imputation_paie=imputation_paie where pai_tournee_id is not null;
    call recalcul_majoration(null, null, null, null);
    
    select * from pai_int_log where idtrt=1 order by id desc limit 1000;
select * from crm_detail where imputation_paie and pai_tournee_id not in (select tournee_id from pai_reclamation) and pai_tournee_id in (select id from pai_tournee);
select * from pai_reclamation order by id desc;
    
    select * from pai_reclamation;
    select * 
    from pai_reclamation pr
    inner join pai_tournee pt on pr.tournee_id=pt.id
    where pr.date_extrait is null
    order by pt.id
    ;
    kill 6095089
    IF ((@TRIGGER_CHECKS = FALSE)
*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS crm_detail_insert_ta;
CREATE TRIGGER crm_detail_insert_ta
AFTER INSERT ON crm_detail
FOR EACH ROW
BEGIN
  call pai_valide_logger('crm_detail_insert_ta', concat_ws('*',coalesce(@TRIGGER_CRM_RECALCUL_MAJORATION,'true'),NEW.imputation_paie,coalesce(NEW.pai_tournee_id,'NULL')));
  if NEW.pai_tournee_id is not null then
    call recalcul_reclamation(NEW.pai_tournee_id,NEW.societe_id);
  end if;
END;
-- date_imputation_paie
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS crm_detail_update_ta;
CREATE TRIGGER crm_detail_update_ta
AFTER UPDATE ON crm_detail
FOR EACH ROW
BEGIN
  call pai_valide_logger('crm_detail_update_ta', concat_ws('*',coalesce(@TRIGGER_CRM_RECALCUL_MAJORATION,'true'),OLD.imputation_paie,coalesce(OLD.pai_tournee_id,'NULL'),NEW.imputation_paie,coalesce(NEW.pai_tournee_id,'NULL')));
    -- A Mettre en commentaire la condition dans le trigger update pour l'initalisation
--  if NEW.imputation_paie<>OLD.imputation_paie OR coalesce(NEW.pai_tournee_id,0)<>coalesce(OLD.pai_tournee_id,0) then
    if OLD.pai_tournee_id is not null then
      call recalcul_reclamation(OLD.pai_tournee_id,OLD.societe_id);
    end if;
    if NEW.pai_tournee_id is not null AND NEW.pai_tournee_id<>coalesce(OLD.pai_tournee_id,0) then
      call recalcul_reclamation(NEW.pai_tournee_id,NEW.societe_id);
    end if;
--  end if;
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS crm_detail_delete_ta;
CREATE TRIGGER crm_detail_delete_ta
AFTER DELETE ON crm_detail
FOR EACH ROW
BEGIN
  call pai_valide_logger('crm_detail_delete_ta', concat_ws('*',coalesce(@TRIGGER_CRM_RECALCUL_MAJORATION,'true'),OLD.imputation_paie,coalesce(OLD.pai_tournee_id,'NULL')));
  if OLD.pai_tournee_id is not null then
    call recalcul_reclamation(OLD.pai_tournee_id,OLD.societe_id);
  end if;
END;


-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_reclamation;
CREATE PROCEDURE recalcul_reclamation(
    IN 		_pai_tournee_id INT,
    IN 		_societe_id INT
) BEGIN
DECLARE _validation_id INT;

    delete pr
    from pai_reclamation pr
    inner join pai_tournee pt on pr.tournee_id=pt.id
    left outer join pai_mois pm on pt.flux_id=pm.flux_id and pt.date_distrib<=pm.date_fin
    inner join pai_ref_mois prm on pt.date_distrib between prm.date_debut and prm.date_fin
    where pr.tournee_id=_pai_tournee_id
    and pr.societe_id=_societe_id
    and pr.type_id=1
    -- JL 20161114
    -- La réclamation peut-être sur pm.anneemois ou pm.anneemois_reclamation suivant que c.imputation_paie soit null ou non
--    and pr.anneemois=coalesce(pm.anneemois_reclamation,prm.anneemois)
    and pr.date_extrait is null;
    
    insert into pai_reclamation(date_creation,utilisateur_id,type_id,societe_id,tournee_id,anneemois,nbrec_abonne_brut,nbrec_diffuseur_brut,nbrec_abonne,nbrec_diffuseur)
    select 
      now(),0,
      1, -- crm
      c.societe_id,
      c.pai_tournee_id,
      -- JL 20161114
      -- Si imputation_paie est null (nouvelle réclamation), on utilise pm.anneemois_reclamation
      -- Si imputation_pai n'est pas null (l'utilsateur a modifié l'imputation paie), on utilise pm.anneemois jusqu'a la cloture
      coalesce(if(c.imputation_paie is null,pm.anneemois_reclamation,pm.anneemois),prm.anneemois),
      -- le total peut-être négatif, on fait éventuellement de la regul
      sum(case when c.client_type=0 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_abonne_brut) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id and pr2.type_id=1),0),
      sum(case when c.client_type=1 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_diffuseur_brut) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id and pr2.type_id=1),0),
      sum(case when not c.imputation_paie then 0 when c.client_type=0 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_abonne) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id and pr2.type_id=1),0),
      sum(case when not c.imputation_paie then 0 when c.client_type=1 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_diffuseur) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id and pr2.type_id=1),0)
    from crm_detail c
    inner join crm_demande cd on c.crm_demande_id=cd.id and cd.crm_categorie_id=1 -- seulement les reclamations
    inner join pai_tournee pt on c.pai_tournee_id=pt.id
    left outer join pai_mois pm on pt.flux_id=pm.flux_id and pt.date_distrib<=pm.date_fin
    inner join pai_ref_mois prm on pt.date_distrib between prm.date_debut and prm.date_fin
    left outer join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
    left outer join pai_stc ps on epd.rcoid=ps.rcoid
    where c.pai_tournee_id=_pai_tournee_id and c.societe_id=_societe_id
    -- JL 20150624
    -- Si un STC a été fait, la réclamation est extraite, est elle na pas été supprimée ci_dessus == > on ne touche plus aux données
--    and not exists(select null from pai_reclamation pr2 where pr2.anneemois = coalesce(pm.anneemois,prm.anneemois) and pr2.type_id=1 and pr2.tournee_id=_pai_tournee_id and pr2.societe_id=_societe_id)
    and ps.date_extrait is null
    group by c.societe_id,c.pai_tournee_id,coalesce(if(c.imputation_paie is null,pm.anneemois_reclamation,pm.anneemois),prm.anneemois)
    having sum(case when not c.imputation_paie then 0 when c.client_type=0 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_abonne_brut) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id and pr2.type_id=1),0)<>0
    or     sum(case when not c.imputation_paie then 0 when c.client_type=1 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_diffuseur_brut) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id and pr2.type_id=1),0)<>0
    or     sum(case when c.client_type=0 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_abonne) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id and pr2.type_id=1),0)<>0
    or     sum(case when c.client_type=1 THEN 1 ELSE 0 END)-coalesce((select sum(nbrec_diffuseur) from pai_reclamation pr2 where pr2.tournee_id=c.pai_tournee_id and pr2.societe_id=c.societe_id and pr2.type_id=1),0)<>0
   ;
    -- @TRIGGER_CRM_RECALCUL_MAJORATION est positionné à false dans alim_paie (recalcul manuel en fin d'alimentation)
    IF (coalesce(@TRIGGER_CRM_RECALCUL_MAJORATION,true) = TRUE) THEN
      call recalcul_majoration_tournee(_pai_tournee_id);
    END IF;
    
    CALL pai_valide_reclamation(_validation_id, null, null, null, _pai_tournee_id);
END;
