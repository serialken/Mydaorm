<?php

namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\EmployeBundle\Controller\GlobalEmployeController;

class EmpJournalController extends GlobalEmployeController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':EmpJournal';
    }
    public function getRoute() { return 'liste-emp-journal'; }


    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => false,
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id")),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboEmpJournal($session->get("depot_id"), $session->get("flux_id"))),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session, $request, 'journal_id');
        $form = $this->initFiltreDepotFluxMois($session, $request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'isActu' => $this->getIsActu($this->getRoute()),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'depot_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id"),
            'journal_id' => $session->get("journal_id"),
        ));
    }

    public function ajaxActualisationAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AmsEmployeBundle:EmpJournal')->actualisation($msg, $msgException, $session->get("depot_id"), $session->get("flux_id"));
        return $this->ajaxResponse($msg, $msgException);
    }

}
