-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_verif_stc;
CREATE PROCEDURE int_mroad2ev_verif_stc(
    IN 		_idtrt		  INT,
    IN 		_isStc      BOOLEAN,
    IN 		_date_debut DATE,
    IN 		_date_fin 	DATE,
    IN 		_depot_id		  INT,
    IN 		_flux_id		  INT
) BEGIN
	  -- On supprimer les STC qui ne sont plus en phase avec Pleiades NG
	  INSERT INTO pai_int_log(idtrt,module,msg)
	  SELECT _idtrt,'int_mroad2ev_select_contrat',CONCAT('STC non valide pour le matricule ',e.matricule,' le ',s.date_stc)
	  FROM pai_stc s
	  INNER JOIN employe e ON s.employe_id=e.id
	  WHERE _isStc and s.date_extrait is null
	  AND NOT EXISTS(SELECT NULL
			FROM emp_pop_depot e
      INNER JOIN ref_emploi re ON e.emploi_id=re.id
			WHERE s.employe_id=e.employe_id
			AND s.date_stc=e.date_fin
			AND s.date_stc>=_date_debut
			AND re.paie);

	  DELETE s FROM pai_stc s
	  WHERE _isStc and s.date_extrait is null
	  AND NOT EXISTS(SELECT NULL
			FROM emp_pop_depot e
      INNER JOIN ref_emploi re ON e.emploi_id=re.id
			WHERE s.employe_id=e.employe_id
			AND s.date_stc=e.date_fin
			AND s.date_stc>=_date_debut
			AND re.paie);
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_log_warning;
CREATE PROCEDURE int_mroad2ev_log_warning(
    IN 		_idtrt		INT,
    IN 		_date_debut 	DATE,
    IN 		_date_fin 	DATE
) BEGIN
  INSERT INTO pai_int_log(idtrt,date_log,module,level,msg)
  SELECT _idtrt,now(),'Mutation Etablissement',1,CONCAT('Mutation du matricule ',e.matricule,' dans l''établissement ',d.code,', le ',date_format(e.dCtr,'%d/%m/%Y'))  
  FROM pai_ev_emp_depot e
  LEFT OUTER JOIN depot d ON e.depot_id=d.id
  WHERE e.dCtr<>e.dRC AND e.dCtr>_date_debut
  -- Permet de ne pas prendre en compte les retour de suspension
  and exists(select null from pai_ev_emp_depot e3 where e3.matricule=e.matricule and e3.rc=e.rc and e3.f+INTERVAL 1 DAY=e.d and e3.depot_id<>e.depot_id)
  ;

  INSERT INTO pai_int_log(idtrt,date_log,module,level,msg)
  SELECT _idtrt,now(),'Changement Population',1,CONCAT('Passage à la population ',e.emploi_code,' du matricule ',e.matricule,', le ',date_format(e.dCtr,'%d/%m/%Y'))  
  FROM pai_ev_emp_pop e
