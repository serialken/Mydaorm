select * from tmp_peretti
select * from depot

INSERT INTO mroad.groupe_tournee(depot_id, flux_id, utilisateur_id, code, heure_debut, heure_fin, date_creation, date_modif, libelle) 
VALUES (22, 1, 0, 'UB', '06:00', null, now(), null, 'Groupe UB');
INSERT INTO mroad.groupe_tournee(depot_id, flux_id, utilisateur_id, code, heure_debut, heure_fin, date_creation, date_modif, libelle) 
VALUES (22, 1, 0, 'UC', '06:00', null, now(), null, 'Groupe UC');


INSERT INTO mroad.modele_tournee(groupe_id, utilisateur_id, date_creation, actif, typetournee_id, employe_id, numero, code, codeDCS, libelle) 
select gt.id, 0, sysdate(), false, 0, null, substr(t.code,7,3), t.code, null, t.libelle
-- select *
from tmp_peretti t
inner join groupe_tournee gt where gt.code=substr(t.code,5,2)

INSERT INTO mroad.modele_tournee_jour
(tournee_id, jour_id, employe_id, transport_id, utilisateur_id, date_debut, date_fin, valrem, nbkm, nbkm_paye, date_creation, depart_depot, retour_depot, tauxhoraire) 
select mt.id, rj.id, null, 4, 15, '2016-09-21', '2999-01-01', null, 0, 0, sysdate(), 1, 1, 0
from tmp_peretti b
inner join modele_tournee mt on b.code=mt.code
inner join ref_jour rj on rj.id=1
where b.jour='Dimanche'

INSERT INTO mroad.modele_tournee_jour
(tournee_id, jour_id, employe_id, transport_id, utilisateur_id, date_debut, date_fin, valrem, nbkm, nbkm_paye, date_creation, depart_depot, retour_depot, tauxhoraire) 
select mt.id, rj.id, null, 4, 15, '2016-09-21', '2999-01-01', null, 0, 0, sysdate(), 1, 1, 0
from tmp_peretti b
inner join modele_tournee mt on b.code=mt.code
inner join ref_jour rj on rj.id<>1
where b.jour='Semaine'

select * 
from modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id
where gt.depot_id=22

update modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id
set mtj.transport_id=3
where gt.depot_id=22

delete from employe where matricule like 'EX%'
insert into employe(nom,prenom1,matricule,date_creation,civilite_id) values('DIFFUSEUR','EXTERNE 1','DE000001XX',now(),1);
insert into employe(nom,prenom1,matricule,date_creation,civilite_id) values('DIFFUSEUR','EXTERNE 2','DE000002XX',now(),1);

INSERT INTO mroad.emp_pop_depot (employe_id, depot_id, emploi_id, societe_id, date_debut, date_fin, rc, flux_id, typetournee_id, typeurssaf_id, typecontrat_id, population_id, heure_debut, nbheures_garanties, rcoid, dRC, fRC, date_creation, date_modif, km_paye) 
select e.id, 22, -2, 0, '2016-09-21', '2999-01-01', '', 1, 0, 0, 0, -2, '06:00', null, '', '2016-09-21', '2999-01-01', now(), null, 0
from employe e
where e.matricule like 'DE%';

INSERT INTO mroad.emp_cycle (employe_id, date_debut, date_fin, cyc_cod, lundi, mardi, mercredi, jeudi, vendredi, samedi, dimanche, date_creation, date_modif) 
select e.id, '2016-09-21', '2999-01-01', null, 1, 1, 1, 1, 1, 1, 0, now(), null
from employe e
where e.matricule like 'DE%';

