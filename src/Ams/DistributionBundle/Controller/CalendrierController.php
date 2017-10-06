<?php

namespace Ams\DistributionBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Ams\DistributionBundle\Form\ReportType;
use Ams\DistributionBundle\Form\ParutionSpecialeType;
use Ams\DistributionBundle\Entity\ParutionSpeciale;
use Ams\ExtensionBundle\Validator\Constraints\DatePosterieure;
use Ams\DistributionBundle\Entity\JourFerie;

/**
 * Calendrier  controller.
 *
 */
class CalendrierController extends GlobalController {
    
    public function indexAction($mois=0){
      if(!$this->verif_acces()) 
        return $bVerifAcces;
      else
        $this->setDerniere_page();
      
      
      if($mois == 0) { 
          $mois = Date('m');
      }

      $em = $this->getDoctrine()->getManager();
      $events = $em->getRepository('AmsFichierBundle:FicRecap')->getCalendrier();
      $produits = $em->getRepository('AmsProduitBundle:Produit')->getProduitsArray();
      $parutions = $em->getRepository('AmsDistributionBundle:ParutionSpeciale')->getParutions();

      // Récupération des états des events du calendrier
      $ficEtats = array();
      foreach ($events as $event) {
          $ficEtatId = $event['fic_etat_id'];
          if(!isset($ficEtats[$ficEtatId])) {
              $ficEtats[$ficEtatId] = $em->getRepository('AmsFichierBundle:FicEtat')->find($ficEtatId);
          }
      }
      
      $calendarJFeries = array();
      foreach ($em->getRepository('AmsDistributionBundle:JourFerie')->findByActif(1) as $jourFerie) {
          /* @var $jourFerie JourFerie */
          $calendarJFeries[] = $jourFerie->getDate()->format("D M d Y");
      }
      
      return $this->render('AmsDistributionBundle:Calendrier:calendrier.html.twig',
                    array(
                        'events'    =>  $events,
                        'produits'  =>  $produits,
                        'parutions' =>  $parutions,
                        'securise'  =>  false,
                        'Jferies'   =>  json_encode($calendarJFeries),
                        'ficEtats'  =>  $ficEtats
                    ));
    }
    
    public function calendrierSecuriseAction($mois=0){
        if(!$this->verif_acces()) 
          return $bVerifAcces;
        else
          $this->setDerniere_page();

        if($mois == 0) { 
            $mois = Date('m');
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $session = new Session();
        
        $feries = $em->getRepository('AmsDistributionBundle:JourFerie')->findByActif(1);
        $feries = serialize($feries);

        $depots = $session->get('DEPOTS');
        $depotIds = array_keys($depots);
      
        // Création du formulaire de filtre
        $form = $this->createFormBuilder()
            ->add(
                "depots", "choice",
                array( 
                    'choices'   => $depots, 
                    'required'  => false,
                    'empty_value' => 'Choisissez un dépot',
                    'data' => reset($depotIds)
                )
            )
            ->add(
                'flux', 'entity', 
                array(
                    'class' => 'AmsReferentielBundle:RefFlux',
                    'property' => 'libelle',
                    'required' => false,
                    'empty_value' => 'Choisissez un flux',
                    'data' => 1
                )
            )
            ->getForm();

        $request = $this->getRequest();
        
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isValid()) {
            $formData = $form->getData();
            $depotFilterId = $formData['depots'] ? $formData['depots'] : reset($depotIds);
            $fluxId = $formData['flux'] ? $formData['flux'] : 1;
        } else {
            // Par défaut on prend le premier de la liste
            $depotFilterId = reset($depotIds);
            $fluxId = 1;
        }
        
        $events = $em->getRepository('AmsDistributionBundle:ProduitRecapDepot')->getCalendrier(null, $depotFilterId, $fluxId);
        return $this->render('AmsDistributionBundle:Calendrier:calendrier.html.twig',
                array('events'=>$events,'securise'=> true,
                          'Jferies' => json_encode($feries),
                          'form' => $form->createView()
              ));
    }
    
