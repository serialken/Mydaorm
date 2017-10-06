<?php
namespace Ams\PaieBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ams\PaieBundle\Controller\GlobalPaiController;

class PaiPlanningController extends GlobalPaiController {
    
    public function planningAction(Request $request){
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) { return $bVerifAcces; }
        
        $this->setDerniere_page();
        
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
 
        $this->saveUrl2Session($session,$request,'employe_id');
        $form = $this->initFiltreEmploye($session,$request);
        
        return $this->render('AmsPaieBundle:PaiPlanning:planning.html.twig',array(
            'titre' => $this->titre_page,
            'form' => $form->createView(),
            'defaultDate' => substr($session->get("anneemois_id"),0,4).'-'.substr($session->get("anneemois_id"),-2).'-01'
        ));
    }

    public function ajaxTourneeAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository('AmsPaieBundle:PaiTournee')->selectPlanning($session->get("employe_id"), $request->get("start"), $request->get("end"));
        return $this->ajaxCalendar($data);
    }

    public function ajaxActiviteAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository('AmsPaieBundle:PaiActivite')->selectPlanning($session->get("employe_id"), $request->get("start"), $request->get("end"));
        return $this->ajaxCalendar($data);
    }

    public function ajaxCycleAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository('AmsEmployeBundle:EmpCycle')->selectPlanning($session->get("depot_id"),$session->get("flux_id"),$session->get("employe_id"), $request->get("start"), $request->get("end"));
        return $this->ajaxCalendar($data);
    }
    public function ajaxFerieAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository('AmsPaieBundle:PaiRefFerie')->selectPlanning($session->get("employe_id"), $request->get("start"), $request->get("end"));
        return $this->ajaxCalendar($data);
    }
    public function ajaxAbsenceAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository('AmsEmployeBundle:Employe')->selectAbsence($session->get("employe_id"), $request->get("start"), $request->get("end"));
        return $this->ajaxCalendar($data);
    }

}