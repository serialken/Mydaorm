<?php

namespace Ams\DistributionBundle\Controller;

use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Ams\DistributionBundle\Form\ReportType;
use Ams\DistributionBundle\Form\ParutionSpecialeType;
use Ams\DistributionBundle\Entity\ParutionSpeciale;
use Ams\ExtensionBundle\Validator\Constraints\DatePosterieure;
use Ams\DistributionBundle\Entity\JourFerie;
use Ams\DistributionBundle\Entity\Reperage;
use Ams\DistributionBundle\Form\ReperageType;
use Symfony\Component\HttpFoundation\Request;
use HTML2PDF;

/**
 * Calendrier  controller.
 *
 */
class ReperageController extends GlobalController {

    public function gridAction() {
        $session = new Session();
        $dateMin = $session->get('ReperageDateMin');
        $dateMax = $session->get('ReperageDateMax');
        $depotAllowIds = array_keys($session->get('DEPOTS'));

        /** DATA REPERAGE TABLE * */
        $depots = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AmsSilogBundle:Depot')->getDepots($depotAllowIds);

        $societies = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AmsProduitBundle:Societe')->getSocietes();

        $reperage = $this->getDoctrine()
                ->getManager()
                ->getRepository('AmsDistributionBundle:Reperage');
        
        $total = $this->getDoctrine()
                ->getManager()
                ->getRepository('AmsDistributionBundle:Reperage'); //->getTotalReperages($depodId, $societeId, $dateMin, $dateMax);

        $tab = array();
        $nbTotal = array();
        $string = '';
        
        $dateMini = str_replace('/','-',$dateMin);
        $dateMinim = date("Y-m-d", strtotime($dateMini));
        $dateMaxi = str_replace('/','-',$dateMax);
        $dateMaxim = date("Y-m-d", strtotime($dateMaxi));
        
        foreach ($depots as $depot) {
            $string .= '<row>
                <cell>' . $depot->getLibelle() . '</cell>';
                $url = $this->get('router')->generate('reperage_detail', array('depot' => $depot->getId(), 'society' => 0));
                $t = $total->getTotalReperagesByDepot($depot->getId(), $dateMinim, $dateMaxim);
                $tr = $total->getTotalReponseReperagesByDepot($depot->getId(), $dateMinim, $dateMaxim);
                $value = "<![CDATA[<a href='" . $url . "'>" . intval($t[1]) ."(" . intval($tr[1]) . ")</a>]]>";
                $string.= '<cell>' . $value . '</cell>';
                $string .= '<cell> '. $depot->getId() . '</cell>';
                $string .= '<cell> 0 </cell>';
            foreach ($societies as $society) {
                    $ts = $total->getTotalReperagesBySociety($society->getId(), $dateMinim, $dateMaxim);
                    $nbTotal[] = intval($ts[1]);
                if( intval($ts[1]) != 0){
                    $url = $this->get('router')->generate('reperage_detail', array('depot' => $depot->getId(), 'society' => $society->getId()));
                    $query = $reperage->numberResponse($depot->getId(), $society->getId(), $dateMin, $dateMax);
                    $val = ($query) ? "<![CDATA[<a href='" . $url . "'>" . $query . "</a>]]>" : '-';
                    $string.= '<cell>' . $val . '</cell>';
                }
            }
        
            
            $string .= '</row>';
        }
        $tab[] = $string;

        $response = $this->renderView('AmsDistributionBundle:Reperage:grid.xml.twig', array(
            'companies' => $societies,
            'reperages' => $tab,
            'nbTotal' => $nbTotal,
        ));
        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }

    public function gridDetailAction() {
        $session = new Session();
        $dateMin = $session->get('DatePrevMin');
        $dateMax = $session->get('DatePrevMax');

        $request = $this->getRequest();
        $depot = $request->query->get('depot');
        $idSociety = $request->query->get('society');
        $filter = $request->query->get('filter');

        $oTournees = $this->getDoctrine()
                          ->getManager()
                          ->getRepository('AmsModeleBundle:ModeleTournee')->getListeTournee(array($depot),false);
        $sTournees = '';
        foreach($oTournees as $tournee)
            $sTournees .='<option value="'.$tournee->getId().'"> '.trim($tournee->getCode().'-'.$tournee->getLibelle()).'</option>';
        
        $oReponsetopage = $this->getDoctrine()
                                ->getManager()->getRepository('AmsReferentielBundle:RefReperageQualif')->findAll();
        $sReponse = '';
        foreach($oReponsetopage as $rep){
            $sReponse .='<option value="'.$rep->getId().'"> '.trim($rep->getLibelle()).'</option>';
        }
        /** DATA REPERAGE TABLE * */
        $reperages = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AmsDistributionBundle:Reperage')->getReperagesBySocietyDepot($depot, $idSociety, $dateMin, $dateMax, $filter);
        
        $response = $this->renderView('AmsDistributionBundle:Reperage:grid_detail.xml.twig', array(
            'reperages' => $reperages,
            'tournees' => $sTournees,
            'reponse' => $sReponse,
            'filter' => $filter,
        ));


        return new Response($response, 200, array('Content-Type' => 'Application/xml'));
    }
    
