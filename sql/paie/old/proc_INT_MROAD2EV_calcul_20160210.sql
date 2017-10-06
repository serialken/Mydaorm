/**
    IMPORTANT
    Les fichiers SQL qui sont exécutés par Symfony/Doctrine ne doivent pas comporter de changement de délimiteur.
    Le délimiteur naturel (;) est toujours utilisé.
    Attention à ne pas inclure d'instruction DELIMITER dans le script SQL.
*/

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
  call int_mroad2ev_calcul_heure_tournee(_idtrt);
  call int_mroad2ev_calcul_heure_tournee_nuit(_idtrt);
  call int_mroad2ev_corrige_heure_tournee_nuit(_idtrt,'N');
  
  -- Activités
  call int_mroad2ev_calcul_heure_activite(_idtrt);
  call int_mroad2ev_calcul_heure_activite_horspresse(_idtrt);
  call int_mroad2ev_calcul_heure_activite_nuit(_idtrt);

  -- Heures BR/BF pour affichage bulletin Néo/Média
  -- call int_mroad2ev_calcul_heure(_idtrt);

  -- Rémunération
  call int_mroad2ev_calcul_remuneration(_idtrt);
  
  -- Quantité : CLIENT, SUPPLEMENT, REPERAGE
  call int_mroad2ev_calcul_nb_client(_idtrt);
--  call int_mroad2ev_calcul_nb_exemplaire(_idtrt); Il n'y a plus de 0,01€
--  call int_mroad2ev_calcul_nb_supplement_SDVP(_idtrt);
  call int_mroad2ev_calcul_nb_supplement(_idtrt);
  call int_mroad2ev_calcul_nb_reperage(_idtrt);
  
  -- Kilometres
  call int_mroad2ev_calcul_kilometre(_idtrt);
  
  -- Majoration poids MPR/MPF
  call int_mroad2ev_calcul_majoration_poids(_idtrt);
  
  call int_mroad2ev_calcul_horspresse(_idtrt);

  call int_mroad2ev_calcul_majoration_HT(_idtrt);
  call int_mroad2ev_calcul_majoration_HJ(_idtrt);
  IF (NOT _is1M) THEN
    call int_mroad2ev_calcul_majoration_JT(_idtrt);
    call int_mroad2ev_calcul_majoration_JO(_idtrt);
    
  -- majoration des heures complementaires / supplementaires
    -- ev_majoration();

    -- Primes et bonus
    call int_mroad2ev_calcul_prime(_idtrt);
    call int_mroad2ev_calcul_bonus(_idtrt,_isStc,_date_debut,_date_fin);
    call int_mroad2ev_calcul_blocage(_idtrt,_date_debut,_date_fin); -- Prime qualité polyvalent (blocage) PQT
    call int_mroad2ev_calcul_specifique(_idtrt);
    
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
  -- 30/03/2015 On prend en compte le temps des supplément et autres produits dans la tournée
  SELECT 'HEURE JOUR',e.matricule,e.rc,g.poste,e.d,SUM(TIME_TO_SEC(addtime(t.duree,t.duree_autre)))/3600,0,0,g.libelle
  FROM pai_ev_emp_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code in ('HJS','HJD','HJF')
  WHERE (g.semaine AND t.typejour_id=1 OR g.dimanche AND t.typejour_id=2 OR g.ferie AND t.typejour_id=3)
  GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle 
  HAVING SUM(TIME_TO_SEC(addtime(t.duree,t.duree_autre)))<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_heure_tournee','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_heure_tournee_nuit;
CREATE PROCEDURE int_mroad2ev_calcul_heure_tournee_nuit(
    IN 		_idtrt		INT
) BEGIN
-- ATTENTION,
-- ici on proratise seulement sur les titres de presse principaux
-- On fait reperage x2, on ne tiend pas compte de Mediapresse car pas d'heures de nuit !!!
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'HEURE NUIT',e.matricule,e.rc,prpg.poste,e.d
  ,SUM((d.nbcli+2*d.nbrep)*TIME_TO_SEC(t.duree_nuit)/(t.nbcli+2*t.nbrep))/3600
  ,prr.valeur*prpg.majoration/100
  ,SUM((d.nbcli+2*d.nbrep)*TIME_TO_SEC(t.duree_nuit)/(t.nbcli+2*t.nbrep))/3600*prr.valeur*prpg.majoration/100
  ,prpg.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  inner join produit p on d.produit_id=p.id
  INNER JOIN pai_ref_postepaie_general prpg ON prpg.code in ('HSR','HSF','HDR','HDF')
  LEFT OUTER JOIN pai_ref_remuneration prr on e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND e.d between prr.date_debut and prr.date_fin
  WHERE (prpg.semaine AND t.typejour_id=1 OR prpg.dimanche AND t.typejour_id=2 OR prpg.ferie AND t.typejour_id=3)
  AND d.typeurssaf_id=prpg.typeurssaf_id
  -- On envoie les heures de nuit que si elles sont majorées
  AND t.majoration_nuit>0
  and p.type_id=1 -- Seulement pour les titres de presse principaux
