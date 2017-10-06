<?php

namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\ModeleBundle\Controller\GlobalModeleController;

class ModeleTourneeController extends GlobalModeleController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':ModeleTournee';
    }

    public function getRoute() {
        return 'liste_modele_tournee';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id")),
            'comboGroupe' => $this->getCombo($em->getRepository('AmsModeleBundle:GroupeTournee')->selectCombo($session->get("depot_id"), $session->get("flux_id"))),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboModeleTournee($session->get("depot_id"), $session->get("flux_id")), true),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $sqlCondition = "AND (mt.id=" . $newId . ")";

        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $sqlCondition),
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
        $em = $this->getDoctrine()->getManager();

        $this->saveUrl2Session($session, $request, 'modele_tournee_id');
        $form = $this->initFiltre($session, $request);
        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'isTransfert' => $this->isPageElement('TRANSFERT', $this->getRoute()),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
            'depot_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id"),
            'modele_tournee_id' => $session->get("modele_tournee_id"),
            'depotcode' => $em->getRepository('AmsSilogBundle:Depot')->findOneById($session->get("depot_id"))->getCode(),
            'fluxcode' => $em->getRepository('AmsReferentielBundle:RefFlux')->findOneById($session->get("flux_id"))->getCode(),
            'comboTransModele' => $this->getComboHtml($em->getRepository('AmsModeleBundle:ModeleTournee')->selectComboModele($session->get("depot_id"), $session->get("flux_id"))),
            'comboTransDepot' => $this->getComboHtml($em->getRepository('AmsModeleBundle:ModeleTournee')->selectComboDepot($session->get('UTILISATEUR_ID'))),
            'comboTransFlux' => $this->getComboHtml($em->getRepository('AmsReferentielBundle:RefFlux')->selectCombo()),
//                    'inputTransDateDebut' => $session->get('pai_date_fin')
        ));
    }

    public function ajaxTransfererAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AmsModeleBundle:ModeleTournee')->transferer($msg, $msgException, $session->get('UTILISATEUR_ID'), $request->get("modele_tournee_id"), $request->get("groupe_id"), $request->get("code"), $request->get("date_debut"));
        return $this->ajaxResponse($msg, $msgException);
    }
    public function ajaxComboGroupeAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        return $this->ajaxCombo($em->getRepository('AmsModeleBundle:GroupeTournee')->selectCombo($request->get("depot_id"), $request->get("flux_id")));
    }  
}
