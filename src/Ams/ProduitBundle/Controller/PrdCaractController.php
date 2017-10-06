<?php


namespace Ams\ProduitBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Ams\ProduitBundle\Entity\PrdCaract;
use Ams\ProduitBundle\Form\PrdCaractType;
use Symfony\Component\HttpFoundation\Response;

class PrdCaractController extends GlobalController
{    
    public function listAction(){  
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $this->setDerniere_page();
        return $this->render('AmsProduitBundle:PrdCaract:list.html.twig');
    }
    
    /**
     * @return XML  liste des caractéristiques
     */
    public function gridAction() {
        
        $em = $this->getDoctrine()->getManager();
        $caracts = $em->getRepository('AmsProduitBundle:PrdCaract')->findBy(array(
            'dateFin' => null
        ));
        
        $response = $this->renderView('AmsProduitBundle:PrdCaract:grid.xml.twig', array(
            'caracts' => $caracts
        ));
        
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }
    
    public function updateAjaxAction()
    {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        
        $isNew = ($request->get('isNew') === "true");
        
        /* @var $prdCaract PrdCaract */
        if ($isNew) {
            $prdCaract = new PrdCaract();
        } else {
            $id = $request->get('prdCaractId');
            $prdCaract = $em->getRepository('AmsProduitBundle:PrdCaract')->find($id);
        }
        
        $form = $this->createForm(new PrdCaractType,$prdCaract);
        
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);
            if($form->isValid()){
                
                if ($isNew) {    
                    $successMsg = "Votre caractéristique à bien été ajouté!";
                } else {
                    $successMsg = "Votre caractéristique à bien été mise à jour!";
                }
                
                $em->persist($prdCaract);
                $em->flush();
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> ' . $successMsg));
                $isNew = false;
            } else {
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue!'));
            }

            $modal = $this->renderView('AmsProduitBundle:PrdCaract:formPrdCaract.html.twig',array('prdCaract' => $prdCaract, 'prdCaractForm' => $form->createView(), 'isNew' => $isNew));            

            $response = array("modal"=>$modal, "alert" => $alert);            
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }
        
        // au premier appel ajax on affiche le formulaire dans la modale
        return $this->render('AmsProduitBundle:PrdCaract:formPrdCaract.html.twig', array('prdCaract' => $prdCaract, 'prdCaractForm' => $form->createView(), 'isNew' => $isNew));
    }
    
    public function inactivAjaxAction()
    {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        
        $id = $request->get('prdCaractId');
        $actif = ($request->get('actif') === "true");
        
        /* @var $prdCaract PrdCaract */
        $prdCaract = $em->getRepository('AmsProduitBundle:PrdCaract')->find($id);
        $prdCaract->setActif($actif);
        
        $em->persist($prdCaract);
        $em->flush();
        
        $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'success', 'message' => 'Etat de la caractéristique enregistré!'));
        
        return new Response($alert, 200, array('Content-Type'=>'application/json'));
    }
    //supprimer une caractérstique de produit
    
    public function suppressionCaractAction(){
        
        $em = $this->getDoctrine()->getManager();
        $rq = $this->getRequest();
        $id=$rq->request->get('id');
        $caracts = $em->getRepository('AmsProduitBundle:PrdCaract')->find($id);
        $caracts->setDateFin(new \DateTime());
        $em->persist($caracts);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
                        'notice', 'La caractéristique a été supprimée avec succés.'
                );
       
       // return $this->redirect($this->generateUrl('caract_list'));
         return new Response(0);
}
}
