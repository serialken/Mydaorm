SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema silog
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema new_schema1
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `pai_png_salarie`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_salarie` ;
CREATE TABLE IF NOT EXISTS `pai_png_salarie` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `matricule` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `prenom1` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `prenom2` varchar(25) COLLATE utf8_unicode_ci NULL,
  `nompatronymique` varchar(40) COLLATE utf8_unicode_ci NULL,
  `nom_usuel` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`oid`))
 ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

alter table `pai_png_salarie` add civilite_id INT NOT NULL;
alter table `pai_png_salarie` add date_sejour DATE;
alter table `pai_png_salarie` add `nationalite` varchar(36) COLLATE utf8_unicode_ci NOT NULL;
-- -----------------------------------------------------
-- Table `pai_png_societe`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_societe` ;
CREATE TABLE IF NOT EXISTS `pai_png_societe` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `societecode` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `societenom` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`oid`),
  UNIQUE INDEX `un_societe` (`societecode` ASC))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
-- -----------------------------------------------------
-- Table `pai_png_relationcontrat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_relationcontrat` ;
CREATE TABLE IF NOT EXISTS `pai_png_relationcontrat` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `relatmatricule` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `relatnum` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `relatsociete` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `relatdatedeb` DATE NOT NULL,
  `relatdatefinw` DATE NULL,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_relationcontrat_pai_png_salarie_idx` (`relatmatricule` ASC),
  INDEX `fk_pai_png_relationcontrat_pai_png_societe1_idx` (`relatsociete` ASC),
  CONSTRAINT `fk_pai_png_relationcontrat_pai_png_salarie`
    FOREIGN KEY (`relatmatricule`)
    REFERENCES `pai_png_salarie` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_png_relationcontrat_pai_png_societe1`
    FOREIGN KEY (`relatsociete`)
    REFERENCES `pai_png_societe` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
alter table pai_png_relationcontrat add	relatmotifdeb varchar(36) COLLATE utf8_unicode_ci NOT NULL;
alter table pai_png_relationcontrat add	relatmotiffin varchar(36) COLLATE utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `pai_png_salpopulationw`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_salpopulationw` ;
CREATE TABLE IF NOT EXISTS `pai_png_salpopulationw` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `salarie` varchar(36) NOT NULL,
  `begin_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `numrc` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `nocarrierealpha` varchar(2) COLLATE utf8_unicode_ci NULL,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_pngsalpopulationw_pai_png_salarie1_idx` (`salarie` ASC),
  CONSTRAINT `fk_pai_pngsalpopulationw_pai_png_salarie1`
    FOREIGN KEY (`salarie`)
    REFERENCES `pai_png_salarie` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_png_salpopulationw_idx on pai_png_salpopulationw(salarie,numrc);


-- -----------------------------------------------------
-- Table `pai_png_contrat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_contrat` ;
CREATE TABLE IF NOT EXISTS `pai_png_contrat` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `ctrrelation` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_contrat_pai_png_relationcontrat1_idx` (`ctrrelation` ASC),
  CONSTRAINT `fk_pai_png_contrat_pai_png_relationcontrat1`
    FOREIGN KEY (`ctrrelation`)
    REFERENCES `pai_png_relationcontrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
-- -----------------------------------------------------
-- Table `pai_png_suspension`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_suspension` ;
CREATE TABLE IF NOT EXISTS `pai_png_suspension` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `suspcontrat` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `begin_date` DATE NOT NULL,
  `end_date` DATE NULL,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_suspension_pai_png_contrat1_idx` (`suspcontrat` ASC),
  CONSTRAINT `fk_pai_png_suspension_pai_png_contrat1`
    FOREIGN KEY (`suspcontrat`)
    REFERENCES `pai_png_contrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
-- -----------------------------------------------------
-- Table `pai_png_suspension`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_anciennetectr` ;
CREATE TABLE IF NOT EXISTS `pai_png_anciennetectr` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `ancctrmatricule` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `begin_date` DATE NOT NULL,
  `end_date` DATE NULL, 
  `ancctrgroupe` DATE NOT NULL,
  `ancctrsociete` DATE NULL, 
  `ancctrpaie` DATE NULL,  
  `ancctrmaladiew` DATE NOT NULL, 
  PRIMARY KEY (`oid`),
  CONSTRAINT `fk_pai_png_anciennetectr_pai_png_contrat1`
    FOREIGN KEY (`ancctrmatricule`)
    REFERENCES `pai_png_contrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

 

