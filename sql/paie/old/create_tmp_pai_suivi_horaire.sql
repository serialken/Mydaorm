/*
call create_tmp_pai_suivi_horaire(null,null,'201611')
call create_tmp_pai_suivi_horaire2(11,2,'201512',null);
select * from tmp_pai_suivi_horaire where nbheures_garanties_majorees is not null
*/
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_suivi_horaire;
CREATE PROCEDURE create_tmp_pai_suivi_horaire(IN _depot_id INT, IN _flux_id INT, IN _anneemois varchar(6))
BEGIN
  call create_tmp_pai_horaire(_depot_id,_flux_id,null,_anneemois,null);
  call create_tmp_pai_suivi_horaire_exec(_depot_id,_flux_id,_anneemois);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_suivi_horaire_exec;
CREATE PROCEDURE create_tmp_pai_suivi_horaire_exec(IN _depot_id INT, IN _flux_id INT, IN _anneemois varchar(6))
BEGIN
  DROP TEMPORARY TABLE IF EXISTS tmp_pai_suivi_horaire;
  CREATE TEMPORARY TABLE tmp_pai_suivi_horaire(
  epd_id              int
  ,employe_id         int
  ,date_debut         date
  ,date_fin           date
  ,nbheures_garanties decimal(5,2)
  ,nbjours_cycle      int
  ,nbjours_mois       int -- A supprimer
  ,nbjours_contrat    int -- A supprimer
  ,nbjours_travailles int
  ,horaire_moyen      float
  ,nbheures_a_realiser float
  ,nbheures_realisees float
  ,nbheures_delegation float
  ,nbheures_hors_presse float
  ,nbheures_apayer float
  ,est_hors_presse boolean
  ,nbheures_nuit float
  ,majoration_nuit boolean default false
  ,nbheures_garanties_majorees float
  ) engine=memory
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;

  call create_tmp_pai_suivi_horaire_presse_exec(_depot_id, _flux_id, _anneemois);
  call create_tmp_pai_suivi_horaire_horspresse_exec(_depot_id, _flux_id, _anneemois);
  call create_tmp_pai_suivi_horaire_delegation_exec(_depot_id, _flux_id, _anneemois);
  
  update tmp_pai_suivi_horaire
  set nbheures_apayer=nbheures_realisees-nbheures_a_realiser;
END;


