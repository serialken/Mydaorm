insert into modele_tournee_jour(tournee_id, jour_id, employe_id, transport_id, utilisateur_id, date_debut, date_fin, code, duree_geo, dureehp_debut_geo, dureehp_fin_geo, qte_geo, nbcli_geo, nbkm_geo, nbadr_geo, valrem_calculee, valrem, duree, dureeHP, nbkm, nbkm_paye, date_creation, date_modif, valide, date_validation, nbkm_hp_debut_geo, nbkm_hp_fin_geo)
select mt.id, mtj.jour_id, null, mtj.transport_id, mtj.utilisateur_id, mtj.date_debut, mtj.date_fin, concat(mt.code,rj.code), mtj.duree_geo, mtj.dureehp_debut_geo, mtj.dureehp_fin_geo, mtj.qte_geo, mtj.nbcli_geo, mtj.nbkm_geo, mtj.nbadr_geo, mtj.valrem_calculee, mtj.valrem, mtj.duree, mtj.dureeHP, mtj.nbkm, mtj.nbkm_paye, mtj.date_creation, mtj.date_modif, mtj.valide, mtj.date_validation, mtj.nbkm_hp_debut_geo, mtj.nbkm_hp_fin_geo
from modele_tournee_jour mtj
inner join ref_jour rj on mtj.jour_id=rj.id
,modele_tournee mt
where mtj.code like '034NND020%'
and mt.code like '034NND022%';
insert into modele_tournee_jour(tournee_id, jour_id, employe_id, transport_id, utilisateur_id, date_debut, date_fin, code, duree_geo, dureehp_debut_geo, dureehp_fin_geo, qte_geo, nbcli_geo, nbkm_geo, nbadr_geo, valrem_calculee, valrem, duree, dureeHP, nbkm, nbkm_paye, date_creation, date_modif, valide, date_validation, nbkm_hp_debut_geo, nbkm_hp_fin_geo)
select mt.id, mtj.jour_id, null, mtj.transport_id, mtj.utilisateur_id, mtj.date_debut, mtj.date_fin, concat(mt.code,rj.code), mtj.duree_geo, mtj.dureehp_debut_geo, mtj.dureehp_fin_geo, mtj.qte_geo, mtj.nbcli_geo, mtj.nbkm_geo, mtj.nbadr_geo, mtj.valrem_calculee, mtj.valrem, mtj.duree, mtj.dureeHP, mtj.nbkm, mtj.nbkm_paye, mtj.date_creation, mtj.date_modif, mtj.valide, mtj.date_validation, mtj.nbkm_hp_debut_geo, mtj.nbkm_hp_fin_geo
from modele_tournee_jour mtj
inner join ref_jour rj on mtj.jour_id=rj.id
,modele_tournee mt
where mtj.code like '034NND040%'
and mt.code like '034NND041%';


update modele_tournee_jour 
set modele_tournee_jour.valrem=0.001 
,duree_geo='00:00'
, dureehp_debut_geo='00:00'
, dureehp_fin_geo='00:00'
, qte_geo=0
, nbcli_geo=0
, nbkm_geo=0
, nbadr_geo=0
,nbkm_hp_debut_geo=0
, nbkm_hp_fin_geo=0
, nbkm_paye=0
, nbkm=0
, valrem=0
where code like '034NND022%' or code like '034NND041%';

update modele_tournee_jour
set jour_id=5
,code='034NNW001JE'
where code='034NNW001JE';


commit;