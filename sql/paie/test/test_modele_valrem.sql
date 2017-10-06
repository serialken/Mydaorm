select * from client_a_servir_logist where date_distrib='2016-01-07'

-- chevauchement date_debut / date_fin
select mtj1.id,mtj1.code,mtj1.date_debut,mtj1.date_fin,mtj2.id,mtj2.code,mtj2.date_debut,mtj2.date_fin
from modele_tournee_jour mtj1
inner join modele_tournee_jour mtj2 on mtj1.code=mtj2.code and mtj1.id<>mtj2.id
where mtj1.date_debut between mtj2.date_debut and mtj2.date_fin

-- Valeur de rémunération incorrecte
select *
from modele_tournee_jour mtj
inner join modele_tournee mt on mt.id=mtj.tournee_id
inner join groupe_tournee gt on gt.id=mt.groupe_id
inner join ref_typetournee rtt on gt.flux_id=rtt.id
inner join pai_ref_remuneration prr on rtt.societe_id=prr.societe_id AND rtt.population_id=prr.population_id AND mtj.date_debut between prr.date_debut and prr.date_fin
where mtj.tauxhoraire<>prr.valeur;

-- select * from modele_tournee_jour where code like '042JXF305%' order by date_debut
-- pb sur valrem
select *
from pai_tournee pt
inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id -- and pt.date_distrib between mtj.date_debut and mtj.date_fin
and coalesce(pt.valrem,0)<>coalesce(mtj.valrem,0)
and pt.date_extrait is null

-- pd sur modele et date
select pt.date_distrib,pt.code,left(pt.code,11),mtj.date_debut,mtj.date_fin,mtj.code,mtj.date_modif,pt.modele_tournee_jour_id,mtj.id,pt.*
from pai_tournee pt
left outer join modele_tournee_jour mtj on substr(pt.code,1,11)=mtj.code and  pt.date_distrib between mtj.date_debut and mtj.date_fin
where not exists(select null from modele_tournee_jour mtj where pt.modele_tournee_jour_id=mtj.id and pt.date_distrib between mtj.date_debut and mtj.date_fin)

-- maj du modele
update pai_tournee pt
inner join modele_tournee_jour mtj on substr(pt.code,1,11)=mtj.code and  pt.date_distrib between mtj.date_debut and mtj.date_fin
set pt.modele_tournee_jour_id=mtj.id
where not exists(select null from modele_tournee_jour mtj where pt.modele_tournee_jour_id=mtj.id and pt.date_distrib between mtj.date_debut and mtj.date_fin)

-- maj valrem
update pai_tournee pt
inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id -- and pt.date_distrib between mtj.date_debut and mtj.date_fin
set pt.valrem=mtj.valrem
where pt.valrem<>mtj.valrem
and pt.date_extrait is null

call recalcul_horaire(@validation_id,null,null,null,NULL);

select * from modele_tournee_jour where code like '042JXL047%' 

select * from modele_tournee_jour where code like '004NAM001%' order by code,date_debut
delete from modele_tournee_jour where code like '042JXF001%' and date_debut='2015-01-23'
update modele_tournee_jour set date_fin='2015-01-20' where code like '042JXF001%' and date_fin='2015-01-22'

select * from feuille_portage where tournee_jour_id in (select id from modele_tournee_jour where code like '033JME020%' and date_debut='2014-12-22')
select * from modele_tournee_jour where id in (17000,17001,17002,17004)
update  cptr_reception set tournee_jour_id=33662 where tournee_jour_id=17000;
update  cptr_reception set tournee_jour_id=33663 where tournee_jour_id=17001;
update  cptr_reception set tournee_jour_id=33664 where tournee_jour_id=17002;
update  cptr_reception set tournee_jour_id=33666 where tournee_jour_id=17004;
select * from pai_tournee where code like '042JXL047%'

select * from modele_tournee_jour where code like '033JME016%' 
select * from modele_tournee_jour where date_debut='2015-01-23'
select * from modele_tournee_jour where date_debut>=date_fin


select substr(code,1,9) from modele_tournee_jour 
where date_debut>='2015-01-01'
group by substr(code,1,9),date_debut
having count(distinct valrem)>1
order by 1

select * from modele_tournee_jour where code like '040NRQ073%' order by date_debut,code
select * from modele_tournee_jour where code like '028NJT060%' order by date_debut,code
select * from modele_tournee_jour where code like '028NJB068%' order by date_debut,code


028NJN016   
028NJN024 
028NJB068      

update modele_tournee_jour set valrem=0.18087 where id=33689
update modele_tournee_jour set valrem=0.23139 where id=33689
