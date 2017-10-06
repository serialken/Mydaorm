 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul;
CREATE PROCEDURE int_mroad2ev_calcul(
    IN 		_idtrt		    INT,
    IN 		_isStc        BOOLEAN,
    IN 		_date_debut 	DATE,
    IN 		_date_fin 	  DATE,
    IN 		_is1M        	BOOLEAN
) BEGIN
  -- ATTENTION : verifier que tous les champs calculés de pai_tournee,pai_produit et pai_activite sont bien renseignés
  -- TOURNEES
  -- Heures tournées
  CALL int_mroad2ev_calcul_heure_tournee(_idtrt);
  CALL int_mroad2ev_calcul_heure_tournee_nuit(_idtrt);
  CALL int_mroad2ev_corrige_heure_tournee_nuit(_idtrt,'N');
  -- Quantité : CLIENT, SUPPLEMENT, REPERAGE
  CALL int_mroad2ev_calcul_nb_client(_idtrt);
  CALL int_mroad2ev_calcul_nb_supplement(_idtrt);
  CALL int_mroad2ev_calcul_nb_reperage(_idtrt);
  CALL int_mroad2ev_calcul_nb_reperage_FR(_idtrt);
  -- Rémunération
  CALL int_mroad2ev_calcul_remuneration(_idtrt);
  CALL int_mroad2ev_calcul_produit_horspresse(_idtrt);
  CALL int_mroad2ev_calcul_produit_presse(_idtrt);
  -- Majoration poids MPR/MPF
  CALL int_mroad2ev_calcul_majoration_poids(_idtrt);
  
  -- ACTIVITES
  -- Activités presse
  CALL int_mroad2ev_calcul_heure_activite(_idtrt);
  CALL int_mroad2ev_calcul_heure_activite_nuit(_idtrt);
  -- Activités hors-presse
  CALL int_mroad2ev_calcul_heure_activite_horspresse(_idtrt);
  
  -- Kilometres
  CALL int_mroad2ev_calcul_kilometre(_idtrt);
  CALL int_mroad2ev_calcul_pedestre(_idtrt);

  CALL int_mroad2ev_calcul_ouverture(_idtrt);

  -- Majoration
  CALL int_mroad2ev_calcul_majoration_HT(_idtrt);
  CALL int_mroad2ev_calcul_majoration_HJ(_idtrt);
  -- Pénibilité
  CALL int_mroad2ev_penibilite(_idtrt);
  
  IF (NOT _is1M) THEN
    CALL int_mroad2ev_calcul_majoration_JT(_idtrt);
    CALL int_mroad2ev_calcul_majoration_JO(_idtrt);
    
  -- majoration des heures complementaires / supplementaires hors-presse
    CALL int_mroad2ev_calcul_majoration_HC1(_idtrt);
    CALL int_mroad2ev_calcul_majoration_HC2(_idtrt);
    CALL int_mroad2ev_calcul_majoration_HS1(_idtrt);
    CALL int_mroad2ev_calcul_majoration_HS2(_idtrt);

    -- Primes et bonus
    CALL int_mroad2ev_calcul_prime(_idtrt);
    CALL int_mroad2ev_calcul_bonus(_idtrt,_isStc,_date_debut,_date_fin);
    CALL int_mroad2ev_calcul_blocage(_idtrt,_date_debut,_date_fin); -- Prime qualité polyvalent (blocage) PQT
    CALL int_mroad2ev_calcul_specifique(_idtrt);
    
    -- Prime Neo 45€ code 4
    -- Prime Neo 60€ code 5
  END IF;
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- DUREE : HEURE JOUR, HEURE NUIT
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_heure_tournee;
CREATE PROCEDURE int_mroad2ev_calcul_heure_tournee(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  -- 30/03/2015 ON prend en compte le temps des supplément et autres produits dans la tournée
  SELECT 'HEURE JOUR',e.matricule,e.rc,g.poste,e.d,SUM(TIME_TO_SEC(t.duree))/3600,0,0,g.libelle
  FROM pai_ev_emp_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('HJS','HJD','HJF')
  WHERE (g.semaine AND t.typejour_id=1 OR g.dimanche AND t.typejour_id=2 OR g.ferie AND t.typejour_id=3)
  GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle 
  HAVING SUM(TIME_TO_SEC(t.duree))<>0;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_heure_tournee','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_heure_tournee_nuit;
CREATE PROCEDURE int_mroad2ev_calcul_heure_tournee_nuit(
    IN 		_idtrt		INT
) BEGIN
-- ATTENTION,
-- ici ON proratise seulement sur les titres de presse principaux
-- ON fait reperage x2, ON ne tiend pas compte de Mediapresse car pas d'heures de nuit !!!
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'HEURE NUIT',e.matricule,e.rc,prpg.poste,e.d
  ,SUM((d.nbcli+2*d.nbrep)*TIME_TO_SEC(t.duree_nuit)/d2.nb)/3600
  ,prr.valeur*prpg.majoration/100
  ,SUM((d.nbcli+2*d.nbrep)*TIME_TO_SEC(t.duree_nuit)/d2.nb)/3600*prr.valeur*prpg.majoration/100
  ,prpg.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id AND d.typeproduit_id=1 -- Seulement pour les titres de presse principaux
  INNER JOIN (SELECT d2.tournee_id,sum(d2.nbcli+2*d2.nbrep) as nb from pai_ev_produit d2 WHERE d2.typeproduit_id=1 GROUP BY d2.tournee_id) as d2 on d2.tournee_id=d.tournee_id
  INNER JOIN pai_ref_postepaie_general prpg ON prpg.code IN ('HSR','HSF','HDR','HDF')
  LEFT OUTER JOIN pai_ref_remuneration prr ON e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND e.d between prr.date_debut AND prr.date_fin
  WHERE (prpg.semaine AND t.typejour_id=1 OR prpg.dimanche AND t.typejour_id=2 OR prpg.ferie AND t.typejour_id=3)
  AND d.typeurssaf_id=prpg.typeurssaf_id
  -- ON envoie les heures de nuit que si elles sont majorées
  AND t.majoration_nuit>0
--  AND e.typetournee_id=1
  GROUP BY e.matricule, e.rc, prpg.poste, e.d, prpg.libelle, prr.valeur*prpg.majoration/100
  HAVING SUM((d.nbcli+2*d.nbrep)*TIME_TO_SEC(t.duree_nuit)/d2.nb)<>0;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_heure_tournee_nuit','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_corrige_heure_tournee_nuit;
CREATE PROCEDURE int_mroad2ev_corrige_heure_tournee_nuit(
    IN 		_idtrt		INT,
    IN 		_is1M     BOOLEAN
) BEGIN
  delete from pai_ev_correction
  ;
  insert into pai_ev_correction(id,typev,matricule,datev,qte,sumqte)
  SELECT ek.id,ek.typev,ek.matricule,ek.datev,ek.qte,sum(ek.qte)
  from pai_ev ek
  where ek.typev='HEURE NUIT'
  AND ((ek.datev LIKE '%-05-01' AND _is1M) OR (ek.datev NOT LIKE '%-05-01' AND NOT _is1M))
  group by matricule,datev
  having ek.qte=max(ek.qte) AND ek.id=min(ek.id)
  ;
  UPDATE pai_ev ek
  INNER JOIN pai_ev_correction ec ON ek.id=ec.id
  SET ek.qte=ek.qte
          +(SELECT sum(TIME_TO_SEC(t.duree_nuit))/3600
            FROM pai_ev_emp_pop_depot e
            INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
            WHERE e.matricule=ek.matricule AND e.d=ek.datev
            GROUP BY e.matricule,e.d
            )
          -ec.sumqte
  WHERE ek.typev='HEURE NUIT'  -- Si ON est le 1er mai, ON corrige que les ev en 0501, sinon toutes les autres
  AND ((ek.datev LIKE '%-05-01' AND _is1M) OR (ek.datev NOT LIKE '%-05-01' AND NOT _is1M))
  ;
  UPDATE pai_ev ek
  INNER JOIN pai_ev_correction ec ON ek.id=ec.id
  SET ek.val=ek.qte*ek.taux
  WHERE ek.typev='HEURE NUIT'  -- Si ON est le 1er mai, ON corrige que les ev en 0501, sinon toutes les autres
  AND ((ek.datev LIKE '%-05-01' AND _is1M) OR (ek.datev NOT LIKE '%-05-01' AND NOT _is1M))
  ;
  CALL int_logrowcount_C(_idtrt,5,'ev_corrige_heure_tournee_nuit','');
END;

  -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- QUANTITE : CLIENT, SUPPLEMENT et REPERAGE
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_nb_client;
CREATE PROCEDURE int_mroad2ev_calcul_nb_client(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'NB CLIENT',e.matricule,e.rc,g.poste,e.d,CASE WHEN e.typetournee_id=1 THEN SUM(d.nbcli+2*d.nbrep) ELSE SUM(d.nbcli+d.nbrep) END,0,0,g.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('NCR','NCF')
  WHERE d.typeurssaf_id=g.typeurssaf_id
  AND d.typeproduit_id=1
  GROUP BY e.matricule,e.rc,g.poste,e.d,g.libelle,e.typetournee_id
  HAVING CASE WHEN e.typetournee_id=1 THEN SUM(d.nbcli+2*d.nbrep) ELSE SUM(d.nbcli+d.nbrep) END<>0;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_nb_client','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_nb_supplement;
CREATE PROCEDURE int_mroad2ev_calcul_nb_supplement(
    IN 		_idtrt		INT
)BEGIN
-- ATTENTION : faire une rupture également sur changement de date de valorisation du supplément
  set @poste = ''  COLLATE 'utf8_unicode_ci';
  set @matricule = ''  COLLATE 'utf8_unicode_ci';
  set @datev = now();
  set @num  = 1;

  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre)
  SELECT typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'SUPPLEMENT' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@poste = poste AND @matricule=matricule AND @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,e.d as datev,
          SUM(d.pai_qte) as qte,
          d.pai_taux as taux,
          SUM(d.pai_qte)*d.pai_taux as val,
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code IN ('NSR','NSF')
        WHERE d.typeproduit_id IN (2,3)
        AND d.typeurssaf_id=g.typeurssaf_id
        GROUP BY e.matricule, e.rc, g.poste, e.d, d.pai_taux, g.libelle
        HAVING SUM(d.pai_qte)<>0
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_nb_supplement','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_nb_reperage;
CREATE PROCEDURE int_mroad2ev_calcul_nb_reperage(
    IN 		_idtrt		INT
) BEGIN
  set @poste = ''  COLLATE 'utf8_unicode_ci';
  set @matricule = ''  COLLATE 'utf8_unicode_ci';
  set @datev = now();
  set @num  = 1;

  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre)
  SELECT typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'REPERAGE' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@poste = poste AND @matricule=matricule AND @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,e.d as datev,
          SUM(d.nbrep) as qte,
          ROUND(t.valrem_corrigee,3) as taux,
          SUM(d.nbrep)*t.valrem_corrigee as val,
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id AND d.typeproduit_id=1
        INNER JOIN pai_ref_postepaie_general g ON g.code IN ('NRR','NRF') AND d.typeurssaf_id=g.typeurssaf_id
        WHERE e.typetournee_id=2 -- que pour Médiapresse
        GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle,ROUND(t.valrem_corrigee*2,3)
        HAVING SUM(d.nbrep)<>0
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_nb_reperage','Médiapresse');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_nb_reperage_FR;
CREATE PROCEDURE int_mroad2ev_calcul_nb_reperage_FR(
    IN 		_idtrt		INT
) BEGIN
  set @poste = ''  COLLATE 'utf8_unicode_ci';
  set @matricule = ''  COLLATE 'utf8_unicode_ci';
  set @datev = now();
  set @num  = 1;

  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre)
  SELECT typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'REPERAGE' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@poste = poste AND @matricule=matricule AND @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,e.d as datev,
          SUM(d.nbrep) as qte,
          2*d.pai_taux as taux,
          SUM(d.nbrep)*2*d.pai_taux as val,
