-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
/*
set @validation_id=null;
call pai_valide_rh_mensuel(@validation_id,'201611',null);
set @validation_id=null;
call pai_valide_rh_6jour(@validation_id,'201608',null);
select * from pai_journal where erreur_id in (29,30,45,46);
delete from pai_journal where erreur_id in (44) and date_distrib is null;

select * from employe where nom='SAUSSE'
select * from emp_pop_depot where employe_id=129
select * from emp_pop_depot where employe_id=6741
select * from depot
*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_delete_rh_mensuel;
CREATE PROCEDURE `pai_valide_delete_rh_mensuel`(IN _rubrique varchar(2), IN _anneemois VARCHAR(6), IN _employe_id INT)
BEGIN
	DELETE pj
  FROM pai_journal pj
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
	WHERE /*pj.date_extrait is null
  AND*/ pe.rubrique=_rubrique
  AND pj.anneemois=_anneemois
	AND (pj.employe_id=_employe_id OR (_employe_id IS NULL AND pj.employe_id IS NOT NULL))
  AND pj.tournee_id is null
  AND pj.activite_id is null
	;
  call pai_valide_logger('pai_valide_delete_rh_mensuel', 'pai_valide_delete_rh_mensuel');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_rh_mensuel;
CREATE PROCEDURE `pai_valide_rh_mensuel`(INOUT _validation_id INT, IN _anneemois VARCHAR(6), IN _employe_id INT)

BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_RH',concat_ws('*',_validation_id,_anneemois,_employe_id));
	END IF;
  
  CALL pai_valide_delete_rh_mensuel('HM', _anneemois, _employe_id);

  call create_tmp_pai_horaire(null,null,_employe_id,_anneemois,null);

  -- Dépassement de l''horaire contractuel
  /*
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 29,_validation_id,epd.depot_id,epd.flux_id,_anneemois,null,epd.employe_id,NULL,NULL,concat(round(sum(time_to_sec(psh.duree))/3600/epd.nbheures_garanties*100)-100,'% (',format(coalesce(sum(time_to_sec(psh.duree))/3600,0),2),' > ',epd.nbheures_garanties,')')
  from tmp_pai_horaire psh
  inner join pai_ref_mois m ON m.anneemois=_anneemois
  inner join emp_pop_depot epd on psh.employe_id=epd.employe_id and psh.date_distrib between greatest(epd.date_debut,m.date_debut) and least(epd.date_fin,m.date_fin)
  WHERE (psh.employe_id=_employe_id OR _employe_id IS NULL)
  and epd.nbheures_garanties>0
  group by epd.depot_id,epd.flux_id,epd.employe_id,greatest(epd.date_debut,m.date_debut),least(epd.date_fin,m.date_fin),epd.nbheures_garanties
  having coalesce(sum(time_to_sec(psh.duree))/3600,0)>epd.nbheures_garanties
  ;
  call pai_valide_logger('pai_valide_rh_mensuel', 'Dépassement de l''horaire contractuel');
  */
  -- Dépassement de l''horaire contractuel
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 29,_validation_id,phg.depot_id,phg.flux_id,phg.anneemois,null,phg.employe_id,NULL,NULL,concat(round(phg.nbheures_realisees/phg.nbheures_a_realiser*100)-100,'% (',phg.nbheures_realisees,' > ',phg.nbheures_a_realiser,')')
  from pai_hg phg
  WHERE phg.anneemois=_anneemois
  and (phg.employe_id=_employe_id OR _employe_id IS NULL)
  and phg.nbheures_realisees>phg.nbheures_a_realiser
  ;
  call pai_valide_logger('pai_valide_rh_mensuel', 'Dépassement de l''horaire contractuel');

  -- Dépassement de 151h67 mono-societe
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 45,_validation_id,epd.depot_id,epd.flux_id,_anneemois,null,epd.employe_id ,NULL,NULL,concat(round(psh.duree/151.67*100)-100,'% (',format(psh.duree,2),')')
  from (select psh.employe_id,coalesce(sum(time_to_sec(psh.duree))/3600,0) as duree
    from tmp_pai_horaire psh
    WHERE (psh.employe_id=_employe_id OR _employe_id IS NULL)
    group by psh.employe_id
    having coalesce(sum(time_to_sec(psh.duree))/3600,0)>151.67
    ) as psh
  inner join pai_ref_mois m ON m.anneemois=_anneemois
  inner join emp_pop_depot epd on psh.employe_id=epd.employe_id and epd.date_debut<=m.date_fin and epd.date_fin>=m.date_debut
  ;
  call pai_valide_logger('pai_valide_rh_mensuel', 'Dépassement de 151.67');


  -- Dépassement de 190h00 multi-societe
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 46,_validation_id,epd.depot_id,epd.flux_id,_anneemois,null,epd.employe_id ,NULL,NULL,concat(round(psh.duree/190*100)-100,'% (total=',format(psh.duree,2),', mediapresse=',format(psh.duree_media,2),', proximy=',format(psh.duree_proxy,2),')')
  from (select e.saloid,coalesce(sum(time_to_sec(psh.duree))/3600,0) as duree,coalesce(sum(if(psh.flux_id=1,time_to_sec(psh.duree),0))/3600,0) as duree_proxy,coalesce(sum(if(psh.flux_id=2,time_to_sec(psh.duree),0))/3600,0) as duree_media
    from tmp_pai_horaire psh
    inner join employe e on psh.employe_id=e.id
    WHERE (psh.employe_id=_employe_id OR _employe_id IS NULL)
    group by e.saloid
    having coalesce(sum(time_to_sec(psh.duree))/3600,0)>190.00
    ) as psh
  inner join employe e on psh.saloid=e.saloid
  inner join pai_ref_mois m ON m.anneemois=_anneemois
  inner join emp_pop_depot epd on e.id=epd.employe_id and epd.date_debut<=m.date_fin and epd.date_fin>=m.date_debut
  ;
  call pai_valide_logger('pai_valide_rh_mensuel', 'Dépassement de 190.00');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_rh_6jour;
