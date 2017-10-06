<?php

namespace Ams\AdresseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Response;
use \Ams\SilogBundle\Repository\DepotRepository;
use APY\DataGridBundle\Grid\Source\Vector;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Export\CSVExport;
//use APY\DataGridBundle\Grid\Export\DSVExport;
use APY\DataGridBundle\Grid\Export\ExcelExport;
use APY\DataGridBundle\Grid\Source\Entity;
use Ams\AdresseBundle\Form\RequeteExportType;
use Ams\AdresseBundle\Entity\RequeteExport;
use Doctrine\ORM\EntityRepository;
use Ams\ReferentielBundle\Controller\GlobalReferentielController;

/**
 * Gestion des points de livraison
 *
 */
class ExportController extends GlobalController {

    /**
     * Formulaire de recherche
     */
    public function rechercheAction(Request $request) {

        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $em = $this->getDoctrine();

        // stocker cette page comme la derniere visitee apres expiration de session 
        $this->setDerniere_page();

        $session = $this->get('session');
        $depots = $session->get('DEPOTS');

        $formDepot = $this->createFormBuilder()
                ->add('depot', 'choice', array(
                    'choices' => $depots,
                    'multiple' => true,
                    'expanded' => false,
                    'required' => true,
                    'label' => 'Choisir les dépots'
                ))
                ->getForm();

        $formDepot->handleRequest($request);

        if ($formDepot->isSubmitted()) {
            // Permet d'éviter les erreurs 500 sur le chargement de données volumineuses ou de les tronquer lors de leur insertion en bdd
            set_time_limit(0);
            ini_set("memory_limit", "-1");
            ini_set('mysql.connect_timeout', '0');
            ini_set('max_execution_time', '0');

            $listeTournees = array();

            $data = $formDepot->getData();
            $selectedDepots = $data['depot'];
            $session->set("optimDataForm", array(
                "depotsSelected" => $selectedDepots
            ));
            $tournees = $em->getRepository('AmsModeleBundle:ModeleTournee')->getListeTournee($selectedDepots);

            foreach ($tournees as $tournee) {
                $listeTournees[$tournee->getId()] = $tournee->getCode();
            }

            return $this->render('AmsAdresseBundle:Export:recherche.html.twig', array(
                        'formDepot' => $formDepot->createView(),
                        'form' => $this->createFormulaire($listeTournees)
            ));
        }

        return $this->render('AmsAdresseBundle:Export:recherche.html.twig', array(
                    'formDepot' => $formDepot->createView(),
        ));
    }

