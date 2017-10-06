<?php

namespace Ams\SilogBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Repository\PageRepository;
use Ams\SilogBundle\Repository\UtilisateurRepository;
use Ams\SilogBundle\Entity\Profil;
use Ams\SilogBundle\Entity\Page;
use Ams\SilogBundle\Lib\StringLocal;

class StatiqueController extends GlobalController {

    /**
     * 
     * @param int $profilId
     * @return Array liste des menus accessible au profil
     */
    public function navigationAction() {
        
        $stringLocal =  new StringLOcal();
        $profilId = $this->get('session')->get('PROFIL_ID');
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AmsSilogBundle:Categorie')->getCategorieByProfil($profilId);
        
        $listeCategories  = array();
        foreach ($categories as $categorie) {
            $listeCategories[$categorie['CAT_ID']]['id'] = $categorie['CAT_ID'];
            $listeCategories[$categorie['CAT_ID']]['libelle'] = $categorie['CAT_LIB'];
            $listeCategories[$categorie['CAT_ID']]['class'] = $categorie['IMG_CAT'];
            $listeCategories[$categorie['CAT_ID']]['slug'] = $stringLocal->supprAccents($categorie['CAT_LIB']);
            $listeCategories[$categorie['CAT_ID']]['page_defaut'] = $categorie['PAGE_DEFAUT'];
        }

        return $this->render('AmsSilogBundle:Statique:navigation.html.twig', array('listeCategories' => $listeCategories));
    }

    /**
     * Affiche Nom et prenom de l'utilisateur connecté
     *  
     */
    public function identifiantAction() {
        
        $aValTwig = array();
        $srv_droits = $this->get('droits');
        if ($srv_droits->session_ok()) {
            $aValTwig['nom_prenom'] = $this->get('session')->get('PRENOM').' '.$this->get('session')->get('NOM');
            $aValTwig['profil'] = $this->get('session')->get('PROFIL');
            $aValTwig['btn_deconnexion'] = '1';
        }

        return $this->render('AmsSilogBundle:Statique:identifiant.html.twig', $aValTwig);
    }

    /**
     *  file d'ariane 
     * @param string $id_route_courant
     * @return Array
     */
    public function filArianeAction($id_route_courant) {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('AmsSilogBundle:Page')->findOneByidRoute($id_route_courant);
//        $page = $em->getRepository('AmsSilogBundle:Page')->findOneByidRoute('liste_pai_mensuel');

        $aValTwig = array();
        if($page != '' || $page != null){
            $aValTwig['categorieLib'] = $page->getSsCategorie()->getCategorie()->getLibelle();
            $aValTwig['categorieLink'] = $page->getSsCategorie()->getCategorie()->getPageDefaut();
            $aValTwig['categorieId'] = $page->getSsCategorie()->getCategorie()->getId();

            $aValTwig['ssCategorieLib'] = $page->getSsCategorie()->getLibelle();
            $aValTwig['ssCategorieLink'] = $page->getSsCategorie()->getPageDefaut();
            $aValTwig['pageLib'] = $page->getDescCourt();
            $aValTwig['pageLink'] = $page->getIdRoute();
            $aValTwig['pageWiki'] = $this->container->getParameter('MROAD_WIKI_URL').'?title='.str_replace(' ', '_', $page->getDescription());
        }

        return $this->render('AmsSilogBundle:Statique:fil_ariane.html.twig', $aValTwig);
    }

    /**
     * 
     * @return type
     */
    public function faqAction() {
        $env = $this->get('kernel')->getEnvironment();
        switch ($env) {
            case 'dev':
                    $jsonUrl = $this->container->getParameter('MROAD_VERSION_DEV_URL').'/faq.json';
            break;
            case 'recette':
                    $jsonUrl = $this->container->getParameter('MROAD_VERSION_RECETTE_URL').'/faq.json';     
            break;
            case 'preprod':
                $jsonUrl = $this->container->getParameter('MROAD_VERSION_PREPROD_URL');
            break;
            case 'prod':
                $jsonUrl = $this->container->getParameter('MROAD_VERSION_PROD_URL');
            break;
        }
         return $this->render('AmsSilogBundle:Statique:faq.html.twig', array('jsonFile'=>$jsonUrl));
    }

    
}
