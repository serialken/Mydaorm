<?php

namespace Ams\DistributionBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\DistributionBundle\Entity\SuiviDeProduction;
use Ams\SilogBundle\Entity\DepotRoute;
use Ams\SilogBundle\Repository\DepotRouteRepository;



class SuiviProductionController extends GlobalController {

    /**
     * Affiche  le formulaire  et la liste  du suivi de production
     *
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request){

        // verifie si on a droit d'acceder à cette page
//        $bVerifAcces = $this->verif_acces();
//        var_dump($bVerifAcces);
//        die();
//        if ($bVerifAcces !== true) {
//            return $bVerifAcces;
//        }

        //        $this->setDerniere_page();
        $session = $request->getSession();
        $form = $this->createFormBuilder()
            ->add('DateParution', 'text', array('required' => true, 'label' => 'Date de parution'))
            ->getForm();

        /** GET DATE DE PARUTION **/
        if ($request->getMethod() == 'POST') {
            $DataInform = $request->request->get('form');
            $dateParution = $DataInform['DateParution'];
        } else {
            $dateParution = date('d/m/Y');
        }
        $session->set('SuiviProductionDateParution', $dateParution);

        return $this->render('AmsDistributionBundle:SuiviProduction:list.html.twig', array(
            'dateParution' => $dateParution,
            'form' => $form->createView(),
        ));

    }

    /**
     * Affiche la grid du suivi de production
     *
     * @return Response
     */
    public function gridAction() {

        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $date = $session->get('SuiviProductionDateParution');
//        $depotsTab = $session->get('DEPOTS');
//        $depots = $em->getRepository('AmsDistributionBundle:SuiviDeProduction')->getIdLibelleAllDepots();
//        foreach($depots as $depot){
//            $depotsTab[$depot['id']] = $depot['libelle'];
//        }
//        $idDepotsInline = implode(',',array_keys($depotsTab));
        // Recuperation des id, code, libelle centres sur 3chiffres
//        $res = $em->getRepository('AmsDistributionBundle:SuiviDeProduction')->getCodeDepotsActiveById($idDepotsInline);
        $res = $em->getRepository('AmsDistributionBundle:SuiviDeProduction')->getAllDepotsActive();
        foreach($res as $data){
            $codeDepotsTab[] = $data['code'];
        }
        $codeDepotsInline = implode(',', $codeDepotsTab);
        $dateMini = str_replace('/','-',$date);
        $dateParution = date("Y-m-d", strtotime($dateMini));
        //Recuperation du suivi de production avec les libelle route
        $suiviProd =  $em->getRepository('AmsDistributionBundle:SuiviDeProduction')->getSuiviDeProductionByDateEditionAndDepots($dateParution,$codeDepotsInline);
        
        $response = $this->renderView('AmsDistributionBundle:SuiviProduction:grid.xml.twig', array(
            'suivi' => $suiviProd,
            'dateParution' => $dateParution
        ));

        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }
    
    /**
     * Affiche le tableau avec la liste des routes
     * @param Request $request
     * @return type
     */
    public function configurationListAction(Request $request){
         return $this->render('AmsDistributionBundle:SuiviProduction:configuration_list.html.twig');
    }
    
    /**
     * Affiche la Grid des routes
     * @return Response
     */
    public function configurationGridAction(){
        $em = $this->getDoctrine()->getManager();
        $depotRoutes = $em->getRepository('AmsDistributionBundle:SuiviDeProduction')->getAllDepotsRoutes();
        
        $response = $this->renderView('AmsDistributionBundle:SuiviProduction:configuration_grid.xml.twig', array(
            'depotRoutes' => $depotRoutes
        ));

        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }
    
    public function createRouteAction(Request $request){
        // on verifie les droits d'accés
//        $bVerifAcces = $this->verif_acces();
//        if ($bVerifAcces !== true) {
//            return $bVerifAcces;
//        }
        
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $suiviDeProductionRepo = $em->getRepository('AmsDistributionBundle:SuiviDeProduction');
        $utilisateurRepo = $em->getRepository('AmsSilogBundle:Utilisateur');
        $depotRouteRepo = $em->getRepository('AmsSilogBundle:DepotRoute');
        $depotRepo = $em->getRepository('AmsSilogBundle:Depot');
        
        $depots = $suiviDeProductionRepo->getAllDepotsActive();
        foreach($depots as $depot){
            $depotsTab[$depot['id']] = $depot['libelle'];
        }
        
        $utilisateur = $utilisateurRepo->findOneById($session->get('UTILISATEUR_ID'));
        //var_dump($utilisateur);exit();
        
        // // stocker cette page comme la derniere visitee apres expiration de session
//        $this->setDerniere_page();
        $depotRoute = new DepotRoute();
        
        $form = $this->createFormBuilder()
            ->add('CodeRoute', 'text', array('required' => true,'label' => 'Code Route*'))
            ->add('LibelleRoute', 'text', array('required' => false, 'label' => 'Libelle Route'))
            ->add('Centre', 'choice', array(
                                                'choices' => $depotsTab,
                                                'required' => true,
                                                'multiple' => false,
                                                'expanded' => false,
                                                'label' => 'Centre*'
                                             )
                    )
            ->add('Actif', 'choice', array(
                                            'choices' => array(1 => "Oui", 0 => "Non"),
                                            'required' => false,
                                            'multiple' => false,
                                            'expanded' => false,
                                            'label' => 'Active',
                                            'empty_value' => false,
                                        )
                    )
            ->getForm();
        $request = $this->getRequest();
        if($request->getMethod() == 'POST'){
            $idRoute = null;
            $form->handleRequest($request);
            if($form->isValid()){
             $DataInform = $request->request->get('form');
             $createdAt = date('Y-m-d H:i:s');
             $codeRouteForm = $DataInform['CodeRoute'];
             $libelleRouteForm = $DataInform['LibelleRoute'];
             $codeCentreForm = $DataInform['Centre'];
             $libelleCentre = $depotRepo->findOneById($codeCentreForm);
             $ActifForm = $DataInform['Actif'];
             if($ActifForm != 1){
                 $ActifForm = 0;
             }
                 
             $idRoute = intval($depotRouteRepo->insertRoute($utilisateur->getId(),$codeRouteForm,$libelleRouteForm,$codeCentreForm,$libelleCentre->getLibelle(),$createdAt,$ActifForm));
           
//             $form = $this->createFormBuilder()
//            ->add('CodeRoute', 'text', array('required' => true,'label' => 'Code Route*'))
//            ->add('LibelleRoute', 'text', array('required' => false, 'label' => 'Libelle Route'))
//            ->add('Centre', 'choice', array(
//                                                'choices' => $depotsTab,
//                                                'required' => true,
//                                                'multiple' => false,
//                                                'expanded' => false,
//                                                'label' => 'Centre*'
//                                             )
//                    )
//            ->getForm();
            $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'success','message'=>'<strong>Succès!</strong> Votre route a bien été ajouté!'));
            }else{
                 $alert = $this->renderView('::modalAlerte.html.twig',array('type'=>'danger','message'=>'<strong>Attention!</strong> Une erreur est survenue lors de ma création de la route!'));
            }
//            $modal = $this->renderView('AmsDistributionBundle:SuiviProduction:formDepotRoute.html.twig',array('form'=>$form->createView(),'is_new' => true));       
        
        
//            $response = array("modal"=>$modal, "idRoute"=> $idRoute, "alert" => $alert);  
            $response = array("idRoute"=> $idRoute, "alert" => $alert);  
            $return = json_encode($response);
            return new Response($return,200,array('Content-Type'=>'application/json'));  
        }
        
        $response = $this->renderView('AmsDistributionBundle:SuiviProduction:formDepotRoute.html.twig', array(
                                                                                    'form' => $form->createView(),
                                                                                    'is_new' => true));
        $return = json_encode($response);
        return new Response($return,200,array('Content-Type'=>'application/json'));  
    }
}
