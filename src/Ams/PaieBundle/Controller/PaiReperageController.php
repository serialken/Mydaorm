<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaireperageController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiReperage'; }
    public function getServiceName() { return 'ams.repository.paireperage'; }
    public function getRoute() { return 'liste_pai_reperage'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $produitsqte = $this->get($this->getServiceName())->selectQte($session->get("depot_id"),$session->get("flux_id"),$session->get("date_distrib"));
        $qtes   = array();
        foreach($produitsqte as $produitqte){
            $qtes[$produitqte["tournee_id"]][$produitqte["produit_id"]]["nbrep"]=$produitqte["nbrep"];
        }
            
        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'tournees' => $this->get($this->getServiceName())->selectTournee($session->get("depot_id"),$session->get("flux_id"),$session->get("date_distrib")),
            'produits' => $this->get($this->getServiceName())->selectProduit($session->get("depot_id"),$session->get("flux_id"),$session->get("date_distrib")),
            'qtes' => $qtes,
            'comboGroupe' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboGroupeDate($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"))),
            'comboTournee' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboDate($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"))),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboPaiTournee($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib")), true),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');

        if ($param["tournee_org_id"]!='') {
            $sqlCondition = " AND pt.tournee_org_id=" . $param["tournee_org_id"];
        } else {
            $sqlCondition = " AND pt.id=" . $param["tournee_id"];
        }
        $produitsqte = $this->get($this->getServiceName())->selectQte($session->get("depot_id"),$session->get("flux_id"),$session->get("date_distrib"),$sqlCondition);
        $qtes   = array();
        foreach($produitsqte as $produitqte){
            $qtes[$produitqte["tournee_id"]][$produitqte["produit_id"]]["nbrep"]=$produitqte["nbrep"];
        }

        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'tournees' => $this->get($this->getServiceName())->selectTournee($session->get("depot_id"),$session->get("flux_id"),$session->get("date_distrib"),$sqlCondition),
            'produits' => $this->get($this->getServiceName())->selectProduit($session->get("depot_id"),$session->get("flux_id"),$session->get("date_distrib")),
            'qtes' => $qtes,
        ));
        return new Response($response);
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session,$request,'');
        $form = $this->initFiltre($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getServiceName(),
         ));
    }
}