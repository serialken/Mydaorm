<?php

namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\ModeleBundle\Controller\GlobalModeleController;

class ModeleActiviteController extends GlobalModeleController {
    protected $est_hors_presse=false;

    public function getRepositoryName() {
        return $this->getBundleName() . ':ModeleActivite';
    }
    public function getRoute() {
        return 'liste_modele_activite';
    }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"),$this->est_hors_presse),
            'comboJour' => $em->getRepository('AmsReferentielBundle:RefJour')->selectCombo(),
            'comboActivite' => $em->getRepository('AmsReferentielBundle:RefActivite')->selectComboModele($this->est_hors_presse),
            'comboEmploye' => $em->getRepository('AmsEmployeBundle:Employe')->selectComboModeleActivite($session->get("depot_id"), $session->get("flux_id")),
            'comboTransport' => $em->getRepository('AmsReferentielBundle:RefTransport')->selectCombo(),
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
        if ($param["old_employe_id"] == '' && $param["employe_id"]=='') {
            $sqlCondition = "AND (ma.id=".$newId.")";
        } else if ($param["old_employe_id"] == $param["employe_id"]) {
            $sqlCondition = "AND (ma.employe_id=".$param['employe_id'].")";
        } else if ($param["employe_id"] == ''){
            $sqlCondition = "AND (ma.id=".$newId." OR ma.employe_id=".$param["old_employe_id"].")";
        } else if ($param["old_employe_id"] == ''){
            $sqlCondition = "AND (ma.employe_id=".$param["employe_id"].")";
        } else {
            $sqlCondition = "AND (ma.employe_id=".$param['employe_id']." OR ma.employe_id=".$param["old_employe_id"].")";
        }
        // $sqlCondition .= " AND ma.jour_id=".$param['jour_id']; Faux car on peux changer de jour_id
        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("depot_id"), $session->get("flux_id"), $this->est_hors_presse, $sqlCondition),
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

        $this->saveUrl2Session($session, $request, 'modele_activite_id');
        $form = $this->initFiltre($session, $request);
        return $this->render($this->getTwigListe(), array(
            'isModif' => $this->getIsModif(),
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
            'depot_id' => $session->get("depot_id"),
            'flux_id' => $session->get("flux_id"),
            'modele_activite_id' => $session->get("modele_activite_id")
        ));
    }

}
