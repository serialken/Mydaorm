/**
    call recalcul_produit_date_distrib(null, null, null);
    call recalcul_tournee_date_distrib(null, null, null);
    call recalcul_tournee_date_distrib('2016-09-05', null, null);
    select * from depot
    delete from pai_int_log where idtrt=1;

    select * from pai_int_log where idtrt=1 order by id desc limit 1000;
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_tournee;
CREATE PROCEDURE recalcul_tournee(
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_id             INT,
    IN 		_groupe_id      INT
) BEGIN
  set @valrem = 1.0;

	INSERT INTO tmp_pai_recalcul_tournee2 SELECT * FROM tmp_pai_recalcul_tournee;
	
	UPDATE pai_tournee pt
	INNER JOIN tmp_pai_recalcul_tournee tprt on pt.id=tprt.tournee_id
	inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
	left outer join modele_remplacement_jour mrj on mrj.modele_tournee_id=mtj.tournee_id and mrj.jour_id=mtj.jour_id
	left outer join modele_remplacement mr on mrj.remplacement_id=mr.id and pt.date_distrib between mr.date_debut and mr.date_fin
	LEFT OUTER JOIN emp_pop_depot epd ON epd.employe_id=pt.employe_id AND pt.date_distrib BETWEEN epd.date_debut AND epd.date_fin
	LEFT OUTER JOIN ref_population rp ON epd.population_id=rp.id
	LEFT OUTER JOIN pai_ref_remuneration r ON coalesce(epd.societe_id,1)=r.societe_id AND coalesce(epd.population_id,1)=r.population_id AND pt.date_distrib BETWEEN r.date_debut AND r.date_fin
  left outer join ref_transport rt on rt.id=pt.transport_id
  INNER JOIN(SELECT 
        			pt.id as tournee_id
        		  , COALESCE(SUM(IF(p.type_id=1,ppt.nbcli,0)),0) as nbcli
        		  , COALESCE(SUM(IF(p.type_id=1,ppt.nbcli_unique,0)),0) as nbcli_unique
        		  , COALESCE(SUM(IF(p.type_id=1,ppt.nbadr,0)),0) as nbadr
        		  , COALESCE(SUM(IF(p.type_id=1,ppt.nbrep,0)),0) as nbrep
        		  , COALESCE(SUM(IF(p.type_id not in (1,2,3) and not t.hors_presse,ppt.nbrep,0)),0) as nbrep_FR
        		  , COALESCE(SUM(IF(p.type_id=1,ppt.qte,0)),0) as nbtitre
        		  , COALESCE(SUM(IF(p.type_id in (2,3),ppt.qte,0)),0) as nbspl
        		  , COALESCE(SUM(IF(p.type_id not in (1,2,3),ppt.qte,0)),0) as nbprod
        		  , COALESCE(SUM(ppt.poids),0) as poids
        		  , IF(pt.tournee_org_id is null or pt.split_id is not null,COALESCE(sec_to_time(sum(time_to_sec(ppt.duree_supplement))),'00:00:00'),'00:00:00') as duree_supplement
        		  , IF(pt.tournee_org_id is null or pt.split_id is not null,COALESCE(sec_to_time(sum(time_to_sec(ppt.duree_reperage))),'00:00:00'),'00:00:00') as duree_reperage
        	  FROM pai_tournee pt
        	  INNER JOIN tmp_pai_recalcul_tournee2 tprt on pt.id=tprt.tournee_id
        	  LEFT OUTER JOIN pai_prd_tournee ppt on ppt.tournee_id=pt.id
        	  LEFT OUTER JOIN produit p ON ppt.produit_id=p.id
            LEFT OUTER JOIN produit_type t ON p.type_id=t.id
        	  group by pt.id) as ppt on ppt.tournee_id=pt.id
    -- Produits
  SET pt.nbcli	    =ppt.nbcli
  , pt.nbcli_unique =ppt.nbcli_unique
  , pt.nbadr	      =ppt.nbadr
  , pt.nbrep	      =ppt.nbrep+nbrep_FR
  , pt.nbtitre	    =ppt.nbtitre
  , pt.nbspl	      =ppt.nbspl
  , pt.nbprod	      =ppt.nbprod
  , pt.poids	      =ppt.poids
	-- Valeur de rémunération
  , pt.valrem_org		      =pai_valrem_org(pt.flux_id,pt.employe_id,mr.actif,mr.employe_id,mrj.valrem_moyen,mrj.valrem,mtj.employe_id,mtj.remplacant_id,mtj.valrem_moyen,mtj.valrem)
  , pt.valrem_paie	      =(@valrem:=pai_valrem(pt.flux_id,pt.employe_id,mr.actif,mr.employe_id,mrj.valrem_moyen,mrj.valrem,mtj.employe_id,mtj.remplacant_id,mtj.valrem_moyen,mtj.valrem))
  , pt.valrem				      =@valrem
  , pt.valrem_logistique	=mtj.valrem
	-- Pour le calcul des durées, on prend la valeur de rémunération d'origine et non la valeur de rémunération majorée
  , pt.duree_tournee		=pai_duree_tournee(pt.tournee_org_id,pt.split_id,epd.typetournee_id,@valrem,rp.majoration,r.valeur,ppt.nbcli)
  , pt.duree_reperage		=addtime(pai_duree_reperage(pt.tournee_org_id,pt.split_id,epd.typetournee_id,@valrem,rp.majoration,r.valeur,ppt.nbrep),ppt.duree_reperage)
  , pt.duree_supplement	=ppt.duree_supplement
	-- Transport
  , pt.nbkm_paye	=case when coalesce(epd.km_paye,1) and coalesce(rt.km_paye,1) then pt.nbkm_paye else 0 end
  ;

  UPDATE pai_tournee pt
	INNER JOIN tmp_pai_recalcul_tournee tprt on pt.id=tprt.tournee_id
  SET pt.duree = addtime(pt.duree_tournee,addtime(pt.duree_reperage,pt.duree_supplement))
    ;
    /*
    UPDATE pai_tournee pt
    SET pt.nbcli	    =COALESCE((SELECT SUM(ppt.nbcli) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id=1),0)
    , pt.nbcli_unique =COALESCE((SELECT SUM(ppt.nbcli_unique) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id=1),0)
    , pt.nbadr	      =COALESCE((SELECT SUM(ppt.nbadr) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id=1),0)
    , pt.nbrep	      =COALESCE((SELECT SUM(ppt.nbrep) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id=1),0)
    , pt.nbtitre	    =COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id=1),0)
    , pt.nbspl	      =COALESCE((SELECT SUM(ppt.qte) FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id IN (2,3)),0)
    , pt.nbprod	      =COALESCE((SELECT SUM(ppt.qte)    FROM pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id AND p.type_id NOT IN (1,2,3)),0)
    , pt.poids	      =COALESCE((SELECT SUM(ppt.poids) FROM pai_prd_tournee ppt WHERE ppt.tournee_id=pt.id),0)
    , pt.duree_supplement=IF(pt.tournee_org_id is null or pt.split_id is not null,
        COALESCE((select sec_to_time(sum(time_to_sec(ppt.duree_supplement))) from pai_prd_tournee ppt INNER JOIN produit p ON ppt.produit_id=p.id WHERE ppt.tournee_id=pt.id),'00:00:00'),
        '00:00:00')
    WHERE pt.date_extrait is null
    and (pt.date_distrib=_date_distrib or _date_distrib is null)
    and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null)
    and (pt.id=_id or _id is null)
    and (pt.groupe_id=_groupe_id or _groupe_id is null)
    ;
*/    
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_tournee_date_distrib;
CREATE PROCEDURE recalcul_tournee_date_distrib(
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT
) BEGIN
declare _validation_id  INT;
  call recalcul_logger('recalcul_tournee_date_distrib', concat_ws('*',_date_distrib,_depot_id,_flux_id));
  call recalcul_tournee_create_tmp;
  insert into tmp_pai_recalcul_tournee select pt.id from pai_tournee pt where pt.date_extrait is null and (pt.date_distrib=_date_distrib or _date_distrib is null) and (pt.depot_id=_depot_id or _depot_id is null) and (pt.flux_id=_flux_id or _flux_id is null);
  call recalcul_tournee(_date_distrib,_depot_id,_flux_id,null,null);
  call pai_valide_tournee(_validation_id, _depot_id, _flux_id, _date_distrib, null); -- ???
  call recalcul_horaire(_validation_id, _depot_id, _flux_id, _date_distrib, null);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_tournee_id;
