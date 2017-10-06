-- phpMyAdmin SQL Dump
-- version 4.1.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 28, 2014 at 04:00 PM
-- Server version: 5.5.38-0+wheezy1
-- PHP Version: 5.4.4-14+deb7u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
 SET NAMES default;

--
-- Database: `dev_mroad`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorie`
--
SET foreign_key_checks = 0;
DROP TABLE IF EXISTS `categorie`;

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `ID` int(11) NOT NULL,
  `LIBELLE` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `PAGE_DEFAUT` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CLASS_IMAGE` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `categorie`
--

INSERT INTO `categorie` (`ID`, `LIBELLE`, `PAGE_DEFAUT`, `CLASS_IMAGE`) VALUES
(1, 'Administration', '_ams_accueil_administration', 'administration'),
(2, 'Adresses', '_ams_accueil_administration', 'adresses'),
(3, 'Produits', '_ams_accueil_administration', 'produits'),
(5, 'Distribution', '_ams_accueil_administration', 'distribution'),
(50, 'PCO', '_ams_accueil_administration', 'PCO'),
(60, 'Gestion des porteurs', '_ams_accueil_administration', 'porteurs'),
(70, 'Cartographie', '_ams_accueil_administration', 'cartographie'),
(80, 'CRM', '_ams_accueil_administration', 'distributioncrm'),
(90, 'Paie', '_ams_accueil_administration', 'porteurspaie'),
(100,'Reporting','_ams_accueil_administration','reporting'),
(200,'Hors Presse','_ams_accueil_administration','administrationhorspresse');

SET foreign_key_checks = 1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;