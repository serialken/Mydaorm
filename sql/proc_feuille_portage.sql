
DROP PROCEDURE IF EXISTS feuille_portage;
DELIMITER |
CREATE  PROCEDURE `feuille_portage`(
	IN _depot_code     VARCHAR(10),
        IN _flux_id        INT,
	IN _date_distrib   DATE
)
BEGIN
INSERT INTO feuille_portage_tmp
    SELECT 
    NULL,
    d.id as depot_id,
    d.code as depot_code,
    d.libelle as depot_libelle,
    casl.vol1,
    casl.vol2,
    casl.qte,
    casl.flux_id as flux,
    p.id as produit_id,
    p.code as produit_code,
    p.libelle as produit_libelle,
    mtj.id as tournee_jour_id,
    mtj.code as tournee_jour_code,
    e.nom as nom_porteur,
    e.prenom1 as prenom_porteur,
    arnvp.adresse,
    arnvp.cp,
    arnvp.ville,
    asoc.numabo_ext as num_abonne,
    s.code as societe_code,
    f.path,
    casl.point_livraison_id,
    casl.point_livraison_ordre,
    asoc.date_service_1,
    asoc.date_stop,
    GROUP_CONCAT(DISTINCT info_p.valeur
        SEPARATOR ' -- ') AS valeur,
    (SELECT 
            GROUP_CONCAT(DISTINCT valeur SEPARATOR ' -- ')
        FROM
            info_portage info
        JOIN infos_portages_livraisons ipl ON ipl.info_portage_id = info.id
        WHERE
            ipl.livraison_id = casl.point_livraison_id
        AND info.date_debut <= now() + 1
        AND info.date_fin > now()
         AND info.active = 1
  ) as info_portage_livraison,
    CONCAT(adr.vol3,adr.vol5) as vol3 
FROM
    client_a_servir_logist casl
        JOIN 
    adresse adr ON adr.id = casl.adresse_id
        LEFT JOIN
    infos_portages_livraisons info_p_l ON info_p_l.livraison_id = casl.point_livraison_id
        LEFT JOIN
    infos_portages_abonnes info_p_a ON info_p_a.abonne_id = casl.abonne_soc_id
        LEFT JOIN
    info_portage info_p ON info_p.id = info_p_a.info_portage_id
        AND info_p.date_debut <= now() + 1
        AND info_p.date_fin > now()
        AND info_p.active =1
        LEFT JOIN
    depot d ON d.id = casl.depot_id
        LEFT JOIN
    produit p ON p.id = casl.produit_id
        LEFT JOIN
    modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id
        LEFT JOIN
    modele_tournee mt ON mtj.tournee_id = mt.id
        LEFT JOIN
    employe e ON e.id = mtj.employe_id
        LEFT JOIN
    adresse_rnvp arnvp ON arnvp.id = casl.rnvp_id
        LEFT JOIN
    abonne_soc asoc ON asoc.id = casl.abonne_soc_id
        LEFT JOIN
    societe s ON s.id = casl.societe_id
        LEFT JOIN
    fichier f ON f.id = p.image_id
WHERE
    casl.tournee_jour_id is not null
	AND casl.date_distrib = _date_distrib
	AND d.code = _depot_code
	AND casl.flux_id = _flux_id
GROUP BY casl.vol1 , casl.vol2 , casl.qte , d.libelle , d.id , p.id , mtj.code , e.nom , e.prenom1 , arnvp.adresse , arnvp.cp , arnvp.ville , asoc.numabo_ext , s.code , f.path , casl.point_livraison_id , casl.point_livraison_ordre , asoc.date_service_1 , asoc.date_stop
;
END |
DELIMITER ;