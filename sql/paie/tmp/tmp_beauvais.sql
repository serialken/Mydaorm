select * from v_employe where nom='GILLET'
select * from v_employe where employe_id= 855
select * from v_employe
SELECT * FROM mroad.pai_png_xrcautreactivit where relationcontrat='131126-16470300-001-000000000340'
SELECT * FROM mroad.pai_png_xrcautreactivit where relationcontrat='101228-10273299-001-000000016145'

select * from depot
select * from groupe_tournee where depot_id=23
update tmp_beauvais
set code=CONCAT('051NV',left(code_tournee,1),'0',right(code_tournee,2))

INSERT INTO mroad.modele_tournee(groupe_id, utilisateur_id, date_creation, actif, typetournee_id, employe_id, numero, codeDCS, libelle) 
select 535, 15, sysdate(), false, 1, null, concat('0',substr(code_tournee,2,2)), DCS, libelle_tournee
-- select *
from tmp_beauvais b

INSERT INTO mroad.modele_tournee_jour
(tournee_id, jour_id, employe_id, transport_id, utilisateur_id, date_debut, date_fin, valrem, nbkm, nbkm_paye, date_creation, ordre, depart_depot, retour_depot, tauxhoraire) 
select mt.id, rj.id, null, 4, 15, '2016-01-01', '2999-01-01', 0.0001, 0, 0, sysdate(), 1, 1, 1, 9.67
from tmp_beauvais b
inner join modele_tournee mt on b.code=mt.code
inner join ref_jour rj on rj.id=1
where b.jour='Dimanche'

INSERT INTO mroad.modele_tournee_jour
(tournee_id, jour_id, employe_id, transport_id, utilisateur_id, date_debut, date_fin, valrem, nbkm, nbkm_paye, date_creation, ordre, depart_depot, retour_depot, tauxhoraire) 
select mt.id, rj.id, null, 4, 15, '2016-01-01', '2999-01-01', 0.0001, 0, 0, sysdate(), 1, 1, 1, 9.67
from tmp_beauvais b
inner join modele_tournee mt on b.code=mt.code
inner join ref_jour rj on rj.id<>1
where b.jour='Semaine'

