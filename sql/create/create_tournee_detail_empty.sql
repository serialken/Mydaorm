/*Table structure for table `tournee_detail` */

DROP TABLE IF EXISTS `tournee_detail`;

CREATE TABLE `tournee_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordre` int(11) NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `duree_conduite` time NOT NULL,
  `heure_debut` time NULL,
  `duree` time NULL,
  `etat` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `distance_trajet` double NULL,
  `trajet_cumule` double NULL,
  `debut_plage_horaire` time NULL,
  `fin_plage_horaire` time NULL,
  `duree_viste_fixe` time NOT NULL,
  `exclure_ressource` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `assigner_ressource` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `a_traiter` tinyint(1) NOT NULL,
  `modele_tournee_jour_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `num_abonne_soc` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `num_abonne_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
