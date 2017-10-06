<?php

namespace Ams\DistributionBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Ams\DistributionBundle\Form\CrmDetailType;
use Symfony\Component\HttpFoundation\Request;
use Ams\DistributionBundle\Entity\CrmDetail;
use Doctrine\ORM\EntityRepository;
use DateTime;
use HTML2PDF;
use PHPExcel_Worksheet_PageSetup;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Cell_DataType;
use Ams\DistributionBundle\Entity\CrmDetail as CrmEntity;


class CrmController extends GlobalController {
    /**
     * [indexAction affichage de la grid initiale + la recherche des reclam/reminfo par intervalle de date]
     * @return [type] [description]
     */
    public function indexAction(){
       
        $em = $this->getDoctrine()->getManager();
        $bVerifAcces = $this->verif_acces();
        $request = $this->getRequest();
        $session  = $this->get("session");
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        } 
        // Les depots auquels l'utilisateur courant a droit
        $depots      = $this->get('session')->get('DEPOTS');
        $depot_ids   = array_keys($depots);
        //formulaire de recherche des reclam/remInfo  
        $form        = $this->createFormBuilder()
                            ->add('DateParutionMin', 'text', array('required'=>true, 'label'=>'Date de début'))
                            ->add('DateParutionMax', 'text', array('required'=>true, 'label'=>'Date de fin'))
                            ->getForm();

        if($request->getMethod() == 'POST'){
            $dataInform =  $request->request->get('form');
            $dateMinToDisplay = $dataInform['DateParutionMin'];
            $dateMaxToDisplay = $dataInform['DateParutionMax'];          
        } else {
            //date affichée par default
            $dateMinToDisplay =  $dateMaxToDisplay = Date('d/m/Y');
        } 
        