CREATE PROCEDURE recalcul_tournee_id(
    IN 		_id             INT
) BEGIN
declare _validation_id  INT;
  call recalcul_logger('recalcul_tournee_id', concat_ws('*',_id));
  call recalcul_tournee_create_tmp;
  insert into tmp_pai_recalcul_tournee select pt.id from pai_tournee pt where pt.date_extrait is null and (pt.id=_id or _id is null);
  call recalcul_tournee(null,null,null,_id,null);
  call pai_valide_tournee(_validation_id, null, null, null, _id); -- permet de supprimer les tournées incomplète lorsque la qte était égale à 0
  call recalcul_horaire_tournee(_validation_id, _id);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_tournee_poids_groupe;
CREATE PROCEDURE recalcul_tournee_poids_groupe(
    IN 		_date_distrib   DATE,
    IN 		_groupe_id      INT
) BEGIN
declare _validation_id  INT;
  call recalcul_logger('recalcul_tournee_poids_groupe', concat_ws('*',_date_distrib,_groupe_id));
  call recalcul_tournee_create_tmp;
  insert into tmp_pai_recalcul_tournee select pt.id from pai_tournee pt where pt.date_extrait is null and (pt.date_distrib=_date_distrib or _date_distrib is null) and (pt.groupe_id=_groupe_id or _groupe_id is null);
  call recalcul_tournee(_date_distrib,null,null,null,_groupe_id);
    -- appelé lorsque l'on recalcul le poids, on ne recalcul pas les horaire ????
  call recalcul_horaire(_validation_id, null, null, _date_distrib, null);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_tournee_poids_PCO;