--  INNER JOIN ref_population rp ON rp.id=e.population_id
  WHERE e.dCtr<>e.dRC AND e.dCtr>_date_debut;

  INSERT INTO pai_int_log(idtrt,date_log,module,level,msg)
  SELECT DISTINCT _idtrt,now(),'Rémunération Produit',3,concat(rt.libelle,' ',p.libelle,' ',if(prpp.produit_id is not null,'Produit','Type'),'=',coalesce(prpp.valeur,prpt.valeur),'E')
  FROM pai_ev_produit ppt
  inner join pai_tournee pt on ppt.tournee_id=pt.id
  inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
  inner join ref_typetournee rt on epd.typetournee_id=rt.id
  inner join produit p on ppt.produit_id=p.id
  -- rémunération au niveau du produit
  left outer join pai_ref_poids prpp on pt.date_distrib between prpp.date_debut and prpp.date_fin 
                                    and epd.typetournee_id=prpp.typetournee_id 
                                    and p.id=prpp.produit_id
                                    -- pour SDVP on ne tient compte du poids
                                    -- and ((epd.typetournee_id=1 and 0=prpp.borne_inf)
                                    -- pour Néo/Média on ne tient pas compte du poids
                                    -- or   (epd.typetournee_id=2 and coalesce(pcg.valeur_int,pcj.valeur_int) between prpp.borne_inf and prpp.borne_sup))
  -- rémunération au niveau du type
  left outer join pai_ref_poids prpt on pt.date_distrib between prpt.date_debut and prpt.date_fin
                                    and epd.typetournee_id=prpt.typetournee_id
                                    and p.type_id=prpt.produit_type_id and prpt.produit_id is null
                                    -- pour SDVP on ne tient compte du poids
                                    -- and ((epd.typetournee_id=1 and 0=prpt.borne_inf)
                                    -- pour Néo/Média on ne tient pas compte du poids
                                    -- or   (epd.typetournee_id=2 and coalesce(pcg.valeur_int,pcj.valeur_int) between prpt.borne_inf and prpt.borne_sup))
  where coalesce(prpp.valeur,prpt.valeur)<>0
  order by rt.libelle,p.libelle;
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_log_erreur;
CREATE PROCEDURE int_mroad2ev_log_erreur(
    IN 		_idtrt		    INT,
    IN 		_date_debut 	DATE,
    IN 		_date_fin 	  DATE,
    IN    _bloquant     BOOLEAN
) BEGIN
DECLARE nb_erreur INT;
  set nb_erreur=0;

  INSERT INTO pai_int_log(idtrt,date_log,module,msg)
  SELECT DISTINCT _idtrt,now(),'ev_erreur',CONCAT('Type urssaf non renseigné pour le produit ',p.libelle)
  FROM pai_ev_produit pep
  INNER JOIN produit p on pep.produit_id=p.id
  INNER JOIN produit_type pt ON pep.typeproduit_id=pt.id
  LEFT OUTER JOIN prd_caract c on pep.typeproduit_id=c.produit_type_id and c.code='URSSAF'
  LEFT OUTER JOIN prd_caract_constante cc ON cc.prd_caract_id=c.id and pep.produit_id=cc.produit_id  AND cc.valeur_string in ('F','R')
  WHERE not pt.hors_presse
  and cc.valeur_string is null
  ;  
  set nb_erreur=nb_erreur+row_count();
  CALL int_logger(_idtrt,'int_mroad2ev_log_erreur','Type urssaf non renseigné pour le produit');
  
  INSERT INTO pai_int_log(idtrt,date_log,module,msg)
  SELECT DISTINCT _idtrt,now(),'ev_erreur',CONCAT('Rémunération supplément inconnu pour le produit ',p.libelle)
  FROM pai_ev_produit ppt
  inner join pai_ev_tournee pt on ppt.tournee_id=pt.id
  inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
  inner join produit p on ppt.produit_id=p.id 
  where p.type_id in (2,3)
  and ppt.qte>0 and (ppt.pai_qte is null or ppt.pai_taux is null or ppt.pai_val is null)
  and epd.typetournee_id in (1,2) -- pas pour les encadrants
  ;
  set nb_erreur=nb_erreur+row_count();
  CALL int_logger(_idtrt,'int_mroad2ev_log_erreur','Rémunération supplément inconnu pour le produit');

  INSERT INTO pai_int_log(idtrt,date_log,module,msg)
  SELECT DISTINCT _idtrt,now(),'ev_erreur',CONCAT('Rémunération inconnue pour le produit ',p.libelle)
  FROM pai_ev_produit ppt
  inner join pai_ev_tournee pt on ppt.tournee_id=pt.id
  inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
  inner join produit p on ppt.produit_id=p.id 
  where p.type_id>3
  and ppt.qte>0 and (ppt.pai_qte is null or ppt.pai_taux is null or ppt.pai_val is null)
  and epd.typetournee_id in (1,2) -- pas pour les encadrants
  ;
  set nb_erreur=nb_erreur+row_count();
  CALL int_logger(_idtrt,'int_mroad2ev_log_erreur','Rémunération inconnue pour le produit');

  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'ev_erreur',CONCAT('Poste de paie durée totale activité introuvable pour ',a.libelle,'(',a.id,') et ',j.libelle,'(',j.id,')')
