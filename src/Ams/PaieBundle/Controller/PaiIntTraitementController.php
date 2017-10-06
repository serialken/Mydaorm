<?php

namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiIntTraitementController extends GlobalPaiController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':PaiIntTraitement';
    }

    public function getRoute() {
        return 'liste_pai_int_traitement';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => false,
            'curseur' => $this->getRepository()->select($session->get("anneemois_id")),
            'comboUtilisateur' => $this->getCombo($em->getRepository('AmsSilogBundle:Utilisateur')->selectCombo()),
            'comboDepot' => $this->getCombo($em->getRepository('AmsSilogBundle:Depot')->selectCombo()),
            'comboFlux' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefFlux')->selectCombo()),
       ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $this->setDerniere_page();
        $session = $this->get('session');

        $form = $this->initFiltreMois($session,$request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => false,
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
        ));
    }

    public function ajaxComboAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $session->set('anneemois_id', $request->query->get('anneemois_id'));
        return $this->ajaxCombo($this->getRepository()->selectCombo($session->get("anneemois_id")));
    }  


    public function ajaxPleiadesNGAction(Request $request) {
/*        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
*/
        $sCmd = 'php '.$this->get('kernel')->getRootDir().'/console '
            . 'alimentation_employe '
            . ' --env=' . $this->get('kernel')->getEnvironment()
            .' '.$this->get('session')->get('UTILISATEUR_ID');
        $this->bgCommandProxy($sCmd);

        return $this->ajaxResponse("", "");
    }

    public function ajaxOctimeAction(Request $request) {
/*        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
*/
        $sCmd = 'php '.$this->get('kernel')->getRootDir(). '/console '
            . 'generation_octime2 '
            . ' --env=' . $this->get('kernel')->getEnvironment()
            .' '.$this->get('session')->get('UTILISATEUR_ID');
        $this->bgCommandProxy($sCmd);

        return $this->ajaxResponse("", "");
    }
}
