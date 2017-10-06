SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `pai_oct_pers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_pers`;
CREATE TABLE IF NOT EXISTS `pai_oct_pers` (
  `pers_mat` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL,
  `pers_nom` VARCHAR(40) COLLATE utf8_unicode_ci NOT NULL,
  `pers_pre` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`pers_mat`))
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `pai_oct_nivprev`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_nivprev`;
CREATE TABLE IF NOT EXISTS `pai_oct_nivprev` (
  `pers_mat` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL,
  `niv_dat` DATE NOT NULL,
  `niv_datf` DATE NOT NULL,
  `niv_cod1` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  `niv_cod2` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  `niv_cod3` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
  INDEX `fk_table1_pai_oct_pers1_idx` (`pers_mat` ASC),
  PRIMARY KEY (`pers_mat`, `niv_dat`),
  INDEX `pai_oct_nivprev_index1` (`pers_mat` ASC, `niv_dat` ASC, `niv_datf` ASC),
  CONSTRAINT `fk_table1_pai_oct_pers1`
    FOREIGN KEY (`pers_mat`)
    REFERENCES `pai_oct_pers` (`pers_mat`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `pai_oct_cyclig`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_cyclig`;
CREATE TABLE IF NOT EXISTS `pai_oct_cyclig` (
  `cyc_cod` DECIMAL(5,0) NOT NULL,
  `cyc_num` DECIMAL(3,0) NOT NULL,
  `hor_cod` DECIMAL(7,0) NOT NULL,
  PRIMARY KEY (`cyc_cod`, `cyc_num`))
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `pai_oct_horprev`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_horprev`;
CREATE TABLE IF NOT EXISTS `pai_oct_horprev` (
  `pers_mat` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL,
  `hor_dat` DATE NOT NULL,
  `cyc_cod` DECIMAL(5,0) NOT NULL,
  `hor_datf` DATE NOT NULL,
  INDEX `fk_pai_oct_horprev_pai_oct_pers1_idx` (`pers_mat` ASC),
  PRIMARY KEY (`pers_mat`, `hor_dat`),
  INDEX `pai_oct_horprev_index1` (`pers_mat` ASC, `hor_dat` ASC, `hor_datf` ASC),
  INDEX `fk_pai_oct_horprev_pai_oct_cyclig1_idx` (`cyc_cod` ASC),
  CONSTRAINT `fk_pai_oct_horprev_pai_oct_pers1`
    FOREIGN KEY (`pers_mat`)
    REFERENCES `pai_oct_pers` (`pers_mat`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION/*,
  CONSTRAINT `fk_pai_oct_horprev_pai_oct_cyclig1`
    FOREIGN KEY (`cyc_cod`)
    REFERENCES `pai_oct_cyclig` (`cyc_cod`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION*/)
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `pai_oct_varprev`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_varprev`;
CREATE TABLE IF NOT EXISTS `pai_oct_varprev` (
  `pers_mat` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL,
  `var_dat` DATE NOT NULL,
  `par_cod` VARCHAR(4) COLLATE utf8_unicode_ci NOT NULL,
  `var_datf` DATE NOT NULL,
  INDEX `fk_pai_oct_varprev_pai_oct_pers1_idx` (`pers_mat` ASC),
  INDEX `pai_oct_varprev_index1` (`par_cod` ASC),
  INDEX `pai_oct_varprev_index2` (`pers_mat` ASC, `var_dat` ASC, `var_datf` ASC),
  CONSTRAINT `fk_pai_oct_varprev_pai_oct_pers1`
    FOREIGN KEY (`pers_mat`)
    REFERENCES `pai_oct_pers` (`pers_mat`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `pai_oct_saiabs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_absence`;
CREATE TABLE IF NOT EXISTS `pai_oct_absence` (
  `abs_cod` VARCHAR(4) COLLATE utf8_unicode_ci NOT NULL,
  `abs_lib` VARCHAR(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`abs_cod`)
  )
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `pai_oct_saiabs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_saiabs`;
CREATE TABLE IF NOT EXISTS `pai_oct_saiabs` (
  `pers_mat` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL,
  `abs_dat` DATE NOT NULL,
  `abs_num` DECIMAL(1,0) NOT NULL,
  `abs_fin` DATE NOT NULL,
  `abs_cod` VARCHAR(4) COLLATE utf8_unicode_ci NOT NULL,
  `abs_typ` VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL,
  `abs_dh` VARCHAR(4) COLLATE utf8_unicode_ci NOT NULL,
  `abs_fh` VARCHAR(4) COLLATE utf8_unicode_ci NOT NULL,
  INDEX `fk_table1_pai_oct_pers2_idx` (`pers_mat` ASC),
  PRIMARY KEY (`pers_mat`, `abs_dat`, `abs_num`),
  INDEX `pai_oct_saiabs_index1` (`pers_mat` ASC, `abs_dat` ASC, `abs_fin` ASC),
  CONSTRAINT `fk_table1_pai_oct_pers2`
    FOREIGN KEY (`pers_mat`)
    REFERENCES `pai_oct_pers` (`pers_mat`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
alter table pai_oct_saiabs add abs_dur  VARCHAR(4) COLLATE utf8_unicode_ci NOT NULL;
-- -----------------------------------------------------
-- Table `pai_oct_saiabssav`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_saiabssav`;
CREATE TABLE IF NOT EXISTS `pai_oct_saiabssav` (
  `pers_mat` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL,
  `abs_dat` DATE NOT NULL,
  `abs_fin` DATE NOT NULL,
  `abs_cod` VARCHAR(4) COLLATE utf8_unicode_ci NOT NULL,
  `flag` VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL,
  INDEX `fk_table1_pai_oct_pers2_idx` (`pers_mat` ASC),
  CONSTRAINT `fk_table1_pai_oct_pers20`
    FOREIGN KEY (`pers_mat`)
    REFERENCES `pai_oct_pers` (`pers_mat`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `pai_oct_pointage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_pointage`;
CREATE TABLE IF NOT EXISTS `pai_oct_pointage` (
  `pers_mat` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL,
  `bad_dat` DATE NOT NULL,
  `bad_heure` VARCHAR(4) COLLATE utf8_unicode_ci NOT NULL,
  `bad_typ` VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL,
  INDEX `fk_pai_poct_pointage_pai_oct_pers1_idx` (`pers_mat` ASC),
  PRIMARY KEY (`bad_dat`, `bad_heure`, `pers_mat`),
  INDEX `pai_oct_pointage_index1` (`pers_mat` ASC, `bad_dat` ASC, `bad_typ` ASC),
  CONSTRAINT `fk_pai_poct_pointage_pai_oct_pers1`
    FOREIGN KEY (`pers_mat`)
    REFERENCES `pai_oct_pers` (`pers_mat`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `pai_oct_contprev`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_contprev`;
CREATE TABLE IF NOT EXISTS `pai_oct_contprev` (
  `pers_mat` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL,
  `cont_datd` DATE NOT NULL,
  `cont_datf` DATE NOT NULL,
  INDEX `fk_pai_oct_contprev_pai_oct_pers1_idx` (`pers_mat` ASC),
  PRIMARY KEY (`pers_mat`, `cont_datd`),
  INDEX `pai_oct_contprev_index1` (`pers_mat` ASC, `cont_datd` ASC, `cont_datf` ASC),
  CONSTRAINT `fk_pai_oct_contprev_pai_oct_pers1`
    FOREIGN KEY (`pers_mat`)
    REFERENCES `pai_oct_pers` (`pers_mat`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `pai_oct_cjexp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS`pai_oct_cjexp`;
CREATE TABLE IF NOT EXISTS `pai_oct_cjexp` (
  `pers_mat` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL,
  `dat` DATE NOT NULL,
  `hor_cod` DECIMAL(7,0) NOT NULL,
  INDEX `fk_pai_oct_cjexp_pai_oct_pers1_idx` (`pers_mat` ASC),
  PRIMARY KEY (`pers_mat`, `dat`),
  CONSTRAINT `fk_pai_oct_cjexp_pai_oct_pers1`
    FOREIGN KEY (`pers_mat`)
    REFERENCES `pai_oct_pers` (`pers_mat`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