CREATE PROCEDURE `pai_valide_rh_6jour`(INOUT _validation_id INT, IN _anneemois VARCHAR(6), IN _employe_id INT)

BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_6JOUR',concat_ws('*',_validation_id,_anneemois,_employe_id));
	END IF;  
  
  CALL pai_valide_delete_rh_mensuel('R6', _anneemois, _employe_id);
  
  SET @first_date_distrib  = NULL, @last_date_distrib  = NULL, @last_saloid = NULL, @count = 0;
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 30,_validation_id,epd.depot_id,epd.flux_id,m.anneemois,max(psh.date_distrib),epd.employe_id ,NULL,NULL,concat(max(psh.nb),' jours (du ',psh.first_date_distrib, ' au ',max(psh.date_distrib),')')
  from(
      SELECT psh.saloid,psh.date_distrib,
          @count := IF(@last_saloid  = psh.saloid,
                        CASE 
                            WHEN @last_date_distrib  = psh.date_distrib THEN @count
                            WHEN date_add(@last_date_distrib, INTERVAL +1 DAY)  = psh.date_distrib THEN @count + 1
                            ELSE 1
                        END
                      , 1) as nb,
          @first_date_distrib := IF(@last_saloid  = psh.saloid,
                        CASE 
                            WHEN @last_date_distrib  = psh.date_distrib THEN @first_date_distrib
                            WHEN date_add(@last_date_distrib, INTERVAL +1 DAY)  = psh.date_distrib THEN @first_date_distrib
                            ELSE psh.date_distrib
                        END
                      , psh.date_distrib) as first_date_distrib,
          @last_saloid  := psh.saloid,
          @last_date_distrib  := psh.date_distrib 
      FROM (
      -- ATTENTION : balaye tous les 45 derniers jours de la table !!!!!
            select e.saloid,pt.date_distrib 
            from pai_tournee pt
            inner join pai_ref_mois m ON m.anneemois=_anneemois
            inner join employe e on pt.employe_id=e.id
            WHERE (pt.employe_id=_employe_id OR _employe_id IS NULL)
            AND (pt.tournee_org_id is null or pt.split_id is not null)
            AND pt.date_distrib between date_add(m.date_debut,Interval -6 DAY) and m.date_fin
            AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pt.id=pj.tournee_id AND NOT pe.valide)
            and pt.duree>0
            union
            select e.saloid, pa.date_distrib 
            from pai_activite pa
            inner join pai_ref_mois m ON m.anneemois=_anneemois
            inner join employe e on pa.employe_id=e.id
            WHERE (pa.employe_id=_employe_id OR _employe_id IS NULL)
            AND pa.date_distrib between date_add(m.date_debut,Interval -6 DAY) and m.date_fin
            AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pa.id=pj.activite_id AND NOT pe.valide)
            AND pa.activite_id>0
            and pa.duree>0
            ) psh
      ORDER BY psh.saloid,psh.date_distrib
      ) psh
  inner join employe e on psh.saloid=e.saloid
  inner join pai_ref_mois m ON m.anneemois=_anneemois
  inner join emp_pop_depot epd on e.id=epd.employe_id and psh.date_distrib between epd.date_debut and epd.date_fin
  where psh.nb>6
  group by epd.depot_id,epd.flux_id,m.anneemois,psh.first_date_distrib,epd.employe_id;
  call pai_valide_logger('pai_valide_rh_6jour', 'L''employe a travaille plus de 6j consecutif');
     
