SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `fic_format_enregistrement`;

CREATE TABLE IF NOT EXISTS `fic_format_enregistrement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fic_code` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `attribut` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `col_debut` int(11) DEFAULT NULL,
  `col_long` int(11) DEFAULT NULL,
  `col_val` int(11) DEFAULT NULL,
  `col_val_rplct` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `col_desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fic_code_attribut` (`fic_code`,`attribut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `fic_format_enregistrement` (`id`, `fic_code`, `attribut`, `col_debut`, `col_long`, `col_val`, `col_val_rplct`, `col_desc`) VALUES
(1, 'JADE_CAS', 'CP', NULL, NULL, 9, NULL, 'Code Postal'),
(2, 'JADE_CAS', 'DATE_DISTRIB', NULL, NULL, 2, NULL, 'Par defaut, c''est la date de parution'),
(3, 'JADE_CAS', 'DATE_PARUTION', NULL, NULL, 2, NULL, 'Date de parution'),
(4, 'JADE_CAS', 'DIVERS1', NULL, NULL, 16, NULL, 'Divers 1'),
(5, 'JADE_CAS', 'DIVERS2', NULL, NULL, 19, NULL, 'Message porteur'),
(7, 'JADE_CAS', 'INFO_COMP1', NULL, NULL, 17, NULL, 'Consigne porteur'),
(8, 'JADE_CAS', 'INFO_COMP2', NULL, NULL, 18, NULL, 'Digicode'),
(9, 'JADE_CAS', 'NUMABO_EXT', NULL, NULL, 3, NULL, 'Numero abonne Exterieur'),
(10, 'JADE_CAS', 'NUM_PARUTION', NULL, NULL, 1, NULL, 'Numero de parution'),
(11, 'JADE_CAS', 'PRD_CODE_EXT', NULL, NULL, 13, NULL, 'Code produit (code titre si clients a servir venant de JADE)'),
(12, 'JADE_CAS', 'QTE', NULL, NULL, 15, NULL, 'Quantite (Nombre d''exemplaires)'),
(13, 'JADE_CAS', 'SOC_CODE_EXT', NULL, NULL, 12, NULL, 'Code societe Exterieur'),
(14, 'JADE_CAS', 'SPR_CODE_EXT', NULL, NULL, 14, 'IF(TRIM(@COL_14)='''',''001'',TRIM(@COL_14))', 'Sous produit (code edition si clients a servir venant de JADE)'),
(15, 'JADE_CAS', 'TYPE_PORTAGE', NULL, NULL, 11, NULL, 'Type portage'),
(16, 'JADE_CAS', 'VILLE', NULL, NULL, 10, NULL, 'Ville'),
(17, 'JADE_CAS', 'VOL1', NULL, NULL, 4, NULL, 'Adresse Exterieure - Volet 1'),
(18, 'JADE_CAS', 'VOL2', NULL, NULL, 5, NULL, 'Adresse Exterieure - Volet 2'),
(19, 'JADE_CAS', 'VOL3', NULL, NULL, 6, NULL, 'Adresse Exterieure - Volet 3'),
(20, 'JADE_CAS', 'VOL4', NULL, NULL, 7, NULL, 'Adresse Exterieure - Volet 4'),
(21, 'JADE_CAS', 'VOL5', NULL, NULL, 8, NULL, 'Adresse Exterieure - Volet 5');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;