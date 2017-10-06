/*
DROP TABLE `pai_ref_postepaie_dispress`;
CREATE TABLE `pai_ref_postepaie_dispress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poste` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `libelle` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_ref_postepaie_dispress` (`poste`)
) ENGINE=InnoDB AUTO_INCREMENT=1093 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into pai_ref_postepaie_dispress(poste,libelle) values('35100','Heures - TOTAL');
insert into pai_ref_postepaie_dispress(poste,libelle) values('35200','Heures - Distribution');
insert into pai_ref_postepaie_dispress(poste,libelle) values('38000','Nombre d''exemplaires (cotisation forfaitaire)');
insert into pai_ref_postepaie_dispress(poste,libelle) values('38100','Nombre d''exemplaires (droit commun)');
insert into pai_ref_postepaie_dispress(poste,libelle) values('48500','Prime Polyvalent');
insert into pai_ref_postepaie_dispress(poste,libelle) values('51315','Heures - TOTAL');
insert into pai_ref_postepaie_dispress(poste,libelle) values('64200','Indémnité kilométrique');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95026','NB Suppléments');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95027','Valorisation des heures de nuit');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95029','Heures - Portage cotisation forfaitaire jour férié');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95037','Heures Normales Hors Portage Semaine');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95040','Heures Normales Hors Portage Jour Férié');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95042','Heures - Portage cotisation droit commun jour férié');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95045','Rémunération Repérage');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95046','Majoration poids');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95050','Nombre d''abonnés (cotisation forfaitaire) - Semaine');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95051','Nombre d''abonnés (cotisation forfaitaire) - Jour Férié');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95052','Nombre d''exemplaires (cotisation forfaitaire) - Semaine');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95053','Nombre d''exemplaires (cotisation forfaitaire) - Jour Férié');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95054','Heures Haut le Pied (cotisation forfaitaire) - Semaine');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95055','Heures Haut le Pied (cotisation forfaitaire) - Jour Férié');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95056','Nombre d''abonnés (cotisation de droit commun) - Semaine');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95057','Nombre d''abonnés (cotisation de droit commun) - Jour Férié');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95058','Nombre d''exemplaires (cotisation de droit commun) - Semaine');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95059','Nombre d''exemplaires (cotisation de droit commun) - Jour Férié');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95060','Heures Haut le Pied (cotisation de droit commun) - Semaine');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95061','Heures Haut le Pied (cotisation de droit commun) - Jour Férié');
insert into pai_ref_postepaie_dispress(poste,libelle) values('95062','Produits spéciaux');
insert into pai_ref_postepaie_dispress(poste,libelle) values('96100','Prime qualité');
insert into pai_ref_postepaie_dispress(poste,libelle) values('YYYYY','HEURES Suppléments');
insert into pai_ref_postepaie_dispress(poste,libelle) values('MMMMM','Valorisation Heures Repérage');

DROP TABLE `pai_ref_postepaie_transco`;
CREATE TABLE `pai_ref_postepaie_transco` (
  `posteDIS` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posteNG1` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posteNG2` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `un_ref_postepaie_dispress` (`posteDIS`)
) ENGINE=InnoDB AUTO_INCREMENT=1093 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('35100','HTPX');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('35200','0500'); -- ????

insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('38000','MEBF');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('38100','MEBD');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95052','MEBF');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95053','MEBF');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95058','MEBD');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95059','MEBD');

insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95050','0503');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95051','0515');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95056','0500');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95057','0512');

insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95054','HHLP');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95060','HHLP');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95055','HHLF');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95061','HHLF');

insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('64200','KMSEMAIJFR');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95027','NTSP');

insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95037','0526');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95040','0548');

insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('51315','HTPX');

insert into pai_ref_postepaie_transco(posteDIS,posteNG1,posteNG2) values('95026','SPBD','SPBF');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1,posteNG2) values('95045','MRBD','MRBF');

insert into pai_ref_postepaie_transco(posteDIS,posteNG1,posteNG2) values('YYYYY','SPBD','SPBF');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1,posteNG2) values('MMMMM','MRBD','MRBF');

insert into pai_ref_postepaie_transco(posteDIS,posteNG1,posteNG2) values('95046','MPBD','MPBF');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('95062','PHPP');

-- prime
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('96100','0105');
insert into pai_ref_postepaie_transco(posteDIS,posteNG1) values('48500','DPPY');

*/


