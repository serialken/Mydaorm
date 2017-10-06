SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `produit_type`;

CREATE TABLE IF NOT EXISTS `produit_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

--
-- Dumping data for table `produit_type`
--

INSERT INTO `produit_type` (`id`, `libelle`) VALUES
(1, 'Presse - Titre principal'),
(2, 'Presse - Supplément indépendant'),
(3, 'Presse - Supplément lié au titre'),
(4, 'Prospectus'),
(5, 'Bagues et Bandeaux'),
(6, 'Ouvrages sous enveloppe (type DDB)'),
(7, 'Produit type "CANAL+"');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;