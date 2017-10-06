<?php

namespace Ams\SilogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Entity\Profil;
use Ams\SilogBundle\Lib\StringLocal;

class NavigationController extends GlobalController {

    /**
     * 
     *  Page d'accueil
     */
    public function accueilAction() {

        $droits = $this->get('droits');
        $stringLocal = new StringLocal();
        if ($droits->session_ok() === true) {
            $em = $this->getDoctrine()->getManager();
            $categories = $em->getRepository('AmsSilogBundle:Categorie')->getCategorieByProfil($this->get('session')->get('PROFIL_ID'));
            $listeCategories = array();
            foreach ($categories as $categorie) {
                $listeCategories[$categorie['CAT_ID']]['id'] = $categorie['CAT_ID'];
                $listeCategories[$categorie['CAT_ID']]['libelle'] = $categorie['CAT_LIB'];
                $listeCategories[$categorie['CAT_ID']]['slug'] = $stringLocal->supprAccents($categorie['CAT_LIB']);
                $listeCategories[$categorie['CAT_ID']]['class'] = $categorie['IMG_CAT'];
                $listeCategories[$categorie['CAT_ID']]['page_defaut'] = $categorie['PAGE_DEFAUT']; 
            }
            $pageWiki = $this->container->getParameter('MROAD_WIKI_URL').'?title=Accueil';
            return $this->render('AmsSilogBundle:Statique:accueil_defaut.html.twig', array(
                        'listeCategories' => $listeCategories,
                        'pageWiki' => $pageWiki,
                        'session' => print_r($_SESSION, true)
            ));
        } else {
            $this->get('session')->getFlashBag()->add(
                    'notice', 'Session expirée'
            );

            $this->get('droits')->detruit_session();
            return $this->redirect($this->generateUrl('_ams_authentification'));
        }
    }

    /**
     * Page a propos 
     */
    public function aproposAction(){
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        
        $droits = $this->get('droits');
        $stringLocal = new StringLocal();
        if ($droits->session_ok() === true) {
            $sEnvActuel = $this->get('kernel')->getEnvironment();
            
            // Récupération des informations sur la DEV
            $aDevInfo = $this->getVersionInfo('dev', $sEnvActuel);
            $aDevInfo['uptodate'] = TRUE; // La Dev étant point de référence, est toujours à jour jusqu'à ce qu'on la compare au SVN
            // Récupération des informations sur la Recette
            $aRecetteInfo = $this->getVersionInfo('recette', $sEnvActuel);
            // Récupération des informations sur la Préprod
            $aPreprodInfo = $this->getVersionInfo('preprod', $sEnvActuel);
            // Récupération des informations sur la Prod
            $aProdInfo = $this->getVersionInfo('prod', $sEnvActuel);
            

            // Comparaison des versions
            $aEnvtsRev = array(
                'recette' => $aRecetteInfo['rev'], 
                'preprod' => $aPreprodInfo['rev'], 
                'prod' => $aProdInfo['rev']
                    );
            
                foreach ($aEnvtsRev as $sEnv => $iRev){
                    $bUpToDate = TRUE;
                    if ((int)$aDevInfo['rev'] > (int)$iRev){
                        $bUpToDate = FALSE;
                    }
                    
                    switch ($sEnv){
                        case 'recette':
                            $aRecetteInfo['uptodate'] = $bUpToDate;
                            break;
                        case 'preprod':
                            $aPreprodInfo['uptodate'] = $bUpToDate;
                            break;
                        case 'prod':
                            $aProdInfo['uptodate'] = $bUpToDate;
                            break;
                    }
                }
        }
        else {
            $this->get('session')->getFlashBag()->add(
                    'notice', 'Session expirée'
            );

            $this->get('droits')->detruit_session();
            return $this->redirect($this->generateUrl('_ams_authentification'));
        }
        return $this->render('AmsSilogBundle:Navigation:apropos.html.twig', array(
            'aDevDatas' => $aDevInfo,
            'aRecetteDatas' => $aRecetteInfo,
            'aPreprodDatas' => $aPreprodInfo,
            'aProdDatas' => $aProdInfo,
        ));
    }
    
    /**
     * Liste des sous Categories
     * @param string route = une route
     * $form  formulaire de filtre 
     * 
     */
    public function sousCategorieAction($route = '', $form = '') {

        $em = $this->getDoctrine()->getManager();

        if ($this->getRequest()->get('categorieId')) {
            $categorieId = $this->getRequest()->get('categorieId');
            $twig = 'accueil_sous_categorie.html.twig';
            $titre = ucwords(strtolower($this->getRequest()->get('slug')));
        } else {
            $page = $em->getRepository('AmsSilogBundle:Page')->findOneByidRoute($route);
            $categorieId = $page->getSsCategorie()->getCategorie()->getId();
            $twig = 'nav_sous_categorie.html.twig';
            $titre = ' ';
        }

        $categories = $em->getRepository('AmsSilogBundle:Categorie')->getCategorieByProfil($this->get('session')->get('PROFIL_ID'), $categorieId);

        $router = $this->container->get('router');
        foreach ($categories as $categorie) {
          if($router->getRouteCollection()->get($categorie['PAGE_DEFAUT_SCAT']) === null )
            continue;
            $libCategorie = $categorie['CAT_LIB'];
            $listeSousCategories[$categorie['SCAT_ID']]['libelle'] = $categorie['LIB_SOUS_CAT'];
            $listeSousCategories[$categorie['SCAT_ID']]['class'] = $categorie['IMG_SCAT'];
            $listeSousCategories[$categorie['SCAT_ID']]['page_defaut'] = $categorie['PAGE_DEFAUT_SCAT'];
            $pageWiki = $this->container->getParameter('MROAD_WIKI_URL').'?title='.str_replace(' ', '_', $categorie['CAT_LIB']);
        }
       // $libCategorie = array_shift($libCategories);
       // $pageWiki = array_shift($pageWiki);

        return $this->render('AmsSilogBundle:Statique:' . $twig, array(
                    'listeSousCategories' => $listeSousCategories,
                    'libCategorie' => $libCategorie,
                    'route' => $route,
                    'titre' => $titre,
                    'form' => $form,
                    'pageWiki'  => $pageWiki
        ));
    }

