select * from tmp_vcp
alter table tmp_vcp add employe_id int
alter table tmp_vcp add tournee_id int
insert into tmp_vcp(nom,tournee,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche) values('DUPRE DAVID','B4','X','X','X','X','X','X',null);
insert into tmp_vcp(nom,tournee,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche) values('DUPRE DAVID','B10','X','X','X','X','X','X',null);
insert into tmp_vcp(nom,tournee,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche) values('KEKELA SHAMIMI','B14','X','X','X','X','X','X',null);
insert into tmp_vcp(nom,tournee,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche) values('BONNAY FRANCK','C16','X','X','X','X','X','X',null);
insert into tmp_vcp(nom,tournee,dimanche) values('DUFOSSE GERARD','B33','X');
insert into tmp_vcp(nom,tournee,dimanche) values('EL AMMARI MOHAMED','C38','X');
insert into tmp_vcp(nom,tournee,dimanche) values('EL AMMARI MOHAMED','C40','X');
insert into tmp_vcp(nom,tournee,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche) values('BERTRAND Philippe','C14','X','X','X','X','X','X',null);
insert into tmp_vcp(nom,tournee,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche) values('LANVIN Franck','B15','X','X','X','X','X','X',null);
insert into tmp_vcp(nom,tournee,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche) values('Jean-Pierre RORTAIS','B18','X','X','X','X',null,null,null);

select * from employe where matricule like 'MR%'

select * from depot
update tmp_vcp v
inner join employe e on e.matricule like 'MR%' and trim(v.nom)=upper(concat(trim(e.prenom1),' ',trim(e.nom)))
set v.employe_id=e.id
where v.employe_id is null
update tmp_vcp v
inner join employe e on e.matricule like 'DE%' and trim(v.nom)=concat(trim(e.nom),' ',trim(e.prenom1))
set v.employe_id=e.id
where v.employe_id is null

select * from tmp_vcp where employe_id is null
update tmp_vcp v set v.employe_id=10323 where nom='RIAD NOUI ';
update tmp_vcp v set v.employe_id=10331 where nom='LEMAIRE AUDREY';
update tmp_vcp v set v.employe_id=10325 where nom='ANTHONIPILLAI';
update tmp_vcp v set v.employe_id=10320 where nom='ADGHIR BEHLOUL';
update tmp_vcp v set v.employe_id=10329 where nom='AKARIA CHEMLOUL';
update tmp_vcp v set v.employe_id=10330 where nom='JEAN PIERRE RORTAIS';
update tmp_vcp v set v.employe_id=10298 where nom='LANVIN /DIFFUSEUR';
update tmp_vcp v set v.employe_id=10317 where nom='OUMAR M''BODJI';
update tmp_vcp v set v.employe_id=10297 where nom='BERTRAND / DIFFUSEUR';
update tmp_vcp v set v.employe_id=10384 where nom='LOUAGEUR MONAHEME TANTAN';
update tmp_vcp v set v.employe_id=10356 where nom='DUPRE DAVID';
update tmp_vcp v set v.employe_id=10360 where nom='KEKELA SHAMIMI';
update tmp_vcp v set v.employe_id=10359 where nom='BONNAY FRANCK';
update tmp_vcp v set v.employe_id=10355 where nom='DUFOSSE GERARD';
update tmp_vcp v set v.employe_id=10362 where nom='EL AMMARI MOHAMED';

select * from employe where matricule like 'MR%' order by nom;
select * from employe where matricule like 'DE%' order by nom;
select * from v_employe where depot='050' and emp<>'VCP' order by nom;

select v.*,concat('%',left(v.tournee,1),'%',substr(v.tournee,2,3)) from tmp_vcp v where tournee_id is null
update tmp_vcp v
inner join modele_tournee mt on mt.code like concat('%',left(v.tournee,1),'%',substr(v.tournee,2,3))
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
set v.tournee_id=mt.id
where v.tournee_id is null

update modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set mtj.employe_id=v.employe_id
where dimanche='X' and mtj.jour_id=1 
and mtj.employe_id is null;
update modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set mtj.employe_id=v.employe_id
where lundi='X' and mtj.jour_id=2 
and mtj.employe_id is null;
update modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set mtj.employe_id=v.employe_id
where mardi='X' and mtj.jour_id=3 
and mtj.employe_id is null;
update modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set mtj.employe_id=v.employe_id
where mercredi='X' and mtj.jour_id=4 
and mtj.employe_id is null;
update modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set mtj.employe_id=v.employe_id
where jeudi='X' and mtj.jour_id=5 
and mtj.employe_id is null;
update modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set mtj.employe_id=v.employe_id
where vendredi='X' and mtj.jour_id=6 
and mtj.employe_id is null;
update modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set mtj.employe_id=v.employe_id
where samedi='X' and mtj.jour_id=7 
and mtj.employe_id is null;
select count(*) from tmp_vcp where lundi='X';

update pai_tournee pt
inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set pt.employe_id=mtj.employe_id
where pt.employe_id is null;

-- valrem
select * from modele_tournee_jour mtj inner join modele_tournee mt on mtj.tournee_id=mt.id inner join groupe_tournee gt on mt.groupe_id=gt.id where gt.depot_id=22

update modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set mtj.valrem=0.09,
mtj.valrem_moyen=0.09,
mtj.etalon=0.09/9.67,
mtj.etalon_moyen=0.09/9.67,
mtj.tauxhoraire=9.67
;
update modele_tournee_jour mtj
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set mtj.duree='02:00:00',
mtj.nbcli=round(time_to_sec(coalesce('02:00:00','00:00'))/etalon/3600)
;

select * 
from  pai_tournee pt
inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22;

update pai_tournee pt
inner join modele_tournee_jour mtj on pt.modele_tournee_jour_id=mtj.id
inner join modele_tournee mt on mtj.tournee_id=mt.id
inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=22
inner join tmp_vcp v on v.tournee_id=mt.id
set pt.valrem=0.09;

CALL mod_valide_tournee(@id,NULL,NULL,NULL);
CALL mod_valide_tournee_jour(@id,NULL,NULL,NULL,NULL);

    call recalcul_tournee_date_distrib(null, 22, 1);
    select * from v_employe where nom='KENOUZE'

id
10331
10485
10487
select * from emp_pop_depot where employe_id=10487