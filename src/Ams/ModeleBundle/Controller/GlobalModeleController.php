<?php

namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;
use Ams\ModeleBundle\Form\FiltreModeleType;
use Ams\ModeleBundle\Form\FiltreDateType;
use Ams\ModeleBundle\Form\FiltreDepotFluxMoisType;

class GlobalModeleController extends GlobalController {

    protected function getBundleName() {
        return 'AmsModeleBundle';
    }

    protected function getTwigListe() {
        return $this->getRepositoryName() . ':liste.html.twig';
    }

    protected function getTwigGrid() {
        return $this->getRepositoryName() . ':grid.xml.twig';
    }

    protected function getTwigRows() {
        return $this->getRepositoryName() . ':rows.xml.twig';
    }

    public function getRepository() {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository($this->getRepositoryName());
    }

    protected function initFiltre($session, $request) {
        $form = $this->createForm(new FiltreModeleType($session->get('DEPOTS'), $session->get('FLUXS'), $session->get("depot_id"), $session->get("flux_id")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("depot_id", $form->getData()['depot_id']);
            $session->set("flux_id", $form->getData()['flux_id']);
        }
        return $form;
    }

    protected function initFiltreDateInterval($session, $request) {
        $form = $this->createForm(new FiltreDateType($session->get('DEPOTS'), $session->get('FLUXS'), $session->get("depot_id"), $session->get("flux_id"), $session->get("pai_date_debut"), $session->get("pai_date_fin")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("depot_id", $form->getData()['depot_id']);
            $session->set("flux_id", $form->getData()['flux_id']);
            $session->set("pai_date_debut", $form->getData()['date_debut']);
            $session->set("pai_date_fin", $form->getData()['date_fin']);
        }
        return $form;
    }

    protected function initFiltreDepotFluxMois($session, $request) {
        $form = $this->createForm(new FiltreDepotFluxMoisType($session->get('DEPOTS'), $session->get('FLUXS'), $session->get('ANNEEMOIS'), $session->get("depot_id"), $session->get("flux_id"), $session->get("anneemois_id")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("depot_id", $form->getData()['depot_id']);
            $session->set("flux_id", $form->getData()['flux_id']);
            $session->set("anneemois_id", $form->getData()['anneemois_id']);
        }
        return $form;
    }

    protected function saveUrl2Session($session, $request, $selectedId) {
        if ($request->getMethod() == 'GET') {
            $_depot_id = $request->query->get('depot_id');
            if (isset($_depot_id)) {
                $session->set('depot_id', $request->query->get('depot_id'));
                $session->set('flux_id', $request->query->get('flux_id'));
            }
            $_anneemois_id = $request->query->get('anneemois_id');
            if (isset($_anneemois_id)) {
                $session->set("anneemois_id", $_anneemois_id);
            }
            if ($selectedId != '') {
                $_selectedId = $request->query->get($selectedId);
                if (isset($_selectedId)) {
                    $session->set($selectedId, $_selectedId);
                }
            }
        }
    }

    protected function getCombo($curseur, $withBlanck = false) {
        if ($withBlanck) {
            $combo = '<option value =""></option>';
        } else {
            $combo = '';
        }
        if (isset($curseur)) {
            foreach ($curseur as $row) {
                $combo .= '<option value ="' . $row['id'] . '"><![CDATA[' . $row['libelle'] . ']]></option>';
            }
        }
        return $combo;
    }

    public function getComboHtml($curseur, $withBlanck = false) {
        if ($withBlanck) {
            $combo = '<option value =""></option>';
        } else {
            $combo = '';
        }
        if (isset($curseur)) {
            foreach ($curseur as $row) {
                $combo .= '<option value ="' . $row['id'] . '">' . $row['libelle'] . '</option>';
            }
        }
        return $combo;
    }

    // Transforme des enregistrements (id,libelle) en tableau array[id]=libelle
    protected function getComboArray($curseur, $withBlanck = false) {
        $liste = array();
        if ($withBlanck) {
            $liste[""] = "";
        }
        if (isset($curseur)) {
            foreach ($curseur as $row) {
                $liste[$row["id"]] = $row["libelle"];
            }
        }
        return $liste;
    }

    // Transforme des enregistrements (id,libelle) en tableau array[id]=libelle
    // ajoute un espace devant l'id pour garder l'ordre
    protected function getComboAjax($curseur, $withBlanck = false) {
        $liste = array();
        if ($withBlanck) {
            $liste[" "] = "";
        }
        foreach ($curseur as $row) {
            $liste[' ' . $row["id"]] = $row["libelle"];
        }
        return $liste;
    }

    protected function getIsModif() {
        return $this->isPageElement('MODIF', $this->getRoute());
    }

    public function ajaxCombo($curseur) {
        $response = $this->renderView('::ajax_combo.json.twig', array(
            'combo' => $this->getComboAjax($curseur),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/json'));
    }

    public function ajaxMsg($msg) {
        $response = $this->renderView('::ajax_msg.html.twig', array('msg' => $msg));
        return new Response($response, 200, array('Content-Type' => 'text/html'));
    }

    public function ajaxResponse($msg, $msgException) {
        /*            $response = $this->render('::ajax_error.html.twig', array('msg' => 'test', 'msg_complet' => 'test'));
          $response->setStatusCode(500);
          $response->headers->set('Content-Type', 'text/xml');
          return $response; */
        if ($msg != '') {
            $response = $this->render('::ajax_error.html.twig', array('msg' => $msg, 'msg_complet' => $msgException));
            $response->setStatusCode(500);
            $response->headers->set('Content-Type', 'text/xml');
            return $response;
        } else {
            return new Response(null, 200, array('Content-Type' => 'text/txt'));
        }
    }

}
