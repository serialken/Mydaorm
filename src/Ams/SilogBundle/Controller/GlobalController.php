<?php

namespace Ams\SilogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use APY\DataGridBundle\Grid\Source\Vector;
use Ams\SilogBundle\Repository\PageRepository;
use Ams\SilogBundle\Repository\PageElementRepository;
use APY\DataGridBundle\Grid\Export\CSVExport;

class GlobalController extends Controller {

    protected $page_courante_route;
    protected $acces_page;
    protected $oPages;
    protected $oPagesElements;
    protected $droits_prf_elts_page_courante; // Droit a des elts pour la page courante. array[id_elt][]=droit
    protected $elts_page_accessible;
    protected $session_ok;
    protected $srv_droits;
    protected $srv_param;
    protected $srv_request;
    protected $srv_session;
    protected $oFirePHP;

    /**
     * 
     * Initialisation des parametres utiles pour les controlleurs qui heritent de cette classe
     */
    public function init() {
        $this->srv_droits = $this->get('droits');
        $this->srv_param = $this->get('param');
        $this->srv_request = $this->get('request');
        $this->srv_session = $this->get('session');

        $this->elts_page_accessible = array();
        $this->droits_prf_elts_page_courante = array();

        $this->page_courante_route = $this->srv_request->get('_route');
        if (!is_null($this->page_courante_route)) {
            $page = $this->getDoctrine()->getRepository('AmsSilogBundle:Page')->findOneByIdRoute($this->page_courante_route);
            $this->titre_page = (isset($page)?$page->getDescription():'');

            $aPages = $this->srv_session->get('PAGES');
            $this->acces_page = (is_array($aPages) && in_array($this->page_courante_route, $aPages) ? true : false);

            $this->oPagesElements = $this->getDoctrine()->getManager()->getRepository('AmsSilogBundle:PageElement')
                    ->getPageElementByRoute($this->page_courante_route);

            $this->elts_page_accessible = array();

            $this->getEltsAccessible();
        }

        $this->oFirePHP = NULL;
    }

    /**
     * 
     * Les elements accessibles par le profil courant. Retourne array(id_elt => desc_court_elt)
     * @return array
     */
    protected function getEltsAccessible($page_route = '') {
        if (!isset($this->page_courante_route)) {
            $this->init();
        }
        if ($page_route == '') {
            $page_route = $this->page_courante_route;
        }
        $page_accessibles = $this->getDoctrine()->getManager()->getRepository('AmsSilogBundle:PageElement')
                ->getElementAcessible($this->srv_session->get('PROFIL_ID'), $page_route);

        foreach ($page_accessibles as $page_accessible) {
            $this->elts_page_accessible[] = $page_accessible['desc_court'];
        }
        return $this->elts_page_accessible;
    }

    /**
     *  Verifie si l'utiliseur à le droit de modifie
     * une dhtmlxGrid 
     * @return 1 ou ''
     */
    public function isGridEditable() {
        if (in_array('MODIF', $this->elts_page_accessible)) {
            return '';
        }
        return 1;
    }

    /**
     * verifie si $elt appartient au pageElement de la page identifie par $page_route est accessible avec le compte actuel
     * @param string $page_route
     * @return boolean
     */
    public function isPageElement($elt, $page_route = '') {
        if (!isset($this->page_courante_route)) {
            $this->init();
        }
        if ($page_route == '') {
            $page_route = $this->page_courante_route;
            $elts_page_accessible = $this->elts_page_accessible;
        } else {
            $elts_page_accessible = $this->getEltsAccessible($page_route);
        }
        return (in_array($elt, $elts_page_accessible));
    }

    /**
     * verifie si la page identifie par $page_route est accessible avec le compte actuel
     * @param string $page_route
     * @return boolean
     */
    protected function is_accessible($page_route = '') {
        if (!isset($this->page_courante_route)) {
            $this->init();
        }
        if ($page_route == '') {
            $page_route = $this->page_courante_route;
        }
        $aPages = $this->srv_session->get('PAGES');
        return (is_array($aPages) && in_array($page_route, $aPages) ? true : false);
    }

