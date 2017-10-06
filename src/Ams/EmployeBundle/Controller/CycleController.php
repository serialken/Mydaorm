<?php
namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\EmployeBundle\Controller\GlobalGlobalEmployeControllerController;

class CycleController extends GlobalEmployeController {

    public function getRepositoryName() { return $this->getBundleName().':Cycle'; }
    public function getServiceName() { return 'ams.repository.employecycle'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        
        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => false,
            'curseur' => $this->get($this->getServiceName())->select($session->get("depot_id"),$session->get("flux_id"),$session->get("anneemois_id")), 
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboRH($session->get("depot_id"),$session->get("flux_id"),$session->get("anneemois_id"))),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml')); 
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        
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

    public function ajaxDisplayAction(Request $request) { 
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $msg = $this->get($this->getServiceName())->getText($request->query->get('employe_id'), $request->query->get('date_distrib'));
        return $this->ajaxMsg($msg);
    }

}