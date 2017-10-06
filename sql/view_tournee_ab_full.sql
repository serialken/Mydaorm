DROP VIEW IF EXISTS `tournee_abo_full`;
CREATE VIEW `tournee_abo_full` AS 
SELECT
  `td`.`id`                       AS `td_id`,
  `td`.`modele_tournee_jour_code` AS `code_modele_tournee`,
  `td`.`num_abonne_soc`           AS `num_abonne`,
  `td`.`latitude`                 AS `td_latitude`,
  `td`.`longitude`                AS `td_longitude`,
  `td`.`heure_debut`              AS `heure_debut`,
  `td`.`duree`                    AS `duree`,
  `td`.`duree_conduite`           AS `duree_conduite`,
  `td`.`etat`                     AS `etat`,
  `td`.`distance_trajet`          AS `distance_trajet`,
  `td`.`trajet_cumule`            AS `trajet_cumule`,
  `td`.`debut_plage_horaire`      AS `debut_plage_horaire`,
  `td`.`fin_plage_horaire`        AS `fin_plage_horaire`,
  `td`.`duree_viste_fixe`         AS `duree_viste_fixe`,
  `td`.`exclure_ressource`        AS `exclure_ressource`,
  `td`.`assigner_ressource`       AS `assigner_ressource`,
  `td`.`a_traiter`                AS `a_traiter`,
  `td`.`nb_stop`                  AS `nb_stop`,
  `td`.`ordre_stop`               AS `ordre_stop`,
  `td`.`temps_conduite`           AS `drive_time`,
  `td`.`temps_visite`             AS `temps_visite`,
  `td`.`temps_tournee`            AS `tournee_time`,
  `td`.`trajet_total_tournee`     AS `trajet_total`,
  `td`.`point_livraison_id`       AS `td_point_livraison_id`,
  `casl`.`id`                     AS `casl_id`,
  `casl`.`point_livraison_ordre`  AS `ordre`,
  `casl`.`date_parution`          AS `casl_date_parution`,
  `casl`.`abonne_soc_id`          AS `abonne_soc_id`,
  `casl`.`abonne_unique_id`       AS `abonne_unique_id`,
  `casl`.`point_livraison_id`     AS `point_livraison_id`,
  `casl`.`depot_id`               AS `depot_id`,
  `casl`.`societe_id`             AS `societe_id`,
  `casl`.`produit_id`             AS `produit_id`,
  `casl`.`date_parution`          AS `date_parution`,
  `casl`.`type_portage`           AS `type_portage`,
  `casl`.`qte`                    AS `qte`,
  `casl`.`client_type`            AS `client_type`,
  `casl`.`type_service`           AS `type_service`,
  `asoc`.`id`                     AS `asoc_id`,
  `asoc`.`numabo_ext`             AS `numabo_ext`,
  `asoc`.`vol1`                   AS `vol1`,
  `asoc`.`vol2`                   AS `vol2`,
  `arnvp`.`id`                    AS `arnvp_id`,
  `arnvp`.`cadrs`                 AS `cadrs`,
  `arnvp`.`adresse`               AS `a_adresse`,
  `arnvp`.`lieudit`               AS `lieudit`,
  `arnvp`.`cp`                    AS `cp`,
  `arnvp`.`ville`                 AS `ville`,
  `arnvp`.`insee`                 AS `insee`,
  `arnvp`.`geoy`                  AS `latitude`,
  `arnvp`.`geox`                  AS `longitude`,   
  `d`.`id`                        AS `d_id`,
  `d`.`code`                      AS `code`,
  `d`.`libelle`                   AS `libelle`,
  `d`.`adresse`                   AS `d_adresse`,
  `mtj`.`id`                      AS `mtj_id`,
  `mtj`.`code`                    AS `mtj_code_tournee_jour`,
  `mtj`.`jour_id`                 AS `jour_id`,
  `mtj`.`employe_id`              AS `employe_id`,
  `mtj`.`transport_id`            AS `transport_id`
FROM (((((`client_a_servir_logist` `casl`
       JOIN `tournee_detail` `td`)
      JOIN `abonne_soc` `asoc`)
     JOIN `adresse_rnvp` `arnvp`)
    JOIN `depot` `d`)
   JOIN `modele_tournee_jour` `mtj`)
WHERE ((`td`.`num_abonne_id` = `asoc`.`id`)
       AND (`casl`.`abonne_soc_id` = `td`.`num_abonne_id`)
       AND (`casl`.`abonne_soc_id` = `asoc`.`id`)
       AND (`arnvp`.`id` = `casl`.`point_livraison_id`)
       AND (`d`.`id` = `casl`.`depot_id`)
       AND (`mtj`.`code` = `td`.`modele_tournee_jour_code`)
       AND (`mtj`.`jour_id` = (CAST(DATE_FORMAT(`casl`.`date_distrib`,'%w')AS SIGNED) + 1))
       AND (`mtj`.`id` = `casl`.`tournee_jour_id`));