--  AND e.typetournee_id=1
  GROUP BY e.matricule, e.rc, prpg.poste, e.d, prpg.libelle, prr.valeur*prpg.majoration/100
  HAVING SUM((d.nbcli+2*d.nbrep)*TIME_TO_SEC(t.duree_nuit)/(t.nbcli+2*t.nbrep))<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_heure_tournee_nuit','');
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
  select ek.id,ek.typev,ek.matricule,ek.datev,ek.qte,sum(ek.qte)
  from pai_ev ek
  where ek.typev='HEURE NUIT'
  AND ((ek.datev LIKE '%-05-01' AND _is1M) OR (ek.datev NOT LIKE '%-05-01' AND NOT _is1M))
  group by matricule,datev
  having ek.qte=max(ek.qte) and ek.id=min(ek.id)
  ;
  UPDATE pai_ev ek
  INNER JOIN pai_ev_correction ec on ek.id=ec.id
  SET ek.qte=ek.qte
          +(SELECT sum(TIME_TO_SEC(t.duree_nuit))/3600
            FROM pai_ev_emp_pop_depot e
            INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
            WHERE e.matricule=ek.matricule AND e.d=ek.datev
            GROUP BY e.matricule,e.d
            )
          -ec.sumqte
  WHERE ek.typev='HEURE NUIT'  -- Si on est le 1er mai, on corrige que les ev en 0501, sinon toutes les autres
  AND ((ek.datev LIKE '%-05-01' AND _is1M) OR (ek.datev NOT LIKE '%-05-01' AND NOT _is1M))
  ;
  UPDATE pai_ev ek
  INNER JOIN pai_ev_correction ec on ek.id=ec.id
  SET ek.val=ek.qte*ek.taux
  WHERE ek.typev='HEURE NUIT'  -- Si on est le 1er mai, on corrige que les ev en 0501, sinon toutes les autres
  AND ((ek.datev LIKE '%-05-01' AND _is1M) OR (ek.datev NOT LIKE '%-05-01' AND NOT _is1M))
  ;
  call int_logrowcount_C(_idtrt,5,'ev_corrige_heure_tournee_nuit','');
END;

 -- -----------------------------------------------------------------------------------------------------------------------------------------------
