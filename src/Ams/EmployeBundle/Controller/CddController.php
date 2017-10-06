<?php
namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\EmployeBundle\Controller\GlobalEmployeController;

class CddController extends GlobalEmployeController {

    public function getRepositoryName() { return $this->getBundleName().':Cdd'; }
    public function getServiceName() { return 'ams.repository.employecdd'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'curseur' => $this->get($this->getServiceName())->select($session->get("depot_id"),$session->get("flux_id"),$session->get("anneemois_id")),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboRH($session->get("depot_id"),$session->get("flux_id"),$session->get("anneemois_id"))),
            'comboDepot' => $this->getCombo($em->getRepository('AmsSilogBundle:Depot')->selectCombo()),
            'comboFlux' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefFlux')->selectCombo()),
            'comboMotifDebut' => $this->getCombo($this->get($this->getServiceName())->selectComboMotifDebut()),
            'comboMotifRempl' => $this->getCombo($this->get($this->getServiceName())->selectComboMotifRempl()),
            'comboTermeCdd' => $this->getCombo($this->get($this->getServiceName())->selectComboTermeCdd()),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session,$request,'');
        $form = $this->initFiltreDepotFluxMois($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'form' => $form->createView(),
            'route' => $this->page_courante_route,
            'titre' => $this->titre_page,
            'repository' => $this->getServiceName(),
         ));
    }
}
