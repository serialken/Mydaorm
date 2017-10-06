<?php
namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\EmployeBundle\Controller\GlobalEmployeController;
use Ams\EmployeBundle\Form\FiltreRequestType;

class EmployeRequestController extends GlobalEmployeController {

    public function getRepositoryName() { return $this->getBundleName() . ':EmployeRequest'; }
    public function getRoute() { return 'liste_employe_request'; }
    public function getServiceName() { return 'ams.repository.employerequest'; }
    public function getRepository() { return $this->get($this->getServiceName()); }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $this->getRepository()->exec($session->get("request_employe_id") ,$head ,$rows,$session);
//        var_dump($head);
        if (!isset($rows[0])) {
            $response = $this->renderView($this->getRepositoryName().':nullgrid.xml.twig');
        } else {
            $response = $this->renderView($this->getTwigGrid(), array(
                'isModif' => $this->getIsModif(),
                'head' => $head,
                'rows' => $rows,
            ));
        }
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $this->saveUrl2Session($session, $request,'');
        $form = $this->initFiltreRequest($session, $request);
        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getServiceName(),
        ));
    }

    protected function initFiltreRequest($session, $request) {
        $form_request = $request->request->get('form_filtre');
        if (isset($form_request)) {
            $session->set("depot_id", $form_request['depot_id']);
            $session->set("flux_id", $form_request['flux_id']);
            $session->set("anneemois_id", $form_request['anneemois_id']);
            $session->set("request_employe_id", $form_request['request_id']);
        }
        $comboRequests=$this->getComboArray($this->getRepository()->selectCombo('req_employe'));
        $_request_employe_id=$session->get("request_employe_id");
        if (!isset($_request_employe_id) && count($comboRequests) > 0) {
            $session->set("request_employe_id", array_keys($comboRequests)[0]);
        }
        $form = $this->createForm(new FiltreRequestType($session->get('DEPOTS'), $session->get('FLUXS'), $session->get('ANNEEMOIS'), $comboRequests, $session->get('depot_id'), $session->get('flux_id'), $session->get('anneemois_id'), $session->get("request_employe_id")));
        return $form;
    }
}
