<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiRefRemunerationController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiRefRemuneration'; }
    public function getRoute() { return 'liste_pai_refremuneration'; }

    public function gridAction() {
        $em = $this->getDoctrine()->getManager();
 
        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => false,
            'curseur' => $this->getRepository()->select(),
            'comboSociete' => $em->getRepository('AmsReferentielBundle:RefEmpSociete')->selectCombo(),
            'comboPopulation' => $em->getRepository('AmsReferentielBundle:RefPopulation')->selectCombo(),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction() {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();

        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'form' => '',
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
         ));
    }
}
