<?php

/**
 * Controleur par défaut du bundle hors presse
 *
 * @author madelise
 */

namespace Ams\HorspresseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;
use Ams\HorspresseBundle\Entity\Fichier as Fichier;
use Ams\HorspresseBundle\Form\Type\FileType as FileType;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends GlobalController {

    // Les types de campagne
    const TYPE_DTB_FILE = 'dtb';
    const TYPE_DTB_NOFILE = 'dtb_sansfichier';
    const TYPE_NOMI = 'nominatif';
    const TYPE_ONEONE = '1pour1';
    // Les status de campagne
    const STATUS_NEW = 'new';
    const STATUS_NEW_LIBELLE = 'En étude';
    const STATUS_PLAN = 'planned';
    const STATUS_PLAN_LIBELLE = 'Planifiée';
    const STATUS_EXEC = 'exec';
    const STATUS_EXEC_LIBELLE = 'Exécutée';

    public function initiate() {
        $this->session = $this->get('session');
        $this->em = $this->getDoctrine()->getManager();
    }

    public function indexAction() {
        $this->initiate();

        $aCamp = $this->em->getRepository('AmsHorspresseBundle:Campagne')->findBy(array());

        return $this->render('AmsHorspresseBundle:Default:index.html.twig', array(
                    'liste_campagnes' => $aCamp
        ));
    }

    /**
     * Action qui permet d'ajouter une nouvelle campagne
     */
    public function ajouterAction() {
        $this->initiate();

        // Récupération de la liste des sociétés
        $aListeSte = $this->em->getRepository("AmsProduitBundle:Societe")->getSocietesAvecProduitsHP();

        return $this->render('AmsHorspresseBundle:Default:ajouter.html.twig', array(
                    'aListSte' => $aListeSte,
        ));
    }

    /**
     * Teste une campagne.
     * Fournit la réponse sous forme de JSON pour les requêtes AJAX
     * @param Request $request
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkAction(Request $request) {
        $this->initiate();

        $aTestResult = array('exists' => FALSE);
        $aTests = $request->query->get('tests');

        if (!is_null($request->query->get('type'))) {
            switch ($request->query->get('type')) {
                case 'libelle':
                    if (!empty($aTests)) {
                        foreach ($aTests as $sTest) {
                            switch ($sTest) {
                                case 'exists':
                                    $sLibelle = $request->query->get('libelle');
                                    $oCampagne = $this->em->getRepository('AmsHorspresseBundle:Campagne')->findOneByLibelle($sLibelle);
                                    if (!empty($oCampagne)) {
                                        $aTestResult['exists'] = TRUE;
                                    }
                                    break;
                            }
                        }
                    }
                    break;
            }
        }

        return new JsonResponse($aTestResult);
    }

    /**
     * Retourne une liste de flux filtrée ou non sur un produit de référence.
     * @param Request $request
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getfluxfromproductAction(Request $request) {
        $this->initiate();

        $aTestResult = array(
            'product_id' => NULL
        );

        if (!is_null($request->query->get('product_id'))) {
            // filtrage sur le produit
            $aTestResult['product_id'] = (int) $request->query->get('product_id');
        }

        $aFlux = $this->em->getRepository('AmsReferentielBundle:RefFlux')->getFluxs();
        if (!empty($aFlux)) {
            $aInfo = array();
            foreach ($aFlux as $oFlux) {
                $aInfo['id'] = $oFlux->getId();
                $aInfo['libelle'] = $oFlux->getLibelle();

                // Ajout au tableau final
                $aTestResult['aFlux'][] = $aInfo;
            }
        }

        return new JsonResponse($aTestResult);
    }

    /**
     * Enregistre une campagne via Ajax et retourne un état via JSON
     * @param Request $request
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */
    public function storecampainAction(Request $request) {
        $this->initiate();

        $aReturn = array(
            'returnCode' => NULL,
            'msg' => NULL,
            'errCode' => NULL,
            'errMsg' => NULL,
            'datas' => array()
        );

        if ($request->getMethod() != 'POST') {
            $aReturn['errCode'] = 1; // Pb dans la requête
            $aReturn['errMsg'] = 'Requête incorrecte';
        } else {
            // Test des données envoyées
            if (!$this->validatestorerequest($request->request)) {
                $aReturn['errCode'] = 2; // Pb dans les données
                $aReturn['errMsg'] = 'Données incorrectes';

                return new JsonResponse($aReturn);
            }

            $oCampagne = new \Ams\HorspresseBundle\Entity\Campagne();
            $oCampagne->setLibelle($request->request->get('titre'));
            $oCampagne->setStatut(self::STATUS_NEW);
            $oCampagne->setType($this->setCampainType($oCampagne, $request->request));
            $oCampagne->setDebordement($request->request->get('tournees_debord'));

            // Gestion du produit
            $oProduit = $this->em->getRepository('AmsProduitBundle:Produit')->findOneById($request->request->get('produit_id'));
            $oCampagne->setProduit($oProduit);

            // Gestion de la société
            $oSte = $this->em->getRepository('AmsProduitBundle:Societe')->findOneById($request->request->get('ste_id'));
            $oCampagne->setSociete($oSte);

            // Dates
            $oDateDebut = new \DateTime();
            $aDateDebutElems = explode('/', $request->request->get('date_debut'));
            $oDateDebut->setDate($aDateDebutElems[2], $aDateDebutElems[1], $aDateDebutElems[0]);
            $oCampagne->setDateDebut($oDateDebut);

            $oDateFin = new \DateTime();
            $aDateFinElems = explode('/', $request->request->get('date_fin'));
            $oDateFin->setDate($aDateFinElems[2], $aDateFinElems[1], $aDateFinElems[0]);
            $oCampagne->setDateFin($oDateFin);
            
            // Fichier de données
            $oFichier = $this->em->getRepository('AmsHorspresseBundle:Fichier')->findOneById((int)$request->request->get('fichier_id'));

//            var_dump($oCampagne); exit();
            $oNow = new \DateTime();
            $oCampagne->setDateCrea($oNow);

            $oCampagne->setDebordement($request->request->get('tournees_debord')); // A supprimer

            $this->em->persist($oCampagne);

            // Création de la première configuration
            $oConfig = new \Ams\HorspresseBundle\Entity\ConfigurationCampagne();
            $oConfig->setType($this->setCampainType($oCampagne, $request->request))
                    ->setLibelle('Configuration initiale')
                    ->setFlux($request->request->get('flux'))
                    ->setChargeTournees($request->request->get('charge_tournees') || FALSE)
                    ->setChargeTourneesMax($request->request->get('charge_max') || NULL)
                    ->setTourneesDediees($request->request->get('tournees_debord') || FALSE)
                    ->setChargeTourneesDedieesMax($request->request->get('charge_debord_max') || NULL)
                    ->setFichier($oFichier)
                    ->setStructureFichier($request->request->get('fichier_client_structure') || json_encode(array()))
                    ->setFichierNominatif($request->request->get('fichier_nominatif') || FALSE)
                    ->setFichierQte($request->request->get('fichier_qte') || FALSE)
                    ->setFichierNbBal($request->request->get('fichier_nb_bal') || FALSE)
                    ->setPeriodeHistorique($request->request->get('periode') || NULL)
                    ->setTempsSup($request->request->get('temps_sup') || 0)
                    ->setCampagne($oCampagne)
            ;

            $this->em->persist($oConfig);

            try {
                $this->em->flush();
                $aReturn['returnCode'] = 1;
                $aReturn['msg'] = 'La campagne a bien été créée.';
                $aReturn['datas'] = $oCampagne;
            } catch (\Exception $e) {
                $aReturn['errCode'] = 3; // Pb d'insertion des données
                $aReturn['errMsg'] = 'Erreur rencontrée lors de l\'insertion des données';
            }
        }

        return new JsonResponse($aReturn);
    }

    /**
     * Action d'affichage du calendrier des opérations hors presse
     */
    function calendrierAction() {
        $em = $this->getDoctrine();

        // $evenements se réfère aux différents status des opérations HP
        $evenements = array(
            self::STATUS_NEW => array(
                'libelle' => self::STATUS_NEW_LIBELLE,
                'couleur' => '#A80D16'
            ),
            self::STATUS_PLAN => array(
                'libelle' => self::STATUS_PLAN_LIBELLE,
                'couleur' => '#FF8F48'
            ),
            self::STATUS_EXEC => array(
                'libelle' => self::STATUS_EXEC_LIBELLE,
                'couleur' => '#00A200'
            )
        );

        $operations = $em->getRepository('AmsHorspresseBundle:Campagne')->findAll();

        return $this->render('AmsHorspresseBundle:Default:calendrier.html.twig', array(
                    'operations' => $operations,
                    'evenements' => $evenements,
        ));
    }

    /**
     * Méthode d'envoi de fichier client
     * @param Request $request
     */
    public function uploadfileAction(Request $request) {
        $this->initiate();
        
        $aReturn = array(
            'returnCode' => NULL,
            'msg' => NULL,
            'errCode' => NULL,
            'errMsg' => NULL,
            'datas' => array()
        );

        $oFile = new Fichier();
        $form = $this->createFormBuilder($oFile, array('csrf_protection' => false))
                ->add('fichier', 'file')
                ->add('structure', 'text')
                ->add('config', 'text')
                ->getForm();

        if ($request->isMethod('POST')) {
            $aReturn['msg'] = 'Formulaire posté';

            $form->submit(array(
                'fichier' => $request->files->get('fichier'),
                'structure' => $request->request->get('structure'),
                'config' => $request->request->get('config'),
            ));
            
//            var_dump($request->files->get('fichier')); exit();
            
            if ($form->isSubmitted()) {
                $aReturn['msg'] = 'Formulaire reçu';
                
                if ($form->isValid()) {
                    $aReturn['msg'] = 'Formulaire valide';
                    
                    $fichier = $form->get('fichier')->getNormData();
                    $sStruct = $request->request->get('structure');
                    $sConfig = $request->request->get('config');
                    
                    if ($fichier->isValid()){
                        $aReturn['msg'] = 'Fichier valide';
                        
                        // Configuration du nom de fichier
                        $sUploadDir = $this->get('kernel')->getRootDir()
                                    .'/..'.$this->container->getParameter("UPLOAD_WEB_FILES_ROOT_DIR")
                                    .$this->container->getParameter("UPLOAD_HP_DIR");
                        $sNomFic = md5(time()).rand().'.csv';

                        if ($fichier->move($sUploadDir,$sNomFic)){
                            // Configuration
                            $sConfig = urldecode($sConfig);
                            $sStruct = urldecode($sStruct);
                            
                            // Enregistrement dans la BDD
                            $oFile->setStructure($sStruct)
                                    ->setName($sNomFic)
                                    ->setConfig($sConfig)
                                    ->setDate(new \DateTime())
                                    ->setPath($sUploadDir.$sNomFic)
                                    ->setDatas(array())
                                    ;
                            
                            $this->em->persist($oFile);
                            $this->em->flush();
                            $aReturn['msg'] = 'Fichier enregistré';
                            
                            $oSentConfig = json_decode($sConfig);
                            $oConfig = new \stdClass();
                            $oConfig->delimiter = $this->getRealSeparator($oSentConfig->delim);
                            $oConfig->hasHeader = $oSentConfig->hasHeader; // A modifier
                            $oConfig->header = json_decode($sStruct);
                            $oConfig->headerPadding = 'Auto N/A';
                            
                            $sAnalyse = $this->getCsvAnalysis($sUploadDir.$sNomFic,$oConfig);
                            if ($sAnalyse['status'] == TRUE){
                                $aReturn['msg'] = 'Fichier traité';
                                $aReturn['returnCode'] = 1;
                                $aReturn['result'] = $sAnalyse;
                                $aReturn['result']['idFichier'] = $oFile->getId();
                                $aReturn['result']['chemin'] = $sNomFic;
                                
                                // Modification des données de l'enregistrement
                                $oFile->setDatas($aReturn['result']);
                                $this->em->persist($oFile);
                                $this->em->flush();
                            }
                                
                        }
                        else{
                            $aReturn['msg'] = NULL;
                            $aReturn['errCode'] = 3;
                            $aReturn['errMsg'] = array('Erreur rencontrée lors du déplacement du fichier');
                        }
                    }
                    else{
                        $aReturn['msg'] = NULL;
                        $aReturn['errCode'] = 2;
                        $aReturn['errMsg'] = array('Fichier invalide', $form->getErrors());
                    }
                }
                else{
                    $aReturn['msg'] = NULL;
                    $aReturn['errCode'] = 1;
                    $aReturn['errMsg'] = array('Formulaire invalide', $form->getErrors());
                }
            }
        }

        return new JsonResponse($aReturn);
    }
    
    /**
     * Transforme le code de séparateur posté par le formulaire en chaine de caractère à utiliser pour la séparation des données
     * @param string $sSep Le code de séparateur
     * @return string Le vrai séparateur à utiliser
     */
    public function getRealSeparator($sSep){
        switch ($sSep){
            case 'comma':
                return ',';
                break;
            case 'semi':
                return ';';
                break;
            case 'pipe':
                return '|';
                break;
        }
    }
    
    /**
     * Produit une analyse superficielle d'un fichier CSV et retourne le tableau de données
     * @param string $sFilePath Le chemin du fichier
     * @param object $oConfig Un objet de configuration de l'analyse
     * @return array $aResult Le tableau de résultat (informations + tableau de données)
     */
    public function getCsvAnalysis($sFilePath, $oConfig){
        $aResult = array();
        
        $sDelim = $oConfig->delimiter;
        $bTruncatedHeaders = FALSE;
        $bPaddedHeaders = FALSE;
        $nbLines = 0;
        
        // Compteur de produits (quantité)
        $iNbProd = 0;
        
        // Tableau des points de livraison
        $aAdresses = array();
        
        $csv = array_map('str_getcsv', file($sFilePath), array($sDelim));
        array_walk($csv, function(&$a) use ($csv, $oConfig, &$nbLines, &$bPaddedHeaders, &$bTruncatedHeaders, &$iNbProd, &$aAdresses) {
            // On vérifie le nombre de colonnes  
            
            // Colonnes plus nombreuses que les en-têtes
            if (count($oConfig->header) < count($a)){
                $iDiff = count($a) - count($oConfig->header);
                for ($z = 0; $z < $iDiff; $z++){
                    $oConfig->header[] = $oConfig->headerPadding.$z;
                }
                $bPaddedHeaders = TRUE;
            }
            
            // En-têtes plus nombreuses que les colonnes
            if (count($oConfig->header) > count($a)){
                // On tronque les colonnes
                $oConfig->header = array_slice($oConfig->header, 0, count($a));
                $bTruncatedHeaders = TRUE;
            }
            $a = array_combine($oConfig->header, $a);
            
            // Récupération de la qté
            if (in_array('qte', $oConfig->header)){
                $iNbProd += (int)$a['qte'];
            }
            $nbLines++;
            
            // Ajout de l'adresse
            if (in_array('vol4', $oConfig->header) && in_array('cp', $oConfig->header)){
                $sAddr = $a['vol4']. ' '.$a['cp'];
                if (!in_array($sAddr, $aAdresses)){
                    $aAdresses[] = $a['vol4']. ' '.$a['cp']; 
                }
            }
        });
        
        if ($oConfig->hasHeader){
            array_shift($csv); // Suppression de l'en-tête
            $nbLines--;
        }
        
        // Calcul de la quantité de produits
        $nbProds = in_array('qte', $oConfig->header) ? $iNbProd : $nbLines;
        
        $aResult['datas'] = $csv;
        $aResult['status'] = TRUE; // A changer
        $aResult['info'] = array(
            'truncate' => $bTruncatedHeaders,
            'padding' => $bPaddedHeaders,
            'headers' => $oConfig->header,
            'num' => $nbLines,
            'qte' => $nbProds,
            'nbP2L' => count($aAdresses),
        );
        return $aResult;
    }
    
    
    
    /**
     * Teste les données de la requête de création
     * @param object $oRequest
     * @return boolean $bResult Vrai si la requête est valide
     */
    private function validatestorerequest($oRequest) {
        $bResult = TRUE;

        $aNonEmpty = array(
            'titre', 'date_debut', 'date_fin', 'flux', 'periode', 'produit_id', 'ste_id', 'temps_sup'
        );
        if ($oRequest->get('fichier_fourni') == "1") {
            $aNonEmpty[] = 'fichier_client_delim';
            $aNonEmpty[] = 'fichier_client_entete';
            $aNonEmpty[] = 'fichier_client_structure';
            $aNonEmpty[] = 'fichier_nominatif';
        }
        foreach ($aNonEmpty as $sOblig) {
            $mTestVal = $oRequest->get($sOblig);
            if (empty($mTestVal)) {
                $bResult = FALSE;
                return $bResult;
            }
        }

        return $bResult;
    }

    /**
     * Retourne le type de campagne à enregistrer dans la BDD
     * @param object $oCampain L'objet campagne
     * @param object $oRequest L'objet de la requête POST
     * @return string $sType La chaine de caractère à utiliser en tant que type
     */
    private function setCampainType($oCampain, $oRequest) {
        if ($oRequest->get('fichier_nominatif') == '1') {
            $sType = self::TYPE_NOMI;
        } else {
            if ($oRequest->get('fichier_fourni') == '1') {
                $sType = self::TYPE_DTB_FILE;
            } else {
                $sType = self::TYPE_DTB_NOFILE;
            }
        }

        return $sType;
    }

}
