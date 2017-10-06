 DROP TABLE IF EXISTS pai_int_oct_badge_mroad;
 CREATE TABLE IF NOT EXISTS pai_int_oct_badge_mroad(
	date_distrib DATE NOT NULL, 
  employe_id INT NOT NULL,
	matricule VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL, 
	heure_debut TIME NOT NULL, 
	duree TIME NOT NULL, 
	duree_nuit TIME NOT NULL, 
	eta VARCHAR(3) NOT NULL
)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
  CREATE INDEX pai_int_oct_badge_mroad_idx ON pai_int_oct_badge_mroad (matricule, date_distrib, eta) 
  ;

  DROP TABLE IF EXISTS pai_int_oct_badge;
  CREATE TABLE IF NOT EXISTS pai_int_oct_badge(
  employe_id        INT,
  depot_id          INT,
  flux_id           INT,
	matricule         VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL, 
	date_distrib      DATE NOT NULL, 
	heure_debut_mroad TIME, 
	heure_fin_mroad   TIME, 
	eta               VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL, 
	heure_debut_oct   TIME, 
	heure_fin_oct     TIME, 
	contrat_ok        BOOLEAN DEFAULT FALSE NOT NULL, 
	absence_ok        BOOLEAN DEFAULT FALSE NOT NULL, 
	abs_cod           VARCHAR(4) COLLATE utf8_unicode_ci, 
	CONSTRAINT pai_int_oct_badge_pk PRIMARY KEY (matricule, date_distrib, eta)
)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
 
  DROP TABLE IF EXISTS pai_int_oct_horaire;
  CREATE TABLE pai_int_oct_horaire(
  matricule         VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL, 
	date_distrib      DATE NOT NULL, 
	hor_cod_oct       NUMERIC(7,0), 
	eta               VARCHAR(3) COLLATE utf8_unicode_ci, 
	hor_cod_exp       NUMERIC(7,0), 
	hor_cod_pepp      NUMERIC(7,0), 
	abs_cod           VARCHAR(4) COLLATE utf8_unicode_ci, 
	hor_cod_imp       NUMERIC(7,0), 
	hor_dat           DATE NOT NULL, 
	 CONSTRAINT pai_int_oct_horaire_pk PRIMARY KEY (matricule, date_distrib)
)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

 
  DROP TABLE IF EXISTS pai_int_oct_hgaranties;
  CREATE TABLE pai_int_oct_hgaranties(
  idtrt             INT(11) NOT NULL,
  employe_id        INT NOT NULL, 
	date_distrib      DATE NOT NULL, 
	hgaranties        TIME NOT NULL, 
	hdelegation       TIME NOT NULL, 
	hhorspresse        TIME NOT NULL, 
	CONSTRAINT pai_int_oct_hgaranties_pk PRIMARY KEY (employe_id, date_distrib),
  INDEX `fk_pai_int_oct_hgaranties_pai_int_traitement_idx` (`idtrt` ASC),
  CONSTRAINT `fk_pai_int_oct_hgaranties_pai_int_traitement`
    FOREIGN KEY (`idtrt`)
    REFERENCES `pai_int_traitement` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  INDEX `fk_pai_int_oct_hgaranties_employe_idx` (`employe_id` ASC),
  CONSTRAINT `fk_pai_int_oct_hgaranties_employe`
    FOREIGN KEY (`employe_id`)
    REFERENCES `employe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

