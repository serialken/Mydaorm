<?php

namespace Ams\SilogBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ams\DistributionBundle\Entity\ImputationService;
use Symfony\Component\HttpFoundation\Session\Session;

class ImputationsController extends GlobalController {
  public function indexAction() {
  // verifie si on a droit a acceder a cette page
    $bVerifAcces = $this->verif_acces();
    if ($bVerifAcces !== true) {
      return $bVerifAcces;
    }

    return $this->render('AmsSilogBundle:Imputation:index.html.twig');
  }
  
  public function gridAction() {
    /** DATA MOTIF TABLE **/
    $imputation = $this->getDoctrine()
        ->getManager()
        ->getRepository('AmsDistributionBundle:ImputationService')->findBy(array('etat' => 1));
    
	
    $response = $this->renderView('AmsSilogBundle:Imputation:grid.xml.twig', array(
            'imputations' => $imputation,
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
        $imputation = $em->getRepository('AmsDistributionBundle:ImputationService')->find($request->get('c0'));
        /** UPDATE  **/
        if($imputation){
          $imputation->setLibelle($libelle);
          $imputation->setCode($code);
          $em->flush();
        }
        /** INSERT OR UPDATE**/
        else{
          $aTmp = $session->get('ImputationId');
          $aTmp = (is_array($aTmp))? $aTmp : array();
          /** UPDATE **/
          if(array_key_exists($rowId, $aTmp)){
            $imputation = $em->getRepository('AmsDistributionBundle:ImputationService')->find($aTmp[$rowId]);
            $imputation->setLibelle($libelle);
            $imputation->setCode($code);
            $em->flush();
          }
          /** INSERT **/
          else{
            $imputation = new ImputationService();
            $imputation->setLibelle($libelle);
            $imputation->setCode($code);
            $imputation->setEtat(true);
            $em->persist($imputation);
            $em->flush();
            $aTmp[$rowId] = $imputation->getId();
            $session->set('ImputationId', $aTmp);
          }
        }
      }
    }
    if ($mode == 'deleted') {
      $aTmp = $session->get('ImputationId');
      $aTmp = (is_array($aTmp))? $aTmp : array();
      if(array_key_exists($rowId, $aTmp))
        $imputation = $em->getRepository('AmsDistributionBundle:ImputationService')->find($aTmp[$rowId]);
      else
        $imputation = $em->getRepository('AmsDistributionBundle:ImputationService')->find($request->get('c0'));
      
      $imputation->setEtat(false);
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
