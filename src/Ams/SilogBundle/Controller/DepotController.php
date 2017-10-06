<?php
namespace Ams\SilogBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Form\DepotType;
use Ams\SilogBundle\Entity\Depot;
use Ams\AdresseBundle\Entity\DepotCommune;
use Ams\AdresseBundle\Form\DepotCommuneType;
use DateTime;


class DepotController extends GlobalController {
    
    public function indexAction(){
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        // stocker cette page comme la derniere visitee apres expiration de session
        $this->setDerniere_page();

        $depots = $this->getDoctrine()->getManager()->getRepository('AmsSilogBundle:Depot')->getDepotsAvecCommunes();
        return $this->render('AmsSilogBundle:Depot:index.html.twig', array('depots'=>$depots));
    }
    
     
   /**
    *  Affiche la grid des des communes 
    */
    public function gridCommunesAction(){
        
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        //on recupere les données du depot
        $depotId = $_GET['id'];
        $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneBy(array('id'=> $depotId));
        $depotLibelle = $depot->getLibelle();
        //var_dump($depot);exit;
        // on récupére la liste des communes pour un centre de dépots
        $listeCommunes = $em->getRepository('AmsSilogBundle:Depot')->getCommunesAvecDepotsId($depotId);
        //var_dump($listeCommunes);exit();
        if (count($listeCommunes) > 0) {
            $ct = 1;
            $response = $this->renderView('AmsSilogBundle:Depot:gridCommunes.xml.twig', array(
                                                                                'depot' =>$listeCommunes[0],
                                                                                'ct' => $ct,
                                                                                'libelle' => $depotLibelle,
                                                                                'lenRSpan' => count($listeCommunes)
                    ));
        }
        else {
            $ct = 0;
            $response = $this->renderView('AmsSilogBundle:Depot:gridCommunes.xml.twig', array(
                                                                                'ct' => $ct,
                                                                                'libelle' => $depotLibelle
                    ));
        }
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }
    
    public function createAction() {
                // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        //var_dump($utilisateur);exit();
        // stocker cette page comme la derniere visitee apres expiration de session
        $this->setDerniere_page();
      
        $background = '';
        $ul = '';
        
        $depot = new Depot();
        $form = $this->createForm(new DepotType, $depot); 
        
        $request = $this->getRequest();
        if($request->getMethod() == 'POST'){
            $id = null;
           $form->handleRequest($request);
           if($form->isValid()){       
              //$em = $this->getDoctrine()->getManager();
              $depot->setDateDebut(new DateTime());
              $depot->setUtilisateurId($utilisateur);
              $em->persist($depot);
              $em->flush();   
              $id = $depot->getId();
              $background = $this->renderView('AmsSilogBundle:Depot:tabDepot.html.twig',array('depot'=>$depot,  'active' => 'active in')); 
              $ul = $this->renderView('AmsSilogBundle:Depot:ulMenuDepot.html.twig',array('depot'=>$depot)); 
              $depot = new Depot();
              $form = $this->createForm(new DepotType, $depot);               
              $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> Votre dépôt a bien été ajouté!'));
              
                // Demande de géocodage de l'adresse en mode asynchrone
                $sCmd = 'php ';
                $sCmd .= $this->get('kernel')->getRootDir()
                    . '/console geocodage_depot --scope='.$id
                    . ' --env ' . $this->get('kernel')->getEnvironment();
                GlobalController::bgCommandProxy($sCmd);
                
            } else {
               $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue!'));
            }
            $modal = $this->renderView('AmsSilogBundle:Depot:formDepot.html.twig',array('depotForm'=>$form->createView(),'is_new' => true));            
                        
            $response = array("modal"=>$modal, "depotId"=> $id, "background"=>$background, "alert" => $alert, "ul" => $ul);            
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }             
                                
        $response = $this->renderView('AmsSilogBundle:Depot:formDepot.html.twig', array(
                                                                                    'depotForm' => $form->createView(),
                                                                                    'is_new' => true));
        $return = json_encode($response);
        return new Response($return,200,array('Content-Type'=>'application/json'));        
    }

    public function updateAction() {
                // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        // stocker cette page comme la derniere visitee apres expiration de session
        $this->setDerniere_page();
      
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $depot = $em->getRepository('AmsSilogBundle:Depot')->find($_GET['param1']);
        $background = '';
        
        $form = $this->createForm(new DepotType, $depot);
        $request = $this->getRequest();
        if($request->getMethod() == 'POST'){
           $iDepotId = NULL;
           $form->handleRequest($request);
           if($form->isValid()){
              $depot->setUtilisateurId($utilisateur);
              $depot->setDateModif(new DateTime());
              $em->persist($depot);
              $em->flush();     
              $iDepotId = $depot->getId();
              
              $background = $this->renderView('AmsSilogBundle:Depot:tabDepot.html.twig',array('depot'=>$depot, 'active' => 'active in'));      
              $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> Votre dépot a bien été modifié'));
              
              // Demande de géocodage de l'adresse en mode asynchrone
                $sCmd = 'php ';
                $sCmd .= $this->get('kernel')->getRootDir()
                    . '/console geocodage_depot --scope='.$iDepotId
                    . ' --env ' . $this->get('kernel')->getEnvironment();
                GlobalController::bgCommandProxy($sCmd);
                
            } else {
               $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue!'));
            }
            $modal = $this->renderView('AmsSilogBundle:Depot:formDepot.html.twig',array('depotForm'=>$form->createView(),'is_new' => false,'depot' => $depot));            
                        
            $response = array("modal"=>$modal,  "background"=>$background, "alert" => $alert, 'depotId' => $iDepotId);            
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }
                                
        $response = $this->renderView('AmsSilogBundle:Depot:formDepot.html.twig',
                array('depotForm' => $form->createView(),'is_new' => false,'depot' => $depot));
        $return = json_encode($response);
        return new Response($return,200,array('Content-Type'=>'application/json'));        
    }
    
