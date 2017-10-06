<?php

namespace Ams\SilogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;

class DocumentsController extends GlobalController {

    public function indexAction() {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        return $this->render('AmsSilogBundle:Documents:index.html.twig');
    }
    
    public function gridAction() {
    $session = new Session();
    $em = $this->getDoctrine()->getManager();
    /** DATA MOTIF TABLE **/
    $docs = $this->getDoctrine()
        ->getManager()
        ->getRepository('AmsSilogBundle:Documents')->findByUser($session->get('UTILISATEUR_ID'));
    
    $fs = new Filesystem();
    foreach($docs as $key=>$doc){
    if(!$fs->exists('background/'.$doc->getPath()))
           $docs[$key]->setPath('waiting');
    }
    
    $response = $this->renderView('AmsSilogBundle:Documents:grid.xml.twig', array(
        'documents' => $docs,
    ));
    return new Response($response, 200, array('Content-Type' => 'Application/xml'));
  }
  
    public function DocumentCrudAction(Request $request) {
        if(!$this->verif_acces()) return false;
        $em = $this->getDoctrine()->getManager();
        $oDocument = $em->getRepository('AmsSilogBundle:Documents')->find($request->request->get('id'));
        $em->remove($oDocument);
        $em->flush();
        exit;

      
    }
    

}
