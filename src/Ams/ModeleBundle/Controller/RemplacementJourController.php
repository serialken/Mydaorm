<?php

namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HTML2PDF;

use Ams\ModeleBundle\Controller\GlobalModeleController;
use Ams\ModeleBundle\Form\FiltreRemplacement;

class RemplacementJourController extends GlobalModeleController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':RemplacementJour';
    }

    public function getRoute() {
        return 'liste_remplacement_jour';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'comboJour' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefJour')->selectCombo()),
            'curseur' => $this->getRepository()->select($session->get("remplacement_id")),
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

        $this->saveUrl2Session($session, $request, 'remplacement_id');
        $form = $this->initFiltre($session, $request);
        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
            'remplacement_id' => $session->get("remplacement_id"),
        ));
    }


    protected function initFiltre($session, $request) {
        $em = $this->getDoctrine()->getManager();
        $form_request = $request->request->get('form_filtre');
        if (isset($form_request['employe_id'])) {
            $depot_id = $form_request['depot_id'];
            $flux_id = $form_request['flux_id'];
            $anneemois_id = $form_request['anneemois_id'];
            $remplacement_id = $form_request['remplacement_id'];
        } else {
            $depot_id = $session->get('depot_id');
            $flux_id = $session->get('flux_id');
            $anneemois_id = $session->get('anneemois_id');
            $remplacement_id = $session->get("remplacement_id");
        }

        $comboRemplacement = $this->getComboArray($em->getRepository('AmsModeleBundle:Remplacement')->selectCombo($anneemois_id, $depot_id, $flux_id));
        if (!isset($remplacement_id) & count($comboRemplacement) > 0) {
            $remplacement_id = array_keys($comboRemplacement)[0];
            $session->set("remplacement_id", $remplacement_id);
        }

        $form = $this->createForm(new FiltreRemplacement($session->get('DEPOTS'), $session->get('FLUXS'), $session->get('ANNEEMOIS'), $comboRemplacement, $session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"), $session->get("remplacement_id")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("depot_id", $form->getData()['depot_id']);
            $session->set("flux_id", $form->getData()['flux_id']);
            $session->set("anneemois_id", $form->getData()['anneemois_id']);
            $session->set("remplacement_id", $form->getData()['remplacement_id']);
        }
        return $form;
    }
    
}
