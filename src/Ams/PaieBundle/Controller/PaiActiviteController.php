<?php

namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiActiviteController extends GlobalPaiController {
    protected $est_hors_presse=false;
            
    public function getRepositoryName() {
        return $this->getBundleName() . ':PaiActivite';
    }

    public function getRoute() {
        return 'liste_pai_activite';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
//            'isAjout' => !$this->est_hors_presse,
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"),$this->est_hors_presse),
            'comboActivite' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefActivite')->selectComboPai($this->est_hors_presse)),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboPaiActivite($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"),$this->est_hors_presse), true),
            'comboTransport' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTransport')->selectCombo(), true),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        /*
         *                              old_employe
         *                  0               X                   Y
         * 
         *              0   id              id                  id
         *                                  +old_employe_id     +old_employe_id
         * 
         *  employe     X   employe_id      employe_id          employe_id
         *                                                      +old_employe_id
         * 
         *              Y   employe_id      employe_id          employe_id
         *                                  +old_employe_id     

         */
        if ($param["old_employe_id"] == '' && $param["employe_id"] == '') {
            $sqlCondition = "AND (pa.id=" . $newId . ")";
        } else if ($param["old_employe_id"] == $param["employe_id"]) {
            $sqlCondition = "AND (pa.employe_id=" . $param['employe_id'] . ")";
        } else if ($param["employe_id"] == '') {
            $sqlCondition = "AND (pa.id=" . $newId . " OR pa.employe_id='" . $param["old_employe_id"] . "' OR pa.xaoid='" . $param["old_employe_id"] . "')";
        } else if ($param["old_employe_id"] == '') {
            $sqlCondition = "AND (pa.employe_id='" . $param["employe_id"] . "' OR pa.xaoid='" . $param["employe_id"] . "')";
        } else {
            $sqlCondition = "AND (pa.employe_id='" . $param['employe_id'] . "' OR pa.employe_id='" . $param["old_employe_id"] . "' OR pa.xaoid='" . $param["employe_id"] . "' OR pa.xaoid='" . $param["old_employe_id"] . "')";
        }
        // 23/02/2016 Comme ouverture peut changer sur n'imorte quelle activité, il faut tout recharcher
        $sqlCondition = "";
        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $em->getRepository('AmsPaieBundle:PaiActivite')->select($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"),$this->est_hors_presse, $sqlCondition),
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

        $this->saveUrl2Session($session, $request, 'activite_id');
        $form = $this->initFiltre($session, $request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'isAlim' => $this->getIsAlim($this->getRoute()),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => ($this->est_hors_presse?$this->getRepositoryNameHP():$this->getRepositoryName()),
            'depot_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id"),
            'date_distrib' => $session->get("date_distrib"),
            'activite_id' => $session->get("activite_id"),
            'est_hors_presse' => $this->est_hors_presse
        ));
    }

    public function ajaxAlimentationAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $idtrt = null;
        $em->getRepository('AmsPaieBundle:PaiActivite')->alimentation($msg, $msgException, $idtrt, $session->get('UTILISATEUR_ID'), $session->get("date_distrib"), $session->get("depot_id"), $session->get("flux_id"), $request->get("est_hors_presse"));
        return $this->ajaxResponse($msg, $msgException);
    }

    public function ajaxSuppressionAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $idtrt = null;
        $em->getRepository('AmsPaieBundle:PaiActivite')->suppression($msg, $msgException, $idtrt, $session->get('UTILISATEUR_ID'), $session->get("date_distrib"), $session->get("depot_id"), $session->get("flux_id"), $request->get("est_hors_presse") );
        return $this->ajaxResponse($msg, $msgException);
    }

    public function emargementActiviteAction(Request $request) {
        $session = $this->get('session');
        return $this->get('ams.pai.emargement')->emargementHeureExcel($session->get("depot_id"), $session->get("flux_id"), $session->get('date_distrib'));
    }

    
}