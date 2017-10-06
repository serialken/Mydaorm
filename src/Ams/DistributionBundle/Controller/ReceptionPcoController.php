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


class ReceptionPcoController extends DhtmlxController
{
    public function IndexAction()
    {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->getDoctrine()->getManager();
        $session  = $this->get("session");
        $affichage_date = $session->get('affichage_date');
        $request = $this->getRequest();
        $flux   = $request->query->get('flux');
        if(empty($affichage_date)){ 
            $oDate = new \DateTime('now');
            $affichage_date = $oDate->format('Y-m-d');
            $session->set('affichage_date', $affichage_date);
        }
        $oDepotPCO = $em->getRepository('AmsSilogBundle:Depot')->findOneByLibelle('PCO');
        $ofluxList = $em->getRepository('AmsDistributionBundle:CptrReception')->getAllProduitFlux();
        $fluxList = $this->transformArraysOnSingleArray($ofluxList);
      
        $form = $this->getForm($fluxList); 
        $isPosted = false; 
        if($request->getMethod() == 'POST'){
            $dataForm = $request->request->get('form');
            $affichage_date  = $dataForm['date'];
            $flux = $dataForm['flux'];
            $productsRecepInfo = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTableCompteRenduReception($affichage_date, $oDepotPCO->getId(), $flux,true);
            $isPosted = true; 
            $session->set('affichage_date', $affichage_date); 
        }

        return $this->render('AmsDistributionBundle:ReceptionPco:index.html.twig', array(
            'form' => $form->createView(),
            'depotId' => $oDepotPCO->getId(),
            'affichage_date' => $affichage_date,
            'flux' => !empty($flux) ? $flux: '',
            'productsRecepInfo'=>!empty($productsRecepInfo) ? $productsRecepInfo : "",
            'isPosted'=>$isPosted,
        ));    
    }
    
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
                    /** SI DATA EXISTE DEJA**/
                    if(!empty($val[2])){                       
                      
                        $cptrReception = $em->getRepository('AmsDistributionBundle:CptrReception')->findOneById($val[2]);
                        $cptrReception->setDateCptRendu($dateCptr);
                        $cptrReception->setQteRecue($dataForm['qteRecue_'.$productId.'_'.$val[2]]);
                        $cptrReception->setHeureReception($dateCptrReception);
                        $cptrReception->setCommentaires($dataForm['comment_'.$productId.'_'.$val[2]]);
                        if($action == 'valid' && !is_null($dateCptrReception)) 
                          $cptrReception->setNonModifiable(true);
                    }
                    /** SI DATA EXISTE PAS**/
                    else{
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
            $this->get('session')->getFlashBag()->add(
            'success_pco',
            'Vos changements ont été sauvegardés!'
            );

          return $this->redirect($this->generateUrl('compte_rendu_reception_pco'));
            
        }     
    }
    
    
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
    
    private function getForm($fluxlist){
        $form = $this->get('form.factory')->createBuilder()
            ->add('flux', 'choice', array(
                    'label'=>'Flux',
                    'choices'=>$fluxlist,
                    'empty_value'=>'Jour+Nuit',
                    'required' => false,
            ))
            ->add('date', 'date', array(
            'input' => 'datetime',
            'widget' => 'single_text',
            'label' => 'Date ',
            'mapped' => false,
            'required' => false,
            'attr' => array('class' => 'date'),
            ))
            ->getForm()
        ;
        
        return $form;
    }
   
}