END;
 
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
/*
  CALL pai_valide_delete('SE', null, null);
  CALL pai_valide_delete('VE', null, null);

DROP PROCEDURE IF EXISTS pai_valide_rh_vehicule;
CREATE PROCEDURE `pai_valide_rh_vehicule`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
  SET lc_time_names = 'fr_FR';
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_RH_VEHICULE',concat_ws('*',_validation_id,_depot_id,_flux_id));
	END IF;
  
  CALL pai_valide_delete('VE', _depot_id, _flux_id);
	
  -- Fin validité assurance
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT if(v.dtfinassur<SYSDATE(),7,43),_validation_id,epd.depot_id,epd.flux_id,m.anneemois,m.date_debut,e.id ,NULL,NULL,DATE_FORMAT(v.dtfinassur,'%d/%m/%Y')
  -- CONCAT_WS(' ','Fin validité assurance le',coalesce(DATE_FORMAT(v.dtfinassur,'%d %M %Y'),'???'),'pour',e.nom,e.prenom1,e.prenom2)
	FROM emp_pop_depot epd
	INNER JOIN employe e ON epd.employe_id=e.id
	INNER JOIN pai_png_vehiculew v ON e.saloid=v.salarie
	INNER JOIN pai_ref_mois m ON CURDATE() between m.date_debut and m.date_fin
	WHERE (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND epd.date_fin>=m.date_debut
	AND v.begin_date<=m.date_fin AND  m.date_debut<=v.end_date
  AND v.dtfinassur<DATE_ADD(SYSDATE(),INTERVAL 1 MONTH)
	;
  call pai_valide_logger('pai_valide_rh_vehicule', 'Fin validite assurance');
  
  -- Fin validité de contrôle technique
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT if(v.dtvaliditect<SYSDATE(),8,44),_validation_id,epd.depot_id,epd.flux_id,m.anneemois,m.date_debut,e.id ,NULL,NULL,DATE_FORMAT(v.dtvaliditect,'%d/%m/%Y')
  -- CONCAT_WS(' ','Fin validité de contrôle technique le',coalesce(DATE_FORMAT(v.dtvaliditect,'%d %M %Y'),'???'),'pour',e.nom,e.prenom1,e.prenom2),
	FROM emp_pop_depot epd
	INNER JOIN employe e ON epd.employe_id=e.id
	INNER JOIN pai_png_vehiculew v ON e.saloid=v.salarie
	INNER JOIN pai_ref_mois m ON CURDATE() between m.date_debut and m.date_fin
	WHERE (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND epd.date_fin>=m.date_debut
	AND v.begin_date<=m.date_fin AND  m.date_debut<=v.end_date
  AND v.dtvaliditect<DATE_ADD(SYSDATE(),INTERVAL 1 MONTH)
	;
  call pai_valide_logger('pai_valide_rh_vehicule', 'Fin validite de contrôle technique');
END; 
 
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS pai_valide_rh_sejour;
CREATE PROCEDURE `pai_valide_rh_sejour`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
  SET lc_time_names = 'fr_FR';
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_RH_SEJOUR',concat_ws('*',_validation_id,_depot_id,_flux_id));
	END IF;
  
  CALL pai_valide_delete('SE', _depot_id, _flux_id);
	
  -- Fin validité de la carte de séjour
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT if(e.sejour_date<SYSDATE(),48,49),_validation_id,epd.depot_id,epd.flux_id,m.anneemois,m.date_debut,e.id ,NULL,NULL,DATE_FORMAT(e.sejour_date,'%d/%m/%Y')
	FROM employe e
  INNER JOIN emp_pop_depot epd ON epd.employe_id=e.id
	INNER JOIN pai_ref_mois m ON CURDATE() between m.date_debut and m.date_fin
	WHERE (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND epd.date_fin>=m.date_debut
	AND e.sejour_date>=m.date_debut AND e.sejour_date<DATE_ADD(SYSDATE(),INTERVAL 1 MONTH)
	;
  call pai_valide_logger('pai_valide_rh_sejour', 'Fin validite de la carte de sejour');

END; 
*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
/*
DROP PROCEDURE IF EXISTS pai_valide_rh;
CREATE PROCEDURE `pai_valide_rh`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT)
BEGIN
  SET lc_time_names = 'fr_FR';
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_RH',concat_ws('*',_validation_id,_depot_id,_flux_id));
	END IF;
  
	CALL pai_valide_rh_tournee(_validation_id, _depot_id, _flux_id, NULL, NULL);
	CALL pai_valide_rh_activite(_validation_id, _depot_id, _flux_id, NULL, NULL);
	CALL pai_valide_rh_vehicule(_validation_id, _depot_id, _flux_id, NULL, NULL);
END;
set @validation_id=null;
	CALL pai_valide_rh_vehicule(@validation_id, null, null);

*/