    protected function verif_acces($iModal = 0) {
        if (!isset($this->srv_droits)) {
            $this->init();
        }
        $this->session_ok = 1;
        // Verification de la session
        if ($this->srv_droits->session_ok() !== true) { // session expiree
            $this->srv_session->getFlashBag()->add(
                    'notice', 'Session expirée'
            );
            // stockage de la route par defaut apres l'authentification
            $route_defaut = $this->srv_param->get('P_ACCUEIL_DEFAUT');
            $route_param = array();
            if ($this->srv_session->has('DERNIERE_PAGE_ROUTE')) {
                $route_defaut = $this->srv_session->get('DERNIERE_PAGE_ROUTE');
            }
            if ($this->srv_session->has('DERNIERE_PAGE_ROUTE_PARAM')) {
                $route_param = $this->srv_session->get('DERNIERE_PAGE_ROUTE_PARAM');
            }
            // suppression de la session expiree
            $this->srv_droits->detruit_session();
            // mise en session de la route par defaut apres l'authentification    
            $this->srv_session->set('DERNIERE_PAGE_ROUTE', $route_defaut);
            $this->srv_session->set('DERNIERE_PAGE_ROUTE_PARAM', $route_param);

            $this->session_ok = 0;
        } else {
            // Verifier le droit d'acces a une page		
            if (!$this->is_accessible($this->page_courante_route)) {
                $this->srv_session->getFlashBag()->add(
                        'notice', "Vous n'avez pas le droit d'aller à la page à laquelle vous avez tenté d'accéder"
                );
            }
        }

        if ($this->session_ok === 0) { // si session expiree
            if ($iModal == 1) {
                return $this->redirect($this->generateUrl('_ams_authentification', array('modal' => 1)));
            } else {
                return $this->redirect($this->generateUrl('_ams_authentification'));
            }
        }
        if ($this->acces_page === false) { // si pas de droit acces a cette page
            return $this->redirect($this->generateUrl('_ams_messages'));
            // return $this->redirect($this->getUrl_derniere_page());
        }
        return true;
    }

