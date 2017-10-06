SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `fic_etat`;

CREATE TABLE IF NOT EXISTS `fic_etat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL,
  `libelle` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `poids_erreur` int(11) NOT NULL,
  `couleur` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2D2572F377153098` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `fic_etat` (`id`, `code`, `libelle`, `poids_erreur`, `couleur`) VALUES
(1, 0, 'OK', 0, '#F2F2F2'),
(2, 99, 'A traiter de nouveau', 0, '#31B404'),
(3, 50, 'Plusieurs dates (parution ou distribution) trouvees', 5, '#5882FA'),
(4, 51, 'Fichier vide', 5, '#ff0000'),
(5, 52, 'Plusieurs sociétés dans le même fichier', 9, '#ff0000'),
(6, 53, '"Code société" vide	', 10, '#FE642E'),
(7, 54, 'Société inconnue (non paramétrée)', 10, '#B43104'),
(8, 55, 'Plusieurs "societe ID (sens M-ROAD)" trouvées dans le même fichier', 9, '#FF8000'),
(9, 70, 'Traitement arrêté : Problème de requête', 8, '#B43104'),
(10, 80, 'Traitement arrêté : Problème de RNVP', 5, '#B43104'),
(11, 81, 'Traitement arrêté : Problème de Webservice Géocodage', 5, '#B43104');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;