<?php

namespace Ams\DistributionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;
use Ams\DistributionBundle\Exception\ClientsAServirSQLException;

class ClientAServirLogistRepository extends EntityRepository {

    CONST QUOTIDIEN = 1;
    CONST HEBDO = 2;

    /**
     * Les dates ulterieures deja inserees pour le code societe exterieure courant
     * 
     * @param string $socCodeExt
     * @param \DateTime $dateDistribCourant
     * @return type
     * @throws \Doctrine\DBAL\DBALException
     */
    public function datesUlterieures($socCodeExt, \DateTime $dateDistribCourant) {
        try {
            $qb = $this->createQueryBuilder('c');
            $qb->select('DISTINCT c.dateDistrib AS date_distrib')
                    ->where('c.dateDistrib >= :dateDistrib')
                    ->andWhere('c.socCodeExt = :socCodeExt')
                    ->setParameters(array(':dateDistrib' => $dateDistribCourant, ':socCodeExt' => $socCodeExt));

            return $qb->getQuery()->getResult();
        } catch (DBALException $ex) {
            throw $ex;
        }
    }

    /**
     * Suppression des donnees de date de distribu $dateDistrib et de socCodeExt $socCodeExt.
     * Cette methode est appele quand un fichier d'une societe est a traite de nouveau pour une date donnee
     * 
     * @param \DateTime $dateDistrib
     * @param string $socCodeExt
     * @return type
     */
    public function suppressionAvecDateSoc(\DateTime $dateDistrib, $socCodeExt) {
        $qb = $this->createQueryBuilder('c');
        $qb->delete()
                ->where('c.dateDistrib = :dateDistrib')
                ->andWhere('c.socCodeExt = :socCodeExt')
                ->setParameters(array(':dateDistrib' => $dateDistrib, ':socCodeExt' => $socCodeExt));
        return $qb->getQuery()->getResult();
    }

