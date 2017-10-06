<?php

namespace Ams\SilogBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MessageController extends GlobalController {
  public function indexAction() {
  // verifie si on a droit a acceder a cette page
    $bVerifAcces = $this->verif_acces();
    if ($bVerifAcces !== true) {
      return $bVerifAcces;
    }
    return $this->render('AmsSilogBundle:Message:index.html.twig');
  }
  
  public function gridAction() {
    $messages = $this->getDoctrine()
        ->getManager()
        ->getRepository('AmsSilogBundle:MessagesInfos')->findAll();

    $response = $this->renderView('AmsSilogBundle:Message:grid.xml.twig', array(
            'messages' => $messages,
    ));
    return new Response($response, 200, array('Content-Type' => 'Application/xml'));
  }
  
  public function MessagesCrudAction(Request $request) {
    if(!$this->verif_acces()) return false;
    $em = $this->getDoctrine()->getManager();
    $rowId = $request->get('gr_id');
    $mode = $request->get('!nativeeditor_status');
    $newId = $action = $msg = $msgException ='';
    $result=true;
		
    if ($mode == 'updated') {
        $messages = $em->getRepository('AmsSilogBundle:MessagesInfos')->find($request->get('c0'));
        $messages->setTitle($request->get('c2'))->setDescription($request->get('c3'));
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
