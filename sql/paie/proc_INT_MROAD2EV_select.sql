 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- TOURNEE, PRODUIT, ACTIVITE, RECLAMATION
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_select;
CREATE PROCEDURE int_mroad2ev_select(
    IN 		_idtrt		    INT,
    IN 		_idtrt_org	  INT,
    IN 		_is1M        	BOOLEAN
) BEGIN
DECLARE _date_extrait datetime;
  SELECT date_debut INTO _date_extrait FROM pai_int_traitement WHERE id=_idtrt_org;
  
  CALL int_logger(_idtrt,'int_mroad2ev_select','Vide les tables de travail');
  truncate table pai_ev;
  truncate table pai_ev_reclamation;
  truncate table pai_ev_produit;
  delete from pai_ev_tournee;
  truncate table pai_ev_activite;

  call int_mroad2ev_insert_tournee(_idtrt, _date_extrait, _is1M);
  call int_mroad2ev_insert_produit(_idtrt, _date_extrait);
  call int_mroad2ev_insert_activite(_idtrt, _date_extrait, _is1M);
  call int_mroad2ev_insert_reclamation(_idtrt, _date_extrait, _is1M);
  call int_mroad2ev_maj(_idtrt, _is1M);
  call int_mroad2ev_maj_1M(_idtrt, _is1M);
  -- call int_mroad2ev_rustine(_idtrt);
  -- A FAIRE
  -- loguer les activites et les tournes non valides
  -- ne pas prendre en compte les encadrants
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_insert_tournee;
CREATE PROCEDURE int_mroad2ev_insert_tournee(
    IN 		_idtrt		    INT,
    IN 		_date_extrait datetime,
    IN 		_is1M        	BOOLEAN
) BEGIN
-- ATTENTION, logguer les tournées non valides non prises en compte
-- Supprimer les activites AT et TH liées à ces tournées
  INSERT INTO pai_ev_tournee(id,date_distrib,employe_id,depot_id,flux_id,jour_id,typejour_id,valrem,valrem_corrigee,code,nbkm_paye,transport_id,heure_debut,duree,duree_nuit,duree_tournee,duree_reperage,duree_supplement,nbcli,nbrep,majoration,majoration_nuit,duree_nuit_modele)  
  SELECT t.id,t.date_distrib,t.employe_id,t.depot_id,t.flux_id,t.jour_id,t.typejour_id,t.valrem_paie,t.valrem_majoree,t.code,t.nbkm_paye,t.transport_id,t.heure_debut_calculee,t.duree,t.duree_nuit,t.duree_tournee,t.duree_reperage,t.duree_supplement,t.nbcli,t.nbrep,t.majoration,coalesce(pm.majoration_nuit,0),coalesce(duree_nuit_modele,0)
  FROM pai_tournee t
  INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=t.employe_id and t.date_distrib between e.d and e.f
  LEFT OUTER JOIN pai_majoration pm ON t.employe_id=pm.employe_id and t.date_distrib=pm.date_distrib
