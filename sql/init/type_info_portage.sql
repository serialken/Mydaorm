-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Ven 16 Mai 2014 à 12:23
-- Version du serveur: 5.6.12-log
-- Version de PHP: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `mroad`
--

-- --------------------------------------------------------

DROP TABLE IF EXISTS `type_info_portage`;

--
-- Structure de la table `type_info_portage`
--

CREATE TABLE IF NOT EXISTS `type_info_portage` (
`id` int(11) NOT NULL,
  `libelle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `type_info_portage`
--

INSERT INTO `type_info_portage` (`id`, `libelle`, `code`, `active`) VALUES
(1, 'Divers 1', 'DIVERS1', 1),
(2, 'Info1', 'INFO_COMP1', 1),
(3, 'Info2', 'INFO_COMP2', 1),
(4, 'Divers 2', 'DIVERS2', 1),
(5, 'Accès', 'ACCES', 1),
(6, 'Info3', 'INFO_COMP3', 1),
(7, 'Info_DCS', 'INFO_DCS', 1),
(8, 'Info_Neo_Adr', 'INFO_NEO_1', 1),
(9, 'INFO_CENTRE', 'INFO_CENTR', 1),
(10, 'Info Reperage', 'INFO_REPER', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;