/*
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_heure;
  -- Heures BR/BF pour affichage bulletin Néo/Média
  -- heures travaillées + la transposition des suppléments en temps + repérage
  -- ATTENTION, les durees sont les mêmes pour HRBF et HRBD
  -- Pour le calcul des durées, on prend la valeur de rémunération d'origine et non la valeur de rémunération majorée
CREATE PROCEDURE int_mroad2ev_calcul_heure(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  select typev,matricule,rc,poste,datev,sum(qte),taux,val,libelle
  from (SELECT 'HEURE NEO' as typev,e.matricule,e.rc,g.poste,e.d as datev,
  if(d.typeproduit_id=1,
  	CASE
  	  WHEN d.nbcli = 0 THEN 0
  	  WHEN e.typetournee_id in (1) THEN t.valrem*(1+rp.majoration/100)*(d.nbcli+2*d.nbrep)/r.valeur
  	  WHEN e.typetournee_id in (2) THEN t.valrem*(1+rp.majoration/100)*(d.nbcli+d.nbrep)/r.valeur
  	  WHEN e.typetournee_id = 3 THEN 0
      ELSE t.valrem*d.nbcli/r.valeur
	  END
  ,0)+time_to_sec(d.duree_supplement)/3600 as qte
  ,0 as taux,0 as val,g.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN pai_ref_postepaie_general g ON g.code in ('HNR','HNF')
  LEFT OUTER JOIN pai_ref_remuneration r ON coalesce(e.societe_id,1)=r.societe_id AND coalesce(e.population_id,1)=r.population_id AND t.date_distrib BETWEEN r.date_debut AND r.date_fin
  LEFT OUTER JOIN ref_population rp ON e.population_id=rp.id
  WHERE g.typeurssaf_id=d.typeurssaf_id
  union all
  SELECT 'HEURE NEO',e.matricule,e.rc,g.poste,e.d,time_to_sec(a.duree)/3600,0,0,g.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_activite a ON e.employe_id=a.employe_id AND a.date_distrib BETWEEN e.d AND e.f
  INNER join ref_activite ra on a.activite_id=ra.id and ra.est_JTPX=1
  INNER JOIN pai_ref_postepaie_general g ON g.code in ('HNR')
  LEFT OUTER JOIN pai_ref_remuneration r ON coalesce(e.societe_id,1)=r.societe_id AND coalesce(e.population_id,1)=r.population_id AND a.date_distrib BETWEEN r.date_debut AND r.date_fin
  where a.activite_id not in (-10,-1)
  ) as res
  group by typev,matricule,rc,poste,datev,taux,libelle
  HAVING sum(qte)<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_heure','Médiapresse');
END;
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- ACTIVITE
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
  INNER JOIN pai_ev_activite h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN ref_activite ra on h.activite_id=ra.id and not ra.est_hors_presse -- On ne prend pas les activités hors-presse gérées avec les heures garanties
  INNER JOIN pai_ref_postepaie_activite p ON h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id
  INNER JOIN pai_ref_postepaie_general prpg on p.poste_hj=prpg.poste
  LEFT OUTER JOIN pai_ref_remuneration prr on e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND e.d between prr.date_debut and prr.date_fin
  WHERE p.poste_hj<>'----'
  GROUP BY e.matricule, e.rc, p.poste_hj, e.d, prr.valeur*prpg.majoration/100,prpg.libelle
  HAVING SUM(TIME_TO_SEC(h.duree))<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_heure_activite','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- JL 24/06/2015
 -- On envoie le max entre les heures a réaliser et les heures réalisées
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_heure_activite_horspresse;
CREATE PROCEDURE int_mroad2ev_calcul_heure_activite_horspresse(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'ACTIVITE JOUR',e.matricule,e.rc,if(coalesce(xrc.travhorspresse,'0')='1',if(p.poste_hj='HDIC','HDI2','HDI3'),p.poste_hj),greatest(e.d,prr.date_debut)
  ,SUM(TIME_TO_SEC(greatest(h.duree,h.duree_garantie)))/3600
  ,if(coalesce(xrc.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,SUM(TIME_TO_SEC(greatest(h.duree,h.duree_garantie)))/3600*if(coalesce(xrc.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100
  ,prpg.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_activite h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN ref_activite ra on h.activite_id=ra.id and ra.est_hors_presse -- On ne prend pas les activités hors-presse gérées avec les heures garanties
  INNER JOIN pai_ref_postepaie_activite p ON h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id
  INNER JOIN pai_ref_postepaie_general prpg on p.poste_hj=prpg.poste
  left outer join emp_pop_depot epd on e.employe_id=epd.employe_id and h.date_distrib between epd.date_debut and epd.date_fin
  left outer join pai_png_xrcautreactivit xrc on epd.rcoid=xrc.relationcontrat and h.activite_id=xrc.activite_id and h.date_distrib between xrc.begin_date and xrc.end_date and h.depot_id=xrc.depot_id and h.flux_id=xrc.flux_id
  LEFT OUTER JOIN pai_ref_remuneration prr on e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND h.date_distrib between prr.date_debut and prr.date_fin
  WHERE p.poste_hj<>'----'
  AND TIME_TO_SEC(h.duree)<>0
  GROUP BY e.matricule, e.rc, if(coalesce(xrc.travhorspresse,'0')='1',if(p.poste_hj='HDIC','HDI2','HDI3'),p.poste_hj), greatest(e.d,prr.date_debut), if(coalesce(xrc.travhorspresse,'0')='1',prr.valeurHP2,prr.valeurHP)*prpg.majoration/100,prpg.libelle
  HAVING SUM(TIME_TO_SEC(greatest(h.duree,h.duree_garantie)))<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_heure_activite','');
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
  INNER JOIN pai_ev_activite h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN ref_activite ra on h.activite_id=ra.id
  INNER JOIN pai_ref_postepaie_activite p ON h.activite_id=p.activite_id AND h.typejour_id= p.typejour_id
  INNER JOIN pai_ref_postepaie_general prpg on p.poste_hn=prpg.poste
  LEFT OUTER JOIN pai_ref_remuneration prr on e.societe_id=prr.societe_id AND e.population_id=prr.population_id AND e.d between prr.date_debut and prr.date_fin
  WHERE p.poste_hn<>'----'
  GROUP BY e.matricule, e.rc, p.poste_hn, e.d, if(ra.est_hors_presse,prr.valeurHP,prr.valeur)*prpg.majoration/100
  HAVING SUM(TIME_TO_SEC(h.duree_nuit))<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_heure_activite_nuit','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- REMUNERATION
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_remuneration;
CREATE PROCEDURE int_mroad2ev_calcul_remuneration(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_int_log(idtrt,module,msg) 
  SELECT _idtrt,CONCAT('ev_calcul_remuneration ',g.poste),CONCAT('Tournée ',t.code,' Matricule ',t.employe_id,' Date ',t.date_distrib,' Valrem ',format(t.valrem,'0.00000'),' Valrem2 ',format(t.valrem_corrigee,'0.00000'),' Rejetée')
  FROM pai_ev_tournee t
  INNER JOIN pai_ev_produit d on d.tournee_id=t.id
  INNER JOIN pai_ref_postepaie_general g ON g.code in ('RSR','RSF','RDR','RDF','RFR','RFF')
  WHERE d.typeurssaf_id=g.typeurssaf_id
  AND (g.semaine AND t.typejour_id=1 OR g.dimanche AND t.typejour_id=2 OR g.ferie AND t.typejour_id=3)
  AND t.valrem<>0 and t.valrem_corrigee<=0;

  set @poste = ''  COLLATE 'utf8_unicode_ci';
  set @matricule =''  COLLATE 'utf8_unicode_ci';
  set @datev = now();
  set @num  = 1;

  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre)
  select typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'REMUN' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@matricule=matricule and @poste = poste and @datev=datev, @num + 1, 1) as ordre,
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
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code in ('RSR','RSF','RDR','RDF','RFR','RFF')
        LEFT OUTER JOIN pai_ref_remuneration s ON e.societe_id=s.societe_id AND e.population_id=s.population_id AND t.date_distrib BETWEEN s.date_debut AND s.date_fin
      -- ATTENTION : Sortir les valeurs de rémunération inexistantes
        WHERE g.typeurssaf_id=d.typeurssaf_id
        AND (g.semaine AND t.typejour_id=1 OR g.dimanche AND t.typejour_id=2 OR g.ferie AND t.typejour_id=3)
        AND t.valrem_corrigee>0
        AND d.typeproduit_id=1
        GROUP BY e.matricule, e.rc,g.poste, greatest(e.d,s.date_debut),ROUND(t.valrem_corrigee,3),g.libelle -- to_char(t.valrem2*1000,'FM0000000000')
        HAVING ROUND(SUM((d.nbcli+d.nbrep)*t.valrem_corrigee),2)<>0
 --       ORDER BY 1,2,3,4,6 desc) as res
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_remuneration','');
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
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN pai_ref_postepaie_general g ON g.code in ('NCR','NCF')
  WHERE d.typeurssaf_id=g.typeurssaf_id
  AND d.typeproduit_id=1
  GROUP BY e.matricule,e.rc,g.poste,e.d,g.libelle,e.typetournee_id
  HAVING CASE WHEN e.typetournee_id=1 THEN SUM(d.nbcli+2*d.nbrep) ELSE SUM(d.nbcli+d.nbrep) END<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_nb_client','');
END;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 /*
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_nb_exemplaire;
CREATE PROCEDURE int_mroad2ev_calcul_nb_exemplaire(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'NB EXEMP',e.matricule,e.rc,g.poste,e.d,SUM(d.qte),IF(t.typejour_id=3,0.02,0.01),SUM(d.qte)*IF(t.typejour_id=3,0.02,0.01),g.libelle
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND e.depot_id=t.depot_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN pai_ref_postepaie_general g ON g.code in ('NER','NEF')
  WHERE d.typeurssaf_id=g.typeurssaf_id
  AND d.typeproduit_id=1
  AND e.typetournee_id=2
  GROUP BY e.matricule,e.rc,g.poste,e.d,g.libelle
  HAVING SUM(d.qte)<>0;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_nb_exemplaire','Médiapresse');
END;
*/
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
  select typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'SUPPLEMENT' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@poste = poste and @matricule=matricule and @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,e.d as datev,
          SUM(d.pai_qte) as qte,
          d.pai_taux as taux,
          SUM(d.pai_qte)*d.pai_taux as val,
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code in ('NSR','NSF')
        WHERE d.typeproduit_id IN (2,3)
        AND d.typeurssaf_id=g.typeurssaf_id
      --  AND e.typetournee_id=2
        GROUP BY e.matricule, e.rc, g.poste, e.d, d.pai_taux, g.libelle
        HAVING SUM(d.pai_qte)<>0
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_nb_supplement','');
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
  select typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'REPERAGE' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@poste = poste and @matricule=matricule and @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,e.d as datev,
          SUM(d.nbrep) as qte,
          ROUND(t.valrem_corrigee,3) as taux,
          SUM(d.nbrep)*t.valrem_corrigee as val,
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code in ('NRR','NRF')
        AND d.typeurssaf_id=g.typeurssaf_id
        WHERE e.typetournee_id=2 -- que pour Médiapresse
        GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle,ROUND(t.valrem_corrigee*2,3)
        HAVING SUM(d.nbrep)<>0
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_nb_reperage','Médiapresse');
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
  INNER JOIN ref_emp_societe res on e.societe_id=res.id
  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code in ('KMS','KMD')
  INNER JOIN pai_png_societe pps on res.code=pps.societecode
  INNER JOIN pai_png_ta_primesw pptpf  ON pptpf.code='KMPFIX'
  INNER JOIN pai_png_primesdvrssocw pppf ON pppf.typeprime=pptpf.oid AND pppf.societe=pps.oid AND e.d between pppf.begin_date and pppf.end_date
  INNER JOIN pai_png_ta_primesw pptpv ON pptpv.code='KMPVAR'
  INNER JOIN pai_png_primesdvrssocw pppv ON pppv.typeprime=pptpv.oid AND pppv.societe=pps.oid AND e.d between pppv.begin_date and pppv.end_date
  WHERE (g.semaine AND h.typejour_id=1 OR g.dimanche AND h.typejour_id=2 OR g.ferie AND h.typejour_id=3)
  AND e.typetournee_id=1
  GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle,pppf.taux+pppv.taux
  HAVING SUM(h.nbkm_paye)<>0;  call int_logrowcount_C(_idtrt,5,'ev_calcul_kilometre','Proximy');
  
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'KILOMETRE',e.matricule,e.rc,g.poste,e.d,
  SUM(h.nbkm_paye),
  pppf.taux,
  SUM(h.nbkm_paye)*pppf.taux,
  g.libelle
