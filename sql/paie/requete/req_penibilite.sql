
drop table tmp_penibilite_2016;
create table tmp_penibilite_2016 as
select ph.employe_id,ph.date_distrib,min(ph.heure_debut) as heure_debut,sec_to_time(sum(time_to_sec(ph.duree_nuit))) as duree_nuit
from pai_suivi_horaire ph
where ph.date_distrib between '2015-12-21' and '2016-12-20'
group by ph.employe_id,ph.date_distrib
having min(ph.heure_debut)<='04:00:00' and sec_to_time(sum(time_to_sec(ph.duree_nuit)))>='01:00:00';

select e.matricule,e.nom,e.prenom1,ec.rc,ec.date_debut,ec.date_fin,group_concat(distinct d.code separator ' ') as depot
,sum(case when prm.anneemois='201601' then 1 else 0 end)  as '201601'
,sum( case when prm.anneemois='201602' then 1 else 0 end)  as '201602'
,sum( case when prm.anneemois='201603' then 1 else 0 end)  as '201603'
,sum( case when prm.anneemois='201604' then 1 else 0 end)  as '201604'
,sum( case when prm.anneemois='201605' then 1 else 0 end)  as '201605'
,sum( case when prm.anneemois='201606' then 1 else 0 end)  as '201606'
,sum( case when prm.anneemois='201607' then 1 else 0 end)  as '201607'
,sum( case when prm.anneemois='201608' then 1 else 0 end)  as '201608'
,sum( case when prm.anneemois='201609' then 1 else 0 end)  as '201609'
,sum( case when prm.anneemois='201610' then 1 else 0 end)  as '201610'
,sum( case when prm.anneemois='201611' then 1 else 0 end)  as '201611'
,sum( case when prm.anneemois='201612' then 1 else 0 end)  as '201612'
,count(distinct tp.date_distrib) as nb_nuit
from tmp_penibilite_2016 tp
inner join emp_contrat ec on tp.employe_id=ec.employe_id and tp.date_distrib between ec.date_debut and ec.date_fin
inner join emp_pop_depot epd on tp.employe_id=epd.employe_id and tp.date_distrib between epd.date_debut and epd.date_fin
inner join depot d on epd.depot_id=d.id
inner join pai_ref_mois prm on tp.date_distrib between prm.date_debut and prm.date_fin
inner join employe e on tp.employe_id=e.id
group by ec.rcoid,e.id
