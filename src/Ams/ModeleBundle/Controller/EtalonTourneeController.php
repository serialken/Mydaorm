<?php

namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ams\ModeleBundle\Controller\GlobalModeleController;
use Ams\ModeleBundle\Form\FiltreEtalonType;
use Ams\ModeleBundle\Form\FiltreEtalonRechercheType;

class EtalonTourneeController extends GlobalModeleController {

    public function getRepositoryName() { return $this->getBundleName() . ':EtalonTournee'; }
    public function getRoute() { return 'liste_etalon_tournee'; }

    public function gridAction() {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $_etalon=$em->getRepository('AmsModeleBundle:Etalon')->selectOne($session->get("etalon_id"));
    
        $isSoumettre = $this->isPageElement('MODIF', $this->getRoute()) && isset($_etalon) && is_null($_etalon["date_demande"]);
        $isValider = $this->isPageElement('VALID', $this->getRoute()) && isset($_etalon) && !is_null($_etalon["date_demande"]) && is_null($_etalon["date_validation"]) && is_null($_etalon["date_refus"]);
   
        $response = $this->renderView($this->getTwigGrid(), array(
            'isModif' => !isset($_etalon) || $isSoumettre  || $isValider,
            'curseur' => $this->getRepository()->select($session->get("etalon_id")),
            'comboGroupe' => $this->getCombo($em->getRepository('AmsPaieBundle:PaiTournee')->selectComboGroupeDate($session->get("depot_id"), $session->get("flux_id"), $session->get("date_distrib"))),
            'comboTournee' => $this->getCombo($em->getRepository('AmsModeleBundle:ModeleTournee')->selectComboModele($session->get("depot_id"), $session->get("flux_id")), true),
            'comboEmploye' => $this->getCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboEtalonTournee($session->get("depot_id"), $session->get("flux_id"),$session->get("etalon_recherche_date_debut"),$session->get("etalon_recherche_date_fin")), true),
            'comboTransport' => $this->getCombo($em->getRepository('AmsReferentielBundle:RefTransport')->selectCombo(), true),
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

        $response = $this->renderView($this->getTwigRows(), array(
            'isModif' => $this->getIsModif(),
            'curseur' => $this->getRepository()->select($session->get("etalon_id")),
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

        $this->saveUrl2Session($session, $request, 'etalon_id');
        $form = $this->initFiltreEtalon($session, $request);
        $form2 = $this->initFiltreEtalonRecherche($session, $request, $_etalon, $_date_application, $_min_date_application);

        $isSoumettre = $this->isPageElement('MODIF', $this->getRoute()) && is_null($_etalon["date_demande"]);
        $isValider = $this->isPageElement('VALID', $this->getRoute()) && !is_null($_etalon["date_demande"]) && is_null($_etalon["date_validation"]) && is_null($_etalon["date_refus"]);
        return $this->render($this->getTwigListe(), array(
            'isModif' => !isset($_etalon) || $isSoumettre  || $isValider,
            'isSoumettre' => $isSoumettre,
            'isValider' => $isValider,
            'form' => $form->createView(),
            'form2' => $form2->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'repository' => $this->getRepositoryName(),
            'commentaire' => $_etalon["commentaire"],
            'date_requete' => $_etalon["date_requete"],
            'date_application' => $_date_application,
            'min_date_application' => $_min_date_application,
            'type_id' => $_etalon["type_id"],
            'employe_id' => $_etalon["employe_id"],
            'cycle' => $_etalon["cycle"],
            'comboType' => $this->getComboHtml($em->getRepository('AmsReferentielBundle:RefTypeEtalon')->selectCombo()),
            'comboEmploye' => $this->getComboHtml($em->getRepository('AmsEmployeBundle:Employe')->selectComboEtalonEmploye($session->get("depot_id"), $session->get("flux_id"),$_date_application)),
//                    'msg' => $em->getRepository('AmsModeleBundle:EtalonTournee')->validation($session->get("etalon_id")),
        ));
    }    
    
    protected function initFiltreEtalon($session, $request) {
        $em = $this->getDoctrine()->getManager();
        $form_request = $request->request->get('form_filtre');
        if (isset($form_request)) {
            $session->set("depot_id", $form_request['depot_id']);
            $session->set("flux_id", $form_request['flux_id']);
            if (isset($form_request['etalon_id'])) {
                $session->set("etalon_id", $form_request['etalon_id']);
            } else {
                $session->remove('etalon_id');
            }
        }

        $_etalon_id = $session->get("etalon_id");
        $comboEtalon = $this->getComboArray($em->getRepository('AmsModeleBundle:Etalon')->selectCombo($session->get('depot_id'), $session->get('flux_id'), $_etalon_id));
        if (!isset($_etalon_id)) {
            if (count($comboEtalon) > 0) {
                $session->set("etalon_id", array_keys($comboEtalon)[0]);
            } else {
                $session->set("etalon_id", 0);            
            }
        }
        $form = $this->createForm(new FiltreEtalonType($session->get('DEPOTS'), $session->get('FLUXS'), $comboEtalon, $session->get('depot_id'), $session->get('flux_id'), $session->get("etalon_id")));
        return $form;
    }
    
    protected function initFiltreEtalonRecherche($session, $request, &$_etalon, &$_date_application, &$_min_date_application) {
        $em = $this->getDoctrine()->getManager();

        $session->set("etalon_recherche_date_debut",$session->get("etalon_date_debut"));
        $session->set("etalon_recherche_date_fin",$session->get("etalon_date_fin"));
        $session->set("etalon_recherche_tournee_id","");

        $_etalon_id = $session->get('etalon_id');
        if (isset($_etalon_id) &&  $_etalon_id!=0) {
            $_etalon=$em->getRepository('AmsModeleBundle:Etalon')->selectOne($session->get("etalon_id"));
            $session->set("depot_id",$_etalon["depot_id"]);
            $session->set("flux_id",$_etalon["flux_id"]);
            $_date_application = $_etalon["date_application"];
            $_min_date_application = $_etalon["min_date_application"];

            $session->set("etalon_recherche_employe_id", $_etalon["employe_id"]);
        } else {
            $session->set("etalon_recherche_employe_id", "");
            $_date_application = $em->getRepository('AmsPaieBundle:PaiMois')->getDateDebut($session->get("flux_id"));
            $_min_date_application = $_date_application;
        }

        $comboEmploye = $this->getComboArray($em->getRepository('AmsEmployeBundle:Employe')->selectComboEtalonTournee($session->get("depot_id"), $session->get("flux_id"),$session->get("etalon_recherche_date_debut"),$session->get("etalon_recherche_date_fin")), true);
        $comboTournee = $this->getComboArray($em->getRepository('AmsModeleBundle:ModeleTournee')->selectComboModele($session->get("depot_id"), $session->get("flux_id")),true);
        $form = $this->createForm(new FiltreEtalonRechercheType($comboEmploye, $comboTournee, $session->get("etalon_recherche_date_debut"), $session->get("etalon_recherche_date_fin"), $session->get("etalon_recherche_employe_id"), $session->get("etalon_recherche_tournee_id")));
        return $form;
    }
    
    public function ajaxMsgAction(Request $request) { 
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $msg = $em->getRepository('AmsModeleBundle:EtalonTournee')->validation($session->get("etalon_id"));
        return $this->ajaxMsg($msg);
    }

    public function ajaxRechercherAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        
        $response = $this->forward('AmsModeleBundle:Etalon:ajaxSauvegarder',array('request' => $request));

        $session->set("etalon_recherche_employe_id",$request->query->get('recherche_employe_id'));
        $session->set("etalon_recherche_tournee_id",$request->query->get('recherche_tournee_id'));
        $session->set("etalon_recherche_date_debut",$request->query->get('recherche_date_debut'));
        $session->set("etalon_recherche_date_fin",$request->query->get('recherche_date_fin'));
        $em->getRepository('AmsModeleBundle:EtalonTournee')->insert($msg, $msgException,  array(
                    "etalon_id" => $session->get("etalon_id"),
                    "employe_id" => $request->query->get('recherche_employe_id'),
                    "tournee_id" => $request->query->get('recherche_tournee_id'),
                    "date_debut" => $request->query->get('recherche_date_debut'),
                    "date_fin" => $request->query->get('recherche_date_fin'),
                    "ajout" => $request->query->get('ajout')
                    ), $session->get('UTILISATEUR_ID'), $id);
        return $this->ajaxResponse($msg, $msgException);
    }
    
    public function ajaxSoumettreAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        if ($em->getRepository('AmsModeleBundle:Etalon')->updateDemande($msg, $msgException,array(
                    "etalon_id" => $session->get("etalon_id"),
                    "type_id" => $request->query->get('type_id'),
                    "employe_id" => $request->query->get('employe_id'),
                    "commentaire" => $request->query->get('commentaire'),
                    "date_application" => $request->query->get('date_application'),
                    "date_requete" => $request->query->get('date_requete'),
                    ),$session->get("UTILISATEUR_ID"),$session->get("etalon_id"))) {
            $sCmd = 'php '.$this->get('kernel')->getRootDir(). '/console '
                .'sendmail_etalon_demande '
                .' --env=' . $this->get('kernel')->getEnvironment()
                .' --etalon_id='.$session->get("etalon_id");
            $this->bgCommandProxy($sCmd);
        }
        return $this->ajaxResponse($msg, $msgException);
    }
        
    public function ajaxValiderAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        if ($em->getRepository('AmsModeleBundle:Etalon')->updateValidation($msg, $msgException,array(
                    "etalon_id" => $session->get("etalon_id"),
                    "type_id" => $request->query->get('type_id'),
                    "employe_id" => $request->query->get('employe_id'),
                    "commentaire" => $request->query->get('commentaire'),
                    "date_application" => $request->query->get('date_application'),
                    "date_requete" => $request->query->get('date_requete'),
                    ),$session->get("UTILISATEUR_ID"),$session->get("etalon_id"))
            && $em->getRepository('AmsModeleBundle:Etalon')->transfert($msg, $msgException,$session->get("UTILISATEUR_ID"),$session->get("etalon_id"),$idtrt)) {
            $sCmd = 'php '.$this->get('kernel')->getRootDir(). '/console '
                .'sendmail_etalon_validation '
                .' --env=' . $this->get('kernel')->getEnvironment()
                .' --etalon_id='.$session->get("etalon_id");
            $this->bgCommandProxy($sCmd);
        }
        return $this->ajaxResponse($msg, $msgException);
    }
    
