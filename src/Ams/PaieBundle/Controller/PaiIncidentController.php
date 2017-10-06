<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiIncidentController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiIncident'; }
    public function getRoute() { return 'liste_pai_incident'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
    
        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"),$session->get("flux_id"), $session->get("anneemois_id")),
//            'comboTournee' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiIncident')->selectComboTourneeMois($session->get("depot_id"),$session->get("flux_id"), $session->get('anneemois_id'))),
//            'comboEmploye' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiIncident')->selectComboEmployeMois($session->get("depot_id"),$session->get("flux_id"), $session->get('anneemois_id'))),
            'comboIncident' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefIncident')->selectCombo()),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $sqlCondition = " AND pi.id=" . $newId;
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

    public function ajaxEmployeDateAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        return $this->ajaxCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboPaiIncident($session->get("depot_id"),$session->get("flux_id"), $request->query->get('date_distrib')));
    }
}