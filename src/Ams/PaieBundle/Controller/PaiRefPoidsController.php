<?php

namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiRefPoidsController extends GlobalPaiController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':PaiRefPoids';
    }

    public function getRoute() {
        return 'liste_pai_refpoids';
    }

    public function gridAction() {
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->isPageElement('MODIF', $this->getRoute()),
            'curseur' => $this->getRepository()->select(),
            'comboTypeTournee' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTypeTournee')->selectCombo()),
            'comboProduitType' => $this->getCombo($em->getRepository('AmsProduitBundle:ProduitType')->selectCombo()),
            'comboProduit' => $this->getCombo($em->getRepository('AmsProduitBundle:Produit')->selectCombo(),true),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction() {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->isPageElement('MODIF', $this->getRoute()),
            'form' => '',
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
        ));
    }
    
    public function ajaxMajTourneeAction() {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AmsPaieBundle:PaiPrdTournee')->recalcul_tournee(0);
        return $this->ajaxResponse('', '');
    }


}