-- -----------------------------------------------------
-- Table `pai_png_etablissement`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_etablissement` ;
CREATE TABLE IF NOT EXISTS `pai_png_etablissement` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `etabcode` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `etabnom` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `etabnomc` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `etabdebut` DATE NOT NULL,
  `etabfin` DATE NULL,
  `etabadrnumero` DECIMAL(4,0) NULL,
  `etabadrnumcpt` varchar(36) COLLATE utf8_unicode_ci NULL,
  `etaadrvoietype` varchar(36) COLLATE utf8_unicode_ci NULL,
  `etabadrvoie` varchar(22) COLLATE utf8_unicode_ci NULL,
  `etabadrcommune` varchar(26) COLLATE utf8_unicode_ci NULL,
  `etabadrcp` varchar(5) COLLATE utf8_unicode_ci NULL,
  PRIMARY KEY (`oid`))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
-- -----------------------------------------------------
-- Table `pai_png_etablissrel`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_etablissrel` ;
CREATE TABLE IF NOT EXISTS `pai_png_etablissrel` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `etabrelation` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `begin_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `etabrel` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_etablissrel_pai_png_relationcontrat1_idx` (`etabrelation` ASC),
  INDEX `fk_pai_png_etablissrel_pai_png_etablissement1_idx` (`etabrel` ASC),
  CONSTRAINT `fk_pai_png_etablissrel_pai_png_relationcontrat1`
    FOREIGN KEY (`etabrelation`)
    REFERENCES `pai_png_relationcontrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_png_etablissrel_pai_png_etablissement1`
    FOREIGN KEY (`etabrel`)
    REFERENCES `pai_png_etablissement` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
-- -----------------------------------------------------
-- Table `pai_png_ta_emploi`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_ta_emploi` ;
CREATE TABLE IF NOT EXISTS `pai_png_ta_emploi` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `emploicode` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `emploilibelle` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `emploilib` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `pai_png_ta_emploicol` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`oid`))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
-- -----------------------------------------------------
-- Table `pai_png_emploi`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_emploi` ;
CREATE TABLE IF NOT EXISTS `pai_png_emploi` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `emploirelation` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `emploi` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `begin_date` DATE NOT NULL,
  `end_date` DATE NULL,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_emploi_pai_png_relationcontrat1_idx` (`emploirelation` ASC),
  INDEX `fk_pai_png_emploi_pai_png_ta_emploi1_idx` (`emploi` ASC),
  CONSTRAINT `fk_pai_png_emploi_pai_png_relationcontrat1`
    FOREIGN KEY (`emploirelation`)
    REFERENCES `pai_png_relationcontrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_png_emploi_pai_png_ta_emploi1`
    FOREIGN KEY (`emploi`)
    REFERENCES `pai_png_ta_emploi` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
-- -----------------------------------------------------
-- Table `pai_png_ta_proprietaire`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_ta_proprietaire` ;
CREATE TABLE IF NOT EXISTS `pai_png_ta_proprietaire` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `libcourt` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`oid`))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
-- -----------------------------------------------------
-- Table `pai_png_vehiculew`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_vehiculew` ;
CREATE TABLE IF NOT EXISTS `pai_png_vehiculew` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `salarie` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `begin_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `immatriculation` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `assureur` varchar(20) COLLATE utf8_unicode_ci NULL,
  `dtdebassur` DATE NULL,
  `dtfinassur` DATE NULL,
  `dtvaliditect` DATE NULL,
  `ta_proprietaire` varchar(36) COLLATE utf8_unicode_ci NULL,
  `utilisation` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(4000) COLLATE utf8_unicode_ci NULL,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_vehiculew_pai_png_salarie1_idx` (`salarie` ASC),
  INDEX `fk_pai_png_vehiculew_pai_png_ta_proprietaire1_idx` (`ta_proprietaire` ASC),
  CONSTRAINT `fk_pai_png_vehiculew_pai_png_salarie1`
    FOREIGN KEY (`salarie`)
    REFERENCES `pai_png_salarie` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_png_vehiculew_pai_png_ta_proprietaire1`
    FOREIGN KEY (`ta_proprietaire`)
    REFERENCES `pai_png_ta_proprietaire` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

    -- -----------------------------------------------------
