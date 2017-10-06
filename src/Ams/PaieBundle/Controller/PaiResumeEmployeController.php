<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiResumeEmployeController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiResumeEmploye'; }
    public function getServiceName() { return 'ams.repository.pairesumeemploye'; }

    public function gridAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => false,
            'curseur' => $this->get($this->getServiceName())->select($session->get("depot_id"),$session->get("flux_id"),$session->get("anneemois_id"),$session->get("employe_id"),$request->get("mois_calendaire"),$request->get("resume_valide")),
            'comboJour' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefJour')->selectCombo()),
            'comboTransport' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTransport')->selectCombo()),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session,$request,'');
        $form = $this->initFiltreEmploye($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getServiceName(),
            'depot_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id"),
            'date_distrib' => $session->get("date_distrib"),
         ));
    }
}
