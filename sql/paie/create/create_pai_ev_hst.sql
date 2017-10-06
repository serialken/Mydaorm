SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- Employe
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP table IF EXISTS pai_ev_emp_hst;
CREATE TABLE IF NOT EXISTS `pai_ev_emp_hst` (
  `employe_id` INT(11) NOT NULL,
  `rcoid` VARCHAR(36) COLLATE utf8_unicode_ci NOT NULL,
  `matricule` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
  `rc` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  `dRC` DATE NOT NULL,
  `fRC` DATE NOT NULL,
  `d` DATE NOT NULL,
  `f` DATE NOT NULL,
  `anneemois` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  idtrt int)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_ev_emp_hst_idx1 on pai_ev_emp_hst(idtrt,employe_id,d,f);

DROP table IF EXISTS pai_ev_emp_depot_hst;
CREATE TABLE IF NOT EXISTS `pai_ev_emp_depot_hst` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
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
  nbkm_paye decimal(7,1),
  nbcli decimal(8,0),
  idtrt int)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_ev_emp_depot_hst_idx1 on pai_ev_emp_depot_hst(idtrt,depot_id,employe_id);

-- alter table pai_ev_emp_depot_hst add `id` INT(11) NOT NULL AUTO_INCREMENT FIRST ,add PRIMARY KEY (`id`);

DROP TABLE IF EXISTS `pai_ev_emp_pop_depot_hst`;
CREATE TABLE IF NOT EXISTS `pai_ev_emp_pop_depot_hst` (
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
  idtrt int)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create INDEX `pai_ev_emp_pop_depot_hst_idx1` on `pai_ev_emp_pop_depot_hst` (`idtrt` ASC,employe_id ASC,`d` ASC);

DROP TABLE IF EXISTS `pai_ev_emp_pop_hst`;
CREATE TABLE IF NOT EXISTS `pai_ev_emp_pop_hst` (
  `employe_id` INT(11) NOT NULL,
  `population_id` INT(11) NOT NULL,
  `typetournee_id` INT(11) NOT NULL,
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
  idtrt int)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
-- alter table pai_ev_emp_pop_hst add `nbrec` DECIMAL(4,0) NOT NULL;
-- alter table pai_ev_emp_pop_hst add `taux` DECIMAL(7,3) NOT NULL;
alter table pai_ev_emp_pop_hst add emploi_code VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL;
alter table pai_ev_emp_pop_hst add `societe_id` INT(11) NOT NULL;
alter table pai_ev_emp_pop_hst modify `population_id` INT(11) NULL;
alter table pai_ev_emp_pop_hst modify `typetournee_id` INT(11) NULL;
update pai_ev_emp_pop_hst set societe_id=typetournee_id;
update pai_ev_emp_pop_hst e
inner join ref_population rp on e.population_id=rp.id
inner join ref_emploi re on rp.emploi_id=re.id
set emploi_code=re.code;
alter table pai_ev_emp_pop_hst drop typetournee_id;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- Tournee, produit, activite
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS `pai_ev_tournee_hst`;
CREATE TABLE IF NOT EXISTS `pai_ev_tournee_hst` (
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
  idtrt int)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_ev_tournee_hst_idx1 on pai_ev_tournee_hst(idtrt,id);
create index pai_ev_tournee_hst_idx2 on pai_ev_tournee_hst(idtrt,employe_id,date_distrib);
/*
ALTER TABLE mroad.pai_ev_tournee_hst
 ADD duree_tournee TIME,
 ADD duree_supplement TIME,
 ADD duree_reperage TIME;
*/
 
DROP TABLE IF EXISTS `pai_ev_produit_hst`;
CREATE TABLE IF NOT EXISTS `pai_ev_produit_hst` (
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
  idtrt int)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_ev_produit_hst_idx1 on pai_ev_produit_hst(idtrt,tournee_id,id);
alter table pai_ev_produit_hst add duree_reperage time not null;

DROP TABLE IF EXISTS `pai_ev_activite_hst`;
CREATE TABLE IF NOT EXISTS `pai_ev_activite_hst` (
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
  `nbkm_paye` DECIMAL(5,0) NOT NULL,
  `qte` DECIMAL(4,0) NOT NULL,
  ouverture tinyint(1) NOT NULL,
  idtrt int)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
