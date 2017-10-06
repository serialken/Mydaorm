<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiProduitController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiProduit'; }
    public function getServiceName() { return 'ams.repository.paiproduit'; }
    public function getRoute() { return 'liste_pai_produit'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->get('ams.repository.paiproduit')->select($session->get("depot_id"),$session->get("flux_id"),$session->get("date_distrib")),
            'comboGroupe' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboGroupeDate($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"))),
            'comboTournee' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectCombo($session->get("depot_id"),$session->get("flux_id"),$session->get("date_distrib"))),
            'comboProduit' => $this->getCombo($em->getRepository('AmsProduitBundle:Produit')->selectComboDate($session->get("date_distrib"))),
            'comboNatureClient' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefNatureClient')->selectCombo()),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');

        if ($param["tournee_org_id"]!='') {
            $sqlCondition = " AND pt.tournee_org_id=" . $param["tournee_org_id"] . " AND ppt.produit_id=" . $param["produit_id"] . " AND ppt.natureclient_id=" . $param["natureclient_id"];
        } else {
            $sqlCondition = " AND ppt.id=" . $newId;
        }

        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->get('ams.repository.paiproduit')->select($session->get("depot_id"),$session->get("flux_id"),$session->get("date_distrib"),$sqlCondition),
        ));
        return new Response($response);
    }
    
    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session,$request,'produit_id');
        $form = $this->initFiltre($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getServiceName(),
            'produit_id' => $session->get("produit_id"),
            'filtreSplit' => $session->get("filtreSplit"),
         ));
    }
}
