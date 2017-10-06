<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiIntJournalController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiIntJournal'; }
    public function getServiceName() { return 'ams.repository.paiintjournal'; }
    public function getRoute() { return 'liste_int_journal'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => false,
            'curseur' => $this->get($this->getServiceName())->select($session->get("anneemois_id"),$session->get("flux_id")),
            'comboDepot' => $this->getCombo($em->getRepository('AmsSilogBundle:Depot')->selectCombo()),
            'comboFlux' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefFlux')->selectCombo()),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboPaiJournal($session->get("anneemois_id"))),
            'comboTournee' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboJournal($session->get("anneemois_id"))),
            'comboActivite' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefActivite')->selectComboJournal()),
            'comboProduit' => $this->getCombo($em->getRepository('AmsProduitBundle:Produit')->selectCombo()),
            'anneemois_id' => $session->get("anneemois_id")
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session,$request);
        $form = $this->initFiltreFluxMois($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'isActu' => $this->getIsActu($this->getRoute()),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getServiceName(),
     ));
    }

    public function ajaxActualisationAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AmsPaieBundle:PaiJournal')->actualisation($msg, $msgException, $session->get("anneemois_id"), null, $session->get("flux_id"));
        return $this->ajaxResponse($msg, $msgException);
    }
}