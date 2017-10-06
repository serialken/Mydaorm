<?php

namespace Ams\DistributionBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

use Ams\DistributionBundle\Form\OuvertureCdType;
use Ams\DistributionBundle\Form\FiltreOuvertureType;
use Ams\DistributionBundle\Entity\OuvertureCd;
use Ams\SilogBundle\Entity\Depot;
use Ams\DistributionBundle\Repository\OuvertureCdRepository;

/**
 * Description of OuvertureController
 *
 * @author maximiliendromard
 */
class OuvertureCdController extends GlobalController
{
    
    /**
     * Formulaire d'ouverture des centres de distribution
     * 
     */
    
    public function indexAction()
    {
        if(!$this->verif_acces()) 
            return $bVerifAcces;
         else
        $this->setDerniere_page();     
        
        //Récupération des données de la session
        $session = $this->get('session');
        
        //Récupération des données envoyés dans le formulaire
        $request = $this->getRequest();
        $date_filtre = $request->request->get('ams_distributionbundle_filtreouverture');
        
        //Créations des Entités
        $ouverture = new OuvertureCd;
        $depot = new Depot();
        
        //Ajout d'une entité pour préparer le set dépôt
        $ouverture->setDepot($depot);
        
        //Appel de doctrine + recherche de l'objet utilisateur
        $em = $this->getDoctrine()->getManager();
        $user  = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        
        //Récupèration des dépots grâce aux setters & getters
        $depots = $user->getGrpdepot()->getDepots();
        
        //Création du formulaire d'ajout d'ouverture de centre
        $form = $this->createForm(new OuvertureCdType($depots), $ouverture);
        $form->handleRequest($request);
        
        $error_message = '';
         
        //Création du formulaire de filtrage
        $form2 = $this->createForm(new FiltreOuvertureType());
        
        //Affichage de la bonne date dans le filtre
        if ($date_filtre['filtre']) {
            $affichage_date = $date_filtre['filtre'];
        } else {
            $affichage_date = new \DateTime('now');
            $affichage_date = $affichage_date->format('Y-m-d');
        }
           
        //Envoi des données dans la base après vérification
        if($request->getMethod() == 'POST'){
            if ($form->isValid()) { 
                $data = $request->request->get('ams_distributionbundle_ouverturecd');
                $exist  = $em->getRepository('AmsDistributionBundle:OuvertureCd')->isNew($data['depot'], $affichage_date);
                
                if (!$exist) {
                    //Sauvegarde dans la base de données
                    $em->persist($ouverture);
                    $em->flush(); 
                    return $this->redirect($this->generateUrl('ouverture'));
                } else {
                    $error_message = 'Ce centre a déjà été enregistré aujourd\'hui';
                }
            }
        }
        
        //Verification d'accès au formulaire d'enregistrement
        $elts =  $this->getEltsAccessible();
        if ($elts [1] == 'VISU') {
            $admin = 1;
        } else {
            $admin = 0;
        }
        
        //Affiche la page principale
        return $this->render('AmsDistributionBundle:OuvertureCd:index.html.twig', array(
            'form' => $form->createView(),
            'form2' => $form2->createView(),
            'prenom' => $session->get('PRENOM'),
            'nom' => $session->get('NOM'),
            'affichage_date' => $affichage_date,
            'affichage_form' => $admin,
            'error_message' => $error_message
        ));  
            
        
        
    }
    
    
    /**
     * Création de la liste d'ouverture des centres
     * 
     */
    public function gridAction($date) {
        
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        
        // on récupère la liste des adresse rejetés qui sont sur les dépôts de l'utilisateur connecté selon la date du filtre
        $listeCentre = $em->getRepository('AmsDistributionBundle:OuvertureCd')->getListeFiltree($date);
        
        $response = $this->renderView('AmsDistributionBundle:OuvertureCd:grid.xml.twig', array(
                                                        'liste' => $listeCentre
                ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml')); 
    }

}
