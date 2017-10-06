--
-- Sauvegarde de la table `page`
--
DROP TABLE IF EXISTS `page_auto_backup`;
CREATE TABLE `page_auto_backup` LIKE `page`;
INSERT INTO `page_auto_backup` SELECT * FROM `page` ;

--
-- Sauvegarde de la table `page_element`
--
DROP TABLE IF EXISTS `page_element_auto_backup`;
CREATE TABLE `page_element_auto_backup` LIKE `page_element`;
INSERT INTO `page_element_auto_backup` SELECT * FROM `page_element` ;

--
-- Sauvegarde de la table `categorie`
--
DROP TABLE IF EXISTS `categorie_auto_backup`;
CREATE TABLE `categorie_auto_backup` LIKE `categorie`;
INSERT INTO `categorie_auto_backup` SELECT * FROM `categorie` ;

--
-- Sauvegarde de la table `sous_categorie`
--
DROP TABLE IF EXISTS `sous_categorie_auto_backup`;
CREATE TABLE `sous_categorie_auto_backup` LIKE `sous_categorie`;
INSERT INTO `sous_categorie_auto_backup` SELECT * FROM `sous_categorie` ;