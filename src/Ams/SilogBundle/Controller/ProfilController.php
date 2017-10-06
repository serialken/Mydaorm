<?php

namespace Ams\SilogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ams\SilogBundle\Controller\GlobalController;
use APY\DataGridBundle\Grid\Source\Vector;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use Ams\SilogBundle\Lib\StringLocal;
use Ams\SilogBundle\Entity\Utilisateur;
use Ams\SilogBundle\Entity\Profil;
use Symfony\Component\HttpFoundation\Response;

/**
 * Profil  controller.
 *
 */
class ProfilController extends GlobalController {
    /*
     * Liste des profils
     */

    public function listeAction() {
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        // stocker cette page comme la derniere visitee apres expiration de session
        $this->setDerniere_page();

        $aValTwig = array();
        $aDonnees = array();
        // Les elements du Grid (Partie "filtre", "colonne", "colonne_action")
        $aTousEltsGrid = array(
            'FILT' => array('LIBELLE', 'NOM_PRENOM'),
            'COL' => array('LIBELLE', 'NOM_PRENOM'),
            'ACT' => array('EDIT', 'SUPPR'),
        );


        $em = $this->getDoctrine()->getManager();
        $profils = $em->getRepository('AmsSilogBundle:Profil')->findAll();

        foreach ($profils as $key => $profil) {
            $aDonnees[$key]['id'] = $profil->getId();
            $aDonnees[$key]['LIBELLE'] = $profil->getLibelle();

            $listUtilisateur = array();
            $utilisateurs = $em->getRepository('AmsSilogBundle:Utilisateur')->findByProfil($profil->getId());
            foreach ($utilisateurs as $utilisateur) {
                $listUtilisateur[] = $utilisateur->getPrenom() . " " . $utilisateur->getNom();
            }

            $aDonnees[$key]['NOM_PRENOM'] = implode(',', $listUtilisateur);
        }


        /*         * ******** Filtre & Tableau en fonction des droits ********* */
        // ----- Options
        $aOptions = array();
        $aOptions['nombreResultatsParPage'] = $this->getNombreResultatsParPage('NB_RES_PAR_PAGE_UTL');
        $aOptions['libelles'] = array(
            'id' => 'ID',
            'LIBELLE' => 'Nom du profil',
            'NOM_PRENOM' => 'Utilisateurs'
        );


        // ----- Colonnes Supplementaires
        $aColonnesSuppl = array();
        if ($this->is_accessible('admin_ajout_profil')) {
            $aValTwig['lien_nouveau'] = 'admin_ajout_profil';
            $aColonnesSuppl['EDIT']['actionscolumns'] = new ActionsColumn('EDIT', 'Edition');
            $aColonnesSuppl['EDIT']['rang'] = 4;
            // Rattacher une "rowAction" a chaque "Actions Column"
            $rowActionEdit = new RowAction('Editer', 'admin_ajout_profil', false, '_blank', array('class' => 'nyroModal'));
            $rowActionEdit->setColumn('EDIT'); // "EDIT" <=> identifiant de la colonne 
            //$rowActionEdit->setRouteParameters(array("PRF_CODE", "act" => "edit"));
            $aColonnesSuppl['EDIT']['rowaction'] = $rowActionEdit;


            $aColonnesSuppl['SUPPR']['actionscolumns'] = new ActionsColumn('SUPPR', 'Supression');
            $aColonnesSuppl['SUPPR']['rang'] = 5;
            // Rattacher une "rowAction" a chaque "Actions Column"
            $rowActionSuppr = new RowAction('Suppr', 'admin_supp_profil', true, null);
            $rowActionSuppr->setColumn('SUPPR');
            $rowActionSuppr->setConfirmMessage("Voulez-vous supprimer ce profil ?");
            $aColonnesSuppl['SUPPR']['rowaction'] = $rowActionSuppr;
        }

        $oGrid = $this->apy_grid($aDonnees, $aColonnesSuppl, $aOptions, $aTousEltsGrid);
        // Pour le GRID, on peut utiliser la methode getGridResponse ou isReadyForRedirect. return $grid->getGridResponse(); ou $grid->isReadyForRedirect();return array('grid' => $grid,);
        return $oGrid->getGridResponse('AmsSilogBundle:Profil:liste.html.twig', $aValTwig);
    }

    /**
     * Ajout ou modification profil
     */
    public function ajoutAction(Request $request) {

        $bVerifAcces = $this->verif_acces();

        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $oProfil = new Profil();
        $em = $this->getDoctrine()->getManager();
        if($request->get('id')){
            $oProfil = $em->getRepository('AmsSilogBundle:Profil')->findOneById($request->get('id'));
        }
        $sAction = $this->generateUrl($request->get('_route'), array('id' => $request->get('id')));

        $formElement = array(
            'code' => array('TYPE' => 'text', 'OPTION' => array('label' => "Code",
                    'max_length' => 20,
                    'required' => true,
                    'trim' => true,
                )
            ),
            'libelle' => array('TYPE' => 'text', 'OPTION' => array('label' => "Libellé",
                    'max_length' => 45,
                    'required' => true,
                )
            ),
           /* 'VALIDATION' => array('TYPE' => 'submit', 'OPTION' => array('attr' => array('class' => 'btn btn-primary'),)
            ),*/
        );

       $oForm = $this->formulaire($sAction, $formElement, $oProfil);
 
       $oForm->handleRequest($request);
       if ($oForm->isValid()) {
            $em->persist($oProfil);
            $em->flush();
            //$modal = $this->renderView('AmsSilogBundle:Profil:modif.html.twig');
            $modal = $this->modifAction(true);
            $return = json_encode($modal);
            return new Response($return, 200, array('Content-Type'=>'Application/json')); 
        }

       $modal = $this->renderView('AmsSilogBundle:Profil:ajout.html.twig', array('form' => $oForm->createView(), 'formAction' =>$sAction));
       $return = json_encode($modal);
       return new Response($return, 200, array('Content-Type'=>'Application/json'));
    }

