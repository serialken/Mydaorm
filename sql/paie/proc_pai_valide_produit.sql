-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_delete_produit;
CREATE PROCEDURE `pai_valide_delete_produit`(IN _rubrique varchar(2), IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _tournee_id INT, IN _produit_id INT)
BEGIN
	DELETE pj
  FROM pai_journal pj
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
	WHERE pj.date_extrait is null
  AND pe.rubrique=_rubrique
	AND (pj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pj.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pj.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND (pj.tournee_id=_tournee_id OR _tournee_id IS NULL)
	AND ((_produit_id IS NULL AND pj.produit_id IS NOT NULL) OR produit_id=_produit_id)
	;
  call pai_valide_logger('pai_valide_delete_produit', 'pai_valide_delete_produit');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_delete_produit_org;
CREATE PROCEDURE `pai_valide_delete_produit_org`(IN _rubrique varchar(2), IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _tournee_id INT, IN _produit_id INT)
BEGIN
	DELETE pj
  FROM pai_journal pj
  LEFT OUTER JOIN pai_tournee pto on pto.id=_tournee_id and split_id is not null
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
	WHERE pj.date_extrait is null
  AND pe.rubrique=_rubrique
	AND (pj.depot_id=_depot_id OR _depot_id IS NULL)
	AND (pj.flux_id=_flux_id OR _flux_id IS NULL)
	AND (pj.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND (pj.tournee_id=pto.tournee_org_id OR pto.tournee_org_id IS NULL)
	AND ((_produit_id IS NULL AND pj.produit_id IS NOT NULL) OR produit_id=_produit_id)
	;
  call pai_valide_logger('pai_valide_delete_produit', 'pai_valide_delete_produit');
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS pai_valide_produit;
CREATE PROCEDURE `pai_valide_produit`(INOUT _validation_id INT, IN _depot_id INT, IN _flux_id INT, IN _date_distrib DATE, IN _tournee_id INT, IN _produit_id INT)
BEGIN
	IF (_validation_id IS NULL) THEN
		INSERT INTO pai_validation(utilisateur_id) VALUES(1);
		SELECT LAST_INSERT_ID() INTO _validation_id;
    call pai_valide_logger('PAI_VALIDE_PRODUIT',concat_ws('*',_validation_id,_depot_id,_flux_id,_date_distrib,_tournee_id,_produit_id));
	END IF;
  
  CALL pai_valide_delete_produit_org('PR', _depot_id, _flux_id, _date_distrib,_tournee_id,_produit_id);
  
  -- Somme des quantites differentes sur tournee splitee
  DROP TEMPORARY TABLE IF EXISTS pai_splittee;
  CREATE TEMPORARY TABLE pai_splittee(
  tournee_id   int
  ,produit_id  int
  ,natureclient_id       int
  ) engine=memory
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
  CREATE INDEX pai_splittee_idx1 ON pai_splittee(tournee_id);

-- Attention, les tournées splitées ne sont jamais extraites, donc elles sont toujours sélectionnées et ça risque de prendre de plus en plus de temps !!!!
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
  AND (ppt.produit_id=_produit_id OR _produit_id IS NULL)
  ;
  INSERT INTO pai_prd_tournee(tournee_id,produit_id,natureclient_id,utilisateur_id,date_creation,qte,nbcli,nbadr,nbcli_unique,nbrep,duree_supplement,valide,poids)
  SELECT ps.tournee_id,ps.produit_id,ps.natureclient_id,0,NOW(),0,0,0,0,0,'00:00',true,0
  FROM pai_splittee ps
  WHERE NOT exists(select null from pai_prd_tournee ppt where ppt.tournee_id=ps.tournee_id and ppt.produit_id=ps.produit_id and ppt.natureclient_id=ps.natureclient_id)
  ;
  INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,produit_id)
	SELECT DISTINCT 13,_validation_id,pt.depot_id,pt.flux_id,m.anneemois,pt.date_distrib,pt.employe_id ,pt.id,NULL,ppto.id
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
  
  call pai_valide_logger('pai_valide_produit', 'Somme des quantites differentes sur tournee splitee');

  -- produit sans caracteristique poids
/*	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,produit_id)
	SELECT DISTINCT 14,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,NULL,NULL,NULL,ppt.produit_id
  from pai_prd_tournee ppt
  inner join pai_tournee t on ppt.tournee_id=t.id
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
  inner join produit p on ppt.produit_id=p.id
  left outer join emp_pop_depot epd on pt.employe_id=epd.id and pt.date_distrib between epd.date_debut and epd.date_fin
  where t.date_extrait is null and ppt.date_extrait is null
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND (t.id=_tournee_id OR _tournee_id IS NULL)
  AND (ppt.id=_produit_id OR _produit_id IS NULL)
  and (epd.typetournee_id in (1,4) and p.type_id in (2,3) or epd.typetournee_id=2)
  and not exists(select null from prd_caract pc where p.type_id=pc.produit_type_id and pc.code='POIDS')
  ;
  call pai_valide_logger('pai_valide_produit', 'Poids non saisi');
*/
/*
  -- produit sans poids
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,produit_id)
	SELECT DISTINCT 15,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,NULL,NULL,NULL,ppt.produit_id
  from pai_prd_tournee ppt
  inner join pai_tournee t on ppt.tournee_id=t.id
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
  inner join produit p on ppt.produit_id=p.id
  inner join prd_caract pc on p.type_id=pc.produit_type_id and pc.code='POIDS'
  left outer join emp_pop_depot epd on pt.employe_id=epd.id and pt.date_distrib between epd.date_debut and epd.date_fin
  where t.date_extrait is null and ppt.date_extrait is null
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND (t.id=_tournee_id OR _tournee_id IS NULL)
  AND (ppt.id=_produit_id OR _produit_id IS NULL)
  and epd.typetournee_id=2
  and not exists(select null from prd_caract_jour pcj where ppt.produit_id=pcj.produit_id and pcj.prd_caract_id=pc.id and t.date_distrib=pcj.date_distrib and valeur_int is not null)
  and not exists(select null from prd_caract_groupe pcg where ppt.produit_id=pcg.produit_id and pcg.prd_caract_id=pc.id and t.groupe_id=pcg.groupe_id and t.date_distrib=pcg.date_distrib and valeur_int is not null)
  ;
  call pai_valide_logger('pai_valide_produit', 'Poids non saisi');
*/
/*
  -- produit sans referentiel poids
	INSERT INTO pai_journal(erreur_id,validation_id,depot_id,flux_id,anneemois,date_distrib,employe_id,tournee_id,activite_id,produit_id)
	SELECT DISTINCT 16,_validation_id,t.depot_id,t.flux_id,m.anneemois,t.date_distrib,NULL,NULL,NULL,ppt.produit_id
  from pai_prd_tournee ppt
  inner join pai_tournee t on ppt.tournee_id=t.id
	INNER JOIN pai_ref_mois m ON t.date_distrib between m.date_debut and m.date_fin
  inner join produit p on ppt.produit_id=p.id
  inner join prd_caract pc on p.type_id=pc.produit_type_id and pc.code='POIDS'
  inner join prd_caract_jour pcj on ppt.produit_id=pcj.produit_id and pcj.prd_caract_id=pc.id and t.date_distrib=pcj.date_distrib
  left outer join emp_pop_depot epd on pt.employe_id=epd.id and pt.date_distrib between epd.date_debut and epd.date_fin
  left outer join prd_caract_groupe pcg on ppt.produit_id=pcg.produit_id and pcg.prd_caract_id=pc.id and t.groupe_id=pcg.groupe_id and t.date_distrib=pcg.date_distrib
  where t.date_extrait is null and ppt.date_extrait is null
	AND (t.depot_id=_depot_id OR _depot_id IS NULL)
	AND (t.flux_id=_flux_id OR _flux_id IS NULL)
	AND (t.date_distrib=_date_distrib OR _date_distrib IS NULL)
	AND (t.id=_tournee_id OR _tournee_id IS NULL)
  AND (ppt.id=_produit_id OR _produit_id IS NULL)
  and (epd.typetournee_id in (1,4) and p.type_id in (2,3) or epd.typetournee_id=2)
  and coalesce(pcg.valeur_int,pcj.valeur_int) is not null
  and not exists(select null from pai_ref_poids prpp where t.date_distrib between prpp.date_debut and prpp.date_fin 
                                    -- Si tournée pepp, on utilise la codification proximy
                                    and if(epd.typetournee_id=4,1,epd.typetournee_id)=prpp.typetournee_id 
                                    and p.id=prpp.produit_id
                                    and coalesce(pcg.valeur_int,pcj.valeur_int) between prpp.borne_inf and prpp.borne_sup
                                    )
  and not exists(select null from pai_ref_poids prpt where t.date_distrib between prpt.date_debut and prpt.date_fin 
                                    -- Si tournée pepp, on utilise la codification proximy
                                    and if(epd.typetournee_id=4,1,epd.typetournee_id)=prpt.typetournee_id 
                                    and p.type_id=prpt.produit_type_id
                                    and coalesce(pcg.valeur_int,pcj.valeur_int) between prpt.borne_inf and prpt.borne_sup
                                    )
  ;
  call pai_valide_logger('pai_valide_produit', 'Remuneration non declaree');
  */
end;
