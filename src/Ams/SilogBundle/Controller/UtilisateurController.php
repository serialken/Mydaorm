<?php

namespace Ams\SilogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Ams\SilogBundle\Controller\GlobalController;
use APY\DataGridBundle\Grid\Source\Vector;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Export\CSVExport;
use Ams\SilogBundle\Entity\Utilisateur;
use Ams\SilogBundle\Lib\StringLocal;

/**
 * Utilisateur controller.
 *
 */
class UtilisateurController extends GlobalController {
    /*
     * Affiche la liste des utilisateurs
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
            'FILT' => array('NOM_PRENOM', 'GRD_LIBELLE', 'PRF_LIBELLE', 'ACTIF'),
            'COL' => array('ID', 'LOGIN', 'MOT_DE_PASSE', 'NOM_PRENOM', 'EMAIL', 'GRD_LIBELLE', 'PRF_LIBELLE', 'ACTIF'),
            'ACT' => array('EDIT', 'SUPPR'),
        );


        $em = $this->getDoctrine()->getManager();
        $utilisateurs = $em->getRepository('AmsSilogBundle:Utilisateur')->findAll();

        foreach ($utilisateurs as $key => $utilisateur) {
            $aDonnees[$key]['ID'] = $utilisateur->getId();
            $aDonnees[$key]['LOGIN'] = $utilisateur->getLogin();
            $aDonnees[$key]['MOT_DE_PASSE'] = $utilisateur->getMotDePasse();
            $aDonnees[$key]['NOM_PRENOM'] = $utilisateur->getPrenom() . " " . $utilisateur->getNom();
            $aDonnees[$key]['EMAIL'] = $utilisateur->getEmail();
            $aDonnees[$key]['GRD_LIBELLE'] = $utilisateur->getGrpdepot()->getLibelle();
            $aDonnees[$key]['PRF_LIBELLE'] = $utilisateur->getProfil()->getLibelle();
            $aDonnees[$key]['ACTIF'] = '';
            if ($utilisateur->getActif() == true) {
                $aDonnees[$key]['ACTIF'] = 'Actif';
            } else {
                $aDonnees[$key]['ACTIF'] = "Inactif";
            }
        }

        /*         * ******** Filtre & Tableau en fonction des droits ********* */
        // ----- Options
        $aOptions = array();
        $aOptions['nombreResultatsParPage'] = $this->getNombreResultatsParPage('NB_RES_PAR_PAGE_UTL');
        $aOptions['libelles'] = array(
            'ID' => 'ID',
            'MOT_DE_PASSE' => 'Mot de passe',
            'NOM_PRENOM' => 'Nom & Prenom',
            'EMAIL' => 'Email',
            'GRD_LIBELLE' => 'Groupe de dépôt',
            'PRF_LIBELLE' => 'Profil',
            'ACTIF' => 'Actif',
        );

        // ----- Colonnes Supplementaires
        $aColonnesSuppl = array();
        if ($this->is_accessible('ams_admin_utilisateur')) {
            $aValTwig['lien_nouveau'] = 'ams_admin_utilisateur';
            $aColonnesSuppl['EDIT']['actionscolumns'] = new ActionsColumn('EDIT', 'Edition');
            $aColonnesSuppl['EDIT']['rang'] = 10;
            // Rattacher une "rowAction" a chaque "Actions Column"
            $rowActionEdit = new RowAction('Editer', 'ams_admin_utilisateur', false, '_blank', array('class' => 'nyroModal'));
            $rowActionEdit->setColumn('EDIT'); // "EDIT" <=> identifiant de la colonne 
            $rowActionEdit->setRouteParameters(array("ID", "act" => "edit"));
            $aColonnesSuppl['EDIT']['rowaction'] = $rowActionEdit;

            $aColonnesSuppl['SUPPR']['actionscolumns'] = new ActionsColumn('SUPPR', 'Supression');
            $aColonnesSuppl['SUPPR']['rang'] = 11;
            // Rattacher une "rowAction" a chaque "Actions Column"
            $rowActionSuppr = new RowAction('Suppr', 'ams_admin_utilisateur', true, null);
            $rowActionSuppr->setColumn('SUPPR');
            $rowActionSuppr->setConfirmMessage("Voulez-vous supprimer cet utilisateur ?");
            $rowActionSuppr->setRouteParameters(array("ID", "act" => "suppr"));
            $aColonnesSuppl['SUPPR']['rowaction'] = $rowActionSuppr;
        }

