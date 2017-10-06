<?php
namespace Ams\DistributionBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Ams\SilogBundle\Controller\GlobalController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use \DateTime;

use PHPExcel_Worksheet_PageSetup;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Cell_DataType;

use Ams\DistributionBundle\Form\FiltreFeuillePortageType;

class FeuillePortageController extends GlobalController {

    public function listeAction(Request $request) {
        $bVerifAcces = $this->verif_acces();
        if ($bVerifAcces !== true) {
            return $bVerifAcces;
        }
        $em = $this->getDoctrine()->getManager();
        $this->setDerniere_page();
        $session = $this->get('session');

        $depots = $session->get('DEPOTS');
        $fluxs = $session->get('FLUXS');
        $date_distrib = date('Y-m-d', time());
        $depot_id = $flux_id = $bordereaufile =  $cpamfile = $depotCode='';
        
        $form = $this->createForm(new FiltreFeuillePortageType($depots, $fluxs, $date_distrib)); 
 
        
        $form->handleRequest($request);
        $tourneeBydepot = $aProductBydepot = '';
        if ($request->getMethod() == 'POST') {
            $depot_id = $form->getData()['depot_id'];
            $flux_id = $form->getData()['flux_id'];
            $date_distrib = $form->getData()['date_distrib'];
            $session->set("depot_id", $form->getData()['depot_id']);
            $session->set("flux_id", $form->getData()['flux_id']);
            $session->set("date_distrib", $form->getData()['date_distrib']);
            $oDepot = $em->getRepository('AmsSilogBundle:Depot')->find($depot_id);
            $tourneeBydepot = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneeByDepotByDate($depot_id,$date_distrib,$flux_id);
            $aProductBydepot = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getProductByDepotByDate($depot_id,$date_distrib,$flux_id);
            $depotCode = $oDepot->getCode();
        }
        
        $doted = array(".", "..");
    
        //calculer le code depot
        $dir = $this->container->getParameter('REP_FEUILLE_PORTAGE') .'/'. $date_distrib;
        $dirBordereau = $this->container->getParameter('REP_FEUILLE_PORTAGE') .'/'. $date_distrib.'/bordereau';
        $dirCpam = $this->container->getParameter('REP_FEUILLE_PORTAGE') .'/'. $date_distrib.'/cpam';
        //$dirDispatch = $this->container->getParameter('REP_FEUILLE_PORTAGE') .'/'. $date_distrib.'/dispatch/tmp';
        $liste = array();
        if(is_dir($dir)) {
            $files = array_diff(scandir($dir), $doted);
            foreach ($files as $file) {
                if(!is_file($dir.'/'.$file)) continue;
                
                $tmp = explode('_', $file);
                $depot = $em->getRepository('AmsSilogBundle:Depot')->findOneByCode($tmp[0]);
                if(isset($tmp[2])) { // pour éviter les erreurs avant la fin de la génération 
                        if (($tmp[2] == str_replace('-', '', $date_distrib) ) && ($depot_id == $depot->getId()) && ($tmp[1] == $flux_id) ) {

                            $fileInfo = array();
                            $fileInfo["depot"] = $depots[$depot->getId()];
                            $fileInfo["flux"] = ($tmp[1] == 1) ? "Nuit" : "Jour";
                            $fileInfo['date_distrib'] = $tmp[2];
                            $fileInfo["file"] = $file;

                            $liste[] = $fileInfo;
                        }
                }
            }
        }
        
        if ($request->getMethod() == 'POST') {
            $bordereaufiles = '';
            if(is_dir($dirBordereau)) {
                $bordereaufiles = array_diff(scandir($dirBordereau), $doted);
                foreach ($bordereaufiles as $file) {
                    if($oDepot->getCode() == substr($file, 0,3)){
                       $aSegmentString = explode('_', $file);
                        $flux = $aSegmentString[1];
                        if($flux_id == $flux)
                            $bordereaufile = $file;
                    }
                }
            }
            
            
            
            $cpamfiles = '';
            if(is_dir($dirCpam)) {
                $cpamfiles = array_diff(scandir($dirCpam), $doted);
                foreach ($cpamfiles as $file) {
                    if($oDepot->getCode() == substr($file, 0,3)){
                       $aSegmentString = explode('_', $file);
                        $flux = $aSegmentString[1];
                        if($flux_id == $flux)
                            $cpamfile = $file;
                    }
                }
            }
            
            
        }
        return $this->render('AmsDistributionBundle:FeuillePortage:liste.html.twig', array(
           'form' => $form->createView(),
           'liste' => $liste,
           'bordereaufiles' => $bordereaufile,
            'cpamfiles' => $cpamfile,
           'tourneeBydepot' => $tourneeBydepot,
           'aProductBydepot' => $aProductBydepot,
           'flux' => $flux_id,
           'depot_code' => $depotCode,
           'depot_id' => $depot_id,
           'date' => $date_distrib,
           'path' => $dirBordereau
        ));
    }
    
    
    
