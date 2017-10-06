<?php

/**
 * Classe fournissant des méthodes liés à la géolocalisation
 * Couche d'abstraction des web services sous-jacents
 * @author madelise
 */

namespace Ams\CartoBundle\GeoService;

use \Symfony\Component\DependencyInjection\ContainerAware;
use Ams\SilogBundle\Lib\SoapClientLocal;
use Ams\DistributionBundle\Command;

class AmsGeoservice extends ContainerAware {

    private $oSoapClient;
    private $optionSoapClient;
    private $searchAroundMethodDefaut;
    private $searchAroundProjectionDefaut;
    private $searchAroundWSConfig;

    /**
     * Méthode qui permet d'interroger le serveur GCIS pour les requêtes de proximité
     * @param array $cibleArr Le tableau d'informations du point cible
     * @param array $pointsArr Le tableau d'informations des points à comparer
     * @param array $cibleArr Le tableau des options de l'appel SOAP
     * @return array $retour Le tableau ordonné des points à comparer selon leur proximité avec la cible
     * @author Marc-Antoine Adélise
     */
    public function callSearchAround($cibleArr, $pointsArr, $optionsArr) {
        $nbErreurs = 0;

        // Appel du WS Geoconcept
        $url = $this->container->getParameter("GEOC_WS_SEARCHAROUND_SOAP_URL");
        $this->searchAroundProjectionDefaut = $this->container->getParameter("GEOC_WS_SEARCHAROUND_SOAP_DEFAULT_PROJECTION");
        $this->searchAroundMethodDefaut = $this->container->getParameter("GEOC_WS_SEARCHAROUND_SOAP_DEFAULT_METHOD");

        $this->searchAroundWSConfig = array();
        $this->searchAroundWSConfig['ADRESSE_WSDL'] = $url;

        $socket_context = stream_context_create(
                array('http' => array('protocol_version' => 1.0))
        );

        // Afin d'eviter les erreurs du genre "SoapClient::__doRequest(): send of 704 bytes failed with errno=32 Broken pipe"
        // Ajouter les deux options suivantes
        $this->optionSoapClient['stream_context'] = $socket_context;
        $this->optionSoapClient['trace'] = 1;
        $this->optionSoapClient['keep_alive'] = false;

        $this->oSoapClient = new SoapClientLocal($this->searchAroundWSConfig["ADRESSE_WSDL"], $this->optionSoapClient);

        // Préparation des arguments de la requête
        $soapArgs = array();
        $soapCallOptions = array();
        $soapCallOptions['Projection'] = $this->searchAroundProjectionDefaut;
        $soapCallOptions['SearchMethod'] = $this->searchAroundMethodDefaut;

        // On passe les options au tableau (écrase potentiellement les options précédemment définies)
        if (!empty($optionsArr)) {
            foreach ($optionsArr as $cleOption => $valeurOption) {
                $soapCallOptions[$cleOption] = $valeurOption;
            }
        }

        $soapArgs['Options'] = $soapCallOptions;

        // La cible
        $soapArgs['Target'] = array(
            'Id' => $cibleArr['id'],
            'X' => $cibleArr['x'],
            'Y' => $cibleArr['y']
        );

        // Les points à comparer
        if (!empty($pointsArr)) {
            foreach ($pointsArr as $point) {
                $ressource = array(
                    'Id' => $point['id'],
                    'X' => $point['x'],
                    'Y' => $point['y']
                );

                $soapArgs['Resource'][] = $ressource;
            }
        } else {
            $nbErreurs++;
        }

        // Test final
        if ($nbErreurs == 0) {
            $soapRequest = array('SearchAroundRequest' => $soapArgs);
            try {
                $retour = $this->oSoapClient->__call('SearchAround', $soapRequest);
                // Debugging
//                echo "REQUEST:\n" . htmlentities(str_ireplace('><', ">\n<", $this->oSoapClient->__getLastRequest())) . "\n";
//                echo "Response:\n" . htmlentities(str_ireplace('><', ">\n<", $this->oSoapClient->__getLastResponse())) . "\n";
                return $retour;
            } catch (\SoapFault $f) {
                //var_dump($f);
		echo "\nSOAP Fault: (faultcode: {$f->faultcode}, faultstring: {$f->faultstring})\n";
                trigger_error("SOAP Fault: (faultcode: {$f->faultcode}, faultstring: {$f->faultstring})", E_USER_ERROR);
            }
        }
    }

