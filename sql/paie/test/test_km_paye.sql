select 1 from ref_transport
left outer join ref_activite ra on ra.id=1

select * from modele_activite ma
left outer join ref_activite ra on ra.id=ma.activite_id
left outer join ref_transport rt on rt.id=ma.transport_id
where ma.nbkm_paye<>case when coalesce(rt.km_paye,1) and coalesce(ra.km_paye,1) then ma.nbkm_paye else 0 end

select mtj.code,rt.libelle,mtj.nbkm_paye
from modele_tournee_jour mtj
left outer join ref_transport rt on rt.id=mtj.transport_id
where mtj.nbkm_paye<>case when coalesce(rt.km_paye,1) then mtj.nbkm_paye else 0 end
and date_fin>=now()
order by mtj.code

select e.depot,e.flux,ma.date_distrib,ra.libelle,rt.libelle,e.nom,e.prenom1,ma.nbkm_paye
from pai_activite ma
inner join v_employe e on ma.employe_id=e.employe_id and ma.date_distrib between e.date_debut and e.date_fin
left outer join ref_activite ra on ra.id=ma.activite_id
left outer join ref_transport rt on rt.id=ma.transport_id
where ma.nbkm_paye<>case when coalesce(rt.km_paye,1) and coalesce(ra.km_paye,1) then ma.nbkm_paye else 0 end
and date_extrait is null
order by 1,2,3

select e.depot,e.flux,mtj.date_distrib,mtj.code,rt.libelle,e.nom,e.prenom1,mtj.nbkm_paye
from pai_tournee mtj
inner join v_employe e on mtj.employe_id=e.employe_id and mtj.date_distrib between e.date_debut and e.date_fin
left outer join ref_transport rt on rt.id=mtj.transport_id
where mtj.nbkm_paye<>case when coalesce(rt.km_paye,1) then mtj.nbkm_paye else 0 end
and date_extrait is null
order by 1,2,3


select * from ref_activite