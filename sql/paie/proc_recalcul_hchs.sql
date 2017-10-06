/*
  call recalcul_hchs('201610',null,null);
  
select * from pai_hchs order by employe_id,date_debut desc;
select * from employe where nom='CANDIR'
select * from tmp_pai_horaire where employe_id=6519 order by date_distrib
select * from pai_activite where employe_id=6519 and date_distrib>='2016-06-20'
select * from pai_journal where activite_id=884241
select * from pai_hchs where employe_id=6519;


201605	18	2	509ff690-0799-11e6-8673-e7afda0016f5	1	18/04/2016 00:00:00	24/04/2016 00:00:00	21/04/2016 00:00:00	24/04/2016 00:00:00	9475	43	151,58	6	0	34,3322	34,98	17,49	17,49	17,49			0,65	0	
select * from v_employe where employe_id=9475
select * from pai_hchs where employe_id=9475;
select * from tmp_pai_horaire where employe_id=9475 order by date_distrib
select * from pai_activite where employe_id=9475 


select * from pai_hchs where nbjours_abs<>0;
select * from pai_hchs where nbheures_hs1<>0 or nbheures_hs2<>0 order by anneemois,employe_id,date_debut;
select * from pai_hchs where nbheures_hc1<>0 or nbheures_hc2<>0 or nbheures_hs1<>0 or nbheures_hs2<>0 order by anneemois,employe_id,date_debut;
select * from pai_hchs where round(nbheures_hc10,2)<>0 or round(nbheures_hs25,2)<>0 or round(nbheures_hs50,2)<>0 order by employe_id,date_debut;
select * from pai_hchs where (nbheures_hc10>nbheures_a_realiser or nbheures_hs25>nbheures_a_realiser or nbheures_hs50>nbheures_a_realiser);

select ph.anneemois,e.depot,e.nom,e.prenom1,ra.libelle,ph.date_debut,ph.date_fin,ph.nbheures_mensuelles,ph.nbheures_a_realiser,ph.nbheures_realisees,ph.nbheures_hn,ph.nbheures_hc1,ph.nbheures_hc2,ph.nbheures_hs1,ph.nbheures_hs2 
from pai_hchs ph
inner join v_employe e on ph.employe_id=e.employe_id and ph.date_debut between e.date_debut and e.date_fin
inner join pai_png_xrcautreactivit xa on ph.xaoid=xa.oid
inner join pai_png_xta_rcactivhpre xra on xa.xta_rcactivhpre=xra.oid
inner join ref_activite ra on xra.code=ra.code and ra.est_pleiades
where ph.nbheures_hc1<>0 or ph.nbheures_hc2<>0 or ph.nbheures_hs1<>0 or ph.nbheures_hs2<>0 
order by ph.anneemois,e.depot,e.nom,e.prenom1,ph.date_debut;

select * from employe where nom='RAYE'
select * from pai_hchs where employe_id=6755
  select prm.anneemois,xa.depot_id,xa.flux_id,xa.oid,prm.date_debut, prm.date_fin, xa.nbrheuremensuel
  from pai_png_xrcautreactivit xa
  inner join pai_ref_mois prm -- on prm.anneemois='201606'
  inner join emp_pop_depot epd on xa.relationcontrat=epd.rcoid and xa.begin_date between epd.date_debut and epd.date_fin
  where xa.nbrheuremensuel<151-- .67 -- Temps partiel
  and xa.begin_date<=prm.date_fin and xa.end_date>=prm.date_debut -- contrat sur le mois de paie
  and not exists(select null from pai_hchs ph where ph.anneemois=prm.anneemois and ph.xaoid=xa.oid and ph.date_debut=prm.date_debut and ph.date_extrait is not null)
  and epd.employe_id=6755
  ;

  DROP TABLE pai_hchs;
  CREATE TABLE pai_hchs(
  anneemois           varchar(6) not null
  ,depot_id            INT not null
  ,flux_id             INT not null
  ,xaoid               varchar(36) not null
  ,est_tempcomplet    tinyint(1)
  ,date_debut         date not null
  ,date_fin           date not null
  ,date_debut_2        date not null
  ,date_fin_2          date not null
  ,employe_id         int
  ,activite_id        int
  ,nbheures_mensuelles decimal(5,2)
  ,nbjours_cycle      int
  ,nbjours_absence    int
 -- ,nbjours_abs_famille int
 -- ,nbjours_abs_autre   int
  ,nbheures_a_realiser float
  ,nbheures_realisees float
  ,nbheures_a_realiser_2 float
  ,nbheures_realisees_2 float
--  ,nbheures_contrat   float
  ,nbheures_hn        float
  ,nbheures_hc1       float
  ,nbheures_hc2       float
  ,nbheures_hs1       float
  ,nbheures_hs2       float
  ,date_extrait       date
  ,PRIMARY KEY (anneemois,xaoid,date_debut)
  )
  DEFAULT CHARSET=utf8
  COLLATE=utf8_unicode_ci;
  
  select * from pai_hchs order by anneemois, depot_id, flux_id, employe_id;
alter table pai_hchs add activite_id int
alter table pai_hchs add date_creation DATETIME NOT NULL;
update pai_hchs set date_creation=now();
*/
-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS int_mroad2ev_recalcul_hchs;
CREATE PROCEDURE int_mroad2ev_recalcul_hchs(IN _anneemois varchar(6), IN _flux_id INT)
BEGIN
DECLARE _nextanneemois  VARCHAR(6);
  call recalcul_hchs(_anneemois, null, _flux_id);
  -- Pour les stc
  select min(anneemois) INTO _nextanneemois from pai_ref_mois prm where anneemois>_anneemois;
  call recalcul_hchs(_nextanneemois, null, _flux_id);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hchs;