    public function getNewAbonne($aParam) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
                SELECT
                    *,casl.vol1,casl.vol2,asoc.numabo_ext as num_abonne,
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
                    ) as info_portage_livraison
                FROM
                    client_a_servir_logist casl
                LEFT JOIN infos_portages_livraisons info_p_l ON info_p_l.livraison_id = casl.point_livraison_id
                LEFT JOIN infos_portages_abonnes info_p_a ON info_p_a.abonne_id = casl.abonne_soc_id
                LEFT JOIN info_portage info_p ON info_p.id = info_p_a.info_portage_id
                AND info_p.date_debut <= now() + 1
                AND info_p.date_fin > now()
                AND info_p.active = 1   
                LEFT JOIN produit p ON p.id = casl.produit_id
                LEFT JOIN fichier f ON f.id = p.image_id
                LEFT JOIN adresse_rnvp arnvp ON arnvp.id = casl.point_livraison_id
                LEFT JOIN abonne_soc asoc ON asoc.id = casl.abonne_soc_id
                JOIN modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id 
                JOIN tournee_detail td ON td.modele_tournee_jour_code = mtj.code AND td.num_abonne_id = casl.abonne_soc_id
                WHERE (NOT EXISTS (
                    SELECT 
                        *
                    FROM
                        client_a_servir_logist casl2
                    WHERE
                        casl2.date_distrib = ('" . $aParam['dateDistrib'] . "' - INTERVAL 7 DAY)
                        AND casl2.abonne_soc_id = casl.abonne_soc_id
                        AND casl2.depot_id = " . $aParam['depot'] . ")
                    or mtj.code not in(
					SELECT 
                        mtj2.code
                    FROM
                        client_a_servir_logist casl3
						JOIN modele_tournee_jour mtj2 ON mtj2.id = casl3.tournee_jour_id 
                    WHERE
                        casl3.date_distrib = ('" . $aParam['dateDistrib'] . "' - INTERVAL 7 DAY)
                        AND casl3.abonne_soc_id = casl.abonne_soc_id
                        AND casl3.depot_id = " . $aParam['depot'] . "
                ))
                AND casl.date_distrib = '" . $aParam['dateDistrib'] . "'
                AND casl.depot_id = " . $aParam['depot'] . "
                AND p.periodicite_id IN (" . self::QUOTIDIEN . "," . self::HEBDO . ") ";
        if ($aParam['produitId'])
            $q .=" AND produit_id IN (" . $aParam['produitId'] . ")";
        if (implode("','", $aParam['socCode']))
            $q.=" AND asoc.soc_code_ext IN('" . implode("','", $aParam['socCode']) . "')";
        //$q.=" AND info_p_a.abonne_id IS NOT NULL ";
        $q .=" GROUP BY casl.id ORDER BY casl.commune_id";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function getAbonneStop($aParam) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
                SELECT
                    *,casl.vol1,casl.vol2,asoc.numabo_ext as num_abonne,(select mtj2.id from modele_tournee_jour mtj2 where mtj2.code=mtj1.code and '" . $aParam['dateDistrib'] . "' BETWEEN mtj2.date_debut and mtj2.date_fin ) as id_recent, p.libelle as produit_libelle 
                FROM
                    client_a_servir_logist casl
                LEFT JOIN produit p ON p.id = casl.produit_id
                LEFT JOIN fichier f ON f.id = p.image_id
				LEFT JOIN adresse_rnvp arnvp ON arnvp.id = casl.point_livraison_id
                LEFT JOIN abonne_soc asoc ON asoc.id = casl.abonne_soc_id
                JOIN modele_tournee_jour mtj1 ON mtj1.id=casl.tournee_jour_id
                WHERE (NOT EXISTS (
                    SELECT 
                        *
                    FROM
                        client_a_servir_logist casl2
                    WHERE
                        casl2.date_distrib = '" . $aParam['dateDistrib'] . "'
                        AND casl2.abonne_soc_id = casl.abonne_soc_id
                        AND casl2.depot_id = " . $aParam['depot'] . ")
                 OR mtj1.code not in (
			select mtj3.code from client_a_servir_logist casl3
			inner join modele_tournee_jour mtj3 ON mtj3.id = casl3.tournee_jour_id
			where 
			casl3.date_distrib = '" . $aParam['dateDistrib'] . "' 
                        AND casl3.abonne_soc_id = casl.abonne_soc_id
                        AND casl3.depot_id = " . $aParam['depot'] . " )
                   )
                AND casl.date_distrib = ('" . $aParam['dateDistrib'] . "' - INTERVAL 7 DAY)
                AND casl.depot_id = " . $aParam['depot'] . "
                AND p.periodicite_id IN (" . self::QUOTIDIEN . "," . self::HEBDO . ") ";
        if ($aParam['produitId'])
            $q .=" AND produit_id IN (" . $aParam['produitId'] . ")";
        if (implode("','", $aParam['socCode']))
            $q.=" AND asoc.soc_code_ext IN('" . implode("','", $aParam['socCode']) . "')";
        $q.= " ORDER BY casl.commune_id";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function getSocieteByDateFluxDepot($depotId, $fluxId) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
                SELECT DISTINCT
                    s.id, s.libelle
                FROM client_a_servir_logist casl 
                JOIN societe s ON s.id = casl.societe_id
                WHERE date_distrib BETWEEN CURDATE() - INTERVAL 90 DAY AND CURDATE() 
                    AND depot_id = $depotId 
                    AND flux_id = $fluxId
                ORDER BY s.libelle";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function getCityByDateFluxDepot($depotId, $fluxId) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
                SELECT DISTINCT 
                    commune_id,c.libelle,cp
                FROM client_a_servir_logist casl
                JOIN commune c ON c.id = casl.commune_id
                WHERE date_distrib BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() 
                    AND depot_id = $depotId
                    AND flux_id = $fluxId
                ORDER BY c.libelle";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function getModeleTourneeByDateFluxDepot($depotId, $fluxId) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
                SELECT DISTINCT 
                    mtj.id,mtj.code
                FROM client_a_servir_logist casl
                JOIN modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id
                WHERE date_distrib BETWEEN CURDATE() - INTERVAL 90 DAY AND CURDATE() 
                    AND depot_id = $depotId
                    AND flux_id = $fluxId
                ORDER BY mtj.jour_id,mtj.code";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function rechercheAbonne($aParam) {
        $connection = $this->getEntityManager()->getConnection();
        /*
          $q =    "
          SELECT
          asoc.id as abonne_id,asoc.numabo_ext,
          a.id,a.vol1,a.vol2,a.vol3,a.vol4,a.vol5,a.cp,a.ville,a.adresse_rnvp_etat_id,a.date_debut,a.date_fin,a.point_livraison_id as pointLivraisonId,
          arnvp.cadrs as pointLivraisonCadrs,arnvp.adresse as pointLivraisonAdresse,arnvp.lieudit as pointLivraisonLieuDit,arnvp.cp as pointLivraisonCp,arnvp.ville as pointLivraisonVille,
          soc.libelle as socLibelle,
          dep.libelle AS depotLibelle,
          if(a.adresse_rnvp_etat_id > 2,etat.libelle,'Rejet geocodage') as etat,
          if(a.adresse_rnvp_etat_id > 2,'RNVP','GEO') as type_rejet
          FROM client_a_servir_logist casl
          JOIN abonne_soc asoc ON asoc.id = casl.abonne_soc_id
          JOIN adresse a ON a.abonne_soc_id = asoc.id
          AND a.date_fin > CURDATE()
          AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
          JOIN adresse_rnvp arnvp ON arnvp.id = a.point_livraison_id
          JOIN depot dep ON dep.id = casl.depot_id
          JOIN societe soc ON soc.id = casl.societe_id
          JOIN adresse_rnvp_etat etat ON a.adresse_rnvp_etat_id = etat.id
          WHERE casl.depot_id = ".$aParam['depotId']."
          AND casl.flux_id = ".$aParam['fluxId']."
          AND casl.date_distrib >= CURDATE() - INTERVAL 90 DAY
          AND casl.num_parution=(select max(casl2.num_parution) from
          client_a_servir_logist casl2
          where casl2.abonne_soc_id=casl.abonne_soc_id
          and casl2.abonne_unique_id=casl.abonne_unique_id)";
          $q.=($aParam['societeId'] == '')? "": " AND casl.societe_id = ".$aParam['societeId'];
          $q.=($aParam['numaboExt'] == '')? "": " AND asoc.numabo_ext LIKE '%".$aParam['numaboExt']."%'" ;
          $q.=($aParam['vol1'] == '')? "": " AND asoc.vol1 LIKE '%".$aParam['vol1']."%'";
          $q.=($aParam['vol2'] == '')? "": " AND asoc.vol2 LIKE '%".$aParam['vol2']."%'";
          $q.=($aParam['vol4'] == '')? "": " AND a.vol4 LIKE '%".$aParam['vol4']."%'";
          $q.=($aParam['communeId'] == '')? "": " AND a.commune_id = ".$aParam['communeId'];
          $q.="
          GROUP BY
          asoc.id,asoc.numabo_ext, a.id,a.vol1,a.vol2,a.vol3,a.vol4,
          a.vol5,a.cp,a.ville,a.adresse_rnvp_etat_id,a.date_debut,a.date_fin,a.point_livraison_id , arnvp.cadrs,arnvp.adresse,
          arnvp.lieudit ,arnvp.cp ,arnvp.ville , soc.libelle, dep.libelle ";
         */
        $q = " SELECT
                abonne_id, numabo_ext,
                id, vol1, vol2, vol3, vol4, vol5, cp, ville, adresse_rnvp_etat_id, date_debut, date_fin, pointLivraisonId,
                pointLivraisonCadrs, pointLivraisonAdresse, pointLivraisonLieuDit, pointLivraisonCp, pointLivraisonVille,
                socLibelle,
                depotLibelle,
                etat,
                type_rejet,
                date_distrib
            FROM
                ( SELECT
                    casl.date_distrib, 
                    asoc.id as abonne_id, asoc.numabo_ext,
                    a.id, casl.vol1, casl.vol2, a.vol3, a.vol4, a.vol5, a.cp, a.ville, a.adresse_rnvp_etat_id, a.date_debut, a.date_fin, casl.point_livraison_id as pointLivraisonId,
                    arnvp.cadrs as pointLivraisonCadrs, arnvp.adresse as pointLivraisonAdresse, arnvp.lieudit as pointLivraisonLieuDit, arnvp.cp as pointLivraisonCp, arnvp.ville as pointLivraisonVille,
                    soc.libelle as socLibelle,
                    dep.libelle AS depotLibelle,
                    if(a.adresse_rnvp_etat_id > 2,etat.libelle,'Rejet geocodage') as etat,
                    if(a.adresse_rnvp_etat_id > 2,'RNVP','GEO') as type_rejet
                FROM client_a_servir_logist casl
                    JOIN abonne_soc asoc ON asoc.id = casl.abonne_soc_id
                    JOIN adresse a ON a.id = casl.adresse_id 
                    LEFT JOIN adresse_rnvp arnvp ON arnvp.id = casl.point_livraison_id
                    JOIN depot dep ON dep.id = casl.depot_id
                    JOIN societe soc ON soc.id = casl.societe_id
                    JOIN adresse_rnvp_etat etat ON a.adresse_rnvp_etat_id = etat.id";
//                if($aParam['numaboExt'] != ''){
//                    $q.=" JOIN tournee_detail td ON substr(td.modele_tournee_jour_code,1,3) = dep.code";
//                }

        $q.=" WHERE casl.depot_id = " . $aParam['depotId'] . "
                    AND casl.flux_id = " . $aParam['fluxId'] . "
                    AND casl.date_distrib BETWEEN CURDATE() - INTERVAL 45 DAY AND CURDATE() + INTERVAL 30 day ";
        $q.=($aParam['societeId'] == '') ? "" : " AND casl.societe_id = " . $aParam['societeId'];
        $q.=($aParam['numaboExt'] == '') ? "" : " AND asoc.numabo_ext LIKE '%" . $aParam['numaboExt'] . "%'";
        $q.=($aParam['vol1'] == '') ? "" : " AND casl.vol1 LIKE '%" . $aParam['vol1'] . "%'";
        $q.=($aParam['vol2'] == '') ? "" : " AND casl.vol2 LIKE '%" . $aParam['vol2'] . "%'";
        $q.=($aParam['vol4'] == '') ? "" : " AND a.vol4 LIKE '%" . $aParam['vol4'] . "%'";
        $q.=($aParam['communeId'] == '') ? "" : " AND casl.commune_id = " . $aParam['communeId'];
        $q .= "
                ORDER BY 
                    casl.date_distrib DESC
                ) t
            GROUP BY
                abonne_id, id
            ORDER BY
                socLibelle ASC, numabo_ext ASC, date_distrib DESC
                        ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    /**
     * Transfert des donnees de la table temporaire CLIENT_A_SERVIR_SRC_TMP1 vers la table CLIENT_A_SERVIR_LOGIST
     */
    public function tmpVersClientAServirLogist($ficRecapId) {
        try {
            $sInsert = "INSERT INTO client_a_servir_logist "
                    . " (date_distrib, date_parution, num_parution, vol1, vol2, type_portage, qte, soc_code_ext, fic_recap_id, abonne_soc_id, client_type, abonne_unique_id, adresse_id, rnvp_id, point_livraison_id, commune_id, depot_id, flux_id, societe_id, produit_id, client_a_servir_src_id, type_service)"
                    . "SELECT "
                    . " tmp.date_distrib, tmp.date_parution, tmp.num_parution, tmp.vol1, tmp.vol2, tmp.type_portage, tmp.qte, tmp.soc_code_ext, tmp.fic_recap_id, tmp.abonne_soc_id, tmp.client_type, tmp.abonne_unique_id, tmp.adresse_id, tmp.rnvp_id, tmp.point_livraison_id, tmp.commune_id, tmp.depot_id, prd.flux_id, tmp.societe_id, tmp.produit_id, src.id, 'L' AS type_service "
                    . "     FROM client_a_servir_src_tmp1 tmp"
                    . "         LEFT JOIN client_a_servir_src src ON tmp.id = src.tmp_id "
                    . "         LEFT JOIN produit prd ON tmp.produit_id = prd.id "
                    . " WHERE "
                    . "     src.tmp_id IS NOT NULL ";
            $this->_em->getConnection()->prepare($sInsert)->execute();
        } catch (DBALException $ex) {
            throw ClientsAServirSQLException::transfertLogist($ficRecapId, $ex->getMessage());
        }
    }

    public function deleteByDateProduct($date, $produitId) {
        $q = "
            DELETE FROM client_a_servir_logist
            WHERE date_distrib = '$date'
                AND produit_id = $produitId
            ";
        $this->_em->getConnection()->prepare($q)->execute();
    }

    /**
     * Recuperation des infos de l'abonné et de son stop livraison
     * @param Array $data tableau associatif 
     * @param Array $depots liste des dépots 
     * @param Boolean $toString si TRUE alors on récupère la req SQL comme chaine de caractère 
     * @param int $iDefaultWeekPeriod Le nombre de semaines par défaut à prendre en compte pour le Select si la périodicité du produit n'est pas connue
     * @param bool $bGeoNeverNull si égal à TRUE, les coordonnées remplacent les coodonnées du PL si nulles
     * pret à être insérer dans la table export_geoconcept
     * des parametres
     *
     */
    public function exportClient(array $depots, $data = array(), $toString = false, $iDefaultWeekPeriod = 3, $bGeoNeverNull = FALSE) {

        $query = "  SELECT DISTINCT
                    SC.libelle AS 'Nom_Societe',
                    MAX(CL.date_parution) AS 'date',
                    CL.produit_id AS 'produit_id',
                    PD.libelle AS 'Nom_Produit',
                    DP.libelle AS 'Nom_Depot',
                    TRJ.code AS 'Code_Tournee',
                    CL.abonne_soc_id AS 'abonne_soc_id',
                    AB.numabo_ext AS 'Numero_Abonne',
                    CL.vol1 AS 'Nom',
                    CL.vol2 AS 'Raison_Social',
                    AD.vol3 AS 'Cplt_adresse_1',
                    AD.vol4 AS 'Adresse',
                    AD.vol5 AS 'Lieu_dit',
                    AD.cp AS 'CP',
                    AD.ville AS 'Ville',

                    CL.point_livraison_id AS 'point_livraison_id',

                    PL.cadrs AS 'PL_complement_adresse',
                    PL.adresse AS 'PL_adresse',

                    PL.lieudit AS 'PL_lieu_dit',
                    PL.cp AS 'PL_cp',
                    PL.ville AS 'PL_ville',";

        if ($bGeoNeverNull) {
            $query .= "COALESCE(PL.geox, RN.geox) AS 'PL_X',
                    COALESCE(PL.geoy, RN.geoy) AS 'PL_Y',";
        } else {
            $query .= "PL.geox AS 'PL_X',
                    PL.geoy AS 'PL_Y',";
        }
        $query .= "RN.cadrs AS 'RNVP_Cplt_Adresse',
                    RN.adresse AS 'RNVP_Adresse',
                    RN.lieudit AS 'RNVP_Lieu_dit',
                    RN.cp AS 'RNVP_CP',
                    RN.ville AS 'RNVP_Ville',
                    RN.geox AS 'RNVP_X',
                    RN.geoy AS 'RNVP_Y',

                    CL.type_service AS 'TYP_SERVICE', 
                    CT.duree_livraison AS 'TPS',
                    CL.flux_id AS 'Flux_id',
                    TRJ.jour_id AS 'TRJ_JourId'
                    ";

        if ($toString) {
            $query .= ", ':queryLibelle:'";
            $query .= ", ':queryId:'";
        }

        $query .="  
                    , COALESCE(SF.new_societe_id, CL.societe_id) AS 'fusion_soc_id'
                    FROM client_a_servir_logist CL 
                    LEFT JOIN adresse AD ON CL.adresse_id = AD.id 
                    LEFT JOIN adresse_rnvp PL ON CL.point_livraison_id = PL.id 
                    LEFT JOIN adresse_rnvp RN ON CL.rnvp_id = RN.id 
                    LEFT JOIN abonne_soc AB ON CL.abonne_soc_id =  AB.id
                    LEFT JOIN produit PD ON CL.produit_id =  PD.id
                    LEFT JOIN societe SC ON CL.societe_id =  SC.id
                    LEFT JOIN depot DP ON CL.depot_id  = DP.id
                    LEFT JOIN modele_tournee_jour TRJ ON CL.tournee_jour_id = TRJ.id
                    LEFT JOIN ref_periodicite PRCT ON PD.periodicite_id = PRCT.id
                    LEFT JOIN ref_natureclient CT ON CL.client_type = CT.id 
                    LEFT JOIN soc_fusion SF ON SF.new_societe_id = CL.societe_id
                    ";

        $query .= " WHERE CL.depot_id  IN(" . implode(',', $depots) . ")";

        if (isset($data['tournee']) && count($data['tournee']) > 0 && ($data['operateur_tournee'] != '')) {
            $query .= " AND TRJ.tournee_id " . $data['operateur_tournee'] . " (" . implode(',', $data['tournee']) . ")";
        }

        if (isset($data['produit']) && count($data['produit']) > 0 && ($data['operateur_produit'] != '')) {
            $query .= " AND CL.produit_id " . $data['operateur_produit'] . "  (" . implode(',', $data['produit']) . ")";
        }

        if (isset($data['flux']) && count($data['flux']) > 0 && ($data['operateur_flux'] != '')) {
            $query .= " AND CL.flux_id " . $data['operateur_flux'] . "  (" . implode(',', $data['flux']) . ")";
        }

        if (isset($data['statut']) && count($data['statut']) > 0 && ($data['operateur_statut'] != '')) {
            $query .= " AND type_service " . $data['operateur_statut'] . "('" . implode(",", $data['statut']) . "')";
        }

        if (isset($data['jour']) && count($data['jour']) > 0 && ($data['operateur_jour'] != '')) {
            $query .= " AND  DAYOFWEEK(CL.date_parution) " . $data['operateur_jour'] . " (" . implode(',', $data['jour']) . ")";
        }

        // Ajout de la prise en compte d'une fenêtre temporelle fixe
        $query .= " AND (CL.date_parution >= DATE_SUB(NOW(), interval COALESCE(PRCT.export_nb_sem, $iDefaultWeekPeriod) WEEK) and (CL.date_parution < NOW()))";

        // @TODO: A supprimer lorsque le problème sur Paris Match sera corrigé
        $query .= " AND ( (CL.societe_id NOT IN (54, 23) OR (CL.societe_id IN (54, 23) AND CL.date_parution <> '2015-05-21'))) ";

        // On dédoublonne sur l'abonné dans la tournée pour prendre en compte le cas des éditions multiples
        // $query .= " GROUP BY CL.abonne_soc_id, CL.abonne_unique_id ";
        $query .= " GROUP BY AB.numabo_ext, fusion_soc_id ";


//        exit($query);
        //        if (isset($data['parution']) && $data['parution'] != '' && ($data['operateur_parution'] != '')) {
//            if (!in_array($data['operateur_parution'], array('BETWEEN', 'NOT BETWEEN'))) {
//                $query .= " AND  date_parution " . $data['operateur_parution'] . "'" . $data['parution'] . "' ";
//            } else {
//                $query .= " AND  date_parution  " . $data['operateur_parution'] . " '" . $data['parution'] . "' AND '" . $data['parution_fin'] . "' ";
//            }
//        }
//        $query .= "LIMIT 5000 ";
//        var_dump($data); 
        if ($toString) {
            return $query . ";";
        } else {
            return $this->_em->getConnection()->fetchAll($query);
        }
    }

    /*     * *
     * Insertion dans la table 
     * export_geoconcept
     */

    public function updateGeoConcept($query) {
        // Permet d'éviter les erreurs 500 sur le chargement de données volumineuses ou de les tronquer lors de leur insertion en bdd
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        ini_set('mysql.connect_timeout', '0');
        ini_set('max_execution_time', '0');

        // var_dump($query); exit();
        try {

            $insert = "INSERT INTO export_geoconcept ( ";
            $insert .= "societe, date_parution, ";
            $insert .= "produit_id, produit, depot, code_tournee, abonne_soc_id, numabo_ext, ";
            $insert .= "volet1, volet2, vol3, vol4, vol5, cp, ville, ";
            $insert .= "point_livraison_id, pl_cards, pl_adresse, ";
            $insert .= "pl_lieudit, pl_cp, pl_ville, pl_geox, pl_geoy, rnvp_cards ,rnvp_adresse , ";
            $insert .= "rnvp_lieudit ,rnvp_cp ,rnvp_ville ,rnvp_geox ,rnvp_geoy , ";
            $insert .= "type_service,duree_livraison, flux_id, jour_id,requete, requete_export_id, fusion_soc_id ";
            $insert .= ")" . $query;
            $this->_em->getConnection()->prepare($insert)->execute();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Quantites de produits par jour et pour chaque depot
     * 
     * @param \DateTime $date
     * @param array $depotsId
     * @param boolean $avecPasse
     * @param in
     * @return type
     * @throws \Doctrine\DBAL\DBALException
     */
    public function qtesProduitsParJour(\DateTime $date, $depotsId = array(), $avecPasse = false, $flux = 0) {
        try {
            $dateCourant = clone $date;
            $dateCourant->setTime(0, 0, 0); // Suppression des heure, minute & seconde
            $aParam = array(':depotsIds' => $depotsId, ':dateDistrib' => $dateCourant);
            // SELECT SUM(c0_.qte) AS sclr0, c0_.produit_id AS sclr1, c0_.depot_id AS sclr2 
            // FROM client_a_servir_logist c0_ 
            //      WHERE c0_.date_distrib = ? 
            //      AND (c0_.depot_id IN (?) OR c0_.depot_id IS NULL) 
            // GROUP BY c0_.produit_id, c0_.depot_id
            $qb = $this->createQueryBuilder('c');
            $qb->select('SUM(c.qte) AS qte', 'IDENTITY(c.produit) AS produit', 'COALESCE(IDENTITY(c.depot), 0) AS depot')
                    ->where('c.dateDistrib = :dateDistrib');
            if ($flux > 0) {
                $aParam[':flux'] = $flux;
                $qb->andWhere('c.flux = :flux');
                //return($flux);
            }
            //creation de l'expression OR
            $orModule = $qb->expr()->orX();
            $orModule->add($qb->expr()->in('c.depot', ':depotsIds'));
            $orModule->add($qb->expr()->isNull('c.depot'));

            $qb->andWhere($orModule)
                    ->groupBy('c.produit', 'c.depot')
                    ->setParameters($aParam);
            return $qb->getQuery()->getResult();

            /*
              $slct   = " SELECT
              IFNULL(c.depot_id, 0) AS depot_id
              , p.libelle AS produit_libelle
              , c.produit_id
              , SUM(c.qte) AS qte
              FROM
              client_a_servir_logist c
              LEFT JOIN produit p ON c.produit_id = p.id
              WHERE
              c.date_distrib = '".$dateDistrib->format("Y-m-d")."'
              ".(empty($depotsId) ? " AND c.depot_id IS NULL ":" AND (c.depot_id IN (".implode(', ', $depotsId).") OR c.depot_id IS NULL) ")."
              GROUP BY
              depot_id, produit_libelle, produit_id
              ORDER BY
              produit_libelle
              ";
              return $this->_em->getConnection()->fetchAll($slct);

             */
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Quantites de produits par jour, pour le depot identifie par $depot_id et pour chaque tournee
     * 
     * @param \DateTime $date
     * @param id $depot_id
     * @param  integer $flux
     * @return type
     * @throws \Doctrine\DBAL\DBALException
     */
    public function qtesProduitsParJourParTournee(\DateTime $date, $depot_id, $flux = 0) {
        try {
            $dateCourant = clone $date;
            $dateCourant->setTime(0, 0, 0); // Suppression des heure, minute & seconde

            $qb = $this->createQueryBuilder('c');
            $qb->select('SUM(c.qte) AS qte', 'IDENTITY(c.produit) AS produit', 'COALESCE(IDENTITY(c.tournee), 0) AS tournee')
                    ->where('c.dateDistrib = :dateDistrib');
            if ($depot_id != 0) {
                $qb->andWhere('c.depot = :depot');
            } else {
                $qb->andWhere($qb->expr()->isNull('c.depot'));
            }
            if ($flux > 0) {
                $qb->andWhere('c.flux = :flux');
            }
            $qb->groupBy('c.produit', 'c.tournee');
            if ($depot_id != 0) {
                if ($flux > 0) {
                    $qb->setParameters(array(':depot' => $depot_id, ':dateDistrib' => $dateCourant, ':flux' => $flux));
                } else {
                    $qb->setParameters(array(':depot' => $depot_id, ':dateDistrib' => $dateCourant));
                }
            } else {
                if ($flux > 0) {
                    $qb->setParameters(array(':dateDistrib', $dateCourant, ':flux' => $flux));
                } else {
                    $qb->setParameter(':dateDistrib', $dateCourant);
                }
            }
///echo $qb->getQuery()->getSQL();
            return $qb->getQuery()->getResult();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * !! remplacera la méthode qtesProduitsParJourParTournee  
     * 
     * retourne les quantites de produit par depot, flux, date_distrib 
     * groupe par produit et tournée
     * @param type $depot_id
     * @param type $flux_id
     * @param type $date_distrib
     */
    public function getQteProduits($depot_id, $flux_id = 0, $date_distrib) {

        $query = " SELECT 
                    sum(c.qte) as qte,
                    prd.libelle as prd_libelle,
                    prd.id as prd_id,
                    fic.path as img_path,
                    mtj.code as mtj_code,
                    mtj.id as mtj_id
                FROM client_a_servir_logist c
                LEFT JOIN produit prd ON prd.id = c.produit_id
                LEFT JOIN modele_tournee_jour mtj ON mtj.id =c.tournee_jour_id
                LEFT JOIN fichier fic on fic.id =  prd.image_id
            
                WHERE  c.depot_id = '" . $depot_id . "'
                 AND c.date_distrib = '" . $date_distrib . "'";
        if ($flux_id > 0)
            $query .=" AND c.flux_id ='" . $flux_id . "'";

        $query .=" Group By prd.id, mtj.id order by prd_libelle, mtj_code 
            ";

        return $this->_em->getConnection()->fetchAll($query);
    }

    /**
     * Quantites de produits par jour, pour le depot identifie par $depot_id et pour chaque tournee
     * 
     * @param \DateTime $date
     * @param id $depot_id
     * @param id $produit_id
     * @return type
     * @throws \Doctrine\DBAL\DBALException
     */
    public function listeClientsParCDParProduit(\DateTime $date, $depot_id, $produit_id, $flux = 0) {
        try {
            $dateCourant = clone $date;
            $dateCourant->setTime(0, 0, 0); // Suppression des heure, minute & seconde

            $qb = $this->createQueryBuilder('c');
            $qb->select('c.vol1 AS vol1', 'c.vol2 AS vol2', 'c.qte AS qte')
                    ->join('c.abonneSoc', 'aboSoc')
                    ->addSelect('aboSoc.numaboExt AS numaboExt')
                    ->join('c.adresse', 'adr')
                    ->addSelect('adr.vol3 AS vol3', 'adr.vol4 AS vol4', 'adr.vol5 AS vol5', 'adr.cp AS cp', 'adr.ville AS ville')
                    ->leftJoin('c.tournee', 'trn')
                    ->addSelect("COALESCE(trn.code, '-') AS tournee")
                    ->leftJoin('c.produit', 'p')
                    ->addSelect('p.libelle AS produit_libelle')
                    ->leftJoin('c.societe', 's')
                    ->addSelect('s.libelle AS societe_libelle')
                    ->where('c.dateDistrib = :dateDistrib')
                    ->andWhere('c.produit = :produit');
            if ($depot_id != 0) {
                $qb->andWhere('c.depot = :depot');
            } else {
                $qb->andWhere($qb->expr()->isNull('c.depot'));
            }
            if ($flux > 0) {
                $qb->andWhere('c.flux = :flux');
            }
            $parametres = array(':produit' => $produit_id, ':dateDistrib' => $dateCourant);
            if ($depot_id != 0) {
                $parametres[':depot'] = $depot_id;
            }
            if ($flux > 0) {
                $parametres[':flux'] = $flux;
            }
            $qb->setParameters($parametres);

            return $qb->getQuery()->getResult();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    public function getProduitsByTourneeIds($tourneeIds) {
        $qb = $this->createQueryBuilder('c');
        $param = implode(",", $tourneeIds);
        $qb->select('IDENTITY(c.produit) as produitId', 'c.dateParution', 'IDENTITY(tournee_jour.jour) as jourId', 'ref_jour.libelle', 'c.typeService')
                ->join("c.tournee", "tournee_jour")
                ->join("tournee_jour.jour", "ref_jour")
                ->where('tournee_jour.tournee IN ( ' . $param . ' )')
                ->groupBy("c.produit,ref_jour.id")
        ;
        return $qb->getQuery()->getResult();
    }

    /**
     * PERMUTATION DE TOURNEE PAR DATE
     * */
    public function permutationTourneeJour($tourneeIdOrigine, $tourneeIdDestination, $dateDistrib, $sPointLivraisonId) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
            UPDATE  client_a_servir_logist 
            SET tournee_jour_id = $tourneeIdDestination 
            WHERE tournee_jour_id = $tourneeIdOrigine
                AND date_distrib = '$dateDistrib'
                AND point_livraison_id IN ($sPointLivraisonId)
        ";
        $connection->executeQuery($q);
    }

    /**
     * Méthode qui permet de basculer un point de livraison vers une autre tournée
     * @param array $conditionsArr Le tableau contenant les les conditions de la requete:tournee_destination_jour_id,  point_livraison_id, tournee_source_jour_id,flux_id, date_distribution
     */
    public function changerPointsdeTournee($conditionsArr) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "UPDATE `client_a_servir_logist` AS casl "
                . "SET tournee_jour_id = :tournee_destination_jour_id "
                . ",point_livraison_ordre = :nouvel_ordre "
                . "WHERE "
                . "point_livraison_id = :point_livraison_id "
                . "AND tournee_jour_id = :tournee_source_jour_id "
                . "AND flux_id = :flux_id "
                . "AND date_distrib >= :date_distribution;";
        return $connection->executeQuery($q, $conditionsArr);
    }

    /**
     * Méthode qui permet de basculer pluisieurs points de livraison vers une autre tournée
     * tag: yannick
     * @param array $condArr Le tableau contenant  les conditions de la requete:tournee_destination_jour_id,  point_livraison_id, tournee_source_jour_id,flux_id, date_distribution
     * @param array $infosArr Le tableau contenant tous les points de la tournee avec le point de livraison id , le tournee detail id ,  l'ordre et l'etat(ancien ou nouveau)
     */
    public function changerMultPointsdeTournee($condArr, $infosArr) {
        $connection = $this->getEntityManager()->getConnection();
        $aRetour = array();
        $ct = 0;
        foreach ($infosArr as $infos) {
            if ($infos['etat'] == "nouveau") {
                $q = "UPDATE `client_a_servir_logist` AS casl "
                        . "SET tournee_jour_id = " . $condArr['tournee_destination_jour_id']
                        . ", point_livraison_ordre = " . $infos['ordre']
                        . " WHERE "
                        . "point_livraison_id = " . $infos['plId']
                        . " AND tournee_jour_id = " . $condArr['tournee_jour_id']
                        . " AND flux_id = " . $condArr['flux_id']
                        . " AND date_distrib >= " . $condArr['date_distribution']
                        . " ;";
                $aRetour[$ct] = $connection->executeQuery($q);
                $ct++;
            }
        }

        return $aRetour;
    }

    /**
     * Méthode qui permet de modifier l'ordre dans une tournée
     * @param array $conditionsArr Le tableau contenant les les conditions de la requete :date_distrib, abonne_soc_id, point_livraison_id, tournee_jour_id, point_livraison_ordre
     */
    public function modifierOrdrePoint($conditionsArr) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "UPDATE `client_a_servir_logist` AS casl "
                . "SET point_livraison_ordre = :point_livraison_ordre "
                . "WHERE "
                . "point_livraison_id = :point_livraison_id "
                . "AND tournee_jour_id = :tournee_jour_id "
                . "AND date_distrib >= :date_distrib;";
        return $connection->executeQuery($q, $conditionsArr);
    }

    /**
     * Méthode qui permet de compter le nombre de fois qu'un point de livraison est associé à une tournée à partir d'un moment donné
     * @param array $conditionsArr Le tableau contenant les les conditions de la requete:tournee_jour_id,  point_livraison_id, flux_id, date_distribution
     * @return array Le tableau de résultat de la requete avec le compte dans l'index "nb"
     */
    public function compterLivraisonsPourPointDansTournee($conditionsArr) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "SELECT count(id) AS nb FROM `client_a_servir_logist` "
                . "WHERE tournee_jour_id=:tournee_jour_id "
                . "AND point_livraison_id=:point_livraison_id "
                . "AND flux_id=:flux_id "
                . "AND date_distrib >= " . $conditionsArr['date_distrib']
                . ";";

        $stmt = $connection->executeQuery($q, $conditionsArr);
        return $stmt->fetchAll();
    }

    /**
     * Méthode qui permet de compter le nombre de fois qu'un ensemble de points de livraison est associé à une tournée à partir d'un moment donné
     * @param array $condArr Le tableau contenant les les conditions de la requete:tournee_jour_id,  point_livraison_id, flux_id, date_distribution
     * @return array Le tableau de résultat de la requete avec le compte dans l'index "nb"
     */
    public function countDeliverMultPointTrn($condArr) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "SELECT count(id) AS nb FROM `client_a_servir_logist` "
                . "WHERE tournee_jour_id = " . $condArr['tournee_jour_id']
                . "  AND point_livraison_id IN ( " . implode(",", $condArr['tab_livraison_id']) . " )"
                . "  AND flux_id = " . $condArr['flux_id']
                . "  AND date_distrib >=" . $condArr['date_distrib']
                . " ;";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    /**
     * Méthode qui retourne une liste de clients pour un daté, une tournée, un flux et un point de livraison donnés
     * @param array $conditionsArr Le tableau contenant les les conditions de la requete:tournee_jour_id,  point_livraison_id, flux_id, date_distribution
     * @return array Le tableau de résultat de la requete avec le compte dans l'index "nb"
     */
    public function listerAbonnesPourPointDistrib($conditionsArr) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "SELECT DISTINCT abonne_soc_id FROM `client_a_servir_logist` "
                . "WHERE tournee_jour_id=:tournee_jour_id "
                . "AND point_livraison_id=:point_livraison_id "
                . "AND flux_id=:flux_id "
                . "AND date_distrib >= " . $conditionsArr['date_distribution']
                . ";";

        $stmt = $connection->executeQuery($q, $conditionsArr);
        return $stmt->fetchAll();
    }

    /**
     * Méthode qui retourne une liste de clients pour un daté, une tournée, un flux et un tableau de point de livraison donnés
     * @param array $condArr Le tableau contenant les les conditions de la requete:tournee_jour_id,  point_livraison_id, flux_id, date_distribution
     * @param integer $base 0/1 permet de définir la direction pou la récupération des données [0: on va de la source, 1:on va de la destination ]
     * @return array  le tableau contenant les resultats 
     */
    public function getListAboForDeliver($condArr, $base = 0) {
        $connection = $this->getEntityManager()->getConnection();
        if ($base == 0) {
            $q = "SELECT DISTINCT abonne_soc_id FROM `client_a_servir_logist` "
                    . "WHERE tournee_jour_id= " . $condArr['tournee_source_jour_id']
                    . "  AND point_livraison_id IN ( " . implode(",", $condArr['tab_livraison_id']) . " )"
                    . "  AND flux_id = " . $condArr['flux_id']
                    . "  AND date_distrib >= '" . $condArr['date_distribution']
                    . "' ;";
            $stmt = $connection->executeQuery($q, $condArr);
            return $stmt->fetchAll();
        } else if ($base == 1) {
            $q = "SELECT DISTINCT abonne_soc_id FROM `client_a_servir_logist` "
                    . "WHERE tournee_jour_id= " . $condArr['tournee_destination_jour_id']
                    . "  AND point_livraison_id IN ( " . implode(",", $condArr['tab_dest_livraison_id']) . " )"
                    . "  AND flux_id = " . $condArr['flux_id']
                    . "  AND date_distrib >= '" . $condArr['date_distribution']
                    . "' ;";
            $stmt = $connection->executeQuery($q, $condArr);
            return $stmt->fetchAll();
        }
    }

    /**
     * [serachAbonnee recherche des abonne pour créer une remonté d'info]
     * @param  [type] $societeId [description]
     * @param  [type] $numaboExt [description]
     * @param  [type] $name      [description]
     * @param  [type] $commune   [description]
     * @param  [type] $depot     [description]
     * @param  [type] $tourneeId [description]
     * @return [type]            [description]
     */
    public function serachAbonnee($societeId, $numaboExt, $name, $commune, $depot, $tourneeId) {

        $subReq = "and adr.id is not null ";
        if (!empty($name)) {
            $subReq .= " and  cli_serv.vol1  LIKE '%" . $name . "%' ";
        }
        if (!empty($numaboExt)) {
            $subReq .= " and  abs.numabo_ext = '" . $numaboExt . "' ";
        }
        if (!empty($commune)) {
            $subReq .= " and  cm.libelle  LIKE '%" . $commune . "%' ";
        }
        if (!empty($societeId)) {
            $subReq .= " and  cli_serv.societe_id = $societeId ";
        }
        if (!empty($tourneeId)) {
            // $subReq .= " and  cli_serv.tournee_jour_id = $tourneeId ";
            $subReq .= " and  cli_serv.tournee_jour_id = $tourneeId ";
        }
        if (empty($numaboExt) && empty($name)) {
            $subReq .=" AND date_distrib BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()";
        }

        $sql = "SELECT distinct depot_id,
                  /*cli_serv.vol1 as name,
                  cli_serv.vol2 as raison_social,*/
                  abs.id as absId,
                  cli_serv.vol1 as name,
                  cli_serv.vol2 as raison_social,
                  adr.ville as ville,
                  adr.id as adr_id,
                  adr.cp as cp,
                  adr.vol3 as cplt_adr,
                  adr.vol4 as adresse,
                  adr.vol5 as lieut_dit,
                  cm.id as commune,
                  soc.libelle as societe,
                  soc.id as sId,
                  abs.numabo_ext as numaboExt
          FROM `client_a_servir_logist` as cli_serv 
              INNER JOIN `depot` dep ON dep.`id` = cli_serv.`depot_id` 
              INNER JOIN societe soc ON cli_serv.societe_id = soc.id
              INNER JOIN adresse adr ON cli_serv.adresse_id = adr.id AND CURDATE() between adr.date_debut AND adr.date_fin 
              INNER JOIN abonne_soc abs ON cli_serv.abonne_soc_id = abs.id
              INNER JOIN commune cm ON cli_serv.commune_id = cm.id
          
            WHERE cli_serv.depot_id = $depot ";
        $sql .= $subReq;
        $sql .="ORDER BY abs.numabo_ext ASC, adr.date_modif DESC limit 1";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * [getDistibutionsDate description]
     * @param  [type] $numaboExt [description]
     * @param  [type] $limit     [description]
     * @return [type]            [description]
     */
    public function getDistibutionsDate($abonne_soc_id, $limit) {
        $sql = "SELECT distinct cli_serv.date_distrib
                FROM `client_a_servir_logist` as cli_serv 
                LEFT JOIN abonne_soc abs ON cli_serv.abonne_soc_id = abs.id
                WHERE abs.id = $abonne_soc_id
                ORDER by cli_serv.date_distrib DESC
                LIMIT $limit";


        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * [getDistibutionsDate description]
     * @param  [type] $numaboExt [description]
     * @param  [type] $limit     [description]
     * @return [type]            [description]
     */
    public function getDistribDateByAbonneSoc($abonne_soc_id, $limit = 60) {
        $sql = "SELECT distinct date_distrib
                FROM `client_a_servir_logist` as cli_serv 
                INNER JOIN abonne_soc abs ON cli_serv.abonne_soc_id = abs.id
                WHERE abs.id = $abonne_soc_id
                AND date_distrib BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                ORDER by date_distrib DESC
                ";

        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * [getRecTourneeByDepot description]
     * @param  [type] $depot [description]
     * @return [type]        [description]
     */
    public function getRecTourneeByDepot($depot, $societeId = '', $startdate = '', $endDate, $filter) {

        $sql = "SELECT DISTINCT pt.id  , pt.code
                    FROM pai_tournee as pt 
                    WHERE pt.depot_id = $depot";

        if (!is_null($filter) && ($startdate && $endDate)) {
            $sql .= ($filter == false ) ? " AND date_distrib = '" . $startdate . "'" : " AND date_distrib BETWEEN '" . $startdate . "' AND '" . $endDate . "'";
            //$sql .=" AND date_distrib BETWEEN '".$startdate."' AND '".$endDate."'";
        } else {
            $sql .=" AND date_distrib BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()";
        }

        if ($filter == true) {
            $sql .=" AND pt.id IN (select distinct pai_tournee_id from crm_detail as crm where pai_tournee_id is not null and crm.depot_id = $depot AND date_debut between '" . $startdate . "' AND  '" . $endDate . "')";
        }
        $sql.= " ORDER BY  pt.code ASC";
        // echo $sql;die();
        //    var_dump($this->_em->getConnection()->fetchAll($sql));die();
        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * [getTourneeByDepot description]
     * @param  [type] $depot [description]
     * @return [type]        [description]
     */
    public function getTourneeByDepot($depot) {

        $sql = "SELECT DISTINCT mtj.id  , mtj.code
                FROM client_a_servir_logist casl
                RIGHT JOIN modele_tournee_jour mtj ON mtj.id  = casl.tournee_jour_id
                WHERE casl.depot_id = $depot
                AND date_distrib BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()
                ORDER BY  mtj.code ASC";
        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * [getTableCompteRenduReception description]
     * @param  [type] $date     [description]
     * @param  [type] $depot_id [description]
     * @return [type]           [description]
     */
    public function getTableCompteRenduReception($date, $depot_id, $flux_id, $isPCO = false) {
        $var = ($isPCO) ? $depot_id : 'c.depot_id';
        $sql = "SELECT distinct d.libelle as libelle_depot, 

               /* mtj.code as code_tournee,*/

                /*gt.libelle as libelle_tournee, */
                sum(qte) as qte_prevue,
                cptr.qte_recue as qte_recue,
                cptr.id as cptr_id ,
                cptr.non_modifiable,
                cptr.qte_prevue as cptr_qte_prevue,
                /*gt.id as groupe_tournee_id,*/
                c.tournee_jour_id as modele_tournee_jour_id, 
                DATE_FORMAT(cptr.heure_reception,'%H:%i') as heure_reception, 
                TRIM(cptr.commentaires) as commentaires,
                prd.libelle as libelle_produit,
                c.depot_id,
                fc.path as imgPath,
                prd.id  as produit_id,
                c.flux_id
               
            FROM 
                client_a_servir_logist as c
              
            INNER JOIN 
                depot AS d ON d.id = c.depot_id
            INNER JOIN 
                produit AS prd ON prd.id = c.produit_id
            LEFT JOIN 
                cptr_reception AS cptr ON cptr.depot_id = $var  
                AND cptr.product_id = c.produit_id  
                AND c.date_distrib = cptr.date_cpt_rendu
            LEFT JOIN 
                fichier AS fc ON fc.id = prd.image_id         
            WHERE
                c.date_distrib = '" . $date . "'";

        if (!empty($flux_id))
            $sql .= " AND c.flux_id = $flux_id ";

        if (!$isPCO)
            $sql .= " AND c.depot_id = $depot_id  ";

        $sql .= "GROUP BY  prd.id  ";




        ////// Correction de la requete ci-dessus. La requete ci-dessus met trop de temps pour PCO
        $sql = " SELECT
                        t1.libelle_depot,
                        t1.qte_prevue,
                        SUM(cptr.qte_recue) as qte_recue,
                        cptr.id as cptr_id ,
                        cptr.non_modifiable,
                        cptr.qte_prevue as cptr_qte_prevue,
                        DATE_FORMAT(cptr.heure_reception,'%H:%i') as heure_reception, 
                        TRIM(cptr.commentaires) as commentaires,
                        t1.libelle_produit,
                        t1.depot_id,
                        t1.imgPath,
                        t1.produit_id,
                        t1.flux_id
                    FROM
                        (
                        SELECT
                            d.libelle as libelle_depot,
                            sum(qte) as qte_prevue,
                            prd.libelle as libelle_produit,
                            c.depot_id,
                            fc.path as imgPath,
                            prd.id  as produit_id,
                            c.flux_id
                        FROM
                            client_a_servir_logist as c
                            INNER JOIN depot AS d ON d.id = c.depot_id " . (($isPCO) ? "" : " AND d.id = " . $depot_id) . " AND c.date_distrib = '" . $date . "' " . (!empty($flux_id) ? " AND c.flux_id = " . $flux_id : "") . " 
                            INNER JOIN produit AS prd ON prd.id = c.produit_id
                            LEFT JOIN fichier AS fc ON fc.id = prd.image_id
                        WHERE
                            1 = 1
                        GROUP BY
                            prd.id
                        ) t1
                        LEFT JOIN cptr_reception cptr ON cptr.date_cpt_rendu = '" . $date . "' AND cptr.depot_id = " . $depot_id . ((!$isPCO) ? " AND t1.depot_id = cptr.depot_id " : "") . " AND cptr.product_id = t1.produit_id
                    WHERE
                        1 = 1
                    GROUP BY 
                        t1.produit_id
                        ";

        //echo "<pre>";print_r($sql);echo "</pre>";

        return $this->_em->getConnection()->fetchAll($sql);
    }

    /**
     * [deleteByFicRecap description]
     * @param  AmsFichierBundleEntityFicRecap $ficRecap [description]
     * @return [type]                                   [description]
     */
    public function deleteByFicRecap(\Ams\FichierBundle\Entity\FicRecap $ficRecap) {
        $qb = $this->createQueryBuilder('cliServLogist');
        $qb->delete()
                ->where('cliServLogist.ficRecap = :ficRecap')
                ->setParameter(':ficRecap', $ficRecap);

        return $qb->getQuery()->getResult();
    }

    /**
     * [getByClientAServirSrc description]
     * @param  AmsDistributionBundleEntityClientAServirSrc $clientAServirSrc [description]
     * @return [type]                                                        [description]
     */
    public function getByClientAServirSrc(\Ams\DistributionBundle\Entity\ClientAServirSrc $clientAServirSrc) {
        $qb = $this->createQueryBuilder('cliServLogist');
        $qb->where('cliServLogist.clientAServirSrc = :clientAServirSrc')
                ->setParameter(':clientAServirSrc', $clientAServirSrc);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param interger $iCaslId
     * @return array(coordinate)
     */
    public function getCoordinateByIdCasl($iCaslId) {

        $connection = $this->getEntityManager()->getConnection();
        $query = "
	    	SELECT geox,geoy FROM client_a_servir_logist casl
				LEFT JOIN  adresse_rnvp a_rnvp ON casl.rnvp_id = a_rnvp.id 
				WHERE casl.id = $iCaslId
    	";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetch();
    }

    /**
     * [getCaslOrdreByIdTourneeDetail description]
     * @param  [type] $itourneeDetailId [description]
     * @param  [type] $date             [description]
     * @return [type]                   [description]
     */
    public function getCaslOrdreByIdTourneeDetail($itourneeDetailId, $date) {

        $connection = $this->getEntityManager()->getConnection();
        $query = "
				SELECT point_livraison_ordre FROM tournee_detail td
				LEFT JOIN client_a_servir_logist casl ON casl.abonne_soc_id = td.num_abonne_id
				WHERE td.id = $itourneeDetailId";
        if ($date)
            $query = " AND date_distrib = '$date' ";

        $stmt = $connection->executeQuery($query);
        $result = $stmt->fetch();
        if ($result['point_livraison_ordre'])
            return $result;
        else {
            $query = "
				SELECT ordre as point_livraison_ordre FROM tournee_detail td
				WHERE td.id = $itourneeDetailId";
            $stmt = $connection->executeQuery($query);
            return $stmt->fetch();
        }
    }

    /**
     * Méthode qui décale l'ordre des points dans une tournée à partir d'une date donnée
     * @param int $iOrdreDepart L'ordre de la tournée à partir duquel les points de livraison sont décalés
     * @param int $iTourneeJourId L'ID de la tournée impactée
     * @param string $sDateDepart La date à partir de laquel la modification est valable
     * @param string $sOperateur + ou - pour l'incrémentation ou la décrémentation (+ par défaut)
     * @param int $iPas L'ampleur du décalage (1 par défaut)
     */
    public function decalerOrdrePoints($iOrdreDepart, $iTourneeJourId, $sDateDepart, $sOperateur = '+', $iPas = 1) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
	    	UPDATE client_a_servir_logist
	    	SET point_livraison_ordre = point_livraison_ordre $sOperateur $iPas
	    	WHERE tournee_jour_id = $iTourneeJourId
	    	AND point_livraison_ordre >= $iOrdreDepart
                AND date_distrib >='$sDateDepart'
	    	";

        return $this->_em->getConnection()->prepare($query)->execute();
    }

    /**
     * Méthode qui retourne la liste des points d'une tournée, dans l'ordre
     * @param int $iTourneeJourId L'ID de la tournée impactée
     * @param string $sDate La date de la tournée
     */
    public function listerPointsTourneeOrdonnes($iTourneeJourId, $sDate) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT (@cnt := @cnt + 1) AS nouvel_ordre, t.*
		FROM
		(SELECT
		casl.id as casl_id, casl.point_livraison_id, casl.abonne_soc_id,  CONCAT(TRUNCATE(adresse_rnvp.geox,5), '|', TRUNCATE(adresse_rnvp.geoy,5)) AS coords
		FROM client_a_servir_logist AS casl
		LEFT JOIN adresse_rnvp ON casl.point_livraison_id = adresse_rnvp.id
		WHERE tournee_jour_id = $iTourneeJourId
		AND (adresse_rnvp.geox IS NOT NULL AND adresse_rnvp.geoy IS NOT NULL AND adresse_rnvp.geox <> adresse_rnvp.geoy AND adresse_rnvp.geox > 0 AND adresse_rnvp.geoy > 0)
		AND date_distrib = '$sDate'
		AND point_livraison_id IS NOT NULL
		GROUP BY casl.point_livraison_id
		ORDER BY casl.point_livraison_ordre ASC) t
		CROSS JOIN (SELECT @cnt := 0) AS dummy;
            ";

        return $this->_em->getConnection()->fetchAll($query);
    }

    /**
     * Méthode de création de table temporaire
     * @param string $sNomTable Une chaine de caractères à utiliser pour créer le nom de la table (sert de base, ne sera pas utilisé tel quel, voir valeur de retour)
     * @param array $aChampsSyntaxe Les éléments de syntaxe de création de la table
     * @param array $aValeurs Le tableau de valeurs à intégrer
     * @return array $aRetour Un tableau contenant le nom de la nouvelle table et la sortie de l'exécution
     */
    public function creerTableTemp($sNomTable, $aChampsSyntaxe, $aValeurs = null) {
        $aRetour = array(
            'creation' => false,
            'insertion' => false,
        );
        $connection = $this->getEntityManager()->getConnection();
        $aRetour['nom_table'] = $table_name = uniqid('tmp_memory_') . date('Ymd') . '_' . $sNomTable;

        // Création de la table
        $query = "
            CREATE TABLE `$table_name` (";
        if (!empty($aChampsSyntaxe)) {
            foreach ($aChampsSyntaxe as $index => $sSyntaxe) {
                if ($index > 0) {
                    $query .= ', ';
                }
                $query .= $sSyntaxe;
            }
        }
        $query .= ") ENGINE = MEMORY
            ";
        $aRetour['creation'] = $bCreateTable = $this->_em->getConnection()->prepare($query)->execute();
        $aRetour['nb_inserts'] = count($aValeurs);

        // Insertion des valeurs
        if (!empty($aValeurs)) {
            $aCols = array_keys($aValeurs[0]);

            foreach ($aValeurs as $index => $valeur) {
                if ($index == 0) {
                    $queryIns = "INSERT INTO `" . $table_name . "` (" . implode(',', $aCols) . ") VALUES";
                }
                $valeurs = array_values($valeur);
                $queryIns .= "(" . implode(',', $valeurs) . ")";
                $queryIns .= $index < count($aValeurs) - 1 ? ',' : '';
            }
            $aRetour['insertion'] = $bInsert = $this->_em->getConnection()->prepare($queryIns)->execute();
        }

        return $aRetour;
    }

    /**
     * Méthode qui permet de redresser l'ordre d'une CASL pour éviter les trous et n'avoir que des points consécutifs
     * Le pré-requis est que l'ordre fixé précédemment respecte l'ordre du modèle de tournée visible dans TD
     * @param string $sNomTable Le nom de la table temporaire qui contient les points de livraison dans l'ordre (cf CartoController::trouverOrdrePointsCASL)
     * @param string $sDateDistrib La date à partir de laquelle le changement est pris en compte
     * @param int $iTourneeId L'ID de la tournée à impacter
     * @return bool L'état de l'opération
     */
    public function redresserOrdresurTourneeRelle($sNomTable, $sDateDistrib, $iTourneeId) {
        if ($sNomTable == 'client_a_servir_logist') {
            return false;
        } // :)

        $aRetour = array();

        $connection = $this->getEntityManager()->getConnection();
        $query = "
                UPDATE client_a_servir_logist AS casl 
                INNER JOIN `$sNomTable` AS tmp_table ON casl.point_livraison_id = tmp_table.point_livraison_id
                SET casl.point_livraison_ordre = tmp_table.nouvel_ordre
                WHERE 
                date_distrib >= '$sDateDistrib'
                AND tournee_jour_id = $iTourneeId;
                ;
                ";

        $aRetour['update'] = $this->_em->getConnection()->prepare($query)->execute();

        // Suppression de la table temporaire
        if ($aRetour['update']) {
            $dropQuery = "DROP TABLE `$sNomTable`;";
        }
        $aRetour['drop'] = $this->_em->getConnection()->prepare($dropQuery)->execute();

        return $aRetour;
    }

    /**
     * Modifie les enregistrements de CASL dans le cadre d'une application d'optimisation de tournée.
     * @param type $sNomTable Nom de la table temporaire
     * @return boolean
     */
    public function updateCaslOptim($sNomTable) {
        if ($sNomTable == 'client_a_servir_logist') {
            return false;
        } // :)

        $aRetour = array();
        $query = "
                UPDATE 
                    client_a_servir_logist AS casl 
                INNER JOIN 
                    `$sNomTable` AS tmp_table ON casl.abonne_soc_id = tmp_table.abonne_soc_id
                    -- AND tmp_table.point_livraison_id = casl.point_livraison_id
                	AND tmp_table.produit_id = casl.produit_id
                	AND DAYOFWEEK(casl.date_distrib) = tmp_table.jour_id
                	AND casl.date_distrib >= tmp_table.date_application
                SET 
                    casl.point_livraison_ordre = tmp_table.ordre,
                    casl.tournee_jour_id = tmp_table.tournee_jour_id
                ";

        $aRetour['update'] = $this->_em->getConnection()->prepare($query)->execute();

        // Suppression de la table temporaire
        if ($aRetour['update']) {
            $dropQuery = "DROP TABLE `$sNomTable`;"; // Suppression de la table commentée pour investigation à partir du 14/09/15
        }
        $aRetour['drop'] = $this->_em->getConnection()->prepare($dropQuery)->execute();
        return $aRetour;
    }

    /**
     * Méthode qui retourne la liste des codes de tournées jour pour un jour donné
     */
    public function getTourneesDuJour($sDate) {
        $conditionsArr = array(
            'date' => $sDate
        );
        $connection = $this->getEntityManager()->getConnection();
        $q = "SELECT DISTINCT casl.tournee_jour_id, modele_tournee_jour.code FROM client_a_servir_logist as casl
	LEFT JOIN modele_tournee_jour ON casl.tournee_jour_id = modele_tournee_jour.id
		WHERE date_distrib = :date 
		AND casl.tournee_jour_id IS NOT NULL
		ORDER BY tournee_jour_id ASC;";

        $stmt = $connection->executeQuery($q, $conditionsArr);
        return $stmt->fetchAll();
    }

    public function getCodeDepotByPointLivraison($point_livraison) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "SELECT d.code FROM client_a_servir_logist casl " .
                "LEFT JOIN depot d ON casl.depot_id =  d.id " .
                "WHERE point_livraison_id = $point_livraison " .
                "LIMIT 1; ";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetch();
    }

    public function getDataBordereau($point_livraison, $code_tournee, $date_distrib, $flux) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "  SELECT arnvp.adresse,arnvp.cp,arnvp.ville,d.code as depot_code,d.libelle as depot_libelle,mtj.code as tournee,j.libelle as jour,casl.vol2,casl.vol1,p.libelle as libelle_produit,asoc.numabo_ext,casl.qte,
            (   SELECT sum(casl_1.qte) 
                FROM client_a_servir_logist casl_1 
                WHERE point_livraison_id = $point_livraison 
                AND date_distrib = '$date_distrib' 
                AND casl_1.flux_id = $flux
            ) as qte_total,f.path 
            FROM client_a_servir_logist casl
            LEFT JOIN produit p ON casl.produit_id =  p.id
            LEFT JOIN adresse_rnvp arnvp ON arnvp.id = casl.point_livraison_id
            LEFT JOIN abonne_soc asoc ON asoc.id = casl.abonne_soc_id
            LEFT JOIN modele_tournee_jour mtj ON casl.tournee_jour_id = mtj.id
            LEFT JOIN ref_jour j ON j.id = mtj.jour_id
            LEFT JOIN fichier f ON f.id = p.image_id
            LEFT JOIN depot d ON d.id = casl.depot_id
            WHERE point_livraison_id = $point_livraison
            AND mtj.code = '$code_tournee'
            AND casl.flux_id = $flux 
            AND date_distrib = '$date_distrib'
            ORDER BY tournee ASC";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function getDataDispatchTournee($depot_code, $date_distrib, $flux) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
        SELECT casl.tournee_jour_id,p.id as id_produit,p.libelle as product_libelle,mtj.code as libelle_tournee,casl.qte
        FROM client_a_servir_logist casl
        LEFT JOIN produit p ON p.id = casl.produit_id
        LEFT JOIN depot d ON d.id = casl.depot_id
        LEFT JOIN modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id
        WHERE date_distrib = '$date_distrib'
        AND casl.tournee_jour_id is not null
        AND d.code = $depot_code
        AND casl.flux_id = $flux 
        ORDER BY p.id,mtj.code
        ";



        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function getCountDispatchTourneeProduct($depot_code, $date_distrib, $produit, $tourneeJour = false) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
        SELECT sum(casl.qte) as quantite
        FROM client_a_servir_logist casl
        LEFT JOIN produit p ON p.id = casl.produit_id
        LEFT JOIN depot d ON d.id = casl.depot_id
        LEFT JOIN modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id
        WHERE date_distrib = '$date_distrib'
        AND casl.tournee_jour_id is not null
        AND d.code = $depot_code
        AND p.id = $produit ";

        if ($tourneeJour)
            $q.="AND tournee_jour_id = $tourneeJour ";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    /** RETOURNE LES TOURNEES PAR DEPOT POUR UN JOUR DONNER * */
    public function getTourneeByDepotByDate($depot, $dateDistrib, $flux) {
        $query = "SELECT mtj.id,mtj.code FROM client_a_servir_logist casl " .
                "LEFT JOIN modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id " .
                "WHERE depot_id = $depot " .
                "AND date_distrib = '$dateDistrib' " .
                "AND flux_id = $flux " .
                "AND tournee_jour_id is not null " .
                "GROUP BY tournee_jour_id " .
                "ORDER BY mtj.code ASC"
        ;
        return $this->_em->getConnection()->fetchAll($query);
    }

    public function getProductByDepotByDate($depot, $dateDistrib, $flux) {
        $query = "SELECT p.libelle,produit_id FROM client_a_servir_logist casl " .
                "LEFT JOIN produit p ON p.id = casl.produit_id " .
                "WHERE depot_id = $depot " .
                "AND date_distrib = '$dateDistrib' " .
                "AND casl.flux_id = $flux " .
                "AND tournee_jour_id is not null " .
                "GROUP BY produit_id " .
                "ORDER BY p.libelle ASC "
        ;
        return $this->_em->getConnection()->fetchAll($query);
    }

    /** RETOURNE LES TOURNEES PAR DEPOT POUR UN JOUR DONNER * */
    public function getTourneesByDepotByDate($depot, $dateDistrib, $flux) {
        $query = "SELECT mtj.id, substring(mtj.code,4,6 ) as code FROM client_a_servir_logist casl " .
                "LEFT JOIN modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id " .
                "WHERE depot_id = $depot " .
                "AND date_distrib = '$dateDistrib' " .
                "AND flux_id = $flux " .
                "AND tournee_jour_id is not null " .
                "GROUP BY tournee_jour_id " .
                "ORDER BY mtj.code "
        ;
        return $this->_em->getConnection()->fetchAll($query);
    }

    /**
     * Method qui affiche la requete stockée dans le champs "requete" de la table "requete_export"
     * @param string $stmt
     * @return array point_livraison_id
     */
    public function fetchRequeteExport($stmt) {
        return $this->_em->getConnection()->fetchAll($stmt);
    }

    /**
     * Suppression des lignes de tournee_detail avec incoherence flux/depot. 
     * On exclut les tournées hors presse (table tournee_interdite)
     * @param array $datesDistrib tableau de dates en \Datetime 
     * @param integer $flux_id
     * @throws \Doctrine\DBAL\DBALException
     */
    public function supprIncoherenceTournee($datesDistrib = array(), $flux_id = 1) {
        try {
            //print_r($datesDistrib);
            foreach ($datesDistrib as $date) {
                $sDateATraiter = $date->format("Y-m-d");
                $aTdIdASuppr = array();
                $aCslIdAMaj = array();

                $sSlctIdASuppr = " SELECT DISTINCT 
                                        td.id AS td_id, csl.id AS csl_id 
                                    FROM
                                        client_a_servir_logist csl
                                        INNER JOIN modele_tournee_jour mtj ON csl.tournee_jour_id = mtj.id
                                        INNER JOIN tournee_detail td ON csl.abonne_soc_id = td.num_abonne_id AND td.jour_id = CAST(DATE_FORMAT(csl.date_distrib, '%w') AS SIGNED)+1 
                                        INNER JOIN depot d ON csl.depot_id = d.id
                                        INNER JOIN ref_flux f ON csl.flux_id = f.id 
                                        LEFT JOIN tournee_interdite ti on mtj.code = ti.code
                                    WHERE
                                        1 = 1
                                        AND csl.date_distrib IN ('" . $sDateATraiter . "')
                                        AND csl.flux_id = " . $flux_id . "
                                        AND (csl.flux_id <> td.flux_id OR mtj.code NOT LIKE CONCAT(d.code, f.code, '%') OR td.flux_id IS NULL)
                                        AND ti.code IS NULL
                                 ";
                $rSlctIdASuppr = $this->_em->getConnection()->fetchAll($sSlctIdASuppr);
                //print_r($sSlctIdASuppr);
                foreach ($rSlctIdASuppr as $aArr) {
                    $aTdIdASuppr[] = $aArr['td_id'];
                    $aCslIdAMaj[] = $aArr['csl_id'];
                }
                //print_r($aTdIdASuppr);
                //print_r($aCslIdAMaj);

                if (!empty($aTdIdASuppr)) {
                    $aTdIdASupprTmp = array();
                    $iNbASuppr = 100;
                    $iNb = 0;
                    foreach ($aTdIdASuppr as $iIdASuppr) {
                        $aTdIdASupprTmp[] = $iIdASuppr;
                        $iNb++;
                        if (!empty($aTdIdASupprTmp) && ($iNb % $iNbASuppr == 0)) {
                            $sDelete = " DELETE FROM tournee_detail WHERE id IN (" . implode(', ', $aTdIdASupprTmp) . ") ";
                            //echo "$sDelete\n";
                            $this->_em->getConnection()->executeQuery($sDelete);
                            $this->_em->clear();
                            $aTdIdASupprTmp = array();
                            $iNb = 0;
                        }
                    }
                    if (!empty($aTdIdASupprTmp)) {
                        $sDelete = " DELETE FROM tournee_detail WHERE id IN (" . implode(', ', $aTdIdASupprTmp) . ") ";
                        //echo "$sDelete\n";
                        $this->_em->getConnection()->executeQuery($sDelete);
                        $this->_em->clear();
                    }
                }

                if (!empty($aCslIdAMaj)) {
                    $aCslIdAMajTmp = array();
                    $iNbAMaj = 100;
                    $iNb = 0;
                    foreach ($aCslIdAMaj as $iIdASuppr) {
                        $aCslIdAMajTmp[] = $iIdASuppr;
                        $iNb++;
                        if (!empty($aCslIdAMajTmp) && ($iNb % $iNbAMaj == 0)) {
                            $sUpdate = " UPDATE client_a_servir_logist SET tournee_jour_id=NULL WHERE id IN (" . implode(', ', $aCslIdAMajTmp) . ") ";
                            //echo "$sUpdate\n";
                            $this->_em->getConnection()->executeQuery($sUpdate);
                            $this->_em->clear();
                            $aCslIdAMajTmp = array();
                            $iNb = 0;
                        }
                    }
                    if (!empty($aCslIdAMajTmp)) {
                        $sUpdate = " UPDATE client_a_servir_logist SET tournee_jour_id=NULL WHERE id IN (" . implode(', ', $aCslIdAMajTmp) . ") ";
                        //echo "$sUpdate\n";
                        $this->_em->getConnection()->executeQuery($sUpdate);
                        $this->_em->clear();
                    }
                }
            }
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Mise a jour tournee
     * @param array $datesDistrib
     * @param integer $flux_id
     * @param string $sAMettreAJour
     * @throws \Doctrine\DBAL\DBALException
     */
    public function miseAJourTournee($datesDistrib = array(), $flux_id = 1, $sAMettreAJour = 'tournee_NULL') {
        try {
            $dates = array();
            foreach ($datesDistrib as $date) {
                $dates[] = $date->format("Y-m-d");
            }
            // Traitement des tournees des clients a servir
            $update = " UPDATE
                            client_a_servir_logist csl
                            LEFT JOIN tournee_detail td ON csl.date_distrib IN ('" . implode("', '", $dates) . "') AND csl.abonne_soc_id = td.num_abonne_id AND td.jour_id = CAST(DATE_FORMAT(csl.date_distrib, '%w') AS SIGNED)+1 AND csl.flux_id = td.flux_id
                            LEFT JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND csl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                            LEFT JOIN modele_tournee mt ON mtj.tournee_id = mt.id AND mt.actif = 1
                        SET
                            csl.tournee_jour_id = mtj.id
                            , csl.point_livraison_ordre = td.ordre
                        WHERE
                            1 = 1
                            AND csl.date_distrib IN ('" . implode("', '", $dates) . "')
                            AND csl.flux_id = " . $flux_id . " ";
            if ($sAMettreAJour == 'tournee_NULL') {
                $update .= "   AND (csl.tournee_jour_id IS NULL OR csl.point_livraison_ordre IS NULL OR csl.point_livraison_ordre = 0) ";
            }

            $update .= "   AND td.modele_tournee_jour_code IS NOT NULL
                            AND mtj.code IS NOT NULL
                            AND csl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                            AND mt.actif = 1
                     ";
            $this->_em->getConnection()->prepare($update)->execute();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    public function miseAJourChangeTournee($refJour, $abonneSocId) {
        try {
            $update = " UPDATE
                            client_a_servir_logist csl
                            LEFT JOIN tournee_detail td ON csl.date_distrib > CURDATE()  
                                AND csl.abonne_soc_id = $abonneSocId 
                                AND csl.abonne_soc_id = td.num_abonne_id 
                                AND td.jour_id = CAST(DATE_FORMAT(csl.date_distrib, '%w') AS SIGNED)+1 
                                AND td.jour_id = $refJour  
                            LEFT JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code 
                                AND csl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                        SET
                            csl.tournee_jour_id = mtj.id
                            , csl.point_livraison_ordre = td.ordre
                        WHERE
                            1 = 1
                            AND td.modele_tournee_jour_code IS NOT NULL
                            AND mtj.code IS NOT NULL
                     ";
            $this->_em->getConnection()->prepare($update)->execute();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Les clients a servir non classes, a classer selon les reperage
     * @param integer $iNbMaxATraiter
     * @param array $datesDistrib tableau de dates en \Datetime 
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function clientsAServirAClasserSelonReper($iNbMaxATraiter = 100, $datesDistrib = array(), $flux_id = false) {
        try {
            $dates = array();
            $iJMin = '90';
            $iJMax = '90';
            foreach ($datesDistrib as $date) {
                $dates[] = $date->format("Y-m-d");
            }
            $sSlct = " SELECT 
                        DATE_FORMAT(csl.date_distrib, '%w')+1 AS id_jour
                        , MAX(c.insee) AS insee, csl.abonne_soc_id, pt_l_r.geox, pt_l_r.geoy, csl.point_livraison_id
                        , p.flux_id, a.numabo_ext, p.soc_code_ext, p.prd_code_ext, csl.depot_id
                        , CONCAT(mt.code, rj.code) AS tournee_jour_code
                        , 'Class. a partir des tournees de reperage' AS source_modification
                    FROM
                        client_a_servir_logist csl
                        LEFT JOIN abonne_soc a ON csl.abonne_soc_id = a.id AND csl.date_distrib IN ('" . implode("', '", $dates) . "') 
                        LEFT JOIN ref_jour rj ON DATE_FORMAT(csl.date_distrib, '%w')+1 = rj.id
                        LEFT JOIN reperage r ON csl.abonne_soc_id = r.abonne_soc_id AND csl.date_distrib BETWEEN DATE_SUB(r.date_demar, INTERVAL " . $iJMin . " DAY) AND DATE_ADD(r.date_demar, INTERVAL " . $iJMax . " DAY) 
                        LEFT JOIN modele_tournee mt ON r.tournee_id = mt.id AND mt.actif = 1
                        LEFT JOIN produit p ON csl.produit_id = p.id
                        LEFT jOIN commune c ON csl.commune_id = c.id

                        LEFT JOIN adresse_rnvp csl_ar ON csl.rnvp_id = csl_ar.id
                        LEFT JOIN adresse_rnvp csl_r ON r.rnvp_id = csl_r.id
                        LEFT JOIN adresse_rnvp pt_l_r ON csl.point_livraison_id = pt_l_r.id
                    WHERE
                        1 = 1
                        AND csl.date_distrib IN ('" . implode("', '", $dates) . "') 
                        AND (csl.tournee_jour_id IS NULL) AND csl.point_livraison_id IS NOT NULL
                        
                        /* On ne prend la tournee du reperage que si l adresse est pareille que celle de CASL */
                        AND csl_ar.adresse IS NOT NULL AND csl_ar.insee IS NOT NULL AND csl_r.adresse IS NOT NULL AND csl_r.insee IS NOT NULL
                        AND csl_ar.adresse=csl_r.adresse AND csl_ar.insee=csl_r.insee
						/* On ne prend que les reperages repondus*/
						AND r.topage IS NOT NULL
                        ";
            if ($flux_id)
                $sSlct .= " AND csl.flux_id = " . $flux_id . " ";

            $sSlct .= "
                    GROUP BY
                        DATE_FORMAT(csl.date_distrib, '%w')+1, c.insee, csl.abonne_soc_id, p.flux_id, a.numabo_ext, p.soc_code_ext
                    ORDER BY 
                        csl.abonne_soc_id
                        LIMIT 0, $iNbMaxATraiter
                        ";
            return $this->_em->getConnection()->executeQuery($sSlct)->fetchAll();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Les clients a servir non classes pour ce jour distrib mais classes pour d'autres jours
     * @param integer $iNbMaxATraiter
     * @param array $datesDistrib tableau de dates en \Datetime 
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function clientsAServirNonClassesLeJDistrib($iNbMaxATraiter = 100, $datesDistrib = array(), $flux_id = false) {
        try {
            $dates = array();
            foreach ($datesDistrib as $date) {
                $dates[] = $date->format("Y-m-d");
            }
            $sSlct = " SELECT 
                        DATE_FORMAT(csl.date_distrib, '%w')+1 AS id_jour
                        , MAX(c.insee) AS insee, csl.abonne_soc_id, ar.geox, ar.geoy, csl.point_livraison_id
                        , p.flux_id, a.numabo_ext, p.soc_code_ext, p.prd_code_ext, csl.depot_id, MAX(td.jour_id)
                        , td.modele_tournee_jour_code
                        , LEFT(td.modele_tournee_jour_code, LENGTH(td.modele_tournee_jour_code)-2) AS tournee_jour_code
                        , 'Class. a partir de la tournee des autres jours' AS source_modification
                    FROM
                        client_a_servir_logist csl
                        LEFT JOIN abonne_soc a ON csl.abonne_soc_id = a.id AND csl.date_distrib IN ('" . implode("', '", $dates) . "') 
                        LEFT JOIN adresse_rnvp ar ON csl.point_livraison_id = ar.id
                        LEFT JOIN produit p ON csl.produit_id = p.id
                        LEFT JOIN commune c ON csl.commune_id = c.id
                        LEFT JOIN tournee_detail td ON csl.abonne_soc_id = td.num_abonne_id AND csl.flux_id = td.flux_id 
                                                    AND DATE_FORMAT(csl.date_distrib, '%w')+1 <> td.jour_id /** recupere la tournee de cet abonne les autres jours **/
                                                    AND td.jour_id <> 1 
                        LEFT JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND csl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                    WHERE
                        1 = 1
                        AND csl.date_distrib IN ('" . implode("', '", $dates) . "') 
                        AND (csl.tournee_jour_id IS NULL OR csl.point_livraison_ordre IS NULL OR csl.point_livraison_ordre = 0)
                        AND ar.id IS NOT NULL
                        AND p.id IS NOT NULL
                        AND c.id IS NOT NULL
                        AND csl.depot_id IS NOT NULL
                        AND ar.geox IS NOT NULL AND ar.geoy IS NOT NULL

                        AND td.modele_tournee_jour_code IS NOT NULL
                        AND mtj.code IS NOT NULL
                        ";
            if ($flux_id)
                $sSlct .= " AND csl.flux_id = " . $flux_id . " ";

            $sSlct .= "
                    GROUP BY
                        DATE_FORMAT(csl.date_distrib, '%w')+1, c.insee, csl.abonne_soc_id, p.flux_id, a.numabo_ext, p.soc_code_ext
                    ORDER BY 
                        csl.date_distrib, csl.abonne_soc_id
                        LIMIT 0, $iNbMaxATraiter
                        ";
            return $this->_em->getConnection()->executeQuery($sSlct)->fetchAll();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Les clients a servir non classes
     * @param integer $iNbMaxATraiter
     * @param array $datesDistrib tableau de dates en \Datetime 
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function clientsAServirNonClasses($iNbMaxATraiter = 100, $datesDistrib = array(), $flux_id = false) {
        try {
            $dates = array();
            foreach ($datesDistrib as $date) {
                $dates[] = $date->format("Y-m-d");
            }
            $sSlct = " SELECT DISTINCT 
                            DATE_FORMAT(csl.date_distrib, '%Y/%m/%d') AS jour, DATE_FORMAT(csl.date_distrib, '%w')+1 AS id_jour, c.insee, csl.abonne_soc_id, ar.geox, ar.geoy, csl.point_livraison_id, p.flux_id, a.numabo_ext, p.soc_code_ext, p.prd_code_ext, csl.depot_id
                            , IF(mtj.code IS NULL, '', mtj.code) AS tournee_jour_code
                        FROM 
                            client_a_servir_logist csl
                            LEFT JOIN abonne_soc a ON csl.abonne_soc_id = a.id AND csl.date_distrib IN ('" . implode("', '", $dates) . "')
                            LEFT JOIN adresse_rnvp ar ON csl.point_livraison_id = ar.id
                            LEFT JOIN produit p ON csl.produit_id = p.id
                            LEFT JOIN commune c ON csl.commune_id = c.id
                            LEFT JOIN modele_tournee_jour mtj ON mtj.id = csl.tournee_jour_id
                        WHERE
                            1 = 1
                            ";
            $sSlct .= " AND csl.date_distrib IN ('" . implode("', '", $dates) . "') ";
            $sSlct .= " 
                            AND (csl.tournee_jour_id IS NULL OR csl.point_livraison_ordre IS NULL OR csl.point_livraison_ordre = 0)
                            AND ar.id IS NOT NULL
                            AND p.id IS NOT NULL
                            AND c.id IS NOT NULL
                            AND csl.depot_id IS NOT NULL
                            AND ar.geox IS NOT NULL AND ar.geoy IS NOT NULL ";
            if ($flux_id)
                $sSlct .= " AND csl.flux_id = $flux_id ";
            $sSlct .= " 
                        ORDER BY jour, csl.abonne_soc_id
                        LIMIT 0, $iNbMaxATraiter
                        ";
            //print_r($sSlct);
            //$sSlct  .= " AND c.insee='75115' ";
            //$sSlct  .= " AND csl.numabo_ext='65542' ";
            return $this->_em->getConnection()->executeQuery($sSlct)->fetchAll();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * USE : COMPTE RENDU CAMION
     * @param $date
     * @return : quantité,nom,image de produit prévue pour chaque produit
     */
    public function fetchAllByDate($date, $flux = false) {
        $query = '
            SELECT casl.id as casl_id,p.id as id_produit,f.path,p.libelle,sum(qte) as qte,heure_reception,cptr.qte_recue,cptr.commentaires
            FROM client_a_servir_logist casl
            LEFT JOIN produit p ON p.id = casl.produit_id
            LEFT JOIN fichier f ON f.id = p.image_id
            LEFT JOIN cptr_reception_camion cptr ON cptr.id_casl = casl.id
            WHERE casl.date_distrib = "' . $date . '"';
        if ($flux)
            $query .= ' AND casl.flux_id = ' . $flux;
        $query .= ' GROUP BY casl.produit_id,casl.flux_id';

        return $this->_em->getConnection()->executeQuery($query)->fetchAll();
    }

    /**
     * Méthode qui retourne les enregistrements à intégrer dans le récapitulatif liant la paye à la distribution
     * @return array : Nb_client_Abo  Nb_Ex_Abo  Nb_Diff  Nb_clients_DIV  Nb_Ex_DIV  Nb_ex_en_suppléments  Nb_adresses  Etalon  nb_Heure  nb_km     nombre_reclam_brut  nombre_reclam_net  nombre_reclam_Div_brut  nombre_reclam_Div_Net  code_tournee  depot   date_distrib  pai_tournee_id  pai_tournee_id  flux_id
     */
    public function getPaieRecapInfo($oDateDebut, $oDateCourante) {
        /* @var $oDateDebut \Ams\PaieBundle\Entity\PaiRefMois */
        /* @var $oDateCourante \Datetime */


        $sql = "SELECT 
            Nb_clients_Abo Nb_client_Abo,
            Nb_Ex_Abo Nb_Ex_Abo,
            Nb_Diff Nb_Diff,
            Nb_clients_DIV Nb_clients_DIV,
            Nb_Ex_DIV Nb_Ex_DIV,
            Nb_ex_en_supplements Nb_ex_en_supplements,
            Nb_adresses Nb_adresses,
            AVG(pt.valrem_logistique) Etalon,
            SUBTIME(pt.duree, IFNULL(pt.duree_reperage, cast('00:00:00' as time))) nb_Heure,
            AVG(pt.nbkm_paye) nb_km,
            nombre_reclam_brut nombre_reclam_brut,
            nombre_reclam_net nombre_reclam_net,
            nombre_reclam_div_brut nombre_reclam_Div_brut,
            nombre_reclam_div_Net nombre_reclam_Div_Net,
            pt.code code_tournee,
            d.code depot,
            a.date_distrib,
            pt.flux_id
            FROM
                        pai_tournee pt,
                        modele_tournee_jour mtj,
                        depot d,
                        (SELECT 
                                SUM(CASE WHEN p.soc_code_ext = 'LP' AND ppt.natureclient_id = 0 AND type_id = 1 THEN ppt.nbcli ELSE 0 END) AS Nb_clients_Abo,
                                SUM(CASE WHEN p.soc_code_ext = 'LP' AND ppt.natureclient_id = 0 AND p.type_id = 1 THEN ppt.qte ELSE 0 END) AS Nb_Ex_Abo,
                                SUM(CASE WHEN  p.soc_code_ext != 'LP' AND ppt.natureclient_id = 1 AND p.type_id = 1 THEN ppt.nbcli ELSE 0 END) AS Nb_Diff,
                                SUM(CASE WHEN  p.soc_code_ext != 'LP' AND ppt.natureclient_id = 0 AND p.type_id = 1 THEN ppt.nbcli ELSE 0 END) AS Nb_clients_DIV,
                                SUM(CASE WHEN  p.soc_code_ext != 'LP' AND ppt.natureclient_id = 0 AND p.type_id = 1 THEN ppt.qte ELSE 0 END) AS Nb_Ex_DIV,
                                CASE WHEN  pt.nbspl != 0 THEN pt.nbcli ELSE 0 END AS Nb_clients_en_supplements,
                                CASE WHEN  pt.nbspl != 0 THEN pt.nbspl	ELSE 0 END AS Nb_ex_en_supplements,
                                pt.nbadr Nb_adresses,
                                pt.date_distrib,
                                pt.depot_id,
                                pt.id,
                                pt.flux_id
                        FROM pai_tournee pt
                        LEFT JOIN pai_prd_tournee ppt ON ppt.tournee_id = pt.id
                        LEFT JOIN produit p ON ppt.produit_id = p.id
                        WHERE  pt.date_distrib BETWEEN '" . $oDateDebut->format("Y-m-d") . "' AND '" . $oDateCourante->format("Y-m-d") . "'
                        GROUP BY pt.date_distrib , pt.id , pt.flux_id , pt.depot_id
                        ) a

            LEFT OUTER JOIN
                        ( SELECT 
                                COUNT( CASE WHEN a.soc_code_ext = 'LP'  THEN 1 ELSE NULL END) AS nombre_reclam_brut,
                                COUNT( CASE WHEN a.soc_code_ext = 'LP' AND  imputation_paie = 1 THEN 1 ELSE NULL END) AS nombre_reclam_net,
                                COUNT( CASE WHEN a.soc_code_ext != 'LP'  THEN 1 ELSE NULL END) AS nombre_reclam_Div_brut,
                                COUNT( CASE WHEN a.soc_code_ext != 'LP' AND imputation_paie = 1 THEN 1 ELSE NULL END) AS nombre_reclam_Div_Net,
                                DATE_FORMAT(date_imputation_paie, '%Y-%m-%d') date_imputation_paie,
                                a.depot_id,
                                pt.id,
                                pt.flux_id
                            FROM crm_detail a
                            INNER JOIN crm_demande b ON a.crm_demande_id = b.id 
                            INNER  JOIN pai_tournee pt ON a.pai_tournee_id = pt.id
                            WHERE
                                b.crm_categorie_id = 1
                                AND pt.id IS NOT NULL
                                AND a.date_imputation_paie BETWEEN '" . $oDateDebut->format("Y-m-d") . "' AND '" . $oDateCourante->format("Y-m-d") . "'
                            GROUP BY a.depot_id , DATE_FORMAT(date_imputation_paie, '%Y-%m-%d') , pt.id , pt.flux_id
                            )  AS crm ON a.date_distrib = crm.date_imputation_paie
                            
                            AND a.id = crm.id
                            AND a.flux_id = crm.flux_id
                            AND a.depot_id = crm.depot_id
            WHERE
                pt.date_distrib = a.date_distrib
                AND pt.date_distrib BETWEEN '" . $oDateDebut->format("Y-m-d") . "' AND '" . $oDateCourante->format("Y-m-d") . "'
                AND pt.modele_tournee_jour_id = mtj.id
                AND a.id = pt.id
                AND d.id = pt.depot_id
                AND (pt.tournee_org_id is null OR pt.split_id is not null) 
            GROUP BY pt.code , d.code , date_distrib , pt.flux_id
            ORDER BY date_distrib , pt.code , d.code , pt.flux_id;";

        return $this->_em->getConnection()->executeQuery($sql)->fetchAll();



        /* $sSql = "SELECT 
          SUM(Nb_clients_Abo) Nb_client_Abo,
          SUM(Nb_Ex_Abo) Nb_Ex_Abo,
          SUM(Nb_Diff) Nb_Diff,
          SUM(Nb_clients_DIV) Nb_clients_DIV,
          SUM(Nb_Ex_DIV) Nb_Ex_DIV,
          SUM(Nb_ex_en_supplements) Nb_ex_en_supplements,
          SUM(Nb_adresses) Nb_adresses,
          AVG(pt.valrem) Etalon,
          SUBTIME(pt.duree, IFNULL(pt.duree_reperage, cast('00:00:00' as time))) nb_Heure,
          AVG(pt.nbkm_paye) nb_km,
          MAX(nombre_reclam_brut) nombre_reclam_brut,
          MAX(nombre_reclam_net) nombre_reclam_net,
          MAX(nombre_reclam_div_brut) nombre_reclam_Div_brut,
          MAX(nombre_reclam_div_Net) nombre_reclam_Div_Net,
          pt.code code_tournee,
          d.code depot,
          a.date_distrib,
          pt.flux_id
          FROM
          pai_tournee pt,
          modele_tournee_jour mtj,
          depot d,
          (SELECT
          SUM(ppt.nbcli) Nb_clients_Abo,
          SUM(ppt.qte) Nb_Ex_Abo,
          0 Nb_Diff,
          '' Nb_clients_DIV,
          '' Nb_Ex_DIV,
          '' Nb_clients_en_supplements,
          '' Nb_ex_en_supplements,
          '' Nb_adresses,
          pt.date_distrib,
          pt.depot_id,
          pt.id,
          pt.flux_id
          FROM
          pai_tournee pt
          INNER JOIN pai_prd_tournee ppt ON ppt.tournee_id = pt.id
          INNER JOIN produit p ON ppt.produit_id = p.id
          WHERE
          p.soc_code_ext = 'LP'
          AND ppt.natureclient_id = 0
          AND p.type_id IN (1)
          AND pt.date_distrib BETWEEN '".$oDateDebut->format("Y-m-d")."' AND '" . $oDateCourante->format("Y-m-d")."'
          GROUP BY pt.date_distrib , pt.id , pt.flux_id , pt.depot_id UNION SELECT
          '' Nb_clients_Abo,
          '' Nb_Ex_Abo,
          SUM(ppt.nbcli) Nb_Diff,
          '' Nb_clients_DIV,
          '' Nb_Ex_DIV,
          '' Nb_clients_en_supplements,
          '' Nb_ex_en_supplements,
          '' Nb_adresses,
          pt.date_distrib,
          pt.depot_id,
          pt.id,
          pt.flux_id
          FROM
          pai_tournee pt
          INNER JOIN pai_prd_tournee ppt ON ppt.tournee_id = pt.id
          INNER JOIN produit p ON ppt.produit_id = p.id
          WHERE
          1 = 1 AND ppt.natureclient_id = 1
          AND p.type_id IN (1)
          AND p.soc_code_ext != 'LP'
          AND pt.date_distrib BETWEEN '".$oDateDebut->format("Y-m-d")."' AND '" . $oDateCourante->format("Y-m-d")."'
          GROUP BY pt.date_distrib , pt.id , pt.flux_id , pt.depot_id UNION SELECT
          '' Nb_clients_Abo,
          '' Nb_Ex_Abo,
          0 Nb_Diff,
          SUM(ppt.nbcli) Nb_clients_DIV,
          SUM(ppt.qte) Nb_Ex_DIV,
          '' Nb_clients_en_supplements,
          '' Nb_ex_en_supplements,
          '' Nb_adresses,
          pt.date_distrib,
          pt.depot_id,
          pt.id,
          pt.flux_id
          FROM
          pai_tournee pt
          INNER JOIN pai_prd_tournee ppt ON ppt.tournee_id = pt.id
          INNER JOIN produit p ON ppt.produit_id = p.id
          WHERE
          1 = 1 AND ppt.natureclient_id = 0
          AND p.type_id IN (1)
          AND p.soc_code_ext != 'LP'
          AND pt.date_distrib BETWEEN '".$oDateDebut->format("Y-m-d")."' AND '" . $oDateCourante->format("Y-m-d")."'
          GROUP BY pt.date_distrib , pt.id , pt.flux_id , pt.depot_id UNION SELECT
          '' Nb_clients_Abo,
          '' Nb_Ex_Abo,
          0 Nb_Diff,
          '' Nb_clients_DIV,
          '' Nb_Ex_DIV,
          SUM(nbcli) Nb_clients_en_supplements,
          SUM(nbspl) Nb_ex_en_supplements,
          '' Nb_adresses,
          pt.date_distrib,
          pt.depot_id,
          pt.id,
          pt.flux_id
          FROM
          pai_tournee pt
          WHERE
          1 = 1 AND nbspl != 0
          AND pt.date_distrib BETWEEN '".$oDateDebut->format("Y-m-d")."' AND '" . $oDateCourante->format("Y-m-d")."'
          GROUP BY pt.date_distrib , pt.id , pt.flux_id , pt.depot_id UNION SELECT
          '' Nb_clients_Abo,
          '' Nb_Ex_Abo,
          0 Nb_Diff,
          '' Nb_clients_DIV,
          '' Nb_Ex_DIV,
          '' Nb_clients_en_supplements,
          '' Nb_ex_en_supplements,
          nbadr Nb_adresses,
          pt.date_distrib,
          pt.depot_id,
          pt.id,
          pt.flux_id
          FROM
          pai_tournee pt
          WHERE
          1 = 1
          AND pt.date_distrib BETWEEN '".$oDateDebut->format("Y-m-d")."' AND '" . $oDateCourante->format("Y-m-d")."'
          GROUP BY pt.date_distrib , pt.id , pt.flux_id , pt.depot_id) a
          LEFT OUTER JOIN
          (SELECT
          SUM(nombre_reclam_brut) nombre_reclam_brut,
          SUM(nombre_reclam_net) nombre_reclam_net,
          SUM(Nb_Reclam_Div_brut) nombre_reclam_Div_brut,
          SUM(Nb_Reclam_Div_Net) nombre_reclam_Div_Net,
          b.date_imputation_paie,
          b.depot_id,
          b.id,
          b.flux_id
          FROM
          (SELECT
          COUNT(*) nombre_reclam_brut,
          0 nombre_reclam_net,
          0 Nb_Reclam_Div_brut,
          0 Nb_Reclam_Div_Net,
          DATE_FORMAT(date_imputation_paie, '%Y-%m-%d') date_imputation_paie,
          a.depot_id,
          pt.id,
          pt.flux_id
          FROM
          crm_detail a, crm_demande b, pai_tournee pt
          WHERE
          1 = 1 AND b.crm_categorie_id = 1
          AND a.crm_demande_id = b.id
          AND pt.id IS NOT NULL
          AND a.pai_tournee_id = pt.id
          AND soc_code_ext = 'LP'
          AND a.date_imputation_paie BETWEEN '".$oDateDebut->format("Y-m-d")."' AND '" . $oDateCourante->format("Y-m-d")."'
          GROUP BY a.depot_id , DATE_FORMAT(date_imputation_paie, '%Y-%m-%d') , pt.id , pt.flux_id UNION SELECT
          0 nombre_reclam_brut,
          COUNT(*) nombre_reclam_net,
          0 Nb_Reclam_Div_brut,
          0 Nb_Reclam_Div_Net,
          DATE_FORMAT(date_imputation_paie, '%Y-%m-%d') date_imputation_paie,
          a.depot_id,
          pt.id,
          pt.flux_id
          FROM
          crm_detail a, crm_demande b, pai_tournee pt
          WHERE
          imputation_paie = '1'
          AND b.crm_categorie_id = 1
          AND a.crm_demande_id = b.id
          AND pt.id IS NOT NULL
          AND a.pai_tournee_id = pt.id
          AND soc_code_ext = 'LP'
          AND a.date_imputation_paie BETWEEN '".$oDateDebut->format("Y-m-d")."' AND '" . $oDateCourante->format("Y-m-d")."'
          GROUP BY a.depot_id , DATE_FORMAT(date_imputation_paie, '%Y-%m-%d') , pt.id , pt.flux_id UNION SELECT
          0 nombre_reclam_brut,
          0 nombre_reclam_net,
          COUNT(*) Nb_Reclam_Div_brut,
          0 Nb_Reclam_Div_Net,
          DATE_FORMAT(date_imputation_paie, '%Y-%m-%d') date_imputation_paie,
          a.depot_id,
          pt.id,
          pt.flux_id
          FROM
          crm_detail a, crm_demande b, pai_tournee pt
          WHERE
          1 = 1 AND soc_code_ext != 'LP'
          AND pt.id IS NOT NULL
          AND b.crm_categorie_id = 1
          AND a.crm_demande_id = b.id
          AND a.pai_tournee_id = pt.id
          AND a.date_imputation_paie BETWEEN '".$oDateDebut->format("Y-m-d")."' AND '" . $oDateCourante->format("Y-m-d")."'
          GROUP BY a.depot_id , DATE_FORMAT(date_imputation_paie, '%Y-%m-%d') , pt.id , pt.flux_id UNION SELECT
          0 nombre_reclam_brut,
          0 nombre_reclam_net,
          0 Nb_Reclam_Div_brut,
          COUNT(*) Nb_Reclam_Div_Net,
          DATE_FORMAT(date_imputation_paie, '%Y-%m-%d') date_imputation_paie,
          a.depot_id,
          pt.id,
          pt.flux_id
          FROM
          crm_detail a, crm_demande b, pai_tournee pt
          WHERE
          1 = 1 AND imputation_paie = '1'
          AND soc_code_ext != 'LP'
          AND pt.id IS NOT NULL
          AND b.crm_categorie_id = '1'
          AND a.crm_demande_id = b.id
          AND a.pai_tournee_id = pt.id
          AND a.date_imputation_paie BETWEEN '".$oDateDebut->format("Y-m-d")."' AND '" . $oDateCourante->format("Y-m-d")."'
          GROUP BY a.depot_id , DATE_FORMAT(date_imputation_paie, '%Y-%m-%d') , pt.id , pt.flux_id) AS b
          GROUP BY date_imputation_paie , depot_id , b.id , b.flux_id) AS crm ON a.date_distrib = crm.date_imputation_paie
          AND a.id = crm.id
          AND a.flux_id = crm.flux_id
          AND a.depot_id = crm.depot_id
          WHERE
          pt.date_distrib = a.date_distrib
          AND pt.date_distrib BETWEEN '".$oDateDebut->format("Y-m-d")."' AND '" . $oDateCourante->format("Y-m-d")."'
          AND pt.modele_tournee_jour_id = mtj.id
          AND a.id = pt.id
          AND d.id = pt.depot_id
          AND (pt.tournee_org_id is null OR pt.split_id is not null)
          GROUP BY pt.code , d.code , date_distrib , pt.flux_id
          ORDER BY date_distrib , pt.code , d.code , pt.flux_id;

          "; */
    }

    /**
     * Donnees des abonnes a servir LP a exporter vers DCS 
     * 
     * @param array $aCodesSocieteATraiter
     * @param \DateTime $oJourATraiter
     * @param array $aDepotsATraiter    si vide, on prend tous les depots
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function donneesDcsTourneeCasLPExport($aCodesSocieteATraiter = array('LP'), \DateTime $oJourATraiter, $aDepotsATraiter = array()) {
        try {
            $sDateAExporter = $oJourATraiter->format("Y-m-d");
            /*

              SELECT
              csl.num_parution, date_format(csl.date_distrib, '%Y/%m/%d') AS date_distrib, a.numabo_ext, p.soc_code_ext, p.prd_code_ext, p.spr_code_ext, '' AS divers, IFNULL(mt.codeDCS, '') AS codeDCS, IFNULL(d.code, '') AS depot_code
              , IFNULL(csl.point_livraison_ordre, '') AS ordre, p.societe_id
              FROM
              client_a_servir_logist csl
              INNER JOIN abonne_soc a ON csl.abonne_soc_id = a.id
              INNER JOIN societe s ON csl.societe_id = s.id AND s.code = 'LP'
              LEFT JOIN produit p ON csl.produit_id = p.id
              LEFT JOIN modele_tournee_jour mtj ON csl.tournee_jour_id = mtj.id
              LEFT JOIN modele_tournee mt ON mtj.tournee_id = mt.id
              LEFT JOIN groupe_tournee gt ON mt.groupe_id = gt.id
              LEFT JOIN depot d ON gt.depot_id = d.id
              WHERE
              csl.date_distrib = '2015/01/21'
              AND d.code IS NOT NULL
              ORDER BY
              depot_code, ordre

             */
            $sSlct = " SELECT
                            csl.num_parution, date_format(csl.date_distrib, '%Y/%m/%d') AS date_distrib, a.numabo_ext, p.soc_code_ext
                            , p.prd_code_ext, p.spr_code_ext, '' AS divers, IFNULL(mt.codeDCS, '') AS codeDCS, IFNULL(d.code, '') AS depot_code
                            , IFNULL(csl.point_livraison_ordre, '') AS ordre, p.societe_id
                        FROM
                            client_a_servir_logist csl
                            INNER JOIN abonne_soc a ON csl.abonne_soc_id = a.id
                            INNER JOIN societe s ON csl.societe_id = s.id AND s.code IN ('" . implode("', '", $aCodesSocieteATraiter) . "') 
                            LEFT JOIN produit p ON csl.produit_id = p.id
                            LEFT JOIN modele_tournee_jour mtj ON csl.tournee_jour_id = mtj.id
                            LEFT JOIN modele_tournee mt ON mtj.tournee_id = mt.id
                            LEFT JOIN groupe_tournee gt ON mt.groupe_id = gt.id 
                            LEFT JOIN depot d ON gt.depot_id = d.id
                        WHERE
                            csl.date_distrib = '" . $sDateAExporter . "'
                            AND d.code IS NOT NULL ";
            if (!empty($aDepotsATraiter)) {
                $sSlct .= " AND d.code IN ('" . implode("', '", $aDepotsATraiter) . "') ";
            }
            $sSlct .= " ORDER BY
                            depot_code, ordre
                            ";
            return $this->_em->getConnection()->executeQuery($sSlct)->fetchAll();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Retourne les informations liées à un client à servir
     * @param array $aCrits Le tableau des critères de sélection
     * @return array Le jeu de résultats correspondants aux critères
     */
    public function getClientInfo($aCrits) {
        try {
            $sSql = "SELECT * FROM client_a_servir_logist 
            WHERE 
            point_livraison_id = " . $aCrits['point_livraison_id'] . "
            AND abonne_soc_id = " . $aCrits['abonne_soc_id'] . "
            AND date_parution = '" . $aCrits['date_distrib'] . "'
            AND flux_id = " . $aCrits['flux_id'] . "
        ";

            return $this->_em->getConnection()->executeQuery($sSql)->fetchAll();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Compte le nombre de lignes dans CASL pour une tournée et une date donnée
     * @param array $aCrits Un tableau contenant les critères à utiliser
     * @return array Un tableau avec le compte dans l'index "nb"
     * @throws DBALException
     */
    public function compterLignes($aCrits) {
        try {
            $sSql = "SELECT COUNT(*) AS nb FROM client_a_servir_logist WHERE 
	date_distrib = '" . $aCrits['date'] . "'
	AND flux_id = " . $aCrits['flux_id'] . "
	AND tournee_jour_id = " . $aCrits['tournee_jour_id'];
            return $this->_em->getConnection()->executeQuery($sSql)->fetchAll();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    public function getDataByTourneeJour($tournee_id) {
        $sSql = "
                SELECT * FROM (
                    SELECT 
                        date_distrib,abonne_soc_id,p.id as produit_id,casl.id,
                        CONCAT(arnvp.adresse,' ',arnvp.cp,' ',arnvp.ville) as ville,
                        casl.vol1,casl.vol2,point_livraison_ordre,
                        mtj.code as code_tournee,s.libelle as societe_libelle,
                        p.libelle as produit_libelle,td.num_abonne_soc
                    FROM
                        client_a_servir_logist casl
                            INNER JOIN
                        modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id
                            INNER JOIN
                        societe s ON s.id = casl.societe_id
                            INNER JOIN
                        produit p ON p.id = casl.produit_id
                            LEFT JOIN
                        adresse_rnvp arnvp ON arnvp.id  = casl.point_livraison_id
                            INNER JOIN 
                        tournee_detail td ON td.num_abonne_id = casl.abonne_soc_id
                    where
                        tournee_jour_id = $tournee_id
                            AND date_distrib >= CURDATE() - INTERVAL 90 DAY
                            AND td.modele_tournee_jour_code = mtj.code
                    GROUP BY abonne_soc_id , p.id,date_distrib
                    ORDER by date_distrib DESC,point_livraison_ordre ASC
                    ) as t
                    GROUP BY abonne_soc_id,produit_id;
                ";
        return $this->_em->getConnection()->executeQuery($sSql)->fetchAll();
    }

    /*     * * 
     *  Dispatch excel
     */

    public function getDataDispatchTourneeExcel($codeDepot, $dateDistrib, $fluxId) {

        $sql = "   SELECT p.id as produit_id ,p.libelle as libelle_produit, mtj.code as libelle_tournee, casl.tournee_jour_id as tournee_jour_id,
                
                 if(sum(casl.qte) > pqt.nb_exemplaires &&  pqt.nb_exemplaires > 0,
                    concat(
                                sum(casl.qte) div pqt.nb_exemplaires,
                                'p',
                                if(sum(casl.qte) MOD pqt.nb_exemplaires> 0,
                                    concat('+', sum(casl.qte) MOD pqt.nb_exemplaires) ,'')
                                ),

                                if (sum(casl.qte) > 0, sum(casl.qte), '')
                   ) as qte,sum(casl.qte) as total_qte


                FROM client_a_servir_logist casl
                LEFT JOIN produit p ON p.id = casl.produit_id
                LEFT JOIN depot d ON d.id = casl.depot_id
                LEFT JOIN modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id
                LEFT JOIN paquet_volume pqt ON pqt.produit_id = p.id AND pqt.date_distrib = casl.date_distrib
                WHERE casl.date_distrib = '" . $dateDistrib . "'
                AND casl.tournee_jour_id is not null
                AND d.code = '" . $codeDepot . "'
                AND casl.flux_id = '" . $fluxId . "'
		GROUP BY p.id, mtj.code
                ORDER BY p.id, mtj.code";

        return $this->_em->getConnection()->executeQuery($sql)->fetchAll();
    }

    public function UpdatePointLivraisonScriptRepair($iPointLivraisonId, $sId) {
        try {
            $slct = " UPDATE client_a_servir_logist
                        SET 
                            point_livraison_id = $iPointLivraisonId 
                        WHERE date_parution > CURDATE() - INTERVAL 30 DAY
                        AND point_livraison_id IN($sId)
                    ";
            $this->_em->getConnection()->prepare($slct)->execute();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**  Tournee de l'abonne pour une date et un depot */
    public function getTourneeByAbonneDepotDate($abonneId, $depot, $dateDistrib) {
        $query = "SELECT 
                     mtj.id mtj_id , mtj.code
                 FROM client_a_servir_logist casl  
                 LEFT JOIN modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id 
                 WHERE depot_id = '" . $depot . "' 
                 AND date_distrib = '" . $dateDistrib . "' 
                  AND abonne_soc_id = '" . $abonneId . "'"

        ;

        return $this->_em->getConnection()->fetchAll($query);
    }

    /**
     * MISE EN COHERENCE DE L'ORDRE DES POINTS DE LIVRAISON 
     * attribut un ordre unique au point de livraison
     */
    public function orderUniqueDeliveryPoint($tourneeId, $date) {
        $slct = " 
                DROP table IF EXISTS casl_tmp;
                CREATE TEMPORARY TABLE casl_tmp
                (
                    new_ordre int NOT NULL AUTO_INCREMENT,
					point_livraison_id int,
					adresse varchar(255),
					point_livraison_ordre int ,
					PRIMARY KEY (new_ordre)
                );
                
                INSERT INTO casl_tmp 
                    SELECT 0,point_livraison_id,adresse,point_livraison_ordre from client_a_servir_logist casl
                    left join adresse_rnvp a ON a.id = casl.point_livraison_id
                    where
                    casl.date_distrib = '$date'
                    and casl.tournee_jour_id = $tourneeId
                    group by casl.point_livraison_id
                    order by min(casl.point_livraison_ordre)
                ;
                UPDATE client_a_servir_logist casl
                        JOIN
                    casl_tmp ON casl_tmp.point_livraison_id = casl.point_livraison_id 
                SET 
                    casl.point_livraison_ordre = casl_tmp.new_ordre
                WHERE
                    casl.tournee_jour_id = $tourneeId
                    AND casl.date_distrib = '$date'
                        AND casl.point_livraison_id = casl_tmp.point_livraison_id

                ";
        $this->_em->getConnection()->prepare($slct)->execute();
    }

    public function getAddressByTourneeJour($tourneeCode, $dateDistrib, $GeocodeGoogle = false) {
        $query = "SELECT 
                    adresse, cp, ville, insee
                FROM
                    client_a_servir_logist casl
                        JOIN
                    adresse_rnvp arnvp ON arnvp.id = casl.point_livraison_id
                            JOIN
                    modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id
                WHERE
                    mtj.code = '$tourneeCode'
                        AND date_distrib = '$dateDistrib'
                ";
        if ($GeocodeGoogle)
            $query.=" 
                AND (geo_type  IN (1,2)  or  (geo_type  IN (3,4) AND geo_score < 18) ) ";
        $query.=" 
                GROUP BY casl.point_livraison_id
                "
        ;
        return $this->_em->getConnection()->fetchAll($query);
    }

    public function getRejectedAddressByDepot($depot, $dateMin, $dateMax) {
        $query = "  SELECT DISTINCT
                        arnvp.id, arnvp.adresse, arnvp.cp, arnvp.ville, arnvp.insee
                    FROM
                        client_a_servir_logist casl
                            INNER JOIN
                        adresse_rnvp arnvp ON arnvp.id = casl.rnvp_id
                    WHERE
                        casl.depot_id = $depot
                            AND date_distrib between '$dateMin' AND '$dateMax'
                            AND arnvp.geo_etat = 0
                ";
        ;
        return $this->_em->getConnection()->fetchAll($query);
    }

    public function getRejectedNormaliseAddressByDepot($depot) {
        $query = "  SELECT DISTINCT
                        arnvp.id,arnvp.adresse, arnvp.cp, arnvp.ville, arnvp.insee
                    FROM
                        client_a_servir_logist casl
                            INNER JOIN
                        adresse_rnvp arnvp ON arnvp.id = casl.rnvp_id
                        INNER JOIN adresse a ON casl.adresse_id = a.id
                        INNER jOIN societe s ON casl.societe_id = s.id
                    WHERE
                        casl.depot_id = $depot
                    AND a.adresse_rnvp_etat_id > 2
                ";
        ;
        return $this->_em->getConnection()->fetchAll($query);
    }

    /**
     * Méthode applique l'ordre d'un modèle de tournée (TD) sur une tournée effective (CASL)
     * @param int $sTourneeJourCode Le code du MTJ 
     * @param string $sDate La date de la tournée
     */
    public function repliquerOrdreModeleSurDistrib($sTourneeJourCode, $sDate) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
            UPDATE client_a_servir_logist AS casl_ref 
	 INNER JOIN (
	SELECT (@cnt := @cnt + 1) AS nouvel_ordre, t.* FROM (
	SELECT casl.id AS casl_id, casl.point_livraison_id AS casl_point_livraison_id, casl.tournee_jour_id AS casl_tjid, casl.date_parution AS casl_date, casl.* FROM client_a_servir_logist AS casl
		JOIN modele_tournee_jour AS mtj ON mtj.id = casl.tournee_jour_id
		INNER JOIN tournee_detail AS td ON td.modele_tournee_jour_code = mtj.code AND td.point_livraison_id = casl.point_livraison_id
		WHERE
		casl.date_parution = '$sDate'	
		AND
		casl.date_parution BETWEEN mtj.date_debut AND mtj.date_fin
		AND
		td.modele_tournee_jour_code = '$sTourneeJourCode'
		AND 
		(`mtj`.`jour_id` = (CAST(DATE_FORMAT(`casl`.`date_distrib`,'%w')AS SIGNED) + 1))
		GROUP BY casl.point_livraison_id
		ORDER BY td.ordre ASC
	) AS t
	CROSS JOIN (SELECT @cnt := 0) AS dummy	)
	AS ref_modif
	SET casl_ref.point_livraison_ordre = nouvel_ordre
		WHERE casl_point_livraison_id = casl_ref.point_livraison_id
		AND
		casl_tjid = casl_ref.tournee_jour_id
		AND
		casl_date = casl_ref.date_parution
            ";

        return $connection->executeQuery($query);
    }

    /**
     * Retourne la dernière date pour laquelle des données sont disponibles pour une tournée
     * @param string $sTourneeJourCode Le MTJ Code de la tournée
     * @return string|bool $aDateInfo['last_date'] si une date a été trouvée, FALSE dans le cas contraire 
     */
    public function recupererDerniereDate($sTourneeJourCode) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT MAX(date_distrib) AS last_date FROM client_a_servir_logist AS casl
	JOIN modele_tournee_jour AS mtj ON mtj.id = casl.tournee_jour_id
	WHERE mtj.code = '$sTourneeJourCode'
	AND
	casl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin";

        $stmt = $connection->executeQuery($query);
        $aDateInfo = $stmt->fetch();
        if (!empty($aDateInfo)) {
            return $aDateInfo['last_date'];
        } else {
            return FALSE;
        }
    }

    /**
     * RECUPERENT LES ABONNES DANS UNE TOURNEE INCOHERENTE
     * @param type $dateDistrib
     * @param type $iDepot
     * @param type $produit_id
     * @return type
     */
    public function getAbonneInconsistencyTournee($tourneeJourId, $dateDistrib, $week = 1) {
        $connection = $this->getEntityManager()->getConnection();
        $day = $week * 7;
        $q = "
                SELECT
                    abonne_soc_id,mtj.code,jour_id
                FROM  client_a_servir_logist casl
                JOIN modele_tournee_jour mtj on mtj.id = casl.tournee_jour_id
                WHERE NOT EXISTS (
                    SELECT 
                        *
                    FROM
                        client_a_servir_logist casl_2
                    WHERE
                        casl_2.abonne_soc_id = casl.abonne_soc_id
                        AND casl_2.tournee_jour_id = $tourneeJourId 
                        AND casl_2.date_distrib = ('$dateDistrib' - INTERVAL $day DAY)
                    )
                    AND casl.tournee_jour_id = $tourneeJourId 
                    AND casl.date_distrib = '$dateDistrib'
                ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function getTourneeJourByAbonneSocDate($abonneSocId, $dateDistrib, $week = 1) {
        $connection = $this->getEntityManager()->getConnection();
        $day = $week * 7;
        $q = "
                SELECT 
                    mtj.code
                FROM
                    client_a_servir_logist c
                        JOIN
                    modele_tournee_jour mtj ON mtj.id = c.tournee_jour_id
                where
                    abonne_soc_id = $abonneSocId
                        and date_distrib = ('$dateDistrib' - INTERVAL $day DAY)
                ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetch();
    }

    /**
     * RETURN LES TOURNEES OU AU MOINS 1 ABONNE POSSEDE DES ETIQUETTES
     * */
    public function getTourneeByLabelAbo($depotId, $date, $flux) {
        $connection = $this->getEntityManager()->getConnection();

        $q = "
            SELECT 
                mtj.id as MTJ, mtj.code
            FROM
                client_a_servir_logist casl
                    JOIN
                etiquette e ON e.abonne_soc_id = casl.abonne_soc_id
                    JOIN
                modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id
            WHERE
                date_distrib = '$date'
                    AND flux_id = $flux
                    AND casl.depot_id = $depotId
            GROUP BY mtj.id
            ORDER BY mtj.code ASC
            ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    /**
     * RETURN LES PRODUITS PAR TOURNEE, JOUR OU AU MOINS 1 ABONNE POSSEDE DES ETIQUETTES
     * */
    public function getProductsByDateTourneeId($tournees, $date) {
        $connection = $this->getEntityManager()->getConnection();

        $q = "
            SELECT 
                casl.produit_id, p.libelle as produit_libelle
            FROM
                client_a_servir_logist casl
                    JOIN
                etiquette e ON e.abonne_soc_id = casl.abonne_soc_id
                    JOIN
                produit p ON p.id = casl.produit_id
            WHERE
                date_distrib = '$date'
                    AND tournee_jour_id IN ($tournees)
            GROUP BY casl.produit_id
            ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function getAddressIdByPlDateTourneeId($pointLivraisonId, $tourneeId, $dateDistrib) {
        $connection = $this->getEntityManager()->getConnection();

        $q = "
            SELECT 
                adresse_id

            FROM
                client_a_servir_logist
            WHERE
                point_livraison_id = $pointLivraisonId
                AND date_distrib = '$dateDistrib'
                AND tournee_jour_id = $tourneeId;
            ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    /**
     * Recuperation des infos principales des feuilles de portages a verifier 
     * @param \DateTime $dateDistribAVerif
     * @param integer $iFluxId
     * @param array $aCodesCD
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getInfosFeuillesPortageAVerifier(\DateTime $dateDistribAVerif, $iFluxId = 0, $aCodesCD = array()) {
        try {
            $aRetour = array();
            $sSlct = " SELECT DISTINCT
                            d.code AS depot_code, d.libelle AS depot_libelle, csl.flux_id, f.libelle AS flux_libelle, DATE_FORMAT(csl.date_distrib, '%Y%m%d') AS date_Ymd, DATE_FORMAT(csl.date_distrib, '%Y-%m-%d') AS date_Y_m_d, DATE_FORMAT(csl.date_distrib, '%d/%m/%Y') AS date_dmY
                        FROM
                            client_a_servir_logist csl
                            INNER JOIN depot d ON csl.depot_id = d.id
                            INNER JOIN ref_flux f ON csl.flux_id = f.id
                        WHERE
                            csl.date_distrib = '" . $dateDistribAVerif->format('Y-m-d') . "'
                            AND csl.tournee_jour_id IS NOT NULL ";
            if ($iFluxId != 0) {
                $sSlct .= " AND csl.flux_id = " . $iFluxId;
            }
            if (!empty($aCodesCD)) {
                $aWhereOr = array();
                foreach ($aCodesCD as $v) {
                    $aWhereOr[] = " d.code LIKE '%" . $v . "%'";
                }
                if (!empty($aWhereOr)) {
                    $sSlct .= " AND (" . implode(' OR ', $aWhereOr) . ") ";
                }
            }
            $sSlct .= "  
                        ORDER BY
                            date_Ymd, csl.flux_id, depot_code
                        ";
            // echo "\n";print_r($sSlct);echo "\n";
            $rSlct = $this->_em->getConnection()->fetchAll($sSlct);
            foreach ($rSlct as $aArr) {
                $aRetour[$aArr['date_Y_m_d']][] = $aArr;
            }
            return $aRetour;
        } catch (DBALException $ex) {
            throw $ex;
        }
    }

    /** METHODE TEMPORAIRE * */
    public function getSgp() {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
            SELECT 
                p.libelle as nom_produit,mtj.code as code_tournee,qte as nb_ex,
                casl.vol1 as nom,casl.vol2,asoc.numabo_ext,casl.point_livraison_ordre,
                CONCAT_WS(' ',a.vol3,a.vol4,a.vol5,a.cp,a.ville) as a_adresse,
                CONCAT_WS(' ', arnvp.cadrs ,arnvp.adresse,arnvp.lieudit,arnvp.cp,arnvp.ville) as arnvp_adresse,
                GROUP_CONCAT(ip.valeur SEPARATOR ' -- ') as info_portage_abo
            FROM
                client_a_servir_logist casl
                    JOIN
                produit p ON p.id = casl.produit_id
                    LEFT JOIN
                modele_tournee_jour mtj ON mtj.id = casl.tournee_jour_id
                    JOIN
                adresse a ON a.id = casl.adresse_id
                    JOIN
                adresse_rnvp arnvp ON arnvp.id = casl.rnvp_id
                    AND CURDATE() BETWEEN a.date_debut AND a.date_fin
                    JOIN
                abonne_soc asoc ON asoc.id = casl.abonne_soc_id
                    JOIN
                infos_portages_abonnes ipa ON ipa.abonne_id = casl.abonne_soc_id
                    JOIN
                info_portage ip ON ip.id = ipa.info_portage_id
            WHERE
                casl.date_distrib = '2015-11-17'
                    AND casl.soc_code_ext = 'CE'
            GROUP BY casl.abonne_soc_id
            ORDER BY mtj.code , point_livraison_ordre
        ";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function trouveLastTourneeJour($pl, $date, $j) {//classment dans distribution
        $connection = $this->getEntityManager()->getConnection();
        $q = "select * from client_a_servir_logist
              where 
              point_livraison_id= $pl and date_parution= '$date' - interval $j day;";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function mettreAjourTournee_jour($id, $tourneeIdDestination) {//classment dans distribution
        $connection = $this->getEntityManager()->getConnection();
        try {
            $q = "
            UPDATE  client_a_servir_logist 
            SET tournee_jour_id = $tourneeIdDestination 
            WHERE id = $id";
            $connection->executeQuery($q);
        } catch (Exception $ex) {
            
        }
    }

    public function compterPlv($aCrits) {
        try {
            $sSql = "SELECT COUNT(*) AS nb FROM client_a_servir_logist WHERE 
	date_distrib = '" . $aCrits['date'] . "'
	AND flux_id = " . $aCrits['flux_id'] . "
	AND tournee_jour_id = " . $aCrits['tournee_jour_id'] . " group by point_livraison_id, produit_id";
            return $this->_em->getConnection()->executeQuery($sSql)->fetchAll();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    public function setAbonnePl($date_distrib, $pl_id, $ordre) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
            UPDATE  client_a_servir_logist 
            SET point_livraison_ordre = $ordre 
            WHERE point_livraison_id = $pl_id
                AND date_distrib = '$date_distrib'
        ";
        $connection->executeQuery($q);
    }

}
