-- -------------------------------------------------------------------- Variables à rensigner
-- mroad_local.ini
# SOUSREP_FICHIERS_DEJA_INTEG="BkpDejaTraite"
# verifier l'existence de ce parametre dans mroad_prod.ini



-- ---------------------------------------------------------------------- requete a executer pour la creation des ecrans suivi de production

-- sous_categorie.sql
-- Distribution --
INSERT INTO `sous_categorie` (`id`, `cat_id`, `libelle`, `class_image`, `page_defaut`) VALUES
-- (59, 5, 'Suivi De Production', 'suivi_de_production', 'suivi_production_vue_generale'),
(60, 5, 'Gestion Des Routes', 'suivi_de_production', 'suivi_production_parametrage')

-- page.sql
-- Distribution --
INSERT INTO `page` (`ID`, `ID_ROUTE`, `DESC_COURT`, `DESCRIPTION`, `MENU`, `PAG_DEFAUT`, `SS_CAT_ID`) VALUES
-- (5910, 'suivi_production', 'suivi de production', 'suivi de la production', '1', 'suivi_production_vue_generale', 59),
(6010, 'suivi_production_configuration', 'parametrage route centre', 'parametrage route centre', '1', 'suivi_production_parametrage', 60)

-- page_element.sql
-- Distribution --
INSERT INTO `page_element` (`id`, `pag_id`, `desc_court`, `libelle`, `oblig`) VALUES
-- (59100, 5910, 'PAGE', 'Accès page - Suivi Production', 0),
(60100, 6010, 'PAGE', 'Accès page - Parametrage Suivi Production', 0)

-- profil_page_element.sql
INSERT INTO `profil_page_element` (`PROFIL_ID`, `PAGE_ELEM_ID`) VALUES
-- (1, 59100),
(1, 60100)


-- Ajout de la table suivi de production
CREATE TABLE suivi_de_production (id INT AUTO_INCREMENT NOT NULL, fic_recap_id INT DEFAULT NULL, date_edi DATETIME NOT NULL, libelle_edi VARCHAR(45) NOT NULL, code_route VARCHAR(10) NOT NULL, libelle_route VARCHAR(45) DEFAULT NULL, pqt_prev INT NOT NULL, pqt_eject INT NOT NULL, ex_prev INT NOT NULL, ex_eject INT NOT NULL, date_up DATETIME DEFAULT NULL, INDEX IDX_52EF6432EFEEFCF6 (fic_recap_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE suivi_de_production ADD CONSTRAINT FK_52EF6432EFEEFCF6 FOREIGN KEY (fic_recap_id) REFERENCES fic_recap (id);


-- Ajout de la table depot_route
CREATE TABLE depot_route (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, code_route VARCHAR(10) NOT NULL, libelle_route VARCHAR(40) DEFAULT NULL, code_centre VARCHAR(3) NOT NULL, libelle_centre VARCHAR(40) DEFAULT NULL, updated_at DATE DEFAULT NULL, created_at DATE NOT NULL, actif TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_643573DEEE913935 (code_route), INDEX IDX_643573DEFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE depot_route ADD CONSTRAINT FK_643573DEFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id);


--ajout de la table suivi_de_production_mail
CREATE TABLE suivi_de_production_mail (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(45) NOT NULL, date_edition DATE NOT NULL, envoyer TINYINT(1) NOT NULL, date_envoi DATE DEFAULT NULL, etat VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE `mroad`.`suivi_de_production_mail` 
CHANGE date_envoi date_envoi DATETIME DEFAULT NULL;
-- --------------------------------------------------------------------- requete a executer pour le parametrage de l'import de fichier'

-- A rajouter pour les champs date_crea et date_update de la table fic_recap
ALTER TABLE fic_recap ADD date_update DATETIME DEFAULT NULL;

-- A mettre dans un fichier trigger pour  update automatiquement la date lors d'un import de fichier'

DROP TRIGGER IF EXISTS fic_recap_update_date_update;
CREATE TRIGGER fic_recap_update_date_update
BEFORE UPDATE ON fic_recap FOR EACH ROW
SET NEW.date_update=NOW();

-- fic_ftp parametre d'import de la commande import_fic_ftp
INSERT INTO fic_ftp(`code`,`serveur`,`login`,`mdp`,`repertoire`,`rep_sauvegarde`,`id_soc_distrib`) VALUES
('SUIVI_PRODUCTION', '10.151.93.2','MROAD','M245road', 'MROAD_RECETTE/SUIVI_PRODUCTION','Bkp', '39');

-- fic_source
INSERT INTO fic_source (`code`,`libelle`,`client_type`) VALUES
('SUIVI_PRODUCTION', 'SUIVI  DE LA PRODUCTION', 0);

-- fic_flux
INSERT INTO fic_flux (`id`,`libelle`) VALUES (75,'Import des fichiers liés au suivi de la production');

-- fic_chrgt_fichiers_bdd
INSERT INTO fic_chrgt_fichiers_bdd (`fic_ftp`,`fic_source`,`fic_code`,`regex_fic`,`format_fic`,`nb_lignes_ignorees`,`separateur`,`trim_val`,`nb_col`,`flux_id`,`ss_rep_traitement`) VALUES
 (23,6,'SUIVI_PRODUCTION','/^(\\.\\/)?[0-9]+_Prev_Routes\\.csv$/i', 'CSV', 1, ';', 1, 7, 75, 'SuiviProduction');
-- --------------------------------------------------------------requete a executer pour le parametrage de l'integration de fichiers aprés import

-- fic format enregistrement
INSERT INTO fic_format_enregistrement (`fic_code`,`attribut`,`col_debut`,`col_long`,`col_val`,`col_val_rplct`,`col_desc`) VALUES
('SUIVI_PRODUCTION', 'DATE_EDI',NULL ,NULL ,1,NULL ,'Date d édition'),
('SUIVI_PRODUCTION', 'LIBELLE_EDI',NULL ,NULL ,2,NULL ,'Libelle de l édition'),
('SUIVI_PRODUCTION', 'CODE_ROUTE',NULL ,NULL ,3,NULL ,'Code de la route'),
('SUIVI_PRODUCTION', 'PQT_PREV',NULL ,NULL ,4,NULL ,'Nombre de paquets prévus'),
('SUIVI_PRODUCTION', 'PQT_EJECT',NULL ,NULL ,5,NULL ,'Nombre de paquets éjectés'),
('SUIVI_PRODUCTION', 'EX_PREV',NULL ,NULL ,6,NULL ,'Nombre d exemplaires prévus'),
('SUIVI_PRODUCTION', 'EX_EJECT',NULL ,NULL ,7,NULL ,'Nombre d exemplaires éjectés');


