-- phpMyAdmin SQL Dump
-- version 4.1.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 28, 2014 at 04:23 PM
-- Server version: 5.5.38-0+wheezy1
-- PHP Version: 5.4.4-14+deb7u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
 SET NAMES default;
--
-- Database: `dev_mroad`
--
DROP TABLE IF EXISTS `page_element`;

-- --------------------------------------------------------

--
-- Table structure for table `page_element`
--

CREATE TABLE IF NOT EXISTS `page_element` (
  `id` int(11) NOT NULL,
  `pag_id` int(11) DEFAULT NULL,
  `desc_court` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `oblig` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BB20B5AC21432107` (`pag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `page_element`
--

INSERT INTO `page_element` (`id`, `pag_id`, `desc_court`, `libelle`, `oblig`) VALUES
(1, 1, 'PAGE', 'Accès page', 0),
(7, 3, 'PAGE', 'Accès page', 0),
(8, 4, 'PAGE', 'Accès page', 0),
(9, 5, 'PAGE', 'Accès page', 0),

-- ADMINISTRATION --
(995, 105, 'PAGE', 'Accés répartition société',0),
(996,106, 'PAGE', 'Accés répartition produit',0),
(999, 102, 'PAGE', 'Accés à la page gestion des infos bulles', 0),
(998, 103, 'PAGE', 'Accés à la page gestion des repartitions', 0),
-- (997, 104, 'PAGE', 'Accès à la page des schémas de répartition', 0),
(1000, 100, 'PAGE', 'Accés', 0),
(1001, 100, 'GRD_CODE', 'Code', 1),
(1002, 100, 'GRD_LIBELLE', 'Libellé', 1),
(1003, 100, 'DEP_CODE', 'Dépôts', 0),
(1004, 100, 'NOUVEAU', 'Ajout de nouveau', 0),
(1005, 100, 'VALIDATION', 'Enregistrer', 0),
(1010, 101, 'FILT_GRD_CODE', 'Filtre Code', 0),
(1011, 101, 'FILT_GRD_LIBELLE', 'Filtre Libellé', 0),
(1012, 101, 'FILT_DEP_LIBELLE', 'Filtre Dépôts', 0),
(1013, 101, 'COL_GRD_CODE', 'Col. Code', 0),
(1014, 101, 'COL_GRD_LIBELLE', 'Col. Libellé', 0),
(1015, 101, 'COL_DEP_LIBELLE', 'Col. Dépôts', 0),
(1016, 101, 'EDIT', 'Editer', 0),
(1017, 101, 'SUPPR', 'Supprimer', 0),

(1100, 110, 'ID', 'Identifiant', 1),
(1101, 110, 'MDP', 'Mot de passe', 0),
(1102, 110, 'NOM', 'Nom', 1),
(1103, 110, 'PRENOM', 'Prénom', 0),
(1104, 110, 'EMAIL', 'Courriel', 0),
(1105, 110, 'PRF_CODE', 'Profil', 0),
(1106, 110, 'GRD_CODE', 'Groupe de dépôts', 0),
(1107, 110, 'NOUVEAU', 'Ajout de nouveau', 0),
(1108, 110, 'VALIDATION', 'Enregistrer', 0),
(1110, 111, 'FILT_NOM_PRENOM', 'Filtre Nom complet', 1),
(1111, 111, 'FILT_GRD_LIBELLE', 'Filtre Groupe de dépôts', 0),
(1112, 111, 'FILT_PRF_LIBELLE', 'Filtre Profil', 0),
(1113, 111, 'COL_UTL_ID', 'Col. ID', 0),
(1114, 111, 'COL_UTL_MDP', 'Col. Mot de passe', 0),
(1115, 111, 'COL_NOM_PRENOM', 'Col. Nom complet', 0),
(1116, 111, 'COL_UTL_EMAIL', 'Col. Email', 0),
(1117, 111, 'COL_GRD_LIBELLE', 'Col. Groupe de dépôts', 0),
(1118, 111, 'COL_PRF_LIBELLE', 'Col. Profil', 0),
(1119, 111, 'EDIT', 'Editer', 0),
(11110, 111, 'SUPPR', 'Supprimer', 0),

(1200, 120, 'PRF_CODE', 'Code', 1),
(1201, 120, 'PRF_LIBELLE', 'Libellé', 1),
(1202, 120, 'NOUVEAU', 'Ajout de nouveau', 1),
(1203, 120, 'VALIDATION', 'Enregistrer', 1),
(1204, 120, 'CAT_MENU', 'Menus', 1),
(1210, 121, 'PAGE', 'Liste des profil', 0),
(1211, 121, 'FILT_PRF_LIBELLE', 'Filtre Nom du profil', 1),
(1212, 121, 'FILT_UTL_NOM_PRENOM', 'Filtre Utilisateurs', 0),
(1213, 121, 'COL_PRF_LIBELLE', 'Col. Nom du profil', 0),
(1214, 121, 'COL_UTL_NOM_PRENOM', 'Col. Utilisateurs', 0),
(1215, 121, 'EDIT', 'Editer', 0),
(1216, 121, 'SUPPR', 'Supprimer', 0),
(1220, 122, 'PAGE', 'Liste des profil', 0),
(1221, 122, 'EDIT', 'Modification profil', 0),
(1230, 123, 'PAGE', 'Accés à la page', 0),
(1231, 123, 'EDIT', 'Modification', 0),
(1240, 124, 'PAGE', 'Suppression', 0),
(1250, 125, 'PAGE', 'Arborescence des pages', 0),

(1300, 130, 'PAGE', 'Accès gestion logos', 0),
(1310, 131, 'PAGE', 'Accès ajout logo', 0),
(1320, 132, 'PAGE', 'Accès mise à jour logo', 0),
(1330, 133, 'PAGE', 'Accès suppression logo', 0),

(1400, 140, 'PAGE', 'Accés à la page gestion depot', 0),
(1410, 141, 'PAGE', 'Accés à la page modification depot', 0),
(1420, 142, 'PAGE', 'Accés à la page creation depot', 0),
(1430, 143, 'PAGE', 'Accés à la page ajout commune depot', 0),

(1500, 150, 'PAGE', 'Accès page jours fériés', 0),
(1510, 150, 'PAGE', 'Accès ajout fériés', 0),
(1520, 150, 'PAGE', 'Accès suppression fériés', 0),

(1600, 160, 'PAGE', 'Accès page - Administration des motifs', 0),
(1700, 170, 'PAGE', 'Accès page - Gestion des imputations', 0),
(1800, 180, 'PAGE', 'Accès page - Gestion des imprimantes', 0),

(40100, 4010, 'PAGE', 'Accès onglet - Suivi CRON', 0),

(1900, 190, 'PAGE', 'Accès page - Gestion des types d\'info portage', 0),
(1950, 195, 'PAGE', 'Accès page - Mes documents', 0),

-- Adresses --

(2000, 200, 'PAGE', 'Accés à la page rejets adresse', 0),
(2001, 200, 'EDIT', 'Edition adresse rejetée', 0),
(2010, 201, 'PAGE', 'Accés à la page de modification d''adresse rej', 0),
(2020, 202, 'PAGE', 'Accés à la page liste geocode', 0),
(2030, 203, 'PAGE', 'Accés à la page des infos portages', 0),

(2100, 210, 'PAGE', 'Accés à la page point livraison', 0),
(2110, 211, 'PAGE', 'Accés à la page liste des adresses geocodées', 0),
(2120, 212, 'PAGE', 'Accés à la page modification des adresses geo', 0),

(2200, 220, 'PAGE', 'Recherche abonné', 0),
(2210, 221, 'PAGE', 'Accés fiche abonné', 0),
(2220, 222, 'PAGE', 'Ajout info portage', 0),

(2300, 230, 'PAGE', 'Accés page export', 0),
(2310, 231, 'PAGE', 'Accés resultat et export au format CSV/EXCEL', 0),
(2320, 232, 'PAGE', 'Accés à la liste de ses requête enregistrées', 0),

(2400, 242, 'PAGE', 'Accès page - Gestion des bordereaux', 0),

(2500, 243, 'PAGE', 'Accès page - Gestion des etiquettes', 0),

(2600, 260, 'PAGE', 'Accès page - Changement de tournées des abonnés', 0),
-- Produits --

(3000, 300, 'PAGE', 'Accés à la page des sociétés', 0),
(3010, 301, 'PAGE', 'Accés à la page produit creation', 0),
(3020, 302, 'PAGE', 'Accés à la page societe creation', 0),
(3030, 303, 'PAGE', 'Accés à la page societe modification', 0),
(3040, 304, 'PAGE', 'Accés à la page produit modification', 0),

(3100, 310, 'PAGE', 'Accés à la page calendrier', 0),
(3110, 311, 'PAGE', 'Accés Ajax parution spéciale', 0),
(3120, 312, 'PAGE', 'Accés Ajax report fichier', 0),

(3200, 320, 'PAGE', 'Accès liste caractéristiques', 0),
(3210, 321, 'PAGE', 'Update Caractéristique', 0),
(3220, 322, 'PAGE', 'Desactive Caractéristique', 0),

(3300, 330, 'PAGE', 'Accés à la page calendrier securise', 0),
(3310, 331, 'PAGE', 'Accés opérations Spéciale', 0),
(3320, 	332, 'Ajout opération spéciale' ,'Ajout opération spéciale', 0),

(3400, 	340, 'Accés à la pagecalendrier opér' ,'calendrier opération spéciale', 0),

(3500, 	350, 'PAGE' ,'Accés à la page type produit', 0),
(3501, 	351, 'MODIF' ,'Accés à la page de modification', 0),
(3502, 	352, 'CREA' ,'Accés à la page de creation', 0),
-- (3503, 	353, 'SUPP' ,'Accés à la page supression', 0),

-- Distribution -- 

(50000, 5000, 'PAGE', 'Accès page', 0),
(50100, 5010, 'PAGE', 'Accès page Qtés par dépôt', 0),
(50101, 5011, 'PAGE', 'Accès page Qtés non classés', 0),
(50200, 5020, 'PAGE', 'Accès page Liste Clients', 0),
(50300, 5030, 'PAGE', 'Accès carto', 0),

(51061, 5104, 'PAGE', 'Accès page-reclamations-rem-infos-detail', 1),
(51062, 5104, 'ARBITRAGE', 'Arbitrage', 0),
(51063, 5104, 'ARBITRAGE-IMPUTATION-PAIE', 'Arbitrage-imputation-paie', 0),
(51100, 5101, 'PAGE', 'Accès page - Vue générale CRM ', 0),

(52100, 5201, 'PAGE', 'Accès page - Vue générale Repérage', 0),
(52101, 5202, 'PAGE', 'Accès page - Vue Reperage detail', 0),

(53100, 5301, 'PAGE', 'Accès page - Vue générale Ouverture CD', 0),
(53101, 5301, 'VISU', 'Affichage formulaire Ouverture CD', 0),

(54100, 5401, 'PAGE', 'Accès page - Vue générale Comptes Rendus Rece', 0),

(55100, 5501, 'PAGE', 'Accès page - Vue générale Comptes Rendus Dist', 0),
(55101, 5502, 'PAGE', 'Accès page - Vue générale Modif Comptes Rendu', 0),

(56100, 5601, 'PAGE', 'Accès page - Vue générale arbitrage', 0),
(56101, 5601, 'MODIF', 'Modification', 0),

(57000, 5700, 'PAGE', 'Accès page - Feuilles portage', 0),
(58000, 5800, 'PAGE', 'Accès page - Etiquette', 0),
(58001, 5800, 'IMPR_ETIQ', 'Accès impr (grosse) etiquette', 0),
(59000, 5900, 'PAGE', 'Accès page - France routage', 0),

(59010, 5901, 'PAGE', 'Accès page - Hors presse', 0),
(59020, 5902, 'PAGE', 'Accès page - Nouvelle campagne hors presse', 0),
(59030, 5903, 'PAGE', 'Accès calendrier', 0),
(59050, 5905, 'PAGE', 'Accès page - France routage prospection', 0),

(59100, 5910, 'PAGE', 'Accès page - Suivi Production', 0),

-- PCO
(55010, 5510, 'VISU', 'Visualisation', 1),
(55011, 5510, 'MODIF', 'Modification', 0),
(55015, 5510, 'ALIM_JOUR', 'Alimentation du jour courant', 1),
(55016, 5510, 'ALIM_MOIS', 'Alimentation du mois entier', 1),
(56000, 5600, 'PAGE', 'Accès page - Reception des camions', 1),
(56010, 5610, 'PAGE', 'Accès page - Reception PCO', 1),

-- Gestion des porteurs --
(61100, 6110, 'VISU', 'Visualisation', 1),
(61101, 6110, 'MODIF', 'Modification', 0),
(61200, 6120, 'VISU', 'Visualisation', 1),
(61201, 6120, 'MODIF', 'Modification', 0),
(61250, 6125, 'VISU', 'Visualisation', 1),
(61251, 6125, 'MODIF', 'Modification', 0),
(61300, 6130, 'VISU', 'Visualisation', 1),
(61400, 6140, 'VISU', 'Visualisation', 1),
(61500, 6150, 'VISU', 'Visualisation', 1),
(61600, 6160, 'VISU', 'Visualisation', 1),
(61601, 6160, 'MODIF', 'Modification', 1),

(66000, 6600, 'VISU', 'Visualisation', 1),
(66100, 6610, 'VISU', 'Visualisation', 1),
(66101, 6610, 'MODIF', 'Modification', 0),
(66200, 6620, 'VISU', 'Visualisation', 1),
(66201, 6620, 'MODIF', 'Modification', 0),
(66202, 6620, 'TRANSFERT', 'Transfert', 0),
(66300, 6630, 'VISU', 'Visualisation', 1),
(66301, 6630, 'MODIF', 'Modification', 0),
(66302, 6630, 'SUPERMODIF', 'Super Modification', 0),
(66400, 6640, 'VISU', 'Visualisation', 1),
(66401, 6640, 'MODIF', 'Modification', 0),
(66600, 6660, 'VISU', 'Visualisation', 1),
(66601, 6660, 'MODIF', 'Modification', 0),
-- (66700, 6670, 'VISU', 'Visualisation', 1),
-- (66701, 6670, 'MODIF', 'Modification', 0),
(66750, 6675, 'VISU', 'Visualisation', 1),
(66751, 6675, 'MODIF', 'Modification', 0),
(66760, 6676, 'VISU', 'Visualisation', 1),
(66900, 6690, 'VISU', 'Visualisation', 1),
(66950, 6695, 'VISU', 'Visualisation', 1),

-- Etalonnage
(67000, 6700, 'VISU', 'Visualisation', 1),
(67001, 6700, 'MODIF', 'Modification', 0),

(67050, 6705, 'VISU', 'Visualisation', 1),
(67051, 6705, 'MODIF', 'Modification', 0),
(67052, 6705, 'VALID', 'Validation', 0),

(68000, 6800, 'MOIS', 'Modification du mois entier', 1),
(68001, 6800, 'PAIE', 'Modification après blocage', 1),
(68002, 6800, 'RETRO', 'Modification rétroactive (DANGER !!!)', 1),
(68100, 6810, 'VISU', 'Visualisation', 1),
(68101, 6810, 'ACTU', 'Actualisation', 0),
(68150, 6815, 'VISU', 'Visualisation', 1),
(68151, 6815, 'MODIF', 'Modification', 0),
(68200, 6820, 'VISU', 'Visualisation', 1),
(68201, 6820, 'MODIF', 'Modification', 0),
(68310, 6831, 'VISU', 'Visualisation', 1),
(68311, 6831, 'MODIF', 'Modification', 0),
(68315, 6831, 'ALIM_JOUR', 'Alimentation du jour courant', 0),
(68316, 6831, 'ALIM_MOIS', 'Alimentation du mois entier', 0),
(68320, 6832, 'VISU', 'Visualisation', 1),
(68321, 6832, 'MODIF', 'Modification', 0),
(68400, 6840, 'VISU', 'Visualisation', 1),
(68401, 6840, 'MODIF', 'Modification', 0),
(68410, 6841, 'VISU', 'Visualisation', 1),
(68411, 6841, 'MODIF', 'Modification', 0),
(68420, 6842, 'VISU', 'Visualisation', 1),
(68421, 6842, 'MODIF', 'Modification', 0),
(68430, 6843, 'VISU', 'Visualisation', 1),
(68431, 6843, 'MODIF', 'Modification', 0),
(68440, 6844, 'VISU', 'Visualisation', 1),
(68441, 6844, 'MODIF', 'Modification', 0),
(68500, 6850, 'VISU', 'Visualisation', 1),
(68501, 6850, 'MODIF', 'Modification', 0),
(68505, 6850, 'ALIM_JOUR', 'Alimentation du jour courant', 0),
(68506, 6850, 'ALIM_MOIS', 'Alimentation du mois entier', 1),
(68520, 6852, 'VISU', 'Visualisation', 1),
(68521, 6852, 'MODIF', 'Modification', 0),
(68525, 6852, 'ALIM_JOUR', 'Alimentation du jour courant', 0),
(68526, 6852, 'ALIM_MOIS', 'Alimentation du mois entier', 1),
(68580, 6858, 'VISU', 'Visualisation', 1),
(68581, 6858, 'MODIF', 'Modification', 0),
(68600, 6860, 'VISU', 'Visualisation', 1),
(68601, 6860, 'MODIF', 'Modification', 0),
(68700, 6870, 'ALIM', 'Alimentation', 1),
(68900, 6890, 'VISU', 'Visualisation', 1),
(68910, 6891, 'VISU', 'Visualisation', 1),
(68950, 6895, 'VISU', 'Visualisation', 1),
(68990, 6899, 'VISU', 'Visualisation', 1),

-- RH
(64000, 6400, 'VISU', 'Visualisation', 1),
(64001, 6400, 'ACTU', 'Actualisation', 0),
(65000, 6500, 'VISU', 'Visualisation', 1),
(66800, 6680, 'VISU', 'Visualisation', 1),
(68710, 6871, 'VISU', 'Visualisation', 1),
(68730, 6873, 'VISU', 'Visualisation', 1),
(68750, 6875, 'VISU', 'Visualisation', 1),
(68751, 6875, 'MODIF', 'Modification', 0),
(68913, 6894, 'VISU', 'Visualisation', 1),
(68941, 6894, 'ACTU', 'Actualisation', 1),
(69200, 6920, 'VISU', 'Visualisation', 1),
(69201, 6920, 'ACTU', 'Actualisation', 1),
(69500, 6950, 'VISU', 'Visualisation', 1),
(69501, 6950, 'MODIF', 'Modification', 0),
(69600, 6960, 'VISU', 'Visualisation', 1),
(69601, 6960, 'MODIF', 'Modification', 0),
(69950, 6995, 'VISU', 'Visualisation', 1),

-- Cartographie --
(70000, 7000, 'ACCES', 'Accès à la page', 1),
(70001, 7003, 'ACCES', 'Accès à la page', 1),
(70002, 7002, 'ACCES', 'Accès à la carte', 0),
(70003, 7004, 'ACCESS', 'Accès à la carte', 0),

-- PAIE
(91100, 9110, 'VISU', 'Visualisation', 1),
(91150, 9115, 'VISU', 'Visualisation', 1),
(91151, 9115, 'MODIF', 'Modification', 0),
(91200, 9120, 'VISU', 'Visualisation', 1),
(91300, 9130, 'VISU', 'Visualisation', 1),
(91301, 9130, 'MODIF', 'Modification', 0),
(91350, 9135, 'VISU', 'Visualisation', 1),
(91351, 9135, 'MODIF', 'Modification', 0),
(91400, 9140, 'VISU', 'Visualisation', 1),
(91401, 9140, 'MODIF', 'Modification', 0),
(91450, 9145, 'VISU', 'Visualisation', 1),
(91451, 9145, 'MODIF', 'Modification', 0),
(91500, 9150, 'VISU', 'Visualisation', 1),
(91501, 9150, 'MODIF', 'Modification', 0),
(91600, 9160, 'VISU', 'Visualisation', 1),
(91601, 9160, 'MODIF', 'Modification', 0),
-- (91700, 9170, 'VISU', 'Visualisation', 1),
-- (91701, 9170, 'MODIF', 'Modification', 0),

(93300, 9330, 'VISU', 'Visualisation', 1),
(93301, 9330, 'ACTU', 'Actualisation', 0),
(93400, 9340, 'VISU', 'Visualisation', 1),
(93401, 9340, 'MODIF', 'Lancement', 0),
(93402, 9340, 'ANNUL', 'Annulation', 0),
(93500, 9350, 'VISU', 'Visualisation', 1),
(93501, 9350, 'MODIF', 'Lancement', 1),
(93502, 9350, 'BLOCAGE', 'Blocage', 1),
(93503, 9350, 'CLOTURE', 'Cloture', 1),
(93700, 9370, 'VISU', 'Visualisation', 1),
(93800, 9380, 'VISU', 'Visualisation', 1),
(93900, 9390, 'VISU', 'Visualisation', 1),

-- REPORTING
(100000, 10000 , 'PAGE', 'Accès page - Reporting', 0),
(100040, 10004, 'PAGE', 'Accés page indicateur', 0),

-- INVENDU
(100002, 10003 , 'PAGE', 'Accès page', 0);


-- Constraints for dumped tables
--

--
-- Constraints for table `page_element`
--
ALTER TABLE `page_element`
  ADD CONSTRAINT `FK_BB20B5AC21432107` FOREIGN KEY (`pag_id`) REFERENCES `page` (`ID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET FOREIGN_KEY_CHECKS = 1;