/*          SUM(d.nbrep) as qte,
          ROUND(t.valrem_corrigee,3) as taux,
          SUM(d.nbrep)*t.valrem_corrigee as val,*/
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code IN ('NRR','NRF')
        INNER JOIN produit_type pt ON d.typeproduit_id=pt.id
        WHERE d.typeproduit_id not in (1,2,3) and not pt.hors_presse
        AND d.typeurssaf_id=g.typeurssaf_id
        GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle,d.pai_taux
        HAVING SUM(d.nbrep)<>0
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_nb_reperage_HP','');
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- REMUNERATION TOURNEE
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_remuneration;
CREATE PROCEDURE int_mroad2ev_calcul_remuneration(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_int_log(idtrt,module,msg) 
  SELECT _idtrt,CONCAT('ev_calcul_remuneration ',g.poste),CONCAT('Tournée ',t.code,' Matricule ',t.employe_id,' Date ',t.date_distrib,' Valrem ',format(t.valrem,'0.00000'),' Valrem2 ',format(t.valrem_corrigee,'0.00000'),' Rejetée')
  FROM pai_ev_tournee t
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('RSR','RSF','RDR','RDF','RFR','RFF')
  WHERE d.typeurssaf_id=g.typeurssaf_id
  AND (g.semaine AND t.typejour_id=1 OR g.dimanche AND t.typejour_id=2 OR g.ferie AND t.typejour_id=3)
  AND d.typeproduit_id=1
  AND t.valrem<>0 AND t.valrem_corrigee<=0;

  set @poste = ''  COLLATE 'utf8_unicode_ci';
  set @matricule =''  COLLATE 'utf8_unicode_ci';
  set @datev = now();
  set @num  = 1;

  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre)
  SELECT typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'REMUN' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@matricule=matricule AND @poste = poste AND @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,greatest(e.d,s.date_debut) as datev,
          CASE 
            WHEN e.typetournee_id=1 THEN SUM(d.nbcli+2*d.nbrep)
            WHEN e.typetournee_id=2 THEN SUM(d.nbcli+d.nbrep)
          END as qte,
          ROUND(t.valrem_corrigee,3) as taux,
          CASE 
            WHEN e.typetournee_id=1 THEN ROUND(SUM((d.nbcli+2*d.nbrep)*t.valrem_corrigee),2)
            WHEN e.typetournee_id=2 THEN ROUND(SUM((d.nbcli+  d.nbrep)*t.valrem_corrigee/*+d.qte*0.01*/),2)
          END as val,
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code IN ('RSR','RSF','RDR','RDF','RFR','RFF')
        LEFT OUTER JOIN pai_ref_remuneration s ON e.societe_id=s.societe_id AND e.population_id=s.population_id AND t.date_distrib BETWEEN s.date_debut AND s.date_fin
      -- ATTENTION : Sortir les valeurs de rémunération inexistantes
        WHERE g.typeurssaf_id=d.typeurssaf_id
        AND (g.semaine AND t.typejour_id=1 OR g.dimanche AND t.typejour_id=2 OR g.ferie AND t.typejour_id=3)
        AND d.typeproduit_id=1
        AND t.valrem_corrigee>0
        GROUP BY e.matricule, e.rc,g.poste, greatest(e.d,s.date_debut),ROUND(t.valrem_corrigee,3),g.libelle -- to_char(t.valrem2*1000,'FM0000000000')
        HAVING ROUND(SUM((d.nbcli+d.nbrep)*t.valrem_corrigee),2)<>0
 --       ORDER BY 1,2,3,4,6 desc) as res
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_remuneration','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- PRODUIT HORS-PRESSE
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_produit_horspresse;
CREATE PROCEDURE int_mroad2ev_calcul_produit_horspresse(
    IN 		_idtrt		INT
)BEGIN
-- ATTENTION : faire une rupture également sur changement de date de valorisation du supplément
  set @poste = ''  COLLATE 'utf8_unicode_ci';
  set @matricule = ''  COLLATE 'utf8_unicode_ci';
  set @datev = now();
  set @num  = 1;

  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre)
  SELECT typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'HORSPRESSE' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@poste = poste AND @matricule=matricule AND @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,e.d as datev,
          SUM(d.pai_qte) as qte,
          d.pai_taux as taux,
          SUM(d.pai_qte)*d.pai_taux as val,
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code IN ('PHP')
        INNER JOIN produit_type pt ON d.typeproduit_id=pt.id
        WHERE pt.hors_presse
        GROUP BY e.matricule, e.rc, g.poste, e.d, d.pai_taux, g.libelle
        HAVING SUM(d.pai_qte)<>0 AND d.pai_taux<>0
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_produit_horspresse','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- PRODUIT PRESSE
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_produit_presse;
CREATE PROCEDURE int_mroad2ev_calcul_produit_presse(
    IN 		_idtrt		INT
)BEGIN
-- ATTENTION : faire une rupture également sur changement de date de valorisation du supplément
  set @poste = ''  COLLATE 'utf8_unicode_ci';
  set @matricule = ''  COLLATE 'utf8_unicode_ci';
  set @datev = now();
  set @num  = 1;

  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre)
  SELECT typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'PRESSE' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@poste = poste AND @matricule=matricule AND @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,e.d as datev,
          SUM(d.pai_qte) as qte,
          d.pai_taux as taux,
          SUM(d.pai_qte)*d.pai_taux as val,
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code IN ('PPR','PPF')
        INNER JOIN produit_type pt ON d.typeproduit_id=pt.id
        WHERE d.typeproduit_id not in (1,2,3) and not pt.hors_presse
        AND d.typeurssaf_id=g.typeurssaf_id
        GROUP BY e.matricule, e.rc, g.poste, e.d, d.pai_taux, g.libelle
        HAVING SUM(d.pai_qte)<>0 AND d.pai_taux<>0
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_produit_presse','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- KILOMETRE
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_kilometre;
CREATE PROCEDURE int_mroad2ev_calcul_kilometre(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'KILOMETRE',e.matricule,e.rc,g.poste,e.d,
  SUM(h.nbkm_paye),
  pppf.taux+pppv.taux,
  SUM(h.nbkm_paye)*(pppf.taux+pppv.taux),
  g.libelle
--  FROM pai_ev_emp_depot e
  FROM pai_ev_emp_pop_depot e
  INNER JOIN ref_emp_societe res ON e.societe_id=res.id
  INNER JOIN pai_png_societe pps ON res.code=pps.societecode
  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('KMS','KMD')
  INNER JOIN pai_png_ta_primesw pptpf  ON pptpf.code='KMPFIX'
  INNER JOIN pai_png_primesdvrssocw pppf ON pppf.typeprime=pptpf.oid AND pppf.societe=pps.oid AND e.d between pppf.begin_date AND pppf.end_date
  INNER JOIN pai_png_ta_primesw pptpv ON pptpv.code='KMPVAR'
  INNER JOIN pai_png_primesdvrssocw pppv ON pppv.typeprime=pptpv.oid AND pppv.societe=pps.oid AND e.d between pppv.begin_date AND pppv.end_date
  WHERE (g.semaine AND h.typejour_id=1 OR g.dimanche AND h.typejour_id=2 OR g.ferie AND h.typejour_id=3)
  AND (e.typetournee_id=1 AND h.transport_id IN (2,3))
  GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle,pppf.taux+pppv.taux
  HAVING SUM(h.nbkm_paye)<>0;  
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_kilometre','Proximy');
  
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'KILOMETRE',e.matricule,e.rc,g.poste,e.d,
  SUM(h.nbkm_paye),
  pppf.taux,
  SUM(h.nbkm_paye)*pppf.taux,
  g.libelle
--  FROM pai_ev_emp_depot e
  FROM pai_ev_emp_pop_depot e
  INNER JOIN ref_emp_societe res ON e.societe_id=res.id
  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('KM2')
  INNER JOIN pai_png_societe pps ON res.code=pps.societecode
  INNER JOIN pai_png_ta_primesw pptpf  ON pptpf.code='KM2REX'
  INNER JOIN pai_png_primesdvrssocw pppf ON pppf.typeprime=pptpf.oid AND pppf.societe=pps.oid AND e.d between pppf.begin_date AND pppf.end_date
  WHERE (g.semaine AND h.typejour_id=1 OR g.dimanche AND h.typejour_id=2 OR g.ferie AND h.typejour_id=3)
  AND (e.typetournee_id=2 AND h.transport_id=2)
  GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle, pppf.taux
  HAVING SUM(h.nbkm_paye)<>0;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_kilometre','2 roues Mediapresse');
  
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'KILOMETRE',e.matricule,e.rc,g.poste,e.d,
  SUM(h.nbkm_paye),
  pppf.taux,
  SUM(h.nbkm_paye)*pppf.taux,
  g.libelle
--  FROM pai_ev_emp_depot e
  FROM pai_ev_emp_pop_depot e
  INNER JOIN ref_emp_societe res ON e.societe_id=res.id
  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('KMS','KMD')
  INNER JOIN pai_png_societe pps ON res.code=pps.societecode
  INNER JOIN pai_png_ta_primesw pptpf  ON pptpf.code='KM4REX'
  INNER JOIN pai_png_primesdvrssocw pppf ON pppf.typeprime=pptpf.oid AND pppf.societe=pps.oid AND e.d between pppf.begin_date AND pppf.end_date
  WHERE (g.semaine AND h.typejour_id=1 OR g.dimanche AND h.typejour_id=2 OR g.ferie AND h.typejour_id=3)
  AND (e.typetournee_id=2 AND h.transport_id=3)
  GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle, pppf.taux
  HAVING SUM(h.nbkm_paye)<>0;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_kilometre','4 roues Mediapresse');
END;

-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_pedestre;
CREATE PROCEDURE int_mroad2ev_calcul_pedestre(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'KILOMETRE',e.matricule,e.rc,g.poste,e.d,
  COUNT(distinct t.date_distrib),
  pppf.montant,
  COUNT(distinct t.date_distrib)*pppf.montant,
  g.libelle
--  FROM pai_ev_emp_depot e
  FROM pai_ev_emp_pop_depot e
  INNER JOIN ref_emp_societe res ON e.societe_id=res.id
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('KMP')
  INNER JOIN pai_png_societe pps ON res.code=pps.societecode
  INNER JOIN pai_png_ta_primesw pptpf  ON pptpf.code='PRTOPE'
  INNER JOIN pai_png_primesdvrssocw pppf ON pppf.typeprime=pptpf.oid AND pppf.societe=pps.oid AND t.date_distrib between pppf.begin_date AND pppf.end_date
  WHERE e.typetournee_id=2 AND t.transport_id=1
  AND t.date_distrib>='2016-06-21'
  AND coalesce((SELECT SUM(coalesce(r.nbrec_abonne,0)+coalesce(r.nbrec_diffuseur,0))
       FROM pai_ev_reclamation r
       WHERE r.tournee_id=t.id
       ),0)/t.nbcli<3/1000
  GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle, pppf.montant
  HAVING COUNT(distinct t.date_distrib)>0;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_pedestre','Prime a pied');
END;
/*
select * from pai_png_ta_primesw pptpf  
  INNER JOIN pai_png_primesdvrssocw pppf ON pppf.typeprime=pptpf.oid
  WHERE pptpf.code='PRTOPE'
  */

-- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- MAJORATION POIDS
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_poids;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_poids(
    IN 		_idtrt		INT
) BEGIN
  set @poste = ''  COLLATE 'utf8_unicode_ci';
  set @matricule = ''  COLLATE 'utf8_unicode_ci';
  set @datev = now();
  set @num  = 1;

  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre)
  SELECT typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'POIDS' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@poste = poste AND @matricule=matricule AND @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,e.d as datev,
          SUM(d.pai_qte) as qte,
          d.pai_taux as taux,
          SUM(d.pai_qte)*d.pai_taux as val,
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code IN ('MPR','MPF')
        WHERE d.typeproduit_id IN (1)
        AND d.typeurssaf_id=g.typeurssaf_id
        GROUP BY e.matricule, e.rc, g.poste, e.d, d.pai_taux, g.libelle
        HAVING SUM(d.pai_qte)<>0 AND pai_taux is not null
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_poids','Médiapresse');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- ACTIVITE PRESSE
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_heure_activite;
CREATE PROCEDURE int_mroad2ev_calcul_heure_activite(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'ACTIVITE JOUR',e.matricule,e.rc,p.poste_hj,e.d
  ,SUM(TIME_TO_SEC(h.duree))/3600
  ,prr.valeur*prpg.majoration/100
  ,SUM(TIME_TO_SEC(h.duree))/3600*prr.valeur*prpg.majoration/100
  ,prpg.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_activite h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN ref_activite ra ON h.activite_id=ra.id AND not ra.est_hors_presse -- ON ne prend pas les activités hors-presse gérées avec les heures garanties
  INNER JOIN pai_ref_postepaie_activite p ON h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id
  INNER JOIN pai_ref_postepaie_general prpg ON p.poste_hj=prpg.poste
  LEFT OUTER JOIN pai_ref_remuneration prr ON e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND e.d between prr.date_debut AND prr.date_fin
  WHERE p.poste_hj<>'----'
  GROUP BY e.matricule, e.rc, p.poste_hj, e.d, prr.valeur*prpg.majoration/100,prpg.libelle
  HAVING SUM(TIME_TO_SEC(h.duree))<>0;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_heure_activite','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_heure_activite_nuit;
CREATE PROCEDURE int_mroad2ev_calcul_heure_activite_nuit(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'ACTIVITE NUIT',e.matricule,e.rc,p.poste_hn,e.d
  ,SUM(TIME_TO_SEC(h.duree_nuit))/3600
  ,if(ra.est_hors_presse,prr.valeurHP,prr.valeur)*prpg.majoration/100
  ,SUM(TIME_TO_SEC(h.duree_nuit))/3600*if(ra.est_hors_presse,prr.valeurHP,prr.valeur)*prpg.majoration/100
  ,'Durée nuit activité'
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_activite h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN ref_activite ra ON h.activite_id=ra.id
  INNER JOIN pai_ref_postepaie_activite p ON h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id
  INNER JOIN pai_ref_postepaie_general prpg ON p.poste_hn=prpg.poste
  LEFT OUTER JOIN pai_ref_remuneration prr ON e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND e.d between prr.date_debut AND prr.date_fin
  WHERE p.poste_hn<>'----'
  GROUP BY e.matricule, e.rc, p.poste_hn, e.d, if(ra.est_hors_presse,prr.valeurHP,prr.valeur)*prpg.majoration/100
  HAVING SUM(TIME_TO_SEC(h.duree_nuit))<>0;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_heure_activite_nuit','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- CONTRAT HORS-PRESSE
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- JL 24/06/2015
 -- ON envoie le max entre les heures a réaliser et les heures réalisées
 -- JL 01/06/2016
 -- Pour les activités Pleiades : ON paye la durée garantie en heure normale, le reste est payé en heures complémentaires/supplémentaire
 -- Pour les activités non Pleiades (formation) : ON paye la durée

DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_heure_activite_horspresse;
CREATE PROCEDURE int_mroad2ev_calcul_heure_activite_horspresse(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'ACTIVITE JOUR',e.matricule,e.rc,if(coalesce(ech.travhorspresse,'0')='1',if(p.poste_hj='HDIC','HDI2','HDI3'),p.poste_hj),greatest(e.d,prr.date_debut)
  ,greatest(sum(time_to_sec(h.duree)),sum(time_to_sec(h.duree_garantie)))/3600
  ,if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,greatest(sum(time_to_sec(h.duree)),sum(time_to_sec(h.duree_garantie)))/3600*if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,prpg.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_activite h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN ref_activite ra ON h.activite_id=ra.id AND ra.est_hors_presse -- ON ne prend pas les activités hors-presse gérées avec les heures garanties
  INNER JOIN pai_ref_postepaie_activite p ON h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id
  INNER JOIN pai_ref_postepaie_general prpg ON p.poste_hj=prpg.poste
  LEFT OUTER JOIN emp_pop_depot epd ON e.employe_id=epd.employe_id AND h.date_distrib between epd.date_debut AND epd.date_fin
  LEFT OUTER JOIN emp_contrat_hp ech ON e.employe_id=ech.employe_id AND h.date_distrib between ech.date_debut AND ech.date_fin AND h.activite_id=ech.activite_id-- AND h.depot_id=xrc.depot_id AND h.flux_id=xrc.flux_id
--  LEFT OUTER JOIN emp_contrat_hp ech ON h.xaoid=ech.xaoid
  LEFT OUTER JOIN pai_ref_remuneration prr ON e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND h.date_distrib between prr.date_debut AND prr.date_fin
  WHERE p.poste_hj<>'----'
  AND TIME_TO_SEC(h.duree)<>0
  GROUP BY e.matricule, e.rc, if(coalesce(ech.travhorspresse,'0')='1',if(p.poste_hj='HDIC','HDI2','HDI3'),p.poste_hj), greatest(e.d,prr.date_debut), if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100,prpg.libelle
  HAVING greatest(sum(time_to_sec(h.duree)),sum(time_to_sec(h.duree_garantie)))/3600<>0;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_heure_activite_horspresse','');
END;
/*
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_heure_activite_horspresse;
CREATE PROCEDURE int_mroad2ev_calcul_heure_activite_horspresse(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'ACTIVITE JOUR',e.matricule,e.rc,if(coalesce(xrc.travhorspresse,'0')='1',if(p.poste_hj='HDIC','HDI2','HDI3'),p.poste_hj),greatest(e.d,prr.date_debut)
  ,SUM(case when h.duree='00:00' then 0 when ra.est_pleiades then TIME_TO_SEC(h.duree_garantie) else TIME_TO_SEC(h.duree) end)/3600
  ,if(coalesce(xrc.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,SUM(case when h.duree='00:00' then 0 when ra.est_pleiades then TIME_TO_SEC(h.duree_garantie) else TIME_TO_SEC(h.duree) end)/3600*if(coalesce(xrc.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,prpg.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_activite h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN ref_activite ra ON h.activite_id=ra.id AND ra.est_hors_presse -- ON ne prend pas les activités hors-presse gérées avec les heures garanties
  INNER JOIN pai_ref_postepaie_activite p ON h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id
  INNER JOIN pai_ref_postepaie_general prpg ON p.poste_hj=prpg.poste
  LEFT OUTER JOIN emp_pop_depot epd ON e.employe_id=epd.employe_id AND h.date_distrib between epd.date_debut AND epd.date_fin
  LEFT OUTER JOIN pai_png_xrcautreactivit xrc ON epd.rcoid=xrc.relationcontrat AND h.activite_id=xrc.activite_id AND h.date_distrib between xrc.begin_date AND xrc.end_date -- AND h.depot_id=xrc.depot_id AND h.flux_id=xrc.flux_id
  LEFT OUTER JOIN pai_ref_remuneration prr ON e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND h.date_distrib between prr.date_debut AND prr.date_fin
  WHERE p.poste_hj<>'----'
  GROUP BY e.matricule, e.rc, if(coalesce(xrc.travhorspresse,'0')='1',if(p.poste_hj='HDIC','HDI2','HDI3'),p.poste_hj), greatest(e.d,prr.date_debut), if(coalesce(xrc.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100,prpg.libelle
  HAVING SUM(case when h.duree='00:00' then 0 when ra.est_pleiades then TIME_TO_SEC(h.duree_garantie) else TIME_TO_SEC(h.duree) end)/3600>0;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_heure_activite_horspresse','');
END;*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_HC1;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_HC1(
    IN 		_idtrt		INT
-- majoration heures complémentaires 10%
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'MAJO HCHS',e.matricule,e.rc,prpg.poste,least(h.date_fin,ech.date_fin)
  ,sum(h.nbheures_hc1)
  ,if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,sum(h.nbheures_hc1)*if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,prpg.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ref_postepaie_general prpg ON prpg.code='HC1'
  INNER JOIN pai_hchs h ON e.employe_id=h.employe_id
  INNER JOIN emp_contrat_hp ech ON h.xaoid=ech.xaoid
  LEFT OUTER JOIN pai_ref_remuneration prr ON e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND h.date_fin between prr.date_debut AND prr.date_fin
  WHERE h.nbheures_hc1>0
  AND least(h.date_fin,ech.date_fin) between e.d AND e.f
  GROUP BY e.matricule,e.rc,prpg.poste,least(h.date_fin,ech.date_fin),if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100,prpg.libelle
  ;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_majoration_HC1','');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_HC2;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_HC2(
    IN 		_idtrt		INT
-- majoration heures complémentaires 10%
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'MAJO HCHS',e.matricule,e.rc,prpg.poste,least(h.date_fin,ech.date_fin)
  ,sum(h.nbheures_hc2)
  ,if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,sum(h.nbheures_hc2)*if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,prpg.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ref_postepaie_general prpg ON prpg.code='HC2'
  INNER JOIN pai_hchs h ON e.employe_id=h.employe_id
  INNER JOIN emp_contrat_hp ech ON h.xaoid=ech.xaoid
  LEFT OUTER JOIN pai_ref_remuneration prr ON e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND h.date_fin between prr.date_debut AND prr.date_fin
  WHERE h.nbheures_hc2>0
  AND least(h.date_fin,ech.date_fin) between e.d AND e.f
  GROUP BY e.matricule,e.rc,prpg.poste,least(h.date_fin,ech.date_fin),if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100,prpg.libelle
  ;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_majoration_HC2','');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_HS1;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_HS1(
    IN 		_idtrt		INT
-- majoration heures complémentaires 10%
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'MAJO HCHS',e.matricule,e.rc,prpg.poste,least(h.date_fin,ech.date_fin)
  ,sum(h.nbheures_hs1)
  ,if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,sum(h.nbheures_hs1)*if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,prpg.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ref_postepaie_general prpg ON prpg.code='HS1'
  INNER JOIN pai_hchs h ON e.employe_id=h.employe_id
  INNER JOIN emp_contrat_hp ech ON h.xaoid=ech.xaoid
  LEFT OUTER JOIN pai_ref_remuneration prr ON e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND h.date_fin between prr.date_debut AND prr.date_fin
  WHERE h.nbheures_hs1>0
  AND least(h.date_fin,ech.date_fin) between e.d AND e.f
  GROUP BY e.matricule,e.rc,prpg.poste,least(h.date_fin,ech.date_fin),if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100,prpg.libelle
  ;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_majoration_HS1','');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_HS2;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_HS2(
    IN 		_idtrt		INT
-- majoration heures complémentaires 10%
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'MAJO HCHS',e.matricule,e.rc,prpg.poste,least(h.date_fin,ech.date_fin)
  ,sum(h.nbheures_hs2)
  ,if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,sum(h.nbheures_hs2)*if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,prpg.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ref_postepaie_general prpg ON prpg.code='HS2'
  INNER JOIN pai_hchs h ON e.employe_id=h.employe_id
  INNER JOIN emp_contrat_hp ech ON h.xaoid=ech.xaoid
  LEFT OUTER JOIN pai_ref_remuneration prr ON e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND h.date_fin between prr.date_debut AND prr.date_fin
  WHERE h.nbheures_hs2>0
  AND least(h.date_fin,ech.date_fin) between e.d AND e.f
  GROUP BY e.matricule,e.rc,prpg.poste,least(h.date_fin,ech.date_fin),if(coalesce(ech.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100,prpg.libelle
  ;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_majoration_HS2','');
END;
/*
select * from pai_int_traitement where typetrt like '%CLOTURE%' order by id desc
  SELECT h.anneemois,count(h.nbheures_hc1>0),count(h.nbheures_hc2>0),count(h.nbheures_hs1>0),count(h.nbheures_hs2>0)
  FROM pai_ev_emp_pop_depot_hst e
  INNER JOIN pai_int_traitement pit ON e.idtrt=pit.id AND  (typetrt like '%CLOTURE%' or typetrt like '%STC%') AND pit.statut='T'
  INNER JOIN pai_ref_postepaie_general prpg ON prpg.code='HC1'
  INNER JOIN pai_hchs h ON e.employe_id=h.employe_id AND h.date_fin between e.d AND e.f
  LEFT OUTER JOIN pai_png_xrcautreactivit xrc ON h.xaoid=xrc.oid
  LEFT OUTER JOIN pai_ref_remuneration prr ON e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND h.date_fin between prr.date_debut AND prr.date_fin
--  where h.anneemois='201605' 
  group by h.anneemois;
select anneemois,count(h.nbheures_hc1>0),count(h.nbheures_hc2>0),count(h.nbheures_hs1>0),count(h.nbheures_hs2>0) 
from pai_hchs h 
 group by anneemois

select * from pai_int_traitement where(typetrt like '%CLOTURE%' or typetrt like '%STC%') AND  anneemois='201605' order by id desc
select * from pai_hchs where anneemois='201605' and( nbheures_hc1>0 or nbheures_hc2>0 or nbheures_hs1>0 or nbheures_hs2>0)
select * from pai_ev_emp_pop_depot_hst where idtrt IN (5855,6002,6003,6172,6173) AND employe_id=7158
201501	0	0	0	0
201502	0	0	0	0
201503	3	3	0	0
201504	0	0	0	0
201505	0	0	4	0
201506	6	3	2	0 201506	6	3	4	0
201507	1	0	0	0
201508	1	0	5	0
201509	1	0	4	0
201510	0	0	4	0
201511	3	3	8	2
201512	5	3	10	1 201512	6	4	10	1
201601	3	0	13	0
201602	1	0	8	0
201603	3	1	10	0
201604	91	91	26	26  201604	98	98	29	29
201605	83	83	31	31  201605	92	92	24	24
*/

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- PRIME OUVERTURE CENTRE
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_ouverture;
CREATE PROCEDURE int_mroad2ev_calcul_ouverture(
    IN 		_idtrt		INT
)BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'OUVERTURE',e.matricule,e.rc,g.poste,e.d,count(*),pppf.montant,count(*)*pppf.montant,g.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN ref_emp_societe res ON e.societe_id=res.id
  INNER JOIN pai_png_societe pps ON res.code=pps.societecode
  INNER JOIN pai_ev_activite pa ON e.employe_id=pa.employe_id AND pa.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('OUV')
  INNER JOIN pai_png_ta_primesw pptpf  ON pptpf.code='POUVCE'
  INNER JOIN pai_png_primesdvrssocw pppf ON pppf.typeprime=pptpf.oid AND pppf.societe=pps.oid AND e.d between pppf.begin_date AND pppf.end_date
  WHERE ouverture
  GROUP BY e.matricule, e.rc, g.poste, e.d, pppf.taux, g.libelle
  HAVING count(*)<>0
  ;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_ouverture','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- PENIBILITE
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_penibilite;
CREATE PROCEDURE int_mroad2ev_penibilite(
    IN 		_idtrt		INT
) BEGIN
	  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
	  SELECT 'PENIBILITE',e.matricule,e.rc,g.poste,e.d,COUNT(DISTINCT h.date_distrib),0,0,g.libelle
	  FROM pai_ev_emp_pop_depot e
	  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
	  INNER JOIN pai_ref_postepaie_general g ON g.code='PEN'
      WHERE coalesce(h.activite_id,0) not IN (-1,-10,-11) -- ON ne prend pas les activtés bidons "complément heures garanties"
	  GROUP BY e.matricule,e.rc,g.code,e.d,g.libelle
	  having min(h.heure_debut)<='04:00:00' and sec_to_time(sum(time_to_sec(h.duree_nuit)))>='01:00:00';
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_penibilite','');
END;
-- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- MAJORATION
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_HT;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_HT(
    IN 		_idtrt		INT
  ) BEGIN
-- majoration heures travaillées
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'NB HEURE',e.matricule,e.rc,g.poste,e.d,SUM(TIME_TO_SEC(h.duree))/3600,0,0,g.libelle
  FROM pai_ev_emp_depot e
  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code='HTP'
  WHERE coalesce(h.activite_id,0) not in (-1,-11) -- ON ne prend pas les heures de retard, ni les heures garanties majorées
  GROUP BY e.matricule, e.rc, g.code, e.d, g.libelle 
  HAVING SUM(TIME_TO_SEC(h.duree))<>0;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_HT','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_HJ;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_HJ(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'NB HEURE',e.matricule,e.rc,g.poste,e.d,SUM(TIME_TO_SEC(h.duree)-TIME_TO_SEC(h.duree_nuit))/3600,0,0,g.libelle
  FROM pai_ev_emp_depot e
  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code='HJP'
  WHERE coalesce(h.activite_id,0) not in (-1,-11) -- ON ne prend pas les heures de retard
  GROUP BY e.matricule, e.rc, g.code, e.d, g.libelle 
  HAVING SUM(TIME_TO_SEC(h.duree)-TIME_TO_SEC(h.duree_nuit))<>0;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_HJ','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_JT;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_JT(
    IN 		_idtrt		INT
) BEGIN
-- majoration jours ouvrables travaillés
-- pour les porteurs, seulement les tournées
-- pour les polyvalents, tournées+heures
-- ==> utiliser pai_ev_emp_pop_depot pour prendre en compte le changement de statut
	  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
	  SELECT 'NB JOUR',e.matricule,e.rc,g.poste,e.d,COUNT(DISTINCT h.date_distrib),0,0,g.libelle
	  FROM pai_ev_emp_pop_depot e
	  INNER JOIN ref_population rp ON e.population_id=rp.id
	  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
	  INNER JOIN pai_ref_postepaie_general g ON g.code='JTP'
	  WHERE (e.typetournee_id=1 AND h.typejour_id=1 		-- SDVP et semaine
	  OR 	 e.typetournee_id=2) 	-- Neo/Media et semaine+ferie
	  AND rp.code IN ('EMDDPP','EMIDPP','EMIDTB','EMIDTE','EMDDTB','EMDDTE') -- pour les polyvalent ON prend également les activités en compte
      AND coalesce(h.activite_id,0) not IN (-1,-10,-11) -- ON ne prend pas les activtés bidons "complément heures garanties"
	  GROUP BY e.matricule,e.rc,g.code,e.d,g.libelle
  UNION ALL
	  SELECT 'NB JOUR',e.matricule,e.rc,g.poste,e.d,COUNT(DISTINCT h.date_distrib),0,0,g.libelle
	  FROM pai_ev_emp_pop_depot e
      INNER JOIN ref_population rp ON e.population_id=rp.id
	  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f
    -- INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
    LEFT OUTER JOIN ref_activite ra ON h.activite_id=ra.id
	  INNER JOIN pai_ref_postepaie_general g ON g.code='JTP'
	  WHERE (e.typetournee_id=1 AND h.typejour_id=1 		    -- SDVP et semaine
	  OR 	 e.typetournee_id=2) 	-- Media et semaine+ferie
	  AND rp.code IN ('EMIDPO','EMDDPO','EMIDRB','EMIDRE','EMDDRB','EMDDRE')
      AND coalesce(ra.est_JTPX,true) -- ON ne prend que les tournées ou les activités qui ON le flag JTPX
	  GROUP BY e.matricule, e.rc, g.code, e.d, g.libelle;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_JT','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_JO;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_JO(
    IN 		_idtrt		INT
-- majoration jours ouvrables periode
/*
Mail de Samuel du 17/02/2016
Si le changement de dépôt s'accompagne dans Pléiades d'une modification du dossier salarié entraînant un découpage de période de paie (à confirmer nécessairement par les CRHPAIE), il convient en effet pour les natures d'EV :
- JOPX => oui à envoyer à chaque découpage (ne pas prorater le nombre de jours ouvrables de la période, mais continuer à envoyer la valeur de la période complète. Exemple : 26 jours).
Attention, cela va pénaliser la prime de constance qualité (trimestrielle) qui s'appuie sur le cumul des jours travaillés et des jours ouvrables du trimestre. Autrement dit, les JOPX vont être doublés le mois où il y a découpage. Afin de parer à cet effet de bord, il convient d'envoyer en parallèle des JOPX dans ce cas bien précis, un nouvel EV pour neutraliser les JOPX de la seconde sous-période.
*/
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'NB JOUR',e.matricule,e.rc,g.poste,e.d,COUNT(DISTINCT prc.datecal),0,0,g.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ref_postepaie_general g ON g.code='JOP'
  INNER JOIN pai_ref_mois prm ON e.d between prm.date_debut AND prm.date_fin
  INNER JOIN pai_ref_calendrier prc ON prc.datecal BETWEEN prm.date_debut AND prm.date_fin
  WHERE prc.jour_id<>1 -- pas le dimanche
   -- Si neo/media, ON prend également les feriés
  AND (e.societe_id=2 OR not exists(select null from pai_ref_ferie f where e.societe_id=f.societe_id and prc.datecal=f.jfdate))
   -- Au moins une activité sur la rc
--  AND EXISTS(SELECT NULL FROM pai_ev_heure h WHERE e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f AND coalesce(h.activite_id,0) not IN (-1,-10,-11))
  AND ( EXISTS(SELECT NULL FROM pai_ev_tournee h WHERE e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f)
  OR exists(SELECT NULL FROM pai_ev_activite h WHERE e.employe_id=h.employe_id AND h.date_distrib BETWEEN e.d AND e.f AND h.activite_id not IN (-1,-10,-11)))
  GROUP BY e.matricule, e.rc, g.code, e.d,g.libelle;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_JO','');
/*
Mail de Samuel du 17/02/2016
En effet, si ON a des cas sur PROXIMY, il conviendra de compenser les JOPX "en doublon" pour le calcul de la prime de constance qualité.
Je te suggère d'envoyer en plus le code "JOXP" avec la même valeur que JOPX pour ce cas de figure.
*/
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT e.typev,e.matricule,e.rc,g.poste,e.datev,e.qte,e.taux,e.val,g.libelle
  FROM pai_ev e
  INNER JOIN pai_ev_emp_depot e2 ON e.matricule=e2.matricule AND e.rc=e2.rc AND e.datev=e2.d
  INNER JOIN pai_ref_postepaie_general g ON g.code='JOX'
  INNER JOIN pai_ref_mois prm ON e2.d between prm.date_debut AND prm.date_fin
  WHERE e.poste='JOPX'
  AND e2.d<>prm.date_debut  AND e2.d<>e2.dRC
  AND exists(select null from pai_ev_emp_depot e3 where e3.matricule=e2.matricule AND e3.rc=e2.rc AND e3.f+INTERVAL 1 DAY=e2.d AND e3.depot_id<>e2.depot_id)
  ;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_JOXP','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- PRIME ET BONUS
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_prime;
CREATE PROCEDURE int_mroad2ev_calcul_prime(
    IN 		_idtrt		INT
) BEGIN
    delete from pai_ev where typev='PRIME';
    delete from pai_ev_hst where idtrt=_idtrt AND typev='PRIME';
    
    call int_mroad2ev_maj_abo(_idtrt);

    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    CALL int_logger(_idtrt,'ev_calcul_prime','  Colonne Qualite sur Table pai_ev_emp_pop_depot');
    UPDATE pai_ev_emp_pop e SET qualite='O';
      
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
      -- MEDIAPRESSE Pas de prime pour les collecteurs/distributeurs de courrier/porteur de colis qui n'ont pas de contrat porteur
      -- supprimé au remplissage de la table
/*      UPDATE pai_ev_emp_pop_depot e SET qualite='P'
      WHERE NOT EXISTS(SELECT NULL 
                      FROM emp_pop_depot epd
                      INNER JOIN ref_emploi re ON epd.emploi_id=re.id
                      WHERE e.employe_id=epd.employe_id 
                      AND e.d<=epd.date_fin AND e.f>=date_debut
                      AND re.prime
                      )
      AND e.qualite='O' AND e.societe_id=2
      ;
      CALL int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Porteur sans prime');*/
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
     -- Si pas de tournée (semaine pour proximy), ON met un code particulier
     -- ATTENTION, il n'y a pas de code S dans la table de référence pour les polyvalents
      UPDATE pai_ev_emp_pop e SET qualite='S'
      WHERE NOT EXISTS(SELECT NULL 
                      FROM pai_ev_tournee t 
                      WHERE e.employe_id=t.employe_id 
                      AND t.date_distrib BETWEEN e.d AND e.f 
                      AND t.typejour_id=1
                      )
      AND NOT EXISTS(SELECT NULL 
                      FROM pai_ev_activite a 
                      INNER JOIN ref_activite ra ON a.activite_id=ra.id AND ra.est_JTPX
                      WHERE e.employe_id=a.employe_id
                      AND a.date_distrib BETWEEN e.d AND e.f 
                      AND a.typejour_id=1
                      AND a.activite_id not IN (-1,-10,-11)
                      )
      AND e.qualite='O' AND e.societe_id=1 AND e.emploi_code='POR'-- Mediapresse supprimé le 20/02/2015
      ;
      CALL int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Porteur sans tournée semaine Proximy');
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
      UPDATE pai_ev_emp_pop e SET qualite='H'
      WHERE NOT EXISTS(SELECT NULL 
                      FROM pai_ev_tournee t 
                      WHERE e.employe_id=t.employe_id 
                      AND t.date_distrib BETWEEN e.d AND e.f 
                      )
      AND NOT EXISTS(SELECT NULL 
                      FROM pai_ev_activite t 
                      WHERE e.employe_id=t.employe_id 
                      AND t.date_distrib BETWEEN e.d AND e.f 
                      AND t.activite_id not IN (-1,-10,-11)
                      )
      AND e.qualite='O' 
      AND (e.societe_id=1 AND e.emploi_code='POL'
      OR e.societe_id=2) -- Mediapresse supprimé le 20/02/2015
      ;
      CALL int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Polyvalent proximy ou Individus Mediapresse sans tournée/activité');
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
      -- si incident<>0 ==> pas de prime
      UPDATE pai_ev_emp_pop e SET qualite='I'
      WHERE EXISTS(SELECT NULL 
                    FROM pai_incident pi
                    WHERE pi.employe_id=e.employe_id 
                    AND pi.date_distrib BETWEEN e.d AND e.f -- AND pi.date_extrait is null 
                    AND pai_typejour_societe(pi.date_distrib,e.societe_id)=1
                    )
      AND e.qualite='O' AND e.societe_id=1
      ;
      CALL int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Individus avec incident Proximy');
      UPDATE pai_ev_emp_pop e SET qualite='J'
      WHERE EXISTS(SELECT NULL 
		              FROM pai_incident pi
                  WHERE pi.employe_id=e.employe_id 
                  AND pi.date_distrib BETWEEN e.d AND e.f -- AND pi.date_extrait is null 
                  )
      AND e.qualite='O' AND e.societe_id=2
      ;
      CALL int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Individus avec incident Mediapresse');
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
      -- réclamation diffuseur
      UPDATE pai_ev_emp_pop e SET qualite='D'
      WHERE EXISTS(SELECT NULL
		              FROM pai_ev_reclamation r
	  	            INNER JOIN pai_tournee t ON r.tournee_id=t.id
	 	              WHERE e.employe_id=t.employe_id
	  	            AND t.date_distrib BETWEEN e.d AND e.f -- loupée en cas de retro
                  -- RAJOUTER ANNEEMOIS dans PAI_EV_RECLAMATION ???
                  AND t.typejour_id=1/*='S' ATTENTION, faut-il prendre les jours feriés */ 
                  GROUP BY e.employe_id
                  HAVING SUM(coalesce(r.nbrec_diffuseur,0))>0)
      AND e.qualite='O' AND e.societe_id=1
      ;
      CALL int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Individus avec réclamation diffuseur');
     -- ------------------------------------------------------------------------------------------------------------------------------------------------
     -- qualite=1 au lieu de 2 si seulement abonné ou seulement diffuseur (mono client)
      UPDATE pai_ev_emp_pop e SET qualite='U'
      WHERE qualite='O' AND e.societe_id=1
      AND (
          NOT EXISTS(SELECT NULL FROM pai_ev_tournee t INNER JOIN pai_ev_produit p ON t.id=p.tournee_id WHERE t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.d AND e.f AND t.typejour_id=1/*'S'*/ AND p.natureclient_id=0/*'A'*/)
      OR  NOT EXISTS(SELECT NULL FROM pai_ev_tournee t INNER JOIN pai_ev_produit p ON t.id=p.tournee_id WHERE t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.d AND e.f AND t.typejour_id=1/*'S'*/ AND p.natureclient_id=1/*'D'*/)
      )
      ;
      CALL int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Individus mono client');
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    -- Propage la qualite sur pai_ev_emp_pop_depot
    -- Permet de dupliquer l'indicateur de prime sur tous les depôts
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    update pai_ev_emp_pop_depot ed
    INNER JOIN pai_ev_emp_pop e on e.employe_id=ed.employe_id and ed.d between e.d and e.f
-- emploi_code<>POR/POL si prime=0 !!!!    
--    INNER JOIN emp_pop_depot epd on  epd.employe_id=ed.employe_id and ed.d between epd.date_debut and epd.date_fin
--    INNER JOIN ref_emploi re on epd.emploi_id=re.id
    SET ed.qualite=e.qualite,
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
--    WHERE re.prime
    ;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
    SELECT DISTINCT 'PRIME',ed.matricule,ed.rc,p.poste,ed.d,rq.valeur,0,0,p.libelle
    FROM pai_ev_emp_pop_depot ed
    INNER JOIN pai_ref_postepaie_general p ON p.code='QLT'
    INNER JOIN pai_ref_qualite rq ON ed.qualite=rq.qualite AND ed.societe_id=rq.societe_id AND ed.emploi_code=rq.emploi_code
    WHERE ed.taux between rq.borne_inf AND rq.borne_sup
    AND rq.envoiNG
    ;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
/*
Mail de Samuel du 17/02/2016
En effet, si ON a des cas sur PROXIMY, il conviendra de compenser les JOPX "en doublon" pour le calcul de la prime de constance qualité.
Je te suggère d'envoyer en plus le code "JOXP" avec la même valeur que JOPX pour ce cas de figure.
*/
    INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
    SELECT e.typev,e.matricule,e.rc,g.poste,e.datev,e.qte,e.taux,e.val,g.libelle
    FROM pai_ev e
    INNER JOIN pai_ev_emp_depot e2 ON e.matricule=e2.matricule AND e.rc=e2.rc AND e.datev=e2.d
    INNER JOIN pai_ref_postepaie_general g ON g.code='QLD'
    INNER JOIN pai_ref_mois prm ON e2.d between prm.date_debut AND prm.date_fin
    WHERE e.poste='0105'
    AND e2.d<>prm.date_debut  AND e2.d<>e2.dRC
    AND exists(select null from pai_ev_emp_depot e3 where e3.matricule=e2.matricule AND e3.rc=e2.rc AND e3.f+INTERVAL 1 DAY=e2.d AND e3.depot_id<>e2.depot_id)
    AND e.qte<>0
    ;
    CALL int_logrowcount_C(_idtrt,5,'ev_calcul_prime','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_bonus;
CREATE PROCEDURE int_mroad2ev_calcul_bonus(
    IN 		_idtrt		  INT,
    IN 		_isStc      BOOLEAN,
    IN 		_date_debut DATE,
    IN 		_date_fin 	DATE
) BEGIN
  -- delete FROM ev WHERE codepaie IN (SELECT codepaieple FROM poste_paie WHERE codepaie='SBQ');
  -- Tous les trimestres
  IF (NOT _isStc AND mod(date_format(_date_fin,"%c"),3)=0) THEN
    INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
    SELECT DISTINCT 'BONUS',e.matricule,e.rc,p.poste,_date_debut,COUNT(DISTINCT t.date_distrib),0,datediff(_date_fin,date_add(date_add(_date_debut,interval -2 month),interval 1 day)),p.libelle
    FROM pai_ev_emp_depot e
    LEFT OUTER JOIN pai_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN date_add(_date_debut,INTERVAL -2 MONTH) AND _date_fin
    LEFT OUTER JOIN pai_reclamation r ON t.id=r.tournee_id
    LEFT OUTER JOIN pai_incident i ON e.employe_id=i.employe_id AND i.date_distrib BETWEEN date_add(_date_debut,INTERVAL -2 MONTH) AND _date_fin
    INNER JOIN pai_ref_postepaie_general p ON p.code='SBQ'
    -- Le contrat est superieur à 3 mois
    WHERE e.dRc<=date_add(_date_debut,INTERVAL -2 MONTH)
    -- L'individu est la en fin de periode de paie
    AND e.fRc>=_date_fin
    -- ON regarde toutes les réclamations sur les 3 derniers mois
    -- ATTENTION : Normalement, il faudrait regarder sur anneemois de pai_reclamation !!!
    AND e.flux_id=1 -- que pour Proximy
    GROUP BY e.employe_id, e.rc, p.poste, _date_debut, datediff(_date_fin,date_add(date_add(_date_debut,interval -2 month),interval 1 day)),p.libelle
    HAVING  SUM(coalesce(r.nbrec_abonne,0))<=0
    AND     SUM(coalesce(r.nbrec_diffuseur,0))<=0
    AND     SUM(coalesce(i.id,0))<=0
    -- ON génère seulement s'il y a au moins une tournée semaine
    AND  (  (EXISTS(SELECT NULL FROM pai_tournee t2 WHERE e.employe_id=t2.employe_id AND t2.typejour_id=1 AND t2.date_distrib BETWEEN date_add(_date_debut,INTERVAL -2 MONTH) AND date_add(_date_fin,INTERVAL -2 MONTH))
    or  EXISTS(SELECT NULL FROM pai_activite a2 INNER JOIN ref_activite ra ON a2.activite_id=ra.id AND ra.est_JTPX WHERE e.employe_id=a2.employe_id AND a2.typejour_id=1 AND a2.date_distrib BETWEEN date_add(_date_debut,INTERVAL -2 MONTH) AND date_add(_date_fin,INTERVAL -2 MONTH))
    )
    AND    (EXISTS(SELECT NULL FROM pai_tournee t2 WHERE e.employe_id=t2.employe_id AND t2.typejour_id=1 AND t2.date_distrib BETWEEN date_add(_date_debut,INTERVAL -1 MONTH) AND date_add(_date_fin,INTERVAL -1 MONTH))
    or  EXISTS(SELECT NULL FROM pai_activite a2 INNER JOIN ref_activite ra ON a2.activite_id=ra.id AND ra.est_JTPX WHERE e.employe_id=a2.employe_id AND a2.typejour_id=1 AND a2.date_distrib BETWEEN date_add(_date_debut,INTERVAL -1 MONTH) AND date_add(_date_fin,INTERVAL -1 MONTH))
    )
    AND    (EXISTS(SELECT NULL FROM pai_tournee t2 WHERE e.employe_id=t2.employe_id AND t2.typejour_id=1 AND t2.date_distrib BETWEEN _date_debut AND _date_fin)
    or  EXISTS(SELECT NULL FROM pai_activite a2 INNER JOIN ref_activite ra ON a2.activite_id=ra.id AND ra.est_JTPX WHERE e.employe_id=a2.employe_id AND a2.typejour_id=1 AND a2.date_distrib BETWEEN _date_debut AND _date_fin)
    )
    )
    ;
    CALL int_logrowcount_C(_idtrt,5,'ev_calcul_bonus','');
  END IF;
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_blocage;
CREATE PROCEDURE int_mroad2ev_calcul_blocage(
    IN 		_idtrt		  INT,
    IN 		_date_debut DATE,
    IN 		_date_fin DATE
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT DISTINCT 'PRIME',e.matricule,e.rc,g.poste,e.d,CASE WHEN COUNT(i.id)>0 THEN 0 ELSE 1 END,0,0,g.libelle
  FROM pai_ev_emp_pop_depot e
--  INNER JOIN ref_population rp ON e.population_id=rp.id AND rp.code IN ('EMIDTB','EMIDTE','EMDDTB','EMDDTE') -- polyvalents
  LEFT OUTER JOIN pai_incident i ON e.employe_id=i.employe_id AND i.date_distrib BETWEEN e.d AND e.f -- AND i.date_extrait is null
  INNER JOIN pai_ref_postepaie_general g ON g.code='PQT'
  WHERE e.emploi_code in ('POL')
  AND _date_fin>=e.dRc AND _date_debut<=e.fRc -- ATTENTION il y a les STC du mois suivant !!!!
  AND EXISTS(SELECT null FROM pai_ev_heure ph WHERE e.employe_id=ph.employe_id AND ph.date_distrib BETWEEN e.d AND e.f AND coalesce(ph.activite_id,0) not IN (-1,-10,-11)) -- Au moins une activité sur la période
      -- MEDIAPRESSE Pas de prime pour les collecteurs/distributeurs de courrier/porteur de colis qui n'ont pas de contrat porteur
  AND EXISTS(SELECT NULL 
                      FROM emp_pop_depot epd
                      INNER JOIN ref_population rp ON epd.population_id=rp.id AND rp.code IN ('EMIDTB','EMIDTE','EMDDTB','EMDDTE') -- polyvalents
                      INNER JOIN ref_emploi re ON epd.emploi_id=re.id
                      WHERE e.employe_id=epd.employe_id 
                      AND e.d<=epd.date_fin AND e.f>=epd.date_debut
                      AND re.prime
                      )
  AND e.societe_id=2
  GROUP BY e.matricule, e.rc, g.poste, e.d,g.libelle;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_blocage','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- 22/12/2015 Ajout d'un déclencheur lorsque le salarié n'a fait que des tournées spécifiques, et n'a pas de clients
 -- Permet de ventiler la prime qualité à 100% en BDC
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_specifique;
CREATE PROCEDURE int_mroad2ev_calcul_specifique(
    IN 		_idtrt		  INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT DISTINCT 'PRIME',e.matricule,e.rc,g.poste,e.d,1,0,0,g.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev e3 ON e3.poste='0105' AND e3.matricule=e.matricule AND e3.datev=e.d -- ON a une prime
  INNER JOIN pai_ev e1 ON e1.poste='JTPX' AND e1.matricule=e.matricule AND e1.datev between e.d AND e.f
	INNER JOIN pai_ref_postepaie_general g ON g.code='CPR'
  WHERE not exists(select null from pai_ev e2 where e1.matricule=e2.matricule AND e1.datev=e2.datev AND e2.poste IN ('0350','0351'))
  AND e.societe_id=1 -- seulement pour Proximy
  ;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_specifique','');
END;

 -- -----------------------------------------------------------------------------------------------------------------------------------------------
/*
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_heure;
  -- Heures BR/BF pour affichage bulletin Néo/Média
  -- heures travaillées + la transposition des suppléments en temps + repérage
  -- ATTENTION, les durees sont les mêmes pour HRBF et HRBD
  -- Pour le calcul des durées, ON prend la valeur de rémunération d'origine et non la valeur de rémunération majorée
CREATE PROCEDURE int_mroad2ev_calcul_heure(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT typev,matricule,rc,poste,datev,sum(qte),taux,val,libelle
  from (SELECT 'HEURE NEO' as typev,e.matricule,e.rc,g.poste,e.d as datev,
  if(d.typeproduit_id=1,
  	CASE
  	  WHEN d.nbcli = 0 THEN 0
  	  WHEN e.typetournee_id IN (1) THEN t.valrem*(1+rp.majoration/100)*(d.nbcli+2*d.nbrep)/r.valeur
  	  WHEN e.typetournee_id IN (2) THEN t.valrem*(1+rp.majoration/100)*(d.nbcli+d.nbrep)/r.valeur
  	  WHEN e.typetournee_id = 3 THEN 0
      ELSE t.valrem*d.nbcli/r.valeur
	  END
  ,0)+time_to_sec(d.duree_supplement)/3600 as qte
  ,0 as taux,0 as val,g.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('HNR','HNF')
  LEFT OUTER JOIN pai_ref_remuneration r ON coalesce(e.societe_id,1)=r.societe_id AND coalesce(e.population_id,1)=r.population_id AND t.date_distrib BETWEEN r.date_debut AND r.date_fin
  LEFT OUTER JOIN ref_population rp ON e.population_id=rp.id
  WHERE g.typeurssaf_id=d.typeurssaf_id
  union all
  SELECT 'HEURE NEO',e.matricule,e.rc,g.poste,e.d,time_to_sec(a.duree)/3600,0,0,g.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_activite a ON e.employe_id=a.employe_id AND a.date_distrib BETWEEN e.d AND e.f
  INNER join ref_activite ra ON a.activite_id=ra.id AND ra.est_JTPX=1
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('HNR')
  LEFT OUTER JOIN pai_ref_remuneration r ON coalesce(e.societe_id,1)=r.societe_id AND coalesce(e.population_id,1)=r.population_id AND a.date_distrib BETWEEN r.date_debut AND r.date_fin
  where a.activite_id not IN (-1,-10,-11)
  ) as res
  group by typev,matricule,rc,poste,datev,taux,libelle
  HAVING sum(qte)<>0;
  CALL int_logrowcount_C(_idtrt,5,'ev_calcul_heure','Médiapresse');
END;
*/

-- ------------------------------------------------------------------------------------------------------------------------------------------------
 /*
 -- Il n'y a plus de 0,01€
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_nb_exemplaire;
CREATE PROCEDURE int_mroad2ev_calcul_nb_exemplaire(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'NB EXEMP',e.matricule,e.rc,g.poste,e.d,SUM(d.qte),IF(t.typejour_id=3,0.02,0.01),SUM(d.qte)*IF(t.typejour_id=3,0.02,0.01),g.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN pai_ref_postepaie_general g ON g.code IN ('NER','NEF')
  WHERE d.typeurssaf_id=g.typeurssaf_id
  AND d.typeproduit_id=1
  AND e.typetournee_id=2
  GROUP BY e.matricule,e.rc,g.poste,e.d,g.libelle
  HAVING SUM(d.qte)<>0;
  CALL int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_nb_exemplaire','Médiapresse');
END;
*/

/*
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_nb_supplement_SDVP;
CREATE PROCEDURE int_mroad2ev_calcul_nb_supplement_SDVP(
    IN 		_idtrt		INT
)BEGIN
  INSERT INTO pai_int_log(idtrt,module,msg) 
  SELECT distinct _idtrt,'ev_calcul_supplement',CONCAT('Poste de paie ',case when d.typeurssaf_id=1 THEN 'BF' ELSE 'BDC' END,' non renseigné pour ',p.libelle,'(',p.id,')')
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN produit p ON d.produit_id=p.id
  LEFT OUTER JOIN pai_ref_postepaie_supplement s ON s.produit_id=d.produit_id
  WHERE d.typeproduit_id IN (2,3)
  AND d.qte<>0
  AND e.typetournee_id=1
  AND ( d.typeurssaf_id=1 AND (s.poste_bf='' or s.poste_bf is null)
  OR    d.typeurssaf_id=2 AND (s.poste_bdc='' or s.poste_bdc is null))
  ;
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'NB SUP SDVP',e.matricule,rc,CASE WHEN d.typeurssaf_id=1 THEN s.poste_bf ELSE s.poste_bdc END,e.d,SUM(d.qte),0,0,Concat('Supplément ',CASE WHEN d.typeurssaf_id=1 THEN s.poste_bf ELSE s.poste_bdc END)
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN pai_ref_postepaie_supplement s ON s.produit_id=d.produit_id
  WHERE d.typeproduit_id IN (2,3)
  AND e.typetournee_id=1
  AND ((d.typeurssaf_id=1 AND s.poste_bf is not null AND s.poste_bf<>'')
  OR (d.typeurssaf_id=2 AND s.poste_bdc is not null  AND s.poste_bdc<>''))
  GROUP BY  e.matricule, e.rc, CASE WHEN d.typeurssaf_id=1 THEN s.poste_bf ELSE s.poste_bdc END, e.d
  HAVING SUM(d.qte)<>0 
  ;
END;
*/