    public function ajaxEventModalAction()
    {
        if(!$this->verif_acces()) 
          return $bVerifAcces;
      
        $id = $_GET['id'];
        
        $em = $this->getDoctrine()->getManager();
        $ficRecapRepository             = $em->getRepository('AmsFichierBundle:FicRecap');
        $produitRecapDepotRepository    = $em->getRepository('AmsDistributionBundle:ProduitRecapDepot');
        $ficEtatRepository              = $em->getRepository('AmsFichierBundle:FicEtat');
        
        /*@var $event \Ams\FichierBundle\Entity\FicRecap */
        $event = $ficRecapRepository->find($id);
        
        $distributions = $event->getProduitRecapDepots();
        //$distributions = $produitRecapDepotRepository->findBy(array('ficRecap'=>$id));
        
        // Construction du formulaire
        $distinctProduits = array();
        $defaultValueProduits = array();
        
        foreach ($distributions as $distribution) {
            $produit = $distribution->getProduit();
            
            if (!isset($distinctProduits[$produit->getId()])) {
                
                $distinctProduits[$produit->getId()] = $produit->getLibelle();
                // Par defaut on coche toutes les checkbox
                $defaultValueProduits[] = $produit->getId();
            }
        }
        
        $form = $this->createFormBuilder(array('date'=>null), array('csrf_protection' => false))
            ->add('produits', 'choice', array(
                'choices'   => $distinctProduits,
                'required'  => false,
                'multiple'  => true,
                'expanded' => true,
                'data' => $defaultValueProduits
            ))
            ->add('date', 'date', array(
                'label' => 'Copie vers',
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date'),
                'constraints' => array(new DatePosterieure()),
            ))
            ->getForm();
        
        // Traitement du formulaire
        $request = $this->getRequest();
        
        if($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $eventIdToDelete = null;
            if($form->isValid()){

                $produits = $form->get('produits')->getData();
                $date = $form->get('date')->getData();
                
                // 0 - Edition de l'éventuel fichier qu'on écrase, 
                // suppression des produits recap_depot associé 
                // ainsi que les clients dans client_as_servir_logist et client_a_servir_src
                $ficRecapInDate = $ficRecapRepository->getLastFicBySocieteAndDate($event->getSociete(), $date);
                
                if (isset($ficRecapInDate[0])) {
                    /*@var $ficToOverWrite \Ams\FichierBundle\Entity\FicRecap */
                    $ficToOverWrite = $ficRecapInDate[0];
                    $eventIdToDelete = $ficToOverWrite->getId();
                    
                    // Mise à jour de son état :
                    $ficEtatOverWrite = $ficEtatRepository->findOneBy(array(
                        'code'=>  \Ams\FichierBundle\Entity\FicEtat::STATE_CODE_OVERWRITE
                    ));
                    
                    $ficToOverWrite->setFicEtat($ficEtatOverWrite);
                    
                    // Suppression des ProduitsRecapDepot associé
                    $produitRecapDepotRepository->deleteByFicRecap($ficToOverWrite);
                    // Suppression des client_a_servir_logist associé
                    $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->deleteByFicRecap($ficToOverWrite);
                    // Suppression des client_a_servir_src associé
                    $em->getRepository('AmsDistributionBundle:ClientAServirSrc')->deleteByFicRecap($ficToOverWrite);
                }
                
                // 1 - Copie du "fic_recap" => Etat "Copié"
                
                $ficEtatCopy = $ficEtatRepository->findOneBy(array(
                    'code'=>  \Ams\FichierBundle\Entity\FicEtat::STATE_CODE_COPY
                ));

                $copy = clone $event;
                $copy->setDateDistrib($date);
                $copy->setDateParution($date);
                $copy->setDateTraitement(new \DateTime);
                $copy->setFicEtat($ficEtatCopy);
                
                $em->persist($copy);
                $em->flush();
                
                // 2 - Copie des "produit_recap_depot" selectionné dans le formulaire
                foreach($distributions as $fichierProduit){
                    if (in_array($fichierProduit->getProduit()->getId(), $produits)) {
                        
                        $copyFichierProduit = clone $fichierProduit;
                        $copyFichierProduit->setDateDistrib($date);
                        $copyFichierProduit->setFicRecap($copy);
                        $em->persist($copyFichierProduit);
                    }
                }
                $em->flush();
                
                // 3 - Copie des "client_a_servir_logist" et "client_a_servir_src"
                
                $clientAServirSrcList = $em->getRepository('AmsDistributionBundle:ClientAServirSrc')->getByFicRecap($event);
                
                foreach ($clientAServirSrcList as $clientAServirSrc) {
                    /*@var $clientAServirSrc \Ams\DistributionBundle\Entity\ClientAServirSrc */
                    $clientAServirSrcCopied = clone $clientAServirSrc;
                    $clientAServirSrcCopied->setFicRecap($copy);
                    $clientAServirSrcCopied->setDateDistrib($date);
                    $clientAServirSrcCopied->setDateParution($date);
                    $em->persist($clientAServirSrcCopied);
                    
                    /*@var $clientAServirLogist \Ams\DistributionBundle\Entity\ClientAServirLogist */
                    $clientAServirLogist = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getByClientAServirSrc($clientAServirSrc);
                    /*@var int $iDayWeek ajoute 1 pour coller a la table ref_jour */
                    $iDayWeek = date('w',strtotime($date->format('Y-m-d'))) + 1 ;
                    $idModeleTourneeJour = $clientAServirLogist->getTournee();
                    if($idModeleTourneeJour) {
                        $iIdModeleTournee = $clientAServirLogist->getTournee()->getTournee()->getId();
                        $aModeleTourneeJour = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')
                            ->findIdByModeleTourneeRefJour($iIdModeleTournee, $iDayWeek, $date->format('Y-m-d'));
                        $oModeleTourneeJour = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->find($aModeleTourneeJour['id']);
                        $oModeleTourneeJour = ($oModeleTourneeJour) ? $oModeleTourneeJour : null;
                    }else
                        $oModeleTourneeJour = null;

                    if ($clientAServirLogist) {
                        $clientAServirLogistCopied = clone $clientAServirLogist;
                        $clientAServirLogistCopied->setFicRecap($copy);
                        $clientAServirLogistCopied->setDateDistrib($date);
                        $clientAServirLogistCopied->setDateParution($date);
                        $clientAServirLogistCopied->setClientAServirSrc($clientAServirSrcCopied);
                        $clientAServirLogistCopied->setTournee($oModeleTourneeJour);
                        $clientAServirLogistCopied->setPaiTournee(null);
                        $em->persist($clientAServirLogistCopied);
                    }
                }
                
                $em->flush();
                
                $newEventData = $copy->getEventCalendarData();
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> Le fichier a bien été copié!'));
            } else {
                $newEventData = null;
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue!'));
            }
            $modal = $this->renderView('AmsDistributionBundle:Calendrier:eventModal.html.twig',array('event'=>$event,'distributions' => $distributions,'form'=>$form->createView(),'securise'=>false));            
            
            $response = array("modal"=>$modal, "alert" => $alert, "newEventData" => $newEventData, "eventIdToDelete" => $eventIdToDelete);
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }
        
        return $this->render('AmsDistributionBundle:Calendrier:eventModal.html.twig',
                array('event'=>$event,'distributions' => $distributions,'form'=>$form->createView(),'securise'=>false));
    }
    
