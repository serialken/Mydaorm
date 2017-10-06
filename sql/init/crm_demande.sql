SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `crm_demande`
--

DROP TABLE IF EXISTS `crm_demande`;

CREATE TABLE IF NOT EXISTS `crm_demande` (
  `id` int(11) NOT NULL,
  `crm_categorie_id` int(11) DEFAULT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `code_type_demande` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CADF9B3280152569` (`crm_categorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `crm_demande`
--

INSERT INTO `crm_demande` (`id`, `crm_categorie_id`, `code`, `libelle`, `code_type_demande`) VALUES
(101, 1, '1', 'Client non démarré', 'RC01'),
(102, 1, '2', 'Retard de livraison', 'RC02'),
(103, 1, '3', 'Client démarré avec retard', 'RC03'),
(120, 1, '20', 'Livraison irrégulière', 'DC'),
(121, 1, '21', 'Consigne de dépôt non respectée', 'DC'),
(122, 1, '22', 'Etiquette mal collée', 'DC'),
(123, 1, '23', 'Etiquette non collée', 'DC'),
(127, 1, '27', 'Non livraison ce jour', 'RC05'),
(128, 1, '28', 'Non livraison 2 à 6 jours', 'RC06'),
(129, 1, '29', 'Non livraison jours antérieurs', 'RC'),
(130, 1, '30', 'Livraison à tort', 'RC'),
(131, 1, '31', 'Non livraison 7 jours et plus', 'RC'),
(132, 1, '32', 'Bruit porteur', 'RC'),
(133, 1, '33', 'Vol', 'RC'),
(135, 1, '35', 'Produit détérioré', 'RC'),
(138, 1, '38', 'Quantité non respectée', 'RC'),
(141, 1, '41', 'Supplément non livré', 'RC'),
(174, 1, '74', 'Livraison d''un autre titre', 'RC'),
(175, 1, '75', 'Réassort non effectué', 'RC'),
(176, 1, '76', 'Demande client prioritaire', 'DC'),
(177, 1, '77', 'Livraison le lendemain', 'RC'),
(251, 2, '51', 'Retard tournée, panne', 'RI'),
(252, 2, '52', 'Distribution hors délai', 'RI'),
(258, 2, '58', 'Code défectueux', 'RI'),
(259, 2, '59', 'BAL absente sans nom', 'RI'),
(260, 2, '60', 'BAL pleine ou cassée', 'RI'),
(261, 2, '61', 'Adresse à confirmer', 'RI'),
(269, 2, '69', 'Sécurité-Vol / Produit détérioré', 'RI'),
(272, 2, '72', 'Problème d''accès', 'RI'),
(273, 2, '73', 'Hors zone de portage', 'RI'),
(277, 2, '77', 'Jnal livré à l''accueil ou gardien', 'RI'),
(278, 2, '78', 'Erreur Dispatch / Client Non Livré', 'RI'),
(308, 3, '8', 'Information', 'DC');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `crm_demande`
--
ALTER TABLE `crm_demande`
  ADD CONSTRAINT `FK_CADF9B3280152569` FOREIGN KEY (`crm_categorie_id`) REFERENCES `crm_categorie` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;