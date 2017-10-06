select coalesce(pre.valide,true) as valide ,res.libelle as societe,d.libelle as depot,e.nom,e.prenom1,pa.date_distrib,rtc.code as contrat,ra.code,ra.libelle as activite,pa.commentaire,pa.duree,pa.nbkm_paye
from pai_activite pa
inner join ref_activite ra on pa.activite_id=ra.id and ra.est_hors_presse
inner join employe e on pa.employe_id=e.id
inner join depot d ON d.id = pa.depot_id
left outer join emp_pop_depot epd on pa.employe_id=epd.employe_id and pa.date_distrib between epd.date_debut and epd.date_fin
left outer join ref_typecontrat rtc ON rtc.id = epd.typecontrat_id
left outer join ref_emp_societe res on res.id = epd.societe_id
left outer join pai_journal pj on pa.id=pj.activite_id
left outer join pai_ref_erreur pre on pj.erreur_id=pre.id
where pa.date_distrib between '2015-01-01' and  '2015-01-31'
-- where pa.date_extrait='2015-01-23 16:59:10'
order by 1,2,3,4,5,6;

select coalesce(pre.valide,true) as valide ,res.libelle as societe,d.libelle as depot,if(pa.flux_id=1,'Nuit','Jour') as flux,pa.id,e.nom,e.prenom1,e.prenom2,pa.date_distrib,rtc.code as contrat,ra.code,ra.libelle as activite,pa.duree,pa.nbkm_paye,pa.commentaire
from pai_activite pa
inner join ref_activite ra on pa.activite_id=ra.id and not ra.est_hors_presse
inner join employe e on pa.employe_id=e.id
inner join depot d ON d.id = pa.depot_id
left outer join emp_pop_depot epd on pa.employe_id=epd.employe_id and pa.date_distrib between epd.date_debut and epd.date_fin
left outer join ref_typecontrat rtc ON rtc.id = epd.typecontrat_id
left outer join ref_emp_societe res on res.id = epd.societe_id
left outer join pai_journal pj on pa.id=pj.activite_id
left outer join pai_ref_erreur pre on pj.erreur_id=pre.id
where pa.date_distrib between '2014-12-21' and  '2015-01-20'
-- where pa.date_extrait='2015-01-23 16:59:10'
order by 1,2,3,4,5,6;
/*
1		CD29 ARCUEIL	Jour	557835	DEVY	MICHEL		10/01/2015 00:00:00		AT	Tournée - Attente	00:40:00	0	
1		CD29 ARCUEIL	Jour	557836	BERTOLEZ ROCHAT	ANTONIO		10/01/2015 00:00:00		AT	Tournée - Attente	00:40:00	0	
1		CD29 ARCUEIL	Jour	558247	BERTOLEZ ROCHAT	ANTONIO		13/01/2015 00:00:00		AT	Tournée - Attente	00:20:00	0	
1		CD29 ARCUEIL	Jour	559061	BERTOLEZ ROCHAT	ANTONIO		16/01/2015 00:00:00		AT	Tournée - Attente	00:25:00	0	
1		CD29 ARCUEIL	Jour	560157	LOUIS	KENOL	OCTAVE	20/01/2015 00:00:00		FO	Formation Porteurs	04:30:00	40	formation tournée 1512*/
select * from employe where nom='BERTOLEZ ROCHAT'
select * from employe where nom='DEVY'
select * from employe where nom='LOUIS'
select * from pai_ev_emp_pop_depot_hst where employe_id in (7333);
select * from pai_ev_emp_pop_depot_hst where employe_id in (6978);
select * from pai_ev_emp_pop_depot_hst where employe_id in (7046);
select * from pai_ev_activite_hst where id in (557835,557836,558247,559061,560157);
-- 560157	11	2	7046	9	1	3	20/01/2015 00:00:00	04:30:00	00:00:00	40,0	0	1400	
select * from pai_int_traitement order by date_debut desc;
date_debut='23/01/2015 16:59:10';
select * from pai_activite where id in (557835,557836,558247,559061,560157);
select * from pai_journal where activite_id in (557835,557836,558247,559061,560157)
select * from pai_journal where tournee_id in (656264,656265,657404,659372)
select * from pai_tournee where id in (656264,656265,657404,659372)