    /**
     * Méthode qui filtre une liste de résultats selon des critères
     * @param array $pointsArr Le tableau des points à filtrer
     * @param float $maxTime La durée maximale de déplacement acceptée
     * @param float $maxDistance La distance maximale de déplacement acceptée
     * @param array $listeFiltree La liste des éléments répondant aux critères
     * @param array $aFiltrerArr La liste des points passés à l'origine au service de recherche de proximité
     * @author Marc-Antoine Adélise
     */
    public function filtreListe($pointsArr, $maxTime, $maxDistance, $aFiltrerArr) {
        $listeFiltree = array();
        $aAbosFiltres = array(); // Tableau pour le dédoublonnage

        if (!empty($pointsArr)) {
            $accept = true; // Le marqueur
            foreach ($pointsArr as $point) {
                // Filtrage sur la durée ?
                if ($maxTime > 0) {
                    $accept = ($point->Time > $maxTime) ? false : true;
                }

                // Filtrage sur la distance
                if ($maxDistance > 0) {
                    $accept = ($point->Distance > $maxDistance) ? false : true;
                }

                // Test final
                if ($accept) {
                    foreach ($aFiltrerArr as $abo) {
                        if ($abo['rnvp_id'] == $point->Id) {
                            if (!in_array($abo['abo_id'], $aAbosFiltres)) {
                                $listeFiltree[] = $abo;
                                $aAbosFiltres[] = $abo['abo_id'];
                            }
                        }
                    }
                }
            }
        }

        return $listeFiltree;
    }

    /**
     * Méthode d'appel du web service de calcul d'itinéraire
     * @param array $aDepartCoords Le tableau des coordonnées du dépot en tant que point de démarrage de la tournée
     * @param array $aRetourCoords Le tableau des coordonnées du dépot en tant que point de retour de la tournée
     * @author Kevin Jean-Baptiste
     */
    public function callRouteService($aStepsId, $param = FALSE, $aCoordinate = false, $aDepartCoords = null, $aRetourCoords = null) {
        if (empty($aStepsId))
            return false;

        $url = $this->container->getParameter("GEOC_WS_ROUTESERVICE_SOAP_URL");
        $socket_context = stream_context_create(
                array('http' => array('protocol_version' => 1.0))
        );

        $this->optionSoapClient['stream_context'] = $socket_context;
        $this->optionSoapClient['trace'] = 1;
        $this->optionSoapClient['keep_alive'] = false;

        $this->oSoapClient = new SoapClientLocal($url, $this->optionSoapClient);

        $soapArgs = array();
        $soapCallOptions = array();
        $soapCallOptions['Projection'] = $this->container->getParameter("GEOC_WS_ROUTESERVICE_SOAP_DEFAULT_PROJECTION");
        $soapCallOptions['Method'] = $this->container->getParameter("GEOC_WS_ROUTESERVICE_SOAP_DEFAULT_METHOD");
        $soapCallOptions['Exclusions']['Exclude'] = 'Toll';
        $soapArgs['Options'] = $soapCallOptions;

        $em = $this->container->get('doctrine.orm.entity_manager');

        if ($aCoordinate) {
            foreach ($aCoordinate as $step) {
                if (isset($param['Before']) && isset($step['id']) && $param['Before'] == $step['id']) {
                    $soapArgs['Step'][] = array('X' => $param['Longitude'], 'Y' => $param['Latitude']);
                }
                $soapArgs['Step'][] = array('X' => $step['X'], 'Y' => $step['Y']);
                if (isset($param['After']) && isset($step['id']) && $param['After'] == $step['id']) {
                    $soapArgs['Step'][] = array('X' => $param['Longitude'], 'Y' => $param['Latitude']);
                }
            }
        } else {
            if(is_array($aStepsId))
            {
            foreach ($aStepsId as $step) {
                $query = $em->getRepository('AmsAdresseBundle:TourneeDetail')->find($step);
                if (isset($param['Before']) && $param['Before'] == $query->getId()) {
                    $soapArgs['Step'][] = array('X' => $param['Longitude'], 'Y' => $param['Latitude']);
                }
                $soapArgs['Step'][] = array('X' => $query->getLongitude(), 'Y' => $query->getLatitude());
                if (isset($param['After']) && $param['After'] == $query->getId())
                    $soapArgs['Step'][] = array('X' => $param['Longitude'], 'Y' => $param['Latitude']);
            }
            }
        }

        // Intégration dépot en tant que point de départ
        if (!is_null($aDepartCoords)) {
            $aInfosDepot = array(
                'X' => (float) $aDepartCoords['x'],
                'Y' => (float) $aDepartCoords['y']
            );

            array_unshift($soapArgs['Step'], $aInfosDepot);
        }

        // Intégration du dépot en tant que point de retour de la tournée
        if (!is_null($aRetourCoords)) {
            $aInfosRetourDepot = array(
                'X' => (float) $aRetourCoords['x'],
                'Y' => (float) $aRetourCoords['y']
            );

            $soapArgs['Step'][] = $aInfosRetourDepot;
        }

        $soapRequest = array('RouteRequest' => $soapArgs);
        //print_r($soapArgs);die();
        if (isset($soapArgs['Step']) && count($soapArgs['Step']) > 1) {
            try {
                $return = $this->oSoapClient->__call('Route', $soapRequest);
                return $return;
            } catch (\SoapFault $f) {
                //var_dump($f);
		echo "\nSOAP Fault: (faultcode: {$f->faultcode}, faultstring: {$f->faultstring})\n";
                trigger_error("SOAP Fault: (faultcode: {$f->faultcode}, faultstring: {$f->faultstring})", E_USER_ERROR);
            }
        } else {
            echo "\r\nIl faut au minimum deux points pour calculer une itineraire\r\n";
            print_r($aStepsId);
            return false;
        }
    }

