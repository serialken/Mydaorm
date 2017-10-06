commit;

SET foreign_key_checks = 0;
--------------------------------------------------------------------
DROP TABLE IF EXISTS `ref_jour`;
CREATE TABLE IF NOT EXISTS `ref_jour` (
  `id` INT(11) NOT NULL,
  `code` VARCHAR(2) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `libelle` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

insert into ref_jour values(1,'DI','Dimanche');
insert into ref_jour values(2,'LU','Lundi');
insert into ref_jour values(3,'MA','Mardi');
insert into ref_jour values(4,'ME','Mercredi');
insert into ref_jour values(5,'JE','Jeudi');
insert into ref_jour values(6,'VE','Vendredi');
insert into ref_jour values(7,'SA','Samedi');

--------------------------------------------------------------------
DROP TABLE IF EXISTS `ref_typejour`;
CREATE TABLE IF NOT EXISTS `ref_typejour` (
  `id` INT(11) NOT NULL,
  `code` VARCHAR(1) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `libelle` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

insert into ref_typejour values(1,'S','Semaine');
insert into ref_typejour values(2,'D','Dimanche');
insert into ref_typejour values(3,'F','Ferié');

--------------------------------------------------------------------
DROP TABLE IF EXISTS `ref_flux`;
CREATE TABLE IF NOT EXISTS `ref_flux` (
  `id` INT(11) NOT NULL,
  `code` VARCHAR(1) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `libelle` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

insert into ref_flux values(1,'N','Nuit');
insert into ref_flux values(2,'J','Jour');

--------------------------------------------------------------------
DROP TABLE IF EXISTS `ref_transport`;
CREATE TABLE IF NOT EXISTS `ref_transport` (
  `id` INT(11) NOT NULL,
  `code` VARCHAR(3) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `libelle` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

insert into ref_transport values(1,'PIE','A pied');
insert into ref_transport values(2,'2RO','2 roues');
insert into ref_transport values(3,'4RO','4 roues');

--------------------------------------------------------------------
DROP TABLE IF EXISTS `ref_emploi`;
CREATE TABLE IF NOT EXISTS `ref_emploi` (
  `id` INT(11) NOT NULL,
  `code` VARCHAR(6) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `libelle` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

insert into ref_emploi values(1,'000219','Porteur');
insert into ref_emploi values(2,'000220','Polyvalent');

--------------------------------------------------------------------
DROP TABLE IF EXISTS `ref_population`;
CREATE TABLE IF NOT EXISTS `ref_population` (
  `id` INT(11) NOT NULL,
  `emploi_id` INT(11) NULL DEFAULT NULL,
  `societe_id` INT(11) NULL DEFAULT NULL,
  `code` VARCHAR(4) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `libelle` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `IDX_B28C4F16EC013E12` (`emploi_id` ASC),
  INDEX `IDX_B28C4F16FCF77503` (`societe_id` ASC),
  CONSTRAINT `FK_B28C4F16FCF77503`
    FOREIGN KEY (`societe_id`)
    REFERENCES `silog`.`ref_emp_societe` (`id`),
  CONSTRAINT `FK_B28C4F16EC013E12`
    FOREIGN KEY (`emploi_id`)
    REFERENCES `silog`.`ref_emploi` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

insert into ref_population values(1,1,1,'SPOR','Porteur SDVP');
insert into ref_population values(2,1,1,'NPOR','Porteur NeoPress');
insert into ref_population values(3,1,1,'MPOR','Porteur MediaPresse');
insert into ref_population values(4,1,1,'SPOL','Polyvalent SDVP');
insert into ref_population values(5,1,1,'NPOL','Polyvalent NeoPress');
insert into ref_population values(6,1,1,'MPOL','Polyvalent MediaPresse');

--------------------------------------------------------------------
DROP TABLE IF EXISTS `ref_typetournee`;
CREATE TABLE `ref_typetournee` (
  `id` int(11) NOT NULL,
  `code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `population_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_B28C4F16EC013E13` (`population_id`),
  CONSTRAINT `FK_B28C4F16EC013E13` FOREIGN KEY (`population_id`) REFERENCES `ref_population` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into ref_typetournee values(1,'SDV','SDVP',1);
insert into ref_typetournee values(2,'NEO','NeoPress',2);
insert into ref_typetournee values(3,'MED','MediaPresse',3);

--------------------------------------------------------------------
DROP TABLE IF EXISTS `ref_incident`;
CREATE TABLE IF NOT EXISTS `ref_incident`(
  `id` int(11) NOT NULL,
  `code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BB14679D77153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into ref_incident values(1,'PO','Manque de ponctualité');
insert into ref_incident values(2,'CL','Perte de clef');
insert into ref_incident values(3,'VE','Véhicules endommagés ou dégradés');
insert into ref_incident values(4,'RE','Repérages non effectués ou mal faits');
insert into ref_incident values(5,'VM','Non présentation récurrente à la visite médicale');

--------------------------------------------------------------------
DROP TABLE IF EXISTS `ref_typeurssaf`;
CREATE TABLE IF NOT EXISTS `ref_typeurssaf` (
  `id` INT(11) NOT NULL,
  `code` VARCHAR(3) COLLATE 'utf8_unicode_ci' NOT NULL,
  `libelle` VARCHAR(32) COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into ref_typeurssaf values(1,'BF','Base forfaitaire');
insert into ref_typeurssaf values(2,'BR','Base réelle');

--------------------------------------------------------------------
DROP TABLE IF EXISTS `ref_natureclient`;
CREATE TABLE IF NOT EXISTS `ref_natureclient` (
  `id` INT(11) NOT NULL,
  `code` VARCHAR(1) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `libelle` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `typeurssaf_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_B28C4F16FCF77504`
    FOREIGN KEY (`typeurssaf_id`)
    REFERENCES `silog`.`ref_typeurssaf` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

insert into ref_natureclient(id,code,libelle,typeurssaf_id) values(0,'A','Abonné',1);
insert into ref_natureclient(id,code,libelle,typeurssaf_id) values(1,'D','Diffuseur',2);

--------------------------------------------------------------------
/*
DROP TABLE IF EXISTS `ref_activite`;
CREATE TABLE IF NOT EXISTS `ref_activite` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(2) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `libelle` VARCHAR(32) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `affichage_modele` TINYINT(1) NOT NULL,
  `affichage_duree` TINYINT(1) NOT NULL,
  `affichage_km` TINYINT(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `un_ref_activite` (`code` ASC, `date_debut` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;
*/
CREATE TABLE IF NOT EXISTS `pai_ref_mois` (
  `annee` varchar(4) NOT NULL,
  `mois` varchar(2) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  PRIMARY KEY (`annee`,`mois`),
  UNIQUE KEY `un_pai_ref_mois` (`annee`,`mois`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Préremplie avec toutes les dates de paie du 21 au 20 jusqu''en 2099';

CREATE TABLE IF NOT EXISTS `pai_mois` (
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  PRIMARY KEY (`date_debut`),
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--------------------------------------------------------------------
DROP TABLE IF EXISTS `pai_ref_postepaie_general`;
CREATE TABLE IF NOT EXISTS `pai_ref_postepaie_general` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(3) NOT NULL,
  `libelle` VARCHAR(128) NOT NULL,
  `poste` VARCHAR(10) NOT NULL,
  `typeurssaf_id` INT(11),
  `semaine` TINYINT(1) NOT NULL,
  `dimanche` TINYINT(1) NOT NULL,
  `ferie` TINYINT(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `code_UNIQUE` (`code` ASC),
  CONSTRAINT `FK_B28C4F16FCF77505`
    FOREIGN KEY (`typeurssaf_id`)
    REFERENCES `silog`.`ref_typeurssaf` (`id`))
ENGINE = InnoDB;

Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('HRS','Heures Semaine','0560',null,true,false,false);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('HRD','Heures Dimanche','0561',null,false,true,false);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('HRF','Heures Ferié','0562',null,false,false,true);

Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('TSN','Heures Nuit Semaine+Ferié BR','NTSN',2,true,false,true);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('TS4','Heures Nuit Semaine+Ferié BDC','NTS4',1,true,false,true);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('TDN','Heures Nuit Dimanche BR','NTSN',2,false,true,false);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('TD4','Heures Nuit Dimanche BDC','NTS4',1,false,true,false);

Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('NA1','Rémunération Semaine BR','0500',2,true,false,false);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('N41','Rémunération Semaine BDC','0503',1,true,false,false);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('DA1','Rémunération Dimanche BR','0506',2,false,true,false);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('D41','Rémunération Dimanche BDC','0509',1,false,true,false);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('FA1','Rémunération Ferié BR','0512',2,false,false,true);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('F41','Rémunération Ferié BDC','0515',1,false,false,true);

Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('NBN','quantite de journaux BR','0350',2,true,true,true);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('NB4','Nombre de journaux BDC','0351',1,true,true,true);

Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('KMS','Kilomètre Semaine+Ferié','KMSEMAIJFR',null,true,false,true);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('KMD','Kilomètre Dimanche','KMDIMANCHE',null,false,true,false);

Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('HTP','Majoration heures travaillées','HTPX',null,true,true,true);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('HJP','Majoration heures de jour','HJPX',null,true,true,true);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('JOP','Majoration jours ouvrables periode','JOPX',null,true,true,true);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('JTP','Majoration jours ouvrables travaillés','JTPX',null,true,true,true);

Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('QLT','Prime Qualité porteur','0105',null,true,true,true);
Insert into pai_ref_postepaie_general (code,libelle,poste,typeurssaf_id,semaine,dimanche,ferie) values ('SBQ','Super Bonus Qualite','0140',null,true,true,true);

--------------------------------------------------------------------
DROP TABLE IF EXISTS `pai_ref_postepaie_activite`;
CREATE TABLE IF NOT EXISTS `pai_ref_postepaie_activite` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `typejour_id` INT(11) NOT NULL,
  `activite_id` INT(11) NOT NULL,
  `population_id` INT(11) NOT NULL,
  `poste_km` VARCHAR(10) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
  `poste_hj` VARCHAR(10) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
  `poste_hn` VARCHAR(10) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `un_ref_postepaie_activite` (`population_id` ASC, `activite_id` ASC, `typejour_id` ASC),
  INDEX `IDX_EF3A09FEF8E8E66B` (`typejour_id` ASC),
  INDEX `IDX_EF3A09FE9B0F88B1` (`activite_id` ASC),
  INDEX `IDX_EF3A09FEC955D1E1` (`population_id` ASC),
  CONSTRAINT `FK_EF3A09FEC955D1E1`
    FOREIGN KEY (`population_id`)
    REFERENCES `silog`.`ref_population` (`id`),
  CONSTRAINT `FK_EF3A09FE9B0F88B1`
    FOREIGN KEY (`activite_id`)
    REFERENCES `silog`.`ref_activite` (`id`),
  CONSTRAINT `FK_EF3A09FEF8E8E66B`
    FOREIGN KEY (`typejour_id`)
    REFERENCES `silog`.`ref_typejour` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

--------------------------------------------------------------------
DROP TABLE IF EXISTS `pai_ref_qualite`;
CREATE TABLE IF NOT EXISTS `pai_ref_qualite` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `population_id` INT(11) NOT NULL,
  `qualite` VARCHAR(1) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `borne_inf` DECIMAL(7,3) NOT NULL,
  `borne_sup` DECIMAL(7,3) NOT NULL,
  `valeur` VARCHAR(1) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `un_pai_ref_qualite` (`population_id` ASC, `qualite` ASC, `borne_inf` ASC),
  INDEX `IDX_C0F417BCC955D1E1` (`population_id` ASC),
  CONSTRAINT `FK_C0F417BCC955D1E1`
    FOREIGN KEY (`population_id`)
    REFERENCES `silog`.`ref_population` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

Insert into pai_ref_qualite (population_id,qualite,borne_inf,borne_sup,valeur) values (1,'U','0','1','1');
Insert into pai_ref_qualite (population_id,qualite,borne_inf,borne_sup,valeur) values (1,'U','1.001','9999','0');
Insert into pai_ref_qualite (population_id,qualite,borne_inf,borne_sup,valeur) values (1,'O','0','0.5','2');
Insert into pai_ref_qualite (population_id,qualite,borne_inf,borne_sup,valeur) values (1,'O','0.501','1','3');
Insert into pai_ref_qualite (population_id,qualite,borne_inf,borne_sup,valeur) values (1,'O','1.001','9999','0');
Insert into pai_ref_qualite (population_id,qualite,borne_inf,borne_sup,valeur) values (1,'A','0','9999','0');
Insert into pai_ref_qualite (population_id,qualite,borne_inf,borne_sup,valeur) values (1,'I','0','9999','0');
Insert into pai_ref_qualite (population_id,qualite,borne_inf,borne_sup,valeur) values (1,'D','0','9999','0');

--------------------------------------------------------------------
DROP TABLE IF EXISTS `pai_ref_postepaie_supplement`;
CREATE TABLE IF NOT EXISTS `pai_ref_postepaie_supplement` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `population_id` INT(11) NOT NULL,
  `produit_id` INT(11) NOT NULL,
  `typeurssaf_id` INT(11) NOT NULL,
  `poste` VARCHAR(10) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `un_pai_ref_postepaie_supplement` (`population_id` ASC, `produit_id` ASC, `typeurssaf_id` ASC),
  INDEX `IDX_DE0D137EC955D1E1` (`population_id` ASC),
  INDEX `IDX_DE0D137EF347EFB` (`produit_id` ASC),
  CONSTRAINT `FK_DE0D137EF347EFB`
    FOREIGN KEY (`produit_id`)
    REFERENCES `silog`.`produit` (`id`),
  CONSTRAINT `FK_DE0D137EC955D1E1`
    FOREIGN KEY (`population_id`)
    REFERENCES `silog`.`ref_population` (`id`),
  CONSTRAINT `FK_B28C4F16FCF77506`
    FOREIGN KEY (`typeurssaf_id`)
    REFERENCES `silog`.`ref_typeurssaf` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;
/*
insert into produit_type(libelle) values('Prospectus');
insert into produit_type(libelle) values('Bagues et Bandeaux');
insert into produit_type(libelle) values('Ouvrages sous enveloppe (type DDB)');
insert into produit_type(libelle) values('Produit type "CANAL+"');
*/
--------------------------------------------------------------------
DROP TABLE IF EXISTS `pai_ref_poids`;
CREATE TABLE IF NOT EXISTS `pai_ref_poids` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `population_id` INT(11) NOT NULL,
  `produit_type_id` INT(11) NULL,
  `produit_id` INT(11) NULL,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `quantite` INTEGER NOT NULL,
  `borne_inf` DECIMAL(7,3) NOT NULL,
  `borne_sup` DECIMAL(7,3) NOT NULL,
  `valeur` DECIMAL(6,4) NOT NULL,
  `valeur_unite` DECIMAL(4,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `un_pai_ref_poids` (`produit_id` ASC, `date_debut` ASC, `population_id` ASC, `produit_type_id` ASC),
  INDEX `IDX_231D9078C955D1E1` (`population_id` ASC),
  INDEX `IDX_231D90783B707EA` (`produit_type_id` ASC),
  INDEX `IDX_231D9078F347EFB` (`produit_id` ASC),
  CONSTRAINT `FK_231D9078F347EFB`
    FOREIGN KEY (`produit_id`)
    REFERENCES `silog`.`produit` (`id`),
  CONSTRAINT `FK_231D90783B707EA`
    FOREIGN KEY (`produit_type_id`)
    REFERENCES `silog`.`produit_type` (`id`),
  CONSTRAINT `FK_231D9078C955D1E1`
    FOREIGN KEY (`population_id`)
    REFERENCES `silog`.`ref_population` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

/*
4	Prospectus
5	Bagues et Bandeaux
6	Ouvrages sous enveloppe (type DDB)
7	Produit type "CANAL+"
*/
-- Supplément
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(1,2,null,'2012-01-01','2999-01-01',0,9999,0.01,0,100);
-- Supplément non lié au titre
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(1,3,null,'2012-01-01','2999-01-01',0,9999,0.01,0,100);
-- Titre principal
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,1,null,'2012-01-01','2999-01-01',0.350,0.449,1,0,100);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,1,null,'2012-01-01','2999-01-01',0.450,0.649,3,0,100);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,1,null,'2012-01-01','2999-01-01',0.650,0.749,5,0,100);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,1,null,'2012-01-01','2999-01-01',0.750,9999,7,0,100);
-- Supplément
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2012-01-01','2013-03-31',0,0.050,0.0145,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2012-01-01','2013-03-31',0.051,0.100,0.0198,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2012-01-01','2013-03-31',0.101,0.300,0.0269,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2012-01-01','2013-03-31',0.301,0.450,0.0358,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2012-01-01','2013-03-31',0.451,0.650,0.0472,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2012-01-01','2013-03-31',0.651,0.750,0.0520,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2012-01-01','2013-03-31',0.751,9999,0.0727,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2013-04-01','2999-01-01',0,0.050,0.0148,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2013-04-01','2999-01-01',0.051,0.100,0.0202,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2013-04-01','2999-01-01',0.101,0.300,0.0274,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2013-04-01','2999-01-01',0.301,0.450,0.0365,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2013-04-01','2999-01-01',0.451,0.650,0.0481,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2013-04-01','2999-01-01',0.651,0.750,0.0530,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,2,null,'2013-04-01','2999-01-01',0.751,9999,0.0742,0,1);
-- Supplément non lié au titre
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2012-01-01','2013-03-31',0,0.050,0.0145,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2012-01-01','2013-03-31',0.051,0.100,0.0198,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2012-01-01','2013-03-31',0.101,0.300,0.0269,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2012-01-01','2013-03-31',0.301,0.450,0.0358,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2012-01-01','2013-03-31',0.451,0.650,0.0472,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2012-01-01','2013-03-31',0.651,0.750,0.0520,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2012-01-01','2013-03-31',0.751,9999,0.0727,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2013-04-01','2999-01-01',0,0.050,0.0148,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2013-04-01','2999-01-01',0.051,0.100,0.0202,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2013-04-01','2999-01-01',0.101,0.300,0.0274,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2013-04-01','2999-01-01',0.301,0.450,0.0365,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2013-04-01','2999-01-01',0.451,0.650,0.0481,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2013-04-01','2999-01-01',0.651,0.750,0.0530,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,3,null,'2013-04-01','2999-01-01',0.751,9999,0.0742,0,1);
-- Prospectus desservis sur tournée
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,4,null,'2012-01-01','2013-03-31',0,0.074,0.75,0,100);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,4,null,'2012-01-01','2013-03-31',0.075,9999,0.95,0,100);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,4,null,'2013-04-01','2999-01-01',0,0.074,0.77,0,100);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,4,null,'2013-04-01','2999-01-01',0.075,9999,0.97,0,100);
-- Prospectus desservis hors tournée
-- insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,4,null,'2013-04-01','2999-01-01',0,0.074,1.6,0,100);
-- insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,4,null,'2013-04-01','2999-01-01',0.075,9999,1.96,0,100);
-- bagues et bandeau
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,5,null,'2012-01-01','2013-03-31',0,0.050,0.0141,0.01,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,5,null,'2012-01-01','2013-03-31',0.050,0.100,0.0192,0.01,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,5,null,'2012-01-01','2013-03-31',0.101,0.300,0.0261,0.01,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,5,null,'2012-01-01','2013-03-31',0.301,0.450,0.0348,0.01,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,5,null,'2013-04-01','2999-01-01',0,0.050,0.0148,0.01,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,5,null,'2013-04-01','2999-01-01',0.050,0.100,0.0202,0.01,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,5,null,'2013-04-01','2999-01-01',0.101,0.300,0.0274,0.01,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,5,null,'2013-04-01','2999-01-01',0.301,0.450,0.0356,0.01,1);
-- Ouvrages sous enveloppe (type DDB)
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2012-01-01','2013-03-31',0,0.050,0.028,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2012-01-01','2013-03-31',0.051,0.100,0.038,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2012-01-01','2013-03-31',0.101,0.300,0.052,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2012-01-01','2013-03-31',0.301,0.450,0.070,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2012-01-01','2013-03-31',0.451,0.650,0.091,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2012-01-01','2013-03-31',0.651,0.750,0.101,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2012-01-01','2013-03-31',0.751,9999,0.141,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2013-04-01','2999-01-01',0,0.050,0.029,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2013-04-01','2999-01-01',0.051,0.100,0.039,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2013-04-01','2999-01-01',0.101,0.300,0.053,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2013-04-01','2999-01-01',0.301,0.450,0.071,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2013-04-01','2999-01-01',0.451,0.650,0.093,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2013-04-01','2999-01-01',0.651,0.750,0.103,0,1);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,6,null,'2013-04-01','2999-01-01',0.751,9999,0.144,0,1);
-- Produit type "CANAL+"
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,4,null,'2013-04-01','2999-01-01',0,0.100,0.052,0,100);
insert into pai_ref_poids(typetournee_id,produit_type_id,produit_id,date_debut,date_fin,borne_inf,borne_sup,valeur,valeur_unite,quantite) values(2,4,null,'2013-04-01','2999-01-01',0.101,9999,0.070,0,100);


SET foreign_key_checks = 1;
commit;