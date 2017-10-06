<?php
namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiStcController extends GlobalPaiController {

    public function getRepositoryName() { return $this->getBundleName().':PaiStc'; }
    public function getRoute() { return 'liste_pai_stc'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsPrivilege('MODIF'),
            'isDelete' =>  $this->getIsPrivilege('ANNUL'),
            'curseur' => $this->getRepository()->select($session->get("anneemois_id"),$session->get("flux_id")),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboPaiStc($session->get("anneemois_id"),$session->get("flux_id"))),
            'comboDepot' => $this->getCombo($em->getRepository('AmsSilogBundle:Depot')->selectCombo()),
            'comboFlux' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefFlux')->selectCombo()),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $sqlCondition = " AND (epd.employe_id=".$param['employe_id'].")";
        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsPrivilege('MODIF'),
            'isDelete' =>  $this->getIsPrivilege('ANNUL'),
            'curseur' => $this->getRepository()->select($session->get("anneemois_id"),$session->get("flux_id"),$sqlCondition),
        ));
        return new Response($response);
     }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $this->saveUrl2Session($session,$request,'');
        $form = $this->initFiltreFluxMois($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsPrivilege('MODIF'),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
            'paie_en_cours' => $em->getRepository('AmsPaieBundle:PaiIntTraitement')->paie_en_cours(),
         ));
    }

    protected function getIsPrivilege($droit) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $isPrivilegie = $this->isPageElement($droit, $this->getRoute());
        $isMoisCourantFutur = $em->getRepository('AmsPaieBundle:PaiMois')->isMoisCourantFutur($session->get('anneemois_id'), $session->get("flux_id"));

        return ($isPrivilegie && $isMoisCourantFutur);
    }
    
    public function ajaxGenererAction(Request $request) {
        $sCmd = 'php '.$this->get('kernel')->getRootDir(). '/console '
            . 'generation_stc '
            . ' --env=' . $this->get('kernel')->getEnvironment()
            .' '.$this->get('session')->get("flux_id")
            .' '.$this->get('session')->get('UTILISATEUR_ID');
        $this->bgCommandProxy($sCmd);

        return $this->ajaxResponse("", "");
    }
}

