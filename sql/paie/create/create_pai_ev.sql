SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- Employe
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP table IF EXISTS pai_ev_emp_depot;
CREATE TABLE IF NOT EXISTS `pai_ev_emp_depot` (
  `employe_id` INT(11) NOT NULL,
  `depot_id` INT(11) NOT NULL,
  `flux_id` INT(11) NOT NULL,
  `matricule` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `dRC` DATE NOT NULL,
  `fRC` DATE NOT NULL,
  `dCtr` DATE NOT NULL,
  `fCtr` DATE NOT NULL,
  `d` DATE NOT NULL,
  `f` DATE NOT NULL,
  `rc` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
--  nbkm_paye decimal(7,1),
--  nbcli decimal(8,0),
  INDEX `fk_pai_ev_emp_depot_employe1_idx` (`employe_id` ASC),
  INDEX `fk_pai_ev_emp_depot_depot1_idx` (`depot_id` ASC),
  INDEX `fk_pai_ev_emp_depot_ref_flux1_idx` (`flux_id` ASC),
  CONSTRAINT `fk_pai_ev_emp_depot_employe1`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
/*  CONSTRAINT `fk_pai_ev_emp_depot_depot1`
    FOREIGN KEY (`depot_id`)
    REFERENCES `depot` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,*/
  CONSTRAINT `fk_pai_ev_emp_depot_ref_flux1`
    FOREIGN KEY (`flux_id`)
    REFERENCES `ref_flux` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_ev_emp_depot_idx1 on pai_ev_emp_depot(dCtr);

DROP TABLE IF EXISTS `pai_ev_emp_pop_depot`;
CREATE TABLE IF NOT EXISTS `pai_ev_emp_pop_depot` (
  `employe_id` INT(11) NOT NULL,
  `depot_id` INT(11) NOT NULL,
  `flux_id` INT(11) NOT NULL,
  `population_id` INT(11) NOT NULL,
  `typetournee_id` INT(11) NOT NULL,
  `societe_id` INT(11) NOT NULL,
  `matricule` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `dRC` DATE NOT NULL,
  `fRC` DATE NOT NULL,
  `dCtr` DATE NOT NULL,
  `fCtr` DATE NOT NULL,
  `d` DATE NOT NULL,
  `f` DATE NOT NULL,
  `rc` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  INDEX `fk_pai_ev_emp_pop_depot_employe1_idx` (`employe_id` ASC),
  INDEX `fk_pai_ev_emp_pop_depot_depot1_idx` (`depot_id` ASC),
  INDEX `fk_pai_ev_emp_pop_depot_ref_flux1_idx` (`flux_id` ASC),
  INDEX `fk_pai_ev_emp_pop_depot_ref_population1_idx` (`population_id` ASC),
  INDEX `fk_pai_ev_emp_pop_depot_ref_typetournee1_idx` (`typetournee_id` ASC),
  INDEX `fk_pai_ev_emp_pop_depot_ref_emp_societe1_idx` (`societe_id` ASC),
  CONSTRAINT `fk_pai_ev_emp_pop_depot_employe1`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
/*  CONSTRAINT `fk_pai_ev_emp_pop_depot_depot1`
    FOREIGN KEY (`depot_id`)
    REFERENCES `depot` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,*/
  CONSTRAINT `fk_pai_ev_emp_pop_depot_ref_flux1`
    FOREIGN KEY (`flux_id`)
    REFERENCES `ref_flux` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_emp_pop_depot_ref_population1`
    FOREIGN KEY (`population_id`)
    REFERENCES `ref_population` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_emp_pop_depot_ref_typetournee_id1`
    FOREIGN KEY (`typetournee_id`)
    REFERENCES `ref_typetournee` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_emp_pop_depot_ref_emp_societe1`
    FOREIGN KEY (`societe_id`)
    REFERENCES `ref_emp_societe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_ev_emp_pop_depot_idx1 on pai_ev_emp_pop_depot(matricule,d);
alter table pai_ev_emp_pop_depot add   nbabo	numeric(12,0);
alter table pai_ev_emp_pop_depot add   `nbrec` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot add   `taux` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop_depot add   qualite	CHAR(1);
alter table pai_ev_emp_pop_depot add  emploi_code VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL;
alter table pai_ev_emp_pop_depot_hst add   nbabo	numeric(12,0);
alter table pai_ev_emp_pop_depot_hst add   `nbrec` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot_hst add   `taux` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop_depot_hst add   qualite	CHAR(1);
alter table pai_ev_emp_pop_depot_hst add  emploi_code VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL;

alter table pai_ev_emp_pop_depot add   nbabo_DF	numeric(12,0);
alter table pai_ev_emp_pop_depot add   `nbrec_DF` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot add   `taux_DF` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop_depot_hst add   nbabo_DF	numeric(12,0);
alter table pai_ev_emp_pop_depot_hst add   `nbrec_DF` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot_hst add   `taux_DF` DECIMAL(7,3) NOT NULL;

alter table pai_ev_emp_pop_depot 		add   `nbrec_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot 		add   `taux_brut` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop_depot_hst 	add   `nbrec_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot_hst 	add   `taux_brut` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop_depot 		add   `nbrec_DF_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot 		add   `taux_DF_brut` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop_depot_hst 	add   `nbrec_DF_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot_hst 	add   `taux_DF_brut` DECIMAL(7,3) NOT NULL;

alter table pai_ev_emp_pop_depot 		add   `nbrec_dif` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot_hst 	add   `nbrec_dif` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot 		add   `nbrec_dif_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot_hst 	add   `nbrec_dif_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot 		add   `nbrec_dif_DF` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot_hst 	add   `nbrec_dif_DF` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot 		add   `nbrec_dif_DF_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_depot_hst 	add   `nbrec_dif_DF_brut` DECIMAL(4,0) NOT NULL;

DROP TABLE IF EXISTS `pai_ev_emp_pop`;
CREATE TABLE IF NOT EXISTS `pai_ev_emp_pop` (
  `employe_id` INT(11) NOT NULL,
  `societe_id` INT(11) NOT NULL,
  emploi_code VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL,
  `matricule` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `dRC` DATE NOT NULL,
  `fRC` DATE NOT NULL,
  `dCtr` DATE NOT NULL,
  `fCtr` DATE NOT NULL,
  `d` DATE NOT NULL,
  `f` DATE NOT NULL,
  `rc` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  nbabo	numeric(12,0),
  `nbrec` DECIMAL(4,0) NOT NULL,
  qualite	CHAR(1),
  `taux` DECIMAL(7,3) NOT NULL,
  INDEX `fk_pai_ev_emp_pop_employe1_idx` (`employe_id` ASC),
  INDEX `fk_pai_ev_emp_pop_ref_societe1_idx` (`societe_id` ASC),
  CONSTRAINT `fk_pai_ev_emp_pop_employe1`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_emp_pop_ref_societe1`
    FOREIGN KEY (`societe_id`)
    REFERENCES `ref_emp_societe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_ev_emp_pop_idx1 on pai_ev_emp_pop(dCtr);

alter table pai_ev_emp_pop add   nbabo_DF	numeric(12,0);
alter table pai_ev_emp_pop add   `nbrec_DF` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop add   `taux_DF` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop_hst add   nbabo_DF	numeric(12,0);
alter table pai_ev_emp_pop_hst add   `nbrec_DF` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_hst add   `taux_DF` DECIMAL(7,3) NOT NULL;

alter table pai_ev_emp_pop add   `nbrec_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop add   `taux_brut` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop_hst add   `nbrec_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_hst add   `taux_brut` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop add   `nbrec_DF_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop add   `taux_DF_brut` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop_hst add   `nbrec_DF_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_hst add   `taux_DF_brut` DECIMAL(7,3) NOT NULL;

alter table pai_ev_emp_pop 		add   `nbrec_dif` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_hst 	add   `nbrec_dif` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop 		add   `nbrec_dif_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_hst 	add   `nbrec_dif_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop 		add   `nbrec_dif_DF` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_hst 	add   `nbrec_dif_DF` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop 		add   `nbrec_dif_DF_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_emp_pop_hst 	add   `nbrec_dif_DF_brut` DECIMAL(4,0) NOT NULL;

DROP TABLE IF EXISTS `pai_ev_emp`;
CREATE TABLE IF NOT EXISTS `pai_ev_emp` (
  `employe_id` INT(11) NOT NULL,
  `rcoid` VARCHAR(36) COLLATE utf8_unicode_ci NOT NULL,
  `matricule` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `rc` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  `dRC` DATE NOT NULL,
  `fRC` DATE NOT NULL,
  `d` DATE NOT NULL,
  `f` DATE NOT NULL,
  `anneemois` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  INDEX `fk_pai_ev_emp_employe1_idx` (`employe_id` ASC),
  CONSTRAINT `fk_pai_ev_emp_employe1`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- Tournee, produit, activite
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS `pai_ev_produit`;
DROP TABLE IF EXISTS `pai_ev_reclamation`;
DROP TABLE IF EXISTS `pai_ev_tournee`;
CREATE TABLE IF NOT EXISTS `pai_ev_tournee` (
  `id` INT(11) NOT NULL,
  `depot_id` INT(11) NOT NULL,
  `flux_id` INT(11) NOT NULL,
  `employe_id` INT(11) NOT NULL,
  `typejour_id` INT(11) NOT NULL,
  `jour_id` INT(11) NOT NULL,
  `date_distrib` DATE NOT NULL,
  code varchar(13) COLLATE utf8_unicode_ci NOT NULL,
--  `matricule` CHAR(10) NOT NULL,
  `nbkm_paye` DECIMAL(5,0) NULL,
  `transport_id` INT(11) NOT NULL,
  `duree` TIME NULL,
  `duree_nuit` TIME NULL,
  `duree_tournee` TIME NULL,
  `duree_reperage` TIME NULL,
  `duree_supplement` TIME NULL,
  `nbcli` DECIMAL(6,0) NULL,
  `nbrep` DECIMAL(6,0) NULL,
/*  `nbtitre` decimal(6,0) NOT NULL,
  `nbspl` decimal(6,0) NOT NULL,
  `nbprod` decimal(6,0) NOT NULL,*/
  `valrem` DECIMAL(7,5) NULL,
--  valrem_majo DECIMAL(7,5) NULL,
  valrem_corrigee DECIMAL(7,5) NULL,
  majoration DECIMAL(5,2) NULL,
  majoration_nuit DECIMAL(5,2)  NULL,
  duree_nuit_modele TIME NULL,
/*  majoration_poly DECIMAL(5,2)  NULL,
  majoration_df DECIMAL(5,2)  NULL,
  remuneration DECIMAL(8,5)  NULL,*/
  PRIMARY KEY (`id`),
  INDEX `fk_pai_ev_tournee_depot1_idx` (`depot_id` ASC),
  INDEX `fk_pai_ev_tournee_ref_flux1_idx` (`flux_id` ASC),
  INDEX `fk_pai_ev_tournee_employe1_idx` (`employe_id` ASC),
  INDEX `fk_pai_ev_tournee_ref_typejour1_idx` (`typejour_id` ASC),
  INDEX `fk_pai_ev_tournee_ref_jour1_idx` (`jour_id` ASC),
  CONSTRAINT `fk_pai_ev_tournee_depot1`
    FOREIGN KEY (`depot_id`)
    REFERENCES `depot` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_tournee_ref_flux1`
    FOREIGN KEY (`flux_id`)
    REFERENCES `ref_flux` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_tournee_employe1`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_tournee_ref_typejour1`
    FOREIGN KEY (`typejour_id`)
    REFERENCES `ref_typejour` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_tournee_ref_jour1`
    FOREIGN KEY (`jour_id`)
    REFERENCES `ref_jour` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  INDEX `fk_pai_ev_tournee_ref_transport1_idx` (`transport_id` ASC),
  CONSTRAINT `fk_pai_ev_tournee_ref_transport1`
    FOREIGN KEY (`transport_id`)
    REFERENCES `ref_transport` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
alter table pai_ev_tournee add heure_debut time;
alter table pai_ev_tournee_hst add heure_debut time;
alter table pai_ev_activite add heure_debut time;
alter table pai_ev_activite_hst add heure_debut time;


DROP TABLE IF EXISTS `pai_ev_produit`;
CREATE TABLE IF NOT EXISTS `pai_ev_produit` (
  `id` INT(11) NOT NULL,
  `tournee_id` INT(11) NOT NULL,
  `produit_id` INT(11) NOT NULL,
  `natureclient_id` INT(11) NOT NULL,
  `typeurssaf_id` INT(11),
  `typeproduit_id` INT(11),
  `qte` DECIMAL(5,0) NOT NULL,
  `nbcli` DECIMAL(4,0) NOT NULL,
  `nbrep` DECIMAL(4,0) NOT NULL,
  `duree_supplement` time NOT NULL,
  `pai_qte` DECIMAL(9,2) NULL DEFAULT NULL,
  `pai_taux` DECIMAL(8,3) NULL DEFAULT NULL,
  `pai_val` DECIMAL(9,2) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_pai_ev_produit_pai_ev_tournee1_idx` (`tournee_id` ASC),
  CONSTRAINT `fk_pai_ev_produit_pai_ev_tournee1`
    FOREIGN KEY (`tournee_id`)
    REFERENCES `pai_ev_tournee` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

alter table pai_ev_produit add duree_reperage time not null;

DROP TABLE IF EXISTS `pai_ev_activite`;
CREATE TABLE IF NOT EXISTS `pai_ev_activite` (
  `id` INT(11) NOT NULL,
  `depot_id` INT(11) NOT NULL,
  `flux_id` INT(11) NOT NULL,
  `employe_id` INT(11) NOT NULL,
  `activite_id` INT(11) NOT NULL,
  `typejour_id` INT(11) NOT NULL,
  `jour_id` INT(11) NOT NULL,
  `date_distrib` DATE NOT NULL,
  `duree` TIME NOT NULL,
  `duree_nuit` TIME NOT NULL,
  `duree_garantie` TIME NOT NULL,
  `nbkm_paye` DECIMAL(5,0) NOT NULL,
  `transport_id` INT(11) NOT NULL,
  `qte` DECIMAL(4,0) NOT NULL,
  ouverture tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_pai_ev_activite_depot1_idx` (`depot_id` ASC),
  INDEX `fk_pai_ev_activite_ref_flux1_idx` (`flux_id` ASC),
  INDEX `fk_pai_ev_activite_employe1_idx` (`employe_id` ASC),
  INDEX `fk_pai_ev_activite_ref_typejour1_idx` (`typejour_id` ASC),
  INDEX `fk_pai_ev_activite_ref_jour1_idx` (`jour_id` ASC),
  INDEX `fk_pai_ev_activite_ref_activite1_idx` (`activite_id` ASC),
  CONSTRAINT `fk_pai_ev_activite_depot1`
    FOREIGN KEY (`depot_id`)
    REFERENCES `depot` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_activite_ref_flux1`
    FOREIGN KEY (`flux_id`)
    REFERENCES `ref_flux` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_activite_employe1`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_activite_ref_typejour1`
    FOREIGN KEY (`typejour_id`)
    REFERENCES `ref_typejour` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_activite_ref_jour1`
    FOREIGN KEY (`jour_id`)
    REFERENCES `ref_jour` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_activite_ref_activite1`
    FOREIGN KEY (`activite_id`)
    REFERENCES `ref_activite` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  INDEX `fk_pai_ev_activite_ref_transport1_idx` (`transport_id` ASC),
  CONSTRAINT `fk_pai_ev_activite_ref_transport1`
    FOREIGN KEY (`transport_id`)
    REFERENCES `ref_transport` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `pai_ev_reclamation`;
CREATE TABLE IF NOT EXISTS `pai_ev_reclamation` (
  `id` INT(11) NOT NULL,
  `tournee_id` INT(11) NOT NULL,
  `nbrec_abonne` DECIMAL(4,0) NOT NULL,
  `nbrec_diffuseur` DECIMAL(4,0) NOT NULL,
  INDEX `fk_pai_ev_reclamation_pai_ev_tournee1_idx` (`tournee_id` ASC)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

alter table pai_ev_reclamation add `nbrec_abonne_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_reclamation add `nbrec_diffuseur_brut` DECIMAL(4,0) NOT NULL;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- Travail
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS `pai_ev_majoration_df`;
/*
CREATE TABLE IF NOT EXISTS `pai_ev_majoration_df` (
  `date_distrib` DATE NOT NULL,
  `employe_id` INT(11) NOT NULL,
  `majoration_df` DECIMAL(5,3) NOT NULL,
  INDEX `fk_pai_ev_majoration_df_employe1_idx` (`employe_id` ASC),
  CONSTRAINT `fk_pai_ev_majoration_df_employe1`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
*/
DROP TABLE IF EXISTS `pai_ev_qualite_1M`;
CREATE TABLE IF NOT EXISTS `pai_ev_qualite_1M` (
  `date_distrib` DATE NOT NULL,
  `nbcli` DECIMAL(6,0) NULL DEFAULT NULL,
  `qualite` DECIMAL(10,0) NULL DEFAULT NULL,
  `employe_id` INT(11) NOT NULL,
  INDEX `fk_pai_ev_qualite_1M_employe1_idx` (`employe_id` ASC),
  CONSTRAINT `fk_pai_ev_qualite_1M_employe1`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
/*
DROP TABLE `pai_ev_serpentin`;
CREATE TABLE IF NOT EXISTS `pai_ev_serpentin`(
  `matricule` VARCHAR(10) NOT NULL,
  `rc` VARCHAR(6) NULL DEFAULT NULL,
  `datesmic` DATE NULL DEFAULT NULL,
  `date_debut` DATE NULL DEFAULT NULL,
  `date_fin` DATE NULL DEFAULT NULL,
  `qte` DECIMAL(12,5) NULL DEFAULT NULL,
  `taux` DECIMAL(12,7) NULL DEFAULT NULL,
  `val` DECIMAL(12,5) NULL DEFAULT NULL
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


DROP TABLE `pai_ev_km`;
CREATE TABLE IF NOT EXISTS `pai_ev_km`(
  `matricule` CHAR(10) NOT NULL,
  `rc` VARCHAR(6) NOT NULL,
  `datev` DATE NOT NULL,
  `poste` VARCHAR(10) NOT NULL,
  `qte` DECIMAL(9,2) NOT NULL,
  `libelle` VARCHAR(30) NOT NULL)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- ev
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS `pai_ev_qualite`;
CREATE TABLE IF NOT EXISTS `pai_ev_qualite` (
  `idtrt` INT(11) NOT NULL,
  `employe_id` INT(11) NOT NULL,
  `datev` DATE NOT NULL,
  `code` varchar(1) NOT NULL,
  `taux` DECIMAL(7,3) NOT NULL,
  INDEX `fk_pai_ev_qualite_employe1_idx` (`employe_id` ASC),
  INDEX `fk_pai_ev_qualite_pai_int_traitement1_idx` (`idtrt` ASC),
  CONSTRAINT `fk_pai_ev_qualite_employe1`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_ev_qualite_pai_int_traitement1`
    FOREIGN KEY (`idtrt`)
    REFERENCES `pai_int_traitement` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 /*
DROP TABLE IF EXISTS `pai_ev_prime`;
CREATE TABLE IF NOT EXISTS `pai_ev_prime` (
  `employe_id` INT(11) NOT NULL,
  `datev` DATE NOT NULL,
  `code` varchar(1) NOT NULL,
  `taux` DECIMAL(7,3) NOT NULL,
  INDEX `fk_pai_ev_prime_employe1_idx` (`employe_id` ASC),
  CONSTRAINT `fk_pai_ev_prime_employe1`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
*/
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS `pai_ev`;
CREATE TABLE IF NOT EXISTS `pai_ev` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `typev` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `matricule` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `rc` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  `poste` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `datev` DATE NOT NULL,
  `ordre` DECIMAL(2,0) NULL DEFAULT NULL,
  `qte` DECIMAL(9,2) NULL DEFAULT NULL,
  `taux` DECIMAL(8,3) NULL DEFAULT NULL,
  `val` DECIMAL(9,2) NULL DEFAULT NULL,
  `libelle` VARCHAR(150) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `res` VARCHAR(175) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_ev_idx1 on pai_ev(matricule,datev,poste);
create index pai_ev_idx2 on pai_ev(typev,matricule,datev);

/*
DROP TABLE IF EXISTS `pai_ev_numerotation`;
CREATE TABLE IF NOT EXISTS `pai_ev_numerotation` (
  `matricule` CHAR(10) NOT NULL,
  `poste` VARCHAR(10) NOT NULL,
  `datev` DATE NOT NULL,
  `ordre` DECIMAL(2,0) NULL DEFAULT NULL,
  `taux` DECIMAL(8,3) NULL DEFAULT NULL,
  `_matricule` CHAR(10) NOT NULL,
  `_poste` VARCHAR(10) NOT NULL,
  `_datev` DATE NOT NULL)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
*/
DROP TABLE IF EXISTS `pai_ev_correction`;
CREATE TABLE IF NOT EXISTS `pai_ev_correction` (
  `id` INT(11) NOT NULL,
  `typev` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `matricule` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `datev` DATE NOT NULL,
  `qte` DECIMAL(9,2) NOT NULL,
  `sumqte` DECIMAL(9,2) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;



DROP TABLE IF EXISTS `pai_ev_diff`;
CREATE TABLE IF NOT EXISTS `pai_ev_diff`(
  `idtrt1` INT(11) NOT NULL,
  `idtrt2` INT(11) NOT NULL,
  `diff` VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL,
  `typev` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL,
  `matricule` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `rc` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  `poste` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `datev` DATE NOT NULL,
  `ordre` DECIMAL(2,0) NULL DEFAULT NULL,
  `libelle` VARCHAR(150) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `qte1` DECIMAL(9,2) NULL DEFAULT NULL,
  `qte2` DECIMAL(9,2) NULL DEFAULT NULL,
  `taux1` DECIMAL(8,3) NULL DEFAULT NULL,
  `taux2` DECIMAL(8,3) NULL DEFAULT NULL,
  `val1` DECIMAL(9,2) NULL DEFAULT NULL,
  `val2` DECIMAL(9,2) NULL DEFAULT NULL)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
alter table pai_ev_diff add   CONSTRAINT `fk_pai_ev_annexe_hst_pai_int_traitement1` FOREIGN KEY (`idtrt1`) REFERENCES `pai_int_traitement` (`id`);
alter table pai_ev_diff add   CONSTRAINT `fk_pai_ev_annexe_hst_pai_int_traitement2` FOREIGN KEY (`idtrt2`) REFERENCES `pai_int_traitement` (`id`);

drop index pai_ev_diff_idx1 on pai_ev_diff;
create index pai_ev_diff_idx1 on pai_ev_diff(idtrt1,idtrt2,diff);

/*  
  DROP VIEW  pai_ev_taux;
  CREATE VIEW  pai_ev_taux (date_distrib, matricule, rc, eta, CODE, valrem, nbcli, typeurssaf_id, typejour_is, date_debut, date_fin, valrem2) AS 
  SELECT t.date_distrib,
    e.matricule,
    e.rc,
    e.eta,
    t.code,
    t.valrem,
    t.nbcli,
    t.typeurssaf_id,
    t.typejour_id,
    e.date_debut,
    e.date_fin,
    pai_ev_taux_maj(t.typejour_id,t.nbcli,COALESCE(q.qualite,g.txqport),t.duree_nuit,g.majonuit/100,e.pol,g.majopoly/100,s.valeur,t.valrem)
  FROM pai_ev_emp_pop_depot e
  INNER JOIN pai_ev_tournee t ON e.employe_id=t.employe_id AND e.depot_id=t.depot_id AND e.flux_id=t.flux_id
  INNER JOIN pai_ev_produit p ON t.id=p.tournee_id
  INNER JOIN pai_ref_general g ON 1=1
  INNER JOIN pai_ref_remuneration s ON e2.population_id=s.population_id AND e2.societe_id=s.societe_id AND t.date_distrib BETWEEN s.date_debut AND s.date_fin
  LEFT OUTER JOIN pai_ev_qualite_DF q ON t.employe_id=q.employe_id AND t.date_distrib=q.date_distrib 
  WHERE t.date_distrib BETWEEN e.dCtr AND e.f
  AND t.valrem>0;

DELIMITER |
DROP FUNCTION IF EXISTS pai_ev_taux_maj|
CREATE FUNCTION pai_ev_taux_maj(
  _typejour_id   INT
, _nbcli     	NUMERIC(6,0)
, _majodf    	NUMERIC(5,3)
, _duree_nuit    TIME
, _majonuit   	NUMERIC(5,2)
, _polyvalent 	BOOLEAN
, _majopoly 	NUMERIC(5,2)
, _valsmic     	NUMERIC(8,5)
, _valrem     	NUMERIC(7,5)
)
  RETURNS NUMERIC(7,5)
  LANGUAGE SQL -- This element is optional and will be omitted from subsequent examples
  READS SQL DATA
BEGIN
DECLARE   valrem2 NUMERIC(7,5);
  IF (_polyvalent AND typetournee_id=1) THEN
    SET valrem2=_valrem*(1+_majopoly); -- Majoration de 10% pour les polyvalents SDVP
  ELSE
    SET valrem2=_valrem;
  END IF;
  IF (_typejour_id=1) THEN
    RETURN valrem2;
  ELSE
 -- La majoration ne doit pas dépasser 100% (avec les heures de nuit)
    IF _majodf+TIME_TO_SEC(_duree_nuit)/3600*_majonuit*_valsmic/(_nbcli*valrem2)>2 THEN
      RETURN (nbcli*valrem2*2-TIME_TO_SEC(_duree_nuit)/3600*_majonuit*_valsmic)/(_nbcli*(_majodf-1));
    ELSE
      RETURN valrem2*_majodf;
    END IF;
  END IF;
END|
DELIMITER ;
*/
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