        public function wsRouteService($aCoordinate) {
            $url = $this->container->getParameter("GEOC_WS_ROUTESERVICE_SOAP_URL");
            $socket_context = stream_context_create(array('http' => array('protocol_version' => 1.0)));
            $this->optionSoapClient['stream_context'] = $socket_context;
            $this->optionSoapClient['trace'] = 1;
            $this->optionSoapClient['keep_alive'] = false;
            $this->oSoapClient = new SoapClientLocal($url, $this->optionSoapClient);
            $soapArgs = array();
            $soapCallOptions = array();
            $soapCallOptions['Projection'] = $this->container->getParameter("GEOC_WS_ROUTESERVICE_SOAP_DEFAULT_PROJECTION");
            $soapCallOptions['Method'] = $this->container->getParameter("GEOC_WS_ROUTESERVICE_SOAP_DEFAULT_METHOD");
            $soapCallOptions['Exclusions']['Exclude'] = 'Toll';
            $soapArgs['Options'] = $soapCallOptions;

            foreach ($aCoordinate as $step) {
                $soapArgs['Step'][] = array('X' => $step['X'], 'Y' => $step['Y']);
            }
            
            $soapRequest = array('RouteRequest' => $soapArgs);
            try {
                $return = $this->oSoapClient->__call('Route', $soapRequest);
                return $return;
            } catch (\SoapFault $f) {
                echo "\nSOAP Fault: (faultcode: {$f->faultcode}, faultstring: {$f->faultstring})\n";
            }
    }
    
    
    /**
     * Méthode qui permet l'insertion d'un point dans une tournée, au meilleur endroit
     * @param array $aPoints Les points actuellements intégrés dans la tournée
     * @param array $aCible Le point à intégrer dans la tournée
     * @param bool $bFusionOk Drapeau autorisant ou non la fusion avec un point déjà intégré dans la tournée
     * @param string $sContexte Le contexte de l'opération (permet d'adapter le comportement)
     * @return array $aRetour Un tableau contenant les informations sur l'opération
     * @author Marc-Antoine Adélise
     */
    public function insertNewPoint($aPoints, $aCible, $bFusionOk = true, $sContexte = '') {
        // Init du tableau de retour 
        $aRetour = array(
            'pointsTournee' => array(),
            'infosOperation' => array(
                'insertionResult' => false,
                'cibleFusion' => false,
                'cibleNouvOrdre' => null,
            ),
            'contexte' => $sContexte,
        );

        // Les points de la tournée ont bien été reseignés ?
        if (empty($aPoints)) {
            $aRetour['infosOpertation']['insertionResult'] = false;

            return $aRetour;
        }

        // Tentative de fusion du point si autorisée
        if ($bFusionOk) {
            $aFusionResult = $this->fusionnerPoint($aPoints, $aCible, $sContexte);

            if ($aFusionResult['fusion']) {
                $aRetour['pointsTournee'] = $aPoints;
                $aRetour['infosOperation']['insertionResult'] = true;
                $aRetour['infosOperation']['cibleFusion'] = true;
                $aRetour['infosOperation']['cibleNouvOrdre'] = $aFusionResult['pointFusionne']['ordre'];

                return $aRetour;
            }
        }
        
        // Pas de fusion, on lance le process d'intégration basé sur les WS
        $aOptionsWsSa = array('Projection' => 'WGS84');
        $classement = self::callSearchAround($aCible, $aPoints, $aOptionsWsSa);

        /** TROUVER LE POINT LE PLUS PROCHE * */
        $pointMin = null;
        if (count($classement->SearchAroundResult) > 1) {
            foreach ($classement->SearchAroundResult as $index => $point) {
                if (is_null($pointMin) && (int) $point->Time >= 0) {
                    $pointMin = $point;
                }

                if (!is_null($pointMin) && $pointMin->Time > $point->Time && $point->Time > 0) {
                    $pointMin = $point;
                }
            }
        } else {
            // Un seul point dans le classement
            $point = $classement->SearchAroundResult;
            if (is_null($pointMin) && (int) $point->Time >= 0) {
                $pointMin = $point;
            }

            if (!is_null($pointMin) && $pointMin->Time > $point->Time && $point->Time > 0) {
                $pointMin = $point;
            }
        }
        
        $iKey = $this->in_array_r($pointMin->Id, $aPoints, false, true);
        
        if (count($aPoints) == 1) {
            // Préparation du retour
            $aRetour['infosOperation']['insertionResult'] = true;
            $aRetour['infosOperation']['cibleNouvOrdre'] = 2;

            // intégration du point dans la tournée
            $aRetour['pointsTournee'] = $aPoints;
            $aCible['ordre'] = (int) 2;
            array_push($aRetour['pointsTournee'], $aCible);
        } else {
            $order = $aPoints[$iKey]['ordre'];

            if ($pointMin->Time > 0) {
                // Adaptation du tableau de la cible pour Geoconcept
                $aCible['X'] = $aCible['x'];
                $aCible['Y'] = $aCible['y'];

                if ($iKey >= count($aPoints)){
                    /** APPEL AU WEB SERVICE ROUTE SERVICE sau si le point est à intégrer en tout dernier * */
                    if ($iKey)
                        $aBeforeCible = $aPoints[$iKey - 1];
                    $aPointCible = $aPoints[$iKey];
                    $aPointAfterCible = $aPoints[$iKey + 1];
                    $aFirstRoad = $aSecondRoad = array();

                    /** 1ER ROUTE  * */
                    if ($iKey)
                        $aFirstRoad[] = array('X' => $aBeforeCible['x'], 'Y' => $aBeforeCible['y']);
                    $aFirstRoad[] = $aCible;
                    $aFirstRoad[] = array('X' => $aPointCible['x'], 'Y' => $aPointCible['y']);
                    $aFirstRoad[] = array('X' => $aPointAfterCible['x'], 'Y' => $aPointAfterCible['y']);
                    /** 2EME ROUTE * */
                    if ($iKey)
                        $aSecondRoad[] = array('X' => $aBeforeCible['x'], 'Y' => $aBeforeCible['y']);
                    $aSecondRoad[] = array('X' => $aPointCible['x'], 'Y' => $aPointCible['y']);
                    $aSecondRoad[] = $aCible;
                    $aSecondRoad[] = array('X' => $aPointAfterCible['x'], 'Y' => $aPointAfterCible['y']);
                    /** CALCUL DES ROUTES VIA LE WEB SERVICE "ROUTE SERVICE" * */
                    $routeService_1 = self::callRouteService(TRUE, FALSE, $aFirstRoad);
                    $routeService_2 = self::callRouteService(TRUE, FALSE, $aSecondRoad);

                    $nouvelOrdre = ($routeService_1->ROUTE->Time <= $routeService_2->ROUTE->Time) ? intval($order) : $order + 1;
                }
                else{
                    $nouvelOrdre = $order + 1;
                }

                // Préparation du retour
                $aRetour['infosOperation']['insertionResult'] = true;
                $aRetour['infosOperation']['cibleNouvOrdre'] = (int) $nouvelOrdre;

                // intégration du point dans la tournée
                $aRetour['pointsTournee'] = $aPoints;
                $aCible['ordre'] = (int) $nouvelOrdre;
                array_push($aRetour['pointsTournee'], $aCible);
                usort($aRetour['pointsTournee'], array('self', 'sortTournee'));
            }
        }
        return $aRetour;
    }