CREATE PROCEDURE recalcul_hchs(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
  call create_tmp_pai_hchs(_depot_id,_flux_id,_anneemois);
  call recalcul_hchs_delete(_anneemois, _depot_id, _flux_id);
  call recalcul_hchs_init(_anneemois, _depot_id, _flux_id);
  call recalcul_hchs_update(_anneemois, _depot_id, _flux_id);
  call recalcul_hn_exec(_anneemois, _depot_id, _flux_id);
  call recalcul_hc_exec(_anneemois, _depot_id, _flux_id);
  call recalcul_hs_exec(_anneemois, _depot_id, _flux_id);
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hchs_delete;
CREATE PROCEDURE recalcul_hchs_delete(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
  delete from pai_hchs
  where anneemois=_anneemois
	and (depot_id=_depot_id OR _depot_id IS NULL)
	and (flux_id=_flux_id OR _flux_id IS NULL)
  and date_extrait is null
  ;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hchs_init;
CREATE PROCEDURE recalcul_hchs_init(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
  -- Temps partiel (<151.67) ==> calcul au mois (du 21 au 20)
  insert into pai_hchs(date_creation,anneemois,depot_id,flux_id,xaoid,est_tempcomplet,employe_id,activite_id,date_debut,date_fin,date_debut_2,date_fin_2,nbheures_mensuelles)
--  select xa.oid,xa.relationcontrat,greatest(prm.date_debut,xa.begin_date), least(prm.date_fin,xa.end_date), xa.nbrheuremensuel
  select now(),prm.anneemois,ech.depot_id,ech.flux_id,ech.xaoid,0,ech.employe_id,ech.activite_id,prm.date_debut, prm.date_fin,prm.date_debut, prm.date_fin, ech.nbheures_mensuel
  from emp_contrat_hp ech
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  where ech.nbheures_mensuel<151-- .67 -- Temps partiel
  and ech.date_debut<=prm.date_fin and ech.date_fin>=prm.date_debut -- contrat sur le mois de paie
	AND (ech.depot_id=_depot_id OR _depot_id IS NULL)
	AND (ech.flux_id=_flux_id OR _flux_id IS NULL)
  and not exists(select null from pai_hchs ph where ph.anneemois=prm.anneemois and ph.xaoid=ech.xaoid and ph.date_debut=prm.date_debut and ph.date_extrait is not null)
  ;
  -- Temps complet (>=151.67) ==> calcul à la semaine (du lundi au dimanche)
  insert into pai_hchs(date_creation,anneemois,depot_id,flux_id,xaoid,est_tempcomplet,employe_id,activite_id,date_debut,date_fin,date_debut_2,date_fin_2,nbheures_mensuelles)
--  select xa.oid,xa.relationcontrat,greatest(prs.date_debut,xa.begin_date), least(prs.date_fin,xa.end_date), xa.nbrheuremensuel
  select now(),prm.anneemois,ech.depot_id,ech.flux_id,ech.xaoid,1,ech.employe_id,ech.activite_id,prs.date_debut, prs.date_fin,greatest(prs.date_debut,prm.date_debut), least(prs.date_fin,prm.date_fin), ech.nbheures_mensuel
  from emp_contrat_hp ech
  inner join pai_ref_mois prm on prm.anneemois=_anneemois
  inner join pai_ref_semaine prs on prs.date_fin>=prm.date_debut and prs.date_debut<=prm.date_fin -- and prs.date_fin<=prm.date_fin ?????
  where ech.nbheures_mensuel>=151-- .67
  and ech.date_debut<=prm.date_fin and ech.date_fin>=prm.date_debut -- contrat sur le mois de paie
  and ech.date_debut<=prs.date_fin and ech.date_fin>=prs.date_debut -- contrat sur le mois de paie
	AND (ech.depot_id=_depot_id OR _depot_id IS NULL)
	AND (ech.flux_id=_flux_id OR _flux_id IS NULL)
  and not exists(select null from pai_hchs ph where ph.anneemois=prm.anneemois and ph.xaoid=ech.xaoid and ph.date_debut=prs.date_debut and ph.date_extrait is not null)
  ;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hchs_update;
CREATE PROCEDURE recalcul_hchs_update(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
/*  update pai_hchs ph
  inner join pai_png_xrcautreactivit xa on ph.xaoid=xa.oid
  set employe_id=(select distinct epd.employe_id from emp_pop_depot epd where xa.relationcontrat=epd.rcoid)
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  ;*/
  update pai_hchs ph
  set nbjours_cycle=pai_nbjours_cycle_HP(ph.xaoid,ph.date_debut,ph.date_fin)
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null;

  /*  Les absences familiales à ne pas prendre en compte comme jours travaillés
      MW	Maternité + 800 h
      MX	Maternité - 800h
      PT	Paternité
      
      DBP	Décès beaux parents
      DCBP	Décès Parents
      DCC	Décès du conjoint
      DCFS	Décès Frère/Soeur
      DEN	Décès enfant
      EFM	Enfant malade
      EVF	Autres Evts  familiaux
      MAR	Mariage
      MARE	Mariage Enfant
      MW	Maternité + 800 h (MW00,MW01,MW02,MW03,MW04,MW20,MW21,MW22,MW23,MW24,MW30,MW31,MW32,MW33,MW34,MW40,MW41,MW42,MW43,MW04,MW50,MW51,MW52,MW53,MW54)
      MX	Maternité - 800h  (MX00,MX01,MX02,MX03,MX04,MX20,MX21,MX22,MX23,MX24,MX30,MX31,MX32,MX33,MX34,MX40,MX41,MX42,MX43,MX04,MX50,MX51,MX52,MX53,MX54)
      NAIS	Naissance Adopt. Enfant
      PT	Paternité (PT00)
  */
  update pai_hchs ph
  set nbjours_absence=(select count(distinct prc.datecal) 
                  from emp_contrat_hp ech
		  inner join employe e on ech.employe_id=e.id
                  inner join pai_oct_saiabs pos on e.matricule=pos.pers_mat 
                  and (substr(pos.abs_cod,1,2) in ('MW','MX','PT') or pos.abs_cod in ('DBP','DCBP','DCC','DCFS','DEN','EFM','EVF','MAR','MARE','NAIS'))
                  inner join pai_ref_calendrier prc on prc.datecal between pos.abs_dat and pos.abs_fin
                  where ph.employe_id=e.id
                  and ph.xaoid=ech.xaoid
                  AND (CASE DAYOFWEEK(prc.datecal)
                        WHEN 1 THEN ech.dimanche
                        WHEN 2 THEN ech.lundi
                        WHEN 3 THEN ech.mardi
                        WHEN 4 THEN ech.mercredi
                        WHEN 5 THEN ech.jeudi
                        WHEN 6 THEN ech.vendredi
                        WHEN 7 THEN ech.samedi
                        END)=1
                  and prc.datecal between ph.date_debut and ph.date_fin
                  )
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null;
  
  -- Horaire théorique pure
  update pai_hchs ph
  set nbheures_a_realiser=(select sum(case
                           when (dayofweek(prc.datecal)=1 and ech.dimanche='1'
                              or dayofweek(prc.datecal)=2 and ech.lundi='1'
                              or dayofweek(prc.datecal)=3 and ech.mardi='1'
                              or dayofweek(prc.datecal)=4 and ech.mercredi='1'
                              or dayofweek(prc.datecal)=5 and ech.jeudi='1'
                              or dayofweek(prc.datecal)=6 and ech.vendredi='1'
                              or dayofweek(prc.datecal)=7 and ech.samedi='1') then
                                  case 
                                  when pai_hp_est_mensualise(ech.xaoid,ech.date_debut,ech.date_fin) then pai_horaire_moyen_HP_float(ech.xaoid,ech.nbheures_mensuel,prm.date_debut,prm.date_fin)
                                  else sec_to_time(case dayofweek(prc.datecal) when 1 then ech.nbheures_dimanche when 2 then ech.nbheures_lundi when 3 then ech.nbheures_mardi when 4 then ech.nbheures_mercredi when 5 then ech.nbheures_jeudi when 6 then ech.nbheures_vendredi when 7 then ech.nbheures_samedi end*3600)
                                  end
                              else
                                0
                              end
                            )
                          from emp_contrat_hp ech, pai_ref_calendrier prc ,pai_ref_mois prm
                          where ph.xaoid=ech.xaoid
                          and prc.datecal between ph.date_debut and ph.date_fin
                          and prc.datecal between prm.date_debut and prm.date_fin
                          and not exists(select null
                                        from employe e
                                        inner join pai_oct_saiabs pos on e.matricule=pos.pers_mat 
                                          and (substr(pos.abs_cod,1,2) in ('MW','MX','PT') or pos.abs_cod in ('DBP','DCBP','DCC','DCFS','DEM','DEN','EFM','EVF','MAR','MARE','NAIS'))
                                        where ph.employe_id=e.id
                                        and prc.datecal between pos.abs_dat and pos.abs_fin
                                        )
                          )
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  ;
  update pai_hchs ph
  set nbheures_a_realiser_2=(select sum(case
                           when (dayofweek(prc.datecal)=1 and ech.dimanche='1'
                              or dayofweek(prc.datecal)=2 and ech.lundi='1'
                              or dayofweek(prc.datecal)=3 and ech.mardi='1'
                              or dayofweek(prc.datecal)=4 and ech.mercredi='1'
                              or dayofweek(prc.datecal)=5 and ech.jeudi='1'
                              or dayofweek(prc.datecal)=6 and ech.vendredi='1'
                              or dayofweek(prc.datecal)=7 and ech.samedi='1') then
                                  case 
                                  when pai_hp_est_mensualise(ech.xaoid,ech.date_debut,ech.date_fin) then pai_horaire_moyen_HP_float(ech.xaoid,ech.nbheures_mensuel,prm.date_debut,prm.date_fin)
                                  else sec_to_time(case dayofweek(prc.datecal) when 1 then ech.nbheures_dimanche when 2 then ech.nbheures_lundi when 3 then ech.nbheures_mardi when 4 then ech.nbheures_mercredi when 5 then ech.nbheures_jeudi when 6 then ech.nbheures_vendredi when 7 then ech.nbheures_samedi end*3600)
                                  end
                              else
                                0
                              end
                            )
                          from emp_contrat_hp ech, pai_ref_calendrier prc ,pai_ref_mois prm
                          where ph.xaoid=ech.xaoid
                          and prc.datecal between ph.date_debut_2 and ph.date_fin_2
                          and prc.datecal between prm.date_debut and prm.date_fin
                          and not exists(select null
                                        from employe e
                                        inner join pai_oct_saiabs pos on e.matricule=pos.pers_mat 
                                          and (substr(pos.abs_cod,1,2) in ('MW','MX','PT') or pos.abs_cod in ('DBP','DCBP','DCC','DCFS','DEM','DEN','EFM','EVF','MAR','MARE','NAIS'))
                                        where ph.employe_id=e.id
                                        and prc.datecal between pos.abs_dat and pos.abs_fin
                                        )
                          )
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  ;
    update pai_hchs ph
  set nbheures_a_realiser=(select coalesce(sum(time_to_sec(pd.duree_garantie))/3600,0)
                          from tmp_pai_horaire pd
                          where ph.xaoid=pd.xaoid and pd.date_distrib between ph.date_debut and ph.date_fin
                          and not exists(select null
                                        from employe e
                                        inner join pai_oct_saiabs pos on e.matricule=pos.pers_mat 
                                          and (substr(pos.abs_cod,1,2) in ('MW','MX','PT') or pos.abs_cod in ('DBP','DCBP','DCC','DCFS','DEM','DEN','EFM','EVF','MAR','MARE','NAIS'))
                                        where ph.employe_id=e.id
                                        and pd.date_distrib between pos.abs_dat and pos.abs_fin
                                        )
                          )
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  ;
  update pai_hchs ph
  set nbheures_a_realiser_2=(select coalesce(sum(time_to_sec(pd.duree_garantie))/3600,0)
                          from tmp_pai_horaire pd
                          where ph.xaoid=pd.xaoid and pd.date_distrib between ph.date_debut_2 and ph.date_fin_2
                          and not exists(select null
                                        from employe e
                                        inner join pai_oct_saiabs pos on e.matricule=pos.pers_mat 
                                          and (substr(pos.abs_cod,1,2) in ('MW','MX','PT') or pos.abs_cod in ('DBP','DCBP','DCC','DCFS','DEM','DEN','EFM','EVF','MAR','MARE','NAIS'))
                                        where ph.employe_id=e.id
                                        and pd.date_distrib between pos.abs_dat and pos.abs_fin
                                        )
                          )
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  ;
  update pai_hchs ph
  set nbheures_realisees=(select coalesce(sum(time_to_sec(pd.duree))/3600,0)
                          from tmp_pai_horaire pd
                          where ph.xaoid=pd.xaoid and pd.date_distrib between ph.date_debut and ph.date_fin)
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  ;
  update pai_hchs ph
  set nbheures_realisees_2=(select coalesce(sum(time_to_sec(pd.duree))/3600,0)
                          from tmp_pai_horaire pd
                          where ph.xaoid=pd.xaoid and pd.date_distrib between ph.date_debut_2 and ph.date_fin_2)
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  ;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hn_exec;
CREATE PROCEDURE recalcul_hn_exec(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
  update pai_hchs ph
  set nbheures_hn=least(nbheures_realisees_2,nbheures_a_realiser_2)
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  ;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hc_exec;
CREATE PROCEDURE recalcul_hc_exec(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
  /*
Mise en place des majorations d’heures complémentaires pour les salariés à temps partiel (inférieur à 151.67h/mois), calcul au mois (pour nous du 21 au 20) :
Majoration de 10% dans la limite de 1/10 des heures contractuelles
Majoration de 25% au-delà
Ex : contrat de 100h/mois si 112h de travail effectif : 10h majorées à 10% et 2h majorées à 25%  */
  update pai_hchs ph
  set nbheures_hc1=if(ph.nbheures_realisees<=ph.nbheures_a_realiser,
                    0,
                    round(least(nbheures_realisees-nbheures_a_realiser,nbheures_a_realiser*0.1),2)
                    )
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  and not ph.est_tempcomplet
  ;
  update pai_hchs ph
  set nbheures_hc2=if(ph.nbheures_realisees<=ph.nbheures_a_realiser*1.10,
                    0,
                    round(nbheures_realisees-nbheures_a_realiser*1.10,2)
                    )
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  and not ph.est_tempcomplet
  ;
END;

-- --------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_hs_exec;
CREATE PROCEDURE recalcul_hs_exec(IN _anneemois varchar(6), IN _depot_id INT, IN _flux_id INT)
BEGIN
/*
Mise en place des majorations d’heures supplémentaires pour les salariés à temps complet (égal à 151.67h/mois), calcul à la semaine complète (Lundi au Dimanche) :
Majoration de 25% des 8 premières heures supplémentaires
Majoration de 50% au-delà
Ex : un salarié effectue 44h dans la semaine : 8h majorées à 25% et 1h majorée à 50%
*/
  update pai_hchs ph
  set nbheures_hs1=if(ph.nbheures_realisees<=ph.nbheures_a_realiser,
                    0,
                    round(least(nbheures_realisees-nbheures_a_realiser,8),2)
                    )
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  and ph.est_tempcomplet
  and ph.date_fin=ph.date_fin_2 -- seuelement pour les periodes complètes
  ;
  update pai_hchs ph
  set nbheures_hs2=if(ph.nbheures_realisees<=ph.nbheures_a_realiser+8,
                    0,
                    round(nbheures_realisees-nbheures_a_realiser-8,2)
                    )
  where ph.anneemois=_anneemois
	and (ph.depot_id=_depot_id OR _depot_id IS NULL)
	and (ph.flux_id=_flux_id OR _flux_id IS NULL)
  and ph.date_extrait is null
  and ph.est_tempcomplet
  and ph.date_fin=ph.date_fin_2 -- seuelement pour les periodes complètes
  ;
-- Pour le calcul des heures à réaliser, on ne prend pas en compte les jours ou il y a un MAT ou un AT
end;

