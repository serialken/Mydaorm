/*
      UPDATE modele_tournee_jour mtj
      inner join modele_tournee mt on mtj.tournee_id=mt.id
    SET valrem_calculee = modele_valrem(mtj.date_debut,mt.typetournee_id,mtj.duree_geo,mtj.nbcli_geo,mtj.qte_geo)
    ,	duree = modele_duree(mtj.date_debut,mt.typetournee_id,mtj.valrem,mtj.nbcli_geo,mtj.qte_geo)
    ;
call mod_valide_tournee(@id,null,null,null);
*/

select * from modele_tournee_jour where date_fin is null
select * from modele_tournee_jour where date_fin<date_debut
-- chevauchement
select mtj1.id,mtj2.id,mtj1.code,mtj1.date_debut,mtj1.date_fin,mtj2.date_debut,mtj2.date_fin
from modele_tournee_jour mtj1
inner join modele_tournee_jour mtj2 on mtj1.id<>mtj2.id and mtj1.code=mtj2.code
where mtj1.date_fin>=mtj2.date_debut and mtj1.date_debut<=mtj2.date_fin
and mtj1.date_debut<=mtj2.date_debut
order by mtj1.code,mtj1.date_debut

select * from modele_tournee_jour where code like '023NHS051%' order by code,date_debut
delete from modele_tournee_jour where code like '023NHS051%' and date_fin<date_debut


select * from modele_tournee_jour where date_fin is null
select * from modele_tournee_jour where code='031NLU005LU'
update modele_tournee_jour
set date_fin='2016-07-20'
 where id=50227
update modele_tournee_jour
set date_fin='2016-08-14'
 where id=64849
 commit