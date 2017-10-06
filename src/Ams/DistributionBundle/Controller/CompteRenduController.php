<?php

namespace Ams\DistributionBundle\Controller;

use Ams\ModeleBundle\Controller\DhtmlxController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Ams\DistributionBundle\Form\FiltreOuvertureType;
use Ams\DistributionBundle\Form\CptrReceptionType;
use Ams\DistributionBundle\Entity\CptrReception;
use Ams\DistributionBundle\Form\FiltreReceptionDepotType;
use Ams\DistributionBundle\Repository\ClientAServirLogistRepository;
use Ams\DistributionBundle\Repository\CptrDistributionRepository;
use Ams\DistributionBundle\Repository\CptrDetailExNonDistribRepository;
use Ams\ModeleBundle\Entity\GroupeTournee;
use Ams\ModeleBundle\Entity\ModeleTourneeJour;
use Ams\DistributionBundle\Entity\CptrDistribution;
use Ams\DistributionBundle\Entity\CptrDetailExNonDistrib;
use Doctrine\ORM\EntityRepository;
/**
 * Description of CompreRenduController
 *
 * @author maximiliendromard
 */
class CompteRenduController extends DhtmlxController
{
    /**
     * [ReceptionDepotAction description]
     */
    public function ReceptionDepotAction()
    {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->getDoctrine()->getManager();
        $session  = $this->get("session");
        $affichage_date = $session->get('affichage_date');
        // Les depots auquels l'utilisateur courant a droit
        $depots         = $session->get('DEPOTS'); 
        $request        = $this->getRequest();
        $succes         = $request->query->get('succes');
        $defaultDepot  = $request->query->get('depot');
        $oDepotPCO = $em->getRepository('AmsSilogBundle:Depot')->findOneByLibelle('PCO');
       // $affichage_date =  $request->query->get('affichage_date');
        $flux   = $request->query->get('flux');
        $succesDepot    = $em->getRepository('AmsSilogBundle:Depot')->findOneById($defaultDepot);
        $depot_ids      = array_keys($depots);
       // $defaultDepot   = $depot_ids[0];
        $liste_produit  = array();
        $request        = $this->getRequest();
        if(empty($affichage_date)){ 
            $affichage_date = new \DateTime('now');
            $affichage_date = $affichage_date->format('Y-m-d');
            $session->set('affichage_date', $affichage_date);
        }
        $fluxList = $em->getRepository('AmsDistributionBundle:CptrReception')->getAllProduitFlux();
        $fluxList = $this->transformArraysOnSingleArray($fluxList);
      
        $form = $this->createForm(new FiltreReceptionDepotType($depots, $fluxList)); 
        $isPosted = false; 
        $aProductNonRecuPco = array();
        if($request->getMethod() == 'POST'){
            $succes ="";
            $dataForm = $request->request->get('ams_distributionbundle_filtrereception');
            $affichage_date  = $dataForm['date'];
            $depot = $dataForm['depot'];
            $flux = $dataForm['flux'];//var_dump($affichage_date);die();
            $productsRecepInfo = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTableCompteRenduReception($affichage_date, $depot, $flux);
            $productNonRecuPco = $em->getRepository('AmsDistributionBundle:CptrReception')->getProductPcoNonRecu($affichage_date,$oDepotPCO->getId());
            foreach($productNonRecuPco as $data){
                $aProductNonRecuPco[] = $data['product_id'];
            }
            $defaultDepot = $depot;
            $isPosted = true; 
            $session->set('affichage_date', $affichage_date);  
        }

        if(!empty($succesDepot)){
        $alert = $this->renderView('::modalAlerte.html.twig', array('type' => 'success', 
                                                                'message' => '</strong>Vos modifications pour le dépot <b>'.$succesDepot->getLibelle().'</b> ont été bien enregistrées!</strong>'));
       // $defaultDepot = $succesDepot->getId();  
        }
        $defaultDepotLabel =  array_key_exists($defaultDepot, $depots) ? $depots[$defaultDepot] :'';

        return $this->render('AmsDistributionBundle:CompteRendu:ReceptionDepot.html.twig', array(
            'form' => $form->createView(),
            'affichage_date' => $affichage_date,
            'depot' => $depots,
            'flux' => !empty($flux) ? $flux: '',
            'productsRecepInfo'=>!empty($productsRecepInfo) ? $productsRecepInfo : "",
            'defaultDepot'=> $defaultDepot ,
            'isPosted'=>$isPosted,
            'alert'=> !empty($succes) ? $alert : "",
            'aProductNonRecuPco'=> $aProductNonRecuPco,
            'defaultDepotLabel'=>$defaultDepotLabel));    
    }
    
