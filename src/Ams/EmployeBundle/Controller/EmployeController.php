<?php

namespace Ams\EmployeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\EmployeBundle\Controller\GlobalEmployeController;
use Symfony\Component\HttpFoundation\Response;

class EmployeController extends GlobalEmployeController {
/*   
 * SUPPRIMER également le répertoire ressources/view/Employe
    public function listeAction(Request $request) {
        $em = $this->getDoctrine();
        $session = $this->get('session');
        if( $request->get('depot_id'))
            $depot_id = $request->get('depot_id');
        else
            $depot_id = $session->get("depot_id");
        
         if( $request->get('flux_id'))
            $flux_id = $request->get('flux_id');
        else
            $flux_id = $session->get("flux_id");
        
        if( $request->get('debut'))
            $date_debut = $request->get('debut');
        else
            $date_debut = $session->get("pai_date_debut");
        
       if( $request->get('fin'))
            $date_fin = $request->get('fin');
        else
            $date_fin = $session->get("pai_date_fin");

        $employes=$em->getRepository('AmsEmployeBundle:Employe')->selectComboListeEmploye($depot_id,$flux_id, $date_debut,$date_fin);
        asort($employes);
        $response = $this->renderView('AmsEmployeBundle:Employe:liste.json.twig', array(
                    'employes' => $employes,
        ));
        
        return new Response($response, 200, array('Content-Type' => 'Application/json'));
    }
*/    
    public function ajaxComboModelePlanningAction(Request $request) {
        $em = $this->getDoctrine();
        $session = $this->get('session');
        if( $request->get('depot_id'))
            $depot_id = $request->get('depot_id');
        else
            $depot_id = $session->get("depot_id");
        
         if( $request->get('flux_id'))
            $flux_id = $request->get('flux_id');
        else
            $flux_id = $session->get("flux_id");

        return $this->ajaxCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboModele($depot_id,$flux_id));
    }
    
    public function ajaxComboPaiResumeAction(Request $request) {
        $em = $this->getDoctrine();
        $session = $this->get('session');
        if( $request->get('depot_id'))
            $depot_id = $request->get('depot_id');
        else
            $depot_id = $session->get("depot_id");
        
         if( $request->get('flux_id'))
            $flux_id = $request->get('flux_id');
        else
            $flux_id = $session->get("flux_id");
        
        if( $request->get('anneemois_id'))
            $anneemois_id = $request->get('anneemois_id');
        else
            $anneemois_id = $session->get("anneemois_id");
        
        return $this->ajaxCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboPaiMois($anneemois_id, $depot_id, $flux_id));
    }
    
    public function ajaxComboPaiPlanningAction(Request $request) {
        return $this->ajaxComboPaiResumeAction($request);
    }
    
    public function ajaxComboPaiAnnexeAction(Request $request) {
        $em = $this->getDoctrine();
        $session = $this->get('session');
        if( $request->get('depot_id'))
            $depot_id = $request->get('depot_id');
        else
            $depot_id = $session->get("depot_id");
        
         if( $request->get('flux_id'))
            $flux_id = $request->get('flux_id');
        else
            $flux_id = $session->get("flux_id");
        
        if( $request->get('anneemois_id'))
            $anneemois_id = $request->get('anneemois_id');
        else
            $anneemois_id = $session->get("anneemois_id");
        
        return $this->ajaxCombo($em->getRepository('AmsEmployeBundle:Employe')->selectComboAnnexe($depot_id,$flux_id, $anneemois_id));
    }
    
}
