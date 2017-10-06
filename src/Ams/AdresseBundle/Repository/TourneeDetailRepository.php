<?php

namespace Ams\AdresseBundle\Repository;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\DBALException;
use Ams\CartoBundle\GeoService;

/**
 * Description of TourneeDetailRepository
 *
 * @author madelise
 */
class TourneeDetailRepository extends EntityRepository implements ContainerAwareInterface {

    private $container;
    
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function getAllResults($datas) {
        try {
            $qb = $this->createQueryBuilder('t');

            $qb->expr()->isNotNull('t.numAbonne');

            return $qb->getQuery()
                            ->getArrayResult();
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    /**
     * Méthode qui interroge la vue des tournées avec toutes leurs informations
     */
    public function getDatasFromView($optionsArr) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "SELECT *, SUBTIME(`fin_plage_horaire`, `debut_plage_horaire`) AS temps_tournee, "
                . "SEC_TO_TIME(SUM(`duree_conduite`)) AS temps_conduite, "
                . "MAX(`trajet_cumule`) AS trajet_total "
                . "FROM tournee_abo_full WHERE date_parution = :date_parution AND d_id = :depot_id  AND latitude > 0 AND longitude > 0 GROUP BY td_id ORDER BY ordre ASC;";
        $stmt = $connection->executeQuery($q, $optionsArr);
        return $stmt->fetchAll();
    }

    /**
     * Methode qui recupere de tournee detail tous les abonnées d'une tournee
     * et par date de distrib
     */
    public function getTourneeDatas($tourneeJourCode,$dateDistrib, $bStartFromDepot = FALSE, $bEndToDepot = FALSE) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
            SELECT 
                *,SUBTIME(`fin_plage_horaire`,`debut_plage_horaire`) AS temps_tournee,
                SEC_TO_TIME(SUM(`duree_conduite`)) AS temps_conduite,gt.heure_debut,
                modele_tournee_jour_code as code_modele_tournee, td.id as td_id,
                arnvp.adresse AS a_adresse,td.num_abonne_soc AS num_abonne,mtj.id as mtj_id,
                td.trajet_total_tournee AS trajet_total,td.temps_conduite AS drive_time,
                td.temps_tournee AS tournee_time,d.id AS d_id,d.adresse AS d_adresse,
                a.point_livraison_id as point_livraison_id,td.ordre as ordre,arnvp.geox as longitude,arnvp.geoy as latitude
            FROM
                tournee_detail td
                    LEFT JOIN
                client_a_servir_logist casl ON td.num_abonne_id = casl.abonne_soc_id
                    AND date_distrib = '$dateDistrib'
                    LEFT JOIN
                modele_tournee_jour mtj ON mtj.code = td.modele_tournee_jour_code
                    AND '$dateDistrib' BETWEEN date_debut AND date_fin
                    LEFT JOIN
                modele_tournee mt ON mt.id = mtj.tournee_id
                    LEFT JOIN
                groupe_tournee gt ON gt.id = mt.groupe_id
                    LEFT JOIN 
                adresse a on a.abonne_soc_id = td.num_abonne_id
                    AND '$dateDistrib' BETWEEN a.date_debut and a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
                    LEFT JOIN 
                adresse_rnvp arnvp ON arnvp.id = a.point_livraison_id
                    LEFT JOIN 
                abonne_soc asoc ON  asoc.id = td.num_abonne_id
                    LEFT JOIN 
                depot d ON d.id = casl.depot_id
            WHERE
                mtj.code = '$tourneeJourCode'
            GROUP BY td.num_abonne_id
            ORDER BY td.ordre ASC,casl.id DESC
            ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
                
    }
    /**
     * Méthode qui interroge la vue pour récupérer toutes les informations d'une vue
     * @param array $optionsArr Le tableau d'options pour paramétrer la requête sur la vue
     * @param bool $bStartFromDepot si TRUE, la tournée devrait démarrer depuis le dépôt
     * @param bool $bEndToDepot si TRUE, la tournée devrait se terminer au dépôt
     * @return array $aPointsTournee Le tableau contenant les points de livraison de la tournée
     */
    public function getTourneeDatasFromView($optionsArr, $bStartFromDepot = FALSE, $bEndToDepot = FALSE) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
            SELECT 
                abo_full . *,
                SUBTIME(`fin_plage_horaire`,
                        `debut_plage_horaire`) AS temps_tournee,
                SEC_TO_TIME(SUM(`duree_conduite`)) AS temps_conduite,
                gt.heure_debut
            FROM
                tournee_abo_full abo_full
                    LEFT JOIN
                modele_tournee_jour mtj ON mtj.id = mtj_id
                    LEFT JOIN
                modele_tournee mt ON mt.id = mtj.tournee_id
                    LEFT JOIN
                groupe_tournee gt ON gt.id = mt.groupe_id
            WHERE
                date_parution = :date_parution
                    AND d_id = :depot_id
                    AND code_modele_tournee = :code_modele_tournee
                    AND latitude > 0
                    AND longitude > 0
            GROUP BY td_id
            ORDER BY abo_full.ordre ASC , ordre_stop ASC
        ";
        
        $stmt = $connection->executeQuery($q, $optionsArr);
        $aPointsTournee = $stmt->fetchAll();
        $aFirstpointsTournee = current($aPointsTournee);

        // Intégration du dépot en tant que point de départ
       $bFonctionDepartDepot = $this->container->getParameter("GEOC_TOURNEE_DEPOT_START_ENABLE");
        if ($bFonctionDepartDepot == 1 && $bStartFromDepot
                || $bFonctionDepartDepot == 1 && $bEndToDepot) {
            $em = $this->getEntityManager();
            $oDepot = $em->getRepository('AmsSilogBundle:Depot')->findById($optionsArr['depot_id']);
            if (!empty($oDepot)){
                if ($oDepot[0]->getPtDepart()){
                    // On teste les coordonnées du dépot
                    if ($this->container->get('ams_carto.geoservice')->testPointGPS($oDepot[0]->getGeoX(), $oDepot[0]->getGeoY())){
                        $aInfoPointDepart = array(
                            'code_modele_tournee' => $aFirstpointsTournee['code_modele_tournee'],
                            'latitude' => $oDepot[0]->getGeoY(),
                            'longitude' => $oDepot[0]->getGeoX(),
                            'heure_debut' => $aFirstpointsTournee['heure_debut'],
                            'distance_trajet' => 0,
                            'trajet_cumule' => 0,
                            'debut_plage_horaire' => $aFirstpointsTournee['debut_plage_horaire'],
                            'fin_plage_horaire' => $aFirstpointsTournee['fin_plage_horaire'],
                            'nb_stop' => $aFirstpointsTournee['nb_stop'],
                            'drive_time' => null,
                            'temps_visite' => null,
                            'tournee_time' => $aFirstpointsTournee['tournee_time'],
                            'ordre' => 0,
                            'point_livraison_id' => null,
                            'client_type' => 'depart',
                            'mtj_code_tournee_jour' => $optionsArr['code_modele_tournee'],
                            'temps_tournee' => $aFirstpointsTournee['temps_tournee'],
                            'temps_conduite' => $aFirstpointsTournee['temps_conduite'],
                            'trajet_total' => $aFirstpointsTournee['trajet_total'],
                            'nom_depot' => $oDepot[0]->getLibelle(),
                            'adresse_depot' => $oDepot[0]->getAdresse(),
                            'mtj_id' => $aFirstpointsTournee['mtj_id']
                        );    
                        
                        // Les coordonnées du dépôt sont valide
                        // Intégration en point de départ ?
                        if ($bStartFromDepot){
                            array_unshift($aPointsTournee, $aInfoPointDepart);
                        }
                        
                        // Retour au dépot ?
                        if ($bEndToDepot){
                            $aPointsTournee[] = $aInfoPointDepart;
                        }
                    }
                }
            }
        }

        return $aPointsTournee;
    }

    /**
     * Méthode qui rècupere la vue pour récupérer toutes les informations d'une vue
     * @todo Supprimer si non utilisé. MAA
     */
