/*
ATTENTION ==> repasser "Modele de tournée introuvable" en bloquant danss pai_ref_erreur !!!!!!

set @id=null;
call pai_valide_activite(@id,null,null,null,null);
call pai_valide_activite(@id,null,null,'2016-09-05',null);
set @id=null;
call pai_valide_tournee(@id,null,null,'2016-09-05',null);
set @id=null;
call pai_valide_produit(@id,null,null,'2016-09-05',null,null);
select * from depot
select * from pai_int_log where idtrt=1 order by id desc limit 1000;
--truncate table pai_journal;
delete from pai_int_log where idtrt=1;

select * from depot
select * from pai_journal pj
inner join v_employe e on pj.employe_id=e.employe_id and pj.date_distrib between e.date_debut and e.date_fin
where erreur_id in (51)
*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_delete_activite;
CREATE PROCEDURE `pai_valide_delete_activite`(IN _rubrique varchar(2), IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _activite_id INT)
BEGIN
	DELETE pj
  FROM pai_journal pj
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
	WHERE pj.date_extrait is null
  AND pe.rubrique=_rubrique
	AND (pj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pj.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pj.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND (pj.activite_id=_activite_id OR (_activite_id IS NULL AND pj.activite_id IS NOT NULL))
	;
  call pai_valide_logger('pai_valide_delete_activite', 'pai_valide_delete_activite');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_rh_activite;
CREATE PROCEDURE `pai_valide_rh_activite`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _activite_id INT)
BEGIN
	IF (_validation_id IS NULL) THEN
  	INSERT INTO pai_validation(utilisateur_id) VALUES(1);
    SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_RH_ACTIVITE',concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib,_activite_id));
	END IF;

  CALL pai_valide_delete_activite('RA', _depot_id, _flux_id, _date_distrib,_activite_id);
	
	-- Activité hors contrat
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 5,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id ,NULL,a.id
	FROM pai_activite a
  INNER JOIN ref_activite ra on a.activite_id=ra.id and not ra.est_pleiades
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and m.date_fin
	WHERE a.date_extrait is null
	AND (a.depot_id=_depot_id OR _depot_id IS NULL)
	AND (a.flux_id=_flux_id OR _flux_id IS NULL)
	AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (a.id=_activite_id OR _activite_id IS NULL)
	AND a.employe_id IS NOT NULL
  AND a.activite_id>=-1
	AND NOT EXISTS(SELECT NULL 
                    FROM emp_pop_depot epd 
                    WHERE a.employe_id=epd.employe_id 
                    AND a.date_distrib BETWEEN epd.date_debut AND epd.date_fin 
                    AND a.depot_id=epd.depot_id 
                    AND a.flux_id=epd.flux_id)
	AND NOT EXISTS(SELECT NULL 
                  FROM emp_pop_depot epd
				  INNER JOIN emp_affectation eaf ON epd.contrat_id=eaf.contrat_id AND epd.depot_id=eaf.depot_org_id
                  WHERE a.employe_id=epd.employe_id 
                  AND a.date_distrib BETWEEN epd.date_debut AND epd.date_fin
                  AND a.date_distrib BETWEEN eaf.date_debut AND eaf.date_fin
                  AND a.depot_id=eaf.depot_dst_id 
                  AND a.flux_id=eaf.flux_id)
	;
  call pai_valide_logger('pai_valide_rh_activite', 'Activite presse hors contrat');
  
	-- Activite presse sur contrat hors-presse
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 40,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id ,NULL,a.id
	FROM pai_activite a
  -- 21/01/2016 pour prendre en compte les formations hors-presse
  INNER JOIN ref_activite ra on a.activite_id=ra.id and not ra.est_hors_presse -- ra.est_pleiades
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and m.date_fin
  INNER JOIN emp_pop_depot epd on a.employe_id=epd.employe_id and a.date_distrib between epd.date_debut and epd.date_fin
	WHERE a.date_extrait is null
	AND (a.depot_id=_depot_id OR _depot_id IS NULL)
	AND (a.flux_id=_flux_id OR _flux_id IS NULL)
	AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (a.id=_activite_id OR _activite_id IS NULL)
	AND a.employe_id IS NOT NULL
  AND a.activite_id>0
	and exists(select null
            from emp_contrat_hp ech
            inner join pai_png_xta_rcactivite xrc on ech.xta_rcactivte=xrc.oid and xrc.code='RC'
            where a.employe_id=ech.employe_id
            and a.date_distrib between ech.date_debut and ech.date_fin
            )
	;
  call pai_valide_logger('pai_valide_rh_activite', 'Activite presse sur contrat hors-presse');
  
	-- Employé avec STC
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 39,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id ,NULL,a.id,concat('STC effectué le ',date_format(ps.date_extrait,'%d/%m/%Y %k:%i:%s'))
	FROM pai_activite a
  INNER JOIN ref_activite ra on a.activite_id=ra.id and not ra.est_pleiades
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and m.date_fin
  INNER JOIN emp_pop_depot epd on a.employe_id=epd.employe_id and a.date_distrib between epd.date_debut and epd.date_fin
  INNER JOIN pai_stc ps on epd.rcoid=ps.rcoid and ps.date_extrait is not null
	WHERE a.date_extrait is null
	AND (a.depot_id=_depot_id OR _depot_id IS NULL)
	AND (a.flux_id=_flux_id OR _flux_id IS NULL)
	AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (a.id=_activite_id OR _activite_id IS NULL)
	AND a.employe_id IS NOT NULL
  AND a.activite_id>0
	;
  call pai_valide_logger('pai_valide_rh_tournee', 'Activite sur STC');
	-- Activité hors cycle
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 6,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id ,NULL, a.id, cycle_to_string(ec.lundi,ec.mardi,ec.mercredi,ec.jeudi,ec.vendredi,ec.samedi,ec.dimanche)
	FROM pai_activite a
  INNER JOIN ref_activite ra on a.activite_id=ra.id and not ra.est_pleiades
  INNER JOIN emp_pop_depot epd ON a.employe_id=epd.employe_id AND a.date_distrib BETWEEN epd.date_debut AND epd.date_fin 
  LEFT OUTER JOIN emp_cycle ec ON a.employe_id=ec.employe_id and a.date_distrib BETWEEN ec.date_debut AND ec.date_fin 
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and m.date_fin
	WHERE a.date_extrait is null
	AND (a.depot_id=_depot_id OR _depot_id IS NULL)
	AND (a.flux_id=_flux_id OR _flux_id IS NULL)
	AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (a.id=_activite_id OR _activite_id IS NULL)
  AND a.activite_id>0
  AND epd.typetournee_id in (0,1,2) -- pas pour les encadrants
	AND NOT EXISTS(SELECT NULL 
                  FROM emp_cycle ec
                  WHERE a.employe_id=ec.employe_id 
                  AND a.date_distrib BETWEEN ec.date_debut AND ec.date_fin 
                  AND CASE DAYOFWEEK(a.date_distrib)
                      WHEN 1 THEN ec.dimanche
                      WHEN 2 THEN ec.lundi
                      WHEN 3 THEN ec.mardi
                      WHEN 4 THEN ec.mercredi
                      WHEN 5 THEN ec.jeudi
                      WHEN 6 THEN ec.vendredi
                      WHEN 7 THEN ec.samedi
                      END
                      )
	;
  call pai_valide_logger('pai_valide_rh_activite', 'Activite presse hors cycle');
  
  -- Activité hors-presse
	-- Employé hors cycle
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 35,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id,NULL ,a.id
	FROM pai_activite a
  INNER JOIN ref_activite ra on a.activite_id=ra.id and ra.est_pleiades
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and m.date_fin
	WHERE a.date_extrait is null and a.date_distrib>='2015-04-21'
	AND (a.depot_id=_depot_id OR _depot_id IS NULL)
	AND (a.flux_id=_flux_id OR _flux_id IS NULL)
	AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (a.id=_activite_id OR _activite_id IS NULL)
  and not exists(select null 
                from emp_contrat_hp ech
                inner join ref_activite ra on ech.activite_id=ra.id and ra.est_pleiades
                where a.date_distrib between ech.date_debut AND ech.date_fin
                and a.activite_id=ra.id
                and a.employe_id=ech.employe_id 
                AND a.depot_id=ech.depot_id -- Gestion dans un autre dépot !!!
                AND a.flux_id=ech.flux_id
                AND a.xaoid=ech.xaoid
                )
	--  AND epd.typetournee_id in (1,2)
  ;
  call pai_valide_logger('pai_valide_rh_activite', 'Activité hors-presse hors contrat');

	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 36,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id ,NULL,a.id, cycle_to_string(ech.lundi,ech.mardi,ech.mercredi,ech.jeudi,ech.vendredi,ech.samedi,ech.dimanche)
	FROM pai_activite a
  INNER JOIN ref_activite ra on a.activite_id=ra.id and ra.est_pleiades
  INNER JOIN emp_pop_depot epd ON a.employe_id=epd.employe_id AND a.date_distrib BETWEEN epd.date_debut AND epd.date_fin 
  INNER JOIN emp_contrat_hp ech ON a.xaoid=ech.xaoid 
  LEFT OUTER JOIN emp_cycle ec ON a.employe_id=ec.employe_id and a.date_distrib BETWEEN ec.date_debut AND ec.date_fin 
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and m.date_fin
	WHERE a.date_extrait is null and a.date_distrib>='2015-04-21'
	AND (a.depot_id=_depot_id OR _depot_id IS NULL)
	AND (a.flux_id=_flux_id OR _flux_id IS NULL)
	AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (a.id=_activite_id OR _activite_id IS NULL)
	AND epd.typetournee_id in (0,1,2)
  AND (CASE DAYOFWEEK(a.date_distrib)
      WHEN 1 THEN ech.dimanche
      WHEN 2 THEN ech.lundi
      WHEN 3 THEN ech.mardi
      WHEN 4 THEN ech.mercredi
      WHEN 5 THEN ech.jeudi
      WHEN 6 THEN ech.vendredi
      WHEN 7 THEN ech.samedi
      END)=0
  ;  
call pai_valide_logger('pai_valide_rh_activite', 'Activité hors-presse hors cycle');

	-- Employé avec STC
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 51,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id ,NULL,a.id,concat('STC effectué le ',date_format(ps.date_extrait,'%d/%m/%Y %k:%i:%s'))
	FROM pai_activite a
  INNER JOIN ref_activite ra on a.activite_id=ra.id and  ra.est_pleiades
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and m.date_fin
  INNER JOIN emp_pop_depot epd on a.employe_id=epd.employe_id and a.date_distrib between epd.date_debut and epd.date_fin
  INNER JOIN pai_stc ps on epd.rcoid=ps.rcoid and ps.date_extrait is not null
	WHERE a.date_extrait is null
	AND (a.depot_id=_depot_id OR _depot_id IS NULL)
	AND (a.flux_id=_flux_id OR _flux_id IS NULL)
	AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (a.id=_activite_id OR _activite_id IS NULL)
	AND a.employe_id IS NOT NULL
	;
call pai_valide_logger('pai_valide_rh_activite', 'Activité hors-presse sur STC');
/*  
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 37,_validation_id,epd.depot_id,epd.flux_id,m.anneemois,pc.datecal,epd.employe_id ,null,NULL
  FROM pai_png_xrcautreactivit xa
  INNER JOIN emp_pop_depot epd ON epd.rcoid=xa.relationcontrat
	INNER JOIN pai_mois m ON epd.flux_id=m.flux_id
  INNER JOIN pai_ref_calendrier pc ON pc.datecal between m.date_debut and m.date_fin and pc.datecal  between xa.begin_date and xa.end_date
	WHERE xa.end_date>=m.date_debut
	AND (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
	AND (_date_distrib between xa.begin_date and xa.end_date OR _date_distrib IS NULL)
  and (epd.id=_activite_id OR _activite_id IS NULL)
	and not exists(select null
                  from pai_activite a
                  INNER JOIN ref_activite ra on a.activite_id=ra.id and ra.est_hors_presse
                  where a.date_distrib BETWEEN epd.date_debut AND epd.date_fin
                    AND a.depot_id=epd.depot_id 
                    AND a.flux_id=epd.flux_id)
  and (CASE DAYOFWEEK(pc.datecal)
      WHEN 1 THEN xa.trvdimanche
      WHEN 2 THEN xa.trvlundi
      WHEN 3 THEN xa.trvmardi
      WHEN 4 THEN xa.trvmercredi
      WHEN 5 THEN xa.trvjeudi
      WHEN 6 THEN xa.trvvendredi
      WHEN 7 THEN xa.trvsamedi
      END)=1
      and pc.datecal<now()
  ;  
call pai_valide_logger('pai_valide_rh_activite', 'Activité hors-presse manquante sur cycle');
*/
  CALL pai_valide_delete_activite('AO', _depot_id, _flux_id, _date_distrib,null);
	-- Ouverture de centre seulement pour polyvalent Proximy
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 50,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id ,NULL, a.id, null
	FROM pai_activite a
  INNER JOIN emp_pop_depot epd ON a.employe_id=epd.employe_id AND a.date_distrib BETWEEN epd.date_debut AND epd.date_fin 
  INNER JOIN ref_population rp on epd.population_id=rp.id                  
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and m.date_fin
	WHERE a.date_extrait is null
	AND (a.depot_id=_depot_id OR _depot_id IS NULL)
	AND (a.flux_id=_flux_id OR _flux_id IS NULL)
	AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