CREATE PROCEDURE recalcul_tournee_poids_PCO(
    IN 		_date_distrib   DATE
) BEGIN
declare _validation_id  INT;
  call recalcul_logger('recalcul_tournee_poids_PCO', concat_ws('*',_date_distrib));
  call recalcul_tournee_create_tmp;
  insert into tmp_pai_recalcul_tournee select pt.id from pai_tournee pt where pt.date_extrait is null and (pt.date_distrib=_date_distrib or _date_distrib is null);
  call recalcul_tournee(_date_distrib,null,null,null,null);
    -- appelé lorsque l'on recalcul le poids, on ne recalcul pas les horaire ????
  call recalcul_horaire(_validation_id, null, null, _date_distrib, null);
END;

-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_tournee_modele;
CREATE PROCEDURE recalcul_tournee_modele(
	INOUT 	_validation_id INT, 
    IN 		_date_debut 				DATE,
	IN 		_modele_tournee_jour_id     INT
) BEGIN
  call recalcul_logger('recalcul_tournee_modele', concat_ws('*',_date_debut,_modele_tournee_jour_id));
  call recalcul_tournee_create_tmp;
  insert into tmp_pai_recalcul_tournee select pt.id from pai_tournee pt inner join modele_tournee_jour mtj on mtj.id=pt.modele_tournee_jour_id where pt.date_extrait is null and pt.modele_tournee_jour_id = _modele_tournee_jour_id AND (pt.date_distrib>=_date_debut or _date_debut is null);
  call recalcul_tournee(null,null,null,null,null);
  call recalcul_horaire_modele(_validation_id, _date_debut, _modele_tournee_jour_id);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_tournee_etalon;
CREATE PROCEDURE `recalcul_tournee_etalon`(
	INOUT 	_validation_id INT, 
	IN _etalon_id INT)
BEGIN
  call recalcul_logger('recalcul_tournee_etalon', concat_ws('*',_etalon_id));
  call recalcul_tournee_create_tmp;
  insert into tmp_pai_recalcul_tournee select pt.id from pai_tournee pt inner join etalon_transferer ett on ett.new_modele_tournee_jour_id=pt.modele_tournee_jour_id and pt.date_distrib>=ett.date_application where pt.date_extrait is null and ett.etalon_id=_etalon_id;
  call recalcul_tournee(null,null,null,null,null);
  call recalcul_horaire_etalon(_validation_id, _etalon_id);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_tournee_remplacement;
CREATE PROCEDURE `recalcul_tournee_remplacement`(IN _remplacement_id INT)
BEGIN
declare _validation_id  INT;
  call recalcul_logger('recalcul_tournee_remplacement', concat_ws('*',_remplacement_id));
  call recalcul_tournee_create_tmp;
  insert into tmp_pai_recalcul_tournee select pt.id 
  FROM pai_tournee pt
  inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
  left outer join modele_remplacement_jour mrj on mrj.modele_tournee_id=mtj.tournee_id and mrj.jour_id=mtj.jour_id
  left outer join modele_remplacement mr on mrj.remplacement_id=mr.id and pt.date_distrib between mr.date_debut and mr.date_fin
  where pt.date_extrait is null
  AND mr.id=_remplacement_id;
  call recalcul_tournee(null,null,null,null,null);
  call recalcul_horaire_remplacement(_validation_id, _remplacement_id);
END;
-- -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_tournee_create_tmp;
CREATE PROCEDURE recalcul_tournee_create_tmp()
begin
  DROP TEMPORARY TABLE IF EXISTS tmp_pai_recalcul_tournee;
  CREATE TEMPORARY TABLE tmp_pai_recalcul_tournee(
  tournee_id  int
  ) engine=memory
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
  CREATE INDEX tmp_pai_recalcul_tournee_idx1 ON tmp_pai_recalcul_tournee(tournee_id);
  
  DROP TEMPORARY TABLE IF EXISTS tmp_pai_recalcul_tournee2;
  CREATE TEMPORARY TABLE tmp_pai_recalcul_tournee2(
  tournee_id  int
  ) engine=memory
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
  CREATE INDEX tmp_pai_recalcul_tournee2_idx1 ON tmp_pai_recalcul_tournee2(tournee_id);
END;