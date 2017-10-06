<?php

namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\EmployeBundle\Controller\GlobalEmployeController;

class EmpContratHPController extends GlobalEmployeController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':EmpContratHP';
    }

    public function getRoute() {
        return 'liste_employe_contrathp';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id")),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboActiviteHP($session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id"))),
            'comboContrat' => $this->getCombo($this->getRepository()->selectComboContrat()),
            'comboMetier' => $this->getCombo($this->getRepository()->selectComboMetier()),
            'comboActivite' => $this->getCombo($this->getRepository()->selectComboActivite()),
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

        $this->saveUrl2Session($session, $request, '');
        $form = $this->initFiltreDepotFluxMois($session, $request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'form' => $form->createView(),
            'route' => $this->page_courante_route,
            'titre' => $this->titre_page,
            'repository' => $this->getRepositoryName(),
        ));
    }

}