--  AND (a.id=_activite_id OR _activite_id IS NULL)
  AND a.ouverture
  and not rp.ouverture
  ;
call pai_valide_logger('pai_valide_rh_activite', 'Ouverture de centre non autorisée');
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_activite;
CREATE PROCEDURE `pai_valide_activite`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _activite_id INT)
BEGIN
/*
	AC	VEH	Mode de transport incoherent avec celui de l’individu	X		X
*/

	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_ACTIVITE',concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib,_activite_id));
	END IF;
  
	CALL pai_valide_rh_activite(_validation_id, _depot_id, _flux_id, _date_distrib, _activite_id);
	CALL pai_valide_octime_activite(_validation_id, _depot_id, _flux_id, _date_distrib, _activite_id);

	CALL pai_valide_delete_activite('AC', _depot_id, _flux_id, _date_distrib, _activite_id);

  -- Acitivite incomplète
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 17,_validation_id,a.depot_id,a.flux_id,m.anneemois,a.date_distrib,a.employe_id ,NULL,a.id
	FROM pai_activite a
  INNER JOIN ref_activite ra on a.activite_id=ra.id
	INNER JOIN pai_ref_mois m ON a.date_distrib between m.date_debut and m.date_fin
	WHERE a.date_extrait is null
  AND a.activite_id>0
  AND (a.depot_id=_depot_id OR _depot_id IS NULL)
  AND (a.flux_id=_flux_id OR _flux_id IS NULL)
	AND (a.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (a.id=_activite_id OR _activite_id IS NULL)
	AND  (a.employe_id IS NULL
	OR    a.transport_id IS NULL
	OR    a.duree IS NULL OR (a.duree=0 and not ra.est_hors_presse)
	OR    a.nbkm_paye IS NULL
	)
	;
  call pai_valide_logger('pai_valide_activite', 'Activite incomplete');
END;
