SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `sous_categorie`;

CREATE TABLE IF NOT EXISTS `sous_categorie` (
  `id` int(11) NOT NULL,
  `libelle` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `class_image` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `page_defaut` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `cat_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F60144962E182825` (`CAT_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `sous_categorie` (`id`, `cat_id`, `libelle`, `class_image`, `page_defaut`) VALUES

-- ADMINISTRATION --

(1, 1,  'Types d\'info portage', 'administrationinfoportage', 'admin_info_portage'),
(2, 1,'Gestion des infos bulles', 'administrationmessage', 'admin_message'),
(3, 1,'Gestion des repartitions', 'administrationrepartitionglobal', 'repartition_global'),
(4, 1,'Schémas de répartition', 'administrationrepartition', 'repartition_exception'),
(10, 1, 'Groupes de depôts', 'administrationgroupesdepots', 'admin_liste_groupedepot'),
(11, 1, 'Gestion des utilisateurs', 'administrationutilisateurs', 'admin_liste_utilisateur'),
(12, 1, 'Gestion des profils', 'administrationprofils', 'admin_liste_profil'),
(13, 1, 'Gestion des logos', 'administrationlogos', 'fichier_index'),
(14, 1, 'Gestion des dépôts', 'administrationdepots', 'depot'),
(15, 1, 'Gestion des jours fériés', 'administrationparutions', 'feries_index'),
(16, 1, 'Gestion des motifs', 'administrationmotifarbitrage', 'admin_liste_motif'),
(17, 1, 'Gestion des imputations', 'administrationimputationservice', 'admin_imputation_service'),
(18, 1, 'Gestion des imprimantes', 'administrationimprimante', 'admin_imprimante'),
(19, 1, 'A propos', 'apropos', '_ams_apropos'),
(40, 1, 'Exploitation', 'exploitation', 'suivi_cron_vue_generale'),

-- Adresses --

(20, 2, 'Gestion des rejets', 'adressesrejets', 'adresse_liste_rejet'),
(21, 2, 'Point livraison', 'adressespointlivraison', 'adresse_point_livraison'),
(22, 2, 'Abonnés', 'adressesabonnes', 'recherche_abonne'),
(24, 2, 'Gestion des bordereaux', 'adressebordereau', 'adresse_bordereau'),
(25, 2, 'Gestion des etiquettes', 'adresse_etiquette', 'adresse_etiquette'),
(26, 2, 'Changement de tournées des abonnés', 'change_tournee_abos', 'change_tournee_abos'),

-- Produits --

(30, 3, 'Gestion des produits', 'produitsproduits', 'societe'),
(31, 3, 'Calendrier', 'produitscalendrier', 'calendrier'),
(32, 3, 'Caractéristiques produit', 'produitscaracteristiques', 'caract_list'),
(35, 3, 'Gestion types produits', 'produitstypes', 'type_produit'),


-- Distribution -- 

(50, 5, 'Quantités quotidiennes', 'distributionquantitesquotidiennes', 'qtes_quotidiennes_index'),
(52, 5, 'Repérage', 'distributionreperage', 'reperage_vue_generale'),
(53, 5, 'Ouverture CD', 'distributionouverturecd', 'ouverture'),
(54, 5, 'Comptes-Rendus Réception', 'receptioncomptesrendus', 'comptes_rendus_reception'),
(55, 5, 'Comptes-Rendus Distribution', 'distributioncomptesrendus', 'comptes_rendus_distribution'),
(33, 5, 'Calendrier livraisons', 'produitscalendrierlivraisons', 'calendrier_securise'),
(34, 5,'calendrier Parution Spéciale','calendrieroperationsspeciales','calendrier_parution_speciale'),
(57, 5, 'Documents de Distribution', 'feuilleportage', 'feuille_portage'),
(58, 5, 'Etiquettes', 'etiquette', 'etiquette'),
(111, 5, 'Mes documents', 'administrationdocument', 'admin_document'),
(112, 5, 'France Routage', 'franceroutage', 'france_routage'),
(115, 5, 'France Routage Prospection', 'france_routage_prospection', 'prospec_france_routage'),
(59, 5, 'Suivi De Production', 'suivi_de_production', 'suivi_production_vue_generale'),

-- PCO
(550, 50, 'Saisie des poids', 'porteurssaisiepoids', 'liste_pai_poids_pco'),
(560, 50, 'Reception des camions', 'reception_camions', 'compte_rendu_reception_camion'),
(570, 50, 'Reception PCO', 'cptr_reception_pco', 'compte_rendu_reception_pco'),

-- Gestion des porteurs
(610, 60, 'Référentiel Tournée', 'porteursreferentieltournee', 'liste_pai_refactivite'),
(660, 60, 'Modèle', 'porteursmodele', 'liste_modele_journal'),
(680, 60, 'Paie', 'porteurspaie', 'liste_pai_journal'),
(690, 60, 'RH', 'porteursRH', 'liste_employe_contrat'),

-- Paie
(910, 90, 'Référentiel Paie', 'porteursreferentielpaie', 'liste_pai_refpostepaiegeneral'),
(930, 90, 'Pléiades', 'pleiades', 'liste_int_journal'),

-- Carto --
(700, 70, 'Sélectionner les tournées', 'cartographieselectiontournees', 'ams_carto_voirtournees'),
(701, 70, 'Afficher la carte des tournées', 'cartographieaffichercarte', 'ams_carto_affichertournees'),
(702, 70, 'Nouveau point de livraison', 'cartographiepointlivraison', 'ams_carto_nouveaupoint'),
(703, 70, 'Ordonner les tournées', 'cartographieordonnertournees', 'ams_carto_ordonnertournees'),
(23, 70, 'Optimisation', 'adressesptimisation', 'adresse_export_recherche'),

-- CRM --
(51, 80, 'CRM', 'distributioncrm', 'crm_vue_generale'),
(56, 80, 'Arbitrage', 'distributionarbitrage', 'arbitrage_vue_generale'),

-- Reporting --
(1000, 100, 'Suivi des centres', 'reportingpilotagecentre', 'ams_reporting_homepage'),
(1001, 100, 'Indicateurs', 'reportingpilotagecentre', 'indicateurs'),

-- Hors Presse --
(113, 200, 'Gestion des campagnes', 'administrationhorspresse', 'ams_horspresse_liste'),
(114, 200, 'Calendrier Hors Presse', 'hpcalendrier', 'ams_horspresse_calendrier_index'),

-- INVENDU
(116, 5, 'Gestions des invendus', 'invendus', 'saisie_invendu_index');

ALTER TABLE `sous_categorie`
  ADD CONSTRAINT `FK_F60144962E182825` FOREIGN KEY (`cat_id`) REFERENCES `categorie` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;
