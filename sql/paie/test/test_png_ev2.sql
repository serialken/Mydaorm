

select * from employe where matricule='7000310300';

select * from pai_ev_tournee where employe_id=293;

select * 
from pai_ev_produit
where tournee_id=58063;

select * from pai_tournee where employe_id=822 order by employe_id;
select * from pai_ev_tournee where employe_id=822 order by employe_id;
select * from pai_ev_produit;
select * from employe where matricule='7000002600';
select * from pai_ev_tournee where employe_id=1324;
select * from pai_ev_tournee where typejour_id=3;
select * from pai_tournee where typejour_id=3;
select * from pai_ev_tournee where duree_nuit_modele is not null;
select * from pai_ev_tournee where remuneration is null;
select * from emp_pop_depot where employe_id=197;
select * from pai_ev_serpentin;

SELECT distinct 'ev_calcul_supplement',CONCAT('Poste de paie ',case when d.typeurssaf_id=1 THEN 'BF' ELSE 'BDC' END,' non renseigné pour ',p.libelle,'(',p.id,')')
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND e.depot_id=t.depot_id AND t.date_distrib BETWEEN e.dCtr AND e.f
  INNER JOIN pai_ev_produit d ON d.tournee_id=t.id
  INNER JOIN produit p ON d.produit_id=p.id
  LEFT OUTER JOIN pai_ref_postepaie_supplement s ON s.produit_id=d.produit_id
  WHERE p.type_id IN (2,3)
  AND d.qte<>0
  AND e.typetournee_id=1
  AND ( d.typeurssaf_id=1 and (poste_bf='' or poste_bf is null)
  OR    d.typeurssaf_id=2 and (poste_bdc='' or poste_bdc is null))
  ;
  
  