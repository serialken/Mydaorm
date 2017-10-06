-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_maj;
CREATE PROCEDURE int_mroad2ev_maj(
    IN 		_idtrt		INT,
    IN 		_is1M        	BOOLEAN
) BEGIN
    INSERT INTO pai_int_log(idtrt,date_log,module,level,msg)
    SELECT _idtrt,now(),'Kilomètres non payés',2,CONCAT('Kilomètre non payé sur tournée ( ',pt.code,', ',pt.date_distrib,', ',e.matricule,') : ',pt.nbkm_paye)
    FROM pai_ev_tournee pt
    LEFT OUTER JOIN ref_transport rt on pt.transport_id=rt.id
    INNER JOIN employe e on pt.employe_id=e.id
    WHERE NOT coalesce(rt.km_paye,false)
    AND pt.nbkm_paye<>0
    ;
    UPDATE pai_ev_tournee pt
    LEFT OUTER JOIN ref_transport rt on pt.transport_id=rt.id
    SET pt.nbkm_paye=0
    WHERE NOT coalesce(rt.km_paye,false)
    AND pt.nbkm_paye<>0
    ;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_maj','Supprime les kilomètres tournée')
    ;
    INSERT INTO pai_int_log(idtrt,date_log,module,msg)
    SELECT _idtrt,now(),'Kilomètres non payés',CONCAT('Kilomètre non payé sur activité ( ',ra.libelle,', ',pa.date_distrib,', ',e.matricule,') : ',pa.nbkm_paye)
    FROM pai_ev_activite pa
    INNER JOIN ref_activite ra on pa.activite_id=ra.id
    LEFT OUTER JOIN ref_transport rt on pa.transport_id=rt.id
    INNER JOIN employe e on pa.employe_id=e.id
    WHERE NOT coalesce(rt.km_paye,false)
    AND pa.nbkm_paye<>0
    ;
    UPDATE pai_ev_activite pa
    LEFT OUTER JOIN ref_transport rt on pa.transport_id=rt.id
    SET pa.nbkm_paye=0
    WHERE NOT coalesce(rt.km_paye,false)
    AND pa.nbkm_paye<>0
    ;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_maj','Supprime les kilomètres activité')
    ;
    DELETE pa FROM pai_ev_activite pa WHERE pa.activite_id IN (-1);
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_maj','Supprime les retards')
    ;

    INSERT INTO pai_int_log(idtrt,date_log,module,msg)
    SELECT _idtrt,now(),'Ouverture de centre',CONCAT('Ouverture de centre non autorisée pour ',e.matricule,' le ',pa.date_distrib)
    FROM pai_ev_activite pa
    INNER JOIN emp_pop_depot epd ON pa.employe_id=epd.employe_id and pa.date_distrib between epd.date_debut and epd.date_fin
    INNER JOIN ref_population rp on epd.population_id=rp.id
    INNER JOIN employe e on pa.employe_id=e.id
    WHERE pa.ouverture
    and not rp.ouverture
    ;
    UPDATE pai_ev_activite pa
    INNER JOIN emp_pop_depot epd ON pa.employe_id=epd.employe_id and pa.date_distrib between epd.date_debut and epd.date_fin
    INNER JOIN ref_population rp on epd.population_id=rp.id
    set pa.ouverture=0
    WHERE pa.ouverture
    and not rp.ouverture
    ;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_maj','Supprime les ouvertures de centre non autorisées')
    ;
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_maj_1M;
CREATE PROCEDURE int_mroad2ev_maj_1M(
    IN 		_idtrt		INT,
    IN 		_is1M        	BOOLEAN
) BEGIN
  IF (_is1M) THEN
    UPDATE pai_ev_tournee SET typejour_id=CASE WHEN DAYOFWEEK(date_distrib)=1 THEN 2 ELSE 1 END;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_maj_1M','Supprime le type de jour F sur tournée')
    ;
    UPDATE pai_ev_activite SET typejour_id=CASE WHEN DAYOFWEEK(date_distrib)=1 THEN 2 ELSE 1 END;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_maj_1M','Supprime le type de jour F sur activité')
    ;
    UPDATE pai_ev_tournee SET nbkm_paye=0 WHERE nbkm_paye<>0;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_maj_1M','Supprime les kilomètres tournée')
    ;
    UPDATE pai_ev_activite SET nbkm_paye=0 WHERE nbkm_paye<>0;
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_maj_1M','Supprime les kilomètres activité')
    ;
    DELETE FROM pai_ev_activite WHERE activite_id IN (-1,-2);
    call int_logrowcount_C(_idtrt,4,'int_mroad2ev_maj_1M','Supprime les heures d''attente et les retards')
    ;
  END IF;
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- Mise à jour de employe_id dans client_a_servir_logist
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_maj_casl;
CREATE PROCEDURE int_mroad2ev_maj_casl(
    IN 		_idtrt		INT,
    IN 		_date_debut 	DATE,
    IN 		_date_fin 	DATE
) BEGIN
  update client_a_servir_logist casl
  inner join pai_ev_tournee pt on casl.pai_tournee_id=pt.id
  set casl.employe_id=pt.employe_id
  where coalesce(casl.employe_id,0)<>coalesce(pt.employe_id,0)
