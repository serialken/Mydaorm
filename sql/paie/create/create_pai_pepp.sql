-- -----------------------------------------------------
-- Table `pai_oct_pers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_pepp_heures`;
CREATE TABLE `pai_pepp_heures` (
  `datetr` VARCHAR(8) COLLATE utf8_unicode_ci NOT NULL, 
	`mat` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL, 
	`codeactivite` VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL, 
	`codestatut` VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL, 
	`typejour` VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL, 
	`temps1` NUMERIC(4,2) NOT NULL, 
	`temps2` NUMERIC(4,2) NOT NULL, 
	`nbkm` NUMERIC(4,0) NOT NULL, 
	`eta` VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL, 
	`nomuser` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL, 
	`datemaj` VARCHAR(19) COLLATE utf8_unicode_ci NOT NULL, 
	`codetr` VARCHAR(11) COLLATE utf8_unicode_ci NOT NULL, 
	`commentaire` VARCHAR(100) COLLATE utf8_unicode_ci, 
	`dextrait` VARCHAR(15) COLLATE utf8_unicode_ci, 
	`qte` NUMERIC(4,0) NOT NULL,
	 PRIMARY KEY (`datetr`, `mat`, `codeactivite`))
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
 

  CREATE INDEX `pai_pepp_heures_idx1` ON `pai_pepp_heures` (`dextrait`,`datetr`, `mat`);

DROP TABLE IF EXISTS`pai_pepp_tournees`;
CREATE TABLE `pai_pepp_tournees` (
  `datetr` VARCHAR(8) COLLATE utf8_unicode_ci NOT NULL, 
	`codetr` VARCHAR(11) COLLATE utf8_unicode_ci NOT NULL, 
	`mat` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL, 
	`nbkm` NUMERIC(4,0) NOT NULL, 
	`temps` NUMERIC(4,2) NOT NULL, 
	`valrem` NUMERIC(7,5) NOT NULL, 
	`matchef` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL, 
	`eta` VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL, 
	`typejour` VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL, 
	`codedev` VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL, 
	`trstatus` NUMERIC(4,0) NOT NULL, 
	`nomuser` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL, 
	`datemaj` VARCHAR(19) COLLATE utf8_unicode_ci NOT NULL, 
	`nbkm2` NUMERIC(4,0), 
	`valremc` NUMERIC(7,5), 
	`tnbrcli` NUMERIC(6,0), 
	`tnuit` NUMERIC(4,2), 
	`tatt` NUMERIC(4,2) NOT NULL, 
	`dextrait` VARCHAR(15) COLLATE utf8_unicode_ci, 
	PRIMARY KEY (`datetr`, `codetr`))
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

  CREATE INDEX `pai_pepp_tournees_idx1` ON `pai_pepp_tournees` (`dextrait`,`datetr`, `mat`);

 
 
DROP TABLE IF EXISTS`pai_pepp_tournees_detail`;
CREATE TABLE `pai_pepp_tournees_detail` (
  `datetr` VARCHAR(8) COLLATE utf8_unicode_ci NOT NULL, 
	`codetr` VARCHAR(11) COLLATE utf8_unicode_ci NOT NULL, 
	`codetitre` VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL, 
	`codenatcli` VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL, 
	`typ4pc` VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL, 
	`nbrcli` NUMERIC(4,0) NOT NULL, 
	`nbrex` NUMERIC(5,0) NOT NULL, 
	`nbrspl` NUMERIC(4,0) NOT NULL, 
	`trstatus` NUMERIC(4,0) NOT NULL, 
	`datemaj` VARCHAR(19) COLLATE utf8_unicode_ci NOT NULL, 
	PRIMARY KEY (`datetr`, `codetr`, `codetitre`, `codenatcli`))
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS`pai_pepp_reclamations`;
CREATE TABLE `pai_pepp_reclamations`  (
	`datepaiefm` VARCHAR(8) COLLATE utf8_unicode_ci NOT NULL, 
  `datetr` VARCHAR(8) COLLATE utf8_unicode_ci NOT NULL, 
	`codetr` VARCHAR(11) COLLATE utf8_unicode_ci NOT NULL, 
	`codetitre` VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL,  
	`nbrecab` NUMERIC(4,0) NOT NULL, 
	`nbannab` NUMERIC(4,0) NOT NULL, 
	`nbrecdif` NUMERIC(4,0) NOT NULL, 
	`nbanndif` NUMERIC(4,0) NOT NULL, 
	`indicinc` NUMERIC(1,0) NOT NULL, 
	`anninc` NUMERIC(1,0) NOT NULL, 
	`nomuser` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL, 
	`datemaj` VARCHAR(19) COLLATE utf8_unicode_ci NOT NULL, 
	PRIMARY KEY (`datetr`, `codetr`, `codetitre`, `datepaiefm`))
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS`pai_pepp_transco_activite`;
CREATE TABLE `pai_pepp_transco_activite` (
	`codeactivite` VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL,
	`libelle` VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL,
  `activite_id` INT,
  PRIMARY KEY (`codeactivite`))
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS`pai_pepp_transco_titre`;
CREATE TABLE `pai_pepp_transco_titre` (
	`codetitre` VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL,
	`libelle` VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL,
  `produit_id` INT,
  `supplement_id` INT,
  PRIMARY KEY (`codetitre`))
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS`pai_pepp_bonus`;
CREATE TABLE `pai_pepp_bonus` (
	`trimestre` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
	`matricule` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL,
	`eta` VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL,
	`nbtrnmois1` NUMERIC(4,0) NOT NULL, 
	`nbtrnmois2` NUMERIC(4,0) NOT NULL, 
	`nbtrnmois3` NUMERIC(4,0) NOT NULL, 
	`nbrecab` NUMERIC(4,0) NOT NULL, 
	`nbrecdif` NUMERIC(4,0) NOT NULL, 
	`nbinc` NUMERIC(4,0) NOT NULL, 
  PRIMARY KEY (`trimestre`,`matricule`))
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
