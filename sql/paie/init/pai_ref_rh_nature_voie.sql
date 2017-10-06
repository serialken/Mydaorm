DROP TABLE pai_ref_rh_naturevoie;
CREATE TABLE pai_ref_rh_naturevoie(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  code varchar(4) CHARACTER SET'utf8' COLLATE'utf8_unicode_ci' NOT NULL,
  libelle varchar(64) CHARACTER SET'utf8' COLLATE'utf8_unicode_ci' NOT NULL,
  UNIQUE INDEX pai_ref_rh_naturevoie_uk (`code` ASC),
  CONSTRAINT pai_ref_rh_naturevoie_pk PRIMARY KEY (id)
  )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


insert into pai_ref_rh_naturevoie(code,libelle) values('ALL','Allée');
insert into pai_ref_rh_naturevoie(code,libelle) values('AV','Avenue');
insert into pai_ref_rh_naturevoie(code,libelle) values('BD','Boulevard');
insert into pai_ref_rh_naturevoie(code,libelle) values('CHE','Chemin');
insert into pai_ref_rh_naturevoie(code,libelle) values('CHS','Chaussée');
insert into pai_ref_rh_naturevoie(code,libelle) values('CIT','Cité');
insert into pai_ref_rh_naturevoie(code,libelle) values('CLS','Clos');
insert into pai_ref_rh_naturevoie(code,libelle) values('COU','Cour');
insert into pai_ref_rh_naturevoie(code,libelle) values('CRS','Cours');
insert into pai_ref_rh_naturevoie(code,libelle) values('DOM','Domaine');
insert into pai_ref_rh_naturevoie(code,libelle) values('ESP','Esplanade');
insert into pai_ref_rh_naturevoie(code,libelle) values('FBG','Faubourg');
insert into pai_ref_rh_naturevoie(code,libelle) values('FRM','Ferme');
insert into pai_ref_rh_naturevoie(code,libelle) values('HAM','Hameau');
insert into pai_ref_rh_naturevoie(code,libelle) values('IMP','Impasse');
insert into pai_ref_rh_naturevoie(code,libelle) values('JAR','Jardin');
insert into pai_ref_rh_naturevoie(code,libelle) values('LOT','Lotissement');
insert into pai_ref_rh_naturevoie(code,libelle) values('MAI','Mail');
insert into pai_ref_rh_naturevoie(code,libelle) values('MON','Montée');
insert into pai_ref_rh_naturevoie(code,libelle) values('PAR','Parc');
insert into pai_ref_rh_naturevoie(code,libelle) values('PAS','Passage');
insert into pai_ref_rh_naturevoie(code,libelle) values('PL','Place');
insert into pai_ref_rh_naturevoie(code,libelle) values('PRE','Pré');
insert into pai_ref_rh_naturevoie(code,libelle) values('PRO','Promenade');
insert into pai_ref_rh_naturevoie(code,libelle) values('QU.','Quai');
insert into pai_ref_rh_naturevoie(code,libelle) values('QUA','Quartier');
insert into pai_ref_rh_naturevoie(code,libelle) values('RES','Résidence');
insert into pai_ref_rh_naturevoie(code,libelle) values('RPT','Rond-Point');
insert into pai_ref_rh_naturevoie(code,libelle) values('RTE','Route');
insert into pai_ref_rh_naturevoie(code,libelle) values('RUE','Rue');
insert into pai_ref_rh_naturevoie(code,libelle) values('RUL','Ruelle');
insert into pai_ref_rh_naturevoie(code,libelle) values('SEN','Sente');
insert into pai_ref_rh_naturevoie(code,libelle) values('SQ','Square');
insert into pai_ref_rh_naturevoie(code,libelle) values('TRV','Traverse');
insert into pai_ref_rh_naturevoie(code,libelle) values('VIL','Villa');