    public function ajaxRefuserAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        if ($em->getRepository('AmsModeleBundle:Etalon')->updateRefus($msg, $msgException,array(
                    "etalon_id" => $session->get("etalon_id"),
                    "type_id" => $request->query->get('type_id'),
                    "employe_id" => $request->query->get('employe_id'),
                    "commentaire" => $request->query->get('commentaire'),
                    "date_application" => $request->query->get('date_application'),
                    "date_requete" => $request->query->get('date_requete'),
                    ),$session->get("UTILISATEUR_ID"),$session->get("etalon_id"))) {
            $sCmd = 'php '.$this->get('kernel')->getRootDir(). '/console '
                .'sendmail_etalon_refus '
                .' --env=' . $this->get('kernel')->getEnvironment()
                .' --etalon_id='.$session->get("etalon_id");
            $this->bgCommandProxy($sCmd);
        }
        return $this->ajaxResponse($msg, $msgException);
    }
    
    public function ajaxComboEtalonAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
    
        return $this->ajaxCombo($em->getRepository('AmsModeleBundle:Etalon')->selectCombo($request->get('depot_id'), $request->get('flux_id'),$session->get("etalon_id")));        
    }
    
    public function ajaxComboEmployeAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
    
        return $this->ajaxCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboEtalonEmploye($request->get('depot_id'), $request->get('flux_id'),$request->get('date_application')));        
    }
}