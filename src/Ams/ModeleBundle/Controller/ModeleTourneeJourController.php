<?php

namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\ModeleBundle\Controller\GlobalModeleController;
use Ams\ModeleBundle\Form\FiltreTourneeType;

class ModeleTourneeJourController extends GlobalModeleController {

    public function getRepositoryName() {
        return $this->getBundleName() . ':ModeleTourneeJour';
    }

    public function getRoute() {
        return 'liste_modele_tournee_jour';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $isSuperModif = $session->get("flux_id")==2 || $this->isPageElement('SUPERMODIF', $this->getRoute());
        $response = $this->renderView($this->getTwigGrid(), array(
            'flux_id' => $session->get("flux_id"),
            'isModif' => $this->getIsModif(),
            'isSuperModif' => $isSuperModif,
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get("modele_tournee_id")),
            'comboGroupe' => $this->getCombo($em->getRepository('AmsModeleBundle:GroupeTournee')->selectCombo($session->get("depot_id"), $session->get("flux_id"))),
            'comboTournee' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTournee')->selectComboModele($session->get("depot_id"), $session->get("flux_id"))),
            'comboJour' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefJour')->selectCombo()),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboModeleTourneeJour($session->get("depot_id"), $session->get("flux_id")), true),
            'comboTransport' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTransport')->selectCombo()),
            'comboDepartRetour' => $this->getCombo(array(
            array('id' => '1', 'libelle' => 'Oui'),
            array('id' => '0', 'libelle' => 'Non')
            )),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function newGridAction($param, $newId) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        /*                              old_employe
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
        // Attention, prendre en compte tournee_id si modification de la valrem
        if ($param["old_employe_id"] == '' && $param["employe_id"] == '') {
            $sqlCondition = "AND ((mtj.id=" . $newId . ")";
        } else if ($param["old_employe_id"] == $param["employe_id"]) {
            $sqlCondition = "AND ((mtj.employe_id=" . $param['employe_id'] . ")";
        } else if ($param["employe_id"] == '') {
            $sqlCondition = "AND ((mtj.id=" . $newId . " OR mtj.employe_id=" . $param["old_employe_id"] . ")";
        } else if ($param["old_employe_id"] == '') {
            $sqlCondition = "AND ((mtj.employe_id=" . $param["employe_id"] . ")";
        } else {
            $sqlCondition = "AND ((mtj.employe_id=" . $param['employe_id'] . " OR mtj.employe_id=" . $param["old_employe_id"] . ")";
        }
        if ($param["old_valrem"] != $param["valrem"]) {
            $sqlCondition .= "OR (mt.id=" . $param['tournee_id'] . "))";
        } else {
            $sqlCondition .= ")";
        }
        $isSuperModif = $session->get("flux_id")==2 || $this->isPageElement('SUPERMODIF', $this->getRoute());
        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'isSuperModif' => $isSuperModif,
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $session->get("modele_tournee_id"), $sqlCondition),
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

        $this->saveUrl2Session($session, $request, 'modele_tournee_id');
        $this->saveUrl2Session($session, $request, 'modele_tournee_jour_id');
        $form = $this->initFiltreTournee($session, $request);
        $isSuperModif = $session->get("flux_id")==2 || $this->isPageElement('SUPERMODIF', $this->getRoute());
        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'isSuperModif' => $isSuperModif,
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
            'depot_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id"),
            'modele_tournee_jour_id' => $session->get("modele_tournee_jour_id")
        ));
    }

    
    protected function initFiltreTournee($session, $request) {
        $em = $this->getDoctrine()->getManager();
        $form_request = $request->request->get('form_filtre');
        if (isset($form_request)) {
            $session->set("depot_id", $form_request['depot_id']);
            $session->set("flux_id", $form_request['flux_id']);
            $session->set("modele_tournee_id", $form_request['modele_tournee_id']);
        }

        $_modele_tournee_id = $session->get("modele_tournee_id");
        $comboTournee = $this->getComboArray($em->getRepository('AmsModeleBundle:ModeleTournee')->selectComboToutes($session->get('depot_id'), $session->get('flux_id')));
        if ((!isset($_modele_tournee_id) || $_modele_tournee_id=="") && count($comboTournee) > 0) {
            $session->set("modele_tournee_id", array_keys($comboTournee)[0]);
        }
        $form = $this->createForm(new FiltreTourneeType($session->get('DEPOTS'), $session->get('FLUXS'), $comboTournee, $session->get('depot_id'), $session->get('flux_id'), $session->get("modele_tournee_id")));
        return $form;
    }
    
    public function ajaxComboTourneeAction(Request $request) {
        $em = $this->getDoctrine();
        return $this->ajaxCombo($em->getRepository('AmsModeleBundle:ModeleTournee')->selectComboToutes($request->get('depot_id'), $request->get('flux_id')));
    }
}