--  FROM pai_ev_emp_depot e
  FROM pai_ev_emp_pop_depot e
  INNER JOIN ref_emp_societe res on e.societe_id=res.id
  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code in ('KMS','KMD')
  INNER JOIN pai_png_societe pps on res.code=pps.societecode
  INNER JOIN pai_png_ta_primesw pptpf  ON pptpf.code='KM4REX'
  INNER JOIN pai_png_primesdvrssocw pppf ON pppf.typeprime=pptpf.oid AND pppf.societe=pps.oid AND e.d between pppf.begin_date and pppf.end_date
  WHERE (g.semaine AND h.typejour_id=1 OR g.dimanche AND h.typejour_id=2 OR g.ferie AND h.typejour_id=3)
  AND (e.typetournee_id=2 and h.transport_id<>2)
  GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle, pppf.taux
  HAVING SUM(h.nbkm_paye)<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_kilometre','4 roues Mediapresse');
  
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'KILOMETRE',e.matricule,e.rc,g.poste,e.d,
  SUM(h.nbkm_paye),
  pppf.taux,
  SUM(h.nbkm_paye)*pppf.taux,
  g.libelle
--  FROM pai_ev_emp_depot e
  FROM pai_ev_emp_pop_depot e
  INNER JOIN ref_emp_societe res on e.societe_id=res.id
  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code in ('KM2')
  INNER JOIN pai_png_societe pps on res.code=pps.societecode
  INNER JOIN pai_png_ta_primesw pptpf  ON pptpf.code='KM2REX'
  INNER JOIN pai_png_primesdvrssocw pppf ON pppf.typeprime=pptpf.oid AND pppf.societe=pps.oid AND e.d between pppf.begin_date and pppf.end_date
  WHERE (g.semaine AND h.typejour_id=1 OR g.dimanche AND h.typejour_id=2 OR g.ferie AND h.typejour_id=3)
  AND (e.typetournee_id=2 and h.transport_id=2)
  GROUP BY e.matricule, e.rc, g.poste, e.d, g.libelle, pppf.taux
  HAVING SUM(h.nbkm_paye)<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_kilometre','2 roues Mediapresse');
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
  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code='HTP'
  WHERE coalesce(h.activite_id,0)<>-1 -- on ne prend pas les heures de retard
  GROUP BY e.matricule, e.rc, g.code, e.d, g.libelle 
  HAVING SUM(TIME_TO_SEC(h.duree))<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_HT','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_HJ;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_HJ(
    IN 		_idtrt		INT
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'NB HEURE',e.matricule,e.rc,g.poste,e.d,SUM(TIME_TO_SEC(h.duree)-TIME_TO_SEC(h.duree_nuit))/3600,0,0,g.libelle
  FROM pai_ev_emp_depot e
  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ref_postepaie_general g ON g.code='HJP'
  WHERE coalesce(h.activite_id,0)<>-1 -- on ne prend pas les heures de retard
  GROUP BY e.matricule, e.rc, g.code, e.d, g.libelle 
  HAVING SUM(TIME_TO_SEC(h.duree)-TIME_TO_SEC(h.duree_nuit))<>0;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_HJ','');
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
    INNER JOIN ref_population rp on e.population_id=rp.id
	  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
	  INNER JOIN pai_ref_postepaie_general g ON g.code='JTP'
	  WHERE (e.typetournee_id=1 AND h.typejour_id=1 		-- SDVP et semaine
	  OR 	 e.typetournee_id=2) 	-- Neo/Media et semaine+ferie
	  AND rp.code in ('EMDDPP','EMIDPP','EMIDTB','EMIDTE','EMDDTB','EMDDTE') -- pour les polyvalent on prend également les activités en compte
    AND coalesce(h.activite_id,0) not in (-1,-10) -- on ne prend pas les activtés bidons "complément heures garanties"
	  GROUP BY e.matricule,e.rc,g.code,e.d,g.libelle
  UNION ALL
	  SELECT 'NB JOUR',e.matricule,e.rc,g.poste,e.d,COUNT(DISTINCT h.date_distrib),0,0,g.libelle
	  FROM pai_ev_emp_pop_depot e
    INNER JOIN ref_population rp on e.population_id=rp.id
	  INNER JOIN pai_ev_heure h ON e.employe_id=h.employe_id /*AND e.depot_id=h.depot_id*/ AND h.date_distrib BETWEEN e.d AND e.f
    -- INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
    LEFT OUTER JOIN ref_activite ra on h.activite_id=ra.id
	  INNER JOIN pai_ref_postepaie_general g ON g.code='JTP'
	  WHERE (e.typetournee_id=1 AND h.typejour_id=1 		    -- SDVP et semaine
	  OR 	 e.typetournee_id=2) 	-- Media et semaine+ferie
	  AND rp.code in ('EMIDPO','EMDDPO','EMIDRB','EMIDRE','EMDDRB','EMDDRE')
    and coalesce(ra.est_JTPX,true) -- On ne prend que les tournées ou les activités qui on le flag JTPX
	  GROUP BY e.matricule, e.rc, g.code, e.d, g.libelle;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_JT','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_majoration_JO;
CREATE PROCEDURE int_mroad2ev_calcul_majoration_JO(
    IN 		_idtrt		INT
-- majoration jours ouvrables periode
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'NB JOUR',e.matricule,e.rc,g.poste,e.d,COUNT(DISTINCT c.datecal),0,0,g.libelle
  FROM pai_ev_emp_pop e
  INNER JOIN pai_ref_postepaie_general g ON g.code='JOP'
  INNER JOIN pai_ref_mois prm on e.d between prm.date_debut and prm.date_fin
  INNER JOIN pai_ref_calendrier c ON c.datecal BETWEEN prm.date_debut and prm.date_fin
--  LEFT OUTER JOIN pai_ref_ferie f ON c.datecal=f.jfdate
  WHERE c.jour_id<>1 -- pas le dimanche
   -- Si neo/media, on prend également les feriés
--  AND (e.societe_id=2 OR f.jfdate IS NULL)
  AND (e.societe_id=2 OR not exists(select null from pai_ref_ferie f where c.datecal=f.jfdate))
   -- Au moins une activité sur la rc
--  AND EXISTS(SELECT NULL FROM pai_ev_heure h WHERE e.employe_id=h.employe_id and h.date_distrib BETWEEN e.d AND e.f AND coalesce(h.activite_id,0) not in (-1,-10))
  AND ( EXISTS(SELECT NULL FROM pai_ev_tournee h WHERE e.employe_id=h.employe_id and h.date_distrib BETWEEN e.d AND e.f)
  OR exists(SELECT NULL FROM pai_ev_activite h WHERE e.employe_id=h.employe_id and h.date_distrib BETWEEN e.d AND e.f AND h.activite_id not in (-1,-10)))
  GROUP BY e.matricule, e.rc, g.code, e.d,g.libelle;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_JO','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_horspresse;
CREATE PROCEDURE int_mroad2ev_calcul_horspresse(
    IN 		_idtrt		INT
)BEGIN
-- ATTENTION : faire une rupture également sur changement de date de valorisation du supplément
  set @poste = ''  COLLATE 'utf8_unicode_ci';
  set @matricule = ''  COLLATE 'utf8_unicode_ci';
  set @datev = now();
  set @num  = 1;

  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre)
  select typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'HORSPRESSE' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@poste = poste and @matricule=matricule and @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,e.d as datev,
          SUM(d.pai_qte) as qte,
          d.pai_taux as taux,
          SUM(d.pai_qte)*d.pai_taux as val,
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code in ('PHP')
        WHERE d.typeproduit_id NOT IN (1,2,3)
      --  AND e.typetournee_id=2
        GROUP BY e.matricule, e.rc, g.poste, e.d, d.pai_taux, g.libelle
        HAVING SUM(d.pai_qte)<>0 and d.pai_taux<>0
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_horspresse','');
END;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- PRIME ET BONUS
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_prime;
CREATE PROCEDURE int_mroad2ev_calcul_prime(
    IN 		_idtrt		INT
) BEGIN
-- ATTENTION : pour Neo/Media, les reclamations ferié compte double !!! (et il faut les prendre)
    delete from pai_ev_qualite where idtrt=_idtrt;
    delete from pai_ev where typev='PRIME';
    delete from pai_ev_hst where idtrt=_idtrt and typev='PRIME';
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_emp_pop e SET nbabo=(SELECT coalesce(SUM(p.nbcli),0) 
                                      FROM pai_ev_tournee t 
                                      INNER JOIN pai_ev_produit p ON p.tournee_id=t.id 
                                      WHERE t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.d AND e.f 
                                      AND (e.societe_id=1 AND t.typejour_id=1/*'S'*/ AND p.natureclient_id=0/*A*/
                                      OR e.societe_id=2)
                                      AND p.typeproduit_id=1 -- seulement les journaux
                                      GROUP BY t.employe_id);
    call int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Colonne tnbrabo sur Table pai_ev_emp_pop');
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
      INNER JOIN pai_tournee t on r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND (e.societe_id=1 AND t.typejour_id=1
      OR e.societe_id=2)
      GROUP BY t.employe_id)
    ;
    UPDATE pai_ev_emp_pop e SET taux= CASE WHEN e.nbabo<>0 THEN e.nbrec*1000/coalesce(e.nbabo,0) ELSE 0 END;
    UPDATE pai_ev_emp_pop SET taux=0 WHERE taux IS NULL or  taux<0;
      
    CALL int_logger(_idtrt,'ev_calcul_prime','  Colonne Qualite sur Table pai_ev_emp_pop');
    UPDATE pai_ev_emp_pop e SET qualite='O';
      
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
      -- MEDIAPRESSE Pas de prime pour les collecteurs/distributeurs de courrier/porteur de colis qui n'ont pas de contrat porteur
      UPDATE pai_ev_emp_pop e SET qualite='P'
      WHERE NOT EXISTS(SELECT NULL 
                      FROM emp_pop_depot epd
                      inner join ref_emploi re on epd.emploi_id=re.id
                      WHERE e.employe_id=epd.employe_id 
                      AND e.d<=epd.date_fin and e.f>=date_debut
                      AND re.prime
                      )
      AND e.qualite='O' AND e.societe_id=2
      ;
      call int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Porteur sans prime');
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
     -- Si pas de tournée (semaine pour proximy), on met un code particulier
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
                      inner join ref_activite ra on a.activite_id=ra.id and ra.est_JTPX
                      WHERE e.employe_id=a.employe_id
                      AND a.date_distrib BETWEEN e.d AND e.f 
                      AND a.typejour_id=1
                      AND a.activite_id not in (-1,-10)
                      )
      AND e.qualite='O' AND e.societe_id=1 and e.emploi_code='POR'-- Mediapresse supprimé le 20/02/2015
      ;
      call int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Porteur sans tournée semaine Proximy');
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
                      AND t.activite_id not in (-1,-10)
                      )
      AND e.qualite='O' 
      AND (e.societe_id=1 and e.emploi_code='POL'
      OR e.societe_id=2) -- Mediapresse supprimé le 20/02/2015
      ;
      call int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Polyvalent proximy ou Individus Mediapresse sans tournée/activité');
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
      -- si incident<>0 ==> pas de prime
      UPDATE pai_ev_emp_pop e SET qualite='I'
      WHERE EXISTS(SELECT NULL 
		              FROM pai_incident pi
		              INNER JOIN pai_ref_calendrier prc on pi.date_distrib=prc.datecal
                  WHERE pi.employe_id=e.employe_id 
                  AND pi.date_distrib BETWEEN e.d AND e.f -- and pi.date_extrait is null 
                  and (prc.typejour_id=1/*'S'*/)
                  )
      AND e.qualite='O' AND e.societe_id=1
      ;
      call int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Individus avec incident Proximy');
      UPDATE pai_ev_emp_pop e SET qualite='J'
      WHERE EXISTS(SELECT NULL 
		              FROM pai_incident pi
                  WHERE pi.employe_id=e.employe_id 
                  AND pi.date_distrib BETWEEN e.d AND e.f -- and pi.date_extrait is null 
                  )
      AND e.qualite='O' AND e.societe_id=2
      ;
      call int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Individus avec incident Mediapresse');
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
      AND e.qualite='O' and e.societe_id=1
      ;
      call int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Individus avec réclamation diffuseur');
     -- ------------------------------------------------------------------------------------------------------------------------------------------------
     -- qualite=1 au lieu de 2 si seulement abonné ou seulement diffuseur (mono client)
      UPDATE pai_ev_emp_pop e SET qualite='U'
      WHERE qualite='O' and e.societe_id=1
      AND (
          NOT EXISTS(SELECT NULL FROM pai_ev_tournee t INNER JOIN pai_ev_produit p ON t.id=p.tournee_id WHERE t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.d AND e.f AND t.typejour_id=1/*'S'*/ AND p.natureclient_id=0/*'A'*/)
      OR  NOT EXISTS(SELECT NULL FROM pai_ev_tournee t INNER JOIN pai_ev_produit p ON t.id=p.tournee_id WHERE t.employe_id=e.employe_id AND t.date_distrib BETWEEN e.d AND e.f AND t.typejour_id=1/*'S'*/ AND p.natureclient_id=1/*'D'*/)
      )
      ;
      call int_logrowcount_C(_idtrt,5,'ev_calcul_prime','Individus mono client');
    -- -----------------------------------------------------------------------------------------------------------------------------------------------
    -- ATTENTION A priori, la table pai_ev_qualite ne sert à rien le taux est stocké dans pai_ev_emp_pop
    -- De plus le calcul est faux pour MediaPresse (manque diffuseur)
    CALL int_logger(_idtrt,'ev_calcul_prime','  Table ev_qualite');
    INSERT INTO pai_ev_qualite(idtrt,employe_id,datev,qualite)
    SELECT _idtrt,e.employe_id,e.d,
    CASE WHEN e.nbabo<>0 THEN 
    -- ATTENTION : POUR MEDIAPRESSE, il faudrait prendre le nombre de réclamations abonné+diffuseur
      (SELECT SUM(coalesce(r.nbrec_abonne,0))*1000/coalesce(e.nbabo,0) 
      FROM pai_ev_reclamation r
      INNER JOIN pai_tournee t on r.tournee_id=t.id
      WHERE e.employe_id=t.employe_id
      AND t.date_distrib between e.dCtr AND e.f -- réclamation prise en compte plusieurs fois en cas de changement en cours de mois !!!!
--      AND t.date_distrib between e.d AND e.f 
      AND (e.societe_id=1 AND t.typejour_id=1/*='S' ATTENTION, faut-il prendre les jours ferie */ 
      OR e.societe_id=2)
      GROUP BY t.employe_id)
    ELSE 0
    END
    FROM pai_ev_emp_pop e
    ;
    -- ------------------------------------------------------------------------------------------------------------------------------------------------
    UPDATE pai_ev_qualite SET qualite=0 WHERE qualite IS NULL and idtrt=_idtrt;

    INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
    SELECT DISTINCT 'PRIME',e.matricule,e.rc,p.poste,e.d,rq.valeur,0,0,p.libelle
    FROM pai_ev_emp_pop e
    INNER JOIN pai_ref_postepaie_general p ON p.code='QLT'
    INNER JOIN pai_ref_qualite rq on e.qualite=rq.qualite and e.societe_id=rq.societe_id and e.emploi_code=rq.emploi_code
    WHERE e.taux between rq.borne_inf AND rq.borne_sup
    AND rq.envoiNG
    ;
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
    SELECT DISTINCT 'BONUS',e.matricule,e.rc,p.poste,_date_debut,COUNT(DISTINCT t.date_distrib)+coalesce(pb.nbtrnmois1,0)+coalesce(pb.nbtrnmois2,0)+coalesce(pb.nbtrnmois3,0),0,datediff(_date_fin,date_add(date_add(_date_debut,interval -2 month),interval 1 day)),p.libelle
    FROM pai_ev_emp_depot e
    LEFT OUTER JOIN pai_tournee t ON e.employe_id=t.employe_id AND e.depot_id=t.depot_id AND t.date_distrib BETWEEN date_add(_date_debut,INTERVAL -2 MONTH) AND _date_fin
    LEFT OUTER JOIN pai_reclamation r ON t.id=r.tournee_id
    LEFT OUTER JOIN pai_incident i ON e.employe_id=i.employe_id AND i.date_distrib BETWEEN date_add(_date_debut,INTERVAL -2 MONTH) AND _date_fin
    INNER JOIN pai_ref_postepaie_general p ON p.code='SBQ'
    -- rustine Pepp --
    inner join depot d on e.depot_id=d.id
    left outer join pai_pepp_bonus pb on e.matricule=pb.matricule and substr(d.code,2,2)=pb.eta
    -- FIN rustine Pepp --
    -- Le contrat est superieur à 3 mois
    WHERE e.dRc<=date_add(_date_debut,INTERVAL -2 MONTH)
    -- L'individu est la en fin de periode de paie
    AND e.fRc>=_date_fin
    -- On regarde toutes les réclamations sur les 3 derniers mois
    -- ATTENTION : Normalement, il faudrait regarder sur anneemois de pai_reclamation !!!
    and (pb.matricule is null or (pb.nbrecab=0 and pb.nbrecdif=0 and pb.nbinc=0))
    AND e.flux_id=1 -- que pour Proximy
    GROUP BY e.employe_id, e.rc, p.poste, _date_debut, datediff(_date_fin,date_add(date_add(_date_debut,interval -2 month),interval 1 day)),p.libelle
    HAVING  SUM(coalesce(r.nbrec_abonne,0))<=0
    AND     SUM(coalesce(r.nbrec_diffuseur,0))<=0
    AND     SUM(coalesce(i.id,0))<=0
    -- On génère seulement s'il y a au moins une tournée semaine
    AND  (  (EXISTS(SELECT NULL FROM pai_tournee t2 WHERE e.employe_id=t2.employe_id AND t2.typejour_id=1 AND t2.date_distrib BETWEEN date_add(_date_debut,INTERVAL -2 MONTH) AND date_add(_date_fin,INTERVAL -2 MONTH))
    or  EXISTS(SELECT NULL FROM pai_activite a2 inner join ref_activite ra on a2.activite_id=ra.id and ra.est_JTPX WHERE e.employe_id=a2.employe_id AND a2.typejour_id=1 AND a2.date_distrib BETWEEN date_add(_date_debut,INTERVAL -2 MONTH) AND date_add(_date_fin,INTERVAL -2 MONTH))
    -- or  exists(select null from pai_pepp_bonus pb where  pb.trimestre=date_format(_date_fin,'%Y%m') and e.matricule=pb.matricule and nbtrnmois1>0)
    )
    AND    (EXISTS(SELECT NULL FROM pai_tournee t2 WHERE e.employe_id=t2.employe_id AND t2.typejour_id=1 AND t2.date_distrib BETWEEN date_add(_date_debut,INTERVAL -1 MONTH) AND date_add(_date_fin,INTERVAL -1 MONTH))
    or  EXISTS(SELECT NULL FROM pai_activite a2 inner join ref_activite ra on a2.activite_id=ra.id and ra.est_JTPX WHERE e.employe_id=a2.employe_id AND a2.typejour_id=1 AND a2.date_distrib BETWEEN date_add(_date_debut,INTERVAL -1 MONTH) AND date_add(_date_fin,INTERVAL -1 MONTH))
    -- or  exists(select null from pai_pepp_bonus pb where  pb.trimestre=date_format(_date_fin,'%Y%m') and e.matricule=pb.matricule and nbtrnmois2>0)
    )
    AND    (EXISTS(SELECT NULL FROM pai_tournee t2 WHERE e.employe_id=t2.employe_id AND t2.typejour_id=1 AND t2.date_distrib BETWEEN _date_debut AND _date_fin)
    or  EXISTS(SELECT NULL FROM pai_activite a2 inner join ref_activite ra on a2.activite_id=ra.id and ra.est_JTPX WHERE e.employe_id=a2.employe_id AND a2.typejour_id=1 AND a2.date_distrib BETWEEN _date_debut AND _date_fin)
    -- or  exists(select null from pai_pepp_bonus pb where  pb.trimestre=date_format(_date_fin,'%Y%m') and e.matricule=pb.matricule and nbtrnmois3>0)
    )
    )
    ;
    call int_logrowcount_C(_idtrt,5,'ev_calcul_bonus','');
  END IF;
