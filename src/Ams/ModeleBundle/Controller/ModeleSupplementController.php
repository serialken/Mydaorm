<?php

namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\ModeleBundle\Controller\GlobalModeleController;

class ModeleSupplementController extends GlobalModeleController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':ModeleSupplement';
    }
    public function getRoute() {
        return 'liste_modele_supplement';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id")),
            'comboJour' => $em->getRepository('AmsReferentielBundle:RefJour')->selectCombo(),
            'comboNatureClient' => $em->getRepository('AmsReferentielBundle:RefNatureClient')->selectCombo(),
            'comboSupplement' => $em->getRepository('AmsModeleBundle:ModeleSupplement')->selectComboSupplement($session->get("flux_id")),
            'comboTitre' => $em->getRepository('AmsModeleBundle:ModeleSupplement')->selectComboTitre($session->get("flux_id")),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $sqlCondition = "AND (ms.id=".$newId.")";
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

        $this->saveUrl2Session($session, $request, 'supplement_id');
        $form = $this->initFiltre($session, $request);
        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
            'depot_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id"),
            'comboJour' => $this->getComboHtml($em->getRepository('AmsReferentielBundle:RefJour')->selectCombo()),
            'comboNatureClient' => $this->getComboHtml($em->getRepository('AmsReferentielBundle:RefNatureClient')->selectCombo()),
        ));
    }
    public function ajaxComboAjouterOrgAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        return $this->ajaxCombo($em->getRepository('AmsModeleBundle:ModeleSupplement')->selectComboAjouterOrg($session->get("flux_id"), $request->get('supplement_id')));
    }  
}
