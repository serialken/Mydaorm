<?php

namespace Ams\PaieBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;
use Ams\PaieBundle\Form\FiltreAnnexeTourneeType;
use Ams\PaieBundle\Form\FiltreAnnexeType;
use Ams\PaieBundle\Form\FiltreDateType;
use Ams\PaieBundle\Form\FiltreDepotFluxMoisType;
use Ams\PaieBundle\Form\FiltreFluxMoisType;
use Ams\PaieBundle\Form\FiltreFluxType;
use Ams\PaieBundle\Form\FiltreDepotFluxType;
use Ams\PaieBundle\Form\FiltreEmployeType;
use Ams\PaieBundle\Form\FiltreMoisType;
use Ams\PaieBundle\Form\FiltrePaiType;
use Ams\PaieBundle\Form\FiltreInterfaceType;

class GlobalPaiController extends GlobalController {

    protected function getBundleName() {
        return 'AmsPaieBundle';
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
        $form = $this->createForm(new FiltrePaiType($session->get('DEPOTS'), $session->get('FLUXS'), $session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("depot_id", $form->getData()['depot_id']);
            $session->set("flux_id", $form->getData()['flux_id']);
            $session->set("date_distrib", $form->getData()['date_distrib']);
        }
        return $form;
    }

    protected function initFiltreMois($session, $request) {
        $form = $this->createForm(new FiltreMoisType($session->get('ANNEEMOIS'), $session->get("anneemois_id")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("anneemois_id", $form->getData()['anneemois_id']);
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

    protected function initFiltreFlux($session, $request) {
        $form = $this->createForm(new FiltreFluxType($session->get('FLUXS'), $session->get("flux_id")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("flux_id", $form->getData()['flux_id']);
        }
        return $form;
    }

    protected function initFiltreFluxMois($session, $request) {
        $form = $this->createForm(new FiltreFluxMoisType($session->get('FLUXS'), $session->get('ANNEEMOIS'), $session->get("flux_id"), $session->get("anneemois_id")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("flux_id", $form->getData()['flux_id']);
            $session->set("anneemois_id", $form->getData()['anneemois_id']);
        }
        return $form;
    }

    protected function initFiltreDepotFlux($session, $request) {
        $form = $this->createForm(new FiltreDepotFluxType($session->get('DEPOTS'), $session->get('FLUXS'), $session->get("depot_id"), $session->get("flux_id")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("depot_id", $form->getData()['depot_id']);
            $session->set("flux_id", $form->getData()['flux_id']);
        }
        return $form;
    }

    protected function initFiltreEmploye($session, $request) {

        $em = $this->getDoctrine()->getManager();
        $form_request = $request->request->get('form_filtre');
        if (isset($form_request['employe_id'])) {
            $depot_id = $form_request['depot_id'];
            $flux_id = $form_request['flux_id'];
            $anneemois_id = $form_request['anneemois_id'];
            $employe_id = $form_request['employe_id'];
        } else {
            $depot_id = $session->get('depot_id');
            $flux_id = $session->get('flux_id');
            $anneemois_id = $session->get('anneemois_id');
            $employe_id = $session->get("employe_id");
        }

        $comboEmploye = $this->getComboArray($em->getRepository('AmsEmployeBundle:Employe')->selectComboPaiMois($anneemois_id, $depot_id, $flux_id));
        if (!isset($employe_id) & count($comboEmploye) > 0) {
            $employe_id = array_keys($comboEmploye)[0];
            $session->set("employe_id", $employe_id);
        }

        $form = $this->createForm(new FiltreEmployeType($session->get('DEPOTS'), $session->get('FLUXS'), $session->get('ANNEEMOIS'), $comboEmploye, $depot_id, $flux_id, $anneemois_id, $employe_id));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set('depot_id', $form->getData()['depot_id']);
            $session->set('flux_id', $form->getData()['flux_id']);
            $session->set("anneemois_id", $form->getData()['anneemois_id']);
            $session->set("employe_id", $form->getData()['employe_id']);
        }
        return $form;
    }

    protected function initFiltreAnnexe($session, $request) {
        $em = $this->getDoctrine()->getManager();
        $form_request = $request->request->get('form_filtre');
        if (isset($form_request['employe_depot_hst_id'])) {
            $depot_id = $form_request['depot_id'];
            $flux_id = $form_request['flux_id'];
            $anneemois_id = $form_request['anneemois_id'];
            $employe_depot_hst_id = $form_request['employe_depot_hst_id'];
        } else {
            $depot_id = $session->get('depot_id');
            $flux_id = $session->get('flux_id');
            $anneemois_id = $session->get('anneemois_id');
            $employe_depot_hst_id = $session->get("employe_depot_hst_id");
        }

        $comboEmploye = $this->getComboArray($em->getRepository('AmsEmployeBundle:Employe')->selectComboAnnexe($depot_id, $flux_id, $anneemois_id));
        if (!isset($employe_depot_hst_id) & count($comboEmploye) > 0) {
            $employe_depot_hst_id = array_keys($comboEmploye)[0];
            $session->set("employe_depot_hst_id", $employe_depot_hst_id);
        }

        $form = $this->createForm(new FiltreAnnexeType($session->get('DEPOTS'), $session->get('FLUXS'), $session->get('ANNEEMOIS'), $comboEmploye, $depot_id, $flux_id, $anneemois_id, $employe_depot_hst_id));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set('depot_id', $form->getData()['depot_id']);
            $session->set('flux_id', $form->getData()['flux_id']);
            $session->set("anneemois_id", $form->getData()['anneemois_id']);
//            $session->set("employe_depot_hst_id", $form->getData()['employe_depot_hst_id']);
            if (isset($_POST['form_filtre']['employe_depot_hst_id'])) {
                $session->set("employe_depot_hst_id", $form->getData()['employe_depot_hst_id']);
            } else {
                $session->set("employe_depot_hst_id", 0);
            }
        }
        return $form;
    }

    protected function initFiltreDate($session, $request) {
        $form = $this->createForm(new FiltreDateType($session->get("date_distrib")));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("date_distrib", $form->getData()['date_distrib']);
        }
        return $form;
    }

    protected function initFiltreInterface($session, $request) {
        $em = $this->getDoctrine()->getManager();
        $form_request = $request->request->get('form_filtre');
        if (isset($form_request['idtrt'])) {
            $idtrt = $form_request['idtrt'];
        } else {
            $idtrt = $session->get('idtrt');
        }
        $comboInterface = $this->getComboArray($em->getRepository('AmsPaieBundle:PaiIntTraitement')->selectCombo($session->get("anneemois_id")));

        if (!isset($idtrt) & count($comboInterface) > 0) {
            $idtrt = array_keys($comboInterface)[0];
            $session->set("idtrt", $idtrt);
        }

        $form = $this->createForm(new FiltreInterfaceType($session->get('ANNEEMOIS'), $comboInterface, $session->get("anneemois_id"), $idtrt));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            $session->set("anneemois_id", $form->getData()['anneemois_id']);
            if (isset($_POST['form_filtre']['idtrt']))
                $session->set("idtrt", $_POST['form_filtre']['idtrt']);
            else
                $session->set("idtrt", 0);
        }
        return $form;
    }

    protected function saveUrl2Session($session, $request, $selectedId = '') {
        if ($request->getMethod() == 'GET') {
            $_depot_id = $request->query->get('depot_id');
            if (isset($_depot_id)) {
                $session->set('depot_id', $request->query->get('depot_id'));
                $session->set('flux_id', $request->query->get('flux_id'));
            }
            $_date_distrib = $request->query->get('date_distrib');
            if (isset($_date_distrib)) {
                $session->set("date_distrib", $_date_distrib);
            }
            $_anneemois_id = $request->query->get('anneemois_id');
            if (isset($_anneemois_id)) {
                $session->set("anneemois_id", $_anneemois_id);
            }
            $_employe_id = $request->query->get('employe_id');
            if (isset($_employe_id)) {
                $session->set("employe_id", $_employe_id);
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

    public function getComboHtmlFromArray($curseur, $withBlanck = false) {
        if ($withBlanck) {
            $combo = '<option value =""></option>';
        } else {
            $combo = '';
        }
        if (isset($curseur)) {
            foreach ($curseur as $key => $value) {
                $combo .= '<option value ="' . $key . '">' . $value . '</option>';
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
        /*      Ne MARCHE PAS : pourquoi ???
          foreach ($curseur as $id => $libelle) {
          $liste[' '.$id] = $libelle;
          } */
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
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $isModif = $this->isPageElement('MODIF', $this->getRoute());
        $isBloque = !is_null($em->getRepository('AmsPaieBundle:PaiMois')->getBlocage($session->get("flux_id")));
        $isJourOk = ($session->get('date_distrib') >= date('Y-m-d'));
        $isDateInMoisCourant = $em->getRepository('AmsPaieBundle:PaiMois')->isDateInMoisCourant($session->get('date_distrib'), $session->get("flux_id"));
        $isPrivilegieMois = $this->isPageElement('MOIS', 'pai_privilegie');
        $isPrivilegiePaie = $this->isPageElement('PAIE', 'pai_privilegie');
        $isPrivilegieRetro = $this->isPageElement('RETRO', 'pai_privilegie');
        return $isModif && (($isDateInMoisCourant == -1 && $isPrivilegieRetro) // Sur les mois précédent le mois de paie
                || ($isDateInMoisCourant == 0 && ($isPrivilegiePaie || $isPrivilegieMois && !$isBloque || !$isBloque && $isJourOk)) || ($isDateInMoisCourant == 1 && ($isPrivilegiePaie || $isPrivilegieMois || $isJourOk))
                );
    }

    protected function getIsAlim($route) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $isBloque = !is_null($em->getRepository('AmsPaieBundle:PaiMois')->getBlocage($session->get("flux_id")));
        $isJourOk = ($session->get('date_distrib') >= date('Y-m-d'));
        $isDateInMoisCourant = $em->getRepository('AmsPaieBundle:PaiMois')->isDateInMoisCourant($session->get('date_distrib'), $session->get("flux_id"));
        $isPrivilegieJour = $this->isPageElement('ALIM_JOUR', $route);
        $isPrivilegieMois = $this->isPageElement('ALIM_MOIS', $route);
        $isPrivilegiePaie = $this->isPageElement('PAIE', 'pai_privilegie');
        $isPrivilegieRetro = $this->isPageElement('RETRO', 'pai_privilegie');
        return ($isDateInMoisCourant == -1 && $isPrivilegieMois && $isPrivilegieRetro // Sur les mois précédent le mois de paie
                || $isDateInMoisCourant == 0 && ($isPrivilegieMois && ($isPrivilegiePaie || !$isBloque) || $isPrivilegieJour && !$isBloque && $isJourOk)) || $isDateInMoisCourant == 1 && ($isPrivilegieMois || $isPrivilegieJour && $isJourOk)
        ;
    }

    /**
     * Addition tableau de time  
     * @param type $times tableau de time 00:00:00
     * @return time 00:00:00
     */
    public function addTime($times) {
        $secs = array();
        $hours = array();
        $mns = array();
        foreach ($times as $time) {
            $tmp = explode(':', $time);
            $hours[] += $tmp[0];
            $mns[] += $tmp[1];
            $secs[] += $tmp[2];
        }
        $total = array_sum($hours) * 3600 + array_sum($mns) * 60 + array_sum($secs);
        $h = floor($total / 3600);
        $m = floor(($total % 3600) / 60);
        $s = $total - $h * 3600 - $m * 60;
        if ($h < 10)
            $h = '0' . $h;
        if ($m < 10)
            $m = '0' . $m;
        if ($s < 10)
            $s = '0' . $s;
        return $h . ":" . $m . ":" . $s;
    }

    public function ajaxCombo($curseur) {
        $response = $this->renderView('::ajax_combo.json.twig', array(
            'combo' => $this->getComboAjax($curseur),
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/json'));
    }

    public function ajaxCalendar($curseur) {
        $response = $this->renderView('::ajax_combo.json.twig', array(
            'combo' => $curseur,
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

    public function ajaxSaveSessionAction(Request $request) {
        $session = $this->get('session');
        $session->set($request->get("name"), $request->get("value"));
        return new Response(null, 200, array('Content-Type' => 'text/txt'));
    }

    protected function downloadFile($dir, $filename) {
        $file = $dir . '/' . $filename;
        $content = file_get_contents($file);
        $response = new Response();

        //set headers
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename);

        $response->setContent($content);
        return $response;
    }

}