    /**
     * 
     * Filtre & Tableau (APY/DataGridBundle) en fonction des droits
     * @param array $aDonnees
     * @param array $aColonnesSuppl
     * @param array $aOptions
     * @param array $aTousEltsGrid
     */
    protected function apy_grid($aDonnees, $aColonnesSuppl = array(), $aOptions = array(), $aTousEltsGrid = array(), $export = false) {
        // $this->droits_prf_elts_page_courante --- Array([9] => Array([0] => LIRE        [1] => MODIF))
        // $this->elts_page_accessible --- Array([9] => ACCES_PAGE)
        // $aBDDEltsPage = $this->oPagesElements->getPageElements($this->oPages->getPageParRoute($this->page_courante_route)->getId(), 'Desc_court');

        $aBDDEltsPage = array();
        foreach ($this->oPagesElements as $pageElement) {
            $aBDDEltsPage[$pageElement->getId()] = $pageElement->getDescCourt();
        }

        $oSourceGrid = new Vector($aDonnees);
        $oGrid = $this->get('grid');

        // Message par defaut
        $oGrid->setNoDataMessage("Aucune donnée trouvée");
        $oGrid->setNoResultMessage('Aucun résultat');

        $oGrid->setSource($oSourceGrid);
        if ($export) {
            $oGrid->addExport(new CSVExport('CSV Export in French', 'export', array('delimiter' => ';')));
        }





        // Nombre de resultats par page
        $oGrid->setLimits(isset($aOptions['nombreResultatsParPage']) ? $aOptions['nombreResultatsParPage'] : $this->getNombreResultatsParPage('NB_RES_PAR_PAGE_DEFAUT') ); // nombre de reponses par page
        // Libelle de chaque colonne
        $aColsDonnees = array();
        if (is_array($aDonnees) && !empty($aDonnees)) {
            $aColsDonnees = array_keys($aDonnees[0]);
        }
        if (isset($aOptions['libelles']) && !empty($aColsDonnees)) {
            foreach ($aOptions['libelles'] as $sC => $sV) {
                $oGrid->getColumn($sC)->setTitle($sV);
            }
        }

        // ----- Filtre
        // Hors filtre
        // Hors filtre par defaut
        if (!empty($aColsDonnees)) {
            foreach ($aColsDonnees as $sV) {
                if (!isset($aTousEltsGrid['FILT']) || empty($aTousEltsGrid['FILT']) || (isset($aTousEltsGrid['FILT']) && !in_array($sV, $aTousEltsGrid['FILT']) && ((isset($aOptions['horsFiltre']) && !in_array($sV, $aOptions['horsFiltre'])) || !isset($aOptions['horsFiltre'])))) {
                    $aOptions['horsFiltre'][] = $sV;
                }
            }
        }


        /** récupération des pageElements dont le profil a accés */
        $profilPageElement = $this->getDoctrine()->getManager()->getRepository('AmsSilogBundle:Profil')
                ->getPageElementByProfilAndRoute($this->srv_session->get('PROFIL_ID'), $this->page_courante_route);
        foreach ($profilPageElement->getPageElements() as $pageElement) {
            $this->elts_page_accessible[$pageElement->getId()] = $pageElement->getDescCourt();
        }

        // Verification droit
        if (isset($aTousEltsGrid['FILT'])) {
            foreach ($aTousEltsGrid['FILT'] as $sVal) {
                // Hors filtre <=> Dans $aTousEltsGrid['FILT']  && Pas dans $aBDDEltsPage && Pas dans $this->elts_page_accessible
                //if(in_array($sVal, $aBDDEltsPage) && !in_array('FILT_'.$sVal, $this->elts_page_accessible))
                if (in_array('FILT_' . $sVal, $aBDDEltsPage) && !in_array('FILT_' . $sVal, $this->elts_page_accessible)) {
                    if (!isset($aOptions['horsFiltre']) || !in_array($sVal, $aOptions['horsFiltre'])) {
                        $aOptions['horsFiltre'][] = $sVal;
                    }
                }
            }
        }


        if (isset($aOptions['horsFiltre'])) {
            foreach ($aOptions['horsFiltre'] as $sV) {
                $oGrid->getColumn($sV)->setFilterable(false);
            }
        }

        // ----- Operateur filtre
        // Operateur filtre non visible - ne pas afficher tout operateur
        if (!empty($aColsDonnees)) {
            foreach ($aColsDonnees as $sV) {
                $oGrid->getColumn($sV)->setOperatorsVisible(false);
            }
        }

        // ----- Colonnes
        // Colonnes a ne pas afficher
        // Colonnes a ne pas afficher par defaut
        if (!empty($aColsDonnees)) {
            foreach ($aColsDonnees as $sV) {
                if (!isset($aTousEltsGrid['COL']) || empty($aTousEltsGrid['COL']) || (isset($aTousEltsGrid['COL']) && !in_array($sV, $aTousEltsGrid['COL']) && ((isset($aOptions['horsColonnes']) && !in_array($sV, $aOptions['horsColonnes'])) || !isset($aOptions['horsColonnes'])))) {
                    $aOptions['horsColonnes'][] = $sV;
                }
            }
        }

        // Verification droit
        if (isset($aTousEltsGrid['COL'])) {
            foreach ($aTousEltsGrid['COL'] as $sVal) {
                // Hors colonnes <=> Dans $aTousEltsGrid['COL']  && Pas dans $aBDDEltsPage && Pas dans $this->elts_page_accessible
                if (in_array('COL_' . $sVal, $aBDDEltsPage) && !in_array('COL_' . $sVal, $this->elts_page_accessible)) {
                    if (!isset($aOptions['horsColonnes']) || !in_array($sVal, $aOptions['horsColonnes'])) {
                        $aOptions['horsColonnes'][] = $sVal;
                    }
                }
            }
        }

        if (isset($aOptions['horsColonnes'])) {
            foreach ($aOptions['horsColonnes'] as $sV) {
                $oGrid->getColumn($sV)->setVisible(false);
            }
        }

        // ----- Colonnes supplementaires
        if (!empty($aColonnesSuppl)) {
            $iRang = 100;
            foreach ($aColonnesSuppl as $sIdCol => $aArr) {
                if (in_array($sIdCol, $aTousEltsGrid['ACT']) && ((in_array($sIdCol, $aBDDEltsPage) && in_array($sIdCol, $this->elts_page_accessible)) || (!in_array($sIdCol, $aBDDEltsPage) && !in_array($sIdCol, $this->elts_page_accessible)))) {
                    if (isset($aArr['actionscolumns'])) {
                        // Ajout de colonnes
                        $oGrid->addColumn($aArr['actionscolumns'], (isset($aArr['rang']) ? $aArr['rang'] : $iRang));
                        // Rattacher une "rowAction" au tableau
                        if (isset($aArr['rowaction'])) {
                            $oGrid->addRowAction($aArr['rowaction']);
                        }
                    }
                    $iRang++;
                }
            }
        }

        return $oGrid;
    }