alter table pai_ev_dispress add mat_mroad varchar(10);
alter table pai_ev_dispress add postepaieNG varchar(8);
alter table pai_ev_dispress add rc varchar(6);
alter table pai_ev_dispress add employe_id int;
alter table pai_ev_dispress add epd_id int;

-- transco du matricule
update pai_ev_dispress set mat_mroad=concat('NE',matricule,'00');
update pai_ev_dispress ev inner join emp_transco e on ev.mat_mroad=e.mat_org set ev.mat_mroad=e.mat_dst;
update pai_ev_dispress di inner join employe e on di.mat_mroad=e.matricule set di.employe_id=e.id;
-- matricule inconnu
select distinct matricule from pai_ev_dispress where employe_id is null;

-- Affectation contrat
update pai_ev_dispress di inner join emp_pop_depot epd on di.employe_id=epd.employe_id and epd.date_debut<'2014-08-27' and epd.date_fin>'2014-08-21' set di.epd_id=epd.id;
-- individu hors contrat
select distinct di.matricule,di.employe_id from pai_ev_dispress di where epd_id is null; 
select distinct di.matricule,di.employe_id from pai_ev_dispress di inner join emp_pop_depot epd on di.epd_id=epd.id where epd.population_id<5; 
select * from emp_pop_depot where employe_id in (6092,6130,454);
select * from employe where id in (454);

-- poste de paie inconnu
select  distinct di.postepaie from pai_ev_dispress di where postepaie not in (select poste from pai_ref_postepaie_dispress);

-- poste de paie par population
select rp.code as population,rp.libelle as lib_pop,di.postepaie,pd.libelle,count(*),t.posteNG1,t.posteNG2
from pai_ev_dispress di
inner join emp_pop_depot epd on di.epd_id=epd.id
inner join ref_population rp on epd.population_id=rp.id
left outer join pai_ref_postepaie_dispress pd on di.postepaie=pd.poste
left outer join pai_ref_postepaie_transco t on pd.poste=t.posteDIS
group by rp.id,pd.id,di.postepaie
order by rp.id,pd.id,di.postepaie;

select *
from pai_ev_dispress di
inner join emp_pop_depot epd on di.epd_id=epd.id
inner join ref_population rp on epd.population_id=rp.id
left outer join pai_ref_postepaie_dispress pd on di.postepaie=pd.poste
left outer join pai_ref_postepaie_transco t on pd.poste=t.posteDIS
where population_id=5
;

select * from pai_ev_dispress where postepaie in ('95029','95042');

select * from employe where matricule='';

select * from ref_population;

select * from pai_ev;
select * from pai_ev_dispress;
select * from emp_pop_depot;

create procedure int_dis2ev_insert(
  IN  postepaieDIS varchar(10),
  IN  postepaieNG varchar(10)
) BEGIN
  insert into pai_ev(matricule,rc,poste,datev,ordre,qte,taux,val)
  select matricule,rc,postepaieNG,date_debut,null,qte,taux,mnt
  from pai_ev_dispress
  where postepaie=postepaieDIS;
END;


call int_dis2ev_insert('38000','MEBF');
call int_dis2ev_insert('38100','MEBD');
call int_dis2ev_insert('95052','MEBF');
call int_dis2ev_insert('95053','MEBF');
call int_dis2ev_insert('95058','MEBD');
call int_dis2ev_insert('95059','MEBD');

call int_dis2ev_insert('95050','0503');
call int_dis2ev_insert('95051','0515');
call int_dis2ev_insert('95056','0500');
call int_dis2ev_insert('95057','0512');

call int_dis2ev_insert('95054','95060','HHLP');
call int_dis2ev_insert('95055','95061','HHLF');

call int_dis2ev_insert('64200','KMSEMAIJFR');

call int_dis2ev_insert('95027','NTSP');

call int_dis2ev_insert('95037','0526');
call int_dis2ev_insert('95040','0548');

call int_dis2ev_insert('51315','HTPX');
call int_dis2ev_insert('96100','0105');
/*
95026	SPBD	SPBF
95045	MRBD	MRBF
*/



select * from pai_ev;