-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_suivi_horaire;
CREATE PROCEDURE create_tmp_pai_suivi_horaire(IN _depot_id INT, IN _flux_id INT, IN _anneemois varchar(6))
BEGIN
  call create_tmp_pai_horaire(_depot_id,_flux_id,null,_anneemois,null);
  call create_tmp_pai_suivi_horaire_exec(_depot_id,_flux_id,_anneemois, null);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_suivi_horaire_exec;
CREATE PROCEDURE create_tmp_pai_suivi_horaire_exec(IN _depot_id INT, IN _flux_id INT, IN _anneemois varchar(6), IN _idtrt INT)
BEGIN
  DROP TEMPORARY TABLE IF EXISTS tmp_pai_suivi_horaire;
  CREATE TEMPORARY TABLE tmp_pai_suivi_horaire(
  epd_id              int
  ,employe_id         int
  ,date_debut         date
  ,date_fin           date
  ,nbheures_garanties decimal(5,2)
  ,nbjours_cycle      int
  ,nbjours_mois       int
  ,nbjours_contrat    int
  ,nbjours_travailles int
  ,horaire_moyen      float
  ,nbheures_a_realiser float
  ,nbheures_realisees float
  ,nbheures_delegation float
  ,nbheures_hors_presse float
  ,nbheures_apayer float
  ,est_hors_presse boolean
  ) engine=memory
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;

  insert into tmp_pai_suivi_horaire(epd_id,employe_id,date_debut,date_fin,nbheures_garanties,nbjours_travailles,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse)
  select
      epd.emploi_id,
      epd.employe_id,
      greatest(epd.date_debut,prm.date_debut) as date_debut,
      least(epd.date_fin,prm.date_fin) as date_fin,
      epd.nbheures_garanties,
      count(distinct if(not coalesce(ra.est_pleiades,false) and coalesce(ra.est_garantie,1),pd.date_distrib,null)) as nbjours_travailles,
      -- ra.est_garantie=null pour les tournées !!!
      coalesce(sum(time_to_sec(if(not coalesce(ra.est_pleiades,false) and coalesce(ra.est_garantie,1),pd.duree,0)))/3600,0) as nbheures_realisees,
      coalesce(sum(time_to_sec(if(not coalesce(ra.est_pleiades,false) and coalesce(ra.est_hors_travail,0),pd.duree,0)))/3600,0) as nbheures_delegation,
      coalesce(sum(time_to_sec(if(not coalesce(ra.est_pleiades,false) and coalesce(ra.est_hors_presse,0),pd.duree,0)))/3600,0) as nbheures_hors_presse,
      false
  from emp_pop_depot epd
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  left outer join tmp_pai_horaire pd on pd.employe_id=epd.employe_id and pd.date_distrib between greatest(epd.date_debut,prm.date_debut) and least(epd.date_fin,prm.date_fin)
  left outer join pai_activite pa on pd.activite_id=pa.id
  left outer join ref_activite ra on pa.activite_id=ra.id 
  where epd.nbheures_garanties is not null and epd.nbheures_garanties<>0
  and epd.date_debut<=prm.date_fin and epd.date_fin>=prm.date_debut
	AND (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND (_idtrt is null or pd.employe_id is not null) -- A l'écran on sélectionne tout le monde, pour l'interface Octime que ceux qui ont des heures
  group by epd.id,greatest(epd.date_debut,prm.date_debut),least(epd.date_fin,prm.date_fin);

  update tmp_pai_suivi_horaire epd
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  set nbjours_cycle=( select sum(CASE DAYOFWEEK(prc.datecal)
                                            WHEN 1 THEN ec.dimanche
                                            WHEN 2 THEN ec.lundi
                                            WHEN 3 THEN ec.mardi
                                            WHEN 4 THEN ec.mercredi
                                            WHEN 5 THEN ec.jeudi
                                            WHEN 6 THEN ec.vendredi
                                            WHEN 7 THEN ec.samedi
                                            END
                                            )
                        from pai_ref_calendrier prc
                        inner join emp_cycle ec on prc.datecal between ec.date_debut and ec.date_fin
                        where epd.employe_id=ec.employe_id 
                        and prc.datecal between prm.date_debut and prm.date_fin
                        and prc.datecal between epd.date_debut and epd.date_fin
                        );
  
  update tmp_pai_suivi_horaire epd
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  set nbjours_mois=( select sum(CASE DAYOFWEEK(prc.datecal)
                                            WHEN 1 THEN ec.dimanche
                                            WHEN 2 THEN ec.lundi
                                            WHEN 3 THEN ec.mardi
                                            WHEN 4 THEN ec.mercredi
                                            WHEN 5 THEN ec.jeudi
                                            WHEN 6 THEN ec.vendredi
                                            WHEN 7 THEN ec.samedi
                                            END
                                            )
                        from pai_ref_calendrier prc
                        inner join emp_cycle ec
                        where epd.employe_id=ec.employe_id 
                        and prc.datecal between prm.date_debut and prm.date_fin
                        and epd.date_debut between ec.date_debut and ec.date_fin
                        );
  
  update tmp_pai_suivi_horaire epd
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  set nbjours_contrat=( select sum(CASE DAYOFWEEK(prc.datecal)
                                            WHEN 1 THEN ec.dimanche
                                            WHEN 2 THEN ec.lundi
                                            WHEN 3 THEN ec.mardi
                                            WHEN 4 THEN ec.mercredi
                                            WHEN 5 THEN ec.jeudi
                                            WHEN 6 THEN ec.vendredi
                                            WHEN 7 THEN ec.samedi
                                            END
                                            )
                        from pai_ref_calendrier prc
                        inner join emp_cycle ec
                        where epd.employe_id=ec.employe_id 
                        and prc.datecal between prm.date_debut and prm.date_fin
                        and prc.datecal between epd.date_debut and epd.date_fin
                        and epd.date_debut between ec.date_debut and ec.date_fin
                        );
  
  update tmp_pai_suivi_horaire
  set horaire_moyen=nbheures_garanties*nbjours_contrat/nbjours_mois/nbjours_cycle;
  /*
  update tmp_pai_suivi_horaire epd
  set nbjours_mois=( select sum(CASE DAYOFWEEK(prc.datecal)
                                            WHEN 1 THEN ec.dimanche
                                            WHEN 2 THEN ec.lundi
                                            WHEN 3 THEN ec.mardi
                                            WHEN 4 THEN ec.mercredi
                                            WHEN 5 THEN ec.jeudi
                                            WHEN 6 THEN ec.vendredi
                                            WHEN 7 THEN ec.samedi
                                            END
                                            )
                        from pai_ref_calendrier prc
                        inner join pai_ref_mois prm on prc.datecal between prm.date_debut and prm.date_fin
                        inner join emp_cycle ec
                        where epd.employe_id=ec.employe_id and epd.date_debut between ec.date_debut and ec.date_fin
                        and prm.anneemois like concat(left(_anneemois,4),'%')
                        );
  update tmp_pai_suivi_horaire
  set horaire_moyen=nbheures_garanties*12/nbjours_mois;
*/
  update tmp_pai_suivi_horaire
  set nbheures_a_realiser=least(horaire_moyen*nbjours_travailles,nbheures_garanties);

  call create_tmp_pai_suivi_horaire_horspresse_exec(_depot_id, _flux_id, _anneemois, _idtrt);
  call create_tmp_pai_suivi_horaire_delegation_exec(_depot_id, _flux_id, _anneemois, _idtrt);
  
  update tmp_pai_suivi_horaire
  set nbheures_apayer=nbheures_realisees-nbheures_a_realiser;
END;


-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_suivi_horaire_horspresse_exec;
CREATE PROCEDURE create_tmp_pai_suivi_horaire_horspresse_exec(IN _depot_id INT, IN _flux_id INT, IN _anneemois varchar(6), IN _idtrt INT)
BEGIN
  insert into tmp_pai_suivi_horaire(epd_id,employe_id,date_debut,date_fin,nbheures_garanties,horaire_moyen,nbjours_travailles,nbheures_a_realiser,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse)
  select
      -ra.id,
      pa.employe_id,
      greatest(epd.date_debut,xa.begin_date,prm.date_debut) as date_debut,
      least(epd.date_fin,xa.end_date,prm.date_fin) as date_fin,
      coalesce(sum(time_to_sec(if(pa.duree<>'00:00',xa.nbheurejr,0))),0),
      xa.nbheurejr,
      count(distinct if(pa.duree<>'00:00',pa.date_distrib,null)) as nbjours_travailles,
      -- ra.est_garantie=null pour les tournées !!!
      coalesce(sum(time_to_sec(if(pa.duree<>'00:00',xa.nbheurejr,0))),0) as nbheures_a_realiser,
      coalesce(sum(time_to_sec(pa.duree))/3600,0) as nbheures_realisees,
      0 as nbheures_delegation,
      coalesce(sum(time_to_sec(pa.duree))/3600,0) as nbheures_hors_presse,
      true
    from emp_pop_depot epd
    inner join pai_ref_mois prm on prm.anneemois=_anneemois
    inner join pai_png_xrcautreactivit xa on epd.rcoid=xa.relationcontrat
    inner join pai_png_xta_rcactivhpre xra on xa.xta_rcactivhpre=xra.oid
    inner join ref_activite ra on xra.code=ra.code and ra.est_pleiades
    left outer join pai_activite pa on pa.employe_id=epd.employe_id 
      and pa.date_distrib between epd.date_debut and epd.date_fin
      and pa.date_distrib between xa.begin_date and xa.end_date
      and pa.date_distrib between prm.date_debut and prm.date_fin
      and pa.depot_id=xa.depot_id and pa.flux_id=xa.flux_id
      and pa.activite_id=ra.id
    where /*(epd.nbheures_garanties is null or epd.nbheures_garanties=0)
  and*/ (dayofweek(pa.date_distrib)=1 and trvdimanche='1'
    or dayofweek(pa.date_distrib)=2 and xa.trvlundi='1'
    or dayofweek(pa.date_distrib)=3 and xa.trvmardi='1'
    or dayofweek(pa.date_distrib)=4 and xa.trvmercredi='1'
    or dayofweek(pa.date_distrib)=5 and xa.trvjeudi='1'
    or dayofweek(pa.date_distrib)=6 and xa.trvvendredi='1'
    or dayofweek(pa.date_distrib)=7 and xa.trvsamedi='1')
    and xa.begin_date<=prm.date_fin and xa.end_date>=prm.date_debut
  	AND (pa.depot_id=_depot_id OR _depot_id IS NULL)
  	AND (pa.flux_id=_flux_id OR _flux_id IS NULL)
--    AND (_idtrt is null or pd.employe_id is not null) -- A l'écran on sélectionne tout le monde, pour l'interface Octime que ceux qui ont des heures
        group by xa.oid,pa.employe_id,ra.id/*,epd.id*/;

  -- 14/12/2015 On ajouteles individus qui n'ont que des heures hors-presse hors-pleiades(formation hors-presse)
  insert into tmp_pai_suivi_horaire(epd_id,employe_id,date_debut,date_fin,nbheures_garanties,nbjours_travailles,nbheures_a_realiser,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse)
  select
      epd.emploi_id,
      epd.employe_id,
      greatest(epd.date_debut,prm.date_debut) as date_debut,
      least(epd.date_fin,prm.date_fin) as date_fin,
      null,
      count(distinct if(pa.duree<>'00:00',pa.date_distrib,null)) as nbjours_travailles,
      0 as nbheures_a_realiser,
      0 as nbheures_realisees,
      0 as nbheures_delegation,
      coalesce(sum(time_to_sec(if(not coalesce(ra.est_pleiades,false) and coalesce(ra.est_hors_presse,0),pd.duree,0)))/3600,0) as nbheures_hors_presse,
      false
  from emp_pop_depot epd
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  inner join tmp_pai_horaire pd on pd.employe_id=epd.employe_id and pd.date_distrib between greatest(epd.date_debut,prm.date_debut) and least(epd.date_fin,prm.date_fin)
  inner join pai_activite pa on pd.activite_id=pa.id
  inner join ref_activite ra on pa.activite_id=ra.id and not ra.est_pleiades and ra.est_hors_presse
  where (epd.nbheures_garanties is null or epd.nbheures_garanties=0)
  and epd.date_debut<=prm.date_fin and epd.date_fin>=prm.date_debut
	AND (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND (_idtrt is null or pd.employe_id is not null) -- A l'écran on sélectionne tout le monde, pour l'interface Octime que ceux qui ont des heures
  group by epd.id,greatest(epd.date_debut,prm.date_debut),least(epd.date_fin,prm.date_fin);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_suivi_horaire_delegation_exec;
CREATE PROCEDURE create_tmp_pai_suivi_horaire_delegation_exec(IN _depot_id INT, IN _flux_id INT, IN _anneemois varchar(6), IN _idtrt INT)
BEGIN
  insert into tmp_pai_suivi_horaire(epd_id,employe_id,date_debut,date_fin,nbheures_garanties,nbjours_travailles,nbheures_a_realiser,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse)
  select
      epd.emploi_id,
      epd.employe_id,
      greatest(epd.date_debut,prm.date_debut) as date_debut,
      least(epd.date_fin,prm.date_fin) as date_fin,
      null,
      count(distinct if(pa.duree<>'00:00',pa.date_distrib,null)) as nbjours_travailles,
      0 as nbheures_a_realiser,
      0 as nbheures_realisees,
      coalesce(sum(time_to_sec(if(not coalesce(ra.est_pleiades,false) and coalesce(ra.est_hors_travail,0),pd.duree,0)))/3600,0) as nbheures_delegation,
      0 as nbheures_hors_presse,
      false
  from emp_pop_depot epd
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  inner join tmp_pai_horaire pd on pd.employe_id=epd.employe_id and pd.date_distrib between greatest(epd.date_debut,prm.date_debut) and least(epd.date_fin,prm.date_fin)
  inner join pai_activite pa on pd.activite_id=pa.id
  inner join ref_activite ra on pa.activite_id=ra.id and not ra.est_pleiades and ra.est_hors_travail
  where (epd.nbheures_garanties is null or epd.nbheures_garanties=0)
  and epd.date_debut<=prm.date_fin and epd.date_fin>=prm.date_debut
	AND (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  AND (_idtrt is null or pd.employe_id is not null) -- A l'écran on sélectionne tout le monde, pour l'interface Octime que ceux qui ont des heures
  group by epd.id,greatest(epd.date_debut,prm.date_debut),least(epd.date_fin,prm.date_fin);
end;

