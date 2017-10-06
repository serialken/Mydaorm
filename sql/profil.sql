-- phpMyAdmin SQL Dump
-- version 4.1.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 28, 2014 at 04:24 PM
-- Server version: 5.5.38-0+wheezy1
-- PHP Version: 5.4.4-14+deb7u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dev_mroad`
--

-- --------------------------------------------------------

--
-- Table structure for table `profil`
--

CREATE TABLE IF NOT EXISTS `profil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E6D6B29777153098` (`code`),
  UNIQUE KEY `UNIQ_E6D6B297A4D60759` (`libelle`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

--
-- Dumping data for table `profil`
--

INSERT INTO `profil` (`id`, `code`, `libelle`) VALUES
(1, 'INFO_ADMIN', 'Informatique - Admin'),
(2, 'INFO_CONSULT1000', 'Informatique - Consultation'),
(3, 'INFO_EXPLOIT', 'Informatique - Exploitation'),
(4, 'LOG_ADMIN', 'Logistique - Admin'),
(5, 'LOG_PORTEUR', 'Logistique - Porteur'),
(6, 'LOG_REGION', 'Logistique - Région'),
(7, 'Rh Paye', 'RH Paye');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
