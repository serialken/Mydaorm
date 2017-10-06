<?php

namespace Ams\AdresseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\SilogBundle\Controller\GlobalController;
use Ams\AdresseBundle\Form\AdresseRnvpType;
use Ams\AdresseBundle\Entity\AdresseRnvp;
use Ams\SilogBundle\Lib\SoapClientLocal;
use Ams\RnvpBundle\Lib\RnvpLocalException;
use Ams\AdresseBundle\Entity\Commune;
use Symfony\Component\HttpFoundation\Response;
use Ams\AdresseBundle\Entity\TypeChangement;
use Ams\AbonneBundle\Form\AbonneSocType;
use Ams\SilogBundle\Entity\Depot;

/**
 * Gestion des points de livraison
*/
class PointLivraisonController extends GlobalController {

    /**
     * Ajout d'un nouveau point de livraison
    */
    public function indexAction(Request $request) {

        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $em = $this->getDoctrine()->getManager();
        
        $session = $this->get('session');
        // Liste des depôts accessible à l'utilisateur
        $depots = $session->get("DEPOTS");
        
        $ResRNVP = '';
        $adresseRnvp = new AdresseRnvp();

        // Récupération des informations envoyées par la carto
        if ($request->query->get('cartopreset') == 1) {
            
            if ($request->query->get("ville")) {
                
                $commune = $em->getRepository('AmsAdresseBundle:Commune')->findBy(array("libelle" => $request->query->get("ville")));
                $commune = current($commune);

                /* @var $commune Commune */
                if ($commune) {
                    $adresseRnvp->setCommune($commune);
                    $adresseRnvp->setCp($commune->getCp());
                } else {
                    $adresseRnvp->setCp($request->query->get('cp'));
                }
            }

            $adresseRnvp->setVille($request->query->get('ville'));
            $adresseRnvp->setAdresse($request->query->get('adresse'));
        }
        
        $codeTournee = null;
        // ID de tournée passé en post ou en get ?
        if ($request->getMethod() == 'POST' || $request->getMethod() == 'GET') {
            $idTournee = $request->request->get('tourneeId') > 0 ? (int) $request->request->get('tourneeId') : null;
        }

        if ($idTournee) {
            $tournee = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->find($idTournee);
            $codeTournee = $tournee->getCode();
        }

        $form = $this->createForm(new AdresseRnvpType($depots), $adresseRnvp);
        $form->handleRequest($request);

        $msg = null;
        $etat = "alert";
        if ($request->getMethod() == 'POST' && $form->isValid()) {

            $param = array(
                "volet1" => '',
                "volet2" => '',
                "volet3" => $adresseRnvp->getCAdrs(),
                "volet4" => $adresseRnvp->getAdresse(),
                "volet5" => $adresseRnvp->getLieuDit(),
                "cp" => $adresseRnvp->getCp(),
                "ville" => $adresseRnvp->getCommune()->getLibelle()
            );

            $ws = $this->get('rnvp');
            $ResRNVP = $ws->normalise($param);
            $session->set('resultatRnvp', $ResRNVP);
            
            if ($ResRNVP->etatRetourRnvp != 'RNVP_OK') {
                $msg = "Attention le RNVP n'a pas bien fonctionnée [ " . $ResRNVP->etatRetourRnvp ." ] ! Voici l'adresse proposée:";
                $etat = "alert-warning";
            } else {
                $msg = "Le RNVP a bien fonctionnée vous pouvez passer au géocodage!";
                $etat = "alert-success";
            }
        }

        return $this->render('AmsAdresseBundle:PointLivraison:index.html.twig', array(
                    'form' => $form->createView(),
                    'ResRNVP' => $ResRNVP,
                    'message' => $msg,
                    'etat' => $etat,
                    'codeTournee' => $codeTournee,
                    'idTournee' => $idTournee
        ));
    }

