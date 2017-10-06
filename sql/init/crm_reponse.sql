SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `crm_reponse`
--

DROP TABLE IF EXISTS `crm_reponse`;

CREATE TABLE IF NOT EXISTS `crm_reponse` (
  `id` int(11) NOT NULL,
  `crm_categorie_id` int(11) DEFAULT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B3FD925080152569` (`crm_categorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `crm_reponse`
--

INSERT INTO `crm_reponse` (`id`, `crm_categorie_id`, `code`, `libelle`) VALUES
(102, 1, '2', 'Retard imprimerie /gréve'),
(104, 1, '4', 'Adresse incomplète'),
(106, 1, '6', 'Problème d''accès'),
(108, 1, '8', 'Problème listing informatique'),
(110, 1, '10', 'Porteur ne suit pas les infos client'),
(111, 1, '11', 'Erreur Dispatch'),
(112, 1, '12', 'Risque de vol'),
(113, 1, '13', 'Dépot chez le gardien'),
(114, 1, '14', 'Nouveau porteur'),
(115, 1, '15', 'Erreur Remplaçant'),
(116, 1, '16', 'Erreur boite'),
(117, 1, '17', 'Retard tournée / panne'),
(118, 1, '18', 'Qte Insuffisante / Réassort Impossible'),
(119, 1, '19', 'Observation'),
(120, 1, '20', 'BAL pleine ou cassée'),
(121, 1, '21',  'Problème consigne dépôt'),
(201, 2, '1', 'Abonné transmet un badge'),
(202, 2, '2', 'Abonné transmet une clé'),
(203, 2, '3', 'Abonné transmet un code'),
(204, 2, '4', 'Abonné transmet N° BAL'),
(205, 2, '5', 'Abonné transmet nom sur BAL'),
(206, 2, '6', 'Abonné transmet plusieurs infos'),
(207, 2, '7', 'Refus client préfère poste'),
(208, 2, '8', 'Refus client mauvaise réception'),
(209, 2, '9', 'Refus transmettre Badge / Code'),
(210, 2, '10', 'Refus client divers');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `crm_reponse`
--
ALTER TABLE `crm_reponse`
  ADD CONSTRAINT `FK_B3FD925080152569` FOREIGN KEY (`crm_categorie_id`) REFERENCES `crm_categorie` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;