/*
call pai_valide_octime(@id , null, null, null);

select * from pai_int_log where idtrt=1 order by id desc limit 1000;
select * from pai_journal where validation_id=10639;
--truncate table pai_journal;
delete from pai_int_log where idtrt=1;
*/
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_octime_tournee;
CREATE PROCEDURE pai_valide_octime_tournee(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _tournee_id INT)
BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_OCTIME_TOURNEE',concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib,_employe_id));
	END IF;

  CALL pai_valide_delete_tournee('OC', _depot_id, _flux_id, _date_distrib,_tournee_id);
  
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT 32,_validation_id,pt.depot_id,pt.flux_id,m.anneemois,pt.date_distrib,pt.employe_id ,pt.id,NULL,poa.abs_lib
	FROM pai_tournee pt
  INNER JOIN employe e on pt.employe_id=e.id
	INNER JOIN pai_ref_mois m ON pt.date_distrib between m.date_debut and m.date_fin
  INNER JOIN pai_oct_saiabs pos ON e.matricule=pos.pers_mat AND pt.date_distrib BETWEEN pos.abs_dat AND pos.abs_fin
  INNER JOIN pai_oct_absence poa ON poa.abs_cod=if(RIGHT(pos.abs_cod,2)  REGEXP '^[0-9]+$',substr(pos.abs_cod,1,2),pos.abs_cod)
	WHERE pt.date_extrait is null
  AND (pt.tournee_org_id is null or pt.split_id is not null)
	AND (pt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pt.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pt.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (pt.id=_tournee_id OR _tournee_id IS NULL)
  and pos.abs_cod NOT IN ('1MAI','JFER','AT99');
  call pai_valide_logger('pai_valide_octime_tournee', 'Tournee pendant absence Octime');
END;
  
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_octime_activite;
CREATE PROCEDURE pai_valide_octime_activite(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _activite_id INT)
BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_OCTIME_ACTIVITE',concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib,_employe_id));
	END IF;

  CALL pai_valide_delete_activite('OC', _depot_id, _flux_id, _date_distrib,_activite_id);

	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT 33,_validation_id,pa.depot_id,pa.flux_id,m.anneemois,pa.date_distrib,pa.employe_id ,NULL, pa.id, poa.abs_lib
	FROM pai_activite pa
  INNER JOIN ref_activite ra on pa.activite_id=ra.id
  INNER JOIN employe e on pa.employe_id=e.id
	INNER JOIN pai_ref_mois m ON pa.date_distrib between m.date_debut and m.date_fin
  INNER JOIN pai_oct_saiabs pos ON e.matricule=pos.pers_mat AND pa.date_distrib BETWEEN pos.abs_dat AND pos.abs_fin
  INNER JOIN pai_oct_absence poa ON poa.abs_cod=if(RIGHT(pos.abs_cod,2)  REGEXP '^[0-9]+$',substr(pos.abs_cod,1,2),pos.abs_cod)
	WHERE pa.date_extrait is null
	AND (pa.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pa.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pa.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (pa.id=_activite_id OR _activite_id IS NULL)
  AND pa.activite_id>0
  and (not ra.est_hors_travail)
  and (not ra.est_hors_presse or time_to_sec(pa.duree)>0)
  and pos.abs_cod NOT IN ('1MAI','JFER','AT99');
  call pai_valide_logger('pai_valide_octime_activite', 'Activite pendant absence Octime');

	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT 37,_validation_id,pa.depot_id,pa.flux_id,m.anneemois,pa.date_distrib,pa.employe_id ,NULL, pa.id, NULL
	FROM pai_activite pa
  INNER JOIN ref_activite ra on pa.activite_id=ra.id
  INNER JOIN employe e on pa.employe_id=e.id
	INNER JOIN pai_ref_mois m ON pa.date_distrib between m.date_debut and m.date_fin
	WHERE pa.date_extrait is null
	AND (pa.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pa.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pa.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (pa.id=_activite_id OR _activite_id IS NULL)
  AND pa.activite_id>0
  and (ra.est_hors_presse and time_to_sec(pa.duree)=0)
  and not exists(select null
                  from pai_oct_saiabs pos
                  where e.matricule=pos.pers_mat AND pa.date_distrib BETWEEN pos.abs_dat AND pos.abs_fin
                  and pos.abs_cod NOT IN ('1MAI','JFER','AT99')
                  )
  ;
  call pai_valide_logger('pai_valide_octime_activite', 'Activite hors-presse à 0 sans absence Octime');
END;
  
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_octime;
CREATE PROCEDURE pai_valide_octime(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE)
BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO modele_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_OCTIME',concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib));
	END IF;

	call pai_valide_octime_tournee(_validation_id , _depot_id, _flux_id , _date_distrib, null);
	call pai_valide_octime_activite(_validation_id , _depot_id, _flux_id , _date_distrib, null);

/*
  -- Dépassement de l''horaire contractuel
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 29,_validation_id,psh.depot_id,psh.flux_id,psh.anneemois,null,psh.employe_id ,NULL,NULL
  from pai_suivi_horaire psh
	INNER JOIN pai_ref_mois m ON psh.date_distrib between m.date_debut and m.date_fin
  inner join emp_pop_depot epd on psh.employe_id=epd.employe_id and psh.date_distrib between greatest(epd.date_debut,m.date_debut) and least(epd.date_fin,m.date_fin)
  WHERE psh.date_extrait is null
  AND (psh.depot_id=_depot_id OR _depot_id IS NULL)
	AND (psh.flux_id=_flux_id OR _flux_id IS NULL)
  AND (psh.employe_id=_employe_id OR _employe_id IS NULL)
  and epd.nbheures_garanties>0
  group by epd.id,greatest(epd.date_debut,m.date_debut),least(epd.date_fin,m.date_fin),epd.nbheures_garanties
  having coalesce(sum(time_to_sec(psh.duree))/3600,0)>epd.nbheures_garanties
  ;
  call pai_valide_logger('pai_valide_heure', 'Dépassement de l''horaire contractuel');

  SET @last_date_distrib  = NULL, @last_saloid = NULL, @count = 0;
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 30,_validation_id,epd.depot_id,epd.flux_id,m.anneemois,psh.date_distrib,psh.employe_id ,NULL,NULL
  from(
      SELECT psh.saloid,psh.employe_id,psh.date_distrib,
          @count := IF(@last_saloid  = psh.saloid,
                        CASE 
                            WHEN @last_date_distrib  = psh.date_distrib THEN @count
                            WHEN date_add(@last_date_distrib, INTERVAL +1 DAY)  = psh.date_distrib THEN @count + 1
                            ELSE 1
                        END
                      , 1) as nb,
          @last_saloid  := psh.saloid,
          @last_date_distrib  := psh.date_distrib 
      FROM (
      -- ATTENTION : balaye tous les 45 derniers jours de la table !!!!!
            select saloid, employe_id ,date_distrib 
            from pai_tournee pt
            inner join employe e on pt.employe_id=e.id
            WHERE (pt.employe_id=_employe_id OR _employe_id IS NULL)
            AND (pt.tournee_org_id is null or pt.split_id is not null)
            AND pt.date_distrib>=date_add(now(),Interval -45 DAY)
            AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pt.id=pj.tournee_id AND NOT pe.valide)
            union
            select saloid, employe_id ,date_distrib 
            from pai_activite pa
            inner join employe e on pa.employe_id=e.id
            WHERE (pa.employe_id=_employe_id OR _employe_id IS NULL)
            AND pa.date_distrib>=date_add(now(),Interval -45 DAY)
            AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE pa.id=pj.activite_id AND NOT pe.valide)
            AND pa.activite_id>0
            ) psh
      ORDER BY psh.saloid,psh.date_distrib
      ) psh
  inner join emp_pop_depot epd on psh.employe_id=epd.employe_id and psh.date_distrib between epd.date_debut and epd.date_fin
	INNER JOIN pai_ref_mois m ON psh.date_distrib between m.date_debut and m.date_fin
  where nb>6;
  call pai_valide_logger('pai_valide_heure', 'L''employe a travaille plus de 6j consecutif');
     */
     END;
