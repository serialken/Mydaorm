create table modele_tournee_jour_20160930 as select * from modele_tournee_jour;
ALTER TABLE modele_tournee_jour ADD etalon NUMERIC(7, 6) NOT NULL, ADD etalon_moyen NUMERIC(7, 6) NOT NULL;

select * from modele_tournee_jour where coalesce(tauxhoraire,0)=0;
select * from modele_tournee_jour where coalesce(valrem,0)=0;
select * from modele_tournee_jour where date_fin='2999-01-01'

select * from modele_tournee_jour where coalesce(tauxhoraire,0)=0
-- Controle du taux horaire sur date_debut
select * from modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id
inner join ref_typetournee rtt_new on gt.flux_id=rtt_new.id
inner join pai_ref_remuneration prr_new on rtt_new.societe_id=prr_new.societe_id AND rtt_new.population_id=prr_new.population_id AND mtj.date_debut=prr_new.date_debut
where mtj.tauxhoraire<>prr_new.valeur;
-- Controle du taux horaire sur date_fin
select * from modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id
inner join ref_typetournee rtt_new on gt.flux_id=rtt_new.id
inner join pai_ref_remuneration prr_new on rtt_new.societe_id=prr_new.societe_id AND rtt_new.population_id=prr_new.population_id AND mtj.date_fin=prr_new.date_debut
where mtj.tauxhoraire<>prr_new.valeur;

update modele_tournee_jour set tauxhoraire=9.67 where coalesce(tauxhoraire,0)=0 and code like '050%';

update modele_tournee_jour set valrem=0 where valrem is null;
update modele_tournee_jour set valrem_moyen=valrem where valrem_moyen is null;

update modele_tournee_jour set etalon=round(valrem/tauxhoraire,6),etalon_moyen=round(valrem_moyenne/tauxhoraire,6);
-- Controle etalonxtauxhoraire = valrem
select round(etalon*tauxhoraire,5),valrem,mtj.* from modele_tournee_jour mtj where round(etalon*tauxhoraire,5)<>valrem;
select round(etalon*tauxhoraire,5),valrem,mtj.* from modele_tournee_jour mtj where valrem=0.1852;

select * from modele_tournee_jour where valrem is null or etalon is null;

update modele_tournee_jour set nbcli=nbcli_geo where nbcli is null;
select * from modele_tournee_jour where coalesce(nbcli,0)<>coalesce(nbcli_geo,0);
select * from modele_tournee_jour where nbcli is null and duree<>'00:00';

update modele_tournee_jour set duree=duree_geo where duree is null;
select * from modele_tournee_jour where coalesce(duree,0)<>coalesce(duree_geo,0);
select * -- distinct mt.code
from modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
where (mtj.duree is null or mtj.duree='00:00') and mtj.date_fin>=now()
and valrem not in (0.00000,0.00001);

select coalesce(duree,'00:00') as duree
,sec_to_time(round(coalesce(nbcli,0)*etalon*3600)) as duree_calculee
,mtj.* 
from modele_tournee_jour mtj  
where abs(time_to_sec(coalesce(duree,'00:00'))-round(coalesce(nbcli,0)*etalon*3600))>0
and mtj.date_fin>=now()
;
-- maj des modele passé (une fois le trigger supprimé)
update modele_tournee_jour mtj 
set duree=sec_to_time(round(coalesce(nbcli,0)*etalon*3600))
where mtj.date_fin>=now()
and abs(time_to_sec(coalesce(duree,'00:00'))-round(coalesce(nbcli,0)*etalon*3600))>0
;

-- duree moyenne par client
select
valrem,
etalon,
sec_to_time(etalon*3600)
,mtj.*
from modele_tournee_jour mtj