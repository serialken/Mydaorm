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
use HTML2PDF;


class EtiquetteController extends GlobalController {

  public function indexAction(){
    // verifie si on a droit d'acceder à cette page
    $bVerifAcces = $this->verif_acces();
    if ($bVerifAcces !== true) {
      return $bVerifAcces;
    }
        
    $session = $this->get('session');
    $allowDepId = array_keys($session->get('DEPOTS'));
    $em = $this->getDoctrine()->getManager();
    $sDepots = implode(',', $allowDepId);
    $depots = $em->getRepository('AmsSilogBundle:Depot')->getDepotOrderByOrdre($sDepots);
    $bRightBigLabel = in_array('IMPR_ETIQ', $this->getEltsAccessible()) ? true :false;

    $imprimante = $em->getRepository('AmsDistributionBundle:Imprimante')->getActiveAllowImp('1', $allowDepId);
    if (count ($imprimante) > 0){
        $flag = 'true';
    } else {
        $flag = 'false';
    }
    
    $request = $this->getRequest();
    if($request->isXmlHttpRequest()){
      $depot = $request->query->get('id_depot');
      $date  = $request->query->get('date');
      $flux  = $request->query->get('id_flux');
      $tournee  = $request->query->get('tournee');
      $oDepot = $em->getRepository('AmsSilogBundle:Depot')->findOneByCode($depot);
      if(isset($tournee) && $tournee!= ''){
        $data = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getProductsByDateTourneeId($tournee,$date);
        return $this->render('AmsDistributionBundle:Etiquette:produit.html.twig', array(
                    'data' => $data,
                    'date' => $date,
                    'bigLabel'=> $bRightBigLabel,
                ));
      }
      
      $tournees = $em->getRepository('AmsDistributionBundle:ClientAServirLogist')->getTourneeByLabelAbo($oDepot->getId(),$date, $flux);
      return $this->render('AmsDistributionBundle:Etiquette:ajax.html.twig', array(
                    'tournees' => $tournees,
                ));
    }
    return $this->render('AmsDistributionBundle:Etiquette:index.html.twig', array(
                                                                                'imprimantes' => $imprimante,
                                                                                'flag' => $flag,
                                                                                'depots' => $depots
                                                                                ));  
  }
  
  public function imprimAction(){
    $em = $this->getDoctrine()->getManager();
    $request = $this->getRequest();
    $date  = $request->query->get('date');
    $tournees  = explode(',',$request->query->get('tournee'));
    $ip  = $request->query->get('ip');
    $product  = $request->query->get('product');
    $format  = $request->query->get('format');
    $port = 9100;

    $labelservice = $this->container->get('ams_distribution.etiquetteservice');
    
    $sFile= '';
    $template = ($format == 'big') ? 'modele_big' : 'modele1' ;
    foreach($tournees as $tournee){
        if(!$tournee) continue;
        $query = $em->getRepository('AmsDistributionBundle:FeuillePortage')->findClient($product,$tournee,$date, ' - ');
        $firstOcurrence = current($query); 
        $sFile .= $labelservice->generer($query,$firstOcurrence['code_tournee'],$firstOcurrence['nom_porteur'],$template);
    }
    
    
    $folder = $this->container->getParameter('TAGS_DIR');
    $filename= $date.'_'.$tournee.'_etiquettes.txt';
    $file = $folder.$filename;
    if (file_exists($file)) {
        unlink($file);
    }
        
    $oFic = fopen($file, "w+");
    fputs($oFic,$sFile); 
    fclose($oFic);
    
    exec('cat '.$file .' | nc -w 2 '.$ip.' '.$port.'  2>&1 /dev/null');
    exit('done');
//    exec('nohup '.$this->container->getParameter('SCRIPTS_ROOT_DIR').'print_tag.sh '.$filename.' '.$ip.' 2>&1 /dev/null &');
  }

}