;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_maj_casl','employe_id maj');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- abo
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_maj_abo;
CREATE PROCEDURE int_mroad2ev_maj_abo(
    IN 		_idtrt		INT
) BEGIN
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    -- Taux de qualité semaine proximy/media
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e SET nbabo=(SELECT coalesce(SUM(p.nbcli),0) 
                                      FROM pai_ev_tournee t 
                                      INNER JOIN pai_ev_produit p ON p.tournee_id=t.id 
                                      WHERE t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.d AND e.f 
                                      AND (e.societe_id=1 AND t.typejour_id=1/*'S'*/ AND p.natureclient_id=0/*A*/
                                      OR e.societe_id=2)
                                      AND p.typeproduit_id=1 -- seulement les journaux
                                      GROUP BY t.employe_id);
    CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_maj_abo','Colonne tnbrabo sur Table pai_ev_emp_pop_depot');
   -- Si pas d'abonnés, tnbrabo reste à null
    UPDATE pai_ev_emp_pop e SET nbabo=0 WHERE nbabo IS NULL;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e 
    SET nbrec=
      (SELECT CASE WHEN e.societe_id=1 THEN 
        SUM(coalesce(r.nbrec_abonne,0))
      ELSE
        SUM(coalesce(r.nbrec_abonne,0)+coalesce(r.nbrec_diffuseur,0))
      END
      FROM pai_ev_reclamation r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND (e.societe_id=1 AND t.typejour_id=1
      OR e.societe_id=2)
      GROUP BY t.employe_id)
    ;
    UPDATE pai_ev_emp_pop e SET taux= CASE WHEN e.nbabo<>0 THEN e.nbrec*1000/coalesce(e.nbabo,0) ELSE 0 END;
    UPDATE pai_ev_emp_pop SET taux=0 WHERE taux IS NULL or  taux<0;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e 
    SET nbrec_brut=
      (SELECT CASE WHEN e.societe_id=1 THEN 
        SUM(coalesce(r.nbrec_abonne_brut,0))
      ELSE
        SUM(coalesce(r.nbrec_abonne_brut,0)+coalesce(r.nbrec_diffuseur_brut,0))
      END
      FROM pai_ev_reclamation r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND (e.societe_id=1 AND t.typejour_id=1
      OR e.societe_id=2)
      GROUP BY t.employe_id)
    ;
    UPDATE pai_ev_emp_pop e SET taux_brut= CASE WHEN e.nbabo<>0 THEN e.nbrec_brut*1000/coalesce(e.nbabo,0) ELSE 0 END;
    UPDATE pai_ev_emp_pop SET taux_brut=0 WHERE taux_brut IS NULL or  taux_brut<0;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    -- Taux de qualité dimanche proximy
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e SET nbabo_DF=(SELECT coalesce(SUM(p.nbcli),0) 
                                      FROM pai_ev_tournee t 
                                      INNER JOIN pai_ev_produit p ON p.tournee_id=t.id 
                                      WHERE t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.d AND e.f 
                                      AND e.societe_id=1 AND t.typejour_id in (2,3)
                                      AND p.typeproduit_id=1 -- seulement les journaux
                                      GROUP BY t.employe_id);
    CALL int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Colonne nbabo_DF sur Table pai_ev_emp_pop_depot');
   -- Si pas d'abonnés, tnbrabo reste à null
    UPDATE pai_ev_emp_pop e SET nbabo_DF=0 WHERE nbabo_DF IS NULL;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e 
    SET nbrec_DF=
      (SELECT  CASE WHEN e.societe_id=1 THEN 
        SUM(coalesce(r.nbrec_abonne,0))
      ELSE
        SUM(coalesce(r.nbrec_abonne,0)+coalesce(r.nbrec_diffuseur,0))
      END
      FROM pai_ev_reclamation r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND e.societe_id=1 AND t.typejour_id in (2,3)
      GROUP BY t.employe_id)
    ;
    UPDATE pai_ev_emp_pop e SET taux_DF= CASE WHEN e.nbabo_DF<>0 THEN e.nbrec_DF*1000/coalesce(e.nbabo_DF,0) ELSE 0 END;
    UPDATE pai_ev_emp_pop SET taux_DF=0 WHERE taux_DF IS NULL or  taux_DF<0;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e 
    SET nbrec_DF_brut=
      (SELECT  CASE WHEN e.societe_id=1 THEN 
        SUM(coalesce(r.nbrec_abonne_brut,0))
      ELSE
        SUM(coalesce(r.nbrec_abonne_brut,0)+coalesce(r.nbrec_diffuseur_brut,0))
      END
      FROM pai_ev_reclamation r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND e.societe_id=1 AND t.typejour_id in (2,3)
      GROUP BY t.employe_id)
    ;
    UPDATE pai_ev_emp_pop e SET taux_DF_brut= CASE WHEN e.nbabo_DF<>0 THEN e.nbrec_DF_brut*1000/coalesce(e.nbabo_DF,0) ELSE 0 END;
    UPDATE pai_ev_emp_pop SET taux_DF_brut=0 WHERE taux_DF_brut IS NULL or  taux_DF_brut<0;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
	-- Les diffuseurs
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e 
    SET nbrec_dif=
      (SELECT  SUM(coalesce(r.nbrec_diffuseur,0))
      FROM pai_ev_reclamation r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND t.typejour_id in (1)
      GROUP BY t.employe_id)
    ;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e 
    SET nbrec_dif_DF=
      (SELECT  SUM(coalesce(r.nbrec_diffuseur,0))
      FROM pai_ev_reclamation r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND t.typejour_id in (2,3)
      GROUP BY t.employe_id)
    ;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e 
    SET nbrec_dif_brut=
      (SELECT  SUM(coalesce(r.nbrec_diffuseur_brut,0))
      FROM pai_ev_reclamation r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND t.typejour_id in (1)
      GROUP BY t.employe_id)
    ;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e 
    SET nbrec_dif_DF_brut=
      (SELECT  SUM(coalesce(r.nbrec_diffuseur_brut,0))
      FROM pai_ev_reclamation r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND t.typejour_id in (2,3)
      GROUP BY t.employe_id)
    ;
  END;
  
  
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_maj_qualite_DF;
/* -- 08/02/2017 INUTILISEE
CREATE PROCEDURE int_mroad2ev_maj_qualite_DF(
    IN 		_idtrt		    INT,
    IN 		_date_debut 	DATE,
    IN 		_date_fin 	  DATE,
    IN 		_is1M        	BOOLEAN
) BEGIN
DECLARE _date_dimanche DATE;
    -- Table pai_ev_qualite_1M utilisée le 1er mai pour la moyenne sur les 4 derniers datés
    -- Table pai_ev_majoration_df utilisée pour le calcul de la majoration de rémunération
-- ATTENTION : faire la même chose pour les repérages
  CALL int_logger(_idtrt,'ev_maj_qualite_DF','Table de travail pour la qualite Dimanche/Férié');
  DELETE FROM pai_ev_majoration_df;

  IF (_is1M AND DAYOFWEEK(_date_debut)=1) THEN
    DELETE FROM pai_ev_qualite_1M;

    SELECT MAX(prc.datecal) INTO _date_dimanche  FROM pai_ref_calendrier prc WHERE prc.datecal<=_date_debut AND prc.jour_id=1;

    -- SDVP 1er mai
    -- dimanche et jours feriés
    -- Majoration de 100% si aucune réclamation et aucun incident (majoration_dfq)
    -- Majoration de 50% sinon (majoration_df)
    INSERT INTO pai_ev_qualite_1M(employe_id,date_distrib,nbcli,qualite)
    SELECT t.employe_id,t.date_distrib,t.nbcli,
    CASE  WHEN r.tournee_id IS NULL THEN rp.majoration_dfq
          -- ATTENTION : normalement il faut appliquer le taux de 1/1000, mais il n'y a jamais 1000 clients de livrer le dimanche ==> simplification
          WHEN SUM(r.nbrec_abonne)<=0 AND SUM(r.nbrec_diffuseur)<=0 AND COUNT(i.id)=0 THEN rp.majoration_dfq
          ELSE rp.majoration_df
    END AS qualite
    FROM pai_tournee t
    INNER JOIN emp_pop_depot e ON t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.date_debut AND e.date_fin
    INNER JOIN ref_population rp ON e.population_id=rp.id
    LEFT OUTER JOIN pai_reclamation r ON t.id=r.tournee_id
    LEFT OUTER JOIN pai_incident i ON e.employe_id=i.employe_id AND i.date_distrib BETWEEN e.date_debut AND e.date_fin
    WHERE   (t.date_distrib=_date_dimanche
    OR   t.date_distrib=_date_dimanche - INTERVAL 7 DAY
    OR   t.date_distrib=_date_dimanche - INTERVAL 14 DAY
    OR   t.date_distrib=_date_dimanche - INTERVAL 21 DAY)
    GROUP BY t.id,t.employe_id, t.date_distrib, t.nbcli;
    
    -- si plusieurs tournées ont été validées pour un même porteur sur un même dimanche, c'est celle qui aura le plus grand nombre de clients qui devra être prise en compte
    -- ATTENTION, si les 2 tournees ont le même nombre de clients, on supprime les 2 !!!!
    DELETE FROM pai_ev_qualite_1M 
    WHERE (employe_id,date_distrib) IN (SELECT e.employe_id,e.date_distrib FROM pai_ev_qualite_1M e GROUP BY e.employe_id, e.date_distrib HAVING COUNT(*)>1)
    AND (employe_id,date_distrib,tnbrcli) NOT IN (SELECT e.employe_id,e.date_distrib,MAX(nbcli) FROM pai_ev_qualite_1M e GROUP BY e.employe_id, e.date_distrib HAVING COUNT(*)>1);

    INSERT INTO pai_ev_majoration_df(date_distrib,employe_id,majoration_df)     
    SELECT _date_fin,t.employe_id,
    CASE  -- l'individu n'a pas de tournées sur le mois précédent
          WHEN COUNT(q.date_distrib)=0 THEN rp.majoration_df
          ELSE SUM(coalesce(q.qualite,0))/COUNT(q.date_distrib)
    END
    FROM pai_ev_tournee t
    INNER JOIN emp_pop_depot e ON t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.date_debut AND e.date_fin
    INNER JOIN ref_population rp ON e.population_id=rp.id
    LEFT OUTER JOIN pai_ev_qualite_1M q ON t.mat=q.mat
    -- SDVP dimanche et jours ferié, Neo seulement jours feries
    WHERE (t.typejour_id IN (2,3) AND e.typetournee_id=1 OR t.typejour_id=3 AND e.typetournee_id=2)
    GROUP BY _date_fin, t.employe_id,rp.majoration_df;
    
  ELSE
    -- SDVP
    -- Seulement les dimanches et jours feriés
    -- Majoration de 100% si aucune réclamation et aucun incident (majoration_dfq)
    -- Majoration de 50% sinon (majoration_df)
    INSERT INTO pai_ev_majoration_df(date_distrib,employe_id,majoration_df)     
    SELECT t.date_distrib,t.employe_id,
    CASE  -- Il n'y a pas de réclamation
          WHEN COUNT(r.tournee_id)=0 THEN rp.majoration_dfq
          -- Toutes les réclamations sont annulées
          -- ATTENTION : normalement il faut appliquer le taux de 1/1000, mais il n'y a jamais 1000 clients de livrer le dimanche ==> simplification
          WHEN SUM(r.nbrec_abonne)/t.nbcli<=1/1000 AND SUM(r.nbrec_diffuseur)/t.nbcli<=1/1000 AND COUNT(i.id)=0  THEN rp.majoration_dfq
          -- Il y a des réclamations
          ELSE rp.majoration_df
    END
    FROM pai_ev_tournee t
    INNER JOIN emp_pop_depot e ON t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.date_debut AND e.date_fin
    INNER JOIN ref_population rp ON e.population_id=rp.id
    LEFT OUTER JOIN pai_ev_reclamation r ON t.id=r.tournee_id
    LEFT OUTER JOIN pai_incident i ON i.employe_id=e.employe_id AND i.date_distrib BETWEEN e.date_debut AND e.date_fin
    WHERE t.typejour_id IN (2,3) AND e.typetournee_id=1 -- SDVP dimanche et jours feriés
    GROUP BY t.date_distrib, t.employe_id, rp.majoration_df,rp.majoration_dfq;

    -- Neo/Media
    -- Seulement les jours feriés
    -- Majoration de 100%
    INSERT INTO pai_ev_majoration_df(date_distrib,employe_id,majoration_df)     
    SELECT t.date_distrib,t.employe_id,rp.majoration_df
    FROM pai_ev_tournee t
    -- ATTENTION, est-ce qu'il y a une condition sur les réclamations ?
    INNER JOIN emp_pop_depot e ON t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.date_debut AND e.date_fin
    INNER JOIN ref_population rp ON e.population_id=rp.id
    WHERE t.typejour_id=3 AND e.typetournee_id=2 -- Neo seulement jours feries
    GROUP BY t.date_distrib, t.employe_id, rp.majoration_df;
  END IF;
END;
*/

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_maj_tournee;
-- 08/02/2017, ne sert a rien, réalisé par recalcul majoration
/*
CREATE PROCEDURE int_mroad2ev_maj_tournee(
    IN 		_idtrt		INT,
    IN 		_date_debut 	DATE,
    IN 		_date_fin 	DATE
) BEGIN
	UPDATE pai_ev_tournee t
	INNER JOIN pai_ev_emp_pop_depot e ON t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.dCtr AND e.f
	INNER JOIN ref_population rp ON e.population_id=rp.id
	LEFT OUTER JOIN pai_ref_remuneration r ON e.societe_id=r.societe_id AND e.population_id=r.population_id AND t.date_distrib BETWEEN r.date_debut AND r.date_fin
	SET 	t.majoration_nuit=rp.majoration_nuit
  ;

  -- Recuperation du nombre d'heure de nuit du modèle
	UPDATE pai_ev_tournee t
	INNER JOIN pai_ev_emp_pop_depot e ON t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.dCtr AND e.f
  SET duree_nuit_modele = (SELECT SEC_TO_TIME(coalesce(SUM(TIME_TO_SEC(pai_heure_nuit(gt.heure_debut,mtj.duree))),0)) 
                            FROM modele_tournee_jour mtj
                            INNER JOIN modele_tournee mt on mtj.tournee_id=mt.id -- AND mt.actif=1
                            INNER JOIN groupe_tournee gt on mt.groupe_id=gt.id
                            WHERE t.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin AND t.employe_id=mtj.employe_id
                            GROUP BY e.employe_id)
	WHERE e.typetournee_id=1 -- SDVP
  ;
	  UPDATE pai_ev_tournee t
--    INNER JOIN pai_mois m on pm.date_distrib>=m.date_debut and pm.flux_id=m.flux_id
    SET duree_nuit_modele = 0
    WHERE duree_nuit_modele is null
    ;
  -- On remet à 0 la majoration de nuit lorsque le nombre d'heure nuit du modèle (sur une semaine)est inférieur à 5
	UPDATE pai_ev_tournee t
	INNER JOIN pai_ev_emp_pop_depot e ON t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.dCtr AND e.f
  SET t.majoration_nuit = 0
	WHERE TIME_TO_SEC(duree_nuit_modele)<5*3600
  AND e.typetournee_id=1 -- SDVP
  ;
END;
*/