    /**
     * Méthode qui permet d'effectuer une fusion de point si possible
     * @param array $aPoints Les points actuellements intégrés dans la tournée
     * @param array $aCible Le point à intégrer dans la tournée
     * @param string $sContexte Le contexte de l'opération (permet d'adapter le comportement)
     * @return array $aRetour Un tableau contenant les informations sur l'opération
     * @author Marc-Antoine Adélise
     */
    private function fusionnerPoint($aPoints, $aCible, $sContexte) {
        $aRetour = array(
            'fusion' => false,
            'infos' => '',
            'pointFusionne' => array()
        );

        // Les points de tournée sont ils renseignés ?
        if (empty($aPoints)) {
            $aRetour['infos'] = 'tournee_vide';
            return $aRetour;
        }

        // La cible est elle renseignée ?
        if (empty($aCible)) {
            $aRetour['infos'] = 'cible_vide';
            return $aRetour;
        }

        // Le tableau contenant les points éligibles à la fusion
        $aPointsFusion = array();

        foreach ($aPoints as $point) {
            if (($point['x'] == $aCible['x']) && ($point['y'] == $point['y'])) {
                $aPointsFusion[] = $point;
            }
        }

        // Il y a au moins 1 point à fusionner
        if (!empty($aPointsFusion)) {
            switch ($sContexte) {
                default:
                    $aRetour['fusion'] = true;
                    $aRetour['pointFusionne'] = $aPointsFusion[0];
                    $aRetour['infos'] = count($aPointsFusion); // Le nombre de points qui auraient pu être fusionnés
                    break;
            }
        }

        return $aRetour;
    }

