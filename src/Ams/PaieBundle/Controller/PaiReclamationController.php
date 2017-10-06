<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiReclamationController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiReclamation'; }
    public function getRoute() { return 'liste_pai_reclamation'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
    
        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"),$session->get("flux_id"), $session->get("anneemois_id")),
            'comboType' => $this->getCombo(array(
                array('id' => '1', 'libelle' => 'CRM'),
                array('id' => '2', 'libelle' => 'Manuelle'),
                array('id' => '3', 'libelle' => 'Pepp')
            )),
//            'comboTournee' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiReclamation')->selectComboTourneeMois($session->get("depot_id"),$session->get("flux_id"), $session->get('anneemois_id'))),
//            'comboEmploye' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiReclamation')->selectComboEmployeMois($session->get("depot_id"),$session->get("flux_id"), $session->get('anneemois_id'))),
            'comboSociete' => $this->getCombo($em->getRepository('AmsProduitBundle:Societe')->selectCombo()),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $sqlCondition = " AND pr.id=" . $newId;
        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), $sqlCondition),
        ));
        return new Response($response);
    }
    
    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session,$request,'');
        $form = $this->initFiltreDepotFluxMois($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
            'date_distrib' => date('Y-m-d'),
         ));
    }

    public function ajaxTourneeDateAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        return $this->ajaxCombo($em->getRepository('AmsPaieBundle:PaiReclamation')->selectComboTourneeDate($session->get("depot_id"),$session->get("flux_id"), $request->query->get('date_distrib')));
    }
    public function ajaxTourneeMoisAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        return $this->ajaxCombo($em->getRepository('AmsPaieBundle:PaiReclamation')->selectComboTourneeMois($session->get("depot_id"),$session->get("flux_id"), $session->get('anneemois_id')));
    }
}