    /**
     * creation de la requête et affichage resultat
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function exportQueryAction(Request $request) {
        // Permet d'éviter les erreurs 500 sur le chargement de données volumineuses
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        ini_set('mysql.connect_timeout', '0');
        ini_set('max_execution_time', '0');

        // verifie si on a droit a acceder a cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $session = $this->get('session');
        $formData = $request->request->get('form');

        $optimDataForm = $session->get('optimDataForm');
        $optimDataForm['dataForm'] = $formData;
        $session->set("optimDataForm", $optimDataForm);

        $reqId = $request->get('reqId', null);

        return $this->render('AmsAdresseBundle:Export:export_query.html.twig', array(
                    'reqId' => $reqId
        ));
    }

    /**
     *  creation de la grid 
     */
    public function gridQueryAction(Request $request) {
        // Permet d'éviter les erreurs 500 sur le chargement de données volumineuses
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        ini_set('mysql.connect_timeout', '0');
        ini_set('max_execution_time', '0');

        $em = $this->getDoctrine()->getManager();
        $reqId = $request->get('reqId', null);

        $session = $this->get('session');
        $optimDataForm = $session->get('optimDataForm');

        if ($reqId) {
            $requeteExport = $em->getRepository('AmsAdresseBundle:RequeteExport')->findOneById($reqId);
            $result = $em->getConnection()->fetchAll($requeteExport->getRequete());
        } else {
            $depots = $optimDataForm['depotsSelected'];
            $formRequestData = $optimDataForm['dataForm'];
            $result = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->exportClient($depots, $formRequestData, FALSE, $this->container->getParameter('GEOC_EXPORT_PERIODE_SEMAINES_DEFAUT'), TRUE);
            $dateCourant = new \DateTime();
        }
        $tmp = $result;

        $aHeaderCsv = array(
            'Nom Societe', 'ID Produit', 'Nom Produit', 'Nom Depot', 'Code Tournée', 'ID Abonné',
            'Numéro Abonné', 'Nom', 'Raison Sociale', 'Cplt adresse', 'Adresse', 'Lieu dit', 'Code Postal', 'Ville', 'ID PL',
            'PL complement adresse', 'PL adresse', 'PL lieu dit', 'PL cp', 'PL ville', 'PL X', 'PL Y',
            'RNVP Cplt Adresse', 'RNVP Adresse', 'RNVP Lieu dit', 'RNVP CP', 'RNVP Ville', 'RNVP X', 'RNVP Y',
            'Type Service', 'TPS', 'ID Flux'
        );

        /** SUPPRIMER FICHIER SUPERIEUR A 1 HEURE * */
        if (!$this->container->hasParameter("PARAM_LOCAL")) {
//            $this->dumpTmpFile();
            /** CREATION D'UN FICHIER CSV AVEC LES DONNEES CALCULER* */
            array_unshift($tmp, $aHeaderCsv);
            $file = 'file_exportTourneeParTournee.csv';
            $fp = fopen('tmp/' . $file, 'w+');
            foreach ($tmp as $fields) {
                fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($fp, $fields, ';');
            }
            fclose($fp);
        }
        $response = $this->renderView('AmsAdresseBundle:Export:grid.xml.twig', array('data' => $result));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    /**
     * Enregistre la requête
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function enregistreQueryAction(Request $request) {
        // Permet d'éviter les erreurs 500 sur le chargement de données volumineuses ou de les tronquer lors de leur insertion en bdd
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        ini_set('mysql.connect_timeout', '0');
        ini_set('max_execution_time', '0');
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));

        $reqId = $request->get('reqId', null);

        if ($reqId) {
            $requete = $em->getRepository('AmsAdresseBundle:RequeteExport')->findOneById($reqId);
        } else {
            $requete = new RequeteExport();

            $optimDataForm = $session->get('optimDataForm');
            $formRequestData = $optimDataForm['dataForm'];
            $depots = $optimDataForm['depotsSelected'];

            // Récupération des jours
            $aJours = $formRequestData['jour'];
            $aJoursCodes = array();
            foreach ($aJours as $iJourId) {
                $oInfoJour = $em->getRepository('AmsReferentielBundle:RefJour')->findOneById((int) $iJourId);
                $aJoursCodes[] = $oInfoJour->getCode();
            }

            // Liste des codes tournées concernés
            $aListeCodesTournees = array();
            if (!empty($formRequestData['tournee'])) {
                foreach ($formRequestData['tournee'] as $aTournee) {
                    $aInfoTournee = $em->getRepository('AmsModeleBundle:ModeleTournee')->findOneById((int) $aTournee);
                    /* @var $aInfoTournee \Ams\ModeleBundle\Entity\ModeleTourneeJour */
                    foreach ($aJoursCodes as $sJourCode) {
                        $aListeCodesTournees[] = $aInfoTournee->getCode() . $sJourCode;
                    }
                }
                $requete->setListeTournees(base64_encode(serialize($aListeCodesTournees)));
            }
            
            // @ TODO traiter la non sélection de tournée

            $query = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->exportClient($depots, $formRequestData, true, $this->container->getParameter('GEOC_EXPORT_PERIODE_SEMAINES_DEFAUT'), TRUE);

            $result = $em->getConnection()->fetchAll($query);
            $nbResult = count($result);

            $idMax = $em->getRepository('AmsAdresseBundle:RequeteExport')->getMaxRequeteId();
            $libelleRequete = $utilisateur->getLogin() . "_Requete N° " . ($idMax[0] + 1);

            $requete->setLibelle($libelleRequete);
            $requete->setDateCreation(new \DateTime());
            $requete->setRequete($query);
            $requete->setNbResultat($nbResult);
        }

        $form = $this->createForm(new RequeteExportType(), $requete);

        $form->handleRequest($request);

        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                $requete->setUtilisateur($utilisateur);

                $em->persist($requete);
                $em->flush();

                if (!$reqId) {
                    $query = str_replace(":queryId:", $requete->getId(), $requete->getRequete());
                    $query = str_replace(":queryLibelle:", $requete->getLibelle(), $query);
                    $requete->setRequete($query);

                    // enregistre les resultat de la requête
                    $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->updateGeoConcept($query);
                }

                $em->persist($requete);
                $em->flush();

                $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'success', 'message' => '<strong>Votre requête a été enregistrée!</strong>'));
            } else {
                $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'danger', 'message' => '<strong>Attention!</strong> Une erreur est survenue!'));
            }

            $modal = $this->renderView('AmsAdresseBundle:Export:enregistre_query.html.twig', array(
                'form' => $form->createView(),
                'reqId' => $reqId
            ));

            $response = array("modal" => $modal, "alert" => $alert);
            $return = json_encode($response);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

        $response = $this->renderView('AmsAdresseBundle:Export:enregistre_query.html.twig', array(
            'form' => $form->createView(),
            'reqId' => $reqId
        ));

        return new Response($response);
    }

    /*     * *
     * Liste de requête déjà créer par l'utilisateur
     * connecté
     */

    public function listeQueryAction() {

        $session = $this->get('session');
        $em = $this->getDoctrine();
        //on récupère la liste des requête enregistré par le user connecté
        $utilisateur = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($session->get('UTILISATEUR_ID'));
        $requetes = $em->getRepository('AmsAdresseBundle:RequeteExport')->getQueryByUser($utilisateur->getId());

        // Récupération des statuts de requêtes
        if (!empty($requetes)) {
            foreach ($requetes as &$oReq) {
                $oReq['infoStatut'] = $em->getRepository('AmsAdresseBundle:RequeteExport')->getStatutInfos($oReq['id']);

//                if ($oReq['id'] == 144) {
//                    $aPerimEffect = $this->declinerListeTournees($oReq['liste_tournees'], $oReq['jour_type'], $em);
//                }
            }
        }

        return $this->render('AmsAdresseBundle:Export:liste_query.html.twig', array('requetes' => $requetes));
    }

    public function reloadedExportAction(Request $request) {
        $em = $this->get("doctrine.orm.entity_manager");
        $id_query = $request->get('id_query');

        /** UPDATE STATE QUERY * */
        $oQueryExport = $em->getRepository('AmsAdresseBundle:RequeteExport')->find($id_query);
        $oQueryExport->setDateVerification(null)
                ->setDateApplication(null)
                ->setIsValid(false)
                ->setCommentVerif('');

        /** REMOVE RESULT EXPORT GEOCONCEPT * */
        $aExportGeo = $em->getRepository('AmsAdresseBundle:ExportGeoconcept')->findByRequeteExport($oQueryExport);
        foreach ($aExportGeo as $oExportGeo)
            $em->remove($oExportGeo);

        /** REMOVE RESULT IMPORT GEOCONCEPT * */
        $aImportGeo = $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->findByRequeteExport($oQueryExport);
        foreach ($aImportGeo as $oImportGeo)
            $em->remove($oImportGeo);

        $em->flush();

        /** BUILT QUERY EXPORT * */
        $smtp = $oQueryExport->getRequete();
        $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->updateGeoConcept($smtp);
        exit;
    }

    /*
     * METHOD : DEFINIT LA DATE D APPLICATION DE LA REQUETE D'IMPORT GEOCONCEPT
     * @return array json
     */

    public function addDateApplicationAction(Request $request) {
        $em = $this->get("doctrine.orm.entity_manager");
        $dateTmp = explode('/', $request->get('date_application'));
        $id_query = $request->get('id_query');
        $query = $em->getRepository('AmsAdresseBundle:RequeteExport')->find($id_query);
        $dateApplication = new \DateTime;
        $dateApplication->setDate($dateTmp[2], $dateTmp[1], $dateTmp[0]);
        $dateApplication->setTime(0, 0, 0);
        $query->setDateApplication($dateApplication);
        $query->setIsValid(1);
        $query->setJourType(json_encode($request->get('jour_type')));
        $em->flush();
        $comment = '<div class="alert alert-info" >La mise à jour sera effective à partir de la date indiquée. </div><div style="text-align:center;"><div class="btn btn-xs btn-default close-modal">Fermer</div></div>';

        /** REPONSE AJAX* */
        $response = array('comment' => $comment, 'date' => $request->get('date_application'));
        $return = json_encode($response);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * METHOD : SUPPRIME LA REQUETE D'EXPORT
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array json
     */
    public function deleteQueryAction(Request $request) {
        $em = $this->get("doctrine.orm.entity_manager");
        $id_query = $request->get('id_query');

        $em->getRepository('AmsAdresseBundle:RequeteExport')->deleteQuery($id_query);

        $response = array('success');
        $return = json_encode($response);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Supprime les données importées d'une requête
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array json
     */
    public function deleteImportAction(Request $request) {
        $aReturn = array(
            'returnCode' => NULL,
            'msg' => NULL,
            'errCode' => NULL,
            'errMsg' => NULL,
        );

        $iUserId = $request->getSession()->get('UTILISATEUR_ID');

        $em = $this->get("doctrine.orm.entity_manager");
        $id_query = $request->get('id_query');

        // Suppression de la date d'application
        /* @var $oReqExp RequeteExport */
        $oReqExp = $em->getRepository('AmsAdresseBundle:RequeteExport')->findOneById((int) $id_query);

        if (empty($oReqExp)) {
            $aReturn['errCode'] = 99;
            $aReturn['errMsg'] = "Requête non trouvée.";

            $return = json_encode($aReturn);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

        // Vérification de l'apartenance de la requete d'export
        if ($oReqExp->getUtilisateur()->getId() != $iUserId) {
            $aReturn['errCode'] = 1;
            $aReturn['errMsg'] = "Requête rattachée à un autre utilisateur.";

            $return = json_encode($aReturn);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

        if ($id_query > 0 && $iUserId > 0) {
            $nbDeletes = $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->clearImports((int) $id_query);

            // Suppression de la date d'application
            $oReqExp->setDateApplication(NULL);
            $em->flush();

            if ($nbDeletes > 0) {
                $aReturn['returnCode'] = 1;
                $aReturn['msg'] = 'Les ' . $nbDeletes . ' points importés ont été supprimés.';
            } else {
                $aReturn['errCode'] = 2;
                $aReturn['errMsg'] = "Aucun point importé n'a été supprimé.";
            }
        } else {
            $aReturn['errCode'] = 3;
            $aReturn['errMsg'] = "Erreur détectée dans les paramètres.";
        }
        $return = json_encode($aReturn);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * METHOD : Verifie la coherence entre les données envoyés à geoconcept
     *          et celle retourné par ces derniers
     * @return type
     */
    public function verifQueryImportExportAction(Request $request) {
        $em = $this->get("doctrine.orm.entity_manager");
        $id_query = $request->get('id_query');
        $query = $em->getRepository('AmsAdresseBundle:RequeteExport')->find($id_query);
        /* @var $query RequeteExport */

        /** REQUETE EXPORT* */
        /** JOUR DE LA SEMAINE * */
        $oDays = $em->getRepository('AmsReferentielBundle:RefJour')->findAll();
        $selectorDay = '<select id="day_type" multiple="multiple" name="day_type">';
        foreach ($oDays as $day)
            $selectorDay .='<option value="' . $day->getId() . '">' . $day->getLibelle() . '</option>';
        $selectorDay .= '</select>';

        // Modifications à la volée sur la requête
        $smtp = $this->sanitizeExportQuery($query->getRequete());
        $matches = explode('FROM', $smtp);
        $smtp = 'SELECT CL.point_livraison_id FROM ' . $matches[1] . ' GROUP BY CL.point_livraison_id';
        $aPointLivraisonExport = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->fetchRequeteExport($smtp);
        $aPointLivraisonExport = $this->getArrayWithoutKey($aPointLivraisonExport, 'point_livraison_id');
        /** REQUETE GEOCONCEPT * */
        $aPointLivraisonImport = $em->getRepository('AmsAdresseBundle:RequeteExport')->getPointLivraisonByQueryExportId($id_query);

        // Requête sur CASL (nombre de clients)
        $sCASLReq = 'SELECT COUNT(DISTINCT CL.abonne_soc_id) as nb_clients FROM  ' . $matches[1];
        $aNbClients = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->fetchRequeteExport($sCASLReq);
        $iNbClientsCASL = (int) $aNbClients[0]['nb_clients'];

        // Requête sur CASL (nombre de points de livraison)
        $sCASLNbPointsReq = 'SELECT COUNT(DISTINCT CL.point_livraison_id) as nb_points FROM  ' . $matches[1];
        $aNbPoints = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->fetchRequeteExport($sCASLNbPointsReq);
        $iNbPointsCASL = (int) $aNbPoints[0]['nb_points'];

        $aPointLivraisonImport = $this->getArrayWithoutKey($aPointLivraisonImport, 'point_livraison_id');
        /** COMPARAISON DES RESULTATS * */
        $result_1 = array_diff($aPointLivraisonImport, $aPointLivraisonExport);
        $result_2 = array_diff($aPointLivraisonExport, $aPointLivraisonImport);
        $result = array_merge($result_1, $result_2);
        /** COUNT TOURNEE (EXPORT,IMPORT) * */
        $aTourneeExport = $em->getRepository('AmsAdresseBundle:ExportGeoconcept')->listerTournees($id_query);
        $aTourneeImport = $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->listerTournees($id_query);
        /** COUNT POINT LIVRAISON (EXPORT,IMPORT) * */
        $iPlivraisonExport = $em->getRepository('AmsAdresseBundle:ExportGeoconcept')->compterPointsDansExport($id_query);
        $iPlivraisonImport = $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->compterPointsDansImport($id_query);

        // Mise à jour du nombre de lignes à importer au niveau de la requête
        $iLignesImport = $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->compterLignesAImporter($id_query);
        $query->setNbImports($iLignesImport);

        // Compte les clients compris dans l'import
        $iNbClientsImport = $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->compterClientsDansImport($id_query);
        /**         * */
        $aChangementPoint = $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->detecterChangementPointLivraison($id_query);
        $aChangementTournee = $em->getRepository('AmsAdresseBundle:ImportGeoconcept')->detecterChangementTournee($id_query);

        /** AFFECTATION & INSERTION DES RESULTATS * */
        $comment = $paragraph = '';
        $paragraph .=(count($result_1)) ? 'Nous avons détecté des changements dans les tournées depuis la création de la requête d\'export: <strong>' . count($result_1) . ' nouveau(x) point(s) de livraison</strong>.<br />' : '';
        $paragraph .=(count($result_2)) ? 'Il manque ' . count($result_2) . ' point(s) de livraison après l\'optimisation. <br />' : '';

        // Nombre de basculement de tournée
        $sChangeTournTxt_default = 'Aucun changement de tournée détecté.';
        if ($aChangementTournee)
            $sChangeTourntxt = count($aChangementTournee) > 1 ? count($aChangementTournee) . ' points ont changé de tournée.' : '1 point a changé de tournée.';
        else
            $sChangeTourntxt = $sChangeTournTxt_default;

        // Nombre de regroupement de points
        $sChangePointTxt_default = 'Aucun regroupement de point de livraison détecté.';

        if ($aChangementPoint)
            $sChangeP2Ltxt = count($aChangementPoint) > 1 ? count($aChangementPoint) . ' abonnés ont changé de point de livraison.' : '1 abonné a changé de point de livraison.';
        else
            $sChangeP2Ltxt = $sChangePointTxt_default;

        // Comparaison sur le nombre de points entre l'import et CASL actuellement
        $bDeltaPtsCASL = FALSE;
        $fCoeffCompPoints = $this->container->hasParameter("GEOC_OPTIM_COMP_CASL_POINTS_COEFF") ? $this->container->getParameter("GEOC_OPTIM_COMP_CASL_POINTS_COEFF") : 1;
        if ($iPlivraisonImport < ($iNbPointsCASL * $fCoeffCompPoints)) {
            $bDeltaPtsCASL = TRUE;
            $sDeltaNbPointsCASLtxt = 'Il y a actuellement <strong>' . $iNbPointsCASL . ' points</strong> de livraison dans ces tournées contre <strong>' . $iPlivraisonImport . '</strong> après l\'optimisation.';
        } else {
            $sDeltaNbPointsCASLtxt = 'Le nombre de points de livraison actuellement desservi par ces tournées est identique ou inférieur.';
        }

        // Comparaison sur le nombre de clients entre l'import et CASL actuellement
        $bDeltaClientsCASL = FALSE;
        $fCoeffCompClients = $this->container->hasParameter("GEOC_OPTIM_COMP_CASL_CLIENTS_COEFF") ? $this->container->getParameter("GEOC_OPTIM_COMP_CASL_CLIENTS_COEFF") : 1;
        if ($iNbClientsImport < ($iNbClientsCASL * $fCoeffCompClients)) {
            $bDeltaClientsCASL = TRUE;
            $sDeltaNbClientsCASLtxt = 'Il y a actuellement <strong>' . $iNbClientsCASL . ' clients</strong> à livrer dans ces tournées contre <strong>' . $iNbClientsImport . '</strong> après l\'optimisation.';
        } else {
            $sDeltaNbClientsCASLtxt = 'Le nombre de clients à livrer par ces tournées actuellement est identique ou inférieur.';
        }

        $aResult = '<table class="table table-striped table-bordered table-condensed" style="margin-bottom:20px">
                <tr>
                    <td></td>
                    <td style="text-align:center"><strong>Tournées exportées</strong></td>
                    <td style="text-align:center"><strong>Tournées optimisées</strong></td>
                </tr>
                <tr>
                    <td> <em>Nombre de Tournées</em></td>
                    <td style="text-align:center">' . count($aTourneeExport) . '</td>
                    <td style="text-align:center">' . count($aTourneeImport) . '</td>
                </tr>
                <tr>
                    <td> <em>Nombre de points</em></td>
                    <td style="text-align:center">' . $iPlivraisonExport . '</td>
                    <td style="text-align:center">' . $iPlivraisonImport . '</td>
                </tr>';
        if ($aChangementPoint || $aChangementTournee || $bDeltaClientsCASL || $bDeltaPtsCASL)
            $aResult .='<tr>
                            <td colspan="3">
                            <p><em>En résumé:</em></p>
                                <ul> 
                                    <li>' . $sChangeTourntxt . '</li>
                                    <li>' . $sChangeP2Ltxt . '</li>
                                    <li>' . $sDeltaNbPointsCASLtxt . '</li>
                                    <li>' . $sDeltaNbClientsCASLtxt . '</li>
                                </ul> 
                            </td>
                        </tr>';
        if ($paragraph)
            $aResult .='<tr><td colspan="3">' . $paragraph . '</td></tr>';
        $aResult .= '</table>';
        $comment .=(count($result_1) || count($result_2) || $bDeltaPtsCASL || $bDeltaClientsCASL ) ? '<div class="panel panel-warning"><div class="panel-heading">Incohérences détectées</div><div class="panel-body">Deux choix s\'offrent à vous : <br />
                      1) Appliquer l\'optimisation Géoconcept 
                        <div style="display:inline;margin-left:10px"> 
                            <input type="text" name="date_application" /> 
                            <input id="apply-optimization" type="submit" class="btn btn-warning btn-xs" value="Appliquer" /> 
                        </div><br />
                        '.$this->selectJourType($selectorDay,620).'<br />
                            
                       2) Relancer l\'export en cliquant sur le bouton ci-contre <div id="reflate" class="btn btn-primary btn-xs">Relancer</div>
                            <div style="margin:10px auto 0;width:60px;display:block" class="btn btn-xs btn-default close-modal">Annuler</div>
                        </div>'
                        : '
                        <div class="panel panel-info">
                        <div class="panel-heading">Appliquer l\'optimisation</div>
                        <div class="panel-body">
                            La vérification à été effectuée sans relever d\'anomalie.<br/>
                            Vous pouvez appliquer l\'optimisation des tournées.
                            '.$this->selectJourType($selectorDay).'
                            <div style="margin-top:10px;"> 
                                <strong style="float:left;width:225px;display:block;text-align:right;padding-right:15px">Date d\'application :</strong> <input type="text" name="date_application" />  &nbsp; 
                                <input id="apply-optimization" type="submit" class="btn btn-primary btn-xs" value="Appliquer" /> 
                            </div>
                        </div>
                     </div>
                        <div style="text-align:center;"><div class="btn btn-xs btn-default close-modal">Annuler</div>
                     </div>';

        $comment = $aResult . $comment;
        $valid = (!count($result)) ? true : false;

        // Invalidation de la requête en cas de trop grand décalage avec les points recensés actuellement dans CASL
        if ($valid) {
            $valid = $bDeltaPtsCASL ? FALSE : TRUE;
        }

        // Invalidation de la requête en cas de trop grand décalage avec les clients recensés actuellement dans CASL
        if ($valid) {
            $valid = $bDeltaClientsCASL ? FALSE : TRUE;
        }

        $dateVerification = new \DateTime;
        $query->setIsValid($valid);
        $query->setDateVerification($dateVerification);
        $query->setCommentVerif($comment);
        $em->flush();

        /** REPONSE AJAX* */
        $response = array('result' => $valid, 'comment' => $comment, 'dateVerif' => $dateVerification->format('d-m-Y'));
        $return = json_encode($response);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Method qui recois un tableau multidimentionnel et qui retourne un tableau unidimentionelle
     * @param array $data
     * @param string $key
     * @return array
     */
    private function getArrayWithoutKey($data, $key) {
        $aData = array();
        foreach ($data as $value)
            $aData[] = $value[$key];
        return $aData;
    }

    /**
     * formulaire avec le filtre sur les dépots 
     * creation du formualire  
     */
    public function createFormulaire($tournees) {
        $operateurDate = array(
            '=' => 'Egale à',
            '!=' => 'Différente de',
            '>' => 'Supérieure à',
            '>=' => 'Supérieure ou égal',
            '<' => 'Inférieure',
            '<=' => 'Supérieure ou égale',
            'BETWEEN' => "Est comprise",
            'NOT BETWEEN' => "N'est pas comprise"
        );
        $operateurListeMultiple = array(
            'IN ' => "Est dans la liste",
            'NOT IN ' => "N'est Pas dans la liste"
        );

        $operateurListeUnique = array(
            'IN ' => "Est dans la liste",
        );

        $form = $this->createFormBuilder()
                ->add('tournee', 'choice', array(
                    'choices' => $tournees,
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                ))
                ->add('operateur_tournee', 'choice', array(
                    'choices' => $operateurListeMultiple,
                    'empty_value' => 'Sans Condition',
                    'required' => false,
                ))
//            ->add('statut', 'choice', array(
//                'empty_value' => 'Tous',
//                'choices' => array(
//                    "L" => "Livré",
//                    "R" => "Repérage",
//                ),
//                'multiple' => true,
//                'expanded' => false,
//                'required' => false,
//                'required' => false,
//            ))
//            ->add('operateur_statut', 'choice', array(
//                'choices' => $operateurListeMultiple,
//                'empty_value' => 'Sans Condition',
//                'required' => false,
//            ))
                ->add('produit', 'entity', array(
                    'class' => 'AmsProduitBundle:Produit',
                    'property' => 'libelle',
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                ))
                ->add('operateur_produit', 'choice', array(
                    'choices' => $operateurListeMultiple,
                    'required' => false,
                    'empty_value' => 'Sans Condition'
                ))
                ->add('flux', 'entity', array(
                    'class' => 'AmsReferentielBundle:RefFlux',
                    'property' => 'libelle',
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                ))
                ->add('operateur_flux', 'choice', array(
                    'choices' => $operateurListeMultiple,
                    'required' => false,
                    'empty_value' => 'Sans Condition'
                ))
//            ->add('parution', 'date', array(
//                'widget' => 'single_text',
//                'input' => 'datetime',
//                'format' => 'dd/MM/yyyy',
//                'attr' => array('class' => 'date'),
//            ))
//            ->add('operateur_parution', 'choice', array(
//                'choices' => $operateurDate,
//                'required' => false,
//                'empty_value' => 'Sans Condition'
//            ))
                ->add('parution_fin', 'date', array(
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'date'),
                ))
                ->add('jour', 'entity', array(
                    'class' => 'AmsReferentielBundle:RefJour',
//                    'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('j')
//                            ->orderBy('j.id', 'DESC');
//                    },
                    'property' => 'libelle',
                    'label' => ' Jour Type',
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                ))
                ->add('operateur_jour', 'choice', array(
                    'choices' => $operateurListeUnique,
                    'required' => true,
                ))
                ->getForm();

        return $form->createView();
    }

    public function getProduitByTourneeAjaxAction() {
//        // verifie si on a droit a acceder a cette page
//        $bVerifAcces = $this->verif_acces();
//        if ($bVerifAcces !== true) {
//            return $bVerifAcces;
//        }      

        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $ids = $request->get("tourneeIds");

        $response = array(
            "produits" => array(),
            "jour_type" => array(),
            "typeService" => array()
        );

        if (!setlocale(LC_TIME, "fr_FR")) {
            setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        }

        $produitsData = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getProduitsByTourneeIds($ids);
        $aTmpJourdId = $aTmpProduitId = array();

        foreach ($produitsData as $produitData) {
            $id = $produitData['produitId'];

            if (!in_array($produitData['jourId'], $aTmpJourdId)) {
                $response["jour_type"][$produitData['jourId']] = ucfirst($produitData['libelle']);
                $aTmpJourdId[] = $produitData['jourId'];
            }

            /* @var $Produit \Ams\ProduitBundle\Entity\Produit */
            if (!in_array($id, $aTmpProduitId)) {
                $Produit = $em->getRepository('AmsProduitBundle:Produit')->find($id);
                $response["produits"][$id] = $Produit->getLibelle();
                $aTmpProduitId[] = $id;
            }

            $typeService = ($produitData["typeService"] != null) ? $produitData["typeService"] : "L";
            $response["typeService"][$typeService] = ($typeService == "L") ? "Livré" : "Repérage";
        }

        $return = json_encode($response);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    /** EFFACE LES FICHIERS SUPERIEUR A 1 HEURE* */
    function dumpTmpFile() {
        if ($handle = opendir('tmp')) {
            while (false !== ($file = readdir($handle))) {
                if (!preg_match('/^file*/', $file))
                    continue;
                if ((time() - filemtime('tmp/' . $file)) > 3600)
                    unlink('tmp/' . $file);
            }
        }
        closedir($handle);
    }

    /**
     * Permet d'assainir une requête d'export afin d'en filtrer tout élément indésirable
     * @param string $sQuery La requête d'export
     * @return string $sNewQuery La requête d'export modifiée
     */
    private function sanitizeExportQuery($sQuery) {
        // Suppression d'un éventuel point virgule à la fin
        $sNewQuery = trim($sQuery);
        if ($sNewQuery[strlen($sNewQuery) - 1] == ';') {
            $sNewQuery = substr($sNewQuery, 0, strlen($sNewQuery) - 1);
        }

        // Suppression de " GROUP BY AB.numabo_ext, fusion_soc_id "
        $sNewQuery = str_replace('GROUP BY AB.numabo_ext, fusion_soc_id', '', $sNewQuery);

        return $sNewQuery;
    }

    /**
     * Permet de récupérer une liste de tournées (codes MTJ) déclinée selon les jours d'application sélectionnés en prenant en compte les jours déjà appliqués
     * @param int $iReqExpId L'ID de la requete d'export
     * @param string $sListeOrig La liste des tournées du périmètre à l'export
     * @param string $sJours Le tableau des jours sélectionnés pour l'application au format JSON
     * @param string $sOptimInfo L'historique d'application de cette requete au format JSON
     * @param string $sDateAppliq La date d'application choisie par l'opérateur
     * @param object $em L'Entity Manager
     * @param int $iDelai Le délai à respecter pour ne pas appliquer d'optimisations in extremis
     * @return array $aListe 
     */
    public static function defPerimetreEffectif($iReqExpId, $sListeOrig, $sJours, $sOptimInfo, $sDateAppliq, $em, $iDelai) {
        // Décodage de la liste
        $aListeTournee = unserialize(base64_decode($sListeOrig));

        if (empty($aListeTournee)) {
            return false;
        }

        // Parcours dans la liste
        $aListe = array();
        $aListeTournees = array();
        $aListeTourneesJours = array();
        $aListeJours = array();
        $aListeJoursId = array();
        $aListeJoursExclusId = array();
        $aJoursIds = json_decode($sJours);
        $aJoursCodes = array();
        // Récupération des codes des jours
        if (empty($aJoursIds)) {
            return false;
        }

        foreach ($aJoursIds as $iJourID) {
            $oJour = $em->getRepository('AmsReferentielBundle:RefJour')->findOneById($iJourID);
            // Prise en compte de l'historique de cette requete d'export
            $aJoursAppliq = self::getDaysListFromOptimInfo($sOptimInfo);
            
            if (!in_array($oJour->getCode(), $aJoursAppliq)){
                // On vérifie le jour est appliquable maintenant
                if (self::checkIfApplicationOk(GlobalReferentielController::convertMroadDay2PHPDOW($iJourID), $sDateAppliq, $iDelai)){
                    $aJoursCodes[] = $oJour->getCode();
                    $aListeJoursId[] = $iJourID;
                    $aListeJours[$oJour->getCode()] = array(
                        'date' => date('Y-m-d h:i:s')
                    );
                }
                else{
                    // Ce jour n'a pas été appliqué et n'est pas applicable aujourd'hui
                    $aListeJoursExclusId[] = $iJourID;
                }
            }
        }

        // On récupère des codes de modèles tournées uniques
        $aMTUniques = array();
        foreach ($aListeTournee as $sMtjCode) {
            $sRacineMT = self::getMtFromMtj($sMtjCode);

            if (!in_array($sRacineMT, $aMTUniques)) {
                $aMTUniques[] = $sRacineMT;
            }
        }

        // On parcourt la liste des MT uniques pour les décliner selon les jours sélectionnés par l'opérateur
        foreach ($aMTUniques as $sRacineMT) {
            foreach ($aJoursCodes as $sJour) {
                $sMTJ = $sRacineMT . $sJour;
                $aListeTourneesJours[] = $sMTJ;
            }
        }
        $aListe['jours'] = $aListeJours;
        $aListe['jours_id'] = $aListeJoursId; // Tableau des ID des jours à appliquer
        $aListe['jours_exclus_id'] = $aListeJoursExclusId; // Tableau des ID des jours non applicables
        $aListe['tournees'] = $aMTUniques;
        $aListe['tournees_jour'] = $aListeTourneesJours;
        $aListe['req_exp_id'] = $iReqExpId;
        return $aListe;
    }

    /**
     * Retourne la racine d'un code MTJ en supprimant le code jour (2 derniers caractères)
     * @param string $sMtjCode Le code de tournée-jour
     * @return string $sMt
     */
    public static function getMtFromMtj($sMtjCode) {
        $iJrStrLen = 2; // Longueur du code jour
        return substr($sMtjCode, 0, strlen($sMtjCode) - $iJrStrLen);
    }

    /**
     * Retourne VRAI si l'optimisation peut être appliquée pour un jour de semaine et une date d'application donnée
     * @param int $iDayOfWeek Le jour de la semaine au format numérique PHP\date de 0 (pour dimanche) à 6 (pour samedi)
     * @param string $sDate La date au format MySQL Date YYYY-MM-DD
     * @param int $iDelai Le nombre de jours d'écart à respecter entre le jour de l'application et la date prévue
     * @return bool $bResult TRUE si l'application peut être faite
     */
    public static function checkIfApplicationOk($iDayOfWeek, $sDate, $iDelai = 2) {
        $bResult = false;

        $dowMap = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
        // On détermine le 1er jour d'application pour les tournées du jour en question
        $firstDayNext = date('Y-m-d', strtotime($dowMap[$iDayOfWeek] . ' next week ' . $sDate));
        $firstDayThis = date('Y-m-d', strtotime($dowMap[$iDayOfWeek] . ' this week ' . $sDate));

        if ($firstDayThis < $sDate) {
            $sTargetDay = $firstDayNext;
        } else {
            $sTargetDay = $sDate;
        }

        // Comparaison avec la date courante
        $sNextDow = date('Y-m-d', strtotime('next ' . $dowMap[$iDayOfWeek]));
        
        if ($sNextDow >= $sTargetDay) {
            // Test sur le délai
            if ($iDelai){
                $oNow = new \DateTime();
                $oNow->setTime(0,0,0);

                $oAppDate = new \DateTime($sTargetDay);
                $oInterval = $oNow->diff($oAppDate, true);
                
                $bResult = (int)$oInterval->format('%a') >= $iDelai ? true : false;
            }
            else{
                $bResult = true;
            }
        } else {
            $bResult = false;
        }

        return $bResult;
    }

    /**
     * Retourne un tableau contenant la liste des jours dont l'optimisation a déjà été appliquée
     * @param type $sOptimInfo Le JSON contenant l'historique d'application de la requête d'optim
     * @return array $aListe La liste des jours déjà appliqués
     */
    public static function getDaysListFromOptimInfo($sOptimInfo){
        $aListe = array();
        
        $aOptimInfo = json_decode($sOptimInfo);
        if (!empty($aOptimInfo)){
            foreach ($aOptimInfo as $aPasse){
                    foreach ($aPasse->tournees->historique as $sJourCode => $aOptim){
                        if (!in_array($sJourCode, $aListe)){
                            $aListe[] = $sJourCode;
                        }
                    }
            }
        }
        return $aListe;
    }
    
    /**
     * Retourne le nombre de tournées ayant été optimisées en se basant sur l'historique d'application
     * @param type $sOptimInfo Le JSON contenant l'historique d'application de la requête d'optim
     * @return int $iNbTournees Le nombre de tournées ayant été optimisées pour cette requête
     */
    public static function compterTourneesOptimisees($sOptimInfo){
        $aMtjUniques = array();
        $aOptimInfo = json_decode($sOptimInfo);
        if (!empty($aOptimInfo)){
            foreach ($aOptimInfo as $aPasse){
                    foreach ($aPasse->tournees->historique as $sJourCode => $aOptim){
                        if (!empty($aOptim)){
                            foreach ($aOptim as $sMtjCode){
                                if (!in_array($sMtjCode, $aMtjUniques)){
                                    $aMtjUniques[] = $sMtjCode;
                                }
                            }
                        }
                    }
            }
        }
        
        $iNbTournees = count($aMtjUniques);
        return $iNbTournees;
    }
    
    /**
     * Retourne le nombre de tournées vides ou non existantes détectées lors d'une d'application
     * @param type $sOptimInfo Le JSON contenant l'historique d'application de la requête d'optim
     * @return int $iNbTournees Le nombre de tournées vides ou inexistantes pour cette requête
     */
    public static function compterTourneesVides($sOptimInfo){
        $aMtjUniques = array();
        $aOptimInfo = json_decode($sOptimInfo);
        if (!empty($aOptimInfo)){
            foreach ($aOptimInfo as $aPasse){
                    foreach ($aPasse->tournees_inexistantes as $sJourCode){
                        if (!empty($sJourCode)){
                            if (!in_array($sJourCode, $aMtjUniques)){
                                    $aMtjUniques[] = $sJourCode;
                                }
                        }
                    }
            }
        }
        
        $iNbTourneesVides = count($aMtjUniques);
        return $iNbTourneesVides;
    }
    
    private function selectJourType($selectorDay,$sizeModal = false){
        $modal = ($sizeModal) ? '$("#amsModalBody").attr("style","height:'.$sizeModal.'px")' : '';
        return '
            <script>
                $(function(){
                  $("#day_type").multipleSelect();
                  '.$modal.'
                });
            </script>
            <div style="margin-top:10px;"> 
                <strong style="float:left;width:200px;display:block;text-align:right;padding-right:15px"> Selectionner les jours à appliquer : </strong>
               ' . $selectorDay . '
            </div> ';
    }
    
}
