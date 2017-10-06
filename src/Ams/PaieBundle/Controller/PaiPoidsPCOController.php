<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiPoidsPCOController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiPoidsPCO'; }
    public function getServiceName() { return 'ams.repository.paipoidspco'; }
    public function getRoute() { return 'liste_pai_poids_pco'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->get($this->getServiceName())->select($session->get("date_distrib")),
            'comboProduit' => $this->getCombo($em->getRepository('AmsProduitBundle:Produit')->selectComboDate($session->get("date_distrib"))),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        $this->saveUrl2Session($session,$request,'tournee_id');
        $form = $this->initFiltreDate($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'isAlim' => $this->getIsAlim('alimentation_pai_poids_pco'),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getServiceName(),
            'date_distrib' => $session->get("date_distrib"),
         ));
    }


    public function ajaxAlimentationAction(Request $request) {
        $session = $this->get('session');
        $idtrt=null;
        $this->get($this->getServiceName())->alimentation($msg, $msgException, $idtrt, $session->get('UTILISATEUR_ID'), $session->get("date_distrib"));
        return $this->ajaxResponse($msg, $msgException);
    }
}
