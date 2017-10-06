<?php
namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\EmployeBundle\Controller\GlobalEmployeController;

class RechercheController extends GlobalEmployeController {

    public function getRepositoryName() { return $this->getBundleName().':Recherche'; }
    public function getServiceName() { return 'ams.repository.employerecherche'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'curseur' => $this->get($this->getServiceName())->select($session->get("employe_nom"),$session->get("employe_prenom")),
            'comboDepot' => $this->getCombo($em->getRepository('AmsSilogBundle:Depot')->selectCombo()),
            'comboFlux' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefFlux')->selectCombo()),
            'comboSociete' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefEmpSociete')->selectCombo()),
            'comboEmploi' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefEmploi')->selectCombo()),
            'comboTypeTournee' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTypeTournee')->selectComboAll()),
            'comboTypeContrat' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTypeContrat')->selectCombo()),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session,$request,'');
        $form = $this->initFiltreNomPrenom($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'form' => $form->createView(),
            'route' => $this->page_courante_route,
            'titre' => $this->titre_page,
            'repository' => $this->getServiceName(),
         ));
    }
}
