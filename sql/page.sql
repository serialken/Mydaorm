-- phpMyAdmin SQL Dump
-- version 4.1.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 28, 2014 at 04:22 PM
-- Server version: 5.5.38-0+wheezy1
-- PHP Version: 5.4.4-14+deb7u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/* SET NAMES default; */

DROP TABLE IF EXISTS `page`;
--
-- Database: `dev_mroad`
--

-- --------------------------------------------------------

--
-- Table structure for table `page`
--

CREATE TABLE IF NOT EXISTS `page` (
  `ID` int(11) NOT NULL,
  `ID_ROUTE` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `DESC_COURT` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `DESCRIPTION` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `MENU` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PAG_DEFAUT` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SS_CAT_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UNIQ_229F5B942DDEF833` (`ID_ROUTE`),
  KEY `IDX_229F5B943C388DE0` (`SS_CAT_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
--
-- Dumping data for table `page`
--

INSERT INTO `page` (`ID`, `ID_ROUTE`, `DESC_COURT`, `DESCRIPTION`, `MENU`, `PAG_DEFAUT`, `SS_CAT_ID`) VALUES
(1, 'ams_accueil', 'Accueil', 'Accueil', '1', NULL, NULL),
(2, 'cat_admin', 'Administration', 'Administration', '1', 'ams_admin_utilisateurs', NULL),
(3, '_ams_authentification', 'Identification', 'Page d''identification', '1', NULL, NULL),
(4, '_ams_deconnexion', 'Déconnexion', 'Déconnexion', '1', NULL, NULL),
(5, '_ams_apropos', 'A propos', 'A propos d MRoad', '1', NULL, 19),

-- ADMINISTRATION --

(100, 'admin_modif_groupedepot', 'Modif groupe de dépôts', 'Groupe de dépôts', '0', NULL, 10),
(101, 'admin_liste_groupedepot', 'Groupe de dépôts', 'Groupe dépôts', '1', NULL, 10),
(102, 'admin_message', 'Gestion des infos bulles', 'Gestion des infos bulles', '', NULL, 2),
(103, 'repartition_global', 'Répartition par défaut', 'Répartition par défaut', 1, '',  3),
(105, 'repartition_societe', 'Exception société', 'Exception société',  1, '', 3),
(106, 'repartition_produit', 'Exception produit', 'Exception produit', 1, '',  3),

(110, 'ams_admin_utilisateur', 'Modification utilisateur', 'Administration d''un utilisateur', '0', NULL, 11),
(111, 'admin_liste_utilisateur', 'Gestion des utilisateurs', 'Gestion des utilisateurs', '1', NULL, 11),

(120, 'ams_admin_profil', 'Profil', 'Pages rattachées à  un profil', '0', NULL, 12),
(121, 'admin_liste_profil', 'Gestion des profils', 'Gestion des profils', '1', NULL, 12),
(122, 'admin_modif_profil', 'Modification profil', 'Modification d''un profil', '', NULL, 12),
(123, 'admin_ajout_profil', 'Ajout d''un profil', NULL, '', NULL, 12),
(124, 'admin_supp_profil', 'Suppression d''un profil', NULL, '', NULL, 12),
(125, 'admin_arborescence_page', 'Arborescence des pages', NULL, '', NULL, 12),

(130, 'fichier_index', 'Gestion des logos', 'Gestion des logos', '', NULL, 13),
(131, 'fichier_ajout', 'Ajout logo', 'Ajout d''un logo', '', NULL, 13),
(132, 'fichier_update', 'Mise à jour logo', 'Mise à jour d''un logo', '', NULL, 13),
(133, 'fichier_delete', 'Suppression logo', 'Suppression d''un logo', '', NULL, 13),

(140, 'depot', 'Gestion dépôts', 'Gestion des dépôts', '', '', 14),
(141, 'depot_modification_ajax', 'Modification dépôt', 'Modification dépôt', '', '', 14),
(142, 'depot_creation_ajax', 'Création dépôt', 'Création dépôt', '', NULL, 14),
(143, 'depot_ajout_commune_ajax', 'Ajout commune', 'Ajout commune', '', NULL, 14),

(150, 'feries_index', 'Gestion des jours fériés', 'Gestion des jours fériés', '', NULL, 15),
(151, 'feries_ajout', 'Ajout jour fériés', 'Ajout des jours fériés', '', NULL, 15),
(152, 'feries_suppression', 'Suppression jour fériés', 'Suppression des jours fériés', '', NULL, 15),

(160, 'admin_liste_motif', 'Administration des motifs', 'Administration des motifs', '', NULL, 16),
(170, 'admin_imputation_service', 'Gestion des imputations', 'Gestion des imputations', '', NULL, 17),
(180, 'admin_imprimante', 'Gestion des imprimantes', 'Gestion des imprimantes', '', NULL, 18),
(190, 'admin_info_portage', 'Types d\'info portage', 'Types d\'info portage', '', NULL, 1),
(4010, 'suivi_cron', 'suivi des cron', 'Suivi Activité Cron', '1', 'suivi_cron_vue_generale', 40),


-- Adresses --

(200, 'adresse_liste_rejet', 'Gestion des rejets', 'Gestion des rejets', '', NULL, 20),
(201, 'adresse_modif_rejet', 'Modification adresse reje', 'Modification adresse rejetée', '', NULL, 20),
(202, 'adresse_liste_geocode', 'Liste adresse geocode', 'Liste adresse geocode', '', '', 20),
(203, 'portage_details', 'Détails infos portages', '', '', NULL, 20),

(210, 'adresse_point_livraison', 'Point de livraison', 'Point livraison', '', NULL, 21),
(211, 'point_livraison_geocode', 'Liste des adresses geocod', '', '', NULL, 21),
(212, 'adresse_geocode_update', 'Mis à  jour adresse geoco', '', '', NULL, 21),

(220, 'recherche_abonne', 'Abonné', 'Abonnés', '', NULL, 22),
(221, 'fiche_abonne', 'Fiche  abonné', '', '', NULL, 22),
(222, 'ajout_info_portage', 'Ajout info portage', '', '', NULL, 22),

(242, 'adresse_bordereau', 'Gestion des bordereaux', 'Gestion des bordereaux', '', NULL, 24),
(243, 'adresse_etiquette', 'Gestion des étiquettes', 'Gestion des étiquettes', '', NULL, 25),

(260, 'change_tournee_abos', 'Changement de tournées', 'Changement de tournées', '', NULL, 26),

-- Produits --

(300, 'societe', 'Gestion des produits', 'Gestion des produits societe', '', NULL, 30),
(301, 'produit_creation_ajax', 'creation produit', NULL, '', NULL, 30),
(302, 'societe_creation_ajax', 'Création Société', 'Création Société', '', '', 30),
(303, 'societe_modification_ajax', 'Modification Société', 'Modification Société', '', '', 30),
(304, 'produit_modification_ajax', 'Modification produit', NULL, '', NULL, 30),

(320, 'caract_list', 'Caractéristiques produit', 'Caractéristiques produit', '', NULL, 32),
(321, 'caract_update_ajax', 'Update caractéristique', 'Mise à jour d''une caractéristique', '', NULL, 32),
(322, 'caract_inactiv_ajax', 'Desactive caractéristique', 'Désactive une caractéristique', '', NULL, 32),

(330, 'calendrier_securise', 'Calendrier livraisons', 'Calendrier livraisons', '', 'calendrier_securise', 33),

(331, 'calendrier_operation_speciale', 'Operation speciale', 'Calendrier opérations spéciales', '', 'calendrier_operation_speciale', 34),
(332, 'calendrier_operation_special_add', 'Ajout opération spéciale', 'Ajout opération spéciale', '', NULL, 34),

(340, 'calendrier_parution_speciale', 'Calendrier parutions spéc', 	'Calendrier parutions spéciales', '', 'calendrier_parution_speciale' ,34),

(350, 'type_produit', 'Type Produit', 	'type produit', '', '' ,35),
(351, 'type_produit_modification_ajax', 'Modification Produit', 'Modification type produit', '', '' ,35),
(352, 'type_produit_creation', 'Creation Produit', ' Creationtype produit', '', '' ,35),
-- (353, 'type_produit_suppression', 'Supression Produit', ' Supressiontype produit', '', '' ,35),

-- Distribution -- 

(310, 'calendrier', 'Calendrier parutions', 'calendrier parutions', '', 'calendrier', 31),
(311, 'calendrier_create_parution', 'Edition spéciale', 'Ajout d''édition spéciale au calendrier', '', NULL, 31),
(312, 'calendrier_update_report', 'Update report fichier', 'Mis a jour des reports de fichiers auto', '', NULL, 31),

(5000, 'qtes_quotidiennes_index', 'Quantités quotidiennes', 'Quantités quotidiennes', '', NULL, 50),
(5010, 'qtes_quotidiennes_depot', 'Qtés quotidiennes / dépôt', 'Quantités quotidiennes par dépôts', '0', NULL, 50),
(5011, 'qtes_quotidiennes_classify', 'Non classés', 'Non classés', '0', NULL, 50),
(5020, 'liste_clients_cd_produit', 'Liste des clients servis ', 'Liste des clients d''un dépôt servi d''un produit', '0', NULL, 50),
(5030, 'ams_carto_demo', 'Démo suivi des tournées', 'Démonstration de l''affichage du suivi des tournées.', '0', NULL, 50),

(5201, 'reperage_vue_generale', 'Repérage', 'Repérage', '0', NULL, 52),
(5202, 'reperage_detail', 'reperage detail', 'reperage detail', '0', NULL, 52),

(5301, 'ouverture', 'Ouverture Centres', 'Ouverture des Centres de Distibution', '0', NULL, 53),

(5401, 'comptes_rendus_reception', 'Comptes Rendus Réception', 'Comptes Rendus de Réception', '0', NULL, 54),

(5501, 'comptes_rendus_distribution', 'Comptes Rendus Distrib', 'Comptes Rendus Distrib', '0', NULL, 55),
(5502, 'compte_rendu_modif_tournee', 'Modif Comptes Rendus ', 'Modification des Comptes Rendus de Distribution', '0', NULL, 55),


(5700, 'feuille_portage', 'Documents de Distribution', 'Documents de Distribution', '0', NULL, 57),
(5800, 'etiquette', 'Etiquette', 'Etiquette', '0', NULL, 58),
(195, 'admin_document', 'Mes documents', 'Mes documents', '', NULL, 111),
(5900, 'france_routage', 'France routage', 'France routage', '', NULL, 112),
(5901, 'ams_horspresse_liste', 'Campagnes hors presse', 'Hors presse', 1, NULL, 113),
(5902, 'ams_horspresse_ajouter', 'Nouvelle campagne hors presse', 'Hors presse', 0, NULL, 113),
(5903, 'ams_horspresse_calendrier_index', 'Calendrier Hors Presse', 'Affiche le calendrier des opérations hors presse', 1, NULL, 114),
(5905, 'prospec_france_routage', 'France routage prospec', 'France routage prospection', '', NULL, 115),

(5910, 'suivi_production', 'suivi de production', 'suivi de la production', '1', 'suivi_production_vue_generale', 59),

-- CRM --

(5101, 'crm_vue_generale', 'Réclams / Rem. Infos', 'Réclams / Rem. Infos', '0', NULL, 51),
(5102, 'crm_index_donnees', 'reclamations-rem-infos-de', 'reclamations-rem-infos-detail/tout', '1', NULL, 51),
(5103, 'crm_display_detail', 'reclamations-rem-infos-de', 'reclamations-rem-infos-detail', '2', NULL, 51),
(5104, 'crm_edit_and_validate', 'reclamations-rem-infos-de', 'reclamations-rem-infos-detail', '3', NULL, 51),
(5105, 'crm_serach_client_to_create_rem_info', 'reclamations-rem-infos-cr', 'reclamations-rem-infos-search-client', '2', NULL, 51),
(5106, 'crm_create_rem', 'reclamations-rem-infos-cr', 'reclamations-rem-infos-create-rem', '3', NULL, 51),
(5107, 'crm_create_arbitrage', 'creation-arbitrage', 'creation-arbitrage', '3', NULL, 51),
(5108, 'crm_display_zoom', 'reclamations-rem-infos-societe', 'reclamations-rem-infos-societe','2', NULL,51),
(5109, 'crm_display_div', 'reclamations-rem-infos-societe-div', 'reclamations-rem-infos-societe-div','2', NULL,51),

(5601, 'arbitrage_vue_generale', 'Arbitrage', 'Arbitrage', '0', NULL, 56),

-- PCO

-- (5500, 'alimentation_pai_poids_pco', 'Alimentation Poids PCO', 'Alimentation Poids PCO', '0', NULL, 550),
(5510, 'liste_pai_poids_pco', 'Poids PCO', 'Saisie des poids', '1', NULL, 550),
(5600, 'compte_rendu_reception_camion', 'Réception des camions', 'Réception des camions', '1', NULL, 560),
(5610, 'compte_rendu_reception_pco', 'Réception PCO', 'Reception PCO', '1', NULL, 570),

-- Gestion Des Porteurs --
(6110, 'liste_pai_refactivite', 'Activité', 'Référentiel - Activité', '1', NULL, 610),
(6120, 'liste_pai_reftransport', 'Transport', 'Référentiel - Transport', '1', NULL, 610),
(6125, 'liste_pai_refincident', 'Incident', 'Référentiel - Incident', '1', NULL, 610),

(6600, 'liste_modele_journal', 'Journal', 'Modèle - Journal', '1', NULL, 660),
(6610, 'liste_modele_groupe', 'Groupes tournées', 'Modèle - Groupes tournées', '1', NULL, 660),
(6620, 'liste_modele_tournee', 'Tournées', 'Modèle - Tournées', '1', NULL, 660),
(6630, 'liste_modele_tournee_jour', 'Tournées Jour', 'Modèle - Tournées Jour', '1', NULL, 660),
(6640, 'liste_modele_supplement', 'Suppléments', 'Modèle - Suppléments', '1', NULL, 660),
(6660, 'liste_modele_activite', 'Activités Presse', 'Modèle - Activités Presse', '1', NULL, 660),
-- (6670, 'liste_modele_activiteHP', 'Activités Hors-Presse', 'Modèle - Activités Hors-Presse', '1', NULL, 660),
(6675, 'liste_remplacement', 'Remplacement', 'Modèle - Remplacement', '1', NULL, 660),
(6676, 'liste_remplacement_jour', 'Détail Remplacement', 'Modèle - Détail Remplacement', '1', NULL, 660),
(6690, 'modele_planning', 'Planning', 'Modèle - Planning', '1', NULL, 660),
(6695, 'liste_modele_request', 'Requêtes', 'Modèle - Requêtes', '1', NULL, 660),

(6700, 'liste_etalon', 'Etalons', 'Modèle - Etalons', '1', NULL, 660),
(6705, 'liste_etalon_tournee', 'Détail Etalons', 'Modèle - Détail Etalons', '1', NULL, 660),

(6800, 'pai_privilegie', 'Privilège de modification', 'Privilège de modification', '0', NULL, 680),
(6810, 'liste_pai_journal', 'Journal', 'Paie - Journal', '1', NULL, 680),
(6815, 'liste_pai_poids_depot', 'Poids', 'Paie - Poids', '1', NULL, 680),
(6820, 'liste_pai_heure', 'Heures', 'Paie - Heures', '1', NULL, 680),
(6831, 'liste_pai_tournee', 'Tournées', 'Paie - Tournées', '1', NULL, 680),
(6832, 'liste_pai_produit', 'Produits', 'Paie - Produits', '1', NULL, 680),
(6840, 'liste_pai_abonne', 'Abonnés', 'Paie - Abonnés', '1', NULL, 680),
(6841, 'liste_pai_reperage', 'Repérages', 'Paie - Repérages', '1', NULL, 680),
(6842, 'liste_pai_diffuseur', 'Diffuseurs', 'Paie - Diffuseurs', '1', NULL, 680),
(6843, 'liste_pai_horspresse', 'Hors-Presse', 'Paie - Hors-Presse', '1', NULL, 680),
(6844, 'liste_pai_presse', 'France Routage', 'Paie - France Routage', '1', NULL, 680),
(6850, 'liste_pai_activite', 'Activités Presse', 'Paie - Activités Presse', '1', NULL, 680),
(6852, 'liste_pai_activiteHP', 'Activités Hors-Presse', 'Paie - Activités Hors-Presse', '1', NULL, 680),
(6858, 'liste_pai_reclamation', 'Réclamations', 'Paie - Réclamations', '1', NULL, 680),
(6860, 'liste_pai_incident', 'Incidents', 'Paie - Incidents', '1', NULL, 680),
(6890, 'pai_planning', 'Planning', 'Paie - Planning', '1', NULL, 680),
(6891, 'liste_pai_resumeemploye', 'Résumé', 'Paie - Résumé', '1', NULL, 680),
(6895, 'liste_pai_annexe', 'Annexes', 'Paie - Annexes', '1', NULL, 680),
(6899, 'liste_annexe', 'Fichiers', 'Paie - Fichiers', '1', NULL, 680),
-- RH
(6870, 'alimentation_employe', 'Alimentation des employés', 'Alimentation des employés', '0', NULL, 680),
(6400, 'liste_employe_journal', 'Journal', 'RH - Journal', '1', NULL, 690),
(6500, 'liste_employe_recherche', 'Recherche', 'RH - Recherche employé', '1', NULL, 690),
(6680, 'liste_employe_cycle', 'Cycle', 'RH - Cycle', '1', NULL, 690),
(6871, 'liste_employe_contrat', 'Contrat', 'RH - Contrat', '1', NULL, 690),
(6873, 'liste_employe_cdd', 'CDD', 'RH - CDD', '1', NULL, 690),
(6875, 'liste_employe_contrathp', 'Hors-Presse', 'RH - Hors-Presse', '1', NULL, 690),
(6894, 'liste_employe_suivi_horaire', 'Heures garanties', 'RH - Heures garanties', '1', NULL, 690),
(6920, 'liste_employe_heure_sup', 'Heures supplémentaires', 'RH - Heures supplémentaires', '1', NULL, 690),
(6950, 'liste_employe_transfert', 'Transfert', 'RH - Transfert', '1', NULL, 690),
(6960, 'liste_employe_affectation', 'Affectation', 'RH - Affectation', '1', NULL, 690),
(6995, 'liste_employe_request', 'Requêtes', 'RH - Requêtes', '1', NULL, 690),

-- Paie
(6130, 'liste_pai_refnatureclient', 'Nature Client', 'Référentiel - Nature Client', '1', NULL, 910),
(6140, 'liste_pai_reftypeurssaf', 'Type Urssaf', 'Référentiel - Type Urssaf', '1', NULL, 910),
(6150, 'liste_pai_refempsociete', 'Société', 'Référentiel - Société', '1', NULL, 910),
(6160, 'liste_pai_refemploi', 'Emploi', 'Référentiel - Emploi', '1', NULL, 910),
(9110, 'liste_pai_refpopulation', 'Population', 'Référentiel - Population', '1', NULL, 910),
(9115, 'liste_pai_refferie', 'Jours Fériés', 'Référentiel - Jours Fériés', '1', NULL, 910),
(9120, 'liste_pai_refremuneration', 'Taux horaire', 'Référentiel - Taux horaire', '1', NULL, 910),
(9130, 'liste_pai_refpoids', 'Rémunération Produit', 'Référentiel - Rémunération Produit', '1', NULL, 910),
(9135, 'liste_pai_refsupplement', 'Majoration supplément', 'Référentiel - Majoration supplément', '1', NULL, 910),
(9140, 'liste_pai_refqualite', 'Qualité', 'Référentiel - Qualité', '1', NULL, 910),
(9145, 'liste_pai_refurssaf', 'Urssaf', 'Référentiel - Urssaf', '1', NULL, 910),
(9150, 'liste_pai_refpostepaiegeneral', 'Poste Paie Général', 'Référentiel - Poste Paie Général', '1', NULL, 910),
(9160, 'liste_pai_refpostepaieactivite', 'Poste Paie Activité', 'Référentiel - Poste Paie Activité', '1', NULL, 910),
-- (9170, 'liste_pai_refpostepaiesupplement', 'Poste Paie Supplément', 'Poste Paie Supplément', '1', NULL, 910),

(9330, 'liste_int_journal', 'Journal', 'Interface - Journal', '1', NULL, 930),
(9340, 'liste_pai_stc', 'Paie Individuelle', 'Interface - Paie Individuelle', '1', NULL, 930),
(9350, 'liste_pai_mensuel', 'Paie Collective', 'Interface - Paie Collective', '1', NULL, 930),
(9370, 'liste_pai_int_traitement', 'Interfaces', 'Interface - Interfaces', '1', NULL, 930),
(9380, 'liste_pai_int_log', 'Log', 'Interface - Log', '1', NULL, 930),
(9390, 'liste_pai_int_alimentation', 'Alimentation', 'Interface - Alimentation', '1', NULL, 930),

-- Cartographie --
(230, 'adresse_export_recherche', 'Requêteur d''optimisation', 'Requêteur d''optimisation', '', NULL, 23),
(231, 'adresse_export_query', 'Export optimisation', 'Export optimisation', '', NULL, 23),
(232, 'adresse_export_query_liste', 'Liste des requete enregis', '', '', NULL, 23),

(7000, 'ams_carto_voirtournees', 'Sélectionner des tournées', 'Sélectionner des tournées', '', NULL, 700),
(7001, 'ams_carto_demo3', 'Démonstration de tournée', NULL, '', NULL, 700),
(7002, 'ams_carto_affichertournees', 'Voir les tournées sur la ', 'Afficher les tournées', '', NULL, 700),
(7003, 'ams_carto_nouveaupoint', 'Nouveau point', 'Nouveau point livraison', '', NULL, 702),
(7004, 'ams_carto_creerpoint', 'Désigner le nouveau point', 'Désigner le nouveau point de livraison sur la carte', '', NULL, 702),

-- Reporting --
(10000, 'ams_reporting_homepage', 'Reporting global', 'reporting global','0', NULL,1000),
(10001, 'reporting_detail', 'Reporting detail', 'reporting detail','0', NULL,1000),
(10004, 'indicateurs', 'Indicateurs', 'Indicateurs', '0' , NULL, '1001'),
 
-- INVENDU
 (10003, 'saisie_invendu_index', 'Saisie des invendus', 'Saisie des invendus','', NULL, 116);

--
-- Constraints for dumped tables
--
--
-- Constraints for table `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `FK_229F5B943C388DE0` FOREIGN KEY (`SS_CAT_ID`) REFERENCES `sous_categorie` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;