    /**
     * 
     * @param Request $request
     * return une liste d'adresse avec le geocodage
    */
    public function geocodeAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        
        $adresseRnvp = $session->get('resultatRnvp'); // retour rnvp
        $listeAdrGeocodes = array(); //retour ws
        $listeAbonnes = array(); //liste des abonnes rataché au point de livraison
        $pointLivraisonId = ''; // point de livraison
        // Affectation à venir à une tournée ?
        $tourneeId = $request->query->get('idTournee') > 0 ? (int) $request->query->get('idTournee') : null;
        $commune = $em->getRepository('AmsAdresseBundle:Commune')->findOneByInsee($adresseRnvp->po_insee);
        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $date = new \DateTime();
        
        $adresseGeocode = new AdresseRnvp(); // adresse a normalisee et a geocodee
        $adresseGeocode->setCAdrs($adresseRnvp->pio_cadrs);
        $adresseGeocode->setAdresse($adresseRnvp->pio_adresse);
        $adresseGeocode->setLieuDit($adresseRnvp->pio_lieudit);
        $adresseGeocode->setCp($adresseRnvp->po_cp);
        $adresseGeocode->setVille($adresseRnvp->po_ville);
        $adresseGeocode->setInsee($adresseRnvp->po_insee);
        $adresseGeocode->setCommune($commune);
        $adresseGeocode->setTypeRnvp(1);
        $adresseGeocode->setUtilisateurModif($utilisateur);
        $adresseGeocode->setDateModif($date);

        $erreurs = $this->get('validator')->validate($adresseGeocode);
        //var_dump($erreurs);
        if (count($erreurs) == 0) { 
            // l'adresse rnvp nexiste pas dans la table rnvp voir contrainte d'unicite 
            // au premier appel on fait appel au ws pour recup la liste des adresse geocodés a proposé
            $srvGeocodage = $this->get('geocodage');
            $param = array(
                "City" => $adresseGeocode->getVille(),
                "PostalCode" => $adresseGeocode->getCp(),
                "AddressLine" => $adresseGeocode->getAdresse()
            );
            $listeAdrGeocodes = $srvGeocodage->geocode($param);
            // au second appel il a validé le geocodage donc on sette les valeurs du geocodage
            //  plus le rnvp qui est deja rempli au dessus
            if ($request->isXmlHttpRequest()) {
                $adresseGeocode->setStopLivraisonPossible('1');
                $adresseGeocode->setGeox($request->request->get('geox'));
                $adresseGeocode->setGeoy($request->request->get('geoy'));
                $adresseGeocode->setGeoScore($request->request->get('geoscore'));
                $adresseGeocode->setGeoType($request->request->get('geotype'));
                $adresseGeocode->setGeoEtat(2);
                
                $em->persist($adresseGeocode);
                $em->flush();
                $pointLivraisonId = $adresseGeocode->getId();//on recup le point de livraison
            }
        } else { 
            // l'adresse existe deja dans la table rnvp on la recup 
            // on sette le stop_livraison_possible pour qu'elle devienne un point de livraison ainsi que des infos supp(insee/commune/user/date)
            $adresseGeocode = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->findOneBy(array(
                'cAdrs' => $adresseRnvp->pio_cadrs,
                'adresse' => $adresseRnvp->pio_adresse,
                'lieuDit' => $adresseRnvp->pio_lieudit,
                'cp' => $adresseRnvp->po_cp,
                'ville' => $adresseRnvp->po_ville
            ));
            $adresseGeocode->setInsee($adresseRnvp->po_insee);
            $adresseGeocode->setCommune($commune);
            $adresseGeocode->setUtilisateurModif($utilisateur);
            $adresseGeocode->setDateModif($date);
            $adresseGeocode->setStopLivraisonPossible('1');
            $adresseGeocode->setGeoEtat(2);
            
            $em->persist($adresseGeocode);
            $em->flush();
            //var_dump($adresseGeocode);
            //on récupère le rnvp_id  pour le passer comme point de livraison et la liste des abonnés
            $pointLivraisonId = $adresseGeocode->getId();
            $listeAbonnes = $em->getRepository('AmsAdresseBundle:Adresse')->getAdresseByCritere(array('point_livraison_id' => $pointLivraisonId));
        }
       //throw new \Symfony\Component\HttpKernel\Exception\HttpException(405, "Uncaught exception!");
        return $this->render('AmsAdresseBundle:PointLivraison:geocode.html.twig', array(
                    'listeAdrGeocodes' => $listeAdrGeocodes,
                    'erreurs' => $erreurs,
                    'adresseGeocode' => $adresseGeocode,
                    'rnvpId' => $adresseGeocode->getId(),
                    'pointLivraisonId' => $pointLivraisonId,
                    'listeAbonnes' => $listeAbonnes,
                    'ctRegroup' => -1,
                    'idTournee' => $tourneeId
        ));
    }

    /**
     * Regroupement d'abonnees à un point de livraison
     * existant ou nouvellement cree
     * @param Request $request 
     * 
    */
    public function regroupementAbonneAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $pointLivraisonId = $request->get('pointLivraisonId');
        $tourneeJourId = $request->get('tourneeId');
        $utilisateurId = $session->get('UTILISATEUR_ID');
        $regroup = 1;
        $background = '';
        // si 0 on est dans la liaison pt de livraison --  si 1 on est dans le regroupement d'abonnés
        // A remplacer par les critères qui seront ultérieurement définis
        $adresseRnvp = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->findOneById($pointLivraisonId);
        $criteresCasl = array();
        if ($tourneeJourId) {
            $criteresCasl['tournee_jour_id'] = $tourneeJourId;
            $tournee = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->find($tourneeJourId);
            $tourneeJourCode = $tournee->getCode();
            // Récupération du dépot
            $depotCodeLib = $tournee->getTournee()->getGroupe()->getDepot();
            $depotCodeLibTbl = explode(' - ', $depotCodeLib);
            if (!empty($depotCodeLibTbl)){
                $depotCode = $depotCodeLibTbl[0];
                $depot =  $em->getRepository('AmsSilogBundle:Depot')->findOneByCode($depotCode);
                $depotId = $depot->getId();
            }
            
        } else {
            $tourneeJourCode = null;
        }

        $gpsCoords = array(
            'x'=>$adresseRnvp->getGeox(),
            'y'=>$adresseRnvp->getGeoy()
        );
        
        $listeAbonnes = $em->getRepository('AmsAdresseBundle:Adresse')->getAdresseByCritere(array('cp' => $adresseRnvp->getCp()), array('point_livraison_id' => $pointLivraisonId), $criteresCasl,$gpsCoords);
