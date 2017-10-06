CREATE TABLE ref_civilite (id INT NOT NULL, code VARCHAR(4) NOT NULL, libelle VARCHAR(32) NOT NULL, UNIQUE INDEX UNIQ_AA5BDD5177153098 (code), PRIMARY KEY(id))
DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
insert into ref_civilite values (1,'Mr',	'Monsieur');
insert into ref_civilite values (2,'Mme',	'Madame');
insert into ref_civilite values (3,'Mlle',	'Mademoiselle');

ALTER TABLE employe 
ADD civilite_id INT NOT NULL, 
ADD secu_numero VARCHAR(13) DEFAULT NULL, 
ADD secu_cle VARCHAR(2) DEFAULT NULL, 
ADD naissance_date DATETIME DEFAULT NULL, 
ADD naissance_lieu VARCHAR(5) DEFAULT NULL, 
ADD naissance_pays VARCHAR(3) DEFAULT NULL;
update employe set civilite_id=1;
ALTER TABLE employe ADD CONSTRAINT FK_F804D3B939194ABF FOREIGN KEY (civilite_id) REFERENCES ref_civilite (id);
CREATE INDEX IDX_F804D3B939194ABF ON employe (civilite_id);

ALTER TABLE ref_activite CHANGE affichage_modele affichage_modele TINYINT(1) DEFAULT '0' NOT NULL, CHANGE km_paye km_paye TINYINT(1) DEFAULT '1' NOT NULL, CHANGE actif actif TINYINT(1) DEFAULT '1' NOT NULL, CHANGE est_hors_presse est_hors_presse TINYINT(1) DEFAULT '0' NOT NULL, CHANGE est_garantie est_garantie TINYINT(1) DEFAULT '0' NOT NULL, CHANGE est_1mai est_1mai TINYINT(1) DEFAULT '0' NOT NULL, CHANGE est_pleiades est_pleiades TINYINT(1) DEFAULT '0' NOT NULL, CHANGE est_hors_travail est_hors_travail TINYINT(1) DEFAULT '0' NOT NULL, CHANGE est_badge est_badge TINYINT(1) DEFAULT '1' NOT NULL, CHANGE est_JTPX est_JTPX TINYINT(1) DEFAULT '0' NOT NULL;

ALTER TABLE ref_population ADD est_badge TINYINT(1) NOT NULL;
ALTER TABLE ref_population CHANGE est_badge est_badge TINYINT(1) DEFAULT '0' NOT NULL, CHANGE majoration majoration NUMERIC(5, 2) DEFAULT '0' NOT NULL, CHANGE majoration_df majoration_df NUMERIC(5, 2) DEFAULT '0' NOT NULL, CHANGE majoration_dfq majoration_dfq NUMERIC(5, 2) DEFAULT '0' NOT NULL, CHANGE majoration_nuit majoration_nuit NUMERIC(5, 2) DEFAULT '0' NOT NULL;
update ref_population set est_badge=1 where emploi_id in (1,2);

INSERT into ref_emp_societe(id,code,libelle) values(0,'IND','Indépendant');
INSERT INTO ref_typeurssaf(id, code, libelle) VALUES (0, 'N', 'Non Applicable');
INSERT INTO ref_typecontrat(id, code, libelle) VALUES (0, 'IND', 'Indépendant');

INSERT INTO ref_emploi(id,code,libelle,codeNG,affichage_modele_tournee,affichage_modele_activite,paie,prime)
values (-1,'VCP','Vendeur Colporteur de Presse','VCPVCP',false,false,false,false);

INSERT INTO mroad.ref_population(id,emploi_id, code, libelle, typecontrat_id, typeurssaf_id, typetournee_id, majoration, majoration_df, majoration_dfq, majoration_nuit, pop_paie_id) 
VALUES (-1, -1, 'VCPVCP', 'Vendeur Colporteur de Presse', 0, 0, 1, 0, 0, 0, 0, -1);