--  left outer join  pai_journal pj on t.id=pj.tournee_id
--  left outer join pai_ref_erreur pe on pj.erreur_id=pe.id
  WHERE (t.date_extrait=_date_extrait or _date_extrait is null and t.date_extrait is null)
  AND e.typetournee_id in (1,2) -- on exclu les encadrants
  AND (not _is1M and  t.date_distrib NOT LIKE '%-05-01'or _is1M and t.date_distrib LIKE '%-05-01')
  AND (t.tournee_org_id is null or split_id is not null)
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id AND NOT pe.valide WHERE t.id=pj.tournee_id)
--  and (pe.valide is null or pe.valide)
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_insert_tournee','Tournées valides')
  ;
  INSERT INTO pai_ev_activite(id,date_distrib,employe_id,depot_id,flux_id,jour_id,typejour_id,activite_id,nbkm_paye,transport_id,heure_debut,duree,duree_nuit) 
  SELECT a.id,a.date_distrib,a.employe_id,a.depot_id,a.flux_id,a.jour_id,a.typejour_id,a.activite_id,a.nbkm_paye,a.transport_id,a.heure_debut_calculee,a.duree,a.duree_nuit 
  FROM pai_ev_tournee t -- on ne prend que les activités des tournées valides
  INNER JOIN pai_activite a ON a.tournee_id=t.id
  WHERE NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE a.id=pj.activite_id AND NOT pe.valide)
  ;
  -- ATTENTION, ne faut-il pas ignorer les retard !!! ==> poste de paie non affecté
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_insert_tournee','Activitées liées à une tournée')
  ;
  INSERT INTO pai_int_log(idtrt,date_log,module,msg)
  SELECT _idtrt,now(),'Erreur Tournée',CONCAT('Tournée non valide ( ',pt.code,', ',pt.date_distrib,', ',e.matricule,') : ',pe.msg)
  FROM pai_journal pj
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
  INNER JOIN pai_tournee pt ON pj.tournee_id=pt.id
  INNER JOIN pai_ev_emp_pop_depot epd ON epd.employe_id=pt.employe_id and pt.date_distrib between epd.d and epd.f
  INNER JOIN employe e on pt.employe_id=e.id
  WHERE (pt.date_extrait=_date_extrait or _date_extrait is null and pt.date_extrait is null)
  AND (not _is1M and  pt.date_distrib NOT LIKE '%-05-01'or _is1M and pt.date_distrib LIKE '%-05-01')
  AND (pt.tournee_org_id is null or split_id is not null)
  AND NOT pe.valide
  ;
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_insert_produit;
CREATE PROCEDURE int_mroad2ev_insert_produit(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
  INSERT INTO pai_ev_produit(id,tournee_id,produit_id,natureclient_id,typeproduit_id,nbcli,nbrep,qte,duree_supplement,duree_reperage,pai_qte,pai_taux,pai_val)  
  SELECT ppt.id,ppt.tournee_id,ppt.produit_id,ppt.natureclient_id,p.type_id,ppt.nbcli,ppt.nbrep,ppt.qte,ppt.duree_supplement,ppt.duree_reperage,ppt.pai_qte,ppt.pai_taux,ppt.pai_mnt
  FROM pai_ev_tournee t -- on ne prend que les produits des tournées valides
  INNER JOIN pai_prd_tournee ppt ON ppt.tournee_id=t.id
  INNER JOIN produit p on ppt.produit_id=p.id
  WHERE (ppt.date_extrait=_date_extrait or _date_extrait is null and ppt.date_extrait is null)
--  WHERE NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE ppt.id=pj.produit_id AND NOT pe.valide)
-- les produits invalides sont supprimés en dessous, par soucis de rapidité ?
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_insert_produit','Produits')
  ;
  DELETE ppt
  FROM pai_ev_produit ppt
  INNER JOIN pai_journal pj on ppt.id=pj.produit_id
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id 
  WHERE not pe.valide
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_insert_produit','Produits invalides')
  ;
  UPDATE pai_ev_produit p
  INNER JOIN pai_ev_tournee t on p.tournee_id=t.id
  INNER JOIN emp_pop_depot epd  ON epd.employe_id=t.employe_id AND t.date_distrib BETWEEN epd.date_debut AND epd.date_fin
  LEFT OUTER JOIN prd_caract c on p.typeproduit_id=c.produit_type_id and c.code='URSSAF'
  LEFT OUTER JOIN prd_caract_constante cc ON p.produit_id=cc.produit_id and cc.prd_caract_id=c.id
  SET p.typeurssaf_id=CASE
      WHEN epd.typeurssaf_id=2 THEN 2 -- population base réelle
      WHEN p.natureclient_id=0 and cc.valeur_string='F' THEN 1 -- abonné et produit base forfaitaire ==> BF
      WHEN p.natureclient_id<>0 OR coalesce(cc.valeur_string,'R')<>'F' THEN 2 -- abonné et produit base de droit commun ==> BDC
      ELSE null -- base réelle
      END
  ;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_insert_produit','Maj urssaf')
  ;
END; 
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_insert_activite;
CREATE PROCEDURE int_mroad2ev_insert_activite(
    IN 		_idtrt		    INT,
    IN 		_date_extrait datetime,
    IN 		_is1M        	BOOLEAN
) BEGIN
  INSERT INTO pai_ev_activite(id,date_distrib,employe_id,depot_id,flux_id,jour_id,typejour_id,activite_id,nbkm_paye,transport_id,heure_debut,duree,duree_nuit,duree_garantie,ouverture) 
  SELECT a.id,a.date_distrib,a.employe_id,a.depot_id,a.flux_id,a.jour_id,a.typejour_id,a.activite_id,a.nbkm_paye,a.transport_id,a.heure_debut_calculee,a.duree,a.duree_nuit,a.duree_garantie,a.ouverture
  FROM pai_activite a 
  INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=a.employe_id and a.date_distrib between e.d and e.f
--  left outer join  pai_journal pj on a.id=pj.activite_id
--  left outer join pai_ref_erreur pe on pj.erreur_id=pe.id
  WHERE (a.date_extrait=_date_extrait or _date_extrait is null and a.date_extrait is null)
  AND (e.typetournee_id in (1,2) or a.activite_id in (-10,-11)) -- on exclu les encadrants sauf pour les heures garanties
  AND (not _is1M and  a.date_distrib NOT LIKE '%-05-01'or _is1M and a.date_distrib LIKE '%-05-01')
  AND NOT exists(SELECT NULL FROM pai_journal pj INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id WHERE a.id=pj.activite_id AND NOT pe.valide)
  and a.tournee_id is null -- on ne prend pas les activités liées à une tournées car elles peuvent être invalide