    public function ajaxSecureModalAction(){     
        if(!$this->verif_acces()) 
            return $bVerifAcces;
      
        $em = $this->getDoctrine()->getManager();
        $id = $_GET['id'];
        $event = $em->getRepository('AmsDistributionBundle:ProduitRecapDepot')->find($id);
        return $this->render('AmsDistributionBundle:Calendrier:eventModal.html.twig',
                array('event'=>$event,'securise'=>true));
    }
    
    public function gestionProduitsAction(){
      if(!$this->verif_acces()) 
        return $bVerifAcces;
      else
        $this->setDerniere_page();
      
     $em = $this->getDoctrine()->getManager();

     $produit = $em->getRepository('AmsProduitBundle:Produit')->find($_GET['id']);
     $parution = new ParutionSpeciale();
     $parution->setProduit($produit);

     $formReport = $this->createForm(new ReportType(),$produit);
     $formParutionSpeciale = $this->createForm(new ParutionSpecialeType(),$parution);


     return $this->render('AmsDistributionBundle:Calendrier:reportModal.html.twig',
             array('produit'=>$produit,'formReport'=>$formReport->createView(),'formParution'=>$formParutionSpeciale->createView()));
    }
    
    public function ajaxUpdateReportAction(){
        if(!$this->verif_acces()) 
            return $bVerifAcces;
        
        $em = $this->getDoctrine()->getManager();
        $produit = $em->getRepository('AmsProduitBundle:Produit')->find($_GET['id']);
        $formReport = $this->createForm(new ReportType(),$produit);
        $parution = new ParutionSpeciale();
        $formParutionSpeciale = $this->createForm(new ParutionSpecialeType(),$parution);

        $request = $this->getRequest();
        if($request->getMethod() == 'POST') {
            $formReport->handleRequest($request);            
            if($formReport->isValid()){
                $em->persist($produit);
                $em->flush();
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> Le calendrier des reports a bien été mis à jour!'));
            } else {
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue!'));
            }
            $modal = $this->renderView('AmsDistributionBundle:Calendrier:reportModal.html.twig',
                array('produit'=>$produit,'formReport'=>$formReport->createView(),'formParution'=>$formParutionSpeciale->createView()));       

            $response = array("modal"=>$modal, "alert" => $alert);
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }
    }
    
