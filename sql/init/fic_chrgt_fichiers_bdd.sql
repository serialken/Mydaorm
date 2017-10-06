SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `fic_chrgt_fichiers_bdd`; 

CREATE TABLE IF NOT EXISTS `fic_chrgt_fichiers_bdd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fic_ftp` int(11) NOT NULL,
  `fic_source` int(11) NOT NULL,
  `fic_code` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `regex_fic` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `format_fic` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nb_lignes_ignorees` int(11) NOT NULL,
  `separateur` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trim_val` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nb_col` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_54545360FFAB509` (`fic_code`),
  KEY `IDX_5454536040019006` (`fic_ftp`),
  KEY `IDX_54545360AA02CB68` (`fic_source`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `fic_chrgt_fichiers_bdd` (`id`, `fic_ftp`, `fic_source`, `fic_code`, `regex_fic`, `format_fic`, `nb_lignes_ignorees`, `separateur`, `trim_val`, `nb_col`) VALUES
(1, 1, 1, 'JADE_CAS', '/^(\\.\\/)?[A-Z0-9]{2}`date_Ymd_1_10`\\.txt$/i', 'CSV', 0, '|', '1', 25);

ALTER TABLE `fic_chrgt_fichiers_bdd`
  ADD CONSTRAINT `FK_54545360AA02CB68` FOREIGN KEY (`fic_source`) REFERENCES `fic_source` (`id`),
  ADD CONSTRAINT `FK_5454536040019006` FOREIGN KEY (`fic_ftp`) REFERENCES `fic_ftp` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;