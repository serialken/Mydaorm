-- reclamations manuelles ou pepp
update pai_reclamation
set nbrec_abonne_brut=nbrec_abonne
,nbrec_diffuseur_brut=nbrec_diffuseur
where type_id<>1;

-- 3202 enregistrement datant de 2014
update pai_reclamation pr
inner join pai_tournee pt on pr.tournee_id=pt.id
set nbrec_abonne_brut=nbrec_abonne
where (nbrec_abonne>nbrec_abonne_brut)


select * from pai_reclamation where tournee_id=881879
select * from pai_reclamation where tournee_id=1556833
select * from crm_detail where pai_tournee_id=881879

select e.depot,e.flux,e.nom,e.prenom1,sum(nbrec_abonne),sum(nbrec_diffuseur)
from pai_reclamation pr
inner join pai_tournee pt on pr.tournee_id=pt.id
inner join v_employe e on pt.employe_id=e.employe_id and pt.date_distrib between e.date_debut and e.date_fin
where pt.date_distrib<'2016-01-01' and pr.date_creation>='2016-12-20'
and (nbrec_abonne<>0 or nbrec_diffuseur<>0)
group by e.employe_id
order by 5 desc


select e.depot,e.flux,e.nom,e.prenom1,nbrec_abonne,nbrec_diffuseur,pt.date_distrib,pr.date_creation
from pai_reclamation pr
inner join pai_tournee pt on pr.tournee_id=pt.id
inner join v_employe e on pt.employe_id=e.employe_id and pt.date_distrib between e.date_debut and e.date_fin
inner join utilisateur u on pr.utilisateur_id=u.id
where pt.date_distrib<'2016-09-01' and pr.date_creation>='2016-12-20'
and (nbrec_abonne<>0 or nbrec_diffuseur<>0)
order by 7 

select *
from pai_reclamation pr
inner join pai_tournee pt on pr.tournee_id=pt.id
inner join v_employe e on pt.employe_id=e.employe_id and pt.date_distrib between e.date_debut and e.date_fin
inner join utilisateur u on pr.utilisateur_id=u.id
where pt.date_distrib<'2016-09-01' and pr.date_creation>='2016-12-29'
and (nbrec_abonne<>0 or nbrec_diffuseur<>0)
order by 7 

select * from crm_detail where pai_tournee_id in (838309,840613,888398)
