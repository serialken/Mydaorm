-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Lun 19 Mai 2014 à 09:46
-- Version du serveur: 5.6.12-log
-- Version de PHP: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `fichier`;

--
-- Base de données: `silog`
--

-- --------------------------------------------------------

--
-- Structure de la table `fichier`
--

CREATE TABLE IF NOT EXISTS `fichier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=65 ;

--
-- Contenu de la table `fichier`
--

INSERT INTO `fichier` (`id`, `name`, `path`) VALUES
(0, 'par défaut', '356be46f5a9ce0215f5434d4a75c918962aecd3b.png'),
(28, 'AF', '5c36f001ced789eff0b5ebbf813670ca670b19b1.png'),
(29, 'La parisienne', '67018c77467c165510b54d679ce3125dbcb34a2b.png'),
(30, 'Liberation', '0330cc31457371414165da7b43f1967ff8dbcf71.png'),
(31, 'Les echos', 'b3c6e5650206d26375068341147b55aaed45cae6.png'),
(32, 'Elle', '6a0c1526332b4fec0af52fef5e4108423fb1cbea.png'),
(33, 'France Football', '8c3ec0d5761b5e9b816ee034b61fc616a052488b.png'),
(34, 'Le Figaro', '668e80cc2343652540948affb4a81c3d18c181fa.png'),
(35, 'Gala', 'a46394fe93a3c0666aedd30bc5aae09088b61bf7.png'),
(37, 'La Gazette Drouot', '26c81cf44e94112bdcab02d6c0ff6d70d29e2e22.png'),
(38, 'GENY Courses', '59f6b509dbed18a7d933134b285d38004c71e042.png'),
(39, 'Hebdo', 'b517d680434e93a0589aec84475f4d0bf61890d2.png'),
(40, 'Herald Tribune', '94e3b559275408eaab12e1e19fcf612c7960387c.png'),
(41, 'L''Humanité', '99dfc3ab9157354d397faf7fac60c4f99cd2e787.png'),
(42, 'Le Journal du dimanche', 'a4dfab786e8d54402dd8d7cc48ae39c354a7c56a.png'),
(43, 'La Croix', 'f456b50933bfbc9b5d9c7ce6720f6bbc1ad2a932.png'),
(44, 'Le Monde G.C.', 'd128412db246d98dcc078672f8cd11c4e2f49882.png'),
(45, 'Le Monde', 'd8f729aeade5dc8e534a4c1824b744ec8e5e6286.png'),
(46, 'Midi Olympique', '544cac6beb7869a62f4b79d2e2cc6a85eb4a8c01.png'),
(47, 'Le Monde APM', '8283d1d8e8c51f7858f07df53aa9aab7bac21135.png'),
(48, 'Le Nouvel Observateur', '7fadee9b63b8c9b0c9457a14047f321a1ab76b6d.png'),
(49, 'L''Opinion', '904a2081fc62c06acd35fbbd5610e7a23cffb432.png'),
(50, 'L''équipe', '67bfb1b0339ed201cb26c932603d8c8acceb7b86.png'),
(61, 'Paris Match', 'df7adb72d8dbe70b9bb84c2b2d7dba024d318d8f.png'),
(62, 'Paris Turf', '296cb6434b26b7a611539598bf8c5e6972ce6741.png'),
(63, 'République', '2006f81291bd96ce6cee4d76b0e9188c2bad0ea6.png');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
SET foreign_key_checks = 1;