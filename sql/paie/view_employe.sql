drop VIEW IF EXISTS v_employe;
CREATE VIEW v_employe AS 
select e.matricule,e.nom,e.prenom1,e.prenom2
,epd.rc,epd.date_debut,epd.date_fin
,f.code as flux, d.code AS depot
,rp.code AS pop,rp.libelle AS libpop
,re.code AS emp,re.libelle AS libemp
,e.id AS employe_id
,epd.depot_id,epd.flux_id
,epd.rcoid,epd.societe_id,epd.population_id,epd.emploi_id
,epd.km_paye
from employe e 
join emp_pop_depot epd on epd.employe_id = e.id
join depot d on epd.depot_id = d.id
join ref_flux f on epd.flux_id = f.id
join ref_population rp on epd.population_id = rp.id
join ref_emploi re on epd.emploi_id = re.id
;
