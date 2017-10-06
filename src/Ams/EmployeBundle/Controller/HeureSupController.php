<?php
namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\EmployeBundle\Controller\GlobalEmployeController;

class HeureSupController extends GlobalEmployeController {
    
    public function getRepositoryName() { return $this->getBundleName().':HeureSup'; }
    public function getServiceName() { return 'ams.repository.employeheuresup'; }
    public function getRoute() { return 'liste-employe-heure-sup'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => false,
            'curseur' => $this->get($this->getServiceName())->select($session->get("depot_id"),$session->get("flux_id"),$session->get("anneemois_id")),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboSuiviHoraire($session->get("depot_id"),$session->get("flux_id"),$session->get("anneemois_id"))),
            'comboEmploi' => $this->getCombo($this->get($this->getServiceName())->selectCombo()),
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
            'isActu' => $this->getIsActu($this->getRoute()),
            'form' => $form->createView(),
            'route' => $this->page_courante_route,
            'titre' => $this->titre_page,
            'repository' => $this->getServiceName(),
         ));
    }

    public function ajaxActualisationAction(Request $request) {
        $session = $this->get('session');
        $this->get($this->getServiceName())->actualisation($msg, $msgException, $session->get("anneemois_id"), $session->get("depot_id"), $session->get("flux_id"));
        return $this->ajaxResponse($msg, $msgException);
    }
}