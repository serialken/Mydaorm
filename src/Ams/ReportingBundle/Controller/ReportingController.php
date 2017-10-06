<?php

namespace Ams\ReportingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ams\ReportingBundle\Form\FormReporting;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class ReportingController extends Controller
{    
    
//    fonction permettant l'affichage de la vue reporting global
    public function indexAction() {        
        $request =  $this->getRequest();
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        
        $form = $this->createFormBuilder()
                    ->add('DateParutionMin', 'text', array('required'=>true, 'label'=>'Date de début'))
                    ->add('DateParutionMax', 'text', array('required'=>true, 'label'=>'Date de fin'))
                    ->add('flux', 'choice', array('choices'=>array(0 => 'Choisissez un flux ...' ,1 => 'Nuit',2 => 'Jour'), 'label'=>'Flux'))
                    ->getForm();
        
        if($request->getMethod() == 'POST'){
            $formData =  $request->request->get('form');
            $dateMinToDisplay = $formData['DateParutionMin'];
            $dateMaxToDisplay = $formData['DateParutionMax'];
            $flux = $formData['flux'];
            
            if ($flux == "")
            {
                $flux = 0;
            }
        }else{
            $dateMinToDisplay =  $dateMaxToDisplay = Date('d-m-Y', strtotime('-1 day'));
            $flux = 0;
        }
//        $aRecapResults = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getPaieRecapInfo('2015-01-16', '2015-01-17');
//            
//        $em->getRepository('AmsReportingBundle:ReportPilotageCentre')->insertDataReporting($aRecapResults);
//        
////        var_dump($aRecapResults);exit;
        
        return $this->render('AmsReportingBundle:Reporting:index.html.twig', array('form'=>$form->createView(),
                                                                                    'dateMin'=>$dateMinToDisplay,
                                                                                    'dateMax'=>$dateMaxToDisplay,
                                                                                    'flux'=>$flux
                                                                                   ));
    }
//    Fonction permettant l'affichage de la vue reporting detail
    public function indexDetailAction(Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
                    ->add('DateParutionMin', 'text', array('required'=>true, 'label'=>'Date de début'))
                    ->add('DateParutionMax', 'text', array('required'=>true, 'label'=>'Date de fin'))
                    ->add('flux', 'choice', array('choices'=>array(0 => 'Choisissez un flux ...' ,1 => 'Nuit',2 => 'Jour'), 'label'=>'Flux'))
                    ->getForm(); 
        
        if($request->getMethod() == 'POST'){
            $formData =  $request->request->get('form');
            $dateMinToDisplay = $formData['DateParutionMin'];
            $dateMaxToDisplay = $formData['DateParutionMax'];
            $flux = $formData['flux'];
            
            if ($flux == "")
            {
                $flux = 0;
            }
        }else{
            $dateMinToDisplay =  $dateMaxToDisplay = Date('d-m-Y');
            $flux = 0;
        }
        
        return $this->render('AmsReportingBundle:Reporting:indexDetail.html.twig', array('form'=>$form->createView(),
                                                                                    'dateMin'=>$dateMinToDisplay,
                                                                                    'dateMax'=>$dateMaxToDisplay,
                                                                                    'flux'=>$flux,
                                                                                    'depotId'=>$request->get('depot')
                                                                                   ));
    }
    
     
    /**
     * @return XML  liste des centres
     * Sert a remplir le grid global
    */
    public function gridAction(Request $request) {        
        
        $em = $this->getDoctrine()->getManager();
        
        $flux = $request->get('flux');
        $dateMin = date('Y-m-d',strtotime($request->get('dateMin')));
        $dateMax = date('Y-m-d',strtotime($request->get('dateMax')));
        
        $session = new Session();
        $session->set('crmDateMin', $dateMin);
        $session->set('crmDateMax', $dateMax);
        $depots      = $this->get('session')->get('DEPOTS');
        $sDepotId = array_keys($depots);

        $reportings = $em->getRepository('AmsReportingBundle:ReportPilotageCentre')->sumAbo($dateMin, $dateMax, $flux, $sDepotId);

        $response = $this->renderView('AmsReportingBundle:Reporting:grid_reporting.xml.twig', array(
            'reportings' => $reportings,
            'dateMin' => $dateMin,
            'dateMax' => $dateMax,
            'flux' => $flux
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }
    
    /**
     * @return XML  liste des tournées
     * Sert a remplir le grid detail
    */
    public function gridDetailAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();        
        $depot = $request->get('depot');
        $flux = $request->get('flux');
        $dateMin = date('Y-m-d',strtotime($request->get('dateMin')));
        $dateMax = date('Y-m-d',strtotime($request->get('dateMax')));
        
        $session = new Session();
        $session->set('crmDateMin', $dateMin);
        $session->set('crmDateMax', $dateMax);
        $depots      = $this->get('session')->get('DEPOTS');
        $sDepotId = array_keys($depots);

        $reportings = $em->getRepository('AmsReportingBundle:ReportPilotageCentre')->majDetailDepot($depot, $dateMin, $dateMax, $flux, $sDepotId);

        $response = $this->renderView('AmsReportingBundle:Reporting:grid_reporting_detail.xml.twig', array(
            'reportings' => $reportings,
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
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
     * [transformDateToDataBaseFormat transforme la date au format définit en base de données
     * @param  [string] $date  [valeur de date]
     * @return [string]        [valeur de date edans le format définit en base de données]
     */
    
    private function transformDateToDataBaseFormat($date, $delim, $delim2=null){
        $delim1 = empty($delim2) ?  $delim : $delim2;
        $dateItems = explode($delim, $date);
   
        return   $dateItems[2].$delim1.$dateItems[1].$delim1.$dateItems[0];
    }
}   