    /**
     * 
     * Formulaire en fonction des droits
     * @param string $sAction
     * @param array $aTousEltsForm
     */
    protected function formulaire($sAction = "", $aTousEltsForm = array(), $objet = NULL) {
        // $this->droits_prf_elts_page_courante --- Array([9] => Array([0] => LIRE        [1] => MODIF))
        // $this->elts_page_accessible --- Array([9] => ACCES_PAGE)
        //$aBDDEltsPage = $this->oPagesElements->getPageElements($this->oPages->getPageParRoute($this->page_courante_route)->getId(), 'Desc_court');
        // $route = $this->srv_request->get('_route');

        $route = $this->getRequest()->get('_route');
        $page = $this->getDoctrine()->getRepository('AmsSilogBundle:Page')
                ->findOneByIdRoute($route);

        $pageElements = $this->getDoctrine()->getRepository('AmsSilogBundle:PageElement')
                ->findByPage($page);

        foreach ($pageElements as $pageElement) {
            $aBDDEltsPage[] = $pageElement->getDescCourt();
        }

//		echo "<pre>"; print_r($this->droits_prf_elts_page_courante);echo "</pre><hr />";
//		echo "<pre>"; print_r($this->elts_page_accessible);echo "</pre><hr />";
//		echo "<pre>"; print_r($aBDDEltsPage);echo "</pre><hr />";

        $oForm = $this->createFormBuilder($objet);
        if ($sAction != "") {
            $oForm->setAction($sAction);
        }
        foreach ($aTousEltsForm as $sIdEltK => $aCaracteristiquesElt) {
            $bAutorisation = false;
            $sId = (isset($aCaracteristiquesElt['ID']) ? $aCaracteristiquesElt['ID'] : strtolower($sIdEltK));
            $sType = (isset($aCaracteristiquesElt['TYPE']) ? $aCaracteristiquesElt['TYPE'] : 'text');
            $aOptions = (isset($aCaracteristiquesElt['OPTION']) ? $aCaracteristiquesElt['OPTION'] : array());
            // Ici on verifie si le profil a le droit d'editer a l'element
            $oForm->add($sId, $sType, $aOptions);
        }
        return $oForm->getForm();



        $aEltAccessibleIdParNom = array_flip($this->elts_page_accessible); // Id d'un element a partir de son nom
        foreach ($aTousEltsForm as $sIdEltK => $aCaracteristiquesElt) {
            $bAutorisation = false;
            $sId = (isset($aCaracteristiquesElt['ID']) ? $aCaracteristiquesElt['ID'] : strtolower($sIdEltK));
            $sType = (isset($aCaracteristiquesElt['TYPE']) ? $aCaracteristiquesElt['TYPE'] : 'text');
            $aOptions = (isset($aCaracteristiquesElt['OPTION']) ? $aCaracteristiquesElt['OPTION'] : array());

            // On autorise si l'elt n'est pas mentionne dans la BDD ou l'elt est autorise pour le profil.
            if (!in_array($sIdEltK, $aBDDEltsPage)) {
                $bAutorisation = true;
            } else if (in_array($sIdEltK, $aBDDEltsPage) && in_array($sIdEltK, $this->elts_page_accessible)) {
                $bAutorisation = true;
                // Verifier si LIRE ou MODIF
                if (isset($aEltAccessibleIdParNom[$sIdEltK]) && isset($this->droits_prf_elts_page_courante[$aEltAccessibleIdParNom[$sIdEltK]])) {
                    if (in_array('MODIF', $this->droits_prf_elts_page_courante[$aEltAccessibleIdParNom[$sIdEltK]])) {
                        if (isset($aCaracteristiquesElt['TYPE_MODIF'])) {
                            $sType = $aCaracteristiquesElt['TYPE_MODIF'];
                        }
                        if (isset($aCaracteristiquesElt['OPTION_MODIF'])) {
                            if (is_array($aCaracteristiquesElt['OPTION_MODIF']) && !empty($aCaracteristiquesElt['OPTION_MODIF'])) {
                                foreach ($aCaracteristiquesElt['OPTION_MODIF'] as $sK => $sV) {
                                    $aOptions[$sK] = $sV;
                                }
                            }
                        }
                    } else if (in_array('LIRE', $this->droits_prf_elts_page_courante[$aEltAccessibleIdParNom[$sIdEltK]])) {
                        if (isset($aCaracteristiquesElt['TYPE_LIRE'])) {
                            $sType = $aCaracteristiquesElt['TYPE_LIRE'];
                        }
                        if (isset($aCaracteristiquesElt['OPTION_LIRE'])) {
                            if (is_array($aCaracteristiquesElt['OPTION_LIRE']) && !empty($aCaracteristiquesElt['OPTION_LIRE'])) {
                                foreach ($aCaracteristiquesElt['OPTION_LIRE'] as $sK => $sV) {
                                    $aOptions[$sK] = $sV;
                                }
                            }
                        }
                    }
                }
            }
            if ($bAutorisation === true) {
                $oForm->add($sId, $sType, $aOptions);
            }
        }
        return $oForm->getForm();
    }

