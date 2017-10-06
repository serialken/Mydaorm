<?php

namespace Ams\DistributionBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ams\DistributionBundle\Entity\PaquetVolume;
use Ams\DistributionBundle\Entity\ResultatDistribution;
use Ams\DistributionBundle\Form\QtesQuotidiennesType;
use Ams\DistributionBundle\Form\ResultatDistributionType;
use Ams\DistributionBundle\Repository\ResultatDistributionRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Cell_DataType;
use PHPExcel_Worksheet_PageSetup;

/**
 * Description of QtesQuotidiennesController
 *
 * @author aandrianiaina
 */
class QtesQuotidiennesController extends GlobalController {

    /**
     * Quantites de produits par jour et par depot
     * 
     * @param \DateTime $date_distrib
     * @ParamConverter("date_distrib", options={"mapping": {"date_distrib": "date_du_jour"}})
     * @return type
     * 
     */
    public function indexAction() {

        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces(); //die('ok');
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $request = $this->getRequest();
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $aErr = array(
            'codeRetour' => null,
            'msgRetour' => null,
            'codeErr' => null,
            'msgErr' => null
        );
        // Les depots auquels l'utilisateur courant a droit
        $depots = $session->get('DEPOTS');
        $depot_ids = array_keys($depots);

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->get('qtesquotidiennes');
            $date_distrib = $this->tarnsformDateToEnFormat($formData['dateDistrib'], '/', '-');
            $passe = $formData['passe'];
            $flux = $formData['flux'];
            //var_dump($flux);
            if ($flux == "") {
                $flux = 0;
            }
        } else {
            $date_distrib = date('Y-m-d');
            $passe = 0;
            $flux = 0;
        }
        //var_dump($flux);exit();
        $date_distrib = new \DateTime($date_distrib);
        $dateCourant = clone $date_distrib;
        $dateCourant->setTime(0, 0, 0); // Suppression des heure, minute & seconde
        $paquetVolumeParProduit = array(); // Volume (nb d'exemplaires)/(conditionnement) d'un paquet de produits
        $repoPaquetVolume = $em->getRepository('AmsDistributionBundle:PaquetVolume');
        $paquetVolume = $repoPaquetVolume->findByDateDistrib($dateCourant);
        foreach ($paquetVolume as $o) {
            $paquetVolumeParProduit[$o->getProduit()->getId()] = $o->getNbExemplaires();
        }

        $repoClientAServirLogist = $em->getRepository('AmsDistributionBundle:ClientAServirLogist');
        //var_dump($flux);exit();
        $qtesProduitsParJour = $repoClientAServirLogist->qtesProduitsParJour($dateCourant, $depot_ids, false, $flux);
        //var_dump($qtesProduitsParJour);exit();
        $produitIds = array();
        foreach ($qtesProduitsParJour as $o) {
            if (!in_array($o["produit"], $produitIds)) {
                $produitIds[] = $o["produit"];
            }
            if (!isset($paquetVolumeParProduit[$o["produit"]])) {
                $paquetVolumeParProduit[$o["produit"]] = 0;
            }
        }

        // Liste des objets "Produit" ordonnances en fonction de "libelle"
        $produits = $em->getRepository('AmsProduitBundle:Produit')->findByIdOrderByLibelle($produitIds);

        $champsForm = array("dateDistrib", "passe", "flux");
        $form = $this->createForm(new QtesQuotidiennesType($champsForm));
        $pathfileExcel = 'tmp/' . md5($session->get('UTILISATEUR_ID'));

