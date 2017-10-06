<?php

namespace Ams\CartoBundle\Controller;

//namespace Ams\AdresseBundle\Entity;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Ams\SilogBundle\Controller\GlobalController;

class DefaultController extends Controller {

    public function indexAction($name) {
        return $this->render('AmsCartoBundle:Default:index.html.twig', array('name' => $name));
    }

    /**
     * Action affichant une démo de carte
     */
    public function demoAction() {
        // Récupération de la tournée de démo
        $em = $this->getDoctrine()->getManager();
        
        // Test de décalage d'ordre dans CASL
        $iOrdre = 10;
        $iTourneeJourId = 13802;
        $sTourneeJourCode = '034NND022ME';
        $sDate ='2014-09-24';
        
        $listeTD = $em->getRepository('AmsAdresseBundle:TourneeDetail')->listerPointsTourneeOrdonnes($sTourneeJourCode);
//        var_dump($listeTD);
        
        $listeCASL = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->listerPointsTourneeOrdonnes($iTourneeJourId, $sDate);
//        var_dump($listeCASL);
        
        $aPointsALivrer = CartoController::trouverOrdrePointsCASL($listeTD, $listeCASL);            
        var_dump($aPointsALivrer);
        
        // Création de la table temporaire pour stocker le nouvel ordre
        $sNomTable = $sTourneeJourCode.'_'.$sDate;
        $aChampsSyntaxe = array(
            "`id` INT unsigned NOT NULL",
            "`point_livraison_id` INT NOT NULL",
            "`point_livraison_ordre` INT NOT NULL",
            "PRIMARY KEY (`id`)"
        );
        
        $createRequest = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->creerTableTemp($sNomTable, $aChampsSyntaxe, $aPointsALivrer);
        // Appeler la méthode appliquerTDOrdresurTourneeRelle0
        var_dump($createRequest);
        exit('effectué');
        $params = array(
            'date_parution' => '2014-08-21',
            'code_modele_tournee' => '034NND001JE',
            'depot_id' => 14
        );
        $infosPointsTournee = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getTourneeDatasFromView($params);
        
        if(!empty($infosPointsTournee)){
            $cartoController = new CartoController();
            $aContexte = array_fill(0, count($infosPointsTournee), 'td');
            $aPointsTournee = array_map(array($cartoController, 'transformForGeoWs'), $infosPointsTournee, $aContexte);
        }
        
        // La Cible
        $aPointCible = array(
            'id' => 2525,
            'x' => '2.785053',
            'y' => '49.353523'
        );
        
        $logger = $this->get('logger');
        $logger->info('Message informatif');
        
        $geoservice = $this->container->get('ams_carto.geoservice');
        $aInfosNouvTournee = $geoservice->insertNewPoint($aPointsTournee, $aPointCible, true);
        var_dump($aInfosNouvTournee);
        exit();
        
        
//        return $this->render('AmsCartoBundle:Default:demo.html.twig', array(
//                    'points_tournee' => $tournee
//        ));
    }

    /**
     * Action affichant une démo de carte
     */
    public function demo2Action() {

        // Récupération de la tournée de démo
        $em = $this->getDoctrine()->getManager();
        $tournee = $em->getRepository('AmsAdresseBundle:TourneeDetail')->findAll();
        return $this->render('AmsCartoBundle:Default:demo2.html.twig', array(
                    'points_tournee' => $tournee
        ));
    }