    /**
     * Modification d'un profil
     */
    public function modifAction($ajax = false) {

        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $em = $this->getDoctrine()->getManager();
        $profils = $em->getRepository('AmsSilogBundle:Profil')->findAll();

        $aValTwig['choisissez'] = $this->srv_param->get('MSG_CHOIX_SELECTEUR');
        $aValTwig['separateur_elts_form'] = $this->srv_param->get('SEPARATEUR_ELTS_FORM');

        $aTousEltsForm2 = array(
            'PRF_MENU' => array('TYPE' => 'entity', 'OPTION' => array('label' => "Catégorie",
                    'class' => 'AmsSilogBundle:Profil',
                    'property' => 'libelle',
                    'required' => true,
                    'empty_value' => $this->srv_param->get('MSG_CHOIX_SELECTEUR')
                )
            ),
        );

        if (isset($_POST['noeuds_tree_selectionnes'])) {
            $profil = $_POST['prf_menu'];
            $pageElemnts = explode('|', $_POST['noeuds_tree_selectionnes']);
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('AmsSilogBundle:Profil')->updateProfil($profil, $pageElemnts);
        }

        $oForm2 = $this->formulaire('', $aTousEltsForm2);
        $aValTwig['form2'] = $oForm2->createView();

        $modal = $this->renderView('AmsSilogBundle:Profil:modif.html.twig', $aValTwig);
        if($ajax)
            return $modal;
        else{
            $return = json_encode($modal);
            return new Response($return, 200, array('Content-Type'=>'Application/json')); 
        }
    }

    /**
     * Suppression d'un profil s'il pas aucun utilisateur
     * n'est lié au profil
     */
    public function suppressionAction($id) {

        $bVerifAcces = $this->verif_acces();

        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->getDoctrine()->getManager();
        $profil = $em->getRepository('AmsSilogBundle:Profil')->find($id);

        if (!$profil) {
            throw $this->createNotFoundException("Le profil n'existe pas");
        }

        $em->remove($profil);
        $em->flush();

        return $this->redirect($this->generateUrl('admin_liste_profil'));
    }

    /**
     * return le la liste des 
     */
    public function arborescenceAction() {

        $bVerifAcces = $this->verif_acces();

        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        // $this->setDerniere_page();

        $em = $this->getDoctrine()->getManager();

        $pageAccessibles = array();
        if (isset($_POST['prf_menu'])) {
            $profil = $em->getRepository('AmsSilogBundle:Profil')->findOneById($_POST['prf_menu']);

            foreach ($profil->getPageElements() as $pageElement) {
                $pageAccessibles[] = $pageElement->getId();
            }
        }
        $categories = $em->getRepository('AmsSilogBundle:Categorie')->getArboresence();
        $arborescence = '';
/*
        echo "<pre>";
        print_r($categories);
        
        echo "</pre>";*/
        
        foreach ($categories as $key => $categorie) {
            $arborescence[$key]['title'] = $categorie['libelle'];
            $arborescence[$key]['isFolder'] = true;

           
            
            if (isset($categorie['ssCategories'])) { //s'il existe des sous categories
                foreach ($categorie['ssCategories'] as $key1 => $ssCategorie) {
                    $arborescence[$key]['children'][$key1]['title'] = $ssCategorie['libelle'];
                    $arborescence[$key]['children'][$key1]['isFolder'] = true;
     
                 if (isset($ssCategorie['pages'])) { //s'il existe des pages
                    foreach ($ssCategorie['pages'] as $key2 => $page) {
                        $arborescence[$key]['children'][$key1]['children'][$key2]['title'] = $page['descCourt'];
                        $arborescence[$key]['children'][$key1]['children'][$key2]['isFolder'] = true;

                        if (isset($page['pageElements'])) { // si on a des elts de la page
                            foreach ($page['pageElements'] as $key3 => $pageElement) {
                                $arborescence[$key]['children'][$key1]['children'][$key2]['children'][$key3]['title'] = $pageElement['libelle'];
                                $arborescence[$key]['children'][$key1]['children'][$key2]['children'][$key3]['isFolder'] = false;
                                $arborescence[$key]['children'][$key1]['children'][$key2]['children'][$key3]['key'] = $pageElement['id'];
                                if (in_array($pageElement['id'], $pageAccessibles))
                                    $arborescence[$key]['children'][$key1]['children'][$key2]['children'][$key3]['select'] = true;
                            }
                        }
                    }
                }
            }
        }
      }
        return $this->render('AmsSilogBundle:Profil:arborescence.html.twig', array('arborescence' => json_encode($arborescence)));
    }

}
