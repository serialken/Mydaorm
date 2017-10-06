<?php

namespace Ams\SilogBundle\Controller;
use Ams\AdresseBundle\Form\ReparGlobalType;
use Ams\SilogBundle\Controller\GlobalController;
use Ams\SilogBundle\Form\RepartitionType;
use Ams\SilogBundle\Form\ExceptionsType;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DateInterval;
use Ams\SilogBundle\Form\FiltreDepotType;

class RepartitionController extends GlobalController {
    
    public function reparGlobalAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->getDoctrine()->getManager();
        
        $session = $this->get('session');
        $user  = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $depots = $user->getGrpdepot()->getDepots();
        $depotId = $this->getCurentDepot($request, $depots);
        $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneBy(array('id' => $depotId));
       
        $form = $this->createForm(new FiltreDepotType($depot, $depots));
        $this->setDerniere_page();
    
        return $this->render('AmsSilogBundle:Repartition:repar_global.html.twig', array('depot' => $depot,  'form' => $form->createView()));
    }

    /**
     *  Affiche la grid repartition global
     */
    public function gridReparGlobalAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $user  = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $depots = $user->getGrpdepot()->getDepots();
        $depotId = $this->getCurentDepot($request, $depots);
        $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneBy(array('id' => $depotId));
        $repartitions = $em->getRepository('AmsAdresseBundle:ReparGlob')->getCommunesAvecDepotsId($depotId);
        $response = $this->renderView('AmsSilogBundle:Repartition:gridReparGlobal.xml.twig', array(
                'repartitions' => $repartitions
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    /**
     * grid Repartition par societe
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function gridReparSocAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $user  = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $depots = $user->getGrpdepot()->getDepots();
        $depotId = $this->getCurentDepot($request, $depots);
        $repartitions= $em->getRepository('AmsAdresseBundle:ReparSoc')->getRepartitionByDepot($depotId);
        $response = $this->renderView('AmsSilogBundle:Repartition:gridReparSoc.xml.twig', array(
              'repartitions' => $repartitions    
        )); 
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function reparSocAction(Request $request) {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $user  = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $depots = $user->getGrpdepot()->getDepots();
        $depotId = $this->getCurentDepot($request, $depots);
        $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneBy(array('id' => $depotId));
        $form = $this->createForm(new FiltreDepotType($depot, $depots));
        $this->setDerniere_page();
        return $this->render('AmsSilogBundle:Repartition:repar_soc.html.twig', array('depot' => $depot, 'form' => $form->createView()));
    }

    /**
     * grid Repartition par produit
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function gridReparProdAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $user  = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $depots = $user->getGrpdepot()->getDepots();
        $depotId = $this->getCurentDepot($request, $depots);
        $repartitions= $em->getRepository('AmsAdresseBundle:ReparProd')->getRepartitionByDepot($depotId);
        $response = $this->renderView('AmsSilogBundle:Repartition:gridReparProd.xml.twig', array(
              'repartitions' => $repartitions    
        )); 
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function reparProdAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $user  = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $depots = $user->getGrpdepot()->getDepots();
        $depotId = $this->getCurentDepot($request, $depots);
        $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneBy(array('id' => $depotId));
        $form = $this->createForm(new FiltreDepotType($depot, $depots));
        return $this->render('AmsSilogBundle:Repartition:repar_prod.html.twig', array('depot' => $depot, 'form' => $form->createView()));
    }

    /**
     * Ajout repartition societe
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajoutReparSocAction() {
        $em = $this->getDoctrine()->getManager();
        //$session = $this->get('session');
        $alert = false;
        $error = false;
        $depotId = $_GET['param1'];
        $oDepot = $em->getRepository('AmsSilogBundle:Depot')->find($depotId);
        $date_fin = new DateTime($this->container->getParameter('DATE_FIN'));

        $aDpts = $em->getRepository('AmsAdresseBundle:Commune')->listDpt();
        $form = $this->createForm(new ExceptionsType($depotId, $aDpts, $date_fin, 'societe'));
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $modal = $this->renderView('AmsSilogBundle:Repartition:formReparSoc.html.twig', array('form' => $form->createView()));
                //$alert = "<strong>Succés!</strong> Exception société ajoutée";
                $error = false;
                $response = array("modal" => $modal, 'errorTraitement' => $error);
                $return = json_encode($response);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } else {
                $alert = '<strong>Attention!</strong> Une erreur est survenue! ';
                $error = true;
            }
        }

        $modal = $this->renderView('AmsSilogBundle:Repartition:formReparSoc.html.twig', array(
            'form' => $form->createView(),
            'depot' => $oDepot,
           // 'alert' => $alert,
            'errorTraitement' => $error));
        return new Response($modal, 200);
    }

    /**
     * Ajout repartition produit
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajoutReparProdAction() {
        $em = $this->getDoctrine()->getManager();
        //$session = $this->get('session');
        $alert = false;
        $error = false;
        $depotId = $_GET['param1'];
        $oDepot = $em->getRepository('AmsSilogBundle:Depot')->find($depotId);
        $date_fin = new DateTime($this->container->getParameter('DATE_FIN'));

        $aDpts = $em->getRepository('AmsAdresseBundle:Commune')->listDpt();
        $form = $this->createForm(new ExceptionsType($depotId, $aDpts, $date_fin, 'produit'));
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $modal = $this->renderView('AmsSilogBundle:Repartition:formReparProd.html.twig', array('form' => $form->createView()));
                $error = false;
                $response = array("modal" => $modal, 'errorTraitement' => $error);
                $return = json_encode($response);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } else {
                //$alert = '<strong>Attention!</strong> Une erreur est survenue! ';
                $error = true;
            }
        }

        $modal = $this->renderView('AmsSilogBundle:Repartition:formReparProd.html.twig', array(
            'form' => $form->createView(),
            'depot' => $oDepot,
           // 'alert' => $alert,
            'errorTraitement' => $error));
        return new Response($modal, 200);
    }

    
    
     /**
     * Ajout repartition global remplace 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajoutReparGlobalAction() {
        $em = $this->getDoctrine()->getManager();
        $alert = false;
        $error = false;
        $depotId = $_GET['param1'];
        $oDepot = $em->getRepository('AmsSilogBundle:Depot')->find($depotId);
        $date_fin = new DateTime($this->container->getParameter('DATE_FIN'));

        $aDpts = $em->getRepository('AmsAdresseBundle:Commune')->listDpt();
        $form = $this->createForm(new ExceptionsType($depotId, $aDpts, $date_fin, 'global'));
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $modal = $this->renderView('AmsSilogBundle:Repartition:formReparGlobal.html.twig', array('form' => $form->createView()));
              
                $error = false;
                $response = array("modal" => $modal, 'errorTraitement' => $error);
                $return = json_encode($response);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } else {
                $alert = '<strong>Attention!</strong> Une erreur est survenue! ';
                $error = true;
            }
        }

        $modal = $this->renderView('AmsSilogBundle:Repartition:formReparGlobal.html.twig', array(
            'form' => $form->createView(),
            'depot' => $oDepot,
            'errorTraitement' => $error));
        return new Response($modal, 200);
    }

    

    /**
     * Ecran de gestion des exceptions au schéma de répartition global
     */
    public function exceptionsAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');

        $aDpts = $em->getRepository('AmsAdresseBundle:Commune')->listDpt();
        $SelectForm = $this->createForm(new RepartitionType($aDpts, $this->container->get('router')->getContext()->getBaseUrl()));

        $response = $this->renderView('AmsSilogBundle:Repartition:exceptions.html.twig', array(
            'selectForm' => $SelectForm->createView()
        ));
        return new Response($response, 200);
    }

    /**
     * Retourne les informations du schéma de répartition global
     * selon le flux et le département
     * @param int $dpt Le code du département
     * @param int $fid L'ID du flux
     */
    /*public function getGlobalRepartAction($dpt, $fid) {
        $em = $this->getDoctrine()->getManager();

        $zipCode = (int) $dpt;
        $fluxId = (int) $fid;

        $sDate = date("Y-m-d H:i:s");

        $oFlux = $em->getRepository('AmsReferentielBundle:RefFlux')->findOneById($fluxId);
        $aReglesRepart = $em->getRepository('AmsAdresseBundle:ReparGlob')->getRules($zipCode, $fluxId);

        $aReturn = array(
            'repartition' => array(
                'date' => $sDate,
                'flux' => $oFlux->getLibelle(),
                'dpt' => $zipCode,
                'regles' => $aReglesRepart
            )
        );
        return new Response(json_encode($aReturn), 200);
    }*/

    /**
     * Remplissage du select mutiple
     * Action qui liste les insee pour un département donné,
     * répartis entre non-affectés et faisant partie d'une exception
     */
    public function listProdInseesForDptAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $iDpt = (int) $request->query->get('dpt');
        $iProd = (int) $request->query->get('prodId');
        $iFlux = (int) $request->query->get('fluxId');
        $socId = (int) $request->query->get('socId');
        $depotId = (int) $request->query->get('depotId');

        $aExceptions = array();

        /** Exception non existante (produit non renseigné)* */
        if ($iDpt > 0) {
            $aInsees = $em->getRepository('AmsAdresseBundle:ReparSoc')->getInseesForDpt($iDpt);
            $aExceptionsProd = array();
            if ($iProd > 0) {
                //$aExceptions = $em->getRepository('AmsAdresseBundle:ReparProd')->getExceptionsByProd($iDpt, $iProd, $iFlux);
                $aExceptions = $em->getRepository('AmsAdresseBundle:ReparProd')->getExceptionsByProdAndDepot($iDpt, $iProd, $depotId, $iFlux);
                if (!empty($aExceptions)) {
                    foreach ($aExceptions as $aException) {
                        // Comparaison et filtrage
                        foreach ($aInsees as $key => &$aInsee) {
                            if ($aInsee['insee'] == $aException['insee']) {
                                unset($aInsees[$key]);
                                break;
                            }
                        }
                    }
                }
            } elseif ($socId > 0) {
                $aExceptions = $em->getRepository('AmsAdresseBundle:ReparSoc')->getExceptions($iDpt, $socId, $depotId, 0, 1);    // exceptions societe actives  
                if (!empty($aExceptions)) {
                    foreach ($aExceptions as $aException) {
                        // Comparaison et filtrage
                        foreach ($aInsees as $key => &$aInsee) {
                            if ($aInsee['insee'] == $aException['insee']) {
                                unset($aInsees[$key]);
                                break;
                            }
                        }
                    }
                }
            }
            
            print_r($aInsees);

            $aReturn = array(
                'insees_dispos' => $aInsees,
                'exceptions' => $aExceptions
            );

            return new Response(json_encode($aReturn), 200);
        }
        // Mauvais paramètres
        else {
            $aJson = $this->JsonArray(
                    'nok', 'Erreur détectée', 1, 'Un des paramètres obligatoire est vide'
            );
            return new Response($aJson, 200);
        }
    }

    /**
     * Retourne une liste de produits pour une société donnée, au format JSON
     */
    public function listProdForSteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $iSte = $request->query->get('ste');

        if ((int) $iSte <= 0) {
            $aJson = $this->JsonArray(
                    'nok', 'Erreur détectée', 1, 'Mauvais ID de société'
            );

            return new Response($aJson, 200);
        }

        // Récupération des produits de la société
        $aProds = $em->getRepository("AmsProduitBundle:Produit")->getProdsForCompany($iSte);
        $aJson = array('produits' => $aProds);

        $response = new Response(json_encode($aJson));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Retourne une liste de dépôts, au format JSON
     */
    public function listDepotsAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        // Récupération des produits de la société
        $aDepots = $em->getRepository("AmsSilogBundle:Depot")->selectCombo();
        $aJson = array('depots' => $aDepots);
        return new Response(json_encode($aJson), 200);
    }

    /**
     * Retourne une liste de flux, au format JSON
     */
    public function listFluxAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        // Récupération des flux disponibles
        $aFlux = $em->getRepository("AmsReferentielBundle:RefFlux")->selectCombo();
        $aJson = array('flux' => $aFlux);

        return new Response(json_encode($aJson), 200);
    }

    /**
     * Méthode d'enregistrement d'une exception
     */
    public function storeExceptionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $date_fin = new DateTime($this->container->getParameter('DATE_FIN'));

        // Récupération des informations postées
        $data = $request->request->get('ams_exception');
       // $sDpt = $data['dpt'];
        $sType = $data['sType'];
        $iDepotId = $data['depotId'];
        $aInsees = $data['commune'];
        $fluxId = $data['flux'];
        $listInsee = implode(',', $data['commune']);
        $dateDebut = DateTime::createFromFormat('d/m/Y', $data['dateDebut']);
        $dateFinOldExcep = clone($dateDebut);
        $dateFinOldExcep->sub(new DateInterval('P1D'))->format('Y-m-d');
     
        switch ($sType) {
            case 'produit':
                $prodId = $data['produit'];
                $listInsee = implode(',', $aInsees);
                $produit = $em->getRepository('AmsProduitBundle:Produit')->getProduit($prodId);
                $em->getRepository('AmsAdresseBundle:ReparProd')->UpdateExceptions($prodId, $listInsee, $dateFinOldExcep->format('Y-m-d'), $session->get('UTILISATEUR_ID'));
                $em->getRepository('AmsAdresseBundle:ReparProd')->insertExceptions($aInsees, $iDepotId, $prodId, $produit['flux_id'], $session->get('UTILISATEUR_ID'), $dateDebut->format('Y-m-d'), $date_fin->format('Y-m-d'));
                $bOpRet = 1;
                break;                                                                          
            case 'societe':
                $listInsee = implode(',', $aInsees);
                $em->getRepository('AmsAdresseBundle:ReparSoc')->UpdateExceptions($data['societe'], $listInsee, $dateFinOldExcep->format('Y-m-d'), $session->get('UTILISATEUR_ID'));
                $em->getRepository('AmsAdresseBundle:ReparSoc')->insertExceptions($aInsees, $iDepotId, $data['societe'], $data['flux'], $session->get('UTILISATEUR_ID'), $dateDebut->format('Y-m-d'), $date_fin->format('Y-m-d'));
                $bOpRet = 1;
                break;
            
            
             case 'global':
                $listInsee = implode(',', $aInsees);
                $em->getRepository('AmsAdresseBundle:ReparGlob')->UpdateReparGlobal($listInsee, $dateFinOldExcep->format('Y-m-d'), $session->get('UTILISATEUR_ID'));
                $em->getRepository('AmsAdresseBundle:ReparGlob')->insertReparGlob($aInsees,$iDepotId, $fluxId, $session->get('UTILISATEUR_ID'), $dateDebut->format('Y-m-d'), $date_fin->format('Y-m-d'));
                $bOpRet = 1;
                break;
        }

        if ($bOpRet) {
            $sJson = $this->JsonArray('ok', 'Enregistrement effectué avec succès.', '', '', array());
        }

        return new Response($sJson, 200);
    }

    /**
     * Formate le tableau JSON retourné pour les appels AJAX
     * @param mixed $codeRetour Le code de retour de l'opération
     * @param string $msgRetour Le message à afficher à l'utilisateur
     * @param mixed $codeErr Le code d'erreur
     * @param string $msgErr Le message d'erreur à afficher à l'utilisateur
     * @param mixed $datas Tableau de données complémentaires à transmettre
     * @return type
     */
    private function JsonArray($codeRetour, $msgRetour, $codeErr, $msgErr, $datas = null) {
        $jsonArr = array(
            'codeRetour' => $codeRetour,
            'msgRetour' => $msgRetour,
            'codeErr' => $codeErr,
            'msgErr' => $msgErr,
            'datas' => $datas
        );
        return json_encode($jsonArr);
    }


    /**
     * Méthode qui retourne un tableau de valeurs uniques
     * @param array $aInput Le tableau d'entrée
     * @param string $sKey Optionnellement on travaille sur une clé spécifique
     * @return array Le tableau de valeurs uniques
     */
    public static function getUniqValues($aInput, $sKey = NULL) {
        $aUniqVal = array();

        foreach ($aInput as $item) {
            if (is_null($sKey)) {
                if (!in_array($item, $aUniqVal)) {
                    $aUniqVal[] = $item;
                }
            } else {
                if (!in_array($item[$sKey], $aUniqVal)) {
                    $aUniqVal[] = $item[$sKey];
                }
            }
        }

        return $aUniqVal;
    }

    /**
     * Retourne le nom des flux sur la base de leur ID
     * @param array $aFluxIds Un tableau contenant les IDs des flux à récupérer
     * @param EntityManager $em L'Entity Manager
     * @return array $aFluxLabels Le tableau contenant les nom des flux
     */
    public static function getFluxLabels($aFluxIds, $em) 
    {
        $aFluxLabels = array();

        if (!empty($aFluxIds)) {
            foreach ($aFluxIds as $iFluxId) {
                $oFlux = $em->getRepository('AmsReferentielBundle:RefFlux')->findOneById($iFluxId);
                $aFluxLabels[$iFluxId] = $oFlux->getLibelle();
            }
        }
        return $aFluxLabels;
    }
    
    
    private function getCurentDepot(Request $request, $depots) {
       $session = $this->get('session');
       if ($request->getMethod() == 'POST'){
          $depotId = $request->request->get('form_filtre')['depot'];
          $session->set('dptCourant',$depotId);
        }
        else if($session->get('dptCourant') > 0)  {
            $depotId = $session->get('dptCourant');
        } else {
           $depotId = $depots[0]->getId();
        }
        return $depotId;
        
    }
    
 /*  private function getFormFilter($depots) {
       
        $session = $this->get('session');
        $user  = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $depots = $user->getGrpdepot()->getDepots();
        $form = $this->createForm(new FiltreDepotType($depot, $depots));
        
        return $form;
        
    }*/

}