/*  FROM pai_ev_emp_depot e
  INNER JOIN pai_ev_activite h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.dCtr AND e.f*/
  FROM pai_ev_activite h 
  INNER JOIN ref_activite a ON h.activite_id=a.id
  INNER JOIN ref_typejour j ON h.typejour_id=j.id
  LEFT OUTER JOIN pai_ref_postepaie_activite p on h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id 
  WHERE TIME_TO_SEC(h.duree)<>0
  AND p.poste_hj IS NULL
  ;
  set nb_erreur=nb_erreur+row_count();
  CALL int_logger(_idtrt,'int_mroad2ev_log_erreur','Poste de paie durée totale activité introuvable');

  INSERT INTO pai_int_log(idtrt,date_log,module,msg) 
  SELECT distinct _idtrt,now(),'ev_erreur',CONCAT('Poste de paie durée nuit activité introuvable pour ',a.libelle,'(',a.id,') et ',j.libelle,'(',j.id,')')
/*  FROM pai_ev_emp_depot e
  INNER JOIN pai_ev_activite h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.dCtr AND e.f*/
  FROM pai_ev_activite h 
  INNER JOIN ref_activite a ON h.activite_id=a.id
  INNER JOIN ref_typejour j ON h.typejour_id=j.id
  LEFT OUTER JOIN pai_ref_postepaie_activite p on h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id 
  WHERE TIME_TO_SEC(h.duree_nuit)<>0
  AND p.poste_hn IS NULL
  ;
  set nb_erreur=nb_erreur+row_count();
  CALL int_logger(_idtrt,'int_mroad2ev_log_erreur','Poste de paie durée nuit activité introuvable');

  IF (nb_erreur>0 AND _bloquant) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur bloquante';
  END IF;
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_controle;
CREATE PROCEDURE int_mroad2ev_controle(
    IN 		_idtrt		INT,
    IN 		_date_debut 	DATE,
    IN 		_date_fin 	DATE
) BEGIN
DECLARE _idtrt INT;
  set _idtrt=0;

  SELECT DISTINCT concat(rt.libelle,' ',p.libelle,' '/*,p.type_id,' '*/,coalesce(pcg.valeur_int,pcj.valeur_int),'g ',if(prpp.produit_id is not null,'Produit','Type'),'=',coalesce(prpp.valeur,prpt.valeur),'E')
  FROM pai_prd_tournee ppt
  inner join pai_tournee pt on ppt.tournee_id=pt.id
  inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
  inner join ref_typetournee rt on epd.typetournee_id=rt.id
  inner join produit p on ppt.produit_id=p.id
  inner join prd_caract pc on p.type_id=pc.produit_type_id and pc.code='POIDS'
  left outer join prd_caract_jour pcj on ppt.produit_id=pcj.produit_id and pcj.prd_caract_id=pc.id and pt.date_distrib = pcj.date_distrib 
  left outer join prd_caract_groupe pcg on ppt.produit_id=pcg.produit_id and pcg.prd_caract_id=pc.id and pt.groupe_id=pcg.groupe_id and pt.date_distrib=pcg.date_distrib
  -- rémunération au niveau du produit
  left outer join pai_ref_poids prpp on pt.date_distrib between prpp.date_debut and prpp.date_fin 
                                    and epd.typetournee_id=prpp.typetournee_id 
                                    and p.id=prpp.produit_id
                                    -- pour SDVP on ne tient compte du poids
                                    -- and ((epd.typetournee_id=1 and 0=prpp.borne_inf)
                                    -- pour Néo/Média on ne tient pas compte du poids
                                    -- or   (epd.typetournee_id=2 and coalesce(pcg.valeur_int,pcj.valeur_int) between prpp.borne_inf and prpp.borne_sup))
  -- rémunération au niveau du type
  left outer join pai_ref_poids prpt on pt.date_distrib between prpt.date_debut and prpt.date_fin
                                    and epd.typetournee_id=prpt.typetournee_id
                                    and p.type_id=prpt.produit_type_id and prpt.produit_id is null
                                    -- pour SDVP on ne tient compte du poids
                                    -- and ((epd.typetournee_id=1 and 0=prpt.borne_inf)
                                    -- pour Néo/Média on ne tient pas compte du poids
                                    -- or   (epd.typetournee_id=2 and coalesce(pcg.valeur_int,pcj.valeur_int) between prpt.borne_inf and prpt.borne_sup))
  where coalesce(prpp.valeur,prpt.valeur)<>0
  and pt.date_extrait is null
  order by rt.libelle,p.libelle;
  
  SELECT DISTINCT CONCAT('Type urssaf non renseigné pour le produit ',p.libelle)
  FROM pai_prd_tournee pep
  INNER JOIN produit p on pep.produit_id=p.id
  INNER JOIN produit_type pt ON p.type_id=pt.id
  LEFT OUTER JOIN prd_caract c on p.type_id=c.produit_type_id and c.code='URSSAF'
  LEFT OUTER JOIN prd_caract_constante cc ON cc.prd_caract_id=c.id and pep.produit_id=cc.produit_id  AND cc.valeur_string in ('F','R')
  WHERE not pt.hors_presse
  and cc.valeur_string is null
  and pep.date_extrait is null
  ;  
  
  SELECT DISTINCT CONCAT('Rémunération supplément inconnu pour le produit ',p.libelle) ,pt.date_distrib,pt.depot_id,pt.flux_id-- ,ppt.*,pt.*
  FROM pai_prd_tournee ppt
  inner join pai_tournee pt on ppt.tournee_id=pt.id
  inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
  inner join produit p on ppt.produit_id=p.id 
  where p.type_id in (2,3)
  and ppt.qte>0 and (ppt.pai_qte is null or ppt.pai_taux is null or ppt.pai_mnt is null)
  and pt.date_extrait is null
  and epd.typetournee_id in (1,2) -- pas pour les encadrants
  ;

  SELECT DISTINCT CONCAT('Rémunération inconnu pour le produit ',p.libelle) ,pt.date_distrib,pt.depot_id,pt.flux_id-- ,ppt.*,pt.*
  FROM pai_prd_tournee ppt
  inner join pai_tournee pt on ppt.tournee_id=pt.id
  inner join emp_pop_depot epd on pt.employe_id=epd.employe_id and pt.date_distrib between epd.date_debut and epd.date_fin
  inner join produit p on ppt.produit_id=p.id 
  where p.type_id>3
  and ppt.qte>0 and (ppt.pai_qte is null or ppt.pai_taux is null or ppt.pai_mnt is null)
  and pt.date_extrait is null
  and epd.typetournee_id in (1,2) -- pas pour les encadrants
  ;

  SELECT distinct CONCAT('Poste de paie durée totale activité introuvable pour ',a.libelle,'(',a.id,') et ',j.libelle,'(',j.id,')')
  FROM emp_pop_depot e
  INNER JOIN pai_activite h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.date_debut AND e.date_fin
  INNER JOIN ref_activite a ON h.activite_id=a.id
  INNER JOIN ref_typejour j ON h.typejour_id=j.id
  LEFT OUTER JOIN pai_ref_postepaie_activite p on h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id 
  WHERE TIME_TO_SEC(h.duree)<>0
  AND p.poste_hj IS NULL
  and h.date_extrait is null
  ;

  SELECT distinct CONCAT('Poste de paie durée nuit activité introuvable pour ',a.libelle,'(',a.id,') et ',j.libelle,'(',j.id,')')
  FROM emp_pop_depot e
  INNER JOIN pai_activite h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.date_debut AND e.date_fin
  INNER JOIN ref_activite a ON h.activite_id=a.id
  INNER JOIN ref_typejour j ON h.typejour_id=j.id
  LEFT OUTER JOIN pai_ref_postepaie_activite p on h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id 
  WHERE TIME_TO_SEC(h.duree_nuit)<>0
  AND p.poste_hn IS NULL
  and h.date_extrait is null
  ;
END;