-- Table `pai_png_saletranger`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_saletranger` ;
CREATE TABLE IF NOT EXISTS `pai_png_saletranger` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `etrangmatricule` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `begin_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `sejourcarte` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `etrangnumero` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `administration` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `etranglieu` varchar(30) COLLATE utf8_unicode_ci,
  `etrangduree` numeric(3,1),
  `etrangactivite` varchar(100) COLLATE utf8_unicode_ci,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_saletranger_pai_png_salarie1_idx` (`etrangmatricule` ASC),
  CONSTRAINT `fk_pai_png_saletranger_pai_png_salarie1`
    FOREIGN KEY (`etrangmatricule`)
    REFERENCES `pai_png_salarie` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `pai_png_medicalvisite`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_medicalvisiste` ;
CREATE TABLE IF NOT EXISTS `pai_png_medicalvisite` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `medicmatricule` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `bgndate` DATE NOT NULL,
  `medicprochain` DATE NULL,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_medicalvisiste_pai_png_salarie1_idx` (`medicmatricule` ASC),
  CONSTRAINT `fk_pai_png_medicalvisite_pai_png_salarie1`
    FOREIGN KEY (`medicmatricule`)
    REFERENCES `pai_png_salarie` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

 -- -----------------------------------------------------
-- Table `pai_png_ta_populationw`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_ta_populationw` ;
CREATE TABLE IF NOT EXISTS `pai_png_ta_populationw` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`oid`))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
create index pai_png_ta_populationw_idx on pai_png_ta_populationw(code);

-- -----------------------------------------------------
-- Table `pai_png_rcpopulationw`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_rcpopulationw` ;
CREATE TABLE IF NOT EXISTS `pai_png_rcpopulationw` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `relationcontrat` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `begin_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `population` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `populationprec` varchar(36) COLLATE utf8_unicode_ci NULL,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_rcpopulationw_pai_png_relationcontrat1_idx` (`relationcontrat` ASC),
  INDEX `fk_pai_png_rcpopulationw_pai_png_ta_populationw1_idx` (`population` ASC),
  CONSTRAINT `fk_pai_png_rcpopulationw_pai_png_relationcontrat1`
    FOREIGN KEY (`relationcontrat`)
    REFERENCES `pai_png_relationcontrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_png_rcpopulationw_pai_png_ta_populationw1`
    FOREIGN KEY (`population`)
    REFERENCES `pai_png_ta_populationw` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_png_rcpopulationw_pai_png_ta_populationw2`
    FOREIGN KEY (`populationprec`)
    REFERENCES `pai_png_ta_populationw` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
CREATE INDEX pai_png_rcpopulationw_idx ON pai_png_rcpopulationw(relationcontrat,begin_date,end_date);

-- -----------------------------------------------------
-- Table `pai_png_tarifhorairew`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_tarifhorairew` ;
CREATE TABLE IF NOT EXISTS `pai_png_tarifhorairew` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `societe` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `population` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `begin_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `tauxhoraire` DECIMAL(8,5) NOT NULL,
  `tauxhorairedisc` DECIMAL(8,5),
  `tauxhorairedis2` DECIMAL(8,5),
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_tarifhorairew_pai_png_societe1_idx` (`societe` ASC),
  INDEX `fk_pai_png_tarifhorairew_pai_png_ta_populationw1_idx` (`population` ASC),
  CONSTRAINT `fk_pai_png_tarifhorairew_pai_png_societe1`
    FOREIGN KEY (`societe`)
    REFERENCES `pai_png_societe` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_png_tarifhorairew_pai_png_ta_populationw1`
    FOREIGN KEY (`population`)
    REFERENCES `pai_png_ta_populationw` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