    public function listeAction() {

        // verifie si on a droit d'acceder à cette page
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $request = $this->getRequest();

        $form = $this->createFormBuilder()
                ->add('DateParutionMin', 'text', array('required' => true, 'label' => 'Date de début'))
                ->add('DateParutionMax', 'text', array('required' => true, 'label' => 'Date de fin'))
                ->getForm();

        $voirTableaux = "off"; //intialisation de la variable voirTableaux à off si je ne clique pas sur le bouton

        /** GET DATE(MIN&MAX) REPERAGE * */
        if ($request->getMethod() == 'POST') {
            $DataInform = $request->request->get('form');
            $dateMinToDisplay = $DataInform['DateParutionMin'];
            $dateMaxToDisplay = $DataInform['DateParutionMax'];

            $voirTableaux = "on"; //intialisation de la variable voirTableaux à on si je clique sur le bouton

        } else {
            $startDate = time();
            $dateMaxToDisplay = date('d/m/Y', strtotime('+60 day', $startDate));
            $dateMinToDisplay = date('d/m/Y');
        }

        $session = new Session();
        $session->set('ReperageDateMin', $dateMinToDisplay);
        $session->set('ReperageDateMax', $dateMaxToDisplay);
        
        $societies = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AmsProduitBundle:Societe')->getSocietes();

        
        $total = $this->getDoctrine()
                ->getManager()
                ->getRepository('AmsDistributionBundle:Reperage'); //->getTotalReperages($depodId, $societeId, $dateMin, $dateMax);
        
        $dateMin = str_replace('/','-',$dateMinToDisplay);
        $dateMini = date("Y-m-d", strtotime($dateMin));
        $dateMax = str_replace('/','-',$dateMaxToDisplay);
        $dateMaxi = date("Y-m-d", strtotime($dateMax));
        
        
        foreach ($societies as $society){
            $ts = $total->getTotalReperagesBySociety($society->getId(), $dateMini, $dateMaxi);
            $nbTotal[] = intval($ts[1]);
        }
        return $this->render('AmsDistributionBundle:Reperage:liste.html.twig', array(
                    'dateMin' => $dateMinToDisplay,
                    'dateMax' => $dateMaxToDisplay,
                    'form' => $form->createView(),
                    'nbTotal' => $nbTotal,
                    'post' => $voirTableaux
        ));
    }

