<?php
namespace Ams\ReferentielBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Ams\ReferentielBundle\Controller\GlobalReferentielController;

class RefPopulationController extends GlobalReferentielController {

    public function getRepositoryName() { return $this->getBundleName().':RefPopulation'; }

    public function gridAction() {
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => false,
            'curseur' => $this->getRepository()->select(),
            'comboEmploi' => $em->getRepository('AmsReferentielBundle:RefEmploi')->selectCombo(),
            'comboTypeContrat' => $em->getRepository('AmsReferentielBundle:RefTypeContrat')->selectCombo(),
            'comboTypeUrssaf' => $em->getRepository('AmsReferentielBundle:RefTypeUrssaf')->selectCombo(),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction() {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();

        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
         ));
    }
}
