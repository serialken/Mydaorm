<?php

namespace Ams\ProduitBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Ams\ProduitBundle\Entity\Societe;
use Ams\ProduitBundle\Form\SocieteType;
/**
 * Societe  controller.
 */
class SocieteController extends GlobalController {

    public function indexAction(){
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        // stocker cette page comme la derniere visitee apres expiration de session
        $this->setDerniere_page();
         
                
        $societes = $this->getDoctrine()->getManager()->getRepository('AmsProduitBundle:Societe')->getSocietesAvecProduits();

        return $this->render('AmsProduitBundle:Societe:index.html.twig',
                array('societes'=>$societes));//*/
    }   
    
    public function createAction() {
                // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
      
        $background = '';
        $ul = '';
        
        $societe = new Societe();
        $form = $this->createForm(new SocieteType, $societe);
        $request = $this->getRequest();
        if($request->getMethod() == 'POST'){
           $form->bind($request);
           if($form->isValid()){
              $em = $this->getDoctrine()->getManager();
              $em->persist($societe);
              $em->flush();              
              $background = $this->renderView('AmsProduitBundle:Societe:tabSociete.html.twig',array('societe'=>$societe, 'active' => 'active in')); 
              $ul = $this->renderView('AmsProduitBundle:Societe:ulMenuSociete.html.twig',array('societe'=>$societe)); 
              $societe = new Societe();
              $form = $this->createForm(new SocieteType, $societe);               
              $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> Votre société à bien été ajoutée!'));
            } else {
               $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue!'));
            }
            $modal = $this->renderView('AmsProduitBundle:Societe:formSociete.html.twig',array('societeForm'=>$form->createView(),'is_new' => true));            
                        
            $response = array("modal"=>$modal,  "background"=>$background, "alert" => $alert, "ul" => $ul);            
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }             
                                
        $response = $this->renderView('AmsProduitBundle:Societe:formSociete.html.twig',
                array('societeForm' => $form->createView(),'is_new' => true));
        $return = json_encode($response);
        return new Response($return,200,array('Content-Type'=>'application/json'));        
    }

    public function ajaxListeSocietesAction(){
        $societes = $this->getDoctrine()->getManager()->getRepository('AmsProduitBundle:Societe')->getSocietesAvecProduits();
        return $this->render('AmsProduitBundle:Societe:liste_societes.html.twig',
                  array('societes'=>$societes));      
    }
    
    public function updateAction() {
                // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
  
        $em = $this->getDoctrine()->getManager();
        $societe = $em->getRepository('AmsProduitBundle:Societe')->find($_GET['param1']);
        $background = '';
        
        $form = $this->createForm(new SocieteType, $societe);
        $request = $this->getRequest();
        if($request->getMethod() == 'POST'){
           $form->bind($request);
           if($form->isValid()){
                             
              $em = $this->getDoctrine()->getManager();
              $em->persist($societe);
              $em->flush(); 
              $background = $this->renderView('AmsProduitBundle:Societe:tabSociete.html.twig',array('societe'=>$societe, 'active' => 'active in'));      
              $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> Votre société à bien été modifiée!'));
            } else {
               $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue!'));
            }
            $modal = $this->renderView('AmsProduitBundle:Societe:formSociete.html.twig',array('societeForm'=>$form->createView(),'is_new' => false,'societe' => $societe));            
                        
            $response = array("modal"=>$modal,  "background"=>$background, "alert" => $alert);            
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }             
                                
        $response = $this->renderView('AmsProduitBundle:Societe:formSociete.html.twig',
                array('societeForm' => $form->createView(),'is_new' => false,'societe' => $societe));
        $return = json_encode($response);
        return new Response($return,200,array('Content-Type'=>'application/json'));        
    }//*/
  
    
    private function deleteProduitDep($idProduit)
    {
        $em = $this->getDoctrine()->getManager();
        //on recupere la liste des caract contante s'il yen a 
        $listeCaractConst = $em->getRepository('AmsProduitBundle:PrdCaractConstante')->getConstByProduitId($idProduit);
        if (count($listeCaractConst) > 0) {
            $delRes = $em->getRepository('AmsProduitBundle:PrdCaractConstante')->delDataByProduitId($idProduit);
        }
        //on récupere ttes les dependances produit liées au produit
        $listeParents = $em->getRepository('AmsProduitBundle:Produit')->getDataByEnfantsId($idProduit);
        $listeEnfants = $em->getRepository('AmsProduitBundle:Produit')->getDataByParentsId($idProduit);
        if (count($listeParents) > 0) {
           foreach ($listeParents as $elem) {
               $idParents = $elem['parent_id'];
               $resP = $em->getRepository('AmsProduitBundle:Produit')->delDataByParentId($idParents, $idProduit);
           }
        }
        if (count($listeEnfants) > 0) {
            foreach($listeEnfants as $elem){
                $idEnfants = $elem['enfant_id'];
                $resE = $em->getRepository('AmsProduitBundle:Produit')->delDataByEnfantsId($idEnfants, $idProduit);
            }
        }
        return true;
    }
    
    public function deleteSocieteAction() 
    { 
       $em = $this->getDoctrine()->getManager();
       $request = $this->getRequest();
       $id = $request->get('societeId');
       $societe = $em->getRepository('AmsProduitBundle:Societe')->find($id);
       $societe->setActive(false);
       $em->flush();
       /** SOCIETE QUI COMPORTE DES PRODUITS
        $societeAvecProd = $em->getRepository('AmsProduitBundle:Societe')->getOneSocieteAvecProduits($id);
        */
       $message = '<strong>Succés!</strong> La société <strong>[ ' . $societe->getLibelle() . ' ]</strong> a bien été supprimé !';
       $response = array("alert" => $message);
       $return = json_encode($response);
       return new Response($return, 200, array('Content-Type' => 'application/json'));
    }  
}
