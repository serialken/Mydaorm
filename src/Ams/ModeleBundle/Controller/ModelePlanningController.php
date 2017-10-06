<?php

namespace Ams\ModeleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Ams\ModeleBundle\Controller\GlobalModeleController;
use Ams\ModeleBundle\Form\FiltrePlanningType;

class ModelePlanningController extends GlobalModeleController {
    
    
    public function planningAction(Request $request){
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        
        $this->setDerniere_page();
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $this->saveUrl2Session($session,$request,'employe_id');
        $form = $this->initFiltrePlanning($session,$request);
        
        $tournees = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->selectPlanning($session->get("depot_id"),$session->get("flux_id"),$session->get("employe_id"));
        $activites = $em->getRepository('AmsModeleBundle:ModeleActivite')->selectPlanning($session->get("depot_id"),$session->get("flux_id"),$session->get("employe_id"));
        $cycles = $em->getRepository('AmsEmployeBundle:EmpCycle')->getCurrent($session->get("employe_id"));
        
        // Préparation des jours de la semaine actif pour le calendrier
        $cyclesJson = isset($cycles[0]) ? json_encode(array_values($cycles[0])) : json_encode(array());
                
        return $this->render('AmsModeleBundle:ModelePlanning:planning.html.twig',array(
            'form' => $form->createView(),
            'titre' => $this->titre_page,
            'route' => $this->page_courante_route,
            'tournees' => $tournees,
            'activites' => $activites,
            'cycles' => $cyclesJson,
        ));
    }

    protected function initFiltrePlanning($session, $request) {
        $em = $this->getDoctrine()->getManager();
        $comboEmploye = $this->getComboArray($em->getRepository('AmsEmployeBundle:Employe')->selectComboModele($session->get("depot_id"), $session->get("flux_id")));
        $_employe_id = $session->get("employe_id");
        if (!isset($_employe_id) & count($comboEmploye) > 0) {
            $_employe_id = array_keys($comboEmploye)[0];
            $session->set("employe_id", $_employe_id);
        }
        $form = $this->createForm(new FiltrePlanningType($session->get('DEPOTS'), $session->get('FLUXS'), $comboEmploye, $session->get("depot_id"), $session->get("flux_id"), $session->get("employe_id")));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $session->set("depot_id", $form->get('depot_id')->getData());
                $session->set("flux_id", $form->get('flux_id')->getData());
                $session->set("employe_id", $form->get('employe_id')->getData());
            }
        }
        return $form;
    }
}