        $oGrid = $this->apy_grid($aDonnees, $aColonnesSuppl, $aOptions, $aTousEltsGrid);
        // Pour le GRID, on peut utiliser la methode getGridResponse ou isReadyForRedirect. return $grid->getGridResponse(); ou $grid->isReadyForRedirect();return array('grid' => $grid,);
        return $oGrid->getGridResponse('AmsSilogBundle:Utilisateur:liste.html.twig', $aValTwig);
    }

    public function utilisateurAction($act) {
        $session = $this->get('session');
        $userEncours = $session->get('UTILISATEUR_ID');
        $aValTwig = array();
        $aValTwig['modal'] = 1;
        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces($aValTwig['modal']);

        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $aTousEltsFormUtilisateur = array(
            'login' => array('TYPE' => 'text', 'OPTION' => array('label' => "Login",
                    'max_length' => 40,
                    'required' => true,
                )),
            'motDePasse' => array('TYPE' => 'text', 'OPTION' => array('label' => "Mot de passe",
                    'max_length' => 20,
                    'required' => true,
                ),
                'OPTION_LIRE' => array('read_only' => true,
                ),
            ),
            'nom' => array('TYPE' => 'text', 'OPTION' => array('label' => "Nom",
                    'max_length' => 40,
                    'required' => true
                )),
            'prenom' => array('TYPE' => 'text', 'OPTION' => array('label' => "Prénom",
                    'max_length' => 40
                )),
            'email' => array('TYPE' => 'text', 'OPTION' => array('label' => "Courriel",
                    'max_length' => 40,
                    'required' => true,
                )),
            'profil' => array('TYPE' => 'entity', 'OPTION' => array('label' => "Profil",
                    'class' => 'AmsSilogBundle:Profil',
                    'property' => 'libelle',
                    'required' => true,
                    'empty_value' => 'Choisissez un profil'
                )),
            'grpdepot' => array('TYPE' => 'entity', 'OPTION' => array('label' => "Groupe de dépôts",
                    'class' => 'AmsSilogBundle:GroupeDepot',
                    'property' => 'libelle',
                    'required' => true,
                    'empty_value' => 'Choisissez un groupe',
                )),
            'actif' => array('TYPE' => 'checkbox', 'OPTION' => array('label' => 'Actif',
                    'required' => false,
                ))


                // 'VALIDATION' => array('TYPE' => 'submit', 'OPTION' => array('attr' => array('class' => 'btn btn-primary'))
        );

        $oUtilisateur = new Utilisateur();

        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        if ($request->get('ID') != '0') {
            $oUtilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($request->get('ID'));
            if (!$oUtilisateur) {
                throw $this->createNotFoundException("Cet utilisateur n'existe pas.");
            }
        }

        if ($act === "suppr") {
            try {
                $em->remove($oUtilisateur);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'L\'utilisateur a été supprimé avec succés.'
                );
            } catch (\Doctrine\DBAL\DBALException $e) {
                $init = $this->getDoctrine()->resetEntityManager();
                $em = $this->getDoctrine()->getManager();
                $oUtilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($request->get('ID'));
                $oUtilisateur->setActif(0);
                $em->persist($oUtilisateur);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'error', 'L\'utilisateur ne peut pas être supprimé car il est référencé ailleurs. Il a été juste désactivé.'
                );
            }
            if ($userEncours == $request->get('ID')) {
                return $this->redirect($this->generateUrl('_ams_authentification'));
            }
            return $this->redirect($this->generateUrl('admin_liste_utilisateur'));
        }

        $sAction = $this->generateUrl($this->srv_request->get('_route'), $this->srv_request->get('_route_params'));
        $oForm = $this->formulaire($sAction, $aTousEltsFormUtilisateur, $oUtilisateur);


        $oForm->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            try {
                $em->persist($oUtilisateur);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'Vos changements ont été sauvegardés!'
                );
            } catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add(
                        'notice', 'L\'utilisateur est déjà existant'
                );
            }
            exit('ok');
        }

        $aValTwig['form'] = $oForm->createView();
        $aValTwig['erreurs'] = array();
        $aValTwig['sAction'] = $sAction;

        return $this->render('AmsSilogBundle:Utilisateur:utilisateur_action.html.twig', $aValTwig);
    }

    public function exportAction() {
        $em = $this->getDoctrine()->getEntityManager();

        $answers = $em->getRepository('AmsSilogBundle:Utilisateur')->findAll();
        $handle = fopen('php://memory', 'r+');
        $header = array();

        $content_header = array('Login', 'Mot de Passe', utf8_decode('Nom & Prénom'), 'Email', utf8_decode('Groupe de dépôt'), utf8_decode('Profil'));
        fputcsv($handle, $content_header, ';');

        foreach ($answers as $answer) {
            $content_csv = array($answer->getLogin(), $answer->getMotDePasse(), utf8_decode($answer->getNom() . ' ' . $answer->getPrenom()), $answer->getEmail(), utf8_decode($answer->getGrpdepot()->getLibelle()), utf8_decode($answer->getProfil()->getLibelle()));
            fputcsv($handle, $content_csv, ';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return new Response($content, 200, array(
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="export.csv"'
        ));
    }

}