    public function detailAction($depot, $society) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }

        $session = new Session();
        $dateMin=$session->get('ReperageDateMin');
        $dateMax=$session->get('ReperageDateMax');
        $datePrevMin = $dateMin;
        $datePrevMax = $dateMax;
        $session->set('toMakeDate', date('d/m/Y'));

        $this->get('session')->get('UTILISATEUR_ID');
        $populate = (isset($_POST['form']['Topage'])) ? $_POST['form']['Topage'] : 0;
        $filter = false;

        $form = $this->createFormBuilder()
                ->add('Topage', 'choice', array(
                    'choices' => array(
                        '0' => 'Tout',
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        '1' => 'Topé(s)',
                        '2' => 'Aucun',
                    ),
                    'multiple' => false, 'data' => $populate))
                ->add('DatePrevMin', 'text', array('required' => true, 'label' => 'Date previsionelle minimum'))
                ->add('DatePrevMax', 'text', array('required' => true, 'label' => 'Date previsionelle maximun'))
                ->getForm();

        

           $ObjSociety = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AmsProduitBundle:Societe')->find($society);


            $ObjDepot = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AmsSilogBundle:Depot')->find($depot);

         


        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            /** FORM REPERAGE FILTER  * */
            $postQuery = $request->request->all();
            if (isset($postQuery['form']['Topage']))
                $filter = $postQuery['form']['Topage'];
            
            $datePrevMin = $postQuery['form']['DatePrevMin'];
            $datePrevMax = $postQuery['form']['DatePrevMax'];
            $dateMin = $datePrevMin;
            $dateMax = $datePrevMax;       
        }
        
        $session->set('DatePrevMin', $datePrevMin);
        $session->set('DatePrevMax', $datePrevMax);

        return $this->render('AmsDistributionBundle:Reperage:detail.html.twig', array(
                    'depot' => $depot,
                    'society' => $society,
                    'ObjSociety' => $ObjSociety,
                    'ObjDepot' => $ObjDepot,
                    'form' => $form->createView(),
                    'date1' => $dateMin,
                    'date2' => $dateMax,
                    'datePrevMin' => $datePrevMin,
                    'datePrevMax' => $datePrevMax,
                    'filter' => $filter
        ));
    }

    public function reperageToPdfAction($depot, $society) {

        $this->deleteOldFolder('tmp');
        $session = new Session();
        $request = $this->getRequest();
        $dateMin=$session->get('ReperageDateMin');
        $dateMax=$session->get('ReperageDateMax');
        
        $aReperageId = ($request->get('aReperageId') =='') ? false : explode(',', $request->get('aReperageId'));
        $reperages = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AmsDistributionBundle:Reperage')->getReperagesForPdf($depot, $society, $dateMin,$dateMax,$aReperageId,$request->get('filter'));

        $html = $this->renderView('AmsDistributionBundle:Reperage:reperage_pdf.html.twig', array(
            'list' => $reperages
        ));

        
        $uniqId = 'tmp/'.uniqid();
        exec('mkdir '.$uniqId);
        $file    = $uniqId.'/reperage_sdvp.html';
        $filePdf = $uniqId.'/reperage_sdvp.pdf';
        $oFic = fopen($file, "w+");
        fputs($oFic,$html); 
        fclose($oFic);
        exec('wkhtmltopdf '.$file.' '.$filePdf);
        exit($uniqId);
    }
    
    
    private function deleteOldFolder($path){
        $doted = array(".", "..");
        $list = array_diff(scandir($path), $doted);
        foreach($list as $folder){
            if(is_dir($path.'/'.$folder)){
                $differenceInSeconds = time() - filectime($path.'/'.$folder);
                if($differenceInSeconds > 60 )
                    exec('rm -rf '.$path.'/'.$folder);
            }
       }
    }
    

    
    private function sendHeader($filename,$tmpFolder){
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($filename));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
//        exec('rm -rf '.$tmpFolder);
    }
    
     public function topageAction(Request $request) {
   
        $em = $this->getDoctrine();
        $id ='';
        if( $request->get('id'))
            $id = $request->get('id');

        $topage = $this->getDoctrine()
                        ->getManager()->getRepository('AmsReferentielBundle:RefReperageQualif')->findOneById($id);
        if (!$topage) {
            $cTopage = '';
        }
        else $cTopage = $topage->getTopage();
        
        $response = $this->renderView('AmsDistributionBundle:Reperage:topage.json.twig', array(
                    'topage' => $cTopage,
        ));
        
        return new Response($response, 200, array('Content-Type' => 'Application/json'));
       
    }
    
    
    public function crudAction(Request $request) {
        if(!$this->verif_acces()) return false;
		$em = $this->getDoctrine()->getManager();
		$newId = $msg = $msgException = $action ='';
		$result=true;
        $rowId = $request->get('gr_id');
        $id_reperage = $request->get('c0');
        
        $oReperage = $em->getRepository('AmsDistributionBundle:Reperage')->find($id_reperage);
        $oUser = $em->getRepository('AmsSilogBundle:Utilisateur')->find($this->get('session')->get('UTILISATEUR_ID'));
        
        if(is_numeric($request->get('c2'))){
            if($request->get('c2') == 0){
                $oReperage->setQualif(null);
                $oReperage->setTopage('');
            }
            else{
                $oQualif = $em->getRepository('AmsReferentielBundle:RefReperageQualif')->find($request->get('c2'));
                $oReperage->setQualif($oQualif);
                $oReperage->setTopage($oQualif->getTopage());
            }
        }
        
        if(is_numeric($request->get('c3'))){
            $oTournee = $em->getRepository('AmsModeleBundle:ModeleTournee')->find($request->get('c3'));
            $oReperage->setTournee($oTournee);
        }
        
        $oReperage->setCmtReponse($request->get('c4'));
        $oReperage->setUtlReponse($oUser);

        $em->flush();
        
        if (!$result) {
			$action="error";
			$response = $this->render('::grid_action_error.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg, 'msg_complet' => $msgException));
		}
		else
			$response = $this->render('::grid_action.html.twig', array('action' => $action, 'rowId' => $rowId, 'newId' => $newId, 'msg' => $msg));
	
		$response->headers->set('Content-Type', 'text/xml');
		return $response;
    }


}