    /**
     * Adaptation de in_array() qui lui ajoute la possibilité de rechercher récursivement
     * @param type $needle
     * @param type $haystack
     * @param type $strict
     * @param type $have_key
     * @return mixed
     */
    public static function in_array_r($needle, $haystack, $strict = false, $have_key = false) {
        foreach ($haystack as $key => $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::in_array_r($needle, $item, $strict))) {
                if ($have_key)
                    return $key;
                return $item;
            }
        }
        return false;
    }

    /**
     * Méthode qui permet de classer les tournées dans un tableau selon leur ordre
     * @param array $t1 La première tournée
     * @param array $t2 La deuxième tournée
     * @return int Retourne 1 ou -1 selon la comparaison
     */
    public function sortTournee($t1, $t2) {
        if ($t1['ordre'] == $t2['ordre']) {
            return 0;
        }
        return ($t1['ordre'] < $t2['ordre']) ? -1 : 1;
    }

    /**
     * Méthode qui teste la validité de coordonnées
     * @param float $geoX La longitude du point à valider
     * @param float $geoY La latitude du point à valider
     * @return bool $bValid True si les coordonnées sont valides
     */
    public static function testPointGPS($geoX, $geoY) {
        $bValid = true;

        $aCheckLon = explode('.', $geoX);
        if ($geoX == $geoY || strlen($aCheckLon[0]) > 1 || $geoX == 0 || $geoY == 0) {
            $bValid = false;
        }

        return $bValid;
    }

    /**
     * 
     * @param array $pointsAClasser Tableau des points a classer. Un point = array('numabo_ext'=>..., 'abonne_soc_id'=>..., 'soc_code_ext'=>..., 'prd_code_ext'=>..., 'flux_id'=>..., 'insee'=>..., 'geox'=>..., 'geoy'=>..., 'id_jour'=>...)
     * @author Andry ANDRIANIAINA
     */
    public function classementAuto($pointsAClasser) {
        try {
            if (!empty($pointsAClasser)) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $repoTourneeDetail = $em->getRepository('AmsAdresseBundle:TourneeDetail');
                
                $repoReperageTmp = $em->getRepository('AmsDistributionBundle:ReperageTmp');
                
                $aOptionsSearchAround = array('Projection' => 'WGS84');
                $iI = 0;
                foreach ($pointsAClasser as $data) {
                    //echo "\r\n".date("d/m/Y H:i:s : ")."\r\n";
                    //print_r($data);echo "\r\n";
                    // Les pts candidats les plus proches. On se limite dans la ville du point
                    // !!! Cette partie est a corriger quand GeoConcept nous fournit une methode permettant de recuperer le point le plus proche 
                    $aCriteresPtsCandidatsProches = array();
                    $aPtsCandidatsProches = array();
                    //$aCriteresPtsCandidatsProches['insee']  = $data['insee'];
                    if (isset($data['id_jour'])) {
                        $aCriteresPtsCandidatsProches['jour_id'] = $data['id_jour'];
                    }
                    $aCriteresPtsCandidatsProches['rayon_max'] = $this->container->getParameter("GEOC_RAYON_MAX");
                    $aCriteresPtsCandidatsProches['nb_pts_proches_max'] = $this->container->getParameter("GEOC_NB_PTS_PROCHES_MAX");
                    if (isset($data['depot_id'])) {
                        $aCriteresPtsCandidatsProches['depot_id'] = $data['depot_id'];
                    }
                    if (isset($data['flux_id'])) {
                        $aCriteresPtsCandidatsProches['flux_id'] = $data['flux_id'];
                    }
                    if (isset($data['tournee_jour_code']) && trim($data['tournee_jour_code'])!='') {
                        $aCriteresPtsCandidatsProches['tournee'] = $data['tournee_jour_code'];
                    }
                    $aPtAClasser = array(
                        'id' => 'target',
                        'x' => $data['geox'],
                        'y' => $data['geoy'],
                        'longitude' => $data['geox'],
                        'latitude' => $data['geoy'],
                        'abonne_soc_id' => $data['abonne_soc_id'],
                    );
                    //echo "aPtAClasser\n";
                    //print_r($aPtAClasser);

                    if ($aPtAClasser['latitude'] > 0 && $aPtAClasser['longitude'] > 0) {
                        $aPtsCandidatsProches = $repoTourneeDetail->ptsCandidatsProches($aPtAClasser, $aCriteresPtsCandidatsProches);
                        if (empty($aPtsCandidatsProches)) {
                        $aCriteresPtsCandidatsProches['rayon_max'] = $aCriteresPtsCandidatsProches['rayon_max'] * 2;
                        $aPtsCandidatsProches = $repoTourneeDetail->ptsCandidatsProches($aPtAClasser, $aCriteresPtsCandidatsProches);
                    }

                    if (empty($aPtsCandidatsProches)) {
                        $aCriteresPtsCandidatsProches['rayon_max'] = $aCriteresPtsCandidatsProches['rayon_max'] * 2.5;
                        $aPtsCandidatsProches = $repoTourneeDetail->ptsCandidatsProches($aPtAClasser, $aCriteresPtsCandidatsProches);
                    }

                    }

                    //echo "aPtsCandidatsProches\n";
                    //print_r($aPtsCandidatsProches);
                    
                    $oMinDistance = "";
                    $iIdPlusProche = "";
                    $bPtDejaConnu = false; // "true" si les coordonnees font parties des coordonnees deja connues

                    if (!empty($aPtsCandidatsProches)) {
                        // verifier d'abord si c'est un point connu
                        foreach ($aPtsCandidatsProches as $aPts) {
                            if ($aPts['x'] == $aPtAClasser['x'] && $aPts['y'] == $aPtAClasser['y']) {
                                $iIdPlusProche = $aPts['id'];
                                $bPtDejaConnu = true;
                                break;
                            }
                        }

                        if ($bPtDejaConnu == false) {
                            /**  DETERMINATION DU POINT LE PLUS PROCHE * */
                            $classement = self::callSearchAround($aPtAClasser, $aPtsCandidatsProches, $aOptionsSearchAround);

                            //print_r($classement->SearchAroundResult);
                            if (!is_array($classement->SearchAroundResult)) {
                                $oMinDistance = $classement->SearchAroundResult;
                            } else {
                                foreach ($classement->SearchAroundResult as $result) {
                                    if ($result->Distance != -1) {
                                        if ($oMinDistance == "") {
                                            $oMinDistance = $result; // Le point le plus proche est le premier point du $classement->SearchAroundResult
                                            break;
                                        }
                                    }
                                }
                            }
                            if ($oMinDistance != "") {
                                $iIdPlusProche = $oMinDistance->Id;
                            }
                        }
                    }

                    if ($iIdPlusProche != "") { // Si le point le plus pret est trouve
                        // Tournee et ordre du point le plus proche
                        $sSlct = " SELECT 
                                        td.ordre, td.modele_tournee_jour_code, mtj.id AS tournee_jour_id, IFNULL(mt.id, 0) AS tournee_id, IFNULL(mt.code, '') AS tournee_code
                                        , DATE_FORMAT(debut_plage_horaire, '%H:%i:%s') AS debut_plage_horaire
                                        , DATE_FORMAT(fin_plage_horaire, '%H:%i:%s') AS fin_plage_horaire
                                        , DATE_FORMAT(duree_viste_fixe, '%H:%i:%s') AS duree_viste_fixe
                                    FROM
                                       tournee_detail td
                                       LEFT JOIN modele_tournee_jour mtj ON td.modele_tournee_jour_code = mtj.code AND current_date() BETWEEN mtj.date_debut AND mtj.date_fin
                                       LEFT JOIN modele_tournee mt ON mtj.tournee_id = mt.id AND mt.actif = 1
                                    WHERE 
                                        td.id = '" . $iIdPlusProche . "' ";
                        $aRes = $em->getConnection()->executeQuery($sSlct)->fetchAll();
                        foreach ($aRes as $aArr) {
                            $ordre = $aArr['ordre'];
                            $ordre1 = $aArr['ordre'];
                            $modele_tournee_jour_code = $aArr['modele_tournee_jour_code'];
                            $tournee_jour_id = $aArr['tournee_jour_id'];
                            $tournee_id = $aArr['tournee_id'];
                            $tournee_code = $aArr['tournee_code'];
                            
                            if(isset($data['reperage']))
                            {
                                if(intval($data['reperage'])>0 && $tournee_id>0 && isset($data['id']))
                                {
                                    echo "\r\n-----------Point classe -- num_abonne_soc : ".$data['numabo_ext']." - num_abonne_id : ".$data['abonne_soc_id']." - jour_id : ".(isset($data['id_jour']) ? $data['id_jour'] : '*')." - flux_id : ".$data['flux_id']." - tournee_code : ".$tournee_code."\r\n";
                                    $repoReperageTmp->updateTournee($data['id'], $tournee_id);
                                }
                            }
                            else
                            {
                                if ($bPtDejaConnu == false) {
                                    $aTournee = $repoTourneeDetail->ptsAutourRefDansTournee($modele_tournee_jour_code, $iIdPlusProche);

                                    $param = array('Before' => $iIdPlusProche, 'Longitude' => $data['geox'], 'Latitude' => $data['geoy']);

                                    $geoservice_time_1 = self::callRouteService(TRUE, $param, $aTournee);

                                    $param = array('After' => $iIdPlusProche, 'Longitude' => $data['geox'], 'Latitude' => $data['geoy']);
                                    $geoservice_time_2 = self::callRouteService(TRUE, $param, $aTournee);

                                    if ($geoservice_time_1 !== false || $geoservice_time_2 !== false) {
                                        if ($geoservice_time_1 !== false && $geoservice_time_2 !== false) {
                                            if ($geoservice_time_2->ROUTE->Time <= $geoservice_time_1->ROUTE->Time) {
                                                $ordre = $ordre + 1;
                                            }
                                        } elseif ($geoservice_time_2 !== false) {
                                            $ordre = $ordre + 1;
                                        }

                                        // Mise a jour de l'ordre de la tournee ou est insere le nouveau point
                                        if(isset($data['reperage']) && intval($data['reperage'])>0) 
                                        {
                                            // Dans le cas des reperages, on ne fait rien
                                        }
                                        else
                                        {
                                            $repoTourneeDetail->UpOrderCascade($ordre, $modele_tournee_jour_code);
                                        }
                                    }
                                }

                                // Inserer une nouvelle ligne de tournee_detail
                                $aInsertTourneeDetail = array(
                                    'ordre' => $ordre,
                                    'point_livraison_id' => $data['point_livraison_id'],
                                    'longitude' => $data['geox'],
                                    'latitude' => $data['geoy'],
                                    'debut_plage_horaire' => $aArr['debut_plage_horaire'],
                                    'fin_plage_horaire' => $aArr['fin_plage_horaire'],
                                    'duree_viste_fixe' => $aArr['duree_viste_fixe'],
                                    'modele_tournee_jour_code' => $modele_tournee_jour_code,
                                    'num_abonne_soc' => $data['numabo_ext'],
                                    'num_abonne_id' => $data['abonne_soc_id'],
                                    'soc' => $data['soc_code_ext'],
                                    'titre' => (isset($data['prd_code_ext'])) ? $data['prd_code_ext']  : '',
                                    'insee' => $data['insee'],
                                    'flux_id' => $data['flux_id'],
                                    'jour_id' => $data['id_jour'],
                                    'date_modification' => date('Y-m-d H:i:s'),
                                    'source_modification' => (isset($data['source_modification'])?$data['source_modification']:'GEOSERVICE'),
                                    'reperage' => (isset($data['reperage']) ? intval($data['reperage']) : 0),
                                );
                                if(isset($data['jour'])) $aInsertTourneeDetail['jour'] = $data['jour'];

                                //print_r($aInsertTourneeDetail);
                                $repoTourneeDetail->insertTourneeDetail($aInsertTourneeDetail);
                                echo "\r\nNouveau point classe -- num_abonne_soc : " . $data['numabo_ext'] . " - num_abonne_id : " . $data['abonne_soc_id'] . " - jour_id : " . (isset($data['id_jour']) ? $data['id_jour'] : '*') . " - flux_id : " . $data['flux_id'] . "\r\n";
                            }
                        }
                        $oMinDistance = "";
                        $iIdPlusProche = "";
                        $bPtDejaConnu = false;
                    }
                    else
                    {
                        echo "\r\n-----------Point non classe -- Aucun point trouve au alentours -- num_abonne_soc : ".$data['numabo_ext']." - num_abonne_id : ".$data['abonne_soc_id']." - jour_id : ".(isset($data['id_jour']) ? $data['id_jour'] : '*')." - flux_id : ".$data['flux_id']."\r\n";
                        //print_r($aPtsCandidatsProches);
                    }
                    $iI++;
                    if ($iI == 100) {
                        //die("\nFin Traitement de $iI adresses : ".date("d/m/Y H:i:s")."\n");
                    }
                }
            }
        } catch (DBALException $DBALException) {
            throw $DBALException;
        }
    }

}