 /* !!!!!  
  * private function getQteByProduct($data,$product){
        $quantite = 0;
        $libelle = '';
        foreach($data as $d){
            if($d['id_produit'] != $product) continue;
            $quantite += $d['qte'];
            $libelle = $d['product_libelle'];
        }
        $data = array('libelle'=>$libelle,'quantite' => $quantite);
        return $data;
    }
    
     private function calculQte($aQte){
        $qte = 0;
        foreach($aQte as $key=>$val)
            $qte += $key * $val;
     
        return $qte;
    } */
    
    
   
    private  function setCellQte($cell, $objWorkSheet, $paquetTournees, $indSheet, $k,$qteProduitTournee,$produit_id ) {      
        if (isset($paquetTournees[$indSheet][$k]) && key_exists($paquetTournees[$indSheet][$k]['id'], $qteProduitTournee[$produit_id]))
          return   $objWorkSheet->setCellValue("$cell", isset($paquetTournees[$indSheet][$k]['id']) ? $qteProduitTournee[$produit_id][$paquetTournees[$indSheet][$k]['id']] : 0);
        else
         return   $objWorkSheet->setCellValue("$cell", 0);
        
    }
    private  function setQte($paquetTournees, $indSheet, $k,$qteProduitTournee,$produit_id ) {      
        if (isset($paquetTournees[$indSheet][$k]) && key_exists($paquetTournees[$indSheet][$k]['id'], $qteProduitTournee[$produit_id]))
          return   isset($paquetTournees[$indSheet][$k]['id']) ? $qteProduitTournee[$produit_id][$paquetTournees[$indSheet][$k]['id']] : 0;
        else
         return   0;
    }
    

    /**
     * Dispatch au format excel généré à la volet
     *  
     */
    public function dispatchExcelAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $dateDistrib = $request->query->get('dateDistrib');
        $codeDepot = $request->query->get('depotCode');
        $fluxId = $request->query->get('fluxId');
        $depotId = $request->query->get('depotId');
        $disPatchTournees = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getDataDispatchTourneeExcel($codeDepot, $dateDistrib, $fluxId);
        $allTournees = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneesByDepotByDate($depotId, $dateDistrib, $fluxId); // toutes les tournees possible

        $produits = array();
        $produits = array_combine(array_unique($this->array_column($disPatchTournees, 'produit_id')), array_unique($this->array_column($disPatchTournees, 'libelle_produit')));

        $qteProduitTournee = array();
        foreach ($disPatchTournees as $disPatchTournee) {
            $qteProduitTournee[$disPatchTournee['produit_id']][$disPatchTournee['tournee_jour_id']] = $disPatchTournee['qte'];
        }
        
        $qteProduitTourneeTotal = array();
        foreach ($disPatchTournees as $disPatchTournee) {
            $qteProduitTourneeTotal[$disPatchTournee['produit_id']][$disPatchTournee['tournee_jour_id']] = $disPatchTournee['total_qte'];
        }

        $typeTournee = $aTournees = array();
        foreach($allTournees as $tournee){
            $needle = substr($tournee['code'],0,3);
            $aTournees[$needle][] = $tournee;
            if(!in_array($needle, $typeTournee)){
                $typeTournee[] = $needle;
            }
        }