--  and (pe.valide is null or pe.valide)

  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_insert_activite','Activités valides')
  ;
  INSERT INTO pai_int_log(idtrt,date_log,module,msg)
  SELECT _idtrt,now(),'Erreur Activité',CONCAT('Activité non valide ( ',ra.libelle,', ',pa.date_distrib,', ',e.matricule,') : ',pe.msg)
  FROM pai_journal pj
  INNER JOIN pai_ref_erreur pe on pj.erreur_id=pe.id
  INNER JOIN pai_activite pa ON pj.activite_id=pa.id
  INNER JOIN ref_activite ra on pa.activite_id=ra.id
  INNER JOIN pai_ev_emp_pop_depot epd ON epd.employe_id=pa.employe_id and pa.date_distrib between epd.d and epd.f
  INNER JOIN employe e on pa.employe_id=e.id
  WHERE (pa.date_extrait=_date_extrait or _date_extrait is null and pa.date_extrait is null)
  AND (not _is1M and  pa.date_distrib NOT LIKE '%-05-01'or _is1M and pa.date_distrib LIKE '%-05-01')
--  and a.tournee_id is null -- on ne prend pas les activités liées à une tournées car elles peuvent être invalide
  AND NOT pe.valide
  ;
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_insert_reclamation;
CREATE PROCEDURE int_mroad2ev_insert_reclamation(
    IN 		_idtrt		    INT,
    IN 		_date_extrait datetime,
    IN 		_is1M        	BOOLEAN
) BEGIN
    -- 12/02/2016 On double plus les réclamations Media
  IF (not _is1M) THEN
    INSERT INTO pai_ev_reclamation(id,tournee_id,nbrec_abonne,nbrec_diffuseur,nbrec_abonne_brut,nbrec_diffuseur_brut)    
    SELECT DISTINCT r.id,r.tournee_id,r.nbrec_abonne,r.nbrec_diffuseur,r.nbrec_abonne_brut,r.nbrec_diffuseur_brut
    FROM pai_reclamation r
    INNER JOIN pai_int_traitement pit on pit.id=_idtrt
    -- on utilise pai_tournee car on peut revenir en retro sur les réclamations (d'ou le dRC !!!)
    INNER JOIN pai_tournee pt ON pt.id=r.tournee_id
    INNER JOIN pai_ev_emp_pop_depot e  ON e.employe_id=pt.employe_id AND pt.date_distrib BETWEEN e.dRC AND e.f
    INNER JOIN emp_pop_depot epd  ON epd.employe_id=pt.employe_id AND pt.date_distrib BETWEEN epd.date_debut AND epd.date_fin
    WHERE  (r.date_extrait=_date_extrait or _date_extrait is null and r.date_extrait is null)
    -- 24/01/2016 On rajoute la sélection sur anneemois pour le blocage des réclamations
    and (pit.anneemois=r.anneemois
    -- 29/01/2016 On prend également tous les stc
    or exists (select null from pai_stc ps where epd.rcoid=ps.rcoid and (ps.date_extrait=_date_extrait or _date_extrait is null and ps.date_extrait is null))
    )
    -- 25/02/2016 On ne prend pas les réclamations sur les tournées mères splittées
    and (pt.tournee_org_id is null or pt.split_id is not null)
    ;
    call int_logrowcount_C(_idtrt,4,'ev_insert_reclamation','Réclamations');
  END IF;
END;


 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_rustine;
CREATE PROCEDURE int_mroad2ev_rustine(
    IN 		_idtrt		INT
) BEGIN
/*
  update pai_ev_tournee ph
  inner join emp_pop_depot epd on epd.employe_id=ph.employe_id and ph.date_distrib between epd.date_debut and epd.date_fin
  set ph.duree='00:00',ph.duree_nuit='00:00',ph.nbcli=0,majoration=0
  where (ph.employe_id in (6730,6695,6767,6478,6032,6731,6798,5998,5730,1747,1716,6502,6714,5789,5790,6604,5792,6500,6698) or epd.nbheures_garanties<>0)
  and ph.date_distrib between '2014-12-21' and '2014-12-31'
  and ph.flux_id=2;
  
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_insert_tournee','Tournées dispress maj')
  ;
  delete p
  from pai_ev_produit p
  INNER JOIN pai_ev_tournee ph on p.tournee_id=ph.id
  inner join emp_pop_depot epd on epd.employe_id=ph.employe_id and ph.date_distrib between epd.date_debut and epd.date_fin
  where (ph.employe_id in (6730,6695,6767,6478,6032,6731,6798,5998,5730,1747,1716,6502,6714,5789,5790,6604,5792,6500,6698) or epd.nbheures_garanties<>0)
  and ph.date_distrib between '2014-12-21' and '2014-12-31'
  and ph.flux_id=2;
  
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_insert_tournee','Produits dispress supprimés')
  ;
  update pai_ev_activite ph
  inner join emp_pop_depot epd on epd.employe_id=ph.employe_id and ph.date_distrib between epd.date_debut and epd.date_fin
  set ph.duree='00:00',ph.duree_nuit='00:00'
  where (ph.employe_id in (6730,6695,6767,6478,6032,6731,6798,5998,5730,1747,1716,6502,6714,5789,5790,6604,5792,6500,6698) or epd.nbheures_garanties<>0)
  and ph.date_distrib between '2014-12-21' and '2014-12-31'
  and ph.flux_id=2;
  
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_insert_tournee','Activités dispress maj')
  ;
  */
END;