//    public function getTourneeFromViewByDepot($iDepotId, $iFlux) {
//        $connection = $this->getEntityManager()->getConnection();
//        $q = "SELECT modele_tournee_jour_code FROM tournee_detail td
//							LEFT JOIN abonne_soc asoc ON asoc.id = td.num_abonne_id
//							LEFT JOIN client_a_servir_logist casl ON asoc.id = casl.abonne_soc_id
//							WHERE depot_id = $iDepotId
//							AND flux_id = $iFlux
//							AND modele_tournee_jour_code !=''
//							GROUP BY modele_tournee_jour_code";
//        $stmt = $connection->executeQuery($q);
//        return $stmt->fetchAll();
//    }

    public function findByCodeGroupByOrdre($code) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
    			SELECT *,td.id as id_tournee_detail FROM tournee_detail td
					LEFT JOIN client_a_servir_logist casl ON casl.abonne_soc_id  =td.num_abonne_id
					LEFT JOIN adresse_rnvp arnvp  ON arnvp.id = casl.point_livraison_id
					WHERE td.modele_tournee_jour_code='$code'
					AND ordre IS NOT NULL
					AND point_livraison_id IS NOT NULL
					GROUP BY casl.point_livraison_id
					ORDER BY td.ordre";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }


    /**
     * Méthode qui renvoie toutes les tournées qui correspondent au dépôt, et selon les critères
     * @param  string $code_depot Le code du dépot concerné
     * @param  string $date La date sur laquelle filtrer
     * @param  int $flux Le flux sur lequel filtrer
     * @param  int $limit La limit de la requete
     */
    public function getTourneeFromDepot($code_depot, $date, $flux = null, $limit = 100) {
        $connection = $this->getEntityManager()->getConnection();

        $q = "SELECT tournee_id, mtj.code, casl.depot_id, casl.flux_id, casl.type_service, d.libelle,d.adresse, mtj.id as MTJ,t.libelle as modele_jour_libelle,"
                . " COUNT(distinct casl.abonne_unique_id) AS nb_abos,"
                . " COUNT(distinct casl.produit_id) AS nb_prods,"
                . " COUNT(distinct casl.point_livraison_id) AS nb_stops"
                . " FROM modele_tournee_jour mtj"
                . " JOIN modele_tournee t ON mtj.tournee_id=t.id"
                . " JOIN groupe_tournee gt ON t.groupe_id=gt.id"
                . " JOIN depot d ON gt.depot_id=d.id"
                . " JOIN `client_a_servir_logist` casl ON casl.tournee_jour_id=mtj.id"
                . " WHERE"
                . " d.code='" . $code_depot . "'"
                . " AND date_distrib = '$date'"
                . " AND casl.point_livraison_id IS NOT NULL"
                . " AND jour_id = DATE_FORMAT('$date', '%w') + 1";
        
        // Intégration du flux si spécifié
        if (!is_null($flux)) {
            $q .= " AND gt.flux_id = $flux AND casl.flux_id = $flux";
        }

        $q .= " GROUP BY tournee_id ORDER BY mtj.code ASC";
        $q .= ";";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    /**
     * Méthode qui met à jour les informations dans le cadre d'un basculement de tournée
     */
    public function changePointDeTournee($conditionsArr) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "UPDATE `tournee_detail` AS td "
                . "SET modele_tournee_jour_code = :tournee_destination_jour_code "
                . ", ordre = :nouvel_ordre "
                . ", duree_conduite = NULL "
                . ", heure_debut = NULL "
                . ", duree = :duree "
                . ", duree_viste_fixe = :duree_visite_fixe "
                . ", distance_trajet = NULL "
                . ", trajet_cumule = NULL "
                . ", debut_plage_horaire = :debut_plage_horaire "
                . ", fin_plage_horaire = :fin_plage_horaire "
                . ", source_modification = 'changePointDeTournee' "
                . ", date_modification = NOW() "
                . "WHERE modele_tournee_jour_code = :tournee_source_jour_code "
                . "AND num_abonne_id IN(" . $conditionsArr['liste_abonne_id'] . ");";
        return $connection->executeQuery($q, $conditionsArr);
    }

    /**
     * Méthode qui permet de basculer pluisieurs points de livraison vers une autre tournée 
     * @author   yannick Dieng
     * @param array $condArr Le tableau contenant  les conditions de la requete:tournee_destination_jour_code,  tournee_source_jour_code, nouv_liste_abonne_id, anc_liste_abonne_id, debut_plage_horaire, fin_plage_horaire, duree, duree_visite_fixe, pt_ordre
     * @param array $infosArr Le tableau contenant tous les points de la tournee avec le point de livraison id , le tournee detail id ,  l'ordre et l'etat(ancien ou nouveau)
     */
    public function changerMultPointsDeTournee($condArr, $infosArr) {
        $connection = $this->getEntityManager()->getConnection();
        $aRetour = array();
        $ct = 0;
        foreach ($infosArr as $infos){
            if ($infos['etat'] == 'ancien')
            {
                $q = "UPDATE `tournee_detail` AS td "
                . "SET ordre = " . $infos['ordre']
                . " WHERE modele_tournee_jour_code = '" . $condArr['tournee_destination_jour_code']
                . "' AND num_abonne_id IN(" . $infos['liste_abo'] . ");";
                $aRetour[$ct] = $connection->executeQuery($q);
                $ct++;
            }
            else if ($infos['etat'] == 'nouveau')
            {
                $r = "UPDATE `tournee_detail` AS td "
                . "SET modele_tournee_jour_code = '" . $condArr['tournee_destination_jour_code']
                . "', ordre = " . $infos['ordre']
                . ", duree_conduite = NULL "
                . ", heure_debut = NULL "
                . ", duree = '" . $condArr['duree']
                . "', duree_viste_fixe = '" . $condArr['duree_visite_fixe']
                . "', distance_trajet = NULL "
                . ", trajet_cumule = NULL "
                . ", debut_plage_horaire = '" . $condArr['debut_plage_horaire']
                . "', fin_plage_horaire = '" . $condArr['fin_plage_horaire']
                . "' WHERE modele_tournee_jour_code = '" . $condArr['tournee_source_jour_code']
                . "' AND num_abonne_id IN(" . $infos['liste_abo'] . ");";
                $aRetour[$ct] = $connection->executeQuery($r);
                $ct++;
            }
        }
        return $aRetour;
    }
    

    public function upOptimTournee($modele_tournee_jour_code, $nb_stop, $visitTime, $driveTime, $totalTime) {
        $sql = "UPDATE `tournee_detail` "
                . "SET nb_stop = '$nb_stop', temps_visite = '$visitTime', temps_conduite = '$driveTime', temps_tournee = '$totalTime' "
                . "WHERE `modele_tournee_jour_code` = '$modele_tournee_jour_code'";
        $connection = $this->getEntityManager()->getConnection();
        return $connection->executeQuery($sql);
    }

    public function updateTournee($aTourneeData, $newOrder = false) {
        $params = array(
            'modeleTournee' => $aTourneeData['MODELE_TOURNEE'],
            "tourneeDetailId" => $aTourneeData['TOURNEE_DETAIL_ID'],
            "trajetCumule" => $aTourneeData['DISTANCE_CUMUL'],
            "nbStop" => $aTourneeData['NB_STOP'],
            "heureDebut" => $aTourneeData['BEGIN_TIME'],
            "tempsConduite" => $aTourneeData['DRIVE_TIME'],
            "tempsTournee" => $aTourneeData['TOTAL_TIME'],
            "tempsVisite" => $aTourneeData['VISIT_TIME'],
            "distanceTrajet" => $aTourneeData['DISTANCE'],
            "trajetTotalTournee" => $aTourneeData['TRAJET_TOTAL'],
        );

        $sSet = "SET `heure_debut`=:heureDebut,
                        `trajet_cumule`=:trajetCumule,
                        `nb_stop`=:nbStop,
                        `temps_conduite`=:tempsConduite,
                        `temps_tournee`=:tempsTournee,
                        `temps_visite`=:tempsVisite,
                        `trajet_total_tournee`=:trajetTotalTournee,
                        `distance_trajet`=:distanceTrajet ";
        
        // Prise en compte de l'ordre
        if ($newOrder !== FALSE){
            $params['ordre'] = (int) $newOrder;
            $sSet .= ', `ordre`=:ordre';
        }

        $sql = "UPDATE `tournee_detail` "
                . "$sSet"
                . " WHERE `id` = :tourneeDetailId; ";

        $connection = $this->getEntityManager()->getConnection();
        return $connection->executeQuery($sql, $params);
    }

    /**
     * Méthode qui retourne un point d'une tournée pour éventuellement récupérer ses informations générales
     * @param string $code Le code modèle tournée jour
     * @param int $flux_id L'ID du flux concerné
     * @param int $jour_id L'ID du jour concerné
     * @return array $point Le point de la tournée
     */
    public function getSampleTourneePoint($code, $flux_id, $jour_id) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "SELECT * FROM tournee_detail 
                WHERE modele_tournee_jour_code = '$code'
                AND flux_id = $flux_id 
                AND jour_id = $jour_id 
                AND debut_plage_horaire IS NOT NULL
                AND fin_plage_horaire IS NOT NULL
                AND duree_viste_fixe IS NOT NULL
                LIMIT 0, 1;";

        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function getTourneesJoinAbonne($code, $pointlivraison, $date) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "SELECT a.td_id as id,a.vol1,a.vol2 FROM tournee_abo_full a
						WHERE code_modele_tournee = '$code'
						AND point_livraison_id = $pointlivraison
						AND casl_date_parution = '$date'
						GROUP BY abonne_unique_id
    				ORDER BY a.ordre ASC, a.ordre_stop ASC";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }

    public function findTourneesByInsee($insee, $codeJour, $zipcode) {
        $connection = $this->getEntityManager()->getConnection();

        $query = "
					    	SELECT DISTINCT td.modele_tournee_jour_code as code FROM tournee_detail td
					    	WHERE td.insee = $insee
					    	AND modele_tournee_jour_code like '%$codeJour'
					    	GROUP BY td.longitude,td.latitude
					    ";
        $stmt = $connection->executeQuery($query);
        $result = $stmt->fetchAll();
        if (!$result) {
            $query = 'SELECT id FROM commune where cp = ' . $zipcode;
            $stmt = $connection->executeQuery($query);
            $acommuneId = $stmt->fetchAll();

            if ($acommuneId) {
                $sId = '';
                foreach ($acommuneId as $key => $aId) {
                    if ($key)
                        $sId.=',' . $aId['id'];
                    else
                        $sId.=$aId['id'];
                }

                $query = 'SELECT DISTINCT td.modele_tournee_jour_code as code FROM tournee_detail td
	    		 LEFT JOIN abonne_soc ac ON td.num_abonne_id = ac.id
	    		 LEFT JOIN adresse a ON a.abonne_soc_id = ac.id AND (a.type_adresse IS NULL OR a.type_adresse = "L")
	    		 WHERE commune_id in(' . $sId . ')
	    		 AND modele_tournee_jour_code like "%' . $codeJour . '"
	    		 GROUP BY td.longitude,td.latitude
    		';

                $stmt = $connection->executeQuery($query);
                $result = $stmt->fetchAll();
                return $result;
            } else
                return false;
        }
        return $result;
    }

    public function getPointsByTournee($insee, $codeJour, $zipcode) {
        $connection = $this->getEntityManager()->getConnection();
        $sId = '';
        $tournees = $this->findTourneesByInsee($insee, $codeJour, $zipcode);
        if (!$tournees)
            return false;

        foreach ($tournees as $key => $tournee) {
            if ($key)
                $sId .= ',';
            $sId .= "'" . $tournee['code'] . "'";
        }

        if ($sId == '')
            return false;
        $query = "SELECT id,longitude as lon,latitude as lat FROM tournee_detail WHERE modele_tournee_jour_code in($sId);
					    ";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    public function findTourneeIdByTourneeCode($sTourneeCode) {

        $connection = $this->getEntityManager()->getConnection();
        $query = "
    	SELECT DISTINCT td.id FROM tournee_detail td
    	WHERE td.modele_tournee_jour_code = '$sTourneeCode'
            AND td.longitude > 0 AND td.latitude > 0
    	/*AND ordre IS NOT NULL
    	GROUP BY ordre*/
    	ORDER BY ordre asc";
        //print_r($query);
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    /**
     * Extrait d'une tournee. En retour, en plus du point de reference signale par $ptRefId, on a les points de devant et de derriere de ce dernier
     * 
     * @param string $sTourneeCode
     * @param int $ptRefId
     * @return array
     */
    public function ptsAutourRefDansTournee($sTourneeCode, $ptRefId=0) {

        $connection = $this->getEntityManager()->getConnection();
        $query = "
                SELECT DISTINCT td.id, td.longitude AS X, td.latitude AS Y, ordre FROM tournee_detail td
                WHERE td.modele_tournee_jour_code = '$sTourneeCode'
                    AND td.longitude > 0 AND td.latitude > 0
                ORDER BY ordre asc";
        //print_r($query);
        //echo "\r\nptRefId : $ptRefId\r\n";
        $stmt = $connection->executeQuery($query);
        $aPtDevant  = array();
        $aPtRef  = array();
        $aPtDerriere  = array();
        $bPtRefTrouve   = false;
        if($ptRefId>0)
        {
            $retour = array();            
            foreach($stmt as $aArr)
            {
                if($bPtRefTrouve==true)
                {
                    $aPtDerriere  = $aArr;
                    break;
                }
                if($aArr['id']==$ptRefId)
                {
                    $aPtRef = $aArr;
                    $bPtRefTrouve = true;
                }
                else
                {
                    $aPtDevant = $aArr;
                }
            }
            if(!empty($aPtDevant))
            {
                $retour[]   = $aPtDevant;
            }
            if(!empty($aPtRef))
            {
                $retour[]   = $aPtRef;
            }
            if(!empty($aPtDerriere))
            {
                $retour[]   = $aPtDerriere;
            }
            return $retour;
        }
        else
        {
            return $stmt->fetchAll();
        }
    }


    public function getDataByTourneeJour($sTourneeJour) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
				SELECT id,ordre,longitude as X,latitude as Y FROM tournee_detail 
				WHERE Modele_tournee_jour_code = '$sTourneeJour'
				AND longitude != 0
				AND latitude != 0
				GROUP BY longitude,latitude
				ORDER BY ordre;
    	";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    /**
     * Récupere les coordonnées et abonne_soc_id
     * de client_a_servir_logist par date
     * @return array
     */
    public function getCoordinatesCasl($date) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
    	SELECT casl.id,casl.abonne_soc_id,arnvp.geox,arnvp.geoy,casl.flux_id,casl.soc_code_ext,abo.numabo_ext,p.prd_code_ext FROM client_a_servir_logist as casl
    	LEFT JOIN adresse_rnvp arnvp ON arnvp.id = casl.point_livraison_id
    	LEFT JOIN abonne_soc abo ON abo.id = casl.abonne_soc_id
    	LEFT JOIN produit p ON p.id = casl.produit_id
    	WHERE tournee_jour_id is null
    	AND date_distrib ='$date'
    	AND geox is not null
    	AND geoy is not null
    	AND depot_id = 14
  		";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    public function getDataCaslByDepotDate($date, $depot, $flux = 0) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
    	SELECT casl.*,casl.id as casl_id, p.libelle,ad.*,abo.id as num_abo_id,abo.numabo_ext,abo.vol1,abo.vol2,d.libelle as libelle_depot 
    	FROM client_a_servir_logist as casl
    	LEFT JOIN adresse ad ON ad.id = casl.adresse_id
    	LEFT JOIN abonne_soc abo ON abo.id = casl.abonne_soc_id
    	LEFT JOIN produit p ON p.id = casl.produit_id
    	LEFT JOIN depot d ON d.id = casl.depot_id
    	WHERE tournee_jour_id is null
    	AND date_distrib ='$date'
    	AND depot_id = $depot";
        if ($flux > 0)
        {
            $query .= " AND casl.flux_id = " . $flux;
        }
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    public function getTourneeJourByDateDepot($date, $depot, $flux = 0) {
        $query = "
                SELECT DISTINCT mjt.code FROM client_a_servir_logist casl, modele_tournee_jour mjt
                                WHERE casl.depot_id = '$depot'
                                AND casl.date_distrib = '$date'
                                AND casl.tournee_jour_id = mjt.id";
        if ($flux > 0)
        {
            $query .= " AND casl.flux_id = " . $flux;
        }
        $query .= " ORDER BY mjt.code";
                    

        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    /**
     * Méthode qui décale l'ordre des points dans la tournée d'un cran sur les tables CASL et TD
     * @todo Refactorer et déplacer vers le répository de CASL
     * @author Kévin Jean-Baptiste
     * @param int $caslId ID de la ligne dans client_a_servir_logist 
     * @param string $tourneeJourCode Le code modele tournée jour
     * @param int $iTourneeDetailOrdre Ordre du point de livraison dans la tournée dans TD
     * @param int $icaslOrdre Ordre du point dans la tournée dans CASL
     * @param bool $bSameOrder Si TRUE, pas de décalage effectué car le point a le même ordre qu'un autre point dans la tournée
     * @param string $sDateDepart Si défini, les modifications ne seront effectives qu'à partir de la date spécifiée
     */
    public function caslUpdateClassify($caslId, $tourneeJourCode, $iTourneeDetailOrdre, $icaslOrdre, $bSameOrder, $sDateDepart = null) {

        $connection = $this->getEntityManager()->getConnection();
        /** RECUPERATION DE L'ID MODELE_TOURNEE_JOUR **/
        $query = "SELECT * FROM modele_tournee_jour where code = '$tourneeJourCode' AND CURDATE() BETWEEN date_debut AND date_fin";
        $result = $this->_em->getConnection()->prepare($query)->execute();
        $stmt = $connection->executeQuery($query);
        $result = $stmt->fetch();
        $iModeleTourneeJour = $result['id'];
        $jourId = $result['jour_id'];

        /** INCREMENTATION ORDRE LIVRAISON SUPERIEUR OU EGALE A L'ORDRE DU POINT A INSERER **/
        if (!$bSameOrder) {
            $query = "
	    	UPDATE client_a_servir_logist
	    	SET point_livraison_ordre = point_livraison_ordre + 1
	    	WHERE tournee_jour_id = $iModeleTourneeJour
	    	AND point_livraison_ordre >= $iTourneeDetailOrdre
	    	";

            // Ajout de la date
            if (!is_null($sDateDepart)) {
                $query .= " AND date_distrib >= '" . $sDateDepart . "'";
            }

            $this->_em->getConnection()->prepare($query)->execute();
        }

        /** UPDATE ORDRE ET TOURNEE_JOUR_ID POUR CLIENT_A_SERVIR_LOGISTIC **/
        $query = "
    	UPDATE client_a_servir_logist
    	SET point_livraison_ordre = $icaslOrdre, tournee_jour_id = $iModeleTourneeJour
    	WHERE id = $caslId
			";
        // Ajout de la date
        if (!is_null($sDateDepart)) {
            $query .= " AND date_distrib >= '" . $sDateDepart . "'";
        }

        $this->_em->getConnection()->prepare($query)->execute();

        /** UPDATE ORDER TOURNEE DETAIL BY TOURNEE * */
        if (!$bSameOrder) {
            $query = '
	    	UPDATE tournee_detail
	    	SET ordre = ordre + 1 /*,date_modification = now()*/
	    	WHERE modele_tournee_jour_code = "' . $tourneeJourCode . '"
	    	AND ordre >= ' . $iTourneeDetailOrdre . '
	    	';
            $this->_em->getConnection()->prepare($query)->execute();
        }


        $query = '
	    	INSERT INTO tournee_detail (ordre,a_traiter,modele_tournee_jour_code,etat,longitude,latitude,insee,flux_id,num_abonne_id,duree_viste_fixe,debut_plage_horaire,fin_plage_horaire,duree,num_abonne_soc,soc,titre,source_modification,date_modification,jour_id)
    		SELECT ' . $iTourneeDetailOrdre . ' as ordre ,1 as a_traiter,"' . $tourneeJourCode . '" as modele_tournee_jour_code,
				"A l\'heure" as etat ,a.geox as longitude ,a.geoy as latitude ,a.insee ,casl.flux_id,casl.abonne_soc_id,"00:00:30" as duree_viste_fixe
				,"04:00:00" as debut_plage_horaire,"11:00:00" as fin_plage_horaire,"00:00:30" as duree,asoc.numabo_ext as num_abonne_soc, asoc.soc_code_ext as soc, p.prd_code_ext as titre,"utl non-classe" as source_modification,now() as date_modification, '.$jourId.' as jour_id
				FROM client_a_servir_logist casl LEFT JOIN adresse_rnvp a ON a.id = casl.point_livraison_id 
				left join abonne_soc asoc on asoc.id = casl.abonne_soc_id
				left join produit p on p.id = casl.produit_id
				WHERE casl.id = ' . $caslId;

        $this->_em->getConnection()->prepare($query)->execute();
    }

    /*
     * @Author Jean-Baptiste Kevin
     * Mise à jour est insertion de donnée dans "client_a_servir_logist"
     * et "tournee_detail" lors du traitement de ralliement d'un point à une tournée
     */

    public function caslUpdateData($caslId, $iModeleTourneeJour, $order) {
        /** UPDATE NEW ORDER BY TOURNEE_JOUR **/
        $query = "
    	UPDATE client_a_servir_logist
    	SET point_livraison_ordre = point_livraison_ordre + 1
    	WHERE tournee_jour_id = $iModeleTourneeJour
    	AND point_livraison_ordre >= $order";
        $this->_em->getConnection()->prepare($query)->execute();

        /** UPDATE TOURNEE AND ORDER **/
        $query = "
	    	UPDATE client_a_servir_logist 
				SET tournee_jour_id = '$iModeleTourneeJour', point_livraison_ordre = $order
				WHERE id = $caslId";
        $this->_em->getConnection()->prepare($query)->execute();
    }

    public function UpOrderCascade($order, $sTourneeCode) {
        if ($order >= 0){
            $query = '
            UPDATE tournee_detail
            SET ordre = ordre + 1 
            WHERE ordre >= ' . $order . '
            AND modele_tournee_jour_code = "' . $sTourneeCode . '"';
            $this->_em->getConnection()->prepare($query)->execute();
        }
    }

    public function geocodeInsertData($aData) {
        /** UPDATE CLIENT_A_SERVIR_LOGISTIC * */
        $this->caslUpdateData($aData['id_casl'], $aData['modele_tournee_jour_id'], $aData['ordre']);
        /** UPDATE ORDER TOURNEE DETAIL * */
        $this->UpOrderCascade($aData['ordre'], $aData['modele_tournee_jour_code']);
        /** INSERTION DANS TOURNEE DETAIL * */
        $query = '
	    	INSERT INTO tournee_detail (ordre,longitude,latitude,a_traiter,modele_tournee_jour_code,num_abonne_id,num_abonne_soc,duree_viste_fixe,debut_plage_horaire,fin_plage_horaire,duree,soc,titre,insee,flux_id,etat)
    		VALUES (' . $aData['ordre'] . ',' . $aData['longitude'] . ',' . $aData['latitude'] . ',1,"' . $aData['modele_tournee_jour_code'] . '",' . $aData['num_abonne_id'] . ',' . $aData['num_abonne_soc'] . ',"' . $aData['duree_viste_fixe'] . '","' . $aData['debut_plage_horaire'] . '","' . $aData['fin_plage_horaire'] . '","' . $aData['duree_viste_fixe'] . '","' . $aData['soc'] . '","' . $aData['titre'] . '","' . $aData['insee'] . '","' . $aData['flux_id'] . '","A l\'heure")';
        $this->_em->getConnection()->prepare($query)->execute();
    }

    
    /**
     * Insert un point dans TD pour une nouvelle tournee
     * @param type $data Le tableau contenant les informations à insérer
     * @return bool $bReturn TRUE si l'insertion a été faite.
     */
    public function insertPointNouvTournee($data = array()){
        try{
            $insert = " INSERT INTO tournee_detail 
                            (" . implode(', ', array_keys($data)) . ")
                        VALUES
                            (";
            
            // Insertion d'une boucle pour traiter les valeurs nulles
            end($data); $lastElementKey = key($data);
            foreach ($data as $key => $d){
                // Mise en place d'un switch pour traiter les cas particuliers (en attendant l'impact sur les méthodes qui dépendent de celle-ci)
                switch ($key){
                    default:
                        $insert .= is_null($d) ? "''" : "'".$d."'";
                        break;
                    case 'point_livraison_id':
                        $insert .= is_null($d) ? 'NULL' : "'".$d."'";
                        break;
                }
                
                $insert .= $key == $lastElementKey ? '':',';
            }
            
            $insert .= ");";
            
            $iInsertId = $this->_em->getConnection()->prepare($insert)->execute()->lastInsertId();
            return $iInsertId;
            
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    /**
     * Insertion d'une ligne la table tournee_detail
     * @param array $data
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertTourneeDetail($data = array()) {
        try {
            if((isset($data['reperage']) && $data['reperage']==0) || !isset($data['reperage']))
            {
                $aConditions = array(
                    array('fields' => 'num_abonne_id','value' => $data['num_abonne_id'],'operand' => '=')  ,
                    array('fields' => 'jour_id','value' => $data['jour_id'],'operand' => '=')  ,
                );
                $this->_em->getRepository('AmsAdresseBundle:TourneeDetailRecovery')->insertBeforeDelete($aConditions,'delete_'.$data['source_modification']);
                $delete = " DELETE FROM tournee_detail 
                            WHERE
                                num_abonne_id = ".$data['num_abonne_id']." 
                                AND jour_id = ".$data['jour_id']."
                         ";
                $this->_em->getConnection()->prepare($delete)->execute();

                if(isset($data['jour']))
                    unset($data['jour']);
                
                $insert = " INSERT INTO tournee_detail 
                                (" . implode(', ', array_keys($data)) . ")
                            VALUES
                                (";

                // Insertion d'une boucle pour traiter les valeurs nulles
                end($data); $lastElementKey = key($data);
                foreach ($data as $key => $d){
                    // Mise en place d'un switch pour traiter les cas particuliers (en attendant l'impact sur les méthodes qui dépendent de celle-ci)
                    switch ($key){
                        default:
                            $insert .= is_null($d) ? "''" : "'".$d."'";
                            break;
                        case 'point_livraison_id':
                            $insert .= is_null($d) ? 'NULL' : "'".$d."'";
                            break;
                    }

                    $insert .= $key == $lastElementKey ? '':',';
                }

                $insert .= ");";

                return $this->_em->getConnection()->prepare($insert)->execute();
            }
            // Dans le cas des reperages
            else
            {
                // On ne stocke par dans la table "tournee_detail" les tournees des reperages
                if(isset($data["num_abonne_id"]) && isset($data["modele_tournee_jour_code"]))
                {
                    $mt_id  = 0;
                    $sSlcModeleTournee  = " SELECT
                                                MAX(mt.id) AS mt_id
                                            FROM
                                                modele_tournee_jour mtj
                                                INNER JOIN modele_tournee mt ON mtj.tournee_id = mt.id AND mt.actif = 1 AND ".(isset($data["jour"]) ? "'".trim($data["jour"])."'" : 'CURDATE()')." BETWEEN mtj.date_debut AND mtj.date_fin
                                            WHERE
                                                mtj.code = '".$data["modele_tournee_jour_code"]."'
                                        ";
                    $sUpdate    = " UPDATE reperage 
                                        SET
                                            tournee_id = (".$sSlcModeleTournee.")
                                        WHERE abonne_soc_id = ".$data["num_abonne_id"]." ";
                        
                    return $this->_em->getConnection()->prepare($sUpdate)->execute();
                }
            }
            
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

    public function tmp() {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
    	SELECT * FROM tournee_detail 
			WHERE Modele_tournee_jour_code = '045NT1030VE'
			GROUP BY ORDRE
			ORDER BY ordre";

        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    /**
     * Méthode qui décale l'ordre des points dans une tournée
     * @param int $iOrdreDepart L'ordre de la tournée à partir duquel les points de livraison sont décalés
     * @param string $sTourneeJourCode Le code modele tournee jour de la tournée impactée
     * @param string $sOperateur + ou - pour l'incrémentation ou la décrémentation (+ par défaut)
     * @param string $sExceptions La liste des IDs à ne pas prendre en compte
     * @param int $iPas L'ampleur du décalage (1 par défaut)
     */
    public function decalerOrdrePoints($iOrdreDepart, $sTourneeJourCode, $sOperateur = '+', $iPas = 1, $sExceptions = NULL) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
	    	UPDATE tournee_detail
	    	SET ordre = ordre $sOperateur $iPas /*, date_modification = NOW(), source_modification = 'decalage_ordre'*/
	    	WHERE modele_tournee_jour_code = '$sTourneeJourCode'
	    	AND ordre >= $iOrdreDepart
                AND latitude > 0
                AND longitude > 0
	    	";
        
        if (!is_null($sExceptions)){
            $query .= " AND point_livraison_id NOT IN ($sExceptions)";
        }

        return $this->_em->getConnection()->prepare($query)->execute();
    }
    
    /**
     * Retourne les points d'une tournée dédoublonnés par coordonnées et triés par ordre.
     * Exclut les repérages du jeu de résultat
     * @param string $sTourneeJourCode Le modele tournée jour code
     * @return array Le jeu d'enregistrement
     */
    public function getPointsTournee($sTourneeJourCode){
        $connection = $this->getEntityManager()->getConnection();
        $query = "
            SELECT * FROM tournee_detail 
	WHERE 
		modele_tournee_jour_code = '".$sTourneeJourCode."' 
	AND
		latitude IS NOT NULL
	AND
		longitude IS NOT NULL		

	GROUP BY CONCAT(longitude,'|',latitude)
	
	ORDER BY ordre 
            ";

        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    /**
     * Méthode qui liste les points d'une tournée dans l'ordre en les dédoublonnant et excluant les coordonnées nulles
     * @param string $sTourneeJourCode
     * @return array Les points de la tournée
     */
    public function listerPointsTourneeOrdonnes($sTourneeJourCode) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
            SELECT *, CONCAT(longitude, '|', latitude) AS coords FROM tournee_detail 
            WHERE modele_tournee_jour_code = '$sTourneeJourCode'
            AND longitude > 0
            AND latitude > 0 ";
        $query .= "
            GROUP BY coords
            ORDER BY ordre ASC
            ";

        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }

    public function findOneTourneeByTimeNotNull($tourneeCode) {

        $connection = $this->getEntityManager()->getConnection();
        $query = "
	    	SELECT * FROM tournee_detail
            WHERE modele_tournee_jour_code = '$tourneeCode'
            AND debut_plage_horaire is not null
    	";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetch();
    }

    public function setPlagesHoraire($tourneeCode, $dateDebut, $dateFin) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
              UPDATE `tournee_detail` AS td
              SET debut_plage_horaire = '$dateDebut',fin_plage_horaire = '$dateFin'
              WHERE modele_tournee_jour_code = '$tourneeCode' 
             ";
        return $connection->executeQuery($q);
    }
    
    /**
     * Retourne les points candidats les plus proches selon les criteres definis par $aCritere 
     * @param array $aPoints
     * @param array $aCritere
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function ptsCandidatsProches($aPoints=array('latitude'=>0, 'longitude'=>0), $aCritere=array('rayon_max'=>1, 'nb_pts_proches_max'=>10))
    {
        $aPtsCandidats = array();
        try {
            $sSlct  = " SELECT 
                            MAX(td.id) AS id
                            , ar.geox AS longitude, ar.geoy AS latitude, td.ordre
                            , ((ACOS(SIN(".$aPoints['latitude']." * PI() / 180) * SIN(ar.geoy * PI() / 180) + COS(".$aPoints['latitude']." * PI() / 180) * COS(ar.geoy * PI() / 180) * COS((".$aPoints['longitude']." - ar.geox) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance
                        FROM 
                            tournee_detail td
                            INNER JOIN adresse a ON a.abonne_soc_id = td.num_abonne_id AND a.adresse_rnvp_etat_id <= 2 AND CURRENT_DATE() BETWEEN a.date_debut AND a.date_fin AND (a.type_adresse IS NULL OR a.type_adresse = 'L')
                            INNER JOIN adresse_rnvp ar ON a.rnvp_id = ar.id AND ar.geo_etat IN (1, 2)
                            INNER JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND CURRENT_DATE() BETWEEN mtj.date_debut AND mtj.date_fin
                            INNER JOIN modele_tournee mt ON mtj.tournee_id = mt.id AND mt.actif = 1
                            INNER JOIN groupe_tournee grt ON mt.groupe_id = grt.id
                        WHERE
                            1 = 1 
                            AND exclure_ressource <> 1
                            AND td.ordre IS NOT NULL AND td.ordre > 0 
                            "; 

            if(isset($aCritere['depot_id']) && !in_array(trim($aCritere['depot_id']), array('', 0))) // si depot_id defini, on se limite par rapport a ce depot
            {
                $sSlct  .= " AND grt.depot_id = '".$aCritere['depot_id']."' ";
            }
            if(isset($aCritere['jour_id']) && !in_array(trim($aCritere['jour_id']), array('', 0))) // si id_jour defini, on se limite par au jour
            {
                $sSlct  .= " AND td.jour_id = '".$aCritere['jour_id']."' ";
            }
            if(isset($aCritere['flux_id']) && !in_array(trim($aCritere['flux_id']), array('', 0))) // si flux_id defini, on se limite par flux (nuit ou jour)
            {
                $sSlct  .= " AND td.flux_id = '".$aCritere['flux_id']."' ";
            }
            // La valeur de $aCritere[rayon_max] est un numerique exprime en "miles" (==1.609344 km)
            if(!isset($aCritere['rayon_max']))
            {
                $aCritere['rayon_max']   = '2.5';
            }
            $sSlct  .= " AND ((ACOS(SIN(".$aPoints['latitude']." * PI() / 180) * SIN(ar.geoy * PI() / 180) + COS(".$aPoints['latitude']." * PI() / 180) * COS(ar.geoy * PI() / 180) * COS((".$aPoints['longitude']." - ar.geox) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) < ".$aCritere['rayon_max']." ";
            if(isset($aCritere['insee']) && !in_array(trim($aCritere['insee']), array('', 0))) // si insee defini, on se limite par rapport a la ville
            {
                $sSlct  .= " AND td.insee = '".$aCritere['insee']."' ";
            }
            if(isset($aCritere['tournee']) && trim($aCritere['tournee']) != '')
            {
                $sSlct  .= " AND td.modele_tournee_jour_code = '".$aCritere['tournee']."' ";
            }
            if(isset($aPoints['abonne_soc_id']))
            {
                $sSlct  .= " AND td.num_abonne_id <> ".$aPoints['abonne_soc_id']." ";
            }
            $sSlct  .= " GROUP BY 
                            ar.geox, ar.geoy
                            , ((ACOS(SIN(".$aPoints['latitude']." * PI() / 180) * SIN(ar.geoy * PI() / 180) + COS(".$aPoints['latitude']." * PI() / 180) * COS(ar.geoy * PI() / 180) * COS((".$aPoints['longitude']." - ar.geox) * PI() / 180)) * 180 / PI()) * 60 * 1.1515)
                        ORDER BY 
                            ((ACOS(SIN(".$aPoints['latitude']." * PI() / 180) * SIN(ar.geoy * PI() / 180) + COS(".$aPoints['latitude']." * PI() / 180) * COS(ar.geoy * PI() / 180) * COS((".$aPoints['longitude']." - ar.geox) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) ASC
                            , td.ordre DESC ";
            if(!isset($aCritere['nb_pts_proches_max']))
            {
                $aCritere['nb_pts_proches_max']   = 10;
            }
            $sSlct  .= " LIMIT 0, ".$aCritere['nb_pts_proches_max']." ";
//            print_r($sSlct);exit;
            $aRes   = $this->_em->getConnection()->executeQuery($sSlct)->fetchAll();
            foreach($aRes as $aArr)
            {
                if(isset($aCritere['ordre']))
                $aPtsCandidats[]    = array(
					'id' => $aArr['id'],
					'x'  => $aArr['longitude'],
					'y'  => $aArr['latitude'],
					'ordre'  => $aArr['ordre'],
                    );
                else
                $aPtsCandidats[]    = array(
					'id' => $aArr['id'],
					'x'  => $aArr['longitude'],
					'y'  => $aArr['latitude'],
                    );

            }
            
            return $aPtsCandidats;
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function deleteTourneeByModeleTournee($aModelTournee) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
        DELETE
        FROM 
            tournee_detail
        WHERE
            modele_tournee_jour_code IN ($aModelTournee)
        ";
        $stmt = $connection->executeQuery($q);
        return $stmt;
    }
    
    /**
     * Méthode qui met à jour les l'heure de debut dans tournee_detail
     * Utilise dans la commande "TourneeDetailHeure"
     */
    public function updateBeginHour($codeTournee) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "UPDATE tournee_detail td
                    LEFT JOIN
                modele_tournee_jour mtj ON mtj.code = td.modele_tournee_jour_code
                    AND curdate() between mtj.date_debut AND mtj.date_fin
                    LEFT JOIN
                modele_tournee mt ON mt.id = mtj.tournee_id
                    LEFT JOIN
                groupe_tournee gt ON gt.id = mt.groupe_id 
            SET 
                td.heure_debut = gt.heure_debut
            WHERE
                td.modele_tournee_jour_code = '$codeTournee'
            ";
        return $connection->executeQuery($q);
    }
    
    /**
     * Méthode qui met à jour les points de livraison null dans tournee_detail
     * Utilise dans la commande "TourneeDetailPointLivraison"
     */
    public function updatePointLivraison($data = false) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "UPDATE tournee_detail td
                    LEFT JOIN
                adresse ad ON ad.abonne_soc_id = td.num_abonne_id AND CURDATE() BETWEEN date_debut and date_fin AND (ad.type_adresse IS NULL OR ad.type_adresse = 'L')
            SET 
                td.point_livraison_id = ad.point_livraison_id
            WHERE
                td.point_livraison_id is null ";
        if($data['modele_tournee_jour_code'])
            $q .="
            AND
                td.modele_tournee_jour_code = '".$data['modele_tournee_jour_code']."'"
            ;
        return $connection->executeQuery($q);
    }
    
    public function UpdatePointLivraisonScriptRepair($iPointLivraisonId,$sId)
    {
        try {
            $slct   = " UPDATE tournee_detail
                        SET 
                            point_livraison_id = $iPointLivraisonId
                        WHERE point_livraison_id IN($sId)
                    ";
            $this->_em->getConnection()->prepare($slct)->execute();
        } 
        catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }
    
    public function getAboProperlyClassify($codeTournee) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
            SELECT 
                td.ordre,ad.point_livraison_id,arnvp.geox,arnvp.geoy
            FROM
                tournee_detail td
                JOIN adresse ad ON ad.abonne_soc_id = td.num_abonne_id AND CURDATE() BETWEEN date_debut and date_fin AND (ad.type_adresse IS NULL OR ad.type_adresse = 'L')
                JOIN adresse_rnvp arnvp ON arnvp.id = ad.point_livraison_id
            WHERE
                td.modele_tournee_jour_code = '$codeTournee'
                    AND td.exclure_ressource <> 1
            GROUP BY ad.point_livraison_id
            ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }
    
    public function getAboToReintegrate($codeTournee) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
            SELECT 
                td.id,ad.point_livraison_id,arnvp.geox,arnvp.geoy
            FROM
                tournee_detail td
                JOIN adresse ad ON ad.abonne_soc_id = td.num_abonne_id AND CURDATE() BETWEEN date_debut and date_fin AND (ad.type_adresse IS NULL OR ad.type_adresse = 'L')
                JOIN adresse_rnvp arnvp ON arnvp.id = ad.point_livraison_id
            WHERE
                td.modele_tournee_jour_code = '$codeTournee'
                    AND td.exclure_ressource = 1
            GROUP BY ad.point_livraison_id
            ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetchAll();
    }
    
    public function updateOrderTourneeByPointLivraison($TourneeDestination,$TourneeOrigine,$ordre,$pointLivraisonId)
    {
        $q= "
            UPDATE tournee_detail td
                JOIN adresse ad ON ad.abonne_soc_id = td.num_abonne_id AND CURDATE() BETWEEN date_debut and date_fin AND (ad.type_adresse IS NULL OR ad.type_adresse = 'L')
                JOIN adresse_rnvp arnvp ON arnvp.id = ad.point_livraison_id
            SET 
                td.ordre = $ordre,
                td.modele_tournee_jour_code = '$TourneeDestination'
            WHERE modele_tournee_jour_code IN ('$TourneeDestination','$TourneeOrigine')
                AND ad.point_livraison_id = $pointLivraisonId
            ";
        $this->_em->getConnection()->prepare($q)->execute();
    }
    
    public function updateOrderByPointLivraison($codeTournee,$ordre,$pointLivraisonId, $sig = "reprise_td_commande_repair")
    {
        $q= "
            UPDATE tournee_detail td
                JOIN adresse ad ON ad.abonne_soc_id = td.num_abonne_id AND CURDATE() BETWEEN date_debut and date_fin AND (ad.type_adresse IS NULL OR ad.type_adresse = 'L')
                JOIN adresse_rnvp arnvp ON arnvp.id = ad.point_livraison_id
            SET 
                td.ordre = $ordre
                ,exclure_ressource = NULL
                , source_modification = '$sig'
                , date_modification = NOW() 
            WHERE modele_tournee_jour_code = '$codeTournee'
                AND ad.point_livraison_id = $pointLivraisonId
            ";
        $this->_em->getConnection()->prepare($q)->execute();
    }
    
    public function getAboOrderBefore($codeTournee,$order) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
            SELECT 
                max(td.ordre) as ordre, td.id, ad.point_livraison_id, arnvp.geox as X,arnvp.geoy as Y
            FROM
                tournee_detail td
                JOIN adresse ad ON ad.abonne_soc_id = td.num_abonne_id AND CURDATE() BETWEEN date_debut and date_fin AND (ad.type_adresse IS NULL OR ad.type_adresse = 'L')
                JOIN adresse_rnvp arnvp ON arnvp.id = ad.point_livraison_id
            WHERE
                td.modele_tournee_jour_code = '$codeTournee'
                    AND td.exclure_ressource <> 1
                    AND td.ordre < $order
            ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetch();
    }
    
    public function getAboOrderAfter($codeTournee,$order) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
            SELECT 
                min(td.ordre) as ordre,td.id,ad.point_livraison_id,arnvp.geox as X,arnvp.geoy as Y
            FROM
                tournee_detail td
                JOIN adresse ad ON ad.abonne_soc_id = td.num_abonne_id AND CURDATE() BETWEEN date_debut and date_fin AND (ad.type_adresse IS NULL OR ad.type_adresse = 'L')
                JOIN adresse_rnvp arnvp ON arnvp.id = ad.point_livraison_id
            WHERE
                td.modele_tournee_jour_code = '$codeTournee'
                    AND td.exclure_ressource <> 1
                    AND td.ordre > $order
            ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetch();
    }
    
    public function updateOrderById($id, $order, $code, $sig = "reprise_td_commande_repair2") {
        $this->setDecalage($order, $code);
        sleep(1);
        $this->setOrdreIntegration($id, $order, $sig);
    }
    
    public function setDecalage($order, $code){
        $q = "
            UPDATE tournee_detail
                SET ordre = ordre + 1, source_modification = 'reprise_td_commande_repair_decal', date_modification = NOW()
            WHERE modele_tournee_jour_code = '$code'
                AND ordre >= $order
           ";
            $this->_em->getConnection()->executeQuery($q);
    }
    
    public function setOrdreIntegration($id, $order, $sig){
        $q = "
            UPDATE tournee_detail
                SET ordre = $order , exclure_ressource = NULL, source_modification = 'sig--$sig', date_modification = NOW()
            WHERE id = $id
            ";
        $this->_em->getConnection()->executeQuery($q);
    }
    
    /**
     * Méthode qui recrée un ordre "propre" d'un modele de tournée dans TD
     * A utiliser si aucune passe de marquage n'est faite sur les lignes à exclure
     * @param string $sTourneeJourCode Le MTJ Code de la tournée
     * @return bool Le statut d'exécution de la requête
     */
     public function resetOrdreTournee($sTourneeJourCode, $bLivresUniquement) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
            UPDATE tournee_detail AS td_ref 
 INNER JOIN (
SELECT (@cnt := @cnt + 1) AS nouvel_ordre, t.* FROM (
	SELECT td.ordre, longitude, latitude, modele_tournee_jour_code, num_abonne_id, ordre_stop, td.point_livraison_id , td.id AS td_id,
	mtj.tournee_id , mtj.id AS mtj_id
	FROM  tournee_detail AS td
	JOIN modele_tournee_jour AS mtj ON mtj.code = modele_tournee_jour_code ";
        
        // Restriction de la requête aux abonnés ayant déjà été livrés
        if ($bLivresUniquement){
            $query .= " JOIN client_a_servir_logist AS casl ON casl.tournee_jour_id = mtj.id";
        }
        
	$query .= " WHERE modele_tournee_jour_code ='$sTourneeJourCode'";
        
        // Restriction de la requête aux abonnés ayant déjà été livrés
        if ($bLivresUniquement){
            $query .= " AND casl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin ";
        }
	
	$query .= "GROUP BY td_id
	ORDER BY ordre ASC
	LIMIT 99999999		
	 ) AS t
	CROSS JOIN (SELECT @cnt := 0) AS dummy
) AS _ref_modif
	SET td_ref.ordre = nouvel_ordre
	WHERE 	td_ref.modele_tournee_jour_code ='$sTourneeJourCode'
		AND td_id = td_ref.id	
	;
            ";

        return $connection->executeQuery($query);
    }
    
    /**
     * Méthode qui recrée un ordre "propre" d'un modele de tournée dans TD
     * A utiliser SI une passe de marquage a été faite sur les lignes à exclure
     * @param string $sTourneeJourCode Le MTJ Code de la tournée
     * @return bool Le statut d'exécution de la requête
     */
     public function resetOrdreTourneeApresMarquage($sTourneeJourCode) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
            UPDATE tournee_detail AS td_ref 
 INNER JOIN (
SELECT (@cnt := @cnt + 1) AS nouvel_ordre, t.* FROM (
	SELECT td.ordre, longitude, latitude, modele_tournee_jour_code, num_abonne_id, ordre_stop, td.point_livraison_id , td.id AS td_id
	FROM  tournee_detail AS td 
        
        WHERE modele_tournee_jour_code ='$sTourneeJourCode' 
        AND exclure_ressource <> 1    
        GROUP BY point_livraison_id
	ORDER BY ordre ASC
	LIMIT 99999999		
	 ) AS t
	CROSS JOIN (SELECT @cnt := 0) AS dummy
) AS _ref_modif
	SET td_ref.ordre = nouvel_ordre, date_modification= NOW(), source_modification='reprise_td_compression_ordre'
	WHERE 	td_ref.modele_tournee_jour_code ='$sTourneeJourCode'
		AND _ref_modif.point_livraison_id = td_ref.point_livraison_id	
	;
            ";

        return $connection->executeQuery($query); 
     }
    
    /**
     * Marque les points de TD qui n'ont jamais été livrés dans CASL, au sein de cette même tournée.
     * Permet donc d'isoler les repérages.
     * @param string $sTourneeJourCode Le MTJ Code de la tournée
     * @param string $sDate La date à prendre en compte p/r à CASL
     */
    public function marquerNonLivresTournee($sTourneeJourCode, $sDate = NULL){
        $connection = $this->getEntityManager()->getConnection();
        $query = "UPDATE tournee_detail AS t INNER JOIN (
SELECT 

 td.* FROM tournee_detail AS td

	WHERE td.modele_tournee_jour_code ='$sTourneeJourCode'
	AND
	td.num_abonne_id NOT IN 
	
	(
SELECT 
 		num_abonne_id 
		FROM  tournee_detail AS td
		JOIN modele_tournee_jour AS mtj ON mtj.code = modele_tournee_jour_code
		JOIN client_a_servir_logist AS casl ON casl.tournee_jour_id = mtj.id
                JOIN adresse as ad ON ad.abonne_soc_id = td.num_abonne_id AND '$sDate' BETWEEN ad.date_debut AND ad.date_fin AND (ad.type_adresse IS NULL OR ad.type_adresse = 'L')
		WHERE modele_tournee_jour_code ='$sTourneeJourCode'
		AND casl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin
                AND ad.point_livraison_id = casl.point_livraison_id ";
                
        // Prise en compte de la date
        if (!is_null($sDate)){
            $query .= " AND casl.date_distrib <= '$sDate' ";
        }
        
        $query .= " AND casl.abonne_soc_id = td.num_abonne_id
		GROUP BY td.id
		ORDER BY td.ordre ASC
        )
) AS A ON A.id = t.id
SET t.exclure_ressource = 1, t.date_modification = NOW(), t.source_modification = 'correctif_donnees_exclusion_non_livres'"
                ;
        
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $iNbPoints = $stmt->rowCount();
        
         return $iNbPoints;
    }
    
    /**
     * Retourne la liste des points marqués non livrés d'une tournée
     * @param string $sTourneeJourCode Le MTJ Code de la tournée
     */
    public function listerNonLivres($sTourneeJourCode){
        $connection = $this->getEntityManager()->getConnection();
        $query = "
    	SELECT * FROM tournee_detail 
			WHERE Modele_tournee_jour_code = '$sTourneeJourCode'
			AND exclure_ressource = 1";

        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }
    
    /**
     * Marque les lignes de TD ayant été exclues à intégrer via le classement automatique dans le cadre d'une reprise de TD
     * @param string $sTourneeJourCode Le MTJ Code de la tournée
     * @param string $sDate La date à prendre en compte p/r à CASL
     * @return int $iNbPoints Le nombre de lignes modifiées
     */
    public function marquerLignesAIntegrer($sTourneeJourCode, $sDate = NULL){
        $connection = $this->getEntityManager()->getConnection();
        $query = "UPDATE tournee_detail AS t INNER JOIN (
SELECT td.id AS td_id, td.* FROM tournee_detail AS td
	JOIN modele_tournee_jour AS mtj ON mtj.code = td.modele_tournee_jour_code
	JOIN client_a_servir_logist AS casl ON mtj.id = casl.tournee_jour_id
			WHERE td.modele_tournee_jour_code = '$sTourneeJourCode'
			AND td.exclure_ressource = 1
			AND casl.date_distrib BETWEEN mtj.date_debut AND mtj.date_fin ";
        if (!is_null($sDate)){
            $query .= " AND casl.date_distrib >= '$sDate' ";
        }
        
        $query .= " AND casl.abonne_soc_id = td.num_abonne_id
) AS td_ref ON td_ref.td_id = t.id
SET t.reperage = -1, t.source_modification = 'correctif_donnees_marquage_re-integration_point', t.date_modification = NOW() ;";

        $stmt = $connection->prepare($query);
        $stmt->execute();
        $iNbPoints = $stmt->rowCount();
        
        return $iNbPoints;
    }
    
    /**
     * Supprime les repérages de la table TD
     * A n'utiliser que dans le cadre d'une reprise de TD, après la passe de ré-intégration des abonnés à livrés, via classement auto
     * @param string $sTourneeJourCode Le MTJ Code de la tournée
     * @return int $iNbPoints Le nombre de lignes supprimées
     */
    public function supprimerReperages($sTourneeJourCode){
        $connection = $this->getEntityManager()->getConnection();
        $query = "DELETE FROM tournee_detail WHERE exclure_ressource = 1 AND reperage <> -1 and modele_tournee_jour_code ='$sTourneeJourCode';";
        
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $iNbPoints = $stmt->rowCount();
        
        return $iNbPoints;
    }
    
    /**
     * Copie l'ordre d'une tournée depuis CASL vers TD à une date donnée
     * @param string $sTourneeJourCode Le MTJ Code de la tournée
     * @param string $sDate La date à prendre en compte pour la tournée CASL
     * @return int $iNbPoints Le nombre de lignes modifiées
     */
    public function copierOrdreDeCASL($sTourneeJourCode, $sDate){
        $connection = $this->getEntityManager()->getConnection();

        $query = "UPDATE tournee_detail AS td_ref 
	 INNER JOIN (
SELECT (@cnt := @cnt + 1) AS nouvel_ordre, c.* FROM (
SELECT MIN(casl.point_livraison_ordre) AS ordre_minmax, mtj.code AS mtj_code, casl.* FROM  client_a_servir_logist AS casl
	JOIN modele_tournee_jour AS mtj ON mtj.id = casl.tournee_jour_id
	WHERE 
		casl.date_distrib = '$sDate'
		AND
		mtj.code = '$sTourneeJourCode'
	GROUP BY casl.point_livraison_id
	ORDER BY ordre_minmax ASC
) AS c
	CROSS JOIN (SELECT @cnt := 0) AS dummy	)
	AS ref_modif
        JOIN adresse ad ON ad.abonne_soc_id = td_ref.num_abonne_id AND ref_modif.date_distrib BETWEEN ad.date_debut AND ad.date_fin AND (ad.type_adresse IS NULL OR ad.type_adresse = 'L')
	SET td_ref.ordre = nouvel_ordre, td_ref.source_modification = 'reprise_td_copie_ordre_CASL', date_modification = NOW()
	WHERE ad.point_livraison_id = ref_modif.point_livraison_id
        AND
        td_ref.exclure_ressource <> 1
	AND
	td_ref.modele_tournee_jour_code = mtj_code";
        
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $iNbPoints = $stmt->rowCount();
        
        return $iNbPoints;
    }
    
    
    /**
     * Retourne la liste des tournées contenant des abonnés à ré-intégrer
     * @param string $sJourCode Le code jour LU|MA|ME|JE|VE|SA|DI
     * @return array
     */
    public function tourneesAvecAbosAReintegrer($sJourCode = NULL){
        $connection = $this->getEntityManager()->getConnection();

        $query = "SELECT DISTINCT modele_tournee_jour_code FROM tournee_detail WHERE reperage = -1";
        
        if (!is_null($sJourCode)){
            $query .= " AND modele_tournee_jour_code LIKE '%$sJourCode'";
        }
        
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }
    
    
    /**
     * Retourne la liste des abonnées en doublon pour un même jour id dans tournee detail
     * @return array
     */
    public function getDoubloon($maxResult=1){
        $connection = $this->getEntityManager()->getConnection();

        $query = "
                SELECT 
                    count(num_abonne_id) as total, num_abonne_id, jour_id,modele_tournee_jour_code,ordre,source_modification,date_modification
                FROM
                    tournee_detail
                GROUP BY num_abonne_id , jour_id
                HAVING total > $maxResult
                ";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }
    
    /**
     * Retourne la liste des abonnées en doublon pour un même jour id dans tournee detail
     * @return array
     */
    public function getAllAbonneJourId($abonneId,$jourId){
        $connection = $this->getEntityManager()->getConnection();

        $query = "
                SELECT 
                    num_abonne_id, jour_id,modele_tournee_jour_code,ordre,source_modification,date_modification,flux_id
                FROM
                    tournee_detail
                WHERE num_abonne_id = $abonneId 
                AND jour_id = $jourId
                ";
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }
    
    
    public function getTourneeOrderZero($flux = 'N'){
        $connection = $this->getEntityManager()->getConnection();
        $query = "
                SELECT 
                    count(*)
                FROM
                    (SELECT 
                        count(*)
                    FROM
                        tournee_detail
                    where
                        ordre = 0
                            AND substring(Modele_tournee_jour_code, 4, 1) = '$flux'
                    GROUP BY Modele_tournee_jour_code
                    ) as tmp
                ";
        
        $stmt = $connection->executeQuery($query);
        return $stmt->fetch();
    }
    
    public function getActiveTourneeOrderZero($flux = 'N'){
        $connection = $this->getEntityManager()->getConnection();
        $query = "
                SELECT 
                    count(*)
                FROM
                    (SELECT 
                        count(*)
                    FROM
                        tournee_detail td
                    JOIN modele_tournee_jour mtj ON mtj.code= modele_tournee_jour_code
                    WHERE
                        td.ordre = 0
                            AND substring(Modele_tournee_jour_code, 4, 1) = '$flux'
                            AND now() between mtj.date_debut and mtj.date_fin
                    GROUP BY Modele_tournee_jour_code
                    ) as tmp
                ";
        
        $stmt = $connection->executeQuery($query);
        return $stmt->fetch();
    }
    
    /**
     * RETOURNE LE MAX ID D'UN ABONNE POUR UN JOUR ID DONNER D'UNE TOURNEE
     * ET OPTIMISER PAR GEOCONCEPT
     * @param int $numAbonneId 
     * @param int $jourId
     * @return array
     */
    public function isOptimSourceModification($numAbonneId,$jourId){
        $connection = $this->getEntityManager()->getConnection();

        $query = "
                SELECT 
                    max(id) as id
                FROM
                    tournee_detail
                WHERE
                    jour_id = $jourId
                        AND num_abonne_id = $numAbonneId
                        AND source_modification = 'optim'
                ";
        
        $stmt = $connection->executeQuery($query);
        return $stmt->fetch();
    }
    
    /**
     * METHODE CREER POUR LE DEDOUBLONNAGE DONNER DANS TOURNEE DETAIL
     * VERIFIE QUE L ABONNE POSSEDE (INSEE,LONG,LAT)
     * @param type $numAbonneId
     * @param type $jourId
     * @return type
     */
    public function hasLongLatInsee($numAbonneId,$jourId){
        $connection = $this->getEntityManager()->getConnection();
        $query = "
                SELECT 
                    max(id) as id
                FROM
                    tournee_detail
                WHERE
                    jour_id = $jourId
                        AND num_abonne_id = $numAbonneId
                        AND longitude > 0
                        AND latitude > 0
                        AND insee > 0
                ";
        
        $stmt = $connection->executeQuery($query);
        return $stmt->fetch();
    }
    
    /**
     * RENVOIE L'ID MAX DES ABONNEES EN DOUBLONS PAR JOUR ID
     * @param type $numAbonneId
     * @param type $jourId
     * @return type
     */
    public function maxIdDoubloon($numAbonneId,$jourId){
        $connection = $this->getEntityManager()->getConnection();
        $query = "
                SELECT 
                    max(id) as id
                FROM
                    tournee_detail
                WHERE
                    jour_id = $jourId
                        AND num_abonne_id = $numAbonneId
                ";
        
        $stmt = $connection->executeQuery($query);
        return $stmt->fetch();
    }
    
    /**
     * Efface les abonnées en doublon pour un même jour id dans tournee detail
     * @param int $numAbonneId
     * @param int $jourId
     * @param int $idRef
     * @return type
     */
    public function deleteDoubloon($numAbonneId,$jourId,$idRef) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
        DELETE
        FROM 
            tournee_detail
        WHERE
            num_abonne_id =  $numAbonneId
                AND jour_id = $jourId
                AND id <> $idRef
        ";
        $stmt = $connection->executeQuery($q);
        return $stmt;
    }
    
    public function getAbonneJourId($numAbonneId,$jourId) {
        $connection = $this->getEntityManager()->getConnection();
        $q = "
            SELECT * 
            FROM 
                tournee_detail
            WHERE
                num_abonne_id =  $numAbonneId
                    AND jour_id = $jourId
        ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetch();
    }
    
    public function updateCodeTourneeAbonneJourId($abonneSocId,$newCodeTournee,$JourId) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
            UPDATE tournee_detail 
            SET modele_tournee_jour_code = '$newCodeTournee'
            WHERE num_abonne_id = $abonneSocId
                AND jour_id = $JourId
            ";

        return $connection->executeQuery($query); 
    }    

    public function updateCodeTourneeById($tourneeDetailId,$newCodeTournee,$aSourceModification = false) {
        $connection = $this->getEntityManager()->getConnection();
        $sSourceModification = '';
        if($aSourceModification) {
            foreach($aSourceModification as $key=>$dataSource)
                $sSourceModification .= ','.$key.'= "'.$dataSource.'"';
        }

        $query = "
            UPDATE tournee_detail 
            SET modele_tournee_jour_code = '$newCodeTournee', ordre = 0 $sSourceModification
            WHERE id = $tourneeDetailId
            ";

        return $connection->executeQuery($query); 
    }
     
    public function getNbOrdreIncoherenceCasl($codeTournee,$dateDistrib){
        $connection = $this->getEntityManager()->getConnection();
        $this->_em->getConnection()->prepare(" SET @Order = 0")->execute();
        $q="
            SELECT COUNT(*) as nb_incoherence FROM (
            SELECT casl.id,mtj.code,casl.point_livraison_id,td.ordre,casl.point_livraison_ordre,
                if(@Order <= casl.point_livraison_ordre,'OK','NOK') as incoherence,
                @Order :=casl.point_livraison_ordre
                from tournee_detail td
                JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND now() BETWEEN mtj.date_debut AND mtj.date_fin
                JOIN client_a_servir_logist casl ON casl.abonne_soc_id = td.num_abonne_id AND date_distrib = '$dateDistrib'
                where Modele_tournee_jour_code = '$codeTournee'
                AND td.jour_id = mtj.jour_id
                ORDER BY td.ordre
            ) as tmp_table
            WHERE incoherence = 'NOK'
        ";
        $stmt = $connection->executeQuery($q);
        return $stmt->fetch();
    }
    
    public function getIncoherenceFluxMTJ($sFlux,$iFlux){
        $connection = $this->getEntityManager()->getConnection();
        $q="
            SELECT * FROM  (
                SELECT *,if(substring(Modele_tournee_jour_code,4,1) = '$sFlux' ,'NOK','OK') as test_incoherence
                FROM tournee_detail 
                WHERE flux_id = $iFlux) as tabl
            WHERE test_incoherence = 'NOK';
        ";
        return $connection->executeQuery($q)->fetchAll();
    }

    public function getDataByAbonneSoc($abonneSocId) {
        $connection = $this->getEntityManager()->getConnection();
        $query = "
            SELECT td.jour_id,modele_tournee_jour_code,d.libelle,d.id as depot_id,rj.libelle as libelle_jour,mtj.id as tournee_jour_id,td.flux_id
            FROM tournee_detail td
            JOIN depot d ON d.code = substr(td.modele_tournee_jour_code,1,3)
            JOIN ref_jour rj on rj.id = td.jour_id
            JOIN modele_tournee_jour mtj ON mtj.code = td.modele_tournee_jour_code AND curdate() BETWEEN mtj.date_debut AND mtj.date_fin
            WHERE num_abonne_id = $abonneSocId
            ORDER BY td.jour_id
            ";
        
        $stmt = $connection->executeQuery($query);
        return $stmt->fetchAll();
    }
}