    /*
     * Fonction sauvegardant les données insérée dans le DhtmlxGrid de la page de réception des dépôts
     */
    public function saveAction() 
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $succes = false;
         if($request->getMethod() == 'POST'){
            $curentDate = new \DateTime('now');
           // $valiAction = $request->request->get('valid');
            $action = $request->request->get('valid');
            $dataForm = $request->request->all();
            $dateCptr= new \DateTime($dataForm['date']);
            $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneById($dataForm['depotId']);
          
            foreach ($dataForm as $key => $value) {
                $val = explode('_', $key);
                if($val[0] == 'qtePrevue'){ 
                    $productId = $val[1];
                    $timeReception = explode(':', $dataForm['heureReception_'.$productId.'_'.$val[2]]);
                    if(!empty($timeReception[1])){
                        $dateCptrReception = new \DateTime($dataForm['date']);
                        $dateCptrReception->setTime($timeReception[0], $timeReception[1]);
                    }else{
                        $dateCptrReception =null;
                    }
                    if(!empty($val[2])){                       
                      
                        $cptrReception = $em->getRepository('AmsDistributionBundle:CptrReception')->findOneById($val[2]);
                        $cptrReception->setDateCptRendu($dateCptr);
                        $cptrReception->setQteRecue($dataForm['qteRecue_'.$productId.'_'.$val[2]]);
                        $cptrReception->setHeureReception($dateCptrReception);
                        $cptrReception->setCommentaires($dataForm['comment_'.$productId.'_'.$val[2]]);
                        if($action == 'valid' && !is_null($dateCptrReception)) 
                          $cptrReception->setNonModifiable(true);
                    }else{

                        $cptrReception = new cptrReception();
                        $cptrReception->setQtePrevue($dataForm['qtePrevue_'.$productId.'_'.$val[2]]);
                        $cptrReception->setDepot($depot);
                        $cptrReception->setDateCptRendu($dateCptr);
                        $cptrReception->setQteRecue($dataForm['qteRecue_'.$productId.'_'.$val[2]]);
                        $cptrReception->setheureReception($dateCptrReception);
                        $cptrReception->setCommentaires($dataForm['comment_'.$productId.'_'.$val[2]]);
                        $product = $em->getRepository('AmsProduitBundle:Produit')->findOneById($val[1]); 
                        $cptrReception->setProduit($product);
                        $product = $em->getRepository('AmsProduitBundle:Produit')->findOneById($val[1]); 
                        $cptrReception->setProduit($product);

                        if(!empty($dataForm['mtj_'.$productId.'_'.$val[2]]))
                            $mTourneeJour= $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findOneById($dataForm['mtj_'.$productId.'_'.$val[2]]);
                        if(!empty($mTourneeJour)) 
                            $cptrReception->setTournee($mTourneeJour);
                        if(!empty($dataForm['gtournee_'.$productId.'_'.$val[2]]))
                            $gTournee = $em->getRepository('AmsModeleBundle:GroupeTournee')->findOneById($dataForm['gtournee_'.$productId.'_'.$val[2]]);  
                        if(!empty($gTournee))
                            $cptrReception->setGroupe($gTournee);
                        if($action == 'valid' && !is_null($dateCptrReception)) 
                          $cptrReception->setNonModifiable(true);
                        $em->persist($cptrReception);
                        $em->flush();
                    }                 
                }
            }
            
            $em->flush();
            $succes = true;

          return $this->redirect($this->generateUrl('comptes_rendus_reception',array('succes'=>$succes,'depot'=>$depot->getId(), 'flux' => $dataForm['flux'],'depot'=>$dataForm['depotId'])));
            
        }     
    }
   
    /*
     * Fonction d'affichage de la page de vue des comptes rendus de distribution
     */
    public function DistributionDepotAction()
    {
         $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        //Récupération des données envoyés dans le formulaire
        $request = $this->getRequest();
        $filtre = $request->request->get('ams_distributionbundle_filtreouverture');
        
        //Création du formulaire de filtrage
        $form = $this->createForm(new FiltreOuvertureType());
        
        //Affichage de la bonne date dans le filtre
        if ($filtre['filtre'] && $filtre['flux']) {
            $affichage_date = $filtre['filtre'];
            $flux = $filtre['flux'];
        } else {
            $affichage_date = new \DateTime('now');
            $affichage_date = $affichage_date->format('Y-m-d');
            $flux = 1;
        }
        //Affichage du twig avec le formulaire généré précedement + la date filtrée ou la date actuelle
        return $this->render('AmsDistributionBundle:CompteRendu:DistributionDepot.html.twig', array(
            'form' => $form->createView(),
            'affichage_date' => $affichage_date,
            'flux' => $flux,
                ));    
        
    }
    
    
    /**
     * [gridDistributionAction Création de la liste de distribution des dépots]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function gridDistributionAction($date,$flux) {
        $em = $this->getDoctrine()->getManager();
        $depots      = $this->get('session')->get('DEPOTS');
        $depot_ids   = array_keys($depots);
        $count_rec_view = $em->getRepository('AmsDistributionBundle:CptrDistribution')->getCountReclamationByDepotAndTournee($date);
        // on récupère la liste des adresse rejetés qui sont sur les dépôts de l'utilisateur connecté selon la date du filtre
        $liste = $em->getRepository('AmsDistributionBundle:CptrDistribution')->getTableCompteRenduDistribution($date,$depot_ids,$flux);
        //Affichage du twig avec la liste récupérée + la date filtrée
        $response = $this->renderView('AmsDistributionBundle:CompteRendu:grid_distribution_depot.xml.twig', array('liste' => $liste,'affichage_date' => $date,'flux' => $flux));

        return new Response($response, 200, array('Content-Type' => 'Application/xml')); 
    }
    
    /**
     * [tourneeModifAction Page d'affichage de la liste des tournées en fonction du dépot et de la date]
     * @param  [type] $id   [description]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function tourneeModifAction($id,$date,$flux)
    {
        //Récupération des données envoyés dans le formulaire
        $request = $this->getRequest();
        $disabled = $request->query->get('isGridReadOnly');

        $filtre = $request->request->get('ams_distributionbundle_filtreouverture');
        //Appel de doctrine + récupération du dépot en fonction de l'id renvoyée
        $em = $this->getDoctrine()->getManager();
        $depot  = $em->getRepository('AmsSilogBundle:Depot')->findOneById($id);
        //Création du formulaire de filtrage
        $form = $this->createForm(new FiltreOuvertureType());
        
        //Affichage de la bonne date dans le filtre
        if ($filtre['filtre'] && $_POST) {
            $affichage_date = $filtre['filtre'];
             $flux = $filtre['flux'];
        } else {
            $affichage_date = $date;
             $flux = $flux;
        }
        
        $formAttachment = $this->createFormBuilder()
            ->add('typeAnomalie', 'entity', array(
                'label' => 'Retard/Non Liv.',
                'data' => '',
                'class' => 'AmsDistributionBundle:CptrTypeAnomalie',
                 'query_builder' => function(EntityRepository $er) {
                                                                    return $er->createQueryBuilder('c')
                                                                              //->where('c.code != 0')
                                                                              ->orderBy('c.libelle', 'ASC');},
                'property' => 'libelle',
                'multiple' => false,
                'required' => false
            ))
            ->add('typeIncident', 'entity', array(
                'data' => '',
                'label' => 'Type d\'incident',
                'class' => 'AmsDistributionBundle:CptrTypeIncident',
                'property' => 'libelle',
                'multiple' => false,
                'required' => false
            ))
            ->add('heureFinDeTournee', 'text', array(
                'label' => 'Heure de fin de tournée',
                'max_length' => 8,
                'attr' => array(
                    'size' => 8,
                    'placeholder' => '.. : .. ',
                ),
                'required' => false
            ))  
            ->add('cmtIncidentAb', 'textarea', array(
                'label' => 'Incident Abonné',
                'required' => false
            ))    
            ->add('cmtIncidentDiff', 'textarea', array(
                'label' => 'Incident Diffuseur',
                'required' => false
            ))
            ->getForm();

        return $this->render('AmsDistributionBundle:CompteRendu:DistributionTournee.html.twig', array(
            'form' => $form->createView(),
            'formAttachment' => $formAttachment->createView(),
            'affichage_date' => $affichage_date,
            'depot_libelle' => $depot->getLibelle(),
            'depot_id' => $id,
            'flux' => $flux,
            'disabled'=>$disabled
                ));    
        
    }
    /**
     * [gridDistributionParDepotAction Création de la liste d'affichage des tournee en fonction du dépot et de la date]
     * @param  [type] $id
     * @param  [type] $date
     * @return [type]
     */
    public function gridDistributionParDepotAction($id, $date,$flux) {
        //Appel de doctrine
        $em = $this->getDoctrine()->getManager();
        $incidentsOptions = "";
        $depoIds[] = $id;
        $disabled = $this->getRequest()->query->get('isGridReadOnly');
        $count_rec_view = $em->getRepository('AmsDistributionBundle:CptrDistribution')->getCountReclamationByDepotAndTournee($date);
        // on récupère la liste des adresse rejetés qui sont sur les dépôts de l'utilisateur connecté selon la date du filtre
        //$liste = $em->getRepository('AmsDistributionBundle:CptrDistribution')->getInfosFromDepot($id, $date);
        $liste = $em->getRepository('AmsDistributionBundle:CptrDistribution')->getTableCompteRenduDistribution($date,$depoIds,$flux);

        $incidentList = $em->getRepository('AmsDistributionBundle:CptrTypeIncident')->findAll();
        $incidentsOptions = $this->getOptionList($incidentList, true);
        $anomalieList  = $em->getRepository('AmsDistributionBundle:CptrTypeAnomalie')->findAll();
        $anomalieOptions = $this->getOptionList($anomalieList, false);

        $response = $this->renderView('AmsDistributionBundle:CompteRendu:grid_distribution_tournee.xml.twig', array(
                                                        'liste' => $liste,
                                                        'options' => $anomalieOptions,
                                                        'affichage_date' => $date,
                                                        'id_depot' => $id,
                                                        'incidentsOptions'=>$incidentsOptions,
                                                        'disabled' => $disabled,
                ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml')); 
    }

       /**
     * [getOptionList description]
     * @param  [type] $aElement
     * @return [type]
     */
    public function getOptionList($aElement, $withEmptyOption){
       
        $optionList = ($withEmptyOption == true ) ? '<option value=""></option>' : "";
        foreach ($aElement as $key => $value) {
            $optionList .='<option value="'.$value->getId().'"> '.$value->getLibelle().'</option>';
        }
        return $optionList;
    }
    
    /**
     * [saveDistributionAction description]
     * @param  [type]  $repository [description]
     * @param  [type]  $date       [description]
     * @param  Request $request    [description]
     * @return [type]              [description]
     */
    public function saveDistributionAction($repository, $date, Request $request) 
    {
        //Appel de doctrine
        $em = $this->getDoctrine()->getManager();
        
        /*
         * Syntaxe des parameters
         * $_POST['ids'] => id de la variable modifiée
         * ex : 1_gr_id => id de la ligne 1, 1_depot => contenu de la colonne depot de la ligne 1
         */
        //Initialisation de la variable qui permet de connaitre quelle row est modifiée
        $rowId = $_POST[$_POST['ids'].'_gr_id'];
        $newId ='';
        $msg= '';
      
        //Récupération de l'id du dépot et de la tournée envoyés
        $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneById($_POST[$_POST['ids'].'_depot_id']);
       
        $_POST[$_POST['ids'].'_depot_id'] = $depot->getId();
        $tournee = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->findOneById($_POST[$_POST['ids'].'_tournee_id']);
        $typeIncident = $em->getRepository('AmsDistributionBundle:CptrTypeIncident')->findOneById($_POST[$_POST['ids'].'_type_incident']);
        
        //Vérification de l'action a effectuer (insert ou update)
        $exist = $em->getRepository($repository)->isNew($_POST, $date);
        if ($exist) {
            $result = $em->getRepository($repository)->update($_POST, $exist[0]['id']);
            $action = 'update';

        } else {
            $result = $em->getRepository($repository)->insert($_POST, $date);
            $action = 'insert';
        }
        
        //Afficher une erreur si un problème survient lors des requêtes précédentes
        if (!$result) {
            $action="error";
            $response = $this->render('::grid_action_error.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg, 'msg_complet' => $msgException));
        }else{
            $response = $this->render('::grid_action.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg));
        }
        
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
        
    }
    
    
    /**
     * [modifDistributionAction Fonction d'affichage du formulaire de modification/ajout d'exemplaires abonnés ou diffuseurs]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function modifDistributionAction(Request $request) {
        
        $affichage_date = $request->query->get('date_row');
        $depot_id = $request->query->get('id_depot');
        $tournee_id = $request->query->get('tournee_id');
        $row_id = $request->query->get('row_id');
        $em = $this->getDoctrine()->getManager();
        $data = array('ids' => $row_id, $row_id.'_depot_id' => $depot_id, $row_id.'_tournee_id' => $tournee_id );
    
        $exist = $em->getRepository('AmsDistributionBundle:CptrDistribution')->isNew($data, $affichage_date);
        if(!empty($exist)){
            $cptrDistribId = $exist[0]['id'];
        }else{
            $cptrDistribId = $em->getRepository('AmsDistributionBundle:CptrDistribution')->insertTocreateCptrDistribId($data, $affichage_date);
        }  
         
        return $this->render('AmsDistributionBundle:CompteRendu:modif_nb_exemplaires.html.twig', array(
            'affichage_date' => $affichage_date,
            'cptr_distribution_id' => $cptrDistribId,
            'row_id' => $row_id,
            'id_depot' => $depot_id,
            'tournee' => $tournee_id,
        )); 
    }
    
    public function modifMassiveAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod("POST")) {
          $dDay = new \DateTime();
          $dateTime = new \DateTime($request->get("date"));
          $dataForm = $request->get("form");
          $parentFiletr = $request->get('filter_attachment');
          $tourneeSelected = explode(',', $request->get("tourneeSelected"));
          $dHeureFinTournee = explode(':', $dataForm['heureFinDeTournee']);
          
          foreach($tourneeSelected as $tournee){
            $Cptr = $em->getRepository('AmsDistributionBundle:CptrDistribution')->findOneBy(array('dateCptRendu'=>$dateTime,'tournee'=>$tournee));
            if(!$Cptr){
               $Cptr = new CptrDistribution();
               $oDepot = $em->getRepository('AmsSilogBundle:Depot')->find($request->get("id_depot"));
               $Cptr->setDepot($oDepot);
               $oModeleTourneeJour = $em->getRepository('AmsModeleBundle:ModeleTourneeJour')->find($tournee);
               $Cptr->setTournee($oModeleTourneeJour);
               $Cptr->setDateCptRendu($dateTime);
            }
            if($parentFiletr != '6'){
                if(trim($dataForm['cmtIncidentAb']) != '')
                  $Cptr->setCmtIncidentAb($dataForm['cmtIncidentAb']);
                if(trim($dataForm['cmtIncidentDiff']) != '')
                  $Cptr->setCmtIncidentDiff($dataForm['cmtIncidentDiff']);
                if(trim($dataForm['typeIncident']) != ''){
                  $incident = $em->getRepository('AmsDistributionBundle:CptrTypeIncident')->find($dataForm['typeIncident']);
                  $Cptr->setTypeIncident($incident);
                }
                if(trim($dataForm['typeAnomalie']) != ''){
                  $anomalie = $em->getRepository('AmsDistributionBundle:CptrTypeAnomalie')->find($dataForm['typeAnomalie']);
                  $Cptr->setTypeAnomalie($anomalie);
                }
            }else{
                $anomalie = $em->getRepository('AmsDistributionBundle:CptrTypeAnomalie')->findOneByCode(0);//o est le code de RAS dans la table CptrTypeAnomalie
                $Cptr->setTypeAnomalie($anomalie);
            }
            if(trim($dataForm['heureFinDeTournee']) != '')
                $Cptr->setHeureFinDeTournee($dDay->setTime($dHeureFinTournee[0], $dHeureFinTournee[1]));

            $em->persist($Cptr);
            $em->flush();
          }
          return $this->redirect($this->generateUrl('compte_rendu_modif_tournee',array('id' => $request->get("id_depot") , 'date' => $request->get("date"),'flux' => $request->get("flux"))));
        }

    }

    /**
     * [gridExemplairesNonDistribuesAction description]
     * @param  [type] $date       [description]
     * @param  [type] $tournee_id [description]
     * @param  [type] $id_depot   [description]
     * @param  [type] $id_cptr    [description]
     * @return [type]             [description]
     */
    public function gridExemplairesNonDistribuesAction($date, $tournee_id,$id_depot, $id_cptr) {
        
        $em = $this->getDoctrine()->getManager();
        // on récupère la liste des exemplaires non livrés en fonction du produit selon la date du filtre et la tournée
        $liste = $em->getRepository('AmsDistributionBundle:CptrDetailExNonDistrib')->getListeProduitNonLivre($date,$tournee_id,$id_depot, $id_cptr);
        $response = $this->renderView('AmsDistributionBundle:CompteRendu:grid_cptr_distribution_detail.xml.twig', array('liste' => $liste,));
        
        return new Response($response, 200, array('Content-Type' => 'Application/xml')); 
    }

    
    /**
     * [saveExemplairesNonDistribuesAction description]
     * @param  [type]  $date    [description]
     * @param  [type]  $id_cptr [description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function saveExemplairesNonDistribuesAction($date, $id_cptr, Request $request) 
    {
        //Verification isnew
        $em = $this->getDoctrine()->getManager();
        $rowId = $_POST[$_POST['ids'].'_gr_id'];
        $newId ='';
        $msg= '';
        $msgException = '';
        if(!array_key_exists($_POST['ids'].'_heure_fin_tournee', $_POST)) {
            $_POST[$_POST['ids'].'_heure_fin_tournee'] = NULL;
        }
  
        $_POST[$_POST['ids'].'_produit'] = $_POST[$_POST['ids'].'_id'];
        $exist = $em->getRepository('AmsDistributionBundle:CptrDetailExNonDistrib')->isNew($_POST, $id_cptr, $date);
        
        if ($exist) {
            $cptr = $em->getRepository('AmsDistributionBundle:CptrDetailExNonDistrib')->find($exist[0]['id']);
            $cptr->setNbDiffNonLivre($_POST[$rowId.'_nb_ex_abo']);
            $cptr->setNbAbonneNonLivre($_POST[$rowId.'_nb_ab_non_livre']);
            $result = 'success';
            $action = 'update';
            $em->flush();
        } else {
            $dateCopie = new \DateTime($date);
            $cptr = new CptrDetailExNonDistrib();
            $cptr->setNbDiffNonLivre($_POST[$rowId.'_nb_ex_abo']);
            $cptr->setNbAbonneNonLivre($_POST[$rowId.'_nb_ab_non_livre']);
            $cptr->setQuantiteInitiale($_POST[$rowId.'_qte']); 
            $cptr->setDateCptRendu($dateCopie);
            $cptr->setNbExAbo(0);
            $cptr->setNbExDiff(0);
            
            $commune = $em->getRepository('AmsAdresseBundle:Commune')->find($_POST[$rowId.'_ville_id']);
            $societe = $em->getRepository('AmsProduitBundle:Societe')->find($_POST[$rowId.'_produit']);
            $cptrDistrib = $em->getRepository('AmsDistributionBundle:CptrDistribution')->find($id_cptr);
            $cptr->setCommuneId($commune);
            $cptr->setSociete($societe); 
            $cptr->setCptrDistribId($cptrDistrib);
            $em->persist($cptr);
            $em->flush();
            $result = 'success';
            $action = 'insert';
        }
        $cptrDistrib = $em->getRepository('AmsDistributionBundle:CptrDistribution')->find($id_cptr);
        $countAboNonLivre = $em->getRepository('AmsDistributionBundle:CptrDetailExNonDistrib')->getCountAbonneNonLivre($id_cptr);
        $countDiffNonLivre = $em->getRepository('AmsDistributionBundle:CptrDetailExNonDistrib')->getCountDiffNonLivre($id_cptr);
        $cptrDistrib->setNbAbonneNonLivre($countAboNonLivre[0]['somme']);
        $cptrDistrib->setNbDiffNonLivre($countDiffNonLivre[0]['somme']);
        $em->flush();
       
        if (!$result) {
            $action="error";
            $response = $this->render('::grid_action_error.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg, 'msg_complet' => $msgException));
        }else{
            $response = $this->render('::grid_action.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg));
        }
        
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
        
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
            $newVal = $array['libelle'];
            $singleArray[$newKey] = $newVal;
        }

        return $singleArray;
    }
}