        /**  On contruit des paquets de 16 tournees par type de tournee */
        $paquetTournees = array();
        for($i=0 ; $i<count($typeTournee);$i++){
            $key = $typeTournee[$i];
            $paquetTournees= array_merge($paquetTournees,array_chunk($aTournees[$key], 16));
        }

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Mroad")->setDescription("Dispatch quantité");
        $phpExcelObject->getDefaultStyle()->getFont()->setName('Arial');
        
       
        /** Style des cellules */
        $styleArray = array(
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THICK,
                                'color' => array('argb' => '#8392CD'),
                        ),
                ), 
        ); 

        $styleTournee = array(
                'font' => array(
                    'bold' => true,
                    'size' => 18
                ),
         );
          
         $styleQte = array(
                'font' => array(
                    'bold' => true,
                    'size' => 16,
                ),
                'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
             ),
         );
        
         $styleTitre = array(
                'font' => array(
                    'bold' => true,
                    'size' => 25,
                ),
             'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
             ),

         );
        
        $styleHeader = array(
                'font' => array(
                    'bold' => true,
                    'size' => 30,
                   
                ),
             'fill' => array(
		'type' => PHPExcel_Style_Fill::FILL_SOLID,
                 'color' => array('argb' => 'FF729fcf'),
		
            ),
           'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
             ),
        );

        $indSheet = 0;
        $lettre = array("A", "B", "C", "D", "E", "F", "G", "H");
        for ($indSheet = 0; $indSheet < count($paquetTournees); $indSheet ++) {
            if ($indSheet == 0) {
                $objWorkSheet = $phpExcelObject->getActiveSheet();
            } else {
                $objWorkSheet = $phpExcelObject->createSheet($indSheet);
            }
            

      /*   $objWorkSheet->getPageMargins()
                ->setTop(0.75)
                ->setLeft(0.5)
                ->setRight(0);*/
      
           $objWorkSheet->getPageMargins()->setTop(0.5);
           $objWorkSheet->getPageMargins()->setRight(0);
           $objWorkSheet->getPageMargins()->setLeft(0);
           $objWorkSheet->getPageMargins()->setBottom(0.5);
           $objWorkSheet->getDefaultRowDimension()->setRowHeight(30);
  
           for($l =0; $l < 8; $l++){     
               if($l%2)
                   $objWorkSheet->getColumnDimension($lettre[$l])->setWidth(10);
               else 
                    $objWorkSheet->getColumnDimension($lettre[$l])->setWidth(13);      
           }
     
            $libelle =substr($paquetTournees[$indSheet]['0']['code'], 1)." à ".substr($paquetTournees[$indSheet][count($paquetTournees[$indSheet]) -1]['code'], 1);
            $objWorkSheet->getRowDimension('1')->setRowHeight(100);
            $objWorkSheet->mergeCells("A1:H1");
            $objWorkSheet->setCellValue("A1", "Dispatcheur ".$libelle);
          
            $objWorkSheet->getStyle("A1")->applyFromArray($styleHeader);
            $i = 2;
            
            $break = 1;
            foreach ($produits as $produit_id => $produit_libelle) {
                $objWorkSheet->mergeCells("A$i:H$i");
                $objWorkSheet->getRowDimension("$i")->setRowHeight(50);
                $objWorkSheet->getStyle("A$i")->applyFromArray($styleTitre);
                 
                 $k = 0;
                 $qte = 0;
                 for ($j = $i + 1; $j < $i + 5; $j = $j + 4) {
                     for($l =0; $l < 8; $l++){     
                          if($l%2 ==0) {
                            $objWorkSheet->getStyle("$lettre[$l]$j:$lettre[$l]".($j+3))->applyFromArray($styleTournee); 
                            $objWorkSheet->getRowDimension("$i")->setRowHeight(40);
                            $objWorkSheet->setCellValue("$lettre[$l]$j", isset($paquetTournees[$indSheet][$k]) ? $paquetTournees[$indSheet][$k]['code'] : '-');
                            $objWorkSheet->setCellValue("$lettre[$l]".($j+1), isset($paquetTournees[$indSheet][$k+1]) ? $paquetTournees[$indSheet][$k+1]['code'] : '-');
                            $objWorkSheet->setCellValue("$lettre[$l]".($j+2), isset($paquetTournees[$indSheet][$k+2]) ? $paquetTournees[$indSheet][$k+2]['code'] : '-');
                            $objWorkSheet->setCellValue("$lettre[$l]".($j+3), isset($paquetTournees[$indSheet][$k+3]) ? $paquetTournees[$indSheet][$k+3]['code'] : '-');
                             
                          }
                        else {
                                $objWorkSheet->getStyle("$lettre[$l]$j:$lettre[$l]".($j+3))->applyFromArray($styleQte);
                                $this-> setCellQte("$lettre[$l]$j", $objWorkSheet, $paquetTournees, $indSheet, $k,$qteProduitTournee,$produit_id );
                                $this-> setCellQte("$lettre[$l]".($j+1), $objWorkSheet, $paquetTournees, $indSheet, $k+1,$qteProduitTournee,$produit_id );
                                $this-> setCellQte("$lettre[$l]".($j+2), $objWorkSheet, $paquetTournees, $indSheet, $k+2,$qteProduitTournee,$produit_id );
                                $this-> setCellQte("$lettre[$l]".($j+3), $objWorkSheet, $paquetTournees, $indSheet, $k+3,$qteProduitTournee,$produit_id );
                                
                                $qte +=$this->setQte($paquetTournees, $indSheet, $k,$qteProduitTourneeTotal,$produit_id );
                                $qte +=$this->setQte($paquetTournees, $indSheet, $k+1,$qteProduitTourneeTotal,$produit_id );
                                $qte +=$this->setQte($paquetTournees, $indSheet, $k+2,$qteProduitTourneeTotal,$produit_id );
                                $qte +=$this->setQte($paquetTournees, $indSheet, $k+3,$qteProduitTourneeTotal,$produit_id );
                                $k = $k+4;
                        }

                     }
                 }
                 
                $objWorkSheet->setCellValue("A$i", $produit_libelle.' ('.$qte.')');
            
                $i = $i + 5;
                if($break % 4==0){
                    $objWorkSheet->mergeCells("A$i:H$i");
                    $objWorkSheet->setCellValue("A$i", "Dispatcheur ".$libelle);
                    $objWorkSheet->getRowDimension("$i")->setRowHeight(100);
                    $objWorkSheet->getStyle("A$i")->applyFromArray($styleHeader);
                    $i =$i+1;
                }
                $break++;
            }
            $objWorkSheet->getStyle("A1:H".($i-1))->applyFromArray($styleArray);
            $objWorkSheet->setTitle("$libelle");
        }

        $phpExcelObject->setActiveSheetIndex(0);
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=dispatch_quantite_' . $dateDistrib . '_' . $codeDepot . '_' . ($fluxId == 1 ? 'Nuit' : 'Jour') . '.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        return $response;
    }
    
    
    
    public function downloadAction(Request $request) {

        $filename = $request->get('file');
        $bordereau = $request->get('bordereau');
        $cpam = $request->get('cpam');
        $session = $this->get('session');

        $dir = $this->container->getParameter('REP_FEUILLE_PORTAGE').'/';
        if($bordereau)
          $file = $dir . $session->get("date_distrib") . '/bordereau/' . $filename; 
        else if($cpam)
          $file = $dir . $session->get("date_distrib") . '/cpam/' . $filename; 
        else
            $file = $dir . $session->get("date_distrib") . '/' . $filename;
        $content = file_get_contents($file);

        $response = new Response();

        //set headers
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename="' .$filename);

        $response->setContent($content);
        return $response;
    }
    
    /**
     * GENERATION DE FICHIER PDF A LA VOLEE
     * PAR TOURNEE,PRODUIT
     * @param Request $request
     */
    public function generationAction(Request $request) {
        $session = new Session();
        $userId = $session->get('UTILISATEUR_ID');
        
        $tournees = $request->get('tournees');
        $product = ($request->get('product'))? '--product='.implode(',', $request->get('product')): '';
        $flux = $request->get('flux');
        $depot = $request->get('depot');
        $dateDistib = new DateTime($request->get('date'));
        $date  = new DateTime(date('Y-m-d'));
        $dDiff = $date->diff($dateDistib);
        $J = 'J'.$dDiff->format('%R').$dDiff->days;
        $task = uniqid();
        $path = md5($userId).'/Feuille_Portage/'.$task.'/file.pdf';
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AmsSilogBundle:Documents')->insert($userId,$task,$path,date('Y-m-d H:i:s'),'Generation de feuille de portage');
         
        if(!empty($tournees)){
            $paramEnv = array();
            $sCmd = 'php ';
            $rootDir = $this->get('kernel')->getRootDir();
            if($this->container->get( 'kernel' )->getEnvironment() == 'local'){
                $rootDir = $this->container->getParameter('REP_APP').'/app';
            }
            if($this->container->get( 'kernel' )->getEnvironment() == 'prod'){
                $paramEnv = array('sudo' => 'mroad');
            }
            
            $sCmd .= $rootDir
                  . '/console feuille_portage '.$J.' '.$J.' --flux='.$flux.' --trn='.implode(',',$tournees).' --cd='.$depot.' '
                  . ' --task '.$task.' --user '.$userId.' --env ' . $this->get('kernel')->getEnvironment().' '.$product;
            GlobalController::bgCommandProxy($sCmd,$paramEnv);
            echo $sCmd;
        }
        exit;
    }

}