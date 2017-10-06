<?php

namespace Ams\SilogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ams\SilogBundle\Controller\GlobalController;

use APY\DataGridBundle\Grid\Source\Vector;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use Ams\SilogBundle\Entity\GroupeDepot;



/**
 * GroupeDepot controller.
 *
 */
class GroupeDepotController extends GlobalController {

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
            'FILT' => array('GRD_CODE', 'GRD_LIBELLE', 'DEP_LIBELLE'),
            'COL' => array('GRD_CODE', 'GRD_LIBELLE', 'DEP_LIBELLE'),
            'ACT' => array('EDIT', 'SUPPR'),
        );

        $em = $this->getDoctrine()->getManager();
        $grDepots = $em->getRepository('AmsSilogBundle:GroupeDepot')->getGroupeAvecDepot();

        foreach ($grDepots as $key => $grDepot) {
            $aDonnees[$key]['GRD_CODE'] = $grDepot->getCode();
            $aDonnees[$key]['GRD_LIBELLE'] = $grDepot->getLibelle();
            $depots[$key] = $grDepot->getDepots();
            $listDepotsName = array();

            foreach ($depots[$key] as $depot) {
                $listDepotsName[] = $depot->getLibelle();
              
            }

         
            $aDonnees[$key]['DEP_LIBELLE'] = implode(',', $listDepotsName);
        }


        /*         * ******** Filtre & Tableau en fonction des droits ********* */
        // ----- Options
        $aOptions = array();
        $aOptions['nombreResultatsParPage'] = $this->getNombreResultatsParPage('NB_RES_PAR_PAGE_DEPOTS');
        $aOptions['libelles'] = array(
            'GRD_CODE' => 'Code',
            'GRD_LIBELLE' => 'Libellé',
            'DEP_LIBELLE' => 'Dépôts'
        );
        $aOptions['horsFiltre'] = array(
            'GRD_CODE'
        );

        // ----- Colonnes Supplementaires
        $aColonnesSuppl = array();
        if ($this->is_accessible('admin_modif_groupedepot')) {
            $aValTwig['lien_nouveau'] = 'admin_modif_groupedepot';
            $aColonnesSuppl['EDIT']['actionscolumns'] = new ActionsColumn('EDIT', 'Edition');
            $aColonnesSuppl['EDIT']['rang'] = 10;
            // Rattacher une "rowAction" a chaque "Actions Column"
            $rowActionEdit = new RowAction('Editer', 'admin_modif_groupedepot', false, '_blank', array('class' => 'nyroModal'));
            $rowActionEdit->setColumn('EDIT'); // "EDIT" <=> identifiant de la colonne 
            $rowActionEdit->setRouteParameters(array("GRD_CODE", "act" => "edit"));
            $aColonnesSuppl['EDIT']['rowaction'] = $rowActionEdit;

            $aColonnesSuppl['SUPPR']['actionscolumns'] = new ActionsColumn('SUPPR', 'Supression');
            $aColonnesSuppl['SUPPR']['rang'] = 11;
            // Rattacher une "rowAction" a chaque "Actions Column"
            $rowActionSuppr = new RowAction('Suppr', 'admin_modif_groupedepot', true, null);
            $rowActionSuppr->setColumn('SUPPR');
            $rowActionSuppr->setConfirmMessage("Voulez-vous supprimer ce groupe de dépôts ?");
            $rowActionSuppr->setRouteParameters(array("GRD_CODE", "act" => "suppr"));
            $aColonnesSuppl['SUPPR']['rowaction'] = $rowActionSuppr;
        }
        $oGrid = $this->apy_grid($aDonnees, $aColonnesSuppl, $aOptions, $aTousEltsGrid);
        // Pour le GRID, on peut utiliser la methode getGridResponse ou isReadyForRedirect. return $grid->getGridResponse(); ou $grid->isReadyForRedirect();return array('grid' => $grid,);
        return $oGrid->getGridResponse('AmsSilogBundle:GroupeDepot:liste.html.twig', $aValTwig);
    }

    /**
     *  Ajout, Suppression et modification d'un groupe de depot
     * @param type $GRD_CODE
     * @param type $act
     * @return type
     */
    public function groupeDepotsAction($GRD_CODE, $act) {
        $aValTwig = array();
        $listErreurs = array();
        $aValTwig['modal'] = 1;
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces($aValTwig['modal']);
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $sCodeParDefaut = '';
        $sLibelleParDefaut = '';
        $aDepotsParDefaut = array();

        $bFormSoumis = false;
        $bNouveau = true;
        $oGroupesDepot = new GroupeDepot();
        
        if ($this->srv_request->request->get('form')) {
            $bFormSoumis = true;
        }
        
        $em = $this->getDoctrine()->getManager();
        if ($GRD_CODE != '0') {
            $bNouveau = false;
            $oGroupesDepot = $em->getRepository('AmsSilogBundle:GroupeDepot')->findOneByCode($GRD_CODE);
            if (!$oGroupesDepot) {
                throw $this->createNotFoundException("Le groupe de depot n' existe pas");
            }
        }

        $aTousDepots = array();
       
        $depots = $em->getRepository('AmsSilogBundle:Depot')->getListeDepot();

        foreach ($depots as $depot) {
            $aTousDepots[$depot->getId()] = $depot->getLibelle();
        }
        
        // Supression d'un groupe de dépots
        if ($act === "suppr") {
            $em->remove($oGroupesDepot);
            $em->flush();
            $this->srv_session->getFlashBag()->add(
                            'notice', 'Groupe de dépôt supprimé avec succés.'
                    );
            return $this->redirect($this->generateUrl('admin_liste_groupedepot'));
        }

        if (!$bNouveau) { // Les valeurs par defaut dans le cas d'une editions
            foreach ($oGroupesDepot->getDepots() as $depot) {
                $aDepotsParDefaut[] = $depot->getId();
            }
            $aValTwig['groupe_depot'] = $oGroupesDepot;
            $sCodeParDefaut = $oGroupesDepot->getCode();
            $sLibelleParDefaut = $oGroupesDepot->getLibelle();
        }
        

        $POST = '';
        if ($bFormSoumis) { // Formulaire soumis
            $POST = $this->srv_request->request->get('form');
            $sCodeParDefaut = (isset($POST['grd_code']) ? trim($POST['grd_code']) : $sCodeParDefaut);
            $sLibelleParDefaut = (isset($POST['grd_libelle']) ? trim($POST['grd_libelle']) : $sLibelleParDefaut);

            $aDepots = (isset($POST['dep_code']) ? $POST['dep_code'] : $aDepotsParDefaut );

            $oGroupesDepot->setCode($sCodeParDefaut);
            $oGroupesDepot->setLibelle($sLibelleParDefaut);

            foreach ($oGroupesDepot->getDepots() as $depot) {
                $oGroupesDepot->removeDepot($depot);
            }

            foreach ($aDepots as $depotId) {
                $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneById($depotId);
                $oGroupesDepot->addDepot($depot);
            }
            if ($bNouveau) {
                $validateur = $this->get('validator');
                $listErreurs = $validateur->validate($oGroupesDepot);
            }

            if (count($listErreurs) == 0) {
                $em->persist($oGroupesDepot);
                $em->flush();
                if (isset($POST['nouveau']) && $POST['nouveau'] == 1) {
                    $this->srv_session->getFlashBag()->add(
                            'notice', 'Nouveau groupe de dépôts créé!'
                    );
                } else {
                    $this->srv_session->getFlashBag()->add(
                            'notice', 'Vos changements ont été enregistrés!'
                    );
                }
              
            } else {
                
                 $this->srv_session->getFlashBag()->add(
                            'notice', "Une erreur s'est produite!"
                    );
 
                 
            }
        }

        $aTousEltsForm = array(
            'GRD_CODE' => array('TYPE' => 'text', 'OPTION' => array('label' => "Code",
                    'max_length' => 10,
                    'required' => true, /* par defaut, c'est true */
                    'read_only' => (!$bNouveau ? true : false ),
                    'trim' => true, /* par defaut, c'est true */
                    'data' => $sCodeParDefaut,
                )
            ),
            'GRD_LIBELLE' => array('TYPE' => 'text', 'OPTION' => array('label' => "Libelle",
                    'max_length' => 40,
                    'required' => true,
                    'data' => $sLibelleParDefaut
                ),
            ),
            'DEP_CODE' => array('TYPE' => 'choice', 'OPTION' => array('label' => "Depots",
                    'choices' => $aTousDepots,
                    'data' => $aDepotsParDefaut,
                    'multiple' => true,
                    'expanded' => false,
                    'empty_value' => ($bNouveau ? $this->srv_param->get('MSG_CHOIX_SELECTEUR') : false),
                )
            ),
            'NOUVEAU' => array('TYPE' => 'hidden', 'OPTION' => array('data' => ( $bNouveau ? 1 : 0 ),)
            ),
           /* 'VALIDATION' => array('TYPE' => 'submit', 'OPTION' => array('attr' => array('class' => 'save'),)
            ),*/
        );
        $sAction = $this->generateUrl($this->srv_request->get('_route'), $this->srv_request->get('_route_params'));
        $oForm = $this->formulaire($sAction, $aTousEltsForm);

        $aValTwig['form'] = $oForm->createView();
        $aValTwig['grd_code'] = $GRD_CODE;
        $aValTwig['act'] = $act;
        $aValTwig['listErreurs'] = $listErreurs;
        $aValTwig['sAction'] = $sAction;

        return $this->render('AmsSilogBundle:GroupeDepot:groupe_depots.html.twig', $aValTwig);
    }

}
