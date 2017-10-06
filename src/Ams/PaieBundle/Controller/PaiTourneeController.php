<?php

namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\PaieBundle\Controller\GlobalPaiController;
use HTML2PDF;
use Symfony\Component\Finder\Finder;

class PaiTourneeController extends GlobalPaiController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':PaiTournee';
    }

    public function getRoute() {
        return 'liste_pai_tournee';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'isAlim' => $this->getIsAlim($this->getRoute()),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get('date_distrib')),
            'comboGroupe' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboGroupeDate($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"))),
            'comboTournee' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboDate($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"))),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboPaiTournee($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"),true), true),
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
            $sqlCondition = "AND (pt.id=" . $newId . ")";
        } else if ($param["old_employe_id"] == $param["employe_id"]) {
            $sqlCondition = "AND (pt.employe_id=" . $param['employe_id'] . ")";
        } else if ($param["employe_id"] == '') {
            $sqlCondition = "AND (pt.id=" . $newId . " OR pt.employe_id=" . $param["old_employe_id"] . ")";
        } else if ($param["old_employe_id"] == '') {
            $sqlCondition = "AND (pt.employe_id=" . $param["employe_id"] . ")";
        } else {
            $sqlCondition = "AND (pt.employe_id=" . $param['employe_id'] . " OR pt.employe_id=" . $param["old_employe_id"] . ")";
        }
        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'isAlim' => $this->getIsAlim($this->getRoute()),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"), $sqlCondition),
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

        $this->saveUrl2Session($session, $request, 'tournee_id');
        $form = $this->initFiltre($session, $request);

        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'isAlim' => $this->getIsAlim($this->getRoute()),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
            'depot_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id"),
            'date_distrib' => $session->get("date_distrib"),
            'tournee_id' => $session->get("tournee_id"),
            'comboModele' => $this->getComboHtml($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboAjout($session->get("depot_id"), $session->get("flux_id"), $session->get('date_distrib'))),
            'comboSplit' => $this->getComboHtml($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboSplit($session->get("depot_id"), $session->get("flux_id"), $session->get('date_distrib'))),
            'comboDeSplit' => $this->getComboHtml($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboDeSplit($session->get("depot_id"), $session->get("flux_id"), $session->get('date_distrib'))),
            'filtreSplit' => $session->get("filtreSplit"),
        ));
    }

    public function ajaxAlimentationAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $idtrt = null;
        $em->getRepository('AmsPaieBundle:PaiTournee')->alimentation($msg, $msgException, $idtrt, $session->get('UTILISATEUR_ID'), $session->get("date_distrib"), $session->get("depot_id"), $session->get("flux_id"));
        return $this->ajaxResponse($msg, $msgException);
    }

    public function ajaxSuppressionAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $idtrt = null;
        $em->getRepository('AmsPaieBundle:PaiTournee')->suppression($msg, $msgException, $idtrt, $session->get('UTILISATEUR_ID'), $session->get("date_distrib"), $session->get("depot_id"), $session->get("flux_id"));
        return $this->ajaxResponse($msg, $msgException);
    }

    public function ajaxAjouterAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AmsPaieBundle:PaiTournee')->ajouter($msg, $msgException, $session->get('UTILISATEUR_ID'), $session->get("date_distrib"), $request->get("modele_tournee_jour_id"));
        return $this->ajaxResponse($msg, $msgException);
    }

    public function ajaxSplitterAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AmsPaieBundle:PaiTournee')->splitter($msg, $msgException, $session->get('UTILISATEUR_ID'), $session->get("date_distrib"), $session->get("depot_id"), $session->get("flux_id"), $request->get("tournee_org_id"), $request->get("nb_tournee_dst"));
        return $this->ajaxResponse($msg, $msgException);
    }

    public function ajaxDeSplitterAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AmsPaieBundle:PaiTournee')->deSplitter($msg, $msgException, $session->get('UTILISATEUR_ID'), $request->get("tournee_split_id"));
        return $this->ajaxResponse($msg, $msgException);
    }

    public function ajaxComboSplitAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        return $this->ajaxCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboSplit($session->get("depot_id"), $session->get("flux_id"), $session->get('date_distrib')));
    }

    public function ajaxComboDesplitAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        return $this->ajaxCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboDeSplit($session->get("depot_id"), $session->get("flux_id"), $session->get('date_distrib')));
    }

    public function ajaxComboModeleAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        return $this->ajaxCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboAjout($session->get("depot_id"), $session->get("flux_id"), $session->get('date_distrib')));
    }

    public function emargementAction() {
        $session = $this->get('session');
        return $this->get('ams.pai.emargement')->emargementTourneeExcel($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"));
    }

}
