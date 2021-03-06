  DROP TABLE MROAD_VCP ;
  CREATE TABLE MROAD_VCP(
  employe_id int, 
  MAT CHAR(8 BYTE) NOT NULL ENABLE, 
	NOM CHAR(30 BYTE) NOT NULL ENABLE, 
	PRENOM CHAR(20 BYTE), 
	NUMEROVOIE VARCHAR2(10 BYTE), 
	NATUREVOIE CHAR(4 BYTE), 
	CPLTADR1 VARCHAR2(32 BYTE), 
	CPLTADR2 VARCHAR2(32 BYTE), 
	NOMVOIE1 VARCHAR2(22 BYTE), 
	NOMVOIE2 VARCHAR2(22 BYTE), 
	NOMVOIE3 VARCHAR2(22 BYTE), 
	CODEPOSTAL CHAR(5 BYTE), 
	CODEINSEE CHAR(10 BYTE), 
	VILLE VARCHAR2(26 BYTE), 
	NSECU CHAR(13 BYTE), 
	CLESECU CHAR(2 BYTE), 
	DATENAISSANCE CHAR(8 BYTE), 
	LIEUNAISSANCE CHAR(5 BYTE), 
	PAYSNAISSANCE CHAR(3 BYTE), 
	CIVILITE CHAR(1 BYTE), 
	IBAN VARCHAR2(34 BYTE), 
	BIC VARCHAR2(11 BYTE),
  date_debut date,
  date_fin date,
	 CONSTRAINT PK_MROAD_VCP PRIMARY KEY (employe_id)
   ); 

  DROP TABLE MROAD_PRODUIT;
  CREATE TABLE MROAD_PRODUIT(
  produit_id int, 
  code VARCHAR2(7 BYTE) NOT NULL ENABLE, 
	libelle VARCHAR2(32 BYTE) NOT NULL ENABLE, 
	CODEEDITION CHAR(3 BYTE) NOT NULL ENABLE, 
	CODETITRE CHAR(4 BYTE) NOT NULL ENABLE, 
	CODEJOUR CHAR(1 BYTE), 
	CODEANA CHAR(7 BYTE), 
	PRIXFACIAL NUMBER(7,2) NOT NULL ENABLE, 
	TAUXCOM NUMBER(4,2) NOT NULL ENABLE, 
  POSTEPAIE VARCHAR2(7 BYTE) NOT NULL ENABLE, 
	 CONSTRAINT PK_MROAD_EDITION PRIMARY KEY (produit_id)
   );

  DROP TABLE MROAD_TOURNEE;
  CREATE TABLE MROAD_TOURNEE(
  date_distrib date NOT NULL ENABLE, 
  employe_id int, 
  produit_id int, 
	qte NUMBER(4,0) NOT NULL ENABLE, 
	 CONSTRAINT PK_MROAD_MOUVEMENT PRIMARY KEY (date_distrib, employe_id, produit_id)
  );

  DROP TABLE MROAD_RECLAMATION;
  CREATE TABLE MROAD_RECLAMATION(	
  date_distrib CHAR(8 BYTE) NOT NULL ENABLE, 
  employe_id int, 
	NB NUMBER(4,0) NOT NULL ENABLE, 
	 CONSTRAINT PK_MROAD_RECLAMATION PRIMARY KEY (date_distrib, employe_id)
  );


begin
imp_mroad.importer;
end;
/


	select
        e."id",
    -- ATTENTION, ne faut-il pas mettre MR0YXXXX pour la nuit et MR2YXXXX pour le jour ????
        upper(substr(e."matricule",1,8)), -- as NOM,
        e."civilite_id", -- as CIVILITE,
        upper(substr(e."nom",1,30)), -- as NOM,
        upper(substr(e."prenom1",1,20)), -- as PRENOM,
        upper(ea."numerovoie"), -- as NUMEROVOIE,
        upper(ea."naturevoie"), -- as NATUREVOIE,
        upper(ea."nomvoie1"), -- as NOMVOIE1,
        upper(ea."nomvoie2"), -- as NOMVOIE2,
        upper(ea."nomvoie3"), -- as NOMVOIE3,
        ea."codepostal", -- as CODEPOSTAL,
        ea."codeinsee", -- as CODEINSEE,
        upper(ea."ville"), -- as VILLE,
        upper(ea."cpltadr1"), -- as CPLTADR1,
        upper(ea."cpltadr2"), --" as CPLTADR2,
        e."secu_numero", -- as NSECU,
        e."secu_cle", -- as CLESECU,
        to_char(e."naissance_date",'YYYYMMDD'), -- as DATENAISSANCE,
        e."naissance_lieu", -- as LIEUNAISSANCE,
        e."naissance_pays", -- as PAYSNAISSANCE,
        eb."iban", --IBAN,
        eb."bic", --BIC,
        epd."date_debut", -- as DATEDEBUTVALIDITE,
        epd."date_fin" -- as DATEFINVALIDITE
      from "employe"@MROAD e
      inner join "emp_pop_depot"@MROAD epd on e."id"=epd."employe_id" and epd."population_id"=-1
      inner join "emp_adresse"@MROAD ea on e."id"=ea."employe_id"
      inner join "emp_banque"@MROAD eb on e."id"=eb."employe_id"    
    ;