INSERT INTO mroad.ref_typetournee(id, code, libelle, societe_id, population_id) VALUES (0, 'VCP', 'Vcp', 0, -1);
update ref_population set typetournee_id=0 where id=-1;

ALTER TABLE ref_emp_societe CHANGE id id INT AUTO_INCREMENT NOT NULL;
ALTER TABLE ref_population CHANGE id id INT AUTO_INCREMENT NOT NULL;

 
CREATE TABLE `emp_banque` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employe_id` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `date_creation` datetime NOT NULL,
  `date_modif` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_emp_banque` (`employe_id`,`date_debut`),
  KEY `IDX_DC0729A81B65293` (`employe_id`),
  CONSTRAINT `FK_DC0729A81B65293` FOREIGN KEY (`employe_id`) REFERENCES `employe` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `emp_adresse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employe_id` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `date_creation` datetime NOT NULL,
  `date_modif` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_emp_adresse` (`employe_id`,`date_debut`),
  KEY `IDX_DC0729A81B65294` (`employe_id`),
  CONSTRAINT `FK_DC0729A81B65294` FOREIGN KEY (`employe_id`) REFERENCES `employe` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*
delete from emp_cycle where employe_id in (select id from employe where matricule like 'MR%')
delete from emp_banque where employe_id in (select id from employe where matricule like 'MR%')
delete from emp_adresse where employe_id in (select id from employe where matricule like 'MR%')
delete from emp_pop_depot where employe_id in (select id from employe where matricule like 'MR%')
delete from pai_incident where employe_id in (select id from employe where matricule like 'MR%')
delete from pai_majoration where employe_id in (select id from employe where matricule like 'MR%')
delete from employe where matricule like 'MR%'
*/
select * from tmp_vcp_montataire
alter table tmp_vcp_montataire add civilite_id int
alter table tmp_vcp_montataire add code_lieu_naissance varchar(5)
alter table tmp_vcp_montataire add code_pays_naissance int

update tmp_vcp_montataire set civilite_id=case when civilite='Monsieur' then 1 else 2 end
update tmp_vcp_montataire t
inner join pai_ref_insee_commune c on t.`lieunaissance (code postal)`=c.libelle
set code_lieu_naissance=c.code

update tmp_vcp_montataire t
inner join pai_ref_insee_pays p on t.paysnaissance=p.pays
set code_pays_naissance=p.code
where t.paysnaissance<>'France'

set @rownum=0;
update tmp_vcp_montataire
set mat=concat('MR',lpad(@rownum:=@rownum+1,6,'0'),'XX');

insert into employe(nom,prenom1,matricule,date_creation,civilite_id,secu_numero,secu_cle,naissance_date,naissance_lieu,naissance_pays)
select t.nom,t.prénom,t.mat,now(),t.civilite_id,t.nsecu,t.clesecu,t.datenaissance,code_lieu_naissance,code_pays_naissance
from tmp_vcp_montataire t
;
alter table tmp_vcp_montataire add employe_id int;
update tmp_vcp_montataire t set employe_id=(select id from employe e where t.mat=e.matricule);


INSERT INTO mroad.emp_pop_depot (employe_id, depot_id, emploi_id, societe_id, date_debut, date_fin, rc, flux_id, typetournee_id, typeurssaf_id, typecontrat_id, population_id, heure_debut, nbheures_garanties, rcoid, dRC, fRC, date_creation, date_modif, km_paye) 
select t.employe_id, 22, -1, 0, '2016-09-21', '2999-01-01', '', 1, 0, 0, 0, -1, '06:00', null, '', '2016-09-21', '2999-01-01', now(), null, 0
from tmp_vcp_montataire t;

select * from emp_pop_depot epd inner join employe e on epd.employe_id=e.id where e.matricule like 'MR%';

alter table tmp_vcp_montataire add dimanche tinyint(1) default 0 not null;
alter table tmp_vcp_montataire add lundi tinyint(1) default 0 not null;
alter table tmp_vcp_montataire add mardi tinyint(1) default 0 not null;
alter table tmp_vcp_montataire add mercredi tinyint(1) default 0 not null;
alter table tmp_vcp_montataire add jeudi tinyint(1) default 0 not null;
alter table tmp_vcp_montataire add vendredi tinyint(1) default 0 not null;
alter table tmp_vcp_montataire add samedi tinyint(1) default 0 not null;
select * from tmp_vcp_montataire

update tmp_vcp_montataire set dimanche=1 where upper(cycle) like '%DIMANCHE%';
update tmp_vcp_montataire set lundi=1 where upper(cycle) like '%LUNDI%';
update tmp_vcp_montataire set mardi=1 where upper(cycle) like '%MARDI%';
update tmp_vcp_montataire set mercredi=1 where upper(cycle) like '%MERCREDI%';
update tmp_vcp_montataire set jeudi=1 where upper(cycle) like '%JEUDI%';
update tmp_vcp_montataire set vendredi=1 where upper(cycle) like '%VENDREDI%';
update tmp_vcp_montataire set samedi=1 where upper(cycle) like '%SAMEDI%';
update tmp_vcp_montataire set lundi=1,mardi=1,mercredi=1,jeudi=1,vendredi=1,samedi=1 where upper(cycle) like '%SEMAINE%'
update tmp_vcp_montataire set lundi=1,mardi=1,mercredi=1,jeudi=1,vendredi=1,samedi=1 where mat='MR000019XX';
delete from emp_cycle where employe_id=10327
INSERT INTO mroad.emp_cycle (employe_id, date_debut, date_fin, cyc_cod, lundi, mardi, mercredi, jeudi, vendredi, samedi, dimanche, date_creation, date_modif) 
select t.employe_id, '2016-09-21', '2999-01-01', null,lundi, mardi, mercredi, jeudi, vendredi, samedi, dimanche, now(), null
from tmp_vcp_montataire t
 where mat='MR000019XX';

select * from tmp_vcp_montataire
delete from emp_banque where employe_id in (select employe_id from tmp_vcp_montataire);
INSERT INTO mroad.emp_banque (employe_id, date_debut, date_fin, iban, bic, date_creation, date_modif) 
select t.employe_id, '2016-09-21', '2999-01-01', iban, bic, now(), null
from tmp_vcp_montataire t
where iban is not null;
INSERT INTO mroad.emp_banque (employe_id, date_debut, date_fin, iban, bic, date_creation, date_modif) 
values (10329, '2016-09-21', '2999-01-01', 'FR7618706000009751195484768', 'AGRIFRPP887', now(), null)

select e.nom,e.prenom1,b.*,length(b.iban) from employe e inner join emp_banque b on e.id=b.employe_id where length(b.iban)<>27;
select * from employe where matricule like 'MR%'
update employe set prenom2='(VCP)'  where matricule like 'MR%'
	
  -- ADRESSE
update emp_adresse set naturevoie='CITE' where naturevoie='CITÉ';
update emp_adresse set naturevoie='ALLE' where naturevoie='ALLÉ';
update tmp_vcp_montataire set nomvoie1=trim(nomvoie1)

delete from pai_ref_insee_commune where code='62790'
insert into pai_ref_insee_commune(code,libelle) values('60176','CREPY EN VALOIS');
update pai_ref_insee_commune set libelle='LA NEUVILLE-ROY' where id=25093;      
update tmp_vcp_montataire set ville=replace(ville,' ','-');
update tmp_vcp_montataire set ville='LA NEUVILLE-ROY' where ville='LA-NEUVILLE-ROY';
update tmp_vcp_montataire t set t.codeinsee=(select c.code from pai_ref_insee_commune c where t.ville=c.libelle)
select ville,t.* from tmp_vcp_montataire t where codeinsee is null;


delete from emp_adresse where employe_id in (select employe_id from tmp_vcp_montataire);
INSERT INTO mroad.emp_adresse (employe_id, date_debut, date_fin, numerovoie, naturevoie, cpltadr1, cpltadr2, nomvoie1, nomvoie2, nomvoie3, codepostal, codeinsee, ville, date_creation, date_modif) 
select distinct t.employe_id, '2016-09-21', '2999-01-01', numerovoie, upper(naturevoie), cpltadr1, cpltadr2, nomvoie1, nomvoie2, nomvoie3, codepostal, c.code, ville, now(), null
from tmp_vcp_montataire t
left outer join pai_ref_insee_commune c on t.ville=c.libelle;

select * from tmp_vcp_montataire order by mat
select * from emp_adresse where naturevoie not in (select code from pai_ref_rh_naturevoie);;

select libelle from pai_ref_insee_commune group by libelle having count(distinct code)>1;


select * from employe where matricule like 'DE%';
update employe set nom='BERTRAND',prenom1='Philippe' where id=10297;
update emp_adresse set nom='BERTRAND',prenom1='Philippe' where employe_id=10297;
insert into emp_adresse(employe_id,date_debut,date_fin,numerovoie,naturevoie,nomvoie1,codepostal,codeinsee,ville,date_creation)
select 10297,'2016-09-21','2999-01-01','12','RUE','Pasteur','60340',code,libelle,now()
from pai_ref_insee_commune where libelle like '%ESSERENT%'

update employe set nom='LANVIN',prenom1='Franck' where id=10298;
insert into emp_adresse(employe_id,date_debut,date_fin,numerovoie,naturevoie,nomvoie1,codepostal,codeinsee,ville,date_creation)
select 10298,'2016-09-21','2999-01-01','5','AVEN','de la gare','60290',code,libelle,now()
from pai_ref_insee_commune where libelle like 'RANTIGNY'

update pai_ref_insee_commune set code=trim(code),libelle=trim(libelle);
update emp_adresse set ville=trim(ville)
select * from emp_pop_depot epd
inner join employe e on epd.employe_id=e.id
where matricule like 'MR%' or matricule like 'DE%'
select * from emp_adresse epd
inner join employe e on epd.employe_id=e.id
where matricule like 'MR%' or matricule like 'DE%'
select * from emp_adresse 
select * from employe where id=10297
update emp_pop_depot epd
inner join employe e on epd.employe_id=e.id
set epd.heure_debut='03:30:00',dRC='2016-10-02'
where matricule like 'MR%' or matricule like 'DE%'

select * from employe where nom='RORTAIS';
select * from emp_banque where employe_id=10330;
INSERT INTO mroad.emp_banque (employe_id, date_debut, date_fin, iban, bic, date_creation, date_modif) 
values (10321, '2016-10-02', '2999-01-01', 'FR7618706000005041010010188', 'AGRIFRPP887', now(), null)

update emp_banque eb
inner join employe e on e.id=eb.employe_id
set eb.date_debut='2016-10-02'
where e.matricule like 'MR%';
update emp_adresse eb
inner join employe e on e.id=eb.employe_id
set eb.date_debut='2016-10-02'
where e.matricule like 'MR%'
update emp_cycle eb
inner join employe e on e.id=eb.employe_id
set eb.date_debut='2016-10-02'
where e.matricule like 'MR%'

insert into employe(nom,prenom1,matricule,date_creation,civilite_id,secu_numero,secu_cle,naissance_date,naissance_lieu,naissance_pays)
select t.nom,t.prénom,t.mat,now(),t.civilite_id,t.nsecu,t.clesecu,t.datenaissance,code_lieu_naissance,code_pays_naissance
from tmp_vcp_montataire t
;
alter table tmp_vcp_montataire add employe_id int;
update tmp_vcp_montataire t set employe_id=(select id from employe e where t.mat=e.matricule);

select * from employe where matricule like 'MR%' order by matricule desc;
select * from emp_contrat where employe_id in (select id from employe where matricule like 'MR%' order by matricule desc);
select * from emp_contrat_type where contrat_id in (select ec.id from employe e inner join emp_contrat ec on ec.employe_id=e.id where e.matricule like 'MR%' order by matricule desc);
select * from emp_cycle where employe_id in (select id from employe where matricule like 'MR%' order by matricule desc);
select * from emp_adresse where employe_id in (select id from employe where matricule like 'MR%' order by matricule desc);
select * from emp_pop_depot where employe_id in (select id from employe where matricule like 'MR%' order by matricule desc);
-- Nouveau VCP
insert into employe(nom,prenom1,matricule,date_creation,civilite_id,secu_numero,secu_cle,naissance_date,naissance_lieu,naissance_pays,nationalite_id)
values('NADER','Jessica','MR000026XX',now(),2,'2900599205083','50','1990-05-02',null,205,69);

insert into emp_contrat(employe_id,societe_id,date_debut,date_fin,rcoid,rc,date_creation)
values (10824,0,'2017-03-04', '2999-01-01','MR00002601','',now());

INSERT INTO emp_cycle (employe_id, date_debut, date_fin, cyc_cod, lundi, mardi, mercredi, jeudi, vendredi, samedi, dimanche, date_creation, date_modif,cycle) 
values( 10824, '2017-03-04', '2999-01-01', 0,1, 1, 1, 1, 1, 1, 0, now(), null,'LMMJVS-');

insert into emp_adresse(employe_id,date_debut,date_fin,numerovoie,naturevoie,nomvoie1,codepostal,codeinsee,ville,date_creation)
select 10824,'2017-03-04','2999-01-01','7','AVEN','de la libération','60160',code,libelle,now()
from pai_ref_insee_commune where libelle like 'MONTATAIRE';

INSERT INTO emp_banque (employe_id, date_debut, date_fin, iban, bic, date_creation, date_modif) 
values (10824, '2017-03-04', '2999-01-01', 'FR7618706000005041010010188', 'AGRIFRPP887', now(), null)

set foreign_key_checks=0
INSERT INTO emp_pop_depot (employe_id, depot_id, emploi_id, societe_id, date_debut, date_fin, rc, flux_id, typetournee_id, typeurssaf_id, typecontrat_id, population_id, heure_debut, nbheures_garanties, rcoid, dRC, fRC, date_creation, date_modif, km_paye,contrat_id,contrattype_id,cycle_id,cycle,lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche) 
 select 10824, 22, -1, 0, '2017-03-04', '2999-01-01', '', 1, 0, 0, 0, -1, '06:00', null, '', '2017-03-04', '2999-01-01', now(), null, 0,ec.id,0,ecy.id,ecy.cycle,ecy.lundi,ecy.mardi,ecy.mercredi,ecy.jeudi,ecy.vendredi,ecy.samedi,ecy.dimanche
 from emp_contrat ec
 inner join emp_cycle ecy on ec.employe_id=ecy.employe_id and ec.date_debut=ecy.date_debut
 where ec.employe_id=10824
set foreign_key_checks=1


select * from emp_pop_depot where employe_id=10824
select * from employe where matricule like 'MR%'
select * from emp_banque where employe_id=10824
update emp_banque set date_fin='2017-02-20',date_modif=now() where id=70
INSERT INTO emp_banque (employe_id, date_debut, date_fin, iban, bic, date_creation, date_modif) 
values (10487, '2017-02-21', '2999-01-01', 'FR7618706000005041010010188', 'AGRIFRPP887', now(), null)

INSERT INTO emp_banque (employe_id, date_debut, date_fin, iban, bic, date_creation, date_modif) 
values (10824, '2017-03-04', '2999-01-01', 'FR7630003007010005008714026', 'SOGEFRPP', now(), null)


select * from employe where matricule like 'MR%';