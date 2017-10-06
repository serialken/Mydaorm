DROP TABLE pai_pevp_tournee;
CREATE TABLE pai_pevp_tournee (
  tournee_id int NOT NULL, 
  date_distrib date NOT NULL, 
  employe_id int not null,
  produit_id int not null, 
  qte decimal(6,0) not null,  
UNIQUE INDEX UNIQ_pai_pevp_tournee (tournee_id,date_distrib,employe_id,produit_id), 
PRIMARY KEY(tournee_id,produit_id))
DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

DROP TABLE pai_pevp_produit;
CREATE TABLE pai_pevp_produit (
  produit_id int NOT NULL, 
  libelle varchar(100) not null,
  taux int not null,
PRIMARY KEY(produit_id))
DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

DROP TABLE pai_pevp_reclamation;
CREATE TABLE pai_pevp_reclamation (
  reclamation_id int(11) NOT NULL,
  employe_id int(11) NOT NULL,
  nbrec_abonne decimal(4,0) NOT NULL,
  nbrec_diffuseur decimal(4,0) NOT NULL,
PRIMARY KEY(reclamation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE pai_pevp_incident;
CREATE TABLE pai_pevp_incident (
  incident_id int(11) NOT NULL,
  date_distrib date NOT NULL,
  employe_id int(11) NOT NULL,
PRIMARY KEY(incident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- ----------------------------------------------------------------------------
DROP TABLE pai_pevp_tournee_hst;
CREATE TABLE pai_pevp_tournee_hst (
  idtrt int NOT NULL, 
  tournee_id int NOT NULL, 
  date_distrib date NOT NULL, 
  employe_id int not null,
  produit_id int not null, 
  qte decimal(6,0) not null,  
UNIQUE INDEX UNIQ_pai_pevp_tournee (tournee_id,date_distrib,employe_id,produit_id), 
PRIMARY KEY(tournee_id))
DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

DROP TABLE pai_pevp_produit_hst;
CREATE TABLE pai_pevp_produit_hst (
  idtrt int NOT NULL, 
  produit_id int NOT NULL, 
  libelle varchar(100) not null,
  taux int not null,
PRIMARY KEY(idtrt,produit_id))
DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

DROP TABLE pai_pevp_reclamation_hst;
CREATE TABLE pai_pevp_reclamation_hst (
  idtrt int NOT NULL, 
  reclamation_id int(11) NOT NULL,
  employe_id int(11) NOT NULL,
  nbrec_abonne decimal(4,0) NOT NULL,
  nbrec_diffuseur decimal(4,0) NOT NULL,
PRIMARY KEY(reclamation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE pai_pevp_incident_hst;
CREATE TABLE pai_pevp_incident_hst (
  idtrt int NOT NULL, 
  incident_id int(11) NOT NULL,
  date_distrib date NOT NULL,
  employe_id int(11) NOT NULL,
PRIMARY KEY(incident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;