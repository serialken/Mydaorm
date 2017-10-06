SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `adresse_rnvp_etat`;

CREATE TABLE IF NOT EXISTS `adresse_rnvp_etat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qualite` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1A6D431677153098` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `adresse_rnvp_etat` (`id`, `qualite`, `libelle`, `code`) VALUES
(1, 'OK', 'Normalisation automatique OK', 'RNVP_OK'),
(2, 'OK', 'Saisie OK', 'SAISIE_OK'),
(3, 'A VERIFIER', 'Ville partiellement référencée ou voie inconnue', 'RNVP_INFO_VILLE_VOIE_INCOMPLET'),
(4, 'A VERIFIER', 'Adresse normalisée mais avec risque d''erreur', 'RNVP_AVEC_RISQUE'),
(5, 'KO', 'Rejetée', 'RNVP_KO');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;