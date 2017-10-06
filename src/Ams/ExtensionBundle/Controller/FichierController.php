<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Ams\ExtensionBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Ams\ExtensionBundle\Entity\Fichier;
use Ams\ProduitBundle\Form\PrdCaractType;
use Ams\ProduitBundle\Entity\PrdCaract;

/**
 * Description of FichierController
 *
 * @author DDEMESSENCE
 */
class FichierController extends GlobalController {
    
    public function createAction()
    {
        if (!$this->verif_acces())
            return $bVerifAcces;
        else
            $this->setDerniere_page();
        
        $document = new Fichier();
        $form = $this->createFormBuilder($document)
            ->add('name','text',array('label'=> 'Nom'))
            ->add('file','file',array('label'=>'Fichier'))
            ->getForm();
        
        $em = $this->getDoctrine()->getManager();
        
        if ($this->getRequest()->isMethod('POST')) {
            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $document->setDateModif(new \DateTime());
                $em->persist($document);
                $em->flush();
                 
                $document = new Fichier();
                $form = $this->createFormBuilder($document)
                    ->add('name','text',array('label'=> 'Nom'))
                    ->add('file','file',array('label'=>'Fichier'))
                    ->getForm();
                
                return $this->redirect($this->generateUrl('fichier_index'));
            }
        }
        
        $images = $em->getRepository('AmsExtensionBundle:Fichier')->findAll();
        return $this->render('AmsExtensionBundle:Fichier:index.html.twig',
                array('form' => $form->createView(),'images'=>$images));
    }
    
    public function updateAction()
    {
        if (!$this->verif_acces())
            return $bVerifAcces;
        
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        
        $id = $request->get("param1");
        $document = $em->getRepository('AmsExtensionBundle:Fichier')->find($id);
        
        
        
        if (!$document) {
            return false;
        }
        
        $form = $this->createFormBuilder($document)
            ->add('name','text',array('label'=> 'Nom'))
            ->add('file','file',array('label'=>'Fichier'))
            ->getForm();
        
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);
            if ($form->isValid()) {
                $document->setDateModif(new \DateTime());          
                $em->persist($document);
                $em->flush();
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> Votre société à bien été modifiée!'));
            } else {
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue!'));
            }
            
            $images = $em->getRepository('AmsExtensionBundle:Fichier')->findAll();
            $modal = $this->renderView('AmsExtensionBundle:Fichier:formFichier.html.twig', array('form' => $form->createView(), 'document' => $document ,'images'=>$images));            

            $response = array("modal"=>$modal, "alert" => $alert);            
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }
        
        $images = $em->getRepository('AmsExtensionBundle:Fichier')->findAll();
        return $this->render('AmsExtensionBundle:Fichier:formFichier.html.twig', array('form' => $form->createView(), 'document' => $document ,'images'=>$images));
    }
    
    public function deleteAction()
    {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        
        $id = $request->get('fileId');
        
        /* @var $file Fichier */
        $file = $em->getRepository('AmsExtensionBundle:Fichier')->find($id);
        
        $em->remove($file);
        $em->flush();
        
        $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'success', 'message' => 'Image supprimé !'));
        
        return new Response($alert, 200);
    }
}
