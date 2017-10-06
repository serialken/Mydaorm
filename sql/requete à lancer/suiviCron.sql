-- -----------------------------------------------------------------------requete à lancer pour la creation des ecrans Suivi CRON
-- ---------------------------------------------------------------Concernant la navigation les diferenetes requétes sont présentent dans les fichiers
-- ------------------------------------------------------------------- page, sous-categorie, page_element, profil_page_element

-- sous_categorie.sql
-- Administration --
-- INSERT INTO `sous_categorie` (`id`, `cat_id`, `libelle`, `class_image`, `page_defaut`) VALUES
-- (40, 1, 'Exploitation', 'exploitation', 'suivi_cron_vue_generale')

-- page.sql
-- Administration --
-- INSERT INTO `page` (`ID`, `ID_ROUTE`, `DESC_COURT`, `DESCRIPTION`, `MENU`, `PAG_DEFAUT`, `SS_CAT_ID`) VALUES
-- (4010, 'suivi_cron', 'suivi des cron', 'Suivi Activité Cron', '1', 'suivi_cron_vue_generale', 40)

-- page_element.sql
-- Administration --
-- INSERT INTO `page_element` (`id`, `pag_id`, `desc_court`, `libelle`, `oblig`) VALUES
-- (40100, 4010, 'PAGE', 'Accès onglet - Suivi CRON', 0)

-- profil_page_element.sql
-- INSERT INTO `profil_page_element` (`PROFIL_ID`, `PAGE_ELEM_ID`) VALUES
-- (1, 40100)

-- Ajout de la table suivi_de_command
CREATE TABLE suivi_de_command (id INT AUTO_INCREMENT NOT NULL, libelle_command VARCHAR(100) NOT NULL, heure_debut DATETIME NOT NULL, heure_fin DATETIME DEFAULT NULL, etat VARCHAR(10) DEFAULT NULL, message VARCHAR(255) DEFAULT NULL, error_type VARCHAR(50) DEFAULT NULL, log_file VARCHAR(255) DEFAULT NULL, error_line VARCHAR(25) DEFAULT NULL, INDEX libelle_command_idx (libelle_command), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE suivi_de_command ADD id_cron INT DEFAULT NULL, ADD libelle_cron VARCHAR(100) DEFAULT NULL;

-- Ajout de la table suivi_de_cron
CREATE TABLE suivi_de_cron (id INT AUTO_INCREMENT NOT NULL, libelle_cron VARCHAR(100) NOT NULL, heure_debut DATETIME NOT NULL, heure_fin DATETIME DEFAULT NULL, etat VARCHAR(10) DEFAULT NULL, commentaires VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
