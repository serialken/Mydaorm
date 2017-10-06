select prc.jour,prc.datecal,pm.flux_id,d.code,
(select count(distinct pt.id) from pai_tournee pt where prc.datecal=pt.date_distrib and d.id=pt.depot_id and pm.flux_id=pt.flux_id and pt.date_distrib between pm.date_debut and pm.date_fin) as tournee,
(select sum(coalesce(pt.nbtitre)) from pai_tournee pt where prc.datecal=pt.date_distrib and d.id=pt.depot_id and pm.flux_id=pt.flux_id and pt.date_distrib between pm.date_debut and pm.date_fin) as tournee,
(select count(distinct pa.id) from pai_activite pa where prc.datecal=pa.date_distrib and d.id=pa.depot_id and pa.date_distrib between pm.date_debut and pm.date_fin) as activite
from pai_ref_calendrier prc
inner join pai_mois pm on prc.datecal between pm.date_debut and pm.date_fin
,depot d
where d.id in (10,11,13,18,19,21,24)
order by 4,3,2