END;
/*
    select '201503' as trimestre,e.matricule, e.nom,e.prenom1,e.prenom2,d.code,epd.dRC,epd.fRC
    ,count(distinct if(substr(date_format(t.date_distrib,'%Y%m'),1,6)='201501',t.date_distrib,null)) as nbtrnmois1
    ,count(distinct if(substr(date_format(t.date_distrib,'%Y%m'),1,6)='201502',t.date_distrib,null)) as nbtrnmois2
    ,count(distinct if(substr(date_format(t.date_distrib,'%Y%m'),1,6)='201503',t.date_distrib,null)) as nbtrnmois3
    ,coalesce(sum(nbrec_abonne),0) as nbrecab,coalesce(sum(nbrec_diffuseur),0) as nbrecdif,coalesce(sum(pi.id),0) as nbinc
    from emp_pop_depot epd
    inner join employe e on epd.employe_id=e.id
    inner join depot d on epd.depot_id=d.id
    left outer join pai_tournee t on epd.employe_id=t.employe_id
    left outer join pai_reclamation r on r.tournee_id=t.id
    left outer join pai_incident pi on pi.tournee_id=t.id
    where epd.fRC>='20150101' and epd.dRC<='20150331'
    and t.date_distrib between '2015-01-01' and '2015-03-31'
    and t.typejour_id=1
    and d.id=10
    group by '201503',e.matricule,d.code,epd.dRC,epd.fRC
    order by e.nom,e.prenom1,e.prenom2
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_blocage;
CREATE PROCEDURE int_mroad2ev_calcul_blocage(
    IN 		_idtrt		  INT,
    IN 		_date_debut DATE,
    IN 		_date_fin DATE
) BEGIN
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT DISTINCT 'BLOQUAGE',e.matricule,e.rc,g.poste,e.d,CASE WHEN COUNT(i.id)>0 THEN 0 ELSE 1 END,0,0,g.libelle
  FROM pai_ev_emp_pop e
--  INNER JOIN ref_population rp on e.population_id=rp.id AND rp.code in ('EMIDTB','EMIDTE','EMDDTB','EMDDTE') -- polyvalents
  LEFT OUTER JOIN pai_incident i ON e.employe_id=i.employe_id AND i.date_distrib BETWEEN e.d AND e.f -- and i.date_extrait is null
  INNER JOIN pai_ref_postepaie_general g ON g.code='PQT'
  WHERE e.emploi_code='POL'
  AND _date_fin>=e.dRc and _date_debut<=e.fRc -- ATTENTION il y a les STC du mois suivant !!!!
  AND EXISTS(SELECT null FROM pai_ev_heure ph WHERE e.employe_id=ph.employe_id AND ph.date_distrib BETWEEN e.d AND e.f AND coalesce(ph.activite_id,0) not in (-1,-10)) -- Au moins une activité sur la période
      -- MEDIAPRESSE Pas de prime pour les collecteurs/distributeurs de courrier/porteur de colis qui n'ont pas de contrat porteur
  AND EXISTS(SELECT NULL 
                      FROM emp_pop_depot epd
                      INNER JOIN ref_population rp on epd.population_id=rp.id AND rp.code in ('EMIDTB','EMIDTE','EMDDTB','EMDDTE') -- polyvalents
                      inner join ref_emploi re on epd.emploi_id=re.id
                      WHERE e.employe_id=epd.employe_id 
                      AND e.d<=epd.date_fin and e.f>=epd.date_debut
                      AND re.prime
                      )
  and e.societe_id=2
  GROUP BY e.matricule, e.rc, g.poste, e.d,g.libelle;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_blocage','');
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
  FROM pai_ev_emp_pop e
  INNER JOIN pai_ev e3 on e3.poste='0105' and e3.matricule=e.matricule and e3.datev=e.d -- on a une prime
  INNER JOIN pai_ev e1 on e1.poste='JTPX' and e1.matricule=e.matricule and e1.datev between e.d and e.f
	INNER JOIN pai_ref_postepaie_general g ON g.code='CPR'
  inner join pai_int_traitement pit on pit.id=_idtrt and pit.flux_id=1 -- seulement pour le flux nuit
  WHERE not exists(select null from pai_ev e2 where e1.matricule=e2.matricule and e1.datev=e2.datev and e2.poste in ('0350','0351'))
  ;
  call int_logrowcount_C(_idtrt,5,'int_mroad2ev_calcul_specifique','');
END;
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
  select typev,matricule,rc,poste,datev,qte,taux,val,libelle,ordre from
  (
    SELECT 'POIDS' as typev,matricule,rc,poste,datev,qte,taux,val,libelle,
      @num := if(@poste = poste and @matricule=matricule and @datev=datev, @num + 1, 1) as ordre,
      @poste := poste as _poste,
      @matricule := matricule as _matricule,
      @datev := datev as _datev
    FROM (SELECT e.matricule,e.rc,g.poste,e.d as datev,
          SUM(d.pai_qte) as qte,
          d.pai_taux as taux,
          SUM(d.pai_qte)*d.pai_taux as val,
          g.libelle
        FROM pai_ev_emp_pop_depot e
        INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id /*AND e.depot_id=t.depot_id*/ AND t.date_distrib BETWEEN e.d AND e.f
        INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
        INNER JOIN pai_ref_postepaie_general g ON g.code in ('MPR','MPF')
        WHERE d.typeproduit_id IN (1)
        AND d.typeurssaf_id=g.typeurssaf_id
        GROUP BY e.matricule, e.rc, g.poste, e.d, d.pai_taux, g.libelle
        HAVING SUM(d.pai_qte)<>0 and pai_taux is not null
        ORDER BY 1,2,3,4) as res
  ) as res
  ;
  call int_logrowcount_C(_idtrt,5,'ev_calcul_majoration_poids','Médiapresse');
