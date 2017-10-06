select e.matricule,e.nom,e.prenom1
,d.libelle as depot,rf.libelle as flux
,p.code as population,p.libelle as population,re.libelle as emploi
,eep.nbabo,eep.qualite as "code MRoad"
,peq.qualite as "taux qualité",rq.borne_inf,rq.borne_sup,rq.valeur as "code NG",rq.envoiNG,rq.prime
,rq.libelle
from  pai_ev_emp_pop_hst eep
inner join employe e on eep.employe_id=e.id
inner join ref_population p on eep.population_id=p.id
left outer join emp_pop_depot epd on eep.employe_id=epd.employe_id and eep.d between epd.date_debut and epd.date_fin
left outer join depot d on epd.depot_id=d.id
left outer join ref_flux rf on epd.flux_id=rf.id
left outer join ref_emploi re on epd.emploi_id=re.id
left outer join pai_ev_qualite peq on peq.employe_id=eep.employe_id and peq.datev=eep.d and  eep.idtrt=peq.idtrt
left outer join pai_ref_qualite rq on eep.qualite=rq.qualite and eep.population_id=rq.population_id and greatest(peq.qualite,0) between rq.borne_inf AND rq.borne_sup
where eep.idtrt = (
  select max(id) 
  from pai_int_traitement 
  where typetrt='GENERE_PLEIADES_MENSUEL' and flux_id=2)
--  and depot_id in (10)
  and depot_id in (10,11,13,18,19,24)
-- and eep.population_id in (3,4)  
and e.id=6797
order by d.code,rf.id,e.nom,e.prenom1

select e.matricule,e.nom,e.prenom1
,d.libelle as depot,rf.libelle as flux
,p.code as population,p.libelle as population,re.libelle as emploi
,eep.nbabo,eep.qualite as "code MRoad"
,peq.qualite as "taux qualité",rq.borne_inf,rq.borne_sup,rq.valeur as "code NG",rq.envoiNG,rq.prime
,rq.libelle
from  pai_ev_emp_pop_hst eep
inner join employe e on eep.employe_id=e.id
inner join ref_population p on eep.population_id=p.id
left outer join emp_pop_depot epd on eep.employe_id=epd.employe_id and eep.d between epd.date_debut and epd.date_fin
left outer join depot d on epd.depot_id=d.id
left outer join ref_flux rf on epd.flux_id=rf.id
left outer join ref_emploi re on epd.emploi_id=re.id
left outer join pai_ev_qualite peq on peq.employe_id=eep.employe_id and peq.datev=eep.d and  eep.idtrt=peq.idtrt
left outer join pai_ref_qualite rq on eep.qualite=rq.qualite and eep.population_id=rq.population_id and greatest(peq.qualite,0) between rq.borne_inf AND rq.borne_sup
where eep.idtrt = (
  select max(id) 
  from pai_int_traitement 
  where typetrt='GENERE_PLEIADES_CLOTURE' and flux_id=2)
--  and depot_id in (10)
  and depot_id in (10,11,13,18,19,24)
-- and eep.population_id in (3,4)  
and e.id=6797
order by d.code,rf.id,e.nom,e.prenom1

select r.*,t.flux_id,t.date_distrib,t.* from pai_reclamation r
inner join pai_tournee t on r.tournee_id=t.id where date_distrib>'2015-03-21'
where t.employe_id=6797

select * from pai_int_traitement where id=2060
-- 2060	25/03/2015 19:53:49	25/03/2015 19:59:51	15	GENERE_PLEIADES_CLOTURE		1		T	201503
select * from pai_int_traitement where id=2061
-- 2061	25/03/2015 20:05:04	25/03/2015 20:08:50	15	GENERE_PLEIADES_CLOTURE		2		T	201503

update  pai_reclamation r
inner join pai_tournee t on r.tournee_id=t.id 
set r.date_extrait=null
where r.date_extrait='2015-03-25 19:53:49'
and t.flux_id=2

select * from  pai_reclamation r
inner join pai_tournee t on r.tournee_id=t.id 
where r.date_extrait='2015-03-25 20:05:04'
and t.flux_id=2

