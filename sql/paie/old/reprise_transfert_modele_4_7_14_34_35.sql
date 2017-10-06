select * from depot;
select * from utilisateur
create table modele_activite_20151106 as select * from modele_activite;
create table modele_tournee_jour_20151106 as select * from modele_tournee_jour;
create table modele_tournee_20151106 as select * from modele_tournee;
create table groupe_tournee_20151106 as select * from groupe_tournee;
create table reperage_20151106 as select * from reperage;

delete from modele_journal where flux_id=1 and depot_id in (4,7,14,34,35);
delete from modele_activite where flux_id=1 and depot_id in (4,7,14,34,35);
delete mtj from modele_tournee_jour mtj inner join modele_tournee mt on mtj.tournee_id=mt.id inner join groupe_tournee gt on mt.groupe_id=gt.id where flux_id=1 and depot_id in (4,7,14,34,35);
update reperage set tournee_id=null where tournee_id in (select mt.id from modele_tournee mt inner join groupe_tournee gt on mt.groupe_id=gt.id where flux_id=1 and depot_id in (4,7,14,34,35));
delete mt from modele_tournee mt inner join groupe_tournee gt on mt.groupe_id=gt.id where flux_id=1 and depot_id in (4,7,14,34,35);
delete from pai_heure where groupe_id in (select id from groupe_tournee where flux_id=1 and depot_id in (4,7,14,34,35));
delete from groupe_tournee where flux_id=1 and depot_id in (4,7,14,34,35);
commit;

commit;


commit;



commit;

select * from modele_tournee where id=2823 --1-2015-10-21
select * from modele_tournee where id=2849-1-2015-10-21
select * from modele_tournee where id=2876-6-2015-10-21

/*
Corriger les modèles
013NDH061DI
013NDH089DI
013NDZ024VE
*/


CALL mod_valide_tournee(@id,NULL,NULL,NULL);
CALL mod_valide_tournee_jour(@id,NULL,NULL,NULL,NULL);



