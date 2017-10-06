SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `categorie`;

CREATE TABLE IF NOT EXISTS `categorie` (
  `ID` int(11) NOT NULL,
  `LIBELLE` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `PAGE_DEFAUT` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CLASS_IMAGE` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `categorie` (`id`, `libelle`, `page_defaut`, `class_image`) VALUES
(1, 'Administration', '_ams_accueil_administration', 'administration'),
(2, 'Adresses', '_ams_accueil_administration', 'adresses'),
(3, 'Produits', '_ams_accueil_administration', 'produits'),
(5, 'Distribution', '_ams_accueil_administration', 'distribution'),
(60, 'Gestion des porteurs', '_ams_accueil_administration', 'porteurs'),
(70, 'Cartographie', '_ams_accueil_administration', 'cartographie')
;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;