call pai_alimente_activite(1,curdate());
select * from pai_activite;
call pai_alimente_tournee(1,curdate());
select * from pai_tournee where date_distrib =curdate();
select * from pai_prd_tournee where tournee_id in (select id from pai_tournee where date_distrib =curdate());

delete from pai_activite;
delete from pai_tournee;
commit;
select * from modele_activite where activite_id not in (select id from ref_activite);

select * from pai_ref_mois order by anneemois;

call pai_valide_activite(@id,null,null);
call pai_valide_tournee(@id,null,null);
call pai_valide_produit(@id,null,null);
call pai_valide_rh(@id,null,null);

delete from pai_journal
select rubrique,code,msg,count(*) from pai_journal group by rubrique,code,msg
select date_debut from pai_mois

select count(*) from pai_tournee
select count(*) from pai_activite
	delete from pai_journal
	where rubrique='AC' and code='INC'

	and date_distrib>=(select date_debut from pai_mois)
	;
  commit;
 select * from pai_journal where msg='';
 
 	SELECT DISTINCT t.depot_id,t.flux_id,m.anneemois,t.date_distrib,0,'SP','POI','Poids non saisi',NULL,NULL,NULL,ppt.produit_id
  from pai_prd_tournee ppt
  inner join pai_tournee t on ppt.tournee_id=t.id
	INNER JOIN pai_mois m ON t.date_distrib>=m.date_debut
  inner join produit p on ppt.produit_id=p.id
  inner join prd_caract pc on p.type_id=pc.produit_type_id and pc.code='POIDS'
  where (t.typetournee_id=1 and p.type_id in (2,3) or t.typetournee_id<>1)
--  and t.typetournee_id=2
  and not exists(select null from prd_caract_jour pcj where ppt.produit_id=pcj.produit_id and pcj.prd_caract_id=pc.id and t.date_distrib=pcj.date_distrib and valeur_int is not null)
  and not exists(select null from prd_caract_groupe pcg where ppt.produit_id=pcg.produit_id and pcg.prd_caract_id=pc.id and t.groupe_id=pcg.groupe_id and t.date_distrib=pcg.date_distrib and valeur_int is not null)
  ;