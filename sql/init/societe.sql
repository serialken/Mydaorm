SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET foreign_key_checks = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `societe`;

CREATE TABLE IF NOT EXISTS `societe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_defaut_id` int(11) DEFAULT NULL,
  `utilisateur_modif` int(11) DEFAULT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `date_modif` datetime NOT NULL,
  `image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_19653DBD77153098` (`code`),
  UNIQUE KEY `UNIQ_19653DBD9CBBD543` (`produit_defaut_id`),
  KEY `IDX_19653DBD5D308C15` (`utilisateur_modif`),
  KEY `IDX_19653DBD3DA5256D` (`image_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=74 ;

--
-- Contenu de la table `societe`
--

INSERT INTO `societe` (`id`, `produit_defaut_id`, `utilisateur_modif`, `code`, `libelle`, `date_debut`, `date_fin`, `date_modif`, `image_id`) VALUES
(1, NULL, NULL, '68', 'Libération', '2014-03-01', '2078-12-31', '2014-01-01 00:00:00', 30),
(2, 8, NULL, 'FI', 'Figaro', '2014-03-01', '2078-12-31', '2014-01-01 00:00:00', 34),
(3, NULL, NULL, 'LM', 'Le Monde', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 45),
(4, NULL, NULL, 'AF', 'Aujourd Hui En France', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 28),
(5, NULL, NULL, 'PL', 'L Equipe', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 50),
(6, NULL, NULL, 'EC', 'Les Echos', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 31),
(7, NULL, NULL, 'HT', 'International Herald Tribune', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 62),
(8, NULL, NULL, 'FF', 'France Football', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 33),
(9, NULL, NULL, 'PT', 'Paris Turf', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 62),
(10, NULL, NULL, 'HU', 'L''humanité', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 37),
(14, NULL, NULL, 'GD', 'Gazette De L''hotel Drouot', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 37),
(15, NULL, NULL, 'JD', 'Journal Du Dimanche', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 42),
(16, NULL, NULL, 'PN', 'La Parisienne', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 29),
(17, NULL, NULL, 'MO', 'Midi Olympique', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 46),
(18, NULL, NULL, 'EL', 'Elle', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 32),
(19, NULL, NULL, 'LC', 'La Croix', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 43),
(20, NULL, NULL, 'T7', 'Télé 7 Jours', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 65),
(21, NULL, NULL, 'GY', 'Geny Courses', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 38),
(22, NULL, NULL, 'GA', 'Gala', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 35),
(23, NULL, NULL, 'PM', 'Paris Match', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 61),
(24, NULL, NULL, 'OB', 'Le Nouvel Observateur', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 48),
(25, NULL, NULL, 'RE', 'La République De Seine Et Marne', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 63),
(26, NULL, NULL, 'MQ', '(M) Le Monde Am', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 65),
(27, NULL, NULL, 'OP', 'L Opinion', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 49),
(28, NULL, NULL, 'LG', 'Lm Gc Nuit', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 44),
(29, NULL, NULL, 'LP', 'Le Parisien', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 65),
(30, NULL, NULL, 'VM', 'Velo Mag', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 65),
(31, NULL, NULL, 'AMC', '(M) Architecture Mouvement Continuité', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 65),
(32, NULL, NULL, 'BB', '(M) Bonhomme en Bois', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 65),
(33, NULL, NULL, 'BOR', '(M) Borsen', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 65),
(34, NULL, NULL, 'BW', '(M) Businessweek', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(35, NULL, NULL, 'CAP', '(M) Capital', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(36, NULL, NULL, 'CLO', '(M) Closer', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(37, NULL, NULL, 'DI', '(M) Dagens Industri', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(38, NULL, NULL, 'DF', '(M) Détours en France', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(39, NULL, NULL, 'LL', '(M) Elle', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(40, NULL, NULL, 'GALA', '(M) Gala', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(41, NULL, NULL, 'GRA', '(M) Grazia', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(42, NULL, NULL, 'HN', '(M) HallandsPosten', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(43, NULL, NULL, 'IP', '(M) Intermédia le plus', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(44, NULL, NULL, 'JAL', '(M) Jalouse', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(45, NULL, NULL, 'JP', '(M) Jyllands Posten', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(46, NULL, NULL, 'EXP', '(M) L''Expansion', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(47, NULL, NULL, 'OPT', '(M) L''Optimum', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(48, NULL, NULL, 'GCO', '(M) La gazette des communes', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(49, NULL, NULL, 'MON', '(M) Le moniteur', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(50, NULL, NULL, 'RL', '(M) Le Républicain Lorrain', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(51, NULL, NULL, 'METRO', '(M) Metro école', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(52, NULL, NULL, 'ORG', '(M) Orange', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(53, NULL, NULL, 'PRLX', '(M) Paris de luxe', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(54, NULL, NULL, 'PRM', '(M) Paris Match', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(55, NULL, NULL, 'POL', '(M) Politiken', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(56, NULL, NULL, '1ERE', '(M) Première', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(57, NULL, NULL, 'VRS', '(M) Festival "Versailles"', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', 65),
(58, NULL, NULL, 'RM', '(M) Revue des montres', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(59, NULL, NULL, 'SM', '(M) Santé magazine', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(60, NULL, NULL, 'ST', '(M) Stylist', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(61, NULL, NULL, 'SVD', '(M) Svenska dagbladet', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(62, NULL, NULL, 'TV2', '(M) Télé 2 Semaines', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(63, NULL, NULL, 'TV7', '(M) Télé 7 Jours', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(64, NULL, NULL, 'TVL', '(M) Télé Loisirs', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(65, NULL, NULL, 'TVS', '(M) Télé Star', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(66, NULL, NULL, 'WA', '(M) Wenkendaviser', '2014-01-01', '2078-12-31', '2014-01-01 00:00:00', NULL),
(67, NULL, NULL, 'LCR', '(M) La croix', '2014-07-10', NULL, '2014-07-10 11:00:11', 43),
(68, NULL, NULL, 'LIB', '(M) Libération', '2014-07-10', NULL, '2014-07-10 11:15:03', 30),
(69, NULL, NULL, 'TELE2S', '(M) Télé 2 semaines', '2014-07-10', NULL, '2014-07-10 11:17:16', 65),
(70, NULL, NULL, 'BUQ', '(M) Bulletin Quotidien', '2014-07-10', NULL, '2014-07-10 11:23:28', 65),
(71, NULL, NULL, 'CPR', '(M) Correspondance de la Presse', '2014-07-10', NULL, '2014-07-10 11:25:22', 65),
(72, NULL, NULL, 'CPU', '(M) Correspondance de la Publicité', '2014-07-10', NULL, '2014-07-10 11:26:33', 65),
(73, NULL, NULL, 'CEC', '(M) Correspondance Economique', '2014-07-10', NULL, '2014-07-10 11:27:39', 65);

ALTER TABLE `societe`
  ADD CONSTRAINT `FK_19653DBD3DA5256D` FOREIGN KEY (`image_id`) REFERENCES `fichier` (`id`),
  ADD CONSTRAINT `FK_19653DBD5D308C15` FOREIGN KEY (`utilisateur_modif`) REFERENCES `utilisateur` (`id`),
  ADD CONSTRAINT `FK_19653DBD9CBBD543` FOREIGN KEY (`produit_defaut_id`) REFERENCES `produit` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;

