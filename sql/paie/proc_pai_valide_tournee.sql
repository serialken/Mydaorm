SET NAMES default;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_delete_tournee;
CREATE PROCEDURE `pai_valide_delete_tournee`(IN _rubrique varchar(2), IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _tournee_id INT)
BEGIN
	DELETE pj
  FROM pai_journal pj
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
	WHERE pj.date_extrait is null
  AND pe.rubrique=_rubrique
	AND (pj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pj.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pj.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND ((_tournee_id IS NULL AND pj.tournee_id IS NOT NULL) OR pj.tournee_id=_tournee_id)
	AND pj.produit_id IS NULL
	;
  call pai_valide_logger('pai_valide_delete_tournee', 'pai_valide_delete_tournee');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_delete_tournee_2;
CREATE PROCEDURE `pai_valide_delete_tournee_2`(IN _rubrique varchar(2), IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE)
BEGIN
	DELETE pj
  FROM pai_journal pj
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
	WHERE pj.date_extrait is null
  AND pe.rubrique=_rubrique
	AND (pj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pj.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pj.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND pj.produit_id IS NULL
	;
  call pai_valide_logger('pai_valide_delete_tournee', 'pai_valide_delete_tournee');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_rh_tournee;
CREATE PROCEDURE `pai_valide_rh_tournee`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _tournee_id INT)
BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_RH_TOURNEE',concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib,_tournee_id));
	END IF;

  CALL pai_valide_delete_tournee('RT', _depot_id, _flux_id, _date_distrib,_tournee_id);
	
	-- Employé hors contrat
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 1,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id ,t.id,NULL
	FROM pai_tournee t
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
	WHERE t.date_extrait is null
  AND (t.tournee_org_id is null or t.split_id is not null)
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (t.id=_tournee_id OR _tournee_id IS NULL)
  AND t.employe_id is not null
	AND NOT EXISTS(SELECT NULL 
                    FROM emp_pop_depot epd 
                    WHERE t.employe_id=epd.employe_id 
                    AND t.date_distrib BETWEEN epd.date_debut AND epd.date_fin
                    AND t.depot_id=epd.depot_id 
                    AND t.flux_id=epd.flux_id)
	AND NOT EXISTS(SELECT NULL 
                  FROM emp_pop_depot epd
				  INNER JOIN emp_affectation eaf ON epd.contrat_id=eaf.contrat_id AND epd.depot_id=eaf.depot_org_id
                  WHERE t.employe_id=epd.employe_id 
                  AND t.date_distrib BETWEEN epd.date_debut AND epd.date_fin
                  AND t.date_distrib BETWEEN eaf.date_debut AND eaf.date_fin
                  AND t.depot_id=eaf.depot_dst_id 
                  AND t.flux_id=eaf.flux_id)
	;
  call pai_valide_logger('pai_valide_rh_tournee', 'Tournee hors contrat');
  
	-- Tournee sur contrat hors-presse
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 41,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id ,t.id,NULL
	FROM pai_tournee t
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
  INNER JOIN emp_pop_depot epd on t.employe_id=epd.employe_id and t.date_distrib between epd.date_debut and epd.date_fin
	WHERE t.date_extrait is null
  AND (t.tournee_org_id is null or t.split_id is not null)
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (t.id=_tournee_id OR _tournee_id IS NULL)
  AND t.employe_id is not null
	and exists(select null
            from emp_contrat_hp ech
            inner join pai_png_xta_rcactivite xrc on ech.xta_rcactivte=xrc.oid and xrc.code='RC'
            where t.employe_id=ech.employe_id
            and t.date_distrib between ech.date_debut and ech.date_fin
            )
	;
  call pai_valide_logger('pai_valide_rh_tournee', 'Tournee sur contrat hors-presse');
  
	-- Employé avec STC
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 38,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id ,t.id,NULL,concat('STC effectué le ',date_format(ps.date_extrait,'%d/%m/%Y %k:%i:%s'))
	FROM pai_tournee t
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
  INNER JOIN emp_pop_depot epd on t.employe_id=epd.employe_id and t.date_distrib between epd.date_debut and epd.date_fin
  INNER JOIN pai_stc ps on epd.rcoid=ps.rcoid and ps.date_extrait is not null
	WHERE t.date_extrait is null
  AND (t.tournee_org_id is null or t.split_id is not null)
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (t.id=_tournee_id OR _tournee_id IS NULL)
  AND t.employe_id is not null
	;
  call pai_valide_logger('pai_valide_rh_tournee', 'Tournee sur STC');
  
	-- Employé hors cycle
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
	SELECT DISTINCT 2,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id ,t.id,NULL, cycle_to_string(ec.lundi,ec.mardi,ec.mercredi,ec.jeudi,ec.vendredi,ec.samedi,ec.dimanche)
	FROM pai_tournee t
  INNER JOIN emp_pop_depot epd ON t.employe_id=epd.employe_id AND t.date_distrib BETWEEN epd.date_debut AND epd.date_fin
  LEFT OUTER JOIN emp_cycle ec ON t.employe_id=ec.employe_id and t.date_distrib BETWEEN ec.date_debut AND ec.date_fin 
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
	WHERE t.date_extrait is null
  AND (t.tournee_org_id is null or t.split_id is not null)
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (t.id=_tournee_id OR _tournee_id IS NULL)
	AND epd.typetournee_id in (0,1,2)
	AND NOT EXISTS(SELECT NULL 
                    FROM emp_cycle ec
                    WHERE t.employe_id=ec.employe_id 
                    AND t.date_distrib BETWEEN ec.date_debut AND ec.date_fin 
                    AND CASE DAYOFWEEK(t.date_distrib)
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
  call pai_valide_logger('pai_valide_rh_tournee', 'Tournee hors cycle');

  -- Tournée incomplète
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 9,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id ,t.id,NULL
	FROM pai_tournee t
  LEFT OUTER JOIN emp_pop_depot epd ON t.employe_id=epd.employe_id AND t.date_distrib BETWEEN epd.date_debut AND epd.date_fin
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
	WHERE t.date_extrait is null
  AND (t.tournee_org_id is null or t.split_id is not null)
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (t.id=_tournee_id OR _tournee_id IS NULL)
	AND (t.employe_id IS NULL
  OR   t.transport_id IS NULL
--	OR  (epd.typetournee_id in (1,2) AND (t.valrem_paie IS NULL OR t.valrem_paie=0))
--	OR  (epd.typetournee_id in (1,2) AND (t.duree IS NULL OR t.duree=0))
	OR  (epd.typetournee_id in (0,1,2,4) AND (t.nbcli=0 and t.nbrep=0 and t.nbprod=0))
	OR   t.nbkm IS NULL
	OR   t.nbkm_paye IS NULL
	)
  AND t.depot_id<>21 -- PCO
	;
  call pai_valide_logger('pai_valide_rh_tournee', 'Tournee incomplete');
  
  -- Valeur de rémunération non renseignée
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 47,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id ,t.id,NULL
	FROM pai_tournee t
  LEFT OUTER JOIN emp_pop_depot epd ON t.employe_id=epd.employe_id AND t.date_distrib BETWEEN epd.date_debut AND epd.date_fin
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
	WHERE t.date_extrait is null
  AND (t.tournee_org_id is null or t.split_id is not null)
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (t.id=_tournee_id OR _tournee_id IS NULL)
	AND epd.typetournee_id in (1,2) AND (t.valrem_paie IS NULL OR t.valrem_paie=0)
  AND t.depot_id<>21 -- PCO
	;
  call pai_valide_logger('pai_valide_rh_tournee', 'Valeur de rémunération non renseignée');
  
	-- Type tournée incompatible
/*	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 3,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id ,t.id,NULL
	FROM pai_tournee t
  INNER JOIN emp_pop_depot epd ON t.employe_id=epd.employe_id AND t.date_distrib BETWEEN epd.date_debut AND epd.date_fin
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
	WHERE t.date_extrait is null
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (t.id=_tournee_id OR _tournee_id IS NULL)
	AND epd.typetournee_id in (0,1,2)
  AND t.typetournee_id<>epd.typetournee_id
  AND t.typetournee_id<>4
  ;
  call pai_valide_logger('pai_valide_rh_tournee', 'Type tournee incompatible');
  */
	-- Tournee realisee par encadrant
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 4,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id ,t.id,NULL
	FROM pai_tournee t
  INNER JOIN emp_pop_depot epd ON t.employe_id=epd.employe_id AND t.date_distrib BETWEEN epd.date_debut AND epd.date_fin
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
	WHERE t.date_extrait is null
  AND (t.tournee_org_id is null or t.split_id is not null)
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (t.id=_tournee_id OR _tournee_id IS NULL)
	AND epd.typetournee_id in (3)
  ;
  call pai_valide_logger('pai_valide_rh_tournee', 'Tournee realisee par encadrant');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_tournee;
CREATE PROCEDURE `pai_valide_tournee`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _tournee_id INT)
BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_TOURNEE',concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib,_tournee_id));
	END IF;
  
	CALL pai_valide_rh_tournee(_validation_id, _depot_id, _flux_id, _date_distrib, _tournee_id);
	CALL pai_valide_octime_tournee(_validation_id, _depot_id, _flux_id, _date_distrib, _tournee_id);
	CALL pai_valide_reclamation(_validation_id, _depot_id, _flux_id, _date_distrib, _tournee_id);

  CALL pai_valide_delete_tournee('TO', _depot_id, _flux_id, _date_distrib,_tournee_id);


  -- Modèle introuvable
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 34,_validation_id,pt.depot_id,pt.flux_id,m.anneemois,pt.date_distrib,pt.employe_id ,pt.id,NULL
	FROM pai_tournee pt
  LEFT OUTER JOIN emp_pop_depot epd ON pt.employe_id=epd.employe_id AND pt.date_distrib BETWEEN epd.date_debut AND epd.date_fin
	INNER JOIN pai_ref_mois m ON pt.date_distrib between m.date_debut and m.date_fin
	WHERE pt.date_extrait is null
	AND (pt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pt.flux_id=_flux_id OR _flux_id IS NULL)
  AND (pt.date_distrib=_date_distrib OR _date_distrib IS NULL)
  and (pt.id=_tournee_id OR _tournee_id IS NULL)
	AND not exists(select null from modele_tournee_jour mtj where pt.modele_tournee_jour_id=mtj.id and pt.date_distrib between mtj.date_debut and mtj.date_fin)
	;
  call pai_valide_logger('pai_valide_tournee', 'Modele introuvable');

  -- Somme des quantités différentes sur tournée splitée
 /* DROP TEMPORARY TABLE IF EXISTS pai_splittee;
  CREATE TEMPORARY TABLE pai_splittee(
  tournee_id   int
  ,produit_id  int
  ,natureclient_id       int
  ) engine=memory
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
  CREATE INDEX pai_splittee_idx1 ON pai_splittee(tournee_id);

  INSERT INTO pai_splittee(tournee_id,produit_id,natureclient_id)
  SELECT DISTINCT pt.tournee_org_id,ppt.produit_id,ppt.natureclient_id
  FROM pai_tournee pt
  INNER JOIN pai_prd_tournee ppt ON ppt.tournee_id=pt.id
  where pt.date_extrait is null
  AND pt.tournee_org_id is not null
	AND (pt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pt.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pt.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (pt.id=_tournee_id OR _tournee_id IS NULL)
  ;
  INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 10,_validation_id,pt.depot_id,pt.flux_id,m.anneemois,pt.date_distrib,pt.employe_id ,pt.id,NULL
  from pai_splittee ppt
  INNER JOIN pai_tournee pt on ppt.tournee_id=pt.id -- pour prendre également les produits des tournées filles
	INNER JOIN pai_ref_mois m ON pt.date_distrib between m.date_debut and m.date_fin
  LEFT OUTER JOIN pai_prd_tournee ppto ON ppto.tournee_id=pt.id and ppto.produit_id=ppt.produit_id and ppto.natureclient_id=ppt.natureclient_id
  where (coalesce(ppto.qte,0),coalesce(ppto.nbcli,0),coalesce(ppto.nbrep,0)) not in (select sum(pptf.qte),sum(pptf.nbcli),sum(pptf.nbrep)
                                          from pai_prd_tournee pptf
                                          inner join pai_tournee ptf on pptf.tournee_id=ptf.id
                                          where pt.tournee_org_id=ptf.tournee_org_id and ptf.split_id is not null
                                          and ppt.produit_id=pptf.produit_id and ppt.natureclient_id=pptf.natureclient_id
                                          )
  ;
  DROP TEMPORARY TABLE IF EXISTS pai_splittee;
  call pai_valide_logger('pai_valide_tournee', 'Somme des quantites differentes sur tournee splitee');
  */
  -- Employé semaine différent de employé jour pour les tournée Néo/Média
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 11,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id ,t.id,NULL
	FROM pai_tournee t
  INNER JOIN emp_pop_depot epd on epd.employe_id=t.employe_id and t.date_distrib between epd.date_debut and epd.date_fin
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
	INNER JOIN modele_tournee_jour mtj ON mtj.id=t.modele_tournee_jour_id and t.date_distrib between mtj.date_debut and mtj.date_fin
	WHERE t.date_extrait is null
  AND (t.tournee_org_id is null/* or t.split_id is not null*/) -- on ne contrôle pas sur tournée splittée
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
  AND (t.id=_tournee_id OR _tournee_id IS NULL)
  AND epd.typetournee_id=2 AND (mtj.employe_id<>t.employe_id)
	;
  call pai_valide_logger('pai_valide_tournee', 'Employe different de titulaire sur une tournee Media');

  -- Dimanche renseigné sur une tournée Néo/Média
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id)
	SELECT DISTINCT 12,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,t.employe_id ,t.id,NULL
	FROM pai_tournee t
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
	where t.date_extrait is null
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	and (t.date_distrib=_date_distrib or _date_distrib is null)
  AND (t.id=_tournee_id or _tournee_id is null)
	and t.flux_id=2 and t.jour_id=1
	;
  call pai_valide_logger('pai_valide_tournee', 'Tournee Media sur une journee dimanche');
  
  if _tournee_id is not null then
    select depot_id,flux_id,date_distrib into _depot_id, _flux_id, _date_distrib from pai_tournee where id=_tournee_id and _tournee_id is not null;
  end if;
  
  CALL pai_valide_delete_tournee_2('TM', _depot_id, _flux_id, _date_distrib);
  -- Valeur de rémunération majorée
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,commentaire)
  SELECT DISTINCT 52,_validation_id,pt.depot_id,pt.flux_id,m.anneemois,pt.date_distrib,NULL,NULL,NULL,concat_ws(' ',count(*),'tournée(s)')
	FROM pai_tournee pt
  LEFT OUTER JOIN emp_pop_depot epd ON pt.employe_id=epd.employe_id AND pt.date_distrib BETWEEN epd.date_debut AND epd.date_fin
	INNER JOIN pai_ref_mois m ON pt.date_distrib between m.date_debut and m.date_fin
	WHERE pt.date_extrait is null
  AND pt.majoration<>0
	AND (pt.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pt.flux_id=_flux_id OR _flux_id IS NULL)
	and (pt.date_distrib=_date_distrib or _date_distrib is null)
  group by pt.date_distrib,pt.depot_id,pt.flux_id
	;
  call pai_valide_logger('pai_valide_tournee', 'Valeur de rémunération majorée');
end;
/*
-- delete from pai_journal where erreur_id=52
select * from pai_int_log where idtrt=1 order by id desc;
*/