<?php

/**
 * Description of CartoController
 *
 * @author madelise
 */

namespace Ams\CartoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Ams\SilogBundle\Controller\GlobalController;
use Ams\CartoBundle\Form\SelectionTourneeType;
use Ams\SilogBundle\Entity\Depot;
use \Ams\ModeleBundle\Entity\ModeleTourneeJour;
use \Ams\ModeleBundle\Entity\ModeleTournee;
use \Ams\AdresseBundle\Repository\TourneeDetailRepository;
use Ams\AdresseBundle\Repository\AdresseRnvp;
use Ams\AdresseBundle\Entity\AdresseRnvp as EntityRnvp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Assetic\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Ams\AdresseBundle\Entity\Adresse;
use Ams\AdresseBundle\Entity\TourneeDetail;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use Ams\AdresseBundle\Entity\TourneeStats;

class CartoController extends GlobalController {

    /**
     * Action affichant retournant les informations de démo d'une tournée
     */
    public function jsondemoAction() {

        // Récupération de la tournée de démo
//        $em = $this->getDoctrine()->getManager();
//        $tournee = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAllResults(
//                array(
//                    'assigner_ressource' => 'A14',
////                    'date' => '1'
//                )
//        );

        $features = array();
        $em = $this->getDoctrine()->getManager();
        $tournee = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAllResults(array());
        if (!empty($tournee)) {
            $indexPoint = 0;
            foreach ($tournee as $point) {
//                var_dump($point); exit();

                if (empty($point['numAbonne'])) {
                    $point['numAbonne'] = '';
                }

                $features[] = array(
                    'type' => 'Feature',
                    'geometry' => array(
                        'type' => 'Point',
                        'coordinates' => (float) $point['longitude'] . ', ' . (float) $point['latitude']
                    ),
                    'properties' => array(
                        'aTitle' => 'Adresse ' . $indexPoint,
                        'aId' => $point['numAbonne'],
                        'aType' => 'abo',
                        'aDureeConduite' => $point['dureeConduite']->format('H:i:s'),
                        'aOrdreClient' => $point['ordre'],
                        'aNomClient' => 'abo',
                        'aIdClient' => $point['numAbonne'],
                        'aHeureClient' => $point['heureDebut'],
                        'aDureeClient' => $point['duree'],
                        'aTrajetClient' => $point['distanceTrajet'],
                        'aTrajetCumulClient' => $point['trajetCumule'],
                        'aTourneeNumber' => 1,
                    )
                );

                $indexPoint++;
            }
        }

        // Pseudo flux JSON pour la démo
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
                "tId" => "AZERTY",
                "tDate" => "28/06/2014",
                "tTempsConduite" => "03:24:00",
                "tTempsVisite" => "01:05:00",
                "tDuree" => "02:47:12",
                "tDistance" => "167 km",
                "tDepot" => "Roissy en France",
                "tDepotId" => "REF",
                "tHeureD" => "04:00:00",
                "tHeureF" => "07:15:30",
                "tNbArrets" => "7",
                "tColor" => "blue"
            )
        );


        exit(json_encode($json));
    }

    /**
     * Méthode qui renvoit les codes couleurs pour les tracés et les icones selon le numéro d'affichage de la tournée
     * @param int $num Le numéro d'affichage de la tournée
     */
    public static function getRoadMapColors($num = NULL) {

        $pointsColorsArr = array(
            '#FF00FF',
            '#802222',
            '#FF0000',
            '#005CFF',
            '#008000'
        );
        
        $traceColorsArr = $pointsColorsArr;
/* Désactivation liée à la suppression des tracés sur la carte
 *         $traceColorsArr = array(
            'blue',
            'red',
            'green',
            'black',
            '#000080'
        );
  */
        
        if (!is_null($num)){
            return array(
                'road' => $traceColorsArr[$num],
                'points' => $pointsColorsArr[$num]
            );
        }
        else{
            return array(
                'road' => $traceColorsArr,
                'points' => $pointsColorsArr
            );
        }
    }

    /**
     * Méthode qui renvoit les informations sur les tournées d'un dépot
     * @param string $codeDepot Le code du dépot concerné
     * @param string $date La date sur laquelle filtrer la requête
     */
    public function gettourneesAction($codeDepot, $date) {
        $em = $this->getDoctrine()->getManager();
        $tournees = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getTourneeFromDepot((string) $codeDepot, $date);
        $json = array();
        if (!empty($tournees)) {
            foreach ($tournees as $tournee) {
                $json[] = array(
                    'codeTournee' => $tournee['code'],
                    'idTournee' => $tournee['tournee_id'],
                    'idDepot' => $tournee['depot_id'],
                    'libDepot' => $tournee['libelle'],
                    'idFlux' => $tournee['flux_id'],
                    'typeService' => $tournee['type_service']
                );
            }
        }

        exit(json_encode(array('tournees' => $json)));
    }

    /**
     * Action qui fait office de proxy vers le serveur de R-Geocodage.
     * @param float $lon La longitude de la coordonnée
     * @param float $lat La latitude de la coordonnée
     * @return string $json Le JSON de sortie d'appel du WS de R-Geocodage
     */
    public function reversegeocodingAction($lon, $lat) {
        // Appel du WS Geoconcept
        $url = $this->container->getParameter("GEOC_WS_RGEOCOD_BASE_URL");
        $maxDistance = $this->container->getParameter("GEOC_WS_RGEOCOD_MAX_DISTANCE");
        $maxCandidate = $this->container->getParameter("GEOC_WS_RGEOCOD_MAX_CANDIDATE");
        $srs = $this->container->getParameter("GEOC_WS_RGEOCOD_SRS");

        $urlJSON = $url . $lon . ',' . $lat . '&maxDistance=' . $maxDistance . '&maxCandidates=' . $maxCandidate . '&srs=' . $srs;
        $json = file_get_contents($urlJSON);
        exit($json);
    }
    
    public function geocodingAction(Request $request) {
            $srvGeocodage = $this->get('geocodage');
            $aAdr = array(
                "AddressLine"=> $request->get('adress'),
                "City"       => $request->get('city'),
                "PostalCode" => $request->get('zip'),
            );
            $listeAdrGeocodes = $srvGeocodage->geocode($aAdr);
            $aJson = array();
            if(isset($listeAdrGeocodes['X']) )
                $aJson = array('x'=> $listeAdrGeocodes['X'], 'y'=>$listeAdrGeocodes['Y']);
            return new Response(json_encode($aJson), 200, array('Content-Type' => 'Application/json'));
    }
    
    private function normaliseAddress($address,$zip,$city){
        $param = array(
                "volet1" => '',
                "volet2" => '',
                "volet3" => '',
                "volet4" => $address,
                "volet5" => '',
                "cp"     => $zip,
                "ville" => $city
            );
        $ws = $this->get('rnvp');
        return $ws->normalise($param);
    }
    
    private function getArrayUnidimensional($aData,$key){
        $tab = array();
        foreach($aData as $data){
            $tab[] = $data[$key];
        }
        return $tab;
    }
    private function InsertAddressRnvp($em,$ResRNVP,$long,$lat,$utilisateurId){
        $oCommune = $em->getRepository('AmsAdresseBundle:Commune')->findOneByCp($ResRNVP->po_cp);
        $oUser = $em->getRepository('AmsSilogBundle:Utilisateur')->find($utilisateurId);
        /**  COPIE DU POINT DE LIVRAISON ORIGINE**/
        $oAdresseRnvp = new EntityRnvp();
        $oAdresseRnvp   ->setCAdrs('')
                        ->setLieuDit('')
                        ->setId(NULL)
                        ->setAdresse($ResRNVP->pio_adresse)
                        ->setCp($ResRNVP->po_cp)
                        ->setVille($ResRNVP->po_ville)
                        ->setInsee($oCommune->getInsee())
                        ->setGeox($long)
                        ->setGeoy($lat)
                        ->setGeoScore(100) // PAR DEFAULT ON CONSIDERE QU'IL EST BIEN GEOCODER
                        ->setGeoType(4) // PAR DEFAULT ON CONSIDERE QU'IL EST BIEN GEOCODER
                        ->setGeoEtat(2) // MANUEL
                        ->setStopLivraisonPossible('1')
                        ->setDateModif(new \DateTime)
                        ->setCommune($oCommune)
                        ->setUtilisateurModif($oUser)
                        ->setTypeRnvp(0)
        ;
        $em->persist($oAdresseRnvp);$em->flush();
        return $oAdresseRnvp->getId();
    }
    
    private function isTime($dateDistrib){
        $now = new \DateTime();
        $date = new \DateTime($dateDistrib);
        $interval = $now->diff($date);
        $iNbJoursDiff = (int) $interval->days;
        if ($now > $date && $iNbJoursDiff > $this->container->getParameter("GEOC_TOURNEE_MAX_JOURS_MODIFS")) return false;
        return true;
    }
    
    public function updatePointLivraisonAction(Request $request) {
        $aJson = array();
//        if (!$this->isTime($request->get('dateDistrib'))) {
//            $aJson['Message'] = '<div class="alert alert-danger" style="padding:5px">Les modifications ne peuvent être faites sur le passé, au delà  de ' . $this->container->getParameter("GEOC_TOURNEE_MAX_JOURS_MODIFS") . ' jours.</div>';
//            return new Response(json_encode($aJson), 200, array('Content-Type' => 'Application/json'));
//        }
        
        if($this->isValEmptyArray($request->request->all())){
            $aJson = array('Message'=> '<div class="alert alert-danger" style="padding:5px"> Tous les champs sont obligatoires</div>');
        }
        else {
            $em = $this->getDoctrine()->getManager();
            $session = $this->get('session');

            $pointLivraisonId = $request->get('pointLivraison');
            $ResRNVP = $this->normaliseAddress($request->get('address'),$request->get('zip'),$request->get('city'));
            $idExistAdress = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->findExistAddress($ResRNVP->pio_adresse,$ResRNVP->po_cp,$ResRNVP->po_ville);

            $aMAddressId = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getAddressIdByPlDateTourneeId($request->get('pointLivraison'),$request->get('tourneeId'),$request->get('dateDistrib'));
            $aAddressId = $this->getArrayUnidimensional($aMAddressId,'adresse_id');
            /** VERIFICATION SI LES DONNEES EXISTE EN BASE **/
            if($idExistAdress)
                $pointLivraisonId = $idExistAdress;
            else 
                $pointLivraisonId = $this->InsertAddressRnvp($em,$ResRNVP,$request->get('long'),$request->get('lat'),$session->get('UTILISATEUR_ID'));

            if(!empty($aAddressId))
                $em->getRepository('AmsAdresseBundle:Adresse')->updatePointLivraison($session->get('UTILISATEUR_ID'),$pointLivraisonId,$aAddressId);
            else
                $em->getRepository('AmsAdresseBundle:Adresse')->changePointLivraison($session->get('UTILISATEUR_ID'),$pointLivraisonId,$request->get('pointLivraison'));
            $aJson = array('Message'=> '<div class="alert alert-info" style="padding:5px"> Les modifications ont bien été apporté .<br />Veuillez patienter vos données vont être actualisé</div>');
        }
        return new Response(json_encode($aJson), 200, array('Content-Type' => 'Application/json'));
    }
    
    private function isValEmptyArray($aData){
        foreach($aData as $data){
            if($data == '') return true;
        }
        return false;
    }

    /**
     * Action qui permet à  l'utilisateur de sélectionner les tournées selon le dépot, la date et le flux en vue d'un affichage sur carte
     */
    public function voirtourneesAction(Request $request) {
        // verifie si on a droit a acceder a cette page        
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $em = $this->getDoctrine()->getManager();

        $session = $this->get('session');

        // Liste des depôts accessible à  l'utilisateur
        $depots = $session->get("DEPOTS");

        // Informations passées à  la vue
        $step = 'new'; // 1er affichage du formulaire
        $tournees = array();
        $codeDepot = null;
        $codeLibelle = null;
        $dateTbl = array('0000', '00', '00');
        $flux_id = null;

        // Récupération de l'action entreprise (cas de la création de point de livraison)
        if (!is_null($request->get('typeaction'))) {
            $typeAction = $request->get('typeaction');
        } else {
            $typeAction = null;
        }

        $form = $this->createForm(new SelectionTourneeType($depots));
        if (($request->getMethod() == 'POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                // Récupération des tournées existantes
                $dateTbl = explode('/', $form->get('date')->getData());
                $date = $dateTbl[2] . '-' . $dateTbl[1] . '-' . $dateTbl[0];
                $codeDepot = $form->get('depot')->getData()->getCode();
                $codeLibelle = $form->get('depot')->getData()->getLibelle();
                $flux_id = $flux = $form->get('flux')->getData()->getId();
                $tournees = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getTourneeFromDepot((string) $codeDepot, (string) $date, $flux);

                // Intégration des tournées vides dans la liste des tournées
                $aListeTournesNonVides = array();
                foreach ($tournees as $aTourneeNonVide) {
                    if (!in_array($aTourneeNonVide['MTJ'], $aListeTournesNonVides)) {
                        $aListeTournesNonVides[] = (int) $aTourneeNonVide['MTJ'];
                    }
                }
                $aTVides = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->listeMTJIds((string) $date, (string) $codeDepot, (int) $flux_id, $aListeTournesNonVides);
                foreach ($aTVides as $iTourneeVideId) {
                    /* @var $oTourneeJourVide \Ams\ModeleBundle\Entity\ModeleTourneeJour */
                    $oTourneeJourVide = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findOneById($iTourneeVideId);
                    if (!empty($oTourneeJourVide)) {
                        $tournees[] = array(
                            'tournee_id' => $oTourneeJourVide->getTournee()->getId(),
                            'code' => $oTourneeJourVide->getCode(),
                            'depot_id' => $oTourneeJourVide->getTournee()->getGroupe()->getDepot()->getId(),
                            'flux_id' => (int) $flux_id,
                            'type_service' => 'N/A',
                            'libelle' => $oTourneeJourVide->getTournee()->getGroupe()->getDepot()->getLibelle(),
                            'adresse' => $oTourneeJourVide->getTournee()->getGroupe()->getDepot()->getAdresse(),
                            'MTJ' => $iTourneeVideId,
                            'modele_jour_libelle' => $oTourneeJourVide->getTournee()->getLibelle(),
                            'nb_abos' => 0,
                            'nb_prods' => 0,
                            'nb_stops' => 0
                        );
                    }
                }

                $step = 'validOK';
            } else {
                //@todo: Afficher message d'erreur
                $step = 'validNOK';
            }
        }
//         var_dump($tournees);
        $session->set('CartoTournee', $tournees);
        return $this->render('AmsCartoBundle:Default:voirtournees.html.twig', array(
                    'form' => $form->createView(),
                    'step' => $step,
                    'tournees' => $tournees,
                    'nb_limite_de_tournees' => 5,
                    'depot_code' => $codeDepot,
                    'depot_libelle' => $codeLibelle,
                    'date' => $dateTbl[0] . '-' . $dateTbl[1] . '-' . $dateTbl[2],
                    'flux' => $flux_id,
                    'typeAction' => $typeAction
        ));
    }

    /**
     * Action qui permet d'afficher des tournées sur la carte
     */
    public function affichertourneesAction(Request $request) {
//        // verifie si on a droit a acceder a cette page        
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();

        // On teste si on est face à  un rafraichissement de la page
        $session = $this->get('session');
        $cartoTourneesDatas = $session->get('cartoTourneesDatas');
        if (($request->getMethod() != 'POST') && !empty($cartoTourneesDatas)) {
            $request->setMethod('POST');
            foreach ($cartoTourneesDatas as $key => $value) {
                $request->request->set($key, $value);
            }
            $session->set('cartoTourneesDatas', array());
        }

        if (($request->getMethod() == 'POST')) {
            $dataSession = array();

            $tournees = $request->request->get('tournees');
            $dataSession['tournees'] = $request->request->get('tournees');

            $dateTbl = explode('/', $request->request->get('hdate'));
            $dataSession['hdate'] = $request->request->get('hdate');
            $date = $dateTbl[2] . '-' . $dateTbl[1] . '-' . $dateTbl[0];

            $depot_id = $request->request->get('hdepot_id');
            $dataSession['hdepot_id'] = $request->request->get('hdepot_id');

            $flux_id = $request->request->get('hflux');
            $dataSession['hflux'] = $request->request->get('hflux');
            
            if (count($tournees) > 0) { 
                $em = $this->getDoctrine()->getManager();

                // Récupération de l'action entreprise (cas de la création de point de livraison)
                if (!is_null($request->get('typeaction'))) {
                    $typeAction = $request->get('typeaction');
                } else {
                    $typeAction = null;
                }
                $dataSession['typeaction'] = $typeAction;


                // Tableau contenant toutes les tournées à  afficher avec les points à  livrer
                $tourneesTbl = array();
                // Tableau contenant toutes les tournées vides sélectionnées
                $aTourneesVidesTbl = array();
                $jsonTbl = array(); // Le tableau JSON général transmis à  HTC/Openlayers

                $hdateTmp = explode('/', $request->get('hdate'));
                $hdate = $hdateTmp[2] . '/' . $hdateTmp[1] . '/' . $hdateTmp[0];
                $sDbDate =  $hdateTmp[2] . '-' . $hdateTmp[1] . '-' . $hdateTmp[0];
                $day = date('w', strtotime($hdate));
                $oRefDay = $em->getRepository('AmsReferentielBundle:RefJour')->find($day + 1);
                $nbAbonnes=array();
                foreach ($tournees as $tournee) {
                    $infosTournee = $em->getRepository('AmsModeleBundle:ModeleTournee')->findById((int) $tournee);
                    if (!empty($infosTournee)) {
                        $codeTournee = $infosTournee[0]->getCode() . $oRefDay->getCode();
                        // Récupération des MTJ liés
                        $oMtj = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findByCodeDateValid($codeTournee, $sDbDate);
                        
                        $bModelStartFromDepot = FALSE; // Par défaut pas de démarrage depuis le dépôt
                        $bModelEndToDepot = FALSE; // Par défaut pas de retour au dépôt
                        $aNbPoints = array();
                        $nbPlv = array();
                        $iNbPointsTournee = 0;
                        $iNbPlvTournee = 0;
                        if (!empty($oMtj)) {
                            if ($oMtj->getJour()->getId() == $day + 1) {
                                    if ($this->container->getParameter('GEOC_TOURNEE_DEPOT_START_ENABLE') == 1) {
                                        // On démarre depuis le dépot ?
                                        if ($oMtj->getDepartDepot()) {
                                            $bModelStartFromDepot = TRUE;
                                        }

                                        // On termine la tournée au dépot ?
                                        if ($oMtj->getRetourDepot()) {
                                            $bModelEndToDepot = TRUE;
                                        }
                                    }

                                    // Récupération du nombre de points dans la tournée
                                    $aCritCompte = array(
                                        'date' => $sDbDate,
                                        'flux_id' => $flux_id,
                                        'tournee_jour_id' => $oMtj->getId(),
                                    );
                                    $aNbPoints = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->compterLignes($aCritCompte);
                                    $nbPlv = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->compterPlv($aCritCompte);
                                    
                                    if (!empty($aNbPoints)){
                                        $iNbPointsTournee = (int)$aNbPoints[0]['nb'];
                                    }
                                    if (!empty($nbPlv)){
                                        $iNbPlvTournee = count($nbPlv);
                                    }
                                }
                        }
                        // Récupération de tous les points de la tournée à  partir de la vue
                        $params = array(
                            'date_parution' => $sDbDate,
                            'code_modele_tournee' => $codeTournee,
                            'depot_id' => $depot_id
                        );

                        $tdRepo = $em->getRepository('AmsAdresseBundle:TourneeDetail');
                        $tdRepo->setContainer($this->container);
                        $infosPointsTournee = array(
                            'depotStart' => $bModelStartFromDepot,
                            'depotEnd' => $bModelEndToDepot,
                            'points' => $tdRepo->getTourneeDatas($codeTournee, $sDbDate,$bModelStartFromDepot, $bModelEndToDepot)
                        );

                        // Ajout de la tournée à  la liste générale
                        if ($iNbPointsTournee > 0) {
                            $tourneesTbl[] = $infosPointsTournee;
                        } else {
                            $aTourneesVidesTbl[] = $oMtj;
                        }
                    }
                }
                if (!empty($tourneesTbl)) {
                    // Récupération des informations de produits
                    $produits = $em->getRepository('AmsProduitBundle:Produit')->findAll();

                    $numTournee = 0;
                    $aIdTourneeDistinct = array();
                    $aIdTourneeOthers = array();
                    $aPointLivraison = array();
                    $aPlByCodeModeleDistinct = array();
                    $aPlByCodeModeleAll = array();
                    $aPointsAProblemes = array();
                    $aIdsPointsAProblemes = array(); // Le tableau pour éviter les doublons dans le tableau des points à  problèmes
                    $aParam = array();


                    foreach ($tourneesTbl as $key=>$tourneeTrouvee) {
                        $features = array();
                        $aAbonnesUniques = array();

                        // Récupération de la couleur des points
                        $colors = CartoController::getRoadMapColors($numTournee);

                        foreach ($tourneeTrouvee['points'] as $point) {
                            
                            /** RECUPERATION DES PARAMETRES D'UNE TOURNEE **/
                            $this->fillArrayParameter($aParam, 'sDepot', $point['libelle']);
                            $this->fillArrayParameter($aParam, 'tDepot', $point['d_id']);
                            $this->fillArrayParameter($aParam, 'tCode', $point['code_modele_tournee']);
                            $this->fillArrayParameter($aParam, 'tDate', $point['date_parution']);
                            $this->fillArrayParameter($aParam, 'tAdresseDepot', $point['d_adresse']);
                            
                            // On marque le point de départ
                            if ($point['client_type'] == 'depart' && $tourneeTrouvee['depotStart'] && $this->container->getParameter('GEOC_TOURNEE_DEPOT_START_ENABLE') == 1) {
                                $bDepartDepot = true;
                            } else {
                                $bDepartDepot = false;
                            }
                            // On marque le point de retour
                            if ($point['client_type'] == 'depart' && $tourneeTrouvee['depotEnd'] && $this->container->getParameter('GEOC_TOURNEE_DEPOT_START_ENABLE') == 1) {
                                $bRetourDepot = true;
                            } else {
                                $bRetourDepot = false;
                            }

                            // On n'intégre pas le point si ses coordonnées ne sont pas renseignées
                            if ($this->get('ams_carto.geoservice')->testPointGPS($point['longitude'], $point['latitude']) === false) {
                                continue;
                            }

                            if ($bDepartDepot == false && $bRetourDepot == false) {
                                /** RECUPERATION POINT DE LIVRAISON DIFFERENT * */
                                if (!in_array($point['point_livraison_id'], $aPointLivraison)) {
                                    $aPointLivraison[] = $point['point_livraison_id'];
                                    $aIdTourneeDistinct[$point['code_modele_tournee']][] = $point['td_id'];
                                    $aPlByCodeModeleDistinct[$point['code_modele_tournee']][$point['point_livraison_id']] = $point['td_id'];
                                }
                                /** RECUPERATION DE TOUS LES AUTRES POINTS DE LA TOURNEE * */
                                else
                                    $aIdTourneeOthers[$point['code_modele_tournee']][] = $point['td_id'];

                                $aPlByCodeModeleAll[$point['code_modele_tournee']][$point['point_livraison_id']][] = $point['td_id'];

                                // Récupération des infos sur le produit à  livrer
                                $produitTitre = '';
                                $produitImage = '';
                                $produitImageFound = true;

                                if (!empty($produits)) {
                                    foreach ($produits as $produit) {
                                        if ($produit->getId() == $point['produit_id']) {
                                            $produitTitre = $produit->getLibelle();

                                            $produitImageInfos = $produit->getImage();
                                            if (!empty($produitImageInfos)) {
                                                $produitImage = $em->getRepository('AmsExtensionBundle:Fichier')->findOneById($produit->getImage()->getId())->getWebPath();
                                                $produitImage = $this->container->getParameter("WEB_FILES_ROOT_DIR") . $produitImage;
                                            }
                                        }
                                    }
                                }

                                // Mise en place du logo par défaut si nécessaire
                                if (empty($produitImage)) {
                                    $produitImage = $this->container->getParameter("NO_LOGO_DEFAULT_ICON_FILENAME");
                                    $produitImageFound = false;
                                }

                                // Remplacement du vol1 par le vol2 si le premier est absent
                                if (empty($point['vol1'])) {
                                    $nomClient = $point['vol2'];
                                } else {
                                    // On compléte le nom avec le vol2 si besoin
                                    $nomClient = $point['vol1'];
                                    $nomClient = !empty($point['vol2']) ? $nomClient . ' - ' . trim($point['vol2']) : $nomClient;
                                }

                                // Mise à  l'écart des points problématiques pour l'affichage sur la carte
                                if ($this->get('ams_carto.geoservice')->testPointGPS($point['longitude'], $point['latitude']) === false) {
                                    if (!in_array($point['point_livraison_id'], $aIdsPointsAProblemes)) {
                                        $aPointsAProblemes[] = $point;
                                        $aIdsPointsAProblemes[] = $point['point_livraison_id'];
                                    }
                                    continue;
                                }
                            }
                            
                            // Le  point est le dépot ?                            
                            if ($bDepartDepot === true || $bRetourDepot === true) {
                                $features[] = array(
                                    'type' => 'Feature',
                                    'geometry' => array(
                                        'type' => 'Point',
                                        'coordinates' => array((float) $point['longitude'], (float) $point['latitude'])
                                    ),
                                    'properties' => array(
                                        'aTitle' => 'Dépot',
                                        'aId' => 'ID DEPOT',
//                                    "aTdId" => $point['td_id'],
                                        'aType' => 'depart',
                                        'aDureeConduite' => 0,
                                        'aOrdreClient' => $point['ordre'],
                                        'aPointLivraisonId' => $point['point_livraison_id'],
                                        'aHeureClient' => $point['heure_debut'],
                                        'aNomClient' => $point['nom_depot'],
                                        'aAdresse' => $point['adresse_depot'],
//                                        'aDureeClient' => $point['duree'],
                                        'aTrajetClient' => $point['distance_trajet'],
                                        'aTrajetCumulClient' => $point['trajet_cumule'],
                                        'aTourneeNumber' => $numTournee,
                                        'aTourneeId' => (int) $point['mtj_id'],
                                        'aTourneeCode' => (string) $point['code_modele_tournee'],
                                        "aPointColor" => $colors['points'],
//                                        "aTitreProduit" => $produitTitre,
//                                        "aImageProduitDispo" => $produitImageFound,
//                                        "aImageProduit" => $produitImage
                                    )
                                );
                            } else {
                                $features[] = array(
                                    'type' => 'Feature',
                                    'geometry' => array(
                                        'type' => 'Point',
                                        'coordinates' => array((float) $point['longitude'], (float) $point['latitude'])
                                    ),
                                    'properties' => array(
                                        'aTitle' => $point['a_adresse'] . ' ' . $point['cp'] . ' ' . $point['ville'],
                                        'aAdresseAbonne' => $point['a_adresse'],
                                        'aZipAbonne' =>$point['cp'],
                                        'aCityAbonne' =>$point['ville'],
                                        'aId' => $point['numabo_ext'],
                                        'aUniqueId' => (int) $point['abonne_unique_id'],
                                        "aTdId" => $point['td_id'],
                                        'aType' => 'abo',
                                        'aDureeConduite' => $point['duree_conduite'],
                                        'aOrdreClient' => $point['ordre'],
                                        'aPointLivraisonId' => $point['point_livraison_id'],
                                        'aNomClient' => $nomClient,
                                        'aIdClient' => $point['num_abonne'],
                                        'aHeureClient' => $point['heure_debut'],
                                        'aDureeClient' => $point['duree'],
                                        'aTrajetClient' => $point['distance_trajet'],
                                        'aTrajetCumulClient' => $point['trajet_cumule'],
                                        'aTourneeNumber' => $numTournee,
                                        'aTourneeId' => (int) $point['mtj_id'],
                                        'aTourneeCode' => (string) $point['code_modele_tournee'],
                                        "aPointColor" => $colors['points'],
                                        "aTitreProduit" => $produitTitre,
                                        "aImageProduitDispo" => $produitImageFound,
                                        "aImageProduit" => $produitImage,
                                        "dateDistrib" => (string) $point['date_distrib']
                                    )
                                );

                                // Récupération des abonnés uniques
                                $aAbonnesUniques[] = $point['abonne_unique_id'];
                            }
                        }
                        $session->set('cartoIdTourneesDistinct', $aIdTourneeDistinct);
                        $session->set('cartoIdTourneesOthers', $aIdTourneeOthers);
                        $session->set('aPlByCodeModeleAll', $aPlByCodeModeleAll);
                        $session->set('aPlByCodeModeleDistinct', $aPlByCodeModeleDistinct);


                        /** STATISTIQUE DE LA TOURNEE **/
                        $aStats = array();
                        $stats = $em->getRepository('AmsAdresseBundle:TourneeStats')->exist($sDbDate,$point['modele_tournee_jour_code']);
                        if($stats)  $aStats = (array)json_decode($stats['stats']);
                        $distance   = ($stats && isset($stats['stats']) && $stats['stats'] != 'null')? $aStats['ROUTE']->Distance : '';
                        $driverTime = ($stats && isset($stats['stats']) && $stats['stats'] != 'null')? $aStats['ROUTE']->Time : '';
                        $nbArret = $this->getArretByTournee($tourneesTbl[$key]['points']);
                        $nbAbonne = $this->getAbonneByTournee($tourneesTbl[$key]['points']);
                        $aCalculTime = ($driverTime != '') ? $this->aDuree($nbArret, $nbAbonne, $driverTime) : false;
                        
                        // Récupération du dernier point
                        if (!empty($tourneeTrouvee['points'])) {
                            $point = end($tourneeTrouvee['points']);
                            while (is_null($point['point_livraison_id']) || is_null($point['heure_debut']) || is_null($point['trajet_total'])
                            ) { // Nécessaire pour ne pas récupérer les infos de tournée sur des points contenant ces infos à  NULL
                                $point = prev($tourneeTrouvee['points']);
                                if ($point == false) {
                                    $point = end($tourneeTrouvee['points']);
                                    if (!isset($point['d_adresse'])) {
                                        $point = prev($tourneeTrouvee['points']);
                                    }
                                    $point = $this->ajouterPointGeoValeursDefaut($point);
                                    break;
                                }
                            }
                            $aAbonnesUniques = array_unique($aAbonnesUniques);
                                
                            // Informations liées à  la tournée
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
                                    "tCode" => $aParam['tCode'],
                                    "tDate" => $aParam['tDate'],
                                    "tTempsConduite" => $driverTime,
                                    "tTempsVisiteFixe" => $point['duree_viste_fixe'],
                                    "tTempsVisite" => ($aCalculTime) ? $aCalculTime['VISIT_TIME'] : '',
                                    "tDuree" => ($point['tournee_time']) ? $point['tournee_time'] : '',
                                    "tTempsVisiteFixe" => $point['duree_viste_fixe'],
                                    "tDuree" => ($aCalculTime) ? $aCalculTime['TOTAL_TIME'] : '',
                                    "tDistance" => $distance,
                                    "tDepot" => $aParam['sDepot'],
                                    "tAdresseDepot" => $aParam['tAdresseDepot'],
                                    "tDepotId" => $depot_id,
                                    "tHeureD" => $point['heure_debut'],
                                    "tHeureF" => $this->add_heures($point['heure_debut'], $aCalculTime),
                                    "tNbArrets" => $iNbPlvTournee,
                                    "tNbAbonne" => (int)$aNbPoints[0]['nb'],
                                    "tColor" => $colors['road']
                                )
                            );
                        }
//                        foreach($json as $k=>$v){
//                            echo '<pre>';
//                        print_r($v);
//                            echo '</pre>';
//                        }die;
                        $numTournee++;

                        $jsonTbl[] = json_encode($json);
                    }
                    
                    // Le dépot
                    $iDepotId = !empty($point['d_id']) ? $point['d_id'] : null;
                }
                // Enregistrement des informations dans la session
                $session->set('cartoTourneesDatas', $dataSession);
                $aInfosBulles = $this->getInfosBulles();

                return $this->render('AmsCartoBundle:Default:carte_affichage_tournees.html.twig', array(
                            'infoBulles' => $aInfosBulles,
                            'htcJsonTbl' => $this->getOpenLayerPoints($jsonTbl),
                            'jsonTbl' => $jsonTbl,
                            'flux_id' => $flux_id,
                            'depot_id' => $depot_id,
                            'date_tournee' => $request->request->get('hdate'),
                            'points_ecartes' => $aPointsAProblemes,
                            'typeAction' => $typeAction,
                            'tournees_vides' => $aTourneesVidesTbl,
                            'nbAbonnes'=>$nbAbonnes
                ));
            } else {
                // Redirection vers la page de sélection des tournées
                return $this->redirect($this->generateUrl('ams_carto_voirtournees'));

                //@todo: Afficher un message d'erreur
                exit('Aucune tournée trouvée');
            }
        } else {
            // Redirection vers la page de sélection des tournées
            return $this->redirect($this->generateUrl('ams_carto_voirtournees'));
            exit();
        }
    }
    
    /**
     * Action qui retourne les informations d'une tournée au format OpenLayers
     * @param int $iTourneeJourId L'ID de la tournée
     * @param string $sDate La date pour laquelle on récupère les informations
     * @param int $iFluxId L'ID du flux
     * @param int $iPos La position de la tournée dans l'affichage (pour déterminer la couleur de tracé)
     */
    public function infostourneesopenlayersAction($iTourneeJourId, \DateTime $sDate, $iFluxId, $iPos = 0){
        $bModelStartFromDepot = FALSE; // Par défaut pas de démarrage depuis le dépôt
        $bModelEndToDepot = FALSE; // Par défaut pas de retour au dépôt
        $aIdsPointsAProblemes = array(); // Le tableau pour éviter les doublons dans le tableau des points à  problèmes
        $aPointsAProblemes = array();
        $aInfosJSON = array();
        $aFeatures = array(); // Le tableau des points à encoder en JSON
                        
        if ((int)$iTourneeJourId > 0){
            $em = $this->getDoctrine()->getManager();
            $oTourneeJour = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findOneById((int)$iTourneeJourId);
            
            // Récupération des informations de produits
            $produits = $em->getRepository('AmsProduitBundle:Produit')->findAll();
            
            // Controle sur le flux
            if ($iFluxId <= 0){
                throw new HttpException('500','Mauvais flux');
            }
            else{
                $oFlux = $em->getRepository('AmsReferentielBundle:RefFlux')->findOneById($iFluxId);
                if (is_null($oFlux)){
                    throw new HttpException('404','Flux introuvable');
                }
            }
            
            // Controle sur la tournée
            if (is_null($oTourneeJour)){
                throw new HttpException('404','Tournée introuvable');
            }
            
            /* @var $oTourneeJour \Ams\ModeleBundle\Entity\ModeleTourneeJour */
            $sCodeTournee = $oTourneeJour->getCode();
                    
            // Récupération du nombre de points dans la tournée
            $aCritCompte = array(
                'date' => $sDate->format('Y-m-d'),
                'flux_id' => $iFluxId,
                'tournee_jour_id' => $oTourneeJour->getId(),
            );
            $aNbPoints = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->compterLignes($aCritCompte);
            if (empty($aNbPoints)){
                throw new HttpException('500','Erreur rencontrée lors de la récupération du nombre de points.');
            }
            $iNbPoints = (int)$aNbPoints[0]['nb'];
            
            $day = date('w', strtotime($sDate->format('Y-m-d')));
            $oRefDay = $em->getRepository('AmsReferentielBundle:RefJour')->find($day + 1);
            /* @var $oTournee \Ams\ModeleBundle\Entity\ModeleTournee */
            $oTournee = $oTourneeJour->getTournee();
            
            // Controle sur la tournée
            if (empty($oTournee)){
                throw new HttpException('500','Problème de récupération de la tournée.');
            }
            
            if ($this->container->getParameter('GEOC_TOURNEE_DEPOT_START_ENABLE') == 1) {
                // On démarre depuis le dépot ?
                if ($oTourneeJour->getDepartDepot()) {
                    $bModelStartFromDepot = TRUE;
                }

                // On termine la tournée au dépot ?
                if ($oTourneeJour->getRetourDepot()) {
                    $bModelEndToDepot = TRUE;
                }
            }
            
            // Récupération de tous les points de la tournée à  partir de la vue
            $aParams = array(
                'date_parution' => $sDate->format('Y-m-d'),
                'code_modele_tournee' => $oTourneeJour->getCode(),
                'depot_id' => $oTournee->getGroupe()->getDepot()->getId()
            );
            
            /* @var $oTdRepo \Ams\AdresseBundle\Repository\TourneeDetailRepository */
            $oTdRepo = $em->getRepository('AmsAdresseBundle:TourneeDetail');
            $oTdRepo->setContainer($this->container);
            $aInfosPointsTournee = array(
                'depotStart' => $bModelStartFromDepot,
                'depotEnd' => $bModelEndToDepot,
                'points' => $oTdRepo->getTourneeDatasFromView($aParams, $bModelStartFromDepot, $bModelEndToDepot)
            );
            
            // Détermination des couleurs
            $sRoadColor = $this->getColor($iPos, 'road');
            $sPointsColor = $this->getColor($iPos, 'points');
            
            // On parcourt les points pour les modifier si besoin
            foreach ($aInfosPointsTournee['points'] as &$aPoint){
                $bDepartDepot = $this->verifDepotPtDepartOuRetour('depart', $aPoint['client_type'], $bModelStartFromDepot);
                $bRetourDepot = $this->verifDepotPtDepartOuRetour('retour', $aPoint['client_type'], $bModelEndToDepot);
                
                 // On n'intégre pas le point si ses coordonnées ne sont pas renseignées
                if ($this->get('ams_carto.geoservice')->testPointGPS($aPoint['longitude'], $aPoint['latitude']) === false) {
                    continue;
                }

                if ($bDepartDepot == FALSE && $bRetourDepot == FALSE){
                    // Récupération des infos sur le produit à  livrer
                    $aProdInfos = $this->getProduitInfos($aPoint['produit_id'], $em);

                    // Récupération du nom du client
                    $sNomClient = $this->completeNomClient($aPoint);
                    
                    // Mise à  l'écart des points problématiques pour l'affichage sur la carte
                    if ($this->get('ams_carto.geoservice')->testPointGPS($aPoint['longitude'], $aPoint['latitude']) === false) {
                        if (!in_array($aPoint['point_livraison_id'], $aIdsPointsAProblemes)) {
                            $aPointsAProblemes[] = $aPoint;
                            $aIdsPointsAProblemes[] = $aPoint['point_livraison_id'];
                        }
                        continue;
                    }
                }
                
                if ($bDepartDepot == TRUE || $bRetourDepot == TRUE){
                    $aPoint['d_id'] = $aParams["depot_id"];
                    $aPoint = $this->ajouterPointGeoValeursDefaut($aPoint, TRUE);
                    $aInfosJSON = $this->generateOpenLayersTourneeInfos(NULL, $sDate->format('Y-m-d'), $aPoint, $iFluxId, $sRoadColor);
                }
                else{
                    $aPoint['bDepartDepot'] = $aPoint['bRetourDepot'] = FALSE;
                    $aPoint = $this->ajouterPointGeoValeursDefaut($aPoint);
                    $aFeatures[] = $this->generateOpenLayersPointInfos($aPoint, $sNomClient, $sCodeTournee, $aProdInfos, $sPointsColor);
                }
            }
            
            // Intégration des points dans la tournée
            $aInfosJSON['features'] = $aFeatures;
            
            return new Response(json_encode($aInfosJSON), 200, array('Content-Type' => 'Application/json'));
        }
        else{
            throw new HttpException('404','Mauvais ID de tournée');
        }
    }
    
    /**
     * Méthode qui permet de retourner les couleurs de tracé et de points selon la position de la tournée
     * @param int $iPos La position de la tournée dans la carte
     * @param string $sType road|points Le type d'item (tracé ou point)
     * @return string $sColor La couleur sélectionnée
     */
    private function getColor($iPos, $sType){
        $sColor = '';
        $aCouleurs = $this->getRoadMapColors($iPos);
        
        $sColor = $aCouleurs[$sType];
        
        return $sColor;
    }
    
    /**
     * Méthode qui retourne l'URL de l'image d'un produit
     * @param int $iProduitId L'ID du produit
     * @param object $em L'objet Entity Manager
     * @return array $aReturn Un tableau contenant les informations sur le produit
     */
    private function getProduitInfos($iProduitId, $em){
        $sProduitImage = NULL;
        $bProduitImageFound = TRUE;
        $aReturn = array();
        
        if ((int)$iProduitId > 0){
            $oProduit = $em->getRepository('AmsProduitBundle:Produit')->findOneById((int)$iProduitId);
            $sProduitTitre = $oProduit->getLibelle();
            $produitImageInfos = $oProduit->getImage();
            
            if (!empty($produitImageInfos)) {
                $sProduitImage = $em->getRepository('AmsExtensionBundle:Fichier')->findOneById($oProduit->getImage()->getId())->getWebPath();
                $sProduitImage = $this->container->getParameter("WEB_FILES_ROOT_DIR") . $sProduitImage;
            }
            
             // Mise en place du logo par défaut si nécessaire
            if (!is_null($sProduitImage)) {
                $sProduitImage = $this->container->getParameter("NO_LOGO_DEFAULT_ICON_FILENAME");
                $bProduitImageFound = FALSE;
            }
            
            $aReturn = array(
                'sProduitTitre' => $sProduitTitre,
                'sProduitImage' => $sProduitImage,
                'bproduitImageFound' => $bProduitImageFound
            );
        }
        
        return $aReturn;
    }
    
    /**
     * Méthode qui permet de changer le bouléen lié au départ et retour dépôt selon les informations disponibles
     * @param string $sEtape depart|retour
     * @param string $sClientType Le type de client
     * @param bool $bMarqueur TRUE si la tournée est configurée pour partir ou revenir du/au dépôt
     * @return bool TRUE Si un départ ou un retour au dépôt doit être pris en compte
     */
    private function verifDepotPtDepartOuRetour($sEtape, $sClientType, $bMarqueur){
        $bRetour = FALSE;
        switch ($sEtape){
            case 'depart':
                if ($sClientType == 'depart' && $bMarqueur && $this->container->getParameter('GEOC_TOURNEE_DEPOT_START_ENABLE') == 1) {
                    $bRetour = TRUE;
                }
                break;
            case 'retour':
                if ($sClientType == 'depart' && $bMarqueur && $this->container->getParameter('GEOC_TOURNEE_DEPOT_START_ENABLE') == 1) {
                    $bRetour = TRUE;
                }
                break;
        }
        
        return $bRetour;
    }
    
    /**
     * Complète le nom du client selon les informations présentes dans les volets 1 et 2
     * @param array $aPoint le tableau avec les informations du point
     * @return string $sNomClient Le nom du client à prendre en compte dans l'affichage
     */
    private function completeNomClient($aPoint){
        // Remplacement du vol1 par le vol2 si le premier est absent
        if (empty($aPoint['vol1'])) {
            $sNomClient = $aPoint['vol2'];
        } else {
            // On compléte le nom avec le vol2 si besoin
            $sNomClient = $aPoint['vol1'];
            $sNomClient = !empty($aPoint['vol2']) ? $sNomClient . ' - ' . trim($aPoint['vol2']) : $sNomClient;
        }
        
        return $sNomClient;
    }

    /**
     * Action qui permet de créer un point de livraison
     */
    public function creerpointAction(Request $request) {
        
    }
    
    /**
     *  Action d'appel en Ajax 
     *  qui permet de basculer n points de livraison d'une tournée A vers une tournée B
     *  et de modifier l'ordre d'arrets dans la tournée B
     *  @author yannick Dieng
     */
    public function movePointChangeOrderTourneeAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();
            $TourneeOrigine = $request->get('tourneeOrigine');
            $TourneeDestination = $request->get('tourneeDestination');
            $aDestination = explode(",", $request->get('pointLivraisonDest'));
            $aOrigine = explode(",", $request->get('pointLivraisonOrigine'));
            $newPointDest = (count($request->get('newPointDest'))> 0) ? implode(",", $request->get('newPointDest')) : '';
            
            /** UPDATE TOURNEE ORIGINE (TOURNEE DETAIL)**/
            $order_origine  = $order_destination = 1;
            if($aDestination){
                foreach($aDestination as $pointLivraisonId){
                    $em->getRepository('AmsAdresseBundle:TourneeDetail')->updateOrderTourneeByPointLivraison($TourneeDestination,$TourneeOrigine,$order_destination,$pointLivraisonId);
                    $order_destination++;
                }
            }
            /** UPDATE TOURNEE DESTINATION (TOURNEE DETAIL) **/
            if($aOrigine){
                foreach($aOrigine as $pointLivraisonId){
                    $em->getRepository('AmsAdresseBundle:TourneeDetail')->updateOrderByPointLivraison($TourneeOrigine,$order_origine,$pointLivraisonId,'movePointChangeOrderTourneeAction');
                    $order_origine++;
                }
            }
            if($this->diffDate($request->get('dateDistrib')) <= $this->container->getParameter("GEOC_TOURNEE_MAX_JOURS_MODIFS") ){
//            if($this->diffDate($request->get('dateDistrib')) <= 100){
                /** RECUPERATION TOURNEE JOUR ID TOURNEE (DESTINATION,ORIGINE)**/
                $aTourneeJourIdDestination = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findIdByCodeDateValid($TourneeDestination, $request->get('dateDistrib'));
                $aTourneeJourIdOrigine = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findIdByCodeDateValid($TourneeOrigine, $request->get('dateDistrib'));
                /** UPDATE CASL**/
                if(count($aTourneeJourIdDestination)){
                    $TourneeJourIdDestination = current($aTourneeJourIdDestination);
                    $TourneeJourIdOrigine = current($aTourneeJourIdOrigine);
                    $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->permutationTourneeJour($TourneeJourIdOrigine,$TourneeJourIdDestination, $request->get('dateDistrib'),$newPointDest);
                    
                    $sCmd = 'php '.$this->get('kernel')->getRootDir().'/console commandRepositoryDistribution ClientAServirLogist repliquerOrdreModeleSurDistrib '
                            . '--args='.$TourneeOrigine.'_no-quote,'.$request->get('dateDistrib').'_no-quote '
                            . '--env ' . $this->get('kernel')->getEnvironment();
                   $this->bgCommandProxy($sCmd);
                   
                    $sCmd = 'php '.$this->get('kernel')->getRootDir().'/console commandRepositoryDistribution ClientAServirLogist repliquerOrdreModeleSurDistrib '
                            . '--args='.$TourneeDestination.'_no-quote,'.$request->get('dateDistrib').'_no-quote '
                            . '--env ' . $this->get('kernel')->getEnvironment();
                    $this->bgCommandProxy($sCmd);
//                    $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->repliquerOrdreModeleSurDistrib($TourneeOrigine, $request->get('dateDistrib'));
//                    $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->repliquerOrdreModeleSurDistrib($TourneeDestination, $request->get('dateDistrib'));
                }
            }
            $jsonArr = array('codeErr'=>0,'msgErr'=>'','codeRetour'=>'ok','msgRetour'=>'<strong>Les points de livraison ont bien changé de tournée.</strong><br/>Dans un instant la page va être rechargée...');
            return new Response(json_encode($jsonArr), 200, array('Content-Type' => 'Application/json'));
        }
    }



    /**
     * Action d'appel en Ajax qui permet de basculer un point de livraison d'une tournée à  une autre
     */
    public function basculerpointtourneeAction(Request $request) {
        if (($request->getMethod() == 'POST')) {
            $parametres = $request->request->all();

            // Débug des paramètres
            if ($this->container->has('fire_php')) {
                $this->get('fire_php')->log($parametres);
            }

            $jsonArr = array(
                'codeRetour' => null,
                'msgRetour' => null,
                'codeErr' => null,
                'msgErr' => null,
                'datas' => null
            );

            $em = $this->getDoctrine()->getManager();

            // Vérification de la cohérence des données recues avant de lancer les updates
            // Récupération du point et vérification de ses informations
            $pointLivraison = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->findById((int) $parametres['point_id']);
            if (empty($pointLivraison)) {
                $jsonArr['codeErr'] = 1;
                $jsonArr['msgErr'] = 'Point de livraison inexistant';
            }

            // Récupérer la tournée actuelle et vérifier ses informations
            $tourneeActuelle = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findById((int) $parametres['tourneesource_id']);
            if (empty($tourneeActuelle)) {
                $jsonArr['codeErr'] = 2;
                $jsonArr['msgErr'] = 'Tournée source inexistante.';
            } else {
                // On vérifie la cohérence entre le code et l'ID
                if ($parametres['tourneesource_code'] != substr($tourneeActuelle[0]->getCode(), 0, strlen($parametres['tourneesource_code']))) {
                    $jsonArr['codeErr'] = 3;
                    $jsonArr['msgErr'] = 'Les informations de la tournée source sont incohérentes';
                }
            }

            // Récupérer la tournée de destination et vérifier ses informations
            $tourneeDestination = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findById((int) $parametres['tourneecible_id']);
            if (empty($tourneeDestination)) {
                $jsonArr['codeErr'] = 4;
                $jsonArr['msgErr'] = 'Tournée cible inexistante.';
            } else {
                // On vérifie la cohérence entre le code et l'ID
                if ($parametres['tourneecible_code'] != substr($tourneeDestination[0]->getCode(), 0, strlen($parametres['tourneecible_code']))) {
                    $jsonArr['codeErr'] = 5;
                    $jsonArr['msgErr'] = 'Les informations de la tournée cible sont incohérentes';
                }
            }

            // On vérifie que le point désigné appartient bien à  la tournée source
            $verifPointTourneeArr = array(
                'point_livraison_id' => (int) $parametres['point_id'],
                'tournee_jour_id' => (int) $parametres['tourneesource_id'],
                'flux_id' => (int) $parametres['flux_id'],
                'date_distrib' => $parametres['date']
            );
            $tourneeDistrib = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->compterLivraisonsPourPointDansTournee($verifPointTourneeArr);
            // Débug de la vérification de présence du point dans la distribution
            if ($this->container->has('fire_php')) {
                $this->get('fire_php')->log('Débug de la vérification de point de distribution');
                $this->get('fire_php')->log($verifPointTourneeArr);
                $this->get('fire_php')->log($tourneeDistrib);
            }

            if ($tourneeDistrib[0]['nb'] <= 0) {
                $jsonArr['codeErr'] = 6;
                $jsonArr['msgErr'] = 'Le point de livraison ne semble pas être intégré dans la tournée à  partir de la date sélectionnée.';
            }

            // On récupére les informations générales de la tournée sur un de ses points dans tournee_détail
            $pointTournee = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getSampleTourneePoint($parametres['tourneecible_code'], (int) $parametres['flux_id'], (int) $tourneeDestination[0]->getJour()->getId());

            if (empty($pointTournee)) {
                // Débug du point
                if ($this->container->has('fire_php')) {
                    $this->get('fire_php')->error('$pointTournee est vide, des valeurs par défaut vont être chargées.');
                }

                $pointTournee[] = array(
                    'debut_plage_horaire' => NULL,
                    'fin_plage_horaire' => NULL,
                    'duree' => NULL,
                    'duree_viste_fixe' => NULL,
                );
            }

            // On teste sur la date pour ne pas faire de modifications dans le passé
            $now = new \DateTime();
            $date = new \DateTime($parametres['date'] . ' 00:00:00');
            $interval = $now->diff($date);
            $iNbJoursDiff = (int) $interval->days;
            $iMaxJoursPassesModifOk = $this->container->getParameter("GEOC_TOURNEE_MAX_JOURS_MODIFS");

            if ($now > $date && $iNbJoursDiff > $iMaxJoursPassesModifOk) {
                $jsonArr['codeErr'] = 7;
                $jsonArr['msgErr'] = 'Les modifications ne peuvent être faites sur le passé, au delà  de ' . $iMaxJoursPassesModifOk . ' jours.';
            }

            // Test final
            if ($jsonArr['codeErr']) {
                $jsonArr['codeRetour'] = 'nok';
                $jsonArr['msgRetour'] = 'Des erreurs ont été détectées.';
            } else {
                // On lance les requêtes de mise à  jour
                // Récupération de l'ordre du point dans la nouvelle tournée
                $aPointCible = array(
                    'id' => $parametres['point_td_id'],
                    'x' => $pointLivraison[0]->getGeox(),
                    'y' => $pointLivraison[0]->getGeoy()
                );

                // Récupération de tous les points de la tournée de destination
                $paramsTourneeDest = array(
                    'date_parution' => $parametres['date'],
                    'code_modele_tournee' => $parametres['tourneecible_code'],
                    'depot_id' => $parametres['depot_id']
                );
                
                $tdRepo = $em->getRepository('AmsAdresseBundle:TourneeDetail');
                $tdRepo->setContainer($this->container);
                $infosPointsTournee = $tdRepo->getPointsTournee($parametres['tourneecible_code']);
                
                if ($this->container->has('fire_php')) {
                    $this->get('fire_php')->log('Récupération de tous les points de la tournée de destination');
                    $this->get('fire_php')->log($paramsTourneeDest);
                    $this->get('fire_php')->log($infosPointsTournee);
                }

                // CASL
                $caslConditionsTbl = array(
                    'tournee_destination_jour_id' => (int) $parametres['tourneecible_id'],
                    'point_livraison_id' => (int) $parametres['point_id'],
                    'tournee_source_jour_id' => (int) $parametres['tourneesource_id'],
                    'tournee_jour_id' => (int) $parametres['tourneesource_id'], // Pour utilisation du tableau dans une autre requête
                    'flux_id' => (int) $parametres['flux_id'],
                    'date_distribution' => (string) $parametres['date']
                );

                // Récupération de la liste des abonnés concernés par le changement
                $caslAbosConcernes = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->listerAbonnesPourPointDistrib($caslConditionsTbl);
                // Débug de la liste d'abonnés
                if (empty($caslAbosConcernes)) {
                    if ($this->container->has('fire_php')) {
                        $this->get('fire_php')->error('La liste d\'abonnés concernés est vide.');
                        $this->get('fire_php')->log($caslConditionsTbl);
                        $this->get('fire_php')->log($caslAbosConcernes);
                    }
                }

                $listeAbonne = '';

                if (!empty($caslAbosConcernes)) {
                    $aAbonnesIds = array();
                    foreach ($caslAbosConcernes as $abonne) {
                        if (!in_array($abonne['abonne_soc_id'], $aAbonnesIds)) {
                            $aAbonnesIds[] = $abonne['abonne_soc_id'];
                        }
                    }
                    $listeAbonne = implode(',', $aAbonnesIds);
                }

                // Tournées Détail
                $tdConditionsTbl = array(
                    'tournee_destination_jour_code' => $parametres['tourneecible_code'],
                    'tournee_source_jour_code' => $parametres['tourneesource_code'],
                    'liste_abonne_id' => $listeAbonne,
                    'debut_plage_horaire' => $pointTournee[0]['debut_plage_horaire'],
                    'fin_plage_horaire' => $pointTournee[0]['fin_plage_horaire'],
                    'duree' => $pointTournee[0]['duree'],
                    'duree_visite_fixe' => $pointTournee[0]['duree_viste_fixe']
                );

                // Cas des tournées déjà alimentées   
                if (!empty($infosPointsTournee)) {
                    $aContexte = array_fill(0, count($infosPointsTournee), 'td');
                    $aPointsTournee = array_map(array('self', 'transformForGeoWs'), $infosPointsTournee, $aContexte);
                    
                    // Calcul du nouvel ordre de la tournée de destination
                    $geoservice = $this->container->get('ams_carto.geoservice');
                    $aInfosNouvTournee = $geoservice->insertNewPoint($aPointsTournee, $aPointCible, true);
                    
                    if ($this->container->has('fire_php')) {
                    $this->get('fire_php')->log('Calcul du nouvel ordre de la tournée de destination');
                    $this->get('fire_php')->log($aInfosNouvTournee);
                }

                    // Intégration du nouvel ordre pour le point dans CASL
                    $caslConditionsTbl['nouvel_ordre'] = $aInfosNouvTournee['infosOperation']['insertionResult'] == true ? $aInfosNouvTournee['infosOperation']['cibleNouvOrdre'] : null;

                    // Modification des points de livraison
                    $caslRetOpe = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->changerPointsdeTournee($caslConditionsTbl);

                    // Intégration du nouvel ordre pour le point dans TD
                    $tdConditionsTbl['nouvel_ordre'] = $aInfosNouvTournee['infosOperation']['insertionResult'] == true ? $aInfosNouvTournee['infosOperation']['cibleNouvOrdre'] : null;

                    $tdRetOpe = $em->getRepository('AmsAdresseBundle:TourneeDetail')->changePointDeTournee($tdConditionsTbl);
                    // On décale les points de livraison dans TD si besoin
                    if ($aInfosNouvTournee['infosOperation']['cibleFusion'] == false) {
                        $iOrdreDepartDecalage = (int)$aInfosNouvTournee['infosOperation']['cibleNouvOrdre'];
                        $em->getRepository('AmsAdresseBundle:TourneeDetail')->decalerOrdrePoints($iOrdreDepartDecalage, $parametres['tourneecible_code'], '+', 1, $parametres['point_id']);
                    }

                    // Appliquer le nouvel ordre de la tournée théorique (TD) sur les tournées réelles (CASL)
                    $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->repliquerOrdreModeleSurDistrib($parametres['tourneecible_code'], $parametres['date']);
                    $jsonArr['codeErr'] = 0;
                    $jsonArr['msgErr'] = '';
                    $jsonArr['codeRetour'] = 'ok';
                    $jsonArr['msgRetour'] = '<strong>Le point de livraison a bien changé de tournée.</strong><br/>Dans un instant la page va être rechargée...';
                   
                } 
                else { // Tournée vide initialement
                    // Basculement de point vers une tournée vide
                    $tdConditionsTbl['nouvel_ordre'] = 1; // Ordre du point (prendre en compte le départ dépot)
                    $caslConditionsTbl['nouvel_ordre'] = 1; // Ordre du point (prendre en compte le départ dépot)
                    // Modification des points de livraison
                    $caslRetOpe = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->changerPointsdeTournee($caslConditionsTbl);

                    // Intégration du nouvel ordre pour le point dans TD
                    foreach ($aAbonnesIds as $iAbonne) {
                        $oAbonne = $em->getRepository('AmsAbonneBundle:AbonneSoc')->findOneById($iAbonne);
                        $oTourneeJour = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findOneById($parametres['tourneecible_id']);
                        $aClientInfos = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getClientInfo(array(
                            'abonne_soc_id' => $iAbonne,
                            'tournee_jour_id' => $caslConditionsTbl['tournee_source_jour_id'],
                            'date_distrib' => $caslConditionsTbl['date_distribution'],
                            'point_livraison_id' => $parametres['point_id'],
                            'flux_id' => $caslConditionsTbl['flux_id'],
                        ));

                        if (!empty($aClientInfos)) {
                            $sDate = date('Y-m-d H:i:s');

                            $aNouvPointTD = array(
                                'ordre' => 1,
                                'longitude' => $aPointCible['x'],
                                'latitude' => $aPointCible['y'],
                                'modele_tournee_jour_code' => $parametres['tourneecible_code'],
                                'num_abonne_id' => $iAbonne,
                                'num_abonne_soc' => $oAbonne->getNumaboExt(),
                                'jour_id' => $oTourneeJour->getJour()->getId(),
                                'flux_id' => $oTourneeJour->getTournee()->getGroupe()->getFlux()->getId(),
                                'source_modification' => 'changePointDeTournee-1',
                                'date_modification' => $sDate,
                                'point_livraison_id' => (int) $parametres['point_id'],
                                'a_traiter' => 1,
                                'distance_trajet' => 0,
                                'trajet_cumule' => 0,
                                'duree_conduite' => '00:00:00',
                                'heure_debut' => '00:00:00',
                                'duree' => '00:00:00',
                                'soc' => $aClientInfos[0]['soc_code_ext'],
                            );

                            // Insertion
                            $iNewPointId = $em->getRepository('AmsAdresseBundle:TourneeDetail')->insertTourneeDetail($aNouvPointTD);
                            // Décalage des autres points de la tournée
                            $em->getRepository('AmsAdresseBundle:TourneeDetail')->decalerOrdrePoints(1, $parametres['tourneecible_code'], '+', 1, $iNewPointId);
                        }
                    }

                    if ($iNewPointId) {
                        $jsonArr['codeErr'] = 0;
                        $jsonArr['msgErr'] = '';
                        $jsonArr['codeRetour'] = 'ok';
                        $jsonArr['msgRetour'] = '<strong>Le point de livraison a bien changé de tournée.</strong><br/>Dans un instant la page va être rechargée...';
                    } else {
                        $jsonArr['codeErr'] = 0;
                        $jsonArr['msgErr'] = '';
                        $jsonArr['codeRetour'] = 'ok';
                        $jsonArr['msgRetour'] = 'Opération effectuée. Des incohérences ont peut-être été corrigées. La page va être rechargée...';
                    }
                }

                // On lance le recalcul des ordres de la tournée source en asynchrone
                $sCmd = 'php ';
                $sCmd .= $this->get('kernel')->getRootDir()
                        . '/console update_ordre_tournee_casl '
                        . $parametres['tourneesource_code'] . ' ' . $parametres['date']
                        . ' --env ' . $this->get('kernel')->getEnvironment();
                GlobalController::bgCommandProxy($sCmd);

                // On demande le recalcul des ordres de la tournée de destination en asynchrone
                // Cette commande est exécutée car les opérations menées plus haut s'appuie sur un décalage de l'ordre en non pas sur un recalcul global.
                // Plus les données seront cohérentes (ce sera le cas lorsque les recalculs seront faits quotidiennement) moins il sera nécessaire de le faire.
                $sCmd = 'php ';
                $sCmd .= $this->get('kernel')->getRootDir()
                        . '/console update_ordre_tournee_casl '
                        . $parametres['tourneecible_code'] . ' ' . $parametres['date']
                        . ' --env ' . $this->get('kernel')->getEnvironment();
                GlobalController::bgCommandProxy($sCmd);
            }

            return new Response(json_encode($jsonArr), 200, array('Content-Type' => 'Application/json'));
        }
    }

    
    public function orderTourneeDetailAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $codeTournee = $request->get("code");
        $dateDistrib = $request->get("dateDistrib");
        $sPointLivraisonId = $request->get('ptLivraisonIdOrder');
        $aPointLivraisonId = explode(',', $sPointLivraisonId);
        $order  = 1;
        foreach($aPointLivraisonId as $pointLivraisonId){
            $em->getRepository('AmsAdresseBundle:TourneeDetail')->updateOrderByPointLivraison($codeTournee,$order,$pointLivraisonId,'Reordonnancement de la tournee via cartographie');
            $order++;           
        }
        if($this->diffDate($dateDistrib) <= $this->container->getParameter("GEOC_TOURNEE_MAX_JOURS_MODIFS") )
            $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->repliquerOrdreModeleSurDistrib($codeTournee, $dateDistrib);
            
        $jsonArr = array('codeRetour'=>'ok','msgRetour'=>'Mise à jour réaliser avec succes');
        return new Response(json_encode($jsonArr), 200, array('Content-Type' => 'Application/json'));
    }

    /** MISE A JOUR D'UNE TOURNEE (DISTANCE,TEMPS ...) * */
    public function updateDataAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        if (($request->getMethod() == 'POST')) {
            $code = $request->request->get('code');
            $date = $request->request->get('date');
            $depot = $request->request->get('depot_id');
            /** RECUPERATION DES POINTS DE LA VUE POUR LE WS ROUTE SERVICE**/
            $paramsTourneeDest = array(
                'date_parution' => $date,
                'code_modele_tournee' => $code,
                'depot_id' => $depot
            );
            $em->getRepository('AmsAdresseBundle:TourneeDetail')->setContainer($this->container);
            $data = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getTourneeDatasFromView($paramsTourneeDest, true,true);
            $this->callWsRouteService($data,$date,$code);
        }
        return new Response('', 200, array('Content-Type' => 'Application/json'));
    }

    public function getTourneeByOrderAction(Request $request) {
        $sOrderStop = $request->request->get('order_stop');
        $em = $this->getDoctrine()->getManager();
        $json = '';
        $count = 1;
        /** REOGARNISE L'ORDRE DES ARRETS * */
        if ($sOrderStop == 'true') {
            $sId = $request->request->get('sId');
            $aNewOrder = explode('_', $sId);
            foreach ($aNewOrder as $id) {
                $query = $em->getRepository('AmsAdresseBundle:TourneeDetail')->find($id);
                $query->setOrdreStop($count);
                $em->flush();
                $count++;
            }
            $json = 'success';
        }
        /** AFFICHE TOUS LES ARRETS DU POINTS DE LIVRAISON* */ else {
            $order = $request->request->get('order');
            $code = $request->request->get('code');
            $date = $request->request->get('date');
            $pointLivraison = $request->request->get('pointLivraison');

            $tournees = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getTourneesJoinAbonne($code, $pointLivraison, $date);
            foreach ($tournees as $tournee) {
                $json .= '<li class="' . $tournee['id'] . '" title="order_' . $count . '">' . $tournee['vol1'] . ' ' . $tournee['vol2'] . '</li>';
                $count++;
            }
        }

        return new Response(json_encode($json), 200, array('Content-Type' => 'Application/json'));
    }

    /*
     * Action de test de Search Around
     */

    public function testsearcharoundAction() {
        $cible = array(
            'id' => 12,
            'x' => '2.3420497289501',
            'y' => '48.860518400955'
        );

        $pointsArr = array(
            array(
                'id' => 15,
                'x' => '2.3438823125318',
                'y' => '48.858833348554'
            ),
            array(
                'id' => 16,
                'x' => '2.3430718279',
                'y' => '48.861971435'
            ),
            array(
                'id' => 17,
                'x' => '2.3452872107055',
                'y' => '48.858804236731'
            ),
            array(
                'id' => 18,
                'x' => '2.3470875289',
                'y' => '48.858788379'
            ),
        );

        $optionsArr = array(
            'Projection' => 'WGS84'
        );

        // On récupére le service
        $geoservice = $this->container->get('ams_carto.geoservice');

        // Appel du service
        $classement = $geoservice->callSearchAround($cible, $pointsArr, $optionsArr);
        var_dump($classement);
        exit();
    }

    /**
     * Action utilisé en Ajax pour récupérer toutes
     * les tournées par rapport à  un code de modéle de tournée
     * */
    public function getTourneeByCodeAction(Request $request) {
        if (($request->getMethod() == 'POST')) {
            $code = $request->request->get('tournee');
            $em = $this->getDoctrine()->getManager();
            $tournees = $em->getRepository('AmsAdresseBundle:TourneeDetail')->findByCodeGroupByOrdre($code);
        }
        return $this->render('AmsCartoBundle:Default:modal.html.twig', array(
                    'tournees' => $tournees,
        ));
    }

    /** GRID AFFICHANT NOM DE TOURNEE AINSI QUE SES CARACTERISTIQUES * */
    public function gridListTourneeAction() {
        $session = new Session();
        $tournees = $session->get('CartoListTourneeByDepot');
        $response = $this->renderView('AmsCartoBundle:Default:grid_list_tournee.xml.twig', array(
            'tournees' => $tournees,
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function gridAction() {
        $session = new Session();
        $tournees = $session->get('CartoTournee');
        $response = $this->renderView('AmsCartoBundle:Default:grid.xml.twig', array(
            'tournees' => $tournees,
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function exportAdressToExcelAction(Request $request) {
        if ($request->isXmlHttpRequest()) {
            $lineExcel = 2;
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
            $even = $this->getStyle('styleEventRows');
            $odd = $this->getStyle('styleOddRows');
            $styleArray = $this->getStyle('styleEventRows');
            $phpExcelObject->getActiveSheet()->getStyle("A1:B1")->applyFromArray($styleArray);
            /** ENTETE * */
            $cLetterEnd = 'B';
            $aSize = array('A' => 15, 'B' => 50);
            $aHeaderTitle = array('A' => 'Arrêts', 'B' => 'Adresses');
            for ($cLetter = 'A'; $cLetter <= $cLetterEnd; $cLetter++) {
                $phpExcelObject->getActiveSheet()->getColumnDimension($cLetter)->setWidth($aSize[$cLetter]);
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue($cLetter . '1', $aHeaderTitle[$cLetter]);
            }
            $phpExcelObject->getActiveSheet()->getStyle("A1:B1")->applyFromArray($this->getStyle('styleHeader'));
            /** BODY * */
            foreach ($request->get('aAdresse') as $key => $adresse) {
                $phpExcelObject->setActiveSheetIndex(0)
                        ->setCellValue('A' . $lineExcel, (int) $key + 1)
                        ->setCellValue('B' . $lineExcel, $adresse);
                $style = (($lineExcel % 2) == 0) ? $odd : $even;
                $phpExcelObject->getActiveSheet()->getStyle("A$lineExcel:B$lineExcel")->applyFromArray($style);
                $lineExcel++;
            }
            $phpExcelObject->getActiveSheet()->setTitle($request->get('tournee'));
            $writer->save('tmp/carto_export_adresse.xls');
            exit('done');
        }
    }

    /**
     * export excel en ajax 
     * @return [type] [description]
     */
    public function getFileExportAction() {
        return new Response(file_get_contents('tmp/carto_export_adresse.xls'), 200, array(
//            'Content-Encoding' => 'charset=iso-8859-1', // deprecated notb support in CHROME
            'Content-Encoding' => 'zlib', 'deflate',
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="carto_export_adresse' . '.xls"'
        ));
    }

    /**
     * Fonction de calcul des temps de tournée
     * @param int $iNbLivraison Nombre total d'abonnés supplémentaires livrés sur un point de livraison
     * @param int $iNbArret Nombre de points de livraisons distincts
     * @param time $sTourneeTime Le temps de tournée récupéré de Geoconcept
     * @return array Le tableau qui contient le temps total, le temps de conduite et de visite
     */
    function aDuree($iNbLivraison, $nbAbonne, $sTourneeTime) {
        $iSecondeStop = ($iNbLivraison * 30) + (($nbAbonne - $iNbLivraison) * 5);
        $sTourneeTime = explode(':', $sTourneeTime);
        $iSecondesTournee = $sTourneeTime[0] * 3600 + $sTourneeTime[1] * 60 + $sTourneeTime[2];
        $iTimeTournee = $iSecondeStop + $iSecondesTournee;
        return array('TOTAL_TIME' => gmdate("H:i:s", $iTimeTournee),
            'CONDUITE_TIME' => gmdate("H:i:s", $iSecondesTournee),
            'VISIT_TIME' => gmdate("H:i:s", $iSecondeStop),
        );
    }

     private function change_key( $array, $aReplace_key) {
        $json_array = json_encode($array);
        foreach($aReplace_key as $old_key => $new_key){
            $json_array = str_replace($old_key,$new_key,$json_array);
        }
        return (array)json_decode($json_array);
    }
    
    /** APPEL AU WEBSERVICE ROUTE SERVICE * */
    function callWsRouteService($data,$dateDistrib,$sCodeModeleTournee) {
        $jsonArr = array(
            'codeRetour' => 0,'codeErr' => 0,'msgErr' => '',
            'msgRetour' => "L'ordonnancement de la tournée à  été pris en compte.",
        );
        $em = $this->getDoctrine()->getManager();
        $aReplace_key = array('longitude' => 'X','latitude' => 'Y','x'=>'X','y'=>'Y');
        $data = $this->change_key($data,$aReplace_key);
        /** CAST ARRAY OBJ TO ARRAY TO ARRAY**/
        $aData = json_decode(json_encode($data), true);
        $serv = $this->container->get('ams_carto.geoservice');
        $emTourneeStat = $em->getRepository('AmsAdresseBundle:TourneeStats')->exist($dateDistrib,$sCodeModeleTournee);
        $classement = $serv->wsRouteService($aData);
        $stats = json_encode(json_decode(json_encode($classement),true));
        if($emTourneeStat){
            $em->getRepository('AmsAdresseBundle:TourneeStats')->update($dateDistrib,$sCodeModeleTournee,$stats);
        }else{
            $em->getRepository('AmsAdresseBundle:TourneeStats')->insert($dateDistrib,$sCodeModeleTournee,$stats);
        }
        return $jsonArr;

//        $nb_stop = is_null($aPointDepart) ? $nb_stop = count($aDataWAYPOINT) : count($aDataWAYPOINT) - 1;
//        $nb_stop = is_null($aPointRetour) ? $nb_stop : $nb_stop - 1;

//        $aStatTime = $this->aDuree(count($aIdTourneeDistinct), $nb_stop, $aDataWAYPOINT[(count($aDataWAYPOINT) - 1)]->FoundTime);
//        $aOrderPoint = array();
    }

    /**
     * Méthode qui prépare les points d'une tournée pour l'utilisation par un WS Geoconcept
     * @param array $aPoint Le point dans la tournée
     * @param string $sContexte Le contexte de l'opération (permet d'adapter le comportement)
     * @return array $aPointGeo Le tableau d'informations du point pour Geoconcept
     * @author Marc-Antoine Adélise
     */
    public static function transformForGeoWs($aPoint, $sContexte) {
        $aPointGeo = array();
        if (!empty($aPoint)) {
            $aPointGeo['ordre'] = (int) $aPoint['ordre'] > 0 ? (int) $aPoint['ordre'] : 0;
            $aPointGeo['x'] = !is_null($aPoint['longitude']) ? $aPoint['longitude'] : null;
            $aPointGeo['y'] = !is_null($aPoint['latitude']) ? $aPoint['latitude'] : null;
            $aPointGeo['id'] = $aPoint['id'];
        }

        if (is_null($aPointGeo['x']) || is_null($aPointGeo['y'])) {
            $aPointGeo = null;
        }

        return $aPointGeo;
    }

    /**
     * Méthode qui retourne la liste des points de livraison dans l'ordre pour une tournée de client_a_servir_logist
     * @param type $listePointsTD  Les points de TD provenant de listerPointsTourneeOrdonnes()
     * @param type $listePointsCASL Les points de CASL provenant de listerPointsTourneeOrdonnes()
     * @return array $aPointsCommuns La liste des points de livraison de la tournée CASL dans l'ordre
     */
    public static function trouverOrdrePointsCASL($listePointsTD, $listePointsCASL) {
        $aPointsCommuns = array();
        $aIdsEnregistres = array();
        $ordre = 1;
        if (!empty($listePointsTD) && !empty($listePointsCASL)) {
            foreach ($listePointsTD as $pointTD) {
                foreach ($listePointsCASL as $pointCASL) {
                    if ($pointTD['coords'] == $pointCASL['coords'] || $pointTD['num_abonne_id'] == $pointCASL['abonne_soc_id']) {
                        if (!in_array((int) $pointCASL['point_livraison_id'], $aPointsCommuns)) {
                            if (!in_array((int) $pointCASL['casl_id'], $aIdsEnregistres)) {
                                $aIdsEnregistres[] = (int) $pointCASL['casl_id'];
                                $aPointsCommuns[] = array(
                                    "id" => (int) $pointCASL['casl_id'],
                                    "point_livraison_id" => (int) $pointCASL['point_livraison_id'],
                                    "nouvel_ordre" => $ordre
                                );
                                $ordre++;
                            }
                        }
                    }
                }
            }
        }
        return $aPointsCommuns;
    }

    /**
     * Méthode qui ajoute à  un point les valeurs par défaut qui permettent à  la carte de s'afficher malgré le manque d'informations
     * @param array $aPoint Le point sélectionné pour véhiculer les infos géographiques de la tournée.
     * @param bool $bIsDepot TRUE si le point fourni est un dépôt
     * @return array $aPointInfos Le point augmenté des informations nécessaires.
     */
    private function ajouterPointGeoValeursDefaut($aPoint, $bIsDepot = FALSE) {
        $aPoint['duree_viste_fixe'] = $this->container->getParameter('GEO_DATA_DUREE_VISITE_DEFAUT');
        if (!isset($aPoint['libelle'])){
            $aPoint['libelle'] = 'N/A';
        }
        
        if (!isset($aPoint['d_adresse'])){
            $aPoint['d_adresse'] = $bIsDepot ? $aPoint['adresse_depot']: '';
        }
        
        return $aPoint;
    }

    private function firePhp($value) {
        $firePhp = $this->get('fire_php');
        return $firePhp->log($value);
    }

    public function getStyle($item) {

        if ($item == 'styleEventRows')
            return array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('argb' => 'FFE3EFFF'),
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,),
            ));
        if ($item == 'styleOddRows')
            return array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'FFFFFFFF'),
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,),
            ));

        if ($item == 'styleArray')
            return array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,),),);
        if ($item == 'styleHeader')
            return array('font' => array('bold' => true, 'size' => 10),
                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'FFE1E6FA'),),
            );
        if ($item == 'styleCell')
            return array('font' => array('bold' => false, 'size' => 13,),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'FFC4D7ED'),),);
    }

    function add_heures($heure1, $heure2) {
        if(is_array($heure2))
            $hour_2 = $heure2['TOTAL_TIME'];
        else 
            $hour_2 = $heure2;
        $secondes1 = $this->heure_to_secondes($heure1);
        $secondes2 = $this->heure_to_secondes($hour_2);
        $somme = $secondes1 + $secondes2;
        //transfo en h:i:s
        $s = $somme % 60; //reste de la division en minutes => secondes
        $m1 = ($somme - $s) / 60; //minutes totales
        $m = $m1 % 60; //reste de la division en heures => minutes
        $h = ($m1 - $m) / 60; //heures
        $resultat = sprintf("%02d", $h) . ":" . sprintf("%02d", $m) . ":" . sprintf("%02d", $s);
        return $resultat;
    }

    function heure_to_secondes($heure) {
        $array_heure = explode(":", $heure);
        if(count($array_heure) <=1) return '';
        $secondes = 3600 * $array_heure[0] + 60 * $array_heure[1] + $array_heure[2];
        return $secondes;
    }
    
    function getInfosBulles(){
        $em = $this->getDoctrine()->getManager();
        $oInfoBulles = $em->getRepository('AmsSilogBundle:MessagesInfos')->findAll();
        $aInfosBulles = array();
        foreach($oInfoBulles as $row){
            $aInfosBulles[$row->getGlyphicon()] = 
                array(
                    'title' => $row->getTitle(),
                    'description' => $row->getDescription(),
                );
        }
        return $aInfosBulles;
    }
    
    
    /**
     * Méthode qui retourne le tableau des points d'une tournée au format Openlayers.
     * Sert à représenter une tournée dans la cartographie
     * @see CartoController::generateOpenLayersPointInfos() La méthode qui génère le contenu de la variable $aFeatures
     * @param array $aFeatures Le tableau contenant les points de la tournée
     * @param string $sDate La date de distribution au format YYYY-MM-DD
     * @param array $aPoint Le point de la tournée sur lequel récupérer les informations générales Géoconcept de la tournée
     * @param int $iNbAbosUniques Le nombre d'abonnés uniques
     * @param string $sColor La couleur du tracé sur la carte
     * @return array $aRetour Tableau contenant les informations qui devront être transmises en JSON
     */
    private function generateOpenLayersTourneeInfos($aFeatures, $sDate, $aPoint, $iNbAbosUniques, $sColor) {
        $aRetour = array(
            'crs' => array(
                'type' => "name",
                'properties' => array(
                    'name' => 'urn:ogc:def:crs:OGC:1.3:CRS84'
                )
            ),
            'type' => 'FeatureCollection',
            'features' => $aFeatures,
            'properties' => array(
                "tId" => $aPoint['mtj_id'],
                "tCode" => $aPoint['code_modele_tournee'],
                "tDate" => $sDate,
                "tTempsConduite" => ($aPoint['drive_time']) ? $aPoint['drive_time'] : '',
                "tTempsVisiteFixe" => $aPoint['duree_viste_fixe'],
                "tTempsVisite" => ($aPoint['temps_visite']) ? $aPoint['temps_visite'] : '',
                "tDuree" => ($aPoint['tournee_time']) ? $aPoint['tournee_time'] : '',
                "tTempsVisiteFixe" => $aPoint['duree_viste_fixe'],
                "tDuree" => ($aPoint['tournee_time']) ? $aPoint['tournee_time'] : '',
                "tDistance" => number_format($aPoint['trajet_total']) . ' km',
                "tDepot" => (!is_null($aPoint['libelle'])) ? $aPoint['libelle'] : '',
                "tAdresseDepot" => $aPoint['d_adresse'],
                "tDepotId" => $aPoint['d_id'],
                "tHeureD" => $aPoint['heure_debut'],
                "tHeureF" => $this->add_heures($aPoint['heure_debut'], $aPoint['tournee_time']),
                "tNbArrets" => ($aPoint['nb_stop']) ? $aPoint['nb_stop'] : '',
                "tNbAbonne" => $iNbAbosUniques,
                "tColor" => $sColor
            )
        );
        return $aRetour;
    }
    
    /**
     * Méthode qui formate les informations d'un point à afficher sur la carte au format OpenLayers/Geoconcept
     * Le résultat de cette méthode constitue la variable $aFeatures de la méthode generateOpenLayersTourneeInfos()
     * @see CartoController::generateOpenLayersTourneeInfos() La méthode avec laquelle utiliser le résultat
     * @param array $aPoint Le tableau contenant les informations sur le point à intégrer
     * @param array $aInfosClient Le tableau avec les informations du client
     * @param string $sNumTournee Le numéro de la tournée
     * @param array $aInfosProduit Le tableau avec les informations de produit
     * @param string $sCouleur La couleur de point à utiliser sur la carte.
     * @return $aRetour Le tableau contenant les informations du point
     */
    private function generateOpenLayersPointInfos($aPoint, $sNomClient, $sNumTournee, $aInfosProduit, $sCouleur) {
        $aRetour = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array((float) $aPoint['longitude'], (float) $aPoint['latitude'])
            ),
            'properties' => array(
                'aTitle' => $aPoint['a_adresse'] . ' ' . $aPoint['cp'] . ' ' . $aPoint['ville'],
                'aId' => $aPoint['numabo_ext'],
                'aUniqueId' => (int) $aPoint['abonne_unique_id'],
                "aTdId" => $aPoint['td_id'],
                'aType' => 'abo',
                'aDureeConduite' => $aPoint['duree_conduite'],
                'aOrdreClient' => $aPoint['ordre'],
                'aPointLivraisonId' => $aPoint['point_livraison_id'],
                'aNomClient' => $sNomClient,
                'aIdClient' => $aPoint['num_abonne'],
                'aHeureClient' => $aPoint['heure_debut'],
                'aDureeClient' => $aPoint['duree'],
                'aTrajetClient' => $aPoint['distance_trajet'],
                'aTrajetCumulClient' => $aPoint['trajet_cumule'],
                'aTourneeNumber' => $sNumTournee,
                'aTourneeId' => (int) $aPoint['mtj_id'],
                'aTourneeCode' => (string) $aPoint['code_modele_tournee'],
                "aPointColor" => $sCouleur,
                "aTitreProduit" => $aInfosProduit['sProduitTitre'],
                "aImageProduitDispo" => $aInfosProduit['bproduitImageFound'],
                "aImageProduit" => $aInfosProduit['sProduitImage']
            )
        );
        
        // Modifications faites pour le départ et retour dépôt
        if ($aPoint['bDepartDepot'] || $aPoint['bRetourDepot']){
            $aIndexesASupprimer = array('aTdId', 'aDureeClient', 'aTitreProduit', 'aImageProduitDispo','aImageProduit' );
            foreach ($aIndexesASupprimer as $sIndex){
                unset($aRetour['properties'][$sIndex]);
            }
            
            $aRetour['properties']['aDureeConduite'] = 0;
        }

        return $aRetour;
    }
    
    private function fillArrayParameter(&$data,$param,$value){
        if(!$value) return false;
        $data[$param] = $value;
    }

   private function diffDate($date){
        $date1=date_create(date('Y-m-d'));
        $date2=date_create($date);
        $diff= date_diff($date1,$date2);
        return $diff->days;
    }
    
    private function getArretByTournee($aDataTournee){
        $aPointLivraison= array();
        $nbPointLivraison = array_count_values(array_map(function($item) use(&$aPointLivraison) {
        if($item['date_distrib'] != '' && !in_array($item['point_livraison_id'],$aPointLivraison)){
            $aPointLivraison[] = $item['point_livraison_id'];
            return 1;
        }
            return 0;
        }, $aDataTournee));
        
        return $nbPointLivraison[1];
    }
    
    private function getAbonneByTournee($aDataTournee){
        $nbAbonneALivrer = array_count_values(array_map(function($item) {
        if($item['date_distrib'] == '')
            return  0;
        return 1;
        }, $aDataTournee));
        
        return $nbAbonneALivrer[1];
    }
    
    function getOpenLayerPoints($aJson){
        foreach($aJson as $key => $json){
            $data = json_decode($json,true);
            foreach($data['features'] as $keyFeature=>$tmp){
               if($tmp['properties']['dateDistrib'] == '')
                  unset($data['features'][$keyFeature]);
            }
            $data['features'] = array_values($data['features']);
            $aJson[$key] = json_encode($data);
        }
        return $aJson;
    }
}