    /**
     * Action affichant une démo de carte
     */
    public function demo3Action() {

        $features = array();
        $em = $this->getDoctrine()->getManager();
//        $tournee = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAllResults(array());

        $params = array(
            'date_parution' => "2014-06-26",
            'depot_id' => 12
        );
        
        $logger = $this->get('logger');
        $logger->info('Message informatif');
        $logger->debug($params);
        $logger->error('Message erreur');

        $tournee = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getDatasFromView($params);
//        var_dump($tournee); exit();
        $json = array();

        if (!empty($tournee)) {
            $indexPoint = 0;
            $numTournee = 0;
            // Récupération de la couleur des points
            $colors = CartoController::getRoadMapColors($numTournee);

            foreach ($tournee as $point) {

                $features[] = $json = array(
                    'type' => 'Feature',
                    'geometry' => array(
                        'type' => 'Point',
                        'coordinates' => array($point['longitude'], $point['latitude'])
                    ),
                    'properties' => array(
                        'aTitle' => $point['a_adresse'] . ' ' . $point['cp'] . ' ' . $point['ville'],
                        'aId' => $point['numabo_ext'],
                        'aType' => 'abo',
//                        'aDureeConduite' => $point['dureeConduite']->format('H:i:s'),
                        'aDureeConduite' => $point['duree_conduite'],
//                        'aOrdreClient' => $point['numord'],
                        'aOrdreClient' => $point['ordre'],
                        'aNomClient' => $point['vol1'],
                        'aIdClient' => $point['num_abonne'],
//                        'aHeureClient' => $point['heureDebut']->format('H:i:s'),
                        'aHeureClient' => $point['heure_debut'],
//                        'aDureeClient' => $point['duree']->format('H:i:s'),
                        'aDureeClient' => $point['duree'],
//                        'aTrajetClient' => $point['distanceTrajet'],
                        'aTrajetClient' => $point['distance_trajet'],
//                        'aTrajetCumulClient' => $point['trajetCumule'],
                        'aTrajetCumulClient' => $point['trajet_cumule'],
                        'aTourneeNumber' => $numTournee,
                        "aPointColor" => $colors['points'][$numTournee]
                    )
                );

                $indexPoint++;
            }
        }
        // Récupération du dernier point
        if (!empty($tournee)) {
            $point = end($tournee);

//            var_dump($point); exit();
            // flux JSON pour la démo
            $json = array(
                'crs' => array(
                    'type' => "name",
                    'properties' => array(
                        'name' => 'urn:ogc:def:crs:OGC:1.3:CRS84'
                    )
                ),
                'type' => 'FeatureCollection',
                'features' => $features,
                'properties' => array(
                    "tId" => $point['mtj_id'],
                    "tCode" => $point['code_modele_tournee'],
                    "tDate" => $params['date_parution'],
                    "tTempsConduite" => $point['temps_conduite'],
                    "tTempsVisiteFixe" => $point['duree_viste_fixe'],
                    "tDuree" => $point['temps_tournee'],
                    "tDistance" => $point['trajet_total'],
                    "tDepot" => $point['d_adresse'] . ' ' . $point['libelle'],
                    "tDepotId" => $point['d_id'],
                    "tHeureD" => $point['debut_plage_horaire'],
                    "tHeureF" => $point['fin_plage_horaire'],
                    "tNbArrets" => count($features),
                    "tColor" => $colors['road'][$numTournee]
                )
            );
        }

        return $this->render('AmsCartoBundle:Default:demo3.html.twig', array(
                    'json' => json_encode($json)
        ));
    }

    /**
     * Action qui permet de simuler le service de géocodage inversé de GCIS
     */
    public function mockreversegeocodingAction() {
        // Ne retourne aucun résultat
        //exit('{"listReverseGeocodingResults":[], "message":null, "status":"OK"}');
        // Retourne 2 adresses en résultat
        exit('{
"listReverseGeocodingResults":[
{
"location":"1,631250;49,000870",
"addresses":[
{
"streetLine":"85 RUE NATIONALE",
"distanceToLocation":0.01116383571806782,
"cityName":"ROSNY-SUR-SEINE",
"countryName":"FRANCE",
"zipCode":"78710"
},
{
"streetLine":"68 RUE NATIONALE",
"distanceToLocation":0.01116383571806782,
"cityName":"ROSNY-SUR-SEINE",
"countryName":"FRANCE",
"zipCode":"78710"
}
]
}
],
"message":null,
"status":"OK"}');
    }
    
}