ALTER TABLE pai_ev_activite_hst add transport_id int(11);
ALTER TABLE pai_ev_activite_hst add `duree_garantie` TIME default '00:00' NOT NULL;
create index pai_ev_activite_hst_idx1 on pai_ev_activite_hst(idtrt,id);
create index pai_ev_activite_hst_idx2 on pai_ev_activite_hst(idtrt,employe_id,date_distrib);

DROP TABLE IF EXISTS `pai_ev_reclamation_hst`;
CREATE TABLE IF NOT EXISTS `pai_ev_reclamation_hst` (
  `id` INT(11) NOT NULL,
  `tournee_id` INT(11) NOT NULL,
  `nbrec_abonne` DECIMAL(4,0) NOT NULL,
  `nbrec_diffuseur` DECIMAL(4,0) NOT NULL,
  idtrt int)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_ev_reclamation_hst_idx1 on pai_ev_reclamation_hst(idtrt,tournee_id,id);

alter table pai_ev_reclamation_hst add `nbrec_abonne_brut` DECIMAL(4,0) NOT NULL;
alter table pai_ev_reclamation_hst add `nbrec_diffuseur_brut` DECIMAL(4,0) NOT NULL;
update pai_ev_reclamation_hst set nbrec_abonne_brut=nbrec_abonne;
update pai_ev_reclamation_hst set nbrec_diffuseur_brut=nbrec_diffuseur;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- Travail
 -- ------------------------------------------------------------------------------------------------------------------------------------------------

DROP TABLE IF EXISTS `pai_ev_hst`;
CREATE TABLE IF NOT EXISTS `pai_ev_hst`(
  `typev` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL,
  rcoid varchar(36) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
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
  `idtrt` INT(11) NOT NULL,
  INDEX `fk_pai_ev_hst_pai_int_traitement1_idx` (`idtrt` ASC),
  CONSTRAINT `fk_pai_ev_hst_pai_int_traitement1`
    FOREIGN KEY (`idtrt`)
    REFERENCES `pai_int_traitement` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create INDEX `pai_ev_hst_idx1` on `pai_ev_hst` (`idtrt` ASC,rcoid ASC,`datev` ASC,`poste` ASC,ordre ASC);
create INDEX `pai_ev_hst_idx2` on `pai_ev_hst` (`idtrt` ASC,matricule ASC,`poste` ASC,`datev` ASC);


DROP TABLE IF EXISTS `pai_ev_annexe_hst`;
CREATE TABLE IF NOT EXISTS `pai_ev_annexe_hst` (
  idtrt INT NOT NULL,
  `employe_depot_hst_id` INT(11) NOT NULL,
  `date_distrib` DATE NOT NULL,
  libelle varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `qte` DECIMAL(9,2) NULL DEFAULT NULL,
  `taux` DECIMAL(10,5) NULL DEFAULT NULL,
  `val` DECIMAL(12,5) NULL DEFAULT NULL,
  `duree_tournee` TIME NULL,
  `duree_activite` TIME NULL,
  `duree_autre` TIME NULL,
  `duree_nuit` TIME NULL,
  `duree_totale` TIME NULL,
  `nb_reclamation` DECIMAL(6,0) NULL,
  `nb_incident` DECIMAL(6,0) NULL,
  `nbkm_paye` DECIMAL(5,0) NULL)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
drop index pai_ev_annexe_hst_idx1 on pai_ev_annexe_hst;
create index pai_ev_annexe_hst_idx1 on pai_ev_annexe_hst(idtrt,employe_depot_hst_id,date_distrib);
-- ALTER TABLE pai_ev_tournee_hst add `duree_autre` TIME NULL;
 ALTER TABLE pai_ev_annexe_hst add `nbrec_abonne` DECIMAL(6,0) NULL;
 ALTER TABLE pai_ev_annexe_hst add `nbrec_diffuseur` DECIMAL(6,0) NULL;
update pai_ev_annexe_hst set nb_rec_abo=nb_reclamation;
 -- ------------------------------------------------------------------------------------------------------------------------------------------------
 -- ------------------------------------------------------------------------------------------------------------------------------------------------

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
