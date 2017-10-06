<?php

namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\ModeleBundle\Controller\GlobalModeleController;

class GroupeTourneeController extends GlobalModeleController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':GroupeTournee';
    }

    public function getRoute() {
        return 'liste_modele_groupe';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"))
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

//        $this->saveUrl2Session($session,$request);
        $form = $this->initFiltre($session, $request);
        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
            'depot_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id")
        ));
    }

}
