<?php
namespace Ams\ReferentielBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Ams\ReferentielBundle\Controller\GlobalReferentielController;

class RefEmploiController extends GlobalReferentielController {

    public function getRepositoryName() { return $this->getBundleName().':RefEmploi'; }

    public function gridAction() {
        $em = $this->getDoctrine()->getManager();
        $isModif=$this->isPageElement('MODIF','liste_pai_refemploi');

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $isModif,
            'curseur' => $this->getRepository()->select()
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction() {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->isPageElement('MODIF'),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
         ));
    }
}