-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_suivi_horaire_presse_exec;
CREATE PROCEDURE create_tmp_pai_suivi_horaire_presse_exec(IN _depot_id INT, IN _flux_id INT, IN _anneemois varchar(6))
BEGIN
  insert into tmp_pai_suivi_horaire(epd_id,employe_id,date_debut,date_fin,nbheures_garanties,horaire_moyen,nbjours_cycle,nbjours_travailles,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse,nbheures_nuit)
  select
      epd.emploi_id,
      epd.employe_id,
      greatest(epd.date_debut,prm.date_debut) as date_debut,
      least(epd.date_fin,prm.date_fin) as date_fin,
      epd.nbheures_garanties,
      pai_horaire_moyen_float(epd.employe_id,epd.date_debut,epd.date_fin,epd.nbheures_garanties,prm.date_debut,prm.date_fin),
      pai_nbjours_cycle(epd.employe_id,epd.date_debut,prm.date_debut,prm.date_fin),
      count(distinct if(coalesce(ra.est_garantie,true),pd.date_distrib,null)) as nbjours_travailles,
      -- ra.est_garantie=null pour les tournées !!!
      coalesce(sum(if(coalesce(ra.est_garantie,true),    time_to_sec(pd.duree),0))/3600,0) as nbheures_realisees,
      coalesce(sum(if(coalesce(ra.est_hors_travail,false),time_to_sec(pd.duree),0))/3600,0) as nbheures_delegation,
      coalesce(sum(if(coalesce(ra.est_hors_presse,false), time_to_sec(pd.duree),0))/3600,0) as nbheures_hors_presse,
      false,
      coalesce(sum(if(pd.tournee_id is not null, time_to_sec(pd.duree_nuit),0))/3600,0) as nbheures_nuit
  from emp_pop_depot epd
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  left outer join tmp_pai_horaire pd on pd.employe_id=epd.employe_id and pd.date_distrib between greatest(epd.date_debut,prm.date_debut) and least(epd.date_fin,prm.date_fin)
  left outer join ref_activite ra on pd.ref_activite_id=ra.id
  where epd.nbheures_garanties is not null and epd.nbheures_garanties<>0
  and epd.date_debut<=prm.date_fin and epd.date_fin>=prm.date_debut
  and not coalesce(ra.est_pleiades,false)
  and not exists(select null
                from emp_contrat_hp ech
                inner join pai_png_xta_rcactivite xrc on ech.xta_rcactivte=xrc.oid and xrc.code='RC'
                where epd.rcoid=ech.rcoid)
	AND (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
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
/*
  
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
                        );*/                        
  update tmp_pai_suivi_horaire
  set nbheures_a_realiser=least(horaire_moyen*nbjours_travailles,nbheures_garanties);
                        
  update tmp_pai_suivi_horaire
  set majoration_nuit=(nbheures_nuit>nbjours_travailles*270/365);

  -- 20161114
  -- Lorsqu'il y a un jour ferié, il faut aussi doubler les heures garanties de ce jour
  -- On calcul donc la différence entre l'horaire moyen et la somme des tournées/activités
set @horaire_moyen=null;
set @date_debut=null;
  update tmp_pai_suivi_horaire tpsh
  inner join (select tph2.employe_id,tph2.date_debut,sum(tph2.nbheures_garanties_majorees) as nbheures_garanties_majorees
              from (select epd.employe_id
                    ,@date_debut as date_debut
                    ,tph.date_distrib
                    ,@horaire_moyen-sum(time_to_sec(tph.duree)/3600) as nbheures_garanties_majorees
                    from tmp_pai_horaire tph
                    inner join pai_ref_mois prm on prm.anneemois=_anneemois
                    inner join emp_pop_depot epd on epd.employe_id=tph.employe_id and tph.date_distrib between epd.date_debut and epd.date_fin
                    left outer join ref_activite ra on tph.ref_activite_id=ra.id
                    where tph.typejour_id in(3) -- seulement les jours feriés
                    and tph.flux_id=2 -- seuelemnt mediapresse
                    and not coalesce(ra.est_pleiades,false)
                    group by epd.employe_id,@date_debut:=greatest(epd.date_debut,prm.date_debut),tph.date_distrib,@horaire_moyen:=pai_horaire_moyen_float(epd.employe_id,epd.date_debut,epd.date_fin,epd.nbheures_garanties,prm.date_debut,prm.date_fin)
                    having sum(time_to_sec(tph.duree)/3600)<@horaire_moyen
                    ) tph2
              group by tph2.employe_id,tph2.date_debut
              ) tph3 on tph3.employe_id=tpsh.employe_id and tph3.date_debut=tpsh.date_debut
  set tpsh.nbheures_garanties_majorees=tph3.nbheures_garanties_majorees
  ;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS create_tmp_pai_suivi_horaire_horspresse_exec;
CREATE PROCEDURE create_tmp_pai_suivi_horaire_horspresse_exec(IN _depot_id INT, IN _flux_id INT, IN _anneemois varchar(6))
BEGIN
set @est_mensualise=false;
  insert into tmp_pai_suivi_horaire(epd_id,employe_id,date_debut,date_fin,nbheures_garanties,horaire_moyen,nbjours_cycle,nbjours_travailles,nbheures_a_realiser,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse)
  select
      -ech.activite_id,
      ech.employe_id,
      greatest(ech.date_debut,prm.date_debut) as date_debut,
      least(ech.date_fin,prm.date_fin) as date_fin,
      if(pai_hp_est_mensualise(ech.xaoid,ech.date_debut,ech.date_fin),coalesce(ech.nbheures_mensuel,0),null),
      case 
--      when prm.date_debut<='2015-12-20' then xa.nbheurejr
      when pai_hp_est_mensualise(ech.xaoid,ech.date_debut,ech.date_fin) then pai_horaire_moyen_HP_float(ech.xaoid,ech.nbheures_mensuel,prm.date_debut,prm.date_fin)
      else sec_to_time(case dayofweek(pd.date_distrib) when 1 then ech.nbheures_dimanche when 2 then ech.nbheures_lundi when 3 then ech.nbheures_mardi when 4 then ech.nbheures_mercredi when 5 then ech.nbheures_jeudi when 6 then ech.nbheures_vendredi when 7 then ech.nbheures_samedi end*3600)
      end,
      pai_nbjours_cycle_HP(ech.xaoid,prm.date_debut,prm.date_fin) as nbjours_cycle,
      count(distinct if(pd.duree<>'00:00',pd.date_distrib,null)) as nbjours_travailles,
      coalesce(sum(if(pd.duree<>'00:00',time_to_sec(pd.duree_garantie),0))/3600,0) as nbheures_a_realiser,
      coalesce(sum(time_to_sec(pd.duree))/3600,0) as nbheures_realisees,
      0 as nbheures_delegation,
      coalesce(sum(time_to_sec(pd.duree))/3600,0) as nbheures_hors_presse,
      true
  from emp_contrat_hp ech
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  left outer join tmp_pai_horaire pd on pd.employe_id=ech.employe_id and pd.date_distrib between greatest(ech.date_debut,prm.date_debut) and least(ech.date_fin,prm.date_fin)
    and pd.depot_id=ech.depot_id and pd.flux_id=ech.flux_id
    and pd.ref_activite_id=ech.activite_id
  where ech.date_debut<=prm.date_fin and ech.date_fin>=prm.date_debut
	AND (ech.depot_id=_depot_id OR _depot_id IS NULL)
	AND (ech.flux_id=_flux_id OR _flux_id IS NULL)
  group by ech.xaoid,ech.employe_id,prm.anneemois,greatest(ech.date_debut,prm.date_debut),least(ech.date_fin,prm.date_fin);

  -- 14/12/2015 On ajouteles individus qui n'ont que des heures hors-presse hors-pleiades(formation hors-presse)
  insert into tmp_pai_suivi_horaire(epd_id,employe_id,date_debut,date_fin,nbheures_garanties,nbjours_travailles,nbheures_a_realiser,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse)
  select
      epd.emploi_id,
      epd.employe_id,
      greatest(epd.date_debut,prm.date_debut) as date_debut,
      least(epd.date_fin,prm.date_fin) as date_fin,
      null,
      count(distinct if(pd.duree<>'00:00',pd.date_distrib,null)) as nbjours_travailles,
      0 as nbheures_a_realiser,
      0 as nbheures_realisees,
      0 as nbheures_delegation,
      coalesce(sum(time_to_sec(pd.duree))/3600,0) as nbheures_hors_presse,
      false
  from emp_pop_depot epd
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  inner join tmp_pai_horaire pd on pd.employe_id=epd.employe_id and pd.date_distrib between greatest(epd.date_debut,prm.date_debut) and least(epd.date_fin,prm.date_fin)
  inner join ref_activite ra on pd.ref_activite_id=ra.id and not ra.est_pleiades and ra.est_hors_presse
  where (epd.nbheures_garanties is null or epd.nbheures_garanties=0)
  and epd.date_debut<=prm.date_fin and epd.date_fin>=prm.date_debut
	AND (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  group by epd.id,greatest(epd.date_debut,prm.date_debut),least(epd.date_fin,prm.date_fin);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Ceux qui n'ont pas d'heure garantie, mais des heures de délégation
DROP PROCEDURE IF EXISTS create_tmp_pai_suivi_horaire_delegation_exec;
CREATE PROCEDURE create_tmp_pai_suivi_horaire_delegation_exec(IN _depot_id INT, IN _flux_id INT, IN _anneemois varchar(6))
BEGIN
  insert into tmp_pai_suivi_horaire(epd_id,employe_id,date_debut,date_fin,nbheures_garanties,nbjours_travailles,nbheures_a_realiser,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse)
  select
      epd.emploi_id,
      epd.employe_id,
      greatest(epd.date_debut,prm.date_debut) as date_debut,
      least(epd.date_fin,prm.date_fin) as date_fin,
      null,
      count(distinct if(pd.duree<>'00:00',pd.date_distrib,null)) as nbjours_travailles,
      0 as nbheures_a_realiser,
      0 as nbheures_realisees,
      coalesce(sum(time_to_sec(pd.duree))/3600,0) as nbheures_delegation,
      0 as nbheures_hors_presse,
      false
  from emp_pop_depot epd
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  inner join tmp_pai_horaire pd on pd.employe_id=epd.employe_id and pd.date_distrib between greatest(epd.date_debut,prm.date_debut) and least(epd.date_fin,prm.date_fin)
  inner join ref_activite ra on pd.ref_activite_id=ra.id and not ra.est_pleiades and ra.est_hors_travail
  where (epd.nbheures_garanties is null or epd.nbheures_garanties=0)
  and epd.date_debut<=prm.date_fin and epd.date_fin>=prm.date_debut
	AND (epd.depot_id=_depot_id OR _depot_id IS NULL)
	AND (epd.flux_id=_flux_id OR _flux_id IS NULL)
  group by epd.id,greatest(epd.date_debut,prm.date_debut),least(epd.date_fin,prm.date_fin);
end;

