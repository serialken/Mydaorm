<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiPoidsDepotController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiPoidsDepot'; }
    public function getServiceName() { return 'ams.repository.paipoidsdepot'; }
    public function getRoute() { return 'liste_pai_poids_depot'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->get($this->getServiceName())->select($session->get("depot_id"),$session->get("flux_id"),$session->get("date_distrib")),
            'comboGroupe' => $this->getCombo($em->getRepository('AmsModeleBundle:GroupeTournee')->selectCombo($session->get("depot_id"),$session->get("flux_id"))),
            'comboProduit' => $this->getCombo($em->getRepository('AmsProduitBundle:Produit')->selectComboDate($session->get("date_distrib"))),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session,$request,'');
        $form = $this->initFiltre($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getServiceName(),
            'date_distrib' => $session->get("date_distrib"),
         ));
    }
}