        return $this->render('AmsDistributionBundle:QtesQuotidiennes:index.html.twig', array(
                    'date_distrib' => $dateCourant
                    , 'passe' => $passe
                    , 'flux' => $flux
                    , 'nbDepots' => count($depot_ids)
                    , 'form' => $form->createView()
                    , 'produits' => $produits
                    , 'pqtVol' => $paquetVolumeParProduit
                    , 'fileExcel' => $pathfileExcel
        ));
    }

    /**
     * Quantites de produits par jour et par depot - Donnees pour le tableau
     * Passe est le pourcentage en plus de la quantite reelle. On prend le resultat arrondi.
     * 
     * @param \DateTime $date: date distribution
     * @param integer $passe
     * @param integer $pId id produit
     * @param integer $vol_paquet le conditionnement
     * @param integer $refresh  0|1
     * @param string $flux '1'=> nuit, '2' => jour, '' =>non défini
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexDonneesAction(\DateTime $date, $passe, $pId, $vol_paquet, $refresh, $flux) {

        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $depots = $this->getDepotOrderByOrdre($utilisateur);

        $depot_ids = array_keys($depots);
        $dateCourant = clone $date;
        $dateCourant->setTime(0, 0, 0); // Suppression des heure, minute & seconde

        $repoPaquetVolume = $em->getRepository('AmsDistributionBundle:PaquetVolume');
        $repoProduit = $em->getRepository('AmsProduitBundle:Produit');

        // On enregistre le volume(conditionnement) si le produit est defini
        if ($pId > 0) {
            $produit = $repoProduit->find($pId);
            $paquetVolumeASupprimer = $repoPaquetVolume->findOneBy(array('dateDistrib' => $dateCourant, 'produit' => $produit));
            if ($paquetVolumeASupprimer) {
                $em->remove($paquetVolumeASupprimer);
            }

            $nouvPaquetVolume = new PaquetVolume();
            $nouvPaquetVolume->setDateDistrib($dateCourant);
            $nouvPaquetVolume->setNbExemplaires($vol_paquet);
            $nouvPaquetVolume->setProduit($produit);
            $nouvPaquetVolume->setDateModif(new \DateTime());
            $nouvPaquetVolume->setUtilisateur($utilisateur);
            $em->persist($nouvPaquetVolume);
            $em->flush();
        }


        $paquetVolumeParProduit = array(); // Volume (nb d'exemplaires/conditionnement) d'un paquet de produits
        $paquetVolume = $repoPaquetVolume->findByDateDistrib($dateCourant);
        foreach ($paquetVolume as $o) {
            $paquetVolumeParProduit[$o->getProduit()->getId()] = $o->getNbExemplaires();
        }

        $repoClientAServirLogist = $em->getRepository('AmsDistributionBundle:ClientAServirLogist');
        $qtesProduitsParJour = $repoClientAServirLogist->qtesProduitsParJour($dateCourant, $depot_ids, false, $flux);

        $produitIds = array();
        $qtesProduitsParDepot = array();
        $passeProduit = array();
        foreach ($qtesProduitsParJour as $o) {
            //var_dump($o);exit();
            if (!in_array($o["produit"], $produitIds)) {
                $produitIds[] = $o["produit"];
            }

            // Si on sort les quantites avec la passe
            if ($passe == 1) {
                if (!isset($passeProduit[$o["produit"]])) {
                    $passeProduit[$o["produit"]] = $repoProduit->find($o["produit"])->getPasse();
                }
                $o["qte"] = $o["qte"] + round(($o["qte"] * $passeProduit[$o["produit"]]) / 100);
            }

            if (!isset($paquetVolumeParProduit[$o["produit"]]) || $paquetVolumeParProduit[$o["produit"]] == 0) {
                $qtesProduitsParDepot[$o["depot"]][$o["produit"]]['pqt'] = $o["qte"];
                $qtesProduitsParDepot[$o["depot"]][$o["produit"]]['appt'] = 0;
                $paquetVolumeParProduit[$o["produit"]] = 0;
            } else {
                $qtesProduitsParDepot[$o["depot"]][$o["produit"]]['pqt'] = floor($o["qte"] / $paquetVolumeParProduit[$o["produit"]]);
                $qtesProduitsParDepot[$o["depot"]][$o["produit"]]['appt'] = ($o["qte"] % $paquetVolumeParProduit[$o["produit"]]);
            }
        }

        // Liste des objets "Produit" ordonnances en fonction de "libelle"
        $produits = $em->getRepository('AmsProduitBundle:Produit')->findByIdOrderByLibelle($produitIds);


        $aDataXls = array(
            'depots' => $depots,
            'qtesProduitsParDepot' => $qtesProduitsParDepot,
            'pqtVol' => $paquetVolumeParProduit,
            'passe' => $passe,
            'date_distrib' => $dateCourant
        );
        $session->set('dataXls', $aDataXls);
        $response = $this->renderView('AmsDistributionBundle:QtesQuotidiennes:grid.xml.twig', array(
            'date_distrib' => $dateCourant
            , 'depots' => $depots
            , 'flux' => $flux
            , 'passe' => $passe
            , 'produits' => $produits
            , 'qtesProduitsParDepot' => $qtesProduitsParDepot
            , 'pqtVol' => $paquetVolumeParProduit
            , 'refresh' => $refresh
        ));
        return new Response($response, 200, array('Content-Type' => 'text/xml'));
    }

    public function generationXlsAction(Request $request) {
        $session = $request->getSession();
        $dataXls = $session->get('dataXls');
        $em = $this->getDoctrine()->getManager();
        $produits = $em->getRepository('AmsProduitBundle:Produit')->findByIdOrderByLibelle($request->get('products'));
        $this->createMainXLS($dataXls['depots'], $produits, $dataXls['qtesProduitsParDepot'], $dataXls['pqtVol'], $dataXls['passe'], $dataXls['date_distrib']);
        exit;
    }

    /**
     * [qteParTourneePourUnDepotAction description]
     * @return [type] [description]
     */
    public function qteParTourneePourUnDepotAction() {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {

            $request->request->get('qtesquotidiennes');
            $formData = $request->request->get('qtesquotidiennes');
            $date_distrib = $this->tarnsformDateToEnFormat($formData['dateDistrib'], '/', '-');
            $depot_id = $formData['depot_id'];
            $flux = $formData['flux'];
            if ($flux == "") {
                $flux = 0;
            }
        } else {

            $date_distrib = $request->query->get('date');
            $depot_id = $request->query->get('dId');
            $flux = $request->query->get('flux');
            //var_dump($flux);exit();
        }
        $date_distrib = new \DateTime($date_distrib);
        // Les depots auquels l'utilisateur courant a droit
        $depots = $this->get('session')->get('DEPOTS');
        if (!isset($depots[$depot_id])) {
            $this->srv_session->getFlashBag()->add(
                    'notice', "Le dépôt identifiant par " . '"' . $depot_id . '"' . " ne fait pas partie de ceux auxquels vous avez droit."
            );
            return $this->redirect($this->generateUrl('_ams_messages'));
        }

        $dateCourant = clone $date_distrib;
        $dateCourant->setTime(0, 0, 0); // Suppression des heure, minute & seconde

        $em = $this->getDoctrine()->getManager();

        $repoClientAServirLogist = $em->getRepository('AmsDistributionBundle:ClientAServirLogist');
        $qtesProduits = $repoClientAServirLogist->qtesProduitsParJourParTournee($dateCourant, $depot_id, $flux);

        $produitIds = array();
        $tourneeIds = array();
        foreach ($qtesProduits as $o) {
            if (!in_array($o["produit"], $produitIds)) {
                $produitIds[] = $o["produit"];
            }
            if ($o["tournee"] == 0 && !isset($tourneeIds[0])) {
                $tourneeIds[0] = 'Non classés';
            }
        }

        $nbTournees = count($tourneeIds);

        // Liste des objets "Produit" ordonnances en fonction de "libelle"
        $produits = $em->getRepository('AmsProduitBundle:Produit')->findByIdOrderByLibelle($produitIds);
        $session = new Session();
        $session->set('aProduitIds', $produitIds);


        $champsForm = array("dateDistrib", "depot_id", "flux");
        $form = $this->createForm(new QtesQuotidiennesType($champsForm, $depots));

        //$em->getRepository('AmsProduitBundle:Produit')->findByIdOrderByLibelle($produitIds);
        //echo "<pre>";print_r($produitIds);echo "</pre>";
        //echo "<pre>";print_r($tourneeIds);echo "</pre>";
        return $this->render('AmsDistributionBundle:QtesQuotidiennes:qtes_quotidiennes_depot.html.twig', array(
                    'date_distrib' => $dateCourant
                    , 'depot_id' => $depot_id
                    , 'form' => $form->createView()
                    , 'produits' => $produits
                    , 'flux' => $flux
                    , 'nbTournees' => $nbTournees
        ));
    }

    /**
     * Liste des quantites regroupes par produit et par tournee
     * pour un depot, flux et une date de distrib 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function qteParTourneePourUnDepotDonneesAction(Request $request) {

        $date_distrib = $this->tarnsformDateToEnFormat($request->get('date'), '-', '-');
        $flux_id = $request->get('flux');
        $depot_id = $request->get('dId');
        $em = $this->getDoctrine()->getManager();
        $qtesProduits = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getQteProduits($depot_id, $flux_id, $date_distrib);

        $listeProduit = array();
        $listeTournee = array();
        $qtesProduitsParTournee = array();
        foreach ($qtesProduits as $qtesProduit) {
            $listeProduit[$qtesProduit['prd_id']]['id'] = $qtesProduit['prd_id'];
            $listeProduit[$qtesProduit['prd_id']]['libelle'] = $qtesProduit['prd_libelle'];
            $listeProduit[$qtesProduit['prd_id']]['image'] = $this->container->getParameter("WEB_FILES_ROOT_DIR") . 'uploads/documents/' . $qtesProduit['img_path'];
            $listeTournee[$qtesProduit['mtj_id']] = $qtesProduit['mtj_code'];
            $qtesProduitsParTournee[$qtesProduit['mtj_id']][$qtesProduit['prd_id']] = $qtesProduit['qte'];
        }

        $response = $this->renderView('AmsDistributionBundle:QtesQuotidiennes:qtes_quotidiennes_depot_grid.xml.twig', array(
            'tournees' => $listeTournee,
            'produits' => $listeProduit,
            'qtesProduitsParTournee' => $qtesProduitsParTournee,
            'depot_id' => $depot_id,
            'flux_id' => $flux_id,
            'date_distrib' => $date_distrib,
        ));
        return new Response($response, 200, array('Content-Type' => 'text/xml'));
    }

    /**
     * [qteParTourneePourUnDepotDonneesAction description]
     * @param  [type] $dId  [depot id]
     * @param  [type] $date [date distribution]
     * @return [type]       [description]
     */
    /* public function qteParTourneePourUnDepotDonneesAction($dId = null, \DateTime $date = null, $flux = 0)
      {
      // Les depots auquels l'utilisateur courant a droit
      $depots = $this->get('session')->get('DEPOTS');

      $dateCourant    = clone $date;
      $dateCourant->setTime(0, 0, 0); // Suppression des heure, minute & seconde

      $em = $this->getDoctrine()->getManager();

      $repoClientAServirLogist = $em->getRepository('AmsDistributionBundle:ClientAServirLogist');
      $qtesProduits    = $repoClientAServirLogist->qtesProduitsParJourParTournee($dateCourant, $dId, $flux);

      $session = new Session();
      $produitIds = $session->get('aProduitIds');
      $tournees    = array();
      $qtesProduitsParTournee    = array();

      foreach($qtesProduits as $o)
      {

      if($o["tournee"]==0 && !isset($tournees[0]))
      {
      $tournees[0]   = '<a target=_blank href="'.$this->generateUrl('qtes_quotidiennes_classify').'?depot='.$dId.'&date='.$dateCourant->format('Y-m-d').'&flux='.$flux.'">Non classés</a>';
      } else {
      $tournee_trouvee = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findOneById( $o["tournee"]);
      if ($tournee_trouvee) {
      $tournees[$o["tournee"]] = $tournee_trouvee->getCode();
      }
      }
      $qtesProduitsParTournee[$o["tournee"]][$o["produit"]] = $o["qte"];
      }

      // Liste des objets "Produit" ordonnances en fonction de "libelle"
      $produits   = $em->getRepository('AmsProduitBundle:Produit')->findByIdOrderByLibelle($produitIds);

      //        var_dump("******************************prod************************");
      //        var_dump($produits);
      //        var_dump("******************************tourn************************");
      //        var_dump($dateCourant);
      //        exit();
      $response = $this->renderView('AmsDistributionBundle:QtesQuotidiennes:qtes_quotidiennes_depot_grid.xml.twig', array(
      'date_distrib'  => $dateCourant
      , 'depot_id'    => $dId
      , 'tournees'    => $tournees
      , 'produits'    => $produits
      , 'flux'        => $flux
      , 'qtesProduitsParTournee' => $qtesProduitsParTournee
      ));
      return new Response($response, 200, array('Content-Type' => 'text/xml'));
      } */


    public function listeClientsParCDParProduitAction() {
        // verifie si on a droit a acceder a cette page
        /*
          $bVerifAcces = $this->verif_acces();
          if ($bVerifAcces !== true) {
          return $bVerifAcces;
          }
         */
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $request->request->get('qtesquotidiennes');
            $formData = $request->request->get('qtesquotidiennes');
            $date_distrib = $this->tarnsformDateToEnFormat($formData['dateDistrib'], '/', '-');
            $depot_id = $formData['depot_id'];
            $produit_id = $formData['produit_id'];
            $flux = $formData['flux'];
            //var_dump($flux);exit();
            if ($flux == "") {
                $flux = 0;
            }
        } else {
            $date_distrib = $request->query->get('date');
            $depot_id = $request->query->get('dId');
            $produit_id = $request->query->get('pId');
            $flux = $request->query->get('flux');
        }


        $depots = $this->get('session')->get('DEPOTS');
        $date_distrib = new \DateTime($date_distrib);
        $dateCourant = clone $date_distrib;
        $dateCourant->setTime(0, 0, 0); // Suppression des heure, minute & seconde

        $em = $this->getDoctrine()->getManager();

        $repoClientAServirLogist = $em->getRepository('AmsDistributionBundle:ClientAServirLogist');

        $qtesProduits = $repoClientAServirLogist->qtesProduitsParJourParTournee($dateCourant, $depot_id, $flux);

        $produitIds = array();
        foreach ($qtesProduits as $o) {
            if (!in_array($o["produit"], $produitIds)) {
                $produitIds[] = $o["produit"];
            }
        }

        // Liste des objets "Produit" ordonnances en fonction de "libelle"
        $produits = $em->getRepository('AmsProduitBundle:Produit')->findByIdOrderByLibelle($produitIds);
        $produitsSelecteur = array();
        foreach ($produits as $p) {
            $produitsSelecteur[$p->getId()] = $p->getLibelle();
        }

        $champsForm = array("dateDistrib", "depot_id", "produit_id", "flux");
        $form = $this->createForm(new QtesQuotidiennesType($champsForm, $depots, $produitsSelecteur));

        return $this->render('AmsDistributionBundle:QtesQuotidiennes:liste_clients.html.twig', array(
                    'date_distrib' => $dateCourant
                    , 'depot_id' => $depot_id
                    , 'produit_id' => $produit_id
                    , 'flux' => $flux
                    , 'form' => $form->createView()
        ));
    }

    /**
     * [listeClientsParCDParProduitDonneesAction description]
     * @param  [type]   $dId    [depot id]
     * @param  [type]   $pId    [produit id]
     * @param  DateTime $date   [date distribution]
     * @param  [type]   $export [flag pour exporter ou non ]
     * @return [type]           [description]
     */
    public function listeClientsParCDParProduitDonneesAction($dId, $pId, \DateTime $date, $flux = 0, $export = null) {

        $dateCourant = clone $date;
        $dateCourant->setTime(0, 0, 0);
        $em = $this->getDoctrine()->getManager();
        $listeClients = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->listeClientsParCDParProduit($dateCourant, $dId, $pId, $flux);

        if (!is_null($export)) {
            $tmp = $listeClients;
            $aHeaderCsv = array('vol1' => 'Vol 1', 'vol 2' => 'Vol 2', 'qte' => 'Quantité', 'numaboExt' => 'N° client', 'vol3' => 'Vol 3', 'vol4' => 'Vol 4',
                'vol5' => 'Vol 5', 'cp' => 'CP', 'ville' => 'Ville', 'tournee' => 'Tournee', 'produit_libelle' => 'Produit', 'societe_libelle' => 'Societe');
            $this->dumpTmpFile();
            array_unshift($tmp, $aHeaderCsv);
            $file = 'file_' . $dateCourant->format('d-m-Y') . '_' . $dId . '_' . $pId . '.csv';
            $fp = fopen('tmp/' . $file, 'w+');
            foreach ($tmp as $fields) {
                fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($fp, $fields, ';');
            }
            rewind($fp);
            $content = file_get_contents('tmp/' . $file);
            fclose($fp);
            $response = new Response($content);
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', "application/csv;charset=UTF-8");
            $response->headers->set('Content-Disposition', sprintf('attachment;filename="%s"', $file));
            $response->setCharset('UTF-8');

            return $response;
        }

        $response = $this->renderView('AmsDistributionBundle:QtesQuotidiennes:liste_clients_grid.xml.twig', array(
            'date_distrib' => $dateCourant
            , 'depot_id' => $dId
            , 'flux' => $flux
            , 'listeClients' => $listeClients
        ));

        return new Response($response, 200, array('Content-Type' => 'text/xml'));
    }

    private function insertResultatDistribution($resultats, $parametres) {
        //Appel de la base de données
        $em = $this->getDoctrine()->getManager();
        $resultats->setProduit($parametres['produit_id']);

        //Récupération de la valeur du passe (en %)
        $resultats->setPasseValue($parametres['produit_id']->getPasse());

        $resultats->setDepot($parametres['depot']);
        $resultats->setNbPaquet($parametres['nb_paquet']);
        $resultats->setNbAppoint($parametres['nb_appoint']);
        $resultats->setConditionnement($parametres['conditionnement']);
        $resultats->setDateDistribution($parametres['date_distrib']);
        $resultats->setPasse($parametres['passe']);


        //Sauvegarde dans la base de données
        $em->persist($resultats);
        $em->flush();
    }

    public function enregistrementClientAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            //Récupération des données envoyés dans l'ajax
            $parametres = $request->request->all();

            //Appel de la base de données
            $em = $this->getDoctrine()->getManager();

            //Préparation de la date pour envoi dans la base
            $date_modif = explode('/', $parametres['date_distrib']);
            $parametres['date_distrib'] = new \DateTime($date_modif['2'] . '-' . $date_modif['1'] . '-' . $date_modif['0']);

            //Récupération de l'objet en fonction du nom
            $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneByLibelle($parametres['depot']);
            $parametres['depot'] = $depot;
            $depot_id = $depot->getId();

            //Récupération de l'objet Dépot en fonction de l'id
            $parametres['produit_id'] = $em->getRepository('AmsProduitBundle:Produit')->findOneById($parametres['produit_id']);
            $produit_id = $parametres['produit_id']->getId();

            $r = $em->getRepository('AmsDistributionBundle:ResultatDistribution')
                    ->checkEnregistrements($produit_id, $depot_id, $parametres['date_distrib']);

            if (empty($r)) {
                //Création de l'entité
                $resultats = new ResultatDistribution();
                $this->insertResultatDistribution($resultats, $parametres);

                $type_of_check = 'create';
            } else {
                //Update de l'entité
                if ($r[0]['conditionnement'] != $parametres['conditionnement']) {
                    $resultats = $em->getRepository('AmsDistributionBundle:ResultatDistribution')->find($r[0]['id']);
                    $this->insertResultatDistribution($resultats, $parametres);

                    $type_of_check = 'update';
                } else {
                    $type_of_check = 'rien';
                }
                //$type_of_check = $parametres;
            }

            //$r['type_check'] = $type_of_check;
            return new Response(json_encode($type_of_check), 200, array('Content-Type' => 'Application/json'));
        }
    }

    public function classifyClientAction(Request $request) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $date = $request->query->get('date');
        $depot = $request->query->get('depot');
        $flux = $request->query->get('flux');

        if ($request->getMethod() == 'POST') {
            $tournee = $request->request->get('tournee');
            $casl_id = $request->request->get('casl_id');           
            if($tournee == "" && $casl_id != ""){
                $tournee = $this->rechercheTourneeJour($casl_id, $em);
            }else
            {
              $this->abonneeAffectTournee($em,$casl_id,$tournee,$date);
            }
            if ($tournee) {
                $oCasl = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->find($casl_id);
                $aCoordinatePoint = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getCoordinateByIdCasl($casl_id);
                if ($aCoordinatePoint['geox'] == '' || $aCoordinatePoint['geox'] == '') {
                    $this->get('session')->getFlashBag()->add(
                            'error_geocodage', 'Veuillez Geocoder cette adresse, avant de l\'affecter à une tournée'
                    );
                    return $this->redirect($request->headers->get('referer'));
                }
                $gpsCoordinate = array('latitude' => $aCoordinatePoint['geoy'], 'longitude' => $aCoordinatePoint['geox']);
                $aCritere = array('rayon_max' => 10, 'nb_pts_proches_max' => 10, 'tournee' => $tournee, 'ordre' => true);
                $aCoordinateTourneeDetail = $em->getRepository('AmsAdresseBundle:TourneeDetail')->ptsCandidatsProches($gpsCoordinate, $aCritere);
                if (count($aCoordinateTourneeDetail) == 0) {
                    $gpsCoordinate = array('latitude' => $aCoordinatePoint['geoy'], 'longitude' => $aCoordinatePoint['geox']);
                    $aCritere = array('rayon_max' => 20, 'nb_pts_proches_max' => 10, 'tournee' => $tournee, 'ordre' => true);
                    $aCoordinateTourneeDetail = $em->getRepository('AmsAdresseBundle:TourneeDetail')->ptsCandidatsProches($gpsCoordinate, $aCritere);
                }
                if (count($aCoordinateTourneeDetail) == 0) {
                    $gpsCoordinate = array('latitude' => $aCoordinatePoint['geoy'], 'longitude' => $aCoordinatePoint['geox']);
                    $aCritere = array('rayon_max' => 30, 'nb_pts_proches_max' => 10, 'tournee' => $tournee, 'ordre' => true);
                    $aCoordinateTourneeDetail = $em->getRepository('AmsAdresseBundle:TourneeDetail')->ptsCandidatsProches($gpsCoordinate, $aCritere);
                }
                if (count($aCoordinateTourneeDetail) == 0) {
                    $sErrCalculMsg = 'PB de calcul de l\'ordre dans la commande de réintégration ';
                    $this->get('session')->getFlashBag()->add('notice', $sErrCalculMsg);
                    return $this->redirect($request->headers->get('referer'));
                }
                $aModeleTourneeJour = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findByCode($tournee);
                $jourId = $aModeleTourneeJour['jour_id'];
                $abonneSocId = $oCasl->getAbonneSoc()->getId();


                /**
                 * VERIFICATION SI LE POINT DE LIVRAISON EST DEJA PRESENT DANS LA TOURNEE
                 * SI OUI ON RECUPERE LES DONNEES SINON ON EXECUTE LE SEARCHAROUND POUR DEFINIR LE 
                 * POINT LE PLUS PROCHE
                 * */
                $longVerif = $this->in_array_r($aCoordinatePoint['geox'], $aCoordinateTourneeDetail);
                $latVerif = $this->in_array_r($aCoordinatePoint['geoy'], $aCoordinateTourneeDetail);
                $bSameOrder = false;
                $equals = true;
                if ($longVerif && $latVerif) {
                    $key = $this->in_array_r($longVerif, $aCoordinateTourneeDetail, false, true);
                    $iTourneeDetailOrdre = $aCoordinateTourneeDetail[$key]['ordre'];
                    /** ID TOURNEE_DETAIL DU POINT LE PLUS PROCHE * */
                    $itourneeDetailIdNearest = $aCoordinateTourneeDetail[$key]['id'];
                    $bSameOrder = true;
                } else {
                    $aCoordinatePoint['id'] = 0;
                    $aCoordinatePoint['x'] = $aCoordinatePoint['geox'];
                    $aCoordinatePoint['y'] = $aCoordinatePoint['geoy'];
                    $aInsertPoint = array('X' => $aCoordinatePoint['x'], 'Y' => $aCoordinatePoint['y']);
                    $optionsArr = array('Projection' => 'WGS84');
                    $geoservice = $this->container->get('ams_carto.geoservice');
                    $classement = $geoservice->callSearchAround($aCoordinatePoint, $aCoordinateTourneeDetail, $optionsArr);

                    /** TROUVER LE POINT LE PLUS PROCHE * */
                    if ($classement != null) {
                        if (count($classement->SearchAroundResult) == 1)
                            $PointMin = $classement->SearchAroundResult;
                        else {
                            $PointMin = 0;
                            foreach ($classement->SearchAroundResult as $index => $point) {
                                if (!$index) {
                                    $PointMin = $point;
                                } else {
                                    if ($PointMin->Time > $point->Time)
                                        $PointMin = $point;
                                }
                            }
                        }
                    }
                    /** RECUPERATION DE L'ORDRE LE PLUS PROCHE * */
                    $aNearCoordinate = $this->in_array_r($PointMin->Id, $aCoordinateTourneeDetail);
                    $ordre = $aNearCoordinate['ordre'];
                    /** RECUPERATION DE LA CLE DU TABLEAU DU POINT LE PLUS PROCHE* */
                    $iNearKey = $this->in_array_r($PointMin->Id, $aCoordinateTourneeDetail, false, true);
                    if ($iNearKey)
                        $aBeforeCible = $aCoordinateTourneeDetail[$iNearKey - 1];
                    $aPointCible = $aCoordinateTourneeDetail[$iNearKey];
                    /** VERIFICATION SI LE POINT N'EST PAS LE DERNIER * */
                    $lastPoint = true;
                    if (count($aCoordinateTourneeDetail) > ($iNearKey + 1)) {
                        $lastPoint = false;
                        $aPointAfterCible = $aCoordinateTourneeDetail[$iNearKey + 1];
                    }
                    $aFirstRoad = $aSecondRoad = array();
                    /** 1ER ROUTE  * */
                    if ($iNearKey)
                        $aFirstRoad[] = array('X' => $aBeforeCible['x'], 'Y' => $aBeforeCible['y']);
                    $aFirstRoad[] = $aInsertPoint;
                    $aFirstRoad[] = array('X' => $aPointCible['x'], 'Y' => $aPointCible['y']);
                    if (!$lastPoint)
                        $aFirstRoad[] = array('X' => $aPointAfterCible['x'], 'Y' => $aPointAfterCible['y']);
                    /** 2EME ROUTE * */
                    if ($iNearKey)
                        $aSecondRoad[] = array('X' => $aBeforeCible['x'], 'Y' => $aBeforeCible['y']);
                    $aSecondRoad[] = array('X' => $aPointCible['x'], 'Y' => $aPointCible['y']);
                    $aSecondRoad[] = $aInsertPoint;
                    if (!$lastPoint)
                        $aSecondRoad[] = array('X' => $aPointAfterCible['x'], 'Y' => $aPointAfterCible['y']);

                    /** CALCUL DES ROUTES VIA LE WEB SERVICE "ROUTE SERVICE" * */
                    $routeService_1 = $geoservice->callRouteService(TRUE, FALSE, $aFirstRoad);
                    $routeService_2 = $geoservice->callRouteService(TRUE, FALSE, $aSecondRoad);
                    /** RECUPERATION DE L'ORDRE DU POINT A INSERER * */
                    $iTourneeDetailOrdre = ($routeService_1->ROUTE->Time <= $routeService_2->ROUTE->Time) ? intval($ordre) : $ordre + 1;
                    $equals = ($routeService_1->ROUTE->Time <= $routeService_2->ROUTE->Time) ? true : false;
                    /** ID TOURNEE_DETAIL DU POINT LE PLUS PROCHE * */
                    $itourneeDetailIdNearest = $PointMin->Id;
                }

                /** RECUPERATION ORDRE DU POINT LE PLUS PROCHE DANS CASL* */
                $oCaslOrder = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getCaslOrdreByIdTourneeDetail($itourneeDetailIdNearest, false);
                if ($oCaslOrder) {
                    $icaslOrdre = ($equals) ? $oCaslOrder['point_livraison_ordre'] : $oCaslOrder['point_livraison_ordre'] + 1;
                    /** VERIFICATION QUE L'ABONNER N'EST PAS DEJA INSERER DANS TOURNEE DETAIL * */
                    $abonneExist = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getAbonneJourId($abonneSocId, $jourId);
                    if (!$abonneExist)
                        $em->getRepository('AmsAdresseBundle:TourneeDetail')->caslUpdateClassify($casl_id, $tournee, $iTourneeDetailOrdre, $icaslOrdre, $bSameOrder);
                }
            }
            return $this->redirect($request->headers->get('referer'));
        }

        if (!$date || !$depot) {
            return $this->redirect($this->generateUrl('qtes_quotidiennes_index'));
        }
        $query = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getDataCaslByDepotDate($date, $depot, $flux);
        $aTourneeByDepot = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getTourneeJourByDateDepot($date, $depot, $flux);
        $select = '<select name=tournee> <option value="" selected="selected"></option>';
        foreach ($aTourneeByDepot as $row) {
            $select.='<option value="' . $row['code'] . '">' . $row['code'] . '</option>';
        }
        $select.= '</select>';

        return $this->render('AmsDistributionBundle:QtesQuotidiennes:classify_1.html.twig', array(
                    'casl' => $query,
                    'listTournees' => $select,
                    'date' => $date,
                    'depot' => $depot,
                    'flux' => $flux,
        ));
    }

    public function classifyXmlAction(Request $request) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $date = $request->get('date');
        $depot = $request->get('depot');
        $flux = $request->get('flux');
        $query = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getDataCaslByDepotDate($date, $depot, $flux);
        $aTourneeByDepot = $em->getRepository('AmsAdresseBundle:TourneeDetail')->getTourneeJourByDateDepot($date, $depot, $flux);

        $select = '';
        foreach ($aTourneeByDepot as $row)
            $select.='<option value="' . $row['code'] . '">' . $row['code'] . '</option>';


        $response = $this->renderView('AmsDistributionBundle:QtesQuotidiennes:classify_grid.xml.twig', array(
            'classify' => $query,
            'tournee' => $select,
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    function in_array_r($needle, $haystack, $strict = false, $have_key = false) {
        foreach ($haystack as $key => $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                if ($have_key)
                    return $key;
                return $item;
            }
        }
        return false;
    }

    /** EFFACE LES FICHIERS SUPERIEUR A 1 HEURE* */
    function dumpTmpFile() {

        if ($handle = opendir('tmp')) {
            while (false !== ($file = readdir($handle))) {
                if (!preg_match('/^file*/', $file))
                    continue;
                if ((time() - filemtime('tmp/' . $file)) > 3600)
                    unlink('tmp/' . $file);
            }
        }
        closedir($handle);
    }

    /**
     * [tarnsformDateToEnFormat description]
     * @param  [type] $date     [description]
     * @param  [type] $oldDelim [description]
     * @param  [type] $newDelim [description]
     * @return [type]           [description]
     */
    public function tarnsformDateToEnFormat($date, $oldDelim, $newDelim) {
        $dateItems = explode($oldDelim, $date);
        return $dateItems[2] . $newDelim . $dateItems[1] . $newDelim . $dateItems[0];
    }

    private function getDepotOrderByOrdre($oUtilisateur) {
        $em = $this->getDoctrine()->getManager();
        $grpDepot = $em->getRepository('AmsSilogBundle:GroupeDepot')->getGroupeAvecDepot($oUtilisateur->getGrpdepot()->getId(), null, 'd.ordre');
        $liste_depots = array();
        $depotOrderNull = array();
        foreach ($grpDepot->getDepots() as $depot) {
            if (!$depot->getOrdre())
                $depotOrderNull[$depot->getId()] = $depot->getLibelle();
            else
                $liste_depots[$depot->getId()] = $depot->getLibelle();
        }
        return $liste_depots + $depotOrderNull;
    }

    private function createMainXls($depots, $produits, $qtesProduitsParDepot, $paquetVolumeParProduit, $passe, $dateCourant) {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        // FIRST TABS
        $this->initXls($phpExcelObject, 0, 'Quantités quotidiennes', $passe, $dateCourant);
        $this->constructXlsBySection($phpExcelObject, $depots, $produits, $qtesProduitsParDepot, 0, $paquetVolumeParProduit);
        // SECOND TABS 
        $this->initXls($phpExcelObject, 1, 'Taille standard', $passe, $dateCourant);
        $this->constructXlsBySection($phpExcelObject, $depots, $produits, $qtesProduitsParDepot, 1);
        // THIRD TABS 
        $this->initXls($phpExcelObject, 2, 'Quantités d\'appoint', $passe, $dateCourant);
        $this->constructXlsBySection($phpExcelObject, $depots, $produits, $qtesProduitsParDepot, 2);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        $session = new Session();
        $path = 'tmp/' . md5($session->get('UTILISATEUR_ID'));
        $this->isDirRecursive($path);
        $writer->save($path . '/quantite_quotidienne.xls');
    }

    private function setStyleXls($phpExcelObject, $beginLine, $nbLines, $iTabs) {
        $phpExcelObject->setActiveSheetIndex($iTabs);
        $maxLetter = ($iTabs == 0) ? 'M' : 'G';
        $endLine = $beginLine + ($nbLines + 3);
        for ($i = $beginLine; $i < $endLine; $i++) {
            $phpExcelObject->getActiveSheet()->getStyle("A$i:" . $maxLetter . $i)->applyFromArray($this->getStyle('styleArray'));
            if (($i + 1) % 2 == 0) {
                $phpExcelObject->getActiveSheet()->getStyle("A$i:" . $maxLetter . $i)->applyFromArray($this->getStyle('styleOddRows'));
            } else {
                $phpExcelObject->getActiveSheet()->getStyle("A$i:" . $maxLetter . $i)->applyFromArray($this->getStyle('styleEventRows'));
            }
        }
        if ($iTabs == 0) {
            $phpExcelObject->getActiveSheet()
                    ->getStyle("A" . ($beginLine + 1) . ":" . $maxLetter . ($beginLine + 1))
                    ->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '#FAF3CF'))));
        }
    }

    private function initXls($phpExcelObject, $iTabs, $title, $passe, $dateCourant) {
        $maxLetter = ($iTabs == 0) ? 'M' : 'G';
        $sPasse = ($passe == 0) ? "Sans Passe" : "Avec Passe";
        $phpExcelObject->createSheet($iTabs);
        $phpExcelObject->setActiveSheetIndex($iTabs);
        $phpExcelObject->getActiveSheet()->setTitle($title);
        $phpExcelObject->setActiveSheetIndex($iTabs)->mergeCells("A1:" . $maxLetter . "1")
                ->setCellValue("A1", $sPasse . " " . $dateCourant->format('d/m/Y'));
    }

    private function paramXls($phpExcelObject, $iTabs) {
        $maxLetter = ($iTabs == 0) ? 'M' : 'G';
        $width = ($iTabs == 0) ? 8 : 15;
        $phpExcelObject->getActiveSheet()->getDefaultColumnDimension()->setWidth($width);
        $phpExcelObject->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $phpExcelObject->getActiveSheet()->getStyle("A1:" . $maxLetter . "250")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcelObject->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $phpExcelObject->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    }

    private function setHeaderColumn($phpExcelObject, $row, $iTabs) {
        $maxLetter = ($iTabs == 0) ? 'M' : 'G';
        $phpExcelObject->getActiveSheet()->getRowDimension($row)->setRowHeight(40);
        $phpExcelObject->getActiveSheet()->getStyle("B" . $row . ":" . $maxLetter . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $phpExcelObject->getActiveSheet()->getStyle("B" . $row . ":" . $maxLetter . $row)->getAlignment()->setWrapText(true);
    }

    private function mainXlsLeftColumn($phpExcelObject, $depots, $iTabs, $index) {
        $titleCell = "A" . ($index - 2);
        $depotCell = "A" . ($index - 1);
        if ($iTabs == 0) {
            $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue("A" . ($index - 3), 'Titre');
            $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue("A" . ($index - 2), 'Conditionnement');
            $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue("A" . ($index - 1), 'Dépôt');
        } else {
            $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($titleCell, 'Titre');
            $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($depotCell, 'Dépôt');
        }
        foreach ($depots as $depot) {
            $column = "A" . $index;
            $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column, $depot);
            $index++;
        }
    }

    private function mainXlsContent($phpExcelObject, $depots, $produits, $qtesProduitsParDepot, $iTabs, $line) {
        if ($iTabs == 1)
            $pqt = 'Pqt';
        else
            $pqt = 'Appt';
        $rowPqt = ($line - 1);
        $rowProductLibelle = ($line - 2);
        $this->mainXlsLeftColumn($phpExcelObject, $depots, $iTabs, $line);
        $this->setHeaderColumn($phpExcelObject, $rowProductLibelle, $iTabs);
        $this->setStyleXls($phpExcelObject, $rowProductLibelle, count($depots), $iTabs);
        $phpExcelObject->getActiveSheet()->getStyle("A$rowPqt:G$rowPqt")->getAlignment()->setWrapText(true);
        foreach ($depots as $depotId => $depot) {
            $cursorLetter = 1; // B
            foreach ($produits as $produit) {
                $column = $this->getColumn($cursorLetter);
                $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column . $rowPqt, $pqt);
                $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column . $rowProductLibelle, $produit->getLibelle());
                $cursorLetter++;
                if (isset($qtesProduitsParDepot[$depotId][$produit->getId()])) {
                    if ($iTabs == 1)
                        $val = $qtesProduitsParDepot[$depotId][$produit->getId()]['pqt'];
                    else
                        $val = $qtesProduitsParDepot[$depotId][$produit->getId()]['appt'];
                    $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column . $line, $val);
                } else
                    $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column . $line, 0);
            }
            $line++;
        }
        $this->xlsFooter($phpExcelObject, $depots, $produits, $qtesProduitsParDepot, $iTabs, $line);
    }

    private function xlsFooter($phpExcelObject, $depots, $produits, $qtesProduitsParDepot, $iTabs, $footerLine) {
        if ($iTabs == 1)
            $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue("A$footerLine", 'Total Paquets standards');
        else
            $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue("A$footerLine", 'Total Paquets appoint');
        $cursorLetter = 1; // B
        foreach ($produits as $produit) {
            $sum = 0;
            foreach ($depots as $depotId => $depot) {
                if (isset($qtesProduitsParDepot[$depotId][$produit->getId()])) {
                    $val = ($iTabs == 1) ? $qtesProduitsParDepot[$depotId][$produit->getId()]['pqt'] : $qtesProduitsParDepot[$depotId][$produit->getId()]['appt'];
                    $sum = $sum + $val;
                }
            }
            $column = $this->getColumn($cursorLetter);
            $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column . $footerLine, $sum);
            $cursorLetter++;
        }
        $phpExcelObject->getActiveSheet()->getStyle("A$footerLine:G$footerLine")->applyFromArray($this->getStyle('styleCell'));
        $phpExcelObject->getActiveSheet()->getStyle("A$footerLine:G$footerLine")->applyFromArray($this->getStyle('styleArray'));
    }

    private function mergeXlsContent($phpExcelObject, $depots, $produits, $qtesProduitsParDepot, $iTabs, $line, $paquetVolumeParProduit) {
        $rowPqt = $line;
        $rowConditionnement = ($line - 1);
        $rowProductLibelle = ($line - 2);
        $line++;
        $this->mainXlsLeftColumn($phpExcelObject, $depots, $iTabs, $line);
        $this->setHeaderColumn($phpExcelObject, $rowProductLibelle, $iTabs);
        $this->setStyleXls($phpExcelObject, $rowProductLibelle, count($depots), $iTabs);
        foreach ($depots as $depotId => $depot) {
            $cursorLetter = 1; // B
            foreach ($produits as $produit) {
                $conditionnement = (isset($paquetVolumeParProduit[$produit->getId()])) ? $paquetVolumeParProduit[$produit->getId()] : '0';
                $column_1 = $this->getColumn($cursorLetter);
                $cursorLetter++;
                $column_2 = $this->getColumn($cursorLetter);
                $cursorLetter++;
                $phpExcelObject->setActiveSheetIndex(0)->mergeCells($column_1 . $rowProductLibelle . ":" . $column_2 . $rowProductLibelle)
                        ->setCellValue($column_1 . $rowProductLibelle, $produit->getLibelle());
                $phpExcelObject->setActiveSheetIndex(0)->mergeCells($column_1 . $rowConditionnement . ":" . $column_2 . $rowConditionnement)
                        ->setCellValue($column_1 . $rowConditionnement, $conditionnement);
                $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column_1 . $rowPqt, 'Pqt');
                $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column_2 . $rowPqt, 'Appt');
                if (isset($qtesProduitsParDepot[$depotId][$produit->getId()])) {
                    $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column_1 . $line, $qtesProduitsParDepot[$depotId][$produit->getId()]['pqt']);
                    $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column_2 . $line, $qtesProduitsParDepot[$depotId][$produit->getId()]['appt']);
                } else {
                    $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column_1 . $line, 0);
                    $phpExcelObject->setActiveSheetIndex($iTabs)->setCellValue($column_2 . $line, 0);
                }
            }$line++;
        }
        $this->mergeXlsFooter($phpExcelObject, $depots, $produits, $qtesProduitsParDepot, $paquetVolumeParProduit, $line);
    }

    private function mergeXlsFooter($phpExcelObject, $depots, $produits, $qtesProduitsParDepot, $paquetVolumeParProduit, $footerLine) {
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue("A" . ($footerLine), 'Nb exemplaire');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue("A" . ($footerLine + 1), 'Total Paquets standards');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue("A" . ($footerLine + 2), 'Total Paquets appoint');
        $cursorLetter = 1; // B
        foreach ($produits as $produit) {
            $sum_pqt = $sum_appt = $totalPaquetApoint = 0;
            foreach ($depots as $depotId => $depot) {
                if (isset($qtesProduitsParDepot[$depotId][$produit->getId()])) {
                    $sum_pqt = $qtesProduitsParDepot[$depotId][$produit->getId()]['pqt'] + $sum_pqt;
                    $sum_appt = $qtesProduitsParDepot[$depotId][$produit->getId()]['appt'] + $sum_appt;
                }
            }
            $conditionnement = (isset($paquetVolumeParProduit[$produit->getId()])) ? $paquetVolumeParProduit[$produit->getId()] : '0';
            $nbExemplaire = $sum_pqt;
            if ($conditionnement) {
                $nbExemplaire = $sum_appt + ($sum_pqt * $conditionnement);
                $totalPaquetApoint = floor($sum_appt / $conditionnement) . 'P';
                $rest = $sum_appt - ($totalPaquetApoint * $conditionnement);
                if ($rest > 0)
                    $totalPaquetApoint.= ' + ' . $rest;
            }
            $column_1 = $this->getColumn($cursorLetter);
            $cursorLetter++;
            $column_2 = $this->getColumn($cursorLetter);

            $phpExcelObject->setActiveSheetIndex(0)->mergeCells($column_1 . ($footerLine) . ":" . $column_2 . ($footerLine))
                    ->setCellValue($column_1 . ($footerLine), $nbExemplaire);
            $phpExcelObject->setActiveSheetIndex(0)->mergeCells($column_1 . ($footerLine + 1) . ":" . $column_2 . ($footerLine + 1))
                    ->setCellValue($column_1 . ($footerLine + 1), $sum_pqt);
            $phpExcelObject->setActiveSheetIndex(0)->mergeCells($column_1 . ($footerLine + 2) . ":" . $column_2 . ($footerLine + 2))
                    ->setCellValue($column_1 . ($footerLine + 2), $totalPaquetApoint);
            $cursorLetter++;
        }
        for ($i = $footerLine; $i <= ($footerLine + 2); $i++) {
            $phpExcelObject->getActiveSheet()->getStyle("A$i:M$i")->applyFromArray($this->getStyle('styleArray'));
            $phpExcelObject->getActiveSheet()->getStyle("A$i:M$i")->applyFromArray($this->getStyle('styleCell'));
        }
    }

    private function constructXlsBySection($phpExcelObject, $depots, $produits, $qtesProduitsParDepot, $iTabs, $paquetVolumeParProduit = false) {
        $this->paramXls($phpExcelObject, $iTabs);
        $aProductTmp = array();
        $count = 0;
        $line = 5;
        foreach ($produits as $produit) {
            $count ++;
            $aProductTmp[] = $produit;
            if ($count % 6 == 0 || $count == count($produits)) {
                if ($iTabs == 0)
                    $this->mergeXlsContent($phpExcelObject, $depots, $aProductTmp, $qtesProduitsParDepot, $iTabs, $line, $paquetVolumeParProduit);
                else
                    $this->mainXlsContent($phpExcelObject, $depots, $aProductTmp, $qtesProduitsParDepot, $iTabs, $line);
                $line = $line + 31;
                unset($aProductTmp);
            }
        }
    }

    private function getColumn($index) {
        $aLetters = range('A', 'Z');
        if ($index < 26)
            return $aLetters[$index];
        else {
            $firstNumber = floor($index / 26);
            $secondNumber = $index - ($firstNumber * 26);
            return $aLetters[$firstNumber - 1] . $aLetters[$secondNumber];
        }
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
            return array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,),),
                'font' => array('bold' => false, 'size' => 10,));
        if ($item == 'styleHeader')
            return array('font' => array('bold' => true, 'size' => 8),
                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'FFE1E6FA'),),
            );
        if ($item == 'styleCell')
            return array('font' => array('bold' => false, 'size' => 10,),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'FFC4D7ED'),),);
    }

    private function isDirRecursive($path) {
        $segments = explode('/', $path);
        $tmpPath = '';
        foreach ($segments as $key => $segment) {
            $tmpPath .= (!$key) ? $segment : '/' . $segment;
            if (!is_dir($tmpPath)) {
                mkdir($tmpPath, 0777);
            }
        }
    }

    //mise à jour ordre des depôts après drag and drop

    public function miseAJourOrdreAction() {
        $em = $this->getDoctrine()->getManager();
        $rq = $this->getRequest();
        $i = 1;
        $tab = $rq->request->get('tab');

        foreach ($tab as $key => $value) {
            if ($key > 0) {
                $depot = $em->getRepository('AmsSilogBundle:Depot')->find($value);
                $depot->setOrdre($i);
                $i++;
                $em->persist($depot);
            }
            $em->flush();
        }
        return new Response();
    }
    public function rechercheTourneeJour($id_casl,$em) {
        $tourne=$em->getRepository("AmsDistributionBundle:ClientAServirLogist")->find($id_casl);
        $pointLivraison = $tourne->getPointLivraison()->getId();
        $jourType=$tourne->getDateParution()->format("Y-m-d");
        $i=false;
        $j=7;
        while ($i==false) {
            if($j>50){
                return "";
            }
            $tourne=$em->getRepository("AmsDistributionBundle:ClientAServirLogist")->trouveLastTourneeJour($pointLivraison,$jourType,$j);
            foreach ($tourne as $key => $value) {
                if($value["tournee_jour_id"] != ""){
                   $em->getRepository("AmsDistributionBundle:ClientAServirLogist")->mettreAjourTournee_jour($id_casl,$value["tournee_jour_id"]); 
                    
                    return $value["tournee_jour_id"];
                }
            }
            $j=$j+7;
        }
        return "";
    }

    public function abonneeAffectTournee($em,$id_casl,$code_tournne,$date) {
        $tournee=$em->getRepository("AmsModeleBundle:ModeleTourneeJour")->getTourneeParDateCode($date,$code_tournne);
        $em->getRepository("AmsDistributionBundle:ClientAServirLogist")->mettreAjourTournee_jour($id_casl,$tournee["id"]);
        
    }
}
