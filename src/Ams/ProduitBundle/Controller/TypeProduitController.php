<?php

namespace Ams\ProduitBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Ams\ProduitBundle\Entity\ProduitType;
use Ams\ProduitBundle\Form\TypeProduitType;
/**
 * Type produit controller
 */
class TypeProduitController extends GlobalController {
    
    public function indexAction(){
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        
        //stocker cette page comme la derniere visitee apres expiration de session
        $this->setDerniere_page();
        
        $typeProduit = $this->getDoctrine()->getManager()->getRepository('AmsProduitBundle:ProduitType')->getProduitType();

        return $this->render('AmsProduitBundle:TypeProduit:type_produit.html.twig',
                array('typeProduit'=>$typeProduit));//*/
        
    }
    
    public function createAction() {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $typeProduit = new ProduitType();
        $form = $this->createForm(new TypeProduitType, $typeProduit);
        $request = $this->getRequest();
        if($request->getMethod() == 'POST'){
           $form->bind($request);
            if($form->isValid()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($typeProduit);
                $metadata = $em->getClassMetaData(get_class($typeProduit));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();             
                //$background = $this->renderView('AmsProduitBundle:Societe:tabSociete.html.twig',array('societe'=>$societe, 'active' => 'active in')); 
                $typeProduit = new ProduitType();
                $form = $this->createForm(new TypeProduitType, $typeProduit);               
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> Votre produit à bien été ajoutée!'));
            } else {
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue!'));
            }
            $modal = $this->renderView('AmsProduitBundle:TypeProduit:formTypeProduit.html.twig',
                array('typeProduitForm' => $form->createView(),'is_new' => true));            
                        
            $response = array("modal"=>$modal, "alert" => $alert);            
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }             
                                
        $response = $this->renderView('AmsProduitBundle:TypeProduit:formTypeProduit.html.twig',
                array('typeProduitForm' => $form->createView(),'is_new' => true));
        $return = json_encode($response);
        return new Response($return,200,array('Content-Type'=>'application/json'));        
    }
    
    public function updateTypeAction(){
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $typeProduit = $em->getRepository('AmsProduitBundle:ProduitType')->find($_GET['param1']);
        $form = $this->createForm(new TypeProduitType, $typeProduit);
        $form->handleRequest($request);
        if($request->getMethod() == 'POST'){
            //$form->bind($request);
            if($form->isValid()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($typeProduit);
                $metadata = $em->getClassMetaData(get_class($typeProduit));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
                $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'success','message' => '<strong>Succès!</strong> Votre type a bien été Modifié!'));
            }
            else{
                $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'danger','message' => '<strong>Attention!</strong> Une erreur est survenue!'));
            }
            $modal = $this->renderView('AmsProduitBundle:TypeProduit:formTypeProduit.html.twig',
                array('typeProduitForm' => $form->createView(),'is_new' => false,'typeProduit' => $typeProduit));
            $response = array("modal"=>$modal, "alert" => $alert);            
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }
        $response = $this->renderView('AmsProduitBundle:TypeProduit:formTypeProduit.html.twig',
                array('typeProduitForm' => $form->createView(),'is_new' => false,'typeProduit' => $typeProduit));
        $return = json_encode($response);
        return new Response($return,200,array('Content-Type'=>'application/json'));        
    }
    
    public function deleteTypeAction(){ 
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $id = $request->get('typeId');
        $typeProduit = $em->getRepository('AmsProduitBundle:ProduitType')->find($id);
        $form = $this->createForm(new TypeProduitType, $typeProduit);
        $form->handleRequest($request);
        if($request->getMethod() == 'POST'){
            $em = $this->getDoctrine()->getManager();
            $em->remove($typeProduit);
            $metadata = $em->getClassMetaData(get_class($typeProduit));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $em->flush();
            $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'success','message' => '<strong>Succès!</strong> Votre type a bien été supprimé!'));
            $modal = $this->renderView('AmsProduitBundle:TypeProduit:formTypeProduit.html.twig',
                    array('typeProduitForm' => $form->createView(),'supp' => false,'typeProduit' => $typeProduit));
            $response = array("modal"=>$modal,"alert" => $alert);            
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }
        $response = $this->renderView('AmsProduitBundle:TypeProduit:formTypeProduit.html.twig',
                array('typeProduitForm' => $form->createView(),'supp' => true,'typeProduit' => $typeProduit));
        $return = json_encode($response);
        return new Response($return,200,array('Content-Type'=>'application/json'));
    }
    
}
