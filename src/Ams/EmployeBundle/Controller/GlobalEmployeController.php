<?php

namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;
use Ams\EmployeBundle\Form\FiltreDepotFluxMoisType;
use Ams\EmployeBundle\Form\FiltreNomPrenom;

class GlobalEmployeController extends GlobalController {

    protected function getBundleName() {
        return 'AmsEmployeBundle';
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

    protected function initFiltreNomPrenom($session, $request) {
        $form = $this->createForm(new FiltreNomPrenom($session->get("employe_nom"), $session->get("employe_prenom")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("employe_nom", $form->getData()['employe_nom']);
            $session->set("employe_prenom", $form->getData()['employe_prenom']);
        }
        return $form;
    }

    protected function saveUrl2Session($session, $request, $selectedId) {
        $em = $this->getDoctrine()->getManager();

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
            $_employe_nom = $request->query->get('employe_nom');
            if (isset($_employe_nom)) {
                $session->set('employe_nom', $request->query->get('employe_nom'));
                $session->set('employe_prenom', $request->query->get('employe_prenom'));
            }
            $_employe_id = $request->query->get('employe_id');
            if (isset($_employe_id)) {
                $employe = $em->getRepository('AmsEmployeBundle:Employe')->getById($_employe_id);

                $session->set('employe_nom', isset($employe) ? $employe['nom'] : "");
                $session->set('employe_prenom', isset($employe) ? $employe['prenom1'] : "");
            }
            if ($selectedId != '') {
                $_selectedId = $request->query->get($selectedId);
                if (isset($_selectedId)) {
                    $session->set($selectedId, $_selectedId);
                }
            }
        }
    }

    public function getCombo($curseur, $withBlanck = false) {
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

    /*    public function getComboXml($curseur,$withBlanck=false) {
      $combo='<?xml version="1.0" ?><complete>';
      if ($withBlanck){
      $combo.='<option value =""></option>';
      }else{
      $combo.='';
      }
      foreach($curseur as $row){
      $combo.='<option value ="'.$row['id'].'"><'.$row['libelle'].'></option>';
      }
      $combo.='</complete>';
      return $combo;
      } */

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
        if (isset($curseur)) {
            foreach ($curseur as $row) {
                $liste[' ' . $row["id"]] = $row["libelle"];
            }
        }
        return $liste;
    }

    protected function getIsActu($route) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $isPrivilegie = $this->isPageElement('ACTU', $route);
        $isAnneemoisInMoisCourant = $em->getRepository('AmsPaieBundle:PaiMois')->isAnnemoisInMoisCourant($session->get('anneemois_id'), $session->get("flux_id"));
        return ($isPrivilegie && $isAnneemoisInMoisCourant >= 0);
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