    /**
     * Liste des pages d'une sous_categorie
     * @param string route = une route
     * $form  formulaire de filtre si nécessaire
     * 
     */
    public function PageAction($route = '', $form = '') {

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('AmsSilogBundle:Page')->findOneByidRoute($route);
        $sousCategorieId = $page->getSsCategorie()->getId();
        $pages = $em->getRepository('AmsSilogBundle:Page')->getPageAcessible($this->get('session')->get('PROFIL_ID'), $sousCategorieId);

        $router = $this->container->get('router');
        foreach($pages as $key=>$page){
          if($router->getRouteCollection()->get($page['id_route']) === null )
            unset($pages[$key]);
        }
        
        return $this->render('AmsSilogBundle:Statique:nav_page.html.twig', array(
                    'pages' => $pages,
                    'route' => $route,
                    'form' => $form
        ));
    }

    /**
     * Récupère des informations sur une installation d'MRoad
     * @param string $evt L'environnement pour lequel récupérer les informations
     * @param string $sEnvActuel Environnement actuel
     * @return array $aInfo Le tableau contenant les informations pour l'environnement demandé
     */
    private function getVersionInfo($evt, $sEnvActuel){
        switch ($evt){
            case 'dev':
                // Récupération de l'URL de base
                $sURLBase = $this->container->getParameter('MROAD_VERSION_DEV_URL');
                $sGeoCURLBase = $this->container->getParameter('GEOC_VERSION_DEV_URL');
                break;
            case 'recette':
                // Récupération de l'URL de base
                $sURLBase = $this->container->getParameter('MROAD_VERSION_RECETTE_URL');
                $sGeoCURLBase = $this->container->getParameter('GEOC_VERSION_RECETTE_URL');
                break;
            case 'preprod':
                // Récupération de l'URL de base
                $sURLBase = $this->container->getParameter('MROAD_VERSION_PREPROD_URL');
                $sGeoCURLBase = $this->container->getParameter('GEOC_VERSION_PREPROD_URL');
                break;
            case 'prod':
                // Récupération de l'URL de base
                $sURLBase = $this->container->getParameter('MROAD_VERSION_PROD_URL');
                $sGeoCURLBase = $this->container->getParameter('GEOC_VERSION_PROD_URL');
                break;
        }
        
        // URL du fichier de version
        $sUrlTxtVersion = $sURLBase.'mroad_version.txt';
        try{
        // Informations sur la version d'MRoad    
        $sInfoVersion = file_get_contents($sUrlTxtVersion);
        $aInfoVersionArr = explode('|', $sInfoVersion);

        $oDate = \DateTime::createFromFormat('YmjHis', trim($aInfoVersionArr[1]));
        
         // Récupération des infos sur Geoconcept
        $sGeoWebVersion = '--';
        if (isset($sGeoCURLBase)){
            $sPageGeo = file_get_contents($sGeoCURLBase);
            preg_match("/<input type='hidden' name='internalVersion' value='(.*?)'\/\>/", $sPageGeo, $aGeoValues);
            $sGeoWebVersion = empty($aGeoValues) ? 'N/D' : $aGeoValues[1];
        }
        
        // Récupération des infos sur HTC / Openlayers
        // URL du fichier JS d'HTC
        $sVersionHTC = '--';
        $sVersionOL = '--';
        $sUrlJsHTC = $sURLBase.$this->container->getParameter('GEOC_HTC_VERSION_PATH');
        $sJsHTC = file_get_contents($sUrlJsHTC);
        if (!empty($sJsHTC)){
            preg_match("/VERSION_NUMBER: 'Release (.*?)',/", $sJsHTC, $aOpenLayersValues);
            $sVersionOL = empty($aOpenLayersValues) ? 'N/D' : $aOpenLayersValues[1];
        }
        
        if (!empty($sJsHTC)){
            preg_match("/GCUI.Map.Version = '(.*?)';/", $sJsHTC, $aHTCValues);
            $sVersionHTC = empty($aHTCValues) ? 'N/D' : $aHTCValues[1];
        }
        
        // On marque l'environnement actuel
        $bEnvActif = $sEnvActuel == $evt ? TRUE : FALSE;
        $aInfo = array(
            'rev' => $aInfoVersionArr[0],
            'date' => $oDate,
            'geoweb' => $sGeoWebVersion,
            'openlayers' => $sVersionOL,
            'htc' => $sVersionHTC,
            'actif' => $bEnvActif
        );
        
        return $aInfo;
        }
 catch ( Symfony\Component\Config\Definition\Exception\Exception $e){
     return  null;
 }
    }
}