    public function ajaxCreateParutionAction(){
        if(!$this->verif_acces()) 
          return $bVerifAcces;
        
        $em = $this->getDoctrine()->getManager();
        $produit = $em->getRepository('AmsProduitBundle:Produit')->find($_GET['id']);
        $formReport = $this->createForm(new ReportType(),$produit);
        $parution = new ParutionSpeciale();
        $parution->setProduit($produit);
        $formParutionSpeciale = $this->createForm(new ParutionSpecialeType(),$parution);

        $request = $this->getRequest();
        if($request->getMethod() == 'POST') {
            $formParutionSpeciale->handleRequest($request);            
            if($formParutionSpeciale->isValid()){
                $em->persist($parution);
                $em->flush();
                $parution = new ParutionSpeciale();
                $parution->setProduit($produit);
                $formParutionSpeciale = $this->createForm(new ParutionSpecialeType(),$parution);
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> La parution a bien été ajoutée!'));
            } else {
                $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue!'));
            }
            $modal = $this->renderView('AmsDistributionBundle:Calendrier:reportModal.html.twig',
                array('produit'=>$produit,'formReport'=>$formReport->createView(),'formParution'=>$formParutionSpeciale->createView()));       

            $response = array("modal"=>$modal, "alert" => $alert);            
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));
        }
    }

    public function ajaxCheckProductExistAction() {
        if(!$this->verif_acces())
            return $bVerifAcces;
        
        $em = $this->getDoctrine()->getManager();

        $request = $this->getRequest();
        
        if($request->getMethod() == 'POST') {
            
            $produits = $request->get("produits");
            $dateCopie = $request->get("date");
            
            if (!is_array($produits) && !$dateCopie) {
                $response = array(
                    "error" => true,
                    "message" => "Les paramètres ne sont pas correcte, veuillez contacter un administrateur!"
                );
                
                return new Response(json_encode($response),200,array('Content-Type'=>'application/json'));
            }
            
            $dateCopie = preg_replace('<^([0-9]{2})/([0-9]{2})/([0-9]{4})$>', '$3-$2-$1', $dateCopie);
            $dateCopie = new \DateTime($dateCopie);
            
            $produitsRecapInDate = $em->getRepository('AmsDistributionBundle:ProduitRecapDepot')->hasProductsInDate($produits, $dateCopie);

            if($produitsRecapInDate) {
                
                $prods = array();
                foreach ($produitsRecapInDate as $produitRecap) {
                    $prods[] = "- " . $produitRecap->getProduit()->getLibelle();
                }
                
                $msg = "Les produits suivants sont déja livrée le " . $dateCopie->format("d/m/Y") . " : \n\n";
                $msg .= implode("\n", $prods) . "\n\n";
                $msg .= "Voulez-vous les remplacer ? ";
                
                $response = array(
                    "isExist" => true,
                    "message" => $msg
                );
                
                return new Response(json_encode($response),200,array('Content-Type'=>'application/json'));
            }
            
            $response = array(
                "isExist" => false
            );
            
            return new Response(json_encode($response),200,array('Content-Type'=>'application/json'));
        }
    }
    
    public function ajaxCheckSocExistAction() {
        if(!$this->verif_acces())
            return $bVerifAcces;
        
        $em = $this->getDoctrine()->getManager();

        $request = $this->getRequest();
        
        if($request->getMethod() == 'POST') {
            
            $societeId = $request->get("societe");
            $societe = $em->getRepository('AmsProduitBundle:Societe')->find($societeId);
            
            $date = $request->get("date");
            $dateCopie = preg_replace('<^([0-9]{2})/([0-9]{2})/([0-9]{4})$>', '$3-$2-$1', $date);
            $dateCopie = new \DateTime($dateCopie);
            
            $ficRecap = $em->getRepository('AmsFichierBundle:FicRecap')->getLastFicBySocieteAndDate($societe, $dateCopie);

            if($societe && $ficRecap) {
                
                $msg = "!!ATTENTION \n\n";
                $msg .= "Des produits sont déja livrée le " . $date . " pour la société " . $societe->getLibelle() . ". \n";
                $msg .= "En continuant vous allez remplacer se fichier par celui selectionné. \n";
                $msg .= "Voulez-vous continuer ? ";
                
                $response = array(
                    "isExist" => true,
                    "message" => $msg
                );
                
                return new Response(json_encode($response),200,array('Content-Type'=>'application/json'));
            }
            
            $response = array(
                "isExist" => false
            );
            
            return new Response(json_encode($response),200,array('Content-Type'=>'application/json'));
        }
    }

}//Controller