-- -----------------------------------------------------
-- Table `pai_png_legalferie`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_legalferie` ;
CREATE TABLE IF NOT EXISTS `pai_png_legalferie` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `legalferiedate` DATE NOT NULL,
  PRIMARY KEY (`oid`))
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `pai_png_xhorporpol`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_xhorporpol` ;
CREATE TABLE IF NOT EXISTS `pai_png_xhorporpol` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	relationcontrat varchar(36) COLLATE utf8_unicode_ci NOT NULL, 
	begin_date DATE NOT NULL, 
	end_date DATE NOT NULL, 
	horcontractuel NUMERIC(5,2) NOT NULL, 
	heuredebctr NUMERIC(9,0),
  PRIMARY KEY (`oid`),
	 CONSTRAINT `ak_xhorporpol` UNIQUE (`relationcontrat`, `begin_date`),
  CONSTRAINT `fk_pai_png_xhorporpol_001`
    FOREIGN KEY (`relationcontrat`)
    REFERENCES `pai_png_relationcontrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION 
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
CREATE INDEX pai_png_xhorporpol_idx ON pai_png_xhorporpol(relationcontrat,begin_date,end_date);


-- -----------------------------------------------------
-- Table `pai_png_xhorporpol`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_horaire` ;
DROP TABLE IF EXISTS `pai_png_semainetype` ;
CREATE TABLE IF NOT EXISTS `pai_png_semainetype` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	semtypecode VARCHAR(3) NOT NULL, 
	semtypelibelle VARCHAR(70) NOT NULL, 
	semtypedurlun NUMERIC(4,2) NOT NULL DEFAULT 0, 
	semtypedurmar NUMERIC(4,2) NOT NULL DEFAULT 0, 
	semtypedurmer NUMERIC(4,2) NOT NULL DEFAULT 0, 
	semtypedurjeu NUMERIC(4,2) NOT NULL DEFAULT 0, 
	semtypedurven NUMERIC(4,2) NOT NULL DEFAULT 0, 
	semtypedursam NUMERIC(4,2) NOT NULL DEFAULT 0, 
	semtypedurdim NUMERIC(4,2) NOT NULL DEFAULT 0, 
  PRIMARY KEY (`oid`)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

 
-- -----------------------------------------------------
-- Table `pai_png_horaire`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_horaire` ;
CREATE TABLE IF NOT EXISTS `pai_png_horaire` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	horcontr varchar(36) COLLATE utf8_unicode_ci NOT NULL, 
	begin_date DATE NOT NULL, 
	end_date DATE NOT NULL, 
	horsemainetype varchar(36) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	 CONSTRAINT `ak_horaire` UNIQUE (`horcontr`, `begin_date`),
  CONSTRAINT `fk_pai_png_horaire_002`
    FOREIGN KEY (`horcontr`)
    REFERENCES `pai_png_relationcontrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION ,
  CONSTRAINT `fk_pai_png_horaire_007`
    FOREIGN KEY (`horsemainetype`)
    REFERENCES `pai_png_semainetype` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION 
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
CREATE INDEX pai_png_horaire_idx ON pai_png_horaire(horcontr,begin_date,end_date);

