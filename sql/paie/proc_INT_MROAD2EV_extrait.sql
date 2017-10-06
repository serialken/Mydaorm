-- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_extrait;
CREATE PROCEDURE int_mroad2ev_extrait(
    IN 		_idtrt		    INT,
    IN 		_date_debut DATE,
    IN 		_date_fin 	DATE,
    IN 		_flux_id		  INT
) BEGIN
DECLARE _date_extrait datetime;
  SELECT date_debut INTO _date_extrait FROM pai_int_traitement WHERE id=_idtrt;
  
  call int_mroad2ev_extrait_tournee(_idtrt, _date_extrait);
  call int_mroad2ev_extrait_produit(_idtrt, _date_extrait);
  call int_mroad2ev_extrait_activite(_idtrt, _date_extrait);
  call int_mroad2ev_extrait_reclamation(_idtrt, _date_extrait);
  call int_mroad2ev_extrait_incident(_idtrt, _date_extrait);
  call int_mroad2ev_extrait_majoration(_idtrt, _date_extrait);
  call int_mroad2ev_extrait_hg(_idtrt, _date_extrait);
  call int_mroad2ev_extrait_hchs(_idtrt, _date_extrait);
  call int_mroad2ev_extrait_stc(_idtrt, _date_extrait, _flux_id);
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_extrait_tournee;
CREATE PROCEDURE int_mroad2ev_extrait_tournee(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
  UPDATE pai_tournee pt
  INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=pt.employe_id and pt.date_distrib between e.d and e.f
  SET pt.date_extrait=_date_extrait
  WHERE pt.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_tournee','Tournées extraites')
  ;
  UPDATE pai_journal pj
  INNER JOIN pai_tournee pt ON pj.tournee_id=pt.id
  SET  pj.date_extrait=_date_extrait
  WHERE pt.date_extrait=_date_extrait
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_tournee','Tournées invalides')
  ;
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_extrait_produit;
CREATE PROCEDURE int_mroad2ev_extrait_produit(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
  UPDATE pai_prd_tournee ppt 
  INNER JOIN pai_tournee pt ON ppt.tournee_id=pt.id
  SET ppt.date_extrait=_date_extrait 
  WHERE pt.date_extrait=_date_extrait
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_produit','Produits extraits')
  ;
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_extrait_activite;
CREATE PROCEDURE int_mroad2ev_extrait_activite(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
  UPDATE pai_activite pa 
  INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=pa.employe_id and pa.date_distrib between e.d and e.f
  SET pa.date_extrait=_date_extrait 
  WHERE pa.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_activite','Activités extraites')
  ;
  UPDATE pai_journal pj
  INNER JOIN pai_activite pa ON pj.activite_id=pa.id
  SET  pj.date_extrait=_date_extrait
  where pa.date_extrait=_date_extrait
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_activite','Activités invalides')
  ;
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_extrait_reclamation;
CREATE PROCEDURE int_mroad2ev_extrait_reclamation(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
-- 29/11/2016 On extrait toutes les réclamations qui ont été sélectionnées dans int_mroad2ev_insert_reclamation
  UPDATE pai_reclamation pr
  INNER JOIN pai_ev_reclamation per ON pr.id=per.id
  SET pr.date_extrait=_date_extrait 
  WHERE pr.date_extrait is null
  ;
/*
  UPDATE pai_reclamation pr
  INNER JOIN pai_tournee pt ON pr.tournee_id=pt.id
  INNER JOIN pai_ev_emp_pop_depot e  ON e.employe_id=pt.employe_id AND pt.date_distrib BETWEEN e.dRC AND e.f
  SET pr.date_extrait=_date_extrait 
  WHERE pr.date_extrait is null
  ;
*/  
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_reclamation','Réclamations extraites');
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_extrait_incident;
CREATE PROCEDURE int_mroad2ev_extrait_incident(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
  UPDATE pai_incident pi
  INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=pi.employe_id and pi.date_distrib between e.d and e.f
  SET pi.date_extrait=_date_extrait 
  WHERE pi.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_incident','Incidents extraits');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_extrait_majoration;
CREATE PROCEDURE int_mroad2ev_extrait_majoration(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
  UPDATE pai_majoration pm
  INNER JOIN pai_tournee pt ON pm.employe_id=pt.employe_id and pm.date_distrib=pt.date_distrib
  SET pm.date_extrait=_date_extrait 
  WHERE pt.date_extrait=_date_extrait
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_majoration','Majorations extraites');
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_extrait_hg;
CREATE PROCEDURE int_mroad2ev_extrait_hg(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
  UPDATE pai_hg phg
--  INNER JOIN emp_contrat_hp ech ON phg.xaoid=ech.xaoid
  INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=phg.employe_id and phg.date_fin between e.d and e.f
  SET phg.date_extrait=_date_extrait 
  WHERE phg.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_hg','HG extraits');
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_extrait_hchs;
CREATE PROCEDURE int_mroad2ev_extrait_hchs(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime
) BEGIN
  UPDATE pai_hchs ph
  INNER JOIN emp_contrat_hp ech ON ph.xaoid=ech.xaoid
  INNER JOIN pai_ev_emp_pop_depot e ON e.employe_id=ph.employe_id and least(ech.date_fin,ph.date_fin) between e.d and e.f
  SET ph.date_extrait=_date_extrait 
  WHERE ph.date_extrait is null
  ;
  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_hchs','HCHS extraits');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_extrait_stc;
CREATE PROCEDURE int_mroad2ev_extrait_stc(
    IN 		_idtrt		    INT,
    IN 		_date_extrait	datetime,
    IN 		_flux_id		  INT
) BEGIN
  UPDATE pai_stc ps
  INNER JOIN emp_pop_depot epd on ps.employe_id=epd.employe_id and ps.date_stc=epd.fRC and epd.flux_id=_flux_id
-- /27/11/2015 On extrait tous les STC  
--  INNER JOIN pai_mois pm on pm.anneemois=ps.anneemois and pm.flux_id=_flux_id
  SET ps.date_extrait=_date_extrait 
  WHERE epd.flux_id=_flux_id
  AND ps.date_extrait is null;

  call int_logrowcount_C(_idtrt,4,'int_mroad2ev_extrait_stc','STC extraits');
END;