    /**
     * 
     * enregistre comme derniere page
     */
    protected function setDerniere_page() {
        $this->srv_session->set('DERNIERE_PAGE_ROUTE', $this->srv_request->get('_route'));
        $this->srv_session->set('DERNIERE_PAGE_ROUTE_PARAM', $this->srv_request->get('_route_params'));
    }

    /**
     * 
     * recupere l'URl de la page dernierement enregistree
     */
    protected function getUrl_derniere_page() {
        $route_defaut = $this->srv_param->get('P_ACCUEIL_DEFAUT');
        $route_param = array();
        if ($this->srv_session->has('DERNIERE_PAGE_ROUTE')) {
            $route_defaut = $this->srv_session->get('DERNIERE_PAGE_ROUTE');
        }
        if ($this->srv_session->has('DERNIERE_PAGE_ROUTE_PARAM')) {
            $route_param = $this->srv_session->get('DERNIERE_PAGE_ROUTE_PARAM');
        }
        return $this->generateUrl($route_defaut, $route_param);
    }

    /**
     * 
     * Nombre de resultats par page
     * @param int $param_nb_par_page
     */
    protected function getNombreResultatsParPage($param_nb_par_page = 'NB_RES_PAR_PAGE_DEFAUT') {
        $aRetour = 10;

        if ($this->srv_param->defini($param_nb_par_page)) {
            $aRetour = explode(',', $this->srv_param->get($param_nb_par_page));
            //$aRetour	= intval($this->srv_param->get($param_nb_par_page));
        }
        return $aRetour;
    }

    /**
     * 
     * @param string $sCmd La commande à exécuter
     * Testé uniquement sur Linux pour l'instant
     * @param array $aOptions Un tableau d'options array('specialRedirect' => 'chemin/vers/fichier/de/sortie') 
     * @author Marc-Antoine ADELISE
     */
    public static function bgCommandProxy($sCmd, $aOptions = null) {
        if (substr(php_uname(), 0, 7) == "Windows") {
            $handle = popen('start /b  ' . $sCmd . ' 2>&1', 'r');
            //           echo "'$handle'; " . gettype($handle) . "\n";
            $read = fread($handle, 2096);
            //           echo $read;
            pclose($handle);

            //pclose(popen("start /B " . $sCmd, "r"));
        } else {
            $sSudoSyntaxe = '';
            $fichierRedirection = empty($aOptions['specialRedirect']) ? '/dev/null' : $aOptions['specialRedirect'];
            if (!empty($aOptions['sudo'])){
                $sSudoSyntaxe = 'sudo -u '.$aOptions['sudo']." ";
            }
            exec($sSudoSyntaxe."nohup " . $sCmd . " > " . $fichierRedirection . " &");
        }
    }

    /**
     * Méthode d'initialisation de FirePHP
     */
    public function initFirePHP() {
        // Initialisation de FirePHP
        if (is_null($this->oFirePHP)) {
            if ($this->has('fire_php')) {
                $this->oFirePHP = $this->get('fire_php');
            }
        }

        return $this->oFirePHP;
    }

    
    
    /**
     * [getOptionList description]
     * @param  [type] $aElement
     * @return [type]
     */
    public function getOptionList($aElement, $withEmptyOption){
       
        $optionList = ($withEmptyOption == true ) ? '<option value=""></option>' : "";
        foreach ($aElement as $key => $value) {
            ///$optionList .='<option value="'.$value->getId().'"> <![CDATA['.$value->getLibelle().']]></option>';
            
            $optionList .= '<option value ="' . $value->getId() . '"><![CDATA[' . $value->getLibelle() . ']]></option>';
        }
        
       
        
        return $optionList;
    }
    

    /** 
     *  array_column en php 5.4
     * @param array $input
     * @param type $column_key
     * @param type $index_key
     * @return array
     */
   public  function array_column(array $input, $column_key, $index_key = null) {
        $result = array();
        foreach ($input as $k => $v)
            $result[$index_key ? $v[$index_key] : $k] = $v[$column_key];
        return $result;
    }

}