    public function ajoutDepotCommuneAction() {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
      
        //$background = '';
        //$ul = '';
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $error = false;
        
        $id = $_GET['param1'];
        
        $depot = $em->getRepository('AmsSilogBundle:Depot')->find($id);
        
        $date_fin = new DateTime($this->container->getParameter('DATE_FIN'));
        
        $depotCommune = new DepotCommune($date_fin);
        $depotCommune->setUtilisateurModif($utilisateur);
        $depotCommune->setDepot($depot);

        $form = $this->createForm(new DepotCommuneType($depot->getId(),$date_fin),$depotCommune);
        
        $request = $this->getRequest();
        if($request->getMethod() == 'POST'){
           $form->handleRequest($request);

           if($form->isValid()){
               $depotCommunes = $em->getRepository('AmsAdresseBundle:DepotCommune')->getCommunesBetweenDate(
                    $depotCommune->getCommune(),
                    $depotCommune->getDateDebut()
                );
               
               if(count($depotCommunes) > 1) {
                   $msg = "<strong>Attention!</strong> Cette commune est rattachées à plusieurs dépôts à la fois! ";
                   $msg .= '<ul style="text-align: left; margin-left: 25%;">';
                   foreach ($depotCommunes as $dc) {
                       $msg .= "<li>" . $dc->getDepot()->getLibelle() . "</li>";
                   }
                   $msg .= "</ul>";
                   $msg .= "<br> Veuillez contacter un administrateur pour régulariser la situation.";
                   $alert = $msg;
                   $error = true;
               } elseif(isset($depotCommunes[0])) {
                   /* @var $lastDepotCommune DepotCommune */
                   $lastDepotCommune = $depotCommunes[0];
                   if ($lastDepotCommune->getDateDebut() == $depotCommune->getDateDebut()) {
                       $msg = "<strong>Attention!</strong> Cette commune est rattachée à d'autre dépôt pour la même date! ";
                       $msg .= '<br><ul><li> ' . $lastDepotCommune->getDepot()->getLibelle() . '</li></ul>';
                       $alert = $msg;
                       $error = true;
                   } else {
                       // On met Ã  jour la date du dernier depotCommune affectÃ© Ã  J-1
                       $newDateFin = clone $depotCommune->getDateDebut();
                       $newDateFin->sub(new \DateInterval('P1D'));
                       
                       $lastDepotCommune->setDateFin($newDateFin);
                       
                       $em->persist($depotCommune);
                       $em->flush();
                       
                       $msg = "<strong>Succés!</strong> Cette commune a bien été affecté au dépot " . $depot->getLibelle() . "! ";
                       $msg .= "<br> L'ancienne affectation de cette commune au dépôt : " . $lastDepotCommune->getDepot()->getLibelle() . " a pris fin à la date suivante : " . $newDateFin->format("d/m/y");
                       
                       $alert = $msg;
                   }
               } else {
                   $em->persist($depotCommune);
                   $em->flush();
                   $alert = '<strong>Succés!</strong> La commune a été ajoutée!';
                }
            } else {
               $alert = '<strong>Attention!</strong> Une erreur est survenue! ';
               //$alert .= $form->getErrorsAsString();
               $error = true;
            }     
            $modal = $this->renderView('AmsAdresseBundle:DepotCommune:formDepotCommune.html.twig',array('form'=>$form->createView(),'is_new' => true,'depot' => $depot));   
            $response = array("modal"=>$modal, "alert" => $alert, 'errorTraitement' => $error);
    
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }             
        
        $modal = $this->renderView('AmsAdresseBundle:DepotCommune:formDepotCommune.html.twig',array(
                                                                                                'form'=>$form->createView(),
                                                                                                'is_new' => true,
                                                                                                'depot' => $depot,
                                                                                                'errorTraitement' => $error));         
        return new Response($modal,200);        
    }
    
    public function suppDepotCommuneAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        
        $depot_libelle = $request->request->get('depot');
        $commune_libelle = $request->request->get('commune');
        
        $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneByLibelle($depot_libelle);
        $commune = $em->getRepository('AmsAdresseBundle:Commune')->findOneByLibelle($commune_libelle);
        
        $em->getRepository('AmsSilogBundle:Depot')->deleteDepotCommune($depot->getId(), $commune->getId());
        $return = '1';
        //$return = json_encode($data);
        return new Response($return,200);   
    }
}
