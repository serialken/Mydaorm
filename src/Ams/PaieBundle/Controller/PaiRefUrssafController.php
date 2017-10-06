<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiRefUrssafController extends GlobalPaiController {
    
    public function getRepositoryName() { return $this->getBundleName().':PaiRefUrssaf'; }
    public function getServiceName() { return 'ams.repository.pairefurssaf'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => false,
            'curseur' => $this->get($this->getServiceName())->select(),
            'comboProduit' => $this->getCombo($em->getRepository('AmsProduitBundle:Produit')->selectCombo()),
            'comboType' => $this->getCombo($em->getRepository('AmsProduitBundle:ProduitType')->selectCombo()),
            'comboTypeUrssaf' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTypeUrssaf')->selectComboCode()),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();
        $session = $this->get('session');

        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'form' => '',
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getServiceName(),
         ));
    }
}