END;
/*
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_calcul_nb_supplement_SDVP;
CREATE PROCEDURE int_mroad2ev_calcul_nb_supplement_SDVP(
    IN 		_idtrt		INT
)BEGIN
  INSERT INTO pai_int_log(idtrt,module,msg) 
  SELECT distinct _idtrt,'ev_calcul_supplement',CONCAT('Poste de paie ',case when d.typeurssaf_id=1 THEN 'BF' ELSE 'BDC' END,' non renseigné pour ',p.libelle,'(',p.id,')')
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND e.depot_id=t.depot_id AND t.date_distrib BETWEEN e.d AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN produit p ON d.produit_id=p.id
  LEFT OUTER JOIN pai_ref_postepaie_supplement s ON s.produit_id=d.produit_id
  WHERE d.typeproduit_id IN (2,3)
  AND d.qte<>0
  AND e.typetournee_id=1
  AND ( d.typeurssaf_id=1 and (s.poste_bf='' or s.poste_bf is null)
  OR    d.typeurssaf_id=2 and (s.poste_bdc='' or s.poste_bdc is null))
  ;
  INSERT INTO pai_ev(typev,matricule,rc,poste,datev,qte,taux,val,libelle)
  SELECT 'NB SUP SDVP',e.matricule,rc,CASE WHEN d.typeurssaf_id=1 THEN s.poste_bf ELSE s.poste_bdc END,e.d,SUM(d.qte),0,0,Concat('Supplément ',CASE WHEN d.typeurssaf_id=1 THEN s.poste_bf ELSE s.poste_bdc END)
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND e.depot_id=t.depot_id AND t.date_distrib BETWEEN e.d AND e.f
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