-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_xta_rcactivite` ;
CREATE TABLE IF NOT EXISTS `pai_png_xta_rcactivite` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	code varchar(5) COLLATE utf8_unicode_ci NOT NULL, 
	libelle varchar(80) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_xta_rcactivite` UNIQUE (`code`)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_xta_rcmetier` ;
CREATE TABLE IF NOT EXISTS `pai_png_xta_rcmetier` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	code varchar(5) COLLATE utf8_unicode_ci NOT NULL, 
	libelle varchar(80) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_xta_rcmetier` UNIQUE (`code`)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_xta_rcactivhpre` ;
CREATE TABLE IF NOT EXISTS `pai_png_xta_rcactivhpre` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	code varchar(2) COLLATE utf8_unicode_ci NOT NULL, 
	libelle varchar(80) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_pai_png_xta_rcactivhpre` UNIQUE (`code`)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_xrcautreactivit` ;
CREATE TABLE IF NOT EXISTS `pai_png_xrcautreactivit` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	relationcontrat varchar(36) COLLATE utf8_unicode_ci NOT NULL, 
	ordre NUMERIC(2,0), 
	begin_date DATE NOT NULL, 
	end_date DATE NOT NULL,
  -- heureDebCtr,
	trvlundi varchar(1) COLLATE utf8_unicode_ci NOT NULL, 
	trvmardi varchar(1) COLLATE utf8_unicode_ci NOT NULL, 
	trvmercredi varchar(1) COLLATE utf8_unicode_ci NOT NULL, 
	trvjeudi varchar(1) COLLATE utf8_unicode_ci NOT NULL, 
	trvvendredi varchar(1) COLLATE utf8_unicode_ci NOT NULL, 
	trvsamedi varchar(1) COLLATE utf8_unicode_ci NOT NULL, 
	trvdimanche varchar(1) COLLATE utf8_unicode_ci NOT NULL, 
	heuredebctr NUMERIC(9,0), 
	nbheurejr NUMERIC(5,2) NOT NULL DEFAULT 0, 
	nbrheuremensuel NUMERIC(5,2) NOT NULL DEFAULT 0, 
	xta_rcactivte varchar(36) COLLATE utf8_unicode_ci NOT NULL, 
	xta_rcmetier varchar(36) COLLATE utf8_unicode_ci NOT NULL, 
  xta_rcactivhpre varchar(36) COLLATE utf8_unicode_ci NOT NULL, 
  etabgestion varchar(36) COLLATE utf8_unicode_ci NOT NULL, 
  travhorspresse varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0', 
	heuredeblun NUMERIC(9,0), 
	nbheurelun NUMERIC(5,2) NOT NULL DEFAULT 0, 
	heuredebmar NUMERIC(9,0), 
	nbheuremar NUMERIC(5,2) NOT NULL DEFAULT 0, 
	heuredebmer NUMERIC(9,0), 
	nbheuremer NUMERIC(5,2) NOT NULL DEFAULT 0, 
	heuredebjeu NUMERIC(9,0), 
	nbheurejeu NUMERIC(5,2) NOT NULL DEFAULT 0, 
	heuredebven NUMERIC(9,0), 
	nbheureven NUMERIC(5,2) NOT NULL DEFAULT 0, 
	heuredebsam NUMERIC(9,0), 
	nbheuresam NUMERIC(5,2) NOT NULL DEFAULT 0, 
	heuredebdim NUMERIC(9,0), 
	nbheuredim NUMERIC(5,2) NOT NULL DEFAULT 0, 
  depot_id int(11), 
  flux_id int(11), 
  activite_id int(11), 
  employe_id int(11), 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_xrcautreactivit` UNIQUE (`relationcontrat`, `ordre`, `begin_date`),
  CONSTRAINT `fk_pai_png_xrcautreactivit_001`
    FOREIGN KEY (`relationcontrat`)
    REFERENCES `pai_png_relationcontrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION ,
  CONSTRAINT `fk_pai_png_xrcautreactivit_012`
    FOREIGN KEY (`xta_rcactivte`)
    REFERENCES `pai_png_xta_rcactivite` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION ,
  CONSTRAINT `fk_pai_png_xrcautreactivit_013`
    FOREIGN KEY (`xta_rcmetier`)
    REFERENCES `pai_png_xta_rcmetier` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION, 
	 CONSTRAINT `fk_pai_png_xrcautreactivit_017` FOREIGN KEY (`xta_rcactivhpre`)
	  REFERENCES `pai_png_xta_rcactivhpre` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION, 
	 CONSTRAINT `fk_pai_png_xrcautreactivit_018` FOREIGN KEY (`etabgestion`)
	  REFERENCES `pai_png_etablissement` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION, 
	 CONSTRAINT `fk_pai_png_xrcautreactivit_depot_id` FOREIGN KEY (`depot_id`)
	  REFERENCES `depot` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION, 
	 CONSTRAINT `fk_pai_png_xrcautreactivit_flux_id` FOREIGN KEY (`flux_id`)
	  REFERENCES `ref_flux` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION, 
	 CONSTRAINT `fk_pai_png_xrcautreactivit_activite_id` FOREIGN KEY (`activite_id`)
	  REFERENCES `ref_activite` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
CREATE INDEX pai_png_xrcautreactivit_idx ON pai_png_xrcautreactivit(relationcontrat,begin_date,end_date);

-- -----------------------------------------------------
-- utilisé pour récupérer le flux Media in ('941c5fdd-1882-11e4-9370-8749f6d713da','a447522e-1882-11e4-9370-8749f6d713da')
DROP TABLE IF EXISTS `pai_png_affoctimew` ;
CREATE TABLE IF NOT EXISTS `pai_png_affoctimew` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	relationcontrat varchar(36) COLLATE utf8_unicode_ci NOT NULL,
	begin_date DATE NOT NULL, 
	end_date DATE NOT NULL,
  niveau2 varchar(36) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_affoctimew` UNIQUE (`relationcontrat`, `begin_date`),
  CONSTRAINT `fk_pai_pai_png_affoctimew_001`
    FOREIGN KEY (`relationcontrat`)
    REFERENCES `pai_png_relationcontrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION 
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
CREATE INDEX pai_png_affoctimew_idx ON pai_png_affoctimew(relationcontrat,begin_date,end_date);


-- -----------------------------------------------------
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_ta_relamotifdeb` ;
CREATE TABLE IF NOT EXISTS `pai_png_ta_relamotifdeb` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	relatcodedeb varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  relatlibcdeb varchar(20) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_ta_relamotifdeb` UNIQUE (`relatcodedeb`)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_tp_motifremplw` ;
CREATE TABLE IF NOT EXISTS `pai_png_tp_motifremplw` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	code varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  libelle varchar(30) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_tp_motifremplw` UNIQUE (`code`)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_tp_termecddw` ;
CREATE TABLE IF NOT EXISTS `pai_png_tp_termecddw` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	code varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  libelle varchar(30) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_tp_termecddw` UNIQUE (`code`)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `pai_png_relationc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_relationc` ;
CREATE TABLE IF NOT EXISTS `pai_png_relationc` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `relatmatricule` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `relatnum` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
	begin_date DATE NOT NULL, 
	end_date DATE NOT NULL,
  relatmotifdeb varchar(36) COLLATE utf8_unicode_ci,
  motifremplw varchar(36) COLLATE utf8_unicode_ci,
  xautremotrempl varchar(50) COLLATE utf8_unicode_ci,
  termecddw varchar(36) COLLATE utf8_unicode_ci,
  salremplaw varchar(36) COLLATE utf8_unicode_ci,
  infosalrempw varchar(6) COLLATE utf8_unicode_ci,
  cdddatefinprevu date,
  PRIMARY KEY (`oid`),
  INDEX `fk_pai_png_relationc_pai_png_salarie_idx` (`relatmatricule` ASC),
  INDEX `fk_pai_png_relationc_pai_png_tp_motifremplw_idx` (`motifremplw` ASC),
  INDEX `fk_pai_png_relationc_pai_png_tp_termecddw_idx` (`termecddw` ASC),
  INDEX `fk_pai_png_relationc_pai_png_relationcontrat_idx` (`salremplaw` ASC),
  CONSTRAINT `fk_pai_png_relationc_pai_png_salarie`
    FOREIGN KEY (`relatmatricule`)
    REFERENCES `pai_png_salarie` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_png_relationc_pai_png_tp_motifremplw`
    FOREIGN KEY (`motifremplw`)
    REFERENCES `pai_png_tp_motifremplw` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pai_png_relationc_pai_png_tp_termecddw`
    FOREIGN KEY (`termecddw`)
    REFERENCES `pai_png_tp_termecddw` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION/*,
  CONSTRAINT `fk_pai_png_relationc_pai_png_relationcontrat`
    FOREIGN KEY (`salremplaw`)
    REFERENCES `pai_png_relationcontrat` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION*/
)ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
alter table pai_png_relationc add contrattypecode varchar(3) COLLATE utf8_unicode_ci;
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_ta_primesw` ;
CREATE TABLE IF NOT EXISTS `pai_png_ta_primesw` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	code varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  libelle varchar(50) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_ta_primesw` UNIQUE (`code`)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_primesdvrssocw` ;
CREATE TABLE IF NOT EXISTS `pai_png_primesdvrssocw` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `societe` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
	begin_date DATE NOT NULL, 
	end_date DATE NOT NULL,
  typeprime varchar(36) COLLATE utf8_unicode_ci,
  montant numeric(9,2),
  taux numeric(8,3),
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_primesdvrssocw` UNIQUE (`societe`,`begin_date`,`typeprime`),
  INDEX `fk_pai_png_primesdvrssocw_pai_png_ta_primesw_idx` (`typeprime` ASC),
  CONSTRAINT `fk_pai_png_primesdvrssocw_pai_png_ta_primesw`
    FOREIGN KEY (`typeprime`)
    REFERENCES `pai_png_ta_primesw` (`oid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_ta_relamotiffin` ;
CREATE TABLE IF NOT EXISTS `pai_png_ta_relamotiffin` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	relationcodefin varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  relationlibcfin varchar(20) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_ta_relamotiffin` UNIQUE (`relationcodefin`)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pai_png_nationalite` ;
CREATE TABLE IF NOT EXISTS `pai_png_nationalite` (
  `oid` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	code varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  lib_court varchar(40) COLLATE utf8_unicode_ci NOT NULL, 
  appartenanceue varchar(1) COLLATE utf8_unicode_ci NOT NULL, 
  PRIMARY KEY (`oid`),
	CONSTRAINT `ak_pai_png_nationalite` UNIQUE (`code`)
  )
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;