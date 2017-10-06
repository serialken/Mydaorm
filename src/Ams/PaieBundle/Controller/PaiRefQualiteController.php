<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiRefQualiteController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiRefQualite'; }
    public function getRoute() { return 'liste_pai_refqualite'; }

    public function gridAction() {
        $em = $this->getDoctrine()->getManager();
 
        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->isPageElement('MODIF',$this->getRoute()),
            'curseur' => $this->getRepository()->select(),
            'comboSociete' => $em->getRepository('AmsReferentielBundle:RefEmpSociete')->selectCombo(),
            'comboEmploi' => $em->getRepository('AmsReferentielBundle:RefEmploi')->selectComboCode(),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction() {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        $this->setDerniere_page();

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->isPageElement('MODIF',$this->getRoute()),
            'form' => '',
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
         ));
    }
}
