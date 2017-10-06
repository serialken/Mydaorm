<?php

namespace Ams\SilogBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ams\DistributionBundle\Entity\Imprimante;
use Symfony\Component\HttpFoundation\Session\Session;

class ImprimanteController extends GlobalController {
  public function indexAction() {
  // verifie si on a droit a acceder a cette page
    $bVerifAcces = $this->verif_acces();
    if ($bVerifAcces !== true) {
      return $bVerifAcces;
    }
    return $this->render('AmsSilogBundle:Imprimante:index.html.twig');
  }
  
  public function gridAction() {
    /** DATA MOTIF TABLE **/
    $imprimante = $this->getDoctrine()
        ->getManager()
        ->getRepository('AmsDistributionBundle:Imprimante')->findBy(array('etat' => 1));
    

    $session = new Session();
    $depots = $session->get('DEPOTS');
		
    $sDepot ='';
    foreach($depots as $key=>$depot){
      $sDepot .='<option value="'.$key.'"> '.$depot.'</option>';
    }
        
    $response = $this->renderView('AmsSilogBundle:Imprimante:grid.xml.twig', array(
            'imprimantes' => $imprimante,
            'listeDepot' => $sDepot,
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
		
    $depot_id = $request->get('c1');
    $libelle = $request->get('c2');
    $ip = $request->get('c3');
    
    if ($mode == 'updated') {
      if(trim($libelle) != '' && trim($depot_id) != '' && trim($ip) != ''){
        $imprimante = $em->getRepository('AmsDistributionBundle:Imprimante')->find($request->get('c0'));
        /** UPDATE  **/
        if($imprimante){
          $depot = $em->getRepository('AmsSilogBundle:Depot')->find($depot_id);
          $imprimante->setDepotId($depot);
          $imprimante->setLibelleImprimante($libelle);
          $imprimante->setIpImprimante($ip);
          $em->flush();
        }
        /** INSERT OR UPDATE**/
        else{
          $aTmp = $session->get('imprimante_id');
          $aTmp = (is_array($aTmp))? $aTmp : array();
          $depot = $em->getRepository('AmsSilogBundle:Depot')->find($depot_id);
          /** UPDATE **/
          if(array_key_exists($rowId, $aTmp)){
            $imprimante = $em->getRepository('AmsDistributionBundle:Imprimante')->find($aTmp[$rowId]);
            $imprimante->setDepotId($depot);
            $imprimante->setLibelleImprimante($libelle);
            $imprimante->setIpImprimante($ip);
            $em->flush();
          }
          /** INSERT **/
          else{
            $imprimante = new Imprimante();
            $imprimante->setDepotId($depot);
            $imprimante->setLibelleImprimante($libelle);
            $imprimante->setIpImprimante($ip);
            $imprimante->setEtat(true);
            $em->persist($imprimante);
            $em->flush();
            $aTmp[$rowId] = $imprimante->getId();
            $session->set('imprimante_id', $aTmp);
          }
        }
      }
    }
    if ($mode == 'deleted') {
      $aTmp = $session->get('imprimante_id');
      $aTmp = (is_array($aTmp))? $aTmp : array();
      if(array_key_exists($rowId, $aTmp))
        $motif = $em->getRepository('AmsDistributionBundle:Imprimante')->find($aTmp[$rowId]);
      else
        $motif = $em->getRepository('AmsDistributionBundle:Imprimante')->find($request->get('c0'));
      
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
