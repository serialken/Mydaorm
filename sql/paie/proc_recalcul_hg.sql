/*
-- call recalcul_all_hg;
    
delete from pai_ref_mois where anneemois<'201411'
select min(date_distrib) from pai_tournee
call recalcul_hg_exec('201612',null,null)
select * from tmp_pai_suivi_horaire 
select * from pai_hg 

  call recalcul_hg_delete('201612',null,null);
  call create_tmp_pai_horaire(null,null,null,'201612',null);
  call recalcul_hg_presse_exec('201612',null,null);
  call recalcul_hg_horspresse_exec('201612',null,null);
  call recalcul_hg_delegation_exec('201612',null,null);


update pai_hg phg set date_extrait=now() where anneemois<'201612'

select * from emp_contrat_hp where employe_id=432

Duplicate entry '201612-9702--4441-2016-11-22' for key 'PRIMARY'
select * from pai_hg where date_extrait is not null

DROP TABLE pai_hg;
  CREATE TABLE pai_hg(
  date_creation       DATETIME NOT NULL
  ,anneemois          varchar(6) not null
  ,depot_id           int
  ,flux_id            int
  ,employe_id         int
  ,epd_id             int
  ,emploi_id             int
  ,date_debut         date
  ,date_fin           date
  ,nbheures_garanties decimal(5,2)
  ,nbjours_cycle      int
  ,nbjours_travailles int
  ,horaire_moyen      float
  ,nbheures_a_realiser float
  ,nbheures_realisees float
  ,nbheures_delegation float
  ,nbheures_hors_presse float
  ,suivi_horaire float
  ,nbheures_garanties_majorees float
  ,nbheures_garanties_apayer float
  ,est_hors_presse boolean
  ,nbheures_nuit float
  ,majoration_nuit boolean default false
  ,date_extrait       datetime
  ,PRIMARY KEY (anneemois,employe_id,epd_id,date_debut)
  ) engine=memory
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
*/
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hg;
CREATE PROCEDURE recalcul_hg(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
DECLARE _nextanneemois  VARCHAR(6);
  call recalcul_hg_exec(_anneemois, _depot_id, _flux_id);
  -- Pour les stc
  select min(anneemois) INTO _nextanneemois from pai_ref_mois prm where anneemois>_anneemois;
  call recalcul_hg_exec(_nextanneemois, _depot_id, _flux_id);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hg_exec;
CREATE PROCEDURE recalcul_hg_exec(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
  call recalcul_hg_delete(_anneemois, _depot_id, _flux_id);
  call create_tmp_pai_horaire(_depot_id,_flux_id,null,_anneemois,null);
  call recalcul_hg_presse_exec(_anneemois, _depot_id, _flux_id);
  call recalcul_hg_horspresse_exec(_anneemois, _depot_id, _flux_id);
  call recalcul_hg_delegation_exec(_anneemois, _depot_id, _flux_id);
  
  update pai_hg phg
  set phg.suivi_horaire=phg.nbheures_realisees-phg.nbheures_a_realiser
  where phg.anneemois=_anneemois
	and (phg.depot_id=_depot_id OR _depot_id IS NULL)
	and (phg.flux_id=_flux_id OR _flux_id IS NULL)
  and phg.date_extrait is null
  ;
  
  update pai_hg phg
  set phg.nbheures_garanties_apayer=if(phg.flux_id=1 or phg.est_hors_presse,0,greatest(-phg.suivi_horaire,0)+coalesce(phg.nbheures_garanties_majorees,0))
  where phg.anneemois=_anneemois
	and (phg.depot_id=_depot_id OR _depot_id IS NULL)
	and (phg.flux_id=_flux_id OR _flux_id IS NULL)
  and phg.date_extrait is null
  ;
  call recalcul_hg_insert_activite(_anneemois, _depot_id, _flux_id);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hg_delete;
CREATE PROCEDURE recalcul_hg_delete(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
  delete from pai_hg
  where anneemois=_anneemois
	and (depot_id=_depot_id OR _depot_id IS NULL)
	and (flux_id=_flux_id OR _flux_id IS NULL)
  and date_extrait is null
  ;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hg_presse_exec;
CREATE PROCEDURE recalcul_hg_presse_exec(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
  insert into pai_hg(date_creation,anneemois,depot_id,flux_id,epd_id,emploi_id,employe_id,date_debut,date_fin,nbheures_garanties,horaire_moyen,nbjours_cycle,nbjours_travailles,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse,nbheures_nuit)
  select
    now(),
    prm.anneemois,
    epd.depot_id,
    epd.flux_id,
    epd.id,
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
  and not exists(select null from pai_hg phg where phg.anneemois=prm.anneemois and phg.employe_id=epd.employe_id and phg.epd_id=epd.id and phg.date_debut=greatest(epd.date_debut,prm.date_debut) and phg.date_extrait is not null)
  group by epd.id,greatest(epd.date_debut,prm.date_debut),least(epd.date_fin,prm.date_fin);


  update pai_hg phg
  inner join pai_ref_mois prm on prm.anneemois=phg.anneemois
  set phg.nbjours_cycle=( select sum(CASE DAYOFWEEK(prc.datecal)
                                            WHEN 1 THEN ecy.dimanche
                                            WHEN 2 THEN ecy.lundi
                                            WHEN 3 THEN ecy.mardi
                                            WHEN 4 THEN ecy.mercredi
                                            WHEN 5 THEN ecy.jeudi
                                            WHEN 6 THEN ecy.vendredi
                                            WHEN 7 THEN ecy.samedi
                                            END
                                            )
                        from pai_ref_calendrier prc
                        inner join emp_cycle ecy on prc.datecal between ecy.date_debut and ecy.date_fin
                        where phg.employe_id=ecy.employe_id 
                        and prc.datecal between prm.date_debut and prm.date_fin
                        and prc.datecal between phg.date_debut and phg.date_fin
                        )
  where phg.anneemois=_anneemois
	and (phg.depot_id=_depot_id OR _depot_id IS NULL)
	and (phg.flux_id=_flux_id OR _flux_id IS NULL)
  and phg.date_extrait is null
  ;
  update pai_hg phg
  set phg.nbheures_a_realiser=least(phg.horaire_moyen*phg.nbjours_travailles,phg.nbheures_garanties)
  ,   phg.majoration_nuit=(phg.nbheures_nuit>phg.nbjours_travailles*270/365) -- 08/02/2017 Inutilé, de toute façon pai_hg ne contient que les contrat avec des heures garanties
  where phg.anneemois=_anneemois
	and (phg.depot_id=_depot_id OR _depot_id IS NULL)
	and (phg.flux_id=_flux_id OR _flux_id IS NULL)
  and phg.date_extrait is null
  ;
  -- 20161114
  -- Lorsqu'il y a un jour ferié, il faut aussi doubler les heures garanties de ce jour
  -- On calcul donc la différence entre l'horaire moyen et la somme des tournées/activités
set @horaire_moyen=null;
set @date_debut=null;
  update pai_hg phg
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
                    and tph.date_distrib not like '%-05-01' -- pas le 1er mai 25/05/2017
                    and tph.flux_id=2 -- seuelemnt mediapresse
                    and not coalesce(ra.est_pleiades,false)
                    group by epd.employe_id,@date_debut:=greatest(epd.date_debut,prm.date_debut),tph.date_distrib,@horaire_moyen:=pai_horaire_moyen_float(epd.employe_id,epd.date_debut,epd.date_fin,epd.nbheures_garanties,prm.date_debut,prm.date_fin)
                    having sum(time_to_sec(tph.duree)/3600)<@horaire_moyen
                    ) tph2
              group by tph2.employe_id,tph2.date_debut
              ) tph3 on tph3.employe_id=phg.employe_id and tph3.date_debut=phg.date_debut
  set phg.nbheures_garanties_majorees=tph3.nbheures_garanties_majorees
  where phg.anneemois=_anneemois
	and (phg.depot_id=_depot_id OR _depot_id IS NULL)
	and (phg.flux_id=_flux_id OR _flux_id IS NULL)
  and phg.date_extrait is null
  ;
END;
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hg_horspresse_exec;
CREATE PROCEDURE recalcul_hg_horspresse_exec(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
set @est_mensualise=false;
  insert into pai_hg(date_creation,anneemois,depot_id,flux_id,epd_id,emploi_id,employe_id,date_debut,date_fin,nbheures_garanties,horaire_moyen,nbjours_cycle,nbjours_travailles,nbheures_a_realiser,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse)
  select
    now(),
    prm.anneemois,
    ech.depot_id,
    ech.flux_id,
   -ech.id,
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
    count(distinct pd.date_distrib) as nbjours_travailles,
    coalesce(sum(time_to_sec(pd.duree_garantie))/3600,0) as nbheures_a_realiser,
    coalesce(sum(time_to_sec(pd.duree))/3600,0) as nbheures_realisees,
    0 as nbheures_delegation,
    -- calcul sur le mois
--    coalesce(greatest(sum(time_to_sec(pd.duree)),sum(time_to_sec(pd.duree_garantie)))/3600,0) as nbheures_hors_presse,
    -- calcul au jour le jour
    coalesce(sum(greatest(time_to_sec(pd.duree),time_to_sec(pd.duree_garantie)))/3600,0) as nbheures_hors_presse,
    true
  from emp_contrat_hp ech
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  left outer join tmp_pai_horaire pd on pd.employe_id=ech.employe_id and pd.date_distrib between greatest(ech.date_debut,prm.date_debut) and least(ech.date_fin,prm.date_fin)
    and pd.depot_id=ech.depot_id and pd.flux_id=ech.flux_id
    and pd.ref_activite_id=ech.activite_id
  where ech.date_debut<=prm.date_fin and ech.date_fin>=prm.date_debut
	AND (ech.depot_id=_depot_id OR _depot_id IS NULL)
	AND (ech.flux_id=_flux_id OR _flux_id IS NULL)
  and pd.duree<>'00:00'
  and not exists(select null from pai_hg phg where phg.anneemois=prm.anneemois and phg.employe_id=ech.employe_id and phg.epd_id=-ech.id and phg.date_debut=greatest(ech.date_debut,prm.date_debut) and phg.date_extrait is not null)
  group by ech.xaoid,ech.employe_id,prm.anneemois,greatest(ech.date_debut,prm.date_debut),least(ech.date_fin,prm.date_fin);

  -- 14/12/2015 On ajouteles individus qui n'ont que des heures hors-presse hors-pleiades(formation hors-presse)
  insert into pai_hg(date_creation,anneemois,depot_id,flux_id,epd_id,emploi_id,employe_id,date_debut,date_fin,nbheures_garanties,nbjours_travailles,nbheures_a_realiser,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse)
  select
    now(),
    _anneemois,
    epd.depot_id,
    epd.flux_id,
    epd.id,
    epd.emploi_id,
    epd.employe_id,
    greatest(epd.date_debut,prm.date_debut) as date_debut,
    least(epd.date_fin,prm.date_fin) as date_fin,
    null,
    count(distinct pd.date_distrib) as nbjours_travailles,
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
  and pd.duree<>'00:00'
  and not exists(select null from pai_hg phg where phg.anneemois=prm.anneemois and phg.employe_id=epd.employe_id and phg.epd_id=epd.id and phg.date_debut=greatest(epd.date_debut,prm.date_debut) and phg.date_extrait is not null)
  group by epd.id,greatest(epd.date_debut,prm.date_debut),least(epd.date_fin,prm.date_fin);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
-- Ceux qui n'ont pas d'heure garantie, mais des heures de délégation
DROP PROCEDURE IF EXISTS recalcul_hg_delegation_exec;
CREATE PROCEDURE recalcul_hg_delegation_exec(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
  insert into pai_hg(date_creation,anneemois,depot_id,flux_id,epd_id,emploi_id,employe_id,date_debut,date_fin,nbheures_garanties,nbjours_travailles,nbheures_a_realiser,nbheures_realisees,nbheures_delegation,nbheures_hors_presse,est_hors_presse)
  select
    now(),
    _anneemois,
    epd.depot_id,
    epd.flux_id,
    epd.id,
    epd.emploi_id,
    epd.employe_id,
    greatest(epd.date_debut,prm.date_debut) as date_debut,
    least(epd.date_fin,prm.date_fin) as date_fin,
    null,
    count(distinct pd.date_distrib) as nbjours_travailles,
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
  and pd.duree<>'00:00'
  and not exists(select null from pai_hg phg where phg.anneemois=prm.anneemois and phg.employe_id=epd.employe_id and phg.epd_id=epd.id and phg.date_debut=greatest(epd.date_debut,prm.date_debut) and phg.date_extrait is not null)
  group by epd.id,greatest(epd.date_debut,prm.date_debut),least(epd.date_fin,prm.date_fin);
end;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hg_insert_activite;
CREATE PROCEDURE recalcul_hg_insert_activite(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
  delete pa from pai_activite pa
  inner join pai_ref_mois prm on prm.anneemois=_anneemois and pa.date_distrib between prm.date_debut and prm.date_fin
  where pa.date_extrait is null 
  and pa.activite_id in (-10,-11)
  and (pa.depot_id=_depot_id or _depot_id is null)
  and (pa.flux_id=_flux_id or _flux_id is null)
  ;

  INSERT INTO pai_activite (depot_id, flux_id, employe_id, date_distrib, activite_id, heure_debut, duree, transport_id, nbkm_paye, commentaire, duree_nuit, tournee_id, heure_debut_calculee, utilisateur_id, date_creation, date_extrait) 
  SELECT phg.depot_id, phg.flux_id, phg.employe_id, phg.date_fin, -10, '00:00:00', SEC_TO_TIME(sum(greatest(-phg.suivi_horaire,0))*3600), 1, 0, null, '00:00:00', null, null, 0, now(), null
  from pai_hg phg
  inner join pai_ref_mois prm on prm.anneemois=_anneemois and phg.date_fin between prm.date_debut and prm.date_fin
  where not (phg.flux_id=1 or phg.est_hors_presse)
  and phg.nbheures_garanties_apayer>0
  and (phg.depot_id=_depot_id or _depot_id is null)
  and (phg.flux_id=_flux_id or _flux_id is null)
  and not exists(select null from pai_activite pa where pa.date_distrib=phg.date_fin and pa.depot_id=phg.depot_id and pa.flux_id=phg.flux_id and pa.employe_id=phg.employe_id and pa.activite_id=-10 and pa.date_extrait is not null)
  group by phg.depot_id, phg.flux_id, phg.employe_id,phg.date_fin
  having sum(greatest(-phg.suivi_horaire,0))>0
  ;

  INSERT INTO pai_activite (depot_id, flux_id, employe_id, date_distrib, activite_id, heure_debut, duree, transport_id, nbkm_paye, commentaire, duree_nuit, tournee_id, heure_debut_calculee, utilisateur_id, date_creation, date_extrait) 
  SELECT phg.depot_id, phg.flux_id, phg.employe_id, phg.date_fin, -11, '00:00:00', SEC_TO_TIME(sum(coalesce(phg.nbheures_garanties_majorees,0))*3600), 1, 0, null, '00:00:00', null, null, 0, now(), null
  from pai_hg phg
  inner join pai_ref_mois prm on prm.anneemois=_anneemois and phg.date_fin between prm.date_debut and prm.date_fin
  where not (phg.flux_id=1 or phg.est_hors_presse)
  and phg.nbheures_garanties_apayer>0
  and (phg.depot_id=_depot_id or _depot_id is null)
  and (phg.flux_id=_flux_id or _flux_id is null)
  and not exists(select null from pai_activite pa where pa.date_distrib=phg.date_fin and pa.depot_id=phg.depot_id and pa.flux_id=phg.flux_id and pa.employe_id=phg.employe_id and pa.activite_id=-11 and pa.date_extrait is not null)
  group by phg.depot_id, phg.flux_id, phg.employe_id,phg.date_fin
  having sum(coalesce(phg.nbheures_garanties_majorees,0))>0
  ;
end;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_all_hg;
CREATE PROCEDURE recalcul_all_hg()
BEGIN
declare done int default false;
DECLARE _anneemois varchar(6);
DECLARE c cursor for select anneemois from pai_ref_mois where anneemois<='201611';
DECLARE continue handler for not found set done=true;
  open c;
  igm: loop
    fetch c into _anneemois;
    if done then 
      leave igm;
    end if;
    call recalcul_hg_exec(_anneemois,null,null);
  end loop;
  close c;
end;
