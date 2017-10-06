<?php

namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\EmployeBundle\Controller\GlobalEmployeController;

class EmpTransfertController extends GlobalEmployeController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':EmpTransfert';
    }

    public function getRoute() {
        return 'liste_employe_transfert';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'isDelete' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id")),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboTransfert($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"))),
            'comboDepot' => $this->getCombo($em->getRepository('AmsSilogBundle:Depot')->selectCombo()),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        
        $sqlCondition = "AND (et.contrat_id=".$param["contrat_id"].")";

        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'isDelete' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), $sqlCondition),
        ));
        return new Response($response);
    }
    
    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session, $request, '');
        $form = $this->initFiltreDepotFluxMois($session, $request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'form' => $form->createView(),
            'route' => $this->page_courante_route,
            'titre' => $this->titre_page,
            'repository' => $this->getRepositoryName(),
            'depot_org_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id"),
        ));
    }

    public function ajaxActualisationAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AmsEmployeBundle:EmpTransfert')->actualisation($msg, $msgException, $session->get('UTILISATEUR_ID'), $session->get("depot_id"), $session->get("flux_id"));
        return $this->ajaxResponse($msg, $msgException);
    }
}
