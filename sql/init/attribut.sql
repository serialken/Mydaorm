SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `attribut`;

CREATE TABLE IF NOT EXISTS `attribut` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7AB8E85D77153098` (`code`),
  UNIQUE KEY `UNIQ_7AB8E85DA4D60759` (`libelle`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `attribut` (`id`, `code`, `libelle`) VALUES
(1, 'CLI_A_SRV_DIVERS1', 'Client à servir - Divers 1'),
(2, 'CLI_A_SRV_INFO_COMP1', 'Client à servir - Infos. Compl. 1'),
(3, 'CLI_A_SRV_INFO_COMP2', 'Client à servir - Infos. Compl. 2'),
(4, 'CLI_A_SRV_INFO_DIVERS2', 'Client à servir - Divers 2');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;