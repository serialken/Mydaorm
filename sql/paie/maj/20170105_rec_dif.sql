   -- drop table pai_ev_emp_pop_hst_20170105;
   -- drop table pai_ev_emp_pop_depot_hst_20170105;
--   create table pai_ev_emp_pop_hst_20170105 as select * from pai_ev_emp_pop_hst;
--   create table pai_ev_emp_pop_depot_hst_20170105 as select * from pai_ev_emp_pop_depot_hst;
   
  create index pai_ev_emp_pop_hst_idx1 on pai_ev_emp_pop_hst(idtrt);
 -- create index pai_ev_emp_pop_depot_hst_idx1 on pai_ev_emp_pop_depot_hst(idtrt);
 -- create index pai_ev_tournee_hst_idx1 on pai_ev_tournee_hst(idtrt);
 -- create index pai_ev_produit_hst_idx1 on pai_ev_produit_hst(idtrt,tournee_id);
   
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    -- Taux de qualité semaine proximy/media
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop_hst e SET nbabo=(SELECT coalesce(SUM(p.nbcli),0) 
                                      FROM pai_ev_tournee_hst t 
                                      INNER JOIN pai_ev_produit_hst p ON p.tournee_id=t.id 
                                      WHERE t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.d AND e.f 
                                      AND (e.societe_id=1 AND t.typejour_id=1/*'S'*/ AND p.natureclient_id=0/*A*/
                                      OR e.societe_id=2)
                                      AND p.typeproduit_id=1 -- seulement les journaux
									  AND t.idtrt=e.idtrt and p.idtrt=e.idtrt
                                      GROUP BY t.employe_id);
   -- Si pas d'abonnés, tnbrabo reste à null
    UPDATE pai_ev_emp_pop_hst e SET nbabo=0 WHERE nbabo IS NULL;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop_hst e 
    SET nbrec=
      (SELECT CASE WHEN e.societe_id=1 THEN 
        SUM(coalesce(r.nbrec_abonne,0))
      ELSE
        SUM(coalesce(r.nbrec_abonne,0)+coalesce(r.nbrec_diffuseur,0))
      END
      FROM pai_ev_reclamation_hst r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND (e.societe_id=1 AND t.typejour_id=1
      OR e.societe_id=2)
	  AND r.idtrt=e.idtrt
      GROUP BY t.employe_id)
    ;
    UPDATE pai_ev_emp_pop_hst e SET taux= CASE WHEN e.nbabo<>0 THEN e.nbrec*1000/coalesce(e.nbabo,0) ELSE 0 END;
    UPDATE pai_ev_emp_pop_hst SET taux=0 WHERE taux IS NULL or  taux<0;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop_hst e 
    SET nbrec_brut=
      (SELECT CASE WHEN e.societe_id=1 THEN 
        SUM(coalesce(r.nbrec_abonne_brut,0))
      ELSE
        SUM(coalesce(r.nbrec_abonne_brut,0)+coalesce(r.nbrec_diffuseur_brut,0))
      END
      FROM pai_ev_reclamation_hst r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND (e.societe_id=1 AND t.typejour_id=1
      OR e.societe_id=2)
	  AND r.idtrt=e.idtrt
      GROUP BY t.employe_id)
    ;
    UPDATE pai_ev_emp_pop_hst e SET taux_brut= CASE WHEN e.nbabo<>0 THEN e.nbrec_brut*1000/coalesce(e.nbabo,0) ELSE 0 END;
    UPDATE pai_ev_emp_pop_hst SET taux_brut=0 WHERE taux_brut IS NULL or  taux_brut<0;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    -- Taux de qualité dimanche proximy
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop_hst e SET nbabo_DF=(SELECT coalesce(SUM(p.nbcli),0) 
                                      FROM pai_ev_tournee_hst t 
                                      INNER JOIN pai_ev_produit_hst p ON p.tournee_id=t.id 
                                      WHERE t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.d AND e.f 
                                      AND e.societe_id=1 AND t.typejour_id in (2,3)
                                      AND p.typeproduit_id=1 -- seulement les journaux
									  AND t.idtrt=e.idtrt and p.idtrt=e.idtrt
                                      GROUP BY t.employe_id);
   -- Si pas d'abonnés, tnbrabo reste à null
    UPDATE pai_ev_emp_pop_hst e SET nbabo_DF=0 WHERE nbabo_DF IS NULL;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop_hst e 
    SET nbrec_DF=
      (SELECT  CASE WHEN e.societe_id=1 THEN 
        SUM(coalesce(r.nbrec_abonne,0))
      ELSE
        SUM(coalesce(r.nbrec_abonne,0)+coalesce(r.nbrec_diffuseur,0))
      END
      FROM pai_ev_reclamation_hst r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND e.societe_id=1 AND t.typejour_id in (2,3)
	  AND r.idtrt=e.idtrt
      GROUP BY t.employe_id)
    ;
    UPDATE pai_ev_emp_pop_hst e SET taux_DF= CASE WHEN e.nbabo_DF<>0 THEN e.nbrec_DF*1000/coalesce(e.nbabo_DF,0) ELSE 0 END;
    UPDATE pai_ev_emp_pop_hst SET taux_DF=0 WHERE taux_DF IS NULL or  taux_DF<0;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop_hst e 
    SET nbrec_DF_brut=
      (SELECT  CASE WHEN e.societe_id=1 THEN 
        SUM(coalesce(r.nbrec_abonne_brut,0))
      ELSE
        SUM(coalesce(r.nbrec_abonne_brut,0)+coalesce(r.nbrec_diffuseur_brut,0))
      END
      FROM pai_ev_reclamation_hst r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND e.societe_id=1 AND t.typejour_id in (2,3)
	  AND r.idtrt=e.idtrt
      GROUP BY t.employe_id)
    ;
    UPDATE pai_ev_emp_pop_hst e SET taux_DF_brut= CASE WHEN e.nbabo_DF<>0 THEN e.nbrec_DF_brut*1000/coalesce(e.nbabo_DF,0) ELSE 0 END;
    UPDATE pai_ev_emp_pop_hst SET taux_DF_brut=0 WHERE taux_DF_brut IS NULL or  taux_DF_brut<0;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
	-- Les diffuseurs
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop_hst e 
    SET nbrec_dif=
      (SELECT  SUM(coalesce(r.nbrec_diffuseur,0))
      FROM pai_ev_reclamation_hst r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND t.typejour_id in (1)
	  AND r.idtrt=e.idtrt
      GROUP BY t.employe_id)
    ;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop_hst e 
    SET nbrec_dif_DF=
      (SELECT  SUM(coalesce(r.nbrec_diffuseur,0))
      FROM pai_ev_reclamation_hst r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND t.typejour_id in (2,3)
	  AND r.idtrt=e.idtrt
      GROUP BY t.employe_id)
    ;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop_hst e 
    SET nbrec_dif_brut=
      (SELECT  SUM(coalesce(r.nbrec_diffuseur_brut,0))
      FROM pai_ev_reclamation_hst r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND t.typejour_id in (1)
	  AND r.idtrt=e.idtrt
      GROUP BY t.employe_id)
    ;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop_hst e 
    SET nbrec_dif_DF_brut=
      (SELECT  SUM(coalesce(r.nbrec_diffuseur_brut,0))
      FROM pai_ev_reclamation_hst r
      INNER JOIN pai_tournee t ON r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND t.typejour_id in (2,3)
	  AND r.idtrt=e.idtrt
      GROUP BY t.employe_id)
    ;
	
	   update pai_ev_emp_pop_depot_hst ed
    INNER JOIN pai_ev_emp_pop_hst e on e.employe_id=ed.employe_id and ed.d between e.d and e.f and e.idtrt=ed.idtrt
    SET -- ed.qualite=e.qualite,
    ed.taux=e.taux,
    ed.taux_df=e.taux_df,
    ed.taux_brut=e.taux_brut,
    ed.taux_df_brut=e.taux_df_brut,
    ed.nbabo=e.nbabo,
    ed.nbabo_df=e.nbabo_df,
    ed.nbrec=e.nbrec,
    ed.nbrec_df=e.nbrec_df,
    ed.nbrec_brut=e.nbrec_brut,
    ed.nbrec_df_brut=e.nbrec_df_brut,
    ed.nbrec_dif=e.nbrec_dif,
    ed.nbrec_dif_df=e.nbrec_dif_df,
    ed.nbrec_dif_brut=e.nbrec_dif_brut,
    ed.nbrec_dif_df_brut=e.nbrec_dif_df_brut
    ;
    
    select * from pai_ev_emp_pop_depot_hst;
    select * from pai_ev_emp_pop_hst;
    
        select * from pai_ev_emp_pop_depot_hst where nbrec_dif<>0;
        select * from pai_ev_emp_pop_depot_hst where nbrec_dif_brut<>0;
