SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `parametre`;

CREATE TABLE IF NOT EXISTS `parametre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attr` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `valeur` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_ACC7904147E2314` (`attr`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `parametre` (`id`, `attr`, `valeur`, `description`) VALUES
(1, 'DATE_FIN', '31/12/2078', ''),
(2, 'DUREE_SESSION', '3600', ''),
(3, 'ELT_DROIT_DEFAUT', 'TOUT', ''),
(4, 'FTP_BKP', 'Bkp', 'Sous repertoire de Backup du cote FTP'),
(5, 'LN', '\\n', 'Fin de ligne'),
(6, 'LN_WIN', '\\r\\n', 'Fin de ligne - Windows'),
(7, 'MSG_CHOIX_SELECTEUR', 'Choisissez ...', ''),
(8, 'NB_RES_PAR_PAGE_DEFAUT', '10,20,50', ''),
(9, 'NB_RES_PAR_PAGE_DEPOTS', '2,5,10', ''),
(10, 'NB_RES_PAR_PAGE_UTL', '5', ''),
(11, 'NOM_APPLI', 'M-ROAD', ''),
(12, 'P_ACCUEIL_DEFAUT', '_ams_accueil_defaut', 'Route par defaut comme page d''accueil'),
(13, 'REP_FICHIERS_BKP', 'Bkp', 'Sous repertoire des fichiers de sauvegarde'),
(14, 'REP_FICHIERS_CMD', 'C:\\\\wamp\\\\www\\\\MRoad_Fichiers', 'Repertoire parent par defaut de destination des fichiers si script lance via une ligne de commande'),
(15, 'REP_FICHIERS_TMP', 'Tmp', 'Sous repertoire des fichiers temporaires'),
(16, 'REP_FICHIERS_WEB', '../../MRoad_Fichiers', 'Repertoire parent par defaut de destination des fichiers si script lance via le web'),
(17, 'REP_LOGS', 'Logs', 'Sous repertoire des fichiers de logs'),
(18, 'REP_LOGS_ERR', 'Logs/Err', 'Sous repertoire des fichiers de logs d''erreurs'),
(19, 'RNVP_LOGIN_WSDL', 'SDVP', 'RNVP Login'),
(20, 'RNVP_MDP_WSDL', '76310SDVP', 'RNVP Mot de passe'),
(21, 'RNVP_SERVEUR', 'http://py-ch-prdrnvp.adonis.mediapole.info:80/rnvp/service.asmx?WSDL', 'RNVP Adresses webservices'),
(22, 'SEPARATEUR_ELTS_FORM', ';', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;