        $dateMin = $this->transformDateToDataBaseFormat($dateMinToDisplay, '/', '-');
        $dateMax = $this->transformDateToDataBaseFormat($dateMaxToDisplay, '/', '-');
        $session->set('crmDateMin', $dateMin);
        $session->set('crmDateMax', $dateMax);
        $dateInterval = $this->getDateIntervalToExecuteQuery($dateMin, $dateMax);
       // $societes = $em->getRepository('AmsFichierBundle:FicRecap')->getSocieteFromFicRecapByDate( $dateMin,  $dateMax);
         $societes =  $em->getRepository('AmsDistributionBundle:CrmDetail')->getSocietsFromCrmByDate( $dateInterval['dateMin'],  $dateInterval['dateMax']);
        return $this->render('AmsDistributionBundle:Crm:index.html.twig', array('nbDepots' => count($depot_ids),
                                                                                'form'=>$form->createView(),
                                                                                'dateMin'=>$dateMinToDisplay,
                                                                                'dateMax'=>$dateMaxToDisplay,
                                                                                'societes'=>$societes,
                                                                                 ));
    }
    /**
     * [getDateIntervalToExecuteQuery comme on cherche les réclam par date de création (type datetime) 
     * et les dates venanat du formulaire sont de type string cette fonction permet de contruire l'intervalle de date
     * on doit faire ce traitement pour récupéerer le bon intervale de rechereche]
     * @param  [type] $dateMin [description]
     * @param  [type] $dateMax [description]
     * @return [type]          [description]
     */
    public function getDateIntervalToExecuteQuery($dateMin, $dateMax){
        $interval = array();
        $dateMin = new \DateTime($dateMin);
        $dateMin = $dateMin->modify('-1 day');
        $dateMin->setTime(23, 59, 59);
        $interval['dateMin'] = $dateMin->format('Y-m-d H:i:s');
        $dateMax = new \DateTime($dateMax);
        $dateMax->setTime(23, 59, 59);
        $interval['dateMax'] = $dateMax->format('Y-m-d H:i:s');

        return  $interval;
    }
   
    /**
     * [indexCrmDonneesAction Liste des réclamation/rementée dinfo par societe(mise a jour de la grid xml)
     * @return [\Symfony\Component\HttpFoundation\Response] [description]
     */
    public function indexCrmDonneesAction($dateMin, $dateMax){
      
        $em = $this->getDoctrine()->getManager();
        $depots = $this->get('session')->get('DEPOTS');
        $depot_ids   = array_keys($depots); 
        $dateMin = $this->transformDateToDataBaseFormat($dateMin, '-');
        $dateMax = $this->transformDateToDataBaseFormat($dateMax, '-');
        $dateInterval = $this->getDateIntervalToExecuteQuery($dateMin, $dateMax);
        $societes =  $em->getRepository('AmsDistributionBundle:CrmDetail')->getSocietsFromCrmByDate($dateInterval['dateMin'],  $dateInterval['dateMax']);          
        $data = $this->getAllDataToDisplayInXmlGrid($dateInterval['dateMin'],  $dateInterval['dateMax']);
        $data = array_merge($data,array('societes'=>$societes, 'depots'=>$depots,'dateMin'=>$dateMin, 'dateMax'=>$dateMax));
        $response = $this->renderView('AmsDistributionBundle:Crm:grid.xml.twig',  $data);

        return new Response($response, 200, array('Content-Type' => 'text/xml'));
    }
    
    
    private function convertFormatDate($date){
        $date2 = explode('-',$date);
        return $date2[2].'-'.$date2[1].'-'.$date2[0];
    }
    /**
     * Action d'affichage des réclamations pour une seule société
     */
    public function displayOnlyDetailCrmAction(){
        $session  = $this->get("session");
        $em = $this->getDoctrine()->getManager();
        $crmDetailRepository = $em->getRepository('AmsDistributionBundle:CrmDetail');
        $request = $this->getRequest();
        
        $dateMin = $session->get('crmDateMin');
        $dateMax = $session->get('crmDateMax');
//        $dateMin = $request->get('dateMin');
//        $dateMax = $request->get('dateMax');
        $categorieId = $request->get('cId');
        $depotId = $request->get('dpId');
//        var_dump($depotId);exit;
        $societeId = $request->get('sId');
        $fluxId = $request->get('fId');
        $all = $request->get('all');
        $tourneeId = $request->get('tId');
        $isImputationDate = $request->get('date_imputation');
        
        $isWithResponse = $request->get('wRes');//si reclam avec réponse ou non
        $dateInterval = $this->getDateIntervalToExecuteQuery($dateMin, $dateMax);
        if($isImputationDate == 1){
            if(!is_int($tourneeId)){
                $tourneeId = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findIdByCodeDateValid($tourneeId,$this->convertFormatDate($request->cookies->get('date_imputation_min')));
            }
            $result = $crmDetailRepository->getCrmBySocieteAndDepot($categorieId,$this->convertFormatDate($request->cookies->get('date_imputation_min')), $this->convertFormatDate($request->cookies->get('date_imputation_max')),$societeId, $depotId,$isWithResponse, $tourneeId, $fluxId,1);
        }
        else    
            $result = $crmDetailRepository->getCrmBySocieteAndDepot($categorieId,$dateInterval['dateMin'],  $dateInterval['dateMax'],$societeId, $depotId,$isWithResponse, $tourneeId, $fluxId,0);

        $oResult = new CrmEntity();
        $crmType = '';
        
        if(empty($result)){
            $listTournee = $em->getRepository('AmsDistributionBundle:CrmDetail')->getTourneeIdByDepotDate($depotId,$dateMin,$dateMax);
            $listTournee = $this->transformArraysOnSingleArray($listTournee);
        }
        else
        {
            $depotId     = current($result)->getDepot()->getId();
            $crmResponse = current($result)->getCrmReponse();
            $crmType     = current($result)->getCrmDemande()->getCrmCategorie()->getLibelle();
            $categorieId = current($result)->getCrmDemande()->getCrmCategorie()->getId();
            $socId       = current($result)->getSociete()->getId();
            $depotLibelle = current($result)->getDepot()->getLibelle();
            $filter = true;
            $listTournee = $em->getRepository('AmsDistributionBundle:CrmDetail')->getTourneeIdByDepotDate($depotId,$dateMin,$dateMax);
            $listTournee = $this->transformArraysOnSingleArray($listTournee);
            $oResult = current($result);
        }
        
        $form = $this->createForm(new CrmDetailType( $categorieId ,$dateMin, $dateMax,$depotId, $em));
        $response = empty($crmResponse) ? '2' : '1'; //2 non répondu , 1 répondu
        
        $data['depotId'] = $depotId;
        $data['societeId'] =   $societeId ;
        $data['categorieId'] = $request->get('cId');
        $data['crmDemandeId'] = '';
        $data['isWithResponse'] = $isWithResponse;
        $data['tourneeId'] = $tourneeId;
        $data['societeLibelle'] = "";
        $data['filtre'] = "all";
        $data['depotLibelle'] = (current($result) !== false)? current($result)->getDepot()->getLibelle() : '';
        $data['fluxId'] = $request->get('fId');
        
//        var_dump($dateMin,$dateMax,$categorieId,$depotId,$societeId,$fluxId);exit;
//        var_dump($data);exit;
        
        return $this->render('AmsDistributionBundle:Crm:visualisation.html.twig', array(
                                                                                        'result' => $result,
                                                                                        'form'=>$form->createView(),
                                                                                        'dateMin' => $dateMin,
                                                                                        'dateMax' => $dateMax,
                                                                                        'crmType'=>$crmType,
                                                                                        'categorieId'=>"$categorieId",
                                                                                        'response'=>$response,
                                                                                        'dataPdf'=>$data,
                                                                                        'all'=>$all,
                                                                                        'isImputationDate'=>$isImputationDate,
                                                                                        ));
    }
    
    /**
     * Action d'affichage des réclamations pour une seule société
     */
    public function displayDivDetailCrmAction(){
        $session  = $this->get("session");
        $em = $this->getDoctrine()->getManager();
        $crmDetailRepository = $em->getRepository('AmsDistributionBundle:CrmDetail');
        $request = $this->getRequest();
        
        $dateMin = $session->get('crmDateMin');
        $dateMax = $session->get('crmDateMax');
//        $dateMin = $request->get('dateMin');
//        $dateMax = $request->get('dateMax');
        $categorieId = $request->get('cId');
        $depotId = $request->get('dpId');
//        var_dump($depotId);exit;
        $societeId = $request->get('sId');
        $fluxId = $request->get('fId');
        $all = $request->get('all');
        $tourneeId = $request->get('tId');
        $isImputationDate = $request->get('date_imputation');
        if($isImputationDate == 1){
            if(!is_int($tourneeId)){
                $tourneeId = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findIdByCodeDateValid($tourneeId,$this->convertFormatDate($request->cookies->get('date_imputation_min')));
            }
        }
        
        $isWithResponse = $request->get('wRes');//si reclam avec réponse ou non
        $dateInterval = $this->getDateIntervalToExecuteQuery($dateMin, $dateMax);
        $result = $crmDetailRepository->getCrmBySocieteAndDepotDiv($categorieId,$dateInterval['dateMin'],  $dateInterval['dateMax'],$societeId, $depotId,$isWithResponse, $tourneeId, $fluxId);

        $oResult = new CrmEntity();
        $crmType = '';
        
        if(empty($result)){
            $listTournee = $em->getRepository('AmsDistributionBundle:CrmDetail')->getTourneeIdByDepotDate($depotId,$dateMin,$dateMax);
            $listTournee = $this->transformArraysOnSingleArray($listTournee);
        }
        else
        {
            $depotId     = current($result)->getDepot()->getId();
            $crmResponse = current($result)->getCrmReponse();
            $crmType     = current($result)->getCrmDemande()->getCrmCategorie()->getLibelle();
            $categorieId = current($result)->getCrmDemande()->getCrmCategorie()->getId();
            $socId       = current($result)->getSociete()->getId();
            $depotLibelle = current($result)->getDepot()->getLibelle();
            $filter = true;
            $listTournee = $em->getRepository('AmsDistributionBundle:CrmDetail')->getTourneeIdByDepotDate($depotId,$dateMin,$dateMax);
            $listTournee = $this->transformArraysOnSingleArray($listTournee);
            $oResult = current($result);
        }
        
        $form = $this->createForm(new CrmDetailType( $categorieId ,$dateMin, $dateMax,$depotId, $em));
        $response = empty($crmResponse) ? '2' : '1'; //2 non répondu , 1 répondu
        
        $data['depotId'] = $depotId;
        $data['societeId'] =   $societeId ;
        $data['categorieId'] = $request->get('cId');
        $data['crmDemandeId'] = '';
        $data['isWithResponse'] = $isWithResponse;
        $data['tourneeId'] = $tourneeId;
        $data['societeLibelle'] = "";
        $data['filtre'] = "all";
        $data['depotLibelle'] = (current($result) !== false)? current($result)->getDepot()->getLibelle() : '';
        $data['fluxId'] = $request->get('fId');
        
//        var_dump($dateMin,$dateMax,$categorieId,$depotId,$societeId,$fluxId);exit;
//        var_dump($data);exit;
        
        return $this->render('AmsDistributionBundle:Crm:visualisation.html.twig', array(
                                                                                        'result' => $result,
                                                                                        'form'=>$form->createView(),
                                                                                        'dateMin' => $dateMin,
                                                                                        'dateMax' => $dateMax,
                                                                                        'crmType'=>$crmType,
                                                                                        'categorieId'=>"$categorieId",
                                                                                        'response'=>$response,
                                                                                        'dataPdf'=>$data,
                                                                                        'all'=>$all,
                                                                                        'isImputationDate'=>$isImputationDate,
                                                                                        ));
    }
    
    /**
     * [displayDetailCrm affiche/rechechere  des réclamation /rementée dinfo depuis la grid principale
     */ 
    public function displayDetailCrmAction(){

       /* $bVerifAcces = $this->verif_acces();//var_dump($bVerifAcces );die();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }*/
        $session  = $this->get("session");
        $em = $this->getDoctrine()->getManager();
        $crmDetailRepository = $em->getRepository('AmsDistributionBundle:CrmDetail');
        $request        = $this->getRequest();
        //date min/max saisie dans le formulaire de recherche comme intervalle
        $dateMin        = $session->get('crmDateMin');
        $dateMax        = $session->get('crmDateMax');
        $categorieId    = $request->get('cId');
        $depotId        = $request->get('dpId');
        $all        = $request->get('all');
        $societeId  = $request->get('sSId');
        $tourneeId = $request->get('tId');
        $fluxId = $request->get('fId');
        if(empty($societeId)){
            $societeId = $request->get('sId');
        }  
       
        $isWithResponse = $request->get('wRes');//si reclam avec réponse ou non
        $crmId = $request->get('crmId');
        $dateMaxDb = new \DateTime($dateMax);//modification de la date max pour quelle soit > a la datetime en base 
        $dateInterval = $this->getDateIntervalToExecuteQuery($dateMin, $dateMax);
        $result = $crmDetailRepository->getCrmBySocieteAndDepot($categorieId,$dateInterval['dateMin'],  $dateInterval['dateMax'],$societeId, $depotId,$isWithResponse, $tourneeId, $fluxId);

//        var_dump($result);exit;
        
        $oResult = new CrmEntity();
        $crmType = '';
        
        if(empty($result)){
            $listTournee = $em->getRepository('AmsDistributionBundle:CrmDetail')->getTourneeIdByDepotDate($depotId,$dateMin,$dateMax);
            $listTournee = $this->transformArraysOnSingleArray($listTournee);
        }
        else
        {
            $depotId     = current($result)->getDepot()->getId();
            $crmResponse = current($result)->getCrmReponse();
            $crmType     = current($result)->getCrmDemande()->getCrmCategorie()->getLibelle();
            $categorieId = current($result)->getCrmDemande()->getCrmCategorie()->getId();
            $socId       = current($result)->getSociete()->getId();
            $depotLibelle = current($result)->getDepot()->getLibelle();
            $filter = true;
            $listTournee = $em->getRepository('AmsDistributionBundle:CrmDetail')->getTourneeIdByDepotDate($depotId,$dateMin,$dateMax);
            $listTournee = $this->transformArraysOnSingleArray($listTournee);
            $oResult = current($result);
        }

        $form = $this->createForm(new CrmDetailType( $categorieId ,$dateMin, $dateMax,$depotId, $em, $listTournee),$oResult);
        
        $response = empty($crmResponse) ? '2' : '1'; //2 non répondu , 1 répondu
        
        // récupération des infos pour les passer aux pdf
        $data['depotId'] = $depotId;
        $data['societeId'] =   $societeId ;
        $data['categorieId'] = $request->get('cId');
        $data['crmDemandeId'] = '';
        $data['isWithResponse'] = $isWithResponse;
        $data['tourneeId'] = $tourneeId;
        $data['societeLibelle'] = "";
        $data['filtre'] = "all";
        $data['depotLibelle'] = (current($result) !== false)? current($result)->getDepot()->getLibelle() : '';
        
        if(isset($parameters))
            $data['fluxId']= $parameters['flux_id'];
        else
            $data['fluxId']= 0;
  
        return $this->render('AmsDistributionBundle:Crm:visualisation.html.twig', array(
                                                                                        'result' => $result,
                                                                                        'form'=>$form->createView(),
                                                                                        'dateMin' => $dateMin,
                                                                                        'dateMax' => $dateMax,
                                                                                        'crmType'=>$crmType,
                                                                                        'categorieId'=>"$categorieId",
                                                                                        'response'=>$response,
                                                                                        'dataPdf'=>$data,
                                                                                        'all'=>$all,
                                                                                        ));

    }


       /**
    * [serachReclam rechechere  des réclamations 
    * @return [\Symfony\Component\HttpFoundation\Response] [description]
    */ 
    public function serachReclamAction(){
        $em = $this->getDoctrine()->getManager();
        $session  = $this->get('session');
        $crmDetailRepository = $em->getRepository('AmsDistributionBundle:CrmDetail');
        $request = $this->getRequest();
        if($request->isXmlHttpRequest())
        {
           $result ='';
           $parameters = $request->get('parameters');
           extract($parameters);
          
           $dateMin = $this->transformDateToDataBaseFormat($dateMin, '/', '-');
           $dateMax = $this->transformDateToDataBaseFormat($dateMax, '/', '-');
           $session->set('crmDateMin', $dateMin);
           $session->set('crmDateMax', $dateMax);
           $dateInterval = $this->getDateIntervalToExecuteQuery($dateMin, $dateMax);
           $aData = array(
                            'flux_id' => $fluxId,
                            'demandeArbitrage' => $demandeArbitrage,
                        );
          
           $result = $crmDetailRepository->serachReclam($categorieId,$dateInterval['dateMin'],  $dateInterval['dateMax'],$societeId, $depotId,$isWithResponse, $crmDemandeId, $tourneeId, $aData);
           
           $return = array('result' => $result,'responseCode'=>200);

            return new Response(json_encode($return),$return['responseCode'], array('Content-Type'=>'application/json'));
        }

    }
 
    /**
     * [editAndValidateCrmAction edition et validation/répondre a une reclamation] depuis le tableau de recherche
     * @param  [type] $crmId [id de lentité crmDetail]
     * @return [type]        [description]
     */
    public function editAndValidateCrmAction($crmId){
 
        $request = $this->getRequest();
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $all        = $request->get('all');
       /*$bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        } */
        //droit d'acces au bloc arbitrage
        $hasAccesToArbitrageBloc = in_array('ARBITRAGE', $this->getEltsAccessible('crm_edit_and_validate')) ? true :false;
        //droit d'acces au bloc imputation paie
        $hasAccesToImputationPaie = in_array('ARBITRAGE-IMPUTATION-PAIE', $this->getEltsAccessible('crm_edit_and_validate')) ? true :false;
        $crmDetailRepository = $em->getRepository('AmsDistributionBundle:CrmDetail');

       if($request->getMethod() == 'POST')
        {
            $data = array(); $dataEntity= array();
            $data['cmtResponse'] = $request->get('cmtResponse');
            $data['crmReponse'] =  $request->get('crmReponse');
            $data['crmId'] =  $request->get('crmId');
            $userId      = $this->get('session')->get('UTILISATEUR_ID');
            $dataEntity['user'] = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($userId);
            $result = $this->saveCrmDeatil("update", $data,1, $dataEntity);
            $responseDate = new \DateTime();
            $return = array('result' => ($result == true) ? 'succsses' : 'error','dateResponse'=>$responseDate->format('d/m/Y'), 'user'=> $dataEntity['user']->getNom().' '.$dataEntity['user']->getPrenom());
            $return['responseCode']=200; 

            return new Response(json_encode($return),$return['responseCode'], array('Content-Type'=>'application/json'));
        }

        $alowwArbitrage = true;
        $categorieId = $crmDetail = $crmDetailRepository->findOneById($crmId);
        $crmTournee="";
        $crmTournee = $crmDetail->getTournee();
        if(!empty($crmTournee)){
           $alowwArbitrage =  $this->alowwArbitrage($crmTournee, $crmDetail);     
        }

        $categorieId = $crmDetail->getCrmDemande()->getCrmCategorie()->getId();
        $form = $this->createForm(new CrmDetailType($categorieId),$crmDetail);
        $form->add('crmId','hidden',array('attr'=>array('value'=>$crmId),'mapped'=>false,));
        $numAbo = $crmDetail->getAbonneSoc()->getId();
        $datesDistribution =  $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getDistribDateByAbonneSoc($numAbo);
        $allCrmByClient = $em->getRepository('AmsDistributionBundle:CrmDetail')->findBy(array('abonneSoc'=>$numAbo),array('dateCreat' => 'DESC'));
        $dateImp = $crmDetail->getDateImputationPaie();
        $dateCreat = $crmDetail->getDateCreat(); 
        $dateImputation  = !empty($dateImp) ? $dateImp  : $dateCreat;
        // recupération de la liste des tournées 
        $socId = $crmDetail->getSociete()->getId();
        $filtre = false; //filtre (=true) pour récupérer que les codes tourneés qui ont au moins une reclamation 
        $dateDebut = (!$crmDetail->getDateDebut())?  NULL : $crmDetail->getDateDebut()->format('Y-m-d');
        $dateFin = (!$crmDetail->getDateFin())?  NULL : $crmDetail->getDateFin()->format('Y-m-d');
        
        if($crmDetail->getDepot() != NULL ){
             $depotId = $crmDetail->getDepot()->getId();
            $tourneeList = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getRecTourneeByDepot($depotId, $socId="", $dateDebut, $dateFin, $filtre);
            $tourneeList = $this->transformArraysOnSingleArray($tourneeList);
        } else {
            $depotId = 0;
            $tourneeList = array();
        }
        $trnPaieId = 0;
        if($crmDetail->getTournee()){
            $trnPaieId = $crmDetail->getTournee()->getId();
            if(!(array_key_exists($trnPaieId, $tourneeList))){
                $tourneeList[$trnPaieId] = $crmDetail->getTournee()->getCode();
            }   
        }
        $tourneeList[0] = "Affecter une tournée";
        $formTournee  = $this->createFormBuilder()
                                ->add('tournee', 'choice', array('choices' => $tourneeList,'preferred_choices' => array($trnPaieId),))->getForm();
        $crmReperage = $em->getRepository('AmsDistributionBundle:Reperage')->findBy(array('abonneSoc'=>$numAbo),array('dateDemar' => 'DESC'));
        return $this->render('AmsDistributionBundle:Crm:edit_and_validate_crm.html.twig', array(
                                                                                                'crmReperage'=>$crmReperage,
                                                                                                'crmDetail'=>$crmDetail,
                                                                                                'form'=> $form->createView(),
                                                                                                'datesDistribution'=>$datesDistribution,
                                                                                                'allCrmByClient'=>$allCrmByClient,
                                                                                                'dateMin'=>$session->get('crmDateMin'),
                                                                                                'dateMax'=>$session->get('crmDateMax'),
                                                                                                'sId'=>$crmDetail->getSociete()->getId(),
                                                                                                'dpId'=>$depotId,
                                                                                                'categorieId'=>$categorieId,
                                                                                                'hasAccesToArbitrageBloc'=>$hasAccesToArbitrageBloc,
                                                                                                'imputationPaie'=>$crmDetail->getImputationPaie(),
                                                                                                'dateImputation'=>$dateImputation,
                                                                                                'alowwArbitrage'=>$alowwArbitrage,
                                                                                                'hasAccesToImputationPaie'=>$hasAccesToImputationPaie, 
                                                                                                'formTournee' => $formTournee->createView(),
                                                                                                 'all'=>$all,
                                                                                                 ));

    }

    /**
     * [alowwArbitrage description]
     * @param  [type] $crmTournee
     * @param  [type] $crmDetail
     * @return [type]
     */
    private function alowwArbitrage($crmTournee, $crmDetail){
        $em = $this->getDoctrine()->getManager();
        $tourneStartHour = $crmTournee->getTournee()->getHeureDebut();
        $tourneStartDate = $crmTournee->getDateDistrib();
        $tourneStartHour = $tourneStartHour->format('H:i:s');
        $tourneStartDate = $tourneStartDate->format('Y-m-d');
        $tourneeStart = new \DateTime($tourneStartDate.' '. $tourneStartHour);
        $tourneeStart = $tourneeStart->format('Y-m-d H:i:s');
        $receptionHour = $em->getRepository('AmsDistributionBundle:CrmDetail')->getHourReception($crmDetail->getTournee()->getId(),$crmDetail->getDepot()->getId() );
        $motifReclamId = $crmDetail->getCrmDemande()->getId();
        if( !empty($receptionHour) && ($tourneeStart >= $receptionHour) && $motifReclamId == 102){//code motif retard de livraison
            $alowwArbitrage = false;
        }else{
            $alowwArbitrage = true;
        }

        return $alowwArbitrage;
    }

      /**
     * [createRemInfoAction création d'une remonté d'info]
     * @return [type] [description]
     */
    public function createRemInfoAction(){
      
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $session  = $this->get("session");
        $queryParameters = $request->query;
        $crmDeailId  = $queryParameters->get('crmId');
        $all  = $queryParameters->get('all');
        $crmDetail = $em->getRepository('AmsDistributionBundle:CrmDetail')->findOneById($crmDeailId );
        $categorieId = 2;
        $form = $this->createForm(new CrmDetailType($categorieId), $crmDetail);
        if($request->getMethod() == 'POST'){
            $dataEntity = array();
            $dataInform  =  $request->request->get('ams_distributionbundle_crmdetail');
            
         
            $dataInform['abonneSocId'] = $request->request->get('abonneSocId');  
            $action = $request->request->get('action');
        
            $tourneeJour = $request->request->get('modele_tourneejour_id');
        
           // $adresseId = $request->request->get('adresseId');
            $dataInform['crmId']       = $request->request->get('crmId');
            $dataEntity['commune']     = $em->getRepository('AmsAdresseBundle:Commune')->findOneById($dataInform['communeId']);
            $dataEntity['client']      = $em->getRepository('AmsAbonneBundle:AbonneSoc')->findOneById($dataInform['abonneSocId']);
            $dataEntity['societe']     = $em->getRepository('AmsProduitBundle:Societe')->findOneById($dataInform['societeId']);
            $dataEntity['crmDemande']  = $em->getRepository('AmsDistributionBundle:crmDemande')->findOneById($dataInform['crmDemande'] ); 
            $dataEntity['depot']       =   $em->getRepository('AmsSilogBundle:Depot')->findOneById($dataInform['depotId']); 
            $dataEntity['tourneeJour']       =   $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findOneById($tourneeJour); 
            //$dataEntity['adresse']     = $em->getRepository('AmsAdresseBundle:Adresse')->findOneById($dataEntity['client']->getId());  
            ///$dataEntity['adresse']     = $em->getRepository('AmsAdresseBundle:Adresse')->findOneById($adresseId);  
            $userId                    = $this->get('session')->get('UTILISATEUR_ID');
            $dataEntity['user']        = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($userId);

            $this->saveCrmDeatil($action, $dataInform, 2,$dataEntity);  
            if( $action  == 'create'){
                return $this->redirect($this->generateUrl('crm_serach_client_to_create_rem_info'));
            }
            
            return $this->redirect($this->generateUrl('crm_display_detail', array(  'sId'=>$dataInform['societeId'],
                                                                                    'cId'=>$categorieId,
                                                                                    'dpId'=>$dataInform['depotId'],
                                                                                    'wRes'=>0,
                                                                                    'crmId' => $dataInform['crmId'] ,
                                                                                    'all'=> $all,
                                                                                    )));

        }
        
        if(empty($crmDetail)){
            //création
            $adrId = $queryParameters->get('adr');
            $socId = $queryParameters->get('edit');
            $depotId = $queryParameters->get('dep');   
            $data = $this->getAbonneInfos($adrId, $socId, $depotId);           
            $data['action'] = "create";
            $data['startDate'] =  $data['endDate']  = new \DateTime();   
          
     

            
        }else{   
            //edition d'une remontée dinfo
            $data = $this->getCrmInfosFormDb($crmDetail);
            $data['action'] = "update";
            $data['dateMin']     = $session->get('crmDateMin');
            $data['dateMax']     = $session->get('crmDateMax');  
            $depotId = $queryParameters->get('dpId');  
            $data['startDate'] =  new \DateTime($data['dateMin']);
        }

        $data['allCrmByClient'] = $em->getRepository('AmsDistributionBundle:CrmDetail')->findBy(array('abonneSoc'=>$data['abonneSocId']),array('dateCreat' => 'DESC'));
        $data['datesDistribution'] = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getDistribDateByAbonneSoc($data['abonneSocId']);
        $data['form'] = $form->createView();
        $data['all'] = $request->get('all');
        $data['categorieId'] = $categorieId;
        $data['crmReperage'] = $em->getRepository('AmsDistributionBundle:Reperage')->findBy(array('abonneSoc'=>$data['abonneSocId']),array('dateDemar' => 'DESC'));
        $tournee = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneeByAbonneDepotDate($data['abonneSocId'],$depotId,  $data['startDate']->format('Y-m-d'));
        if(count($tournee) >0) {
           $data['tournee'] = $tournee[0];
          
        }

        return $this->render('AmsDistributionBundle:Crm:create_rem_info.html.twig', $data );
    }

    /**
     * [getAbonneInfos description]
     * @param  [type] $adrId   [description]
     * @param  [type] $socId   [description]
     * @param  [type] $depotId [description]
     * @return [type]          [description]
     */
    private function getAbonneInfos($adrId, $socId, $depotId){
        
        $em = $this->getDoctrine()->getManager();
        $data['curretCrmId'] ="";
        $abonneAdr             =   $em->getRepository('AmsAdresseBundle:Adresse')->findOneById($adrId);
        $abonneSociete         =   $em->getRepository('AmsProduitBundle:Societe')->findOneById($socId);
        $data['numaboExt']     =    $abonneAdr->getAbonneSoc()->getNumAboExt();
        $data['abonneSocId']   =    $abonneAdr->getAbonneSoc()->getId();
        $data['adr']           =   $abonneAdr->getVol4();
        $data['name']          =   $abonneAdr->getVol1();
        $data['compAdr']       =   $abonneAdr->getVol3();
        $data['lDit']          =   $abonneAdr->getVol5();
        $data['cm']            =   (string)$abonneAdr->getCommune()->getId();
        $data['cp']            =   $abonneAdr->getCp();
        $data['edit']          =   $abonneSociete->getId();
        $data['cplnom']        =   $abonneAdr->getVol2();
        $data['depo']          =   $depotId ;
        $data['ville']        =   $abonneAdr->getVille();
        return $data;
    }

    /**
     * [getCrmInfosFormDb description]
     * @param  [type] $crmDetail [description]
     * @return [type]            [description]
     */
    private function getCrmInfosFormDb($crmDetail){
        
        $data['depo']        = (string)$crmDetail->getDepot()->getId();
        $data['edit']        = (string)$crmDetail->getSociete()->getId();
        $data['cm']          = (string)$crmDetail->getCommune()->getId();
        $data['adr']         = $crmDetail->getVol4();
        $data['startDate']   = $crmDetail->getDateDebut();
        $data['endDate']     =   $crmDetail->getDateFin(); 
        $data['curretCrmId'] =  $crmDetail->getId();
        $data['numaboExt']   = $crmDetail->getAbonneSoc()->getNumaboExt();
        $data['abonneSocId']   = $crmDetail->getAbonneSoc()->getId();

        return $data;
    }

  
    /**
     * [searchClientToCreateRemAction recherche d'abonnés pour créer une remonté d'info]
     * @return [type] [description]
     */
    public function searchClientToCreateRemAction(){
        $request = $this->getRequest(); 
        $em = $this->getDoctrine()->getManager();
        $depots      = $this->get('session')->get('DEPOTS');
        $clientAServirLogistRepository =   $em->getRepository('AmsDistributionBundle:ClientAServirLogist');

        $form        = $this->createFormBuilder()
                            ->add('societe','entity',array(
                                            'class'=>'AmsProduitBundle:Societe',
                                            'property'=>'libelle',
                                            'label'=>'Editeur ',
                                            'required'=>true,
                                            'query_builder' => function(EntityRepository $er) {
                                            return $er->qbSocietyActive();
                                            }
                                         ))
                            ->add('numaboExt', 'text', array('required'=>false, 'label'=>'N°Client'))
                            ->add('name', 'text', array('required'=>false, 'label'=>'Nom'))
                            ->add('commune', 'text', array('required'=>false, 'label'=>'Commune'))
                            ->add('depot', 'choice', array('choices'=>$depots,'required'=>false, 'label'=>'Dépôt','empty_value'=>'Choisissez...'))
                            ->add('tournee', 'choice', array('choices'=>array(),'required'=>false, 'label'=>'Tournée','empty_value'=>'Choisissez un depot...'))
                            ->getForm();

        if($request->isXmlHttpRequest())
        {
            $result ='';
            $societeId = $request->get('societeId');
            $numaboExt = $request->get('numaboExt');
            $name = $request->get('name');
            $commune = $request->get('commune');
            $depot = $request->get('depot');
            $tourneeId = $request->get('tourneeId');
            $result = $clientAServirLogistRepository->serachAbonnee($societeId, $numaboExt, $name, $commune, $depot, $tourneeId);
            
            $return = array('result' => $result);
            $return['responseCode']=200; 

            return new Response(json_encode($return),$return['responseCode'], array('Content-Type'=>'application/json'));
        }

        return $this->render('AmsDistributionBundle:Crm:search_client_rem_info.html.twig', array('form'=>$form->createView()));
    }
    
     /**
     * [ createArbitrage création/enregistrement/mise a jour d'une demande d'arbitrage
     */
    
    public function createArbitrageAction(){
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        if ($request->request->get('motifId') != '') {
            if($request->isXmlHttpRequest())
            {

                $motifId = $request->get('motifId');
                $crmId = $request->get('crmId');
                $userId = $this->get('session')->get('UTILISATEUR_ID');

                $dateDemandeArbitrage = new \DateTime();
                $crmDetail            = $crmDetailRepository = $em->getRepository('AmsDistributionBundle:CrmDetail')->findOneById($crmId);
                $motif                =  $em->getRepository('AmsDistributionBundle:DemandeArbitrage')->findOneById($motifId);
                $aribtrageUser        = $em->getRepository('AmsSilogBundle:Utilisateur')->findOneById($userId );
                $imputation        = $em->getRepository('AmsDistributionBundle:imputationService')->findOneById(1);
                $crmDetail->setMotif($motif);
                $crmDetail->setImputation($imputation);
                $crmDetail->setUtlDemandeArbitrage($aribtrageUser);
                $crmDetail->setDateDemandeArbitrage($dateDemandeArbitrage);
                $em->flush();
                $utlArbitrage = $aribtrageUser->getNom().' '.$aribtrageUser->getPrenom();
                $return = array('result' => 'succsses','utlArbitrage'=>$utlArbitrage,'responseCode'=>200);

                return new Response(json_encode($return),$return['responseCode'], array('Content-Type'=>'application/json'));

            }
        }
        else{
            $return = array('result' => 'error','responseCode'=>400);
            return new Response(json_encode($return),$return['responseCode'], array('Content-Type'=>'application/json'));
        }
    }

 
    /**
     * [saveImputationPaieAction description]
     * @return [type] [description]
     */
    public function saveImputationPaieAction(){
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        if($request->isXmlHttpRequest()){
                                                                                                    
            $dataInform =  $request->request->get('ams_distributionbundle_crmdetail'); 
            $userId = $this->get('session')->get('UTILISATEUR_ID');       
            $crmId                  = $request->get('crmId'); 
            $dateMin                = $request->get('dateMin'); 
            $dateMax                = $request->get('dateMax');
            $cmtImputation          = $request->get('cmtImputationPaie'); 
            $isImputable            = $request->get('isImputable'); 
            $abonneSocId            = $request->get('abonneSocId'); 
            $dateImputation = $this->transformDateToDataBaseFormat($request->get('dateImputation'), '-');
            $crmDetail = $crmDetailRepository = $em->getRepository('AmsDistributionBundle:CrmDetail')->findOneById($crmId);
            if( $isImputable == 1){
                $result = $this->checkTournee($dateImputation, $abonneSocId,  $crmId, $crmDetail->getDepot()->getId() );
                if($result['error'] == true){
                    $result['responseCode']=200; 
                    return new Response(json_encode($result),$result['responseCode'], array('Content-Type'=>'application/json'));
                }else{
                    $result['responseCode']=200; 
                  
                    $dateImputation       = new \DateTime( $dateImputation);
                    $crmDetail->setDateImputationPaie($dateImputation);
                    $tourneeInfo = $result['info'];
                    $paieTournee =  $em->getRepository('AmsPaieBundle:PaiTournee')->findOneById($tourneeInfo['id']);
                    $crmDetail->setTournee($paieTournee);
                }
            }else{
                 $result = array('error'=>false,'info' => "noImputable");
            }
           
            $crmDetail->setImputationPaie($isImputable);
            $crmDetail->setcmtImputationPaie($cmtImputation);
            $em->flush();
            $result['responseCode']=200;
            return new Response(json_encode($result),$result['responseCode'], array('Content-Type'=>'application/json'));
        
        }
    }
    
    /**
     * [checkTournee description]
     * @param  [type] $dateImputation [description]
     * @param  [type] $abonneSocId    [description]
     * @param  [type] $crmDetailId    [description]
     * @return [type]                 [description]
     */
    public function checkTournee($dateImputation, $abonneSocId, $crmDetailId, $depId){
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $livraisonExist =  $em->getRepository('AmsDistributionBundle:CrmDetail')->isExistDitrib($abonneSocId, $dateImputation);
        if(empty(  $livraisonExist)){
            $return = array('error'=>true,'info' => "noDistrib");
        }else{
            $tourneeInfo = $em->getRepository('AmsDistributionBundle:CrmDetail')->getPaieTourneeId($crmDetailId, $dateImputation, $abonneSocId, $depId);
            $return = empty($tourneeInfo) ?  array('error'=>true,'info' => "noTournee"): array('error'=>false,'info' =>$tourneeInfo) ;
        }
         
       return $return;
    }
       
    /**
     * [getTourneeByDepotAction description]
     * @return [type] [description]
     */
    public function getTourneeByDepotAction(){

        $request = $this->getRequest(); 
        $em = $this->getDoctrine()->getManager();
        $tourneeList = array();
        if($request->isXmlHttpRequest())
        {
            $depotId = $request->get('depotId');
            $socId    = $request->get('socId');
            $startDate    = $request->get('startDate');
            $endDate    = $request->get('endDate');
            $rechType   = $request->get('rechType');
            $filtre   = $request->get('filter');
            if(!empty($startDate ))
                $startDate  = $this->transformDateToDataBaseFormat( $startDate , '/', '-');
            if(!empty($endDate ))
                $endDate  = $this->transformDateToDataBaseFormat( $endDate , '/', '-');
        
            if(!empty($depotId)){
                if(empty($rechType)){
                    $tourneeList = $em->getRepository('AmsDistributionBundle:CrmDetail')->getTourneeIdByDepotDate($depotId,$startDate,$endDate);
                }else{
                    //tournee sur le formulaire de recherche des cleints
                    $tourneeList = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneeByDepot($depotId);
                }
                $tourneeList = $this->transformArraysOnSingleArray($tourneeList);
            }
            $return = array('result' => $tourneeList);
            $return['responseCode']=200; 

        return new Response(json_encode($return),$return['responseCode'], array('Content-Type'=>'application/json'));
        }
    }
   
    /**
     * [transformArraysOnSingleArray description]
     * @param  [type] $arrayOfArray
     * @return [type]
     */
    private function transformArraysOnSingleArray($arrayOfArray){
        $singleArray = array();
        foreach($arrayOfArray as $key => $array)
        {
            $newKey = $array['id'];
            $newVal = $array['code'];
            $singleArray[$newKey] = $newVal;
        }

        return $singleArray;
    }
    
    /**
     * [getDateDistributionByClient description]
     * @param  [type] $numAbo
     * @return [type]
     */
    private function getDateDistributionByClient($numAbo){

        $em = $this->getDoctrine()->getManager();
        $maxResult = 15;
        $dateDistribution = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getDistibutionsDate($numAbo, $maxResult); 
     
        return $dateDistribution; 
    }

    /**
     * [regroupReclamationCountBySocieteAndDepot regroupe dans un tableau le nombre de reclamation/rementé d'info 
     *  qui a comme clés l'ids de  societe et comme valeur un tableau dont les clés sont les ids des dépots et les valeurs  le nombre de reclamation]
     * @param  [array] $reclamations  [description]
     * @param  [string] $arg          [description]
     * @return [array]                [description]
     */
    private function regroupReclamationCountBySocieteAndDepot($reclamations, $arg ){
        $result = array();$depoIds = array(); 
        foreach ($reclamations as $key => $value) {
            if(!array_key_exists($value[0]->getSociete()->getId(), $result)){
                $depoIds = array();
            }

            $depoIds[$value[0]->getDepot()->getId()] = $value[$arg];
            $result[$value[0]->getSociete()->getId()][$arg] = $depoIds;
        }
    
        return $result;
    }
    
    /**
     * [transformDateToDataBaseFormat transforme la date au format définit en base de données
     * @param  [string] $date  [valeur de date]
     * @return [string]        [valeur de date edans le format définit en base de données]
     */
    private function transformDateToDataBaseFormat($date, $delim, $delim2=null){
        $delim1 = empty($delim2) ?  $delim : $delim2;
        $dateItems = explode($delim, $date);
   
        return   $dateItems[2].$delim1.$dateItems[1].$delim1.$dateItems[0];
    }
    
    
    /**
     * [saveCrmDeatil description]
     * @param  [type] $action      [description]
     * @param  [type] $data        [description]
     * @param  [type] $categorieId [description]
     * @param  [type] $dataEntity  [description]
     * @return [type]              [description]
     */
    private function saveCrmDeatil($action, $data, $categorieId, $dataEntity){
        $em = $this->getDoctrine()->getManager();
        if($action == "create"){
            $crmDetail = new CrmDetail();
        }else{
            $crmDetail   =  $em->getRepository('AmsDistributionBundle:CrmDetail')->findOneById($data['crmId']);
        }
        
 
        if($categorieId == 2 ){
            $dateCreat   = isset($data['dateCreat']) ? new \DateTime(str_replace('/','-',$data['dateCreat'])): new \datetime();
            $dateDebut   = new \DateTime(str_replace('/','-',$data['dateDebut']));
            $dateFin     = new \DateTime(str_replace('/','-',$data['dateFin']));
        
            $crmDetail->setUtlSaisie($dataEntity['user']);
            $crmDetail->setVol1($data['vol1']);
            $crmDetail->setVol2($data['vol2']);
            $crmDetail->setVol3($data['vol3']); 
            $crmDetail->setVol4($data['vol4']);
            $crmDetail->setVol5($data['vol5']);
            $crmDetail->setDateDebut($dateDebut);
            $crmDetail->setDateFin($dateFin);
            $crmDetail->setCp($data['cp']);
            $crmDetail->setCommune($dataEntity['commune']);
            $crmDetail->setAbonneSoc($dataEntity['client']);
            $crmDetail->setCmtDemande($data['cmtDemande'] );
            $crmDetail->setDateCreat($dateCreat);
            $crmDetail->setNumAboExt($data['numaboExt']);
           // $ville = (!empty($dataEntity['adresse'])) ? $dataEntity['adresse']->getVille() : "ville non renseigner dans la table adresse";
            $crmDetail->setVille($data['ville']);
            $crmDetail->setSocCodeExt($dataEntity['client']->getSocCodeExt());
            $crmDetail->setClientType(0);
            $crmDetail->setCodeDemande($data['numaboExt']);//a confirmer avec andry
            $crmDetail->setSociete($dataEntity['societe']);
            $crmDetail->setCrmDemande($dataEntity['crmDemande']);
            $crmDetail->setDepot($dataEntity['depot']);
            $crmDetail->setTourneeJour($dataEntity['tourneeJour']);
        }else{
            if(!empty($data['crmReponse'])){
                $crmReponse = $em->getRepository('AmsDistributionBundle:CrmReponse')->findOneById($data['crmReponse']);  
                $crmDetail->setCrmReponse($crmReponse);
            }
           
            $crmResponseDate = new \DateTime();
            $crmDetail->setUtlReponse($dataEntity['user']);
            $crmDetail->setDateReponse($crmResponseDate);  
            $crmDetail->setCmtReponse(trim($data['cmtResponse']));
        }   
     

        $em->persist($crmDetail);
        $em->flush();
        return true;

    }

    /**
     * [getAllDataToDisplayInXmlGrid exécute des requete en base pour récupérer le nombre de reclamations/rementé d'info..etc
     * @param  [string] $date  [valeur de date min]
     * @param  [string] $date  [valeur de date max]
     * @return [array]  $data  [tableau qui contien des tableau des données]
     */
    public function getAllDataToDisplayInXmlGrid($dateMin, $dateMax){
        //1:rcelamation, 2:remontéé dinformation, 3:demande client
        $em = $this->getDoctrine()->getManager();
        $crmDetailRepository = $em->getRepository('AmsDistributionBundle:CrmDetail');
        //calcule le nombre de reclamaton/remonté info 
        $reclamations                   = $crmDetailRepository->getCountRecBySocieteAndDepot(1,'countRec',$dateMin,  $dateMax);
        $backUpInformation              = $crmDetailRepository->getCountRecBySocieteAndDepot(2,'countRem',$dateMin,  $dateMax);
        $demClint              = $crmDetailRepository->getCountRecBySocieteAndDepot(3,'countDemClient',$dateMin,  $dateMax);
        //calcule le nombre de réponse au  reclamaton/remonté info 
        $reclamationsResponse           = $crmDetailRepository->getCountRecResponseBySocieteAndDepot(1,'countRecRes',$dateMin,  $dateMax);
        $backUpInformationResponse      = $crmDetailRepository->getCountRecResponseBySocieteAndDepot(2,'countRemRes',$dateMin,  $dateMax);
        $demandeClientResponse          = $crmDetailRepository->getCountRecResponseBySocieteAndDepot(3,'countDemClientRes',$dateMin,  $dateMax);
        //calcule le nombre totale reclam/remonté info 
        $totalBackUpInformation         = $crmDetailRepository->getTotalRecByDepot(2,'countRem',$dateMin,  $dateMax);
        $totalreclamations              = $crmDetailRepository->getTotalRecByDepot(1,'countRec',$dateMin,  $dateMax);
        $totalDemClient                 = $crmDetailRepository->getTotalRecByDepot(3,'countDemClient',$dateMin,  $dateMax);
        //calcule le nombre totale des réponse au reclam/remonté info 
        $totalBackUpInformationResponse = $crmDetailRepository->getTotalRecResponseByDepot(2,'countRemResponse',$dateMin,  $dateMax);
        $totalreclamationsResponse      = $crmDetailRepository->getTotalRecResponseByDepot(1,'countRecResponse',$dateMin,  $dateMax);
        $totalDemClientResponse         = $crmDetailRepository->getTotalRecResponseByDepot(3,'countDemClientResponse',$dateMin,  $dateMax);
        $countRecBySociete              = $this->regroupReclamationCountBySocieteAndDepot($reclamations, 'countRec',$dateMin,  $dateMax);
        $countRemBySociete              = $this->regroupReclamationCountBySocieteAndDepot($backUpInformation, 'countRem',$dateMin,  $dateMax);
       
        $countRecResBySociete           = $this->regroupReclamationCountBySocieteAndDepot($reclamationsResponse, 'countRecRes',$dateMin,  $dateMax);
        $countRemResBySociete           = $this->regroupReclamationCountBySocieteAndDepot($backUpInformationResponse, 'countRemRes',$dateMin,  $dateMax);
       
        return array(
                        'reclamations' => $reclamations,
                        'countRecBySociete'=>$countRecBySociete,
                        'countRemBySociete'=>$countRemBySociete,
                        'countRecResBySociete'=>$countRecResBySociete,
                        'countRemResBySociete'=>$countRemResBySociete,
                        'allreclamations'=>$totalreclamations,
                        'allrminfo'=>$totalBackUpInformation,
                        'allDemandeCliants'=>  $totalDemClient,
                        'totalDemClientResponses' =>$totalDemClientResponse ,
                        'totalreclamationsResponse'=>$totalreclamationsResponse,
                        'totalBackUpInformationResponse'=>$totalBackUpInformationResponse,
                    );
    }
    
    /**
     * [reclamToPdfAction fonction qui gere l'export au format pdf des fiche de reclamation]
     * @return [type] [description]
     */
    public function reclamToPdfAction() 
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $session  = $this->get("session");
        $exportType  = "reclamation";
        // recuperation des informations necessaires a la creation du pdf
        $date           = $request->cookies->get('toDeliverDate');
        $dateMin        = $session->get('crmDateMin');
        $dateMax        = $session->get('crmDateMax');
        $depotId        = $request->get('dId');
        $societeId      = $request->get('sId');
        $categorieId    = $request->get('cId');
        $crmDemandeId   = $request->get('cDId');
        $isWithResponse = $request->get('rp');
        $tourneeId      = $request->get('tId');
        $fluxId      = $request->get('fId');
        $filtre         = $request->get('mode');
        $depotLibelle = $em->getRepository('AmsSilogBundle:Depot')->findOneById($depotId)->getLibelle();
        $dateInterval = $this->getDateIntervalToExecuteQuery($dateMin, $dateMax);
        if($categorieId == 1 ){
            $exportType  = "reclamation";
        }else if($categorieId == 2){
            $exportType  = "remontee_d_information";   
        }else{
            $exportType = "demande_client";
        }                                                                                                 
        $allCrmResponse = $em->getRepository('AmsDistributionBundle:CrmReponse')->findBy(array('crmCategorie'=>$categorieId ));
        $title = ucfirst(str_replace('_', ' ', $exportType));
        $aData = array(
                'flux_id' => $fluxId,
        );
        $listeReclam = $em->getRepository('AmsDistributionBundle:CrmDetail')->serachReclam($categorieId, $dateInterval['dateMin'],  $dateInterval['dateMax'], $societeId, $depotId, $isWithResponse, $crmDemandeId, $tourneeId, $aData);
        $chemin = $this->container->getParameter("IMG_ROOT_DIR_TO_PDF");
        
   
       $html = $this->renderView('AmsDistributionBundle:Crm:reclamation_pdf.html.twig', array(
                                                                                 'list' => $listeReclam,
                                                                                 'dateArendre' => $date,
                                                                                 'title'=> $title,
                                                                                 'allCrmResponse'=>$allCrmResponse,
                                                                                 'chemin' =>$chemin,
         ));
       
        $cleanDepot = str_replace(" ","-", $depotLibelle);
        $cleanDate = str_replace("/", "", $date);
        $name = $exportType."_" . $cleanDepot . "_" . $cleanDate . ".pdf";       
        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->writeHTML($html);
        $response = $html2pdf->Output($name, 'D');
        return new Response($response, 200, array('Content-Type' => 'Application/pdf'));
    }
    
    /**
     * Modification de tournée d'une réclamation
     */
    public function UpdateTourneeAction(Request $request){

        $this->getDoctrine()->getManager()
              ->getRepository('AmsDistributionBundle:CrmDetail')->updateCrmTourneeJourId($request->request->get('crmId'),$request->request->get('trnJourId') );
       
        return new Response(null, 200, array('Content-Type' => 'text/txt'));
    }

    /**
     * [getCrmDemandeListAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getCrmDemandeListAction(Request $request){

        $listeDemande = $this->getDoctrine()->getManager()
              ->getRepository('AmsDistributionBundle:CrmDetail')->getCrmDemandeListe($request->request->get('crmCategorieId'));
       
         $return = array('result' => $listeDemande);

         return new Response(json_encode($return) , 200, array('Content-Type' => 'text/txt'));
    }

    /**
     * [exportVisualisationPageToExcelAction description]
     * @return [type] [description]
     */
    public function exportVisualisationPageToExcelAction(){
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {   $em = $this->getDoctrine()->getManager();
            $session= $this->get('session');
            $dateMin   = $session->get('crmDateMin');
            $dateMax   = $session->get('crmDateMax');
            $parameters = $request->get('parameters');
            $excelHeader = $request->get('excelHeader');
            extract($parameters);
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
            $dateInterval = $this->getDateIntervalToExecuteQuery($dateMin, $dateMax);
            $aData = array(
                'flux_id' => $fluxId,
            );
            $result = $em->getRepository('AmsDistributionBundle:CrmDetail')->serachReclam($categorieId,$dateInterval['dateMin'],  $dateInterval['dateMax'],$societeId, $depotId,$isWithResponse, $crmDemandeId, $tourneeId, $aData);
         
            $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneById($depotId);
            $societe = $em->getRepository('AmsProduitBundle:Societe')->findOneById($societeId);
            $societeLibelle = !empty($societe) ? $societe->getLibelle() : "Toutes(s)";
            $demande = $em->getRepository('AmsDistributionBundle:CrmDemande')->findOneById($crmDemandeId);
            $demandeLibelle = !empty($demande) ? $demande->getLibelle() : "Toutes(s)";
            $categorie = $em->getRepository('AmsDistributionBundle:CrmCategorie')->findOneById($categorieId);
            $categorieLibelle = !empty($categorie) ? $categorie->getLibelle() : "Toutes(s)";
            $tournee = $em->getRepository('AmsPaieBundle:PaiTournee')->findOneById($tourneeId);
            $tourneeCode = !empty($tournee) ? $tournee->getCode() : "Toutes(s)";
         
            if(!empty($result)){
                $phpExcelObject->getProperties()->setCreator("Mroad")->setDescription("Visualisation CRM.");
                $phpExcelObject->getDefaultStyle()->getFont()->setName('Arial');
                $phpExcelObject->getDefaultStyle()->getFont()->setSize(8);
                $phpExcelObject->getActiveSheet()->getDefaultColumnDimension()->setWidth(5);
                $phpExcelObject->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $phpExcelObject->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $phpExcelObject->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $phpExcelObject->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $phpExcelObject->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $phpExcelObject->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $phpExcelObject->getActiveSheet()->getColumnDimension('G')->setWidth(40);
                $phpExcelObject->getActiveSheet()->getStyle('G')->getAlignment()->setWrapText(true);
                $phpExcelObject->getActiveSheet()->getColumnDimension('H')->setWidth(40);
                $phpExcelObject->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $phpExcelObject->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $phpExcelObject->getActiveSheet()->getStyle('J')->getAlignment()->setWrapText(true);
                $phpExcelObject->getActiveSheet()->getColumnDimension('K')->setWidth(20);
                $phpExcelObject->getActiveSheet()->getColumnDimension('L')->setWidth(40);
                $phpExcelObject->getActiveSheet()->getStyle('L')->getAlignment()->setWrapText(true);
                $phpExcelObject->getActiveSheet()->getColumnDimension('M')->setWidth(40);
                $phpExcelObject->getActiveSheet()->getStyle('M')->getAlignment()->setWrapText(true);
                $phpExcelObject->getActiveSheet()->getColumnDimension('N')->setWidth(40);
                $phpExcelObject->getActiveSheet()->getStyle('N')->getAlignment()->setWrapText(true);
                $phpExcelObject->getActiveSheet()->getColumnDimension('O')->setWidth(40);
                $phpExcelObject->getActiveSheet()->getStyle('O')->getAlignment()->setWrapText(true);
                $phpExcelObject->getActiveSheet()->getColumnDimension('P')->setWidth(40);
                $phpExcelObject->getActiveSheet()->getStyle('P')->getAlignment()->setWrapText(true);
           
                $styleEventRows = $this->getStyle('styleEventRows');
                $styleOddRows = $this->getStyle('styleOddRows');
                $styleCell = $this->getStyle('styleCell');
                $styleHeader = $this->getStyle('styleHeader');
                $styleArray = $this->getStyle('styleArray');
                $i=0;$j=11;
                $phpExcelObject->getActiveSheet()->getStyle("A10:P10")->applyFromArray($styleHeader);
                $phpExcelObject->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $phpExcelObject->getActiveSheet()->getStyle("A10:P10")->applyFromArray($styleArray);
                $phpExcelObject->getActiveSheet()->getStyle("A1:A7")->applyFromArray($styleCell);
                $depotLibelle = ($depot)? $depot->getLibelle() : 'Tous les dépôts' ;
                $phpExcelObject->setActiveSheetIndex(0)->mergeCells("A1:E1")
                                                       ->setCellValue("A1", 'Catégorie : '.$categorieLibelle)
                                                       ->mergeCells("A2:E2")
                                                       ->setCellValue("A2", 'Dépot : '.$depotLibelle)
                                                       ->mergeCells("A3:E3")
                                                       ->setCellValue("A3", 'Date du : '.$this->transformDateToDataBaseFormat($dateMin, '-', '/').' au '.$this->transformDateToDataBaseFormat($dateMax, '-', '/'))
                                                       ->mergeCells("A4:E4")
                                                       ->setCellValue("A4", 'Société :'.$societeLibelle)
                                                       ->mergeCells("A5:E5")
                                                       ->setCellValue("A5", 'Demande :'.$demandeLibelle)
                                                       ->mergeCells("A6:E6")
                                                       ->setCellValue("A6", 'Avec réponse : '.$isWithResponseLabel)
                                                       ->mergeCells("A7:E7")
                                                      ->setCellValue("A7", 'Tournée: '.$tourneeCode);
                   
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue("A10", trim($excelHeader[0]))
                                                        ->setCellValue("B10", trim($excelHeader[1]))
                                                        ->setCellValue("C10", trim($excelHeader[2]))                                  
                                                        ->setCellValue("D10", trim($excelHeader[3]))
                                                        ->setCellValue("E10",trim($excelHeader[4]))
                                                        ->setCellValue("F10", trim($excelHeader[5]))
                                                        ->setCellValue("G10", trim($excelHeader[6]))
                                                        ->setCellValue("H10", trim($excelHeader[7]))
                                                        ->setCellValue("I10", 'Code postal')
                                                        ->setCellValue("J10", 'Ville')
                                                        ->setCellValue("K10", trim($excelHeader[8]))
                                                        ->setCellValue("L10", trim($excelHeader[9]))
                                                        ->setCellValue("M10", trim($excelHeader[10]))
                                                        ->setCellValue("N10", trim($excelHeader[11]))
                                                        ->setCellValue("O10", trim($excelHeader[12]))    
                                                        ->setCellValue("P10", 'Centre de distribution');    

                                                     
                foreach ($result  as $key => $value) { 
                    $value['date_creat'] = $this->transformDateToDataBaseFormat(substr($value['date_creat'],0, 10), '-', '/');
                    $value['date_debut'] = !empty($value['date_debut']) ? $this->transformDateToDataBaseFormat($value['date_debut'],'-', '/') :  $value['date_debut']; 
                    $value['date_fin']   = !empty($value['date_fin']) ? $this->transformDateToDataBaseFormat($value['date_fin'],'-', '/') :  $value['date_fin']; 

                    $phpExcelObject->setActiveSheetIndex(0)      
                    ->setCellValue("A$j", trim($value['crm_id']))
                    ->setCellValue("B$j", $value['date_creat'])
                    ->setCellValue("C$j", $value['date_debut'])
                    ->setCellValue("D$j", $value['date_fin'])
//                    ->setCellValue("E$j", trim($value['numabo_ext']))
                    ->setCellValue("F$j", trim($value['vol1'].' '.$value['vol2']))
                    ->setCellValue("G$j", trim($value['societe_libelle'])) 
                    ->setCellValue("H$j", trim($value['vol4']).' '.$value['vol3']) 
                    ->setCellValue("I$j", trim($value['cp'])) 
                    ->setCellValue("J$j", trim($value['ville']))
                    ->setCellValue("K$j", trim($value['tournee_code'])) 
                    ->setCellValue("L$j", trim($value['demande_libelle']))
                    ->setCellValue("M$j", trim($value['cmt_demande']))
                    ->setCellValue("N$j", trim($value['response_libelle']))
                    ->setCellValue("O$j", trim($value['cmt_response']))
                    ->setCellValue("P$j", $depotLibelle);
                    $phpExcelObject->getActiveSheet()->getRowDimension("$j")->setRowHeight(-1);
                    $phpExcelObject->getActiveSheet()->getStyle("A$j:P$j")->applyFromArray($styleArray);
                    $phpExcelObject->getActiveSheet()->setCellValueExplicit("E$j",trim($value['numabo_ext']),PHPExcel_Cell_DataType::TYPE_STRING);
                    $phpExcelObject->getActiveSheet()->getStyle("A$j:P$j")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    if(($key+1)%2 == 0){
                        $phpExcelObject->getActiveSheet()->getStyle("A$j:P$j")->applyFromArray($styleOddRows);
                    }else{
                        $phpExcelObject->getActiveSheet()->getStyle("A$j:P$j")->applyFromArray($styleEventRows);
                    }
                    $j++;
                }
            }
  
            $phpExcelObject->getActiveSheet()->setTitle('Visualisation_CRM');
            $phpExcelObject->setActiveSheetIndex(0);
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
            $writer->save('tmp/Visualisation_CRM.xls');
            return new Response('ok');//die();

               
        }
    }
    
    /**
     * [expAction export excel en ajax pour éviter de recharger la page]
     * @return [type] [description]
     */
    public function expAction()
    { 
        return new Response(file_get_contents('tmp/Visualisation_CRM.xls'), 200, array(
//                'Content-Encoding'=> 'charset=iso-8859-1', //deprecated plus supporte par chrome
            'Content-Encoding'=> 'zlib','deflate',
            'Content-Type' => 'application/force-download',
                'Content-Disposition' => 'attachment; filename="Visualisation_CRM'.'.xls"'
            )); 

    }
    
    /**
     * [getStyle description]
     * @param  [type] $item [description]
     * @return [type]       [description]
     */
    public function getStyle($item){
       
        if($item == 'styleEventRows')
            return  array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                              'color' => array('argb' => 'FFE3EFFF'),
                              'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,),
                            ));
        if($item == 'styleOddRows')
            return  array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('argb' => 'FFFFFFFF'),
                        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,),
                ));

        if($item == 'styleArray')
            return array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN ,),),);
        if($item == 'styleHeader')
            return  array('font' => array('bold' => true,'size' => 10),
                          'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
                           'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('argb' => 'FFE1E6FA'),),
                          );
        if($item == 'styleCell')    
            return  array('font' => array('bold' => false,'size' => 13,),
                          'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('argb' => 'FFC4D7ED'),),);
                    
    }
    
    
    
    /**  La tournee de l'abonne pour une date un depot */
      public function getTourneeByAbonneDepotDateAction(){

        $request = $this->getRequest(); 
        $em = $this->getDoctrine()->getManager();
        $tournee = array();
        if($request->isXmlHttpRequest())
        {
            $depotId = $request->get('depot_id');
            $debut    = $request->get('debut');
            $abonneId    = $request->get('abonne_id');
            $debut  = $this->transformDateToDataBaseFormat( $debut , '/', '-');
            $tournee = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneeByAbonneDepotDate($abonneId,$depotId, $debut);
            return new Response(json_encode($tournee) , 200, array('Content-Type'=>'application/json'));
        }
      
      
    }
    
    
}