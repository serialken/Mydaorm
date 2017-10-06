<?php

namespace Ams\SilogBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Request;

class AuthentificationController extends GlobalController {

   

    public function authentificationAction(Request $request) {
       $this->init();
        
        if ($this->srv_request->query->get('modal') && $this->srv_request->query->get('modal') == 1) {
            return $this->render('AmsSilogBundle:Authentification:redirect_authentification.html.twig');
        }

        $form = $this->createFormBuilder()
                ->add("utilisateur", 'text', array('label' => "Nom d'utilisateur",
                    'max_length' => 20,
                    'required' => true, /* par defaut, c'est true */
                    'trim' => true, /* par defaut, c'est true */
                        )
                )
                ->add('mdp', 'password', array('label' => "Mot de passe",
                    'max_length' => 20,
                        )
                )
                ->getForm();

        $form->handleRequest($request);
      
        if ($form->isValid()) {
           
            $get = $request->get('form');
            $_utilisateur = $get['utilisateur'];
            $_mdp = $get['mdp'];
            $droits = $this->get('droits');
 
            if ($droits->authentification($_utilisateur, $_mdp) !== true) {
                return $this->render('AmsSilogBundle:Authentification:formulaire.html.twig', array(
                            'error' => 'Utilisateur inconnu',
                            'form' => $form->createView(),
                            'session' => print_r($this->get('session'), true),
                ));
                
             
            } 
            else { 
                /** SAVE CONNECTION DATA**/
                $em = $this->getDoctrine()->getManager();
                $em->getRepository('AmsSilogBundle:TraceUser')->newConnection(array('id_user'=>$this->get('session')->get('UTILISATEUR_ID'),'connect_time'=>date('Y-m-d H:i:s'),'browser'=>$_SERVER['HTTP_USER_AGENT'],'ip_address' => $_SERVER['REMOTE_ADDR']));

                $route_defaut = $this->get('param')->get('P_ACCUEIL_DEFAUT');  
                $route_param = array();
                $srv_session = $this->get('session');

                if ($srv_session->has('DERNIERE_PAGE_ROUTE')) {
                    $route_defaut = $srv_session->get('DERNIERE_PAGE_ROUTE');
                }
                if ($srv_session->has('DERNIERE_PAGE_ROUTE_PARAM')) {
                    $route_param = $srv_session->get('DERNIERE_PAGE_ROUTE_PARAM');
                }
                return $this->redirect($this->generateUrl($route_defaut, $route_param));
            }
        }

        // Affichage du formulaire de login
        return $this->render('AmsSilogBundle:Authentification:formulaire.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    
    
    
    public function deconnexionAction() {
        $this->get('droits')->detruit_session();

        return $this->redirect($this->generateUrl('_ams_authentification'));
    }

    public function messagesAction() {
        if (!isset($this->srv_droits)) {
            $this->init();
        }
        $aValTwig = array();
        $aValTwig['url_derniere_page'] = $this->getUrl_derniere_page();
        $aValTwig['session'] = print_r($_SESSION, true);
        return $this->render('AmsSilogBundle:Authentification:messages.html.twig', $aValTwig);
    }

}
