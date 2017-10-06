<?php

namespace Ams\SilogBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ams\DistributionBundle\Entity\DemandeArbitrage;
use Symfony\Component\HttpFoundation\Session\Session;

class MotifArbitrageController extends GlobalController {
  public function indexAction() {
  // verifie si on a droit a acceder a cette page
    $bVerifAcces = $this->verif_acces();
    if ($bVerifAcces !== true) {
      return $bVerifAcces;
    }
    return $this->render('AmsSilogBundle:Motif:index.html.twig');
  }
  
  public function gridAction() {
    /** DATA MOTIF TABLE **/
    $motif = $this->getDoctrine()
        ->getManager()
        ->getRepository('AmsDistributionBundle:DemandeArbitrage')->findBy(array('etat' => 1));
	
    $response = $this->renderView('AmsSilogBundle:Motif:grid.xml.twig', array(
            'motifs' => $motif,
    ));
    return new Response($response, 200, array('Content-Type' => 'Application/xml'));
  }
  
  public function MotifCrudAction(Request $request) {
    if(!$this->verif_acces()) return false;
    $em = $this->getDoctrine()->getManager();
    $mode = $request->get('!nativeeditor_status');
    $rowId = $request->get('gr_id');
    $newId ='';
    $action='';
    $msg='';
    $msgException='';
    $result=true;
    $session = new Session();
		
    $libelle = $request->get('c1');
    $code = $request->get('c2');
    if ($mode == 'updated') {
      if(trim($libelle) != '' && trim($code) != ''){
        $motif = $em->getRepository('AmsDistributionBundle:DemandeArbitrage')->find($request->get('c0'));
        /** UPDATE  **/
        if($motif){
          $motif->setLibelle($libelle);
          $motif->setCode($code);
          $em->flush();
        }
        /** INSERT OR UPDATE**/
        else{
          $aTmp = $session->get('MotifId');
          $aTmp = (is_array($aTmp))? $aTmp : array();
          /** UPDATE **/
          if(array_key_exists($rowId, $aTmp)){
            $motif = $em->getRepository('AmsDistributionBundle:DemandeArbitrage')->find($aTmp[$rowId]);
            $motif->setLibelle($libelle);
            $motif->setCode($code);
            $em->flush();
          }
          /** INSERT **/
          else{
            $motif = new DemandeArbitrage();
            $motif->setLibelle($libelle);
            $motif->setCode($code);
            $motif->setEtat(true);
            $em->persist($motif);
            $em->flush();
            $aTmp[$rowId] = $motif->getId();
            $session->set('MotifId', $aTmp);
          }
        }
      }
    }
    if ($mode == 'deleted') {
      $aTmp = $session->get('MotifId');
      $aTmp = (is_array($aTmp))? $aTmp : array();
      if(array_key_exists($rowId, $aTmp))
        $motif = $em->getRepository('AmsDistributionBundle:DemandeArbitrage')->find($aTmp[$rowId]);
      else
        $motif = $em->getRepository('AmsDistributionBundle:DemandeArbitrage')->find($request->get('c0'));
      
      $motif->setEtat(false);
      $em->flush();
      
    }
    if (!$result) {
        $action="error";
        $response = $this->render('::grid_action_error.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg, 'msg_complet' => $msgException));
    }
    else
        $response = $this->render('::grid_action.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg));

    $response->headers->set('Content-Type', 'text/xml');
    return $response;
	}

}