// On formate la liste des abonnés pour le tri
        $pointsArr = array();
        if (!empty($listeAbonnes)){
            // On prépare la cible de la requête Search Around
            $cible = array(
                'id' => $adresseRnvp->getId(),
                'x' => $adresseRnvp->getGeox(),
                'y' => $adresseRnvp->getGeoy()
            );
            // Tableau des options de l'appel du WS
            $optionsArr = array();
            // On boucle dans les abonnés
            foreach ($listeAbonnes as $pointAbo){
                $pointsArr[] = array(
                    'id' => (int)$pointAbo['rnvp_id'],
                    'x' => (float)$pointAbo['geox'],
                    'y' => (float)$pointAbo['geoy']
                );
            }
            
            // On récupère le service
            $geoservice = $this->container->get('ams_carto.geoservice');
            $this->saMaxDistance = $this->container->getParameter("GEOC_WS_SEARCHAROUND_SOAP_MAX_RADIUS");

            // On fait appel au tri des points de livraison
            $ordreAbonnes = $geoservice->callSearchAround($cible, $pointsArr, $optionsArr);
            $abonnesFiltres = $geoservice->filtreListe($ordreAbonnes->SearchAroundResult, null, 1000, $listeAbonnes);
            if (!empty($abonnesFiltres)){
                $listeAbonnes = $abonnesFiltres;
            }
        }
        $adresseIds = $request->request->get('adresseIds');

        // enregistrement des abonnés séléctionnées et mise à jour de la liste 
        if ($request->isXmlHttpRequest() && count($adresseIds) > 0) {
            $utilisateurId = $request->request->get('utilisateurId');
            $pointLivraisonId = $request->request->get('pointLivraisonId');
            $regroup = $request->request->get('regroup');
            
            $pointLivraison = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->findOneById($pointLivraisonId);
            $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($utilisateurId);
            $typeChangement = $em->getRepository('AmsAdresseBundle:TypeChangement')->findOneById('1'); // 1 = Changement point livraison

            $msg = "les abonnés séléctionnés ont bien été afféctés au point de livraison.";
            foreach ($adresseIds as $key => $adresseId) {
                $adresse = $em->getRepository('AmsAdresseBundle:Adresse')->findOneById($adresseId);
                
                $autreAdresse = $em->getRepository('AmsAdresseBundle:Adresse')->findAdresseChangePl($adresse);
                if (is_object($adresse)) {
                    $newAdresse = clone($adresse);
                } else {
                    $newAdresse = new \Ams\AdresseBundle\Entity\Adresse();
                }

                $newAdresse->setPointLivraison($pointLivraison);
                $newAdresse->setDateDebut(new \DateTime('tomorrow'));
                $newAdresse->setDateModif(new \DateTime());
                $newAdresse->setUtilisateurModif($utilisateur);

                if (is_object($adresse)) {
                    $adresse->setDateFin(new \DateTime());
                    $adresse->setDateModif(new \DateTime());
                    $adresse->setUtilisateurModif($utilisateur);
                    $adresse->setTypeChangement($typeChangement);

                    $em->persist($adresse);
                }
                $this->miseAjourAutreAdress($autreAdresse,$utilisateur,$typeChangement,$pointLivraison,$em);
                $em->persist($newAdresse);
                $em->flush();
            }

            $listeAbonnesRattache = $em->getRepository('AmsAdresseBundle:Adresse')->getAdresseByCritere(array('point_livraison_id' => $pointLivraisonId), null, $criteresCasl);
            //$listeAbonnes = $em->getRepository('AmsAdresseBundle:Adresse')->getAdresseByCritere(array('cp' => $adresseRnvp->getCp()), array('point_livraison_id' => $pointLivraisonId));

            $modal = $this->renderView('AmsAdresseBundle:PointLivraison:regroupement_abonne.html.twig', array(
                'listeAbonnes' => $listeAbonnes,
                'pointLivraisonId' => $pointLivraisonId,
                'utilisateurId' => $utilisateurId,
                'adresseRnvp' => $adresseRnvp
            ));

            $doublons = array_count_values(array_map(function($abo){  return $abo['pointLivraisonId']; }, $listeAbonnesRattache));
            
            if ($regroup == 0){
                $background = $this->renderView('AmsAdresseBundle:PointLivraison:liste_abonne_addr_exist.html.twig', array(
                    'listeAbonnes' => $listeAbonnesRattache,
                    'pointLivraisonId' => $pointLivraisonId,
                    'pointLivraisonAdresse' => $pointLivraisonId,
                    'adresseRnvp' => $adresseRnvp,
                    'idTournee' => $tourneeJourId
                ));
            }
            else{
                $background = $this->renderView('AmsAdresseBundle:PointLivraison:liste_abonne.html.twig', array(
                    'listeAbonnes' => $listeAbonnesRattache,
                    'tabDoublons' => $doublons,
                    'pointLivraisonId' => $pointLivraisonId,
                    'pointLivraisonAdresse' => $pointLivraisonId,
                    'adresseRnvp' => $adresseRnvp,
                    'idTournee' => $tourneeJourId
                ));
            }

            $response = array("modal" => $modal, 'background' => $background, 'listeAbonnes' => $listeAbonnes, 'message' => $msg);
            $return = json_encode($response);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

        return $this->render('AmsAdresseBundle:PointLivraison:regroupement_abonne.html.twig', array(
                    'listeAbonnes' => $listeAbonnes,
                    'pointLivraisonId' => $pointLivraisonId,
                    'utilisateurId' => $utilisateurId,
                    'adresseRnvp' => $adresseRnvp,
                    'idTournee' => $tourneeJourId,
                    'codeTournee' => $tourneeJourCode,
        ));
    }

    /**
     * recherche par abonne
    */
    public function rechercheAbonneAction(Request $request) {

        $listeAbonnes = array();
        $em = $this->getDoctrine()->getManager();
        
        // Liste des depôts accessible à l'utilisateur
        $depots = $this->get("session")->get("DEPOTS");
        
        $form = $this->createForm(new AbonneSocType($depots));

        // on affiche la liste des abonnées  
        $form->handleRequest($request);
        if ($request->isXmlHttpRequest()) {
            $data = $form->getData();
            $societe = $form->get('societe')->getData() === NULL ? "" : $form->get('societe')->getData()->getId();
            $paramAbonne = array(
               // 'soc_code_ext' => $form->get('societe')->getData() === NULL ? "" : $form->get('societe')->getData()->getCode(),
                'numabo_ext' => $data->getNumaboExt()
            );
            $paramAdresse = array(
                'vol1' => $data->getVol1(),
                'vol2' => $data->getVol2(),
                'vol4' => $form->get('vol4')->getData(),
                'cp' => $form->get('cp')->getData()
            );
            
            $ville = $form->get('ville')->getData();
            $depot = $form->get('depot')->getData();
            
            $listeAbonnes = $em->getRepository('AmsAdresseBundle:Adresse')->rechercheAbonne($paramAdresse, $paramAbonne, $ville, $depot, $societe, "pointLivraison");
            // compteur p:parcours r:remplir a:actuel
//            $r = 0;
//            $a = 0;
//            $tmp = array();
            // $listeAbonnes[$r]['row']   pointLivraisonId
            
            $doublons = array_count_values(array_map(function($abo){  return $abo['pointLivraisonId']; }, $listeAbonnes));

            return $this->render('AmsAdresseBundle:PointLivraison:liste_abonne.html.twig', array(
                        'listeAbonnes' => $listeAbonnes,
                        'type_liste' => "recherche",
                        'tabDoublons' => $doublons,
                        'idTournee' => null
            ));
        }
        return $this->render('AmsAdresseBundle:PointLivraison:recherche_abonne.html.twig', array(
                    'form' => $form->createView(),
                    'listeAbonnes' => $listeAbonnes
        ));
    }

    /**
     * 
     * @param array $listeAbonne 
     * @return type
    */
    public function listeAbonneAction($listeAbonnes = array(), $pointLivraisonId = '', $tourneeId = null, $regroup = 1) {
        $em = $this->getDoctrine()->getManager();
        $adresseRnvp = new AdresseRnvp();
        if ($pointLivraisonId > 0) {
            $adresseRnvp = $em->getRepository('AmsAdresseBundle:AdresseRnvp')->findOneById($pointLivraisonId);
        }
        
        if ($regroup == 0){
            return $this->render('AmsAdresseBundle:PointLivraison:liste_abonne_addr_exist.html.twig', array(
                        'listeAbonnes' => $listeAbonnes,
                        'pointLivraisonId' => $pointLivraisonId,
                        'adresseRnvp' => $adresseRnvp,
                        'idTournee' => $tourneeId
            ));
        }
        else{
            return $this->render('AmsAdresseBundle:PointLivraison:liste_abonne.html.twig', array(
                        'listeAbonnes' => $listeAbonnes,
                        'pointLivraisonId' => $pointLivraisonId,
                        'adresseRnvp' => $adresseRnvp,
                        'idTournee' => $tourneeId
            ));
        }
    }

    public function miseAjourAutreAdress($listeAdresse,$utilisateur,$typeChangement,$pointLivraison,$em) {
        if($listeAdresse){
            foreach ($listeAdresse as $key => $adresse) {
                $newAdresse = clone($adresse);
                $newAdresse->setPointLivraison($pointLivraison);
                $newAdresse->setDateDebut(new \DateTime('tomorrow'));
                $newAdresse->setDateModif(new \DateTime());
                $newAdresse->setUtilisateurModif($utilisateur);
                if (is_object($adresse)) {
                    $adresse->setDateFin(new \DateTime());
                    $adresse->setDateModif(new \DateTime());
                    $adresse->setUtilisateurModif($utilisateur);
                    $adresse->setTypeChangement($typeChangement);
                    $em->persist($adresse);
                }
                $em->persist($newAdresse);
                $em->flush();
            }
        }
        
    }
}
